@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')

@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center mb-15">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(31, 59, 100, 0.08); display: flex; align-items: center; justify-content: center; margin-right: 15px; color: #1f3b64;">
                <i data-feather="bar-chart-2" width="22" height="22"></i>
            </div>
            <h2 class="section-title mb-0">{{ trans('update.assignment_statistics') }}</h2>
        </div>

        <div class="mt-20 px-15 py-30 rounded-lg" style="background: #f8faff; border: 1px solid #e8edf5; border-radius: 20px;">
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
        </div>
    </section>

    <section class="mt-35">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('update.your_students_assignments') }}</h2>
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
                                    <th class="text-center">{{ trans('update.min_grade') }}</th>
                                    <th class="text-center">{{ trans('quiz.average') }}</th>
                                    <th class="text-center">{{ trans('update.submissions') }}</th>
                                    <th class="text-center">{{ trans('public.pending') }}</th>
                                    <th class="text-center">{{ trans('quiz.passed') }}</th>
                                    <th class="text-center">{{ trans('quiz.failed') }}</th>
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
                                            <span class="font-weight-500">{{ $assignment->min_grade ?? '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ $assignment->average_grade ?? '-' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ $assignment->submissions }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span class="font-weight-500">{{ $assignment->pendingCount }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ $assignment->passedCount }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <span>{{ $assignment->failedCount }}</span>
                                        </td>

                                        <td class="align-middle">
                                            @switch($assignment->status)
                                                @case('active')
                                                <span class="text-dark-blue font-weight-500">{{ trans('public.active') }}</span>
                                                @break
                                                @case('inactive')
                                                <span class="text-danger font-weight-500">{{ trans('public.inactive') }}</span>
                                                @break
                                            @endswitch
                                        </td>

                                        <td class="align-middle text-right">

                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>

                                                <div class="dropdown-menu menu-lg">
                                                    <a href="/panel/assignments/{{ $assignment->id }}/students?status=pending" target="_blank"
                                                       class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.pending_review') }}</a>

                                                    <a href="/panel/assignments/{{ $assignment->id }}/students" target="_blank"
                                                       class="webinar-actions d-block mt-10 font-weight-normal">{{ trans('update.all_assignments') }}</a>
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
                'hint' => nl2br(trans('update.my_assignments_no_result_hint')),
            ])
        @endif
    </section>
@endsection

@push('scripts_bottom')

@endpush
