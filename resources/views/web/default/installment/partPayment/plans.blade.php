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
            <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="payment_type" value="part"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="amount" id="razorpay_payment_amount">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
            <input type="hidden" name="installment_id" value="{{ $installment->id ?? null }}">

                <script   id="myScript" src="https://checkout.razorpay.com/v1/checkout.js"></script>

                <button type="submit" id="razorpayauto" style="display:none;">ok</button>
</form>

        @php
        }
        @endphp
    </div>
@endsection

@push('scripts_bottom')

<script   src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script   src="https://www.asttrolok.com/js/unified-payment.js"></script>
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
