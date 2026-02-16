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
                                $planStatusClass = match($plan->status) {
                                    'active' => 'badge-primary',
                                    'completed' => 'badge-primary',
                                    'defaulted' => 'badge-danger',
                                    'restructured' => 'badge-warning',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $planStatusClass }}">{{ ucfirst($plan->status) }}</span>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Total Amount</span>
                        <span class="font-weight-bold">₹{{ number_format($plan->total_amount, 2) }}</span>
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
                        $totalPaid = $plan->schedules->where('status', 'paid')->sum('amount_due');
                        $totalRemaining = $plan->total_amount - $totalPaid;
                        $paidPercent = $plan->total_amount > 0 ? round(($totalPaid / $plan->total_amount) * 100) : 0;
                    @endphp

                    <div class="mt-15">
                        <div class="d-flex justify-content-between mb-5">
                            <span class="text-gray">Paid</span>
                            <span class="text-primary font-weight-bold">₹{{ number_format($totalPaid, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-10">
                            <span class="text-gray">Remaining</span>
                            <span class="text-danger font-weight-bold">₹{{ number_format(max(0, $totalRemaining), 2) }}</span>
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
                            <div class="font-16 font-weight-bold text-primary">₹{{ number_format($ledgerSummary['total_credits'], 2) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="font-12 text-gray">Debits</div>
                            <div class="font-16 font-weight-bold text-danger">₹{{ number_format($ledgerSummary['total_debits'], 2) }}</div>
                        </div>
                        <div class="col-4">
                            <div class="font-12 text-gray">Balance</div>
                            <div class="font-16 font-weight-bold">₹{{ number_format($ledgerSummary['net_balance'], 2) }}</div>
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
                                        $schedStatusClass = match($schedule->status) {
                                            'paid' => 'badge-primary',
                                            'due' => 'badge-warning',
                                            'upcoming' => 'badge-info',
                                            'partial' => 'badge-warning',
                                            'overdue' => 'badge-danger',
                                            'waived' => 'badge-secondary',
                                            default => 'badge-secondary',
                                        };
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
                                        <td>₹{{ number_format($schedule->amount_due, 2) }}</td>
                                        <td>
                                            @if(($schedule->amount_paid ?? 0) > 0)
                                                <span class="text-primary">₹{{ number_format($schedule->amount_paid, 2) }}</span>
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

    <div class="mt-20">
        <a href="/panel/upe/installments" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back to EMI Plans</a>
        <a href="/panel/upe/purchases/{{ $plan->sale_id }}" class="btn btn-sm btn-primary ml-10">View Purchase</a>
    </div>
@endsection
