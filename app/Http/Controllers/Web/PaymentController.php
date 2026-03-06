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
use App\Models\DiscountCourse;
use App\Models\Api\User;
use App\Models\Affiliate;
use Illuminate\Support\Facades\Hash;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Services\PaymentEngine\PaymentLedgerService;
use App\Services\PaymentEngine\WalletService;

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
            'password' => 'nullable|string|min:6',
            'discount_id' => 'nullable|integer',
            'installment_id' => 'nullable|integer',
            'selectedDay' => 'nullable',
            'selectedTime' => 'nullable|integer',
            'amount' => 'nullable',
            'use_wallet' => 'nullable|boolean',
            'wallet_amount' => 'nullable|numeric|min:0',
            'slot_duration' => 'nullable|integer',
            'currency' => 'nullable|string|size:3',
        ]);
        session()->forget('meeting_discount_id');
        session()->forget('discountCouponId');
        session()->forget('discount_id');

        try {
            DB::beginTransaction();

            $paymentData = $this->getPaymentData($validated);

            // ── Wallet-mediated payment: auto-apply wallet balance ──
            $walletDeduction = 0;
            $walletService = app(WalletService::class);
            $originalAmount = (int) round((float) $paymentData['amount']);

            // Auto-apply wallet for logged-in users with balance
            if (auth()->check() && $originalAmount > 0) {
                $userId = auth()->id();
                $walletBalance = $walletService->balance($userId);

                if ($walletBalance > 0) {
                    $walletDeduction = (int) min(floor($walletBalance), $originalAmount);

                    Log::info('Wallet auto-applied', [
                        'user_id' => $userId,
                        'wallet_balance' => $walletBalance,
                        'wallet_deduction' => $walletDeduction,
                        'original_amount' => $originalAmount,
                    ]);
                }
            }

            // Always create order first (needed for all paths)
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

            // Always store wallet info on the order (DB-persisted — survives webhooks)
            // Even when walletDeduction=0, original_amount is needed so
            // processWalletMediatedPayment can credit gateway→wallet→purchase
            // Capture the currency the user is paying in (from request or user profile/cookie).
            // Storing it on the order ensures Razorpay receives the correct currency regardless
            // of what currency() returns at callback time.
            $paymentCurrency = !empty($validated['currency'])
                ? strtoupper($validated['currency'])
                : currency();

            $order->update([
                'payment_data' => json_encode([
                    'wallet_deduction' => $walletDeduction,
                    'original_amount'  => $originalAmount,
                    'currency'         => $paymentCurrency,
                ]),
            ]);

            // ── Full wallet payment (wallet covers entire amount) ──
            if ($walletDeduction > 0 && $walletDeduction >= $originalAmount) {
                $userId = auth()->id();
                if (empty($userId)) {
                    $user = User::findOrCreateForPurchase(
                        $input['email'], $input['number'], $input['name'], $input['password'] ?? null
                    );
                    $userId = $user->id;
                }

                // Debit full purchase amount from wallet
                $walletService->purchaseFromWallet(
                    $userId,
                    $originalAmount,
                    (int) $order->id,
                    'Full wallet purchase for order #' . $order->id
                );

                // Create a synthetic TransactionsHistoryRazorpay record so getTransactionAmount() works
                $walletPaymentId = 'wallet_' . $order->id . '_' . $userId . '_' . time();
                TransactionsHistoryRazorpay::create([
                    'razorpay_payment_id' => $walletPaymentId,
                    'razorpay_order_id' => 'wallet_order_' . $order->id,
                    'order_id' => $order->id,
                    'payment_type' => $paymentData['type'],
                    'user_id' => $userId,
                    'name' => $input['name'] ?? null,
                    'email' => $input['email'] ?? null,
                    'number' => $input['number'] ?? null,
                    'amount' => $originalAmount,
                    'status' => 'completed',
                    'payment_method' => 'wallet',
                    'source' => 'wallet',
                    'metadata' => json_encode(['wallet_deduction' => $walletDeduction, 'order_id' => $order->id]),
                    'razorpay_description' => $paymentData['description'] ?? 'Wallet Payment',
                    'processed_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::commit();

                // Dispatch via normal job path (outside transaction — avoids nested beginTransaction)
                $jobData = array_merge($paymentData, [
                    'razorpay_payment_id' => $walletPaymentId,
                    'payment_type' => $paymentData['type'],
                    'order_id' => $order->id,
                    'user_id' => $userId,
                    'name' => $input['name'] ?? null,
                    'email' => $input['email'] ?? null,
                    'number' => $input['number'] ?? null,
                ]);

                // Process synchronously (record already marked processed_at, so job idempotency check passes)
                try {
                    // Ensure all required fields are present for installment processing
                    $jobData = array_merge($paymentData, [
                        'razorpay_payment_id' => $walletPaymentId,
                        'payment_type' => $paymentData['type'],
                        'order_id' => $order->id,
                        'user_id' => $userId,
                        'name' => $input['name'] ?? null,
                        'email' => $input['email'] ?? null,
                        'number' => $input['number'] ?? null,
                        // Ensure installment-specific fields are present
                        'webinar_id' => $paymentData['webinar_id'] ?? null,
                        'installment_id' => $paymentData['installment_id'] ?? null,
                        'discount_id' => $paymentData['discount'] ?? 0,
                    ]);
                    
                    Log::info('Processing wallet full payment', [
                        'order_id' => $order->id,
                        'user_id' => $userId,
                        'payment_type' => $paymentData['type'],
                        'webinar_id' => $jobData['webinar_id'],
                        'installment_id' => $jobData['installment_id'],
                        'amount' => $originalAmount,
                    ]);
                    
                    $this->paymentVerifyBackgroundProccess($jobData);
                } catch (\Exception $e) {
                    Log::error('Wallet full-payment processing failed (order created, wallet debited): ' . $e->getMessage(), [
                        'order_id' => $order->id, 'user_id' => $userId,
                        'exception' => $e->getTraceAsString(),
                    ]);
                    throw $e;
                }

                return response()->json([
                    'success' => true,
                    'wallet_paid' => true,
                    'wallet_deduction' => $walletDeduction,
                    'message' => 'Payment completed using wallet balance',
                ]);
            }

            // ── Partial wallet + Razorpay (or no wallet) ──
            if ($walletDeduction > 0) {
                // Reduce the Razorpay amount by wallet deduction (integer math, no float issues)
                $paymentData['amount'] = $originalAmount - $walletDeduction;
                $order->update(['total_amount' => $paymentData['amount']]);
            }

            $razorpayOrder = $this->createRazorpayOrder($order, $paymentData);

            DB::commit();

            return response()->json(array_merge([
                'success' => true,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency'   => $razorpayOrder['currency'],
                'order_id' => $order->id,
                'key' => env('RAZORPAY_API_KEY'),
                'wallet_deduction' => $walletDeduction,
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
                $totalDiscount = 0;
                $Discount = null;

                // Only apply discount from explicit discount_id (user-entered coupon)
                if ($discountId > 0) {
                    $Discount = Discount::where('id', $discountId)->where('status', 'active')->first();
                }

                if ($Discount) {
                    if ($Discount->discount_type == 'fixed_amount') {
                        $totalDiscount = min($Discount->amount, $itemPrice);
                    } else {
                        $percent = $Discount->percent ?? 0;
                        $totalDiscount = ($itemPrice > 0) ? round($itemPrice * $percent / 100, 2) : 0;
                        if (!empty($Discount->max_amount) && $totalDiscount > $Discount->max_amount) {
                            $totalDiscount = $Discount->max_amount;
                        }
                    }
                }

                $itemPrice1 = max($itemPrice - $totalDiscount, 0);

                return [
                    'type' => 'webinar',
                    'item' => $webinar,
                    'webinar_id' => $webinar->id,
                    'discount' => $totalDiscount,
                    'amount' => $itemPrice1,
                    'description' => "Course: {$webinar->title}",
                    'user_data' => $validated,
                ];

            case 'part':
                $webinar = Webinar::findOrFail($itemId);

                $itemPrice = $webinar->getPrice();
                $totalDiscount = 0;
                $Discount = null;

                // Only apply discount from explicit discount_id (user-entered coupon)
                if ($discountId > 0) {
                    $Discount = Discount::where('id', $discountId)->where('status', 'active')->first();
                }
                if ($Discount) {
                    if ($Discount->discount_type == 'fixed_amount') {
                        $totalDiscount = min($Discount->amount, $itemPrice);
                    } else {
                        $percent = $Discount->percent ?? 0;
                        $totalDiscount = ($itemPrice > 0) ? round($itemPrice * $percent / 100, 2) : 0;
                        if (!empty($Discount->max_amount) && $totalDiscount > $Discount->max_amount) {
                            $totalDiscount = $Discount->max_amount;
                        }
                    }
                }
                $itemPrice1 = max($itemPrice - $totalDiscount, 0);

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
                                    ->sortBy('due_date')
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
                    $user = User::findOrCreateForPurchase(
                        $input['email'],
                        $input['number'],
                        $input['name'],
                        $input['password'] ?? null
                    );
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
                    $user = User::findOrCreateForPurchase(
                        $input['email'],
                        $input['number'],
                        $input['name'],
                        $input['password'] ?? null
                    );
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
                $slotDuration = (int) ($validated['slot_duration'] ?? 30);

                // Compute hours and end time based on selected slot duration
                $instructorTimezone = $meeting->getTimezone();
                $startAt = $this->handleUtcDate($day, $explodetime[0], $instructorTimezone);

                if ($slotDuration === 15) {
                    // 15-min half-slot: end = start + 15 min, price = half hourly rate
                    $slotEndTime = date('H:i', strtotime('+15 minutes', strtotime($explodetime[0])));
                    $endAt = $this->handleUtcDate($day, $slotEndTime, $instructorTimezone);
                    $hours = 0.5;
                } else {
                    // Full slot (30 min or whatever the slot defines)
                    $endAt = $this->handleUtcDate($day, $explodetime[1], $instructorTimezone);
                    $hours = (strtotime($explodetime[1]) - strtotime($explodetime[0])) / 1800;
                }

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

                // Mirror InstallmentsController exactly:
                //   getPrice() = effective/promotional price → base for EMI calculation
                //   price      = raw DB price               → base for coupon % discount
                $itemPrice = $item->getPrice();
                $rawPrice   = (float) ($item->price ?? $itemPrice);
                $totalDiscount = 0;
                $Discount = null;

                // Only apply discount from explicit discount_id (user-entered coupon)
                if ($discountId > 0) {
                    $Discount = Discount::where('id', $discountId)->where('status', 'active')->first();
                }
                if ($Discount) {
                    if ($Discount->discount_type == 'fixed_amount') {
                        $totalDiscount = min($Discount->amount, $itemPrice);
                    } else {
                        $percent = $Discount->percent ?? 0;
                        // Use raw price for discount calc — matches display logic in InstallmentsController
                        $totalDiscount = ($rawPrice > 0) ? round($rawPrice * $percent / 100, 2) : 0;
                        if (!empty($Discount->max_amount) && $totalDiscount > $Discount->max_amount) {
                            $totalDiscount = $Discount->max_amount;
                        }
                    }
                }
                $itemPrice1 = max($itemPrice - $totalDiscount, 0);

                if(empty($user)){
                    $input=$validated;
                    $user = User::findOrCreateForPurchase(
                        $input['email'],
                        $input['number'],
                        $input['name'],
                        $input['password'] ?? null
                    );
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
            $user = User::findOrCreateForPurchase(
                $input['email'],
                $input['number'],
                $input['name'],
                $input['password'] ?? null
            );
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

        // Use the currency stored on the order (set during initiatePayment from the request).
        // Falls back to currency() if not stored (e.g., legacy orders).
        $storedPaymentData = json_decode($order->payment_data ?? '{}', true);
        $orderCurrency = $storedPaymentData['currency'] ?? currency();

        $razorpayOrder = $this->razorpayApi->order->create([
            'receipt'  => 'order_' . $order->id . '_' . time(),
            'amount'   => (int) round((float) $order->total_amount, 0, PHP_ROUND_HALF_UP) * 100,
            'currency' => $orderCurrency,
            'notes'    => $notes,
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

            // Wallet-mediated: credit gateway to wallet, then debit full purchase from wallet
            $this->processWalletMediatedPayment($orderId, $razorpayPaymentId);

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
                'user_id' => $userId,
                'webinar_id' => $data['webinar_id'] ?? null,
                'installment_id' => $data['installment_id'] ?? null,
                'amount' => $this->getTransactionAmount($paymentId),
                'is_wallet_payment' => str_starts_with($paymentId, 'wallet_'),
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

            // Record wallet portion in UPE ledger for partial wallet payments
            $this->recordWalletInUpeLedger($data);

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

        // Legacy: record payment + sync SubscriptionAccess via shared service.
        // Renewal rule (max of fresh vs extended end date) is enforced inside the service.
        // See SubscriptionAccessService and SubscriptionAccessResolver for full documentation.
        $accessService = app(\App\Services\SubscriptionAccessService::class);
        $accessService->syncAccessAfterPayment($userId, $subscriptionId, $amount);

        // UPE CheckoutService (creates UPE sale + subscription + ledger + legacy Sale + accounting)
        $checkout = app(\App\Services\PaymentEngine\CheckoutService::class);
        $checkout->processSubscriptionPurchase($userId, $subscriptionId, $amount, 'razorpay', $data['razorpay_payment_id']);

        $this->processAffiliate($userId, $amount, 'subscription', $subscriptionId);

        Log::info('Subscription access granted', [
            'user_id'         => $userId,
            'subscription_id' => $subscriptionId,
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

        Log::info('processInstallmentPayment started', [
            'webinar_id' => $webinarId,
            'user_id' => $userId,
            'installment_id' => $installmentId,
            'amount' => $amount,
            'discount_id' => $discountId,
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'is_wallet_payment' => str_starts_with($data['razorpay_payment_id'], 'wallet_'),
        ]);

        // Backward compat: old Razorpay notes may still carry installment_payment_id
        if (!$webinarId && !empty($data['installment_payment_id'])) {
            Log::info('Using backward compat installment_payment_id');
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
            Log::error('processInstallmentPayment: webinar_id missing from payment data', $data);
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
            // Preserve existing wallet payment_data, merge with gateway info
            $existingData = $order->payment_data ? json_decode($order->payment_data, true) : [];
            $existingData['gateway'] = 'Razorpay';
            $existingData['paid_at'] = time();

            $order->update([
                'status' => 'paid',
                'payment_data' => json_encode($existingData),
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

    /**
     * Record wallet portion of a payment in the UPE ledger.
     * For partial wallet payments: adds a wallet_payment ledger entry alongside the razorpay entry.
     * For full wallet payments: the CheckoutService already records the full amount; this corrects payment_method.
     */
    protected function recordWalletInUpeLedger(array $data)
    {
        try {
            $orderId = $data['order_id'] ?? null;
            $userId = $data['user_id'] ?? null;
            if (!$orderId || !$userId) return;

            $order = Order::find($orderId);
            if (!$order || !$order->payment_data) return;

            $orderPaymentData = json_decode($order->payment_data, true);
            $walletDeduction = (float) ($orderPaymentData['wallet_deduction'] ?? 0);
            $originalAmount = (float) ($orderPaymentData['original_amount'] ?? 0);
            if ($walletDeduction <= 0) return;

            // Wallet-mediated flow: processWalletMediatedPayment already updated
            // TransactionsHistoryRazorpay to originalAmount, so CheckoutService
            // records the full amount. Adding a wallet entry here would double-count.
            if (!empty($orderPaymentData['wallet_mediated_processed'])) {
                Log::info('Wallet-mediated: skipping recordWalletInUpeLedger (full amount already in ledger)', [
                    'order_id' => $orderId, 'wallet_deduction' => $walletDeduction, 'original_amount' => $originalAmount,
                ]);
                return;
            }

            $isFullWallet = ($walletDeduction >= $originalAmount);
            $paymentType = $data['payment_type'] ?? $data['type'] ?? 'webinar';

            // Find the most recent UPE sale for this user created in the last minute
            $recentSale = UpeSale::where('user_id', $userId)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->orderByDesc('id')
                ->first();

            if (!$recentSale) return;

            $ledger = app(PaymentLedgerService::class);

            if ($isFullWallet) {
                Log::info('Full wallet payment recorded in UPE ledger', [
                    'sale_id' => $recentSale->id, 'amount' => $walletDeduction,
                ]);
            } else {
                // Legacy partial wallet (non-mediated): add a wallet_payment entry
                $idempotencyKey = "wallet_partial_{$orderId}_{$userId}";
                $existing = \App\Models\PaymentEngine\UpeLedgerEntry::where('idempotency_key', $idempotencyKey)->first();
                if (!$existing) {
                    $ledger->recordWalletPayment(
                        saleId: $recentSale->id,
                        amount: $walletDeduction,
                        processedBy: $userId,
                        description: "Wallet payment (partial) for order #{$orderId}",
                        idempotencyKey: $idempotencyKey
                    );
                    Log::info('Partial wallet payment recorded in UPE ledger', [
                        'sale_id' => $recentSale->id, 'wallet_amount' => $walletDeduction, 'order_id' => $orderId,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to record wallet in UPE ledger (non-fatal): ' . $e->getMessage(), [
                'order_id' => $data['order_id'] ?? null,
            ]);
        }
    }

    /**
     * Wallet-mediated payment processing.
     * After successful Razorpay payment:
     *   1. Credit gateway amount to wallet (TXN_GATEWAY_TOPUP)
     *   2. Debit full purchase amount from wallet (TXN_WALLET_PURCHASE)
     * Safe to call from both callback and webhook paths.
     * Idempotent: checks for existing wallet transaction before processing.
     */
    protected function processWalletMediatedPayment($orderId, $razorpayPaymentId = null)
    {
        if (empty($orderId)) return;

        try {
            $order = Order::find($orderId);
            if (!$order || !$order->payment_data) return;

            $paymentData = json_decode($order->payment_data, true);
            if (!empty($paymentData['wallet_mediated_processed'])) return;

            $walletDeduction = (float) ($paymentData['wallet_deduction'] ?? 0);
            $originalAmount = (float) ($paymentData['original_amount'] ?? 0);
            if ($originalAmount <= 0) return;

            $userId = $order->user_id ?? (auth()->check() ? auth()->id() : null);
            if (!$userId) return;

            // Idempotency: check if wallet purchase was already debited for this order
            $existing = \App\Models\PaymentEngine\WalletTransaction::where('reference_type', 'order')
                ->where('reference_id', $orderId)
                ->where('transaction_type', \App\Models\PaymentEngine\WalletTransaction::TXN_WALLET_PURCHASE)
                ->first();
            if ($existing) {
                Log::info('Wallet-mediated already processed for order', ['order_id' => $orderId]);
                return;
            }

            $walletSvc = app(WalletService::class);
            $gatewayAmount = $originalAmount - $walletDeduction;

            // Step 1: Credit gateway amount to wallet
            if ($gatewayAmount > 0) {
                $walletSvc->creditFromGateway(
                    $userId,
                    $gatewayAmount,
                    (int) $orderId,
                    $razorpayPaymentId
                );

                Log::info('Wallet-mediated: gateway credited to wallet', [
                    'user_id' => $userId, 'gateway_amount' => $gatewayAmount, 'order_id' => $orderId,
                ]);
            }

            // Step 2: Debit full purchase amount from wallet
            $walletSvc->purchaseFromWallet(
                $userId,
                $originalAmount,
                (int) $orderId,
                'Purchase for order #' . $orderId
            );

            // Update TransactionsHistoryRazorpay amount to full purchase amount
            // so getTransactionAmount() returns the correct total for process methods
            $txnHistory = TransactionsHistoryRazorpay::where('razorpay_payment_id', $razorpayPaymentId)->first();
            if ($txnHistory) {
                $txnHistory->update(['amount' => $originalAmount]);
            }

            // Mark as processed to prevent re-processing
            $paymentData['wallet_mediated_processed'] = true;
            $order->update(['payment_data' => json_encode($paymentData)]);

            Log::info('Wallet-mediated payment completed', [
                'user_id' => $userId,
                'gateway_amount' => $gatewayAmount,
                'wallet_used' => $walletDeduction,
                'total_purchase' => $originalAmount,
                'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Wallet-mediated payment failed (non-fatal): ' . $e->getMessage(), [
                'order_id' => $orderId,
            ]);
        }
    }

}