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
        <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/forums/posts" method="get">
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

                    {{-- Forums --}}
                    <div style="flex:1 1 200px;min-width:180px;">
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
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <div style="position:relative;width:130px;">
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
