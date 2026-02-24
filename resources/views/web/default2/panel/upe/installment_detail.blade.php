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
                        <span class="font-weight-bold">
                            <span class="text-primary">₹{{ number_format($plan->total_amount, 2) }}</span>
                            @if($plan->sale && $plan->sale->base_fee_snapshot > $plan->total_amount)
                                <span class="font-12 text-gray font-weight-normal ml-5" style="text-decoration: line-through;">₹{{ number_format($plan->sale->base_fee_snapshot, 2) }}</span>
                            @endif
                        </span>
                    </div>
                    @if($plan->sale && $plan->sale->base_fee_snapshot > $plan->total_amount)
                        <div class="d-flex justify-content-between py-5 border-bottom">
                            <span class="text-gray">Discount</span>
                            <span>
                                <span class="badge badge-warning">Coupon Applied</span>
                                <span class="font-weight-500 text-primary ml-5">₹{{ number_format($plan->sale->base_fee_snapshot - $plan->total_amount, 2) }} saved</span>
                            </span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Installments</span>
                        <span>{{ $plan->num_installments }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-5 border-bottom">
                        <span class="text-gray">Plan Type</span>
                        <span>{{ ucfirst($plan->plan_type) }}</span>
                    </div>

                    @php
                        $totalPaid = $plan->schedules->whereNotIn('status', ['waived'])->sum('amount_paid');
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
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plan->schedules->where('status', '!=', 'waived')->sortBy('sequence') as $schedule)
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
                                        <td>
                                            @if($nextUnpaid && $schedule->id === $nextUnpaid->id && $payUrl)
                                                <a href="{{ $payUrl }}" class="btn btn-sm btn-primary">Pay Now</a>
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

                    @if(isset($existingRestructureRequest) && $existingRestructureRequest)
                        <div class="alert alert-{{ $existingRestructureRequest->status === 'pending' ? 'warning' : ($existingRestructureRequest->status === 'approved' ? 'info' : 'secondary') }} mb-15">
                            <h6 class="mb-5"><i class="fa fa-clock"></i> Restructure Request #{{ $existingRestructureRequest->id }}</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Status:</strong>
                                    <span class="badge badge-{{ $existingRestructureRequest->status === 'pending' ? 'warning' : ($existingRestructureRequest->status === 'approved' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($existingRestructureRequest->status) }}
                                    </span><br>
                                    <strong>Submitted:</strong> {{ $existingRestructureRequest->created_at->format('d M Y H:i') }}
                                </div>
                                <div class="col-md-6">
                                    @if(isset($existingRestructureRequest->payload['schedule_sequence']))
                                        <strong>Target EMI:</strong> #{{ $existingRestructureRequest->payload['schedule_sequence'] }}
                                        ({{ $existingRestructureRequest->payload['is_upfront'] ? 'Upfront' : 'Regular' }})<br>
                                        <strong>Amount:</strong> ₹{{ number_format($existingRestructureRequest->payload['schedule_remaining'] ?? $existingRestructureRequest->payload['schedule_amount'], 2) }}
                                    @endif
                                </div>
                            </div>
                            @if(isset($existingRestructureRequest->payload['reason']))
                                <div class="mt-5"><strong>Reason:</strong> {{ $existingRestructureRequest->payload['reason'] }}</div>
                            @endif
                            <p class="mt-10 mb-0 font-12 text-gray">Your request is being reviewed by support. You will be notified once a decision is made.</p>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <p class="font-14 text-gray mb-15">
                                    If you're having difficulty with your current EMI schedule, you can request a restructure.
                                    @if($overdueCount > 0)
                                        <span class="text-danger font-weight-500">You have {{ $overdueCount }} overdue payment(s).</span>
                                    @endif
                                    Support will review your request and propose a revised schedule.
                                </p>

                                @if(isset($restructureTarget) && $restructureTarget)
                                    <div class="alert alert-info font-13 mb-15">
                                        <strong>Installment to restructure:</strong>
                                        EMI #{{ $restructureTarget->sequence }}
                                        ({{ $restructureTarget->sequence <= 1 ? 'Upfront' : 'Step ' . $restructureTarget->sequence }})
                                        &mdash; ₹{{ number_format($restructureTarget->remainingAmount(), 2) }}
                                        @if($restructureTarget->due_date)
                                            due {{ \Carbon\Carbon::parse($restructureTarget->due_date)->format('d M Y') }}
                                        @endif
                                        <br><small class="text-gray">{{ $restructureTarget->sequence <= 1 ? 'Since your upfront payment is pending, only the upfront installment can be restructured.' : 'The first unpaid installment will be restructured.' }}</small>
                                    </div>
                                @endif

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
                                        <span class="font-weight-500">₹{{ number_format($plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->sum('amount_due') - $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->sum('amount_paid'), 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif

    <div class="mt-20">
        <a href="/panel/upe/installments" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back to EMI Plans</a>
        <a href="/panel/upe/purchases/{{ $plan->sale_id }}" class="btn btn-sm btn-primary ml-10">View Purchase</a>
    </div>
@endsection
