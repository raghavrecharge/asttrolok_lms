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
        .upe-status-pill.pending_payment { background: #fff8e1; color: #e6a800; }
        .upe-status-pill.refunded { background: #ffebee; color: #c62828; }
        .upe-status-pill.partially_refunded { background: #fff3e0; color: #e65100; }
        .upe-status-pill.completed { background: #e3f2fd; color: #1565c0; }
        .upe-status-pill.expired { background: #fce4ec; color: #880e4f; }
        .upe-status-pill.cancelled { background: #f5f5f5; color: #616161; }
        .upe-status-pill.emi-due { background: #fff8e1; color: #e6a800; }
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
        .upe-empty {
            text-align: center; padding: 60px 20px;
            background: #fff; border-radius: 20px;
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
        <h2 class="section-title">My Purchases</h2>

        <div class="mt-25">
            <div class="row">
                <div class="col-12 col-md-4">
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

                <div class="col-12 col-md-4 mt-15 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="clock" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ convertMinutesToHourAndMinute($hours) }}</span>
                            <span class="stat-label">{{ trans('home.hours') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 mt-15 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="video" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $upComing }}</span>
                            <span class="stat-label">{{ trans('panel.upcoming') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <!-- <h2 class="section-title">My Purchases</h2> -->

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form method="get">
                <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">

                    {{-- Type --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="grid" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.type') }}
                        </label>
                        <div style="position:relative;width:140px;">
                            <select name="type" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="course" {{ request('type', 'course') == 'course' ? 'selected' : '' }}>Courses</option>
                                <option value="meeting" {{ request('type') == 'meeting' ? 'selected' : '' }}>Meetings</option>
                                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <div style="position:relative;width:160px;">
                            <select name="status" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="">All Statuses</option>
                                @foreach(['active','pending_payment','completed','refunded','partially_refunded','expired'] as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
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

    <section class="mt-20">
        <div class="row">
            @forelse($sales as $sale)
                @php
                    $item = $sale->item ?? null;
                    $percent = $progress[$sale->id] ?? 0;
                    $hasEmiDue = $sale->pricing_mode === 'installment'
                        && $sale->installmentPlan
                        && $sale->installmentPlan->schedules->whereIn('status', ['due','partial','overdue','upcoming'])->count() > 0;
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
                                @if($sale->pricing_mode === 'installment')
                                    <span class="upe-type-badge" style="background:rgba(255,193,7,0.8);color:#000;">EMI</span>
                                @endif
                            </div>
                            <div class="upe-status-pos">
                                <span class="upe-status-pill {{ $sale->status }}">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
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
                                    <span class="meta-label">Amount</span>
                                    <span class="meta-value">
                                        @php
                                            $displayPrice = $sale->installmentPlan
                                                ? $sale->installmentPlan->total_amount
                                                : ($balances[$sale->id] ?? $sale->base_fee_snapshot);
                                        @endphp
                                        {{ handlePrice($displayPrice) }}
                                    </span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Access</span>
                                    <span class="meta-value">
                                        @if(isset($accessResults[$sale->id]) && $accessResults[$sale->id]->hasAccess)
                                            <span style="color:#2e7d32;">Active</span>
                                        @else
                                            <span style="color:#c62828;">No</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Valid Till</span>
                                    <span class="meta-value">
                                        @if($sale->valid_until)
                                            {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                        @else
                                            Lifetime
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="card-progress-container trigger-progress-modal" data-sale-id="{{ $sale->id }}">
                                <div class="card-progress-label">
                                    <span class="text">Course Progress</span>
                                    <span class="percent">{{ $percent }}%</span>
                                </div>
                                <div class="card-progress-bar">
                                    <div class="card-progress-bar-fill" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="upe-card-footer">
                            @if($hasEmiDue)
                                <span class="upe-status-pill emi-due">EMI Due</span>
                            @endif
                            <a href="/panel/upe/purchases/{{ $sale->id }}" class="btn-upe-detail">
                                <i data-feather="info" width="12" height="12"></i> Details
                            </a>
                            @if($item && $sale->product && in_array($sale->product->product_type, ['course_video','webinar','course_live']) && isset($accessResults[$sale->id]) && $accessResults[$sale->id]->hasAccess)
                                @if($item->slug)
                                    <a href="/course/learning/{{ $item->slug }}" target="_blank" class="btn-upe-learn">
                                        <i data-feather="play-circle" width="12" height="12"></i> Learn
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 mt-15">
                    <div class="upe-empty">
                        <div style="width:60px;height:60px;border-radius:50%;background:#f0f3ff;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i data-feather="shopping-bag" width="26" height="26" style="color:#1f3b64;"></i>
                        </div>
                        <h3 style="font-size:18px;font-weight:700;color:#1f3b64;">No purchases found</h3>
                        <p class="text-gray mt-5" style="font-size:13px;">You haven't purchased any courses yet.</p>
                        <a href="/classes" class="btn btn-primary mt-15 px-25" style="border-radius:10px;">Browse Courses</a>
                    </div>
                </div>
            @endforelse
        </div>

        @if($sales->hasPages())
            <div class="mt-30">
                {{ $sales->appends(request()->query())->links('vendor.pagination.panel') }}
            </div>
        @endif
    </section>

    {{-- Progress Detail Modal --}}
    <div class="modal fade" id="progressDetailModal" tabindex="-1" role="dialog" aria-labelledby="progressDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 15px 50px rgba(0,0,0,0.15);">
                <div class="modal-header border-0 pb-0" style="padding: 25px 25px 10px;">
                    <div>
                        <h5 class="modal-title font-18 font-weight-bold text-dark-blue" id="progressDetailModalLabel">Course Progress Breakdown</h5>
                        <p class="text-gray font-12 mb-0" id="modalCourseTitle">Loading course details...</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px 25px 30px;">
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (typeof feather !== 'undefined') feather.replace();

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

