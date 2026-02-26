@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        body {
            background-color: #f4f7fa;
        }
        .purchase-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .purchase-card:hover {
            box-shadow: 0 20px 50px rgba(31, 59, 100, 0.12);
            transform: translateY(-8px);
            border-color: #1f3b64;
        }
        .purchase-card:hover .info-item i {
            transform: scale(1.2);
            color: #1f3b64;
        }
        .info-item i {
            transition: all 0.3s ease;
        }
        .card-header-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #f1f4f8 100%);
            padding: 22px 25px;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }
        .card-header-gradient::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 25px;
            right: 25px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(31, 59, 100, 0.1), transparent);
        }
        .card-body-content {
            padding: 25px;
            flex-grow: 1;
        }
        .card-footer-actions {
            padding: 18px 25px;
            background: #fbfcfe;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 10px;
            color: #8c98a4;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            font-weight: 600;
        }
        .info-value {
            font-size: 15px;
            font-weight: 700;
            color: #1f3b64;
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .type-tag {
            font-size: 9px;
            background: rgba(67, 212, 119, 0.15);
            color: #28a745;
            padding: 3px 10px;
            border-radius: 6px;
            font-weight: 700;
            margin-right: 8px;
            letter-spacing: 0.5px;
        }
        
        /* Pagination Refined Styling */
        .pagination-container {
            background: #fff;
            padding: 15px 25px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }
        .pagination-container nav {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .pagination-container nav > div:first-child {
            display: none !important;
        }
        .pagination-container nav > div:last-child {
            display: flex !important;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        .pagination-container nav div:last-child div:first-child {
            display: none !important;
        }
        .pagination-container nav div:last-child div:last-child {
            display: flex !important;
            box-shadow: none !important;
            border: none !important;
            background: transparent !important;
        }
        .pagination-container nav a, 
        .pagination-container nav span {
            border-radius: 12px !important;
            margin: 4px;
            border: 1px solid #eee !important;
            min-width: 40px;
            height: 40px;
            display: flex !important;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            transition: all 0.3s ease;
            color: #1f3b64;
            background: #fff;
            padding: 0 10px !important;
            font-size: 14px;
            text-decoration: none !important;
        }
        .pagination-container nav a:hover {
            background: #f8f9fb;
            border-color: #1f3b64 !important;
        }
        .pagination-container nav [aria-current="page"] span {
            background-color: #1f3b64 !important;
            color: #fff !important;
            border: none !important;
            width: 100%;
            height: 100%;
        }
        .pagination-container svg {
            width: 18px !important;
            height: 18px !important;
            stroke-width: 3;
        }

        .btn-learning {
            background: linear-gradient(135deg, #43d477 0%, #28a745 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(67, 212, 119, 0.3);
            transition: all 0.3s ease;
            color: white !important;
        }
        .btn-learning:hover {
            box-shadow: 0 6px 20px rgba(67, 212, 119, 0.4);
            transform: scale(1.02);
            background: linear-gradient(135deg, #28a745 0%, #43d477 100%);
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">My EMI Plans</h2>

        <div class="row mt-20">
            @forelse($sales as $sale)
                @if($sale->installmentPlan)
                    <div class="col-12 col-lg-6 mt-20">
                        <div class="purchase-card">
                            <div class="card-header-gradient">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div style="flex: 1;">
                                        <div class="d-flex align-items-center mb-5">
                                            <span class="type-tag text-uppercase">EMI PLAN</span>
                                            <span class="font-12 text-gray">Sale #{{ $sale->id }}</span>
                                        </div>
                                        <h3 class="font-16 font-weight-bold text-dark-blue line-height-1" style="max-width: 90%;">
                                            @if($sale->product)
                                                {{ $sale->product->name }}
                                            @else
                                                Product #{{ $sale->product_id }}
                                            @endif
                                        </h3>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $planColors = [
                                                'active' => 'primary',
                                                'completed' => 'primary',
                                                'defaulted' => 'danger',
                                                'restructured' => 'warning',
                                            ];
                                            $planStatusClass = 'badge-' . ($planColors[$sale->installmentPlan->status] ?? 'secondary');
                                        @endphp
                                        <div class="status-badge {{ $planStatusClass }}">{{ ucfirst($sale->installmentPlan->status) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body-content">
                                @php
                                    $paidCount = $sale->installmentPlan->schedules->where('status', 'paid')->count();
                                    $totalCount = $sale->installmentPlan->schedules->count();
                                    $paidPercent = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0;
                                    $overdueCount = $sale->installmentPlan->schedules->where('status', 'overdue')->count();
                                    
                                    $nextDue = $sale->installmentPlan->schedules
                                        ->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])
                                        ->sortBy('sequence')
                                        ->first();
                                @endphp

                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-label">
                                            <i data-feather="dollar-sign" width="12" class="mr-5"></i> Total Amount
                                        </div>
                                        <div class="info-value">
                                            {{ handlePrice($sale->installmentPlan->total_amount) }}
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">
                                            <i data-feather="layers" width="12" class="mr-5"></i> Installments
                                        </div>
                                        <div class="info-value">
                                            {{ $sale->installmentPlan->num_installments }}
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">
                                            <i data-feather="check-square" width="12" class="mr-5"></i> Paid Status
                                        </div>
                                        <div class="info-value d-flex align-items-center">
                                            <span>{{ $paidCount }}/{{ $totalCount }}</span>
                                            @if($overdueCount > 0)
                                                <span class="text-danger ml-5 font-11 font-weight-bold">({{ $overdueCount }} Overdue)</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-label">
                                            <i data-feather="calendar" width="12" class="mr-5"></i> Next Due
                                        </div>
                                        <div class="info-value {{ ($nextDue && $nextDue->status === 'overdue') ? 'text-danger' : '' }}">
                                            @if($nextDue)
                                                {{ $nextDue->due_date ? \Carbon\Carbon::parse($nextDue->due_date)->format('d M Y') : '-' }}
                                            @else
                                                <span class="text-primary">Completed</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-20">
                                    <div class="d-flex align-items-center justify-content-between mb-5">
                                        <span class="font-11 font-weight-bold text-gray uppercase">Overall Progress</span>
                                        <span class="font-11 font-weight-bold text-primary">{{ $paidPercent }}%</span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 10px; background: #f0f0f0; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
                                        <div class="progress-bar {{ $overdueCount > 0 ? 'bg-danger' : 'bg-primary' }}" 
                                             role="progressbar" 
                                             style="width: {{ $paidPercent }}%; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);" 
                                             aria-valuenow="{{ $paidPercent }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer-actions">
                                <a href="/panel/upe/installments/{{ $sale->installmentPlan->id }}" class="btn btn-sm btn-outline-primary px-20 btn-hover-shadow" style="border-radius: 10px; font-weight: 600;">
                                    <i data-feather="list" width="14" class="mr-5"></i> Full Schedule
                                </a>
                                @if($sale->product && in_array($sale->product->product_type, ['course_video', 'webinar', 'course_live']) && in_array($sale->status, ['active', 'completed']))
                                    @php $webinarForLink = \App\Models\Webinar::find($sale->product->external_id); @endphp
                                    @if($webinarForLink && $webinarForLink->slug)
                                        <a href="/course/learning/{{ $webinarForLink->slug }}" target="_blank" class="btn btn-sm btn-primary ml-10 d-flex align-items-center px-18 btn-learning" style="border-radius: 10px; font-weight: 600;">
                                            <i data-feather="play-circle" width="16" class="mr-8"></i> Learning Page
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-12 mt-15 text-center">
                    <div class="purchase-card py-50">
                        <div class="d-flex flex-column align-items-center">
                            <i data-feather="credit-card" width="60" height="60" class="text-gray mb-20" style="opacity: 0.3;"></i>
                            <h3 class="font-20 font-weight-bold text-dark-blue">No EMI plans found</h3>
                            <p class="text-gray mt-5">You haven't purchased anything on installments yet.</p>
                            <a href="/courses" class="btn btn-primary mt-20 px-30" style="border-radius: 12px;">Browse Courses</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="pagination-container">
                {{ $sales->appends(request()->query())->links() }}
            </div>
        @endif
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
