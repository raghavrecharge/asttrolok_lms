<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Cashback\CashbackAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductOrder;
use App\Models\OrderAddress;
use App\Models\Webinar;
use App\Models\TransactionsHistoryRazorpay;
use App\Jobs\BuyNowProcessJob;
use Razorpay\Api\Api;
use App\Models\Sale;
use App\Models\Accounting;
use App\Models\SubscriptionAccess;
use App\Models\Subscription;
use App\Models\SubscriptionPayments;
use App\Models\WebinarPartPayment;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\Discount;
use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\PaymentChannel;
use App\Models\ReserveMeeting;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderPayment;
use App\Mixins\Installment\InstallmentPlans;
use App\PaymentChannels\ChannelManager;
use App\Models\Cart;
use App\Models\Api\User;
use App\Models\Affiliate;
use Illuminate\Support\Facades\Hash;

class PaymentController extends Controller
{
    protected $razorpayApi;
    protected $order_session_key = 'payment.order_id';

    public function __construct()
    {
        $this->razorpayApi = new Api(
            env('RAZORPAY_API_KEY'),
            env('RAZORPAY_API_SECRET')
        );
    }

    public function initiatePayment(Request $request)
    {
        Log::info('initiatePayment');
        $validated = $request->validate([
            'payment_type' => 'required|in:subscription,webinar,cart,part,meeting,product,bundle,installment',
            'item_id' => 'required',
            'name' => 'required|string',
            'email' => 'required|email',
            'number' => 'required',
            'discount_id' => 'nullable|integer',
            'installment_id' => 'nullable|integer',
            'selectedDay' => 'nullable',
            'selectedTime' => 'nullable|integer',
            'amount' => 'nullable',
        ]);
        session()->forget('meeting_discount_id');
        session()->forget('discountCouponId');
        session()->forget('discount_id');

        try {
            DB::beginTransaction();

            $paymentData = $this->getPaymentData($validated);

            $order = $this->createOrder($paymentData);

            $input = $request->all();
            if (!empty($input['Country'])) {
                    OrderAddress::create([
                        'order_id' => $order->id,
                        'RecipientName' => $input['name'] ?? null,
                        'City' => $input['City'] ?? null,
                        'StateProvince' => $input['StateProvince'] ?? null,
                        'PostalCode' => $input['pin_code'] ?? null,
                        'Country' => $input['Country'] ?? null,
                        'PhoneNumber' => $input['number'] ?? null,
                        'Address' => $input['address'] ?? null,
                        'message' => $input['message'] ?? null,
                    ]);
                }

            $razorpayOrder = $this->createRazorpayOrder($order, $paymentData);

            DB::commit();

            return response()->json(array_merge([
                'success' => true,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'order_id' => $order->id,
                'key' => env('RAZORPAY_API_KEY'),
            ],$paymentData));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment initiation failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function getPaymentData($validated)
    {
        Log::info('getPaymentData');
        $type = $validated['payment_type'];
        $itemId = $validated['item_id'];
        $discountId = $validated['discount_id'];

        switch ($type) {
            case 'subscription':
                $subscription = Subscription::findOrFail($itemId);
                return [
                    'type' => 'subscription',
                    'item' => $subscription,
                    'subscription_id' => $subscription->id,
                    'discount' => 0,
                    'amount' => $subscription->getPrice(),
                    'description' => "Subscription: {$subscription->title}",
                    'user_data' => $validated,
                ];

            case 'webinar':
                $webinar = Webinar::findOrFail($itemId);

                $itemPrice = $webinar->getPrice();
                $price = $webinar->price;
                if($discountId > 0){
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 1;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }
                return [
                    'type' => 'webinar',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'discount' => $totalDiscount,
                    'amount' => $itemPrice1 ?? $webinar->price,
                    'description' => "Course: {$webinar->title}",
                    'user_data' => $validated,
                ];

            case 'part':
                $webinar = Webinar::findOrFail($itemId);

                $itemPrice = $webinar->getPrice();
                $price = $webinar->price;
                if($discountId > 0){
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 1;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }
                return [
                    'type' => 'part',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'discount' => $totalDiscount,
                    'installment_id' => $validated['installment_id'],
                    'amount' => $validated['amount'],
                    'description' => "Course: {$webinar->title}",
                    'user_data' => $validated,
                ];

            case 'cart':
                $order = Order::findOrFail($itemId);
                $user = auth()->user();

                if(empty($user)){
                    $input=$validated;
                    $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();

                    if(empty($user)){
                         $user = User::create([
                        'role_name' => 'user',
                        'role_id' => 1,
                        'mobile' => $input['number'],
                        'email' => $input['email'],
                        'full_name' => $input['name'],

                        'status'=>'active',
                        'access_content' => 1,
                        'password' => Hash::make(123456),
                        'pwd_hint' => 123456,
                        'affiliate' => 0,
                        'timezone' => 'Asia/Kolkata' ?? null,
                        'created_at' => time()
                      ]);
                    }

                }
                $userId = $user->id;
                $order->update(['user_id' => $userId]);

                $order->orderItems()->update(['user_id' => $userId]);

                return [
                    'type' => 'cart',
                    'item' => $order,
                    'discount' => 0,
                    'amount' => $order->total_amount,
                    'description' => "Cart Order #{$order->id}",
                    'user_data' => $validated,
                ];

            case 'bundle':
                $bundle = Bundle::findOrFail($itemId);

                return [
                    'type' => 'bundle',
                    'item' => $bundle,
                    'bundle_id' => $bundle->id,
                    'discount' => 0,
                    'amount' => $bundle->getPrice(),
                    'description' => "Bundle: {$bundle->title}",
                    'user_data' => $validated,
                ];

            case 'product':
                $product = Product::findOrFail($itemId);

                return [
                    'type' => 'product',
                    'item' => $product,
                    'product_id' => $product->id,
                    'discount' => 0,
                    'amount' => $product->getPrice(),
                    'description' => "Product: {$product->title}",
                    'user_data' => $validated,
                ];

            case 'meeting':

                $user = auth()->user();

                if(empty($user)){
                    $input=$validated;
                    $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();

                    if(empty($user)){
                         $user = User::create([
                        'role_name' => 'user',
                        'role_id' => 1,
                        'mobile' => $input['number'],
                        'email' => $input['email'],
                        'full_name' => $input['name'],

                        'status'=>'active',
                        'access_content' => 1,
                        'password' => Hash::make(123456),
                        'pwd_hint' => 123456,
                        'affiliate' => 0,
                        'timezone' => 'Asia/Kolkata' ?? null,
                        'created_at' => time()
                      ]);
                    }

                }
                $userId = $user->id;
                $day = $validated['selectedDay'] ?? null;

                $fields = explode(',', $itemId);

                if (count($fields) == 2)
                {

                    $itemId = intval($fields[0]);
                    $slot_id = intval($fields[1]);

                }
                $meetingTime = MeetingTime::where('id', $itemId)
                    ->with('meeting')
                    ->first();

                $meeting = Meeting::findOrFail($meetingTime->meeting_id);

                $explodetime = explode('-', $meetingTime->time);

                $hours = isset($slot_id)?((strtotime($explodetime[1]) - strtotime($explodetime[0])) / 1800)/2:(strtotime($explodetime[1]) - strtotime($explodetime[0])) / 1800;
                $hourlyAmount = $meeting->amount;

                $itemPrice = (!empty($hourlyAmount) and $hourlyAmount > 0) ? ($hourlyAmount * $hours) : 0;

                if($discountId > 0){
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 1;
                $totalDiscount = (($itemPrice > 0) ? $itemPrice * $percent / 100 : 0);
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                $instructorTimezone = $meeting->getTimezone();

                $startAt = $this->handleUtcDate($day, $explodetime[0], $instructorTimezone);
                $endAt = $this->handleUtcDate($day, $explodetime[1], $instructorTimezone);

                $reserveMeeting = ReserveMeeting::updateOrCreate([
                                'user_id' => $user->id,
                                'meeting_time_id' => $meetingTime->id,
                                'meeting_id' => $meetingTime->meeting_id,
                                'status' => ReserveMeeting::$pending,
                                'day' => $day,
                                'meeting_type' => 'online',
                                'student_count' => 1,
                                'slotid' => $slot_id ?? null
                            ], [
                                'date' => strtotime($day),
                                'start_at' => $startAt,
                                'end_at' => $endAt,
                                'paid_amount' => $itemPrice1 ?? 0,
                                'discount' => $meetingTime->meeting->discount,
                                'description' => 'ok',
                                'created_at' => time(),
                            ]);

                return [
                    'type' => 'meeting',

                    'meeting_id' => $meeting->id,
                    'meeting_time_id' => $itemId,
                    'reserve_meeting_id' => $reserveMeeting->id,
                    'day' => $day,
                    'discount' => $totalDiscount,
                    'amount' =>$reserveMeeting->paid_amount,
                    'description' => "Meeting: {$meeting->title}",
                    'user_data' => $validated,
                ];

            case 'installment':
                $installmentId = $validated['installment_id'];
                $item = Webinar::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                $user = auth()->user();

                $itemPrice = $item->getPrice();
                $price = $item->price;
                if($discountId > 0){
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 1;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                if(empty($user)){
                    $input=$validated;
                    $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();

                    if(empty($user)){
                         $user = User::create([
                        'role_name' => 'user',
                        'role_id' => 1,
                        'mobile' => $input['number'],
                        'email' => $input['email'],
                        'full_name' => $input['name'],

                        'status'=>'active',
                        'access_content' => 1,
                        'password' => Hash::make(123456),
                        'pwd_hint' => 123456,
                        'affiliate' => 0,
                        'timezone' => 'Asia/Kolkata' ?? null,
                        'created_at' => time()
                      ]);
                    }

                }
                    $userId = $user->id;
                $installment = Installment::query()->where('id', $installmentId)
                    ->where('enable', true)
                    ->withCount([
                        'steps'
                    ])
                    ->first();

                $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);

                $installmentOrder = InstallmentOrder::query()->updateOrCreate([
                    'installment_id' => $installment->id,
                    'user_id' => $user->id,
                    'discount' => $totalDiscount,
                    'webinar_id' => $itemId,
                    'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                    'item_price' => $itemPrice1 ?? $item->getPrice(),
                    'status' => 'paying',
                ], [
                    'created_at' => time(),
                ]);

                $step = $validated['installment_step'] ?? 1;

                $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                        'installment_order_id' => $installmentOrder->id,
                        'sale_id' => null,
                        'type' => 'upfront',
                        'step_id' => null,
                        'amount' => $installment->getUpfront($installmentOrder->getItemPrice()),
                        'status' => 'paying',
                    ], [
                        'created_at' => time(),
                    ]);

                return [
                    'type' => 'installment',
                    'item' => $installmentOrder,
                    'installment_payment' => $installmentPayment,
                    'installment_payment_id' => $installmentPayment->id,
                    'installment_step' => $step,
                    'discount' => $totalDiscount,
                    'amount' => $installmentPayment->amount,
                    'description' => "Installment {$step} - Order #{$installmentOrder->id}",
                    'user_data' => $validated,
                    'is_installment' => true,
                ];

            default:
                throw new \Exception('Invalid payment type');
        }
    }

    private function handleUtcDate($day, $clock, $instructorTimezone)
    {
        $date = $day . ' ' . $clock;

        $utcDate = convertTimeToUTCzone($date, $instructorTimezone);

        return $utcDate->getTimestamp();
    }

    protected function createOrder($paymentData)
    {
        Log::info('createOrder');
        $userId = auth()->id();
        $type = $paymentData['type'];

        if(empty($userId)){
            $input=$paymentData['user_data'];
            $user = User::where('email',$input['email'])->orwhere('mobile', $input['number'])->first();

            if(empty($user)){
                 $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $input['number'],
                'email' => $input['email'],
                'full_name' => $input['name'],

                'access_content' => 1,
                'password' => Hash::make(123456),
                'pwd_hint' => 123456,
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time()
              ]);
            }
            $userId = $user->id;
        }

        if ($type === 'cart') {
            return $paymentData['item'];
        }

        $order = Order::create([
            'user_id' => $userId,
            'status' => Order::$paying,
            'payment_method' => 'payment_channel',
            'is_charge_account' => 0,
            'amount' => $paymentData['amount'] + $paymentData['discount'],
            'tax' => 0,
            'total_discount' => $paymentData['discount'],
            'total_amount' => $paymentData['amount'],
            'product_delivery_fee' => null,
            'reference_id' => null,
            'payment_data' => null,
            'created_at' => time(),
        ]);

        $this->createOrderItem($order, $paymentData);

        return $order;
    }

    protected function createOrderItem($order, $paymentData)
    {Log::info('createOrderItem');
        $itemData = [
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'webinar_id' => null,
            'bundle_id' => null,
            'subscribe_id' => null,
            'subscription_id' => null,
            'promotion_id' => null,
            'gift_id' => null,
            'registration_package_id' => null,
            'product_id' => null,
            'installment_payment_id' => null,
            'via_payment' => null,
            'amount' => $paymentData['amount'],
            'total_amount' => $paymentData['amount'],
            'created_at' => time(),
        ];

        switch ($paymentData['type']) {
            case 'subscription':
                $itemData['subscription_id'] = $paymentData['subscription_id'];
                break;
            case 'webinar':
                $itemData['webinar_id'] = $paymentData['webinar_id'];
                break;
            case 'part':
                $itemData['webinar_id'] = $paymentData['webinar_id'];
                $itemData['installment_type'] = $paymentData['type'];
                break;
            case 'bundle':
                $itemData['bundle_id'] = $paymentData['bundle_id'];
                break;
            case 'product':
                $itemData['product_id'] = $paymentData['product_id'];
                break;
            case 'meeting':
                $itemData['reserve_meeting_id'] = $paymentData['reserve_meeting_id'];
                break;
            case 'installment':
                $itemData['installment_payment_id'] = $paymentData['installment_payment_id'];
                break;
        }

        return OrderItem::create($itemData);
    }

    protected function createRazorpayOrder($order, $paymentData)
    {Log::info('createRazorpayOrder');
        $userData = $paymentData['user_data'];
        $type = $paymentData['type'];

        $notes = [
            'order_id' => $order->id,
            'payment_type' => $type,
            'user_id' => auth()->id() ?? $order->user_id,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'mobile' => $userData['number'],
            'discount_id' => $userData['discount_id'] ?? null,
        ];

        switch ($type) {
            case 'subscription':
                $notes['subscription_id'] = $paymentData['subscription_id'];
                break;
            case 'webinar':
                $notes['webinar_id'] = $paymentData['webinar_id'];
                break;
            case 'part':
                $notes['webinar_id'] = $paymentData['webinar_id'];
                $notes['installment_id'] = $paymentData['installment_id'];
                $notes['amount'] = $paymentData['amount'];
                break;
            case 'bundle':
                $notes['bundle_id'] = $paymentData['bundle_id'];
                break;
            case 'product':
                $notes['product_id'] = $paymentData['product_id'];
                break;
            case 'meeting':
                $notes['meeting_id'] = $paymentData['meeting_id'];
                $notes['meeting_time_id'] = $paymentData['meeting_time_id'] ?? null;
                $notes['reserve_meeting_id'] = $paymentData['reserve_meeting_id'] ?? null;
                break;
            case 'installment':
                $notes['installment_payment_id'] = $paymentData['installment_payment_id'];
                $notes['installment_step'] = $paymentData['installment_step'];
                $notes['is_installment'] = true;
                break;
        }

        $razorpayOrder = $this->razorpayApi->order->create([
            'receipt' => 'order_' . $order->id . '_' . time(),
            'amount' => (int)(preg_replace('/[^\d.]/', '', handlePrice($order->total_amount * 100))),

            'currency' => currency(),
            'notes' => $notes,
        ]);

        $order->update([
            'razorpay_order_id' => $razorpayOrder['id']
        ]);

        return $razorpayOrder;
    }

    public function handleCallback(Request $request)
    { Log::info('handleCallback');
        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpaySignature = $request->razorpay_signature;
        $orderId = $request->order_id;

        try {

            $existingTransaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)
                ->where('status', 'completed')
                ->whereNotNull('processed_at')
                ->first();

            if ($existingTransaction) {
                Log::info('Payment already processed by webhook: ' . $razorpayPaymentId);
                return redirect('/payment/success?source=callback&already_processed=true');
            }

            $payment = $this->razorpayApi->payment->fetch($razorpayPaymentId);

            $this->verifyPaymentSignature($payment, $razorpaySignature);

            $this->storeTransaction($payment, 'callback');

            $this->dispatchPaymentJob($payment, $orderId);

            return redirect('/payment/success?source=callback&payment_id=' . $razorpayPaymentId);

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return redirect('/payment/failed?error=' . urlencode($e->getMessage()));
        }

    }

    protected function verifyPaymentSignature($payment, $signature)
    {
        Log::info('verifyPaymentSignature');
        $attributes = [
            'razorpay_order_id' => $payment['order_id'],
            'razorpay_payment_id' => $payment['id'],
            'razorpay_signature' => $signature
        ];

        $this->razorpayApi->utility->verifyPaymentSignature($attributes);
    }

    protected function storeTransaction($payment, $source = 'webhook')
    {Log::info('storeTransaction');
        $notes = $payment['notes'] ?? [];

        return TransactionsHistoryRazorpay::updateOrCreate(
            ['razorpay_payment_id' => $payment['id']],
            [
                'razorpay_order_id' => $payment['order_id'],
                'razorpay_signature' => null,
                'order_id' => $notes['order_id'] ?? null,
                'payment_type' => $notes['payment_type'] ?? 'webinar',
                'user_id' => $notes['user_id'] ?? null,
                'name' => $notes['name'] ?? null,
                'email' => $payment['email'] ?? $notes['email'] ?? null,
                'number' => $payment['contact'] ?? $notes['mobile'] ?? null,
                'amount' => $payment['amount'] / 100,
                'status' => $payment['status'] === 'captured' ? 'completed' : 'pending',
                'payment_method' => $payment['method'] ?? null,
                'source' => $source,
                'metadata' => json_encode($notes),
                'razorpay_description' => $notes['description'] ?? 'Payment',
                'updated_at' => now(),
            ]
        );
    }

    protected function dispatchPaymentJob($payment, $orderId = null)
    {Log::info('dispatchPaymentJob');
        $notes = $payment['notes'] ?? [];

        $jobData = [
            'razorpay_payment_id' => $payment['id'],
            'order_id' => $orderId ?? $notes['order_id'] ?? null,
            'payment_type' => $notes['payment_type'] ?? 'webinar',
            'user_id' => $notes['user_id'] ?? null,
            'name' => $notes['name'] ?? null,
            'email' => $payment['email'] ?? $notes['email'] ?? null,
            'number' => $payment['contact'] ?? $notes['mobile'] ?? null,
            'subscription_id' => $notes['subscription_id'] ?? null,
            'webinar_id' => $notes['webinar_id'] ?? null,
            'discount_id' => $notes['discount_id'] ?? null,
            'gateway' => 'Razorpay',
            'installment_payment_id' => $notes['installment_payment_id'] ?? null,
            'installment_id' => $notes['installment_id'] ?? null,
            'amount' => $notes['amount'] ?? null,
            'reserve_meeting_id' => $notes['reserve_meeting_id'] ?? null
        ];

        BuyNowProcessJob::dispatch($jobData)->delay(now()->addSeconds(5));
    }

    public function paymentVerifyBackgroundProccess($data)
    {
        $paymentId = $data['razorpay_payment_id'];
        $paymentType = $data['payment_type'] ?? 'webinar';
        $orderId = $data['order_id'] ?? null;
        $userId = $data['user_id'] ?? null;

        try {
            Log::info('Payment verification started', [
                'payment_id' => $paymentId,
                'payment_type' => $paymentType,
                'order_id' => $orderId,
            ]);

            DB::beginTransaction();

            switch ($paymentType) {
                case 'subscription':
                    $this->processSubscriptionPayment($data);
                    break;

                case 'webinar':
                    $this->processWebinarPayment($data);
                    break;

                case 'part':
                    $this->processPartPayment($data);
                    break;

                case 'bundle':
                    $this->processBundlePayment($data);
                    break;

                case 'product':
                    $this->processProductPayment($data);
                    break;

                case 'cart':
                    $this->processCartPayment($data);
                    break;

                case 'meeting':
                case 'consultation':
                    $this->processMeetingPayment($data);
                    break;

                case 'installment':
                    $this->processInstallmentPayment($data);
                    break;

                default:
                    throw new \Exception('Unknown payment type: ' . $paymentType);
            }

            if ($orderId) {
                $this->updateOrderStatus($orderId);
            }

            DB::commit();

            Log::info('Payment verification completed successfully', [
                'payment_id' => $paymentId,
                'payment_type' => $paymentType,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payment verification failed', [
                'payment_id' => $paymentId,
                'payment_type' => $paymentType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    protected function processSubscriptionPayment($data)
    {
        $subscriptionId = $data['subscription_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        Log::info('processSubscriptionPayment', [
                'subscriptionId' => $subscriptionId
            ]);

        $subscription = Subscription::findOrFail($subscriptionId);
        $user = User::findOrFail($userId);

        $SubscriptionAccess = SubscriptionAccess::where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->first();

        $startDate = time();
        $endDate = $this->calculateSubscriptionEndDate($subscription, $startDate);

        SubscriptionPayments::create([
                        'user_id' => $userId,
                        'subscription_id' => $subscriptionId,
                        'amount' => $amount,
                        'created_at' => time()
                        ]);

        $Subscription = Subscription::where('id' , $subscriptionId)
                            ->first();

        $SubscriptionPayments = SubscriptionPayments::where('user_id', $userId)
                                    ->where('subscription_id', $subscriptionId)
                                            ->get();

        $access_till_date = time() + ($Subscription->access_days * 24 * 60 * 60);
        $paid_no_of_subscriptions = $SubscriptionPayments->count();
        $access_content_count = $Subscription->video_count * $SubscriptionPayments->count();

        if(!empty($SubscriptionAccess->subscription_id)){

            $access_till_date1 = $SubscriptionAccess->access_till_date + ($Subscription->access_days * 24 * 60 * 60);

            $access_till_date = $access_till_date >= $access_till_date1 ? $access_till_date : $access_till_date1;

            $SubscriptionAccess->update([
                'access_till_date' => $access_till_date,
                'access_content_count' => $access_content_count,
                'paid_no_of_subscriptions' => $paid_no_of_subscriptions
                ]);

        }else{

            SubscriptionAccess::create([
                'user_id' => $userId,
                'subscription_id' => $subscriptionId,
                'access_till_date' => $access_till_date,
                'access_content_count' => $access_content_count,
                'paid_no_of_subscriptions' => $paid_no_of_subscriptions,
                'created_at' => time()
                ]);

        }

        $this->createSaleRecord([
            'buyer_id' => $userId,
            'seller_id' => $subscription->creator_id ?? 1,
            'subscription_id' => $subscriptionId,
            'type' => 'subscription',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        $this->createAccountingEntry([
            'user_id' => $subscription->creator_id ?? 1,
            'subscription_id' => $subscriptionId,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Subscription: ' . $subscription->title,
        ]);

        $this->processAffiliate($userId, $amount, 'subscription', $subscriptionId);

        Log::info('Subscription access granted', [
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'end_date' => date('Y-m-d H:i:s', $endDate),
        ]);
    }

    protected function processPartPayment($data)
    {

        $PartPaymentController = new PartPaymentController();
        $installments = $PartPaymentController->processPartPayment($data);

    }

    protected function processWebinarPayment($data)
    {
        $webinarId = $data['webinar_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $webinar = Webinar::findOrFail($webinarId);
        $user = User::findOrFail($userId);

        $existingSale = Sale::where('buyer_id', $userId)
            ->where('webinar_id', $webinarId)
            ->where('type', 'webinar')
            ->first();

        if ($existingSale) {
            Log::warning('User already purchased this webinar', [
                'user_id' => $userId,
                'webinar_id' => $webinarId,
            ]);
            return;
        }

        $sale = $this->createSaleRecord([
            'buyer_id' => $userId,
            'seller_id' => $webinar->creator_id,
            'webinar_id' => $webinarId,
            'type' => 'webinar',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        $this->createAccountingEntry([
            'user_id' => $webinar->creator_id,
            'webinar_id' => $webinarId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Course purchase: ' . $webinar->title,
        ]);

        $this->processAffiliate($userId, $amount, 'webinar', $webinarId);

        $this->sendPurchaseNotification($user, $webinar, 'webinar');

        Log::info('Webinar access granted', [
            'user_id' => $userId,
            'webinar_id' => $webinarId,
        ]);
    }

    protected function processBundlePayment($data)
    {
        $bundleId = $data['bundle_id'];
        $orderId = $data['order_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $bundle = Bundle::findOrFail($bundleId);
        $user = User::findOrFail($userId);

        $existingSale = Sale::where('buyer_id', $userId)
            ->where('bundle_id', $bundleId)
            ->where('type', 'bundle')
            ->first();

        if ($existingSale) {
            Log::warning('User already purchased this bundle', [
                'user_id' => $userId,
                'bundle_id' => $bundleId,
            ]);
            return;
        }

        $sale = $this->createSaleRecord([
            'buyer_id' => $userId,
            'seller_id' => $bundle->creator_id,
            'bundle_id' => $bundleId,
            'type' => 'bundle',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        $this->createAccountingEntry([
            'user_id' => $bundle->creator_id,
            'bundle_id' => $bundleId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Bundle purchase: ' . $bundle->title,
        ]);
        $order = Order::where('id',$orderId)->first();
        foreach ($order->orderItems as $orderItem) {
            if($orderItem->bundle->bundleWebinars){

                Log::info('For only bundleWebinars payment');
                foreach ($orderItem->bundle->bundleWebinars as $bundleWebinar){
                    if($bundleWebinar->product_id){
                        Log::info('For only product payment');
                        $product = $bundleWebinar->product;

                        $productOrder = ProductOrder::updateOrCreate([
                            'product_id' => $product->id,
                            'seller_id' => $product->creator_id,
                            'buyer_id' => $orderItem->user_id,
                            'bundle_id' => $orderItem->bundle_id,
                            'sale_id' => null,
                            'status' => 'pending',
                        ], [
                            'specifications' => null,
                            'quantity' => 1,
                            'discount_id' => null,
                            'created_at' => time()
                        ]);

                        Log::info('productOrder ceated');

                        $order1 = Order::create([
                            'user_id' => $orderItem->user_id,
                            'status' => 'paid',
                            'payment_method' => $order->payment_method,
                            'amount' => 0,
                            'tax' => 0,
                            'total_discount' => 0,
                            'total_amount' => 0,
                            'product_delivery_fee' => null,
                            'created_at' => time(),
                        ]);

                        $OrderItem1 = OrderItem::create([
                            'user_id' => $orderItem->user_id,
                            'order_id' => $order1->id,
                            'webinar_id' => null,
                            'bundle_id' =>  null,
                            'product_id' => $product->id,
                            'product_order_id' => $productOrder->id,
                            'reserve_meeting_id' =>null,
                            'subscribe_id' => null,
                            'promotion_id' =>  null,
                            'gift_id' => null,
                            'installment_payment_id' =>  null,
                            'ticket_id' =>  null,
                            'discount_id' =>null,
                            'amount' => 0,
                            'total_amount' => 0,
                            'tax' => 0,
                            'tax_price' => 0,
                            'commission' => 0,
                            'commission_price' => 0,
                            'product_delivery_fee' => 0,
                            'discount' => 0,
                            'created_at' => time(),
                        ]);

                        $seller_id = OrderItem::getSeller($orderItem);
                        $sale = Sale::create([
                            'buyer_id' => $orderItem->user_id,
                            'seller_id' => $seller_id,
                            'order_id' => $OrderItem1->order_id,
                            'webinar_id' => null,
                            'bundle_id' => null,
                            'meeting_id' => null,
                            'meeting_time_id' =>  null,
                            'subscribe_id' => null,
                            'promotion_id' => null,
                            'registration_package_id' => null,
                            'product_order_id' => $productOrder->id,
                            'installment_payment_id' => null,
                            'status' =>  null,
                            'gift_id' => null,
                            'type' => 'product',
                            'payment_method' => $order->payment_method,
                            'amount' => 0,
                            'tax' => 0,
                            'via_payment' => null,
                            'commission' => 0,
                            'discount' => 0,
                            'total_amount' => 0,
                            'product_delivery_fee' => 0,
                            'created_at' => time(),
                        ]);
                        Log::info('sale created');
                        $status = ProductOrder::$waitingDelivery;

                        if ($product and $product->isVirtual()) {
                            $status = ProductOrder::$success;
                        }

                        ProductOrder::where('id', $productOrder->id)
                            ->where(function ($query) use ($orderItem) {
                                $query->where(function ($query) use ($orderItem) {
                                    $query->whereNotNull('buyer_id');
                                    $query->where('buyer_id', $orderItem->user_id);
                                });
                            })
                            ->update([
                                'sale_id' => $sale->id,
                                'status' => $status,
                            ]);

                        OrderAddress::where('order_id', $order->id)
                            ->update([
                                'order_id' => $order1->id,
                            ]);

                        Log::info('Done');
                    }
                }
            }
        }

        $this->processAffiliate($userId, $amount, 'bundle', $bundleId);

        Log::info('Bundle access granted', [
            'user_id' => $userId,
            'bundle_id' => $bundleId,
        ]);
    }

    protected function processProductPayment($data)
    {
        $productId = $data['product_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $product = Product::findOrFail($productId);
        $user = User::findOrFail($userId);

        $productOrder = ProductOrder::updateOrCreate([
                            'product_id' => $product->id,
                            'seller_id' => $product->creator_id,
                            'buyer_id' => $userId,
                            'sale_id' => null,
                            'status' => 'pending',
                        ], [
                            'specifications' => null,
                            'quantity' => 1,
                            'discount_id' => null,
                            'created_at' => time()
                        ]);

        $sale = $this->createSaleRecord([
            'buyer_id' => $userId,
            'seller_id' => $product->creator_id,
            'product_id' => $productId,
            'type' => 'product',
            'product_order_id' => $productOrder->id,
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        $this->createAccountingEntry([
            'user_id' => $product->creator_id,
            'product_id' => $productId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Product purchase: ' . $product->title,
        ]);

        if ($product->unlimited_inventory == 0) {
            $product->decrement('inventory');
        }
        $status = ProductOrder::$waitingDelivery;

        if ($product and $product->isVirtual()) {
            $status = ProductOrder::$success;
        }

        $productOrder
            ->update([
                'sale_id' => $sale->id,
                'status' => $status,
            ]);

        $this->processAffiliate($userId, $amount, 'product', $productId);

        Log::info('Product purchase completed', [
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    protected function processCartPayment($data)
    {
        $orderId = $data['order_id'];
        $userId = $data['user_id'];

        $order = Order::with('orderItems')->findOrFail($orderId);

        foreach ($order->orderItems as $item) {

            if ($item->webinar_id) {
                $this->processWebinarPayment([
                    'webinar_id' => $item->webinar_id,
                    'user_id' => $userId,
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                ]);
            } elseif ($item->bundle_id) {
                $this->processBundlePayment([
                    'bundle_id' => $item->bundle_id,
                    'user_id' => $userId,
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'order_id' => $data['order_id'],
                ]);
            } elseif ($item->product_id) {
                $this->processProductPayment([
                    'product_id' => $item->product_id,
                    'user_id' => $userId,
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                ]);
            } elseif ($item->subscription_id) {
                $this->processSubscriptionPayment([
                    'subscription_id' => $item->subscription_id,
                    'user_id' => $userId,
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                ]);
            }
        }

        Cart::where('creator_id', $userId)->delete();

        Log::info('Cart payment processed', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'items_count' => $order->orderItems->count(),
        ]);
    }

    protected function processMeetingPayment($data)
    {
        $reserve_meeting_id = $data['reserve_meeting_id'];

        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $user = User::findOrFail($userId);

        $reserveMeeting = ReserveMeeting::where('id' , $reserve_meeting_id)->first();
        $meetingId = $reserveMeeting->meeting_id;
        $meeting = Meeting::findOrFail($meetingId);

        $sale = $this->createSaleRecord([
            'buyer_id' => $userId,
            'seller_id' => $meeting->creator_id,
            'meeting_id' => $meetingId,
            'type' => 'meeting',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        $this->createAccountingEntry([
            'user_id' => $meeting->creator_id,
            'meeting_id' => $meetingId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Meeting booking: ' . $meeting->title,
        ]);

        $reserveMeeting ->update([
            'sale_id' => $sale->id,
            'reserved_at' => time()]);

        $this->processAffiliate($userId, $amount, 'meeting', $meetingId);

        Log::info('Meeting booking completed', [
            'user_id' => $userId,
            'meeting_id' => $meetingId,
            'reserve_meeting_id' => $reserveMeeting->id,
        ]);
    }

    protected function processInstallmentPayment($data)
    {
        $orderId = $data['order_id'];

        Log::info('processInstallmentPayment', [
            'data' => $data
        ]);
        $installmentPaymentId = $data['installment_payment_id'];
        $installmentStep = $data['installment_step'] ?? 1;
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $installmentPayment = InstallmentOrderPayment::with('installmentOrder')
            ->findOrFail($installmentPaymentId);

        $installmentPayment->update([
            'status' => 'paid',
            'payment_date' => time(),
        ]);

        $installmentOrder = $installmentPayment->installmentOrder;
        $webinarId = $installmentOrder->webinar_id;
        $webinar = Webinar::findOrFail($webinarId);

        $sale = $this->createSaleRecord([
            'buyer_id' => $userId,
            'seller_id' => $webinar->seller_id ?? 1,
            'webinar_id' => $webinarId,
            'order_id' => $orderId,
            'installment_payment_id' => $installmentPaymentId,
            'type' => 'installment_payment',
            'payment_method' => 'payment_channel',
            'amount' => $amount,
            'total_amount' => $amount,
            'created_at' => time(),
        ]);

        $installmentOrder->update([
            'status' => 'open'
        ]);

        $this->createAccountingEntry([
            'user_id' => $installmentPayment->installmentOrder->seller_id ?? 1,
            'installment_payment_id' => $installmentPaymentId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => "Installment payment {$installmentStep}",
        ]);

        Log::info('Installment payment processed', [
            'user_id' => $userId,
            'installment_payment_id' => $installmentPaymentId,
            'step' => $installmentStep,

        ]);
    }

    protected function grantInstallmentItemAccess($installmentOrder, $userId)
    {
        $itemType = $installmentOrder->item_type;
        $itemId = $installmentOrder->item_id;

        switch ($itemType) {
            case 'webinar':
                $this->processWebinarPayment([
                    'webinar_id' => $itemId,
                    'user_id' => $userId,
                    'razorpay_payment_id' => 'installment_complete',
                ]);
                break;

            case 'bundle':
                $this->processBundlePayment([
                    'bundle_id' => $itemId,
                    'user_id' => $userId,
                    'razorpay_payment_id' => 'installment_complete',
                ]);
                break;

        }

        Log::info('Installment item access granted', [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
        ]);
    }

    protected function createSaleRecord($data)
    {
        return Sale::create($data);
    }

    protected function createAccountingEntry($data)
    {
        $defaults = [
            'is_affiliate' => false,
            'is_cashback' => false,
            'store_type' => 'automatic',
            'tax' => 0,
            'commission' => 0,
            'discount' => 0,
            'created_at' => time(),
        ];

        return Accounting::create(array_merge($defaults, $data));
    }

    protected function processAffiliate($userId, $amount, $type, $itemId)
    {

        $user = User::find($userId);
        if (!$user || !$user->affiliate_user_id) {
            return;
        }

        $affiliate = Affiliate::where('user_id', $user->affiliate_user_id)
            ->where('status', 'active')
            ->first();

        if (!$affiliate) {
            return;
        }

        $commissionRate = 0.10;
        $commission = $amount * $commissionRate;

        Accounting::create([
            'user_id' => $affiliate->user_id,
            'amount' => $commission,
            'type' => 'affiliate_commission',
            'is_affiliate' => true,
            'description' => "Affiliate commission for {$type} #{$itemId}",
            'created_at' => time(),
        ]);

        Log::info('Affiliate commission processed', [
            'affiliate_user_id' => $affiliate->user_id,
            'commission' => $commission,
            'referred_user_id' => $userId,
        ]);
    }

    protected function calculateSubscriptionEndDate($subscription, $startDate)
    {
        $days = $subscription->days ?? 30;

        if ($subscription->usable_count) {

            $days = 365;
        }

        return strtotime("+{$days} days", $startDate);
    }

    protected function getTransactionAmount($razorpayPaymentId)
    {
        $transaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)
            ->first();

        return $transaction ? $transaction->amount : 0;
    }

    protected function updateOrderStatus($orderId)
    {
        $order = Order::find($orderId);

        if ($order) {
            $order->update([
                'status' => 'paid',
                'payment_data' => json_encode([
                    'gateway' => 'Razorpay',
                    'paid_at' => time(),
                ]),
            ]);
        }
    }

    protected function sendPurchaseNotification($user, $item, $type)
    {
        try {

            Log::info('Purchase notification sent', [
                'user_id' => $user->id,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send purchase notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

}