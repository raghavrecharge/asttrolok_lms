@extends('web.default2'.'.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="cart-banner position-relative text-center">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
        <span class="payment-hint font-20 text-white d-block">{{ handlePrice($total) . ' ' .  trans('cart.for_items',['count' => $count]) }}</span>
    </section>

    <section class="container mt-45">

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
        @endphp

        <h2 class="section-title">Please Fill The Form</h2>

        <form action="/payments/payment-request" method="post" class=" mt-25">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">
             @if(!auth()->check())
            <input type="text" name="name" value="" id="customer_name" placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" value="" id="customer_email" placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" value="" id="customer_number" placeholder="Contact Number" class="form-control mt-25 mb-25" >
             <h2 class="section-title">{{ trans('financial.select_a_payment_gateway') }}</h2>
             <br>
             @endif
            
            <div class="row">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                                <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}">
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

                <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit">
                    <label for="offline" class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets2/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                            {{ trans('financial.account') }}
                            <span class="font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>

                        <span class="mt-5">{{ handlePrice($userCharge) }}</span>
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


            <div class="d-flex align-items-center justify-content-between mt-45">
                <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }} {{ handlePrice($total) }}</span>
                <button type="button"  class="btn btn-sm btn-primary paymentSubmit">{{ trans('public.start_payment') }}</button>
            </div>
        </form>

        @if(!empty($razorpay) and $razorpay)
        
            <form action="/payments/verify/Razorpay" method="get" id="razorpayview">
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="name" value="" id="user_name" placeholder="Name" class="form-control mt-25 " required>
                <input type="hidden" name="email" value="" id="user_email" placeholder="Email" class="form-control mt-25 " required>
                <input type="hidden" name="number" value="" id="user_number" placeholder="Contact Number" class="form-control mt-25 mb-25" required>
                @if(auth()->check())
               
                <script src="https://checkout.razorpay.com/v1/checkout.js"
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
                
                 @endif
                 
            </form>
        @endif
    </section>

@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/payment.min.js"></script>
    
     <script>
     
    //  function IsEmail(email) {
    //     var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    //     if(!regex.test(email)) {
    //         document.getElementById("customer_email").value =email;
    //         var emailvalidation ='Email field is required';
    //         $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
    //       return false;
    //     }else{
    //       return true;
    //     }
    //   }
  
    function addscript(){
            
        var name = '';
        var email = '';
        var mobile = '';
         name = document.getElementById("customer_name").value ;
         email = document.getElementById("customer_email").value;
         mobile = document.getElementById("customer_number").value;
        
        $('.textdanger').remove();
        // $('#customer_email').html('');
        // $('#customer_number').html('');
        if(name ===''){
            $('#paymentSubmit').prop('disabled', false);
            $("input:radio").attr("checked", false);
            var namevalidation ='Name field is required';
            $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');
             
        }
         if(email ===''){
            $('#paymentSubmit').prop('disabled', false);
            $("input:radio").attr("checked", false);
            var emailvalidation ='Email field is required';
            $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
         if(mobile ===''){
            $('#paymentSubmit').prop('disabled', false);
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
          
     var s = document.createElement( 'script' );
        s.setAttribute( 'src', "https://checkout.razorpay.com/v1/checkout.js" );
        s.setAttribute( 'id', "razorpay_script" );
        s.setAttribute( 'data-key',datakey );
        s.setAttribute( 'data-amount', dataamount );
        s.setAttribute( 'data-buttontext', databuttontext );
        s.setAttribute( 'data-currency', datacurrency );
        s.setAttribute( 'data-name', 'Asttrolok' );
        s.setAttribute( 'data-description', datadescription );
        s.setAttribute( 'data-image', dataimage);
        s.setAttribute( 'data-theme.color', "#43d477" );
        s.setAttribute( 'data-prefill.name', dataprefillname );
        s.setAttribute( 'data-prefill.email', dataprefillemail );
        s.setAttribute( 'data-prefill.contact', dataprefillcontact );
        document.querySelector("#razorpayview").appendChild( s );
        } 
        }
    $(document).ready(function(){
        
         $('body').on('click', '.paymentSubmit', function (e) {
            //  addscript();
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
</script>
@endpush
