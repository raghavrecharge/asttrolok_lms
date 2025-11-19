@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <style>
.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;
  /*width: 120px;*/
  height: 120px;
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
/*}*/

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
@endpush


@section('content')
    <section class="cart-banner position-relative text-center">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
       @php
        $priceWithDiscount = handleCoursePagePrice($webinar->getPrice());
        @endphp
        @if($priceWithDiscount)
        @php
        if(!$total)
        $total=$priceWithDiscount['price'];
        
        @endphp
        
        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($total) }} item 1</span>
        @else
        @php
        $total=$webinar->price;
        @endphp
        <span class="payment-hint font-20 text-white d-block"> {{ handlePrice($total) }}  item 1</span>
        @endif
    </section>

    <section class="container mt-45" id="toggleScroll">

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
            $webinar_id =$webinar->id;
        @endphp

        <h2 class="section-title">Please validate any coupon code before use</h2>
        <form action="/cart/coupon/validate2" method="Post">
                    {{ csrf_field() }}
                    <div class="row"> 
                    <div class="col-8 col-lg-3"> 
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-10 {{ session('discountCoupon') ? (session('discountCoupon')=='no' ? 'is-invalid' : 'is-valid') : '' }}" value="{{ session('discountCoupon') ? (session('discountCoupon')=='no' ? '' : session('discountCoupon')) : '' }}"
                         placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <input type="hidden" name="item_id" id="web_id1" value="{{$webinar_id}}" class="form-control mt-25" >
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div>
                    <div class="col-4 col-lg-3 ">
                    <button type="submit" id="checkCoupon1" class="btn btn-sm btn-primary mt-10">{{ trans('cart.validate') }}</button>
                    </div></div>
                </form> 
                 
        <h2 class="section-title">Please Fill The Form</h2>
{{session()->forget('discountCoupon')}}
        <form action="/payments/payment-request" method="post" class=" mt-25"  id="razor-pay-request">
            {{ csrf_field() }}
           {{-- <input type="hidden" name="order_id" value="{{ $order->id }}"> --}}
           <input type="hidden" name="webinar_id" value="{{ $webinar->id }}">
             
            <input type="text" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >
             <h2 class="section-title d-none">{{ trans('financial.select_a_payment_gateway') }}</h2>
            
            
            <div class="row d-none">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                                <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" >
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-20 p-lg-20 d-flex flex-column align-items-center justify-content-center">
                                    <img src="{{ $paymentChannel->image }}" width="120" height="60" alt="">

                                    <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                        {{ trans('financial.pay_via') }}
                                        <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                    </p>
                                </label>
                            </div>
                        @else
                            @php
                                $invalidChannels[] = $paymentChannel;
                            @endphp
                        @endif
                    @endforeach
                @endif

               {{-- <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit">
                    <label for="offline" class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets2/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                            {{ trans('financial.account') }}
                            <span class="font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>

                        <span class="mt-5">{{ handlePrice($userCharge) }}</span>
                    </label>
                </div> --}}
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

                <div class="row ">
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
            

             <center><div class="loader mt-50" id="loader" style="dispay:none ">
            <img width= '80px' height= '80px' src="{{ config('app.js_css_url') }}/assets/default/img/loading.gif">
            <br>
            <h3>Please do not refresh or close the page while your payment is being processed...</h3>
            </div></center>

            <div class="d-flex align-items-center justify-content-between mt-45">
                <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }} {{ handlePrice($total) }}</span>
                <button type="button" id="paymentSubmit"  class="btn btn-sm btn-primary ">{{ trans('public.start_payment') }}</button>
            </div> 
        </form>

      
            <form action="/payments/verify/Razorpay" method="get" id="razorpayvieww">
                <input type="hidden" name="order_id" value="1">
                <input type="hidden" name="webinar_id" value="{{ $webinar->id }}">
                <input type="hidden" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="user_name" placeholder="Name" class="form-control mt-25 " required>
                <input type="hidden" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="user_email" placeholder="Email" class="form-control mt-25 " required>
                <input type="hidden" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="user_number" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                <input type="hidden" name="discount_id" value="{{ session('discountCouponId') ? session('discountCouponId') : 0 }}" id="discount_id" placeholder="discountCouponId" class="form-control mt-25 mb-25" >
                
                 <input type="hidden" name="razorpay_payment_id" value="" id="razorpay_payment_id" class="form-control mt-25 mb-25">
                 <input type="hidden" name="razorpay_signature" value="" id="razorpay_signature" class="form-control mt-25 mb-25">
                
                 
            </form>
        
    </section>


