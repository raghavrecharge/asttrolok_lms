<div class="installment-card p-15 mt-20" data-card-id="{{ $installment->id }}">
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
                    @php
                        $originalTotal = isset($cash) ? $installment->totalPayments($cash) : $totalPayments;
                    @endphp
                    <div class="d-flex align-items-baseline justify-content-center">
                        <span class="font-36 font-weight-bold text-primary" data-total-price>{{ handlePrice($totalPayments) }}</span>
                        @if($originalTotal > $totalPayments)
                            <span class="font-16 text-gray ml-10" style="text-decoration: line-through;" data-original-price>{{ handlePrice($originalTotal) }}</span>
                        @endif
                    </div>
                    <span class="mt-10 font-12 text-gray">{{ trans('update.total_payment') }} @if($installmentTotalInterest > 0)
                            ({{ trans('update.percent_interest',['percent' => $installmentTotalInterest]) }})
                        @endif</span>
                    <span class="font-12 font-weight-bold mt-5" style="color:#16a34a;display:none;" data-savings-badge></span>
                </div>

                <div class="mt-25 mb-15">
                    <div class="installment-step d-flex align-items-center font-12 text-gray" data-upfront-row>{{ !empty($installment->upfront) ? (trans('update.amount_upfront',['amount' => handlePrice($installment->getUpfront($itemPrice))]) . ($installment->upfront_type == "percent" ? " ({$installment->upfront}%)" : '')) : trans('update.no_upfront') }}</div>

                    @foreach($installment->steps as $installmentStep)
                        <div class="installment-step d-flex align-items-center font-12 text-gray" data-step-row>{{ $installmentStep->getDeadlineTitle($itemPrice) }}</div>
                    @endforeach
                </div>

                <form class="js-coupon-form" action="/cart/coupon/validate1" method="Post">
                    {{ csrf_field() }}
                    <div class="row">
                    <div class="col-12 col-lg-9">
                    <div class="form-group">
                        <input type="text" name="coupon" class="form-control mt-25 js-coupon-input"
                         placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <input type="hidden" name="web_id1" class="js-web-id" value="{{$itemId}}">
                        <input type="hidden" name="webinsta_id1" class="js-webinsta-id" value="{{$installment->id}}">
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-12 col-lg-3">
                    {{-- type="button" + class-based: no form submit, works with multiple cards --}}
                    <button type="button" style="font-family: 'Inter', sans-serif !important;"
                            class="btn btn-sm btn-primary mt-25 js-check-coupon"
                            data-label="{{ trans('cart.validate') }}">{{ trans('cart.validate') }}</button></div></div>
                </form>
 <a href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}#Payment-Option" style="font-family: 'Inter', sans-serif !important;"  class=" btn btn-primary btn-block mt-auto">{{ trans('update.pay_with_installments') }}</a>

            </div>
        </div>
    </div>
</div>
{{--
  Single delegated listener registered once for all .js-check-coupon buttons.
  Scopes every DOM query to the clicked card's container (.installment-card)
  so multiple plans on the same page each update their own price rows.
--}}
@once
@push('scripts_bottom')
<script>
(function () {
    var INVALID_LNG = '{{ trans('cart.coupon_invalid') }}';
    var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-check-coupon');
        if (!btn || btn.disabled) return;
        e.preventDefault();

        var card       = btn.closest('.installment-card');
        if (!card) return;

        var couponInput = card.querySelector('.js-coupon-input');
        var webId       = (card.querySelector('.js-web-id')      || {}).value || '';
        var webInstaId  = (card.querySelector('.js-webinsta-id') || {}).value || '';
        var coupon      = couponInput ? couponInput.value.trim() : '';
        var invalidFb   = couponInput ? couponInput.parentNode.querySelector('.invalid-feedback') : null;
        var btnLabel    = btn.dataset.label || '{{ trans('cart.validate') }}';

        if (couponInput) couponInput.classList.remove('is-invalid', 'is-valid');

        if (!coupon) {
            if (couponInput) couponInput.classList.add('is-invalid');
            return;
        }

        btn.disabled    = true;
        btn.textContent = '...';

        var fd = new FormData();
        fd.append('coupon',       coupon);
        fd.append('web_id1',      webId);
        fd.append('webinsta_id1', webInstaId);
        fd.append('_token',       CSRF);

        fetch('/cart/coupon/validate1', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.status === 200) {

                    // ── Coupon input: mark valid + lock ──
                    if (couponInput) {
                        couponInput.classList.add('is-valid');
                        couponInput.readOnly = true;
                    }
                    btn.disabled    = true;
                    btn.textContent = '✓';

                    // ── Total price ──
                    var totalEl = card.querySelector('[data-total-price]');
                    if (totalEl && res.total_payments) {
                        totalEl.textContent = res.total_payments;
                    }

                    // ── Cross-out original price (make visible if hidden) ──
                    var origEl = card.querySelector('[data-original-price]');
                    if (origEl) {
                        origEl.style.display = 'inline';
                    }

                    // ── Upfront row ──
                    var upfrontEl = card.querySelector('[data-upfront-row]');
                    if (upfrontEl && res.upfront_label) {
                        upfrontEl.textContent = res.upfront_label;
                    }

                    // ── Per-step rows ──
                    if (res.steps && res.steps.length) {
                        var stepEls = card.querySelectorAll('[data-step-row]');
                        res.steps.forEach(function (text, i) {
                            if (stepEls[i]) stepEls[i].textContent = text;
                        });
                    }

                    // ── Savings badge ──
                    var badge = card.querySelector('[data-savings-badge]');
                    if (badge && res.savings) {
                        badge.textContent = '✓ You save ' + res.savings + '!';
                        badge.style.display = 'inline-block';
                    }

                    // ── Update payment form hidden fields ──
                    var discountField = document.querySelector('input[name="discountId"]');
                    if (discountField && res.discount_id) discountField.value = res.discount_id;

                    // Update price field with discounted upfront so Razorpay receives correct amount
                    var priceField = document.querySelector('input[name="price"]');
                    if (priceField && res.upfront_original != null) {
                        var discountedUpfront = (parseFloat(res.upfront_original) || 0) - (parseFloat(res.upfront_savings) || 0);
                        priceField.value = discountedUpfront.toFixed(2);
                    }

                    // ── Payment Summary widget: coupon row + recalc wallet ──
                    if (typeof window.updateCouponDiscount === 'function') {
                        var emiSavings = parseFloat(res.upfront_savings) || 0;
                        window.updateCouponDiscount(emiSavings, coupon);
                    }
                    if (res.upfront_original != null && typeof window.updateWalletTotalAmount === 'function') {
                        window.updateWalletTotalAmount(parseFloat(res.upfront_original) || 0);
                    }

                } else {
                    if (couponInput) couponInput.classList.add('is-invalid');
                    if (invalidFb)   invalidFb.textContent = (res && res.msg) ? res.msg : INVALID_LNG;
                    btn.disabled    = false;
                    btn.textContent = btnLabel;
                }
            })
            .catch(function () {
                btn.disabled    = false;
                btn.textContent = btnLabel;
            });
    });
})();
</script>
@endpush
@endonce
