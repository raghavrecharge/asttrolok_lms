@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
<style>
    .wallet-balance-card {
        background: linear-gradient(135deg, #1f3b64 0%, #2d5aa0 100%);
        border-radius: 16px;
        padding: 30px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .wallet-balance-card::after {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .wallet-balance-label {
        font-size: 14px;
        opacity: 0.8;
        margin-bottom: 5px;
    }
    .wallet-balance-amount {
        font-size: 36px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .wallet-actions {
        margin-top: 20px;
    }
    .wallet-add-btn {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        border-radius: 10px;
        padding: 10px 24px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .wallet-add-btn:hover {
        background: rgba(255,255,255,0.3);
        color: #fff;
        text-decoration: none;
    }

    .wallet-add-form {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f0f0f0;
        padding: 24px;
    }
    .wallet-add-form h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1f3b64;
        margin-bottom: 16px;
    }
    .wallet-amount-input {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 18px;
        font-weight: 600;
        width: 100%;
        transition: border-color 0.2s;
    }
    .wallet-amount-input:focus {
        outline: none;
        border-color: #1f3b64;
    }
    .quick-amount-btn {
        background: #f5f7fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #1f3b64;
        cursor: pointer;
        transition: all 0.2s;
    }
    .quick-amount-btn:hover, .quick-amount-btn.active {
        background: #1f3b64;
        color: #fff;
        border-color: #1f3b64;
    }
    .add-funds-btn {
        background: #1f3b64;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 12px 32px;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
    }
    .add-funds-btn:hover {
        background: #2d5aa0;
    }
    .add-funds-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .txn-table {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        border: 1px solid #f0f0f0;
        overflow: hidden;
    }
    .txn-table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f0f0f0;
    }
    .txn-table-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1f3b64;
        margin: 0;
    }
    .txn-table table {
        width: 100%;
        border-collapse: collapse;
    }
    .txn-table table th {
        background: #f8f9fc;
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #f0f0f0;
    }
    .txn-table table td {
        padding: 14px 16px;
        font-size: 14px;
        color: #333;
        border-bottom: 1px solid #f5f5f5;
        vertical-align: middle;
    }
    .txn-table table tr:last-child td {
        border-bottom: none;
    }
    .txn-credit {
        color: #28a745;
        font-weight: 600;
    }
    .txn-debit {
        color: #dc3545;
        font-weight: 600;
    }
    .txn-type-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .txn-type-badge.top_up { background: #e8f5e9; color: #2e7d32; }
    .txn-type-badge.refund { background: #e3f2fd; color: #1565c0; }
    .txn-type-badge.purchase, .txn-type-badge.wallet_payment { background: #fff3e0; color: #e65100; }
    .txn-type-badge.admin_credit { background: #f3e5f5; color: #7b1fa2; }
    .txn-type-badge.admin_debit { background: #fce4ec; color: #c62828; }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .empty-state i { margin-bottom: 10px; }

@endpush

@section('content')
    <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
        <div>
            <h1 class="section-title">My Wallet</h1>
            <p class="font-14 text-gray mt-5">Manage your wallet balance and transactions</p>
        </div>
    </div>

    {{-- Balance Card --}}
    <div class="row mt-25">
        <div class="col-12 col-lg-7">
            <div class="wallet-balance-card">
                <div class="wallet-balance-label">Available Balance</div>
                <div class="wallet-balance-amount" id="walletBalanceDisplay">{{ handlePrice((float)$wallet->balance) }}</div>
            </div>
        </div>

        {{-- Add Funds Form --}}
        <div class="col-12 col-lg-5 mt-20 mt-lg-0">
            <div class="wallet-add-form h-100">
                <h3>Add Money</h3>
                <div class="mb-10">
                    <input type="number" id="topUpAmount" class="wallet-amount-input" placeholder="Enter amount" min="1" max="500000">
                </div>
                <div class="d-flex flex-wrap gap-2 mb-15" style="gap: 8px;">
                    <button type="button" class="quick-amount-btn" data-amount="100">₹100</button>
                    <button type="button" class="quick-amount-btn" data-amount="500">₹500</button>
                    <button type="button" class="quick-amount-btn" data-amount="1000">₹1,000</button>
                    <button type="button" class="quick-amount-btn" data-amount="2000">₹2,000</button>
                    <button type="button" class="quick-amount-btn" data-amount="5000">₹5,000</button>
                </div>
                <button type="button" id="addFundsBtn" class="add-funds-btn" disabled>Add Funds via Razorpay</button>
            </div>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="txn-table mt-30">
        <div class="txn-table-header">
            <h3>Transaction History</h3>
        </div>

        @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->count() > 0)
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $txn)
                            <tr>
                                <td>{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <span class="txn-type-badge {{ $txn->transaction_type }}">
                                        {{ str_replace('_', ' ', $txn->transaction_type) }}
                                    </span>
                                </td>
                                <td>{{ $txn->description ?? '-' }}</td>
                                <td>
                                    @if($txn->isCredit())
                                        <span class="txn-credit">+{{ handlePrice((float)$txn->amount) }}</span>
                                    @else
                                        <span class="txn-debit">-{{ handlePrice((float)$txn->amount) }}</span>
                                    @endif
                                </td>
                                <td>{{ handlePrice((float)$txn->balance_after) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-15">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="empty-state">
                <i data-feather="inbox" width="40" height="40"></i>
                <p class="mt-10 font-14">No transactions yet</p>
            </div>
        @endif
    </div>

    <div class="wallet-loader" id="walletLoader">
        <div class="spinner"></div>
    </div>
@endsection

@push('scripts_bottom')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const topUpInput = document.getElementById('topUpAmount');
    const addFundsBtn = document.getElementById('addFundsBtn');
    const quickAmountBtns = document.querySelectorAll('.quick-amount-btn');
    const loader = document.getElementById('walletLoader');

    // Quick amount buttons
    quickAmountBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            topUpInput.value = amount;
            quickAmountBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            addFundsBtn.disabled = false;
        });
    });

    // Enable/disable button based on input
    topUpInput.addEventListener('input', function() {
        const val = parseFloat(this.value);
        addFundsBtn.disabled = !(val > 0 && val <= 500000);
        quickAmountBtns.forEach(function(b) {
            b.classList.toggle('active', parseInt(b.getAttribute('data-amount')) === parseInt(topUpInput.value));
        });
    });

    // Add Funds
    addFundsBtn.addEventListener('click', function() {
        const amount = parseFloat(topUpInput.value);
        if (!amount || amount < 1) {
            alert('Please enter a valid amount');
            return;
        }

        addFundsBtn.disabled = true;
        loader.style.display = 'flex';

        fetch('/panel/wallet/add-funds', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ amount: amount })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                throw new Error(data.error);
            }

            // Open Razorpay checkout
            var options = {
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: 'Asttrolok - Wallet Top Up',
                description: 'Add ₹' + amount + ' to wallet',
                order_id: data.razorpay_order_id,
                handler: function(response) {
                    loader.style.display = 'flex';
                    // Verify payment
                    fetch('/panel/wallet/verify-topup', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature
                        })
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(verifyData) {
                        if (verifyData.success) {
                            alert('₹' + verifyData.amount_added + ' added to your wallet successfully!');
                            window.location.reload();
                        } else {
                            throw new Error(verifyData.error || 'Verification failed');
                        }
                    })
                    .catch(function(err) {
                        alert('Payment verification failed: ' + err.message);
                        loader.style.display = 'none';
                        addFundsBtn.disabled = false;
                    });
                },
                prefill: {
                    name: data.user_name || '',
                    email: data.user_email || '',
                    contact: data.user_contact || ''
                },
                theme: { color: '#1f3b64' },
                modal: {
                    ondismiss: function() {
                        loader.style.display = 'none';
                        addFundsBtn.disabled = false;
                    }
                }
            };

            loader.style.display = 'none';
            var rzp = new Razorpay(options);
            rzp.open();
        })
        .catch(function(err) {
            alert('Failed to initiate payment: ' + err.message);
            loader.style.display = 'none';
            addFundsBtn.disabled = false;
        });
    });
});
</script>
@endpush
