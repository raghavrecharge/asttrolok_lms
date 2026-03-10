@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#32A128",
                        "background-light": "#F7F9FC",
                        "background-dark": "#112210",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "12px",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        /* Compact form sections */
        .compact-form-card {
            @apply bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-4;
        }
        .compact-section-heading {
            @apply text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800;
        }
        .compact-grid {
            @apply grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4;
        }
        .compact-form-row {
            @apply grid grid-cols-1 md:grid-cols-2 gap-4;
        }
        .compact-field {
            @apply space-y-1;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.'.(!empty($post) ? 'edit_blog' : 'create_blog')) }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.blog') }}</div>
            </div>
        </div>

        <div class="section-body ">

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl() }}/blog/{{ (!empty($post) ? $post->id.'/update' : 'store') }}" method="post">
                                {{ csrf_field() }}

                                <div class="compact-grid">
                                    <!-- Left Column: Basic Info & Content -->
                                    <div class="col-12 col-lg-8">
                                        <!-- Language & Author -->
                                        <div class="compact-form-row">
                                            <div class="compact-field">
                                                @if(!empty(getGeneralSettings('content_translate')) and !empty($userLanguages))
                                                    <label class="input-label">{{ trans('auth.language') }}</label>
                                                    <select name="locale" class="form-control {{ !empty($post) ? 'js-edit-content-locale' : '' }}">
                                                        @foreach($userLanguages as $lang => $language)
                                                            <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }} {{ (!empty($definedLanguage) and is_array($definedLanguage) and in_array(mb_strtolower($lang), $definedLanguage)) ? '('. trans('panel.content_defined') .')' : '' }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('locale')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            @else
                                                <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                                            @endif
                                            </div>
                                            <div class="compact-field">
                                                <label class="input-label d-block">{{ trans('update.author') }}</label>
                                                <select name="author_id" class="form-control search-user-select2"
                                                            data-placeholder="{{ trans('update.select_a_user') }}"
                                                            data-search-option="except_user"
                                                        >
                                                        @if(!empty($post))
                                                            <option value="{{ $post->author->id }}" selected>{{ $post->author->full_name }}</option>
                                                        @else
                                                            <option selected disabled>{{ trans('update.select_a_user') }}</option>
                                                        @endif
                                                    </select>
                                                    @error('teacher_id')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                        </div>

                                        <!-- Title & Category -->
                                        <div class="compact-form-row">
                                            <div class="compact-field">
                                                <label class="input-label d-block">{{ trans('admin/main.title') }}</label>
                                                <input type="text" name="title"
                                                               class="form-control  @error('title') is-invalid @enderror"
                                                               value="{{ !empty($post) ? $post->title : old('title') }}"
                                                               placeholder="{{ trans('admin/main.choose_title') }}"/>
                                                @error('title')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                            </div>
                                            <div class="compact-field">
                                                <label class="input-label d-block">{{ trans('/admin/main.category') }}</label>
                                                <select class="form-control @error('category_id') is-invalid @enderror" name="category_id">
                                                    <option {{ !empty($trend) ? '' : 'selected' }} disabled>{{ trans('admin/main.choose_category') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" {{ (((!empty($post) and $post->category_id == $category->id) or (old('category_id') == $category->id)) ? 'selected="selected"' : '') }}>{{ $category->title }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                        </div>
                                                    @enderror
                                            </div>
                                        </div>

                                        <!-- Cover Image & Slug -->
                                        <div class="compact-form-row">
                                            <div class="compact-field">
                                                <label class="input-label d-block">{{ trans('public.cover_image') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="image" data-preview="holder">
                                                            <i class="fa fa-chevron-up"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="image" id="image" value="{{ (!empty($post)) ? $post->image : old('image') }}" class="form-control @error('image') is-invalid @enderror" placeholder="{{ trans('update.blog_cover_image_placeholder') }}"/>
                                                    @error('image')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                        @enderror
                                                </div>
                                            </div>
                                            <div class="compact-field">
                                                <label>Slug</label>
                                                <input type="text" name="slug"
                                                               class="form-control  @error('slug') is-invalid @enderror"
                                                               value="{{ !empty($post) ? $post->slug : old('slug') }}"
                                                               placeholder="slug"/>
                                                @error('slug')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                            </div>
                                        </div>

                                        <!-- Description & Content -->
                                        <div class="compact-form-row">
                                            <div class="compact-field">
                                                <label class="input-label d-block">{{ trans('public.description') }}</label>
                                                <div class="text-muted text-small mb-3">{{ trans('admin/main.create_blog_description_hint') }}</div>
                                                <textarea id="summernote" name="description" class="summernote form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('admin/main.description_placeholder') }}">{!! !empty($post) ? $post->description : old('description')  !!}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                            </div>
                                            <div class="compact-field">
                                                <label class="input-label d-block">{{ trans('admin/main.content') }}</label>
                                                <div class="text-muted text-small mb-3">{{ trans('admin/main.create_blog_content_hint') }}</div>
                                                <textarea id="contentSummernote" name="content" class="summernote form-control @error('content')  is-invalid @enderror" placeholder="{{ trans('admin/main.content_placeholder') }}">{!! !empty($post) ? $post->content : old('content')  !!}</textarea>
                                                @error('content')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Settings & Publish -->
                                    <div class="col-12 col-lg-4">
                                        <div class="compact-form-card">
                                            <h3 class="compact-section-heading">
                                                <span class="material-symbols-outlined text-primary">settings</span>
                                                Blog Settings
                                            </h3>
                                            <div class="space-y-4">
                                                <!-- Tags -->
                                                <div class="compact-field">
                                                    <label class="input-label d-block">{{ trans('blog.tags') }}</label>
                                                    <input type="text" name="tags" value="{{ !empty($post) ? $post->tags : old('tags') }}" class="form-control {{ !empty($post) ? 'js-edit-content-tags' : '' }}"/>
                                                    <p class="text-[10px] text-slate-400 font-semibold">{{ trans('blog.tags_hint') }}</p>
                                                    @error('tags')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>

                                                <!-- Status & Publish -->
                                                <div class="compact-form-row">
                                                    <div class="compact-field">
                                                        <label class="input-label d-block">{{ trans('public.status') }}</label>
                                                        <select name="status" class="form-control">
                                                            <option value="publish" {{ (!empty($post) and $post->status == 'publish') ? 'selected' : '' }}>{{ trans('blog.publish') }}</option>
                                                            <option value="draft" {{ (!empty($post) and $post->status == 'draft') ? 'selected' : '' }}>{{ trans('blog.draft') }}</option>
                                                            <option value="pending" {{ (!empty($post) and $post->status == 'pending') ? 'selected' : '' }}>{{ trans('blog.pending') }}</option>
                                                        </select>
                                                        @error('status')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                            @enderror
                                                    </div>
                                                    <div class="compact-field">
                                                        <label class="input-label d-block">{{ trans('blog.publish_at') }}</label>
                                                        <div class="flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                                            <span class="px-4 py-3 text-slate-400"><span class="material-symbols-outlined text-sm">calendar_today</span></span>
                                                            <input type="text" name="publish_at" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 datetimepicker" autocomplete="off" value="{{ (!empty($post) and !empty($post->publish_at)) ? dateTimeFormat($post->publish_at, 'Y-m-d H:i', false) : old('publish_at') }}"/>
                                                        </div>
                                                        @error('publish_at')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                            @enderror
                                                    </div>
                                                </div>

                                                <!-- Submit Button -->
                                                <div class="compact-field">
                                                    <button type="submit" class="btn btn-primary btn-lg">{{ trans('public.save') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
@endpush
