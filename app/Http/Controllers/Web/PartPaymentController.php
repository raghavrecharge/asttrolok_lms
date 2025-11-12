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
use App\Models\TicketUser;
use Illuminate\Support\Facades\Hash;

class PartPaymentController extends Controller
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

    /**
     * Initiate payment - Universal entry point
     */
    public function initiatePayment(Request $request)
    {
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
        
        try {
            DB::beginTransaction();
            
            // Get payment details based on type
            $paymentData = $this->getPaymentData($validated);
            
            

            // Create or update order (using your existing orders table)
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

            // Create Razorpay order
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

    /**
     * Get payment data based on type
     */
    protected function getPaymentData($validated)
    {
        $type = $validated['payment_type'];
        $itemId = $validated['item_id'];

        switch ($type) {
            case 'subscription':
                $subscription = Subscription::findOrFail($itemId);
                return [
                    'type' => 'subscription',
                    'item' => $subscription,
                    'subscription_id' => $subscription->id,
                    'amount' => $subscription->getPrice(),
                    'description' => "Subscription: {$subscription->title}",
                    'user_data' => $validated,
                ];

            case 'webinar':
                $webinar = Webinar::findOrFail($itemId);
                return [
                    'type' => 'webinar',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'amount' => $webinar->getPrice() ?? $webinar->price,
                    'description' => "Course: {$webinar->title}",
                    'user_data' => $validated,
                ];

            case 'part':
                $webinar = Webinar::findOrFail($itemId);
                return [
                    'type' => 'part',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'installment_id' => $validated['installment_id'],
                    'amount' => $validated['amount'],
                    'description' => "Course: {$webinar->title}",
                    'user_data' => $validated,
                ];

            case 'cart':
                $order = Order::findOrFail($itemId);
                return [
                    'type' => 'cart',
                    'item' => $order,
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
                    'amount' => $product->getPrice(),
                    'description' => "Product: {$product->title}",
                    'user_data' => $validated,
                ];
                
            case 'meeting':
                
                $user = auth()->user();
                $day = $validated['selectedDay'] ?? null;
                $meetingTime = MeetingTime::where('id', $itemId)
                    ->with('meeting')
                    ->first();
                    
                $meeting = Meeting::findOrFail($meetingTime->meeting_id);
                
                $explodetime = explode('-', $meetingTime->time);
                
                $hours = (strtotime($explodetime[1]) - strtotime($explodetime[0])) / 1800;
                $hourlyAmount = $meeting->amount;
                $discountAmount=0;

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
                                'student_count' => 1
                            ], [
                                'date' => strtotime($day),
                                'start_at' => $startAt,
                                'end_at' => $endAt,
                                'paid_amount' => (!empty($hourlyAmount) and $hourlyAmount > 0) ? ($hourlyAmount * $hours) - $discountAmount : 0,
                                'discount' => $meetingTime->meeting->discount,
                                'description' => 'ok',
                                'created_at' => time(),
                            ]);
                
                return [
                    'type' => 'meeting',
                    'item' => $meeting,
                    'meeting_id' => $meeting->id,
                    'meeting_time_id' => $itemId,
                    'reserveMeeting_id' => $reserveMeeting->id,
                    'day' => $day,
                    'amount' => $reserveMeeting->paid_amount,
                    'description' => "Meeting: {$meeting->title}",
                    'user_data' => $validated,
                ];
                
            
            case 'installment':
                $installmentId = $validated['installment_id'];
                $item = Webinar::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();
                    
                $user = auth()->user();
                    
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
                    'discount' => 0,
                    'webinar_id' => $itemId,
                    'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                    'item_price' => $item->getPrice(),
                    'status' => 'paying',
                ], [
                    'created_at' => time(),
                ]);
                    
                // $installmentOrder = InstallmentOrder::findOrFail($itemId);
                $step = $validated['installment_step'] ?? 1;
                
                // // Get the specific installment payment
                // $installmentPayment = InstallmentOrderPayment::where('installment_order_id', $installmentOrder->id)
                //     ->where('step', $step)
                //     ->where('status', 'paying')
                //     ->firstOrFail();
                    
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

    /**
     * Create order using your existing orders table structure
     */
    protected function createOrder($paymentData)
    {
        
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
                // 'status' => User::$pending,
                'status'=>'active',
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

        // For cart, order already exists
        if ($type === 'cart') {
            return $paymentData['item'];
        }

        // Create new order using your schema
        $order = Order::create([
            'user_id' => $userId,
            'status' => Order::$paying, // 'paying' status from your enum
            'payment_method' => 'payment_channel',
            'is_charge_account' => 0,
            'amount' => $paymentData['amount'],
            'tax' => 0,
            'total_discount' => 0,
            'total_amount' => $paymentData['amount'],
            'product_delivery_fee' => null,
            'reference_id' => null,
            'payment_data' => null,
            'created_at' => time(),
        ]);

        // Create order item using your schema
        $this->createOrderItem($order, $paymentData);
        
        

        return $order;
    }

    /**
     * Create order item using your existing order_items table structure
     */
    protected function createOrderItem($order, $paymentData)
    {
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

        // Set specific ID based on payment type
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
                $itemData['reserve_meeting_id'] = $paymentData['reserveMeeting_id'];
                break;
            case 'installment':
                $itemData['installment_payment_id'] = $paymentData['installment_payment_id'];
                break;
        }
        
        return OrderItem::create($itemData);
    }

    /**
     * Create Razorpay order with complete metadata
     */
    protected function createRazorpayOrder($order, $paymentData)
    {
        $userData = $paymentData['user_data'];
        $type = $paymentData['type'];

        // Complete notes for webhook
        $notes = [
            'order_id' => $order->id,
            'payment_type' => $type,
            'user_id' => auth()->id() ?? null,
            'name' => $userData['name'],
            'email' => $userData['email'],
            'mobile' => $userData['number'],
            'discount_id' => $userData['discount_id'] ?? null,
        ];

        // Add type-specific data
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
                break;
            case 'installment':
                $notes['installment_payment_id'] = $paymentData['installment_payment_id'];
                $notes['installment_step'] = $paymentData['installment_step'];
                $notes['is_installment'] = true;
                break;
        }

        $razorpayOrder = $this->razorpayApi->order->create([
            'receipt' => 'order_' . $order->id . '_' . time(),
            'amount' => (int)($paymentData['amount'] * 100), // Convert to paise
            'currency' => currency(),
            'notes' => $notes,
        ]);

        // Store Razorpay order ID in orders table
        $order->update([
            'razorpay_order_id' => $razorpayOrder['id']
        ]);

        return $razorpayOrder;
    }

    /**
     * Handle callback when user returns (updated to use your table)
     */
    public function handleCallback(Request $request)
    {
        $razorpayPaymentId = $request->razorpay_payment_id;
        $razorpaySignature = $request->razorpay_signature;
        $orderId = $request->order_id;

        try {
            // Check if webhook already processed using YOUR table
            $existingTransaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)
                ->where('status', 'completed')
                ->whereNotNull('processed_at')
                ->first();

            if ($existingTransaction) {
                Log::info('Payment already processed by webhook: ' . $razorpayPaymentId);
                return redirect('/payment/success?source=callback&already_processed=true');
            }

            // Fetch payment details from Razorpay
            $payment = $this->razorpayApi->payment->fetch($razorpayPaymentId);

            // Verify signature
            $this->verifyPaymentSignature($payment, $razorpaySignature);

            // Store in YOUR transactions_history_razorpay table
            $this->storeTransaction($payment, 'callback');

            // Dispatch your existing job
            $this->dispatchPaymentJob($payment, $orderId);

            return redirect('/payment/success?source=callback&payment_id=' . $razorpayPaymentId);

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return redirect('/payment/failed?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Verify Razorpay signature
     */
    protected function verifyPaymentSignature($payment, $signature)
    {
        $attributes = [
            'razorpay_order_id' => $payment['order_id'],
            'razorpay_payment_id' => $payment['id'],
            'razorpay_signature' => $signature
        ];

        $this->razorpayApi->utility->verifyPaymentSignature($attributes);
    }

    /**
     * Store transaction in YOUR transactions_history_razorpay table
     */
    protected function storeTransaction($payment, $source = 'webhook')
    {
        $notes = $payment['notes'] ?? [];

        return TransactionsHistoryRazorpay::updateOrCreate(
            ['razorpay_payment_id' => $payment['id']],
            [
                'razorpay_order_id' => $payment['order_id'],
                'razorpay_signature' => null, // Will be updated on callback
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

    /**
     * Dispatch your existing BuyNowProcessJob
     */
    protected function dispatchPaymentJob($payment, $orderId = null)
    {
        $notes = $payment['notes'] ?? [];

        // Format data exactly as your existing job expects
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
            'amount' => $notes['amount'] ?? null
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

            // Process based on payment type
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

            // Update order status
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

    /**
     * Process Subscription Payment
     */
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

        // Check if already has access
        $SubscriptionAccess = SubscriptionAccess::where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->first();

        // if ($existingAccess) {
        //     Log::warning('User already has subscription access', [
        //         'user_id' => $userId,
        //         'subscription_id' => $subscriptionId,
        //     ]);
        //     return;
        // }

        // Calculate dates
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
                                            
        // $SubscriptionAccess = SubscriptionAccess::where('subscription_id' , $orderItem->subscription_id)
        //                         ->where('user_id' , $orderItem->user_id)
        //                         ->first();
                                            
                                            
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

        // Create sale record
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

        // Create accounting entry
        $this->createAccountingEntry([
            'user_id' => $subscription->creator_id ?? 1,
            'subscription_id' => $subscriptionId,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Subscription: ' . $subscription->title,
        ]);

        // Process affiliate if exists
        $this->processAffiliate($userId, $amount, 'subscription', $subscriptionId);

        Log::info('Subscription access granted', [
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
            'end_date' => date('Y-m-d H:i:s', $endDate),
        ]);
    }

    /**
     * Process Webinar/Course Payment
     */
    public function processPartPayment($data)
    {
        $webinarId = $data['webinar_id'];
        $installmentId = $data['installment_id'];
        $userId = $data['user_id'];
        $amount = $data['amount'];
        $discountId = $data['discount_id'];
        
        
        // $part = WebinarPartPayment::Create([
        //             'user_id' => $userId,
        //             'installment_id' => $installmentId,
        //             'webinar_id' => $webinarId,
        //             'amount' => $amount,
        //             'created_at' => date('Y-m-d H:i:s')
        //         ]);
        
        // Log::info('Webinar Part payment Updated', [
        //     'user_id' => $userId,
        //     'webinar_id' => $webinarId,
        //     'part_id' => $part->id,
        // ]);
        
        
        $itemId = $webinarId;
        $itemType = $data['item_type'] ?? null;
        $totalDiscount= 0;
        // $discountId= $data['discountId'] ?? null;
        // $installmentId= $data['installment_id'];
        // $name = $data['name'];
        // $email = $data['email'];
        // $contact = $data['number'];
        
        $payment_type ="part";
        $launch_date =null;
        $paymentChannel = PaymentChannel::where('status', 'active')
            ->first();
        
        Log::info('$discountId = '.$discountId);
        
        $user = User::where('id', $userId)->first();
        
        Log::info('paid by user id '.$user->id);
       
        $item = Webinar::findOrFail($itemId);
        // $itemPrice = round($item->getPrice());
        
        $itemPrice = $item->getPrice();
        $price = $item->price;
        if($discountId > 0){
        $discountCoupon = Discount::where('id', $discountId)->first();
         $percent = $discountCoupon->percent ?? 1;
        $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
        // $itemPrice1=$itemPrice-$totalDiscount;
        }else{
            $totalDiscount = 0;
            // $itemPrice1=$itemPrice-$totalDiscount;
        }
        
        if($totalDiscount)
        $itemPrice -= $totalDiscount;
        
        if(isset($item->start_date) and $item->isCourse())
        $launch_date = $item->start_date;
        
        
        
        if($amount >= $itemPrice){
            //paid full payment of course
            Log::info('$amount >= $itemPrice means paid full payment');

        
        $order_main_table = Order::create([
                        'user_id' => $user->id,
                        'status' => 'part',
                        'amount' => isset($amount)?$amount: $installment->getUpfront($order->getItemPrice()),
                        'tax' => 0,
                        'total_discount' => $totalDiscount,
                        'total_amount' => isset($amount)?$amount: $installment->getUpfront($order->getItemPrice()),
                        'product_delivery_fee' => null,
                        'created_at' => time(),
                    ]);
                    
                    
                     $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
            $discountCoupon = null;
        }
                    
            if($order_main_table){
                            
               $order_item = OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order_main_table->id,
                    'webinar_id' => $itemId ?? null,
                    'bundle_id' => null,
                    'product_id' =>  null,
                    'product_order_id' =>null,
                    'reserve_meeting_id' => null,
                    'subscribe_id' =>null,
                    'promotion_id' => null,
                    'gift_id' =>null,
                    'installment_payment_id' => $installmentPayment->id ?? null,
                    'ticket_id' => null,
                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                    'amount' =>  isset($amount)?$amount:  $installment->getUpfront($order->getItemPrice()),
                    'total_amount' =>  isset($amount)?$amount: $installment->getUpfront($order->getItemPrice()),
                    'tax' => 0,
                    'tax_price' => 0,
                    'commission' => 0,
                    'commission_price' => 0,
                    'product_delivery_fee' => 0,
                    'discount' => $totalDiscount,
                    'created_at' => time(),
                ]);  
                session()->put('order_id1', $order_main_table->id);
                $data['order_id'] =$order_main_table->id;
            }
            

            // $channelManager = ChannelManager::makeChannel($paymentChannel);
            // $order1 = $channelManager->verifyBackgroundProccess($data);
                $sales_account=new PartPaymentController();
               $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);
               return  true;
            
    }else{
        //not paid full payment of course
        Log::info('not paid full payment of course');
        $installment = Installment::query()->where('id', $installmentId)
            ->where('enable', true)
            ->withCount([
                'steps'
            ])
            ->first();
            
           
             

        if (!empty($installment)) {
            if (!$installment->hasCapacity()) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.installment_not_capacity'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }


            $attachments = (!empty($data['attachments']) and count($data['attachments'])) ? array_map('array_filter', $data['attachments']) : [];
            $attachments = !empty($attachments) ? array_filter($attachments) : [];

            if ($installment->request_uploads) {
                if (count($attachments) < 1) {
                    return redirect()->back()->withErrors([
                        'attachments' => trans('validation.required', ['attribute' => 'attachments'])
                    ]);
                }
            }

            if (!empty($installment->capacity)) {
                $openOrdersCount = InstallmentOrder::query()->where('installment_id', $installment->id)
                    ->where('status', 'open')
                    ->count();

                if ($openOrdersCount >= $installment->capacity) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => trans('update.installment_not_capacity'),
                        'status' => 'error'
                    ];

                    return false;
                }
            }
            
        //   $item = $this->getItem($itemId, $itemType, $user);
          
          $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);
                
                // echo 'get installment plan <br>';
                
                $itemPrice = round($item->getPrice());
                $cash = $installments->sum('upfront');
                $plansCount = $installments->count();
                $minimumAmount = 0;
                  foreach ($installments as $installment) {
                    if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                        $minimumAmount = $installment->totalPayments($itemPrice);
                    }
                }
                // foreach 
                
                

            if (!empty($item)) {

                $productOrder = null;

                if ($itemType == 'product') {
                    $hasPhysicalProduct = ($item->type == Product::$physical);

                    $this->validate($request, [
                        'country_id' => Rule::requiredIf($hasPhysicalProduct),
                        'province_id' => Rule::requiredIf($hasPhysicalProduct),
                        'city_id' => Rule::requiredIf($hasPhysicalProduct),
                        'district_id' => Rule::requiredIf($hasPhysicalProduct),
                        'address' => Rule::requiredIf($hasPhysicalProduct),
                    ]);

                    /* Product Order */
                    $productOrder = $this->handleProductOrder($request, $user, $item);
                }

                $columnName = 'webinar_id';

                $status = 'paying';

                if (empty($installment->upfront)) {
                    $status = 'open';

                    if ($installment->needToVerify()) {
                        $status = 'pending_verification';
                    }
                }
                
                $order = InstallmentOrder:: where([
                    'installment_id' => $installment->id,
                    'user_id' => $user->id,
                    'webinar_id' => $item->id,
                    'status' => 'open',
                ])->first();
             
                $itemPrice = round($item->getPrice());
                $itemPrice1=$itemPrice-$totalDiscount;

                if(!$order){
                    //if doing 1st payment of course in part or installment
                    Log::info('doing 1st payment of course in part or installment no installment order');
                $order = InstallmentOrder::query()->updateOrCreate([
                    'installment_id' => $installment->id,
                    'user_id' => $user->id,
                    'discount' => $totalDiscount,
                    $columnName => $itemId,
                    'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                    'item_price' => $itemPrice1,
                    'status' => $status,
                ], [
                    'created_at' => $launch_date ?? time(),
                ]);
                }
                
        
                $part_amount_status=true;
                if (!empty($payment_type)) {
                    //paid by part payment link
                    $status = $payment_type;
                    date_default_timezone_set("Asia/Kolkata");
                    if($amount != 0){
                    WebinarPartPayment::Create([
                    'user_id' => $user->id,
                    'installment_id' => $installmentId,
                    'webinar_id' => $itemId,
                    'amount' => $amount,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                    }
              
                $part_amount=0;
                
           
                $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$user->id)
                ->where('webinar_id',$itemId)
                ->get();
                foreach ($WebinarPartPayment as $WebinarPartPayment1){
                    $part_amount = $part_amount + $WebinarPartPayment1->amount;
                }
                
                Log::info('total paid in part payment '.$part_amount);
                Log::info('order status is '.$order->status);
                if($order->status == 'open'){
                    //if already purcheshed the course in installment or part payment
                    Log::info('if already purcheshed the course in installment or part payment');
                    
                    $orderPayments = InstallmentOrderPayment:: where(
                    'installment_order_id', $order->id)
                    ->get();
                    $totalSaleAmount=0;
                    
                foreach($orderPayments as $orderPayment){
                    $saleId = $orderPayment->sale_id;
                    if($saleId){
                    $sale = Sale :: where(['id' => $saleId ,
                    'status' => null,])
                    ->first();
                    if($sale)
                $totalSaleAmount+=$sale->total_amount;
                    }
              
                    
                }
               
                $paidAmount=round($totalSaleAmount+$part_amount);                                                       //total paid amount including  totalSaleAmount and amount
            
                foreach($installments as $installment){
                    if($paidAmount > 0){
                     
                        $orderPayments1 = InstallmentOrderPayment:: where([
                        'type' => 'upfront' ,
                        'installment_order_id' => $order->id
                        ])->first();
                        // print_r($orderPayments1);
                        if($orderPayments1->status !='paid'){
                            // echo 'if upfront is not paid <br>';
                            if($paidAmount >= $order->item_price*$installment->upfront/100){
                                // echo 'do upfront paid <br>';
                                InstallmentOrderPayment:: where([
                        'id' => $orderPayments1 ->id
                        ])->update(['status'=>'paid']);
                                
                                //create order and order item also
                                $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                    ->where('user_id', $user->id)
                                    ->first();
                                    
                                $OrderItem = OrderItem :: where('id',$accounting->order_item_id)
                                    ->first();
                                    
                                OrderItem :: where('id',$OrderItem->id)
                                    ->update([
                                    'installment_type' => 'part' ?? null,
                                ]);
                                    
                                $order1 = Order :: where('id', $OrderItem->order_id)
                                    ->first();
                                
                               
                            }
                        }
                        
                        $paidAmount -=$order->item_price*$installment->upfront/100;
                  
                        foreach($installment->steps as $steps){
                            
                            // echo 'check steps <br>';
                            
                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'step_id' => $steps->id,
                            'installment_order_id' => $order->id,
                            ])
                            ->first();
                            
                            if($orderPayments1){
                                Log::info('$orderPayments1', [
                                    'orderPayments1' => $orderPayments1,
                                ]);
                                
                                // echo 'there is a step in installment order payment with id-'.$orderPayments1->id.'<br>';
                                if($orderPayments1->status !='paid'){
                                    // echo 'there is a step in installment order payment with unpaid status-'.$orderPayments1->id.'<br>';
                                    if($paidAmount >= $order->item_price*$steps->amount/100){
                                        $orderPayments1 -> update(['status'=>'paid']);
                                        Log::info('$orderPayments1', [
                                    'orderPayments1' => 'orderpayment paid',
                                ]);
                                        
                                        //create order and order item also
                                        $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                            ->where('user_id', $user->id)
                                            ->first();
                                            // echo 'set it paid and check if accounting is set or not for this IOP id-'.$orderPayments1->id.'<br>';
                                        if(!$accounting){  
                                            Log::info('$orderPayments1', [
                                    'orderPayments1' => '$accounting not',
                                ]);
                                            // echo ' accounting is not set so we will create a order ,order item ,sale, and accounting-'.$orderPayments1->id.'<br>';
                                        $order_main_table = Order::create([
                                            'user_id' => $user->id,
                                            'status' => Order::$paying,
                                            'amount' =>$order->item_price*$steps->amount/100,
                                            'tax' => 0,
                                            'total_discount' => $totalDiscount,
                                            'total_amount' => $order->item_price*$steps->amount/100,
                                            'product_delivery_fee' => null,
                                            'created_at' => time(),
                                        ]);
                                        
                                        
                                         $discountCoupon = Discount::where('id', $discountId)->first();
                    
                                        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
                                            $discountCoupon = null;
                                        }
                                                    
                                            if($order_main_table){
                                                            
                                              $order_item = OrderItem::create([
                                                    'user_id' => $user->id,
                                                    'order_id' => $order_main_table->id,
                                                    'webinar_id' => $itemId ?? null,
                                                    'bundle_id' => null,
                                                    'product_id' =>  null,
                                                    'product_order_id' =>null,
                                                    'reserve_meeting_id' => null,
                                                    'subscribe_id' =>null,
                                                    'promotion_id' => null,
                                                    'gift_id' =>null,
                                                    'installment_payment_id' => $orderPayments1->id ?? null,
                                                    'installment_type' => 'part' ?? null,
                                                    'ticket_id' => null,
                                                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                                                    'amount' =>  $order->item_price*$steps->amount/100,
                                                    'total_amount' =>  $order->item_price*$steps->amount/100,
                                                    'tax' => 0,
                                                    'tax_price' => 0,
                                                    'commission' => 0,
                                                    'commission_price' => 0,
                                                    'product_delivery_fee' => 0,
                                                    'discount' => $totalDiscount,
                                                    'created_at' => time(),
                                                ]);  
                                                session()->put('order_id1', $order_main_table->id);
                                                $data['order_id'] =$order_main_table->id;
              }

                                // $channelManager = ChannelManager::makeChannel($paymentChannel);
                                // $order1 = $channelManager->verifyBackgroundProccess($data);
                                    $sales_account=new PartPaymentController();
                                   $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);
               
                                        }else{
                                            // echo ' accounting is already set so we will update order ,order item ,sale, and accounting price-'.$orderPayments1->id.'<br>';
                                            Accounting :: where('id',$accounting->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                            ]);
                                            
                                            $OrderItem = OrderItem :: where('id',$accounting->order_item_id)
                                                ->first();
                                                
                                                OrderItem :: where('id',$OrderItem->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                                'installment_type' => 'part' ?? null,
                                                
                                            ]);
                                                
                                            $order = Order :: where('id', $OrderItem->order_id)
                                                ->first();
                                                
                                                Order :: where('id',$order->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                            ]);
                                            
                                         
                                            $sale =  Sale :: where('order_id',$order->id)->first();
                                            
                                            Sale ::  where('id',$sale->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                            ]);
                                           
                                                $installmentPayment = InstallmentOrderPayment :: where('_id', $orderPayments1->id)
                                                ->update([
                                                'sale_id' => $sale->id,
                                            ]);
                                            
                                        }
               
               
                                    }
                                    
                                }
                                $paidAmount -=$order->item_price*$steps->amount/100;
                                // echo 'decrease total paid amount by this step '.$steps->id.' payment -'.($order->item_price*$steps->amount/100).' Rs now paid amount is'.$paidAmount.'<br>';
                                    
                            }else{
                                
                                Log::info('InstallmentOrderPayment', [
                                    'installment_order_id' => $order->id,
                                    'step_id' => $steps->id,
                                    'amount' => $order->item_price*$steps->amount/100,
                                    // 'step' => $installmentStep,
                                    // 'all_paid' => $allPaid,
                                ]);

                                $orderPayments1 = InstallmentOrderPayment:: create([
                                    'installment_order_id' => $order->id,
                                    'sale_id' => null,
                                    'type' => 'step',
                                    'step_id' => $steps->id,
                                    'amount' => $order->item_price*$steps->amount/100,
                                    'status' => 'paying',
                                    'created_at' => time(),
                                ]);
                                // echo 'there is no step in installment order payment so we will create it id'.$orderPayments1->id.'<br>';
                                // echo $paidAmount;
                                // die('mayank');
                                if($paidAmount >= $order->item_price*$steps->amount/100){
                                    // echo 'if amount id payble to pay that step'.$orderPayments1->id.'<br>';
                                    // echo ' accounting is not set so we will create order ,order item ,sale, and accounting price-'.$orderPayments1->id.'<br>';
                                    $order_main_table = Order::create([
                                            'user_id' => $user->id,
                                            'status' => Order::$paying,
                                            'amount' =>$order->item_price*$steps->amount/100,
                                            'tax' => 0,
                                            'total_discount' => $totalDiscount,
                                            'total_amount' => $order->item_price*$steps->amount/100,
                                            'product_delivery_fee' => null,
                                            'created_at' => time(),
                                        ]);
                                        Log::info('$order_main_table', [
                                            'user_id' => $user->id,
                                            'status' => Order::$paying,
                                            'amount' =>$order->item_price*$steps->amount/100,
                                            'tax' => 0,
                                            'total_discount' => $totalDiscount,
                                            'total_amount' => $order->item_price*$steps->amount/100,
                                            'product_delivery_fee' => null,
                                            'created_at' => time(),
                                        ]);
                                        
                                         $discountCoupon = Discount::where('id', $discountId)->first();
                    
                                        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
                                            $discountCoupon = null;
                                        }
                                                    
                                            if($order_main_table){
                                                            
                                              $order_item = OrderItem::create([
                                                    'user_id' => $user->id,
                                                    'order_id' => $order_main_table->id,
                                                    'webinar_id' => $itemId ?? null,
                                                    'bundle_id' => null,
                                                    'product_id' =>  null,
                                                    'product_order_id' =>null,
                                                    'reserve_meeting_id' => null,
                                                    'subscribe_id' =>null,
                                                    'promotion_id' => null,
                                                    'gift_id' =>null,
                                                    'installment_payment_id' => $orderPayments1->id ?? null,
                                                    'installment_type' => 'part' ?? null,
                                                    'ticket_id' => null,
                                                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                                                    'amount' =>  $order->item_price*$steps->amount/100,
                                                    'total_amount' =>  $order->item_price*$steps->amount/100,
                                                    'tax' => 0,
                                                    'tax_price' => 0,
                                                    'commission' => 0,
                                                    'commission_price' => 0,
                                                    'product_delivery_fee' => 0,
                                                    'discount' => $totalDiscount,
                                                    'created_at' => time(),
                                                ]);  
                                                
                                                
                                        Log::info('$order_item', [
                                            'user_id' => $user->id,
                                                    'order_id' => $order_main_table->id,
                                                    'webinar_id' => $itemId ?? null,
                                                    'bundle_id' => null,
                                                    'product_id' =>  null,
                                                    'product_order_id' =>null,
                                                    'reserve_meeting_id' => null,
                                                    'subscribe_id' =>null,
                                                    'promotion_id' => null,
                                                    'gift_id' =>null,
                                                    'installment_payment_id' => $orderPayments1->id ?? null,
                                                    'installment_type' => 'part' ?? null,
                                                    'ticket_id' => null,
                                                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                                                    'amount' =>  $order->item_price*$steps->amount/100,
                                                    'total_amount' =>  $order->item_price*$steps->amount/100,
                                                    'tax' => 0,
                                                    'tax_price' => 0,
                                                    'commission' => 0,
                                                    'commission_price' => 0,
                                                    'product_delivery_fee' => 0,
                                                    'discount' => $totalDiscount,
                                                    'created_at' => time(),
                                        ]);
                                                session()->put('order_id1', $order_main_table->id);
                                                $data['order_id'] =$order_main_table->id;
            }

                    // $channelManager = ChannelManager::makeChannel($paymentChannel);
                    // $order1 = $channelManager->verifyBackgroundProccess($data);
                    // Log::info('$order1', [
                    //                         'order1' => $order1->id,
                    //                     ]);
                    $sales_account=new PartPaymentController();
                    $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);
                    }
                    
                    $paidAmount -=$order->item_price*$steps->amount/100;

                            }
                        }
                    }
                }
                return true;
                }
                
               
                if($part_amount > $amount){
                    $part_amount_status=false;
                }
                Log::info('$part_amount_status= '.$part_amount_status);

            }
            
      if($order->status != 'open'){
          Log::info('$order->status != open order status is '.$order->status);
        //   if paid 1st time in installment or part payment
        Log::info('if paid 1st time in installment or part payment');
 

                /* Update Product Order */
                if (!empty($productOrder)) {
                    $productOrder->update([
                        'installment_order_id' => $order->id
                    ]);
                }

                $notifyOptions = [
                    '[u.name]' => $order->user->full_name,
                    '[installment_title]' => $installment->main_title,
                    '[time.date]' => dateTimeFormat(time(), 'j M Y - H:i'),
                    '[amount]' => handlePrice($itemPrice),
                ];

                sendNotification("instalment_request_submitted", $notifyOptions, $order->user_id);
                sendNotification("instalment_request_submitted_for_admin", $notifyOptions, 1);

                /* Payment and Cart */
                // if($part_amount_status){
                if (!empty($installment->upfront)) {
                    $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                        'installment_order_id' => $order->id,
                        'sale_id' => null,
                        'type' => 'upfront',
                        'step_id' => null,
                        'amount' => $installment->getUpfront($order->getItemPrice()),
                        'status' => ($installment->getUpfront($order->getItemPrice()) <= $amount ? $status : 'part'),
                    ], [
                        'created_at' => time(),
                    ]);
                   Log::info('$installmentPayment->status is '.$installmentPayment->status);
                $order_main_table = Order::create([
                        'user_id' => $user->id,
                        'status' => ($status=='part') ? $status:Order::$paying,
                        'amount' => isset($amount)?$amount: $installment->getUpfront($order->getItemPrice()),
                        'tax' => 0,
                        'total_discount' => $totalDiscount,
                        'total_amount' => isset($amount)?$amount: $installment->getUpfront($order->getItemPrice()),
                        'product_delivery_fee' => null,
                        'created_at' => time(),
                    ]);
                    Log::info('$order_main_table->status is '.$order_main_table->status);
                    
                     $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
            $discountCoupon = null;
        }
                    
            if($order_main_table){
                            
               $order_item = OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order_main_table->id,
                    'webinar_id' => $itemId ?? null,
                    'bundle_id' => null,
                    'product_id' =>  null,
                    'product_order_id' =>null,
                    'reserve_meeting_id' => null,
                    'subscribe_id' =>null,
                    'promotion_id' => null,
                    'gift_id' =>null,
                    'installment_payment_id' => $installmentPayment->id ?? null,
                    'installment_type' => $status == 'part' ? $status : null,
                    'ticket_id' => null,
                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                    'amount' =>  isset($amount)?$amount:  $installment->getUpfront($order->getItemPrice()),
                    'total_amount' =>  isset($amount)?$amount: $installment->getUpfront($order->getItemPrice()),
                    'tax' => 0,
                    'tax_price' => 0,
                    'commission' => 0,
                    'commission_price' => 0,
                    'product_delivery_fee' => 0,
                    'discount' => $totalDiscount,
                    'created_at' => time(),
                ]);  
                 session()->put('order_id1', $order_main_table->id);
                 $data['order_id'] =$order_main_table->id;
                 Log::info('$order_item->installment_type is '.$order_item->installment_type);
            }
            Log::info('befor $channelManager verified the payment with order status '.$order_main_table->status);
            // $channelManager = ChannelManager::makeChannel($paymentChannel);
            // $order1 = $channelManager->verifyBackgroundProccess($data);
            // Log::info('$channelManager verified the payment with order status '.$order1->status);
            $sales_account=new PartPaymentController();
            $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);
              return true;
    
                   
                } else {

                    if ($installment->needToVerify()) {
                        sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                        sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1); // Admin

                        return redirect('/installments/request_submitted');
                    } else {
                        sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                        return $this->handleOpenOrder($item, $productOrder);
                    }
                }
          
            }
            }
        }
        $this->shortPaymentSectionBackgroundProcess($data ,$userid,$webinarId);
    }
        
    }
    
    public function shortPaymentSectionBackgroundProcess($data ,$userid,$item){
        // find problem in this function to short single user
        Log::info('shortPaymentSectionBackgroundProcess');
        $WebinarPartPayment = WebinarPartPayment :: select('user_id', 'webinar_id', 'installment_id', DB::raw('sum(amount) as total_amount'))
        ->where('user_id',$userid)
        ->where('webinar_id',$item)
        ->groupBy('user_id', 'webinar_id')
        ->first();
    
        $order =InstallmentOrder::where([
                    'installment_id' => $WebinarPartPayment->installment_id,
                    'user_id' => $WebinarPartPayment->user_id,
                    'webinar_id' => $WebinarPartPayment->webinar_id,
                    'status' => 'open',
                ])->first();
                
        $orderPayments = InstallmentOrderPayment :: where('installment_order_id', $order->id)
            ->get();

                    $totalSaleAmount=0;
                    
                foreach($orderPayments as $orderPayment){
                    $saleId = $orderPayment->sale_id;
                    if($saleId){
                    $sale = Sale :: where(['id' => $saleId ,
                    'status' => null,])
                    ->first();
                    if($sale)
                $totalSaleAmount+=$sale->total_amount;
                    }
             
                }
              $paidAmount = $totalSaleAmount  + $WebinarPartPayment->total_amount;
             
               $user= User::where('id', $WebinarPartPayment->user_id)->first();
              
              $item = $this->getItem($WebinarPartPayment->webinar_id, 'course', $user);
          
          $installmentPlans = new InstallmentPlans($user);
                $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);
             
                $itemPrice = $item->getPrice();
                $cash = $installments->sum('upfront');
                $plansCount = $installments->count();
                $minimumAmount = 0;
                  foreach ($installments as $installment) {
                    if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                        $minimumAmount = $installment->totalPayments($itemPrice);
                    }
                }
                
                foreach($installments as $installment){
                   
                    if($paidAmount > 0){
                       
                        $orderPayments1 = InstallmentOrderPayment:: where([
                        'type' => 'upfront' ,
                        'installment_order_id' => $order->id
                        ])->first();
                        $installmentOrderId= $order->id;
                        
                        if($orderPayments1->status !='paid'){
                            if($paidAmount >= $order->item_price*$installment->upfront/100){
                              
                                InstallmentOrderPayment:: where([
                        'id' => $installmentOrderId
                        ])->update(['status'=>'paid']);
                                
                                
                                // create order and order item also
                                $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                    ->where('user_id', $user->id)
                                    ->first();
                                    
                                $OrderItem = OrderItem :: where('id',$accounting->order_item_id)
                                    ->first();
                                    
                                OrderItem :: where('id',$OrderItem->id)
                                    ->update([
                                    'installment_type' => 'part' ?? null,
                                ]);
                                    
                                $order1 = Order :: where('id', $OrderItem->order_id)
                                    ->first();
                             
                            }
                        }
                        
                        $paidAmount -=$order->item_price*$installment->upfront/100;
                   
                        foreach($installment->steps as $steps){
                            
                      
                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'step_id' => $steps->id,
                            'installment_order_id' => $installmentOrderId,
                            ])
                            ->first();
                            
                            if($orderPayments1){
                                if($orderPayments1->status !='paid'){
                                    if($paidAmount >= $order->item_price*$steps->amount/100){
                                        $orderPayments1 -> update(['status'=>'paid']);
                                        
                                        // create order and order item also
                                        $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                            ->where('user_id', $user->id)
                                            ->first();
                                        if(!$accounting){  
                                        $order_main_table = Order::create([
                                            'user_id' => $user->id,
                                            'status' => Order::$paying,
                                            'amount' =>$order->item_price*$steps->amount/100,
                                            'tax' => 0,
                                            'total_discount' => $totalDiscount ?? null,
                                            'total_amount' => $order->item_price*$steps->amount/100,
                                            'product_delivery_fee' => null,
                                            'created_at' => time(),
                                        ]);
                                        
                                        
                                         $discountCoupon = Discount::where('id', $discountId)->first();
                    
                                        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
                                            $discountCoupon = null;
                                        }
                                                    
                                            if($order_main_table){
                                                            
                                               $order_item = OrderItem::create([
                                                    'user_id' => $user->id,
                                                    'order_id' => $order_main_table->id,
                                                    'webinar_id' => $itemId ?? null,
                                                    'bundle_id' => null,
                                                    'product_id' =>  null,
                                                    'product_order_id' =>null,
                                                    'reserve_meeting_id' => null,
                                                    'subscribe_id' =>null,
                                                    'promotion_id' => null,
                                                    'gift_id' =>null,
                                                    'installment_payment_id' => $orderPayments1->id ?? null,
                                                    'installment_type' => 'part' ?? null,
                                                    'ticket_id' => null,
                                                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                                                    'amount' =>  $order->item_price*$steps->amount/100,
                                                    'total_amount' =>  $order->item_price*$steps->amount/100,
                                                    'tax' => 0,
                                                    'tax_price' => 0,
                                                    'commission' => 0,
                                                    'commission_price' => 0,
                                                    'product_delivery_fee' => 0,
                                                    'discount' => $totalDiscount ?? null,
                                                    'created_at' => time(),
                                                ]);  
                                                }
                                                $sales_account=new PartPaymentController();
                                               $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);
               
                                        }else{
                                            Accounting :: where('id',$accounting->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                            ]);
                                            
                                            $OrderItem = OrderItem :: where('id',$accounting->order_item_id)
                                                ->first();
                                                
                                                OrderItem :: where('id',$OrderItem->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                                'installment_type' => 'part' ?? null,
                                                
                                            ]);
                                                
                                            $order = Order :: where('id', $OrderItem->order_id)
                                                ->first();
                                                
                                                Order :: where('id',$order->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                            ]);
                                            
                                            $sale =  Sale :: where('order_id',$order->id)->first();
                                            
                                            Sale ::  where('id',$sale->id)
                                                ->update([
                                                'total_amount' => $order->item_price*$steps->amount/100,
                                            ]);
                                            
                                            
                                                $installmentPayment = InstallmentOrderPayment :: where('id', $orderPayments1->id)
                                                ->update([
                                                'sale_id' => $sale->id,
                                            ]);
                                            
                                        }
               
                                    }
                                    
                                }
                                $paidAmount -=$order->item_price*$steps->amount/100;

                            }else{
                               
                                $orderPayments1 = InstallmentOrderPayment:: create([
                                    'installment_order_id' => $installmentOrderId,
                                    'sale_id' => null,
                                    'type' => 'step',
                                    'step_id' => $steps->id,
                                    'amount' => $order->item_price*$steps->amount/100,
                                    'status' => 'paying',
                                
                                    'created_at' => time(),
                                ]);
                             
                                if($paidAmount >= $order->item_price*$steps->amount/100){
                                   
                                    $order_main_table = Order::create([
                                            'user_id' => $user->id,
                                            'status' => Order::$paying,
                                            'amount' =>$order->item_price*$steps->amount/100,
                                            'tax' => 0,
                                            'total_discount' => $totalDiscount ?? null,
                                            'total_amount' => $order->item_price*$steps->amount/100,
                                            'product_delivery_fee' => null,
                                            'created_at' => time(),
                                        ]);
                                        $discountId=null;
                                        
                                         $discountCoupon = Discount::where('id', $discountId)->first();
                    
                                        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
                                            $discountCoupon = null;
                                        }
                                                    
                                            if($order_main_table){
                                                            
                                               $order_item = OrderItem::create([
                                                    'user_id' => $user->id,
                                                    'order_id' => $order_main_table->id,
                                                    'webinar_id' => $itemId ?? null,
                                                    'bundle_id' => null,
                                                    'product_id' =>  null,
                                                    'product_order_id' =>null,
                                                    'reserve_meeting_id' => null,
                                                    'subscribe_id' =>null,
                                                    'promotion_id' => null,
                                                    'gift_id' =>null,
                                                    'installment_payment_id' => $orderPayments1->id ?? null,
                                                    'installment_type' => 'part' ?? null,
                                                    'ticket_id' => null,
                                                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                                                    'amount' =>  $order->item_price*$steps->amount/100,
                                                    'total_amount' =>  $order->item_price*$steps->amount/100,
                                                    'tax' => 0,
                                                    'tax_price' => 0,
                                                    'commission' => 0,
                                                    'commission_price' => 0,
                                                    'product_delivery_fee' => 0,
                                                    'discount' => $totalDiscount ?? null,
                                                    'created_at' => time(),
                                                ]);  
                                                }
                                                $sales_account=new PartPaymentController();
                                               $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);
                                    
                                }
                             
                          $paidAmount -=$order->item_price*$steps->amount/100;

                            }
                        }
                    }
                }

    }

    /**
     * Process Webinar/Course Payment
     */
    protected function processWebinarPayment($data)
    {
        $webinarId = $data['webinar_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $webinar = Webinar::findOrFail($webinarId);
        $user = User::findOrFail($userId);

        // Check if already purchased
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

        // Create sale record (this grants access)
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

        // Create accounting entry
        $this->createAccountingEntry([ 
            'user_id' => $webinar->creator_id,
            'webinar_id' => $webinarId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Course purchase: ' . $webinar->title,
        ]);

        // Update webinar sales count
        // $webinar->increment('sales');

        // Process affiliate if exists
        $this->processAffiliate($userId, $amount, 'webinar', $webinarId);

        // Send notification (if you have notification system)
        $this->sendPurchaseNotification($user, $webinar, 'webinar');

        Log::info('Webinar access granted', [
            'user_id' => $userId,
            'webinar_id' => $webinarId,
        ]);
    }

    /**
     * Process Bundle Payment
     */
    protected function processBundlePayment($data)
    {
        $bundleId = $data['bundle_id'];
        $orderId = $data['order_id'];
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $bundle = Bundle::findOrFail($bundleId);
        $user = User::findOrFail($userId);

        // Check if already purchased
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

        // Create sale record
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

        // Create accounting entry
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
                // Log::info('OrderItem',[$orderItem->bundle->bundleWebinars]);
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

        // Update bundle sales count
        // $bundle->increment('sales');

        // Process affiliate if exists
        $this->processAffiliate($userId, $amount, 'bundle', $bundleId);

        Log::info('Bundle access granted', [
            'user_id' => $userId,
            'bundle_id' => $bundleId,
        ]);
    }

    /**
     * Process Product Payment
     */
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

        // Create sale record
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

        // Create accounting entry
        $this->createAccountingEntry([
            'user_id' => $product->creator_id,
            'product_id' => $productId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Product purchase: ' . $product->title,
        ]);

        // Update product sales and inventory
        // $product->increment('sales');
        
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

        // Process affiliate if exists
        $this->processAffiliate($userId, $amount, 'product', $productId);

        Log::info('Product purchase completed', [
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Process Cart Payment (Multiple Items)
     */
    protected function processCartPayment($data)
    {
        $orderId = $data['order_id'];
        $userId = $data['user_id'];

        $order = Order::with('orderItems')->findOrFail($orderId);

        foreach ($order->orderItems as $item) {
            // Process each item based on its type
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

        // Clear user's cart
        Cart::where('creator_id', $userId)->delete();

        Log::info('Cart payment processed', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'items_count' => $order->orderItems->count(),
        ]);
    }

    /**
     * Process Meeting/Consultation Payment
     */
    protected function processMeetingPayment($data)
    {
        $meetingId = $data['meeting_id'];
        $meetingTimeId = $data['meeting_time_id'] ?? null;
        $userId = $data['user_id'];
        $amount = $this->getTransactionAmount($data['razorpay_payment_id']);

        $meeting = Meeting::findOrFail($meetingId);
        $user = User::findOrFail($userId);

        // Create reserve meeting record
        $reserveMeeting = ReserveMeeting::create([
            'meeting_id' => $meetingId,
            'meeting_time_id' => $meetingTimeId,
            'user_id' => $userId,
            'paid_amount' => $amount,
            'status' => 'open',
            'created_at' => time(),
        ]);

        // Create sale record
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

        // Create accounting entry
        $this->createAccountingEntry([
            'user_id' => $meeting->creator_id,
            'meeting_id' => $meetingId,
            'sale_id' => $sale->id,
            'amount' => $amount,
            'type' => 'addiction',
            'description' => 'Meeting booking: ' . $meeting->title,
        ]);

        // Update meeting time status if provided
        if ($meetingTimeId) {
            DB::table('meeting_times')
                ->where('id', $meetingTimeId)
                ->update(['status' => 'reserved']);
        }

        // Process affiliate if exists
        $this->processAffiliate($userId, $amount, 'meeting', $meetingId);

        Log::info('Meeting booking completed', [
            'user_id' => $userId,
            'meeting_id' => $meetingId,
            'reserve_meeting_id' => $reserveMeeting->id,
        ]);
    }

    /**
     * Process Installment Payment
     */
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

        // Update installment payment status
        $installmentPayment->update([
            'status' => 'paid',
            'payment_date' => time(),
        ]);
        
        $installmentOrder = $installmentPayment->installmentOrder;
        $webinarId = $installmentOrder->webinar_id;
        $webinar = Webinar::findOrFail($webinarId);

        // Create sale record
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

        // Check if all installments are paid
        
        $installmentOrder->update([
            'status' => 'open'
        ]);
        // $allPaid = $installmentOrder->installmentOrderPayments()
        //     ->where('status', '!=', 'paid')
        //     ->count() === 0;

        // if ($allPaid) {
        //     // All installments paid, grant full access
        //     $installmentOrder->update(['status' => 'completed']);
            
        //     // Grant access to the item (webinar, bundle, etc.)
        //     $this->grantInstallmentItemAccess($installmentOrder, $userId);
        // } else {
        //     // Activate next installment
        //     $nextInstallment = $installmentOrder->installmentOrderPayments()
        //         ->where('step', '>', $installmentStep)
        //         ->where('status', 'pending')
        //         ->orderBy('step')
        //         ->first();

        //     if ($nextInstallment) {
        //         $nextInstallment->update(['status' => 'paying']);
        //     }
        // }

        // Create accounting entry
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
            // 'all_paid' => $allPaid,
        ]);
    }

    /**
     * Grant access when all installments are paid
     */
    protected function grantInstallmentItemAccess($installmentOrder, $userId)
    {
        $itemType = $installmentOrder->item_type; // 'webinar', 'bundle', etc.
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

            // Add other types as needed
        }

        Log::info('Installment item access granted', [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
        ]);
    }

    /**
     * Helper: Create sale record
     */
    protected function createSaleRecord($data)
    {
        return Sale::create($data);
    }

    /**
     * Helper: Create accounting entry
     */
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
     * Helper: Process affiliate commission
     */
    protected function processAffiliate($userId, $amount, $type, $itemId)
    {
        // Check if user has an affiliate who referred them
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

        // Calculate commission (example: 10%)
        $commissionRate = 0.10; // 10%
        $commission = $amount * $commissionRate;

        // Create accounting entry for affiliate
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

    /**
     * Helper: Calculate subscription end date
     */
    protected function calculateSubscriptionEndDate($subscription, $startDate)
    {
        $days = $subscription->days ?? 30;
        
        if ($subscription->usable_count) {
            // Unlimited until usable_count is exhausted
            $days = 365; // 1 year default
        }

        return strtotime("+{$days} days", $startDate);
    }

    /**
     * Helper: Get transaction amount
     */
    protected function getTransactionAmount($razorpayPaymentId)
    {
        $transaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)
            ->first();

        return $transaction ? $transaction->amount : 0;
    }

    /**
     * Helper: Update order status
     */
    protected function updateOrderStatus($orderId)
    {
        $order = Order::find($orderId);
        
        if ($order) {
            $order->update([
                'status' => 'paid', // 'success' status
                'payment_data' => json_encode([
                    'gateway' => 'Razorpay',
                    'paid_at' => time(),
                ]),
            ]);
        }
    }

    /**
     * Helper: Send purchase notification (if notification system exists)
     */
    protected function sendPurchaseNotification($user, $item, $type)
    {
        try {
            // Implement your notification logic here
            // Example: $user->notify(new PurchaseSuccessNotification($item, $type));
            
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
        public function paymentOrderAfterVerifyBackgroundProccess($order)
    { 
        Log::info('paymentOrderAfterVerifyBackgroundProccess');
        
        if (!empty($order)) {
            Log::info('if', [
                'order_id' => $order->id,
                'type' => $order->status,
            ]);
            if ($order->status == Order::$paying) {
                $this->setPaymentAccountingBackgroundProccess($order);

                $order->update(['status' => Order::$paid]);
            }elseif ($order->status == 'part') {
                $this->setPaymentAccountingBackgroundProccess($order);
                $orderItem = OrderItem::where('order_id', $order->id)->first();
                $InstallmentOrderPayment = InstallmentOrderPayment :: where('id', $orderItem->installment_payment_id)->first();

            if($order->total_amount >= $InstallmentOrderPayment->amount){
                            $order->update(['status' => Order::$paid]);
                            InstallmentOrderPayment :: where('id', $orderItem->installment_payment_id)->update([
                            'status'=> 'paid']);
            }

            } else {
                if ($order->type === Order::$meeting) {
                    $orderItem = OrderItem::where('order_id', $order->id)->first();

                    if ($orderItem && $orderItem->reserve_meeting_id) {
                        $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();

                        if ($reserveMeeting) {
                            $reserveMeeting->update(['locked_at' => null]);
                        }
                    }
                }
            }

            session()->put($this->order_session_key, $order->id);


            return true;
        } else {
         
            return false;
        }
    }
    
     public function setPaymentAccountingBackgroundProccess($order, $type = null)
    { 
        Log::info('setPaymentAccountingBackgroundProccess');
       
        $cashbackAccounting = new CashbackAccounting();

        if ($order->is_charge_account) {
            Accounting::charge($order);

            $cashbackAccounting->rechargeWallet($order);
        } else {
            foreach ($order->orderItems as $orderItem) {
                
            //   if (empty($orderItem->bundle_id)) {
                $sale = Sale::createSales($orderItem, $order->payment_method);
            //   }
Log::info('For only createSales payment');
                if (!empty($orderItem->reserve_meeting_id)) {
                    $reserveMeeting = ReserveMeeting::where('id', $orderItem->reserve_meeting_id)->first();
                    $creater = User::where('id', $orderItem->reserveMeeting->meeting->creator_id)->first();
                    date_default_timezone_set('Asia/Kolkata');
                   
                    $reserveMeeting->update([
                        'sale_id' => $sale->id,
                        'reserved_at' => time()
                    ]);
                    $reserver = $reserveMeeting->user;

                    if ($reserver) {
                        $this->handleMeetingReserveReward($reserver);
                    }
                }

                if (!empty($orderItem->gift_id)) {
                    $gift = $orderItem->gift;

                    $gift->update([
                        'status' => 'active'
                    ]);

                    $gift->sendNotificationsWhenActivated($orderItem->total_amount);
                }

                if (!empty($orderItem->subscribe_id)) {
                    Accounting::createAccountingForSubscribe($orderItem, $type);
                } elseif (!empty($orderItem->promotion_id)) {
                    Accounting::createAccountingForPromotion($orderItem, $type);
                } elseif (!empty($orderItem->registration_package_id)) {
                    Accounting::createAccountingForRegistrationPackage($orderItem, $type);

                    if (!empty($orderItem->become_instructor_id)) {
                        BecomeInstructor::where('id', $orderItem->become_instructor_id)
                            ->update([
                                'package_id' => $orderItem->registration_package_id
                            ]);
                    }
                } elseif (!empty($orderItem->installment_payment_id)) {
                    Accounting::createAccountingForInstallmentPayment($orderItem, $type);

                    $this->updateInstallmentOrder($orderItem, $sale,$order->status);
                } elseif (!empty($orderItem->bundle_id)) {
                    Log::info('For only bundle payment');
                    
                    // For only bundle payment
                    Accounting::createAccounting($orderItem, $type);
                    
                    TicketUser::useTicket($orderItem);
                    
                    // For bundle has product
                    
                    if($orderItem->bundle->bundleWebinars){
                        // Log::info('OrderItem',[$orderItem->bundle->bundleWebinars]);
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
                    // print_r($orderItem);
                    // die();
                    
                    // $this->updateProductOrder1($sale, $orderItem);
                    

                    // if (!empty($orderItem->product_id)) {
                    //     $this->updateProductOrder($sale, $orderItem);
                    // }
                } elseif (!empty($orderItem->subscription_id)) {
                    Log::info('For only subscription_id payment');
                    
                    // For only bundle payment
                    Accounting::createAccounting($orderItem, $type);
                    
                    SubscriptionPayments::create([
                        'user_id' => $orderItem->user_id,
                        'subscription_id' => $orderItem->subscription_id,
                        'amount' => $orderItem->total_amount,
                        'created_at' => time()
                        ]);
                        
                    $SubscriptionPayments = SubscriptionPayments::where('subscription_id' , $orderItem->subscription_id)
                                            ->where('user_id' , $orderItem->user_id)
                                            ->get();
                                            
                                            Log::info('SubscriptionPayments');
                        
                    $Subscription = Subscription::where('id' , $orderItem->subscription_id)
                                            ->first();
                                            
                                            Log::info('Subscription',$Subscription->toArray());
                                            
                    $SubscriptionAccess = SubscriptionAccess::where('subscription_id' , $orderItem->subscription_id)
                                            ->where('user_id' , $orderItem->user_id)
                                            ->first();
                                            
                        Log::info('SubscriptionAccess');
                                            
                    $access_till_date = time() + ($Subscription->access_days * 24 * 60 * 60);
                    $paid_no_of_subscriptions = $SubscriptionPayments->count();
                    $access_content_count = $Subscription->video_count * $SubscriptionPayments->count();
                    
                    
                    Log::info('data');
                    
                    // print_r($access_till_date);
                    // print_r($access_content_count);
                    // print_r($paid_no_of_subscriptions);
                    
                    if(!empty($SubscriptionAccess->subscription_id)){
                        Log::info('if SubscriptionAccess');
                        $access_till_date1 = $SubscriptionAccess->access_till_date + ($Subscription->access_days * 24 * 60 * 60);
                        
                        $access_till_date = $access_till_date >= $access_till_date1 ? $access_till_date : $access_till_date1;
                        
                        $SubscriptionAccess->update([
                            'access_till_date' => $access_till_date,
                            'access_content_count' => $access_content_count,
                            'paid_no_of_subscriptions' => $paid_no_of_subscriptions
                            ]);
                        
                    }else{
                    Log::info('else SubscriptionAccess');
                        SubscriptionAccess::create([
                            'user_id' => $orderItem->user_id,
                            'subscription_id' => $Subscription->id,
                            'access_till_date' => $access_till_date,
                            'access_content_count' => $access_content_count,
                            'paid_no_of_subscriptions' => $paid_no_of_subscriptions,
                            'created_at' => time()
                            ]);
                        
                    }
                    
                    TicketUser::useTicket($orderItem);
                    
                    // For bundle has product
                    
                    // if($orderItem->bundle->bundleWebinars){
                    //     // Log::info('OrderItem',[$orderItem->bundle->bundleWebinars]);
                    //     Log::info('For only bundleWebinars payment');
                    //     foreach ($orderItem->bundle->bundleWebinars as $bundleWebinar){
                    //         if($bundleWebinar->product_id){
                    //             Log::info('For only product payment');
                    //             $product = $bundleWebinar->product;
                                
                    //             $productOrder = ProductOrder::updateOrCreate([
                    //                 'product_id' => $product->id,
                    //                 'seller_id' => $product->creator_id,
                    //                 'buyer_id' => $orderItem->user_id,
                    //                 'bundle_id' => $orderItem->bundle_id,
                    //                 'sale_id' => null,
                    //                 'status' => 'pending',
                    //             ], [
                    //                 'specifications' => null,
                    //                 'quantity' => 1,
                    //                 'discount_id' => null,
                    //                 'created_at' => time()
                    //             ]);
                                
                    //             Log::info('productOrder ceated');
                                
                    //             $order1 = Order::create([
                    //                 'user_id' => $orderItem->user_id,
                    //                 'status' => 'paid',
                    //                 'payment_method' => $order->payment_method,
                    //                 'amount' => 0,
                    //                 'tax' => 0,
                    //                 'total_discount' => 0,
                    //                 'total_amount' => 0,
                    //                 'product_delivery_fee' => null,
                    //                 'created_at' => time(),
                    //             ]);
                                
                    //             $OrderItem1 = OrderItem::create([
                    //                 'user_id' => $orderItem->user_id,
                    //                 'order_id' => $order1->id,
                    //                 'webinar_id' => null,
                    //                 'bundle_id' =>  null,
                    //                 'product_id' => $product->id,
                    //                 'product_order_id' => $productOrder->id,
                    //                 'reserve_meeting_id' =>null,
                    //                 'subscribe_id' => null,
                    //                 'promotion_id' =>  null,
                    //                 'gift_id' => null,
                    //                 'installment_payment_id' =>  null,
                    //                 'ticket_id' =>  null,
                    //                 'discount_id' =>null,
                    //                 'amount' => 0,
                    //                 'total_amount' => 0,
                    //                 'tax' => 0,
                    //                 'tax_price' => 0,
                    //                 'commission' => 0,
                    //                 'commission_price' => 0,
                    //                 'product_delivery_fee' => 0,
                    //                 'discount' => 0,
                    //                 'created_at' => time(),
                    //             ]);
            
            
                    //             $seller_id = OrderItem::getSeller($orderItem);
                    //             $sale = Sale::create([
                    //                 'buyer_id' => $orderItem->user_id,
                    //                 'seller_id' => $seller_id,
                    //                 'order_id' => $OrderItem1->order_id,
                    //                 'webinar_id' => null,
                    //                 'bundle_id' => null,
                    //                 'meeting_id' => null,
                    //                 'meeting_time_id' =>  null,
                    //                 'subscribe_id' => null,
                    //                 'promotion_id' => null,
                    //                 'registration_package_id' => null,
                    //                 'product_order_id' => $productOrder->id,
                    //                 'installment_payment_id' => null,
                    //                 'status' =>  null,
                    //                 'gift_id' => null,
                    //                 'type' => 'product',
                    //                 'payment_method' => $order->payment_method,
                    //                 'amount' => 0,
                    //                 'tax' => 0,
                    //                 'via_payment' => null,
                    //                 'commission' => 0,
                    //                 'discount' => 0,
                    //                 'total_amount' => 0,
                    //                 'product_delivery_fee' => 0,
                    //                 'created_at' => time(),
                    //             ]);
                    //             Log::info('sale created');
                    //             $status = ProductOrder::$waitingDelivery;

                    //             if ($product and $product->isVirtual()) {
                    //                 $status = ProductOrder::$success;
                    //             }
                        
                    //             ProductOrder::where('id', $productOrder->id)
                    //                 ->where(function ($query) use ($orderItem) {
                    //                     $query->where(function ($query) use ($orderItem) {
                    //                         $query->whereNotNull('buyer_id');
                    //                         $query->where('buyer_id', $orderItem->user_id);
                    //                     });
                    //                 })
                    //                 ->update([
                    //                     'sale_id' => $sale->id,
                    //                     'status' => $status,
                    //                 ]);
            
                        
                    //             OrderAddress::where('order_id', $order->id)
                    //                 ->update([
                    //                     'order_id' => $order1->id,
                    //                 ]);
                                    
                    //             Log::info('Done');
                    //         }
                    //     }
                    // }
                    // print_r($orderItem);
                    // die();
                    
                    // $this->updateProductOrder1($sale, $orderItem);
                    

                    // if (!empty($orderItem->product_id)) {
                    //     $this->updateProductOrder($sale, $orderItem);
                    // }
                } else {
                    // webinar and meeting and product and bundle

                    Accounting::createAccounting($orderItem, $type);
                    
                    TicketUser::useTicket($orderItem);

                    if (!empty($orderItem->product_id)) {
                        $this->updateProductOrder($sale, $orderItem);
                    }
                }
            }

            // Set Cashback Accounting For All Order Items
            $cashbackAccounting->setAccountingForOrderItems($order->orderItems);
        }

        Cart::emptyCart($order->user_id);
    }
    
        private function updateInstallmentOrder($orderItem, $sale ,$order_status)
    {
        // die("updateInstallmentOrder");
        $installmentPayment = $orderItem->installmentPayment;

        if (!empty($installmentPayment)) {
            $installmentOrder = $installmentPayment->installmentOrder;

            $installmentPayment->update([
                'sale_id' => $sale->id,
                'status' => $order_status == 'part'?$order_status:'paid',
            ]);

            /* Notification Options */
            $notifyOptions = [
                '[u.name]' => $installmentOrder->user->full_name,
                '[installment_title]' => $installmentOrder->installment->main_title,
                '[time.date]' => dateTimeFormat(time(), 'j M Y - H:i'),
                '[amount]' => handlePrice($installmentPayment->amount),
            ];

            if ($installmentOrder and $installmentOrder->status == 'paying' and $installmentPayment->type == 'upfront') {
                $installment = $installmentOrder->installment;

                if ($installment) {
                    if ($installment->needToVerify()) {
                        $status = 'pending_verification';

                        sendNotification("installment_verification_request_sent", $notifyOptions, $installmentOrder->user_id);
                        sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1); // Admin
                    } else {
                        $status = 'open';

                        sendNotification("paid_installment_upfront", $notifyOptions, $installmentOrder->user_id);
                    }

                    $installmentOrder->update([
                        'status' => $status
                    ]);

                    if ($status == 'open' and !empty($installmentOrder->product_id) and !empty($installmentOrder->product_order_id)) {
                        $productOrder = ProductOrder::query()->where('installment_order_id', $installmentOrder->id)
                            ->where('id', $installmentOrder->product_order_id)
                            ->first();

                        $product = Product::query()->where('id', $installmentOrder->product_id)->first();

                        if (!empty($product) and !empty($productOrder)) {
                            $productOrderStatus = ProductOrder::$waitingDelivery;

                            if ($product->isVirtual()) {
                                $productOrderStatus = ProductOrder::$success;
                            }

                            $productOrder->update([
                                'status' => $productOrderStatus
                            ]);
                        }
                    }
                }
            }


            if ($installmentPayment->type == 'step') {
                sendNotification("paid_installment_step", $notifyOptions, $installmentOrder->user_id);
                sendNotification("paid_installment_step_for_admin", $notifyOptions, 1); // For Admin
            }

        }
    }
    

}