@extends('web.default2'.'.layouts.app')

<style>
.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;
  /*width: 80px;*/
  height: 80px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
/*@-webkit-keyframes spin {*/
/*  0% { -webkit-transform: rotate(0deg); }*/
/*  100% { -webkit-transform: rotate(360deg); }*/
/*}*/

/*@keyframes spin {*/
/*  0% { transform: rotate(0deg); }*/
/*  100% { transform: rotate(360deg); }*/
/*} */
#loader {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

/* Disable page */
.disabled-page {
    pointer-events: none;
    opacity: 0.5;
}
</style>


@section('content')
    <section class="cart-banner1 position-relative text-center  slider-container1">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.shopping_cart') }}</h1>
        @if(count($carts)>1)
        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($subTotal) . ' ' . trans('cart.for_items',['count' => count($carts)]) }}</span>
        @else
        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($subTotal) . ' ' . trans('cart.for_item',['count' => count($carts)]) }}</span>
        @endif
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
            //print_r($cart->id);die;
            $invalidChannels = [];
        @endphp
        <div class=" bg-gray200 mt-30 rounded-lg border p-15">
         <h2 class="section-title">Fill all the Details here:</h2>
          <form action="/payments/payment-request" method="post" class=" mt-25" >
            {{ csrf_field() }}
            <input type="hidden" name="order_id"  value="{{ $order->id ?? 1 }}">
            <input type="text" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >
            

            <div class="row d-none">
                @if(!empty($paymentChannels))
               
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 col-lg-6 mb-20 charge-account-radio">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                                  <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" style="display: block;    visibility: visible;">
                                  <img src="{{ $paymentChannel->image }}" width="120" height="60" alt="">
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

                {{--<div class="col-12 col-lg-6 mb-20 charge-account-radio ">
                   
                    <label for="offline" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                       <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit" style="display: block;    visibility: visible;">
                        <img src="/assets2/default/img/activity/wallet.png" width="120" height="60" alt="">
                        <div>
                         <p class="mt-30 mt-lg-10 font-weight-500 text-dark-blue">
                               <span class="font-weight-bold font-14">Wallet</span>
                            <p class="mt-5"></p>
                        </p>

                        <!--<p class="font-weight-500 text-dark-blue">-->
                        <!--   Purchase with your fingertips. Look for us the next time you're paying from a mobile app, and checkout faster on thousands of mobile websites.-->
                        <!--</p>-->
                        
                        </div>

                        
                    </label>
                </div>--}}
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
                                <img src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

          <button type="button" id="paymentSubmit"  class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>
            <!--<div class="d-flex align-items-center justify-content-between mt-45">-->
            <!--    <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }} {{ handlePrice($total) }}</span>-->
                
            <!--</div>-->
        </form>
        </div>
 <center><div class="loader mt-50" id="loader" style="dispay:none ">
            <img width= '80px' height= '80px' src="{{ asset('assets/default/img/loading.gif')}}">
            <br>
            <h3>Please do not refresh or close the page while your payment is being processed...</h3>
            </div></center>
         
        
        
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
                    @php
                      $extra_amount = 0;
                    @endphp 
                    
                @foreach($carts as $key=>$cart)
               
                  @if(!empty($cart->exists))
                    <div class="row mt-5 cart-row">
                        <div class="col-12 col-lg-10 mb-15 mb-md-0">
                            <div class="webinar-card webinar-list-cart row">
                                <div class="col-4">
                                    <div class="image-box" style="height: 85px !important;">
                                        @php
                                            $cartItemInfo = $cart->getItemInfo();
                                           //print_r($cart);
                                           $extra_amount += $cart['extra_amount'];
                                        @endphp
                                        <img src="{{ $cartItemInfo['imgPath'] ?? '' }}" class="img-cover" alt="user avatar">
                                    </div>
                                </div>

                                <div class="col-8">
                                    <div class="webinar-card-body p-0 w-100 h-100 d-flex flex-column">
                                        <div class="d-flex flex-column">
                                            <a href="{{ $cartItemInfo['itemUrl'] ?? '#!' }}" target="_blank">
                                                <h3 class="font-16 font-weight-bold text-dark-blue">{{ $cartItemInfo['title'] }}</h3>
                                            </a>
                                       <span class="text-gray d-inline-block d-md-none">{{ trans('public.price') }} :</span>

                            @if(!empty($cartItemInfo['discountPrice']))
                                <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                            @else
                                <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
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

                            <a href="/cart/{{$cart->id }}/delete" class="delete-action btn-cart-list-delete d-flex align-items-center justify-content-center">
                                <i data-feather="x" width="20" height="20" class=""></i>
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="row mt-5 cart-row">
                        <div class="col-12 col-lg-10 mb-15 mb-md-0">
                            <div class="webinar-card webinar-list-cart row">
                                <div class="col-4">
                                    <div class="image-box" style="height: 85px !important;">
                                        
                                         @php 
              
                              
                                //echo'<pre>'; print_r($cart[0]);die;
                                 $cartItemInfo = $cart[0];
                                         //print_r($cartItemInfo);die;
                                            
                                        @endphp
                                        <img src="{{ $cartItemInfo['thumbnail'] ?? '' }}" class="img-cover" alt="user avatar">
                                    </div>
                                </div>

                                <div class="col-8">
                                    <div class="webinar-card-body p-0 w-100 h-100 d-flex flex-column">
                                        <div class="d-flex flex-column">
                                            <a href="{{ $cartItemInfo['slug'] ?? '#!' }}" target="_blank">
                                                <h3 class="font-16 font-weight-bold text-dark-blue">{{ $cartItemInfo['title'] }}</h3>
                                            </a>
                               <span class="text-gray d-inline-block d-md-none">{{ trans('public.price') }} :</span>

                            @if(!empty($cartItemInfo['discountPrice']))
                                <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                            @else
                                <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
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

                            <a href="/cart/{{ $cartItemInfo['id'] }}/delete" class="delete-action btn-cart-list-delete d-flex align-items-center justify-content-center">
                                <i data-feather="x" width="20" height="20" class=""></i>
                            </a>
                        </div>
                    </div>
                    
                    
                     @endif
                @endforeach
