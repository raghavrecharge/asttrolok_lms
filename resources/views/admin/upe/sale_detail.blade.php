@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/upe/sales">UPE Sales</a></div>
                <div class="breadcrumb-item">Sale #{{ $sale->id }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                {{-- Sale Info --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h4>Sale Information</h4></div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr><th width="35%">Sale ID</th><td>{{ $sale->id }}</td></tr>
                                <tr><th>UUID</th><td><code class="small">{{ $sale->uuid }}</code></td></tr>
                                <tr>
                                    <th>User</th>
                                    <td>
                                        @if($sale->user)
                                            <a href="{{ getAdminPanelUrl() }}/users/{{ $sale->user_id }}/edit" target="_blank">
                                                {{ $sale->user->full_name }}
                                            </a>
                                            <div class="text-muted small">{{ $sale->user->email }} | {{ $sale->user->mobile }}</div>
                                        @else
                                            User #{{ $sale->user_id }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Product</th>
                                    <td>
                                        @if($sale->product)
                                            <span class="badge badge-light">{{ $sale->product->product_type }}</span>
                                            {{ $sale->product->name }}
                                            <div class="text-muted small">External ID: {{ $sale->product->external_id }}</div>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>Sale Type</th><td><span class="badge badge-info">{{ $sale->sale_type }}</span></td></tr>
                                <tr><th>Pricing Mode</th><td><span class="badge badge-primary">{{ $sale->pricing_mode }}</span></td></tr>
                                <tr><th>Base Fee Snapshot</th><td>₹{{ number_format($sale->base_fee_snapshot, 2) }}</td></tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $statusColors = ['active'=>'success','pending_payment'=>'warning','completed'=>'primary','refunded'=>'danger','partially_refunded'=>'warning','expired'=>'secondary','cancelled'=>'dark','suspended'=>'danger'];
                                        @endphp
                                        <span class="badge badge-{{ $statusColors[$sale->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $sale->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr><th>Valid From</th><td>{{ $sale->valid_from ? \Carbon\Carbon::parse($sale->valid_from)->format('d M Y H:i') : '-' }}</td></tr>
                                <tr>
                                    <th>Valid Until</th>
                                    <td>
                                        @if($sale->valid_until)
                                            {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y H:i') }}
                                            @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                                <span class="badge badge-danger">Expired</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Lifetime</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><th>Created</th><td>{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y H:i') }}</td></tr>
                                @if($sale->metadata)
                                    <tr><th>Metadata</th><td><pre class="small mb-0">{{ json_encode($sale->metadata, JSON_PRETTY_PRINT) }}</pre></td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Access & Balance --}}
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><h4>Access & Balance</h4></div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="p-3 border rounded text-center">
                                        <div class="text-muted small">Access Status</div>
                                        @if($accessResult->hasAccess)
                                            <div class="h4 text-success mb-0"><i class="fas fa-check-circle"></i> Granted</div>
                                            <div class="small text-muted">{{ $accessResult->accessType }}</div>
                                        @else
                                            <div class="h4 text-danger mb-0"><i class="fas fa-times-circle"></i> Denied</div>
                                            <div class="small text-muted">{{ $accessResult->reason }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 border rounded text-center">
                                        <div class="text-muted small">Net Balance</div>
                                        <div class="h4 mb-0 {{ $ledgerSummary['net_balance'] > 0 ? 'text-success' : 'text-danger' }}">
                                            ₹{{ number_format($ledgerSummary['net_balance'], 2) }}
                                        </div>
                                        <div class="small text-muted">
                                            Credits: ₹{{ number_format($ledgerSummary['total_credits'], 2) }} |
                                            Debits: ₹{{ number_format($ledgerSummary['total_debits'], 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Action Buttons --}}
                            @if(in_array($sale->status, ['active', 'partially_refunded']))
                                <div class="mb-3">
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#refundModal">
                                        <i class="fas fa-undo"></i> Process Refund
                                    </button>
                                </div>
                            @endif

                            @if($sale->status === 'pending_payment')
                                <div class="mb-3">
                                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#offlinePaymentModal">
                                        <i class="fas fa-money-bill"></i> Record Offline Payment
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Installment Plan --}}
                    @if($sale->installmentPlan)
                        <div class="card">
                            <div class="card-header"><h4>Installment Plan</h4></div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <strong>Status:</strong>
                                    <span class="badge badge-{{ $sale->installmentPlan->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($sale->installmentPlan->status) }}
                                    </span>
                                    | <strong>Total:</strong> ₹{{ number_format($sale->installmentPlan->total_amount, 2) }}
                                    | <strong>Installments:</strong> {{ $sale->installmentPlan->total_installments ?? $sale->installmentPlan->num_installments }}
                                </div>
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Status</th></tr></thead>
                                    <tbody>
                                        @foreach($sale->installmentPlan->schedules->sortBy('installment_number') as $schedule)
                                            <tr>
                                                <td>{{ $schedule->installment_number ?? $schedule->sequence }}</td>
                                                <td>{{ $schedule->due_date ? \Carbon\Carbon::parse($schedule->due_date)->format('d M Y') : '-' }}</td>
                                                <td>₹{{ number_format($schedule->amount_due, 2) }}</td>
                                                <td>₹{{ number_format($schedule->amount_paid ?? 0, 2) }}</td>
                                                <td>
                                                    @php
                                                        $schedColors = ['paid'=>'success','due'=>'warning','upcoming'=>'info','partial'=>'warning','overdue'=>'danger','waived'=>'secondary'];
                                                    @endphp
                                                    <span class="badge badge-{{ $schedColors[$schedule->status] ?? 'secondary' }}">{{ ucfirst($schedule->status) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Subscription --}}
                    @if($sale->subscription)
                        <div class="card">
                            <div class="card-header"><h4>Subscription</h4></div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><th>Status</th><td><span class="badge badge-{{ $sale->subscription->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($sale->subscription->status) }}</span></td></tr>
                                    <tr><th>Billing</th><td>₹{{ number_format($sale->subscription->billing_amount, 2) }} / {{ $sale->subscription->billing_interval }}</td></tr>
                                    <tr><th>Current Period</th><td>{{ \Carbon\Carbon::parse($sale->subscription->current_period_start)->format('d M Y') }} — {{ \Carbon\Carbon::parse($sale->subscription->current_period_end)->format('d M Y') }}</td></tr>
                                    <tr><th>Grace Days</th><td>{{ $sale->subscription->grace_period_days }}</td></tr>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ledger Entries --}}
            <div class="card">
                <div class="card-header"><h4>Ledger Entries ({{ $ledgerSummary['entry_count'] }})</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Direction</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Description</th>
                                    <th>Gateway Txn</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sale->ledgerEntries->sortByDesc('id') as $entry)
                                    <tr>
                                        <td>{{ $entry->id }}</td>
                                        <td><span class="badge badge-light">{{ $entry->entry_type }}</span></td>
                                        <td>
                                            @if($entry->direction === 'credit')
                                                <span class="text-success"><i class="fas fa-arrow-down"></i> Credit</span>
                                            @else
                                                <span class="text-danger"><i class="fas fa-arrow-up"></i> Debit</span>
                                            @endif
                                        </td>
                                        <td>₹{{ number_format($entry->amount, 2) }}</td>
                                        <td>{{ $entry->payment_method ?? '-' }}</td>
                                        <td class="small">{{ $entry->description ?? '-' }}</td>
                                        <td class="small"><code>{{ $entry->gateway_transaction_id ?? '-' }}</code></td>
                                        <td class="small">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No ledger entries</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Refund Modal --}}
    <div class="modal fade" id="refundModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ getAdminPanelUrl() }}/upe/refund">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Process Refund — Sale #{{ $sale->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Current balance: <strong>₹{{ number_format($ledgerSummary['net_balance'], 2) }}</strong></p>
                        <div class="form-group">
                            <label>Refund Amount (₹)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" max="{{ $ledgerSummary['net_balance'] }}" value="{{ $ledgerSummary['net_balance'] }}" required>
                        </div>
                        <div class="form-group">
                            <label>Reason</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Reason for refund..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Process Refund</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Offline Payment Modal --}}
    <div class="modal fade" id="offlinePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ getAdminPanelUrl() }}/upe/offline-payment">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Offline Payment — Sale #{{ $sale->id }}</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Base fee: <strong>₹{{ number_format($sale->base_fee_snapshot, 2) }}</strong></p>
                        <div class="form-group">
                            <label>Amount Received (₹)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" value="{{ $sale->base_fee_snapshot }}" required>
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="payment_link">Payment Link</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description (optional)</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. Received via NEFT, ref #12345">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
