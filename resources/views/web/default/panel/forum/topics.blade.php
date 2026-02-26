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
        <div class="row mt-20">
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

    <section class="mt-25">
        <h2 class="section-title">{{ trans('update.filter_topics') }}</h2>
        <div class="fm-filter-card mt-15">
            <form action="/panel/forums/topics" method="get" class="row align-items-end">
                <div class="col-12 col-lg-5">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-10">
                                <label class="input-label" style="font-size:12px;font-weight:600;color:#6c757d;">{{ trans('public.from') }}</label>
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
                            <div class="form-group mb-10">
                                <label class="input-label" style="font-size:12px;font-weight:600;color:#6c757d;">{{ trans('public.to') }}</label>
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
                <div class="col-12 col-lg-5">
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <div class="form-group mb-10">
                                <label class="input-label" style="font-size:12px;font-weight:600;color:#6c757d;">{{ trans('update.forums') }}</label>
                                <select name="forum_id" class="form-control" data-placeholder="{{ trans('public.all') }}">
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
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="form-group mb-10">
                                <label class="input-label" style="font-size:12px;font-weight:600;color:#6c757d;">{{ trans('public.status') }}</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="all">{{ trans('public.all') }}</option>
                                    <option value="published" @if(request()->get('status') == 'published') selected @endif>{{ trans('public.published') }}</option>
                                    <option value="closed" @if(request()->get('status') == 'closed') selected @endif>{{ trans('panel.closed') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-center justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100" style="border-radius:10px;height:42px;font-weight:600;">{{ trans('public.show_results') }}</button>
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
                            <th class="text-center">{{ trans('update.forum') }}</th>
                            <th class="text-center">{{ trans('site.posts') }}</th>
                            <th class="text-center">{{ trans('public.status') }}</th>
                            <th class="text-center">{{ trans('public.publish_date') }}</th>
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
                                <td class="text-center"><span class="fm-forum-tag">{{ $topic->forum->title }}</span></td>
                                <td class="text-center"><span class="fm-posts-count">{{ $topic->posts_count }}</span></td>
                                <td class="text-center">
                                    @if($topic->close)
                                        <span class="fm-badge closed">{{ trans('panel.closed') }}</span>
                                    @else
                                        <span class="fm-badge published">{{ trans('public.published') }}</span>
                                    @endif
                                </td>
                                <td class="text-center" style="font-size:12px;color:#6c757d;white-space:nowrap;">{{ dateTimeFormat($topic->created_at, 'j M Y H:i') }}</td>
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
