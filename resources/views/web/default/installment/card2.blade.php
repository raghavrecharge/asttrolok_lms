<div class="installment-card p-15" data-card-id="{{ $installment->id }}">
    <div class="row">

        <div class="col-12">
            <div class="installment-card__payments d-flex flex-column w-100 h-100" style="background-color: #ffffff;">

                @php
                    $totalPayments = $installment->totalPayments($itemPrice ?? 1);
                    $installmentTotalInterest = $installment->totalInterest($itemPrice, $totalPayments);
                @endphp

<div class="price-box p-10 shadow-xs mb-20" style="border: 1px solid #e1e1e1;border-radius: 10px;">
<span class="font-30 font-weight-bold">Total Amount </span>
@php
    $originalTotal = isset($cash) ? $installment->totalPayments($cash) : $totalPayments;
@endphp
<span class="f-right" style="float: right;">
    <span class="font-30 text-primary" data-total-price>{{ handlePrice($totalPayments) }}/-</span>
    @if($originalTotal > $totalPayments)
        <span class="font-16 text-gray" style="text-decoration: line-through;">{{ handlePrice($originalTotal) }}</span>
    @endif
</span>
</div>
                <div class="mt-25 mb-15">
                    <div class="installment-step d-flex align-items-center font-14 text-primary" style="font-weight: 600;" data-upfront-row>{{ !empty($installment->upfront) ? (trans('update.amount_upfront',['amount' => handlePrice($installment->getUpfront($itemPrice))]) . ($installment->upfront_type == "percent" ? " ({$installment->upfront}%)" : '')) : trans('update.no_upfront') }}</div>

                    @foreach($installment->steps as $installmentStep)
                        <div class="installment-step d-flex align-items-center font-14 text-gray" style="font-weight: 600;" data-step-row>{{ $installmentStep->getDeadlineTitle($itemPrice) }}</div>
                    @endforeach
                    <span class="font-12 font-weight-bold mt-5" style="color:#16a34a;display:none;" data-savings-badge></span>
                </div>
                <div class=" p-15 mt-20 mx-20 shadow-sm " style="border: 1px solid #e1e1e1;border-radius: 10px;">
                <p class="text-gray font-12 text-ellipsis">{!! nl2br($installment->description) !!}</p>
            </div>

                <form class="js-coupon-form" action="/cart/coupon/validate1" method="Post">
                    <div class="col-12 col-lg-12">
                    <section>
                    {{ csrf_field() }}
                    <div class="row" style="display: flex;justify-content: space-evenly;">
                    <div class="col-11 col-lg-9">
                    <div class="form-group" style="border-radius: 20px;">
                        <input type="text" name="coupon" class="form-control mt-25 js-coupon-input" style="border-radius: 20px !important;"
                         placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <input type="hidden" name="web_id1" class="js-web-id" value="{{$itemId}}">
                        <input type="hidden" name="webinsta_id1" class="js-webinsta-id" value="{{$installmentRow->id}}">
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-5 col-lg-3 botton-1" style="position: absolute;margin-top: 3px;margin-right: -231px;">
                    <button type="button" style="height: 35px !important; border-radius: 20px !important;"
                            class="btn btn-sm btn-primary mt-25 js-check-coupon"
                            data-label="{{ trans('cart.validate') }}">{{ trans('cart.validate') }}</button></div></div>
                    </section></div>
                </form>
<div style="
    display: flex;
    justify-content: center;
">
                <a style="
    width: 138px;
" href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}#Payment-Option" class=" btn btn-primary btn-block mt-auto">Pay Now</a>
            </div></div>
        </div>
    </div>
</div>

@once
@push('scripts_bottom')
<script>
{{-- Delegated coupon handler for card2 — scoped to each .installment-card container --}}
(function () {
    var INVALID_LNG = '{{ trans('cart.coupon_invalid') }}';
    var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '{{ csrf_token() }}';

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-check-coupon');
        if (!btn || btn.disabled) return;
        e.preventDefault();

        var card = btn.closest('.installment-card');
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

                    if (couponInput) {
                        couponInput.classList.add('is-valid');
                        couponInput.readOnly = true;
                    }
                    btn.disabled    = true;
                    btn.textContent = '✓';

                    // Total price (preserve /- suffix used in card2 layout)
                    var totalEl = card.querySelector('[data-total-price]');
                    if (totalEl && res.total_payments) {
                        totalEl.textContent = res.total_payments + '/-';
                    }

                    // Upfront row — full label from backend
                    var upfrontEl = card.querySelector('[data-upfront-row]');
                    if (upfrontEl && res.upfront_label) upfrontEl.textContent = res.upfront_label;

                    // Per-step rows
                    if (res.steps && res.steps.length) {
                        var stepEls = card.querySelectorAll('[data-step-row]');
                        res.steps.forEach(function (text, i) {
                            if (stepEls[i]) stepEls[i].textContent = text;
                        });
                    }

                    // Savings badge
                    var badge = card.querySelector('[data-savings-badge]');
                    if (badge && res.savings) {
                        badge.textContent = '✓ You save ' + res.savings + '!';
                        badge.style.display = 'inline-block';
                    }

                    // Payment form: store discount_id + update price to discounted upfront
                    var df = document.querySelector('input[name="discountId"]');
                    if (df && res.discount_id) df.value = res.discount_id;

                    var pf = document.querySelector('input[name="price"]');
                    if (pf && res.upfront_original != null) {
                        var discountedUpfront = (parseFloat(res.upfront_original) || 0) - (parseFloat(res.upfront_savings) || 0);
                        pf.value = discountedUpfront.toFixed(2);
                    }

                    // Payment Summary widget
                    if (typeof window.updateCouponDiscount === 'function') {
                        window.updateCouponDiscount(parseFloat(res.upfront_savings) || 0, coupon);
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
    <script src="/assets/default/js/parts/get-regions.min.js"></script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush
@endonce
