@extends(getTemplate().'.layouts.app')

<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-cart.css">
<style>
.loader {

  height: 80px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

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
         <div class="row" >
  <div class="col-12 col-lg-6 order2">
      @php
            $userCurrency = currency();

            $invalidChannels = [];
        @endphp
        <div class=" bg-gray200 mt-30 rounded-lg border p-15">
         <h2 class="section-title">Fill all the Details here:</h2>
          <form action="/payments/payment-request" method="post" class=" mt-25" >
            {{ csrf_field() }}
            <input type="hidden" name="order_id"  value="{{ $order->id ?? 0 }}">
            <input type="text" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >

            <div class="row d-none">
                @if(!empty($paymentChannels))
                @php

                @endphp
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 col-lg-6 mb-20 charge-account-radio">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                                  <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" style="display: block;    visibility: visible;">
                                  <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $paymentChannel->image }}" class="ml-30" width="" height="35px" alt="">

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
                                <img loading="lazy" src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
<center><div class="loader mt-50" id="loader" style="dispay:none;z-index: 550; ">
    <img loading="lazy" width= '80px' height= '80px' src="{{ asset('assets/default/img/loading.gif')}}">
    <br>
    <h3>Please do not refresh or close the page while your payment is being processed...</h3>
    </div></center>
<button type="button" id="paymentSubmit"  class="btn btn-sm btn-primary ">{{ trans('public.start_payment') }}</button>

        </form>
        </div>

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

                  <div class="cart-item mt-10">
                      @php
                      $extra_amount = 0;
                    @endphp
                @foreach($carts as $key=>$cart)

                  @if(!empty($cart->exists))
                    <div class="row mt-10 mx-5 shadow p-0  cart-row" style="
    border-radius: 12px;
">
                        <div class="col-12 col-lg-10  mb-md-0">
                            <div class="webinar-card webinar-list-cart row">
                                <div class="col-4 p-0">
                                    <div class="image-box" >
                                        @php
                                            $cartItemInfo = $cart->getItemInfo();

                                           $extra_amount += $cart['extra_amount'];
                                        @endphp
                                        <img loading="lazy" src="{{ $cartItemInfo['imgPath'] ?? '' }}" class="img-cover" alt="user avatar" style="
    border-top-right-radius: 0px !important;
    border-bottom-right-radius: 0px !important;
">
                                    </div>
                                </div>

                                <div class="col-8 p-0">
                                    <div class="webinar-card-body p-0 w-100 h-100 d-flex ">
                                        <div class="d-flex flex-column">
                                            <a href="{{ $cartItemInfo['itemUrl'] ?? '#!' }}" target="_blank">
                                                <h3 class="font-16 font-weight-bold text-dark-blue">{{ $cartItemInfo['title'] }}</h3>
                                            </a>

                            @if(!empty($cartItemInfo['discountPrice']))
                                <span class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}/-</span>
                                <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}/-</span>
                            @else
                                <span class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}/-</span>
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
                                        <a href="/cart/{{$cart->id }}/delete" class="delete-action btn-cart-list-delete d-flex align-items-center justify-content-center">

