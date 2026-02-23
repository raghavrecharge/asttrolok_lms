@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger mb-20">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                            @else
                                Product #{{ $sale->product_id }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Payment Mode</span>
                        <span class="font-weight-500">
                            @if($sale->pricing_mode === 'installment')
                                EMI / Installment
                            @else
                                {{ ucfirst($sale->pricing_mode) }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Course Price</span>
                        <span class="font-weight-500">
                            @php
                                $displayPrice = $sale->installmentPlan
                                    ? $sale->installmentPlan->total_amount
                                    : $ledgerSummary['net_balance'];
                            @endphp
                            <span class="{{ $sale->base_fee_snapshot > $displayPrice ? 'text-primary' : '' }}">₹{{ number_format($displayPrice, 2) }}</span>
                            @if($sale->base_fee_snapshot > $displayPrice)
                                <span class="font-12 text-gray ml-5" style="text-decoration: line-through;">₹{{ number_format($sale->base_fee_snapshot, 2) }}</span>
                            @endif
                        </span>
                    </div>
                    @if($sale->base_fee_snapshot > $displayPrice)
                        <div class="d-flex justify-content-between py-5 border-bottom">
                            <span class="text-gray">Discount</span>
                            <span>
                                <span class="badge badge-warning">Coupon Applied</span>
                                <span class="font-weight-500 text-primary ml-5">₹{{ number_format($sale->base_fee_snapshot - $displayPrice, 2) }} saved</span>
                            </span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Access Status</span>
                        <span>
                            @if($accessResult->hasAccess)
                                <span class="badge badge-primary">Active</span>
                            @elseif($sale->status === 'pending_payment')
                                <span class="badge badge-warning">Payment Pending</span>
                            @elseif($sale->status === 'refunded')
                                <span class="badge badge-danger">Refunded</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
                            @endif
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

            <div class="col-12 col-lg-6 mt-15 mt-lg-0">
                {{-- Payment Progress --}}
                @if($sale->installmentPlan)
                    @php
                        $plan = $sale->installmentPlan;
                        $schedules = $plan->schedules->sortBy('sequence');
                        $paidSchedules = $schedules->where('status', 'paid');
                        $emiTotalPaid = $paidSchedules->sum('amount_due');
                        $partialPaid = $schedules->where('status', 'partial')->sum('amount_paid');
                        $totalPaidDisplay = $emiTotalPaid + $partialPaid;
                        $totalRemaining = max(0, $plan->total_amount - $totalPaidDisplay);
                        $paidPercent = $plan->total_amount > 0 ? round(($totalPaidDisplay / $plan->total_amount) * 100) : 0;
                    @endphp
                    <div class="panel-section-card py-20 px-25">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-15">EMI Payment Progress</h3>

                        <div class="row text-center mb-15">
                            <div class="col-4">
                                <div class="font-12 text-gray">Total Amount</div>
                                <div class="font-18 font-weight-bold">₹{{ number_format($plan->total_amount, 2) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray">Paid</div>
                                <div class="font-18 font-weight-bold text-primary">₹{{ number_format($totalPaidDisplay, 2) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray">Remaining</div>
                                <div class="font-18 font-weight-bold text-danger">₹{{ number_format($totalRemaining, 2) }}</div>
                            </div>
                        </div>

                        <div class="progress mb-10" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: {{ $paidPercent }}%"></div>
                        </div>
                        <div class="font-12 text-gray text-center">{{ $paidPercent }}% paid — {{ $paidSchedules->count() }} of {{ $schedules->count() }} installments completed</div>

                        <div class="mt-15">
                            <a href="/panel/upe/installments/{{ $plan->id }}" class="btn btn-sm btn-primary">View EMI Schedule</a>
                        </div>
                    </div>
                @else
                    <div class="panel-section-card py-20 px-25">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Payment Summary</h3>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="font-12 text-gray">Amount Paid</div>
                                <div class="font-20 font-weight-bold text-primary">₹{{ number_format($ledgerSummary['net_balance'], 2) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="font-12 text-gray">Course Price</div>
                                @if($sale->base_fee_snapshot > $ledgerSummary['net_balance'])
                                    <div class="font-20 font-weight-bold" style="text-decoration: line-through; color: #aaa;">₹{{ number_format($sale->base_fee_snapshot, 2) }}</div>
                                @else
                                    <div class="font-20 font-weight-bold">₹{{ number_format($sale->base_fee_snapshot, 2) }}</div>
                                @endif
                            </div>
                        </div>
                        @if($sale->base_fee_snapshot > $ledgerSummary['net_balance'])
                            <div class="text-center mt-10">
                                <span class="badge badge-warning">Coupon Applied</span>
                                <span class="font-12 text-primary font-weight-500 ml-5">₹{{ number_format($sale->base_fee_snapshot - $ledgerSummary['net_balance'], 2) }} saved</span>
                            </div>
                        @endif
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
                            <span>₹{{ number_format($sale->subscription->billing_amount, 2) }} / {{ $sale->subscription->billing_interval }}</span>
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

    {{-- Payment History --}}
    @if($sale->installmentPlan && $sale->installmentPlan->schedules->count() > 0)
        <section class="mt-25">
            <h2 class="section-title">Payment History</h2>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Installment</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->installmentPlan->schedules->sortBy('sequence') as $schedule)
                                @php
                                    $schedLabel = $schedule->sequence === 1 ? 'Upfront' : 'EMI ' . ($schedule->sequence - 1);
                                    $schedBadge = match($schedule->status) {
                                        'paid' => 'badge-primary',
                                        'partial' => 'badge-warning',
                                        'due', 'overdue' => 'badge-danger',
                                        default => 'badge-secondary',
                                    };
                                    $schedStatusLabel = match($schedule->status) {
                                        'paid' => 'Paid',
                                        'partial' => 'Partially Paid',
                                        'due' => 'Due',
                                        'overdue' => 'Overdue',
                                        'upcoming' => 'Upcoming',
                                        default => ucfirst($schedule->status),
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $schedule->sequence }}</td>
                                    <td class="font-weight-500">{{ $schedLabel }}</td>
                                    <td>₹{{ number_format($schedule->amount_due, 2) }}</td>
                                    <td>
                                        @if(($schedule->amount_paid ?? 0) > 0)
                                            <span class="text-primary font-weight-500">₹{{ number_format($schedule->amount_paid, 2) }}</span>
                                        @else
                                            <span class="text-gray">-</span>
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $schedBadge }} px-10 py-5">{{ $schedStatusLabel }}</span></td>
                                    <td class="font-12">
                                        @if($schedule->paid_at)
                                            {{ \Carbon\Carbon::parse($schedule->paid_at)->format('d M Y') }}
                                        @elseif($schedule->due_date)
                                            <span class="text-gray">Due: {{ \Carbon\Carbon::parse($schedule->due_date)->format('d M Y') }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @elseif($sale->ledgerEntries->count() > 0)
        <section class="mt-25">
            <h2 class="section-title">Payment History</h2>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->ledgerEntries->where('direction', 'credit')->sortByDesc('id') as $entry)
                                <tr>
                                    <td class="font-weight-500">₹{{ number_format($entry->amount, 2) }}</td>
                                    <td>{{ ucfirst($entry->payment_method ?? '-') }}</td>
                                    <td class="font-12">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif

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
                                <label class="input-label">Refund Amount (₹)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="1" max="{{ $ledgerSummary['net_balance'] }}" value="{{ $ledgerSummary['net_balance'] }}" required>
                                <small class="text-gray">Max refundable: ₹{{ number_format($ledgerSummary['net_balance'], 2) }}</small>
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
                                        <option value="{{ $p->id }}">{{ $p->name }} ({{ ucfirst($p->product_type) }}) — ₹{{ number_format($p->base_fee, 2) }}</option>
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
        </section>
    @endif
@endsection
