@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .fm-stat-card {
            background: #fff; border-radius: 16px; padding: 22px;
            display: flex; align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f0f0f0;
            height: 100%; transition: all 0.3s ease;
        }
        .fm-stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.07); }
        .fm-stat-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 16px; flex-shrink: 0;
        }
        .fm-stat-icon.blue { background: rgba(37,99,235,0.1); color: #2563eb; }
        .fm-stat-icon.red { background: rgba(239,68,68,0.1); color: #ef4444; }
        .fm-stat-icon.green { background: rgba(34,197,94,0.1); color: #22c55e; }
        .fm-stat-value { font-size: 22px; font-weight: 800; color: #1f3b64; line-height: 1.2; display: block; }
        .fm-stat-label { font-size: 12px; font-weight: 600; color: #8c98a4; display: block; margin-top: 2px; }
        .fm-filter-card {
            background: #fff; border-radius: 16px; padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: 1px solid #f0f0f0;
        }
        .fm-table-card {
            background: #fff; border-radius: 16px; padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #f0f0f0; overflow: hidden;
        }
        .fm-table-card .table { margin-bottom: 0; }
        .fm-table-card .table thead th {
            background: #f8fafc; border-bottom: 2px solid #eef2f7;
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.5px; color: #8c98a4; padding: 14px 16px;
        }
        .fm-table-card .table tbody td {
            padding: 14px 16px; border-bottom: 1px solid #f5f7fa;
            font-size: 13px; vertical-align: middle;
        }
        .fm-table-card .table tbody tr:last-child td { border-bottom: none; }
        .fm-table-card .table tbody tr:hover { background: #fafbfd; }
        .fm-topic-title {
            font-size: 14px; font-weight: 600; color: #1f3b64;
            text-decoration: none; transition: color 0.2s;
        }
        .fm-topic-title:hover { color: #2563eb; text-decoration: none; }
        .fm-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 10px;
            font-weight: 700; display: inline-block;
        }
        .fm-badge.published { background: #e8f5e9; color: #2e7d32; }
        .fm-badge.closed { background: #ffebee; color: #c62828; }
        .fm-forum-tag {
            padding: 3px 10px; border-radius: 8px; font-size: 11px;
            font-weight: 600; background: #f0f3ff; color: #1f3b64;
            display: inline-block;
        }
        .fm-posts-count {
            background: #f0f3ff; color: #2563eb; font-weight: 700;
            font-size: 12px; padding: 3px 10px; border-radius: 8px;
            display: inline-block;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('update.topics_statistics') }}</h2>
        <div class="row stat-card-row mt-20">
            <div class="col-12 col-md-4">
                <div class="fm-stat-card">
                    <div class="fm-stat-icon blue"><i data-feather="message-circle" width="22" height="22"></i></div>
                    <div>
                        <span class="fm-stat-value">{{ $publishedTopics }}</span>
                        <span class="fm-stat-label">{{ trans('update.published_topics') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-15 mt-md-0">
                <div class="fm-stat-card">
                    <div class="fm-stat-icon red"><i data-feather="lock" width="22" height="22"></i></div>
                    <div>
                        <span class="fm-stat-value">{{ $lockedTopics }}</span>
                        <span class="fm-stat-label">{{ trans('update.locked_topics') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mt-15 mt-md-0">
                <div class="fm-stat-card">
                    <div class="fm-stat-icon green"><i data-feather="message-square" width="22" height="22"></i></div>
                    <div>
                        <span class="fm-stat-value">{{ $topicMessages }}</span>
                        <span class="fm-stat-label">{{ trans('update.topic_messages') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25 panel-filter-section">
        <h2 class="section-title">{{ trans('update.filter_topics') }}</h2>
        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/forums/topics" method="get" class="row">
                {{-- From & To --}}
                <div class="col-12 col-lg-4 mb-15 mb-lg-0">
                    <div class="row">
                        <div class="col-6">
                            <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                                <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.from') }}
                            </label>
                            <div style="position:relative;">
                                <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                    <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                                </div>
                                <input type="text" name="from" autocomplete="off"
                                       class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                                       style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                       value="{{ request()->get('from','') }}"/>
                            </div>
                        </div>
                        <div class="col-6">
                            <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                                <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.to') }}
                            </label>
                            <div style="position:relative;">
                                <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                    <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                                </div>
                                <input type="text" name="to" autocomplete="off"
                                       class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                                       style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                       value="{{ request()->get('to','') }}"/>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Forums --}}
                <div class="col-12 col-lg-4 mb-15 mb-lg-0">
                    <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                        <i data-feather="message-circle" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('update.forums') }}
                    </label>
                    <div style="position:relative;">
                        <select name="forum_id" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="all">{{ trans('public.all') }}</option>
                            @foreach($forums as $forum)
                                @if(!empty($forum->subForums) and count($forum->subForums))
                                    <optgroup label="{{ $forum->title }}">
                                        @foreach($forum->subForums as $subForum)
                                            <option value="{{ $subForum->id }}" {{ (request()->get('forum_id') == $subForum->id) ? 'selected' : '' }}>{{ $subForum->title }}</option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="{{ $forum->id }}" {{ (request()->get('forum_id') == $forum->id) ? 'selected' : '' }}>{{ $forum->title }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                            <i data-feather="chevron-down" width="13" height="13"></i>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="col-12 col-lg-2 mb-15 mb-lg-0">
                    <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                        <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                    </label>
                    <div style="position:relative;">
                        <select name="status" class="form-control" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                            <option value="all">{{ trans('public.all') }}</option>
                            <option value="published" @if(request()->get('status') == 'published') selected @endif>{{ trans('public.published') }}</option>
                            <option value="closed" @if(request()->get('status') == 'closed') selected @endif>{{ trans('panel.closed') }}</option>
                        </select>
                        <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                            <i data-feather="chevron-down" width="13" height="13"></i>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="col-12 col-lg-2 d-flex align-items-end">
                    <button type="submit" class="w-100" style="height:40px;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                        <i data-feather="search" width="13" height="13"></i>
                        {{ trans('public.show_results') }}
                    </button>
                </div>
            </form>
        </div>

    </section>

    <section class="mt-30">
        <h2 class="section-title">{{ trans('update.my_topics') }}</h2>

        @if($topics->count() > 0)
            <div class="fm-table-card mt-15">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                        <tr>
                            <th class="text-left">{{ trans('public.title') }}</th>
                            <th class="text-center d-none d-lg-table-cell">{{ trans('update.forum') }}</th>
                            <th class="text-center d-none d-md-table-cell">{{ trans('site.posts') }}</th>
                            <th class="text-center">{{ trans('public.status') }}</th>
                            <th class="text-center d-none d-md-table-cell">{{ trans('public.publish_date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topics as $topic)
                            <tr>
                                <td class="text-left">
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <div style="width:36px;height:36px;border-radius:10px;background:#f0f3ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <img src="{{ config('app.img_dynamic_url') }}{{ $topic->forum->icon }}" style="width:20px;height:20px;object-fit:contain;" alt="">
                                        </div>
                                        <a href="{{ $topic->getPostsUrl() }}" target="_blank" class="fm-topic-title">{{ $topic->title }}</a>
                                    </div>
                                </td>
                                <td class="text-center d-none d-lg-table-cell"><span class="fm-forum-tag">{{ $topic->forum->title }}</span></td>
                                <td class="text-center d-none d-md-table-cell"><span class="fm-posts-count">{{ $topic->posts_count }}</span></td>
                                <td class="text-center">
                                    @if($topic->close)
                                        <span class="fm-badge closed">{{ trans('panel.closed') }}</span>
                                    @else
                                        <span class="fm-badge published">{{ trans('public.published') }}</span>
                                    @endif
                                </td>
                                <td class="text-center d-none d-md-table-cell" style="font-size:12px;color:#6c757d;white-space:nowrap;">{{ dateTimeFormat($topic->created_at, 'j M Y H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'quiz.png',
                'title' => trans('update.panel_topics_no_result'),
                'hint' => nl2br(trans('update.panel_topics_no_result_hint')),
                'btn' => ['url' => '/forums','text' => trans('update.forums')]
            ])
        @endif
    </section>

    <div class="my-30">
        {{ $topics->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
@endpush
