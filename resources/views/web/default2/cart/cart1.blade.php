@extends('web.default2'.'.layouts.app')
<style>
.form-control.is-invalid {
    border-color: #dc3545 !important;
}

.form-control.is-valid {
    border-color: #28a745 !important;
}

.form-control.is-invalid:focus {
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-valid:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.invalid-feedback {
    display: none;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-control.is-invalid ~ .invalid-feedback {
    display: block;
}

.loader {
  height: 80px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

#loader {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

.disabled-page {
    pointer-events: none;
    opacity: 0.5;
}

#paymentLoader {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

#paymentLoader .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    position: absolute;
    top: 50%;
    left: 44%;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
<style>
.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;

  height: 80px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

#loader {
    position: fixed;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

.disabled-page {
    pointer-events: none;
    opacity: 0.5;
}
</style>
<style>
  #paymentLoader {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
  }
  #paymentLoader .spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    position: absolute;
    top: 50%;
    left: 44%;
  }
  @keyframes spin {
    to { transform: rotate(360deg); }
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
                       <input type="password" name="password" id="customer_password" placeholder="Create Password" class="form-control mt-25 " >
            <input type="password" name="password_confirmation" id="customer_password_confirmation" placeholder="Confirm Password" class="form-control mt-25 " >

           <div class="invalid-feedback">
    Passwords do not match!
</div>
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

          <button type="button" id="paymentSubmit"  class="btn btn-sm btn-primary" style="font-family: 'Inter', sans-serif !important;">{{ trans('public.start_payment') }}</button>

        </form>
        </div>
 <center><div class="loader mt-50" id="loader" style="dispay:none; z-index: 550;">
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
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] ?? '' }}" class="img-cover" alt="user avatar">
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

                                    </div>
                                </div>
                            </div>
                        </div>

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
                    <button type="submit" id="checkCoupon" class="btn btn-sm btn-primary mt-25 d"  disabled>{{ trans('cart.validate') }}</button></div></div>
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
                    <button type="submit" id="checkCoupon" class="btn btn-sm btn-primary mt-25" style="font-family: 'Inter', sans-serif !important;">{{ trans('cart.validate') }}</button></div></div>
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
                                    <span id="totalAmount">{{ !empty(session('total_amount')) ? session('total_amount') : (handlePrice($total)) }}</span>
                                </span>
                            </div>
                            @else

                            @endif
                             <div class="d-flex align-items-center justify-content-between mt-45">

                            <button type="submit" id="autosubmit" class="autosubmit btn btn-sm btn-primary mt-15 d-none">{{ trans('cart.checkout') }}</button>

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

                <input type="hidden" name="razorpay_payment_id" value="" id="razorpay_payment_id" class="form-control mt-25 mb-25">
                <input type="hidden" name="razorpay_signature" value="" id="razorpay_signature" class="form-control mt-25 mb-25">
                 @if(!empty($hasPhysicalProduct))
                      <input type="hidden" name="Country" id="user_country" value="">
                        <input type="hidden" name="StateProvince" id="user_state" value="">
                        <input type="hidden" name="City" id="user_city" value="">
                      <input type="hidden" id="user_pin_code" name="pin_code">
                        <input type="hidden" id="user_address" name="address">
                        <input type="hidden" id="user_message" name="message">
                @endif
            </form>

        @endif

@php
session()->forget('discount_id');
//session()->forget('total_discount');
session()->forget('total_tax');
//session()->forget('total_amount');
session()->forget('coupon');
session()->forget('discountCouponId');

echo session('discount_id');
@endphp
<div id="paymentLoader">
        <div class="spinner"></div>
        </div>
@endsection

@push('scripts_bottom')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="{{ asset('js/unified-payment.js') }}"></script>
<script>
     const loaderEl = document.getElementById('paymentLoader');

    function showPaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'block';
    }

    function hidePaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'none';
    }

var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
if(@json($hasPhysicalProduct)){
             var country = document.getElementById("country").value;
             var state = document.getElementById("state").value;
             var city = document.getElementById("city").value;
             var pin_code = $('#pin_code').val().trim();
            var address = $('textarea[name="address"]').val().trim();
            var message = $('textarea[name="message"]').val().trim();

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
             document.getElementById("user_pin_code").value = pin_code;
            document.getElementById("user_address").value = address;
            document.getElementById("user_message").value = message;
         }

