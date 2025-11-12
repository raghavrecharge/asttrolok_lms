@extends(getTemplate().'.layouts.app')

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
    <!--<section class="cart-banner1 position-relative text-center  slider-container1">-->
    <!--    <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.shopping_cart') }}</h1>-->
    <!--</section>-->
    <center><div class="loader mt-50">
<img loading="lazy"  width= '120px' height= '120px' src="{{ config('app.img_dynamic_url') }}/store/1/default_images/icons8-loading-90.png"></div></center>
 <form action="/razorpay/consultationdetailsshow" method="get">
                         
                            <input type="hidden" name="order_id" value="{{ $orderid }}">
                            <input type="hidden" name="contact_no" value="{{ $contact }}">
                            <input type="hidden" name="name" value="{{ $full_name }}">
            
                            <script   src="https://checkout.razorpay.com/v1/checkout.js"
                                    data-key="{{ env('RAZORPAY_API_KEY') }}"
                                    data-amount="{{ (int)(preg_replace('/[^\d.]/', '', handlePrice($total ))* 100) }}"
                                    data-buttontext="product_price"
                                    data-description="{{ $full_name }} has booked an appointment with astrologer {{ $astrologer }} via Razorpay on Asttrolok using a mobile ({{ $slotTime }})"
                                    data-currency="{{ currency() }}"
                                    data-image="{{ $generalSettings['logo'] }}"
                                    data-prefill.name="{{ $full_name }}"
                                    data-prefill.email="{{ $email }}"
                                    data-prefill.contact="{{ $contact }}"
                                    data-theme.color="#43d477">
                            </script>
                            <button type="submit" id="razorpayauto" style="display:none;">ok</button>
                        </form>
                    @endsection

@push('scripts_bottom')
    <script  >
    $('#razorpayauto').click();
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/get-regions.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/payment.min.js"></script>
@endpush