@php
    $cardUser = !empty($post) ? $post->user : $topic->creator;
    $isMe = (auth()->check() and auth()->id() == $cardUser->id);
    $isTopicCreator = ($cardUser->id == $topic->creator_id);
@endphp

<div class="topics-post-card forum-suggestion-card mb-30 w-100">
    <div class="suggestion-card-wrapper bg-white rounded-xl shadow-sm border border-light p-20 p-lg-25 position-relative transition-all hover-shadow">
        
        {{-- User Info Header --}}
        <div class="d-flex align-items-center justify-content-between mb-20">
            <div class="d-flex align-items-center">
                <div class="suggestion-avatar rounded-circle overflow-hidden mr-12 shadow-sm" style="width: 50px; height: 50px; border: 2.5px solid {{ $isTopicCreator ? '#43d477' : '#f1f5f9' }};">
                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{$cardUser->getAvatar(50)}}" class="img-cover" alt="{{ $cardUser->full_name }}">
                </div>
                <div class="d-flex flex-column">
                    <span class="font-16 font-weight-bold text-secondary lh-1.2">{{ $cardUser->full_name }}</span>
                    <div class="d-flex align-items-center mt-2">
                        <span class="font-12 text-gray">{{ dateTimeFormat(!empty($post) ? $post->created_at : $topic->created_at, 'j M Y | H:i') }}</span>
                        @if($isTopicCreator)
                            <span class="badge badge-primary-light text-primary font-11 ml-10 px-10 py-2">Author</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="suggestion-actions-top d-flex align-items-center">
                {{-- Premium Like Button (Pill Style) --}}
                <div class="topic-post-like-btn mr-15">
                    <button type="button" class="{{ !empty($authUser) ? 'js-topic-post-like' : 'login-to-access' }} btn-suggestion-like d-flex align-items-center px-15 py-8 rounded-pill transition-all {{ ((!empty($post) and in_array($post->id,$likedPostsIds)) or (empty($post) and $topic->liked)) ? 'active' : '' }}" data-action="{{ !empty($post) ? $post->getLikeUrl($forum->slug,$topic->slug) : $topic->getLikeUrl($forum->slug) }}">
                        <i data-feather="heart" width="18" height="18" class="heart-icon {{ ((!empty($post) and in_array($post->id,$likedPostsIds)) or (empty($post) and $topic->liked)) ? 'fill-danger text-danger' : 'text-gray' }}"></i>
                        <span class="font-14 font-weight-bold ml-8 js-like-count {{ ((!empty($post) and in_array($post->id,$likedPostsIds)) or (empty($post) and $topic->liked)) ? 'text-danger' : 'text-gray' }}">{{ !empty($post) ? $post->likes->count() : $topic->likes->count() }}</span>
                    </button>
                </div>

                @if(!empty($authUser) and !$topic->close)
                    <div class="dropdown custom-dropdown">
                        <button class="btn btn-transparent p-5 text-gray hover-secondary transition-all" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i data-feather="more-vertical" width="22" height="22"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right shadow-20 border-0 rounded-15 py-10 mt-10">
                            @if($authUser->id == $cardUser->id)
                                <a class="dropdown-item js-post-edit font-14" href="javascript:void(0)" data-action="{{ !empty($post) ? $post->getEditUrl($forum->slug,$topic->slug) : $topic->getEditUrl($forum->slug) }}">
                                    <i data-feather="edit-2" width="16" height="16" class="mr-12 text-primary"></i> {{ trans('public.edit') }}
                                </a>
                            @endif
                            @if(!empty($post) and $authUser->id == $topic->creator_id)
                                <a class="dropdown-item font-14 js-btn-post-pin-un-pin {{ $post->pin ? 'js-btn-post-un-pin' : 'js-btn-post-pin' }}" href="javascript:void(0)" data-action="{{ $topic->getPostsUrl() }}/{{ $post->id }}/{{ $post->pin ? 'un_pin' : 'pin' }}">
                                    <i data-feather="pin" width="16" height="16" class="mr-12 {{ $post->pin ? 'text-warning' : 'text-gray' }}"></i> {{ $post->pin ? trans('update.un_pin') : trans('update.pin') }}
                                </a>
                            @endif
                            @if(!empty($post))
                                <a class="dropdown-item js-reply-post-btn font-14" href="javascript:void(0)" data-id="{{ $post->id }}" data-name="{{ $cardUser->full_name }}">
                                    <i data-feather="corner-up-left" width="16" height="16" class="mr-12 text-secondary"></i> {{ trans('panel.reply') }}
                                </a>
                            @endif
                            <a class="dropdown-item js-topic-post-report font-14" href="javascript:void(0)" data-id="{{ !empty($post) ? $post->id : $topic->id }}" data-type="{{ !empty($post) ? 'topic_post' : 'topic' }}">
                                <i data-feather="alert-triangle" width="16" height="16" class="mr-12 text-danger"></i> {{ trans('panel.report') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quote/Reply section if applicable --}}
        @if(!empty($post) and !empty($post->parent))
            <div class="suggestion-quotation p-15 rounded-15 mb-20 bg-light border-left-primary shadow-xs">
                <div class="font-13 font-weight-bold text-primary mb-5 d-flex align-items-center">
                    <i data-feather="corner-up-right" width="14" height="14" class="mr-8"></i>
                    {{ trans('update.reply_to') }} {{ $post->parent->user->full_name }}
                </div>
                <div class="font-13 text-gray lh-1.6 italic">{!! truncate(strip_tags($post->parent->description), 150) !!}</div>
            </div>
        @endif

        {{-- Content Area --}}
        <div class="suggestion-content-area pl-lg-62 mt-5">
            <div class="topic-post-description font-15 text-secondary lh-1.8">
                {!! !empty($post) ? $post->description : $topic->description !!}
            </div>

            {{-- Attachments --}}
            @php
                $attachments = !empty($post) ? ( !empty($post->attach) ? [$post] : [] ) : ($topic->attachments ?? []);
            @endphp

            @if(count($attachments))
                <div class="mt-25 d-flex flex-wrap">
                    @foreach($attachments as $attachment)
                        @php
                            $url = !empty($post) ? $post->getAttachmentUrl($forum->slug, $topic->slug) : $attachment->getDownloadUrl($forum->slug, $topic->slug);
                            $name = !empty($post) ? $post->getAttachmentName() : $attachment->getName();
                        @endphp
                        <a href="{{ $url }}" target="_blank" class="attachment-item d-inline-flex align-items-center bg-light px-15 py-10 rounded-12 mr-10 mb-10 border border-transparent transition-all hover-border-primary shadow-xs">
                            <i data-feather="file" width="16" height="16" class="mr-10 text-primary"></i>
                            <span class="font-13 font-weight-bold text-secondary">{{ truncate($name, 30) }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .forum-suggestion-card {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 15px 45px rgba(0,0,0,0.06) !important;
        transform: translateY(-3px);
    }

    .suggestion-card-wrapper {
        border-radius: 20px;
    }

    .border-left-primary {
        border-left: 4px solid #43d477 !important;
    }

    .bg-light {
        background-color: #f8fafc !important;
    }

    .attachment-item:hover {
        background-color: #edf2f7 !important;
        transform: translateY(-1px);
    }

    .btn-suggestion-like {
        border: 1px solid #f1f5f9;
        background: #fff;
        transition: all 0.2s;
        min-width: 65px; /* Ensure space for heart and count */
        justify-content: center;
    }

    .btn-suggestion-like:hover {
        background: #fdf2f2 !important;
        border-color: #fee2e2 !important;
    }

    .btn-suggestion-like.active {
        background: #fef2f2 !important;
        border-color: #fecaca !important;
        color: #ef4444 !important;
    }

    .fill-danger {
        fill: #ef4444 !important;
        stroke: #ef4444 !important;
        color: #ef4444 !important;
    }

    .heart-icon {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .shadow-20 {
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
        z-index: 1050 !important; /* Ensure popup is above everything */
    }

    .custom-dropdown .dropdown-menu {
        margin-top: 10px !important;
        transform: translate3d(0, 0, 0); /* Force hardware acceleration */
    }

    .rounded-15 {
        border-radius: 15px !important;
    }

    .lh-1.2 { line-height: 1.2; }
    .lh-1.6 { line-height: 1.6; }
    .lh-1.8 { line-height: 1.8; }

    .dropdown-item {
        padding: 10px 20px;
        display: flex;
        align-items: center;
        transition: all 0.2s;
        white-space: nowrap; /* Prevent text wrapping in popup */
    }

    .dropdown-item:hover {
        background-color: #f8fafc;
        color: #1e293b;
    }

    .shadow-xs {
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
</style>
