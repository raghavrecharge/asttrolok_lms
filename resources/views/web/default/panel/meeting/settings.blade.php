@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/bootstrap-clockpicker/bootstrap-clockpicker.min.css">
    <style>
        .panel-section-card-premium {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e8edf5;
            padding: 30px;
            box-shadow: 0 4px 24px rgba(31, 59, 100, 0.04);
            transition: all 0.3s ease;
        }
        .section-title-premium {
            font-size: 18px;
            font-weight: 700;
            color: #1f3b64;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title-premium i {
            color: #43d477;
        }
        
        .timesheet-row {
            border-bottom: 1px solid #f1f5f9;
            padding: 15px 0;
            transition: background 0.2s;
        }
        .timesheet-row:hover {
            background: #fbfcfe;
        }
        .timesheet-row:last-child {
            border-bottom: none;
        }
        
        .day-label-premium {
            font-size: 15px;
            font-weight: 700;
            color: #1f3b64;
            display: block;
        }
        .hours-available-premium {
            font-size: 12px;
            color: #6a737d;
            font-weight: 500;
        }
        
        .selected-time-premium {
            background: rgba(31, 59, 100, 0.05);
            border-radius: 12px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(31, 59, 100, 0.08);
            margin-bottom: 8px;
            margin-right: 8px;
            transition: all 0.2s;
        }
        .selected-time-premium:hover {
            background: rgba(31, 59, 100, 0.08);
            border-color: rgba(31, 59, 100, 0.15);
        }
        .selected-time-premium .inner-time {
            font-size: 13px;
            font-weight: 600;
            color: #1f3b64;
        }
        .remove-time-premium {
            width: 20px;
            height: 20px;
            background: #ff5b5c;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(255, 91, 92, 0.3);
        }
        
        .input-label-premium {
            font-size: 11px;
            font-weight: 700;
            color: #8c98a4;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            display: block;
        }
        .form-control-premium {
            height: 48px;
            border-radius: 12px;
            border: 1.5px solid #e8edf5;
            font-size: 14px;
            font-weight: 600;
            color: #1f3b64;
            padding: 0 15px;
            transition: all 0.2s;
        }
        .form-control-premium:focus {
            border-color: #43d477;
            box-shadow: 0 0 0 4px rgba(67, 212, 119, 0.1);
        }
        
        .input-group-premium {
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            background: #fff !important;
            border: 1.5px solid #e2e8f0 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
        }
        .input-group-premium:focus-within {
            border-color: #43d477 !important;
            box-shadow: 0 4px 20px rgba(67, 212, 119, 0.12) !important;
        }
        .input-group-prepend-premium {
            display: flex !important;
            flex-shrink: 0 !important;
            margin-right: 0 !important;
        }
        .input-group-prepend-premium .input-group-text {
            background: #f8fafc !important;
            border: none !important;
            border-right: 1.5px solid #f1f5f9 !important;
            border-radius: 0 !important;
            color: #64748b !important;
            font-weight: 700;
            padding: 0 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 52px !important;
            font-size: 16px !important;
            transition: all 0.3s ease !important;
            height: 100% !important;
        }
        .input-group-premium .form-control-premium {
            border: none !important;
            border-radius: 0 !important;
            flex: 1 !important;
            background: transparent !important;
            padding-left: 15px !important;
            height: 46px !important;
            font-weight: 600 !important;
            color: #1f3b64 !important;
            margin: 0 !important;
            box-shadow: none !important;
            width: auto !important;
            min-width: 0 !important;
        }
        .input-group-premium .form-control-premium:focus {
            box-shadow: none !important;
            outline: none !important;
        }
        
        /* Direct inputs without group */
        .form-control-premium:not(.input-group-premium *) {
            background: #fff !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 14px !important;
            height: 46px;
            padding: 0 15px !important;
            font-weight: 600;
            color: #1f3b64;
            transition: all 0.3s ease;
        }
        .form-control-premium:not(.input-group-premium *):focus {
            border-color: #43d477 !important;
            box-shadow: 0 4px 20px rgba(67, 212, 119, 0.12) !important;
            outline: none;
        }
        .form-control-premium::placeholder {
            color: #94a3b8;
            font-weight: 500;
            font-size: 14px;
            opacity: 0.7;
        }
        
        /* Premium Container for Group Meeting */
        .group-meeting-options-card {
            padding: 24px;
            border-radius: 16px;
            background: #fcfdfe;
            border: 1px solid #edf2f7;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 4px rgba(31, 59, 100, 0.02);
        }
        
        /* Modern Switch Styling */
        .custom-switch-premium {
            padding-left: 0 !important;
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
        }
        .custom-switch-premium .custom-control-input {
            display: none;
        }
        .custom-switch-premium .custom-control-label {
            position: relative;
            padding-left: 60px !important;
            cursor: pointer;
            margin-bottom: 0;
            min-height: 32px;
            display: flex;
            align-items: center;
            font-weight: 700;
            color: #1f3b64;
            font-size: 15px;
            line-height: 1.4;
        }
        .custom-switch-premium .custom-control-label::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 30px !important;
            width: 54px !important;
            border-radius: 15px !important;
            background-color: #e2e8f0 !important;
            border: 1.5px solid #cbd5e1 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .custom-switch-premium .custom-control-label::after {
            content: '';
            position: absolute;
            left: 5px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            width: 22px !important;
            height: 22px !important;
            background-color: #fff !important;
            border-radius: 11px !important;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .custom-control-input:checked ~ .custom-control-label::before {
            background: linear-gradient(135deg, #43d477 0%, #2ecc71 100%) !important;
            border-color: #27ae60 !important;
        }
        .custom-control-input:checked ~ .custom-control-label::after {
            left: 27px !important;
        }
        
        .action-btn-premium {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .submit-btn-premium {
            background: linear-gradient(135deg, #43d477 0%, #2ecc71 100%);
            color: #fff;
            border: none;
            padding: 12px 35px;
            border-radius: 12px;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(67, 212, 119, 0.3);
        }
        .submit-btn-premium:hover {
            box-shadow: 0 6px 20px rgba(67, 212, 119, 0.4);
            transform: translateY(-1px);
        }

        /* Modal / Clockpicker Polishing */
        .add-time-modal {
            padding: 20px;
        }
        .add-time-modal .font-48 {
            font-size: 40px !important;
            font-weight: 800 !important;
            letter-spacing: -1px;
        }
        .add-time-modal .pulsate {
            animation: pulsate-accent 2s infinite ease-in-out;
        }
        @keyframes pulsate-accent {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        .add-time-modal .input-label {
            font-size: 13px;
            font-weight: 700;
            color: #1f3b64;
            margin-bottom: 8px;
        }
        .add-time-modal .form-control {
            border-radius: 10px;
            border: 1.5px solid #e8edf5;
            font-weight: 600;
        }
        
        .clockpicker-popover {
            border-radius: 15px !important;
            border: none !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
            padding: 10px !important;
        }
        .clockpicker-popover .popover-title {
            background: #fff !important;
            color: #1f3b64 !important;
            font-weight: 700 !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .clockpicker-popover .popover-content {
            padding: 15px !important;
        }
        .clockpicker-canvas line {
            stroke: #43d477 !important;
        }
        .clockpicker-canvas-bearing {
            fill: #43d477 !important;
        }
        .clockpicker-canvas-bg {
            fill: rgba(67, 212, 119, 0.2) !important;
        }
        .clockpicker-tick.active {
            color: #43d477 !important;
            font-weight: 700 !important;
        }

        /* Responsive refinements */
        @media (max-width: 768px) {
            .panel-section-card-premium {
                padding: 20px;
            }
            .selected-time-premium {
                width: 100%;
                justify-content: space-between;
                margin-right: 0;
            }
        }
    </style>
@endpush

@section('content')

    <form action="/panel/meetings/{{ $meeting->id }}/update" method="post" id="meetingSettingsForm">
        {{ csrf_field() }}
        
        <section>
            <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
                <h2 class="section-title-premium">
                    <i data-feather="calendar"></i>
                    {{ trans('panel.my_timesheet') }}
                </h2>

                <div class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                    <label class="mb-0 mr-15 cursor-pointer font-14 text-gray font-weight-600" for="temporaryDisableMeetingsSwitch">{{ trans('panel.temporary_disable_meetings') }}</label>
                    <div class="custom-control custom-switch custom-switch-premium">
                        <input type="checkbox" name="disabled" class="custom-control-input" id="temporaryDisableMeetingsSwitch" {{ $meeting->disabled ? 'checked' : '' }}>
                        <label class="custom-control-label" for="temporaryDisableMeetingsSwitch"></label>
                    </div>
                </div>
            </div>

            <div class="panel-section-card-premium mt-20">
                <div class="row">
                    <div class="col-12">
                        @foreach(\App\Models\MeetingTime::$days as $day)
                            <div class="timesheet-row" id="{{ $day }}TimeSheet" data-day="{{ $day }}">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-3 mb-10 mb-md-0">
                                        <span class="day-label-premium">{{ trans('panel.'.$day) }}</span>
                                        <span class="hours-available-premium">
                                            <i data-feather="clock" width="12" height="12" class="mr-5"></i>
                                            {{ isset($meetingTimes[$day]) ? $meetingTimes[$day]["hours_available"] : 0 }} {{ trans('home.hours') .' '. trans('public.available') }}
                                        </span>
                                    </div>
                                    
                                    <div class="col-12 col-md-7 time-sheet-items">
                                        @if(isset($meetingTimes[$day]))
                                            @foreach($meetingTimes[$day]["times"] as $time)
                                                <div class="selected-time-premium position-relative selected-time">
                                                    <span class="inner-time">
                                                        {{ $time->time }}
                                                        <span class="mx-5 text-gray" style="opacity: 0.5">|</span>
                                                        <span class="font-11 text-gray">{{ trans('update.'.($time->meeting_type == 'all' ? 'both' : $time->meeting_type)) }}</span>
                                                    </span>

                                                    <span data-time-id="{{ $time->id }}" class="remove-time remove-time-premium">
                                                        <i data-feather="x" width="12" height="12"></i>
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    
                                    <div class="col-12 col-md-2 text-md-right mt-10 mt-md-0">
                                        <div class="btn-group dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-gray300 dropdown-toggle action-btn-premium" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-color: #e8edf5; color: #1f3b64;">
                                                <i data-feather="settings" width="14" height="14" class="mr-5"></i>
                                                {{ trans('public.controls') }}
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right p-10" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                                <button type="button" class="add-time dropdown-item font-13 font-weight-600 rounded-pill py-8 px-15 mb-5" style="color: #43d477;">
                                                    <i data-feather="plus" width="14" height="14" class="mr-8"></i>
                                                    {{ trans('public.add_time') }}
                                                </button>

                                                @if(isset($meetingTimes[$day]) and !empty($meetingTimes[$day]["hours_available"]) and $meetingTimes[$day]["hours_available"] > 0)
                                                    <button type="button" class="clear-all dropdown-item font-13 font-weight-600 rounded-pill py-8 px-15" style="color: #ff5b5c;">
                                                        <i data-feather="trash-2" width="14" height="14" class="mr-8"></i>
                                                        {{ trans('public.clear_all') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-40">
            <h2 class="section-title-premium mb-20">
                <i data-feather="dollar-sign"></i>
                Half {{ trans('panel.my_hourly_charge') }}
            </h2>

            <div class="panel-section-card-premium">
                <div class="row align-items-center">
                    <div class="col-12 col-md-4">
                        <label class="input-label-premium">{{ trans('panel.amount') }}</label>
                        <input type="number" name="amount" value="{{ !empty($meeting) ? convertPriceToUserCurrency($meeting->amount) : old('amount') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-12 col-md-4 mt-20 mt-md-0">
                        <label class="input-label-premium">{{ trans('panel.discount') }} (%)</label>
                        <input type="number" name="discount" value="{{ !empty($meeting) ? $meeting->discount : old('discount') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-40">
            <h2 class="section-title-premium mb-20">
                <i data-feather="map-pin"></i>
                {{ trans('update.in_person_meetings') }}
            </h2>

            <div class="panel-section-card-premium">
                <div class="row align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="custom-control custom-switch custom-switch-premium">
                            <input type="checkbox" name="in_person" class="custom-control-input" id="inPersonMeetingSwitch" {{ ((!empty($meeting) and $meeting->in_person) or old('in_person') == 'on') ? 'checked' :  '' }}>
                            <label class="custom-control-label" for="inPersonMeetingSwitch">{{ trans('update.available_for_in_person_meetings') }}</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-4 mt-20 mt-md-0 {{ ((!empty($meeting) and $meeting->in_person) or old('in_person') == 'on') ? '' :  'd-none' }}" id="inPersonMeetingAmount">
                        <label class="input-label-premium">{{ trans('update.hourly_amount') }}</label>
                        <input type="number" name="in_person_amount" value="{{ !empty($meeting) ? convertPriceToUserCurrency($meeting->in_person_amount) : old('in_person_amount') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-40 mb-30">
            <h2 class="section-title-premium mb-20">
                <i data-feather="users"></i>
                {{ trans('update.group_meeting') }}
            </h2>

            <div class="panel-section-card-premium">
                <div class="row align-items-start">
                    <div class="col-12 col-md-4 mb-20">
                        <div class="custom-control custom-switch custom-switch-premium">
                            <input type="checkbox" name="group_meeting" class="custom-control-input" id="groupMeetingSwitch" {{ ((!empty($meeting) and $meeting->group_meeting) or old('group_meeting') == 'on') ? 'checked' :  '' }}>
                            <label class="custom-control-label" for="groupMeetingSwitch">{{ trans('update.available_for_group_meeting') }}</label>
                        </div>
                    </div>

                    <div class="col-12 {{ ((!empty($meeting) and $meeting->group_meeting) or old('group_meeting') == 'on') ? '' :  'd-none' }}" id="onlineGroupMeetingOptions">
                        <div class="group-meeting-options-card">
                            <h4 class="font-14 text-dark-blue font-weight-bold mb-15">
                                <i data-feather="video" width="14" height="14" class="mr-5 text-primary"></i>
                                {{ trans('update.online_group_meeting_options') }}
                            </h4>

                            <div class="row mt-15">
                                <div class="col-12 col-md-4">
                                    <label class="input-label-premium">{{ trans('update.minimum_students') }}</label>
                                    <input type="number" min="2" name="online_group_min_student" value="{{ !empty($meeting) ? $meeting->online_group_min_student : old('online_group_min_student') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-12 col-md-4 mt-15 mt-md-0">
                                    <label class="input-label-premium">{{ trans('update.maximum_students') }}</label>
                                    <input type="number" name="online_group_max_student" value="{{ !empty($meeting) ? $meeting->online_group_max_student : old('online_group_max_student') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-12 col-md-4 mt-15 mt-md-0">
                                    <label class="input-label-premium">{{ trans('update.hourly_amount') }}</label>
                                    <input type="text" name="online_group_amount" value="{{ !empty($meeting) ? convertPriceToUserCurrency($meeting->online_group_amount) : old('online_group_amount') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 {{ ((!empty($meeting) and $meeting->group_meeting and $meeting->in_person) or (old('group_meeting') == 'on' and old('in_person') == 'on')) ? '' :  'd-none' }}" id="inPersonGroupMeetingOptions">
                        <div class="group-meeting-options-card">
                            <h4 class="font-14 text-dark-blue font-weight-bold mb-15">
                                <i data-feather="home" width="14" height="14" class="mr-5 text-success"></i>
                                {{ trans('update.in_person_group_meeting_options') }}
                            </h4>

                            <div class="row mt-15">
                                <div class="col-12 col-md-4">
                                    <label class="input-label-premium">{{ trans('update.minimum_students') }}</label>
                                    <input type="number" min="2" name="in_person_group_min_student" value="{{ !empty($meeting) ? $meeting->in_person_group_min_student : old('in_person_group_min_student') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-12 col-md-4 mt-15 mt-md-0">
                                    <label class="input-label-premium">{{ trans('update.maximum_students') }}</label>
                                    <input type="number" name="in_person_group_max_student" value="{{ !empty($meeting) ? $meeting->in_person_group_max_student : old('in_person_group_max_student') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="col-12 col-md-4 mt-15 mt-md-0">
                                    <label class="input-label-premium">{{ trans('update.hourly_amount') }}</label>
                                    <input type="text" name="in_person_group_amount" value="{{ !empty($meeting) ? convertPriceToUserCurrency($meeting->in_person_group_amount) : old('in_person_group_amount') }}" class="form-control form-control-premium" placeholder="{{ trans('panel.number_only') }}"/>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-40 mb-50">
            <button type="button" id="meetingSettingFormSubmit" class="submit-btn-premium d-flex align-items-center gap-10">
                <i data-feather="check-circle" width="18" height="18"></i>
                {{ trans('public.submit') }}
            </button>
        </div>
    </form>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/bootstrap-clockpicker/bootstrap-clockpicker.min.js"></script>
    <script type="text/javascript">
        var saveLang = '{{ trans('public.save') }}';
        var closeLang = '{{ trans('public.close') }}';
        var successDeleteTime = '{{ trans('meeting.success_delete_time') }}';
        var errorDeleteTime = '{{ trans('meeting.error_delete_time') }}';
        var successSavedTime = '{{ trans('meeting.success_save_time') }}';
        var errorSavingTime = '{{ trans('meeting.error_saving_time') }}';
        var noteToTimeMustGreater = '{{ trans('meeting.note_to_time_must_greater_from_time') }}';
        var requestSuccess = '{{ trans('public.request_success') }}';
        var requestFailed = '{{ trans('public.request_failed') }}';
        var saveMeetingSuccessLang = '{{ trans('meeting.save_meeting_setting_success') }}';
        var meetingTypeLang = '{{ trans('update.meeting_type') }}';
        var inPersonLang = '{{ trans('update.in_person') }}';
        var onlineLang = '{{ trans('update.online') }}';
        var bothLang = '{{ trans('update.both') }}';
        var descriptionLng = '{{ trans('public.description') }}';
        var saveTimeDescriptionPlaceholder = '{{ trans('update.save_time_description_placeholder') }}';

        var toTimepicker, fromTimepicker;
    </script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/meeting.min.js"></script>
    <script>
        // Refresh feather icons after dynamic content loading if needed
        $(document).ready(function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>
@endpush
