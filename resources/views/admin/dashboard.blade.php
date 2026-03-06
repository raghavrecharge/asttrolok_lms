@extends('admin.layouts.app')

@push('libraries_top')
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.theme.min.css">
@endpush

@push('styles_top')
<style>
    .db-greeting {
        background: linear-gradient(135deg, #43d477 0%, #1f3b64 100%);
        border-radius: 16px;
        padding: 24px 32px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .db-greeting h2 { font-size: 1.4rem; font-weight: 600; margin: 0; color: #fff; }
    .db-greeting p { margin: 4px 0 0; opacity: 0.85; font-size: 0.9rem; }
    .db-greeting .db-actions a {
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 0.82rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .db-greeting .db-actions a:hover { background: rgba(255,255,255,0.3); }

    .db-kpi {
        background: #fff;
        border-radius: 14px;
        padding: 22px 24px;
        border: 1px solid #eef0f5;
        transition: box-shadow 0.2s;
        height: 100%;
    }
    .db-kpi:hover { box-shadow: 0 3px 16px rgba(67,212,119,0.15); }
    .db-kpi .db-kpi-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        margin-bottom: 16px;
    }
    .db-kpi .db-kpi-icon.blue { background: #e8f8ef; color: #43d477; }
    .db-kpi .db-kpi-icon.green { background: #ecfdf5; color: #10b981; }
    .db-kpi .db-kpi-icon.amber { background: #fffbeb; color: #f59e0b; }
    .db-kpi .db-kpi-icon.rose { background: #fff1f2; color: #f43f5e; }
    .db-kpi .db-kpi-icon.purple { background: #eef4fb; color: #1f3b64; }
    .db-kpi .db-kpi-icon.teal { background: #e6fffa; color: #14b8a6; }
    .db-kpi .db-kpi-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .db-kpi .db-kpi-label {
        font-size: 0.82rem;
        color: #94a3b8;
        font-weight: 500;
        margin-top: 2px;
    }
    .db-kpi .db-kpi-sub {
        display: flex;
        gap: 16px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
    }
    .db-kpi .db-kpi-sub-item .val { font-size: 0.95rem; font-weight: 600; color: #334155; }
    .db-kpi .db-kpi-sub-item .lbl { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }

    .db-mini-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px 20px;
        border: 1px solid #eef0f5;
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none !important;
        transition: all 0.2s;
        height: 100%;
    }
    .db-mini-card:hover { box-shadow: 0 3px 12px rgba(67,212,119,0.12); transform: translateY(-1px); }
    .db-mini-card .mc-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .db-mini-card .mc-icon.blue { background: #e8f8ef; color: #43d477; }
    .db-mini-card .mc-icon.rose { background: #fff1f2; color: #f43f5e; }
    .db-mini-card .mc-icon.amber { background: #fffbeb; color: #f59e0b; }
    .db-mini-card .mc-icon.green { background: #ecfdf5; color: #10b981; }
    .db-mini-card .mc-val { font-size: 1.3rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
    .db-mini-card .mc-lbl { font-size: 0.78rem; color: #94a3b8; font-weight: 500; }

    .db-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eef0f5;
        overflow: hidden;
    }
    .db-card .db-card-head {
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .db-card .db-card-head h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }
    .db-card .db-card-body { padding: 20px 24px; }

    .db-chart-toggle .btn {
        border-radius: 8px;
        font-size: 0.78rem;
        padding: 5px 14px;
        font-weight: 500;
        border: 1px solid #e2e8f0;
        color: #64748b;
        background: #fff;
    }
    .db-chart-toggle .btn.active, .db-chart-toggle .btn-primary {
        background: #43d477;
        border-color: #43d477;
        color: #fff;
    }

    .db-stat-row {
        display: flex;
        gap: 0;
        flex-wrap: wrap;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #f1f5f9;
    }
    .db-stat-item {
        flex: 1;
        min-width: 100px;
        text-align: center;
        padding: 0 8px;
    }
    .db-stat-item + .db-stat-item { border-left: 1px solid #f1f5f9; }
    .db-stat-item .grow {
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .db-stat-item .grow.up { color: #43d477; }
    .db-stat-item .grow.down { color: #f43f5e; }
    .db-stat-item .stat-val { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-top: 2px; }
    .db-stat-item .stat-lbl { font-size: 0.72rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 2px; }

    .db-health {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eef0f5;
        padding: 22px 24px;
    }
    .db-health h5 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .db-health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 12px;
    }
    .db-health-item {
        background: #f8fafc;
        border-radius: 10px;
        padding: 14px;
        text-align: center;
    }
    .db-health-item .h-val { font-size: 1.3rem; font-weight: 700; color: #1e293b; }
    .db-health-item .h-val.danger { color: #f43f5e; }
    .db-health-item .h-val.warning { color: #f59e0b; }
    .db-health-item .h-val.success { color: #43d477; }
    .db-health-item .h-lbl { font-size: 0.72rem; color: #94a3b8; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.03em; }

    .db-feed-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .db-feed-item:last-child { border-bottom: none; }
    .db-feed-item .feed-avatar {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .db-feed-item .feed-info { flex: 1; min-width: 0; }
    .db-feed-item .feed-title {
        font-size: 0.85rem;
        font-weight: 500;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .db-feed-item .feed-meta { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
    .db-feed-item .feed-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        flex-shrink: 0;
    }
    .db-feed-item .feed-badge.active { background: #e8f8ef; color: #43d477; }
    .db-feed-item .feed-badge.pending { background: #fffbeb; color: #f59e0b; }
    .db-feed-item .feed-badge.draft { background: #f1f5f9; color: #64748b; }
    .db-feed-item .feed-badge.rejected { background: #fff1f2; color: #f43f5e; }
    .db-feed-item .feed-amount { font-size: 0.85rem; font-weight: 600; color: #1e293b; flex-shrink: 0; }

    .db-ticket-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f8fafc;
        text-decoration: none !important;
    }
    .db-ticket-item:last-child { border-bottom: none; }
    .db-ticket-item:hover .t-title { color: #43d477; }
    .db-ticket-item .t-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        margin-top: 6px;
        flex-shrink: 0;
    }
    .db-ticket-item .t-dot.open { background: #f59e0b; }
    .db-ticket-item .t-dot.closed { background: #e2e8f0; }
    .db-ticket-item .t-dot.replied { background: #10b981; }
    .db-ticket-item .t-title { font-size: 0.85rem; font-weight: 500; color: #334155; transition: color 0.15s; }
    .db-ticket-item .t-meta { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }

    .db-comment-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .db-comment-item:last-child { border-bottom: none; }
    .db-comment-item .c-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }
    .db-comment-item .c-name { font-size: 0.82rem; font-weight: 600; color: #334155; }
    .db-comment-item .c-date { font-size: 0.72rem; color: #94a3b8; }
    .db-comment-item .c-text { font-size: 0.8rem; color: #64748b; margin-top: 4px; line-height: 1.4; }

    .db-view-all {
        display: block;
        text-align: center;
        padding: 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #43d477;
        text-decoration: none;
        border-top: 1px solid #f1f5f9;
    }
    .db-view-all:hover { background: #f0fdf4; color: #1FB354; }

    .section { padding-top: 20px; }
    .db-section-gap { margin-bottom: 24px; }
</style>
@endpush

@section('content')
    <section class="section">

        {{-- Greeting Bar --}}
        <div class="db-section-gap">
            <div class="db-greeting">
                <div>
                    <h2>{{trans('admin/main.welcome')}}, {{ $authUser->full_name ?? '' }}!</h2>
                    <p>{{trans('admin/main.welcome_card_text')}}</p>
                </div>
                <div class="db-actions d-flex flex-wrap gap-2">
                    @can('admin_general_dashboard_quick_access_links')
                        <a href="{{ getAdminPanelUrl() }}/comments/webinars"><i class="far fa-comment"></i> {{trans('admin/main.comments')}}</a>
                        <a href="{{ getAdminPanelUrl() }}/supports"><i class="far fa-envelope"></i> {{trans('admin/main.tickets')}}</a>
                        <a href="{{ getAdminPanelUrl() }}/reports/webinars"><i class="fas fa-chart-bar"></i> {{trans('admin/main.reports')}}</a>
                    @endcan
                    @can('admin_clear_cache')
                        @include('admin.includes.delete_button',[
                            'url' => getAdminPanelUrl().'/clear-cache',
                            'btnClass' => 'db-cache-clear-btn',
                            'btnText' => trans('admin/main.clear_all_cache'),
                            'hideDefaultClass' => true
                        ])
                    @endcan
                </div>
            </div>
        </div>

        {{-- KPI Cards Row --}}
        <div class="row db-section-gap">
            @can('admin_general_dashboard_income_statistics')
                @if(!empty($getIncomeStatistics))
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="db-kpi">
                            <div class="db-kpi-icon green"><i class="fas fa-rupee-sign"></i></div>
                            <div class="db-kpi-label">{{trans('admin/main.total_incomes')}}</div>
                            <div class="db-kpi-value">{{ handlePrice($getIncomeStatistics['totalSales']) }}</div>
                            <div class="db-kpi-sub">
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ handlePrice($getIncomeStatistics['todaySales']) }}</div>
                                    <div class="lbl">{{trans('admin/main.today')}}</div>
                                </div>
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ handlePrice($getIncomeStatistics['monthSales']) }}</div>
                                    <div class="lbl">{{trans('admin/main.this_month')}}</div>
                                </div>
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ handlePrice($getIncomeStatistics['yearSales']) }}</div>
                                    <div class="lbl">{{trans('admin/main.this_year')}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan

            @can('admin_general_dashboard_total_sales_statistics')
                @if(!empty($getTotalSalesStatistics))
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="db-kpi">
                            <div class="db-kpi-icon blue"><i class="fas fa-shopping-bag"></i></div>
                            <div class="db-kpi-label">{{trans('admin/main.total_sales')}}</div>
                            <div class="db-kpi-value">{{ number_format($getTotalSalesStatistics['totalSales']) }}</div>
                            <div class="db-kpi-sub">
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ $getTotalSalesStatistics['todaySales'] }}</div>
                                    <div class="lbl">{{trans('admin/main.today')}}</div>
                                </div>
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ $getTotalSalesStatistics['monthSales'] }}</div>
                                    <div class="lbl">{{trans('admin/main.this_month')}}</div>
                                </div>
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ $getTotalSalesStatistics['yearSales'] }}</div>
                                    <div class="lbl">{{trans('admin/main.this_year')}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan

            @can('admin_general_dashboard_daily_sales_statistics')
                @if(!empty($dailySalesTypeStatistics))
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="db-kpi">
                            <div class="db-kpi-icon purple"><i class="fas fa-bolt"></i></div>
                            <div class="db-kpi-label">{{trans('admin/main.today_sales')}}</div>
                            <div class="db-kpi-value">{{ $dailySalesTypeStatistics['allSales'] }}</div>
                            <div class="db-kpi-sub">
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ $dailySalesTypeStatistics['webinarsSales'] }}</div>
                                    <div class="lbl">{{trans('admin/main.live_class')}}</div>
                                </div>
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ $dailySalesTypeStatistics['courseSales'] }}</div>
                                    <div class="lbl">{{trans('admin/main.course')}}</div>
                                </div>
                                <div class="db-kpi-sub-item">
                                    <div class="val">{{ $dailySalesTypeStatistics['appointmentSales'] }}</div>
                                    <div class="lbl">{{trans('admin/main.appointment')}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan
        </div>

        {{-- Quick Stats Row --}}
        <div class="row db-section-gap">
            @can('admin_general_dashboard_new_sales')
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <a href="{{ getAdminPanelUrl() }}/financial/sales" class="db-mini-card">
                        <div class="mc-icon blue"><i class="fas fa-shopping-cart"></i></div>
                        <div>
                            <div class="mc-val">{{ $getNewSalesCount }}</div>
                            <div class="mc-lbl">{{trans('admin/main.new_sale')}}</div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_general_dashboard_new_comments')
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <a href="{{ getAdminPanelUrl() }}/comments/webinars" class="db-mini-card">
                        <div class="mc-icon rose"><i class="fas fa-comment"></i></div>
                        <div>
                            <div class="mc-val">{{ $getNewCommentsCount }}</div>
                            <div class="mc-lbl">{{trans('admin/main.new_comment')}}</div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_general_dashboard_new_tickets')
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <a href="{{ getAdminPanelUrl() }}/supports" class="db-mini-card">
                        <div class="mc-icon amber"><i class="far fa-envelope"></i></div>
                        <div>
                            <div class="mc-val">{{ $getNewTicketsCount }}</div>
                            <div class="mc-lbl">{{trans('admin/main.new_ticket')}}</div>
                        </div>
                    </a>
                </div>
            @endcan

            @can('admin_general_dashboard_new_reviews')
                <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                    <a class="db-mini-card">
                        <div class="mc-icon green"><i class="fas fa-eye"></i></div>
                        <div>
                            <div class="mc-val">{{ $getPendingReviewCount }}</div>
                            <div class="mc-lbl">{{trans('admin/main.pending_review_classes')}}</div>
                        </div>
                    </a>
                </div>
            @endcan
        </div>

        {{-- Revenue Chart + Installment Health --}}
        <div class="row db-section-gap">
            @can('admin_general_dashboard_sales_statistics_chart')
                <div class="col-lg-8 col-md-12 mb-3">
                    <div class="db-card h-100">
                        <div class="db-card-head">
                            <h4>{{trans('admin/main.sales_statistics')}}</h4>
                            <div class="db-chart-toggle">
                                <div class="btn-group">
                                    <button type="button" class="js-sale-chart-month btn">{{trans('admin/main.month')}}</button>
                                    <button type="button" class="js-sale-chart-year btn btn-primary">{{trans('admin/main.year')}}</button>
                                </div>
                            </div>
                        </div>
                        <div class="db-card-body">
                            <canvas id="saleStatisticsChart" height="220"></canvas>

                            @if(!empty($getMonthAndYearSalesChartStatistics))
                                <div class="db-stat-row">
                                    <div class="db-stat-item">
                                        <span class="grow {{ $getMonthAndYearSalesChartStatistics['todaySales']['grow_percent']['status'] }}">
                                            <i class="fas fa-caret-{{ $getMonthAndYearSalesChartStatistics['todaySales']['grow_percent']['status'] }}"></i>
                                            {{ $getMonthAndYearSalesChartStatistics['todaySales']['grow_percent']['percent'] }}
                                        </span>
                                        <div class="stat-val">{{ handlePrice($getMonthAndYearSalesChartStatistics['todaySales']['amount']) }}</div>
                                        <div class="stat-lbl">{{trans('admin/main.today_sales')}}</div>
                                    </div>
                                    <div class="db-stat-item">
                                        <span class="grow {{ $getMonthAndYearSalesChartStatistics['weekSales']['grow_percent']['status'] }}">
                                            <i class="fas fa-caret-{{ $getMonthAndYearSalesChartStatistics['weekSales']['grow_percent']['status'] }}"></i>
                                            {{ $getMonthAndYearSalesChartStatistics['weekSales']['grow_percent']['percent'] }}
                                        </span>
                                        <div class="stat-val">{{ handlePrice($getMonthAndYearSalesChartStatistics['weekSales']['amount']) }}</div>
                                        <div class="stat-lbl">{{trans('admin/main.week_sales')}}</div>
                                    </div>
                                    <div class="db-stat-item">
                                        <span class="grow {{ $getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['status'] }}">
                                            <i class="fas fa-caret-{{ $getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['status'] }}"></i>
                                            {{ $getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['percent'] }}
                                        </span>
                                        <div class="stat-val">{{ handlePrice($getMonthAndYearSalesChartStatistics['monthSales']['amount']) }}</div>
                                        <div class="stat-lbl">{{trans('admin/main.month_sales')}}</div>
                                    </div>
                                    <div class="db-stat-item">
                                        <span class="grow {{ $getMonthAndYearSalesChartStatistics['yearSales']['grow_percent']['status'] }}">
                                            <i class="fas fa-caret-{{ $getMonthAndYearSalesChartStatistics['yearSales']['grow_percent']['status'] }}"></i>
                                            {{ $getMonthAndYearSalesChartStatistics['yearSales']['grow_percent']['percent'] }}
                                        </span>
                                        <div class="stat-val">{{ handlePrice($getMonthAndYearSalesChartStatistics['yearSales']['amount']) }}</div>
                                        <div class="stat-lbl">{{trans('admin/main.year_sales')}}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endcan

            <div class="col-lg-4 col-md-12 mb-3">
                {{-- Installment Health --}}
                @if(!empty($installmentHealth))
                    <div class="db-health mb-3">
                        <h5><i class="fas fa-heartbeat" style="color:#f43f5e"></i> Installment Health</h5>
                        <div class="db-health-grid">
                            <div class="db-health-item">
                                <div class="h-val {{ $installmentHealth['overdueCount'] > 0 ? 'danger' : 'success' }}">{{ $installmentHealth['overdueCount'] }}</div>
                                <div class="h-lbl">Overdue</div>
                            </div>
                            <div class="db-health-item">
                                <div class="h-val warning">{{ $installmentHealth['upcomingDueCount'] }}</div>
                                <div class="h-lbl">Due 7 Days</div>
                            </div>
                            <div class="db-health-item">
                                <div class="h-val">{{ $installmentHealth['activePlans'] }}</div>
                                <div class="h-lbl">Active Plans</div>
                            </div>
                            <div class="db-health-item">
                                <div class="h-val success">{{ $installmentHealth['completedPlans'] }}</div>
                                <div class="h-lbl">Completed</div>
                            </div>
                        </div>
                        @if($installmentHealth['overdueAmount'] > 0)
                            <div style="margin-top:14px; padding:10px 14px; background:#fff5f5; border-radius:8px; border:1px solid #fecaca;">
                                <span style="font-size:0.78rem; color:#f43f5e; font-weight:600;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ handlePrice($installmentHealth['overdueAmount']) }} overdue
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Recent Sales Feed --}}
                @if(!empty($recentSales) && $recentSales->count() > 0)
                    <div class="db-card">
                        <div class="db-card-head">
                            <h4>Recent Enrollments</h4>
                        </div>
                        <div class="db-card-body" style="padding: 8px 24px;">
                            @foreach($recentSales as $sale)
                                <div class="db-feed-item">
                                    <div class="feed-avatar" style="background:{{ ['#e8f8ef','#eef4fb','#fff7ed','#ecfdf5','#f0fdf4'][($loop->index % 5)] }}; color:{{ ['#43d477','#1f3b64','#f97316','#10b981','#22c55e'][($loop->index % 5)] }};">
                                        <i class="fas fa-{{ $sale->product && $sale->product->product_type == 'webinar' ? 'video' : ($sale->product && $sale->product->product_type == 'bundle' ? 'layer-group' : 'play-circle') }}"></i>
                                    </div>
                                    <div class="feed-info">
                                        <div class="feed-title">{{ $sale->user->full_name ?? 'User' }}</div>
                                        <div class="feed-meta">{{ $sale->product->name ?? 'Product' }} &middot; {{ $sale->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="feed-badge {{ $sale->status }}">{{ ucfirst(str_replace('_', ' ', $sale->status)) }}</div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ getAdminPanelUrl() }}/financial/sales" class="db-view-all">View all sales <i class="fas fa-arrow-right" style="font-size:0.7rem"></i></a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Comments + Tickets + Content --}}
        <div class="row db-section-gap">
            @can('admin_general_dashboard_recent_comments')
                @if(!empty($recentComments))
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="db-card h-100 d-flex flex-column">
                            <div class="db-card-head">
                                <h4>{{trans('admin/main.recent_comments')}}</h4>
                            </div>
                            <div class="db-card-body flex-grow-1" style="padding: 8px 24px;">
                                @foreach($recentComments as $recentComment)
                                    <div class="db-comment-item">
                                        <img class="c-avatar" src="{{ $recentComment->user->getAvatar() }}" alt="">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="c-name">{{ $recentComment->user->full_name ?? '' }}</span>
                                                <span class="c-date">{{ dateTimeFormat($recentComment->created_at, 'j M Y') }}</span>
                                            </div>
                                            <div class="c-text">{{ truncate($recentComment->comment, 100) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ getAdminPanelUrl() }}/comments/webinars" class="db-view-all">{{trans('admin/main.view_all')}} <i class="fas fa-arrow-right" style="font-size:0.7rem"></i></a>
                        </div>
                    </div>
                @endif
            @endcan

            @can('admin_general_dashboard_recent_tickets')
                @if(!empty($recentTickets))
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="db-card h-100 d-flex flex-column">
                            <div class="db-card-head">
                                <h4>{{trans('admin/main.recent_tickets')}}</h4>
                                <span style="font-size:0.75rem; background:#fffbeb; color:#f59e0b; padding:3px 10px; border-radius:20px; font-weight:600;">{{ $recentTickets['pendingReply'] }} {{ trans('admin/main.pending_reply') }}</span>
                            </div>
                            <div class="db-card-body flex-grow-1" style="padding: 8px 24px;">
                                @foreach($recentTickets['tickets'] as $ticket)
                                    <a href="{{ getAdminPanelUrl() }}/supports/{{ $ticket->id }}/conversation" class="db-ticket-item">
                                        <div class="t-dot {{ $ticket->status == 'close' ? 'closed' : ($ticket->status == 'supporter_replied' ? 'replied' : 'open') }}"></div>
                                        <div>
                                            <div class="t-title">{{ $ticket->title }}</div>
                                            <div class="t-meta">
                                                {{ $ticket->user->full_name ?? '' }}
                                                &middot;
                                                @if($ticket->status == 'replied' or $ticket->status == 'open')
                                                    {{ trans('admin/main.pending_reply') }}
                                                @elseif($ticket->status == 'close')
                                                    {{ trans('admin/main.close') }}
                                                @else
                                                    {{ trans('admin/main.replied') }}
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ getAdminPanelUrl() }}/supports" class="db-view-all">{{trans('admin/main.view_all')}} <i class="fas fa-arrow-right" style="font-size:0.7rem"></i></a>
                        </div>
                    </div>
                @endif
            @endcan

            <div class="col-lg-4 col-md-12 mb-3">
                @can('admin_general_dashboard_recent_webinars')
                    @if(!empty($recentWebinars))
                        <div class="db-card mb-3">
                            <div class="db-card-head">
                                <h4>{{trans('admin/main.recent_live_classes')}}</h4>
                                @if($recentWebinars['pendingReviews'] > 0)
                                    <span style="font-size:0.75rem; background:#fff7ed; color:#f97316; padding:3px 10px; border-radius:20px; font-weight:600;">{{ $recentWebinars['pendingReviews'] }} {{trans('admin/main.pending_review')}}</span>
                                @endif
                            </div>
                            <div class="db-card-body" style="padding: 4px 24px;">
                                @foreach($recentWebinars['webinars']->take(3) as $webinar)
                                    <div class="db-feed-item">
                                        <div class="feed-avatar" style="background:#e8f8ef; color:#43d477;">
                                            <i class="fas fa-video"></i>
                                        </div>
                                        <div class="feed-info">
                                            <a href="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/edit" class="feed-title" style="color:#334155; text-decoration:none;">{{ $webinar->title }}</a>
                                            <div class="feed-meta">{{ $webinar->teacher->full_name ?? '' }}</div>
                                        </div>
                                        @php
                                            $wStatus = $webinar->status;
                                            $badgeClass = $wStatus == \App\Models\Webinar::$active ? 'active' : ($wStatus == \App\Models\Webinar::$pending ? 'pending' : ($wStatus == \App\Models\Webinar::$inactive ? 'rejected' : 'draft'));
                                        @endphp
                                        <span class="feed-badge {{ $badgeClass }}">{{ ucfirst($wStatus) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ getAdminPanelUrl() }}/webinars?type=webinar" class="db-view-all">{{trans('admin/main.view_all')}} <i class="fas fa-arrow-right" style="font-size:0.7rem"></i></a>
                        </div>
                    @endif
                @endcan

                @can('admin_general_dashboard_recent_courses')
                    @if(!empty($recentCourses))
                        <div class="db-card">
                            <div class="db-card-head">
                                <h4>{{trans('admin/main.recent_courses')}}</h4>
                                @if($recentCourses['pendingReviews'] > 0)
                                    <span style="font-size:0.75rem; background:#fdf2f8; color:#ec4899; padding:3px 10px; border-radius:20px; font-weight:600;">{{ $recentCourses['pendingReviews'] }} {{trans('admin/main.pending_review')}}</span>
                                @endif
                            </div>
                            <div class="db-card-body" style="padding: 4px 24px;">
                                @foreach($recentCourses['courses']->take(3) as $course)
                                    <div class="db-feed-item">
                                        <div class="feed-avatar" style="background:#ecfdf5; color:#10b981;">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                        <div class="feed-info">
                                            <a href="{{ getAdminPanelUrl() }}/webinars/{{ $course->id }}/edit" class="feed-title" style="color:#334155; text-decoration:none;">{{ $course->title }}</a>
                                            <div class="feed-meta">{{ $course->teacher->full_name ?? '' }}</div>
                                        </div>
                                        @php
                                            $cStatus = $course->status;
                                            $badgeClass = $cStatus == \App\Models\Webinar::$active ? 'active' : ($cStatus == \App\Models\Webinar::$pending ? 'pending' : ($cStatus == \App\Models\Webinar::$inactive ? 'rejected' : 'draft'));
                                        @endphp
                                        <span class="feed-badge {{ $badgeClass }}">{{ ucfirst($cStatus) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ getAdminPanelUrl() }}/webinars?type=course" class="db-view-all">{{trans('admin/main.view_all')}} <i class="fas fa-arrow-right" style="font-size:0.7rem"></i></a>
                        </div>
                    @endif
                @endcan
            </div>
        </div>

        {{-- User Registration Chart --}}
        @can('admin_general_dashboard_users_statistics_chart')
            <div class="row db-section-gap">
                <div class="col-12">
                    <div class="db-card">
                        <div class="db-card-head">
                            <h4>{{trans('admin/main.new_registration_statistics')}}</h4>
                        </div>
                        <div class="db-card-body">
                            <canvas id="usersStatisticsChart" height="80"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>
    <script src="/assets/admin/vendor/owl.carousel/owl.carousel.min.js"></script>

    <script src="/assets/admin/js/dashboard.min.js"></script>

    <script>
        (function ($) {
            "use strict";

            @if(!empty($getMonthAndYearSalesChart))
            makeStatisticsChart('saleStatisticsChart', saleStatisticsChart, 'Sale', @json($getMonthAndYearSalesChart['labels']),@json($getMonthAndYearSalesChart['data']));
            @endif

            @if(!empty($usersStatisticsChart))
            makeStatisticsChart('usersStatisticsChart', usersStatisticsChart, 'Users', @json($usersStatisticsChart['labels']),@json($usersStatisticsChart['data']));
            @endif

        })(jQuery)
    </script>
@endpush
