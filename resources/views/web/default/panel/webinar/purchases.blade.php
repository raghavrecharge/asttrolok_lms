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
        .premium-course-card .rating-section {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
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
        .premium-course-card .expiry-info {
            display: flex;
            align-items: center;
        }
        .premium-course-card .expiry-icon {
            width: 32px;
            height: 32px;
            background: rgba(31, 59, 100, 0.08);
            color: #1f3b64;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        .premium-course-card .continue-btn {
            color: #1f3b64;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            background: transparent;
            border: none;
            padding: 5px 10px;
        }
        .premium-course-card .continue-btn:hover {
            color: #3b6096;
            margin-right: -5px;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('panel.my_activity') }}</h2>

        <div class="mt-25">
            <div class="row stat-card-row">
                <div class="col-12 col-sm-4">
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

                <div class="col-12 col-sm-4 mt-15 mt-sm-0">
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

                <div class="col-12 col-sm-4 mt-15 mt-sm-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="calendar" width="24" height="24"></i>
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
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('panel.my_purchases') }}</h2>
        </div>

        @if(!empty($sales) and !$sales->isEmpty())
            <div class="row mt-30">
                @foreach($sales as $sale)
                    @php
                        $item = $sale->item;
                        $nextSession = (!empty($item) and $item instanceof \App\Models\Webinar) ? $item->nextSession() : null;
                    @endphp

                    @if(!empty($item))
                        <div class="col-12 col-lg-6 mt-15">
                            <div class="premium-course-card">
                                <div class="image-container">
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" alt="{{ $item->title }}">
                                    <a href="{{ $item->getLearningPageUrl() }}" class="play-overlay">
                                        <i data-feather="play" width="24" height="24" fill="currentColor"></i>
                                    </a>
                                </div>

                                <div class="info-container">
                                    <div class="header-section">
                                        <div class="w-100">
                                            <a href="{{ $item->getLearningPageUrl() }}" class="course-title">{{ $item->title }}</a>
                                        </div>

                                        <div class="btn-group dropdown table-actions">
                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i data-feather="more-vertical" height="20"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if(!empty($sale->gift_id) and $sale->buyer_id == $authUser->id)
                                                    <a href="/panel/webinars/{{ $item->id }}/sale/{{ $sale->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">
                                                        <i data-feather="file-text" width="14" height="14" class="mr-8"></i>
                                                        {{ trans('public.invoice') }}
                                                    </a>
                                                @else
                                                    @if(!empty($item->access_days) and !$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id))
                                                        <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block mt-10">
                                                            <i data-feather="play-circle" width="14" height="14" class="mr-8"></i>
                                                            {{ trans('update.enroll_on_course') }}
                                                        </a>
                                                    @elseif(!empty($item) and $item instanceof \App\Models\Webinar)
                                                        <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block">
                                                            <i data-feather="book-open" width="14" height="14" class="mr-8"></i>
                                                            {{ trans('update.learning_page') }}
                                                        </a>

                                                        @if(!empty($item->start_date) and ($item->start_date > time() or ($item->isProgressing() and !empty($nextSession))))
                                                            <button type="button" data-webinar-id="{{ $item->id }}" class="join-purchase-webinar webinar-actions btn-transparent d-block mt-10">
                                                                <i data-feather="video" width="14" height="14" class="mr-8"></i>
                                                                {{ trans('footer.join') }}
                                                            </button>
                                                        @endif

                                                        @if(!empty($item->downloadable) or (!empty($item->files) and count($item->files)))
                                                            <a href="{{ $item->getUrl() }}?tab=content" target="_blank" class="webinar-actions d-block mt-10">
                                                                <i data-feather="download" width="14" height="14" class="mr-8"></i>
                                                                {{ trans('home.download') }}
                                                            </a>
                                                        @endif

                                                        @if($item->price > 0)
                                                            <a href="/panel/webinars/{{ $item->id }}/sale/{{ $sale->id }}/invoice" target="_blank" class="webinar-actions d-block mt-10">
                                                                <i data-feather="file-text" width="14" height="14" class="mr-8"></i>
                                                                {{ trans('public.invoice') }}
                                                            </a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $item->getUrl() }}?tab=reviews" target="_blank" class="webinar-actions d-block mt-10">
                                                        <i data-feather="star" width="14" height="14" class="mr-8"></i>
                                                        {{ trans('public.feedback') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="book" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ trans('webinars.'.$item->type) }}</span>
                                                <span class="info-label">Type</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="activity" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ $item->getProgress() }}%</span>
                                                <span class="info-label">Learning Progress</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="calendar" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ dateTimeFormat($sale->created_at, 'j M Y') }}</span>
                                                <span class="info-label">Enrollment Date</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="clock" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ convertMinutesToHourAndMinute($item->duration) }}</span>
                                                <span class="info-label">Duration</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="footer-section">
                                        <div class="expiry-info">
                                            <div class="expiry-icon"><i data-feather="calendar" width="16" height="16"></i></div>
                                            <div>
                                                @if(!empty($item->access_days))
                                                    @php
                                                        $expiryDate = $item->getExpiredAccessDays($sale->created_at, $sale->gift_id);
                                                        $isExpired = !$item->checkHasExpiredAccessDays($sale->created_at, $sale->gift_id);
                                                    @endphp
                                                    <span class="d-block font-12 font-weight-bold {{ $isExpired ? 'text-danger' : 'text-dark-blue' }}">
                                                        {{ $isExpired ? trans('update.access_days_expired') : 'Course Expiry' }}
                                                    </span>
                                                    @if(!$isExpired)
                                                        <span class="d-block font-11 text-gray">Course access expires on {{ dateTimeFormat($expiryDate, 'j M Y') }}</span>
                                                    @endif
                                                @else
                                                    <span class="d-block font-12 font-weight-bold text-dark-blue">Lifetime Access</span>
                                                    <span class="d-block font-11 text-gray">Unlimited course access</span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ $item->getLearningPageUrl() }}" class="continue-btn">
                                            Continue
                                            <i data-feather="arrow-right" width="16" height="16" class="ml-5"></i>
                                        </a>
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
                "title" => trans("panel.no_result_purchases"),
                "hint" => trans("panel.no_result_purchases_hint"),
                "btn" => [
                    "url" => config("app.manual_base_url") . "/classes?sort=newest",
                    "text" => trans("panel.start_learning")
                ]
            ])
        @endif

    </section>

    <div class="my-30">
        {{ $sales->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>

    @include('web.default.panel.webinar.join_webinar_modal')
@endsection

@push('scripts_bottom')
    <script>
        var undefinedActiveSessionLang = '{{ trans('webinars.undefined_active_session') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/join_webinar.min.js"></script>
@endpush
