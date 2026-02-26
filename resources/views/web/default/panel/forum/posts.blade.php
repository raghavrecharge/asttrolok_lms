@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
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
            text-decoration: none; transition: color 0.2s; display: block;
        }
        .fm-topic-title:hover { color: #2563eb; text-decoration: none; }
        .fm-topic-author { font-size: 11px; color: #8c98a4; margin-top: 2px; }
        .fm-forum-tag {
            padding: 3px 10px; border-radius: 8px; font-size: 11px;
            font-weight: 600; background: #f0f3ff; color: #1f3b64; display: inline-block;
        }
        .fm-replies-count {
            background: #f0f3ff; color: #2563eb; font-weight: 700;
            font-size: 12px; padding: 3px 10px; border-radius: 8px; display: inline-block;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('update.filter_posts') }}</h2>
        <div class="fm-filter-card mt-15">
            <form action="/panel/forums/posts" method="get" class="row align-items-end">
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
        <h2 class="section-title">{{ trans('update.my_posts') }}</h2>

        @if($posts->count() > 0)
            <div class="fm-table-card mt-15">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                        <tr>
                            <th class="text-left">{{ trans('public.topic') }}</th>
                            <th class="text-center">{{ trans('update.forum') }}</th>
                            <th class="text-center">{{ trans('update.replies') }}</th>
                            <th class="text-center">{{ trans('public.publish_date') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($posts as $post)
                            <tr>
                                <td class="text-left">
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid #f0f3ff;">
                                            <img src="{{ config('app.img_dynamic_url') }}{{ $post->topic->creator->getAvatar(48) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                                        </div>
                                        <div>
                                            <a href="{{ $post->topic->getPostsUrl() }}" target="_blank" class="fm-topic-title">{{ $post->topic->title }}</a>
                                            <div class="fm-topic-author">{{ trans('public.by') }} {{ $post->topic->creator->full_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><span class="fm-forum-tag">{{ $post->topic->forum->title }}</span></td>
                                <td class="text-center"><span class="fm-replies-count">{{ $post->replies_count }}</span></td>
                                <td class="text-center" style="font-size:12px;color:#6c757d;white-space:nowrap;">{{ dateTimeFormat($post->created_at, 'j M Y H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'comment.png',
                'title' => trans('update.panel_topics_posts_no_result'),
                'hint' => nl2br(trans('update.panel_topics_posts_no_result_hint')),
            ])
        @endif
    </section>

    <div class="my-30">
        {{ $posts->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
@endpush
