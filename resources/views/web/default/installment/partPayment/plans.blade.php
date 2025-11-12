@extends('web.default.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/video/video-js.min.css">
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
            <h1 class="font-36">{{ $item->title }}</h1>
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
            @include('web.default.installment.partPayment.card2',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach

        @php
        if(isset($mayank)){
            $userCurrency = currency();
            $invalidChannels = [];
            
        @endphp
        
        
         
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
            <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="payment_type" value="part"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="amount" id="razorpay_payment_amount">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
            <input type="hidden" name="installment_id" value="{{ $installment->id ?? null }}">
               
                <script   id="myScript" src="https://checkout.razorpay.com/v1/checkout.js"></script>
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

<script   src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script   src="{{ asset('js/unified-payment.js') }}"></script>
<script  >

    
document.getElementById('amount').addEventListener('input', function() {
        var button = document.getElementById('paymentSubmit');
        button.classList.remove('d-none');
    });

document.getElementById('paymentSubmit').addEventListener('click', function(e) {
    e.preventDefault();

    
    const userDetails = {
        name: document.getElementById('customer_name').value,
        email: document.getElementById('customer_email').value,
        number: document.getElementById('customer_number').value,
        amount: document.getElementById('amount').value,
        installment_id: {{ $installment->id ?? null }}, 
        discount_id: {{ session('discountCouponId') ?? 'null' }}
    };

    initiatePayment('part' , '{{!empty($item) ? $item->id : null}}' , userDetails);
});
</script>
@endpush