@endsection

@push('scripts_bottom')
<script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/payment.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    $(document).ready(function() {
        // Restrict mobile number input to digits only and max 10 digits
        $('#customer_number').on('keypress', function(e) {
            var $this = $(this);
            var regex = /^[0-9]$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            
            if ($this.val().length >= 10) {
                e.preventDefault();
                return false;
            }
            
            // Prevent numbers starting with 0-5
            if ($this.val().length === 0 && e.charCode >= 48 && e.charCode <= 53) {
                e.preventDefault();
                return false;
            }
            
            if (regex.test(str)) {
                return true;
            }
            
            e.preventDefault();
            return false;
        });

        // Payment button click handler
        $('#paymentSubmit').on('click', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            $('.error-message').text('');
            
            // Get form values
            var name = $('#customer_name').val().trim();
            var email = $('#customer_email').val().trim();
            var mobile = $('#customer_number').val().trim();
            
            // Validation
            var isValid = true;
            
            if (name === '') {
                $('#name-error').text('Name field is required');
                isValid = false;
            }
            
            if (email === '') {
                $('#email-error').text('Email field is required');
                isValid = false;
            } else {
                var emailRegex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (!emailRegex.test(email)) {
                    $('#email-error').text('Enter a valid email address');
                    isValid = false;
                }
            }
            
            if (mobile === '') {
                $('#mobile-error').text('Mobile field is required');
                isValid = false;
            } else if (mobile.length < 10) {
                $('#mobile-error').text('Enter a valid 10-digit mobile number');
                isValid = false;
            }
            
            if (!isValid) {
                return false;
            }
            
            // Disable button to prevent double clicks
            $(this).prop('disabled', true);
            
            // Send webhook data
            $.ajax({
                method: 'POST',
                url: "{{ url('/webhook-url') }}",
                data: {
                    name: name,
                    email: email,
                    mobile: mobile,
                    course_title: "{{ $webinar->title }}",
                    _token: "{{ csrf_token() }}"
                }
            });
            
            // Initialize Razorpay
            var options = {
                key: "{{ env('RAZORPAY_API_KEY') }}",
                amount: "{{ (int)(preg_replace('/[^\d.]/', '', handlePrice($total * 100))) }}",
                currency: "{{ currency() }}",
                name: 'Asttrolok',
                description: "Payment for the course {{ $webinar->title }}",
                image: "{{ $generalSettings['logo'] ?? '' }}",
                handler: function(response) {
                    // Show loader
                    $('#loader').show();
                    $('#paymentSubmit').prop('disabled', true);
                    
                    // GTM tracking
                    @if(auth()->check())
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            'event': 'purchase',
                            'transaction_id': response.razorpay_payment_id,
                            'user_id': {{ auth()->id() }},
                            'value': {{ $total }},
                            'currency': "{{ currency() }}",
                            'course': '{{ $webinar->id }}'
                        });
                    @else
                        fetch('/get-user-id', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                name: name,
                                email: email,
                                phone: mobile
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            window.dataLayer = window.dataLayer || [];
                            window.dataLayer.push({
                                'event': 'purchase',
                                'transaction_id': response.razorpay_payment_id,
                                'user_id': data.user_id,
                                'value': {{ $total }},
                                'currency': "{{ currency() }}",
                                'course': '{{ $webinar->id }}'
                            });
                        })
                        .catch(err => console.error('User lookup failed:', err));
                    @endif
                    
                    // Fill verification form and submit
                    $('#verify_name').val(name);
                    $('#verify_email').val(email);
                    $('#verify_number').val(mobile);
                    $('#razorpay_payment_id').val(response.razorpay_payment_id);
                    $('#razorpay_signature').val(response.razorpay_signature);
                    $('#razorpay-verify-form').submit();
                },
                modal: {
                    ondismiss: function() {
                        $('#paymentSubmit').prop('disabled', false);
                        alert('Payment cancelled');
                    }
                },
                prefill: {
                    name: name,
                    email: email,
                    contact: mobile
                },
                notes: {
                    name: name,
                    item: 'course'
                },
                theme: {
                    color: "#43d477"
                }
            };
            
            var rzp = new Razorpay(options);
            rzp.open();
            
            // Re-enable button after Razorpay modal opens
            setTimeout(function() {
                $('#paymentSubmit').prop('disabled', false);
            }, 1000);
        });
    });
</script>
@endpush