<div class="installment-card p-15 mt-20">
    <div class="row">
        <div class="col-md-8">
            <h4 class="font-16 font-weight-bold text-dark-blue">{{ $installment->main_title }}</h4>

            <div class="">
                <p class="text-gray font-14 text-ellipsis">{!!($installment->description) !!}</p>
            </div>

            @if(!empty($installment->capacity))
                @php
                    $reachedCapacityPercent = $installment->reachedCapacityPercent();
                @endphp

                @if($reachedCapacityPercent > 0)

                @endif
            @endif

            @if(!empty($installment->banner))
                <div class="mt-20">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $installment->banner }}" alt="{{ $installment->main_title }}" class="img-fluid">
                </div>
                @else

                <div class="default-package-icon mt-20">
                <img src="{{ config('app.js_css_url') }}/assets2/default/img/become-instructor/default.png" class="img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">
            </div>
            @endif

            @if(!empty($installment->options))
                <div class="mt-20">
                    @php
                        $installmentOptions = explode(\App\Models\Installment::$optionsExplodeKey, $installment->options);
                    @endphp

                    @foreach($installmentOptions as $installmentOption)
                        <div class="d-flex align-items-center mb-1">
                            <i data-feather="check" width="25" height="25" class="text-primary"></i>
                            <span class="ml-10 font-14 text-gray">{{ $installmentOption }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-md-4 p-0 pr-15">
            <div class="installment-card__payments d-flex flex-column w-100 h-100">

                @php
                    $totalPayments = $installment->totalPayments($itemPrice ?? 1);
                    $installmentTotalInterest = $installment->totalInterest($itemPrice, $totalPayments);
                @endphp

                <div class="d-flex align-items-center justify-content-center flex-column">
                    <span class="font-36 font-weight-bold text-primary">{{ handlePrice($totalPayments) }}</span>
                    <span class="mt-10 font-12 text-gray">{{ trans('update.total_payment') }} @if($installmentTotalInterest > 0)
                            ({{ trans('update.percent_interest',['percent' => $installmentTotalInterest]) }})
                        @endif</span>
                </div>

                <div class="mt-25 mb-15">
                    <div class="installment-step d-flex align-items-center font-12 text-gray">{{ !empty($installment->upfront) ? (trans('update.amount_upfront',['amount' => handlePrice($installment->getUpfront($itemPrice))]) . ($installment->upfront_type == "percent" ? " ({$installment->upfront}%)" : '')) : trans('update.no_upfront') }}</div>

                    @foreach($installment->steps as $installmentStep)
                        <div class="installment-step d-flex align-items-center font-12 text-gray">{{ $installmentStep->getDeadlineTitle($itemPrice) }}</div>
                    @endforeach
                </div>

                <form action="/cart/coupon/validate1" method="Post">
                    {{ csrf_field() }}
                    <div class="row">
                    <div class="col-12 col-lg-9">
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-25"
                         placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <input type="hidden" name="web_id1" id="web_id1" value="{{$itemId}}" class="form-control mt-25" >
                        <input type="hidden" name="webinsta_id1" id="webinsta_id1" value="{{$installmentRow->id}}" class="form-control mt-25" >
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-12 col-lg-3 ">
                    <button type="submit" id="checkCoupon1" style="font-family: 'Inter', sans-serif !important;"  class="btn btn-sm btn-primary mt-25" >{{ trans('cart.validate') }}</button></div></div>
                </form>
 <a href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}#Payment-Option" style="font-family: 'Inter', sans-serif !important;"  class=" btn btn-primary btn-block mt-auto">{{ trans('update.pay_with_installments') }}</a>

            </div>
        </div>
    </div>
</div>
@push('scripts_bottom')
<script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart1.min.js"></script>
@endpush
