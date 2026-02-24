<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\RegionsDataByUser;
use App\Models\Bundle;
use App\Models\Cart;
use App\Models\CartInstallment;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentOrderAttachment;
use App\Models\InstallmentOrderPayment;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\RegistrationPackage;
use App\Models\Subscribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Accounting;
use App\Models\PaymentChannel;
use App\PaymentChannels\ChannelManager;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\WebinarPartPayment;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Sale;
use App\Http\Controllers\Web\PaymentController_old;
use App\User;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use App\Models\WebinarAccessControl;
use Illuminate\Support\Facades\Auth;
use App\Jobs\InstallmentProcessJob;
use Illuminate\Support\Facades\Log;
use App\Models\SubStepInstallment;

use Razorpay\Api\Api;
use App\Models\TransactionsHistoryRazorpay;
use App\Models\PaymentEngine\UpeProduct;
use App\Models\PaymentEngine\UpeSale;
use App\Models\PaymentEngine\UpeInstallmentPlan;
use App\Services\PaymentEngine\PaymentLedgerService;

class InstallmentsController extends Controller
{
    use RegionsDataByUser;

    public function index(Request $request, $installmentId)
    {
        try {
            $itemId = $request->get('item');
            $itemType = $request->get('item_type');

            if (!empty($itemId) and !empty($itemType) and getInstallmentsSettings('status')) {

                $item = $this->getItem($itemId, $itemType, null);

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

                        $itemPrice = $item->getPrice();
                $price = $item->price;
                if(!empty(session('discountCouponId'))){
                $discountId=session('discountCouponId');
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 0;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                        $hasPhysicalProduct = false;
                        if ($itemType == 'product') {
                            $quantity = $request->get('quantity', 1);
                            $itemPrice = $itemPrice * $quantity;
                            $hasPhysicalProduct = ($item->type == Product::$physical);
                        }
                       $installmentPlans = new InstallmentPlans();
                    $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);

                    $plansCount = $item->count();
                    $minimumAmount = 0;
                    foreach ($installments as $installment) {
                        if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                            $minimumAmount = $installment->totalPayments($itemPrice);
                        }
                    }
                     $paymentChannels = PaymentChannel::where('status', 'active')->get();
                     $webinar = Webinar::where('id', $itemId)
                    ->where('private', false)
                    ->where('status', 'active')
                    ->first();

                        // UPE: Determine next payable installment from immutable ledger
                        $upePayable = [
                            'isNewPurchase' => true,
                            'nextPayableType' => 'upfront',
                            'nextPayableStepId' => null,
                            'nextPayableAmount' => (int) round($installment->getUpfront($itemPrice1), 0, PHP_ROUND_HALF_UP),
                            'totalPaidFromLedger' => 0,
                            'allPaid' => false,
                        ];
                        if (auth()->check()) {
                            $upePayable = $this->getNextPayableFromUpe(auth()->id(), $item->id, $installment, $itemPrice1);
                        }

                        $data = [
                            'pageTitle' => trans('update.verify_your_installments'),
                            'installment' => $installment,
                            'installments' => $installments,
                            'overviewTitle' => $item->title,
                            'itemPrice' => $itemPrice1,
                            'itemType' => $itemType,
                            'itemId' => $item->id,
                            'item' => $item,
                            'cash' => $itemPrice,
                            'plansCount' => $plansCount,
                            'hasPhysicalProduct' => $hasPhysicalProduct,
                            'totalDiscount' => $totalDiscount,
                            'discountId' => !empty($discountId) ? $discountId : null,
                            'minimumAmount' => $minimumAmount,
                             'paymentChannels' => $paymentChannels,
                             'mayank' => '1',
                            'webinar' => $webinar,
                            'upePayable' => $upePayable,
                        ];

                           session(['success'=>false]);
                        $agent = new Agent();
                        if ($agent->isMobile()){
                            return view(getTemplate() . '.installment.plans', $data);
                        }else{
                            return view('web.default2' . '.installment.plans', $data);
                        }

                    }
                }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function partPayment(Request $request, $slug)
    {
        try {
            $course = Webinar::where('slug', $slug)
                    ->where('status', 'active')
                    ->first();
            
            if (!$course) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => 'Course not found',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }
            
            // Handle sub-step payments
            $subStepId = $request->get('sub_step_id');
            $stepId = $request->get('step_id');
            $amount = $request->get('amount');
            
            if ($subStepId && $stepId && $amount) {
                return $this->handleSubStepPayment($request, $course, $subStepId, $stepId, $amount);
            }
            
            $itemId = $course->id;
            $itemType = $course->type;

            $installmentPlans = new InstallmentPlans();
                $installments = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);
                if (count($installments) >0) {

                    $installmentId = $installments[0]->id;
                    
                }else{
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => 'Item not found',
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

                

            if (!empty($itemId) and !empty($itemType) and getInstallmentsSettings('status')) {

                $item = $this->getItem($itemId, $itemType, null);

                if (!$item) {
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => 'Item not found',
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

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

                        $itemPrice = $item->getPrice();
                $price = $item->price;
                if(!empty(session('discountCouponId'))){
                $discountId=session('discountCouponId');
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 0;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                        $hasPhysicalProduct = false;
                        if ($itemType == 'product') {
                            $quantity = $request->get('quantity', 1);
                            $itemPrice = $itemPrice * $quantity;
                            $hasPhysicalProduct = ($item->type == Product::$physical);
                        }
                       $installmentPlans = new InstallmentPlans();
                    $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);

                    $plansCount = $item->count();
                    $minimumAmount = 0;
                    foreach ($installments as $installment) {
                        if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                            $minimumAmount = $installment->totalPayments($itemPrice);
                        }
                    }
                     $paymentChannels = PaymentChannel::where('status', 'active')->get();

                        // UPE: Determine next payable installment from immutable ledger
                        $upePayable = [
                            'isNewPurchase' => true,
                            'nextPayableType' => 'upfront',
                            'nextPayableStepId' => null,
                            'nextPayableAmount' => (int) round($installment->getUpfront($itemPrice1), 0, PHP_ROUND_HALF_UP),
                            'totalPaidFromLedger' => 0,
                            'allPaid' => false,
                        ];
                        if (auth()->check()) {
                            $upePayable = $this->getNextPayableFromUpe(auth()->id(), $item->id, $installment, $itemPrice1);
                        }

                        $data = [
                            'pageTitle' => trans('update.verify_your_installments'),
                            'installment' => $installment,
                            'installments' => $installments,
                            'overviewTitle' => $item->title,
                            'itemPrice' => $itemPrice1,
                            'itemType' => $itemType,
                            'itemId' => $item->id,
                            'item' => $item,
                            'cash' => $itemPrice,
                            'plansCount' => $plansCount,
                            'hasPhysicalProduct' => $hasPhysicalProduct,
                            'totalDiscount' => $totalDiscount,
                            'discountId' => !empty($discountId) ? $discountId : null,
                            'minimumAmount' => $minimumAmount,
                             'paymentChannels' => $paymentChannels,
                             'mayank' => '1',
                             'webinar' => $course,
                             'amount' => $amount,
                            'upePayable' => $upePayable,
                        ];

                           session(['success'=>false]);
                        $agent = new Agent();
                        if ($agent->isMobile()){
                            return view(getTemplate() . '.installment.partPayment.plans', $data);
                        }else{
                            return view('web.default2' . '.installment.partPayment.plans', $data);
                        }

                    }
                }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('partPayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function index1(Request $request, $installmentId)
    {
        try {
            $user = auth()->user();
            $itemId = $request->get('item');
            $itemType = $request->get('item_type');

            if (empty($user) or !$user->enable_installments) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.you_cannot_use_installment_plans'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            if (!empty($itemId) and !empty($itemType) and getInstallmentsSettings('status')) {

                $item = $this->getItem($itemId, $itemType, $user);

                if (!empty($item)) {
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

                        $itemPrice = $item->getPrice();
                $price = $item->price;
                if(!empty(session('discountCouponId'))){
                $discountId=session('discountCouponId');
                $discountCoupon = Discount::where('id', $discountId)->first();
                 $percent = $discountCoupon->percent ?? 0;
                $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
                $itemPrice1=$itemPrice-$totalDiscount;
                }else{
                    $totalDiscount = 0;
                    $itemPrice1=$itemPrice-$totalDiscount;
                }

                        $hasPhysicalProduct = false;
                        if ($itemType == 'product') {
                            $quantity = $request->get('quantity', 1);
                            $itemPrice = $itemPrice * $quantity;
                            $hasPhysicalProduct = ($item->type == Product::$physical);
                        }

                        $data = [
                            'pageTitle' => trans('update.verify_your_installments'),
                            'installment' => $installment,
                            'itemPrice' => $itemPrice1,
                            'itemType' => $itemType,
                            'item' => $item,
                            'hasPhysicalProduct' => $hasPhysicalProduct,
                            'totalDiscount' => $totalDiscount,
                            'discountId' => !empty($discountId) ? $discountId : null,
                        ];

                        if ($hasPhysicalProduct) {
                            $data = array_merge($data, $this->getLocationsData($user));
                        }

                        $agent = new Agent();
                        if ($agent->isMobile()){
                            return view(getTemplate() . '.installment.verify', $data);
                        }else{
                            return view('web.default2' . '.installment.verify', $data);
                        }

                    }
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('index1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getItem($itemId, $itemType, $user)
    {
        try {
            if ($itemType == 'course') {
                $course = Webinar::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                    return $course;

            } else if ($itemType == 'bundles') {
                $bundle = Bundle::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                $hasBought = $bundle->checkUserHasBought($user);
                $canSale = ($bundle->canSale() and !$hasBought);

                if ($canSale and !empty($bundle->price)) {
                    return $bundle;
                }
            } elseif ($itemType == 'product') {
                $product = Product::where('status', Product::$active)
                    ->where('id', $itemId)
                    ->first();

                $hasBought = $product->checkUserHasBought($user);

                if (!$hasBought and !empty($product->price)) {
                    return $product;
                }
            } elseif ($itemType == 'registration_package') {
                $package = RegistrationPackage::where('id', $itemId)
                    ->where('status', 'active')
                    ->first();

                return $package;
            } elseif ($itemType == 'subscribe') {
                return Subscribe::where('id', $itemId)->first();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('getItem error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function getColumnByItemType($itemType)
    {
        if ($itemType == 'course') {
            return 'webinar_id';
        } elseif ($itemType == 'product') {
            return 'product_id';
        } elseif ($itemType == 'bundles') {
            return 'bundle_id';
        } elseif ($itemType == 'subscribe') {
            return 'subscribe_id';
        } elseif ($itemType == 'registration_package') {
            return 'registration_package_id';
        }
    }

    /**
     * Determine the next payable installment using the UPE ledger as source of truth.
     *
     * Returns:
     *   - isNewPurchase: true if no existing installment sale in UPE
     *   - nextPayableType: 'upfront' or 'step'
     *   - nextPayableStepId: legacy InstallmentStep ID (null for upfront)
     *   - nextPayableAmount: INR integer amount the user must pay now
     *   - totalPaidFromLedger: total amount already recorded in UPE ledger
     *   - allPaid: true if every installment (upfront + all steps) is fully covered
     */
    private function getNextPayableFromUpe(int $userId, int $webinarId, Installment $installment, float $itemPrice): array
    {
        $result = [
            'isNewPurchase' => true,
            'nextPayableType' => 'upfront',
            'nextPayableStepId' => null,
            'nextPayableAmount' => (int) round($installment->getUpfront($itemPrice), 0, PHP_ROUND_HALF_UP),
            'totalPaidFromLedger' => 0,
            'allPaid' => false,
            'upeScheduleId' => null,
        ];

        try {
            // 1. Find UPE product for this webinar
            $upeProduct = UpeProduct::where('external_id', $webinarId)
                ->whereIn('product_type', ['course_video', 'webinar'])
                ->first();

            if (!$upeProduct) {
                return $result; // No UPE product yet → new purchase
            }

            // 2. Find UPE sale with installment pricing
            $upeSale = UpeSale::where('user_id', $userId)
                ->where('product_id', $upeProduct->id)
                ->where('pricing_mode', 'installment')
                ->whereIn('status', ['active', 'pending_payment', 'partially_refunded'])
                ->first();

            if (!$upeSale) {
                return $result; // No installment sale → new purchase
            }

            $result['isNewPurchase'] = false;

            // 3. Check for UPE installment plan with schedules (authoritative source)
            $upePlan = UpeInstallmentPlan::where('sale_id', $upeSale->id)
                ->whereIn('status', ['active', 'completed'])
                ->with('schedules')
                ->first();

            if ($upePlan && $upePlan->schedules->isNotEmpty()) {
                // UPE schedules are the authoritative source — handles restructured sub-installments
                $totalPaid = $upePlan->schedules->sum('amount_paid');
                $result['totalPaidFromLedger'] = (int) round($totalPaid, 0, PHP_ROUND_HALF_UP);

                // Find first unpaid schedule (skip waived/paid), ordered by sequence
                $nextSchedule = $upePlan->schedules
                    ->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])
                    ->sortBy('sequence')
                    ->first();

                if (!$nextSchedule) {
                    // All schedules paid/waived
                    $result['allPaid'] = true;
                    $result['nextPayableAmount'] = 0;
                    return $result;
                }

                $remaining = (float) $nextSchedule->amount_due - (float) $nextSchedule->amount_paid;
                $result['nextPayableType'] = ($nextSchedule->sequence <= 1) ? 'upfront' : 'step';
                $result['nextPayableStepId'] = null;
                $result['nextPayableAmount'] = max(1, (int) round($remaining, 0, PHP_ROUND_HALF_UP));
                $result['upeScheduleId'] = $nextSchedule->id;
                return $result;
            }

            // 4. Fallback: UPE sale exists but no plan/schedules — use ledger balance
            $ledgerService = app(PaymentLedgerService::class);
            $totalPaid = $ledgerService->balance($upeSale->id);
            $result['totalPaidFromLedger'] = (int) round($totalPaid, 0, PHP_ROUND_HALF_UP);

            $remaining = max(0, $itemPrice - $totalPaid);
            if ($remaining <= 0) {
                $result['allPaid'] = true;
                $result['nextPayableAmount'] = 0;
            } else {
                $result['nextPayableAmount'] = max(1, (int) round($remaining, 0, PHP_ROUND_HALF_UP));
            }
            return $result;

        } catch (\Exception $e) {
            Log::warning('getNextPayableFromUpe: UPE lookup failed, falling back to upfront amount', [
                'user_id' => $userId,
                'webinar_id' => $webinarId,
                'error' => $e->getMessage(),
            ]);
            return $result; // Fallback: treat as new purchase (upfront)
        }
    }

    public function store(Request $request, $installmentId)
    {
        try {
            session(['success'=>true]);

            $itemId = $request->get('item');
            $itemType = $request->get('item_type');
            $totalDiscount= $request->get('totalDiscount');
            $discountId= $request->get('discount_id');
            $installmentId= $request->get('installment_id');
            $name = $request->input('name');
            $email = $request->input('email');
            $contact = $request->input('number');

            $payment_type ="";

            if(!($request->input('razorpay_payment_id'))){
                    $toastData = [
                        'title' => trans('public.request_failed'),
                        'msg' => 'your transaction could not be completed.',
                        'status' => 'error'
                    ];
                    return back()->with(['toast' => $toastData]);
                }

            if($request->input('payment_type')){
            $payment_type = $request->input('payment_type');
            $amount = $request->input('amount');

            }
             $paymentChannel = PaymentChannel::where('status', 'active')
                ->first();

            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
            $user = User::where('email', $email)->orwhere('mobile', $contact)->first();
            }else{
                $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $contact ?? null,
                'email' => $email ?? null,
                'full_name' => $name,

                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time(),
                'enable_installments' =>1
                ]);
            }

            $item = $this->getItem($itemId, $itemType, $user);
            $itemPrice = round($item->getPrice());
            if($totalDiscount)
            $itemPrice -= $totalDiscount;

            if(isset($amount)){
            if($amount >= $itemPrice){

            $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => Order::$paying,
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
                }

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verify($request);
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order);
                   return redirect('/payment/success');

            }else{

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

                $data = $request->all();
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

                        return back()->with(['toast' => $toastData]);
                    }
                }

              $item = $this->getItem($itemId, $itemType, $user);

              $installmentPlans = new InstallmentPlans($user);
                    $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);

                    $itemPrice = round($item->getPrice());
                    $cash = $installments->sum('upfront');
                    $plansCount = $installments->count();
                    $minimumAmount = 0;
                      foreach ($installments as $installment) {
                        if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                            $minimumAmount = $installment->totalPayments($itemPrice);
                        }
                    }

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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
                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                        'item_price' => $itemPrice1,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);
                    }

                    $part_amount_status=true;
            if (!empty($payment_type)) {
                        $status = $payment_type;
                        date_default_timezone_set("Asia/Kolkata");
                        if($amount != 0){
                        WebinarPartPayment::Create([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                        'amount' => $amount,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);}

                    $part_amount=0;

                    $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$user->id)
                    ->where('webinar_id',$itemId)
                    ->get();

                    foreach ($WebinarPartPayment as $WebinarPartPayment1){
                        $part_amount = $part_amount + $WebinarPartPayment1->amount;
                    }

                    if($order->status == 'open'){

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

                    $paidAmount=round($totalSaleAmount+$part_amount);

                    foreach($installments as $installment){
                        if($paidAmount > 0){

                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'type' => 'upfront' ,
                            'installment_order_id' => $order->id
                            ])->first();

                            if($orderPayments1->status !='paid'){

                                if($paidAmount >= $order->item_price*$installment->upfront/100){

                                    InstallmentOrderPayment:: where([
                            'id' => $orderPayments1 ->id
                            ])->update(['status'=>'paid']);

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
                                'installment_order_id' => $order->id,
                                ])
                                ->first();

                                if($orderPayments1){

                                    if($orderPayments1->status !='paid'){

                                        if($paidAmount >= $order->item_price*$steps->amount/100){
                                            $orderPayments1 -> update(['status'=>'paid']);

                                            $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                                ->where('user_id', $user->id)
                                                ->first();

                                            if(!$accounting){

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
                }

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verify($request);
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order);

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

                                                    $installmentPayment = InstallmentOrderPayment :: where('_id', $orderPayments1->id)
                                                    ->update([
                                                    'sale_id' => $sale->id,
                                                ]);

                                            }

                                        }

                                    }
                                    $paidAmount -=$order->item_price*$steps->amount/100;

                                }else{

                                    $orderPayments1 = InstallmentOrderPayment:: create([
                                        'installment_order_id' => $orderPayments1->id,
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
                }

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verify($request);
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order);

                                    }

                        $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }

                    return redirect('/payment/success');
                    }

                    if($part_amount > $amount){
                        $part_amount_status=false;
                    }

                }
            if($order->status != 'open'){

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

                    if($part_amount_status){
                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);

                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => ($status=='part')?$status:Order::$paying,
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
                }

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verify($request);

                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order);

                $this->shortPaymentSection($request,$user->id,$itemId);

                  return redirect('/payment/success');

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }else{

                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();

                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $order = Order :: where('id',$sale->order_id)
                    ->first();

                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $OrderItem = OrderItem :: where('order_id',$order->id)
                    ->first();

                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $part_amount,
                    'installment_type' => 'part' ?? null,
                ]);

                    $accounting = Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                    'amount' => $part_amount,
                ]);

                $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();

                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}

                    return redirect('/payment/success');

                }
                }
                }
            }
            }

            }
            else{

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

                $data = $request->all();
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

                        return back()->with(['toast' => $toastData]);
                    }
                }

              $item = $this->getItem($itemId, $itemType, $user);

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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

                    $itemPrice = $item->getPrice();
                    $itemPrice1=$itemPrice-$totalDiscount;

                    if(!$order){
                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                        'item_price' => $itemPrice1,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);
                    }

                    $part_amount_status=true;

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

                    if($part_amount_status){
                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);

                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => ($status=='part')?$status:Order::$paying,
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
                }

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verify($request);
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order);

                  return redirect('/payment/success');

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }else{

                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();

                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $order = Order :: where('id',$sale->order_id)
                    ->first();

                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $OrderItem = OrderItem :: where('order_id',$order->id)
                    ->first();

                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $part_amount,
                    'installment_type' => 'part' ?? null,
                ]);

                    $accounting = Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                    'amount' => $part_amount,
                ]);

                $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();

                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}

                    return redirect('/payment/success');

                }

                }
            }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('store error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function directAccess111(){
        try {
            $data = [
            ["babitasingh2308@gmail.com", 7535066577, 2069,100,'2024-09-10 09:49:04'],
            ["duttaaparna124@gmail.com", 7980683638, 2069,100,'2024-09-10 09:49:04'],
            ];

            foreach ($data as $data1){

            $email = $data1[0];
            $contact = $data1[1];
            $course_id = $data1[2];
            $percent = $data1[3];
            $date = $data1[4];
            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
            $user = User::where('email', $email)->orwhere('mobile', $contact)->first();

            $WebinarAccessControl= WebinarAccessControl:: where([
                'user_id' => $user->id,
                'webinar_id' => $course_id,
                ])->first();
                if($WebinarAccessControl){
                    $WebinarAccessControl->update([
                'percentage' => $percent,
                'expire' => $date
                ]);

                }else{
             WebinarAccessControl::create([
                'user_id' => $user->id,
                'webinar_id' => $course_id,
                'percentage' => $percent,
                'expire' => $date
                ]);

                }

            }

            }
        } catch (\Exception $e) {
            \Log::error('directAccess111 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function directAccessForm(){
        try {
            if (Auth::check() && Auth::user()->role_id ==2) {

             $users = User::all();
                $courses = Webinar::where('status', 'active')
                            ->get();

                return view('web.default2' . '.cart.direct_access',compact('courses','users'));
            } else {
               return back();
            }
        } catch (\Exception $e) {
            \Log::error('directAccessForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function directAccess(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|max:255',
                'mobile' => 'required|string|max:15',
                'course' => 'required|string',
                'percentage' => 'required|numeric',
                'expire' => 'required|string',
            ]);

            if (!Auth::check() && Auth::user()->role_id !==2) {
                return back();
            }
            $data1 =$request->all();
            $email = $data1['email'];
            $contact = $data1['mobile'];
            $percentage = $data1['percentage'];
            $expire = $data1['expire'];

            $expirenew=$expire.' '.date('h:i:s');
            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
                $user = User::where('email', $email)->orwhere('mobile', $contact)->first();
            }else{
                $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $contact ?? null,
                'email' => $email ?? null,
                'full_name' => $name,

                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time(),
                'enable_installments' =>1
                ]);
            }
             $itemId = $data1['course'];

             $data = [
            [$email, $contact, $itemId,$percentage,$expirenew],
            ];

            foreach ($data as $data1){

            $email = $data1[0];
            $contact = $data1[1];
            $course_id = $data1[2];
            $percent = $data1[3];
            $date = $data1[4];
            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
            $user = User::where('email', $email)->orwhere('mobile', $contact)->first();

            $WebinarAccessControl= WebinarAccessControl:: where([
                'user_id' => $user->id,
                'webinar_id' => $course_id,
                ])->first();
                if($WebinarAccessControl){
                    $WebinarAccessControl->update([
                'percentage' => $percent,
                'expire' => $date
                ]);

                 return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                }else{
             WebinarAccessControl::create([
                'user_id' => $user->id,
                'webinar_id' => $course_id,
                'percentage' => $percent,
                'expire' => $date
                ]);

                return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                }

            }

            }

             return $response=['status' => 'faild', 'message' => 'Data not received successfully!'];
        } catch (\Exception $e) {
            \Log::error('directAccess error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function shortPaymentSection(Request $request,$userid,$item){
        try {
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

                    if(!$order){

                    }

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
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerify($order_main_table);

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
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerify($order_main_table);

                                    }

                        $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }
        } catch (\Exception $e) {
            \Log::error('shortPaymentSection error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function updatePaymentSection(Request $request){
        try {
            $data = [
            ["Karan Sheth", "karansheth86@gmail.com", 15516894440, 53100.05, 53101, 0],

            ];
            $webinarId=2069;
            $discountId =null;
            foreach ($data as $data1){

            if(!empty(User::where('email', $data1[1])->orwhere('mobile', $data1[2])->first())){
            $user = User::where('email', $data1[1])->orwhere('mobile', $data1[2])->first();
            }
            print_r($user->email);
            if(!$user){
                        break;
                    }
            $WebinarPartPayments = WebinarPartPayment :: select('user_id', 'webinar_id', 'installment_id', DB::raw('sum(amount) as total_amount'))
            ->where(['user_id'=> $user->id,'webinar_id'=> $webinarId])
            ->groupBy('user_id', 'webinar_id')
            ->get();

            if(!$WebinarPartPayments){
                        break;
                    }

            foreach ($WebinarPartPayments as $WebinarPartPayment){

            $order =InstallmentOrder::where([
                        'installment_id' => $WebinarPartPayment->installment_id,
                        'user_id' => $WebinarPartPayment->user_id,
                        'webinar_id' => $WebinarPartPayment->webinar_id,
                        'status' => 'open',
                    ])->first();

                    if(!$order){
                        break;
                    }

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

                    if($data1[4] == $paidAmount)
                  break;

                  if($data1[4] > $paidAmount){
                      $remainingPaidAmount = $data1[4] - $paidAmount;
                      $paidAmount = $data1[4];
                      date_default_timezone_set("Asia/Kolkata");
                      if($amount != 0){
                      WebinarPartPayment::Create([
                        'user_id' => $user->id,
                        'installment_id' => $installments[0]->id,
                        'webinar_id' => $webinarId,
                        'amount' => $remainingPaidAmount,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                      }
                  }

                    foreach($installments as $installment){
                        if($paidAmount > 0){

                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'type' => 'upfront' ,
                            'installment_order_id' => $order->id
                            ])->first();

                            if($orderPayments1->status !='paid'){

                                if($paidAmount >= $order->item_price*$installment->upfront/100){

                                    InstallmentOrderPayment:: where([
                            'id' => $orderPayments1 ->id
                            ])->update(['status'=>'paid']);

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
                                'installment_order_id' => $order->id,
                                ])
                                ->first();

                                if($orderPayments1){

                                    if($orderPayments1->status !='paid'){

                                        if($paidAmount >= $order->item_price*$steps->amount/100){
                                            $orderPayments1 -> update(['status'=>'paid']);

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
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerify($order_main_table);

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

                                                    $installmentPayment = InstallmentOrderPayment :: where('_id', $orderPayments1->id)
                                                    ->update([
                                                    'sale_id' => $sale->id,
                                                ]);

                                            }

                                        }

                                    }
                                    $paidAmount -=$order->item_price*$steps->amount/100;

                                }else{

                                    $orderPayments1 = InstallmentOrderPayment:: create([
                                        'installment_order_id' => $order->id,
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
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerify($order_main_table);

                                    }

                        $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }

            }}
        } catch (\Exception $e) {
            \Log::error('updatePaymentSection error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function shortPaymentSection1(Request $request){
        try {
            $array1=[];
            $amount=[];
            $WebinarPartPayments = WebinarPartPayment :: get();
                echo "<pre>";

            foreach ($WebinarPartPayments as $WebinarPartPayment){

            $WebinarPartPayments1 = WebinarPartPayment::where([
                        'user_id' => $WebinarPartPayment->user_id,
                        'webinar_id' => $WebinarPartPayment->webinar_id,
                    ])->get();

                    foreach ($WebinarPartPayments1 as $WebinarPartPayment1){
                        if(isset($array1[$WebinarPartPayment1->user_id])){
                        if($array1[$WebinarPartPayment1->user_id] == $WebinarPartPayment1->webinar_id){
                            break;
                        }}else{
                            $array1[$WebinarPartPayment->user_id] = $WebinarPartPayment->webinar_id;
                        }
                        $user= User::where('id', $WebinarPartPayment1->user_id)->first();

                    echo "<pre> count: ".count($WebinarPartPayments1);
                    echo " user id: ".User::where('id', $WebinarPartPayment1->user_id)->first()->email." ";
            print_r($WebinarPartPayment1->user_id);
            echo " Web id: ";
            print_r($WebinarPartPayment1->webinar_id);
            echo " amount: ";

            $InstallmentOrder =InstallmentOrder::where([
                        'installment_id' => $WebinarPartPayment->installment_id,
                        'user_id' => $WebinarPartPayment->user_id,
                        'webinar_id' => $WebinarPartPayment->webinar_id,
                        'status' => 'open',
                    ])->first();

                    if($InstallmentOrder){
                    $installmentPayment = InstallmentOrderPayment :: where('installment_order_id', $InstallmentOrder->id)
                ->first();

                $accounting = Accounting::where('installment_payment_id', $installmentPayment->id)
                    ->first();
                if($accounting){
                Accounting::where('id', $accounting->id)
                    ->update([
                    'amount' => $WebinarPartPayment->amount,
                ]);
                }

                $sale =  Sale :: where('installment_payment_id',$installmentPayment->id)
                ->first();
                 if($sale){
                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $WebinarPartPayment->amount,
                    'status' => 'part',
                ]);
                 }
                 if($accounting){
                $OrderItem = OrderItem :: where('id',$accounting->order_item_id)
                    ->first();
                     if($OrderItem){
                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $WebinarPartPayment->amount,
                    'installment_type' => 'part',
                ]);
                     }}
                     if($sale){
                $order = Order :: where('id',$sale->order_id)
                    ->first();
                     if($order){
                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $WebinarPartPayment->amount,
                ]);

                     }}

                    }

             session(['success'=>true]);

            $itemId = $WebinarPartPayment->webinar_id;
            $itemType = 'course';
            $totalDiscount= $WebinarPartPayment->webinar_id;
            $discountId= $request->get('discount_id');
            $installmentId= $WebinarPartPayment->installment_id;

            $payment_type ="part";

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

                $data = $request->all();
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

                        return back()->with(['toast' => $toastData]);
                    }
                }

              $item = $this->getItem($itemId, $itemType, $user);

              $installmentPlans = new InstallmentPlans($user);
                    $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);

                    $itemPrice = round($item->getPrice());
                    $cash = $installments->sum('upfront');
                    $plansCount = $installments->count();
                    $minimumAmount = 0;
                      foreach ($installments as $installment) {
                        if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                            $minimumAmount = $installment->totalPayments($itemPrice);
                        }
                    }

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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

                    $itemPrice = $item->getPrice();
                    $itemPrice1=$itemPrice-$totalDiscount;

                    if(!$order){
                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                        'item_price' => $itemPrice1,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);
                    }

                    $part_amount_status=true;
                        if (!empty($payment_type)) {
                        $status = $payment_type;

                    $part_amount=0;
                    $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$user->id)
                    ->where('webinar_id',$itemId)
                    ->get();

                    foreach ($WebinarPartPayment as $WebinarPartPayment1){
                        $part_amount = $part_amount + $WebinarPartPayment1->amount;
                    }

                    if($order->status == 'open'){

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

                    $paidAmount=round($totalSaleAmount+$part_amount);
                    print_r($paidAmount);
            echo "<br>";

                    foreach($installments as $installment){
                        if($paidAmount > 0){

                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'type' => 'upfront' ,
                            'installment_order_id' => $order->id
                            ])->first();

                            if($orderPayments1->status !='paid'){

                                if($paidAmount >= $order->item_price*$installment->upfront/100){

                                    InstallmentOrderPayment:: where([
                            'id' => $orderPayments1 ->id
                            ])->update(['status'=>'paid']);

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
                                'installment_order_id' => $order->id,
                                ])
                                ->first();

                                if($orderPayments1){

                                    if($orderPayments1->status !='paid'){

                                        if($paidAmount >= $order->item_price*$steps->amount/100){
                                            $orderPayments1 -> update(['status'=>'paid']);

                                            $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                                ->where('user_id', $user->id)
                                                ->first();

                                            if(!$accounting){

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
                                                    }
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerify($order_main_table);

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

                                                    $installmentPayment = InstallmentOrderPayment :: where('_id', $orderPayments1->id)
                                                    ->update([
                                                    'sale_id' => $sale->id,
                                                ]);

                                            }

                                        }

                                    }
                                    $paidAmount -=$order->item_price*$steps->amount/100;

                                }else{

                                    $orderPayments1 = InstallmentOrderPayment:: create([
                                        'installment_order_id' => $order->id,
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
                                                    }
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerify($order_main_table);

                                    }

                        $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }

                    }

                }
                }
            }

            }  }die('done');
        } catch (\Exception $e) {
            \Log::error('shortPaymentSection1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function checkCourseAccess1()
    {
        try {
            $course_id = 2098;
            $installmentId= 23;

            $data = [
                    ["Zeal Sheth", "zealsheth13@gmail.com", "6692685708", 56000, 56000, 0],
            ["Karan Sheth", "karansheth86@gmail.com", "15516894440", 56000, 56000, 0],
            ["Pallavi Save", "pallavi.we@gmail.com", "9869448380", 64900, 64900, 0],
            ["Monica Jain", "monica.jain0129@gmail.com", "919753103500", 61655, 61655, 0],
            ["Shravan Bharathulwar", "shravanb@artofliving.org", "17324764073", 61655, 61655, 0],
            ["Nikunj Gohel", "nikunj.gohel1988@gmail.com", "919987974880", 61655, 61655, 0],
            ["Amitesh", "amitesh.sjc@gmail.com", "917738010560", 61655, 61655, 0],
            ["Aparna Singh", "neha23.email@gmail.com", "9807079111", 61655, 61655, 0],
            ["Indira Trivedi", "Indiratrivedi@yahoo.com", "9886287085", 59000, 59000, 0],
            ["Priya Maheshwari", "Priyamaheshwari1971@gmail.com", "9820264445", 64900, 64900, 0],
            ["Jagruti Shah", "jagu.10j@gmail.com", "9619143388", 61655, 61655, 0],
            ["Shweta Singla", "Shweta.singla75@gmail.com", "9632244127", 59000, 59000, 0],
            ["Manju Singh", "manjusingh0705@gmail.com", "9179828733", 64400, 64400, 0],
            ["Arvind Tiwari", "tiwsk.maths@gmail.com", "7898980808", 64400, 64400, 0],
            ["Aparna Dutta", "duttaaparna124@gmail.com", "917980683638", 22500, 22500, 0],
            ["Vedant Joshi", "jvedant@gmail.com", "919715083221", 61655, 61655, 0],
            ["Keshav Sharma", "paybydaddy@gmail.com", "919958068116", 61655, 61655, 0],

            ["Surendra Gaurav", "surendragurav4u@yahoo.com", "918007917702", 61655, 20000, 41655],
            ["Akshunya Anurupa Bhargav", "anurupa.bhargav@gmail.com", "8826391432", 64900, 16816, 48084],
            ["Dipti Kariwala", "diptikariwala@gmail.com", "9748741783", 64900, 12500, 52400],
            ["Anupama Sharma", "anupama5879@gmail.com", "919891000160", 64900, 20000, 44900],
            ["Manju Pal", "manjupal.180884@gmail.com", "8130579605", 64900, 5000, 59900],
            ["Babita Singh", "babitasingh2308@gmail.com", "7535066577", 64900, 10000, 54900],
            ["Chander Mohan", "chandermohansharma09@gmail.com", "9805532664", 64900, 10000, 54900],
            ["Siddharth Dixit", "siddharthdixit1@gmail.com", "9148792590", 58900, 5005, 53895],
            ["Neha Rani", "neharani286@gmail.com", "919878193303", 60000, 5000, 55000],
            ["Priya Bhusari", "theriabhusari2021@gmail.com", "9501111734", 64900, 32066, 32834],
            ["Archana Kumari", "archanaks17@gmail.com", "9731568163", 64900, 10000, 54900],
            ["Punyaja Swaroop", "punyajaswaroop@gmail.com", "7355523755", 64900, 5000, 59900],
            ["Chaitanya Mistique", "chaitanyamistique@gmail.com", "8446506410", 64900, 20000, 44900],
            ["Taruna Bahl", "tarunabhal84@gmail.com", "8860971154", 59000, 7500, 51500],
            ["Anirudha Talnikar", "aniruddhhnandaa@gmail.com", "9822221161", 62900, 5000, 57900],
            ["Rathipriya Chandrasekaran", "rathipriya.chandrasekaran@gmail.com", "9500034994", 62900, 20000, 42900],
            ["Amita Bhatia", "amita.bhatia10@gmail.com", "9717328566", 22500, 11500, 11000],
            ["Yashasvi Chouhan", "yashasvichouhan2002@gmail.com", "8319018454", 22500, 7500, 15000],
            ["Rajneesh Chaturvedi", "chaturrajneesh@gmail.com", "9918201760", 60000, 15000, 45000],
            ["Vibhu Aggarwal", "vibhuaggarwal26@gmail.com", "919871139193", 64900, 22066, 42834],
            ["Sapna Malhotra", "sapna.s.malhotra@gmail.com", "9810395218", 64900, 35060, 29840],
            ["Aparna Agrawal", "aparna20aparna@gmail.com", "918888792426", 59000, 10000, 49000],
            ["Lakshmi Pishe", "lakshmipishe@gmail.com", "919845001404", 64900, 20000, 44900],
            ["Ankita", "ankita.digitals@gmail.com", "8920019276", 64900, 30000, 34900],
            ["Nivedita Sharma", "niveadevsharma@gmail.com", "917051401300", 61655, 20000, 41655]
            ];

            foreach ($data as $data1){

             session(['success'=>true]);

            $itemId = $course_id;
            $itemType = 'course';
            $totalDiscount= 64900 - $data1[3];
            $discountId= 0;
            $name = $data1[0];
            $email = $data1[1];
            $contact = $data1[2];

            $payment_type ="part";

            $amount = $data1[4];

            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
            $user = User::where('email', $email)->orwhere('mobile', $contact)->first();

            $WebinarPartPayment = WebinarPartPayment::where([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                    ])->first();

            $order = Sale:: where([
                        'buyer_id' => $user->id,
                        'webinar_id' => $itemId,
                    ])->first();

            if($order){
                echo $user->email . '<br>';
            }
            }else{

            }

            }
        } catch (\Exception $e) {
            \Log::error('checkCourseAccess1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function fullAccess2()
    {
        try {
            $data = [

            ["Dipti Kariwala", "diptikariwala@gmail.com", "9748741783", 64900, 12500, 52400],

            ["Manju Pal", "manjupal.180884@gmail.com", "8130579605", 64900, 5000, 59900],

            ["Neha Rani", "neharani286@gmail.com", "919878193303", 60000, 5000, 55000],
            ["Priya Bhusari", "theriabhusari2021@gmail.com", "9501111734", 64900, 32066, 32834],
            ["Archana Kumari", "archanaks17@gmail.com", "9731568163", 64900, 10000, 54900],
            ["Punyaja Swaroop", "punyajaswaroop@gmail.com", "7355523755", 64900, 5000, 59900],

            ["Anirudha Talnikar", "aniruddhhnandaa@gmail.com", "9822221161", 62900, 5000, 57900],

            ["Amita Bhatia", "amita.bhatia10@gmail.com", "9717328566", 22500, 11500, 11000],

            ["Vibhu Aggarwal", "vibhuaggarwal26@gmail.com", "919871139193", 64900, 22066, 42834],
            ["Sapna Malhotra", "sapna.s.malhotra@gmail.com", "9810395218", 64900, 35060, 29840],

            ["Lakshmi Pishe", "lakshmipishe@gmail.com", "919845001404", 64900, 20000, 44900],

            ["Mahima Gotiya", "mahima.chandsoriya@gmail.com", "7354267930", 64900, 20000, 44900],
            ];

            foreach ($data as $data1){

             session(['success'=>true]);

            $itemId = 2098;
            $itemType = 'course';
            $totalDiscount= 64900 - $data1[3];
            $discountId= 0;
            $installmentId= 23;
            $name = $data1[0];
            $email = $data1[1];
            $contact = $data1[2];

            $payment_type ="part";

            $amount = $data1[4];

            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
            $user = User::where('email', $email)->orwhere('mobile', $contact)->first();
            }else{
                $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $contact ?? null,
                'email' => $email ?? null,
                'full_name' => $name,

                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time(),
                'enable_installments' =>1
                ]);
            }
            $WebinarPartPayment = WebinarPartPayment::where([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                    ])->first();

                if(!($WebinarPartPayment)){
            if($data1[5]==0){

            $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => Order::$paying,
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
                    }
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order_main_table);
            }

            if($data1[5]>0){

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

                        return back()->with(['toast' => $toastData]);
                    }
                }

              $item = $this->getItem($itemId, $itemType, $user);

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

                    $status = 'paying';

                    if (empty($installment->upfront)) {
                        $status = 'open';

                        if ($installment->needToVerify()) {
                            $status = 'pending_verification';
                        }
                    }

                    $itemPrice = $item->getPrice();
                    $itemPrice1=$itemPrice-$totalDiscount;

                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                        'item_price' => $itemPrice1,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);
                    $part_amount_status=true;
                        if (!empty($payment_type)) {
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

                    if($part_amount > $amount){
                        $part_amount_status=false;
                    }

                    }

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

                    if($part_amount_status){

                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);

                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => ($status=='part')?$status:Order::$paying,
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
                    }
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order_main_table);

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();

                    $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();

                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}

                }else{

                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();

                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $order = Order :: where('id',$sale->order_id)
                    ->first();

                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $OrderItem = OrderItem :: where('order_id',$order->id)
                    ->first();

                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $accounting = Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                    'amount' => $part_amount,
                ]);

                $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();

                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}

                }
                }
            }

            }
                }
            }
        } catch (\Exception $e) {
            \Log::error('fullAccess2 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function fullAccessForm(){
        try {
            if (Auth::check()) {
                $courses = Webinar::where('status', 'active')
                            ->get();

                return view('web.default2' . '.cart.full_access',compact('courses'));
            } else {
              return back();
            }
        } catch (\Exception $e) {
            \Log::error('fullAccessForm error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function fullAccess(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'mobile' => 'required|string|max:15',
                'course' => 'required|string',
                'amount' => 'required|numeric',
                'paid_amount' => 'required|numeric',
                'pending_amount' => 'required|numeric',
            ]);

            if (!Auth::check() && Auth::user()->role_id !==2) {
                return back();
            }
            $data1 =$request->all();
            $name = $data1['name'];
            $email = $data1['email'];
            $contact = $data1['mobile'];

            $launch_date =null;

            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
                $user = User::where('email', $email)->orwhere('mobile', $contact)->first();
            }else{
                $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $contact ?? null,
                'email' => $email ?? null,
                'full_name' => $name,

                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time(),
                'enable_installments' =>1
                ]);
            }

            $userId = $user->id;
            $webinarId = $validatedData['course'];
            $currentAmount = $validatedData['amount'];

            $existingPayments = InstallmentOrder::where('user_id', $userId)
                ->where('webinar_id', $webinarId)
                ->first();

            if ($existingPayments && !empty($existingPayments->item_price)) {
                $existingPayments_price = $existingPayments->item_price;

                if ($existingPayments_price != $currentAmount) {
                     $itemId_ins = $data1['course'];
             $itemType_ins  = 'course';
             $courses_ins  = Webinar::where('id',$itemId_ins )->where('status', 'active')
                    ->first();
            $item_ins  = $this->getItem($itemId_ins, $itemType_ins , $user);
            $itemPrice_ins = $item_ins->getPrice();
            $totalDiscount_ins= $itemPrice_ins - $data1['amount'];
                    
                    
                    InstallmentOrder::where('user_id', $userId)
    ->where('webinar_id', $webinarId)
    ->update([
        'item_price' => $data1['amount'] ,
        'discount' =>$totalDiscount_ins // jo field update karni ho
    ]);
    
     $orderPayments = InstallmentOrderPayment:: where(
                        'installment_order_id', $existingPayments->id)
                        ->get();
                        
 
     $saleId = $orderPayments->sale_id;
     
              Sale::where('id', $saleId)
    ->update([
       
        'status' =>'part' // jo field update karni ho
    ]);
    

                  
                       
                  
                    
                    // DB::transaction(function () use ($userId, $webinarId) {

                    //     $sale = Sale::where('buyer_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->first();

                    //     if ($sale !== null) {
                    //         InstallmentOrderPayment::where('sale_id', $sale->id)->delete();
                    //     }

                    //     InstallmentOrder::where('user_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->delete();

                    //     OrderItem::where('user_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->delete();

                    //     $orderIds = OrderItem::where('user_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->pluck('order_id')
                    //         ->toArray();

                    //     if (!empty($orderIds)) {
                    //         Order::whereIn('id', $orderIds)
                    //             ->where('user_id', $userId)
                    //             ->delete();
                    //     }

                    //     Accounting::where('user_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->delete();

                    //     Sale::where('buyer_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->delete();

                    //     WebinarPartPayment::where('user_id', $userId)
                    //         ->where('webinar_id', $webinarId)
                    //         ->delete();
                    // });
                }
            }
             $itemId = $data1['course'];
             $itemType = 'course';
             $courses = Webinar::where('id',$itemId)->where('status', 'active')
                    ->first();
            $item = $this->getItem($itemId, $itemType, $user);
            $itemPrice = $item->getPrice();
            $totalDiscount= $itemPrice - $data1['amount'];
            $discountId= 0;

            $installmentPlans = new InstallmentPlans();
            $installments = $installmentPlans->getPlans('courses', $courses->id, $courses->type, $courses->category_id, $courses->teacher_id);

             if($installments->isEmpty()) {

                $installmentId = 24;
            } else {
                $installmentId = $installments[0]->id;
            }
            $payment_type ="part";

                if(isset($item->start_date) and $item->isCourse())
            $launch_date = $item->start_date;

               $part_amount=0;

                    $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$user->id)
                    ->where('webinar_id',$itemId)
                    ->get();
                    if($WebinarPartPayment){
                    foreach ($WebinarPartPayment as $WebinarPartPayment1){
                        $part_amount = $part_amount + $WebinarPartPayment1->amount;
                    }
                    }

                        $order = InstallmentOrder:: where([
                        'installment_id' => $installmentId,
                        'user_id' => $user->id,
                        'webinar_id' => $itemId,
                        'status' => 'open',
                    ])->first();
                    $totalSaleAmount=0;
                        if($order){
                        $orderPayments = InstallmentOrderPayment:: where(
                        'installment_order_id', $order->id)
                        ->get();

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
                        }

                    $paidAmount=$totalSaleAmount+$part_amount;

            $amount = $data1['paid_amount']-$paidAmount;

                   if(!($order)){
                       if($data1['pending_amount']==0){

            $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => Order::$paying,
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
                    }
                    $sales_account=new PaymentController_old();
                    $sales_account->paymentOrderAfterVerify($order_main_table);
                    return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                }}

            $WebinarPartPayment = WebinarPartPayment::where([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                        ])->first();

            if($data1['paid_amount']>0){

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

                $data = $request->all();
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

                        return back()->with(['toast' => $toastData]);
                    }
                }

              $item = $this->getItem($itemId, $itemType, $user);

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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

                    $itemPrice = $item->getPrice();
                    $itemPrice1=$itemPrice-$totalDiscount;

                    if(!$order){
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

                    if($order->status == 'open'){

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

                    $paidAmount=$totalSaleAmount+$part_amount;

                    foreach($installments as $installment){
                        if($paidAmount > 0){

                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'type' => 'upfront' ,
                            'installment_order_id' => $order->id
                            ])->first();

                            if($orderPayments1->status !='paid'){

                                if($paidAmount >= $order->item_price*$installment->upfront/100){

                                    InstallmentOrderPayment:: where([
                            'id' => $orderPayments1 ->id
                            ])->update(['status'=>'paid']);

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
                                'installment_order_id' => $order->id,
                                ])
                                ->first();

                                if($orderPayments1){

                                    if($orderPayments1->status !='paid'){

                                        if($paidAmount >= $order->item_price*$steps->amount/100){
                                            $orderPayments1 -> update(['status'=>'paid']);

                                            $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                                ->where('user_id', $user->id)
                                                ->first();

                                            if(!$accounting){

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
                                                    }
                                                    $sales_account=new PaymentController_old();
                                                  $sales_account->paymentOrderAfterVerify($order_main_table);

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
                                        'installment_order_id' => $order->id,
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
                                                    }
                                                    $sales_account=new PaymentController_old();
                                                  $sales_account->paymentOrderAfterVerify($order_main_table);

                                    }

                        $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }

                    return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                    }

                    if($part_amount > $amount){
                        $part_amount_status=false;
                    }

                }
            if($order->status != 'open'){

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

                    if($part_amount_status){
                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);

                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => $status=='part' ? $status : Order::$paying,
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
                }

                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order_main_table);

                $this->shortPaymentSection($request,$user->id,$itemId);

            return $response=['status' => 'success', 'message' => 'Data received successfully!'];

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }else{

                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();
               if($sale){
                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $order = Order :: where('id',$sale->order_id)
                    ->first();
                    if($order){
                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $OrderItem = OrderItem :: where('order_id',$order->id)
                    ->first();

                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $part_amount,
                    'installment_type' => 'part' ?? null,
                ]);

                    $accounting = Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                    'amount' => $part_amount,
                ]);}

                $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();
                if($installmentPayment){
                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}}
               }

                    return $response=['status' => 'success', 'message' => 'Data received successfully!'];

                }
                }
                }
            }

                return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                }

                return $response=['status' => 'warning', 'message' => 'The selected course has already been purchased by this user.'];
        } catch (\Exception $e) {
            \Log::error('fullAccess error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function astrpshiromani(Request $request)
    {
        try {
            $data =[

                ];
                $paymentChannel = PaymentChannel::where('status', 'active')
                ->first();

            foreach($data as $data1){

            $name = $data1[0];
            $email = $data1[1];
            $contact = $data1[2];
            $purchesh_amount_of_course = $data1[3];
            $paid_amount_by_user = $data1[4];
            $remaining_amount_of_course = $data1[5];

            print_r($email);
            echo"<br>";

            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){
                $user = User::where('email', $email)->orwhere('mobile', $contact)->first();
            }else{
                $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $contact ?? null,
                'email' => $email ?? null,
                'full_name' => $name,

                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time(),
                'enable_installments' =>1
                ]);
            }
             $itemId = 2070;
             $itemType = 'course';
             $courses = Webinar::where('id',$itemId)->where('status', 'active')
                    ->first();
            $item = $this->getItem($itemId, $itemType, $user);
            $itemPrice = $item->getPrice();
            $totalDiscount= $itemPrice - $purchesh_amount_of_course;
            $discountId= 0;

            $installmentPlans = new InstallmentPlans();
            $installments = $installmentPlans->getPlans('courses', $courses->id, $courses->type, $courses->category_id, $courses->teacher_id);
            $installmentId = $installments[0]->id;
            $payment_type ="part";

               $part_amount=0;

                    $WebinarPartPayment =  WebinarPartPayment :: where('user_id',$user->id)
                    ->where('webinar_id',$itemId)
                    ->get();
                    if($WebinarPartPayment){
                    foreach ($WebinarPartPayment as $WebinarPartPayment1){
                        $part_amount = $part_amount + $WebinarPartPayment1->amount;
                    }
                    }

                        $order = InstallmentOrder:: where([
                        'installment_id' => $installmentId,
                        'user_id' => $user->id,
                        'webinar_id' => $itemId,
                        'status' => 'open',
                    ])->first();
                    $totalSaleAmount=0;
                        if($order){
                        $orderPayments = InstallmentOrderPayment:: where(
                        'installment_order_id', $order->id)
                        ->get();

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
                        }

                    $paidAmount=$totalSaleAmount+$part_amount;

            $amount = $paid_amount_by_user-$paidAmount;

                   if(!($order)){
                       if($remaining_amount_of_course==0){

            $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => Order::$paying,
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
                    }
                    $sales_account=new PaymentController_old();
                    $sales_account->paymentOrderAfterVerify($order_main_table);
                    continue;
                    return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                }}

            $WebinarPartPayment = WebinarPartPayment::where([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                        ])->first();

            if($paid_amount_by_user>0){

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
            continue;
                        return back()->with(['toast' => $toastData]);
                    }
                }

              $item = $this->getItem($itemId, $itemType, $user);

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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

                    $itemPrice = $item->getPrice();
                    $itemPrice1=$itemPrice-$totalDiscount;

                    if(!$order){
                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                        'item_price' => $itemPrice1,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);
                    }

                    $part_amount_status=true;
            if (!empty($payment_type)) {
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

                    if($order->status == 'open'){

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

                    $paidAmount=$totalSaleAmount+$part_amount;

                    foreach($installments as $installment){
                        if($paidAmount > 0){

                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'type' => 'upfront' ,
                            'installment_order_id' => $order->id
                            ])->first();

                            if($orderPayments1->status !='paid'){

                                if($paidAmount >= $order->item_price*$installment->upfront/100){

                                    InstallmentOrderPayment:: where([
                            'id' => $orderPayments1 ->id
                            ])->update(['status'=>'paid']);

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
                                'installment_order_id' => $order->id,
                                ])
                                ->first();

                                if($orderPayments1){

                                    if($orderPayments1->status !='paid'){

                                        if($paidAmount >= $order->item_price*$steps->amount/100){
                                            $orderPayments1 -> update(['status'=>'paid']);

                                            $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                                ->where('user_id', $user->id)
                                                ->first();

                                            if(!$accounting){

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
                                                    }
                                                    $sales_account=new PaymentController_old();
                                                  $sales_account->paymentOrderAfterVerify($order_main_table);

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

                                                    $installmentPayment = InstallmentOrderPayment :: where('_id', $orderPayments1->id)
                                                    ->update([
                                                    'sale_id' => $sale->id,
                                                ]);

                                            }

                                        }

                                    }
                                    $paidAmount -=$order->item_price*$steps->amount/100;

                                }else{

                                    $orderPayments1 = InstallmentOrderPayment:: create([
                                        'installment_order_id' => $order->id,
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
                                                    }
                                                    $sales_account=new PaymentController_old();
                                                  $sales_account->paymentOrderAfterVerify($order_main_table);

                                    }

                        $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }

                    continue;
                    }

                    if($part_amount > $amount){
                        $part_amount_status=false;
                    }

                }
            if($order->status != 'open'){

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

                    if($part_amount_status){
                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);

                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => ($status=='part')?$status:Order::$paying,
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
                }

                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerify($order_main_table);

                $this->shortPaymentSection($request,$user->id,$itemId);

            continue;

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }else{

                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();
                if($sale){
                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $order = Order :: where('id',$sale->order_id)
                    ->first();

                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $OrderItem = OrderItem :: where('order_id',$order->id)
                    ->first();

                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $part_amount,
                    'installment_type' => 'part' ?? null,
                ]);

                    $accounting = Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                    'amount' => $part_amount,
                ]);

                $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();

                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}
                }

                        continue;
                }
                }
                }
            }

                 continue;

                }

                continue;

            }
            echo "done";
        } catch (\Exception $e) {
            \Log::error('astrpshiromani error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function store1(Request $request, $installmentId)
    {
        try {
            $user = auth()->user();
            $itemId = $request->get('item');
            $itemType = $request->get('item_type');
            $totalDiscount= $request->get('totalDiscount');
            $discountId= $request->get('discount_id');

            if (empty($user) or !$user->enable_installments) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.you_cannot_use_installment_plans'),
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

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

                $this->validate($request, [
                    'item' => 'required',
                    'item_type' => 'required',
                ]);

                $data = $request->all();
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

                        return back()->with(['toast' => $toastData]);
                    }
                }

                $item = $this->getItem($itemId, $itemType, $user);

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

                    $status = 'paying';

                    if (empty($installment->upfront)) {
                        $status = 'open';

                        if ($installment->needToVerify()) {
                            $status = 'pending_verification';
                        }
                    }

                    $itemPrice = $item->getPrice();
            $itemPrice1=$itemPrice-$totalDiscount;
                    $order = InstallmentOrder::query()->updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => !empty($productOrder) ? $productOrder->id : null,
                        'item_price' => $itemPrice1,
                        'status' => $status,
                    ], [
                        'created_at' => time(),
                    ]);

                    $this->handleAttachments($attachments, $order);

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

                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment::query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => 'paying',
                        ], [
                            'created_at' => time(),
                        ]);

                       $cart = Cart::updateOrCreate([
                            'creator_id' => $user->id,
                            'installment_payment_id' => $installmentPayment->id,
                        ], [
                            'created_at' => time()
                        ]);
                        $installment_price=($itemPrice*$installment->upfront)/100;
                        $installment_price1=$installment_price-$installment->getUpfront($order->getItemPrice());

                        if($discountId){
                        CartInstallment::updateOrCreate([
                            'cart_id' => $cart->id,
                            'user_id' => $user->id,
                            'discount_price' =>  $installment_price1,
                            'installment_price' => $installment_price,
                            'installment_payment_id' => $installmentPayment->id,
                            'discount_id' =>  $discountId ,
                            'total' => $installment->getUpfront($order->getItemPrice())
                        ], [
                            'created_at' => time()
                        ]);
                        }

                        return redirect('/cart');
                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }
            }

            abort(404);
        } catch (\Exception $e) {
            \Log::error('store1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleOpenOrder($item, $productOrder)
    {
        if (!empty($productOrder)) {
            $productOrderStatus = ProductOrder::$waitingDelivery;

            if ($item->isVirtual()) {
                $productOrderStatus = ProductOrder::$success;
            }

            $productOrder->update([
                'status' => $productOrderStatus
            ]);
        }

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.your_installment_purchase_has_been_successfully_completed'),
            'status' => 'success'
        ];

        return redirect('/panel/financial/installments')->with(['toast' => $toastData]);
    }

    private function handleProductOrder(Request $request, $user, $product)
    {
        $data = $request->all();

        $specifications = $data['specifications'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        $order = ProductOrder::query()->create([
            'product_id' => $product->id,
            'seller_id' => $product->creator_id,
            'buyer_id' => $user->id,
            'sale_id' => null,
            'installment_order_id' => null,
            'status' => 'pending',
            'specifications' => $specifications ? json_encode($specifications) : null,
            'quantity' => $quantity,
            'discount_id' => null,
            'message_to_seller' => $data['message_to_seller'],
            'created_at' => time()
        ]);

        if ($product->type == Product::$physical) {
            $user->update([
                'country_id' => $data['country_id'] ?? $user->country_id,
                'province_id' => $data['province_id'] ?? $user->province_id,
                'city_id' => $data['city_id'] ?? $user->city_id,
                'district_id' => $data['district_id'] ?? $user->district_id,
                'address' => $data['address'] ?? $user->address,
            ]);
        }

        return $order;
    }

    private function handleAttachments($attachments, $order)
    {
        InstallmentOrderAttachment::query()->where('installment_order_id', $order->id)->delete();

        if (!empty($attachments)) {
            $attachmentsInsert = [];

            foreach ($attachments as $attachment) {
                if (!empty($attachment['title']) and !empty($attachment['file'])) {
                    $attachmentsInsert[] = [
                        'installment_order_id' => $order->id,
                        'title' => $attachment['title'],
                        'file' => $attachment['file'],
                    ];
                }
            }

            if (!empty($attachmentsInsert)) {
                InstallmentOrderAttachment::query()->insert($attachmentsInsert);
            }
        }
    }

    public function requestSubmitted()
    {
        try {
            $data = [
                'pageTitle' => trans('update.installment_request_submitted'),
            ];

              $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.installment.request_submitted', $data);
                }else{
                    return view('web.default2' . '.installment.request_submitted', $data);
                }
        } catch (\Exception $e) {
            \Log::error('requestSubmitted error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function requestRejected()
    {
        try {
            $data = [
                'pageTitle' => trans('update.installment_request_rejected'),
            ];

            $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.installment.request_rejected', $data);
                }else{
                    return view('web.default2' . '.installment.request_rejected', $data);
                }
        } catch (\Exception $e) {
            \Log::error('requestRejected error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function cronJob(Request $request)
    {

        $data = $request->all();

        try {
          if(!empty($data['razorpay_payment_id'])){
            InstallmentProcessJob::dispatch($data) ->delay(now());
            return redirect('/payment/success');
          }else{
             $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.gateway_error'),
                    'status' => 'error'
                ];
                return redirect('cart')->with($toastData);
          }

        } catch (Exception $e) {

            throw $e->getMessage();
        }

    }

     public function installmentBackgroundProcess($data)
    {
        try {
            Log::info('i am in installmentBackgroundProcess function');

            session(['success'=>true]);
            $itemId = $data['item'];
            $itemType = $data['item_type'];
            $totalDiscount= 0;
            $discountId= $data['discountId'];
            $installmentId= $data['installment_id'];
            $name = $data['name'];
            $email = $data['email'];
            $contact = $data['number'];

            $payment_type ="";
            $launch_date =null;

            Log::info('$discountId = '.$discountId);

            if(!empty($data['razorpay_payment_id'])){

                $api = new Api(env('RAZORPAY_API_KEY'), env('RAZORPAY_API_SECRET'));
                $payment = $api->payment->fetch($data['razorpay_payment_id']);

                TransactionsHistoryRazorpay::create([
                    'user_id' => auth()->id() ?? null,
                    'name' => $data['name'] ?? 'Guest',
                    'number' => $payment->contact ?? null,
                    'email' => $payment->email ?? null,
                    'amount' => $payment->amount / 100 ?? null,
                    'razorpay_payment_id' => $payment->id ?? null,
                    'razorpay_description' => $payment->description ?? 'Razorpay Payment',
                ]);
               }

            if(!empty($data['payment_type'])){

                $payment_type = $data['payment_type'];
                $amount =0;
                if(!empty($data['amount'])){
                    $amount = $data['amount'];
                }
                Log::info('paid by part payment amount '.$amount);
            }

             if(!empty($data['totalDiscount'])){
                 $totalDiscount= $data['totalDiscount'];

            Log::info('$totalDiscount = '.$totalDiscount);
             }

             $paymentChannel = PaymentChannel::where('status', 'active')
                ->first();

            if(!empty(User::where('email', $email)->orwhere('mobile', $contact)->first())){

                $user = User::where('email', $email)->orwhere('mobile', $contact)->first();
            }else{

                $user = User::create([
                'role_name' => 'user',
                'role_id' => 1,
                'mobile' => $contact ?? null,
                'email' => $email ?? null,
                'full_name' => $name,
                'status'=>'active',
                'access_content' => 1,
                'password' => Hash::make(Str::random(16)),
                'affiliate' => 0,
                'timezone' => 'Asia/Kolkata' ?? null,
                'created_at' => time(),
                'enable_installments' =>1
                ]);
            }
            Log::info('paid by user id '.$user->id);

            $item = $this->getItem($itemId, $itemType, $user);
            $itemPrice = round($item->getPrice());

            if($totalDiscount)
            $itemPrice -= $totalDiscount;

            if(isset($item->start_date) and $item->isCourse())
            $launch_date = $item->start_date;

            if(isset($amount)){

               Log::info('user paying by part payment link because $amount is set');
            if($amount >= $itemPrice){

                Log::info('$amount >= $itemPrice means paid full payment');

            $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => Order::$paying,
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

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verifyBackgroundProccess($data);
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);
                   return  true;

            }else{

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

              $item = $this->getItem($itemId, $itemType, $user);

              $installmentPlans = new InstallmentPlans($user);
                    $installments = $installmentPlans->getPlans('courses', $item->id, $item->type, $item->category_id, $item->teacher_id);

                    $itemPrice = round($item->getPrice());
                    $cash = $installments->sum('upfront');
                    $plansCount = $installments->count();
                    $minimumAmount = 0;
                      foreach ($installments as $installment) {
                        if ($minimumAmount == 0 or $minimumAmount > $installment->totalPayments($itemPrice)) {
                            $minimumAmount = $installment->totalPayments($itemPrice);
                        }
                    }

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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
                    Log::info('test 3 order status is '.$order->status);
                    if($order->status == 'open'){

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

                    $paidAmount=round($totalSaleAmount+$part_amount);

                    foreach($installments as $installment){
                        if($paidAmount > 0){

                            $orderPayments1 = InstallmentOrderPayment:: where([
                            'type' => 'upfront' ,
                            'installment_order_id' => $order->id
                            ])->first();

                            if($orderPayments1->status !='paid'){

                                if($paidAmount >= $order->item_price*$installment->upfront/100){

                                    InstallmentOrderPayment:: where([
                            'id' => $orderPayments1 ->id
                            ])->update(['status'=>'paid']);

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
Log::info('testing sub step');
                            foreach($installment->steps as $steps){

                                $orderPayments1 = InstallmentOrderPayment:: where([
                                'step_id' => $steps->id,
                                'installment_order_id' => $order->id,
                                ])
                                ->first();

                                if($orderPayments1){

                                    if($orderPayments1->status !='paid'){

                                         Log::info('SubStep 2 marked as paid for step_id:sss ');
                                    $subSteps = SubStepInstallment::where('order_id', $order->id)
                                            ->where('installment_step_id', $steps->id)
                                            ->where('user_id', $user->id)
                                            ->get();

                                     if ($subSteps) {

                                        foreach ($subSteps as $subStep) {

                                            if ($paidAmount >= $subStep->price && $subStep->status != 'paid') {

                                                $subStep->update([
                                                    'status' => 'paid'
                                                ]);
                                                 Log::info('SubStep 2 marked as paid for step_id: ' . $steps->id);

                                                $paidAmount -= $subStep->price;
                                            }
                                        }
                                    }
                                            
                                            // Agar puri amount paid hai toh Part 2 bhi paid mark karo
                                            $subStep2 = SubStepInstallment::where('order_id', $order->id)
                                        ->where('installment_step_id', $steps->id)
                                        ->where('user_id', $user->id)
                                        ->where('sub_step_number', 2)
                                        ->where('status', 'approved')
                                        ->first();
                                            
                                               if (optional($subStep2)->price && $paidAmount >= $subStep2->price) {
                                                $subStep2->update([
                                                    'status' => 'paid',
                                                ]);
                                                Log::info('SubStep 2 marked as paid for step_id: ' . $steps->id);
                                            }

                                        if($paidAmount >= $order->item_price*$steps->amount/100){
                                            $orderPayments1 -> update(['status'=>'paid']);

                                            $accounting = Accounting::where('installment_payment_id', $orderPayments1->id)
                                                ->where('user_id', $user->id)
                                                ->first();

                                            if(!$accounting){

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

                                    $channelManager = ChannelManager::makeChannel($paymentChannel);
                                    $order = $channelManager->verifyBackgroundProccess($data);
                                        $sales_account=new PaymentController_old();
                                       $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);

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

                                                    $installmentPayment = InstallmentOrderPayment :: where('_id', $orderPayments1->id)
                                                    ->update([
                                                    'sale_id' => $sale->id,
                                                ]);

                                            }

                                        }

                                    }
                                    $paidAmount -=$order->item_price*$steps->amount/100;

                                }else{

                                    $orderPayments1 = InstallmentOrderPayment:: create([
                                        'installment_order_id' => $order->id,
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

                        $channelManager = ChannelManager::makeChannel($paymentChannel);
                        $order = $channelManager->verifyBackgroundProccess($data);
                        $sales_account=new PaymentController_old();
                        $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);
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

            Log::info('if paid 1st time in installment or part payment');

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

                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);
                       Log::info('$installmentPayment->status is '.$installmentPayment->status);
                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => ($status=='part')?$status:Order::$paying,
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
                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verifyBackgroundProccess($data);
                Log::info('$channelManager verified the payment with order status '.$order->status);
                $sales_account=new PaymentController_old();
                $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);

                  return true;

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return redirect('/installments/request_submitted');
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }

                }
                }
            }
            }

            }
            else{

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

                        return false;
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

              $item = $this->getItem($itemId, $itemType, $user);

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

                        $productOrder = $this->handleProductOrder($request, $user, $item);
                    }

                    $columnName = $this->getColumnByItemType($itemType);

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

                    $itemPrice = $item->getPrice();
                    $itemPrice1=$itemPrice-$totalDiscount;

                    if(!$order){
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

                    if($part_amount_status){
                    if (!empty($installment->upfront)) {
                        $installmentPayment = InstallmentOrderPayment :: query()->updateOrCreate([
                            'installment_order_id' => $order->id,
                            'sale_id' => null,
                            'type' => 'upfront',
                            'step_id' => null,
                            'amount' => $installment->getUpfront($order->getItemPrice()),
                            'status' => $status,
                        ], [
                            'created_at' => time(),
                        ]);

                    $order_main_table = Order::create([
                            'user_id' => $user->id,
                            'status' => ($status=='part')?$status:Order::$paying,
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
                }

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verifyBackgroundProccess($data);
                    $sales_account=new PaymentController_old();
                   $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);
               $this->shortPaymentSectionBackgroundProcess($data,$user->id,$itemId);
                  return  true;

                    } else {

                        if ($installment->needToVerify()) {
                            sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                            sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);

                            return  false;
                        } else {
                            sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);

                            return $this->handleOpenOrder($item, $productOrder);
                        }
                    }
                }else{

                    $sale =  Sale :: where('buyer_id',$user->id)
                    ->where('webinar_id',$itemId)->first();

                Sale ::  where('id',$sale->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $order = Order :: where('id',$sale->order_id)
                    ->first();

                    Order :: where('id',$order->id)
                    ->update([
                    'total_amount' => $part_amount,
                ]);

                    $OrderItem = OrderItem :: where('order_id',$order->id)
                    ->first();

                    OrderItem :: where('id',$OrderItem->id)
                    ->update([
                    'total_amount' => $part_amount,
                    'installment_type' => 'part' ?? null,
                ]);

                    $accounting = Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                    'amount' => $part_amount,
                ]);

                $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                ->first();

                    if($installmentPayment->amount <= $part_amount){
                     $installmentPayment = InstallmentOrderPayment :: where('sale_id', $sale->id)
                    ->update([
                    'status' => 'paid',
                ]);}

                   return true;

                }

                }
            }
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('installmentBackgroundProcess error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function shortPaymentSectionBackgroundProcess($data ,$userid,$item){
        try {
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
                                                    $sales_account=new PaymentController_old();
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
                                                    $sales_account=new PaymentController_old();
                                                   $sales_account->paymentOrderAfterVerifyBackgroundProccess($order_main_table);

                                    }

                              $paidAmount -=$order->item_price*$steps->amount/100;

                                }
                            }
                        }
                    }
        } catch (\Exception $e) {
            \Log::error('shortPaymentSectionBackgroundProcess error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Process sub-step payment
     */
    public function processSubStepPayment(Request $request)
    {
        try {
            $razorpayPaymentId = $request->get('razorpay_payment_id');
            $subStepId = $request->get('sub_step_id');
            $userId = $request->get('user_id');
            $courseId = $request->get('course_id');

            // Validate sub-step
            $subStep = \App\Models\SubStepInstallment::where('id', $subStepId)
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->first();

            if (!$subStep) {
                return redirect('/payment/failed?error=Sub-step not found');
            }

            // V-09 FIX: Use server-side amount from sub-step, ignore client amount
            $serverAmount = $subStep->price;

            // Update sub-step status
            $subStep->update([
                'status' => \App\Models\SubStepInstallment::STATUS_PAID,
                'payment_date' => time(),
                'transaction_id' => $razorpayPaymentId,
            ]);

            // Create payment record
            $installmentOrder = \App\Models\InstallmentOrder::where('user_id', $userId)
                ->where('webinar_id', $courseId)
                ->first();

            if ($installmentOrder) {
                \App\Models\InstallmentOrderPayment::create([
                    'installment_order_id' => $installmentOrder->id,
                    'step_id' => $subStep->installment_step_id,
                    'sub_step_id' => $subStep->id,
                    'type' => 'step',
                    'amount' => $serverAmount,
                    'status' => 'paid',
                    'payment_date' => time(),
                    'created_at' => time(),
                ]);
            }

            return redirect('/panel/financial/installments/' . $installmentOrder->id . '/details')
                ->with('success', 'Sub-step payment completed successfully!');

        } catch (\Exception $e) {
            \Log::error('processSubStepPayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect('/payment/failed?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle sub-step payments
     */
    private function handleSubStepPayment($request, $course, $subStepId, $stepId, $amount)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return redirect('/login');
            }

            // Validate sub-step
            $subStep = \App\Models\SubStepInstallment::where('id', $subStepId)
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if (!$subStep) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => 'Sub-step not found or not eligible for payment',
                    'status' => 'error'
                ];
                return back()->with(['toast' => $toastData]);
            }

            // V-09 FIX: Always use server-side amount from sub-step, ignore client amount
            $serverAmount = $subStep->price;

            // Get payment channels
            $paymentChannels = PaymentChannel::where('status', 'active')->get();

            $data = [
                'pageTitle' => trans('update.pay_sub_step'),
                'course' => $course,
                'subStep' => $subStep,
                'amount' => $serverAmount,
                'paymentChannels' => $paymentChannels,
                'user' => $user,
            ];

            $agent = new Agent();
            if ($agent->isMobile()) {
                return view(getTemplate() . '.installment.partPayment.subStepPayment', $data);
            } else {
                return view('web.default2.installment.partPayment.subStepPayment', $data);
            }

        } catch (\Exception $e) {
            \Log::error('handleSubStepPayment error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
