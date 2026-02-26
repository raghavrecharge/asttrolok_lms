@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/summernote/summernote-bs4.min.css">
    <style>
        .ct-form-card {
            background: #fff !important; border-radius: 16px !important; padding: 28px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04) !important; border: 1px solid #f0f0f0 !important;
        }
        .ct-form-card .form-group label {
            font-size: 13px !important; font-weight: 700 !important; color: #1f3b64 !important; margin-bottom: 8px !important;
            display: block !important;
        }
        .ct-form-card .form-control,
        .ct-form-card input.form-control,
        .ct-form-card select.form-control {
            border-radius: 10px !important; border: 1px solid #e5e7eb !important;
            padding: 10px 14px !important; font-size: 14px !important;
        }
        .ct-form-card select.form-control { height: 46px !important; }
        .ct-form-card .form-control:focus {
            border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,0.1) !important;
        }
        .ct-form-card .note-editor {
            border-radius: 12px !important; border: 1px solid #e5e7eb !important; overflow: hidden;
        }
        .ct-form-card .note-toolbar { background: #f8fafc !important; border-bottom: 1px solid #eef2f7 !important; }
        .ct-footer-bar {
            background: #fff !important; border-radius: 14px !important; padding: 16px 22px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04) !important; border: 1px solid #f0f0f0 !important;
            display: flex !important; align-items: center !important; justify-content: space-between !important;
            flex-wrap: wrap !important; gap: 12px !important;
        }
        .ct-footer-bar .ct-terms { font-size: 13px; color: #6c757d; }
        .ct-footer-bar .ct-terms strong { color: #1f3b64; }
        .ct-publish-btn {
            background: linear-gradient(135deg, #2563eb 0%, #1f3b64 100%) !important;
            border: none !important; color: #fff !important; padding: 12px 28px !important; border-radius: 12px !important;
            font-weight: 700 !important; font-size: 14px !important; transition: all 0.3s ease;
            display: inline-flex !important; align-items: center !important; gap: 8px;
        }
        .ct-publish-btn:hover {
            box-shadow: 0 8px 25px rgba(37,99,235,0.3); transform: translateY(-2px); color: #fff !important;
        }
        .ct-form-card .input-group-text {
            border-radius: 10px 0 0 10px !important; border: 1px solid #e5e7eb !important;
            border-right: none !important;
        }
        .ct-form-card .input-group .form-control {
            border-radius: 0 !important;
        }
        .ct-form-card .input-group .btn {
            border-radius: 0 10px 10px 0 !important;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ !empty($topic) ? trans('update.edit_topic') : trans('update.new_topic') }}</h2>
        <p class="text-gray font-14 mt-5">{{ trans('update.new_topic_hint') }}</p>

        <form action="{{ !empty($topic) ? $topic->getEditUrl() : '/forums/create-topic' }}" method="post">
            {{ csrf_field() }}

            <div class="ct-form-card mt-25">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>{{ trans('update.topic_title') }}</label>
                            <input type="text" name="title" value="{{ !empty($topic) ? $topic->title : old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="{{ trans('update.topic_title_placeholder') }}">
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(!empty($error125))
                            <snap style="color: red;">{{ $error125 }}</snap>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>{{ trans('update.forums') }}</label>
                            <select name="forum_id" class="form-control @error('forum_id') is-invalid @enderror">
                                <option selected disabled>{{ trans('admin/main.choose_category') }}</option>
                                @foreach($forums as $forum)
                                    @if(!empty($forum->subForums) and count($forum->subForums))
                                        @php
                                            $showOptgroup = false;
                                            foreach($forum->subForums as $subForum) {
                                                if($subForum->checkUserCanCreateTopic() and !$subForum->close) {
                                                    $showOptgroup = true;
                                                }
                                            }
                                        @endphp
                                        @if($showOptgroup)
                                            <optgroup label="{{ $forum->title }}">
                                                @foreach($forum->subForums as $subForum)
                                                    @if($subForum->checkUserCanCreateTopic() and !$subForum->close)
                                                        <option value="{{ $subForum->id }}" {{ ((!empty($topic) and $topic->forum_id == $subForum->id) or (request()->get('forum_id') == $subForum->id)) ? 'selected' : '' }}>{{ $subForum->title }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @elseif($forum->checkUserCanCreateTopic() and !$forum->close)
                                        <option value="{{ $forum->id }}" {{ (request()->get('forum_id') == $forum->id) ? 'selected' : '' }}>{{ $forum->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('forum_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label>{{ trans('public.description') }}</label>
                            <textarea id="summernote" name="description" class="form-control @error('description') is-invalid @enderror">{!! !empty($topic) ? $topic->description : old('description') !!}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div id="topicImagesInputs" class="create-topic-attachments form-group mt-10">
                            <label class="mb-8">{{ trans('update.attachments') }}</label>

                            <div class="main-row input-group product-images-input-group mt-5">
                                <div class="input-group-prepend">
                                    <button type="button" class="input-group-text panel-file-manager" data-input="attachments_record" data-preview="holder">
                                        <i data-feather="upload" width="18" height="18" class="text-white"></i>
                                    </button>
                                </div>
                                <input type="text" name="attachments[]" id="attachments_record" value="" class="form-control"/>
                                <button type="button" class="btn btn-primary btn-sm add-btn">
                                    <i data-feather="plus" width="18" height="18" class="text-white"></i>
                                </button>
                            </div>

                            @if(!empty($topic) and !empty($topic->attachments) and count($topic->attachments))
                                @foreach($topic->attachments as $topicAttachment)
                                    <div class="input-group product-images-input-group mt-10">
                                        <div class="input-group-prepend">
                                            <button type="button" class="input-group-text panel-file-manager" data-input="attachments_{{ $topicAttachment->id }}" data-preview="holder">
                                                <i data-feather="upload" width="18" height="18" class="text-white"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="attachments[]" id="attachments_{{ $topicAttachment->id }}" value="{{ $topicAttachment->path }}" class="form-control" placeholder="{{ trans('update.attachments_size') }}"/>
                                        <button type="button" class="btn btn-sm btn-danger remove-btn">
                                            <i data-feather="x" width="18" height="18" class="text-white"></i>
                                        </button>
                                    </div>
                                @endforeach
                            @endif

                            @error('images')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="ct-footer-bar mt-20">
                <div class="ct-terms">
                    <strong>{{ trans('update.terms_and_rules_confirmation') }}</strong>
                    <p class="mb-0 mt-3" style="font-size:12px;">{{ trans('update.terms_and_rules_confirmation_hint') }}</p>
                </div>
                <button type="submit" class="ct-publish-btn">
                    <i data-feather="send" width="16" height="16"></i>
                    {{ trans('update.publish_topic') }}
                </button>
            </div>
        </form>
    </section>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/create_topics.min.js"></script>
@endpush
