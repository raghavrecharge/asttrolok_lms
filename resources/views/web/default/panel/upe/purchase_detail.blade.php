@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class="section-title">Purchase Details — #{{ $sale->id }}</h2>

        <div class="row mt-20">
            {{-- Sale Info --}}
            <div class="col-12 col-lg-6">
                <div class="panel-section-card py-20 px-25">
                    <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Purchase Information</h3>

                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Product</span>
                        <span class="font-weight-500">
                            @if($sale->product)
                                {{ $sale->product->name }}
                                <span class="font-12 text-gray">({{ ucfirst($sale->product->product_type) }})</span>
                            @else
                                Product #{{ $sale->product_id }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Type</span>
                        <span class="font-weight-500">{{ ucfirst($sale->pricing_mode) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Base Amount</span>
                        <span class="font-weight-500">{{ handlePrice($sale->base_fee_snapshot) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Status</span>
                        <span>
                            @php
                                $statusColors = [
                                    'active' => 'primary',
                                    'pending_payment' => 'warning',
                                    'refunded' => 'danger',
                                    'partially_refunded' => 'warning',
                                ];
                                $statusClass = 'badge-' . ($statusColors[$sale->status] ?? 'secondary');
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Valid From</span>
                        <span>{{ $sale->valid_from ? \Carbon\Carbon::parse($sale->valid_from)->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Valid Until</span>
                        <span>
                            @if($sale->valid_until)
                                {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                    <span class="text-danger font-12">(expired)</span>
                                @endif
                            @else
                                Lifetime
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5">
                        <span class="text-gray">Purchased On</span>
                        <span>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Balance & Access --}}
            <div class="col-12 col-lg-6 mt-15 mt-lg-0">
                <div class="panel-section-card py-20 px-25">
                    <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Balance & Access</h3>

                    <div class="row text-center mb-15">
                        <div class="col-4">
                            <div class="font-12 text-gray">Credits</div>
                            <div class="font-20 font-weight-bold text-primary">{{ handlePrice($ledgerSummary['total_credits']) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="font-12 text-gray">Debits</div>
                            <div class="font-20 font-weight-bold text-danger">{{ handlePrice($ledgerSummary['total_debits']) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="font-12 text-gray">Balance</div>
                            <div class="font-20 font-weight-bold">{{ handlePrice($ledgerSummary['net_balance']) }}</div>
                        </div>
                    </div>

                    <div class="p-10 rounded {{ $accessResult->hasAccess ? 'bg-primary' : 'bg-danger' }} text-white text-center">
                        @if($accessResult->hasAccess)
                            <i data-feather="check-circle" width="16" height="16"></i> <strong>Access Granted</strong> ({{ ucfirst($accessResult->accessType) }})
                        @else
                            <i data-feather="x-circle" width="16" height="16"></i> <strong>No Access</strong>
                            <div class="font-12 mt-5">{{ $accessResult->reason }}</div>
                        @endif
                    </div>
                </div>

                {{-- Installment Plan Summary --}}
                @if($sale->installmentPlan)
                    <div class="panel-section-card py-20 px-25 mt-15">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-10">EMI Plan</h3>
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Status</span>
                            <span class="badge badge-primary">{{ ucfirst($sale->installmentPlan->status) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Total</span>
                            <span>{{ handlePrice($sale->installmentPlan->total_amount) }}</span>
                        </div>
                        <a href="/panel/upe/installments/{{ $sale->installmentPlan->id }}" class="btn btn-sm btn-primary mt-10">View EMI Details</a>
                    </div>
                @endif

                {{-- Subscription Summary --}}
                @if($sale->subscription)
                    <div class="panel-section-card py-20 px-25 mt-15">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-10">Subscription</h3>
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Status</span>
                            <span class="badge badge-primary">{{ ucfirst($sale->subscription->status) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Billing</span>
                            <span>{{ handlePrice($sale->subscription->billing_amount) }} / {{ $sale->subscription->billing_interval }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Current Period</span>
                            <span>{{ \Carbon\Carbon::parse($sale->subscription->current_period_start)->format('d M') }} — {{ \Carbon\Carbon::parse($sale->subscription->current_period_end)->format('d M Y') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Ledger Entries --}}
    <section class="mt-25">
        <h2 class="section-title">Payment History</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="table-responsive">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th class="text-left">Type</th>
                            <th>Direction</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th class="text-left">Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sale->ledgerEntries->sortByDesc('id') as $entry)
                            <tr>
                                <td class="text-left">
                                    <span class="badge badge-circle-white font-12 px-5 py-2">{{ str_replace('_',' ',ucfirst($entry->entry_type)) }}</span>
                                </td>
                                <td>
                                    @if($entry->direction === 'credit')
                                        <span class="text-primary"><i data-feather="arrow-down-circle" width="14" height="14"></i> Credit</span>
                                    @else
                                        <span class="text-danger"><i data-feather="arrow-up-circle" width="14" height="14"></i> Debit</span>
                                    @endif
                                </td>
                                <td class="font-weight-500">{{ handlePrice($entry->amount) }}</td>
                                <td>{{ $entry->payment_method ?? '-' }}</td>
                                <td class="text-left font-12">{{ $entry->description ?? '-' }}</td>
                                <td class="font-12">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-gray py-15">No payment records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Action Forms --}}
    @if(in_array($sale->status, ['active', 'partially_refunded']))
        <section class="mt-25">
            <h2 class="section-title">Actions</h2>

            <div class="row mt-20">
                {{-- Request Refund --}}
                <div class="col-12 col-lg-6">
                    <div class="panel-section-card py-20 px-25">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Request Refund</h3>
                        <form method="POST" action="/panel/upe/request-refund">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            <div class="form-group">
                                <label class="input-label">Refund Amount</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="1" max="{{ $ledgerSummary['net_balance'] }}" value="{{ $ledgerSummary['net_balance'] }}" required>
                                <small class="text-gray">Max refundable: {{ handlePrice($ledgerSummary['net_balance']) }}</small>
                            </div>
                            <div class="form-group">
                                <label class="input-label">Reason</label>
                                <textarea name="reason" class="form-control" rows="3" required placeholder="Please describe why you want a refund..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Submit refund request? An admin will review it.')">
                                Submit Refund Request
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Request Upgrade --}}
                <div class="col-12 col-lg-6 mt-15 mt-lg-0">
                    <div class="panel-section-card py-20 px-25">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Request Upgrade</h3>
                        <form method="POST" action="/panel/upe/request-upgrade">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            <div class="form-group">
                                <label class="input-label">Upgrade To</label>
                                <select name="target_product_id" class="form-control" required>
                                    <option value="">Select a course to upgrade to...</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->product_type) }}) — {{ handlePrice($p->base_fee) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="input-label">Reason (optional)</label>
                                <input type="text" name="reason" class="form-control" placeholder="e.g. Want to switch to advanced course">
                            </div>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Submit upgrade request? An admin will review it.')">
                                Submit Upgrade Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row mt-20">
                {{-- Request Course Extension --}}
                @if($sale->valid_until)
                    <div class="col-12 col-lg-6">
                        <div class="panel-section-card py-20 px-25">
                            <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Request Extension</h3>
                            <p class="font-12 text-gray mb-10">
                                Current validity: {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                    <span class="text-danger">(expired)</span>
                                @else
                                    ({{ \Carbon\Carbon::parse($sale->valid_until)->diffForHumans() }})
                                @endif
                            </p>
                            <form method="POST" action="/panel/upe/request-extension">
                                @csrf
                                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                <div class="form-group">
                                    <label class="input-label">Extension Period</label>
                                    <select name="extension_days" class="form-control" required>
                                        <option value="7">7 Days</option>
                                        <option value="15">15 Days</option>
                                        <option value="30">30 Days</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="input-label">Reason</label>
                                    <textarea name="reason" class="form-control" rows="2" required placeholder="Why do you need an extension?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Submit extension request? Max 3 per purchase.')">
                                    Submit Extension Request
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- Apply Coupon Code --}}
                <div class="col-12 col-lg-6 mt-15 mt-lg-0">
                    <div class="panel-section-card py-20 px-25">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Apply Coupon Code</h3>
                        <p class="font-12 text-gray mb-10">Have a coupon? Submit it here — support will verify and apply the discount.</p>
                        <form method="POST" action="/panel/upe/request-coupon">
                            @csrf
                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                            <div class="form-group">
                                <label class="input-label">Coupon Code</label>
                                <input type="text" name="coupon_code" class="form-control" required placeholder="Enter coupon code" style="text-transform: uppercase;">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Submit coupon for verification?')">
                                Submit Coupon
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
