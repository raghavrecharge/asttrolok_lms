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
        .bg-glass-danger { background: rgba(244, 110, 110, 0.1); color: #f46e6e; }

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
            margin-bottom: 8px;
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
        .premium-course-card .status-badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
        }
        .premium-course-card .detail-btn {
            color: #1f3b64;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        .premium-course-card .detail-btn:hover {
            color: #3b6096;
            margin-right: -5px;
        }
    </style>
@endpush

@section('content')

    @if(!empty($overdueInstallmentsCount) and $overdueInstallmentsCount > 0)
        <div class="d-flex align-items-center mb-20 p-15 danger-transparent-alert">
            <div class="danger-transparent-alert__icon d-flex align-items-center justify-content-center">
                <i data-feather="credit-card" width="18" height="18" class=""></i>
            </div>
            <div class="ml-10">
                <div class="font-14 font-weight-bold ">{{ trans('update.overdue_installments') }}</div>
                <div class="font-12 ">{{ trans('update.you_have_count_overdue_installments_please_pay_them_to_avoid_restrictions_and_negative_effects_on_your_account',['count' => $overdueInstallmentsCount]) }}</div>
            </div>
        </div>
    @endif

    <section>
        <h2 class="section-title">{{ trans('update.installments_overview') }}</h2>

        <div class="mt-25">
            <div class="row">
                <div class="col-6 col-md-3 mt-30 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="book-open" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $openInstallmentsCount }}</span>
                            <span class="stat-label">{{ trans('update.open_installments') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="clock" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $pendingVerificationCount }}</span>
                            <span class="stat-label">{{ trans('update.pending_verification') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="check-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $finishedInstallmentsCount }}</span>
                            <span class="stat-label">{{ trans('update.finished_installments') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-30 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-danger">
                            <i data-feather="alert-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $overdueInstallmentsCount }}</span>
                            <span class="stat-label">{{ trans('update.overdue_installments') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('update.my_installments') }}</h2>
        </div>

        @if(!empty($orders) and count($orders))
            <div class="row mt-30">
                @foreach($orders as $order)
                    @php
                        $orderItem = $order->item;
                        $itemType = $order->product->product_type ?? 'webinar';
                        if ($itemType === 'webinar') $itemType = 'course'; // Backward compatibility with legacy trans keys
                        
                        $itemPrice = $order->installmentPlan ? $order->installmentPlan->total_amount : $order->base_fee_snapshot;
                    @endphp

                    @if(!empty($orderItem))
                        <div class="col-12 col-lg-6 mt-15">
                            <div class="premium-course-card">
                                <div class="image-container">
                                    @if(in_array($itemType, ['course', 'bundle']))
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $orderItem->getImage() }}" alt="{{ $orderItem->title }}">
                                    @elseif($itemType == 'product')
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $orderItem->thumbnail }}" alt="{{ $orderItem->title }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light">
                                            <i data-feather="package" width="48" height="48" class="text-gray"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="info-container">
                                    <div class="header-section">
                                        <div class="w-100">
                                            <h3 class="course-title">{{ $orderItem->title }}</h3>
                                            @if($order->has_overdue)
                                                <span class="badge badge-danger">Overdue</span>
                                            @endif
                                        </div>

                                        @if(!in_array($order->status, ['refunded', 'canceled']) or $order->isCompleted())
                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    @if($order->installmentPlan)
                                                        <a href="/panel/upe/installments/{{ $order->installmentPlan->id }}" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.view_details') }}</a>
                                                    @endif

                                                    @if($itemType == "course" and !empty($orderItem))
                                                        <a href="{{ $orderItem->getLearningPageUrl() }}" target="_blank" class="webinar-actions d-block mt-10">{{ trans('update.learning_page') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="info-grid">
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="tag" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ trans('update.item_type_'.$itemType) }}</span>
                                                <span class="info-label">Item Type</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="calendar" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ dateTimeFormat($order->created_at, 'j M Y') }}</span>
                                                <span class="info-label">Purchase Date</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="dollar-sign" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ handlePrice($itemPrice) }}</span>
                                                <span class="info-label">Plan Total</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="repeat" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value">{{ $order->remained_installments_count }} / {{ $order->installmentPlan->num_installments ?? 0 }}</span>
                                                <span class="info-label">Remaining EMIs</span>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon"><i data-feather="credit-card" width="16" height="16"></i></div>
                                            <div class="info-text">
                                                <span class="info-value text-danger">{{ handlePrice($order->remained_installments_amount) }}</span>
                                                <span class="info-label">Remaining Amount</span>
                                            </div>
                                        </div>
                                        @if(!empty($order->upcoming_installment))
                                            <div class="info-item">
                                                <div class="info-icon"><i data-feather="clock" width="16" height="16"></i></div>
                                                <div class="info-text">
                                                    <span class="info-value">{{ dateTimeFormat($order->upcoming_installment->due_date, 'j M Y') }}</span>
                                                    <span class="info-label">Next Due: {{ handlePrice($order->upcoming_installment->amount_due) }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="footer-section">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $statusClass = 'bg-primary';
                                                $statusText = $order->status;
                                                if($order->installmentPlan && $order->installmentPlan->isCompleted()) { $statusClass = 'bg-success'; $statusText = 'Completed'; }
                                                elseif($order->status == 'refunded') { $statusClass = 'bg-danger'; }
                                            @endphp
                                            <span class="status-badge {{ $statusClass }} text-white">{{ ucfirst($statusText) }}</span>
                                        </div>

                                        @if($order->installmentPlan)
                                            <a href="/panel/upe/installments/{{ $order->installmentPlan->id }}" class="detail-btn">
                                                View Details
                                                <i data-feather="arrow-right" width="16" height="16" class="ml-5"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <div class="my-30">
                {{ $orders->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include('web.default.includes.no-result',[
                    'file_name' => 'webinar.png',
                    'title' => trans('update.you_not_have_any_installment'),
                    'hint' =>  trans('update.you_not_have_any_installment_hint'),
                ])
        @endif
    </section>
@endsection

@push('scripts_bottom')

@endpush
