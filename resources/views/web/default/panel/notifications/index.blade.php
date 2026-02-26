@push('styles_top')
    <style>
        .notification-card {
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid #f8f8f8;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-color: #1f3b64;
        }
        .notification-card.unread {
            border-left: 4px solid #f46e6e;
            background: rgba(244, 110, 110, 0.02);
        }
        .notification-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(31, 59, 100, 0.05);
            color: #1f3b64;
            margin-right: 15px;
        }
        .notification-title {
            font-size: 16px;
            font-weight: 800;
            color: #1f3b64;
            margin-bottom: 2px;
        }
        .notification-time {
            font-size: 12px;
            color: #8c98a4;
            font-weight: 500;
        }
        .notification-message-preview {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
        }
        .view-notification-btn {
            background: #f8faff;
            color: #1f3b64;
            border: none;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        .view-notification-btn:hover {
            background: #1f3b64;
            color: #fff;
        }
        .view-notification-btn.seen {
            opacity: 0.6;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="section-title">{{ trans('panel.notifications') }}</h2>

            <a href="/panel/notifications/mark-all-as-read" class="delete-action d-flex align-items-center cursor-pointer text-hover-primary" data-title="{{ trans('update.convert_unread_messages_to_read') }}" data-confirm="{{ trans('update.yes_convert') }}">
                <i data-feather="check-square" width="20" height="20" class="text-primary"></i>
                <span class="ml-8 font-14 font-weight-bold text-primary">{{ trans('update.mark_all_notifications_as_read') }}</span>
            </a>
        </div>

        @if(!empty($notifications) and !$notifications->isEmpty())
            <div class="row">
                @foreach($notifications as $notification)
                    <div class="col-12 mt-20">
                        <div class="notification-card {{ empty($notification->notificationStatus) ? 'unread' : '' }}">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-4 d-flex align-items-center">
                                    <div class="notification-icon-box">
                                        <i data-feather="{{ empty($notification->notificationStatus) ? 'mail' : 'book-open' }}" width="22" height="22"></i>
                                    </div>
                                    <div>
                                        <h3 class="notification-title">{{ $notification->title }}</h3>
                                        <span class="notification-time">{{ dateTimeFormat($notification->created_at,'j M Y | H:i') }}</span>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-5 mt-15 mt-lg-0">
                                    <span class="notification-message-preview">{!! truncate($notification->message, 120, true) !!}</span>
                                </div>

                                <div class="col-12 col-lg-3 mt-15 mt-lg-0 text-right">
                                    <button type="button" data-id="{{ $notification->id }}" id="showNotificationMessage{{ $notification->id }}" class="js-show-message view-notification-btn {{ !empty($notification->notificationStatus) ? 'seen' : '' }}">
                                        {{ trans('public.view') }}
                                        <i data-feather="eye" width="14" height="14" class="ml-5"></i>
                                    </button>
                                    <input type="hidden" class="notification-message" value="{!! $notification->message !!}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="my-30">
                {{ $notifications->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        @else
            @include(getTemplate() . '.includes.no-result',[
               'file_name' => 'webinar.png',
               'title' => trans('panel.notification_no_result'),
               'hint' => nl2br(trans('panel.notification_no_result_hint')),
           ])
        @endif
    </section>

    <div class="mt-5 d-none" id="messageModal">
        <div class="text-left py-20 px-10">
            <h3 class="modal-title font-18 font-weight-bold text-dark-blue mb-5"></h3>
            <div class="d-flex align-items-center mb-20 pb-15 border-bottom">
                <i data-feather="clock" width="14" height="14" class="text-gray mr-5"></i>
                <span class="modal-time font-12 text-gray"></span>
            </div>
            <div class="modal-message text-gray font-15 line-height-16"></div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script >
        (function ($) {
            "use strict";

            @if(!empty(request()->get('notification')))
            setTimeout(() => {
                $('body #showNotificationMessage{{ request()->get('notification') }}').trigger('click');

                let url = window.location.href;
                url = url.split('?')[0];
                window.history.pushState("object or string", "Title", url);
            }, 400);
            @endif
        })(jQuery)
    </script>

    <script  src="{{ config('app.js_css_url') }}/assets/default/js/panel/notifications.min.js"></script>
@endpush
