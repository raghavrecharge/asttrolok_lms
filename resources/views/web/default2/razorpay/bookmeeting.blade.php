@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <style>
.loader {
  //border: 16px solid #f3f3f3;
  //border-radius: 50%;
  //border-top: 16px solid #3498db;
  width: 120px;
  height: 120px;
  -webkit-animation: spin 2s linear infinite;
  animation: spin 2s linear infinite;
}

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

    <center><div class="loader mt-50">
<img width= '120px' height= '120px' src="{{ config('app.img_dynamic_url') }}/store/1/default_images/icons8-loading-90.png"></div></center>
 <form action="/razorpay/consultationdetailsshow" method="get">

                            <input type="hidden" name="order_id" value="{{ $orderid }}">
                            <input type="hidden" name="contact_no" value="{{ $contact }}">

                            <script src="https://checkout.razorpay.com/v1/checkout.js"
                                    data-key="{{ env('RAZORPAY_API_KEY') }}"
                                    data-amount="{{ (int)(convertPriceToUserCurrency($total) * 100) }}"
                                    data-buttontext="product_price"
                                    data-description="Rozerpay"
                                    data-currency="{{ currency() }}"
                                    data-image="{{ $generalSettings['logo'] ?? '' }}"
                                    data-prefill.name="{{ $full_name }}"
                                    data-prefill.email="{{ $email }}"
                                    data-prefill.contact="{{ $contact }}"
                                    data-theme.color="#43d477">
                            </script>
                            <button type="submit" id="razorpayauto" style="display:none;">ok</button>
                        </form>
                    @endsection

@push('scripts_bottom')
    <script>
    $('#razorpayauto').click();
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/get-regions.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/payment.min.js"></script>
@endpush
