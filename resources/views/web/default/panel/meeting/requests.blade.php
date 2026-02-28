@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .custom-table thead th {
            border-top: none;
            background-color: #f8faff;
            color: #1f3b64;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            padding: 14px 8px;
            border-bottom: 2px solid #f0f0f0;
            white-space: nowrap;
        }
        .custom-table tbody td {
            padding: 14px 8px;
            vertical-align: middle;
            color: #1f3b64;
            border-bottom: 1px solid #f9f9f9;
            font-size: 13px;
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.12) !important; color: #1f3b64 !important; }
        .bg-glass-success { background: rgba(40, 167, 69, 0.15) !important; color: #1e7e34 !important; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.15) !important; color: #9b7400 !important; }
        .bg-glass-danger { background: rgba(220, 53, 69, 0.12) !important; color: #bd2130 !important; }
        .bg-glass-gray { background: rgba(108, 117, 125, 0.12) !important; color: #495057 !important; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 10px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .meeting-table-wrapper {
            overflow: hidden;
        }
        .meeting-table-wrapper .custom-table {
            width: 100%;
        }
        .custom-table .student-cell {
            max-width: 180px;
        }
        .custom-table .student-cell .student-email {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
            display: block;
        }

        /* Stats Cards */
        .stat-card-premium {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            border: 1px solid #e8edf5;
            box-shadow: 0 4px 24px rgba(31, 59, 100, 0.04);
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        .stat-card-premium:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(31, 59, 100, 0.1);
            border-color: #d1d9e6;
        }
        .stat-icon-wrapper {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .stat-info .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #1f3b64;
            display: block;
            line-height: 1.2;
        }
        .stat-info .stat-label {
            font-size: 13px;
            color: #6a737d;
            font-weight: 500;
            margin-top: 4px;
            display: block;
        }

        /* Filter refinements */
        .filter-container-premium {
            background: linear-gradient(135deg, #f8faff 0%, #fff 100%);
            border-radius: 20px;
            border: 1px solid #e8edf5;
            padding: 28px 30px;
            box-shadow: 0 4px 24px rgba(31, 59, 100, 0.04);
        }
        .filter-label-premium {
            font-size: 10px;
            font-weight: 700;
            color: #8c98a4;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 6px;
            display: block;
        }
        .filter-label-premium i, .filter-label-premium svg {
            vertical-align: middle;
            margin-right: 3px;
        }
        .filter-input-premium {
            height: 42px;
            border-radius: 10px;
            border: 1.5px solid #e8edf5;
            font-size: 12px;
            font-weight: 600;
            color: #1f3b64;
            padding: 0 14px;
            transition: all 0.2s;
            background: #fff;
            box-shadow: 0 2px 6px rgba(31, 59, 100, 0.04);
        }
        .filter-input-premium:focus {
            border-color: #43d477;
            box-shadow: 0 0 0 3px rgba(67, 212, 119, 0.1);
        }
        .btn-filter-premium {
            height: 42px;
            background: linear-gradient(135deg, #43d477 0%, #2ecc71 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(67, 212, 119, 0.3);
            transition: all 0.3s;
        }
        .btn-filter-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 212, 119, 0.4);
            color: #fff;
        }
        .btn-reset-premium {
            height: 42px;
            background: #fff;
            border: 1.5px solid #e8edf5;
            border-radius: 10px;
            color: #6a737d;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-reset-premium:hover {
            border-color: #dc3545;
            color: #dc3545;
            background: rgba(220, 53, 69, 0.04);
        }
        .filter-active-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }
        .filter-tag {
            display: inline-flex;
            align-items: center;
            background: rgba(67, 212, 119, 0.1);
            color: #1e7e34;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .filter-tag i, .filter-tag svg {
            margin-right: 5px;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('panel.meeting_statistics') }}</h2>

        <div class="row mt-25">
            <div class="col-12 col-sm-6 col-md-3 mb-20 mb-md-0">
                <div class="stat-card-premium">
                    <div class="stat-icon-wrapper" style="background: rgba(255, 159, 67, 0.1);">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/49.svg" width="22" height="22" alt="">
                    </div>
                    <div class="stat-info">
                        <strong class="stat-value">{{ $pendingReserveCount }}</strong>
                        <span class="stat-label">{{ trans('panel.pending_appointments') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3 mb-20 mb-md-0">
                <div class="stat-card-premium">
                    <div class="stat-icon-wrapper" style="background: rgba(31, 59, 100, 0.08);">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/50.svg" width="22" height="22" alt="">
                    </div>
                    <div class="stat-info">
                        <strong class="stat-value">{{ $totalReserveCount }}</strong>
                        <span class="stat-label">{{ trans('panel.total_meetings') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3 mb-20 mb-sm-0">
                <div class="stat-card-premium">
                    <div class="stat-icon-wrapper" style="background: rgba(46, 204, 113, 0.1);">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/38.svg" width="22" height="22" alt="">
                    </div>
                    <div class="stat-info">
                        <strong class="stat-value">{{ handlePrice($sumReservePaid) }}</strong>
                        <span class="stat-label">{{ trans('panel.sales_amount') }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <div class="stat-card-premium">
                    <div class="stat-icon-wrapper" style="background: rgba(52, 152, 219, 0.1);">
                        <img src="{{ config('app.js_css_url') }}/assets/default/img/activity/hours.svg" width="22" height="22" alt="">
                    </div>
                    <div class="stat-info">
                        <strong class="stat-value">{{ convertMinutesToHourAndMinute($activeHoursCount / 60) }}</strong>
                        <span class="stat-label">{{ trans('panel.active_hours') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25 panel-filter-section">
        <h2 class="section-title">{{ trans('panel.filter_meetings') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/meetings/requests" method="get" id="meetingFilterForm">

                {{-- Row 1: From + To + Day + Student --}}
                <div class="row align-items-end">
                    <div class="col-12 col-sm-6 col-md-3 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.from') }}
                        </label>
                        <input type="text" name="from" autocomplete="off"
                               class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                               style="height:40px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                               placeholder="Start date"
                               value="{{ request()->get('from','') }}"/>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.to') }}
                        </label>
                        <input type="text" name="to" autocomplete="off"
                               class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                               style="height:40px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                               placeholder="End date"
                               value="{{ request()->get('to','') }}"/>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sun" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.day') }}
                        </label>
                        <select name="day" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="all">{{ trans('public.all_days') }}</option>
                            <option value="saturday" {{ request()->get('day') === 'saturday' ? 'selected' : '' }}>{{ trans('public.saturday') }}</option>
                            <option value="sunday" {{ request()->get('day') === 'sunday' ? 'selected' : '' }}>{{ trans('public.sunday') }}</option>
                            <option value="monday" {{ request()->get('day') === 'monday' ? 'selected' : '' }}>{{ trans('public.monday') }}</option>
                            <option value="tuesday" {{ request()->get('day') === 'tuesday' ? 'selected' : '' }}>{{ trans('public.tuesday') }}</option>
                            <option value="wednesday" {{ request()->get('day') === 'wednesday' ? 'selected' : '' }}>{{ trans('public.wednesday') }}</option>
                            <option value="thursday" {{ request()->get('day') === 'thursday' ? 'selected' : '' }}>{{ trans('public.thursday') }}</option>
                            <option value="friday" {{ request()->get('day') === 'friday' ? 'selected' : '' }}>{{ trans('public.friday') }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('quiz.student') }}
                        </label>
                        <select name="student_id" class="form-control select2" style="width:100%;">
                            <option value="all">{{ trans('webinars.all_students') }}</option>
                            @foreach($usersReservedTimes as $student)
                                <option value="{{ $student->id }}" @if(request()->get('student_id') == $student->id) selected @endif>{{ $student->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2: Meeting Type + Status + Sort + Buttons --}}
                <div class="row align-items-end">
                    <div class="col-6 col-sm-4 col-md-2 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="video" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> Meeting Type
                        </label>
                        <select name="meeting_type" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="">All Types</option>
                            <option value="online" {{ request()->get('meeting_type') === 'online' ? 'selected' : '' }}>Online</option>
                            <option value="in_person" {{ request()->get('meeting_type') === 'in_person' ? 'selected' : '' }}>In Person</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <select name="status" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="">{{ trans('public.all') }}</option>
                            <option value="pending" {{ request()->get('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="open" {{ request()->get('status') === 'open' ? 'selected' : '' }}>{{ trans('public.open') }}</option>
                            <option value="finished" {{ request()->get('status') === 'finished' ? 'selected' : '' }}>{{ trans('public.finished') }}</option>
                            <option value="canceled" {{ request()->get('status') === 'canceled' ? 'selected' : '' }}>Canceled</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-4 col-md-2 mb-15">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="bar-chart-2" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> Sort By
                        </label>
                        <select name="sort" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="newest" {{ request()->get('sort', 'newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request()->get('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="amount_high" {{ request()->get('sort') === 'amount_high' ? 'selected' : '' }}>Amount: High→Low</option>
                            <option value="amount_low" {{ request()->get('sort') === 'amount_low' ? 'selected' : '' }}>Amount: Low→High</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-6 col-md-3 mb-15">
                        <button type="submit" style="height:40px;width:100%;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;cursor:pointer;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                            <i data-feather="search" width="13" height="13"></i>
                            {{ trans('public.show_results') }}
                        </button>
                    </div>
                    <div class="col-6 col-sm-6 col-md-3 mb-15">
                        <a href="/panel/meetings/requests" style="height:40px;width:100%;background:#fff;border:1.5px solid #e8edf5;border-radius:9px;color:#6a737d;font-size:13px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;transition:all .2s;cursor:pointer;" onmouseover="this.style.borderColor='#dc3545';this.style.color='#dc3545'" onmouseout="this.style.borderColor='#e8edf5';this.style.color='#6a737d'">
                            <i data-feather="x" width="13" height="13"></i>
                            Reset All
                        </a>
                    </div>
                </div>

                {{-- Active filter tags --}}
                @php
                    $activeFilters = collect(request()->all())->filter(function($v, $k) {
                        return !empty($v) && $v !== 'all' && $k !== 'page';
                    });
                @endphp
                @if($activeFilters->isNotEmpty())
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
                        @foreach($activeFilters as $key => $value)
                            <span style="display:inline-flex;align-items:center;background:rgba(67,212,119,0.1);color:#1e7e34;padding:5px 14px;border-radius:20px;font-size:11px;font-weight:600;">
                                <i data-feather="check" width="11" height="11" style="margin-right:5px;"></i>
                                {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>
    </section>

    <section class="mt-35 pb-50 mb-50">
        <form action="/panel/meetings/requests?{{ http_build_query(request()->all()) }}" method="get" class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('panel.meeting_requests_list') }}</h2>

            <!-- <div class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                <label class="cursor-pointer mb-0 mr-10 text-gray font-14 font-weight-500" for="openMeetingResult">{{ trans('panel.show_only_open_meetings') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="open_meetings" {{ (request()->get('open_meetings', '') == 'on') ? 'checked' : '' }} class="js-panel-list-switch-filter custom-control-input" id="openMeetingResult">
                    <label class="custom-control-label" for="openMeetingResult"></label>
                </div>
            </div> -->
        </form>

        @if($reserveMeetings->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20" style="background: #fff; border-radius: 20px; border: 1px solid #e8edf5; box-shadow: 0 4px 24px rgba(31, 59, 100, 0.04);">
                <div class="row">
                    <div class="col-12">
                        <div class="meeting-table-wrapper">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class="text-left" style="background: #f8faff; border-radius: 12px 0 0 0;">{{ trans('quiz.student') }}</th>
                                    <th class="text-center" style="background: #f8faff;">Type</th>
                                    <th class="text-center" style="background: #f8faff;">{{ trans('public.date') }}</th>
                                    <th class="text-center" style="background: #f8faff;">{{ trans('public.time') }}</th>
                                    <th class="text-center" style="background: #f8faff;">Paid</th>
                                    <th class="text-center" style="background: #f8faff;">#</th>
                                    <th class="text-center" style="background: #f8faff;">{{ trans('public.status') }}</th>
                                    <th class="text-center" style="background: #f8faff; border-radius: 0 12px 0 0;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($reserveMeetings as $ReserveMeeting)
                                    <tr>
                                        <td class="text-left student-cell">
                                            <div class="d-flex align-items-center">
                                                @if(!empty($ReserveMeeting->user))
                                                    <div class="avatar bg-gray200" style="width:32px;height:32px;min-width:32px;border-radius:50%;overflow:hidden;">
                                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $ReserveMeeting->user->getAvatar() }}" class="img-cover" alt="" style="width:100%;height:100%;object-fit:cover;">
                                                    </div>
                                                    <div class="ml-5" style="min-width:0;">
                                                        <span class="d-block font-weight-500" style="font-size:13px;">{{ $ReserveMeeting->user->full_name }}</span>
                                                        <span class="student-email text-gray" style="font-size:11px;">{{ $ReserveMeeting->user->email }}</span>
                                                    </div>
                                                @else
                                                    <div class="avatar bg-gray200" style="width:32px;height:32px;min-width:32px;border-radius:50%;overflow:hidden;">
                                                        <img loading="lazy" src="/assets/default/img/avatar.png" class="img-cover" alt="" style="width:100%;height:100%;object-fit:cover;">
                                                    </div>
                                                    <div class="ml-5">
                                                        <span class="d-block font-weight-500 text-danger" style="font-size:13px;">{{ trans('update.deleted_user') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span style="font-size:12px;">{{ trans('update.'.$ReserveMeeting->meeting_type) }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div style="font-size:12px;font-weight:600;">{{ dateTimeFormat($ReserveMeeting->start_at, 'D') }}</div>
                                            <div style="font-size:11px;color:#6a737d;" class="text-nowrap">{{ dateTimeFormat($ReserveMeeting->start_at, 'j M Y') }}</div>
                                        </td>
                                        <td class="align-middle text-center text-nowrap">
                                            <span class="rounded bg-gray200" style="padding:4px 8px;font-size:12px;font-weight:500;">{{ dateTimeFormat($ReserveMeeting->start_at, 'H:i') }}-{{ dateTimeFormat($ReserveMeeting->end_at, 'H:i') }}</span>
                                        </td>
                                        <td class="align-middle text-center" style="font-weight:700;font-size:13px;">{{ handlePrice($ReserveMeeting->paid_amount) }}</td>
                                        <td class="align-middle text-center" style="font-weight:600;color:#6a737d;font-size:13px;">
                                            {{ $ReserveMeeting->student_count ?? 1 }}
                                        </td>

                                        <td class="align-middle text-center">
                                            @php
                                                $statusClass = 'gray';
                                                switch($ReserveMeeting->status) {
                                                    case \App\Models\ReserveMeeting::$pending: $statusClass = 'warning'; break;
                                                    case \App\Models\ReserveMeeting::$open: $statusClass = 'primary'; break;
                                                    case \App\Models\ReserveMeeting::$finished: $statusClass = 'success'; break;
                                                    case \App\Models\ReserveMeeting::$canceled: $statusClass = 'danger'; break;
                                                }
                                            @endphp
                                            <span class="status-badge bg-glass-{{ $statusClass }}">
                                                {{ trans('public.'.$ReserveMeeting->status) }}
                                            </span>
                                        </td>

                                        <td class="align-middle text-right">
                                            @if($ReserveMeeting->status != \App\Models\ReserveMeeting::$finished)
                                                <input type="hidden" class="js-meeting-password-{{ $ReserveMeeting->id }}" value="{{ $ReserveMeeting->password }}">
                                                <input type="hidden" class="js-meeting-link-{{ $ReserveMeeting->id }}" value="{{ $ReserveMeeting->link }}">

                                                <div class="btn-group dropdown table-actions">
                                                    <button type="button" class="btn-transparent dropdown-toggle"
                                                            data-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                        <i data-feather="more-vertical" height="20"></i>
                                                    </button>
                                                    <div class="dropdown-menu menu-lg">

                                                        @if(getFeaturesSettings('agora_for_meeting') and $ReserveMeeting->meeting_type != 'in_person')
                                                            @if(empty($ReserveMeeting->session))
                                                                <button type="button" data-item-id="{{ $ReserveMeeting->id }}" data-date="{{ dateTimeFormat($ReserveMeeting->start_at, 'j M Y H:i') }}"
                                                                        class="js-add-meeting-session btn-transparent webinar-actions d-block mt-10 text-primary">{{ trans('update.create_a_session') }}</button>
                                                            @elseif($ReserveMeeting->status == \App\Models\ReserveMeeting::$open)
                                                                <button type="button" data-item-id="{{ $ReserveMeeting->id }}" data-date="{{ dateTimeFormat($ReserveMeeting->start_at, 'j M Y H:i') }}" data-link="{{ $ReserveMeeting->session->getJoinLink() }}"
                                                                        class="js-join-meeting-session btn-transparent webinar-actions d-block mt-10 text-primary">{{ trans('update.join_to_session') }}</button>
                                                            @endif
                                                        @endif

                                                        @if($ReserveMeeting->meeting_type != 'in_person' and !empty($ReserveMeeting->link) and $ReserveMeeting->status == \App\Models\ReserveMeeting::$open)
                                                            <button type="button" data-reserve-id="{{ $ReserveMeeting->id }}"
                                                                    class="js-join-reserve btn-transparent webinar-actions d-block mt-10">{{ trans('footer.join') }}</button>
                                                        @endif

                                                        @if($ReserveMeeting->meeting_type != 'in_person')
                                                            <button type="button" data-item-id="{{ $ReserveMeeting->id }}"
                                                                    class="add-meeting-url btn-transparent webinar-actions d-block mt-10">{{ trans('panel.create_link') }}</button>
                                                        @endif

                                                        <a href="{{ $ReserveMeeting->addToCalendarLink() }}" target="_blank" class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('public.add_to_calendar') }}</a>

                                                        <button type="button"
                                                                data-user-id="{{ $ReserveMeeting->user_id }}"
                                                                data-item-id="{{ $ReserveMeeting->id }}"
                                                                data-user-type="student"
                                                                class="contact-info btn-transparent webinar-actions d-block mt-10">{{ trans('panel.contact_student') }}</button>

                                                        <button type="button" data-id="{{ $ReserveMeeting->id }}" class="webinar-actions js-finish-meeting-reserve d-block btn-transparent mt-10 font-weight-normal">{{ trans('panel.finish_meeting') }}</button>
                                                    </div>
                                                </div>
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

            <div class="my-30">
                {{ $reserveMeetings->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>

        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'meeting.png',
                'title' => trans('panel.meeting_no_result'),
                'hint' => nl2br(trans('panel.meeting_no_result_hint')),
            ])
        @endif
    </section>

    <div class="d-none" id="liveMeetingLinkModal">
        <h3 class="section-title after-line font-20 text-dark-blue mb-25">{{ trans('panel.add_live_meeting_link') }}</h3>

        <form action="/panel/meetings/create-link" method="post">
            <input type="hidden" name="item_id" value="">

            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="form-group">
                        <label class="input-label">{{ trans('panel.url') }}</label>
                        <input type="text" name="link" class="form-control"/>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label class="input-label">{{ trans('auth.password') }} ({{ trans('public.optional') }})</label>
                        <input type="text" name="password" class="form-control"/>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <p class="font-weight-500 font-12 text-gray">{{ trans('panel.add_live_meeting_link_hint') }}</p>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class="js-save-meeting-link btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>

    @include('web.default.panel.meeting.join_modal')
    @include('web.default.panel.meeting.meeting_create_session_modal')
@endsection

@push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>

    <script  >
        var instructor_contact_information_lang = '{{ trans('panel.instructor_contact_information') }}';
        var student_contact_information_lang = '{{ trans('panel.student_contact_information') }}';
        var email_lang = '{{ trans('public.email') }}';
        var phone_lang = '{{ trans('public.phone') }}';
        var location_lang = '{{ trans('update.location') }}';
        var close_lang = '{{ trans('public.close') }}';
        var linkSuccessAdd = '{{ trans('panel.add_live_meeting_link_success') }}';
        var linkFailAdd = '{{ trans('panel.add_live_meeting_link_fail') }}';
        var finishReserveHint = '{{ trans('meeting.finish_reserve_modal_hint') }}';
        var finishReserveConfirm = '{{ trans('meeting.finish_reserve_modal_confirm') }}';
        var finishReserveCancel = '{{ trans('meeting.finish_reserve_modal_cancel') }}';
        var finishReserveTitle = '{{ trans('meeting.finish_reserve_modal_title') }}';
        var finishReserveSuccess = '{{ trans('meeting.finish_reserve_modal_success') }}';
        var finishReserveSuccessHint = '{{ trans('meeting.finish_reserve_modal_success_hint') }}';
        var finishReserveFail = '{{ trans('meeting.finish_reserve_modal_fail') }}';
        var finishReserveFailHint = '{{ trans('meeting.finish_reserve_modal_fail_hint') }}';
        var sessionSuccessAdd = '{{ trans('update.add_live_meeting_session_success') }}';
        var youCanJoinTheSessionNowLang = '{{ trans('update.you_can_join_the_session_now') }}';
    </script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/contact-info.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/reserve_meeting.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/requests.min.js"></script>
@endpush
