@extends('web.default2.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/video/video-js.min.css">
@endpush
@push('styles_top')
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

    <div class="container pt-50 mt-10">
        <div class="text-center">
            <h1 class="font-36">{{ trans('update.select_an_installment_plan') }}</h1>
            <p class="mt-10 font-16 text-gray">{{ trans('update.please_select_an_installment_plan_in_order_to_finalize_your_purchase') }}</p>
        </div>

        <!--<div class="d-flex align-items-center flex-column flex-lg-row mt-50 border rounded-lg p-15 p-lg-25">-->
        <!--    <div class="default-package-icon">-->
        <!--        <img src="/assets2/default/img/become-instructor/default.png" class="img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">-->
        <!--    </div>-->

        <!--    <div class="ml-lg-25 w-100 mt-20 mt-lg-0">-->
        <!--        <h2 class="font-24 font-weight-bold text-dark-blue">{{ $overviewTitle }}</h2>-->

        <!--        <div class="d-flex flex-wrap align-items-center justify-content-between w-100">-->

        <!--            <div class="d-flex align-items-center mt-20">-->
        <!--                <i data-feather="check-square" width="20" height="20" class="text-gray"></i>-->
        <!--                <span class="font-14 text-gray ml-5">{{ handlePrice($cash) }} {{ trans('update.cash') }}</span>-->
        <!--            </div>-->

        <!--            <div class="d-flex align-items-center mt-20">-->
        <!--                <i data-feather="menu" width="20" height="20" class="text-gray"></i>-->
        <!--                <span class="font-14 text-gray ml-5">{{ $plansCount }} {{ trans('update.installment_plans') }}</span>-->
        <!--            </div>-->

        <!--            <div class="d-flex align-items-center mt-20">-->
        <!--                <i data-feather="dollar-sign" width="20" height="20" class="text-gray"></i>-->
        <!--                <span class="font-14 text-gray ml-5">{{ handlePrice($minimumAmount) }} {{ trans('update.minimum_installment_amount') }}</span>-->
        <!--            </div>-->

        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->

        @foreach($installments as $installmentRow)
            @include('web.default2.installment.card',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach

        @php
        if(isset($mayank)){
            $userCurrency = currency();
            $invalidChannels = [];
            
        @endphp
        <div id="Payment-Option" class=" bg-gray200 mt-30 rounded-lg border p-15">
            
         <h2 class="section-title">Payment Option</h2>
          <form action="/payments/payment-request" method="post" id="razor-pay-request" class=" mt-25 " >
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id ?? 0 }}">
            <input type="hidden" name="installment_id" value="{{ $installment->id ?? null }}">
             <input type="hidden" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 " required>
             <input type="hidden" name="totalDiscount" value="{{!empty($totalDiscount) ? $totalDiscount : 0}}"  class="form-control mt-25 mb-25 " required>
             <input type="hidden" name="price" value="<?php echo (number_format(((($installments->first()->upfront)*$itemPrice) /100), 2, '.', '')); ?>">
             <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " >
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 ">
            <div class="form-group">
                            
                            <input name="name" type="text" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id='customer_name'  placeholder="Name" class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                           
                            <input name="email" id='customer_email'  placeholder="Email"  type="text" value="{{ auth()->check() ? auth()->user()->email :'' }}"  class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <input name="number" id='customer_number'  placeholder="Contact Number" type="text" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" class="form-control @error('number') is-invalid @enderror">
                            @error('number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
            <!--<input type="text" name="name" id='customer_name'  placeholder="Name" class="form-control mt-25 " >-->
            <!--<input type="email" name="email" id='customer_email'  placeholder="Email" class="form-control mt-25 " >-->
            <!--<input type="number" name="number" id='customer_number'  placeholder="Contact Number" class="form-control mt-25 mb-25" > -->
            

            <div class="row d-none">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 col-lg-6 mb-20 charge-account-radio">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                                  <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" style="display: block;    visibility: visible;">
                                  <img src="{{ config('app.img_dynamic_url') }}{{ $paymentChannel->image }}" width="120" height="60" alt="{{ $paymentChannel->title }}">
                                    <div>
                                    <!--<p class="mt-30 mt-lg-10 font-weight-500 text-dark-blue">-->
                                    <!--    <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>-->
                                    <!--</p>-->
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

               {{-- <div class="col-12 col-lg-6 mb-20 charge-account-radio ">
                   
                    <label for="offline" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                       <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit" style="display: block;    visibility: visible;">
                        <img src="{{ config('app.img_dynamic_url') }}/assets/default/img/activity/wallet.png" width="120" height="60" alt="">
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

<button type="button" id="razor-pay-now"  class="btn btn-sm btn-primary loading">{{ trans('public.start_payment') }}</button>

        </form>
        </div>
         <center><div class="loader mt-50" id="loader" style="dispay:none ">
            <img width= '80px' height= '80px' src="{{ asset('public/assets/default/img/loading.gif') }}">
            <br>
            <h3>Please do not refresh or close the page while your payment is being processed...</h3>
            </div></center>
        <!--@if(!empty(session('success')))-->
        <!--<center><div class="loader mt-50">-->
        <!--<img width= '120px' height= '120px' src="https://storage.googleapis.com/astrolok/store/1/default_images/icons8-loading-90.png"></div></center>-->
        <!--@endif-->

<form action="/installments/{{ $installment->id }}/store" method="get" id="razorpayview">
             
               
            <input type="hidden" name="name" id='user_name' value="{{ auth()->check() ? auth()->user()->full_name :'' }}" placeholder="Name" class="form-control mt-25 " required>
            <!--<input type="hidden" name="callback_url" id='user_name'  placeholder="Name" class="form-control mt-25 " required>-->
            <input type="hidden" name="email" id='user_email' value="{{ auth()->check() ? auth()->user()->email :'' }}" placeholder="Email" class="form-control mt-25 " required>
            <input type="hidden" name="number" id='user_number' value="{{ auth()->check() ? auth()->user()->mobile :'' }}" placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="totalDiscount" value="{{!empty($totalDiscount) ? $totalDiscount : 0}}"  class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
            <input type="hidden" name="installment_id" value="{{ $installment->id ?? null }}">
               
                <script id="myScript" src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <!--        data-key="{{ env('RAZORPAY_API_KEY') }}"-->
                <!--        data-amount="{{ (int)(((($installments->first()->upfront)*$itemPrice) /100) * 100) }}"-->
                <!--        data-buttontext="product_price"-->
                <!--        data-description="Rozerpay"-->
                <!--        data-currency="{{ currency() }}"-->
                <!--        data-image="{{ $generalSettings['logo'] }}"-->
                <!--        data-prefill.name=""-->
                <!--        data-prefill.email=""-->
                <!--        data-prefill.contact=""-->
                <!--        data-theme.color="#43d477">-->
               
                <button type="submit" id="razorpayauto" style="display:none;">ok</button>
</form>

        
        @php
        }
        @endphp
    </div>
@endsection

@push('scripts_bottom')


<script type="text/javascript">
  jQuery(document).on('click', '#razor-pay-now', function (e) {
            
        var name = '';
        var email = '';
        var mobile = '';
         name = document.getElementById("customer_name").value ;
         email = document.getElementById("customer_email").value;
         mobile = document.getElementById("customer_number").value;
       var checkBox = $("input[type='radio']:checked").val();
    
         $('.invalid-feedback').remove();
         $('.textdanger').remove();
        if(name ===''){
            $('#paymentSubmit').prop('disabled', false);
            // $("input:radio").attr("checked", false);
            var namevalidation ='Name field is required';
            $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');
             
        }
        if(email ===''){
            $('#paymentSubmit').prop('disabled', false);
            // $("input:radio").attr("checked", false);
            var emailvalidation ='Email field is required';
            $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
        if(mobile ===''){
            $('#paymentSubmit').prop('disabled', false);
            // $("input:radio").attr("checked", false);
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
                    $("input:radio").attr("checked", false);
                    var mobilevalidation ='Enter Valid Mobile Number';
                    $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
                return false;
              }
             
              if(checkBox ==27){
                 
                 document.getElementById("razor-pay-request").submit();
              }else{
              
       $("input:radio").attr("checked", true);
          var   datakey="<?php echo  env('RAZORPAY_API_KEY'); ?>";
          var   dataamount="<?php echo (int)(preg_replace('/[^\d.]/', '', handlePrice(((($installments->first()->upfront)*$itemPrice) /100) * 100))); ?>";
          var   databuttontext="product_price";
          var   datadescription="Payment for the course {{ $webinar->title ?? null }} was successfully made via Razorpay throughour official website – Asttrolok, using a desktop (installment)";
          var   datacurrency="<?php echo currency(); ?>";
          var   dataimage="<?php echo  $generalSettings['logo']; ?>";
          var   dataprefillname=name;
          var   dataprefillemail=email;
          var   dataprefillcontact=mobile;
          var   storename='Asttrolok';
          
          var   url="{{ url('/webhook-url')}}";
          var data = {
            name: dataprefillname,
            email: dataprefillemail,
            mobile: dataprefillcontact,
            course_title: "{{ $webinar->title ?? null }}",
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
        
        
        
        var razorpay_options = {
            key: datakey,
            amount: dataamount,
            name: storename,
            description: datadescription,
            image: dataimage,
            // netbanking: true,
            currency: datacurrency,
            prefill: {
                name: dataprefillname,
                email: dataprefillemail,
                contact: dataprefillcontact
            },
            notes: {
                soolegal_order_id: '{{ $order->id ?? 0 }}',
            },
            theme: {
                color: "#43d477",
            },
            handler: function (transaction) {
                // jQuery.ajax({
                //     url:'callback.php',
                //     type: 'post',
                //     data: {razorpay_payment_id: transaction.razorpay_payment_id, merchant_order_id: merchant_order_id, merchant_surl_id: merchant_surl_id, merchant_furl_id: merchant_furl_id, card_holder_name_id: card_holder_name_id, merchant_total: merchant_total, merchant_amount: merchant_amount, currency_code_id: currency_code_id}, 
                //     dataType: 'json',
                //     success: function (res) {
                //         if(res.msg){
                //             alert(res.msg);
                //             return false;
                //         } 
                //         window.location = res.redirectURL;
                //     }
                // });
                 $("#loader").removeClass('d-none');
                  // loader
                document.body.classList.add('disabled-page');
                document.getElementById('loader').style.display = 'block';
                document.documentElement.style.overflow = 'hidden';
                document.getElementById('razorpay_payment_id').value =transaction.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = transaction.razorpay_signature;
                document.getElementById('razorpayview').submit();
            },
            "modal": {
                "ondismiss": function () {
                   alert('payment cancelled');
                }
            }
        };
        // obj        
        var objrzpv1 = new Razorpay(razorpay_options);
        objrzpv1.open();
            e.preventDefault();
              }
        }       
    });
</script>



<script>

//     document.getElementById("input1").oninput = () => {
//   const input1 = document.getElementById('input1');
//   const output1 = document.getElementById('output1');
  
// document.getElementById('myScript').setAttribute('data-prefill.name', input1.value);

//   // Trying to insert text into 'output'.
//   output1.value = input1.value;
// };
// document.getElementById("input2").oninput = () => {
//   const input2 = document.getElementById('input2');
//   const output2 = document.getElementById('output2');
  
// document.getElementById('myScript').setAttribute('data-prefill.email', input2.value);

//   // Trying to insert text into 'output'.
//   output2.value = input2.value;
// };
// document.getElementById("input3").oninput = () => {
//   const input3 = document.getElementById('input3');
//   const output3 = document.getElementById('output3');
  
// document.getElementById('myScript').setAttribute('data-prefill.contact', input3.value);

//   // Trying to insert text into 'output'.
//   output3.value = input3.value;
// };
   
   
//   function addscript(){
            
//         var name = '';
//         var email = '';
//         var mobile = '';
//          name = document.getElementById("customer_name").value ;
//          email = document.getElementById("customer_email").value;
//          mobile = document.getElementById("customer_number").value;
//          $('.invalid-feedback').remove();
//         $('.textdanger').remove();
//         if(name ===''){
//             $('#paymentSubmit').prop('disabled', false);
//             $("input:radio").attr("checked", false);
//             var namevalidation ='Name field is required';
//             $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');
             
//         }
//          if(email ===''){
//             $('#paymentSubmit').prop('disabled', false);
//             $("input:radio").attr("checked", false);
//             var emailvalidation ='Email field is required';
//             $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
//         }
//          if(mobile ===''){
//             $('#paymentSubmit').prop('disabled', false);
//             $("input:radio").attr("checked", false);
//             var mobilevalidation ='Mobile field is required';
//             $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
//         }else{
//             var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
//             if(!regex.test(email)) {
//                 $("input:radio").attr("checked", false);
//                 document.getElementById("customer_email").value =email;
//                 var emailvalidation ='Enter Valid Email Address';
//                 $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
//               return false;
//             }
//              if (mobile.length < 9) {
//                   $('#paymentSubmit').prop('disabled', false);
//                     $("input:radio").attr("checked", false);
//                     var mobilevalidation ='Enter Valid Mobile Number';
//                     $(document).find('#customer_number').after('<span class="text-strong textdanger " style="color:red;">' +mobilevalidation+ '</span>');
//                 return false;
//               }
//               $("input:radio").attr("checked", true);
//               var  datakey="<?php echo  env('RAZORPAY_API_KEY'); ?>";
//               var   dataamount="<?php echo (int)(((($installments->first()->upfront)*$itemPrice) /100) * 100); ?>";
//               var   databuttontext="product_price";
//               var   datadescription="Rozerpay";
//               var    datacurrency="<?php echo currency(); ?>";
//               var    dataimage="<?php echo  $generalSettings['logo']; ?>";
//               var    dataprefillname=name;
//               var   dataprefillemail=email;
//               var   dataprefillcontact=mobile;
//               var   storename='Asttrolok';
          
//     //  var s = document.createElement( 'script' );
//     //     s.setAttribute( 'src', "https://checkout.razorpay.com/v1/checkout.js" );
//     //     s.setAttribute( 'id', "razorpay_script" );
//     //     s.setAttribute( 'data-key',datakey );
//     //     s.setAttribute( 'data-amount', dataamount );
//     //     s.setAttribute( 'data-buttontext', databuttontext );
//     //     s.setAttribute( 'data-currency', datacurrency );
//     //     s.setAttribute( 'data-name', 'Asttrolok' );
//     //     s.setAttribute( 'data-description', datadescription );
//     //     s.setAttribute( 'data-image', dataimage);
//     //     s.setAttribute( 'data-theme.color', "#43d477" );
//     //     s.setAttribute( 'data-prefill.name', dataprefillname );
//     //     s.setAttribute( 'data-prefill.email', dataprefillemail );
//     //     s.setAttribute( 'data-prefill.contact', dataprefillcontact );
//     //     document.querySelector("#razorpayview").appendChild( s );
    
//      var razorpay_options = {
//         key: datakey,
//         amount: dataamount,
//         name: storename,
//         description: datadescription,
//         image: dataimage,
//         // netbanking: true,
//         currency: datacurrency,
//         prefill: {
//             name: dataprefillname,
//             email: dataprefillemail,
//             contact: dataprefillcontact
//         },
//         notes: {
//             soolegal_order_id: '2345',
//         },
//         theme: {
//             color: "#43d477",
//         },
//         handler: function (transaction) {
//             // jQuery.ajax({
//             //     url:'callback.php',
//             //     type: 'post',
//             //     data: {razorpay_payment_id: transaction.razorpay_payment_id, merchant_order_id: merchant_order_id, merchant_surl_id: merchant_surl_id, merchant_furl_id: merchant_furl_id, card_holder_name_id: card_holder_name_id, merchant_total: merchant_total, merchant_amount: merchant_amount, currency_code_id: currency_code_id}, 
//             //     dataType: 'json',
//             //     success: function (res) {
//             //         if(res.msg){
//             //             alert(res.msg);
//             //             return false;
//             //         } 
//             //         window.location = res.redirectURL;
//             //     }
//             // });
//         },
//         "modal": {
//             "ondismiss": function () {
//               alert('payment cancelled');
//             }
//         }
//     };
//     // obj        
//     var objrzpv1 = new Razorpay(razorpay_options);
//     objrzpv1.open();
//         e.preventDefault();
//         } 
//         }
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
<script> 
            $("#loading").click($("#loader").css(":display","block"));
            $(document).ready(function(){
            $('#paymentSubmit').on('click', function(){
                setTimeout(function(){
                $("#loader").removeClass('d-none');
        },6600);
   
     });
     $('#razorpay_script').on('click', function(){
                alert('test');
        location.reload();
     });
    });
    
            </script>
    <!--$('#razorpayauto').click();-->
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/video.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/installment_verify.min.js"></script>
@endpush

@push('scripts_bottom')
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/get-regions.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/cart1.min.js"></script>
    <!--<script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/payment.min.js"></script>-->
@endpush