<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.66667 3.33333H10C10 2.89131 9.8244 2.46738 9.51184 2.15482C9.19928 1.84226 8.77536 1.66667 8.33333 1.66667C7.89131 1.66667 7.46738 1.84226 7.15482 2.15482C6.84226 2.46738 6.66667 2.89131 6.66667 3.33333ZM5 3.33333C5 2.44928 5.35119 1.60143 5.97631 0.976311C6.60143 0.35119 7.44928 0 8.33333 0C9.21739 0 10.0652 0.35119 10.6904 0.976311C11.3155 1.60143 11.6667 2.44928 11.6667 3.33333H15.8333C16.0543 3.33333 16.2663 3.42113 16.4226 3.57741C16.5789 3.73369 16.6667 3.94565 16.6667 4.16667C16.6667 4.38768 16.5789 4.59964 16.4226 4.75592C16.2663 4.9122 16.0543 5 15.8333 5H15.0983L14.36 13.6167C14.289 14.4487 13.9083 15.2238 13.2932 15.7886C12.6781 16.3534 11.8734 16.6668 11.0383 16.6667H5.62833C4.79326 16.6668 3.98856 16.3534 3.37346 15.7886C2.75835 15.2238 2.37765 14.4487 2.30667 13.6167L1.56833 5H0.833333C0.61232 5 0.400358 4.9122 0.244078 4.75592C0.0877973 4.59964 0 4.38768 0 4.16667C0 3.94565 0.0877973 3.73369 0.244078 3.57741C0.400358 3.42113 0.61232 3.33333 0.833333 3.33333H5ZM10.8333 8.33333C10.8333 8.11232 10.7455 7.90036 10.5893 7.74408C10.433 7.5878 10.221 7.5 10 7.5C9.77899 7.5 9.56702 7.5878 9.41074 7.74408C9.25446 7.90036 9.16667 8.11232 9.16667 8.33333V11.6667C9.16667 11.8877 9.25446 12.0996 9.41074 12.2559C9.56702 12.4122 9.77899 12.5 10 12.5C10.221 12.5 10.433 12.4122 10.5893 12.2559C10.7455 12.0996 10.8333 11.8877 10.8333 11.6667V8.33333ZM6.66667 7.5C6.44565 7.5 6.23369 7.5878 6.07741 7.74408C5.92113 7.90036 5.83333 8.11232 5.83333 8.33333V11.6667C5.83333 11.8877 5.92113 12.0996 6.07741 12.2559C6.23369 12.4122 6.44565 12.5 6.66667 12.5C6.88768 12.5 7.09964 12.4122 7.25592 12.2559C7.4122 12.0996 7.5 11.8877 7.5 11.6667V8.33333C7.5 8.11232 7.4122 7.90036 7.25592 7.74408C7.09964 7.5878 6.88768 7.5 6.66667 7.5Z" fill="#32BA7C"/>
</svg>

                                        </a>

                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                    @else
                    <div class="row mx-5 p-0 shadow cart-row" style="
    border-radius: 12px;
">
                        <div class="col-12 col-lg-10  mb-md-0">
                            <div class="webinar-card webinar-list-cart row">
                                <div class="col-4 p-0">
                                    <div class="image-box" style="height: 85px !important;">

                                         @php

                                 $cartItemInfo = $cart[0];

                                        @endphp
                                        <img loading="lazy" src="{{ $cartItemInfo['thumbnail'] ?? '' }}" class="img-cover" alt="user avatar" style="
    border-top-right-radius: 0px !important;
    border-bottom-right-radius: 0px !important;
">
                                    </div>
                                </div>

                                <div class="col-8 p-0">
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

<svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.66667 3.33333H10C10 2.89131 9.8244 2.46738 9.51184 2.15482C9.19928 1.84226 8.77536 1.66667 8.33333 1.66667C7.89131 1.66667 7.46738 1.84226 7.15482 2.15482C6.84226 2.46738 6.66667 2.89131 6.66667 3.33333ZM5 3.33333C5 2.44928 5.35119 1.60143 5.97631 0.976311C6.60143 0.35119 7.44928 0 8.33333 0C9.21739 0 10.0652 0.35119 10.6904 0.976311C11.3155 1.60143 11.6667 2.44928 11.6667 3.33333H15.8333C16.0543 3.33333 16.2663 3.42113 16.4226 3.57741C16.5789 3.73369 16.6667 3.94565 16.6667 4.16667C16.6667 4.38768 16.5789 4.59964 16.4226 4.75592C16.2663 4.9122 16.0543 5 15.8333 5H15.0983L14.36 13.6167C14.289 14.4487 13.9083 15.2238 13.2932 15.7886C12.6781 16.3534 11.8734 16.6668 11.0383 16.6667H5.62833C4.79326 16.6668 3.98856 16.3534 3.37346 15.7886C2.75835 15.2238 2.37765 14.4487 2.30667 13.6167L1.56833 5H0.833333C0.61232 5 0.400358 4.9122 0.244078 4.75592C0.0877973 4.59964 0 4.38768 0 4.16667C0 3.94565 0.0877973 3.73369 0.244078 3.57741C0.400358 3.42113 0.61232 3.33333 0.833333 3.33333H5ZM10.8333 8.33333C10.8333 8.11232 10.7455 7.90036 10.5893 7.74408C10.433 7.5878 10.221 7.5 10 7.5C9.77899 7.5 9.56702 7.5878 9.41074 7.74408C9.25446 7.90036 9.16667 8.11232 9.16667 8.33333V11.6667C9.16667 11.8877 9.25446 12.0996 9.41074 12.2559C9.56702 12.4122 9.77899 12.5 10 12.5C10.221 12.5 10.433 12.4122 10.5893 12.2559C10.7455 12.0996 10.8333 11.8877 10.8333 11.6667V8.33333ZM6.66667 7.5C6.44565 7.5 6.23369 7.5878 6.07741 7.74408C5.92113 7.90036 5.83333 8.11232 5.83333 8.33333V11.6667C5.83333 11.8877 5.92113 12.0996 6.07741 12.2559C6.23369 12.4122 6.44565 12.5 6.66667 12.5C6.88768 12.5 7.09964 12.4122 7.25592 12.2559C7.4122 12.0996 7.5 11.8877 7.5 11.6667V8.33333C7.5 8.11232 7.4122 7.90036 7.25592 7.74408C7.09964 7.5878 6.88768 7.5 6.66667 7.5Z" fill="#32BA7C"/>
</svg>

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

                    <section class="">

                        @if(!empty($cartInstallment1))

                     @foreach($cartInstallment1 as $cartInstallment)
                            <form action="/carts/coupon/validate" method="Post">
                    {{ csrf_field() }}
                    <div class="row" >
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
                    <div class="row" style="display: flex;justify-content: space-evenly;">
                    <div class="col-11 col-lg-9">
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-25 {{ !empty(session('total_discount')) ? 'is-valid' : '' }}" style="border-radius: 20px !important;"
                        value="{{ !empty(session('coupon')) ? session('coupon') : '' }}"
                               placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div>
                    <div class="col-5 col-lg-3 botton-1" style="margin-top: 3px;margin-right:-21px;">
                    <button type="submit" id="checkCoupon" class="btn btn-sm btn-primary mt-25" style=" height: 35px !important; border-radius: 20px !important;">{{ trans('cart.validate') }}</button></div></div>
                </form>
                <div class="m-20 border radius-10">

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
                                <span class="font-14 text-gray font-weight-bold"><span id="totalAmount">{{ !empty(session('total_amount')) ? session('total_amount') : (!empty($cartInstallment1->id)?handlePrice($cartInstallment1->total) : handlePrice($total)) }}</span></span>
                            </div>
                            @else

                            @endif
                             <div class="d-flex align-items-center justify-content-between ">

                            <button type="submit" id="autosubmit" class="autosubmit btn btn-sm btn-primary mt-15 d-none">{{ trans('cart.checkout') }}</button>

            </div>
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

                <input type="hidden" name="order_id"  value="{{$order->id}}">
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
                       <input type="hidden" name="pin_code" id="user_pin_code" value="">
                        <input type="hidden" name="address" id="user_address" value="">
                        <input type="hidden" name="message" id="user_message" value="">
                @endif
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
<script defer src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script defer src="{{ asset('js/unified-payment.js') }}"></script>
<script defer>

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
        Country: countryName || null,
        StateProvince: stateName || null,
        City: cityName || null,
        pin_code: pin_code || null,
        address: address || null,
        message: message || null,
        discount_id: {{ session('discount_id') ?? 'null' }}
    };

    initiatePayment('cart',{{ $order->id }}, userDetails);
});
</script>

<script src="/assets2/default/js/parts/cart.min.js"></script>
@endpush
