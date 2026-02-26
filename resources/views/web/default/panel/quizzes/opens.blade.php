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

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/quizzes/opens" method="get">
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
                            <input type="text" name="quiz_or_webinar" class="form-control"
                                   style="height:40px;padding:0 12px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('quiz_or_webinar','') }}"/>
                        </div>
                    </div>

                    {{-- Instructor --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.instructor') }}
                        </label>
                        <div style="position:relative;">
                            <input type="text" name="instructor" class="form-control"
                                   style="height:40px;padding:0 12px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('instructor','') }}"/>
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
