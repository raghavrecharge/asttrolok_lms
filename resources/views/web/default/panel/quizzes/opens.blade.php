@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .custom-table thead th {
            border-top: none;
            background-color: #fcfcfc;
            color: #1f3b64;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            padding: 18px 15px;
        }
        .custom-table tbody td {
            padding: 20px 15px;
            vertical-align: middle;
            color: #1f3b64;
            font-weight: 500;
        }
        .custom-table tbody tr {
            transition: all 0.2s ease;
        }
        .custom-table tbody tr:hover {
            background-color: #fbfcfe;
        }
        .grade-badge {
            padding: 6px 14px;
            border-radius: 30px;
            background: rgba(31, 59, 100, 0.1);
            color: #1f3b64;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('quiz.filter_results') }}</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <form action="/panel/quizzes/opens" method="get" class="row">
                <div class="col-12 col-lg-4">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.from') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class="text-white"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="from" autocomplete="off" class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('from','') }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.to') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="dateInputGroupPrepend">
                                            <i data-feather="calendar" width="18" height="18" class="text-white"></i>
                                        </span>
                                    </div>
                                    <input type="text" name="to" autocomplete="off" class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif" aria-describedby="dateInputGroupPrepend" value="{{ request()->get('to','') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label class="input-label">{{ trans('quiz.quiz_or_webinar') }}</label>
                                <input type="text" name="quiz_or_webinar" class="form-control" value="{{ request()->get('quiz_or_webinar','') }}"/>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group">
                                <label class="input-label">{{ trans('public.instructor') }}</label>
                                <input type="text" name="instructor" class="form-control" value="{{ request()->get('instructor','') }}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">{{ trans('public.show_results') }}</button>
                </div>
            </form>
        </div>
    </section>

    <section class="mt-35">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">{{ trans('quiz.open_quizzes') }}</h2>
        </div>

        @if($quizzes->count() > 0)
            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table custom-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.instructor') }}</th>
                                    <th>{{ trans('quiz.quiz') }}</th>
                                    <th class="text-center">{{ trans('quiz.quiz_grade') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($quizzes as $quiz)
                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar bg-gray200">
                                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $quiz->creator->getAvatar() }}" class="img-cover" alt="">
                                                </div>
                                                <div class=" ml-5">
                                                    <span class="d-block text-dark-blue font-weight-500">{{ $quiz->creator->full_name }}</span>
                                                    <span class="mt-5 font-12 text-gray d-block">{{ $quiz->creator->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left">
                                            <span class="d-block text-dark-blue font-weight-500">{{ $quiz->title }}</span>
                                            <span class="font-12 mt-5 text-gray d-block">{{ $quiz->webinar->title }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="grade-badge">
                                                {{ $quiz->quizQuestions->sum('grade') }}
                                            </span>
                                        </td>

                                        <td class="align-middle text-gray font-13">
                                            <div class="d-flex align-items-center">
                                                <i data-feather="calendar" width="14" height="14" class="mr-5"></i>
                                                {{ dateTimeFormat($quiz->created_at,'j M Y') }}
                                            </div>
                                            <div class="font-11 text-gray mt-5">{{ dateTimeFormat($quiz->created_at,'H:i') }}</div>
                                        </td>

                                        <td class="align-middle text-right font-weight-normal">
                                            <div class="btn-group dropdown table-actions table-actions-lg">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="/panel/quizzes/{{ $quiz->id }}/start" class="webinar-actions d-block mt-10 text-primary font-weight-bold">
                                                        <i data-feather="play" width="14" height="14" class="mr-8"></i>
                                                        {{ trans('public.start') }}
                                                    </a>
                                                    <a href="{{ $quiz->webinar->getUrl() }}" target="_blank" class="webinar-actions d-block mt-10">
                                                        <i data-feather="external-link" width="14" height="14" class="mr-8"></i>
                                                        {{ trans('webinars.webinar_page') }}
                                                    </a>
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
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'result.png',
                'title' => trans('quiz.quiz_result_no_result'),
                'hint' => trans('quiz.quiz_result_no_result_hint'),
            ])
        @endif
    </section>

    <div class="my-30">
        {{ $quizzes->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/moment.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

@endpush