</div>
               
           
        <form action="/cart" method="post" id="cartForm">
            {{ csrf_field() }}
            <input type="hidden" name="discount_id" value="">
            
 @if(!empty($hasPhysicalProduct))
           
                @include('web.default2.cart.includes.shipping_and_delivery')
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
                      
                        @if(!empty($cartInstallment1))

                     @foreach($cartInstallment1 as $cartInstallment)
                            <form action="/carts/coupon/validate" method="Post">
                    {{ csrf_field() }}
                    <div class="row"> 
                    <div class="col-12 col-lg-9"> 
                    <div class="form-group">
                        <input readonly type="text" name="coupon" id="coupon_input" class="form-control mt-25 "
                        value="{{ !empty(session('coupon')) ? session('coupon') : '' }}"
                               placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <span style="color:red;">If you want to purchase other course with discount coupon please remove discounted installment</span>
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
                            
                @if(!empty($cartInstallment1))
                            
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
                            @if($extra_amount>0)
                                <div class="cart-checkout-item">
                                    <h4 class="text-secondary font-14 font-weight-500">Extra paid</h4>
                                    <span class="font-14 text-gray font-weight-bold">-{{ handlePrice($extra_amount) }}</span>
                                </div>
                                @php
                                $total-=$extra_amount;
                                //session('total_amount')-=$extra_amount;
                                @endphp
                            @endif
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
                                <span class="font-14 text-gray font-weight-bold">
                                    <span id="totalAmount">{{ handlePrice($total) }}</span>
                                </span>
                            </div>
                            @else
                            
                            
                            @endif
                             <div class="d-flex align-items-center justify-content-between mt-45">
            <!--<button type="button" id="paymentSubmit" disabled class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>-->
                            <button type="submit" id="autosubmit" class="autosubmit btn btn-sm btn-primary mt-15 d-none">{{ trans('cart.checkout') }}</button>
                         <!--<button type="button" onclick="window.history.back()" class="btn btn-sm btn-primary">{{ trans('cart.continue_shopping') }}</button>-->
                
            </div>

                    </section>
                </div>
            </div>
        </form></div>
                </div>
       </div>
    </div>
      @if(!empty($razorpay) and $razorpay)
         
            <form action="/payments/verify/Razorpay" method="get" id="razorpayview">
             
                <input type="hidden" name="order_id"  value="{{$order->id ?? 1}}">
                <input type="hidden" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="user_name" placeholder="Name" class="form-control mt-25 " required>
                <input type="hidden" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="user_email" placeholder="Email" class="form-control mt-25 " required>
                <input type="hidden" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="user_number" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                <input type="hidden" name="total" value="{{ $total }}" id="total" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                <input type="hidden" name="extra_amount" value="{{ $extra_amount }}" id="extra_amount" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                <!--<script src="https://checkout.razorpay.com/v1/checkout.js"-->
                <!--        data-key="{{ env('RAZORPAY_API_KEY') }}"-->
                <!--        data-amount="{{ (int)($total  * 100) }}"-->
                <!--        data-buttontext="product_price"-->
                <!--        data-description="Rozerpay"-->
                <!--        data-currency="{{ currency() }}"-->
                <!--        data-image="{{ $generalSettings['logo'] ?? '' }}"-->
                <!--        data-prefill.name="{{ $order->user->full_name ??  '' }}"-->
                <!--        data-prefill.email="{{ $order->user->email ?? '' }}"-->
                <!--        data-theme.color="#43d477">-->
                <!--</script>-->
                
                <input type="hidden" name="razorpay_payment_id" value="" id="razorpay_payment_id" class="form-control mt-25 mb-25">
                <input type="hidden" name="razorpay_signature" value="" id="razorpay_signature" class="form-control mt-25 mb-25">
              <input type="hidden" name="Country" id="user_country" value="">
                <input type="hidden" name="StateProvince" id="user_state" value="">
                <input type="hidden" name="City" id="user_city" value="">
               <input type="hidden" name="pin_code" id="user_pin_code" value="">
                <input type="hidden" name="address" id="user_address" value="">
                <input type="hidden" name="message" id="user_message" value="">
            </form>
       
        @endif
        

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
 <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/payment.min.js"></script>
 <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
