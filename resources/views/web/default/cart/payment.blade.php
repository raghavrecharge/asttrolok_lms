@extends(getTemplate().'.layouts.app')

@push('styles_top')

<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-course-payment.css">
@endpush
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
    <section class="cart-banner position-relative text-center homehide">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
        <span class="payment-hint font-20 text-white d-block">{{ handlePrice($total) . ' ' .  trans('cart.for_items',['count' => $count]) }}</span>
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
            $webinar_id =0;
        @endphp
        @foreach($carts as $cart)
        @php
        $webinar_id= $cart->webinar_id;
        @endphp

        @endforeach

        <h2 class="section-title">Please validate any coupon code before use</h2>
        <form action="/cart/coupon/validate2" method="Post">
                    {{ csrf_field() }}
                    <div class="row" style="display: flex;justify-content: space-evenly;">
                    <div class="col-11 col-lg-3">
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-10 {{ session('discountCoupon') ? (session('discountCoupon')=='no' ? 'is-invalid' : 'is-valid') : '' }}" value="{{ session('discountCoupon') ? (session('discountCoupon')=='no' ? '' : session('discountCoupon')) : '' }}"
                         placeholder="{{ trans('cart.enter_your_code_here') }}" style="border-radius: 20px !important;">
                        <input type="hidden" name="item_id" id="web_id1" value="{{$webinar_id}}" class="form-control mt-25" >
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div>
                    <div class="col-5 col-lg-3 " style="margin-top: 3px;margin-right:-21px; position: absolute !important; right: 21px;">
                    <button type="submit" id="checkCoupon1" class="btn btn-sm btn-primary mt-10" style=" height: 35px !important; border-radius: 20px !important;">{{ trans('cart.validate') }}</button>
                    </div></div>
                </form>

        <h2 class="section-title">Fill all the Details here:</h2>
        {{session()->forget('discountCoupon')}}
        <form action="/payments/payment-request" method="post" class=" mt-25">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <input type="text" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="{{ auth()->check() ? auth()->user()->email  :'' }}" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >
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
        @if(!empty($razorpay) and $razorpay)

            <form action="/payments/verify/Razorpay" method="get" id="razorpayview">
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="name" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id="user_name" placeholder="Name" class="form-control mt-25 " required>
                <input type="hidden" name="email" value="{{ auth()->check() ? auth()->user()->email :'' }}" id="user_email" placeholder="Email" class="form-control mt-25 " required>
                <input type="hidden" name="number" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" id="user_number" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                @if(auth()->check())

                <script  src="https://checkout.razorpay.com/v1/checkout.js"
                        data-key="{{ env('RAZORPAY_API_KEY') }}"
                        data-amount="{{ (int)($order->total_amount * 100) }}"
                        data-buttontext="product_price"
                        data-description="Rozerpay"
                        data-currency="{{ currency() }}"
                        data-image="{{ $generalSettings['logo'] }}"
                        data-prefill.name="{{ auth()->user()->full_name }}"
                        data-prefill.email="{{ auth()->user()->email }}"
                        data-prefill.contact="{{ auth()->user()->mobile }}"
                        data-theme.color="#43d477">
                </script>
                @else
                 <input type="hidden" name="razorpay_payment_id" value="" id="razorpay_payment_id" class="form-control mt-25 mb-25">
                 <input type="hidden" name="razorpay_signature" value="" id="razorpay_signature" class="form-control mt-25 mb-25">
                 @endif

            </form>
        @endif
    </section>

@endsection

@push('scripts_bottom')

<script >
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>
   <script  src="{{ config('app.js_css_url') }}/assets2/default/js/parts/payment.min.js"></script>
    <script  src="https://checkout.razorpay.com/v1/checkout.js"></script>

     <script  >

     $("#loader").css("display", "none");

    function addscript(){

        var name = '';
        var email = '';
        var mobile = '';
         name = document.getElementById("customer_name").value ;
         email = document.getElementById("customer_email").value;
         mobile = document.getElementById("customer_number").value;

        $('.textdanger').remove();

        if(name ===''){

            $("input:radio").attr("checked", false);
            var namevalidation ='Name field is required';
            $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');

        }
         if(email ===''){

            var emailvalidation ='Email field is required';
            $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
         if(mobile ===''){

            $("input:radio").attr("checked", false);
            var mobilevalidation ='Mobile field is required';
            $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
        }else{
            var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(!regex.test(email)) {
                $("input:radio").attr("checked", false);
                document.getElementById("customer_email").value =email;
                var emailvalidation ='Enter Valid Email Address';
                $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
               return false;
            }

            if (mobile.length < 9) {
                  $('#paymentSubmit').prop('disabled', false);

                    var mobilevalidation ='Enter Valid Mobile Number';
                    $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
                return false;
              }

            $("input:radio").attr("checked", true);
          var  datakey="<?php echo  env('RAZORPAY_API_KEY'); ?>";
          var   dataamount="<?php echo (int)($order->total_amount * 100); ?>";
          var   databuttontext="product_price";
          var   datadescription="Rozerpay";
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
          }

      $.ajax({
            method: 'post',
            url: url,
            data: data,
        }).done(function(response, status){

        }).fail(function(jqXHR, textStatus, errorThrown){

        });

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

            document.body.classList.add('disabled-page');
            document.getElementById('loader').style.display = 'block';
            document.documentElement.style.overflow = 'hidden';

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

        }
        }
    $(document).ready(function(){

         $('body').on('click', '.paymentSubmit', function (e) {

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
</script>

@endpush
