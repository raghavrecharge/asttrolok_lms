@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            height: 100%;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-color: #1f3b64;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .stat-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
            display: block;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #1f3b64;
            display: block;
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.1); color: #43d477; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }

        /* Premium Course Card */
        .premium-course-card {
            background: #fff;
            border-radius: 24px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border: 1px solid #f8f8f8;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
        }
        .premium-course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            border-color: #1f3b64;
        }
        .premium-course-card .image-container {
            width: 100%;
            height: 180px;
            position: relative;
            flex-shrink: 0;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .premium-course-card .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .premium-course-card .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 2px solid #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            transition: all 0.3s ease;
        }
        .premium-course-card:hover .play-overlay {
            background: #1f3b64;
            transform: translate(-50%, -50%) scale(1.1);
        }
        .premium-course-card .info-container {
            flex-grow: 1;
            padding-left: 0;
            display: flex;
            flex-direction: column;
        }
        .premium-course-card .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .premium-course-card .course-title {
            font-size: 18px;
            font-weight: 800;
            color: #1f3b64;
            margin-bottom: 4px;
            display: block;
        }
        .premium-course-card .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 15px;
            margin-top: 10px;
        }
        .premium-course-card .info-item {
            display: flex;
            align-items: center;
        }
        .premium-course-card .info-icon {
            width: 36px;
            height: 36px;
            background: #f8faff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f3b64;
            margin-right: 10px;
            flex-shrink: 0;
        }
        .premium-course-card .info-text {
            line-height: 1.2;
        }
        .premium-course-card .info-value {
            display: block;
            font-weight: 700;
            font-size: 14px;
            color: #1f3b64;
        }
        .premium-course-card .info-label {
            display: block;
            font-size: 11px;
            color: #8c98a4;
            font-weight: 500;
        }
        .premium-course-card .footer-section {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f4f4f4;
        }
        
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 10px;
            display: inline-block;
            text-transform: uppercase;
        }
        .badge-active { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .badge-completed { background: rgba(67, 212, 119, 0.1); color: #43d477; }
        .badge-defaulted { background: rgba(244, 117, 117, 0.1); color: #f47575; }
        .badge-restructured { background: rgba(255, 193, 7, 0.1); color: #ffc107; }

        .btn-learning {
            background: linear-gradient(135deg, #43d477 0%, #28a745 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(67, 212, 119, 0.3);
            transition: all 0.3s ease;
            color: white !important;
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
        }
        .btn-learning:hover {
            box-shadow: 0 6px 20px rgba(67, 212, 119, 0.4);
            transform: scale(1.02);
            background: linear-gradient(135deg, #28a745 0%, #43d477 100%);
        }
        .btn-schedule {
            color: #1f3b64;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            background: #f8faff;
            padding: 8px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-schedule:hover {
            background: #f0f4ff;
            color: #1f3b64;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="section-title">My EMI Plans</h2>
        </div>

        @if(!empty($sales) and !$sales->isEmpty())
            <div class="row mt-30">
                @foreach($sales as $sale)
                    @if($sale->installmentPlan)
                        <div class="col-12 col-lg-6 mt-15">
                            <div class="premium-course-card">
                                @php
                                    $product = $sale->product;
                                    $webinar = null;
                                    if ($product && $product->external_id) {
                                        $webinar = \App\Models\Webinar::find($product->external_id);
                                    }
                                @endphp
                                
                                <div class="image-container">
                                    <img loading="lazy" src="{{ ($webinar) ? config('app.img_dynamic_url') . $webinar->getImage() : '/assets/default/img/default_course.png' }}" alt="{{ $product->name ?? 'Course' }}">
                                    @if($webinar)
                                        <a href="/course/learning/{{ $webinar->slug }}" class="play-overlay">
                                            <i data-feather="play" width="24" height="24" fill="currentColor"></i>
                                        </a>
                                    @endif
                                </div>

                                <div class="info-container">
                                    <div class="header-section">
                                        <div class="w-100">
                                            <span class="course-title">{{ $product->name ?? 'Course' }}</span>
                                            <span class="font-12 text-gray">Sale #{{ $sale->id }}</span>
                                        </div>

                                        @php
                                            $status = $sale->installmentPlan->status;
                                            $badgeClass = 'badge-' . $status;
                                        @endphp
                                        <div class="status-badge {{ $badgeClass }}">{{ ucfirst($status) }}</div>
                                    </div>

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
                                            <div class="info-icon"><i data-feather="dollar-sign" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ handlePrice($sale->installmentPlan->total_amount) }}</span>
                                                <span class="info-label">Total Amount</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="layers" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ $paidCount }}/{{ $totalCount }}</span>
                                                <span class="info-label">Installments</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="activity" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value {{ $overdueCount > 0 ? 'text-danger' : '' }}">{{ $paidPercent }}%</span>
                                                <span class="info-label">Paid Progress</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="calendar" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value {{ ($nextDue && $nextDue->status === 'overdue') ? 'text-danger' : '' }}">
                                                    @if($nextDue)
                                                        {{ $nextDue->due_date ? \Carbon\Carbon::parse($nextDue->due_date)->format('d M') : '-' }}
                                                    @else
                                                        Closed
                                                    @endif
                                                </span>
                                                <span class="info-label">Next Due</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-20">
                                        <div class="progress" style="height: 8px; border-radius: 10px; background: #f0f4ff;">
                                            <div class="progress-bar {{ $overdueCount > 0 ? 'bg-danger' : 'bg-primary' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $paidPercent }}%; border-radius: 10px;" 
                                                 aria-valuenow="{{ $paidPercent }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </div>

                                    <div class="footer-section">
                                        <a href="/panel/upe/installments/{{ $sale->installmentPlan->id }}" class="btn-schedule mr-10">
                                            <i data-feather="list" width="16" height="16" class="mr-8"></i>
                                            Schedule
                                        </a>

                                        @if($webinar)
                                            <a href="/course/learning/{{ $webinar->slug }}" target="_blank" class="btn-learning ml-auto">
                                                <i data-feather="play-circle" width="16" height="16" class="mr-8"></i>
                                                Learning Page
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            @include(getTemplate() . '.includes.no-result', [
                "file_name" => "student.png",
                "title" => "No EMI plans found",
                "hint" => "You haven't purchased anything on installments yet.",
                "btn" => [
                    "url" => "/courses",
                    "text" => "Browse Courses"
                ]
            ])
        @endif

        @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="my-30">
                {{ $sales->appends(request()->query())->links('vendor.pagination.panel') }}
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
