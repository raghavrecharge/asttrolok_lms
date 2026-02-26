@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ $webinar->title }} | {{ $assignment->title }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-3 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/homework.svg" width="64" height="64" alt="">
                        <strong class="font-30 font-weight-bold mt-5">{{ $courseAssignmentsCount }}</strong>
                        <span class="font-16 text-dark-blue text-gray font-weight-500">{{ trans('update.course_assignments') }}</span>
                    </div>
                </div>

                <div class="col-3 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/58.svg" width="64" height="64" alt="">
                        <strong class="font-30 font-weight-bold mt-5">{{ $pendingReviewCount }}</strong>
                        <span class="font-16 text-dark-blue text-gray font-weight-500">{{ trans('update.pending_review') }}</span>
                    </div>
                </div>

                <div class="col-3 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/45.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $passedCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('quiz.passed') }}</span>
                    </div>
                </div>

                <div class="col-3 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/pin.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $failedCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('quiz.failed') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="mt-25">
        <h2 class="section-title">{{ trans('update.filter_assignments') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/assignments/{{ $assignment->id }}/students" method="get">
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

                    {{-- Student --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('admin/main.student') }}
                        </label>
                        <div style="position:relative;">
                            <select name="student_id" data-search-option="just_student_role" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="">{{ trans('public.all') }}</option>
                                @if(!empty($students) and $students->count() > 0)
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ (request()->get('student_id') == $student->id) ? 'selected' : '' }}>{{ $student->full_name }}</option>
                                    @endforeach
                                @endif
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
            <h2 class="section-title">{{ trans('update.your_course_assignments') }}</h2>
        </div>

        @if($histories->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('quiz.student') }}</th>
                                    <th class="text-center">{{ trans('panel.purchase_date') }}</th>
                                    <th class="text-center">{{ trans('update.first_submission') }}</th>
                                    <th class="text-center">{{ trans('update.last_submission') }}</th>
                                    <th class="text-center">{{ trans('update.attempts') }}</th>
                                    <th class="text-center">{{ trans('quiz.grade') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($histories as $history)
                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar bg-gray200">
                                                    <img loading="lazy"  src="{{ $history->student->getAvatar() }}" class="img-cover" alt="">
                                                </div>
                                                <div class=" ml-5">
                                                    <span class="d-block font-weight-500">{{ $history->student->full_name }}</span>
                                                    <span class="mt-5 font-12 text-gray d-block">{{ $history->student->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($history->purchase_date) ? dateTimeFormat($history->purchase_date, 'j M Y') : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($history->first_submission) ? dateTimeFormat($history->first_submission, 'j M Y | H:i') : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($history->last_submission) ? dateTimeFormat($history->last_submission, 'j M Y | H:i') : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ !empty($assignment->attempts) ? "{$history->usedAttemptsCount}/{$assignment->attempts}" : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ (!empty($history->grade)) ? $history->grade : '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            @if(empty($history) or ($history->status == \App\Models\WebinarAssignmentHistory::$notSubmitted))
                                                <span class="text-danger font-weight-500">{{ trans('update.assignment_history_status_not_submitted') }}</span>
                                            @else
                                                @switch($history->status)
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
                                                    <a href="{{ "{$assignment->webinar->getLearningPageUrl()}?type=assignment&item={$assignment->id}&student={$history->student_id}" }}" target="_blank"
                                                       class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.view_assignment') }}</a>
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
                {{ $histories->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'meeting.png',
                'title' => trans('update.my_assignments_no_result'),
                'hint' => nl2br(trans('update.my_assignments_no_result_hint')),
            ])
        @endif
    </section>
@endsection

@push('scripts_bottom')
    <script  ipt src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script  ipt src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
@endpush
