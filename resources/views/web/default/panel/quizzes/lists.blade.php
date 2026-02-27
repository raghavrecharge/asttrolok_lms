@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('panel.comments_statistics') }}</h2>

        <div class="activities-container mt-25 p-20 p-lg-35">
            <div class="row">
                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/46.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $quizzesCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('quiz.quizzes') }}</span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/47.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $questionsCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('public.questions') }}</span>
                    </div>
                </div>

                <div class="col-4 d-flex align-items-center justify-content-center">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img loading="lazy"  src="{{ config('app.js_css_url') }}/assets/default/img/activity/48.svg" width="64" height="64" alt="">
                        <strong class="font-30 text-dark-blue font-weight-bold mt-5">{{ $userCount }}</strong>
                        <span class="font-16 text-gray font-weight-500">{{ trans('quiz.students') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="mt-25 panel-filter-section">
        <h2 class="section-title">{{ trans('quiz.filter_quizzes') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/quizzes" method="get">
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

                    {{-- Quiz or Webinar --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="help-circle" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('quiz.quiz_or_webinar') }}
                        </label>
                        <div style="position:relative;">
                            <select name="quiz_id" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($allQuizzesLists as $allQuiz)
                                    <option value="{{ $allQuiz->id }}" @if(request()->get('quiz_id') == $allQuiz->id) selected @endif>{{ $allQuiz->title .' - '. ($allQuiz->webinar ? $allQuiz->webinar->title : '-') }}</option>
                                @endforeach
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Total Mark --}}
                    <div style="flex:0 0 auto;width:100px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="target" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> Marks
                        </label>
                        <input type="text" name="total_mark" class="form-control" style="height:40px;border:1.5px solid #e8edf5;border-radius:9px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);" value="{{ request()->get('total_mark','') }}"/>
                    </div>

                    {{-- Status --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <div style="position:relative;width:110px;">
                            <select name="status" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="all">{{ trans('public.all') }}</option>
                                <option value="active" @if(request()->get('status') == 'active') selected @endif >{{ trans('public.active') }}</option>
                                <option value="inactive" @if(request()->get('status') == 'inactive') selected @endif >{{ trans('public.inactive') }}</option>
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
            <h2 class="section-title">{{ trans('quiz.quizzes') }}</h2>

            <form action="/panel/quizzes" method="get" class="">
                <div class="d-flex align-items-center flex-row-reverse flex-md-row justify-content-start justify-content-md-center mt-20 mt-md-0">
                    <label class="mb-0 mr-10 cursor-pointer text-gray font-14 font-weight-500" for="activeQuizzesSwitch">{{ trans('quiz.show_only_active_quizzes') }}</label>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="active_quizzes" class="custom-control-input" id="activeQuizzesSwitch" @if(request()->get('active_quizzes',null) == 'on') checked @endif>
                        <label class="custom-control-label" for="activeQuizzesSwitch"></label>
                    </div>
                </div>
            </form>
        </div>

        @if($quizzes->count() > 0)

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            <table class="table text-center custom-table">
                                <thead>
                                <tr>
                                    <th class="text-left">{{ trans('public.title') }}</th>
                                    <th class="text-center">{{ trans('public.questions') }}</th>
                                    <th class="text-center">{{ trans('public.time') }} <span class="braces">({{ trans('public.min') }})</span></th>
                                    <th class="text-center">{{ trans('public.total_mark') }}</th>
                                    <th class="text-center">{{ trans('public.pass_mark') }}</th>
                                    <th class="text-center">{{ trans('quiz.students') }}</th>

                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th class="text-center">{{ trans('public.date_created') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($quizzes as $quiz)
                                    <tr>
                                        <td class="text-left">
                                            <span class="d-block">{{ $quiz->title }}</span>
                                            <span class="font-12 text-gray d-block">
                                                @if(!empty($quiz->webinar))
                                                    {{ $quiz->webinar->title }}
                                                @else
                                                    {{ trans('panel.not_assign_any_webinar') }}
                                                @endif
                                        </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            {{ $quiz->quizQuestions->count() }}
                                            @if(($quiz->display_limited_questions and !empty($quiz->display_number_of_questions)))
                                                <span class="font-12 text-gray">({{ trans('public.active') }}: {{ $quiz->display_number_of_questions }})</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">{{ $quiz->time }}</td>
                                        <td class="text-center align-middle">{{ $quiz->quizQuestions->sum('grade') }}</td>
                                        <td class="text-center align-middle">{{ $quiz->pass_mark }}</td>
                                        <td class="text-center align-middle">
                                            <span class="d-block">{{ $quiz->quizResults->pluck('user_id')->count() }}</span>

                                            @if(!empty($quiz->userSuccessRate) and $quiz->userSuccessRate > 0)
                                                <span class="font-12 text-primary d-block">{{ $quiz->userSuccessRate }}% {{ trans('quiz.passed')  }}</span>
                                            @endif
                                        </td>

                                        <td class="text-center align-middle">{{ trans('public.'.$quiz->status) }}</td>

                                        <td class="text-center align-middle">{{ dateTimeFormat($quiz->created_at, 'j M Y H:i') }}</td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group dropdown table-actions">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu font-weight-normal">
                                                    <a href="/panel/quizzes/{{ $quiz->id }}/edit" class="webinar-actions d-block mt-10">{{ trans('public.edit') }}</a>
                                                    <a href="/panel/quizzes/{{ $quiz->id }}/delete" data-item-id="1" class="webinar-actions d-block mt-10 delete-action">{{ trans('public.delete') }}</a>
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
                'file_name' => 'quiz.png',
                'title' => trans('quiz.quiz_no_result'),
                'hint' => nl2br(trans('quiz.quiz_no_result_hint')),
                'btn' => ['url' => '/panel/quizzes/new','text' => trans('quiz.create_a_quiz')]
            ])

        @endif

    </section>

    <div class="my-30">
        {{ $quizzes->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/quiz_list.min.js"></script>
@endpush
