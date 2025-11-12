@extends(getTemplate().'.layouts.app')


@section('content')
    <section class="cart-banner1 position-relative text-center  slider-container1">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.shopping_cart') }}</h1>
        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($subTotal) . ' for ' . $carts->count() .( $carts->count()>1? ' items': ' item')}}</span>
    </section>

    <div class="container">

        @if(!empty($totalCashbackAmount))
            <div class="d-flex align-items-center mt-45 p-15 success-transparent-alert">
                <div class="success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=""></i>
                </div>

                <div class="ml-10">
                    <div class="font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class="font-12 ">{{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback',['amount' => handlePrice($totalCashbackAmount)]) }}</div>
                </div>
            </div>
        @endif
         <div class="row">
  <div class="col-12 col-lg-6 ">
      @php
            $userCurrency = currency();
            
            $invalidChannels = [];
        @endphp
        <div class=" bg-gray200 mt-30 rounded-lg border p-15">
         <h2 class="section-title">Payment Option</h2>
          <form action="/payments/payment-request" method="post" class=" mt-25">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="row">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                  
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 col-lg-6 mb-20 charge-account-radio">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                                  <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" style="display: block;    visibility: visible;">
                                  <img src="{{ config('app.img_dynamic_url') }}{{ $paymentChannel->image }}" width="120" height="60" alt="">
                                    <div>
                                    <p class="mt-30 mt-lg-10 font-weight-500 text-dark-blue">
                                        <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                    </p>
                                    <!--<p class="font-weight-500 text-dark-blue">-->
                                    <!--    Purchase with your fingertips. Look for us the next time you're paying from a mobile app, and checkout faster on thousands of mobile websites.-->
                                     
                                    <!--</p>-->
                                    </div>
                                </label>
                            </div>
                        @else
                            @php
                                $invalidChannels[] = $paymentChannel;
                            @endphp
                        @endif
                    @endforeach
                @endif

                <div class="col-12 col-lg-6 mb-20 charge-account-radio ">
                   
                    <label for="offline" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                       <input type="radio" @if(empty($userCharge) or ($order->total_amount > $userCharge)) disabled @endif name="gateway" id="offline" value="credit" style="display: block;    visibility: visible;">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/wallet.png" width="120" height="60" alt="">
                        <div>
                         <p class="mt-30 mt-lg-10 font-weight-500 text-dark-blue">
                               <span class="font-weight-bold font-14">Wallet</span>
                            <p class="mt-5">{{ handlePrice($userCharge) }}</p>
                        </p>

                        <!--<p class="font-weight-500 text-dark-blue">-->
                        <!--   Purchase with your fingertips. Look for us the next time you're paying from a mobile app, and checkout faster on thousands of mobile websites.-->
                        <!--</p>-->
                        
                        </div>

                        
                    </label>
                </div>
            </div>

            @if(!empty($invalidChannels))
                <div class="d-flex align-items-center mt-30 rounded-lg border p-15">
                    <div class="size-40 d-flex-center rounded-circle bg-gray200">
                        <i data-feather="info" class="text-gray" width="20" height="20"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.disabled_payment_gateways') }}</h4>
                        <p class="font-12 text-gray">{{ trans('update.disabled_payment_gateways_hint') }}</p>
                    </div>
                </div>

                <div class="row mt-20">
                    @foreach($invalidChannels as $invalidChannel)
                        <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                            <div class="disabled-payment-channel bg-white border rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ config('app.img_dynamic_url') }}{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

<button type="button" id="paymentSubmit" disabled class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>
            <!--<div class="d-flex align-items-center justify-content-between mt-45">-->
            <!--    <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }} {{ handlePrice($total) }}</span>-->
                
            <!--</div>-->
        </form>
        </div>

         
            <form action="/payments/verify/Razorpay" method="get">
             
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <script src="https://checkout.razorpay.com/v1/checkout.js"
                        data-key="{{ env('RAZORPAY_API_KEY') }}"
                        data-amount="{{ (int)($order->total_amount * 100) }}"
                        data-buttontext="product_price"
                        data-description="Rozerpay"
                        data-currency="{{ currency() }}"
                        data-image="{{ $generalSettings['logo'] }}"
                        data-prefill.name="{{ $order->user->full_name }}"
                        data-prefill.email="{{ $order->user->email }}"
                        data-theme.color="#43d477">
                </script>
            </form>
        
        
        
  </div>
                <div class="col-12 col-lg-6 ">
                    
