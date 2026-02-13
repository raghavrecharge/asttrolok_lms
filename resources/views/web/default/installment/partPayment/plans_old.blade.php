@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/video/video-js.min.css">
@endpush
@push('styles_top')
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
@endpush
@section('content')

    <div class="container pt-50 mt-10">
        <div class="text-center">
            <h1 class="font-36">{{ $item->title }}</h1>
            <p class="mt-10 font-16 text-gray">{{ trans('update.please_select_an_installment_plan_in_order_to_finalize_your_purchase') }}</p>
        </div>

        @foreach($installments as $installmentRow)
            @include('web.default.installment.partPayment.card2',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach

        @php
        if(isset($mayank)){
            $userCurrency = currency();
            $invalidChannels = [];

        @endphp

<form action="/installments/{{ $installment->id }}/store" method="get" id="razorpayview">

            <input type="hidden" name="name" id='user_name' value="{{ auth()->check() ? auth()->user()->full_name :'' }}" placeholder="Name" class="form-control mt-25 " required>

            <input type="hidden" name="email" id='user_email' value="{{ auth()->check() ? auth()->user()->email :'' }}" placeholder="Email" class="form-control mt-25 " required>
            <input type="hidden" name="number" id='user_number' value="{{ auth()->check() ? auth()->user()->mobile :'' }}" placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="totalDiscount" value="{{!empty($totalDiscount) ? $totalDiscount : 0}}"  class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="payment_type" value="part"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="amount" id="razorpay_payment_amount">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
            <input type="hidden" name="installment_id" value="{{ $installment->id ?? null }}">

                <script id="myScript" src="https://checkout.razorpay.com/v1/checkout.js"></script>

                <button type="submit" id="razorpayauto" style="display:none;">ok</button>
</form>

        @php
        }
        @endphp
    </div>
@endsection

@push('scripts_bottom')

<script type="text/javascript">
document.getElementById('amount').addEventListener('input', function() {
            var button = document.getElementById('razor-pay-now');
            button.classList.remove('d-none');
        });

  jQuery(document).on('click', '#razor-pay-now', function (e) {

        var name = '';
        var email = '';
        var mobile = '';
        var amount = '';

         name = document.getElementById("customer_name").value ;
         email = document.getElementById("customer_email").value;
         mobile = document.getElementById("customer_number").value;
         amount = document.getElementById("amount").value;

       var checkBox = $("input[type='radio']:checked").val();

         $('.invalid-feedback').remove();
         $('.textdanger').remove();
        if(name ===''){
            $('#paymentSubmit').prop('disabled', false);

            var namevalidation ='Name field is required';
            $(document).find('#customer_name').after('<span class="text-strong textdanger " style="color:red;">' +namevalidation+ '</span>');

        }
        if(email ===''){
            $('#paymentSubmit').prop('disabled', false);

            var emailvalidation ='Email field is required';
            $(document).find('#customer_email').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
        if(amount ===''){
            $('#paymentSubmit').prop('disabled', false);

            var emailvalidation ='Amount field is required';
            $(document).find('#amount').after('<span class="text-strong textdanger " style="color:red;">' +emailvalidation+ '</span>');
        }
        if(mobile ===''){
            $('#paymentSubmit').prop('disabled', false);

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

              document.getElementById("user_name").value = name;

        document.getElementById("user_email").value = email;

        document.getElementById("user_number").value = mobile;

              if(checkBox ==27){

                 document.getElementById("razor-pay-request").submit();
              }else{

               $("input:radio").attr("checked", true);
              var  datakey="<?php echo  env('RAZORPAY_API_KEY'); ?>";
              var   dataamount=(amount * 100);
              var   databuttontext="product_price";
              var   datadescription="Payment for the course {{ $webinar->title }} was successfully made via Razorpay throughour official website – Asttrolok, using a mobile (partpayment)";
              var    datacurrency="<?php echo currency(); ?>";
              var    dataimage="<?php echo  $generalSettings['logo']; ?>";
              var    dataprefillname=name;
              var   dataprefillemail=email;
              var   dataprefillcontact=mobile;
              var   storename='Asttrolok';

              var   url="{{ url('/webhook-url')}}";
          var data = {
            name: dataprefillname,
            email: dataprefillemail,
            mobile: dataprefillcontact,
            course_title: "<?php echo $webinar->title; ?>",
          }

          $.ajax({
                method: 'post',
                url: url,
                data: data,
            }).done(function(response, status){

            }).fail(function(jqXHR, textStatus, errorThrown){

            });

        var razorpay_options = {
            key: datakey,
            amount: dataamount,
            name: storename,
            description: datadescription,
            image: dataimage,

            currency: datacurrency,
            prefill: {
                name: dataprefillname,
                email: dataprefillemail,
                contact: dataprefillcontact
            },
            notes: {
                soolegal_order_id: '2345',
            },
            theme: {
                color: "#43d477",
            },
            handler: function (transaction) {

                $("#loader").removeClass('d-none');

                document.body.classList.add('disabled-page');
                document.getElementById('loader').style.display = 'block';
                document.documentElement.style.overflow = 'hidden';
                document.getElementById('razorpay_payment_id').value =transaction.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = transaction.razorpay_signature;
                document.getElementById('razorpay_payment_amount').value = amount;
                document.getElementById('razorpayview').submit();
            },
            "modal": {
                "ondismiss": function () {
                   alert('payment cancelled');
                }
            }
        };

        var objrzpv1 = new Razorpay(razorpay_options);
        objrzpv1.open();
            e.preventDefault();
              }
        }
    });
</script>

<script>

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

    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/video.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/installment_verify.min.js"></script>
@endpush

@push('scripts_bottom')
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/get-regions.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart1.min.js"></script>

@endpush