//         var deliveryFeeIndia = '{{ handlePrice($deliveryFeeIndia ?? 0) }}';
// var deliveryFeeInternational = '{{ handlePrice($deliveryFeeInternational ?? 0) }}';
          $("#loader").css("display", "none");
           
    function addscript(){
            
        var name = '';
        var email = '';
        var mobile = '';
         name = document.getElementById("customer_name").value ;
         email = document.getElementById("customer_email").value;
         mobile = document.getElementById("customer_number").value; 
         
         var country = document.getElementById("country").value;
         var state = document.getElementById("state").value;
         var city = document.getElementById("city").value;
        
        var countrySelect = document.getElementById("country");
        var stateSelect = document.getElementById("state");
        var citySelect = document.getElementById("city");
        
        var countryName = countrySelect.options[countrySelect.selectedIndex].text;
        var stateName = stateSelect.options[stateSelect.selectedIndex].text;
        var cityName = citySelect.options[citySelect.selectedIndex].text;
        
        document.getElementById("user_country").value = countryName;
        document.getElementById("user_state").value = stateName;
        document.getElementById("user_city").value = cityName;
        document.getElementById("user_pin_code").value = $('#pin_code').val();
        document.getElementById("user_address").value = $('textarea[name="address"]').val();
        document.getElementById("user_message").value = $('textarea[name="message"]').val();

      
        
                $('.textdanger').remove();
        // $('#customer_email').html('');
        // $('#customer_number').html('');
        if(name ===''){
            // $('#paymentSubmit').prop('disabled', true);
            $("input:radio").attr("checked", false);
            var namevalidation ='Name field is required';
            $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');
             
        }
         if(email ===''){
            // $('#paymentSubmit').prop('disabled', true);
            $("input:radio").attr("checked", false);
            var emailvalidation ='Email field is required';
            $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
         if(mobile ===''){
            // $('#paymentSubmit').prop('disabled', true);
            $("input:radio").attr("checked", false);
            var mobilevalidation ='Mobile field is required';
            $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
        }else{
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(!regex.test(email)) {
                // $("input:radio").attr("checked", true);
                document.getElementById("customer_email").value =email;
                var emailvalidation ='Enter Valid Email Address';
                $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
               return false;
            }
             if (mobile.length < 9) {
                 
                    $("input:radio").attr("checked", false);
                    var mobilevalidation ='Enter Valid Mobile Number';
                    $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
                return false;
              }
             if(country === ''){
                $("input:radio").attr("checked", false);
                var countryValidation = 'Please select a country';
                $(document).find('#country').after('<span class="text-strong textdanger" style="color:red;">' + countryValidation + '</span>');
                return false;
            }
        
            // state validation
            if(state === ''){
                $("input:radio").attr("checked", false);
                var stateValidation = 'Please select a state';
                $(document).find('#state').after('<span class="text-strong textdanger" style="color:red;">' + stateValidation + '</span>');
                return false;
            }
        
            // city validation
            if(city === ''){
                $("input:radio").attr("checked", false);
                var cityValidation = 'Please select a city';
                $(document).find('#city').after('<span class="text-strong textdanger" style="color:red;">' + cityValidation + '</span>');
                return false;
            }
            
              $('#paymentSubmit').prop('disabled', false);
            $("input:radio").attr("checked", true);
          var  datakey="<?php echo  env('RAZORPAY_API_KEY'); ?>";
          var   dataamount="<?php echo (int)(preg_replace('/[^\d.]/', '', handlePrice(($order->total_amount * 100)-($extra_amount*100)))); ?>";
          var   databuttontext="product_price";
          var   datadescription="Payment for {{$course_title}} was successfully made via Razorpay through our official website – Asttrolok, using a desktop(CartPayment)";
          var    datacurrency="<?php echo currency(); ?>";
          var    dataimage="<?php echo  $generalSettings['logo']; ?>";
          var    dataprefillname=name;
          var   dataprefillemail=email;
          var   dataprefillcontact=mobile;
          
          var   url="{{ url('/webhook-url')}}";
          var data = {
            name: dataprefillname,
            email: dataprefillemail,
            mobile: dataprefillcontact,
            course_title: "{{$course_title}}",
          }
    // webhook url sent data
      $.ajax({
            method: 'post',
            url: url,
            data: data,
        }).done(function(response, status){
            //
        }).fail(function(jqXHR, textStatus, errorThrown){
            //
        });
          // payment start proccess
        const rzp_options = {
        key: datakey,
        amount: dataamount,
        name: 'Asttrolok',
        description: datadescription,
        currency:datacurrency,
        image:dataimage,
        handler: function(response) {
            $("#loader").css("display", "block");
            $('#paymentSubmit').prop('disabled', true);
             // loader
            document.body.classList.add('disabled-page');
            document.getElementById('loader').style.display = 'block';
            document.documentElement.style.overflow = 'hidden';
            // alert(`Payment Succesful ${response.razorpay_payment_id}`);
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;
            document.getElementById("razorpayview").submit();
            
        },
        modal: {
            ondismiss: function() {
                alert(`Payment Failed`)
            }
        },
        prefill: {
            email: dataprefillemail,
            contact: dataprefillcontact
        },
        notes: {
            name: dataprefillname,
            item: 'course',
        },
        theme: {
            color: "#43d477"
        }
    };
    const rzp1 = new Razorpay(rzp_options);
    rzp1.open();

          
    //  var s = document.createElement( 'script' );
    //     s.setAttribute( 'src', "https://checkout.razorpay.com/v1/checkout.js" );
    //     s.setAttribute( 'id', "razorpay_script" );
    //     s.setAttribute( 'data-key',datakey );
    //     s.setAttribute( 'data-amount', dataamount );
    //     // s.setAttribute( 'data-buttontext', databuttontext );
    //     s.setAttribute( 'data-currency', datacurrency );
    //     s.setAttribute( 'data-name', 'Asttrolok' );
    //     s.setAttribute( 'data-description', datadescription );
    //     s.setAttribute( 'data-image', dataimage);
    //     s.setAttribute( 'data-theme.color', "#43d477" );
    //     s.setAttribute( 'data-prefill.name', dataprefillname );
    //     s.setAttribute( 'data-prefill.email', dataprefillemail );
    //     s.setAttribute( 'data-prefill.contact', dataprefillcontact );
    //     document.querySelector("#razorpayview").appendChild( s );
        // document.getElementById("pay-btn").click();
        return true;
        }
        }
        
        
        
// function toggleDeliveryFee() {
    // var countryName = $("#country option:selected").text().toLowerCase();
    
    // if(countryName === 'india') {
    //     $("#deliveryFeeIndiaWrapper").show();
    //     $("#deliveryFeeInternationalWrapper").hide();
    // } else {
    //     $("#deliveryFeeIndiaWrapper").hide();
    //     $("#deliveryFeeInternationalWrapper").show();
    // }
// }
    $(document).ready(function(){
         
             $('body').on('click', '#paymentSubmit', function (e) {
              addscript();
                
             });
            
           $('body').on('change paste keyup', '#customer_name', function (e) {
                e.preventDefault();
                document.getElementById("user_name").value = $(this).val();
            }); 
            
            $('body').on('change paste keyup', '#customer_email', function (e) {
                e.preventDefault();
                document.getElementById("user_email").value = $(this).val();
                
            });   
            
            $('body').on('change', '#customer_number', function (e) {
                e.preventDefault();
                document.getElementById("user_number").value = $(this).val();
               
            });
           
  
      });

$(document).ready(function() {
$('#customer_number').on('keypress', function(e) {
 var $this = $(this);
 var regex = new RegExp("^[0-9\b]+$");
 var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
 // for 10 digit number only
 if ($this.val().length > 9) {
    e.preventDefault();
    return false;
  }
 
  if (e.charCode < 54 && e.charCode > 47) {
      if ($this.val().length == 0) {
        e.preventDefault();
        return false;
      } else {
        return true;
      }
  }
  if (regex.test(str)) {
    return true;
  }
  e.preventDefault();
  return false;
  });
});
        
        
        
        $(document).ready(function(){
            
           $('body').on('change paste keyup', '#customer_name', function (e) {
            e.preventDefault();
            document.getElementById("user_name").value = $(this).val();
          }); 
    
            $('body').on('change paste keyup', '#customer_email', function (e) {
                e.preventDefault();
                document.getElementById("user_email").value = $(this).val();
                
              
            });   
    
            $('body').on('change paste keyup', '#customer_number', function (e) {
                e.preventDefault();
                document.getElementById("user_number").value = $(this).val();
                $( "script" ).data( "prefill.contact" ) === "";
            });  
            
  
});
 
       
    </script>

    <!--<script src="/assets2/default/js/parts/get-regions.min.js"></script>-->
    <script src="/assets2/default/js/parts/cart.min.js"></script>
    <!--<script src="/assets2/default/js/parts/payment.min.js"></script>-->
@endpush