<div class="rounded-sm shadow mt-20 py-25 px-10 px-md-30">
              
                <h2 class="section-title">Order Summary</h2>
                <style>
                    .cart-item{
                            max-height: 350px;
    overflow-x: hidden;
    overflow-y: auto;

                    }
                    .cart-item::-webkit-scrollbar {
    width: 5px;
    background-color: lightgrey;
    box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
}
.cart-item::-webkit-scrollbar-thumb {
    background: linear-gradient(to right, #9effc1, var(--primary));
}
                </style>
                  <div class="cart-item">
                @foreach($carts as $cart)
                
                    <div class="row mt-5 cart-row">
                        <div class="col-12 col-lg-10 mb-15 mb-md-0">
                            <div class="webinar-card webinar-list-cart row">
                                <div class="col-4">
                                    <div class="image-box" style="    height: 85px !important;">
                                        @php
                                            $cartItemInfo = $cart->getItemInfo();
                                        @endphp
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" class="img-cover" alt="user avatar">
                                    </div>
                                </div>

                                <div class="col-8">
                                    <div class="webinar-card-body p-0 w-100 h-100 d-flex flex-column">
                                        <div class="d-flex flex-column">
                                            <a href="{{ $cartItemInfo['itemUrl'] ?? '#!' }}" target="_blank">
                                                <h3 class="font-16 font-weight-bold text-dark-blue">{{ $cartItemInfo['title'] }}</h3>
                                            </a>
 <span class="text-gray d-inline-block d-md-none">{{ trans('public.price') }} :</span>
                                @if(count($cartInstallment1)!=0)
                                    @foreach($cartInstallment1 as $cartInstallment)
                                    @if($cart->id == $cartInstallment->cart_id)
                                    
                                        <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'] + $cartInstallment->discount_price, true, true, false, null, true) }}</span>
                                        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                    @else
                                    @if(!empty($cartItemInfo['discountPrice']))
                                        <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                                    @else
                                        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                    @endif
                                    
                                    @endif
                                    @endforeach
                                
                                @else
                                    @if(!empty($cartItemInfo['discountPrice']))
                                        <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                                    @else
                                        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                    @endif
                                @endif

                            @if(!empty($cartItemInfo['quantity']))
                                <span class="font-12 text-warning font-weight-500 mt-0 mt-md-5">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>
                            @endif

                            @if(!empty($cartItemInfo['extraPriceHint']))
                                <span class="font-12 text-gray font-weight-500 mt-0 mt-md-5">{{ $cartItemInfo['extraPriceHint'] }}</span>
                            @endif
                                            @if(!empty($cart->gift_id) and !empty($cart->gift))
                                                <span class="d-block mt-5 text-gray font-12">{!! trans('update.a_gift_for_name_on_date',['name' => $cart->gift->name, 'date' => (!empty($cart->gift->date) ? dateTimeFormat($cart->gift->date, 'j M Y H:i') : trans('update.instantly'))]) !!}</span>
                                            @endif
                                        </div>

                                        @if(!empty($cart->reserve_meeting_id))
                                            <div class="mt-10">
                                                <span class="text-gray font-12 border rounded-pill py-5 px-10">{{ $cart->reserveMeeting->day .' '. $cart->reserveMeeting->meetingTime->time }} ({{ $cart->reserveMeeting->meeting->getTimezone() }})</span>
                                            </div>

                                            @if($cart->reserveMeeting->meeting->getTimezone() != getTimezone())
                                                <div class="mt-10">
                                                    <span class="text-danger font-12 border border-danger rounded-pill py-5 px-10">{{ $cart->reserveMeeting->day .' '. dateTimeFormat($cart->reserveMeeting->start_at,'h:iA',false).'-'.dateTimeFormat($cart->reserveMeeting->end_at,'h:iA',false) }} ({{ getTimezone() }})</span>
                                                </div>
                                            @endif
                                        @endif

                                        
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--<div class="col-6 col-lg-2 d-flex flex-md-column align-items-center justify-content-center">-->
                        <!--    <span class="text-gray d-inline-block d-md-none">{{ trans('public.price') }} :</span>-->

                        <!--    @if(!empty($cartItemInfo['discountPrice']))-->
                        <!--        <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>-->
                        <!--        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>-->
                        <!--    @else-->
                        <!--        <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>-->
                        <!--    @endif-->

                        <!--    @if(!empty($cartItemInfo['quantity']))-->
                        <!--        <span class="font-12 text-warning font-weight-500 mt-0 mt-md-5">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>-->
                        <!--    @endif-->

                        <!--    @if(!empty($cartItemInfo['extraPriceHint']))-->
                        <!--        <span class="font-12 text-gray font-weight-500 mt-0 mt-md-5">{{ $cartItemInfo['extraPriceHint'] }}</span>-->
                        <!--    @endif-->
                        <!--</div>-->

                        <div class="col-6 col-lg-2 d-flex flex-md-column align-items-center justify-content-center">
                            <span class="text-gray d-inline-block d-md-none mr-10 mr-md-0">{{ trans('public.remove') }} :</span>

                            <a href="/cart/{{ $cart->id }}/delete" class="delete-action btn-cart-list-delete d-flex align-items-center justify-content-center">
                                <i data-feather="x" width="20" height="20" class=""></i>
                            </a>
                        </div>
                    </div>
                @endforeach
</div>
               
           
        <form action="/cart" method="post" id="cartForm">
            {{ csrf_field() }}
            <input type="hidden" name="discount_id" value="">

            @if($hasPhysicalProduct)
                @include('web.default.cart.includes.shipping_and_delivery')
            @endif

            <div class="row mt-5">
                 
                <!--<div class="col-12 col-lg-12">-->
                <!--    <section class="mt-45">-->
                <!--        <h3 class="section-title">{{ trans('cart.coupon_code') }}</h3>-->
                <!--        <div class="rounded-sm shadow mt-20 py-25 px-20">-->
                <!--            <p class="text-gray font-14">{{ trans('cart.coupon_code_hint') }}</p>-->

                <!--            @if(!empty($userGroup) and !empty($userGroup->discount))-->
                <!--                <p class="text-gray mt-25">{{ trans('cart.in_user_group',['group_name' => $userGroup->name , 'percent' => $userGroup->discount]) }}</p>-->
                <!--            @endif-->

                <!--            <form action="/carts/coupon/validate" method="Post">-->
                <!--                {{ csrf_field() }}-->
                <!--                <div class="form-group">-->
                <!--                    <input type="text" name="coupon" id="coupon_input" class="form-control mt-25"-->
                <!--                           placeholder="{{ trans('cart.enter_your_code_here') }}">-->
                <!--                    <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>-->
                <!--                    <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>-->
                <!--                </div>-->

                <!--                <button type="submit" id="checkCoupon"-->
                <!--                        class="btn btn-sm btn-primary mt-50">{{ trans('cart.validate') }}</button>-->
                <!--            </form>-->
                <!--        </div>-->
                <!--    </section>-->
                <!--</div>-->

                <div class="col-12 col-lg-12">
                  
                    <section class="mt-20">
                      
                        
                        
                            @if(count($cartInstallment1)!=0)
                            @foreach($cartInstallment1 as $cartInstallment)
                            <form action="/carts/coupon/validate" method="Post">
                    {{ csrf_field() }}
                    <div class="row"> 
                    <div class="col-12 col-lg-9"> 
                    <div class="form-group">
                        <input readonly type="text" name="coupon" id="coupon_input" class="form-control mt-25 "
                        value="{{ !empty(session('coupon')) ? session('coupon') : '' }}"
                               placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <span style="color:red;">Use coupon for another course? Remove existing discounted installments from cart.</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-12 col-lg-3">
                    <button type="submit" id="checkCoupon" class="btn btn-sm btn-primary mt-25 d" disabled>{{ trans('cart.validate') }}</button></div></div>
                </form>
                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.sub_total') }}</h4>
                                <span class="font-14 text-gray font-weight-bold">{{!empty($cartInstallment->discount_price) ? (handlePrice($cartInstallment->discount_price + $subTotal)):handlePrice( $subTotal) }}</span>
                            </div>
                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('public.discount') }}</h4>
                                <span class="font-14 text-gray font-weight-bold">
                                <span id="totalDiscount">{{ !empty(session('total_discount')) ? session('total_discount') : (handlePrice($cartInstallment->discount_price + $totalDiscount)) }}</span>
                            </span>
                            </div>

                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.tax') }}
                                    @if(!$taxIsDifferent)
                                        <span class="font-14 text-gray ">({{ $tax }}%)</span>
                                    @endif
                                </h4>
                                <span class="font-14 text-gray font-weight-bold"><span id="taxPrice">{{ !empty(session('total_tax')) ? session('total_tax') :  handlePrice($taxPrice) }}</span></span>
                            </div>

                            @if(!empty($productDeliveryFee))
                                <div class="cart-checkout-item">
                                    <h4 class="text-secondary font-14 font-weight-500">
                                        {{ trans('update.delivery_fee') }}
                                    </h4>
                                    <span class="font-14 text-gray font-weight-bold"><span id="taxPrice">{{ !empty(session('total_tax')) ? session('total_tax') :(!empty($cartInstallment->id)? 0 : handlePrice($productDeliveryFee)) }}</span></span>
                                </div>
                            @endif

                            <div class="cart-checkout-item border-0">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.total') }}</h4>
                                <span class="font-14 text-gray font-weight-bold"><span id="totalAmount">{{ !empty(session('total_amount')) ? session('total_amount') : (handlePrice($total)) }}</span></span>
                            </div>
                             @endforeach
                            @endif
                            
                            @if(count($cartInstallment1)==0)
                            
                            <form action="/carts/coupon/validate" method="Post">
                    {{ csrf_field() }}
                    <div class="row"> 
                    <div class="col-12 col-lg-9"> 
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-25 {{ !empty(session('total_discount')) ? 'is-valid' : '' }}"
                        value="{{ !empty(session('coupon')) ? session('coupon') : '' }}"
                               placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-12 col-lg-3">
                    <button type="submit" id="checkCoupon" class="btn btn-sm btn-primary mt-25">{{ trans('cart.validate') }}</button></div></div>
                </form>
                
                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.sub_total') }}</h4>
                                <span class="font-14 text-gray font-weight-bold">{{ handlePrice($subTotal) }}</span>
                            </div>

                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('public.discount') }}</h4>
                                <span class="font-14 text-gray font-weight-bold">
                                <span id="totalDiscount">{{ !empty(session('total_discount')) ? session('total_discount') : handlePrice($totalDiscount) }}</span>
                            </span>
                            </div>

                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.tax') }}
                                    @if(!$taxIsDifferent)
                                        <span class="font-14 text-gray ">({{ $tax }}%)</span>
                                    @endif
                                </h4>
                                <span class="font-14 text-gray font-weight-bold"><span id="taxPrice">{{ !empty(session('total_tax')) ? session('total_tax') :  handlePrice($taxPrice) }}</span></span>
                            </div>

                            @if(!empty($productDeliveryFee))
                                <div class="cart-checkout-item">
                                    <h4 class="text-secondary font-14 font-weight-500">
                                        {{ trans('update.delivery_fee') }}
                                    </h4>
                                    <span class="font-14 text-gray font-weight-bold"><span id="taxPrice">{{ !empty(session('total_tax')) ? session('total_tax') :handlePrice($productDeliveryFee) }}</span></span>
                                </div>
                            @endif
                            
                            <div class="cart-checkout-item border-0">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.total') }}</h4>
                                <span class="font-14 text-gray font-weight-bold"><span id="totalAmount">{{ !empty(session('total_amount')) ? session('total_amount') : (!empty($cartInstallment1->id)?handlePrice($cartInstallment1->total) : handlePrice($total)) }}</span></span>
                            </div>
                            @endif
                             <div class="d-flex align-items-center justify-content-between mt-45">
            <!--<button type="button" id="paymentSubmit" disabled class="btn btn-sm btn-primary d-none">{{ trans('public.start_payment') }}</button>-->
                        <button type="submit" id="autosubmit" class="autosubmit btn btn-sm btn-primary mt-15 d-none">{{ trans('cart.checkout') }}</button>
                            <!--<button type="submit" class="btn btn-sm btn-primary mt-15">{{ trans('cart.checkout') }}</button>-->
                         <!--<button type="button" onclick="window.history.back()" class="btn btn-sm btn-primary">{{ trans('cart.continue_shopping') }}</button>-->
                
            </div>

                    </section>
                </div>
            </div>
        </form></div>
                </div>
       </div>
    </div>
      
        
@php
session()->forget('discount_id');
session()->forget('total_discount');
session()->forget('total_tax');
session()->forget('total_amount');
session()->forget('coupon');
session()->forget('discountCouponId');



@endphp        
    
    
    
    
@endsection

@push('scripts_bottom')
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/get-regions.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart.min.js"></script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush
