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
            font-size: 20px;
            font-weight: 800;
            color: #1f3b64;
            display: block;
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.1); color: #43d477; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-glass-info { background: rgba(0, 123, 255, 0.1); color: #007bff; }
        .bg-glass-secondary { background: rgba(108, 117, 125, 0.1); color: #6c757d; }

        /* Custom Badge Styles for visibility */
        .type-badge {
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
            text-align: center;
            min-width: 100px;
        }
        .badge-course { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .badge-part { background: rgba(67, 212, 119, 0.1); color: #36b363; }
        .badge-meeting { background: rgba(0, 123, 255, 0.1); color: #007bff; }
        .badge-subscription { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
        .badge-bundle { background: rgba(103, 58, 183, 0.1); color: #673ab7; }
        .badge-product { background: rgba(96, 125, 139, 0.1); color: #607d8b; }

        .btn-filter {
            background-color: #43d477;
            border-color: #43d477;
            color: #fff;
            font-weight: 700;
            border-radius: 10px;
            height: 45px;
            padding: 0 25px;
            transition: all 0.3s ease;
        }
        .btn-filter:hover {
            background-color: #36b363;
            border-color: #36b363;
            color: #fff;
            box-shadow: 0 5px 15px rgba(67, 212, 119, 0.3);
        }
    </style>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <h2 class="section-title">Financial Summary</h2>
        <div class="mt-25">
            <div class="row">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="video" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $totalCourseCount }}</span>
                            <span class="stat-label">Total Courses</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 mt-15 mt-sm-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="users" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $totalMeetingCount }}</span>
                            <span class="stat-label">Total Meetings</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 mt-15 mt-lg-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="repeat" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $totalSubscriptionCount }}</span>
                            <span class="stat-label">Total Subscriptions</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 mt-15 mt-lg-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-info">
                            <i data-feather="shopping-cart" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $totalProductCount }}</span>
                            <span class="stat-label">Total Products</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <h2 class="section-title">Filter Documents</h2>
        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/financial/summary" method="get">
                <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">

                    {{-- From --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.from') }}
                        </label>
                        <div style="position:relative;width:150px;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="from" autocomplete="off" class="form-control datefilter"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('from') }}"/>
                        </div>
                    </div>

                    {{-- To --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.to') }}
                        </label>
                        <div style="position:relative;width:150px;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="to" autocomplete="off" class="form-control datefilter"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('to') }}"/>
                        </div>
                    </div>

                    {{-- Type --}}
                    <div style="flex:1 1 180px; min-width: 151px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="layers" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> Type
                        </label>
                        <div style="position:relative;">
                            <select name="type" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="all">All</option>
                                <option value="course" {{ request()->get('type') == 'course' ? 'selected' : '' }}>Course</option>
                                <option value="meeting" {{ request()->get('type') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="subscription" {{ request()->get('type') == 'subscription' ? 'selected' : '' }}>Subscription</option>
                                <option value="product" {{ request()->get('type') == 'product' ? 'selected' : '' }}>Product</option>
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
                            Show Results
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <section class="mt-35">
        <h2 class="section-title">Financial Documents List</h2>

        @if(!empty($amount_paid) and count($amount_paid) > 0)
            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-left font-weight-bold">{{ trans('public.title') }}</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th class="text-center">{{ trans('admin/main.actions') }}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($amount_paid as $item)
                                    <tr>
                                        <td><span class="font-weight-bold">#{{ $item[3] }}</span></td>
                                        <td class="text-left align-middle">
                                            <span class="font-weight-500">{{ $item[2] }}</span>
                                        </td>
                                        <td>
                                            @if($item[5] == 'part')
                                                <span class="type-badge badge-part">Installment Part</span>
                                            @elseif($item[5] == 'course')
                                                <span class="type-badge badge-course">{{ ($item[6] == 'installment_payment') ? 'Installment' : 'Course' }}</span>
                                            @elseif($item[5] == 'meeting')
                                                <span class="type-badge badge-meeting">Meeting</span>
                                            @elseif($item[5] == 'subscription')
                                                <span class="type-badge badge-subscription">Subscription</span>
                                            @elseif($item[5] == 'bundle')
                                                <span class="type-badge badge-bundle">Bundle</span>
                                            @elseif($item[5] == 'product')
                                                <span class="type-badge badge-product">Product</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="font-16 font-weight-bold text-primary">{{ handlePrice($item[0], false) }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span>{{ is_numeric($item[1]) ? dateTimeFormat($item[1], 'j M Y') : $item[1] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $invoiceUrl = '';
                                                if($item[5] == 'part') $invoiceUrl = "/panel/webinars/{$item[4]}/part/{$item[3]}/invoice";
                                                elseif($item[5] == 'course') $invoiceUrl = "/panel/webinars/{$item[4]}/sale/{$item[3]}/invoice";
                                                elseif($item[5] == 'meeting') $invoiceUrl = "/panel/webinars/{$item[4]}/meeting/{$item[3]}/invoice";
                                                elseif($item[5] == 'subscription') $invoiceUrl = "/panel/webinars/{$item[4]}/subscription/{$item[3]}/invoice";
                                                elseif($item[5] == 'bundle') $invoiceUrl = "/panel/webinars/{$item[4]}/bundle/{$item[3]}/invoice";
                                            @endphp

                                            @if($invoiceUrl)
                                                <a href="{{ $invoiceUrl }}" target="_blank" class="text-gray" title="{{ trans('admin/main.invoice') }}">
                                                    <i data-feather="printer" width="20"></i>
                                                </a>

                                               
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
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'financial.png',
                'title' => trans('financial.financial_summary_no_result'),
                'hint' => nl2br(trans('financial.financial_summary_no_result_hint')),
            ])
        @endif

        <div class="my-30">
            {{ $accountings->appends(request()->input())->links('vendor.pagination.panel') }}
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/moment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
@endpush
