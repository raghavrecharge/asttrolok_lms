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

    <div class="container pt-50 mt-10">
        <div class="text-center">
            <h1 class="font-36">{{ $item->title }}</h1>

        </div>

        @foreach($installments as $installmentRow)
            @include('web.default2.installment.partPayment.card',['installment' => $installmentRow, 'itemPrice' => $itemPrice, 'itemId' => $itemId, 'itemType' => $itemType])
        @endforeach

        @php
        if(isset($mayank)){
            $userCurrency = currency();
            $invalidChannels = [];

        @endphp

         <center><div class="loader mt-50" id="loader" style="dispay:none ">
            <img width= '80px' height= '80px' src="{{ asset('assets/default/img/loading.gif')}}">
            <br>
            <h3>Please do not refresh or close the page while your payment is being processed...</h3>
            </div></center>

<form action="/installments/{{ $installment->id }}/store" method="get" id="razorpayview">

            <input type="hidden" name="name" id='user_name' value="{{ auth()->check() ? auth()->user()->full_name :'' }}" placeholder="Name" class="form-control mt-25 " required>

            <input type="hidden" name="email" id='user_email' value="{{ auth()->check() ? auth()->user()->email :'' }}" placeholder="Email" class="form-control mt-25 " required>
            <input type="hidden" name="number" id='user_number' value="{{ auth()->check() ? auth()->user()->mobile :'' }}" placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="payment_type" value="part"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="amount" id="razorpay_payment_amount">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
            <input type="hidden" name="installment_id" id="installment_id" value="{{ $installment->id ?? null }}">

                <script id="myScript" src="https://checkout.razorpay.com/v1/checkout.js"></script>

                <button type="submit" id="razorpayauto" style="display:none;">ok</button>
</form>

        @php
        }
        @endphp
    </div>
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
        discount_id: @json(session('discountCouponId'))
    };
    showPaymentLoader();

    initiatePayment('part' , '{{!empty($item) ? $item->id : null}}' , userDetails);
});
</script>
@endpush
