<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mixins\Cashback\CashbackAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Services\PaymentEngine\PaymentLedgerService;

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
            'payment_type' => 'required|in:subscription,webinar,cart,part,meeting,product,bundle,installment,quick_pay',
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
                 $percent = $discountCoupon->percent ?? 0;
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
                 $percent = $discountCoupon->percent ?? 0;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                // UPE: Determine next payable amount from UPE schedules (sole source of truth).
                $installmentIdForPart = $validated['installment_id'] ?? null;
                $partAmount = $itemPrice1 ?? $itemPrice; // fallback for new purchase
                $user = auth()->user();

                if ($user) {
                    $upeProduct = UpeProduct::where('external_id', $webinar->id)
                        ->whereIn('product_type', ['course_video', 'webinar'])
                        ->first();

                    if ($upeProduct) {
                        $upeSale = UpeSale::where('user_id', $user->id)
                            ->where('product_id', $upeProduct->id)
                            ->where('pricing_mode', 'installment')
                            ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
                            ->first();

                        if ($upeSale) {
                            $upePlan = UpeInstallmentPlan::where('sale_id', $upeSale->id)
                                ->whereIn('status', ['active', 'completed'])
                                ->with('schedules')
                                ->first();

                            if ($upePlan && $upePlan->schedules->isNotEmpty()) {
                                $nextSchedule = $upePlan->schedules
                                    ->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])
                                    ->sortBy('sequence')
                                    ->first();

                                if ($nextSchedule) {
                                    $remaining = (float) $nextSchedule->amount_due - (float) $nextSchedule->amount_paid;
                                    $partAmount = max(1, (int) round($remaining, 0, PHP_ROUND_HALF_UP));
                                } else {
                                    $partAmount = 0;
                                }
                            } else {
                                // UPE sale exists but no plan/schedules yet — use ledger balance
                                $ledgerService = app(PaymentLedgerService::class);
                                $totalPaid = $ledgerService->balance($upeSale->id);
                                $partAmount = max(0, (int) round(($itemPrice1 ?? $itemPrice) - $totalPaid, 0, PHP_ROUND_HALF_UP));
                            }
                        } else {
                            // No UPE sale → new purchase → upfront amount
                            if ($installmentIdForPart) {
                                $installment = Installment::where('id', $installmentIdForPart)->where('enable', true)->first();
                                if ($installment) {
                                    $partAmount = (int) round($installment->getUpfront($itemPrice1), 0, PHP_ROUND_HALF_UP);
                                }
                            }
                        }
                    }
                }
                return [
                    'type' => 'part',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'discount' => $totalDiscount,
                    'installment_id' => $validated['installment_id'],
                    'amount' => $partAmount,
                    'description' => "Course: {$webinar->title}",
                    'user_data' => $validated,
                ];

            case 'cart':
                $order = Order::findOrFail($itemId);
                $user = auth()->user();

                if(empty($user)){
                    $input=$validated;
                    $user = User::where(function($q) use ($input) { $q->where('email', $input['email'])->orWhere('mobile', $input['number']); })->first();

                    if(empty($user)){
                         $user = User::create([
                        'role_name' => 'user',
                        'role_id' => 1,
                        'mobile' => $input['number'],
                        'email' => $input['email'],
                        'full_name' => $input['name'],

                        'status'=>'active',
                        'access_content' => 1,
                        'password' => Hash::make(Str::random(16)),
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
                    $user = User::where(function($q) use ($input) { $q->where('email', $input['email'])->orWhere('mobile', $input['number']); })->first();

                    if(empty($user)){
                         $user = User::create([
                        'role_name' => 'user',
                        'role_id' => 1,
                        'mobile' => $input['number'],
                        'email' => $input['email'],
                        'full_name' => $input['name'],

                        'status'=>'active',
                        'access_content' => 1,
                        'password' => Hash::make(Str::random(16)),
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
                 $percent = $discountCoupon->percent ?? 0;
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
                 $percent = $discountCoupon->percent ?? 0;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                if(empty($user)){
                    $input=$validated;
                    $user = User::where(function($q) use ($input) { $q->where('email', $input['email'])->orWhere('mobile', $input['number']); })->first();

                    if(empty($user)){
                         $user = User::create([
                        'role_name' => 'user',
                        'role_id' => 1,
                        'mobile' => $input['number'],
                        'email' => $input['email'],
                        'full_name' => $input['name'],

                        'status'=>'active',
                        'access_content' => 1,
                        'password' => Hash::make(Str::random(16)),
                        'affiliate' => 0,
                        'timezone' => 'Asia/Kolkata' ?? null,
                        'created_at' => time()
                      ]);
                    }

                }

                // UPE: Compute upfront amount from Installment config (no legacy record creation)
                $installment = Installment::query()->where('id', $installmentId)
                    ->where('enable', true)
                    ->first();

                $upfrontAmount = $installment
                    ? (int) round($installment->getUpfront($itemPrice1 ?? $itemPrice), 0, PHP_ROUND_HALF_UP)
                    : ($itemPrice1 ?? $itemPrice);

                return [
                    'type' => 'installment',
                    'item' => $item,
                    'webinar_id' => $item->id,
                    'installment_id' => $installmentId,
                    'discount' => $totalDiscount,
                    'amount' => $upfrontAmount,
                    'description' => "Installment Upfront: {$item->title}",
                    'user_data' => $validated,
                    'is_installment' => true,
                ];

            case 'quick_pay':
                $webinar = Webinar::findOrFail($itemId);
                $quickPayAmount = (float) ($validated['amount'] ?? $webinar->getPrice());
                $installmentIdForQuickPay = $validated['installment_id'] ?? null;
                return [
                    'type' => 'quick_pay',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'installment_id' => $installmentIdForQuickPay,
                    'discount' => 0,
                    'amount' => $quickPayAmount,
                    'description' => "Quick Pay: {$webinar->title}",
                    'user_data' => $validated,
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
            $user = User::where(function($q) use ($input) { $q->where('email', $input['email'])->orWhere('mobile', $input['number']); })->first();

            if(empty($user)){
                 $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $input['number'],
                'email' => $input['email'],
                'full_name' => $input['name'],

                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
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
                $itemData['webinar_id'] = $paymentData['webinar_id'];
                $itemData['installment_payment_id'] = $paymentData['installment_payment_id'] ?? null;
                $itemData['installment_type'] = 'installment';
                break;
            case 'quick_pay':
                $itemData['webinar_id'] = $paymentData['webinar_id'];
                $itemData['installment_type'] = 'quick_pay';
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
                $notes['webinar_id'] = $paymentData['webinar_id'];
                $notes['installment_id'] = $paymentData['installment_id'];
                $notes['is_installment'] = true;
                break;
            case 'quick_pay':
                $notes['webinar_id'] = $paymentData['webinar_id'];
                $notes['installment_id'] = $paymentData['installment_id'];
                $notes['amount'] = $paymentData['amount'];
                $notes['is_quick_pay'] = true;
                break;
        }

        $razorpayOrder = $this->razorpayApi->order->create([
            'receipt' => 'order_' . $order->id . '_' . time(),
            'amount' => (int) round((float) $order->total_amount, 0, PHP_ROUND_HALF_UP) * 100,

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
                'amount' => round($payment['amount'] / 100, 2),
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

                case 'quick_pay':
                    $this->processQuickPayPayment($data);
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

        Log::info('processSubscriptionPayment', ['subscriptionId' => $subscriptionId]);

        $subscription = Subscription::findOrFail($subscriptionId);
        $user = User::findOrFail($userId);

        // Legacy SubscriptionAccess + SubscriptionPayments (keep for backward compat)
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

        $Subscription = Subscription::where('id', $subscriptionId)->first();
        $SubscriptionPayments = SubscriptionPayments::where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)->get();

        $access_till_date = time() + ($Subscription->access_days * 24 * 60 * 60);
        $paid_no_of_subscriptions = $SubscriptionPayments->count();
        $access_content_count = $Subscription->video_count * $SubscriptionPayments->count();

        if (!empty($SubscriptionAccess->subscription_id)) {
            $access_till_date1 = $SubscriptionAccess->access_till_date + ($Subscription->access_days * 24 * 60 * 60);
            $access_till_date = $access_till_date >= $access_till_date1 ? $access_till_date : $access_till_date1;
            $SubscriptionAccess->update([
                'access_till_date' => $access_till_date,
                'access_content_count' => $access_content_count,
                'paid_no_of_subscriptions' => $paid_no_of_subscriptions
            ]);
        } else {
            SubscriptionAccess::create([
                'user_id' => $userId,
                'subscription_id' => $subscriptionId,
                'access_till_date' => $access_till_date,
                'access_content_count' => $access_content_count,
                'paid_no_of_subscriptions' => $paid_no_of_subscriptions,
                'created_at' => time()
            ]);
        }

        // UPE CheckoutService (creates UPE sale + subscription + ledger + legacy Sale + accounting)
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $checkout->processSubscriptionPurchase($userId, $subscriptionId, $amount, 'razorpay', $data['razorpay_payment_id']);

        $this->processAffiliate($userId, $amount, 'subscription', $subscriptionId);

        Log::info('Subscription access granted', [
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'end_date' => date('Y-m-d H:i:s', $endDate),
        ]);
    }

    protected function processPartPayment($data)
    {
        $webinarId = $data['webinar_id'];
        $userId = $data['user_id'];
        $installmentId = !empty($data['installment_id']) ? (int) $data['installment_id'] : null;
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);
        $discountId = $data['discount_id'] ?? null;

        $webinar = Webinar::findOrFail($webinarId);
        $discount = 0;
        if ($discountId) {
            $discountCoupon = Discount::where('id', $discountId)->first();
            if ($discountCoupon) {
                $discount = ($webinar->price > 0) ? $webinar->price * ($discountCoupon->percent ?? 0) / 100 : 0;
            }
        }

        if (!$installmentId) {
            $installmentId = (int) Installment::where('enable', true)->value('id');
        }

        Log::info('processPartPayment via UPE CheckoutService', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'installment_id' => $installmentId, 'amount' => $amount, 'discount' => $discount,
        ]);

        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $result = $checkout->processPartPayment(
            $userId, $webinarId, $amount, $installmentId, 'razorpay', $data['razorpay_payment_id'], $discount
        );

        Log::info('Part payment processed via UPE', [
            'upe_sale_id' => $result['upe_sale']->id,
            'already_exists' => $result['already_exists'],
        ]);
    }

    protected function processQuickPayPayment($data)
    {
        $webinarId = $data['webinar_id'];
        $userId = $data['user_id'];
        $installmentId = !empty($data['installment_id']) ? (int) $data['installment_id'] : null;
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        if (!$installmentId) {
            $installmentId = (int) \App\Models\Installment::where('enable', true)->value('id');
        }

        Log::info('processQuickPayPayment', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'installment_id' => $installmentId, 'amount' => $amount,
        ]);

        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $result = $checkout->processQuickPayment(
            $userId, $webinarId, $amount, $installmentId, 'razorpay', $data['razorpay_payment_id']
        );

        Log::info('Quick pay processed', [
            'upe_sale_id' => $result['upe_sale']->id,
            'already_exists' => $result['already_exists'],
        ]);
    }

    protected function processWebinarPayment($data)
    {
        $webinarId = $data['webinar_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $webinar = Webinar::findOrFail($webinarId);
        $user = User::findOrFail($userId);

        // UPE CheckoutService (handles idempotency, UPE sale + ledger + legacy Sale + accounting)
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $result = $checkout->processWebinarPurchase($userId, $webinarId, $amount, 'razorpay', $data['razorpay_payment_id']);

        if ($result['already_exists']) {
            Log::warning('User already purchased this webinar', [
                'user_id' => $userId, 'webinar_id' => $webinarId,
            ]);
            return;
        }

        $this->processAffiliate($userId, $amount, 'webinar', $webinarId);
        $this->sendPurchaseNotification($user, $webinar, 'webinar');

        Log::info('Webinar access granted', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
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

        // UPE CheckoutService (handles idempotency, UPE sale + ledger + legacy Sale + accounting)
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $result = $checkout->processBundlePurchase($userId, $bundleId, $amount, 'razorpay', $data['razorpay_payment_id']);

        if ($result['already_exists']) {
            Log::warning('User already purchased this bundle', [
                'user_id' => $userId, 'bundle_id' => $bundleId,
            ]);
            return;
        }

        $sale = $result['legacy_sale'];
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

        // Product = physical/digital, not course access — legacy only via CheckoutService
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $sale = $checkout->processProductPurchase($userId, $productId, $amount, $productOrder->id, $data['razorpay_payment_id']);

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
        $reserveMeeting = ReserveMeeting::where('id', $reserve_meeting_id)->first();
        $meetingId = $reserveMeeting->meeting_id;
        $meeting = Meeting::findOrFail($meetingId);

        // Meeting = not course access — legacy only via CheckoutService
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $sale = $checkout->processMeetingPurchase($userId, $meetingId, $amount);

        $reserveMeeting->update([
            'sale_id' => $sale->id,
            'reserved_at' => time()
        ]);

        $this->processAffiliate($userId, $amount, 'meeting', $meetingId);

        Log::info('Meeting booking completed', [
            'user_id' => $userId,
            'meeting_id' => $meetingId,
            'reserve_meeting_id' => $reserveMeeting->id,
        ]);
    }

    protected function processInstallmentPayment($data)
    {
        $webinarId = $data['webinar_id'] ?? null;
        $userId = $data['user_id'];
        $installmentId = !empty($data['installment_id']) ? (int) $data['installment_id'] : null;
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);
        $discountId = $data['discount_id'] ?? null;

        // Backward compat: old Razorpay notes may still carry installment_payment_id
        if (!$webinarId && !empty($data['installment_payment_id'])) {
            $legacyPayment = InstallmentOrderPayment::with('installmentOrder')->find($data['installment_payment_id']);
            if ($legacyPayment) {
                $webinarId = $legacyPayment->installmentOrder->webinar_id ?? null;
                $legacyPayment->update(['status' => 'paid', 'payment_date' => time()]);
                if ($legacyPayment->installmentOrder) {
                    $legacyPayment->installmentOrder->update(['status' => 'open']);
                }
            }
        }

        if (!$webinarId) {
            throw new \Exception('processInstallmentPayment: webinar_id missing from payment data');
        }

        $webinar = Webinar::findOrFail($webinarId);
        $discount = 0;
        if ($discountId) {
            $discountCoupon = Discount::where('id', $discountId)->first();
            if ($discountCoupon) {
                $discount = ($webinar->price > 0) ? $webinar->price * ($discountCoupon->percent ?? 0) / 100 : 0;
            }
        }

        if (!$installmentId) {
            $installmentId = (int) Installment::where('enable', true)->value('id');
        }

        Log::info('processInstallmentPayment via UPE CheckoutService', [
            'user_id' => $userId, 'webinar_id' => $webinarId,
            'installment_id' => $installmentId, 'amount' => $amount, 'discount' => $discount,
        ]);

        // UPE: delegate to CheckoutService::processPartPayment (handles new + existing sales)
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $result = $checkout->processPartPayment(
            $userId, $webinarId, $amount, $installmentId, 'razorpay', $data['razorpay_payment_id'], $discount
        );

        // Legacy dual-write: create InstallmentOrder + InstallmentOrderPayment for admin views
        try {
            $installment = Installment::find($installmentId);
            if ($installment) {
                $itemPrice = ceil($webinar->getPrice()) - $discount;
                $installmentOrder = InstallmentOrder::query()->updateOrCreate([
                    'installment_id' => $installment->id,
                    'user_id' => $userId,
                    'webinar_id' => $webinarId,
                ], [
                    'discount' => $discount,
                    'item_price' => $itemPrice,
                    'status' => 'open',
                    'created_at' => time(),
                ]);

                InstallmentOrderPayment::query()->updateOrCreate([
                    'installment_order_id' => $installmentOrder->id,
                    'type' => 'upfront',
                ], [
                    'amount' => $amount,
                    'status' => 'paid',
                    'created_at' => time(),
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Legacy InstallmentOrder dual-write failed (non-fatal): ' . $e->getMessage());
        }

        Log::info('Installment payment processed via UPE', [
            'upe_sale_id' => $result['upe_sale']->id,
            'already_exists' => $result['already_exists'],
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

    /**
     * Get CheckoutService instance.
     */
    protected function getCheckoutService(): \App\Services\PaymentEngine\CheckoutService
    {
        return app(\App\Services\PaymentEngine\CheckoutService::class);
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