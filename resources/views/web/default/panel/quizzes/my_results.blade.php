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
        .bg-glass-primary { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.1); color: #43d477; }
        .bg-glass-danger { background: rgba(239, 102, 110, 0.1); color: #ef666e; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }

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
        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 11px;
            display: inline-block;
        }
        .btn-hover-shadow {
            transition: all 0.3s ease;
        }
        .btn-hover-shadow:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('quiz.results_statistics') }}</h2>

        <div class="mt-25">
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="file-text" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $quizzesResultsCount }}</span>
                            <span class="stat-label">{{ trans('quiz.quizzes') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="check-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $passedCount }}</span>
                            <span class="stat-label">{{ trans('quiz.passed') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-20 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-danger">
                            <i data-feather="x-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $failedCount }}</span>
                            <span class="stat-label">{{ trans('quiz.failed') }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-20 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="clock" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $waitingCount }}</span>
                            <span class="stat-label">{{ trans('quiz.open_results') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <h2 class="section-title">{{ trans('quiz.filter_results') }}</h2>

        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/quizzes/my-results" method="get">
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
                    <div style="flex:1 1 180px;min-width:160px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="help-circle" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('quiz.quiz_or_webinar') }}
                        </label>
                        <input type="text" name="quiz_or_webinar" class="form-control" style="height:40px;border:1.5px solid #e8edf5;border-radius:9px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);" value="{{ request()->get('quiz_or_webinar','') }}"/>
                    </div>

                    {{-- Instructor --}}
                    <div style="flex:1 1 180px;min-width:160px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="user" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.instructor') }}
                        </label>
                        <input type="text" name="instructor" class="form-control" style="height:40px;border:1.5px solid #e8edf5;border-radius:9px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);" value="{{ request()->get('instructor','') }}"/>
                    </div>

                    {{-- Status --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <div style="position:relative;width:110px;">
                            <select name="status" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="all">{{ trans('public.all') }}</option>
                                <option value="passed" {{ request()->get('status') === "passed" ? 'selected' : '' }}>{{ trans('quiz.passed') }}</option>
                                <option value="failed" {{ request()->get('status') === "failed" ? 'selected' : '' }}>{{ trans('quiz.failed') }}</option>
                                <option value="waiting" {{ request()->get('status') === "waiting" ? 'selected' : '' }}>{{ trans('quiz.waiting') }}</option>
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
            <h2 class="section-title">{{ trans('quiz.my_quizzes') }}</h2>
        </div>

        @if($quizzesResults->count() > 0)
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
                                    <th class="text-center">{{ trans('quiz.my_grade') }}</th>
                                    <th class="text-center">{{ trans('public.status') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($quizzesResults as $result)
                                    <tr>
                                        <td class="text-left">
                                            <div class="user-inline-avatar d-flex align-items-center">
                                                <div class="avatar bg-gray200">
                                                    <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $result->quiz->creator->getAvatar() }}" class="img-cover" alt="">
                                                </div>
                                                <div class=" ml-5">
                                                    <span class="d-block">{{ $result->quiz->creator->full_name }}</span>
                                                    <span class="mt-5 font-12 text-gray d-block">{{ $result->quiz->creator->email }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left">
                                            <span class="d-block">{{ $result->quiz->title }}</span>
                                            <span class="font-12 text-gray d-block">{{ $result->quiz->webinar->title }}</span>
                                        </td>
                                        <td class="align-middle">{{ $result->quiz->quizQuestions->sum('grade') }}</td>

                                        <td class="align-middle">{{ $result->user_grade }}</td>

                                        <td class="align-middle">
                                            @php
                                                $statusClass = ($result->status == 'passed') ? 'success' : ($result->status == 'waiting' ? 'warning' : 'danger');
                                                $statusText = trans('quiz.'.$result->status);
                                            @endphp
                                            <span class="status-badge bg-glass-{{ $statusClass }} text-{{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>

                                            @if($result->status =='failed' and $result->can_try)
                                                <span class="d-block font-11 text-gray mt-5">{{ trans('quiz.quiz_chance_remained',['count' => $result->count_can_try]) }}</span>
                                            @endif
                                        </td>

                                        <td class="align-middle">
                                            <div class="d-flex align-items-center text-gray font-13">
                                                <i data-feather="calendar" width="14" height="14" class="mr-5"></i>
                                                {{ dateTimeFormat($result->created_at,'j M Y') }}
                                            </div>
                                            <div class="font-11 text-gray mt-5">{{ dateTimeFormat($result->created_at,'H:i') }}</div>
                                        </td>

                                        <td class="align-middle text-right font-weight-normal">
                                            <div class="btn-group dropdown table-actions table-actions-lg table-actions-lg">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-vertical" height="20"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    @if(!$result->can_try and $result->status != 'waiting')
                                                        <a href="/panel/quizzes/{{ $result->id }}/result" class="webinar-actions d-block mt-10">{{ trans('public.view_answers') }}</a>
                                                    @endif

                                                    @if($result->status != 'passed')
                                                        @if($result->can_try)
                                                            <a href="/panel/quizzes/{{ $result->quiz->id }}/start" class="webinar-actions d-block mt-10">{{ trans('public.try_again') }}</a>
                                                        @endif
                                                    @endif

                                                    <a href="{{ $result->quiz->webinar->getUrl() }}" class="webinar-actions d-block mt-10">{{ trans('webinars.webinar_page') }}</a>
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
        {{ $quizzesResults->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/moment.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script  src="{{ config('app.js_css_url') }}/assets/default/js/panel/quiz_list.min.js"></script>
@endpush
