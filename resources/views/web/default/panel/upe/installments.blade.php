@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .upe-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid #f0f0f0;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }
        .upe-card:hover {
            box-shadow: 0 12px 35px rgba(31,59,100,0.1);
            transform: translateY(-5px);
            border-color: #1f3b64;
        }
        .upe-card .image-box {
            position: relative;
            height: 160px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .upe-card .image-box img {
            width: 100%; height: 100%;
            object-fit: cover;
        }
        .upe-card .image-box .upe-badge-overlay {
            position: absolute; top: 10px; left: 10px;
            display: flex; gap: 5px; flex-wrap: wrap;
        }
        .upe-card .image-box .upe-type-badge {
            font-size: 9px; font-weight: 700; text-transform: uppercase;
            padding: 3px 8px; border-radius: 6px;
            background: rgba(0,0,0,0.55); color: #fff;
            letter-spacing: 0.4px; backdrop-filter: blur(4px);
        }
        .upe-card .image-box .upe-status-pos {
            position: absolute; top: 10px; right: 10px;
        }
        .upe-card .image-box .progress {
            position: absolute; bottom: 0; left: 0; right: 0;
            height: 4px; border-radius: 0; background: rgba(255,255,255,0.3);
        }
        .upe-card .image-box .progress .progress-bar { border-radius: 0; }
        .upe-card-body {
            padding: 14px 16px;
            flex-grow: 1;
            display: flex; flex-direction: column;
        }
        .upe-card-title {
            font-size: 14px; font-weight: 700; color: #1f3b64;
            line-height: 1.3; margin-bottom: 8px;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .upe-card-title a { color: inherit; text-decoration: none; }
        .upe-card-title a:hover { color: #2563eb; }
        .upe-card-meta {
            display: flex; flex-wrap: wrap; gap: 10px;
            margin-top: auto; padding-top: 10px;
            border-top: 1px solid #f5f5f5;
        }
        .upe-card-meta .meta-item {
            display: flex; flex-direction: column;
        }
        .upe-card-meta .meta-label {
            font-size: 9px; color: #8c98a4; text-transform: uppercase;
            letter-spacing: 0.5px; font-weight: 600;
        }
        .upe-card-meta .meta-value {
            font-size: 12px; font-weight: 700; color: #1f3b64;
        }
        .upe-card-footer {
            padding: 10px 16px 14px;
            display: flex; align-items: center; gap: 8px;
            border-top: 1px solid #f5f5f5;
        }
        .upe-status-pill {
            padding: 3px 10px; border-radius: 20px;
            font-size: 9px; font-weight: 700;
            display: inline-block; white-space: nowrap;
        }
        .upe-status-pill.active { background: #e8f5e9; color: #2e7d32; }
        .upe-status-pill.completed { background: #e3f2fd; color: #1565c0; }
        .upe-status-pill.defaulted { background: #ffebee; color: #c62828; }
        .upe-status-pill.restructured { background: #fff8e1; color: #e6a800; }
        
        .btn-upe-learn {
            background: linear-gradient(135deg, #43d477 0%, #28a745 100%);
            border: none; color: #fff !important; font-size: 11px;
            padding: 5px 12px; border-radius: 8px; font-weight: 600;
            transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-upe-learn:hover { box-shadow: 0 4px 12px rgba(67,212,119,0.35); transform: scale(1.02); }
        .btn-upe-detail {
            font-size: 11px; padding: 5px 12px; border-radius: 8px;
            font-weight: 600; border: 1px solid #dde1e6; color: #1f3b64;
            background: #fff; transition: all 0.2s ease;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-upe-detail:hover { border-color: #1f3b64; background: #f8faff; text-decoration: none; }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="section-title">My EMI Plans</h2>
        </div>

        @if(!empty($sales) and !$sales->isEmpty())
            <div class="row mt-20">
                @foreach($sales as $sale)
                    @if($sale->installmentPlan)
                        @php
                            $item = $sale->item ?? null;
                            $paidCount = $sale->installmentPlan->schedules->where('status', 'paid')->count();
                            $totalCount = $sale->installmentPlan->schedules->count();
                            $paidPercent = $totalCount > 0 ? round(($paidCount / $totalCount) * 100) : 0;
                            $overdueCount = $sale->installmentPlan->schedules->where('status', 'overdue')->count();
                            
                            $nextDue = $sale->installmentPlan->schedules
                                ->whereIn('status', ['due', 'upcoming', 'partial', 'overdue'])
                                ->sortBy('sequence')
                                ->first();
                            $status = $sale->installmentPlan->status;
                        @endphp
                        <div class="col-6 col-lg-4 mt-15">
                            <div class="upe-card">
                                <div class="image-box">
                                    @if($item)
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" alt="{{ $item->title }}">
                                    @else
                                        <div style="width:100%;height:100%;background:linear-gradient(135deg,#1f3b64,#2563eb);display:flex;align-items:center;justify-content:center;">
                                            <i data-feather="book-open" width="40" height="40" style="color:rgba(255,255,255,0.3);"></i>
                                        </div>
                                    @endif
                                    <div class="upe-badge-overlay">
                                        @if($sale->product)
                                            <span class="upe-type-badge">{{ str_replace('_',' ',$sale->product->product_type) }}</span>
                                        @endif
                                        <span class="upe-type-badge" style="background:rgba(255,193,7,0.8);color:#000;">EMI</span>
                                    </div>
                                    <div class="upe-status-pos">
                                        <span class="upe-status-pill {{ $status }}">{{ ucfirst($status) }}</span>
                                    </div>
                                    @if($paidPercent > 0)
                                        <div class="progress">
                                            <div class="progress-bar {{ $overdueCount > 0 ? 'bg-danger' : 'bg-primary' }}" style="width:{{ $paidPercent }}%"></div>
                                        </div>
                                    @endif
                                </div>

                                <div class="upe-card-body">
                                    <h4 class="upe-card-title">
                                        @if($item)
                                            <a href="{{ $item->getUrl() }}">{{ $item->title }}</a>
                                        @elseif($sale->product)
                                            {{ $sale->product->name }}
                                        @else
                                            Product #{{ $sale->product_id }}
                                        @endif
                                    </h4>

                                    @if($item && $item->teacher)
                                        <div class="d-flex align-items-center mb-8" style="gap:6px;">
                                            <i data-feather="user" width="11" height="11" class="text-gray"></i>
                                            <span style="font-size:11px;color:#6c757d;">{{ $item->teacher->full_name }}</span>
                                        </div>
                                    @endif

                                    <div class="upe-card-meta">
                                        <div class="meta-item">
                                            <span class="meta-label">Total</span>
                                            <span class="meta-value">{{ handlePrice($sale->installmentPlan->total_amount) }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Paid</span>
                                            <span class="meta-value">{{ $paidCount }}/{{ $totalCount }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Next Due</span>
                                            <span class="meta-value {{ ($nextDue && $nextDue->status === 'overdue') ? 'text-danger' : '' }}">
                                                @if($nextDue)
                                                    {{ $nextDue->due_date ? \Carbon\Carbon::parse($nextDue->due_date)->format('d M') : '-' }}
                                                @else
                                                    Closed
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="upe-card-footer">
                                    <a href="/panel/upe/installments/{{ $sale->installmentPlan->id }}" class="btn-upe-detail">
                                        <i data-feather="list" width="12" height="12"></i> Schedule
                                    </a>
                                    @if($item && $item->slug)
                                        <a href="/course/learning/{{ $item->slug }}" target="_blank" class="btn-upe-learn">
                                            <i data-feather="play-circle" width="12" height="12"></i> Learn
                                        </a>
                                    @endif
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

        @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator && $sales->hasPages())
            <div class="my-30">
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
