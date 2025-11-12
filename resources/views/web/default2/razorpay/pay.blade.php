@extends('web.default2.layouts.app')


@section('content')
    <section class="cart-banner1 position-relative text-center  slider-container1">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.shopping_cart') }}</h1>
    </section>
  
 <form action="/razorpay" method="get">
                         
                            <input type="hidden" name="consult_id" value="{{ $consult_id }}">
                            <input type="hidden" name="_token" value="{{ $_token }}">
                            <input type="hidden" name="email" value="{{ $email }}">
                            <input type="hidden" name="total" value="{{ $total }}">
                            <input type="hidden" name="contact" value="{{ $contact }}">
                            <input type="hidden" name="name" value="{{ $full_name }}">
            
                            <script src="https://checkout.razorpay.com/v1/checkout.js"
                                    data-key="{{ env('RAZORPAY_API_KEY') }}"
                                    data-amount="{{ (int)($total * 100) }}"
                                    data-buttontext="product_price"
                                    data-description="Rozerpay"
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
    <script>
    $('#razorpayauto').click();
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="/assets/default/js/parts/get-regions.min.js"></script>
    <script src="/assets/default/js/parts/cart.min.js"></script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush