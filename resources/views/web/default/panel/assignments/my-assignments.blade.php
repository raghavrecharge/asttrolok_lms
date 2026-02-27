@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center mb-15">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(31, 59, 100, 0.08); display: flex; align-items: center; justify-content: center; margin-right: 15px; color: #1f3b64;">
                <i data-feather="bar-chart-2" width="22" height="22"></i>
            </div>
            <h2 class="section-title mb-0">{{ trans('update.assignment_statistics') }}</h2>
        </div>

        <!-- <div class="mt-20 px-15 py-30 rounded-lg" style="background: #f8faff; border: 1px solid #e8edf5; border-radius: 20px;"> -->
            <div class="row">
                <div class="col-12 col-md-6 col-lg-3 mb-15 mb-lg-0">
                    <div class="d-flex align-items-center p-20 h-100" style="background: #ffffff; border-radius: 20px; box-shadow: 0 4px 16px rgba(31,59,100,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';" >
                        <div style="width: 64px; height: 64px; border-radius: 18px; background: #eef2f7; display: flex; align-items: center; justify-content: center; margin-right: 18px; color: #1f3b64;">
                            <i data-feather="book-open" width="28" height="28"></i>
                        </div>
                        <div class="text-left">
                            <div style="font-size: 28px; font-weight: 800; color: #1f3b64; line-height: 1;">{{ $courseAssignmentsCount }}</div>
                            <div style="font-size: 13px; color: #6b7280; font-weight: 500; margin-top: 4px;">{{ trans('update.course_assignments') }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-15 mb-lg-0">
                    <div class="d-flex align-items-center p-20 h-100" style="background: #ffffff; border-radius: 20px; box-shadow: 0 4px 16px rgba(31,59,100,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';" >
                        <div style="width: 64px; height: 64px; border-radius: 18px; background: #fff8e1; display: flex; align-items: center; justify-content: center; margin-right: 18px; color: #b5850b;">
                            <i data-feather="clock" width="28" height="28"></i>
                        </div>
                        <div class="text-left">
                            <div style="font-size: 28px; font-weight: 800; color: #1f3b64; line-height: 1;">{{ $pendingReviewCount }}</div>
                            <div style="font-size: 13px; color: #6b7280; font-weight: 500; margin-top: 4px;">{{ trans('update.pending_review') }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-15 mb-md-0">
                    <div class="d-flex align-items-center p-20 h-100" style="background: #ffffff; border-radius: 20px; box-shadow: 0 4px 16px rgba(31,59,100,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';" >
                        <div style="width: 64px; height: 64px; border-radius: 18px; background: #e8f5e9; display: flex; align-items: center; justify-content: center; margin-right: 18px; color: #2e7d32;">
                            <i data-feather="check-circle" width="28" height="28"></i>
                        </div>
                        <div class="text-left">
                            <div style="font-size: 28px; font-weight: 800; color: #1f3b64; line-height: 1;">{{ $passedCount }}</div>
                            <div style="font-size: 13px; color: #6b7280; font-weight: 500; margin-top: 4px;">{{ trans('quiz.passed') }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <div class="d-flex align-items-center p-20 h-100" style="background: #ffffff; border-radius: 20px; box-shadow: 0 4px 16px rgba(31,59,100,0.05); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)';" onmouseout="this.style.transform='translateY(0)';" >
                        <div style="width: 64px; height: 64px; border-radius: 18px; background: #ffebee; display: flex; align-items: center; justify-content: center; margin-right: 18px; color: #c62828;">
                            <i data-feather="x-circle" width="28" height="28"></i>
                        </div>
                        <div class="text-left">
                            <div style="font-size: 28px; font-weight: 800; color: #1f3b64; line-height: 1;">{{ $failedCount }}</div>
                            <div style="font-size: 13px; color: #6b7280; font-weight: 500; margin-top: 4px;">{{ trans('quiz.failed') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- </div> -->
    </section>

    <section class="mt-25">
        <h2 class="section-title">{{ trans('update.filter_assignments') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/assignments/my-assignments" method="get">
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
                            <input type="text" name="from" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('from','') }}"/>
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
                            <input type="text" name="to" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('to','') }}"/>
                        </div>
                    </div>

                    {{-- Course --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="book" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('product.course') }}
                        </label>
                        <div style="position:relative;">
                            <select name="webinar_id" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="">{{ trans('webinars.all_courses') }}</option>
                                @foreach($webinars as $webinar)
                                    <option value="{{ $webinar->id }}" @if(request()->get('webinar_id') == $webinar->id) selected @endif>{{ $webinar->title }}</option>
                                @endforeach
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
                        <div style="position:relative;width:140px;">
                            <select name="status" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="">{{ trans('public.all') }}</option>
                                @foreach(\App\Models\WebinarAssignmentHistory::$assignmentHistoryStatus as $status)
                                    <option value="{{ $status }}" {{ (request()->get('status') == $status) ? 'selected' : '' }}>{{ trans('update.assignment_history_status_'.$status) }}</option>
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

    <section class="mt-35">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('update.my_assignments') }}</h2>
        </div>

        @if($assignments->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('update.title_and_course') }}</th>
                                    <th class="text-center">{{ trans('update.deadline') }}</th>
                                    <th class="text-center">{{ trans('update.first_submission') }}</th>
                                    <th class="text-center">{{ trans('update.last_submission') }}</th>
                                    <th class="text-center">{{ trans('update.attempts') }}</th>
                                    <th class="text-center">{{ trans('quiz.grade') }}</th>
                                    <th class="text-center">{{ trans('update.pass_grade') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($assignments as $assignment)
                                    <tr>
                                        <td class="text-left">
                                            <span class="d-block font-16 font-weight-500 text-dark-blue">{{ $assignment->title }}</span>
                                            <span class="d-block font-12 font-weight-500 text-gray">{{ $assignment->webinar->title }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($assignment->deadline) ? dateTimeFormat($assignment->deadlineTime, 'j M Y') : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($assignment->first_submission) ? dateTimeFormat($assignment->first_submission, 'j M Y | H:i') : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($assignment->last_submission) ? dateTimeFormat($assignment->last_submission, 'j M Y | H:i') : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($assignment->attempts) ? "{$assignment->usedAttemptsCount}/{$assignment->attempts}" : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ (!empty($assignment->assignmentHistory) and !empty($assignment->assignmentHistory->grade)) ? $assignment->assignmentHistory->grade : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ $assignment->pass_grade }}</span>
                                        </td>

                                        <td class="align-middle">
                                            @if(empty($assignment->assignmentHistory) or ($assignment->assignmentHistory->status == \App\Models\WebinarAssignmentHistory::$notSubmitted))
                                                <span class="text-danger font-weight-500">{{ trans('update.assignment_history_status_not_submitted') }}</span>
                                            @else
                                                @switch($assignment->assignmentHistory->status)
                                                    @case(\App\Models\WebinarAssignmentHistory::$passed)
                                                    <span class="text-primary font-weight-500">{{ trans('quiz.passed') }}</span>
                                                    @break
                                                    @case(\App\Models\WebinarAssignmentHistory::$pending)
                                                    <span class="text-warning font-weight-500">{{ trans('public.pending') }}</span>
                                                    @break
                                                    @case(\App\Models\WebinarAssignmentHistory::$notPassed)
                                                    <span class="font-weight-500 text-danger">{{ trans('quiz.failed') }}</span>
                                                    @break
                                                @endswitch
                                            @endif
                                        </td>

                                        <td class="align-middle text-right">

                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>

                                                <div class="dropdown-menu menu-lg">
                                                    @if($assignment->webinar->checkUserHasBought())
                                                        <a href="{{ "{$assignment->webinar->getLearningPageUrl()}?type=assignment&item={$assignment->id}" }}" target="_blank"
                                                           class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.view_assignment') }}</a>
                                                    @else
                                                        <a href="#!" class="not-access-toast webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.view_assignment') }}</a>
                                                    @endif
                                                </div>
                                            </div>

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
                {{ $assignments->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'meeting.png',
                'title' => trans('update.my_assignments_no_result'),
                'hint' => nl2br(trans('update.my_assignments_no_result_hint_student')),
            ])
        @endif
    </section>

@endsection

@push('scripts_bottom')
    <script  >
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
    </script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/my_assignments.min.js"></script>
@endpush
