{{-- Wallet Payment Widget - Auto-applied for all purchases (wallet-mediated flow) --}}
{{-- Required: $totalAmount (float). Wallet balance auto-fetched.              --}}
{{-- Optional: $couponDiscount (float rupee amount), $couponCode (string)      --}}
@if(auth()->check())
    @php
        $walletBalance  = $walletBalance  ?? app(\App\Services\PaymentEngine\WalletService::class)->balance(auth()->id());
        $totalAmount    = (int) round($totalAmount ?? 0);
        $couponDiscount = (float) ($couponDiscount ?? 0);
        $couponCode     = $couponCode ?? null;
        $walletUsed     = (int) min(floor($walletBalance), max($totalAmount - (int)$couponDiscount, 0));
        $gatewayAmount  = max($totalAmount - (int)$couponDiscount - $walletUsed, 0);
    @endphp
    @if($totalAmount > 0)
        <div class="wallet-pay-widget mt-20 p-15 rounded-lg" style="background:linear-gradient(135deg,#f0f7ff 0%,#e8f0fe 100%);border:1px solid #d0e3ff;border-radius:14px;">
            {{-- Header --}}
            <div class="d-flex align-items-center mb-10" style="gap:8px;">
                <div style="width:36px;height:36px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                    <i data-feather="credit-card" width="18" height="18" style="color:#2563eb;"></i>
                </div>
                <div>
                    <div class="font-14 font-weight-bold" style="color:#1e3a5f;">Payment Summary</div>
                    <div class="font-11 text-gray">Wallet balance auto-applied</div>
                </div>
            </div>

            {{-- Summary rows --}}
            <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px;">
                <div class="d-flex justify-content-between font-13 mb-8">
                    <span class="text-gray">Total Amount</span>
                    <span class="font-weight-bold" style="color:#1e3a5f;" id="walletTotalDisplay">₹{{ number_format($totalAmount, 0) }}</span>
                </div>

                {{-- Coupon discount row — hidden until coupon is applied --}}
                <div id="couponDeductRow" class="d-flex justify-content-between font-13 mb-8" style="display:none!important;">
                    <span style="color:#16a34a;">
                        <i data-feather="tag" width="13" height="13" style="vertical-align:-2px;margin-right:4px;"></i>
                        Coupon{{ $couponCode ? ' (' . strtoupper($couponCode) . ')' : '' }}
                    </span>
                    <span class="font-weight-bold" style="color:#16a34a;" id="couponDeductDisplay">{{ $couponDiscount > 0 ? '- ₹' . number_format((int)$couponDiscount, 0) : '' }}</span>
                </div>

                @if($walletBalance > 0)
                <div class="d-flex justify-content-between font-13 mb-8">
                    <span style="color:#2563eb;">
                        <i data-feather="wallet" width="13" height="13" style="vertical-align:-2px;margin-right:4px;"></i>
                        Wallet (₹{{ number_format(floor($walletBalance), 0) }})
                    </span>
                    <span class="font-weight-bold" style="color:#2563eb;" id="walletDeductDisplay">- ₹{{ number_format($walletUsed, 0) }}</span>
                </div>
                @endif

                <div style="border-top:1px dashed #e5e7eb;margin:8px 0;"></div>

                <div class="d-flex justify-content-between font-14">
                    <span class="font-weight-bold" style="color:#1e3a5f;">Pay via Gateway</span>
                    <span class="font-weight-bold" style="color:#16a34a;font-size:16px;" id="walletRemainingDisplay">₹{{ number_format($gatewayAmount, 0) }}</span>
                </div>

                @if($gatewayAmount <= 0 && $walletBalance > 0)
                <div class="font-11 mt-8" style="color:#16a34a;background:#f0fdf4;padding:6px 10px;border-radius:8px;text-align:center;" id="walletFullPayNote">
                    ✓ Full amount covered by wallet — no gateway payment needed!
                </div>
                @endif
            </div>
        </div>

        <script>
        (function() {
            var walletBalance = Math.floor({{ $walletBalance }});
            var totalAmount = Math.round({{ $totalAmount }});

            function formatCurrency(val) {
                return '₹' + Math.round(parseFloat(val)).toLocaleString('en-IN');
            }

            var couponDiscount = Math.round({{ $couponDiscount }});

            function updateDisplays() {
                var afterCoupon = Math.max(totalAmount - couponDiscount, 0);
                var walletUsed  = Math.min(Math.floor(walletBalance), afterCoupon);
                var gateway     = Math.max(afterCoupon - walletUsed, 0);

                var totalEl = document.getElementById('walletTotalDisplay');
                var deductEl = document.getElementById('walletDeductDisplay');
                var remainEl = document.getElementById('walletRemainingDisplay');
                var fullPayEl = document.getElementById('walletFullPayNote');
                var bannerEl = document.getElementById('checkoutBannerAmount');
                var pageTotalEl = document.getElementById('totalAmountDisplay');

                if (totalEl)  totalEl.textContent  = formatCurrency(couponDiscount > 0 ? afterCoupon : totalAmount);
                if (deductEl) deductEl.textContent  = '- ' + formatCurrency(walletUsed);
                if (remainEl) remainEl.textContent  = formatCurrency(gateway);

                if (fullPayEl) {
                    fullPayEl.style.display = (gateway <= 0 && walletUsed > 0) ? 'block' : 'none';
                }

                // Update page-level displays
                if (bannerEl) bannerEl.textContent = ' ' + formatCurrency(gateway) + ' item 1';
                if (pageTotalEl) pageTotalEl.textContent = formatCurrency(gateway) + '/-';
            }

            // Expose wallet amount getter — accounts for coupon discount.
            // Returns the wallet portion of the discounted EMI, not the original.
            window.getWalletPaymentAmount = function() {
                var afterCoupon = Math.max(totalAmount - couponDiscount, 0);
                return Math.floor(Math.min(walletBalance, afterCoupon));
            };

            // Allow external scripts to update totalAmount (e.g. after coupon applied)
            window.updateWalletTotalAmount = function(newTotal) {
                totalAmount = Math.round(parseFloat(newTotal) || 0);
                updateDisplays();
            };

            // Called by coupon AJAX interceptor after successful validation.
            // amount  = rupee discount amount (number)
            // label   = display label e.g. "SAVE10"
            window.updateCouponDiscount = function(amount, label) {
                couponDiscount = Math.round(parseFloat(amount) || 0);

                var rowEl    = document.getElementById('couponDeductRow');
                var deductEl = document.getElementById('couponDeductDisplay');

                // couponDeductRow intentionally kept hidden — Total Amount already shows the discounted upfront

                // Update the label span if a code was supplied
                if (label) {
                    var labelEl = rowEl ? rowEl.querySelector('span:first-child') : null;
                    if (labelEl) {
                        var icon = labelEl.querySelector('i');
                        labelEl.textContent = 'Coupon (' + label.toUpperCase() + ')';
                        if (icon) labelEl.insertBefore(icon, labelEl.firstChild);
                    }
                }

                updateDisplays();
            };

            // Initial render
            updateDisplays();
        })();
        </script>
    @endif
@endif
