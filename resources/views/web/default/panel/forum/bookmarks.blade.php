@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
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
        .fm-remove-btn {
            width: 34px; height: 34px; border-radius: 10px;
            background: #fff1f2; border: 1px solid #fecdd3;
            display: inline-flex; align-items: center; justify-content: center;
            transition: all 0.2s ease; color: #e11d48;
        }
        .fm-remove-btn:hover { background: #ffe4e6; border-color: #fda4af; transform: scale(1.08); }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ trans('update.bookmarks') }}</h2>

        @if($topics->count() > 0)
            <div class="fm-table-card mt-20">
                <div class="table-responsive">
                    <table class="table text-center">
                        <thead>
                        <tr>
                            <th class="text-left">{{ trans('public.topic') }}</th>
                            <th class="text-center">{{ trans('update.forum') }}</th>
                            <th class="text-center">{{ trans('update.replies') }}</th>
                            <th class="text-center">{{ trans('public.publish_date') }}</th>
                            <th class="text-center" style="width:60px;"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($topics as $topic)
                            <tr>
                                <td class="text-left">
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid #f0f3ff;">
                                            <img src="{{ config('app.img_dynamic_url') }}{{ $topic->creator->getAvatar(48) }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                                        </div>
                                        <div>
                                            <a href="{{ $topic->getPostsUrl() }}" target="_blank" class="fm-topic-title">{{ $topic->title }}</a>
                                            <div class="fm-topic-author">{{ trans('public.by') }} {{ $topic->creator->full_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><span class="fm-forum-tag">{{ $topic->forum->title }}</span></td>
                                <td class="text-center"><span class="fm-replies-count">{{ $topic->posts_count }}</span></td>
                                <td class="text-center" style="font-size:12px;color:#6c757d;white-space:nowrap;">{{ dateTimeFormat($topic->created_at, 'j M Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="/panel/forums/topics/{{ $topic->id }}/removeBookmarks"
                                       data-title="{{ trans('update.this_topic_will_be_removed_from_your_bookmark') }}"
                                       data-confirm="{{ trans('update.confirm') }}"
                                       class="fm-remove-btn delete-action">
                                        <i data-feather="bookmark" width="16" height="16"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
                'file_name' => 'comment.png',
                'title' => trans('update.panel_topics_bookmark_no_result'),
                'hint' => nl2br(trans('update.panel_topics_bookmark_no_result_hint')),
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