document.getElementById('paymentSubmit').addEventListener('click', function(e) {
    e.preventDefault();
    if(@json($hasPhysicalProduct)){
    var countrySelect = document.getElementById("country");
            var stateSelect = document.getElementById("state");
            var citySelect = document.getElementById("city");

            var countryName = countrySelect.options[countrySelect.selectedIndex].text;
            var stateName = stateSelect.options[stateSelect.selectedIndex].text;
            var cityName = citySelect.options[citySelect.selectedIndex].text;

            var pin_code = document.getElementById('pin_code').value;
            var address = document.getElementById('address').value;
            var message = document.getElementById('message').value;

            document.getElementById("user_country").value = countryName;
    }
    const userDetails = {
        name: document.getElementById('customer_name').value,
        email: document.getElementById('customer_email').value,
        number: document.getElementById('customer_number').value,
        password: document.getElementById('customer_password').value,
        Country: countryName || null,
        StateProvince: stateName || null,
        City: cityName || null,
        pin_code: pin_code || null,
        address: address || null,
        message: message || null,
        discount_id: @json(session('discount_id'))
    };
    showPaymentLoader();

    initiatePayment('cart',{{ $order->id }}, userDetails);
});
</script>
<script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/cart.min.js"></script>
<script>
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Password confirmation validation
    const passwordField = document.getElementById('customer_password');
    const confirmPasswordField = document.getElementById('customer_password_confirmation');
    
    if (!passwordField || !confirmPasswordField) {
        console.error('Password fields not found');
        return;
    }

    function validatePasswordMatch() {
        const password = passwordField.value;
        const confirmPassword = confirmPasswordField.value;
        
        // अगर confirm password खाली है तो कुछ नहीं करो
        if (confirmPassword === '') {
            confirmPasswordField.classList.remove('is-invalid', 'is-valid');
            return true;
        }
        
        // तभी validate करो जब confirm password की length >= password की length हो
        if (confirmPassword.length >= password.length) {
            if (password === confirmPassword) {
                confirmPasswordField.classList.remove('is-invalid');
                confirmPasswordField.classList.add('is-valid');
                return true;
            } else {
                confirmPasswordField.classList.remove('is-valid');
                confirmPasswordField.classList.add('is-invalid');
                return false;
            }
        }
        
        return true;
    }

    // Input event - sirf tabhi check karo jab puri length match kare
    confirmPasswordField.addEventListener('input', validatePasswordMatch);

    // Blur event - jab user field se bahar jaye tab bhi check karo
    confirmPasswordField.addEventListener('blur', function() {
        if (confirmPasswordField.value !== '') {
            validatePasswordMatch();
        }
    });

    // Jab password field change ho, confirm password ko bhi revalidate karo
    passwordField.addEventListener('input', function() {
        if (confirmPasswordField.value !== '' && 
            confirmPasswordField.value.length >= passwordField.value.length) {
            validatePasswordMatch();
        }
    });

    // Loader functions
    const loaderEl = document.getElementById('paymentLoader');

    function showPaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'flex';
    }

    function hidePaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'none';
    }

    var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
    
    // Physical product validation
    if(@json($hasPhysicalProduct)){
        var country = document.getElementById("country")?.value;
        var state = document.getElementById("state")?.value;
        var city = document.getElementById("city")?.value;
        var pin_code = document.getElementById('pin_code')?.value || '';
        var address = document.querySelector('textarea[name="address"]')?.value || '';
        var message = document.querySelector('textarea[name="message"]')?.value || '';

        var countrySelect = document.getElementById("country");
        var stateSelect = document.getElementById("state");
        var citySelect = document.getElementById("city");

        if (countrySelect && stateSelect && citySelect) {
            var countryName = countrySelect.options[countrySelect.selectedIndex].text;
            var stateName = stateSelect.options[stateSelect.selectedIndex].text;
            var cityName = citySelect.options[citySelect.selectedIndex].text;

            document.getElementById("user_country").value = countryName;
            document.getElementById("user_state").value = stateName;
            document.getElementById("user_city").value = cityName;
            document.getElementById("user_pin_code").value = pin_code;
            document.getElementById("user_address").value = address;
            document.getElementById("user_message").value = message;
        }
    }

    // Payment submit with validation
    const paymentSubmitBtn = document.getElementById('paymentSubmit');
    if (paymentSubmitBtn) {
        paymentSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('customer_name').value.trim();
            const email = document.getElementById('customer_email').value.trim();
            const number = document.getElementById('customer_number').value.trim();
            const password = document.getElementById('customer_password').value;
            const confirmPassword = document.getElementById('customer_password_confirmation').value;
            
            // Check if all required fields are filled
            if (!name || !email || !number || !password || !confirmPassword) {
                alert('Please fill in all required fields!');
                return false;
            }
            
            // Check if passwords match
            if (password !== confirmPassword) {
                confirmPasswordField.classList.add('is-invalid');
                alert('Passwords do not match!');
                return false;
            }
            
            // Physical product details
            var countryName = null, stateName = null, cityName = null;
            var pin_code = null, address = null, message = null;
            
            if(@json($hasPhysicalProduct)){
                var countrySelect = document.getElementById("country");
                var stateSelect = document.getElementById("state");
                var citySelect = document.getElementById("city");

                if (countrySelect && stateSelect && citySelect) {
                    countryName = countrySelect.options[countrySelect.selectedIndex].text;
                    stateName = stateSelect.options[stateSelect.selectedIndex].text;
                    cityName = citySelect.options[citySelect.selectedIndex].text;
                }

                pin_code = document.getElementById('pin_code')?.value || null;
                address = document.querySelector('textarea[name="address"]')?.value || null;
                message = document.querySelector('textarea[name="message"]')?.value || null;

                if (document.getElementById("user_country")) {
                    document.getElementById("user_country").value = countryName;
                    document.getElementById("user_state").value = stateName;
                    document.getElementById("user_city").value = cityName;
                }
            }
            
            const userDetails = {
                name: name,
                email: email,
                number: number,
                password: password,
                password_confirmation: confirmPassword,
                Country: countryName || null,
                StateProvince: stateName || null,
                City: cityName || null,
                pin_code: pin_code || null,
                address: address || null,
                message: message || null,
                discount_id: @json(session('discount_id'))
            };

            showPaymentLoader();

            initiatePayment('cart', {{ $order->id }}, userDetails);
        });
    }
});
</script>
@endpush
