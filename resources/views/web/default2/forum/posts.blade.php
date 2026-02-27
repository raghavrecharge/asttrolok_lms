@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --chat-bg: #fdfdfd;
            --bubble-me: linear-gradient(135deg, #43d477 0%, #2ecc71 100%);
            --bubble-other: #ffffff;
            --text-me: #ffffff;
            --text-other: #1e293b;
        }

        /* Summernote Forced Light Mode & Icon Fix */
        @font_face {
            font-family: "summernote";
            src: url("/assets/vendors/summernote/font/summernote.eot");
            src: url("/assets/vendors/summernote/font/summernote.eot#iefix") format("embedded-opentype"),
                 url("/assets/vendors/summernote/font/summernote.woff2") format("woff2"),
                 url("/assets/vendors/summernote/font/summernote.woff") format("woff"),
                 url("/assets/vendors/summernote/font/summernote.ttf") format("truetype");
        }

        .note-editor.note-frame {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            background: #fff !important;
        }
        
        .note-editable {
            background-color: #ffffff !important;
            color: #1e293b !important;
        }

        .note-toolbar { background: #f8fafc !important; border-bottom: 1px solid #eef2f7 !important; }

        .note-btn {
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 6px !important;
            color: #475569 !important;
        }
        
        .note-btn [class^="note-icon-"], .note-btn [class*=" note-icon-"] {
            font-family: "summernote" !important;
        }

        /* Chat Layout */
        /* Modern Forum Suggestion System Styles */
        .forum-suggestion-container {
            background: #fff;
            border-radius: 20px;
            /* Removed overflow: hidden to allow dropdowns to pop out */
            box-shadow: 0 10px 40px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
            margin-bottom: 30px;
            position: relative;
        }

        .forum-header-section {
            background: #fff;
            border-bottom: 2px solid #f8fafc;
            padding: 20px 30px;
        }

        .forum-posts-list {
            padding: 30px;
            background: #fdfdfd;
        }

        .forum-input-section {
            background: #fff;
            padding: 25px 30px;
            border-top: 2px solid #f8fafc;
            border-radius: 0 0 20px 20px;
        }

        /* Summernote Custom Styling for Suggestion Box */
        .suggestion-input-box .note-editor.note-frame {
            border: 1.5px solid #edf2f7 !important;
            border-radius: 16px !important;
            overflow: hidden !important;
            box-shadow: none !important;
            transition: all 0.3s ease;
        }

        .suggestion-input-box .note-editor.note-frame:focus-within {
            border-color: #43d477 !important;
            box-shadow: 0 10px 25px rgba(67, 212, 119, 0.08) !important;
        }

        .suggestion-input-box .note-toolbar {
            background: #f8fafc !important;
            border-bottom: 1.5px solid #edf2f7 !important;
            padding: 10px 15px !important;
        }

        .suggestion-input-box .note-editable {
            background: #fff !important;
            min-height: 150px !important;
            padding: 20px !important;
            font-size: 15px !important;
            line-height: 1.6 !important;
        }

        .suggestion-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-attachment-custom {
            color: #64748b;
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: all 0.2s;
        }

        .btn-attachment-custom:hover {
            background: #f8fafc;
            color: #1e293b;
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }

        .btn-send-suggestion {
            background: #000;
            color: #fff !important;
            border: none;
            padding: 12px 35px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .btn-send-suggestion:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            opacity: 0.9;
        }

        .forum-sidebar-refined {
            position: sticky;
            top: 100px;
        }

        .topics-right-side-title:after {
            content: "";
            width: 30px;
            height: 2px;
            background: var(--primary);
            position: absolute;
            left: 0;
            bottom: -8px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid mb-50">
        <div class="forum-suggestion-container">
            {{-- Header Section Inside Forum Box --}}
            <div class="forum-header-section">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        {{-- Back Button --}}
                        <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-transparent p-5 mr-15 text-gray hover-primary" title="Back">
                            <i data-feather="arrow-left" width="22" height="22"></i>
                        </a>

                        <div class="d-flex flex-column">
                            <h2 class="font-18 font-weight-bold text-secondary mb-0">{{ $topic->title }}</h2>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <button type="button" data-action="{{ $topic->getBookmarkUrl() }}" class="{{ !empty($authUser) ? 'js-topic-bookmark' : 'login-to-access' }} btn-transparent {{ $topic->bookmarked ? 'text-warning' : '' }} d-flex flex-column align-items-center px-10">
                            <i data-feather="bookmark" width="20" height="20" class="{{ $topic->bookmarked ? 'text-warning' : 'text-gray' }}"></i>
                            <span class="font-10 mt-5 {{ $topic->bookmarked ? 'text-warning' : 'text-gray' }}">{{ trans('update.bookmark') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="forum-posts-list">
                {{-- Starting Topic Message --}}
                @include('web.default.forum.post_card')

                {{-- Topic Posts --}}
                @if(!empty($topic->posts) and count($topic->posts))
                    @foreach($topic->posts as $postRow)
                        @include('web.default.forum.post_card', ['post' => $postRow])
                    @endforeach
                @endif
            </div>

            <div class="forum-input-section">
                @if(!auth()->check())
                    <div class="text-center py-20 bg-info-light rounded-lg">
                        <p class="font-14 text-gray mb-10">{{ trans('update.login_to_reply_hint') }}</p>
                        <a href="/login" class="btn btn-primary btn-sm px-30">{{ trans('auth.login') }}</a>
                    </div>
                @elseif($topic->close or $forum->close)
                    <div class="text-center py-20 bg-danger-light rounded-lg border border-danger">
                        <p class="font-14 text-danger font-weight-bold mb-0">
                            <i data-feather="lock" width="16" height="16" class="mr-5"></i>
                            {{ trans('update.topic_closed') }}
                        </p>
                    </div>
                @else
                    <form action="{{ $topic->getPostsUrl() }}" method="post" id="chat-reply-form">
                        {{ csrf_field() }}

                        <div class="topic-posts-reply-card d-none position-relative px-15 py-10 rounded-lg bg-info-light mb-15 border border-info">
                            <input type="hidden" name="reply_post_id" class="js-reply-post-id">
                            <div class="js-reply-post-title font-12 font-weight-bold text-secondary">Replying to message...</div>
                            <button type="button" class="js-close-reply-post btn-transparent position-absolute" style="top:5px; right:5px;">
                                <i data-feather="x" width="16" height="16" class="text-danger"></i>
                            </button>
                        </div>

                        <div class="suggestion-input-box">
                            <textarea id="summernote" name="description" class="form-control" placeholder="Write your suggestion or reply here..."></textarea>
                            
                            <div class="suggestion-footer">
                                <div class="suggestion-attachments">
                                    <button type="button" class="panel-file-manager btn-attachment-custom" data-input="postAttachmentInput" data-preview="holder">
                                        <i data-feather="paperclip" width="16" height="16" class="mr-8"></i>
                                        <span>Attach Files</span>
                                    </button>
                                    <input type="hidden" name="attach" id="postAttachmentInput" value=""/>
                                </div>

                                <button type="button" id="sendMessage" class="js-save-post btn-send-suggestion">
                                    <span>Post Suggestion</span>
                                    <i data-feather="send" width="16" height="16" class="ml-10"></i>
                                </button>
                            </div>
                        </div>
                        <div id="holder" class="font-11 text-gray mt-5 ml-15"></div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div id="topicReportModal" class="d-none">
        <h3 class="section-title after-line font-20 text-dark-blue">{{ trans('panel.report') }}</h3>

        <form action="{{ $topic->getPostsUrl() }}/report" method="post" class="mt-25">
            <input type="hidden" name="item_id" class="js-item-id-input"/>
            <input type="hidden" name="item_type" class="js-item-type-input"/>

            <div class="form-group">
                <label class="text-dark-blue font-14" for="message_to_reviewer">{{ trans('public.message_to_reviewer') }}</label>
                <textarea name="message" id="message_to_reviewer" class="form-control" rows="10"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <p class="text-gray font-16">{{ trans('product.report_modal_hint') }}</p>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class="js-topic-report-submit btn btn-sm btn-primary">{{ trans('panel.report') }}</button>
                <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        var replyToTopicSuccessfullySubmittedLang = '{{ trans('update.reply_to_topic_successfully_submitted') }}'
        var reportSuccessfullySubmittedLang = '{{ trans('update.report_successfully_submitted') }}';
        var changesSavedSuccessfullyLang = '{{ trans('update.changes_saved_successfully') }}';
        var oopsLang = '{{ trans('update.oops') }}';
        var somethingWentWrongLang = '{{ trans('update.something_went_wrong') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var descriptionLang = '{{ trans('public.description') }}';
        var editAttachmentLabelLang = '{{ trans('update.attach_a_file') }} ({{ trans('public.optional') }})';
        var sendLang = '{{ trans('update.send') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var topicBookmarkedSuccessfullyLang = '{{ trans('update.topic_bookmarked_successfully') }}';
        var topicUnBookmarkedSuccessfullyLang = '{{ trans('update.topic_un_bookmarked_successfully') }}';
        var editPostLang = '{{ trans('update.edit_post') }}';

    <script src="/assets/default/vendors/summernote/summernote-bs4.min.js"></script>

    <script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                // Initialize Summernote
                if ($('#summernote').length) {
                    $('#summernote').summernote({
                        placeholder: 'Write your suggestion or reply here...',
                        tabsize: 2,
                        height: 150,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'clear']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $('#summernote').val(contents);
                            }
                        }
                    });
                }

                // Handle Reply Button (Sync with core logic)
                $('body').on('click', '.js-reply-post-btn', function(e) {
                    if ($('#summernote').length) {
                        $('#summernote').summernote('focus');
                        
                        $('html, body').animate({
                            scrollTop: $(".forum-input-section").offset().top - 100
                        }, 500);
                    }
                });

                // Update Like Button Visuals (Instant feedback only - real action handled by core script)
                $('body').on('click', '.js-topic-post-like', function() {
                    const $btn = $(this);
                    const $icon = $btn.find('.heart-icon');
                    const $count = $btn.find('.js-like-count');
                    
                    if ($btn.hasClass('active')) {
                        $btn.removeClass('active');
                        $icon.removeClass('fill-danger text-danger').addClass('text-gray');
                        $count.removeClass('text-danger').addClass('text-gray');
                    } else {
                        $btn.addClass('active');
                        $icon.addClass('fill-danger text-danger').removeClass('text-gray');
                        $count.addClass('text-danger').removeClass('text-gray');
                    }
                    
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                });

                // Periodic feather check
                setInterval(function() {
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }, 2000);
            });
        })(jQuery);
    </script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/topic_posts.min.js"></script>
@endpush
