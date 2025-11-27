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
@endpush
@section('content')

    <div class="container pt-50 mt-10">
        <div class="text-center">
            <h1 class="font-36">{{ trans('update.select_an_installment_plan') }}</h1>
            <p class="mt-10 font-16 text-gray">{{ trans('update.please_select_an_installment_plan_in_order_to_finalize_your_purchase') }}</p>
        </div>

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

            <div class="row d-none">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 col-lg-6 mb-20 charge-account-radio">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                                  <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}" style="display: block;    visibility: visible;">
                                  <img src="{{ config('app.img_dynamic_url') }}{{ $paymentChannel->image }}" width="120" height="60" alt="{{ $paymentChannel->title }}">
                                    <div>

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

<form action="/installments/{{ $installment->id }}/store" method="get" id="razorpayview">

            <input type="hidden" name="name" id='user_name' value="{{ auth()->check() ? auth()->user()->full_name :'' }}" placeholder="Name" class="form-control mt-25 " required>

            <input type="hidden" name="email" id='user_email' value="{{ auth()->check() ? auth()->user()->email :'' }}" placeholder="Email" class="form-control mt-25 " required>
            <input type="hidden" name="number" id='user_number' value="{{ auth()->check() ? auth()->user()->mobile :'' }}" placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " required>
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
@endsection

@push('scripts_bottom')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="/js/unified-payment.js"></script>
<script>
document.getElementById('razor-pay-now').addEventListener('click', function(e) {
    e.preventDefault();

    const userDetails = {
        name: document.getElementById('customer_name').value,
        email: document.getElementById('customer_email').value,
        number: document.getElementById('customer_number').value,
        installment_id: document.getElementById('installment_id').value,
        discount_id: {{ session('discountCouponId') ?? null }}
    };

    initiatePayment('installment', {{!empty($item) ? $item->id : null}}, userDetails);
});
</script>
@endpush
