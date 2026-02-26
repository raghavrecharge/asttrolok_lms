@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .panel-section-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f1f1;
            transition: all 0.3s ease;
        }
        .panel-section-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(67, 212, 119, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #43d477;
        }
        .summary-label {
            display: flex;
            align-items: center;
            color: #6c757d;
            font-size: 14px;
        }
        .summary-value {
            font-weight: 600;
            color: #1f3b64;
        }
        .custom-progress {
            height: 12px;
            border-radius: 10px;
            background-color: #f1f1f1;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }
        .custom-progress-bar {
            background: linear-gradient(90deg, #43d477 0%, #38b364 100%);
            box-shadow: 0 2px 4px rgba(67, 212, 119, 0.3);
            border-radius: 10px;
            height: 100%;
            transition: width 0.6s ease;
        }
        .schedule-table thead th {
            border-top: none;
            background-color: #f8f9fa;
            color: #1f3b64;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        .schedule-table tbody tr {
            transition: background-color 0.2s;
        }
        .schedule-table tbody tr:hover {
            background-color: #fcfcfc !important;
        }
        .schedule-table td {
            vertical-align: middle;
            padding: 15px 10px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }
        .restructure-section {
            background: linear-gradient(135deg, #fff 0%, #fff9f0 100%);
            border-left: 4px solid #ff9800;
        }
        .btn-hover-shadow:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between mb-20">
            <h2 class="section-title mb-0">EMI Plan Details</h2>
            <div class="d-flex align-items-center">
                <span class="badge badge-primary px-15 py-5 font-12">{{ ucfirst($plan->status) }}</span>
            </div>
        </div>

        <div class="row mt-20">
            {{-- Plan Info --}}
            <div class="col-12 col-lg-5">
                <div class="panel-section-card py-25 px-25">
                    <div class="d-flex align-items-center mb-20">
                        <div class="summary-icon">
                            <i data-feather="info" width="18" height="18"></i>
                        </div>
                        <h3 class="font-18 font-weight-bold text-dark-blue mb-0">Plan Summary</h3>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">
                            <i data-feather="package" width="16" height="16" class="mr-10 text-gray"></i>
                            Product
                        </div>
                        <span class="summary-value">
                            @if($plan->sale && $plan->sale->product)
                                {{ $plan->sale->product->name }}
                            @else
                                Sale #{{ $plan->sale_id }}
                            @endif
                        </span>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">
                            <i data-feather="activity" width="16" height="16" class="mr-10 text-gray"></i>
                            Plan Status
                        </div>
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
                            <span class="status-badge {{ $planStatusClass }}">{{ ucfirst($plan->status) }}</span>
                        </span>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">
                            <i data-feather="dollar-sign" width="16" height="16" class="mr-10 text-gray"></i>
                            Total Amount
                        </div>
                        <span class="summary-value">
                            <span class="text-primary">{{ handlePrice($plan->total_amount) }}</span>
                            @if($plan->sale && $plan->sale->base_fee_snapshot > $plan->total_amount)
                                <span class="font-12 text-gray font-weight-normal ml-5" style="text-decoration: line-through;">{{ handlePrice($plan->sale->base_fee_snapshot) }}</span>
                            @endif
                        </span>
                    </div>

                    @if($plan->sale && $plan->sale->base_fee_snapshot > $plan->total_amount)
                        <div class="summary-item">
                            <div class="summary-label">
                                <i data-feather="tag" width="16" height="16" class="mr-10 text-gray"></i>
                                Discount
                            </div>
                            <span>
                                <span class="badge badge-warning">Coupon Applied</span>
                                <span class="font-weight-600 text-primary ml-5">{{ handlePrice($plan->sale->base_fee_snapshot - $plan->total_amount) }} saved</span>
                            </span>
                        </div>
                    @endif

                    <div class="summary-item">
                        <div class="summary-label">
                            <i data-feather="layers" width="16" height="16" class="mr-10 text-gray"></i>
                            Installments
                        </div>
                        <span class="summary-value">{{ $plan->num_installments }} Payments</span>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">
                            <i data-feather="calendar" width="16" height="16" class="mr-10 text-gray"></i>
                            Plan Type
                        </div>
                        <span class="summary-value">{{ ucfirst($plan->plan_type) }}</span>
                    </div>

                    @php
                        $activeSchedules = $plan->schedules->whereNotIn('status', ['waived']);
                        $totalPaid = $activeSchedules->sum('amount_paid');
                        $totalDue = $activeSchedules->sum('amount_due');
                        $totalRemaining = max(0, $totalDue - $totalPaid);
                        $paidPercent = $totalDue > 0 ? round(($totalPaid / $totalDue) * 100) : 0;
                    @endphp

                    <div class="mt-25 p-15 rounded-lg bg-light">
                        <div class="d-flex justify-content-between mb-8">
                            <span class="text-gray font-14">Repayment Progress</span>
                            <span class="text-dark-blue font-weight-bold">{{ $paidPercent }}%</span>
                        </div>
                        <div class="custom-progress mb-15">
                            <div class="custom-progress-bar" style="width: {{ $paidPercent }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="font-12 text-gray mb-2">Paid</div>
                                <div class="text-primary font-16 font-weight-bold">{{ handlePrice($totalPaid) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-12 text-gray mb-2">Remaining</div>
                                <div class="text-danger font-16 font-weight-bold">{{ handlePrice(max(0, $totalRemaining)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schedule Table --}}
            <div class="col-12 col-lg-7 mt-15 mt-lg-0">
                <div class="panel-section-card py-25 px-25">
                    <div class="d-flex align-items-center mb-20">
                        <div class="summary-icon" style="background: rgba(31, 59, 100, 0.1); color: #1f3b64;">
                            <i data-feather="calendar" width="18" height="18"></i>
                        </div>
                        <h3 class="font-18 font-weight-bold text-dark-blue mb-0">Payment Schedule</h3>
                    </div>

                    <div class="table-responsive">
                        <table class="table schedule-table">
                            <thead>
                                <tr>
                                    <th class="text-left">#</th>
                                    <th class="text-left">Due Date</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $displayIndex = 0; @endphp
                                @foreach($plan->schedules->where('status', '!=', 'waived')->sortBy(['due_date', 'sequence']) as $schedule)
                                    @php
                                        $displayIndex++;
                                        $schedColors = [
                                            'paid' => 'primary',
                                            'due' => 'warning',
                                            'upcoming' => 'info',
                                            'partial' => 'warning',
                                            'overdue' => 'danger',
                                            'waived' => 'secondary',
                                        ];
                                        $schedStatusClass = 'badge-' . ($schedColors[$schedule->status] ?? 'secondary');
                                        $emiLabel = $displayIndex === 1 ? 'Upfront' : 'EMI ' . ($displayIndex - 1);
                                    @endphp
<<<<<<< HEAD
                                    <tr class="{{ $schedule->status === 'overdue' ? 'bg-light-danger' : '' }}">
                                        <td class="font-weight-bold text-dark-blue">{{ $schedule->sequence ?? $schedule->installment_number }}</td>
                                        <td class="text-left">
=======
                                    <tr class="{{ $schedule->status === 'overdue' ? 'bg-light' : '' }}">
                                        <td class="font-weight-500">{{ $emiLabel }}</td>
                                        <td>
>>>>>>> 0e7d69c168339003315a5a94c23904236bf15530
                                            @if($schedule->due_date)
                                                <div class="font-weight-500">{{ \Carbon\Carbon::parse($schedule->due_date)->format('d M Y') }}</div>
                                                @if($schedule->status === 'overdue')
                                                    <div class="font-10 text-danger mt-2"><i data-feather="alert-circle" width="10" height="10"></i> {{ \Carbon\Carbon::parse($schedule->due_date)->diffForHumans() }}</div>
                                                @endif
                                            @else
                                                <span class="text-gray">-</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold text-dark-blue">{{ handlePrice($schedule->amount_due) }}</td>
                                        <td>
                                            @if(($schedule->amount_paid ?? 0) > 0)
                                                <span class="text-primary font-weight-bold">{{ handlePrice($schedule->amount_paid) }}</span>
                                            @else
                                                <span class="text-gray">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="status-badge {{ $schedStatusClass }}">{{ ucfirst($schedule->status) }}</span>
                                            @if($schedule->paid_at)
                                                <div class="font-10 text-gray mt-2">{{ \Carbon\Carbon::parse($schedule->paid_at)->format('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($nextUnpaid && $schedule->id === $nextUnpaid->id && $payUrl)
                                                <a href="{{ $payUrl }}" class="btn btn-sm btn-primary px-15 btn-hover-shadow">Pay Now</a>
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
            <section class="mt-30">
                <div class="d-flex align-items-center mb-15">
                    <h2 class="section-title mb-0">Request Plan Restructure</h2>
                </div>
                <div class="panel-section-card restructure-section py-25 px-25">

                    {{-- Show existing request status if one exists --}}
                    @if(isset($existingRestructureRequest) && $existingRestructureRequest)
                        <div class="alert bg-white border shadow-sm mb-15 p-20" style="border-radius: 12px; border-left: 5px solid {{ $existingRestructureRequest->status === 'pending' ? '#ff9800' : '#43d477' }} !important;">
                            <div class="d-flex align-items-center mb-10">
                                <i data-feather="clock" class="text-warning mr-10" width="20"></i>
                                <h6 class="mb-0 font-16 font-weight-bold">Restructure Request #{{ $existingRestructureRequest->id }}</h6>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-5"><span class="text-gray">Status:</span> <span class="badge badge-{{ $existingRestructureRequest->status === 'pending' ? 'warning' : 'primary' }}">{{ ucfirst($existingRestructureRequest->status) }}</span></div>
                                    <div><span class="text-gray">Submitted:</span> <span class="font-weight-500">{{ $existingRestructureRequest->created_at->format('d M Y H:i') }}</span></div>
                                </div>
                                <div class="col-md-6">
                                    @if(isset($existingRestructureRequest->payload['schedule_sequence']))
                                        <div class="mb-5"><span class="text-gray">Target EMI:</span> <span class="font-weight-500">#{{ $existingRestructureRequest->payload['schedule_sequence'] }} ({{ $existingRestructureRequest->payload['is_upfront'] ? 'Upfront' : 'Regular' }})</span></div>
                                        <div><span class="text-gray">Amount:</span> <span class="font-weight-600 text-primary">{{ handlePrice($existingRestructureRequest->payload['schedule_remaining'] ?? $existingRestructureRequest->payload['schedule_amount']) }}</span></div>
                                    @endif
                                </div>
                            </div>
                            @if(isset($existingRestructureRequest->payload['reason']))
                                <div class="mt-15 p-10 bg-light rounded font-italic">"{{ $existingRestructureRequest->payload['reason'] }}"</div>
                            @endif
                            <p class="mt-15 mb-0 font-12 text-gray border-top pt-10">Your request is being reviewed by support. You will be notified once a decision is made.</p>
                        </div>
                    @else
                        {{-- Show the restructure form --}}
                        @if(isset($lastRejectedRestructure) && $lastRejectedRestructure)
                            <div class="alert alert-danger mb-20 d-flex align-items-start" style="border-radius: 10px;">
                                <i data-feather="x-circle" class="mr-15 mt-5" width="24"></i>
                                <div>
                                    <h6 class="mb-5 font-weight-bold">Previous Request Rejected</h6>
                                    <div class="font-14"><strong>Reason:</strong> {{ $lastRejectedRestructure->rejected_reason ?? 'No reason provided' }}</div>
                                    <small class="opacity-70 mt-5 d-block">Rejected on {{ $lastRejectedRestructure->updated_at->format('d M Y H:i') }}. You may submit a new request below.</small>
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <p class="font-15 text-dark-blue mb-20">
                                    If you're having difficulty with your current EMI schedule, you can request a restructure. 
                                    @if($overdueCount > 0)
                                        <span class="text-danger font-weight-bold">You have {{ $overdueCount }} overdue payment(s).</span>
                                    @endif
                                    Our support team will review your request and propose a revised schedule to help you stay on track.
                                </p>

                                @if(isset($restructureTarget) && $restructureTarget)
                                    <div class="p-15 border border-primary bg-light-primary mb-20" style="border-radius: 10px; border-style: dashed !important;">
                                        <div class="d-flex align-items-center text-primary font-weight-bold mb-5">
                                            <i data-feather="target" width="16" class="mr-8"></i>
                                            Installment to restructure
                                        </div>
                                        <div class="font-14 text-dark-blue">
                                            EMI #{{ $restructureTarget->sequence }} 
                                            ({{ $restructureTarget->sequence <= 1 ? 'Upfront' : 'Step ' . $restructureTarget->sequence }}) 
                                            &mdash; <span class="font-weight-bold text-primary">{{ handlePrice($restructureTarget->remainingAmount()) }}</span>
                                            @if($restructureTarget->due_date)
                                                due <span class="font-weight-500">{{ \Carbon\Carbon::parse($restructureTarget->due_date)->format('d M Y') }}</span>
                                            @endif
                                        </div>
                                        <div class="font-11 text-gray mt-5">{{ $restructureTarget->sequence <= 1 ? 'Since your upfront payment is pending, only the upfront installment can be restructured.' : 'The first unpaid installment will be restructured.' }}</div>
                                    </div>
                                @endif

                                <form method="POST" action="/panel/upe/request-restructure">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <div class="form-group mb-20">
                                        <label class="input-label font-weight-600">Reason for Restructure</label>
                                        <textarea name="reason" class="form-control" rows="4" required placeholder="Please explain your situation and why you need your EMI schedule restructured..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning px-30 py-10 font-16 btn-hover-shadow" onclick="return confirm('Submit restructure request? Support will review your EMI plan.')">
                                        Request EMI Restructure
                                    </button>
                                </form>
                            </div>
                            <div class="col-12 col-lg-4 mt-20 mt-lg-0">
                                <div class="p-20 rounded-lg bg-white border">
                                    <div class="font-14 font-weight-bold text-dark-blue mb-15 pb-10 border-bottom">Current Status Overview</div>
                                    <div class="d-flex justify-content-between mb-12">
                                        <span class="text-gray">Remaining EMIs</span>
                                        <span class="font-weight-bold text-dark-blue">{{ $remainingCount }}</span>
                                    </div>
                                    @if($overdueCount > 0)
                                        <div class="d-flex justify-content-between mb-12">
                                            <span class="text-danger font-weight-500">Overdue Payments</span>
                                            <span class="font-weight-bold text-danger">{{ $overdueCount }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between pt-10 border-top mt-10">
                                        <span class="text-gray font-weight-bold">Total Payable</span>
                                        <span class="font-16 font-weight-bold text-primary">{{ handlePrice($plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->sum('amount_due') - $plan->schedules->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])->sum('amount_paid')) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif

<<<<<<< HEAD
    <div class="mt-30 d-flex flex-column flex-md-row">
        <a href="/panel/upe/installments" class="btn btn-sm btn-secondary d-flex align-items-center justify-content-center mb-10 mb-md-0 px-20 py-10"><i data-feather="arrow-left" width="16" height="16" class="mr-8"></i> Back to EMI Plans</a>
        <a href="/panel/upe/purchases/{{ $plan->sale_id }}" class="btn btn-sm btn-primary ml-md-15 d-flex align-items-center justify-content-center px-20 py-10"><i data-feather="shopping-bag" width="16" height="16" class="mr-8"></i> View Purchase Details</a>
=======
    <div class="mt-20 d-flex">
        <a href="/panel/upe/installments" class="btn btn-sm btn-secondary"><i data-feather="arrow-left" width="14" height="14"></i> Back to EMI Plans</a>
        <a href="/panel/upe/purchases/{{ $plan->sale_id }}" class="btn btn-sm btn-primary ml-10">View Purchase</a>
        @if($plan->sale && $plan->sale->product && in_array($plan->sale->product->product_type, ['course_video', 'webinar', 'course_live']) && in_array($plan->sale->status, ['active', 'completed']))
            @php $webinarForLink = \App\Models\Webinar::find($plan->sale->product->external_id); @endphp
            @if($webinarForLink && $webinarForLink->slug)
                <a href="/course/learning/{{ $webinarForLink->slug }}" target="_blank" class="btn btn-sm btn-success ml-10">
                    <i class="fa fa-play-circle"></i> Learning Page
                </a>
            @endif
        @endif
>>>>>>> 0e7d69c168339003315a5a94c23904236bf15530
    </div>
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
