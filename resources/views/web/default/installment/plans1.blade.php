@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/video/video-js.min.css">
@endpush
@push('styles_top')
    <style>
.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite; /* Safari */
  animation: spin 2s linear infinite;
}

/* Safari */
@-webkit-keyframes spin {
  0% { -webkit-transform: rotate(0deg); }
  100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
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
        <!--        <img src="/assets/default/img/become-instructor/default.png" class="img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">-->
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
            @include('web.default.installment.card2',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach

        @php
        if(isset($mayank)){
            $userCurrency = currency();
            $invalidChannels = [];
            
        @endphp
        <div id="Payment-Option" class=" bg-gray200 mt-30 rounded-lg border p-15">
            
         <h2 class="section-title">Payment Option</h2>
          <form action="/payments/payment-request" method="post" class=" mt-25" >
            {{ csrf_field() }}
            {{--<input type="hidden" name="order_id" value="{{ $order->id ?? 0 }}"> --}}
            <input type="text" name="name" id='input1'  placeholder="Name" class="form-control mt-25 " >
            <input type="email" name="email" id='input2'  placeholder="Email" class="form-control mt-25 " >
            <input type="number" name="number" id='input3'  placeholder="Contact Number" class="form-control mt-25 mb-25" > 
            

            <div class="row">
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

                <div class="col-12 col-lg-6 mb-20 charge-account-radio ">
                   
                    <label for="offline" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                       <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit" style="display: block;    visibility: visible;">
                        <img src="/assets/default/img/activity/wallet.png" width="120" height="60" alt="">
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

<button type="button" id="paymentSubmit" disabled class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>

        </form>
        </div>
        @if(!empty(session('success')))
        <center><div class="loader mt-50">
        <img width= '120px' height= '120px' src="https://storage.googleapis.com/astrolok/store/1/default_images/icons8-loading-90.png"></div></center>
        @endif

<form action="/installments/{{ $installment->id }}/store" method="get">
             
               
            <input type="text" name="name" id='output1'  placeholder="Name" class="form-control mt-25 d-none" required>
            <input type="email" name="email" id='output2'  placeholder="Email" class="form-control mt-25 d-none" required>
            <input type="number" name="number" id='output3'  placeholder="Contact Number" class="form-control mt-25 mb-25 d-none" required>
            <input type="number" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 d-none" required>
            <input type="text" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 d-none" required>
            <input type="text" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 d-none" required>
                <script id="myScript" src="https://checkout.razorpay.com/v1/checkout.js"
                        data-key="{{ env('RAZORPAY_API_KEY') }}"
                        data-amount="{{ (int)(((($installments->first()->upfront)*$itemPrice) /100) * 100) }}"
                        data-buttontext="product_price"
                        data-description="Rozerpay"
                        data-currency="{{ currency() }}"
                        data-image="{{ $generalSettings['logo'] }}"
                        data-prefill.name=""
                        data-prefill.email=""
                        data-prefill.contact=""
                        data-theme.color="#43d477">
                </script>
                <button type="submit" id="razorpayauto" style="display:none;">ok</button>
</form>

        
        @php
        }
        @endphp
    </div>
@endsection

@push('scripts_bottom')
<script>
    document.getElementById("input1").oninput = () => {
  const input1 = document.getElementById('input1');
  const output1 = document.getElementById('output1');
  
document.getElementById('myScript').setAttribute('data-prefill.name', input1.value);

  // Trying to insert text into 'output'.
  output1.value = input1.value;
};
document.getElementById("input2").oninput = () => {
  const input2 = document.getElementById('input2');
  const output2 = document.getElementById('output2');
  
document.getElementById('myScript').setAttribute('data-prefill.email', input2.value);

  // Trying to insert text into 'output'.
  output2.value = input2.value;
};
document.getElementById("input3").oninput = () => {
  const input3 = document.getElementById('input3');
  const output3 = document.getElementById('output3');
  
document.getElementById('myScript').setAttribute('data-prefill.contact', input3.value);

  // Trying to insert text into 'output'.
  output3.value = input3.value;
};
    
</script>
    $('#razorpayauto').click();
    <script src="/assets/default/vendors/video/video.min.js"></script>
    <script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="/assets/default/js/parts/installment_verify.min.js"></script>
@endpush

@push('scripts_bottom')
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="/assets/default/js/parts/get-regions.min.js"></script>
    <script src="/assets/default/js/parts/cart1.min.js"></script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush