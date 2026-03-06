<div class="installment-card p-15 mt-20" data-card-id="{{ $installment->id }}">
    <div class="row">
        <div class="col-8">
            <h4 class="font-16 font-weight-bold text-dark-blue">{{ $installment->main_title }}</h4>

            <div class="">
                <p class="text-gray font-14 text-ellipsis1">{{ nl2br($installment->description) }}</p>
            </div>

            @if(!empty($installment->capacity))
                @php
                    $reachedCapacityPercent = $installment->reachedCapacityPercent();
                @endphp

                @if($reachedCapacityPercent > 0)
                    <div class="mt-20 d-flex align-items-center">
                        <div class="progress card-progress flex-grow-1">
                            <span class="progress-bar rounded-sm {{ $reachedCapacityPercent > 50 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $reachedCapacityPercent }}%"></span>
                        </div>
                        <div class="ml-10 font-12 text-danger">{{ trans('update.percent_capacity_reached',['percent' => $reachedCapacityPercent]) }}</div>
                    </div>
                @endif
            @endif

            @if(!empty($installment->banner))
                <div class="mt-20">
                    <img loading="lazy" src="{{ $installment->banner }}" alt="{{ $installment->main_title }}" class="img-fluid">
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

        <div class="col-4 p-0 pr-15">
            <div class="installment-card__payments d-flex flex-column w-100 h-100">

                @php
                    $totalPayments = $installment->totalPayments($itemPrice ?? 1);
                    $installmentTotalInterest = $installment->totalInterest($itemPrice, $totalPayments);
                @endphp

                <div class="d-flex align-items-center justify-content-center flex-column">
                    <span class="font-36 font-weight-bold text-primary" data-total-price>{{ handlePrice($totalPayments) }}</span>
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
                        <input type="hidden" name="webinsta_id1" class="js-webinsta-id" value="{{$installmentRow->id}}">
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-12 col-lg-3">
                    <button type="button" class="btn btn-sm btn-primary mt-25 js-check-coupon"
                            data-label="{{ trans('cart.validate') }}">{{ trans('cart.validate') }}</button></div></div>
                </form>

                <a href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}#Payment-Option" class=" btn btn-primary btn-block mt-auto">{{ trans('update.pay_with_installments') }}</a>
            </div>
        </div>
    </div>
</div>



@once
@push('scripts_bottom')
<script>
{{-- Delegated listener: works for all cards in the foreach, scoped per card container --}}
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

                    // Total price
                    var totalEl = card.querySelector('[data-total-price]');
                    if (totalEl && res.total_payments) totalEl.textContent = res.total_payments;

                    // Upfront row (full label with % suffix from backend)
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
    <script src="/assets/default/js/parts/cart.min.js"></script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush
@endonce