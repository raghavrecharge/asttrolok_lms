<?php
namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

use App\Http\Controllers\Controller;
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
use App\Models\Accounting;
use App\Models\PaymentChannel;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\WebinarPartPayment;
use App\Models\InstallmentStep;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Sale;
use App\Http\Controllers\Api\Panel\PaymentsController;
use App\User;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use App\Models\WebinarAccessControl;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\Traits\RegionsDataByUser;
use App\Jobs\InstallmentProcessJob;
use App\PaymentChannels\ChannelManager;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\InstallmentsController as webInstallmentsController;
class OfflineOctusPaymentController extends Controller
{
    use RegionsDataByUser;

    public function index(Request $request,$installmentId)
    {
        try {
            $user = apiAuth();
            $itemId = $request->get('item');
            $itemType = $request->get('item_type');

            if (empty($user) or !$user->enable_installments) {
                $toastData = [
                    'title' => trans('public.request_failed'),
                    'msg' => trans('update.you_cannot_use_installment_plans'),
                    'status' => 'error'
                ];
                 return apiResponse2($toastData);
            }

            if (!empty($itemId) and !empty($itemType) and getInstallmentsSettings('status')) {

                $item = $this->getItem($itemId, $itemType, null);

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

                    ];

                    session(['success'=>false]);
                    return apiResponse2(1, 'checkout', trans('api.cart.checkout'), $data);
                }
            }
            }
            return apiResponse2(0, 'faild', 'item id does not exist');

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

    public function istallmentlist()
    {
        try {
            $user = apiAuth();

            $query = InstallmentOrder::query()
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paying');

            $openInstallmentsCount = deepClone($query)->where('status', 'open')->count();
            $pendingVerificationCount = deepClone($query)->where('status', 'pending_verification')->count();
            $finishedInstallmentsCount = $this->getFinishedInstallments($user);

            $orders = $query->with([
                'installment' => function ($query) {
                    $query->with([
                        'steps' => function ($query) {
                            $query->orderBy('deadline', 'asc');
                        }
                    ]);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])->orderBy('created_at', 'desc')
                ->get();

            foreach ($orders as $order) {
                $getRemainedInstallments = $this->getRemainedInstallments($order);

                $order->remained_installments_count = $getRemainedInstallments['total'];
                $order->remained_installments_amount = $getRemainedInstallments['amount'];

                $order->upcoming_installment = $this->getUpcomingInstallment($order);

                $hasOverdue = $order->checkOrderHasOverdue();
                $order->has_overdue = $hasOverdue;
                $order->overdue_count = 0;
                $order->overdue_amount = 0;

                if ($hasOverdue) {
                    $getOrderOverdueCountAndAmount = $order->getOrderOverdueCountAndAmount();
                    $order->overdue_count = $getOrderOverdueCountAndAmount['count'];
                    $order->overdue_amount = $getOrderOverdueCountAndAmount['amount'];
                }
                $order->webinar = $this->getItem($order['webinar_id'], "course", null);

            }

            $overdueInstallmentsCount = $this->getOverdueInstallments($user);

            $data = [
                'pageTitle' => trans('update.installments'),
                'openInstallmentsCount' => $openInstallmentsCount,
                'pendingVerificationCount' => $pendingVerificationCount,
                'finishedInstallmentsCount' => $finishedInstallmentsCount,
                'overdueInstallmentsCount' => $overdueInstallmentsCount,
                'orders' => $orders,
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        } catch (\Exception $e) {
            \Log::error('istallmentlist error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function installmentsOverdue()
    {
        try {
            $user = apiAuth();

            $query = InstallmentOrder::query()
                ->where('user_id', $user->id)
                ->where('status', '!=', 'paying');

            $openInstallmentsCount = deepClone($query)->where('status', 'open')->count();
            $pendingVerificationCount = deepClone($query)->where('status', 'pending_verification')->count();
            $finishedInstallmentsCount = $this->getFinishedInstallments($user);

            $orders = $query->with([
                'installment' => function ($query) {
                    $query->with([
                        'steps' => function ($query) {
                            $query->orderBy('deadline', 'asc');
                        }
                    ]);
                    $query->withCount([
                        'steps'
                    ]);
                }
            ])->orderBy('created_at', 'desc')
                ->get();

            foreach ($orders as $order) {

                $getRemainedInstallments = $this->getRemainedInstallments($order);

                $order->remained_installments_count = $getRemainedInstallments['total'];
                $order->remained_installments_amount = $getRemainedInstallments['amount'];

                $order->upcoming_installment = $this->getUpcomingInstallment($order);

                $hasOverdue = $order->checkOrderHasOverdue();
                $order->has_overdue = $hasOverdue;
                $order->overdue_count = 0;
                $order->overdue_amount = 0;

                if ($hasOverdue) {
                    $getOrderOverdueCountAndAmount = $order->getOrderOverdueCountAndAmount();
                    $order->overdue_count = $getOrderOverdueCountAndAmount['count'];
                    $order->overdue_amount = $getOrderOverdueCountAndAmount['amount'];
                }

            }

            $overdueInstallmentsCount = $this->getOverdueInstallments($user);

            $data = [
                'pageTitle' => trans('update.installments'),
                'openInstallmentsCount' => $openInstallmentsCount,
                'pendingVerificationCount' => $pendingVerificationCount,
                'finishedInstallmentsCount' => $finishedInstallmentsCount,
                'overdueInstallmentsCount' => $overdueInstallmentsCount,
                'orders' => $orders,

            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        } catch (\Exception $e) {
            \Log::error('installmentsOverdue error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

   public function payUpcomingPart($itemId)
{
        try {
            $user = apiAuth();

            $order = InstallmentOrder::query()
            ->where('id', $itemId)
            ->where('user_id', $user->id)
            ->first();

            if (!empty($order)) {
            $upcomingStep = $this->getUpcomingInstallment($order);

            if (!empty($upcomingStep)) {
                return $this->handlePayStep($order, $upcomingStep);
            }
            }

            return response()->json([
            'success' => false,
            'message' => 'Installment order not found or no upcoming step available.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('payUpcomingPart error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

public function payStep($orderId, $stepId)
    {
        try {
            $user = apiAuth();

            $order = InstallmentOrder::query()
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!empty($order)) {
                $step = InstallmentStep::query()
                    ->where('installment_id', $order->installment_id)
                    ->where('id', $stepId)
                    ->first();

                if (!empty($step)) {
                    return $this->handlePayStep($order, $step);
                }
            }

            return response()->json([
            'success' => false,
            'message' => 'Installment order not found or no upcoming step available.',
            'data'=>[]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('payStep error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handlePayStep($order, $step)
     {
        $user = apiAuth();
        $paidAmount = null;

        $WebinarPartPayment = WebinarPartPayment::select(
            'user_id',
            'webinar_id',
            'installment_id',
            DB::raw('SUM(amount) as total_amount')
        )
            ->where('user_id', $user->id)
            ->where('webinar_id', $order->webinar_id)
            ->groupBy('user_id', 'webinar_id')
            ->first();

        if ($WebinarPartPayment) {

            $orderPayments = InstallmentOrderPayment::where('installment_order_id', $order->id)->get();

            $totalSaleAmount = 0;

            foreach ($orderPayments as $orderPayment) {
                $saleId = $orderPayment->sale_id;

                if ($saleId) {
                    $sale = Sale::where([
                        'id' => $saleId,
                        'status' => null
                    ])->first();

                    if ($sale) {
                        $totalSaleAmount += $sale->total_amount;
                    }
                }
            }

            $orderPaymentsTotalPaidAmount = InstallmentOrderPayment::select(
                '*',
                DB::raw('SUM(amount) as total_amount')
            )
                ->where('installment_order_id', $order->id)
                ->where('status', 'paid')
                ->groupBy('installment_order_id')
                ->first();

            $paidAmount = $totalSaleAmount +
                ($WebinarPartPayment->total_amount ?? 0) -
                ($orderPaymentsTotalPaidAmount->total_amount ?? 0);
        }

        $installmentPayment = InstallmentOrderPayment::updateOrCreate(
            [
                'installment_order_id' => $order->id,
                'sale_id' => null,
                'type' => 'step',
                'step_id' => $step->id,
                'amount' => $step->getPrice($order->getItemPrice()),
                'status' => 'paying',
            ],
            [
                'created_at' => time(),
            ]
        );

        Cart::updateOrCreate(
            [
                'creator_id' => $order->user_id,
                'installment_payment_id' => $installmentPayment->id,
                'extra_amount' => $paidAmount,

            ],
            [
                'created_at' => time(),
            ]
        );
        $carts = Cart::where('creator_id', $user->id)->where('installment_payment_id', $installmentPayment->id)->get();

       $paymentChannels = PaymentChannel::where('status', 'active')->get();

        if (!empty($carts) and !$carts->isEmpty()) {
            $calculate = $this->calculatePrice($carts, $user);

          $order1=  $this->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon = null);

        }
         $order->webinar = $this->getItem($order->webinar_id, "course", null);

       $data= [
             'installment_order_payment' =>$installmentPayment,
             'installment_order' => $order,
             'order' => $order1,
             'paymentChannels'=>$paymentChannels
            ];

         return apiResponse2(1, 'retrieved', 'data retrieved successfully', $data);
    }
  public function createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon = null)
    {
        try {
            $totalCouponDiscount = 0;
            $installment_payment_id=0;

            if (!empty($discountCoupon)) {
                $totalCouponDiscount = $this->handleDiscountPrice($discountCoupon, $carts, $calculate['sub_total']);
            }

            if($cartInstallment1 = CartInstallment::where('user_id', $user->id)
                ->get()){
                if($cartInstallment1){
                    foreach( $cartInstallment1 as $cartInstallment){
                        if($cartInstallment->id){
                            $discount_price = $cartInstallment->discount_price;
                            $installment_price =$cartInstallment->installment_price;
                            $installment_payment_id=$cartInstallment->installment_payment_id;
                            $total=$cartInstallment->total;
                        }

                    }
                }

                }

            $totalAmount = $calculate["total"] - $totalCouponDiscount;

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::$pending,
                'amount' => $calculate["sub_total"],
                'tax' => $calculate["tax_price"],
                'total_discount' => $calculate["total_discount"] + $totalCouponDiscount,
                'total_amount' => (($totalAmount > 0) ? $totalAmount : 0),
                'product_delivery_fee' => $calculate["product_delivery_fee"] ?? null,
                'created_at' => time(),
            ]);

            $productsFee = $this->productDeliveryFeeBySeller($carts);
            $sellersProductsCount = $this->productCountBySeller($carts);

            foreach ($carts as $cart) {

                $orderPrices = $this->handleOrderPrices($cart, $user);
                $price = $orderPrices['sub_total'];
                $totalDiscount = $orderPrices['total_discount'];
                $tax = $orderPrices['tax'];
                $taxPrice = $orderPrices['tax_price'];
                $commission = $orderPrices['commission'];
                $commissionPrice = $orderPrices['commission_price'];

                $productDeliveryFee = 0;
                if (!empty($cart->product_order_id)) {
                    $product = $cart->productOrder->product;

                    if (!empty($product) and !empty($productsFee[$product->creator_id])) {
                        $productDeliveryFee = $productsFee[$product->creator_id];
                    }

                    $sellerProductCount = !empty($sellersProductsCount[$product->creator_id]) ? $sellersProductsCount[$product->creator_id] : 1;

                    $productDeliveryFee = $productDeliveryFee > 0 ? $productDeliveryFee / $sellerProductCount : 0;
                }

                $allDiscountPrice = $totalDiscount;
                if ($totalCouponDiscount > 0 and $price > 0) {
                    $percent = (($price / $calculate["total"]) * 100);
                    $allDiscountPrice += (($totalCouponDiscount * $percent) / 100);
                }

                $subTotalWithoutDiscount = $price - $allDiscountPrice;
                $totalAmount = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

                if(auth()->user()){
                $ticket = $cart->ticket;
                if (!empty($ticket) and !$ticket->isValid()) {
                    $ticket = null;
                }
                }
                if ($cart->installment_payment_id == $installment_payment_id){
                OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'webinar_id' => $cart->webinar_id ?? null,
                    'bundle_id' => $cart->bundle_id ?? null,
                    'product_id' => (!empty($cart->product_order_id) and !empty($cart->productOrder->product)) ? $cart->productOrder->product->id : null,
                    'product_order_id' => (!empty($cart->product_order_id)) ? $cart->product_order_id : null,
                    'reserve_meeting_id' => $cart->reserve_meeting_id ?? null,
                    'subscribe_id' => $cart->subscribe_id ?? null,
                    'promotion_id' => $cart->promotion_id ?? null,
                    'gift_id' => $cart->gift_id ?? null,
                    'installment_payment_id' => $cart->installment_payment_id ?? null,
                    'ticket_id' => !empty($ticket) ? $ticket->id : null,
                    'discount_id' => !empty($discountId)?$discountId:($discountCoupon ? $discountCoupon->id : null),
                    'amount' => !empty($installment_price)?$installment_price:$price,
                    'total_amount' => !empty($total)?$total:$totalAmount,
                    'tax' => $tax,
                    'tax_price' => $taxPrice,
                    'commission' => $commission,
                    'commission_price' => $commissionPrice,
                    'product_delivery_fee' => $productDeliveryFee,
                    'discount' => !empty($discount_price)?$discount_price:$allDiscountPrice,
                    'created_at' => time(),
                ]);

                }else{
                    OrderItem::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'webinar_id' => $cart->webinar_id ?? null,
                    'bundle_id' => $cart->bundle_id ?? null,
                    'product_id' => (!empty($cart->product_order_id) and !empty($cart->productOrder->product)) ? $cart->productOrder->product->id : null,
                    'product_order_id' => (!empty($cart->product_order_id)) ? $cart->product_order_id : null,
                    'reserve_meeting_id' => $cart->reserve_meeting_id ?? null,
                    'subscribe_id' => $cart->subscribe_id ?? null,
                    'promotion_id' => $cart->promotion_id ?? null,
                    'gift_id' => $cart->gift_id ?? null,
                    'installment_payment_id' => $cart->installment_payment_id ?? null,
                    'ticket_id' => !empty($ticket) ? $ticket->id : null,
                    'discount_id' =>($discountCoupon ? $discountCoupon->id : null),
                    'amount' => $price,
                    'total_amount' => $totalAmount,
                    'tax' => $tax,
                    'tax_price' => $taxPrice,
                    'commission' => $commission,
                    'commission_price' => $commissionPrice,
                    'product_delivery_fee' => $productDeliveryFee,
                    'discount' => $allDiscountPrice,
                    'created_at' => time(),
                ]);
                }
            }
            Cart::where('id', $cart->id)->delete();

            return $order;
        } catch (\Exception $e) {
            \Log::error('createOrderAndOrderItems error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
       private function handleDiscountPrice($discount, $carts, $subTotal)
    {
        $user = auth()->user();
        $percent = $discount->percent ?? 0;
        $totalDiscount = 0;

        if ($discount->source == Discount::$discountSourceCourse) {
            $totalWebinarsAmount = 0;
            $webinarOtherDiscounts = 0;
            $discountWebinarsIds = $discount->discountCourses()->pluck('course_id')->toArray();

            foreach ($carts as $cart) {
                if(!empty($cart->webinar)){
                    $webinar = $cart->webinar;
                }else{
                     $webinar=Webinar::where('id',$cart['item_id'])->first();
                }

                if (!empty($webinar) and in_array($webinar->id, $discountWebinarsIds)) {
                    $totalWebinarsAmount += $webinar->price;

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalWebinarsAmount > $discount->amount) ? $discount->amount : $totalWebinarsAmount;

            } else {
                $totalDiscount = ($totalWebinarsAmount > 0) ? $totalWebinarsAmount * $percent / 100 : 0;
            }
        } elseif ($discount->source == Discount::$discountSourceBundle) {
            $totalBundlesAmount = 0;
            $bundleOtherDiscounts = 0;
            $discountBundlesIds = $discount->discountBundles()->pluck('bundle_id')->toArray();

            foreach ($carts as $cart) {
                $bundle = $cart->bundle;
                if (!empty($bundle) and in_array($bundle->id, $discountBundlesIds)) {
                    $totalBundlesAmount += $bundle->price;

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalBundlesAmount > $discount->amount) ? $discount->amount : $totalBundlesAmount;

            } else {
                $totalDiscount = ($totalBundlesAmount > 0) ? $totalBundlesAmount * $percent / 100 : 0;
            }
        } elseif ($discount->source == Discount::$discountSourceProduct) {
            $totalProductsAmount = 0;
            $productOtherDiscounts = 0;

            foreach ($carts as $cart) {
                if (!empty($cart->productOrder)) {
                    $product = $cart->productOrder->product;

                    if (!empty($product) and ($discount->product_type == 'all' or $discount->product_type == $product->type)) {
                        $totalProductsAmount += ($product->price * $cart->productOrder->quantity);

                    }
                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalProductsAmount > $discount->amount) ? $discount->amount : $totalProductsAmount;

            } else {
                $totalDiscount = ($totalProductsAmount > 0) ? $totalProductsAmount * $percent / 100 : 0;
            }
        } elseif ($discount->source == Discount::$discountSourceMeeting) {
            $totalMeetingAmount = 0;
            $meetingOtherDiscounts = 0;

            foreach ($carts as $cart) {
                $reserveMeeting = $cart->reserveMeeting;

                if (!empty($reserveMeeting)) {
                    $totalMeetingAmount += $reserveMeeting->paid_amount;

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalMeetingAmount > $discount->amount) ? $discount->amount : $totalMeetingAmount;

            } else {
                $totalDiscount = ($totalMeetingAmount > 0) ? $totalMeetingAmount * $percent / 100 : 0;
            }
        } elseif ($discount->source == Discount::$discountSourceCategory) {
            $totalCategoriesAmount = 0;
            $categoriesOtherDiscounts = 0;

            $categoriesIds = ($discount->discountCategories) ? $discount->discountCategories()->pluck('category_id')->toArray() : [];

            foreach ($carts as $cart) {
                $webinar = $cart->webinar;

                if (!empty($webinar) and in_array($webinar->category_id, $categoriesIds)) {
                    $totalCategoriesAmount += $webinar->price;

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalCategoriesAmount > $discount->amount) ? $discount->amount : $totalCategoriesAmount;

            } else {
                $totalDiscount = ($totalCategoriesAmount > 0) ? $totalCategoriesAmount * $percent / 100 : 0;
            }
        } else {
            $totalCartAmount = 0;
            $totalCartOtherDiscounts = 0;

            foreach ($carts as $cart) {
                $webinar = $cart->webinar;
                $reserveMeeting = $cart->reserveMeeting;

                if (!empty($webinar)) {
                    $totalCartAmount += $webinar->price;

                }

                if (!empty($reserveMeeting)) {
                    $totalCartAmount += $reserveMeeting->paid_amount;

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalCartAmount > $discount->amount) ? $discount->amount : $totalCartAmount;

            } else {
                $totalDiscount = ($totalCartAmount > 0) ? $totalCartAmount * $percent / 100 : 0;
            }
        }

        if ($discount->discount_type != Discount::$discountTypeFixedAmount and !empty($discount->max_amount) and $totalDiscount > $discount->max_amount) {
            $totalDiscount = $discount->max_amount;
        }

        return $totalDiscount;
    }
        private function getSeller($cart)
    {
        $user = null;

        if (!empty($cart->webinar_id) or !empty($cart->bundle_id)) {
            $user = $cart->webinar_id ? $cart->webinar->creator : $cart->bundle->creator;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $user = $cart->reserveMeeting->meeting->creator;
        } elseif (!empty($cart->product_order_id)) {
            $user = $cart->productOrder->seller;
        }

        return $user;
    }
    private function getSeller1($cart)
    {
        $user = null;

        if (!empty($cart['item_id'])) {
            $webinar=Webinar::where('id',$cart['item_id'])->first();

           $user =User::where('id',$webinar->creator_id)->get();
        }

        return $user;
    }
      public function handleOrderPrices($cart, $user, $taxIsDifferent = false)
    {
        try {
            $seller = $this->getSeller($cart);

            $financialSettings = getFinancialSettings();

            $subTotal = 0;
            $totalDiscount = 0;
            $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
            $taxPrice = 0;
            $commissionPrice = 0;

            if (!empty($seller)) {
                $commission = $seller->getCommission();
            } else {
                $commission = 0;

                if (!empty($financialSettings) and !empty($financialSettings['commission'])) {
                    $commission = (int)$financialSettings['commission'];
                }
            }

            if (!empty($cart->webinar_id) or !empty($cart->bundle_id)) {
                $item = !empty($cart->webinar_id) ? $cart->webinar : $cart->bundle;
                $price = $item->price;

                $discount = $item->getDiscount($cart->ticket, $user);

                $priceWithoutDiscount = $price - $discount;

                if ($tax > 0 and $priceWithoutDiscount > 0) {
                    $taxPrice += $priceWithoutDiscount * $tax / 100;
                }

                if (!empty($commission) and $commission > 0) {
                    $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
                }

                $totalDiscount += $discount;
                $subTotal += $price;
            } elseif (!empty($cart->reserve_meeting_id)) {
                $price = $cart->reserveMeeting->paid_amount;
                $discount = $cart->reserveMeeting->getDiscountPrice($user);

                $priceWithoutDiscount = $price - $discount;

                if ($tax > 0 and $priceWithoutDiscount > 0) {
                    $taxPrice += $priceWithoutDiscount * $tax / 100;
                }

                if (!empty($commission) and $commission > 0) {
                    $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
                }

                $totalDiscount += $discount;
                $subTotal += $price;
            } elseif (!empty($cart->product_order_id)) {
                $product = $cart->productOrder->product;

                if (!empty($product)) {
                    $price = ($product->price * $cart->productOrder->quantity);
                    $discount = $product->getDiscountPrice();

                    $commission = $product->getCommission();
                    $productTax = $product->getTax();

                    $priceWithoutDiscount = $price - $discount;

                    $taxIsDifferent = ($taxIsDifferent and $tax != $productTax);

                    $tax = $productTax;
                    if ($productTax > 0 and $priceWithoutDiscount > 0) {
                        $taxPrice += $priceWithoutDiscount * $productTax / 100;
                    }

                    if ($commission > 0) {
                        $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
                    }

                    $totalDiscount += $discount;
                    $subTotal += $price;
                }
            } elseif (!empty($cart->installment_payment_id)) {
                $price = $cart->installmentPayment->amount;

                $discount = 0;

                $priceWithoutDiscount = $price - $discount;

                if ($tax > 0 and $priceWithoutDiscount > 0) {
                    $taxPrice += $priceWithoutDiscount * $tax / 100;
                }

                if (!empty($commission) and $commission > 0) {
                    $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
                }

                $totalDiscount += $discount;
                $subTotal += $price;
            }

            if ($totalDiscount > $subTotal) {
                $totalDiscount = $subTotal;
            }

            return [
                'sub_total' => round($subTotal, 2),
                'total_discount' => round($totalDiscount, 2),
                'tax' => $tax,
                'tax_price' => round($taxPrice, 2),
                'commission' => $commission,
                'commission_price' => round($commissionPrice, 2),

                'tax_is_different' => $taxIsDifferent
            ];
        } catch (\Exception $e) {
            \Log::error('handleOrderPrices error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 private function productDeliveryFeeBySeller($carts)
    {
        $productFee = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;

                if (!empty($product->delivery_fee)) {
                    if (!empty($productFee[$product->creator_id]) and $productFee[$product->creator_id] < $product->delivery_fee) {
                        $productFee[$product->creator_id] = $product->delivery_fee;
                    } else if (empty($productFee[$product->creator_id])) {
                        $productFee[$product->creator_id] = $product->delivery_fee;
                    }
                }
            }
        }

        return $productFee;
    }

    private function productCountBySeller($carts)
    {
        $productCount = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;

                if (!empty($productCount[$product->creator_id])) {
                    $productCount[$product->creator_id] += 1;
                } else {
                    $productCount[$product->creator_id] = 1;
                }
            }
        }

        return $productCount;
    }

    private function calculateProductDeliveryFee($carts)
    {
        $fee = 0;

        if (!empty($carts)) {
            $productsFee = $this->productDeliveryFeeBySeller($carts);

            if (!empty($productsFee) and count($productsFee)) {
                $fee = array_sum($productsFee);
            }
        }

        return $fee;
    }

    private function calculatePrice($carts, $user, $discountCoupon = null)
    {
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $commission = 0;

        $cartHasWebinar = array_filter($carts->pluck('webinar_id')->toArray());
        $cartHasBundle = array_filter($carts->pluck('bundle_id')->toArray());
        $cartHasMeeting = array_filter($carts->pluck('reserve_meeting_id')->toArray());
        $cartHasInstallmentPayment = array_filter($carts->pluck('installment_payment_id')->toArray());

        $taxIsDifferent = (count($cartHasWebinar) or count($cartHasBundle) or count($cartHasMeeting) or count($cartHasInstallmentPayment));

        foreach ($carts as $cart){
            $orderPrices = $this->handleOrderPrices($cart, $user, $taxIsDifferent);

            $subTotal += $orderPrices['sub_total'];
            $totalDiscount += $orderPrices['total_discount'];
            $tax = $orderPrices['tax'];
            $taxPrice += $orderPrices['tax_price'];
            $commission += $orderPrices['commission'];
            $commissionPrice += $orderPrices['commission_price'];
            $taxIsDifferent = $orderPrices['tax_is_different'];
        }

        if (!empty($discountCoupon)) {
            $totalDiscount += $this->handleDiscountPrice($discountCoupon, $carts, $subTotal);
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $subTotalWithoutDiscount = $subTotal - $totalDiscount;
        $productDeliveryFee = $this->calculateProductDeliveryFee($carts);

        $total = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        if ($total < 0) {
            $total = 0;
        }

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            'total' => round($total, 2),
            'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent
        ];
    }

    private function calculatePrice1($carts, $user, $discountCoupon = null)
     {
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $commission = 0;
        $items= [];

        $cartHasWebinar = array_filter($carts);

         $taxIsDifferent = count($cartHasWebinar);

        foreach ($carts as $cart){

            if(!empty($cart['item_id'])){
                 $orderPrices = $this->handleOrderPrices1($cart, $user, $taxIsDifferent);

            }else{
                 $orderPrices = $this->handleOrderPrices($cart, $user, $taxIsDifferent);
            }

             $items = $orderPrices['item'];

            $subTotal += $orderPrices['sub_total'];
            $totalDiscount += $orderPrices['total_discount'];
            $tax = $orderPrices['tax'];
            $taxPrice += $orderPrices['tax_price'];
            $commission += $orderPrices['commission'];
            $commissionPrice += $orderPrices['commission_price'];
            $taxIsDifferent = $orderPrices['tax_is_different'];
        }

        if (!empty($discountCoupon)) {
            $totalDiscount += $this->handleDiscountPrice($discountCoupon, $carts, $subTotal);
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $subTotalWithoutDiscount = $subTotal - $totalDiscount;
        $productDeliveryFee = $this->calculateProductDeliveryFee($carts);

        $total = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        if ($total < 0) {
            $total = 0;
        }

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            'total' => round($total, 2),
            'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent,
            'items' =>$items
        ];
    }
    public function partPayment(Request $request, $slug)
    {
        try {
            $user = apiAuth();

            $course = Webinar::where('slug', $slug)
            ->where('status', 'active')
            ->first();
            $itemId = $course->id;
            $itemType = $course->type;

            $installmentPlans = new InstallmentPlans();
            $installments = $installmentPlans->getPlans('courses', $course->id, $course->type, $course->category_id, $course->teacher_id);

            $installmentId = $installments[0]->id;

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
                        ];

                         return apiResponse2(1, 'retrieved', 'data retrieved successfully', $data);

                    }
                }

                return apiResponse2(0, 'retrieved', 'data retrieved successfully', []);
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

    public function store(Request $request){
    $data = $request->all();

        try {
          if(!empty($data['razorpay_payment_id'])){
          $response=  $this->installmentBackgroundProcess($request);
        if($response){
                        return $response=['status' => 'success','title' => 'Installment Request Submitted', 'message' => 'We received your installment request and the process will be informed to you'];

        }

          }else{
             $toastData = [
                    'title' => trans('cart.fail_purchase'),
                    'msg' => trans('cart.gateway_error'),
                    'status' => 'error'
                ];
                return $toastData;

          }

        } catch (Exception $e) {

            throw $e->getMessage();
        }
    }

     public function installmentBackgroundProcess($request)
    {
        try {
            $data = $request->all();
            $itemId         = $data['item'] ?? null;
            $itemType       = $data['item_type'] ?? null;
            $discountId     = $data['discountId'] ?? null;
            $installmentId  = $data['installment_id'] ?? null;
            $name           = $data['name'] ?? null;
            $email          = $data['email'] ?? null;
            $contact        = $data['number'] ?? null;
            $payment_chennel   = $data['payment_chennel'] ?? '';
            $amount         = isset($data['amount']) && is_numeric($data['amount']) ? floatval($data['amount']) : 0;
            $totalDiscount  = isset($data['totalDiscount']) && is_numeric($data['totalDiscount']) ? floatval($data['totalDiscount']) : 0;

            if (!empty($data['totalDiscount'])) {
                $totalDiscount = $data['totalDiscount'];
            }

             $paymentChannel = PaymentChannel::where('id', $payment_chennel)
            ->where('status', 'active')
            ->first();
            $user = apiAuth();

            $item = $this->getItem($itemId, $itemType, $user);

            $itemPrice = round($item->getPrice());
            if($totalDiscount)
            $itemPrice -= $totalDiscount;

            if (isset($amount)) {

            if ($amount >= $itemPrice) {

            $order_main_table = Order::create([
                'user_id'              => $user->id,
                'status'               => Order::$paying,
                'amount'               => $amount ?? $installment->getUpfront($order->getItemPrice()),
                'tax'                  => 0,
                'total_discount'       => $totalDiscount,
                'total_amount'         => $amount ?? $installment->getUpfront($order->getItemPrice()),
                'product_delivery_fee' => null,
                'created_at'           => time(),
            ]);

            $discountCoupon = Discount::where('id', $discountId)->first();
            if (empty($discountCoupon) || $discountCoupon->checkValidDiscount() !== 'ok') {
                $discountCoupon = null;
            }

            if ($order_main_table) {
                $order_item = OrderItem::create([
                    'user_id'                => $user->id,
                    'order_id'               => $order_main_table->id,
                    'webinar_id'             => $itemId ?? null,
                    'bundle_id'              => null,
                    'product_id'             => null,
                    'product_order_id'       => null,
                    'reserve_meeting_id'     => null,
                    'subscribe_id'           => null,
                    'promotion_id'           => null,
                    'gift_id'                => null,
                    'installment_payment_id' => $installmentPayment->id ?? null,
                    'ticket_id'              => null,
                    'discount_id'            => !empty($discountId) ? $discountId : ($discountCoupon ? $discountCoupon->id : null),
                    'amount'                 => $amount ?? $installment->getUpfront($order->getItemPrice()),
                    'total_amount'           => $amount ?? $installment->getUpfront($order->getItemPrice()),
                    'tax'                    => 0,
                    'tax_price'              => 0,
                    'commission'             => 0,
                    'commission_price'       => 0,
                    'product_delivery_fee'   => 0,
                    'discount'               => $totalDiscount,
                    'created_at'             => time(),
                ]);

                session()->put('order_id1', $order_main_table->id);
                $data['order_id'] = $order_main_table->id;
            }

            $channelManager = ChannelManager::makeChannel($paymentChannel);
            $order = $channelManager->verifyBackgroundProccess($data);
            $sales_account = new PaymentController();
            $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);

            return true;
            } else {

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

                        WebinarPartPayment::Create([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                        'amount' => $amount,
                        'via_payment' =>$via_payment,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

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
                                                    $data['order_id'] =$order_main_table->id;
                  }

                                    $channelManager = ChannelManager::makeChannel($paymentChannel);
                                    $order = $channelManager->verifyBackgroundProccess($data);
                                        $sales_account=new PaymentController();
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
                                                    $data['order_id'] =$order_main_table->id;
                }

                        $channelManager = ChannelManager::makeChannel($paymentChannel);
                        $order = $channelManager->verifyBackgroundProccess($data);
                        $sales_account=new PaymentController();
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

                }
            if ($order->status != 'open') {

            if (!empty($productOrder)) {
            $productOrder->update([
                'installment_order_id' => $order->id,
            ]);
            }

            $notifyOptions = [
            '[u.name]'           => $order->user->full_name,
            '[installment_title]' => $installment->main_title,
            '[time.date]'        => dateTimeFormat(time(), 'j M Y - H:i'),
            '[amount]'           => handlePrice($itemPrice),
            ];

            sendNotification("instalment_request_submitted", $notifyOptions, $order->user_id);
            sendNotification("instalment_request_submitted_for_admin", $notifyOptions, 1);

            if ($part_amount_status) {
            if (!empty($installment->upfront)) {
                $installmentPayment = InstallmentOrderPayment::updateOrCreate([
                    'installment_order_id' => $order->id,
                    'sale_id'              => null,
                    'type'                 => 'upfront',
                    'step_id'              => null,
                    'amount'               => $installment->getUpfront($order->getItemPrice()),
                    'status'               => $status,
                ], [
                    'created_at' => time(),
                ]);

                $order_main_table = Order::create([
                    'user_id'              => $user->id,
                    'status'               => ($status == 'part') ? $status : Order::$paying,
                    'amount'               => $amount ?? $installment->getUpfront($order->getItemPrice()),
                    'tax'                  => 0,
                    'total_discount'       => $totalDiscount,
                    'total_amount'         => $amount ?? $installment->getUpfront($order->getItemPrice()),
                    'product_delivery_fee' => null,
                    'created_at'           => time(),
                ]);

                $discountCoupon = Discount::where('id', $discountId)->first();
                if (empty($discountCoupon) || $discountCoupon->checkValidDiscount() != 'ok') {
                    $discountCoupon = null;
                }

                if ($order_main_table) {
                    $order_item = OrderItem::create([
                        'user_id'                => $user->id,
                        'order_id'               => $order_main_table->id,
                        'webinar_id'             => $itemId ?? null,
                        'bundle_id'              => null,
                        'product_id'             => null,
                        'product_order_id'       => null,
                        'reserve_meeting_id'     => null,
                        'subscribe_id'           => null,
                        'promotion_id'           => null,
                        'gift_id'                => null,
                        'installment_payment_id' => $installmentPayment->id ?? null,
                        'installment_type'       => ($status == 'part') ? $status : null,
                        'ticket_id'              => null,
                        'discount_id'            => !empty($discountId) ? $discountId : ($discountCoupon ? $discountCoupon->id : null),
                        'amount'                 => $amount ?? $installment->getUpfront($order->getItemPrice()),
                        'total_amount'           => $amount ?? $installment->getUpfront($order->getItemPrice()),
                        'tax'                    => 0,
                        'tax_price'              => 0,
                        'commission'             => 0,
                        'commission_price'       => 0,
                        'product_delivery_fee'   => 0,
                        'discount'               => $totalDiscount,
                        'created_at'             => time(),
                    ]);

                    session()->put('order_id1', $order_main_table->id);
                    $data['order_id'] = $order_main_table->id;
                }
                $paymentChannel = PaymentChannel::where('id', $payment_chennel)
            ->where('status', 'active')
            ->first();

                $channelManager = ChannelManager::makeChannel($paymentChannel);
                $order = $channelManager->verifyBackgroundProccess($data);

                $sales_account = new PaymentController();
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
            } else {

            $sale = Sale::where('buyer_id', $user->id)
                ->where('webinar_id', $itemId)
                ->first();

            if ($sale) {
                Sale::where('id', $sale->id)->update([
                    'total_amount' => $part_amount,
                ]);

                $order = Order::where('id', $sale->order_id)->first();

                Order::where('id', $order->id)->update([
                    'total_amount' => $part_amount,
                ]);

                $OrderItem = OrderItem::where('order_id', $order->id)->first();

                OrderItem::where('id', $OrderItem->id)->update([
                    'total_amount'      => $part_amount,
                    'installment_type'  => 'part' ?? null,
                ]);

                Accounting::where('order_item_id', $OrderItem->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'amount' => $part_amount,
                    ]);

                $installmentPayment = InstallmentOrderPayment::where('sale_id', $sale->id)->first();

                if ($installmentPayment && $installmentPayment->amount <= $part_amount) {
                    InstallmentOrderPayment::where('sale_id', $sale->id)->update([
                        'status' => 'paid',
                    ]);
                }

                return true;
            }
            }
            }

                }
            }
            }

            }
            else {
            $installment = Installment::query()
            ->where('id', $installmentId)
            ->where('enable', true)
            ->withCount(['steps'])
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

            $attachments = (!empty($data['attachments']) && count($data['attachments'])) ? array_map('array_filter', $data['attachments']) : [];
            $attachments = !empty($attachments) ? array_filter($attachments) : [];

            if ($installment->request_uploads && count($attachments) < 1) {
                return false;
            }

            if (!empty($installment->capacity)) {
                $openOrdersCount = InstallmentOrder::query()
                    ->where('installment_id', $installment->id)
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

            foreach ($installments as $inst) {
                if ($minimumAmount == 0 || $minimumAmount > $inst->totalPayments($itemPrice)) {
                    $minimumAmount = $inst->totalPayments($itemPrice);
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
                    $status = $installment->needToVerify() ? 'pending_verification' : 'open';
                }

                $order = InstallmentOrder::where([
                    'installment_id' => $installment->id,
                    'user_id' => $user->id,
                    'webinar_id' => $item->id,
                    'status' => 'open',
                ])->first();

                $itemPrice1 = $itemPrice - $totalDiscount;

                if (!$order) {
                    $order = InstallmentOrder::updateOrCreate([
                        'installment_id' => $installment->id,
                        'user_id' => $user->id,
                        'discount' => $totalDiscount,
                        $columnName => $itemId,
                        'product_order_id' => $productOrder->id ?? null,
                        'item_price' => $itemPrice1,
                        'status' => "paying",
                    ], [
                        'created_at' => time(),
                    ]);
                }

                $part_amount_status = true;

                if (!empty($productOrder)) {
                    $productOrder->update(['installment_order_id' => $order->id]);
                }

                $notifyOptions = [
                    '[u.name]' => $order->user->full_name,
                    '[installment_title]' => $installment->main_title,
                    '[time.date]' => dateTimeFormat(time(), 'j M Y - H:i'),
                    '[amount]' => handlePrice($itemPrice),
                ];

                sendNotification("instalment_request_submitted", $notifyOptions, $order->user_id);
                sendNotification("instalment_request_submitted_for_admin", $notifyOptions, 1);

                if ($part_amount_status && !empty($installment->upfront)) {

                    $installmentPayment = InstallmentOrderPayment::updateOrCreate([
                        'installment_order_id' => $order->id,
                        'sale_id' => null,
                        'type' => 'upfront',
                        'step_id' => null,
                        'amount' => $installment->getUpfront($order->getItemPrice()),
                        'status' => "paying",
                    ], [
                        'created_at' => time(),
                    ]);

                    $order_main_table = Order::create([
                        'user_id' => $user->id,
                        'status' => ($status == 'part') ? $status : Order::$paying,
                        'amount' => $amount ?? $installment->getUpfront($order->getItemPrice()),
                        'tax' => 0,
                        'total_discount' => $totalDiscount,
                        'total_amount' => $amount ?? $installment->getUpfront($order->getItemPrice()),
                        'product_delivery_fee' => null,
                        'created_at' => time(),
                    ]);

                    $discountCoupon = Discount::where('id', $discountId)->first();
                    if (empty($discountCoupon) || $discountCoupon->checkValidDiscount() != 'ok') {
                        $discountCoupon = null;
                    }

                    if ($order_main_table) {
                        $order_item = OrderItem::create([
                            'user_id' => $user->id,
                            'order_id' => $order_main_table->id,
                            'webinar_id' => $itemId ?? null,
                            'installment_payment_id' => $installmentPayment->id ?? null,
                            'installment_type' => ($status == 'part') ? $status : null,
                            'discount_id' => !empty($discountId) ? $discountId : ($discountCoupon ? $discountCoupon->id : null),
                            'amount' => $amount ?? $installment->getUpfront($order->getItemPrice()),
                            'total_amount' => $amount ?? $installment->getUpfront($order->getItemPrice()),
                            'tax' => 0,
                            'tax_price' => 0,
                            'commission' => 0,
                            'commission_price' => 0,
                            'product_delivery_fee' => 0,
                            'discount' => $totalDiscount,
                            'created_at' => time(),
                        ]);

                        session()->put('order_id1', $order_main_table->id);
                        $data['order_id'] = $order_main_table->id;
                    }

                    $channelManager = ChannelManager::makeChannel($paymentChannel);
                    $order = $channelManager->verifyBackgroundProccess($data);

                    $sales_account = new PaymentController();
                    $sales_account->paymentOrderAfterVerifyBackgroundProccess($order);

                    $this->shortPaymentSectionBackgroundProcess($data, $user->id, $itemId);

                    return true;

                } else {
                    if ($installment->needToVerify()) {
                        sendNotification("installment_verification_request_sent", $notifyOptions, $order->user_id);
                        sendNotification("admin_installment_verification_request_sent", $notifyOptions, 1);
                        return false;
                    } else {
                        sendNotification("approve_installment_verification_request", $notifyOptions, $order->user_id);
                        return $this->handleOpenOrder($item, $productOrder);
                    }
                }

            } else {

                $sale = Sale::where('buyer_id', $user->id)
                    ->where('webinar_id', $itemId)
                    ->first();

                if ($sale) {
                    Sale::where('id', $sale->id)->update(['total_amount' => $part_amount]);

                    $order = Order::where('id', $sale->order_id)->first();
                    Order::where('id', $order->id)->update(['total_amount' => $part_amount]);

                    $orderItem = OrderItem::where('order_id', $order->id)->first();
                    OrderItem::where('id', $orderItem->id)->update([
                        'total_amount' => $part_amount,
                        'installment_type' => 'part'
                    ]);

                    Accounting::where('order_item_id', $orderItem->id)
                        ->where('user_id', $user->id)
                        ->update(['amount' => $part_amount]);

                    $installmentPayment = InstallmentOrderPayment::where('sale_id', $sale->id)->first();
                    if ($installmentPayment && $installmentPayment->amount <= $part_amount) {
                        InstallmentOrderPayment::where('sale_id', $sale->id)
                            ->update(['status' => 'paid']);
                    }

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
                                                    $sales_account=new PaymentController();
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
                                                    'via_payment'=>"auctus"
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
                                                    $sales_account=new PaymentController();
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

            return $response=['status' => 'faild', 'message' => 'Data not received!'];
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
                                                    $sales_account=new PaymentsController();
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
                                                    $sales_account=new PaymentsController();
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

                    WebinarPartPayment::Create([
                        'user_id' => $user->id,
                        'installment_id' => $installments[0]->id,
                        'webinar_id' => $webinarId,
                        'amount' => $remainingPaidAmount,
                    ]);
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
                                                $sales_account=new PaymentsController();
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
                                                    $sales_account=new PaymentsController();
                                                    $sales_account->paymentOrderAfterVerify($order_main_table);

                                                }

                                                $paidAmount -=$order->item_price*$steps->amount/100;

                                            }
                            }
                        }
                    }

            }
            }
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
                                print_r($WebinarPartPayment1->amount);
                                echo "<br>";
                                die();
                                $InstallmentOrder =InstallmentOrder::where([
                                    'installment_id' => $WebinarPartPayment->installment_id,
                            'user_id' => $WebinarPartPayment->user_id,
                            'webinar_id' => $WebinarPartPayment->webinar_id,
                            'status' => 'open',
                            ])->first();

                            $installmentPayment = InstallmentOrderPayment :: where('installment_order_id', $InstallmentOrder->id)
                            ->first();

                            $accounting = Accounting::where('installment_payment_id', $installmentPayment->id)
                            ->first();

                            Accounting::where('id', $accounting->id)
                            ->update([
                                'amount' => $WebinarPartPayment->amount,
                            ]);

                    $sale =  Sale :: where('installment_payment_id',$installmentPayment->id)
                    ->first();

                    Sale ::  where('id',$sale->id)
                    ->update([
                        'total_amount' => $WebinarPartPayment->amount,
                        'status' => 'part',
                    ]);

                    $OrderItem = OrderItem :: where('id',$accounting->order_item_id)
                        ->first();

                        OrderItem :: where('id',$OrderItem->id)
                        ->update([
                            'total_amount' => $WebinarPartPayment->amount,
                            'installment_type' => 'part',
                        ]);

                        $order = Order :: where('id',$sale->order_id)
                        ->first();

                        Order :: where('id',$order->id)
                        ->update([
                            'total_amount' => $WebinarPartPayment->amount,
                        ]);

                        session(['success'=>true]);

                        $itemId = $WebinarPartPayment->webinar_id;
                        $itemType = 'course';
                        $totalDiscount= $WebinarPartPayment->webinar_id;
                        $discountId= $request->get('discount_id');
                        $installmentId= $WebinarPartPayment->installment_id;

                        $payment_type ="";
                        if($request->input('payment_type')){
                            $payment_type = $request->input('payment_type');
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
                                                        $sales_account=new PaymentsController();
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
                                                    $sales_account=new PaymentsController();
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

            }
            }
        } catch (\Exception $e) {
            \Log::error('shortPaymentSection1 error: ' . $e->getMessage(), [
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
                ["Vinod Nalamwar", "vinod.nalamwar2015@gmail.com",  9822200770, 59000, 9387, 49613],
            ];
            $user = apiAuth();
            foreach ($data as $data1){

                session(['success'=>true]);

                $itemId = 2070;
                $itemType = 'course';
                $totalDiscount= 64900 - $data1[3];
                $discountId= 0;
                $installmentId= 16;
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
                        $sales_account=new PaymentsController();
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

                                WebinarPartPayment::Create([
                                    'user_id' => $user->id,
                                    'installment_id' => $installmentId,
                                    'webinar_id' => $itemId,
                                    'amount' => $amount,
                                ]);

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
                            $sales_account=new PaymentsController();
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
            $itemType = 'course';
            $courses = Webinar::where('id',$itemId)->where('status', 'active')
            ->first();
            $item = $this->getItem($itemId, $itemType, $user);
            $itemPrice = $item->getPrice();
            $totalDiscount= $itemPrice - $data1['amount'];
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
                    $sales_account=new PaymentsController();
                    $sales_account->paymentOrderAfterVerify($order_main_table);
                    return $response=['status' => 'success', 'message' => 'Data received successfully!'];
                }
            }

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
                                        'created_at' => time(),
                                     ]);
                                    }

                    $part_amount_status=true;
                    if (!empty($payment_type)) {
                        $status = $payment_type;

                        WebinarPartPayment::Create([
                        'user_id' => $user->id,
                        'installment_id' => $installmentId,
                        'webinar_id' => $itemId,
                        'amount' => $amount,
                    ]);
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
                                                    $sales_account=new PaymentsController();
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
                                                    $sales_account=new PaymentsController();
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
                }
                    $sales_account=new PaymentsController();
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

    public function fullAccessByoctus(Request $request)
        {
        try {
            $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'mobile' => 'required|max:15',
                    'course' => 'required|string',
                    'amount' => 'required|numeric',
                    'paid_amount' => 'required|numeric',
                    'pending_amount' => 'required|numeric',

                ]);

                $data1 =$request->all();
                $name = $data1['name'];
                $email = $data1['email'];
                $contact = $data1['mobile'];
                $via_payment = "auctus";

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
                $itemId = $data1['course'] ?? null;

                if ($itemId) {
                    if (is_string($itemId) && strpos($itemId, '~') !== false) {
                        $parts = explode("~", $itemId);
                        $itemId = end($parts);
                    }
                    $itemId = (int) $itemId;
                }

                $itemType = 'course';
                $courses = Webinar::where('id',$itemId)->where('status', 'active')
                ->first();
                $item = $this->getItem($itemId, $itemType, $user);
                if ($item && $item->getPrice()) {
                   $itemPrice = $item->getPrice();
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Installment is not available'
                    ]);
                }

                $totalDiscount= $itemPrice - $data1['amount'];
                $discountId= 0;

                $installmentPlans = new InstallmentPlans();
                $installments = $installmentPlans->getPlans('courses', $courses->id, $courses->type, $courses->category_id, $courses->teacher_id);
                if ($installments->isNotEmpty()) {
                    $installmentId = $installments[0]->id;
                } else {

                    return response()->json([
                        'success' => false,
                        'message' => 'No installment available'
                    ]);
                }
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
                                    'via_payment' => "auctus",
                                    'tax_price' => 0,
                                    'commission' => 0,
                                    'commission_price' => 0,
                                    'product_delivery_fee' => 0,
                                    'discount' => $totalDiscount,
                                    'created_at' => time(),
                                ]);
                            }

                        $sales_account=new PaymentsController();
                        $sales_account->paymentOrderAfterVerify($order_main_table);
                        return $response=['status' => 'success', 'message' => 'Data received successfully!1'];
                    }
                }

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
                                            'created_at' => time(),
                                         ]);
                                        }

                        $part_amount_status=true;
                        if (!empty($payment_type)) {
                            $status = $payment_type;

                            WebinarPartPayment::Create([
                            'user_id' => $user->id,
                            'installment_id' => $installmentId,
                            'webinar_id' => $itemId,
                            'amount' => $amount,
                            'via_payment' => "auctus"
                        ]);
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
                                                            'via_payment' => "auctus",
                                                            'tax_price' => 0,
                                                            'commission' => 0,
                                                            'commission_price' => 0,
                                                            'product_delivery_fee' => 0,
                                                            'discount' => $totalDiscount,
                                                            'created_at' => time(),
                                                        ]);
                                                        }
                                                        $sales_account=new PaymentsController();
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
                                                        'via_payment'=>$via_payment
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
                                                          'via_payment' => "auctus",
                                                          'tax_price' => 0,
                                                          'commission' => 0,
                                                          'commission_price' => 0,
                                                          'product_delivery_fee' => 0,
                                                          'discount' => $totalDiscount,
                                                          'created_at' => time(),
                                                        ]);
                                                        }
                                                        $sales_account=new PaymentsController();
                                                        $sales_account->paymentOrderAfterVerify($order_main_table);

                                                    }

                                                    $paidAmount -=$order->item_price*$steps->amount/100;

                                                }
                                }
                            }
                        }

                        return $response=['status' => 'success', 'message' => 'Data received successfully!2'];
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
                            'via_payment' => "auctus",
                            'tax_price' => 0,
                            'commission' => 0,
                            'commission_price' => 0,
                            'product_delivery_fee' => 0,
                            'discount' => $totalDiscount,
                            'created_at' => time(),
                        ]);
                    }
                        $sales_account=new PaymentsController();
                       $sales_account->paymentOrderAfterVerify($order_main_table);
                    $this->shortPaymentSection($request,$user->id,$itemId);

                    return $response=['status' => 'success', 'message' => 'Data received successfully!3'];

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
                            'via_payment'=>$via_payment

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

                        return $response=['status' => 'success', 'message' => 'Data received successfully!'];

                    }
                }
            }
                }

                return $response=['status' => 'success', 'message' => 'Data received successfully!'];
            }

            return $response=['status' => 'warning', 'message' => 'The selected course has already been purchased by this user.'];
        } catch (\Exception $e) {
            \Log::error('fullAccessByoctus error: ' . $e->getMessage(), [
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

               return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Installments not found.'
            ], 404);
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

    public function show($orderId)
    {
        try {
            $user = apiAuth();

            $order = InstallmentOrder::query()
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->with([
                    'installment' => function ($query) {
                        $query->with([
                            'steps' => function ($query) {
                                $query->orderBy('deadline', 'asc');
                            }
                        ]);
                    }
                ])
                ->first();

            if (!empty($order) and !in_array($order->status, ['refunded', 'canceled'])) {

                $getRemainedInstallments = $this->getRemainedInstallments($order);
                $getOverdueOrderInstallments = $this->getOverdueOrderInstallments($order);

                $totalParts = $order->installment->steps->count();
                $remainedParts = $getRemainedInstallments['total'];
                $remainedAmount = $getRemainedInstallments['amount'];
                $overdueAmount = $getOverdueOrderInstallments['amount'];

                $data = [
                    'pageTitle' => trans('update.installments'),
                    'totalParts' => $totalParts,
                    'remainedParts' => $remainedParts,
                    'remainedAmount' => $remainedAmount,
                    'overdueAmount' => $overdueAmount,
                    'order' => $order,
                    'payments' => $order->payments,
                    'installment' => $order->installment,
                    'itemPrice' => $order->getItemPrice(),
                ];
                 return apiResponse2(1, 'retrieved', 'data retrieved successfully', $data);

            }

            return response()->json([
                'status' => 'error',
                'code' => 404,
                'message' => 'Installments not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    private function getRemainedInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->whereHas('sale', function ($query) {
                    $query->whereNull('refund_at');
                })
                ->first();

            if (empty($payment)) {
                $total += 1;
                $amount += $step->getPrice($itemPrice);
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }
    private function getOverdueOrderInstallments($order)
    {
        $total = 0;
        $amount = 0;

        $time = time();
        $itemPrice = $order->getItemPrice();

        foreach ($order->installment->steps as $step) {
            $dueAt = ($step->deadline * 86400) + $order->created_at;

            if ($dueAt < $time) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->first();

                if (empty($payment)) {
                    $total += 1;
                    $amount += $step->getPrice($itemPrice);
                }
            }
        }

        return [
            'total' => $total,
            'amount' => $amount,
        ];
    }

    private function getUpcomingInstallment($order)
    {
        $result = null;
        $deadline = 0;

        foreach ($order->installment->steps as $step) {
            $payment = InstallmentOrderPayment::query()
                ->where('installment_order_id', $order->id)
                ->where('step_id', $step->id)
                ->where('status', 'paid')
                ->first();

            if (empty($payment) and ($deadline == 0 or $deadline > $step->deadline)) {
                $deadline = $step->deadline;
                $result = $step;
            }
        }

        return $result;
    }

    private function getOverdueInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            if ($order->checkOrderHasOverdue()) {
                $count += 1;
            }
        }

        return $count;
    }

    private function getFinishedInstallments($user)
    {
        $orders = InstallmentOrder::query()
            ->where('user_id', $user->id)
            ->where('installment_orders.status', 'open')
            ->get();

        $count = 0;

        foreach ($orders as $order) {
            $steps = $order->installment->steps;
            $paidAllSteps = true;

            foreach ($steps as $step) {
                $payment = InstallmentOrderPayment::query()
                    ->where('installment_order_id', $order->id)
                    ->where('step_id', $step->id)
                    ->where('status', 'paid')
                    ->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    })
                    ->first();

                if (empty($payment)) {
                    $paidAllSteps = false;
                }
            }

            if ($paidAllSteps) {
                $count += 1;
            }
        }

        return $count;
    }
        }