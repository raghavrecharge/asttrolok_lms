{{-- Wallet Payment Widget - Auto-applied for all purchases (wallet-mediated flow) --}}
{{-- Required: $totalAmount (float). Wallet balance auto-fetched. --}}
@if(auth()->check())
    @php
        $walletBalance = $walletBalance ?? app(\App\Services\PaymentEngine\WalletService::class)->balance(auth()->id());
        $totalAmount = (int) round($totalAmount ?? 0);
        $walletUsed = (int) min(floor($walletBalance), $totalAmount);
        $gatewayAmount = max($totalAmount - $walletUsed, 0);
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

            function updateDisplays() {
                var walletUsed = Math.min(Math.floor(walletBalance), totalAmount);
                var gateway = Math.max(totalAmount - walletUsed, 0);

                var totalEl = document.getElementById('walletTotalDisplay');
                var deductEl = document.getElementById('walletDeductDisplay');
                var remainEl = document.getElementById('walletRemainingDisplay');
                var fullPayEl = document.getElementById('walletFullPayNote');
                var bannerEl = document.getElementById('checkoutBannerAmount');
                var pageTotalEl = document.getElementById('totalAmountDisplay');

                if (totalEl) totalEl.textContent = formatCurrency(totalAmount);
                if (deductEl) deductEl.textContent = '- ' + formatCurrency(walletUsed);
                if (remainEl) remainEl.textContent = formatCurrency(gateway);

                if (fullPayEl) {
                    fullPayEl.style.display = (gateway <= 0 && walletUsed > 0) ? 'block' : 'none';
                }

                // Update page-level displays
                if (bannerEl) bannerEl.textContent = ' ' + formatCurrency(gateway) + ' item 1';
                if (pageTotalEl) pageTotalEl.textContent = formatCurrency(gateway) + '/-';
            }

            // Expose wallet amount getter (always returns auto-calculated amount)
            window.getWalletPaymentAmount = function() {
                return Math.floor(Math.min(walletBalance, totalAmount));
            };

            // Allow external scripts to update totalAmount (e.g. after coupon applied)
            window.updateWalletTotalAmount = function(newTotal) {
                totalAmount = Math.round(parseFloat(newTotal) || 0);
                updateDisplays();
            };

            // Initial render
            updateDisplays();
        })();
        </script>
    @endif
@endif
