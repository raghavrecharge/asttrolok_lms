<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\RegionsDataByUser;
use App\Mixins\Cashback\CashbackRules;
use App\Models\Cart;
use App\Models\CartInstallment;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;
use App\Models\Webinar;
use App\User;
use App\Mixins\Cart\CartItemInfo;
use App\Models\DiscountCourse;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    use RegionsDataByUser;

    public function index()
    {
        try {
            $user = auth()->user();
            $carts = Cart::where('creator_id', $user->id)
                ->with([
                    'user',
                    'webinar',
                    'installmentPayment',
                    'reserveMeeting' => function ($query) {
                        $query->with([
                            'meeting',
                            'meetingTime'
                        ]);
                    },
                    'ticket',
                    'productOrder' => function ($query) {
                        $query->whereHas('product');
                        $query->with(['product']);
                    }
                ])
                ->get();

            if (!empty($carts) and !$carts->isEmpty()) {
                $calculate = $this->calculatePrice($carts, $user);

                $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

                $deliveryEstimateTime = 0;

                if (!empty($hasPhysicalProduct) and count($hasPhysicalProduct)) {
                    foreach ($hasPhysicalProduct as $physicalProductCart) {
                        if (!empty($physicalProductCart->productOrder) and
                            !empty($physicalProductCart->productOrder->product) and
                            !empty($physicalProductCart->productOrder->product->delivery_estimated_time) and
                            $physicalProductCart->productOrder->product->delivery_estimated_time > $deliveryEstimateTime
                        ) {
                            $deliveryEstimateTime = $physicalProductCart->productOrder->product->delivery_estimated_time;
                        }
                    }
                }

                if (!empty($calculate)) {

                    $totalCashbackAmount = $this->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                    $data = [
                        'pageTitle' => trans('public.cart_page_title'),
                        'user' => $user,
                        'carts' => $carts,
                        'subTotal' => $calculate["sub_total"],
                        'totalDiscount' => $calculate["total_discount"],
                        'tax' => $calculate["tax"],
                        'taxPrice' => $calculate["tax_price"],
                        'total' => $calculate["total"],
                        'productDeliveryFee' => $calculate["product_delivery_fee"],
                        'taxIsDifferent' => $calculate["tax_is_different"],
                        'userGroup' => !empty($user->userGroup) ? $user->userGroup->group : null,
                        'hasPhysicalProduct' => (count($hasPhysicalProduct) > 0),
                        'deliveryEstimateTime' => $deliveryEstimateTime,
                        'totalCashbackAmount' => $totalCashbackAmount,
                    ];

                    $data = array_merge($data, $this->getLocationsData($user));

                    $agent = new Agent();
                    if ($agent->isMobile()){
                            return view(getTemplate() . '.cart.cart', $data);
                    }else{
                        return view('web.default2' . '.cart.cart', $data);
                    }

                }
            }

            return redirect('/');
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
 public function cart1(Request $request, $carts = null)
    {
        try {
            $data1=[];

            $user = auth()->user();

            if(empty($user)){
               $user = User::where('id','2550')->first();
                if (session('cart_id')) {

                 $carts = Cart::where('id',session('cart_id'))->orwhere('cart_id',session('cart_id'))->get();

                 }
            }

            if (empty($carts)) {
                $carts = Cart::where('creator_id', $user->id)
                    ->get();
            }

            $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

            $discountId = $request->input('discount_id');

            $paymentChannels = PaymentChannel::where('status', 'active')->get();

            $discountCoupon = Discount::where('id', $discountId)->first();

            if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
                $discountCoupon = null;
            }

            if (!empty($carts)) {

                $calculate = $this->calculatePrice($carts, $user);

                $order = $this->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon);

                if (!empty($discountCoupon)) {
                    $totalCouponDiscount = $this->handleDiscountPrice($discountCoupon, $carts, $calculate['sub_total']);
                    $calculate['total_discount'] += $totalCouponDiscount;
                    $calculate["total"] = $calculate["total"] - $totalCouponDiscount;
                }

                if (count($hasPhysicalProduct) > 0) {
                    $this->updateProductOrders($request, $carts, $user);
                }

                if (!empty($order) and $order->total_amount > 0) {
                    $razorpay = false;
                    foreach ($paymentChannels as $paymentChannel) {
                        if ($paymentChannel->class_name == 'Razorpay' and in_array(currency(), $paymentChannel->currencies)) {
                            $razorpay = true;
                        }
                    }

                    $totalCashbackAmount = $this->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                    $data1 = [
                        'paymentChannels' => $paymentChannels,
                        'order' => $order,
                        'count' => $carts->count(),
                        'userCharge' => $user->getAccountingCharge(),
                        'razorpay' => $razorpay,
                    ];

                }
            }

            $user = auth()->user();
            if(!empty($user)){
            $carts = Cart::where('creator_id', $user->id)
                ->with([
                    'user',
                    'webinar',
                    'installmentPayment',
                    'reserveMeeting' => function ($query) {
                        $query->with([
                            'meeting',
                            'meetingTime'
                        ]);
                    },
                    'ticket',
                    'productOrder' => function ($query) {
                        $query->whereHas('product');
                        $query->with(['product']);
                    }
                ])
                ->get();
            }else{

                $user = User::where('id','2550')->first();
               $carts = Cart::where('id',session('cart_id'))->orwhere('cart_id',session('cart_id'))->get();
            }

            if (!empty($carts) and !$carts->isEmpty()) {
                $calculate = $this->calculatePrice($carts, $user);

                $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

                $tax_international =0;
                $delivery_fee_international =0;
                foreach ($carts as $cart) {
                    if(isset($cart->bundle->bundleWebinars)){
                        foreach ($cart->bundle->bundleWebinars as $bundleWebinar){
                        if($bundleWebinar->product_id){

                        if(session('country')==101){
                            $tax_indian= $bundleWebinar->product->tax;
                            $delivery_fee_indian= $bundleWebinar->product->delivery;

                        }else{
                            $tax_international= $bundleWebinar->product->tax_international;
                            $delivery_fee_international= $bundleWebinar->product->delivery_fee_international;

                        }
                        $bundleHasProduct = 1;
                    }}}

                    if(isset($cart->productOrder)){

                        if(session('country')==101){
                            $tax_indian= $cart->productOrder->product->tax;
                            $delivery_fee_indian= $cart->productOrder->product->delivery;

                        }else{
                            $tax_international= $cart->productOrder->product->tax_international;
                            $delivery_fee_international= $cart->productOrder->product->delivery_fee_international;

                        }
                        $bundleHasProduct = 1;
                    }

                }
                $deliveryEstimateTime = 0;

                if (!empty($hasPhysicalProduct) and count($hasPhysicalProduct)) {
                    foreach ($hasPhysicalProduct as $physicalProductCart) {
                        if (!empty($physicalProductCart->productOrder) and
                            !empty($physicalProductCart->productOrder->product) and
                            !empty($physicalProductCart->productOrder->product->delivery_estimated_time) and
                            $physicalProductCart->productOrder->product->delivery_estimated_time > $deliveryEstimateTime
                        ) {
                            $deliveryEstimateTime = $physicalProductCart->productOrder->product->delivery_estimated_time;
                        }
                    }
                }

                if (!empty($calculate)) {

                    $totalCashbackAmount = $this->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                    $course_title='';
                    foreach($carts as $cart){
                        if(isset($cart->webinar)){
                        $course_title= $course_title.$cart->webinar->title.', ';
                        }
                        if(isset($cart->bundle)){

                        $course_title= $course_title.$cart->bundle->title.', ';

                        }
                        if(isset($cart->productOrder->product)){

                        $course_title= $course_title.$cart->productOrder->product->title.', ';

                        }
                    }
                    $data = [
                        'pageTitle' => trans('public.cart_page_title'),
                        'user' => $user,
                        'carts' => $carts,
                        'subTotal' => $calculate["sub_total"],
                        'totalDiscount' => $calculate["total_discount"],
                        'tax' => $calculate["tax"],
                        'taxPrice' => $calculate["tax_price"],
                        'total' => $calculate["total"],
                        'productDeliveryFee' => $calculate["product_delivery_fee"],
                        'taxIsDifferent' => $calculate["tax_is_different"],
                        'tax_international' => $tax_international,
                        'delivery_fee_international' => $delivery_fee_international,
                        'userGroup' => !empty($user->userGroup) ? $user->userGroup->group : null,
                        'hasPhysicalProduct' => $bundleHasProduct ?? (count($hasPhysicalProduct) > 0),
                        'deliveryEstimateTime' => $deliveryEstimateTime,
                        'totalCashbackAmount' => $totalCashbackAmount,
                        'course_title' => $course_title,
                    ];

                    $data = array_merge($data, $this->getLocationsData($user));
                    $data = array_merge($data, $data1);

                    if($cartInstallment1 = CartInstallment::where('user_id', $user->id)
                                ->get()){
                    if($cartInstallment1){
                        $data['cartInstallment1'] = $cartInstallment1;
                }

                }

                $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.cart.cart1', $data);
                }else{
                    return view('web.default2' . '.cart.cart1', $data);
                }

                }

            }

            return redirect('/');
        } catch (\Exception $e) {
            \Log::error('cart1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function getItemInfo()
    {
        try {
            if (empty($this->itemInfo)) {
                $cartItemInfo = new CartItemInfo();

                $this->itemInfo = $cartItemInfo->getItemInfo($this);
            }

            return $this->itemInfo;
        } catch (\Exception $e) {
            \Log::error('getItemInfo error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

     public function couponValidate3(Request $request)
    {
        try {
            $user = auth()->user();
            if(empty($user)){

                $user = User::where('id','2550')->first();

            }
            $coupon = $request->get('coupon');
            $user_id = $request->get('user_id');
            session(['coupon' => $coupon]);
            $discountCoupon = Discount::where('code', $coupon)
                ->where('status', 'active')
                ->first();

            if (!empty($discountCoupon)) {

                if(!empty($user)){

                    $checkDiscount = $discountCoupon->checkValidDiscount();
                if ($discountCoupon->source != 'meeting') {
                    session(['meeting_discount_id' => 0 ]);
                    return response()->json([
                        'status' => 422,
                        'msg' => 'Invalid code'
                    ]);
                }

                        session(['meeting_discount_id' => $discountCoupon->id]);

                        return response()->json([
                            'status' => 200,
                            'meeting_discount_id' => $discountCoupon->id,
                        ], 200);

                }else{}
            }

            return response()->json([
                'status' => 422,
                'msg' => trans('cart.coupon_invalid')
            ]);
        } catch (\Exception $e) {
            \Log::error('couponValidate3 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function couponValidate2(Request $request)
    {
        try {
            $user = auth()->user();
            $coupon = $request->get('coupon');
            $web_id1 = $request->get('item_id');

             $webinarController = new WebinarController();
             session(['discountCouponId' => 0]);
             session(['discountCoupon' => '']);

            $discountCoupon = Discount::where('code', $coupon)
                ->where('status', 'active')
                ->first();

            if (!empty($discountCoupon)) {
                $checkDiscount = $discountCoupon->checkValidDiscount1($web_id1);

                if ($checkDiscount != 'ok') {

            session(['discountCoupon' => 'no']);
                    return $webinarController->directPayment($request);
                }

            session(['discountCouponId' => $discountCoupon->id]);
            session(['discountCoupon' => $coupon]);

                        return $webinarController->directPayment($request);
            return $coupon;
            }else{
                if($coupon)
            session(['discountCoupon' => 'no']);

            }

                    return $webinarController->directPayment($request);
        } catch (\Exception $e) {
            \Log::error('couponValidate2 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function couponValidate1(Request $request)
    {
        try {
            $user = auth()->user();
            $coupon     = $request->get('coupon');
            $web_id1    = $request->get('web_id1');
            $webinsta_id1 = $request->get('webinsta_id1');

            $discountCoupon = Discount::where('code', $coupon)
                ->where('status', 'active')
                ->first();

            if (!empty($discountCoupon)) {
                $checkDiscount = $discountCoupon->checkValidDiscount1($web_id1);
                if ($checkDiscount != 'ok') {
                    return response()->json([
                        'status' => 422,
                        'msg' => $checkDiscount
                    ]);
                }

                session(['discountCouponId' => $discountCoupon->id]);

                // Build price data for live DOM update so the page doesn't need to reload.
                $responseData = [
                    'status'      => 200,
                    'discount_id' => $discountCoupon->id,
                ];

                try {
                    $webinar     = \App\Models\Webinar::find($web_id1);
                    $installment = \App\Models\Installment::with('steps')->find($webinsta_id1);

                    if ($webinar && $installment) {
                        // Mirror InstallmentsController exactly:
                        //   getPrice() = effective/promotional price → used as display base for EMI
                        //   price      = raw DB price              → used for coupon discount calc
                        $effectivePrice  = (float) ($webinar->getPrice() ?? 0);
                        $rawPrice        = (float) ($webinar->price ?? 0);
                        $percent         = (float) ($discountCoupon->percent ?? 0);
                        $discountAmount  = ($percent > 0) ? ($rawPrice * $percent / 100) : 0;
                        $originalPrice   = $effectivePrice;   // alias kept for labels below
                        $discountedPrice = max(0, $effectivePrice - $discountAmount);

                        // Per-step deadline labels with the discounted base price
                        $steps = [];
                        foreach ($installment->steps as $step) {
                            $steps[] = $step->getDeadlineTitle($discountedPrice);
                        }

                        $responseData['percent']          = $percent;
                        $responseData['original_price']   = handlePrice($originalPrice);
                        $responseData['discounted_price'] = handlePrice($discountedPrice);
                        $responseData['total_payments']   = handlePrice($installment->totalPayments($discountedPrice));
                        $responseData['savings']          = handlePrice($discountAmount);
                        $responseData['steps']            = $steps;

                        if ($installment->upfront) {
                            $upfrontOriginal         = $installment->getUpfront($originalPrice);
                            $upfrontDiscounted       = $installment->getUpfront($discountedPrice);
                            $upfrontSuffix           = ($installment->upfront_type === 'percent') ? " ({$installment->upfront}%)" : '';
                            $responseData['upfront']          = handlePrice($upfrontDiscounted);
                            $responseData['upfront_label']    = trans('update.amount_upfront', ['amount' => handlePrice($upfrontDiscounted)]) . $upfrontSuffix;
                            $responseData['upfront_original'] = $upfrontOriginal;
                            $responseData['upfront_savings']  = max(0, $upfrontOriginal - $upfrontDiscounted);
                        }
                    }
                } catch (\Exception $priceErr) {
                    // Price calculation is best-effort; coupon is still valid
                    \Log::warning('couponValidate1: price calculation failed', [
                        'error' => $priceErr->getMessage(),
                    ]);
                }

                return response()->json($responseData, 200);
            }

            return response()->json([
                'status' => 422,
                'msg' => trans('cart.coupon_invalid'),
            ]);
        } catch (\Exception $e) {
            \Log::error('couponValidate1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function setSession(Request $request)
    {
        try {
            $sessionData = [];

            if ($request->has('country')) {
                $sessionData['country'] = $request->country;
            }

            if ($request->has('tax_international')) {
                $sessionData['tax_international'] = $request->tax_international;
            }

            if ($request->has('delivery_fee_international')) {
                $sessionData['delivery_fee_international'] = $request->delivery_fee_international;
            }

            if ($request->has('country_id')) {
                $sessionData['country_id'] = $request->country_id;
            }

            session($sessionData);

            return response()->json([
                'success' => true,
                'stored_data' => $sessionData
            ]);
        } catch (\Exception $e) {
            \Log::error('setSession error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    public function unsetSession(Request $request)
    {
        try {
            session()->forget('tax_international');
            session()->forget('delivery_fee_international');

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('unsetSession error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function couponValidate(Request $request)
    {
        try {
            $user = auth()->user();
            if(empty($user)){

                $user = User::where('id','2550')->first();

            }
            $coupon = $request->get('coupon');
            session(['coupon' => $coupon]);
            $discountCoupon = Discount::where('code', $coupon)
                ->where('status', 'active')
                ->first();

            if (!empty($discountCoupon)) {

                if(!empty($user)){

                    $checkDiscount = $discountCoupon->checkValidDiscount();
                if ($checkDiscount != 'ok') {
                    return response()->json([
                        'status' => 422,
                        'msg' => $checkDiscount
                    ]);
                }

               if(session('cart_id')){
                    $carts = Cart::where('id',session('cart_id'))->orwhere('cart_id',session('cart_id'))->get();

               }else{
                    $carts = Cart::where('creator_id', $user->id)
                    ->get();
               }

                if (!empty($carts) and !$carts->isEmpty()) {
                    $calculate = $this->calculatePrice($carts, $user, $discountCoupon);

                    if (!empty($calculate)) {
                        session(['discount_id' => $discountCoupon->id]);
                        session(['total_discount' => handlePrice($calculate["total_discount"])]);
                        session(['total_tax' => handlePrice($calculate["tax_price"])]);
                        session(['total_amount' => handlePrice($calculate["total"])]);
                        return response()->json([
                            'status' => 200,
                            'discount_id' => $discountCoupon->id,
                            'total_discount' => handlePrice($calculate["total_discount"]),
                            'total_tax' => handlePrice($calculate["tax_price"]),
                            'product_delivery_fee' => handlePrice($calculate["product_delivery_fee"]),
                            'total_amount' => handlePrice($calculate["total"]),
                        ], 200);
                    }
                }
                }else{

                }
            }

            return response()->json([
                'status' => 422,
                'msg' => trans('cart.coupon_invalid')
            ]);
        } catch (\Exception $e) {
            \Log::error('couponValidate error: ' . $e->getMessage(), [
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
                    $totalWebinarsAmount += $webinar->getPrice();

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalWebinarsAmount > $discount->amount) ? $discount->amount : $totalWebinarsAmount;

            } else {
                $totalDiscount = ($totalWebinarsAmount > 0) ? (int) round($totalWebinarsAmount * $percent / 100, 0, PHP_ROUND_HALF_UP) : 0;
            }
        } elseif ($discount->source == Discount::$discountSourceBundle) {
            $totalBundlesAmount = 0;
            $bundleOtherDiscounts = 0;
            $discountBundlesIds = $discount->discountBundles()->pluck('bundle_id')->toArray();

            foreach ($carts as $cart) {
                $bundle = $cart->bundle;
                if (!empty($bundle) and in_array($bundle->id, $discountBundlesIds)) {
                    $totalBundlesAmount += $bundle->getPrice();

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalBundlesAmount > $discount->amount) ? $discount->amount : $totalBundlesAmount;

            } else {
                $totalDiscount = ($totalBundlesAmount > 0) ? (int) round($totalBundlesAmount * $percent / 100, 0, PHP_ROUND_HALF_UP) : 0;
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
                $totalDiscount = ($totalProductsAmount > 0) ? (int) round($totalProductsAmount * $percent / 100, 0, PHP_ROUND_HALF_UP) : 0;
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
                $totalDiscount = ($totalMeetingAmount > 0) ? (int) round($totalMeetingAmount * $percent / 100, 0, PHP_ROUND_HALF_UP) : 0;
            }
        } elseif ($discount->source == Discount::$discountSourceCategory) {
            $totalCategoriesAmount = 0;
            $categoriesOtherDiscounts = 0;

            $categoriesIds = ($discount->discountCategories) ? $discount->discountCategories()->pluck('category_id')->toArray() : [];

            foreach ($carts as $cart) {
                $webinar = $cart->webinar;

                if (!empty($webinar) and in_array($webinar->category_id, $categoriesIds)) {
                    $totalCategoriesAmount += $webinar->getPrice();

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalCategoriesAmount > $discount->amount) ? $discount->amount : $totalCategoriesAmount;

            } else {
                $totalDiscount = ($totalCategoriesAmount > 0) ? (int) round($totalCategoriesAmount * $percent / 100, 0, PHP_ROUND_HALF_UP) : 0;
            }
        } else {
            $totalCartAmount = 0;
            $totalCartOtherDiscounts = 0;

            foreach ($carts as $cart) {
                $webinar = $cart->webinar;
                $reserveMeeting = $cart->reserveMeeting;

                if (!empty($webinar)) {
                    $totalCartAmount += $webinar->getPrice();

                }

                if (!empty($reserveMeeting)) {
                    $totalCartAmount += $reserveMeeting->paid_amount;

                }
            }

            if ($discount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalDiscount = ($totalCartAmount > $discount->amount) ? $discount->amount : $totalCartAmount;

            } else {
                $totalDiscount = ($totalCartAmount > 0) ? (int) round($totalCartAmount * $percent / 100, 0, PHP_ROUND_HALF_UP) : 0;
            }
        }

        if ($discount->discount_type != Discount::$discountTypeFixedAmount and !empty($discount->max_amount) and $totalDiscount > $discount->max_amount) {
            $totalDiscount = $discount->max_amount;
        }

        return $totalDiscount;
    }

    private function productDeliveryFeeBySeller($carts)
    {
        $productFee = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;

                if(session('delivery_fee_international')){
                    isset($productFee[$product->creator_id])?$productFee[$product->creator_id] += $product->delivery_fee_international:$productFee[$product->creator_id] = $product->delivery_fee_international;
                }else{
                    if (!empty($product->delivery_fee)) {
                        if (!empty($productFee[$product->creator_id]) and $productFee[$product->creator_id] < $product->delivery_fee) {
                            isset($productFee[$product->creator_id])?$productFee[$product->creator_id] += $product->delivery_fee:$productFee[$product->creator_id] = $product->delivery_fee;
                        } else if (empty($productFee[$product->creator_id])) {
                            isset($productFee[$product->creator_id])?$productFee[$product->creator_id] += $product->delivery_fee:$productFee[$product->creator_id] = $product->delivery_fee;
                        }
                    }
                }
            }
            if(!empty($cart->bundle) and !empty($cart->bundle->bundleWebinars)){
                    foreach ($cart->bundle->bundleWebinars as $bundleWebinar){
                        if($bundleWebinar->product_id){
                            $product = $bundleWebinar->product;
                            if(session('delivery_fee_international')){
                                isset($productFee[$product->creator_id])?$productFee[$product->creator_id] += $product->delivery_fee_international:$productFee[$product->creator_id] = $product->delivery_fee_international;
                            }else{
                                if (!empty($product->delivery_fee)) {
                                    if (!empty($productFee[$product->creator_id]) and $productFee[$product->creator_id] < $product->delivery_fee) {
                                        isset($productFee[$product->creator_id])?$productFee[$product->creator_id] += $product->delivery_fee:$productFee[$product->creator_id] = $product->delivery_fee;
                                    } else if (empty($productFee[$product->creator_id])) {
                                        isset($productFee[$product->creator_id])?$productFee[$product->creator_id] += $product->delivery_fee:$productFee[$product->creator_id] = $product->delivery_fee;
                                    }
                                }
                            }
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
    public function checkout(Request $request, $carts = null)
    {
        try {
            $user = auth()->user();

            if(empty($user)){
                $user = User::where('id','2550')->first();
            }

            if (empty($carts)) {
                $carts = Cart::where('creator_id', $user->id)
                    ->get();
            }

            $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

            $this->validate($request, [
                'country_id' => Rule::requiredIf(count($hasPhysicalProduct) > 0),
                'province_id' => Rule::requiredIf(count($hasPhysicalProduct) > 0),
                'city_id' => Rule::requiredIf(count($hasPhysicalProduct) > 0),
                'district_id' => Rule::requiredIf(count($hasPhysicalProduct) > 0),
                'address' => Rule::requiredIf(count($hasPhysicalProduct) > 0),
            ]);

            $discountId = $request->input('discount_id');
            $coupon = $request->input('coupon');

                $paymentChannels = PaymentChannel::where('status', 'active')->get();

            $discountCoupon = Discount::where('id', $discountId)->first();
            $discountCoupon = Discount::where('code', $coupon)->first();

            if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {

                $discountCoupon = null;
            }

            if (!empty($carts) and !$carts->isEmpty()) {
                $calculate = $this->calculatePrice($carts, $user);

                $order = $this->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon);

                if (!empty($discountCoupon)) {

                    $totalCouponDiscount = $this->handleDiscountPrice($discountCoupon, $carts, $calculate['sub_total']);
                    $calculate['total_discount'] += $totalCouponDiscount;
                    $calculate["total"] = $calculate["total"] - $totalCouponDiscount;
                }

                if (count($hasPhysicalProduct) > 0) {
                    $this->updateProductOrders($request, $carts, $user);
                }

                if (!empty($order) and $order->total_amount > 0) {
                    $razorpay = false;
                    foreach ($paymentChannels as $paymentChannel) {
                        if ($paymentChannel->class_name == 'Razorpay' and in_array(currency(), $paymentChannel->currencies)) {
                            $razorpay = true;
                        }
                    }

                    $totalCashbackAmount = $this->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                // Get wallet balance for logged-in users
                $walletBalance = 0;
                if (auth()->check()) {
                    $walletBalance = app(\App\Services\PaymentEngine\WalletService::class)->balance(auth()->id());
                }

                    $data = [
                        'pageTitle' => trans('public.checkout_page_title'),
                        'paymentChannels' => $paymentChannels,
                        'carts' => $carts,
                        'subTotal' => $calculate["sub_total"],
                        'totalDiscount' => $calculate["total_discount"],
                        'tax' => $calculate["tax"],
                        'taxPrice' => $calculate["tax_price"],
                        'total' => $calculate["total"],
                        'userGroup' => $user->userGroup ? $user->userGroup->group : null,
                        'order' => $order,
                        'count' => $carts->count(),
                        'userCharge' => $user->getAccountingCharge(),
                        'razorpay' => $razorpay,
                        'totalCashbackAmount' => $totalCashbackAmount,
                        'walletBalance' => $walletBalance,
                    ];

                    $agent = new Agent();
                    if ($agent->isMobile()){
                        return view(getTemplate() . '.cart.payment', $data);
                    }else{
                        return view('web.default2' . '.cart.payment', $data);
                    }

                } else {
                    return $this->handlePaymentOrderWithZeroTotalAmount($order);
                }
            }

            return redirect('/cart');
        } catch (\Exception $e) {
            \Log::error('checkout error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function updateProductOrders(Request $request, $carts, $user)
    {
        $data = $request->all();

        foreach ($carts as $cart) {
            if (!empty($cart->product_order_id)) {
                ProductOrder::where('id', $cart->product_order_id)
                    ->where('buyer_id', $user->id)
                    ->update([
                        'message_to_seller' => 'ok',
                    ]);
            }
        }

        $user->update([
            'country_id' => $data['country_id'] ?? $user->country_id,
            'province_id' => $data['province_id'] ?? $user->province_id,
            'city_id' => $data['city_id'] ?? $user->city_id,
            'district_id' => $data['district_id'] ?? $user->district_id,
            'address' => $data['address'] ?? $user->address,
        ]);
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
                if(isset($cart->bundle->bundleWebinars)){
                    foreach ($cart->bundle->bundleWebinars as $bundleWebinar){
                        if($bundleWebinar->product_id){
                            $product = $bundleWebinar->product;

                            if (!empty($product) and !empty($productsFee[$product->creator_id])) {
                                $productDeliveryFee = $productsFee[$product->creator_id];

                            }
                        }

                    }

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

     public function createOrderAndOrderItems1($carts, $calculate, $user, $discountCoupon = null)
    {
        try {
            $totalCouponDiscount = 0;
            $installment_payment_id=0;

            if (!empty($discountCoupon)) {
                $totalCouponDiscount = $this->handleDiscountPrice($discountCoupon, $carts, $calculate['sub_total']);
            }

            return $order;
        } catch (\Exception $e) {
            \Log::error('createOrderAndOrderItems1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
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
            $taxCount = 0;
            if(!empty($cart->bundle_id)){
                if(isset($cart->bundle->bundleWebinars)){
                        foreach ($cart->bundle->bundleWebinars as $bundleWebinar){

                            if($bundleWebinar->product_id){
                                $product = $bundleWebinar->product;

                                if (!empty($product)) {
                                    $price = ($product->price);
                                    $discount = $product->getDiscountPrice();

                                    $commission = $product->getCommission();
                                    $productTax = session('tax_international') ?? $product->getTax();
                                    $priceWithoutDiscount = $price - $discount;

                                    $taxIsDifferent = ($taxIsDifferent and $tax != $productTax);

                                    $tax = $productTax;
                                    if ($productTax > 0 and $priceWithoutDiscount > 0) {
                                        $taxPrice += $priceWithoutDiscount * $productTax / 100;
                                    }

                                    if ($commission > 0) {
                                        $commissionPrice += $priceWithoutDiscount > 0 ? $priceWithoutDiscount * $commission / 100 : 0;
                                    }

                                    $taxCount = 1;
                                    $discount=0;
                                    $totalDiscount += $discount;
                                    $subTotal += $cart->bundle->price;
                                }

                            }

                        }

                }

            }

            if (!empty($cart->webinar_id) or (!empty($cart->bundle_id) and $taxCount == 0)) {
                $item = !empty($cart->webinar_id) ? $cart->webinar : $cart->bundle;
                if (empty($item)) {
                    return ['sub_total' => 0, 'total_discount' => 0, 'tax' => 0, 'tax_price' => 0, 'commission' => 0, 'commission_price' => 0, 'tax_is_different' => $taxIsDifferent];
                }
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
                    $productTax = session('tax_international') ?? $product->getTax();
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

    public function handleOrderPrices1($cart, $user, $taxIsDifferent = false)
    {
        try {
            if(!empty($cart['item_id'])){
              $seller = $this->getSeller1($cart);
               $commision = false;
            }else{
               $seller = $this->getSeller($cart);
               $commision = true;
            }

            $financialSettings = getFinancialSettings();

            $subTotal = 0;
            $totalDiscount = 0;
            $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
            $taxPrice = 0;
            $commissionPrice = 0;

            if (!empty($seller) && $commision) {
                $commission = $seller->getCommission();
            } else {
                $commission = 0;

                if (!empty($financialSettings) and !empty($financialSettings['commission'])) {
                    $commission = (int)$financialSettings['commission'];
                }
            }

             if(!empty($cart['item_id'])){
                 $item=Webinar::where('id',$cart['item_id'])->first();

                $price = $item->price;

                $totalDiscount += $discount ?? 0;
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

                'tax_is_different' => $taxIsDifferent,
                'item' =>$item
            ];
        } catch (\Exception $e) {
            \Log::error('handleOrderPrices1 error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handlePaymentOrderWithZeroTotalAmount($order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);

        $paymentController = new PaymentController();

        $paymentController->setPaymentAccounting($order);

        $order->update([
            'status' => Order::$paid
        ]);

        return redirect('/payments/status?order_id=' . $order->id);
    }

    private function getTotalCashbackAmount($carts, $user, $subTotal)
    {
        $amount = 0;

        if (getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $applyPerItemRules = [];

            foreach ($carts as $cart) {
                $rules = $cashbackRulesMixin->getRulesByItem($cart);

                if (!empty($rules) and count($rules)) {
                    foreach ($rules as $rule) {
                        if (empty($rule->min_amount) or $rule->min_amount <= $subTotal) {
                            if ($rule->amount_type == "fixed_amount") {
                                if ($rule->apply_cashback_per_item and $rule->apply_cashback_per_item > 0) {
                                    $amount += $rule->amount;
                                } else {
                                    $applyPerItemRules[$rule->id] = $rule;
                                }
                            } else if ($rule->amount_type == "percent") {
                                $itemOrder = $this->handleOrderPrices($cart, $user);
                                $itemPrice = $itemOrder['sub_total'];

                                if (!empty($itemOrder['total_discount'])) {
                                    $itemPrice = $itemPrice - $itemOrder['total_discount'];
                                }

                                $ruleAmount = $rule->getAmount($itemPrice);

                                if (!empty($rule->max_amount) and $rule->max_amount < $ruleAmount) {
                                    $amount += $rule->max_amount;
                                } else {
                                    $amount += $ruleAmount;
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($applyPerItemRules)) {
                foreach ($applyPerItemRules as $applyPerItemRule) {
                    $amount += $applyPerItemRule->amount;
                }
            }
        }

        return $amount;
    }

}