@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class="section-title">My EMI Plans</h2>

        <div class="row mt-20">
            @forelse($sales as $sale)
                @if($sale->installmentPlan)
                    <div class="col-12 col-lg-6 mt-15">
                        <div class="panel-section-card py-20 px-25">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h3 class="font-16 font-weight-bold text-dark-blue">
                                        @if($sale->product)
                                            {{ $sale->product->name }}
                                        @else
                                            Product #{{ $sale->product_id }}
                                        @endif
                                    </h3>
                                    <span class="font-12 text-gray">Sale #{{ $sale->id }}</span>
                                </div>
                                <div>
                                    @php
                                        $planStatusClass = match($sale->installmentPlan->status) {
                                            'active' => 'badge-primary',
                                            'completed' => 'badge-primary',
                                            'defaulted' => 'badge-danger',
                                            'restructured' => 'badge-warning',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $planStatusClass }} px-10 py-5">{{ ucfirst($sale->installmentPlan->status) }}</span>
                                </div>
                            </div>

                            <div class="mt-15">
                                <div class="d-flex justify-content-between mb-5">
                                    <span class="text-gray font-14">Total Amount</span>
                                    <span class="font-weight-500">₹{{ number_format($sale->installmentPlan->total_amount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-5">
                                    <span class="text-gray font-14">Installments</span>
                                    <span class="font-weight-500">{{ $sale->installmentPlan->num_installments }}</span>
                                </div>

                                @php
                                    $paidCount = $sale->installmentPlan->schedules->where('status', 'paid')->count();
                                    $totalCount = $sale->installmentPlan->schedules->count();
                                    $paidPercent = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0;
                                    $overdueCount = $sale->installmentPlan->schedules->where('status', 'overdue')->count();
                                @endphp

                                <div class="progress mt-10" style="height: 8px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $paidPercent }}%"></div>
                                </div>
                                <div class="font-12 text-gray mt-5">
                                    {{ $paidCount }}/{{ $totalCount }} paid ({{ $paidPercent }}%)
                                    @if($overdueCount > 0)
                                        <span class="text-danger ml-10">{{ $overdueCount }} overdue</span>
                                    @endif
                                </div>
                            </div>

                            @php
                                $nextDue = $sale->installmentPlan->schedules
                                    ->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])
                                    ->sortBy('sequence')
                                    ->first();
                            @endphp

                            @if($nextDue)
                                <div class="mt-15 p-10 rounded bg-light">
                                    <div class="font-12 text-gray">Next Payment Due</div>
                                    <div class="d-flex justify-content-between mt-5">
                                        <span class="font-weight-500">
                                            EMI #{{ $nextDue->sequence ?? $nextDue->installment_number }}
                                            — ₹{{ number_format($nextDue->amount_due - ($nextDue->amount_paid ?? 0), 2) }}
                                        </span>
                                        <span class="font-12 {{ $nextDue->status === 'overdue' ? 'text-danger font-weight-bold' : 'text-gray' }}">
                                            {{ $nextDue->due_date ? \Carbon\Carbon::parse($nextDue->due_date)->format('d M Y') : '-' }}
                                            @if($nextDue->status === 'overdue')
                                                (OVERDUE)
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <a href="/panel/upe/installments/{{ $sale->installmentPlan->id }}" class="btn btn-sm btn-primary mt-15">View Full Schedule</a>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-12 mt-15">
                    <div class="panel-section-card py-40 px-25 text-center">
                        <p class="text-gray font-16">No EMI plans found.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endsection
