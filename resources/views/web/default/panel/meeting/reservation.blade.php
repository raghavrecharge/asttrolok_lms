@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
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
        .bg-glass-primary { background: rgba(31, 59, 100, 0.12) !important; color: #1f3b64 !important; }
        .bg-glass-success { background: rgba(40, 167, 69, 0.15) !important; color: #1e7e34 !important; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.15) !important; color: #9b7400 !important; }
        .bg-glass-danger { background: rgba(220, 53, 69, 0.12) !important; color: #bd2130 !important; }
        .bg-glass-gray { background: rgba(108, 117, 125, 0.12) !important; color: #495057 !important; }

        .custom-table thead th {
            border-top: none;
            background-color: #f8faff;
            color: #1f3b64;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            padding: 20px 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .custom-table tbody td {
            padding: 25px 15px;
            vertical-align: middle;
            color: #1f3b64;
            border-bottom: 1px solid #f9f9f9;
        }
        .custom-table tbody tr {
            transition: all 0.2s ease;
        }
        .custom-table tbody tr:hover {
            background-color: #fbfcfe;
        }
        .time-badge {
            background: #f0f3f8;
            color: #1f3b64;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            border: 1px solid #e1e7f0;
        }
        .status-badge {
            padding: 8px 18px;
            border-radius: 30px;
            font-weight: 800;
            font-size: 11px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        /* Datepicker fix */
        .input-group .input-group-prepend .input-group-text {
            background-color: #1f3b64;
            border-color: #1f3b64;
            color: #fff;
            border-radius: 10px 0 0 10px;
        }
        .datepicker, .datefilter {
            cursor: pointer !important;
            border-radius: 0 10px 10px 0 !important;
        }
        .form-control {
            border-radius: 10px;
            height: 45px;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('panel.meeting_statistics') }}</h2>

        <div class="mt-25">
            <div class="row stat-card-row">
                <div class="col-12 col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="users" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $totalReserveCount }}</span>
                            <span class="stat-label">{{ trans('panel.total_meetings') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-4 mt-15 mt-sm-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="check-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $finishedReserveCount }}</span>
                            <span class="stat-label">Finish Meetings</span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-4 mt-15 mt-sm-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="calendar" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $upcomingReserveCount }}</span>
                            <span class="stat-label">Upcoming Meetings</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25 panel-filter-section">
        <h2 class="section-title">{{ trans('panel.filter_meetings') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/meetings/reservation" method="get">
                <div class="row align-items-end">

                    {{-- From --}}
                    <div class="col-12 col-sm-4 col-md-2">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.from') }}
                        </label>
                        <div style="position:relative;width:100%;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="from" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('from','') }}"/>
                        </div>
                    </div>

                    {{-- To --}}
                    <div class="col-12 col-sm-4 col-md-2 mt-15 mt-sm-0">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.to') }}
                        </label>
                        <div style="position:relative;width:100%;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="to" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('to','') }}"/>
                        </div>
                    </div>

                    {{-- Day --}}
                    <div class="col-12 col-sm-4 col-md-2 mt-15 mt-sm-0">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sun" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.day') }}
                        </label>
                        <div style="position:relative;">
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
                    </div>

                    {{-- Instructor --}}
                    <div class="col-12 col-sm-6 col-md-2 mt-15 mt-md-0">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.instructor') }}
                        </label>
                        <div style="position:relative;">
                            <select name="instructor_id" class="form-control select2" style="width:100%;">
                                <option value="all">{{ trans('webinars.all_instructors') }}</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" @if(request()->get('instructor_id') == $instructor->id) selected @endif>{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="col-12 col-sm-6 col-md-2 mt-15 mt-md-0">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <select name="status" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="all">{{ trans('public.all') }}</option>
                            <option value="open" {{ request()->get('status') === 'open' ? 'selected' : '' }}>{{ trans('public.open') }}</option>
                            <option value="finished" {{ request()->get('status') === 'finished' ? 'selected' : '' }}>{{ trans('public.finished') }}</option>
                        </select>
                    </div>

                    {{-- Submit Button --}}
                    <div class="col-12 col-md-2 mt-20 mt-md-0">
                        <button type="submit" style="height:40px;width:100%;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                            <i data-feather="search" width="13" height="13"></i>
                            {{ trans('public.show_results') }}
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </section>

    <section class="mt-35">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('panel.meeting_list') }}</h2>

            <!-- <form action="/panel/meetings/reservation?{{ http_build_query(request()->all()) }}" class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                <label class="cursor-pointer mb-0 mr-10 text-gray font-14 font-weight-500" for="openMeetingResult">{{ trans('panel.show_only_open_meetings') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="open_meetings" class="js-panel-list-switch-filter custom-control-input" id="openMeetingResult" {{ (request()->get('open_meetings', '') == 'on') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="openMeetingResult"></label>
                </div>
            </form> -->
        </div>

        @if($reserveMeetings->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.instructor') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ trans('update.meeting_type') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th class="text-center">{{ trans('public.time') }}</th>
                                    <th class="text-center d-none d-md-table-cell">{{ trans('public.paid_amount') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($reserveMeetings as $ReserveMeeting)
                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                @if(!empty($ReserveMeeting->meeting) and !empty($ReserveMeeting->meeting->creator))
                                                    <div class="avatar bg-gray200">
                                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $ReserveMeeting->meeting->creator->getAvatar() }}" class="img-cover" alt="">
                                                    </div>
                                                    <div class=" ml-5">
                                                        <span class="d-block font-weight-500">{{ $ReserveMeeting->meeting->creator->full_name }}</span>
                                                    </div>
                                                @else
                                                    <div class="avatar bg-gray200">
                                                        <img loading="lazy" src="/assets/default/img/avatar.png" class="img-cover" alt="">
                                                    </div>
                                                    <div class=" ml-5">
                                                        <span class="d-block font-weight-500 text-danger">{{ trans('update.deleted_user') }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle text-center d-none d-md-table-cell">
                                            <span class="font-weight-500">{{ trans('update.'.$ReserveMeeting->meeting_type) }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            @php
                                                $dayText = dateTimeFormat($ReserveMeeting->start_at, 'D');
                                                $dateText = dateTimeFormat($ReserveMeeting->start_at, 'j M Y');
                                            @endphp
                                            <div class="text-dark-blue font-weight-bold font-14">{{ $dayText }}</div>
                                            <div class="text-gray font-11 mt-2 text-nowrap">{{ $dateText }}</div>
                                        </td>

                                        <td class="align-middle text-center text-nowrap">
                                            <div class="time-badge">
                                                <i data-feather="clock" width="12" height="12" class="mr-5"></i>
                                                {{ dateTimeFormat($ReserveMeeting->start_at, 'H:i') }} - {{ dateTimeFormat($ReserveMeeting->end_at, 'H:i') }}
                                            </div>
                                        </td>

                                        <td class="align-middle text-center d-none d-md-table-cell">
                                            <div class="font-weight-bold text-dark-blue font-14">
                                                @if(!empty($ReserveMeeting->sale) and !empty($ReserveMeeting->sale->total_amount) and $ReserveMeeting->sale->total_amount > 0)
                                                    {{ handlePrice($ReserveMeeting->sale->total_amount) }}
                                                @else
                                                    <span class="text-success">{{ trans('public.free') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-gray font-11 mt-2">
                                                {{ $ReserveMeeting->student_count ?? 1 }} {{ trans('update.students_count') }}
                                            </div>
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

                                                        @if(getFeaturesSettings('agora_for_meeting') and $ReserveMeeting->meeting_type != 'in_person' and $ReserveMeeting->status == \App\Models\ReserveMeeting::$open)
                                                            @if(!empty($ReserveMeeting->session))
                                                                <button type="button" data-item-id="{{ $ReserveMeeting->id }}" data-date="{{ dateTimeFormat($ReserveMeeting->start_at, 'j M Y H:i') }}" data-link="{{ $ReserveMeeting->session->getJoinLink() }}"
                                                                        class="js-join-meeting-session btn-transparent webinar-actions d-block mt-10 text-primary">{{ trans('update.join_to_session') }}</button>
                                                            @endif
                                                        @endif

                                                        @if($ReserveMeeting->link and $ReserveMeeting->status == \App\Models\ReserveMeeting::$open)
                                                            <button type="button" data-reserve-id="{{ $ReserveMeeting->id }}"
                                                                    class="js-join-reserve btn-transparent webinar-actions d-block mt-10">{{ trans('footer.join') }}</button>
                                                        @endif

                                                        <a href="{{ $ReserveMeeting->addToCalendarLink() }}" target="_blank"
                                                           class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('public.add_to_calendar') }}</a>

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

    @include('web.default.panel.meeting.join_modal')
    @include('web.default.panel.meeting.meeting_create_session_modal')
@endsection

@push('scripts_bottom')
    <script  >
        var instructor_contact_information_lang = '{{ trans('panel.instructor_contact_information') }}';
        var student_contact_information_lang = '{{ trans('panel.student_contact_information') }}';
        var email_lang = '{{ trans('public.email') }}';
        var phone_lang = '{{ trans('public.phone') }}';
        var location_lang = '{{ trans('update.location') }}';
        var close_lang = '{{ trans('public.close') }}';
        var finishReserveHint = '{{ trans('meeting.finish_reserve_modal_hint') }}';
        var finishReserveConfirm = '{{ trans('meeting.finish_reserve_modal_confirm') }}';
        var finishReserveCancel = '{{ trans('meeting.finish_reserve_modal_cancel') }}';
        var finishReserveTitle = '{{ trans('meeting.finish_reserve_modal_title') }}';
        var finishReserveSuccess = '{{ trans('meeting.finish_reserve_modal_success') }}';
        var finishReserveSuccessHint = '{{ trans('meeting.finish_reserve_modal_success_hint') }}';
        var finishReserveFail = '{{ trans('meeting.finish_reserve_modal_fail') }}';
        var finishReserveFailHint = '{{ trans('meeting.finish_reserve_modal_fail_hint') }}';

    </script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/contact-info.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/reserve_meeting.min.js"></script>
@endpush
