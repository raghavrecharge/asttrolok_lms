@extends(getTemplate().'.layouts.app')

@push('styles_top')

<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-course-payment.css">
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
@endpush

@section('content')
    <section class="cart-banner position-relative text-center homehide">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
        @php
        $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
        @endphp
        @if($priceWithDiscount)
        @php
        if(!$total)
        $total=$priceWithDiscount['price'];

        @endphp

        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($total) }} item 1</span>
        @else
        @php
        $total=$subscription->price;
        @endphp
        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($total) }}  item 1</span>
        @endif
   </section>

    <section class="container mt-45">
<div class="price-box shadow-xs mb-20"> <span class="font-30 font-weight-bold">Total Amount </span> <span class="f-right font-30 text-primary" style="    float: right;">{{handlePrice($total)}}/-</span> </div>
        @if(!empty($totalCashbackAmount))
            <div class="d-flex align-items-center mb-25 p-15 success-transparent-alert">
                <div class="success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=""></i>
                </div>

                <div class="ml-10">
                    <div class="font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class="font-12 ">{{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback',['amount' => handlePrice($totalCashbackAmount)]) }}</div>
                </div>
            </div>
        @endif

         @php
            $userCurrency = currency();
            $invalidChannels = [];
            $subscription_id =$subscription->id;
        @endphp

         <h2 class="section-title">Please validate any coupon code before use</h2>

        <h2 class="section-title">Fill all the Details here:</h2>
        {{session()->forget('discountCoupon')}}
        <form action="/payments/payment-request" method="post" class=" mt-25"  id="razor-pay-request">
            {{ csrf_field() }}

           <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">

           <input type="text" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="{{ auth()->check() ? auth()->user()->email  :'' }}" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="password" name="password" id="customer_password" placeholder="Create Password" class="form-control mt-25" required>
            <input type="password" name="password_confirmation" id="customer_password_confirmation" placeholder="Confirm Password" class="form-control mt-25" required>
            <div class="invalid-feedback">Passwords do not match!</div>
            <input type="number" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >

        @if(!empty($subscription->razorpay_plan_id) && auth()->check())
        <div class="mt-15 mb-15 p-15" style="border: 1px solid #e0e0e0; border-radius: 8px; background: #f9f9f9;">
            <p class="font-14 font-weight-bold mb-10">Choose Payment Method:</p>
            <label style="display:block; padding:10px; margin-bottom:8px; border:1px solid #ddd; border-radius:6px; background:#fff; cursor:pointer;">
                <input type="radio" name="payment_mode" value="one_time" checked style="margin-right:8px;">
                <strong>Pay {{ handlePrice($subscription->getPrice()) }} for this month only</strong>
                <br><small style="color:#666; margin-left:24px;">Manual renewal each month</small>
            </label>
            <label style="display:block; padding:10px; border:1px solid #ddd; border-radius:6px; background:#fff; cursor:pointer;">
                <input type="radio" name="payment_mode" value="autopay" style="margin-right:8px;">
                <strong>Enable AutoPay — {{ handlePrice($subscription->getPrice()) }}/month</strong>
                <br><small style="color:#666; margin-left:24px;">Auto-deducted every month, cancel anytime</small>
            </label>
        </div>
        @endif

             <h2 class="section-title d-none">Payment Option</h2>
             <br>

            <div class="row d-none">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))

                            <div class="col-12 col-lg-4 mb-10 charge-account-radio">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                                <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}"  style="display: block;    visibility: visible;">

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
            <center><div class="loader mt-50" id="loader" style="dispay:none ">
    <img loading="lazy" width= '80px' height= '80px' src="{{ asset('public/assets/default/img/loading.gif')}}">
    <br>
    <h3>Please do not refresh or close the page while your payment is being processed...</h3>
    </div></center>

            <div class="d-flex align-items-center justify-content-between">

                <button type="button" id="paymentSubmit" class="btn btn-sm btn-primary" style="width:;">

                    Pay Now
                </button>
            </div>
        </form>

            <form action="/payments/verify/Razorpay" method="get" id="razorpayvieww">
                <input type="hidden" name="order_id" value="2">
                <input type="hidden" name="subscription_id" value="{{ $subscription->id }}">
               <input type="hidden" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="user_name" placeholder="Name" class="form-control mt-25 " required>
                <input type="hidden" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="user_email" placeholder="Email" class="form-control mt-25 " required>
                <input type="hidden" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="user_number" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                <input type="hidden" name="discount_id" value="{{ session('discountCouponId') ? session('discountCouponId') : 0 }}" id="discount_id" placeholder="discountCouponId" class="form-control mt-25 mb-25" >

                 <input type="hidden" name="razorpay_payment_id" value="" id="razorpay_payment_id" class="form-control mt-25 mb-25">
                 <input type="hidden" name="razorpay_signature" value="" id="razorpay_signature" class="form-control mt-25 mb-25">

            </form>
<div id="paymentLoader">
        <div class="spinner"></div>
        </div>
    </section>

@endsection

@push('scripts_bottom')
<script defer src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script defer src="/assets/design_1/js/unified-payment.js"></script>
<script defer src="/js/subscription-payment.js"></script>
<script defer>
    const loaderEl = document.getElementById('paymentLoader');

    function showPaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'block';
    }

    function hidePaymentLoader() {
        if (loaderEl) loaderEl.style.display = 'none';
    }
document.getElementById('paymentSubmit').addEventListener('click', function(e) {
    e.preventDefault();

    const name = document.getElementById('customer_name').value.trim();
    const email = document.getElementById('customer_email').value.trim();
    const number = document.getElementById('customer_number').value.trim();
    const password = document.getElementById('customer_password').value;
    const confirmPassword = document.getElementById('customer_password_confirmation').value;

    if (!name || !email || !number || !password || !confirmPassword) {
        alert('Please fill in all required fields.');
        return;
    }
    if (password !== confirmPassword) {
        document.getElementById('customer_password_confirmation').classList.add('is-invalid');
        alert('Passwords do not match!');
        return;
    }
    if (password.length < 6) {
        alert('Password must be at least 6 characters.');
        return;
    }
    document.getElementById('customer_password_confirmation').classList.remove('is-invalid');

    const userDetails = {
        name: name,
        email: email,
        number: number,
        password: password,
        discount_id: @json(session('discountCouponId'))
    };

    showPaymentLoader();

    // Check payment mode
    const paymentModeEl = document.querySelector('input[name="payment_mode"]:checked');
    const paymentMode = paymentModeEl ? paymentModeEl.value : 'one_time';

    if (paymentMode === 'autopay') {
        initiateAutoPaySubscription({{ $subscription->id }}, userDetails);
    } else {
        initiatePayment('subscription', {{ $subscription->id }}, userDetails);
    }
});
</script>
@endpush
