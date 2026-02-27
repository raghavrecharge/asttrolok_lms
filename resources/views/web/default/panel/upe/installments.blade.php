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
            display: flex; flex-wrap: wrap; gap: 16px;
            justify-content: space-between;
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

        .upe-filter-bar {
            background: #fff; border-radius: 16px; padding: 16px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f0f0f0;
        }

        /* Stat Card Styles */
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
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07);
        }
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            flex-shrink: 0;
        }
        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: 800;
            color: #1f3b64;
            line-height: 1.2;
        }
        .stat-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #8c98a4;
            margin-top: 2px;
        }
        .bg-glass-primary { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.15); color: #28a745; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.15); color: #e6a800; }
        .bg-glass-info { background: rgba(13, 202, 240, 0.15); color: #0dcaf0; }

        /* Custom Pagination Styling */
        .custom-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 50px;
            padding: 8px 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
            width: fit-content;
            margin: 0 auto;
            border: 1px solid #f0f0f0;
            list-style: none;
        }
        .custom-pagination li {
            margin: 0 4px;
        }
        .custom-pagination li a, 
        .custom-pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .custom-pagination li a:hover {
            background: #f8f9fb;
            color: #43d477;
        }
        .custom-pagination li span.active {
            background: #43d477;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(67,212,119,0.3);
        }
        .custom-pagination li.previous a,
        .custom-pagination li.next a,
        .custom-pagination li.previous.disabled,
        .custom-pagination li.next.disabled {
            border: 1px solid #e2e8f0;
            color: #a0aec0;
        }
        .custom-pagination li.previous a:hover,
        .custom-pagination li.next a:hover {
            border-color: #43d477;
            color: #43d477;
            background: #fff;
        }
        .custom-pagination li.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .custom-pagination li span:not(.active) {
            cursor: default;
        }

        /* Card Progress Styling */
        .card-progress-container {
            margin-top: 15px;
            cursor: pointer;
            padding: 10px 12px;
            background: #f8f9fb;
            border-radius: 12px;
            transition: all 0.2s ease;
            border: 1px solid #f0f0f0;
        }
        .card-progress-container:hover {
            background: #f0f3ff;
            border-color: #d0d7ff;
        }
        .card-progress-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .card-progress-label .text {
            font-size: 11px;
            font-weight: 700;
            color: #1f3b64;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .card-progress-label .percent {
            font-size: 12px;
            font-weight: 800;
            color: #43d477;
        }
        .card-progress-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #43d477 0%, #28a745 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">My EMI Plans</h2>

        <div class="mt-25">
            <div class="row">
                <div class="col-6 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="monitor" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $purchasedCount }}</span>
                            <span class="stat-label">{{ trans('panel.purchased') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="play-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $activeCount }}</span>
                            <span class="stat-label">Active</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-4 mt-15 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="check-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $completedCount }}</span>
                            <span class="stat-label">Completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25 panel-filter-section">
        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="" method="get">
                <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <div style="position:relative;width:160px;">
                            <select name="status" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="">{{ trans('public.all') }}</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>
                    <div style="flex:0 0 auto;">
                        <button type="submit" style="height:40px;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                            <i data-feather="search" width="13" height="13"></i>
                            {{ trans('public.show_results') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

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
                        <div class="col-12 col-lg-4 mt-15">
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

                                    @php
                                        $courseProgress = $progress[$sale->id] ?? 0;
                                    @endphp
                                    <div class="card-progress-container trigger-progress-modal" data-sale-id="{{ $sale->id }}">
                                        <div class="card-progress-label">
                                            <span class="text">Course Progress</span>
                                            <span class="percent">{{ $courseProgress }}%</span>
                                        </div>
                                        <div class="card-progress-bar">
                                            <div class="card-progress-bar-fill" style="width: {{ $courseProgress }}%"></div>
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
                {{ $sales->appends(request()->query())->links('vendor.pagination.panel') }}
            </div>
        @endif

        {{-- Progress Detail Modal --}}
        <div class="modal fade" id="progressDetailModal" tabindex="-1" role="dialog" aria-labelledby="progressDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.15);">
                    <div class="modal-body" style="padding: 30px 25px;">
                        <div class="mb-20">
                            <h5 class="font-18 font-weight-bold text-dark-blue mb-5">Course Progress Breakdown</h5>
                            <p class="text-gray font-12 mb-0" id="modalCourseTitle">Loading course details...</p>
                        </div>

                        <div id="progressLoading" class="text-center py-30">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p class="mt-15 text-gray font-14">Fetching detailed progress...</p>
                        </div>
                        <div id="progressContent" class="d-none"></div>
                        <div id="progressError" class="d-none text-center py-20">
                            <i data-feather="alert-circle" class="text-danger mb-10" width="40" height="40"></i>
                            <p class="text-dark-blue font-14 font-weight-500">Failed to load progress details.</p>
                            <button class="btn btn-sm btn-outline-primary mt-15 px-20" onclick="location.reload()">Retry</button>
                        </div>

                        <div class="mt-30 text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger px-25" data-dismiss="modal" style="border-radius: 12px; font-weight: 700;">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            $('body').on('click', '.trigger-progress-modal', function() {
                var saleId = $(this).data('sale-id');
                if (saleId) triggerProgress(saleId);
            });

            function triggerProgress(saleId) {
                var $modal = $('#progressDetailModal'),
                    $loading = $('#progressLoading'),
                    $content = $('#progressContent'),
                    $error = $('#progressError'),
                    $title = $('#modalCourseTitle');

                $modal.modal('show');
                $loading.removeClass('d-none');
                $content.addClass('d-none').html('');
                $error.addClass('d-none');
                $title.text('Loading...');

                $.ajax({
                    url: '/panel/upe/purchases/' + saleId + '/progress',
                    method: 'GET',
                    success: function(r) {
                        $loading.addClass('d-none');
                        $title.text(r.course_title);
                        var html = '';
                        if (r.chapters && r.chapters.length) {
                            r.chapters.forEach(function(ch) {
                                html += '<div class="mb-20"><h6 class="font-14 font-weight-bold text-dark-blue mb-10">' + ch.title + '</h6>';
                                ch.items.forEach(function(it) {
                                    html += '<div class="p-10 mb-8" style="background:#f8f9fb;border-radius:10px;border:1px solid #f0f0f0;">' +
                                        '<div class="d-flex align-items-center justify-content-between mb-5">' +
                                        '<span class="font-12 text-dark-blue">' + it.title + '</span>' +
                                        '<span class="font-11 font-weight-700 ' + (it.percentage >= 100 ? 'text-primary' : 'text-gray') + '">' + it.percentage + '%</span></div>' +
                                        '<div class="progress" style="height:3px;border-radius:10px;background:rgba(0,0,0,0.05);">' +
                                        '<div class="progress-bar ' + (it.percentage >= 100 ? 'bg-primary' : 'bg-gray') + '" style="width:' + it.percentage + '%"></div></div></div>';
                                });
                                html += '</div>';
                            });
                            $content.html(html).removeClass('d-none');
                            if (typeof feather !== 'undefined') feather.replace();
                        } else {
                            $content.html('<p class="text-center text-gray py-20">No details found.</p>').removeClass('d-none');
                        }
                    },
                    error: function() {
                        $loading.addClass('d-none');
                        $error.removeClass('d-none');
                    }
                });
            }
        });
    </script>
@endpush
