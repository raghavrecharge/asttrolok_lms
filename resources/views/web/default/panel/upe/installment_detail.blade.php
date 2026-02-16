@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class="section-title">EMI Plan Details</h2>

        <div class="row mt-20">
            {{-- Plan Info --}}
            <div class="col-12 col-lg-5">
                <div class="panel-section-card py-20 px-25">
                    <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Plan Summary</h3>

                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Product</span>
                        <span class="font-weight-500">
                            @if($plan->sale && $plan->sale->product)
                                {{ $plan->sale->product->name }}
                            @else
                                Sale #{{ $plan->sale_id }}
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Plan Status</span>
                        <span>
                            @php
                                $planColors = [
                                    'active' => 'primary',
                                    'completed' => 'primary',
                                    'defaulted' => 'danger',
                                    'restructured' => 'warning',
                                ];
                                $planStatusClass = 'badge-' . ($planColors[$plan->status] ?? 'secondary');
                            @endphp
                            <span class="badge {{ $planStatusClass }}">{{ ucfirst($plan->status) }}</span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Total Amount</span>
                        <span class="font-weight-bold">{{ handlePrice($plan->total_amount) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Installments</span>
                        <span>{{ $plan->num_installments }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Plan Type</span>
                        <span>{{ ucfirst($plan->plan_type) }}</span>
                    </div>

                    @php
                        $totalPaid = $plan->schedules->sum('amount_paid');
                        $totalRemaining = $plan->total_amount - $totalPaid;
                        $paidPercent = $plan->total_amount > 0 ? round(($totalPaid / $plan->total_amount) * 100) : 0;
                    @endphp

                    <div class="mt-15">
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Paid</span>
                            <span class="text-primary font-weight-bold">{{ handlePrice($totalPaid) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-10">
                            <span class="text-gray">Remaining</span>
                            <span class="text-danger font-weight-bold">{{ handlePrice(max(0, $totalRemaining)) }}</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: {{ $paidPercent }}%"></div>
                        </div>
                        <div class="font-12 text-gray text-center mt-5">{{ $paidPercent }}% paid</div>
                    </div>
                </div>

                {{-- Ledger Summary --}}
                <div class="panel-section-card py-20 px-25 mt-15">
                    <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Ledger Summary</h3>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="font-12 text-gray">Credits</div>
                            <div class="font-16 font-weight-bold text-primary">{{ handlePrice($ledgerSummary['total_credits']) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="font-12 text-gray">Debits</div>
                            <div class="font-16 font-weight-bold text-danger">{{ handlePrice($ledgerSummary['total_debits']) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="font-12 text-gray">Balance</div>
                            <div class="font-16 font-weight-bold">{{ handlePrice($ledgerSummary['net_balance']) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schedule Table --}}
            <div class="col-12 col-lg-7 mt-15 mt-lg-0">
                <div class="panel-section-card py-20 px-25">
                    <h3 class="font-16 font-weight-bold text-dark-blue mb-15">Payment Schedule</h3>

                    <div class="table-responsive">
                        <table class="table text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plan->schedules->sortBy('sequence') as $schedule)
                                    @php
                                        $schedColors = [
                                            'paid' => 'primary',
                                            'due' => 'warning',
                                            'upcoming' => 'info',
                                            'partial' => 'warning',
                                            'overdue' => 'danger',
                                            'waived' => 'secondary',
                                        ];
                                        $schedStatusClass = 'badge-' . ($schedColors[$schedule->status] ?? 'secondary');
                                    @endphp
                                    <tr class="{{ $schedule->status === 'overdue' ? 'bg-light' : '' }}">
                                        <td class="font-weight-500">{{ $schedule->sequence ?? $schedule->installment_number }}</td>
                                        <td>
                                            @if($schedule->due_date)
                                                {{ \Carbon\Carbon::parse($schedule->due_date)->format('d M Y') }}
                                                @if($schedule->status === 'overdue')
                                                    <div class="font-10 text-danger">{{ \Carbon\Carbon::parse($schedule->due_date)->diffForHumans() }}</div>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ handlePrice($schedule->amount_due) }}</td>
                                        <td>
                                            @if(($schedule->amount_paid ?? 0) > 0)
                                                <span class="text-primary">{{ handlePrice($schedule->amount_paid) }}</span>
                                            @else
                                                <span class="text-gray">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $schedStatusClass }} px-10 py-5">{{ ucfirst($schedule->status) }}</span>
                                            @if($schedule->paid_at)
                                                <div class="font-10 text-gray">{{ \Carbon\Carbon::parse($schedule->paid_at)->format('d M Y') }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Restructure Request --}}
    @if($plan->status === 'active')
        @php
            $overdueCount = $plan->schedules->where('status', 'overdue')->count();
            $remainingCount = $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->count();
        @endphp
        @if($remainingCount > 0)
            <section class="mt-25">
                <h2 class="section-title">Request Restructure</h2>
                <div class="panel-section-card py-20 px-25 mt-20">
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <p class="font-14 text-gray mb-15">
                                If you're having difficulty with your current EMI schedule, you can request a restructure.
                                @if($overdueCount > 0)
                                    <span class="text-danger font-weight-500">You have {{ $overdueCount }} overdue payment(s).</span>
                                @endif
                                Support will review your request and propose a revised schedule.
                            </p>
                            <form method="POST" action="/panel/upe/request-restructure">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <div class="form-group">
                                    <label class="input-label">Reason for Restructure</label>
                                    <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why you need your EMI schedule restructured..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Submit restructure request? Support will review your EMI plan.')">
                                    Request EMI Restructure
                                </button>
                            </form>
                        </div>
                        <div class="col-12 col-lg-4 mt-15 mt-lg-0">
                            <div class="p-15 rounded bg-light">
                                <div class="font-12 text-gray mb-10">Current Plan Status</div>
                                <div class="d-flex justify-content-between mb-5">
                                    <span class="font-12">Remaining EMIs</span>
                                    <span class="font-weight-500">{{ $remainingCount }}</span>
                                </div>
                                @if($overdueCount > 0)
                                    <div class="d-flex justify-content-between mb-5">
                                        <span class="font-12 text-danger">Overdue</span>
                                        <span class="font-weight-500 text-danger">{{ $overdueCount }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <span class="font-12">Remaining Amount</span>
                                    <span class="font-weight-500">{{ handlePrice($plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->sum('amount_due') - $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->sum('amount_paid')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    @endif

    <div class="mt-20">
        <a href="/panel/upe/installments" class="btn btn-sm btn-secondary"><i data-feather="arrow-left" width="14" height="14"></i> Back to EMI Plans</a>
        <a href="/panel/upe/purchases/{{ $plan->sale_id }}" class="btn btn-sm btn-primary ml-10">View Purchase</a>
    </div>
@endsection
