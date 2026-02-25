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
                            <span class="{{ $sale->base_fee_snapshot > $displayPrice ? 'text-primary' : '' }}">{{ handlePrice($displayPrice) }}</span>
                            @if($sale->base_fee_snapshot > $displayPrice)
                                <span class="font-12 text-gray ml-5" style="text-decoration: line-through;">{{ handlePrice($sale->base_fee_snapshot) }}</span>
                            @endif
                        </span>
                    </div>
                    @if($sale->base_fee_snapshot > $displayPrice)
                        <div class="d-flex justify-content-between py-5 border-bottom">
                            <span class="text-gray">Discount</span>
                            <span>
                                <span class="badge badge-warning">Coupon Applied</span>
                                <span class="font-weight-500 text-primary ml-5">{{ handlePrice($sale->base_fee_snapshot - $displayPrice) }} saved</span>
                            </span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Access Status</span>
                        <span>
                            @if($accessResult->hasAccess)
                                @if($sale->pricing_mode === 'installment' && $sale->installmentPlan)
                                    @php
                                        $hasDueSchedules = $sale->installmentPlan->schedules->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])->count() > 0;
                                    @endphp
                                    @if($hasDueSchedules)
                                        <span class="badge badge-primary">Active</span>
                                        <span class="badge badge-warning ml-5">EMI Due</span>
                                    @else
                                        <span class="badge badge-primary">Fully Paid</span>
                                    @endif
                                @else
                                    <span class="badge badge-primary">Active</span>
                                @endif
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

                    @if($sale->product && in_array($sale->product->product_type, ['course_video', 'webinar', 'course_live']) && $accessResult->hasAccess)
                        @php $webinarForLink = \App\Models\Webinar::find($sale->product->external_id); @endphp
                        @if($webinarForLink && $webinarForLink->slug)
                            <div class="mt-15 text-center">
                                <a href="/course/learning/{{ $webinarForLink->slug }}" target="_blank" class="btn btn-success btn-block">
                                    <i class="fa fa-play-circle"></i> Go to Learning Page
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <div class="col-12 col-lg-6 mt-15 mt-lg-0">
                {{-- Payment Progress --}}
                @if($sale->installmentPlan)
                    @php
                        $plan = $sale->installmentPlan;
                        $schedules = $plan->schedules->sortBy('sequence');
                        $totalPaidDisplay = $schedules->sum('amount_paid');
                        $totalRemaining = max(0, $plan->total_amount - $totalPaidDisplay);
                        $paidPercent = $plan->total_amount > 0 ? min(100, round(($totalPaidDisplay / $plan->total_amount) * 100)) : 0;
                        $paidSchedules = $schedules->whereIn('status', ['paid', 'waived']);
                        $activeSchedules = $schedules->whereNotIn('status', ['paid', 'waived']);
                    @endphp
                    <div class="panel-section-card py-20 px-25">
                        <h3 class="font-16 font-weight-bold text-dark-blue mb-15">EMI Payment Progress</h3>

                        <div class="row text-center mb-15">
                            <div class="col-4">
                                <div class="font-12 text-gray">Total Amount</div>
                                <div class="font-18 font-weight-bold">{{ handlePrice($plan->total_amount) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray">Paid</div>
                                <div class="font-18 font-weight-bold text-primary">{{ handlePrice($totalPaidDisplay) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray">Remaining</div>
                                <div class="font-18 font-weight-bold text-danger">{{ handlePrice($totalRemaining) }}</div>
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
                                <div class="font-20 font-weight-bold text-primary">{{ handlePrice($ledgerSummary['net_balance']) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="font-12 text-gray">Course Price</div>
                                @if($sale->base_fee_snapshot > $ledgerSummary['net_balance'])
                                    <div class="font-20 font-weight-bold" style="text-decoration: line-through; color: #aaa;">{{ handlePrice($sale->base_fee_snapshot) }}</div>
                                @else
                                    <div class="font-20 font-weight-bold">{{ handlePrice($sale->base_fee_snapshot) }}</div>
                                @endif
                            </div>
                        </div>
                        @if($sale->base_fee_snapshot > $ledgerSummary['net_balance'])
                            <div class="text-center mt-10">
                                <span class="badge badge-warning">Coupon Applied</span>
                                <span class="font-12 text-primary font-weight-500 ml-5">{{ handlePrice($sale->base_fee_snapshot - $ledgerSummary['net_balance']) }} saved</span>
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
                                    <td>{{ handlePrice($schedule->amount_due) }}</td>
                                    <td>
                                        @if(($schedule->amount_paid ?? 0) > 0)
                                            <span class="text-primary font-weight-500">{{ handlePrice($schedule->amount_paid) }}</span>
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
                                    <td class="font-weight-500">{{ handlePrice($entry->amount) }}</td>
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

                {{-- Request Upgrade (hidden — not wired to support/admin panels yet) --}}
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
