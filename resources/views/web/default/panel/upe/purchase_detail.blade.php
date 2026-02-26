@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .panel-section-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .panel-section-card:hover {
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.07);
            transform: translateY(-2px);
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #fcfcfc;
            border-radius: 12px;
            border: 1px solid #f5f5f5;
            height: 100%;
            transition: all 0.2s ease;
        }
        .summary-item:hover {
            background: #fff;
            border-color: #43d477;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        }
        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(67, 212, 119, 0.1) 0%, rgba(56, 179, 100, 0.1) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #43d477;
        }
        .summary-label {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 13px;
            font-weight: 500;
        }
        .summary-value {
            font-weight: 700;
            color: #1f3b64;
            font-size: 15px;
        }
        .custom-progress {
            height: 10px;
            border-radius: 10px;
            background-color: #ebebeb;
            overflow: hidden;
        }
        .custom-progress-bar {
            background: linear-gradient(90deg, #43d477 0%, #38b364 100%);
            border-radius: 10px;
            height: 100%;
        }
        .history-table thead th {
            border-top: none;
            background-color: #fcfcfc;
            color: #1f3b64;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            padding: 18px 15px;
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
        }
        .btn-hover-shadow {
            transition: all 0.3s ease;
        }
        .btn-hover-shadow:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between mb-20">
            <h2 class="section-title mb-0">Purchase Details — #{{ $sale->id }}</h2>
        </div>

        <div class="row mt-10">
            {{-- Sale Info --}}
            <div class="col-12">
                <div class="panel-section-card py-25 px-25">
                    <div class="d-flex align-items-center mb-25">
                        <div class="summary-icon" style="background: rgba(31, 59, 100, 0.1); color: #1f3b64;">
                            <i data-feather="shopping-bag" width="18" height="18"></i>
                        </div>
                        <h3 class="font-18 font-weight-bold text-dark-blue mb-0">Purchase Information</h3>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 mb-20">
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i data-feather="book-open" width="16" height="16" class="mr-10 text-gray"></i>
                                    Course
                                </div>
                                <span class="summary-value">
                                    @if($sale->product)
                                        {{ $sale->product->name }}
                                    @else
                                        Course #{{ $sale->product_id }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-20">
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i data-feather="credit-card" width="16" height="16" class="mr-10 text-gray"></i>
                                    Payment Mode
                                </div>
                                <span class="summary-value">
                                    @if($sale->pricing_mode === 'installment')
                                        EMI / Installment
                                    @else
                                        {{ ucfirst($sale->pricing_mode) }}
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-20">
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i data-feather="dollar-sign" width="16" height="16" class="mr-10 text-gray"></i>
                                    Net Amount
                                </div>
                                <span class="summary-value">
                                    @php
                                        $displayPrice = $sale->installmentPlan
                                            ? $sale->installmentPlan->total_amount
                                            : $ledgerSummary['net_balance'];
                                    @endphp
                                    <span class="text-primary">{{ handlePrice($displayPrice) }}</span>
                                    @if($sale->base_fee_snapshot > $displayPrice)
                                        <span class="font-12 text-gray font-weight-normal ml-5" style="text-decoration: line-through;">{{ handlePrice($sale->base_fee_snapshot) }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-20">
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i data-feather="activity" width="16" height="16" class="mr-10 text-gray"></i>
                                    Access Status
                                </div>
                                <span>
                                    @if($accessResult->hasAccess)
                                        @if($sale->pricing_mode === 'installment' && $sale->installmentPlan)
                                            @php
                                                $hasDueSchedules = $sale->installmentPlan->schedules->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])->count() > 0;
                                            @endphp
                                            @if($hasDueSchedules)
                                                <span class="status-badge badge-primary">Active</span>
                                                <span class="status-badge badge-warning ml-5">EMI Due</span>
                                            @else
                                                <span class="status-badge badge-primary">Fully Paid</span>
                                            @endif
                                        @else
                                            <span class="status-badge badge-primary">Active</span>
                                        @endif
                                    @elseif($sale->status === 'pending_payment')
                                        <span class="status-badge badge-warning">Payment Pending</span>
                                    @elseif($sale->status === 'refunded')
                                        <span class="status-badge badge-danger">Refunded</span>
                                    @else
                                        <span class="status-badge badge-secondary">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-20">
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i data-feather="calendar" width="16" height="16" class="mr-10 text-gray"></i>
                                    Purchased On
                                </div>
                                <span class="summary-value">{{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y') }}</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-20">
                            <div class="summary-item">
                                <div class="summary-label">
                                    <i data-feather="clock" width="16" height="16" class="mr-10 text-gray"></i>
                                    Valid Until
                                </div>
                                <span class="summary-value">
                                    @if($sale->valid_until)
                                        <span class="{{ \Carbon\Carbon::parse($sale->valid_until)->isPast() ? 'text-danger' : '' }}">
                                            {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                        </span>
                                    @else
                                        Lifetime
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($sale->product && in_array($sale->product->product_type, ['course_video', 'webinar', 'course_live']) && $accessResult->hasAccess)
                        @php $webinarForLink = \App\Models\Webinar::find($sale->product->external_id); @endphp
                        @if($webinarForLink && $webinarForLink->slug)
                            <div class="mt-10">
                                <a href="/course/learning/{{ $webinarForLink->slug }}" target="_blank" class="btn btn-primary px-30 btn-hover-shadow" style="border-radius: 12px; font-weight: 600;">
                                    <i data-feather="play-circle" width="18" height="18" class="mr-8"></i> Go to Learning Page
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="row mt-25">
             {{-- Payment Progress / Summary --}}
             <div class="col-12 col-lg-6">
                 @if($sale->installmentPlan)
                    @php
                        $plan = $sale->installmentPlan;
                        $schedules = $plan->schedules->sortBy('sequence');
                        $totalPaidDisplay = $schedules->sum('amount_paid');
                        $totalRemaining = max(0, $plan->total_amount - $totalPaidDisplay);
                        $paidPercent = $plan->total_amount > 0 ? min(100, round(($totalPaidDisplay / $plan->total_amount) * 100)) : 0;
                        $paidSchedules = $schedules->whereIn('status', ['paid', 'waived']);
                    @endphp
                    <div class="panel-section-card py-25 px-25 h-100">
                        <div class="d-flex align-items-center mb-20">
                            <div class="summary-icon">
                                <i data-feather="pie-chart" width="18" height="18"></i>
                            </div>
                            <h3 class="font-18 font-weight-bold text-dark-blue mb-0">EMI Progress</h3>
                        </div>

                        <div class="row mb-25">
                            <div class="col-4">
                                <div class="font-12 text-gray mb-5">Total</div>
                                <div class="font-16 font-weight-bold text-dark-blue">{{ handlePrice($plan->total_amount) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray mb-5">Paid</div>
                                <div class="font-16 font-weight-bold text-primary">{{ handlePrice($totalPaidDisplay) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="font-12 text-gray mb-5">Remaining</div>
                                <div class="font-16 font-weight-bold text-danger">{{ handlePrice($totalRemaining) }}</div>
                            </div>
                        </div>

                        <div class="custom-progress mb-10">
                            <div class="custom-progress-bar" style="width: {{ $paidPercent }}%"></div>
                        </div>
                        <div class="font-13 text-gray">{{ $paidPercent }}% paid — {{ $paidSchedules->count() }} of {{ $schedules->count() }} installments</div>

                        <div class="mt-20">
                            <a href="/panel/upe/installments/{{ $plan->id }}" class="btn btn-sm btn-outline-primary px-20 btn-hover-shadow" style="border-radius: 10px; font-weight: 600;">
                                <i data-feather="list" width="14" class="mr-5"></i> View EMI Schedule
                            </a>
                        </div>
                    </div>
                 @else
                    <div class="panel-section-card py-25 px-25 h-100">
                        <div class="d-flex align-items-center mb-20">
                            <div class="summary-icon">
                                <i data-feather="check-square" width="18" height="18"></i>
                            </div>
                            <h3 class="font-18 font-weight-bold text-dark-blue mb-0">Payment Summary</h3>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-6">
                                <div class="font-12 text-gray mb-5">Amount Paid</div>
                                <div class="font-24 font-weight-bold text-primary">{{ handlePrice($ledgerSummary['net_balance']) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="font-12 text-gray mb-5">List Price</div>
                                <div class="font-18 font-weight-bold text-gray" style="text-decoration: line-through;">{{ handlePrice($sale->base_fee_snapshot) }}</div>
                            </div>
                        </div>
                        @if($sale->base_fee_snapshot > $ledgerSummary['net_balance'])
                            <div class="mt-15 p-10 bg-light-success-faded rounded-lg border border-success border-opacity-10">
                                <i data-feather="gift" width="14" class="text-success mr-5"></i>
                                <span class="font-13 font-weight-600 text-success">You saved {{ handlePrice($sale->base_fee_snapshot - $ledgerSummary['net_balance']) }} with a coupon!</span>
                            </div>
                        @endif
                    </div>
                 @endif
             </div>

             {{-- Subscription info if any --}}
            @if($sale->subscription)
                <div class="col-12 col-lg-6 mt-15 mt-lg-0">
                    <div class="panel-section-card py-25 px-25 h-100">
                        <div class="d-flex align-items-center mb-20">
                            <div class="summary-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i data-feather="repeat" width="18" height="18"></i>
                            </div>
                            <h3 class="font-18 font-weight-bold text-dark-blue mb-0">Subscription</h3>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-15">
                                <div class="font-12 text-gray mb-2">Status</div>
                                <span class="status-badge badge-primary">{{ ucfirst($sale->subscription->status) }}</span>
                            </div>
                            <div class="col-6 mb-15">
                                <div class="font-12 text-gray mb-2">Billing</div>
                                <div class="font-15 font-weight-bold text-dark-blue">{{ handlePrice($sale->subscription->billing_amount) }} / {{ $sale->subscription->billing_interval }}</div>
                            </div>
                            <div class="col-12">
                                <div class="font-12 text-gray mb-2">Current Period</div>
                                <div class="font-14 font-weight-500 text-dark-blue">
                                    {{ \Carbon\Carbon::parse($sale->subscription->current_period_start)->format('d M') }} — {{ \Carbon\Carbon::parse($sale->subscription->current_period_end)->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Payment History --}}
        @if(($sale->installmentPlan && $sale->installmentPlan->schedules->count() > 0) || $sale->ledgerEntries->count() > 0)
            <section class="mt-30">
                <h2 class="section-title">Payment History</h2>
                <div class="panel-section-card mt-15 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table history-table text-center mb-0">
                            <thead>
                                @if($sale->installmentPlan)
                                    <tr>
                                        <th>#</th>
                                        <th>Installment</th>
                                        <th>Amount</th>
                                        <th>Paid</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Ref ID</th>
                                    </tr>
                                @endif
                            </thead>
                            <tbody>
                                @if($sale->installmentPlan)
                                    @foreach($sale->installmentPlan->schedules->sortBy('sequence') as $schedule)
                                        @php
                                            $schedLabel = $schedule->sequence === 1 ? 'Upfront' : 'EMI ' . ($schedule->sequence - 1);
                                            $schedStatusLabel = match($schedule->status) {
                                                'paid' => 'Paid',
                                                'partial' => 'Partial',
                                                'due' => 'Due',
                                                'overdue' => 'Overdue',
                                                'upcoming' => 'Upcoming',
                                                default => ucfirst($schedule->status),
                                            };
                                            $schedBadge = match($schedule->status) {
                                                'paid' => 'badge-primary',
                                                'partial' => 'badge-warning',
                                                'due', 'overdue' => 'badge-danger',
                                                default => 'badge-secondary',
                                            };
                                        @endphp
                                        <tr class="py-15">
                                            <td>{{ $schedule->sequence }}</td>
                                            <td class="font-weight-600 text-dark-blue">{{ $schedLabel }}</td>
                                            <td class="font-weight-600">{{ handlePrice($schedule->amount_due) }}</td>
                                            <td>
                                                @if(($schedule->amount_paid ?? 0) > 0)
                                                    <span class="text-primary font-weight-700">{{ handlePrice($schedule->amount_paid) }}</span>
                                                @else
                                                    <span class="text-gray">-</span>
                                                @endif
                                            </td>
                                            <td><span class="status-badge {{ $schedBadge }}">{{ $schedStatusLabel }}</span></td>
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
                                @else
                                    @foreach($sale->ledgerEntries->where('direction', 'credit')->sortByDesc('id') as $entry)
                                        <tr>
                                            <td class="font-weight-700 text-primary">{{ handlePrice($entry->amount) }}</td>
                                            <td class="font-weight-500">{{ ucfirst($entry->payment_method ?? '-') }}</td>
                                            <td class="font-12">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y H:i') }}</td>
                                            <td class="text-gray font-11">#{{ $entry->id }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        {{-- Action Forms --}}
        @if(in_array($sale->status, ['active', 'partially_refunded']))
            <section class="mt-30">
                <h2 class="section-title">Actions</h2>
                <div class="row mt-15">
                    {{-- Refund Form --}}
                    <div class="col-12 col-lg-6">
                        <div class="panel-section-card py-25 px-25 h-100">
                            <div class="d-flex align-items-center mb-20">
                                <div class="summary-icon" style="background: rgba(246, 59, 59, 0.1); color: #f63b3b;">
                                    <i data-feather="refresh-ccw" width="18" height="18"></i>
                                </div>
                                <h3 class="font-18 font-weight-bold text-dark-blue mb-0">Request Refund</h3>
                            </div>
                            <form method="POST" action="/panel/upe/request-refund">
                                @csrf
                                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                <div class="form-group">
                                    <label class="input-label">Refund Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white border-right-0" style="border-top-left-radius: 12px; border-bottom-left-radius: 12px;"><i data-feather="dollar-sign" width="14"></i></span>
                                        </div>
                                        <input type="number" name="amount" class="form-control border-left-0" step="0.01" min="1" max="{{ $ledgerSummary['net_balance'] }}" value="{{ $ledgerSummary['net_balance'] }}" required style="border-top-right-radius: 12px; border-bottom-right-radius: 12px; background-color: #fff; z-index: 1;">
                                    </div>
                                    <small class="text-gray d-block mt-5">Max refundable: <span class="font-weight-600">{{ handlePrice($ledgerSummary['net_balance']) }}</span></small>
                                </div>
                                <div class="form-group">
                                    <label class="input-label">Reason</label>
                                    <textarea name="reason" class="form-control" rows="3" required placeholder="Describe why you want a refund..." style="border-radius: 12px;"></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-block py-10 btn-hover-shadow" style="border-radius: 12px; font-weight: 600;" onclick="return confirm('Submit refund request? An admin will review it.')">
                                    Submit Refund Request
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Extension / Coupon --}}
                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        @if($sale->valid_until)
                            <div class="panel-section-card py-25 px-25 mb-20">
                                <div class="d-flex align-items-center mb-15">
                                    <div class="summary-icon">
                                        <i data-feather="calendar" width="18" height="18"></i>
                                    </div>
                                    <h3 class="font-16 font-weight-bold text-dark-blue mb-0">Request Extension</h3>
                                </div>
                                <form method="POST" action="/panel/upe/request-extension">
                                    @csrf
                                    <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                    <div class="row align-items-end">
                                        <div class="col-6">
                                            <div class="form-group mb-0">
                                                <label class="input-label font-11">Period</label>
                                                <select name="extension_days" class="form-control" style="height: 42px; border-radius: 10px;">
                                                    <option value="7">7 Days</option>
                                                    <option value="15">15 Days</option>
                                                    <option value="30" selected>30 Days</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn btn-primary btn-block py-10 btn-hover-shadow" style="border-radius: 10px; height: 42px; font-weight: 600;" onclick="return confirm('Submit extension request?')">
                                                Extend
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-10">
                                        <textarea name="reason" class="form-control font-12" rows="2" placeholder="Brief reason for extension..." style="border-radius: 10px;"></textarea>
                                    </div>
                                </form>
                            </div>
                        @endif

                        <div class="panel-section-card py-25 px-25">
                            <div class="d-flex align-items-center mb-15">
                                <div class="summary-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                    <i data-feather="tag" width="18" height="18"></i>
                                </div>
                                <h3 class="font-16 font-weight-bold text-dark-blue mb-0">Apply Coupon</h3>
                            </div>
                            <form method="POST" action="/panel/upe/request-coupon">
                                @csrf
                                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                <div class="d-flex align-items-center">
                                    <input type="text" name="coupon_code" class="form-control mr-10" required placeholder="Enter code" style="height: 42px; border-radius: 10px; text-transform: uppercase;">
                                    <button type="submit" class="btn btn-primary px-20 btn-hover-shadow" style="border-radius: 10px; height: 42px; font-weight: 600;">
                                        Apply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="mt-30 pb-30">
            <a href="/panel/upe/my-purchases" class="btn btn-outline-secondary px-25" style="border-radius: 10px;">
                <i data-feather="arrow-left" width="14" class="mr-5"></i> Back to My Purchases
            </a>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endpush
