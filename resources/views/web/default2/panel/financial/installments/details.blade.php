@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')

    @if(!empty($overdueInstallments) and count($overdueInstallments))
        <div class="d-flex align-items-center mb-20 p-15 danger-transparent-alert">
            <div class="danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                <i data-feather="credit-card" width="18" height="18" class=""></i>
            </div>
            <div class="ml-10">
                <div class="font-14 font-weight-bold ">{{ trans('update.overdue_installments') }}</div>
                <div class="font-12 ">{{ trans('update.you_have_count_overdue_installments_please_pay_them_to_avoid_restrictions_and_negative_effects_on_your_account',['count' => count($overdueInstallments)]) }}</div>
            </div>
        </div>
    @endif

    <section>
        <h2 class="section-title">{{ trans('update.installments_overview') }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/127.png" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $totalParts }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.total_parts') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/38.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $remainedParts }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.remained_parts') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/33.png" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ handlePrice($remainedAmount) }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.remained_amount') }}</span>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0 d-flex align-items-center justify-content-center mt-5 mt-md-0">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/128.png" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ handlePrice($overdueAmount) }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('update.overdue_amount') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('update.installments_list') }}</h2>
        </div>

        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row">
                <div class="col-12 ">
                    <div class="table-responsive">
                        <table class="table text-center custom-table">
                            <thead>
                            <tr>
                                <th>{{ trans('public.title') }}</th>
                                <th class="text-center">{{ trans('panel.amount') }}</th>
                                <th class="text-center">{{ trans('update.due_date') }}</th>
                                <th class="text-center">{{ trans('update.payment_date') }}</th>
                                <th class="text-center">{{ trans('public.status') }}</th>
                                <th class=""></th>
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                    $count = 0;
                                    $count11 = 0;
                                @endphp

                            @if(!empty($installment->upfront))
                                @php
                                    $upfrontPayment = $payments->where('type','upfront')->first();
                                @endphp
                                <tr>
                                    <td class="text-left">
                                        {{ trans('update.upfront') }}
                                        @if($installment->upfront_type == 'percent')
                                            <span class="ml-5">({{ $installment->upfront }}%)</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($upfrontPayment->status == "paid")
                                            {{ handlePrice($installment->getUpfront($itemPrice)) }}
                                            @php
                                                $paidAmount = $paidAmount - $installment->getUpfront($itemPrice);
                                            @endphp
                                        @else
                                            {{ handlePrice($installment->getUpfront($itemPrice)) }} - {{ $paidAmount }}
                                            @php
                                                $count11 = 1;
                                            @endphp
                                        @endif
                                    </td>

                                    <td class="text-center">-</td>

                                    <td class="text-center">{{ !empty($upfrontPayment) ? dateTimeFormat($upfrontPayment->created_at, 'j M Y H:i') : '-' }}</td>

                                    <td class="text-center">
                                        @if($upfrontPayment->status == "paid")
                                            <span class="text-primary">{{ trans('public.paid') }}</span>
                                            @php
                                                $paidAmount;
                                            @endphp
                                        @else
                                            <span class="text-dark-blue">{{ trans('update.unpaid') }}</span>
                                            @if($count==0) @php $count=1; @endphp@endif
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($count11==1)
                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>

                                                <div class="dropdown-menu menu-lg">
                                                    <a href="/register-course/{{$webinar_title}}" target="_blank"
                                                       class="webinar-actions d-block mt-10 font-weight-normal">Pay Upcomming Part</a>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            @foreach($installment->steps as $step)
                                @php
                                    $stepPayment = $payments->where('step_id', $step->id)->where('status', 'paid')->first();
                                    $dueAt = ($step->deadline * 86400) + $order->created_at;
                                    $isOverdue = ($dueAt < time() and empty($stepPayment));
                                    $stepPrice = $step->getPrice($itemPrice);
                                    
                                    
                                    // Check if approved sub-steps exist for this step
                                    $approvedSubStepsCount = \App\Models\SubStepInstallment::where('installment_step_id', $step->id)
                                        ->where('user_id', auth()->id())
                                        ->where('status', 'approved')
                                        ->count();
                                    
                                    $hasApprovedSubSteps = $approvedSubStepsCount > 0;
                                @endphp

                                {{-- Main Step Row --}}
                                <tr class="step-main-row {{ empty($stepPayment) && $hasApprovedSubSteps ? 'expandable-step' : '' }}" data-step-id="{{ $step->id }}">
                                    <td class="text-left">
                                        <div class="d-flex align-items-center">
                                            @if(empty($stepPayment) && $hasApprovedSubSteps)
                                                <span class="chevron-icon mr-2" style="display: inline-block; transition: transform 0.3s;">▶</span>
                                            @endif
                                            <div>
                                                <div class="d-block font-16 font-weight-500 text-dark-blue">
                                                    {{ $step->title }}
                                                    @if($step->amount_type == 'percent')
                                                        <span class="ml-5 font-12 text-gray">({{ $step->amount }}%)</span>
                                                    @endif
                                                </div>
                                                <span class="d-block font-12 text-gray">{{ trans('update.n_days_after_purchase', ['days' => $step->deadline]) }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        @if(!empty($stepPayment))
                                            {{ handlePrice($stepPrice) }}
                                            @php
                                                $paidAmount = $paidAmount - $stepPrice;
                                            @endphp
                                        @else
                                            @if($paidAmount==0)
                                                {{ handlePrice($stepPrice) }} @if($count==0) @php $count=1; @endphp@endif
                                            @else
                                                {{ handlePrice($stepPrice) }} @if($count==0)<span class="text-primary">- {{ handlePrice($paidAmount) }}</span> @php $count=1; @endphp@endif
                                            @endif
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <span class="{{ $isOverdue ? 'text-danger' : '' }}">{{ dateTimeFormat($dueAt, 'j M Y') }}</span>
                                    </td>

                                    <td class="text-center">{{ !empty($stepPayment) ? dateTimeFormat($stepPayment->created_at, 'j M Y H:i') : '-' }}</td>

                                    <td class="text-center">
                                        @if(!empty($stepPayment))
                                            <span class="text-primary">{{ trans('public.paid') }}</span>
                                            @php
                                                $paidAmount;
                                            @endphp
                                        @else
                                            <span class="{{ $isOverdue ? 'text-danger' : 'text-dark-blue' }}">{{ trans('update.unpaid') }} {{ $isOverdue ? "(". trans('update.overdue') .")" : '' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($count11==0)
                                            @if(empty($stepPayment))
                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle"
                                                            data-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button>

                                                    <div class="dropdown-menu menu-lg">
                                                        <a href="{{ config('app.manual_base_url')}}/register-course/{{$webinar_title}}" target="_blank"
                                                           class="webinar-actions d-block mt-10 font-weight-normal">Pay Upcomming Part</a>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>

                                {{-- Sub Steps (Database-driven, only for unpaid with approved sub-steps) --}}
                                @if(empty($stepPayment))
                                    @php
                                        // Fetch approved sub-steps from database
                                        $approvedSubSteps = \App\Models\SubStepInstallment::where('installment_step_id', $step->id)
                                            ->where('user_id', auth()->id())
                                            ->orderBy('sub_step_number')
                                            ->get();

                                        $hasApprovedSubSteps = $approvedSubSteps->count() > 0;
                                    @endphp

                                    @if($hasApprovedSubSteps)
                                        @foreach($approvedSubSteps as $subIndex => $subStep)
                                            @php
                                                $halfPrice = $subStep->price;
                                                $subStepNumber = $subIndex +1;
                                                $subStepIsPaid = $paidAmount >= $halfPrice && $subStepNumber == 1;
                                                if ($subStepNumber == 2) {
                                                    $subStepIsPaid = $paidAmount >= $stepPrice;
                                                }

                                               
                                            @endphp

                                            <tr class="sub-step-row" data-parent-step="{{ $step->id }}" style="display: none; background-color: #f8f9fa;">
                                                <td class="text-left" style="padding-left: 40px;">
                                                    <div class="d-block font-14 font-weight-500 text-dark-blue">
                                                        {{ $step->title }} - Part {{ $subStepNumber }}
                                                        <span class="ml-5 font-12 text-gray">(50%)</span>
                                                    </div>
                                                    <span class="d-block font-12 text-gray">{{ $subStepNumber == 1 ? 'First' : 'Second' }} Half Payment</span>
                                                </td>

                                                <td class="text-center">
                                                    {{ handlePrice($halfPrice) }}
                                                </td>

                                                <td class="text-center">
                                                    <span class="{{ $isOverdue ? 'text-danger' : '' }}">{{ dateTimeFormat($dueAt, 'j M Y') }}</span>
                                                </td>

                                                <td class="text-center">-</td>

                                                <td class="text-center">
                                                    @if($subStepIsPaid)
                                                        <span class="text-primary">{{ trans('public.paid') }}</span>
                                                    @else
                                                        <span class="{{ $isOverdue ? 'text-danger' : 'text-dark-blue' }}">{{ trans('update.unpaid') }} {{ $isOverdue ? "(". trans('update.overdue') .")" : '' }}</span>
                                                    @endif
                                                </td>

                                                <td class="text-right">
                                                    @if(!$subStepIsPaid)
                                                        <div class="btn-group dropdown table-actions">
                                                            <button type="button" class="btn-transparent dropdown-toggle"
                                                                    data-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                <i data-feather="more-vertical" height="20"></i>
                                                            </button>

                                                            <div class="dropdown-menu menu-lg">
                                                                <a href="{{ config('app.manual_base_url')}}/register-course/{{$webinar_title}}" target="_blank"
                                                                   class="webinar-actions d-block mt-10 font-weight-normal">Pay This Part</a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
<script>
    // Pure JavaScript alternative (agar jQuery nahi hai)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Script loaded'); // Debug
        
        // Get all expandable rows
        var expandableRows = document.querySelectorAll('.expandable-step');
        console.log('Found expandable rows:', expandableRows.length); // Debug
        
        expandableRows.forEach(function(row) {
            // Add pointer cursor
            row.style.cursor = 'pointer';
            
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on dropdown button
                if (e.target.closest('.dropdown') || e.target.closest('button')) {
                    return;
                }
                
                console.log('Row clicked'); // Debug
                
                var stepId = this.getAttribute('data-step-id');
                var subSteps = document.querySelectorAll('tr.sub-step-row[data-parent-step="' + stepId + '"]');
                var chevron = this.querySelector('.chevron-icon');
                
                console.log('Step ID:', stepId, 'Sub steps found:', subSteps.length); // Debug
                
                // Toggle sub-steps
                subSteps.forEach(function(subStep) {
                    if (subStep.style.display === 'none' || subStep.style.display === '') {
                        subStep.style.display = 'table-row';
                    } else {
                        subStep.style.display = 'none';
                    }
                });
                
                // Rotate chevron
                if (chevron) {
                    if (chevron.style.transform === 'rotate(90deg)') {
                        chevron.style.transform = 'rotate(0deg)';
                    } else {
                        chevron.style.transform = 'rotate(90deg)';
                    }
                }
                
                // Re-initialize feather icons if available
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            });
        });
    });

    // jQuery version (agar jQuery available hai)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            console.log('jQuery version loaded'); // Debug
            
            $('.expandable-step').css('cursor', 'pointer').on('click', function(e) {
                // Don't trigger if clicking on dropdown
                if ($(e.target).closest('.dropdown').length || $(e.target).closest('button').length) {
                    return;
                }
                
                console.log('jQuery: Row clicked'); // Debug
                
                var stepId = $(this).data('step-id');
                var subSteps = $('tr.sub-step-row[data-parent-step="' + stepId + '"]');
                var chevron = $(this).find('.chevron-icon');
                
                console.log('jQuery: Step ID:', stepId, 'Sub steps:', subSteps.length); // Debug
                
                // Toggle sub-steps with animation
                subSteps.slideToggle(300);
                
                // Rotate chevron
                if (chevron.css('transform') === 'none' || chevron.css('transform') === 'matrix(1, 0, 0, 1, 0, 0)') {
                    chevron.css('transform', 'rotate(90deg)');
                } else {
                    chevron.css('transform', 'rotate(0deg)');
                }
                
                // Re-initialize feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            });
        });
    }
</script>
@endpush