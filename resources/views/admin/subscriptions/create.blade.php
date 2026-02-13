@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-timepicker/bootstrap-timepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <style>
        .bootstrap-timepicker-widget table td input {
            width: 35px !important;
        }

        .select2-container {
            z-index: 1212 !important;
        }
        
        .top-right-alert {
            position: fixed;
            top: 167px;
            right: 95px;
            z-index: 1055;
            min-width: 300px;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{!empty($subscription) ?trans('/admin/main.edit'): trans('admin/main.new') }} {{ trans('update.subscription') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}/subscriptions">{{ trans('update.subscriptions') }}</a>
                </div>
                <div class="breadcrumb-item">{{!empty($subscription) ?trans('/admin/main.edit'): trans('admin/main.new') }}</div>
            </div>
        </div>

        <div class="section-body">

            <div class="row">
                <div class="col-12 ">
                    <div class="card">
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show top-right-alert" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                           
                            <form method="post" action="{{ getAdminPanelUrl() }}/subscriptions/{{ !empty($subscription) ? $subscription->id.'/update' : 'store' }}" id="webinarForm" class="webinar-form">
                                {{ csrf_field() }}
                                <section>
                                    <h2 class="section-title after-line">{{ trans('public.basic_information') }}</h2>

                                    <div class="row">
                                        <div class="col-12 col-md-5">

                                            @if(!empty(getGeneralSettings('content_translate')))
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('auth.language') }}</label>
                                                    <select name="locale" class="form-control {{ !empty($subscription) ? 'js-edit-content-locale' : '' }}">
                                                        @foreach($userLanguages as $lang => $language)
                                                            <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
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


                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.title') }}</label>
                                                <input type="text" name="title" value="{{ !empty($subscription) ? $subscription->title : old('title') }}" class="form-control @error('title')  is-invalid @enderror" placeholder=""/>
                                                @error('title')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('update.subscription_url') }}</label>
                                                <input type="text" name="slug" value="{{ !empty($subscription) ? $subscription->slug : old('slug') }}" class="form-control @error('slug')  is-invalid @enderror" placeholder=""/>
                                                <div class="text-muted text-small mt-1">{{ trans('update.subscription_url_hint') }}</div>
                                                @error('slug')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            @if(!empty($subscription) and $subscription->creator->isOrganization())
                                                <div class="form-group mt-15 ">
                                                    <label class="input-label d-block">{{ trans('admin/main.organization') }}</label>

                                                    <select class="form-control" disabled readonly data-placeholder="{{ trans('public.search_instructor') }}">
                                                        <option selected>{{ $subscription->creator->full_name }}</option>
                                                    </select>
                                                </div>
                                            @endif


                                                <div class="form-group mt-15">
                                                <label class="input-label d-block">{{ trans('admin/main.select_a_instructor') }}</label>

                                                @php
                                                    $selectedTeacherId = old('teacher_id', $subscription->teacher_id ?? null);
                                                    
                                                    $selectedTeacher = null;
                                                    if ($selectedTeacherId) {
                                                        $selectedTeacher = \App\User::find($selectedTeacherId);
                                                    }
                                                @endphp

                                                <select name="teacher_id" 
                                                        data-search-option="just_teacher_role" 
                                                        class="form-control search-user-select2 @error('teacher_id') is-invalid @enderror"
                                                        data-placeholder="{{ trans('public.select_a_teacher') }}">
                                                    
                                                    @if($selectedTeacher)
                                                        <option value="{{ $selectedTeacher->id }}" selected>
                                                            {{ $selectedTeacher->full_name }}
                                                        </option>
                                                    @else
                                                        <option value="" selected disabled>{{ trans('public.select_a_teacher') }}</option>
                                                    @endif
                                                </select>

                                                @error('teacher_id')
                                                    <div class="invalid-feedback d-block">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>


                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.seo_description') }}</label>
                                                <input type="text" name="seo_description" value="{{ !empty($subscription) ? $subscription->seo_description : old('seo_description') }}" class="form-control @error('seo_description')  is-invalid @enderror"/>
                                                <div class="text-muted text-small mt-1">{{ trans('admin/main.seo_description_hint') }}</div>
                                                @error('seo_description')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.thumbnail_image') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="thumbnail" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="thumbnail" id="thumbnail" value="{{ !empty($subscription) ? $subscription->thumbnail : old('thumbnail') }}" class="form-control @error('thumbnail')  is-invalid @enderror"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view" data-input="thumbnail">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('thumbnail')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div>


                                            <!-- ==================== NEW FIELD - HOME BANNER ==================== -->
                                            <div class="form-group mt-15">
                                                <label class="input-label">Home Banner Image</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="home_banner" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" 
                                                        name="home_banner" 
                                                        id="home_banner" 
                                                        value="{{ old('home_banner', $subscription->extraDetails->home_banner ?? '') }}" 
                                                        class="form-control @error('home_banner') is-invalid @enderror"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view" data-input="home_banner">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('home_banner')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="text-muted text-small mt-1">Image to be displayed on homepage banner (Recommended: 1920x600px)</div>
                                            </div>
                                            <!-- ==================== END HOME BANNER ==================== -->


                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.cover_image') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="cover_image" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="image_cover" id="cover_image" value="{{ !empty($subscription) ? $subscription->image_cover : old('image_cover') }}" class="form-control @error('image_cover')  is-invalid @enderror"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view" data-input="cover_image">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('image_cover')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group mt-25">
                                                <label class="input-label">{{ trans('public.demo_video') }} ({{ trans('public.optional') }})</label>


                                                <div class="">
                                                    <label class="input-label font-12">{{ trans('public.source') }}</label>
                                                    <select name="video_demo_source"
                                                            class="js-video-demo-source form-control"
                                                    >
                                                        @foreach(\App\Models\Webinar::$videoDemoSource as $source)
                                                            <option value="{{ $source }}" @if(!empty($subscription) and $subscription->video_demo_source == $source) selected @endif>{{ trans('update.file_source_'.$source) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group mt-0">
                                                <label class="input-label font-12">{{ trans('update.path') }}</label>
                                                <div class="input-group js-video-demo-path-input">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="js-video-demo-path-upload input-group-text admin-file-manager {{ (empty($subscription) or empty($subscription->video_demo_source) or $subscription->video_demo_source == 'upload') ? '' : 'd-none' }}" data-input="demo_video" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>

                                                        <button type="button" class="js-video-demo-path-links rounded-left input-group-text input-group-text-rounded-left  {{ (empty($subscription) or empty($subscription->video_demo_source) or $subscription->video_demo_source == 'upload') ? 'd-none' : '' }}">
                                                            <i class="fa fa-link"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="video_demo" id="demo_video" value="{{ !empty($subscription) ? $subscription->video_demo : old('video_demo') }}" class="form-control @error('video_demo')  is-invalid @enderror"/>
                                                    @error('video_demo')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.description') }}</label>
                                                <textarea id="summernote" name="description" class="form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! !empty($subscription) ? $subscription->description : old('description')  !!}</textarea>
                                                @error('description')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="mt-3">
                                    <h2 class="section-title after-line">{{ trans('public.additional_information') }}</h2>
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('update.access_days') }}</label>
                                                <input type="text" name="access_days" value="{{ !empty($subscription) ? $subscription->access_days : old('access_days') }}" class="form-control @error('access_days')  is-invalid @enderror"/>
                                                @error('access_days')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                                <p class="mt-1">- {{ trans('update.access_days_input_hint') }}</p>
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">Per Payment Videos Access</label>
                                                <input type="text" name="video_count" value="{{ !empty($subscription) ? $subscription->video_count : old('video_count') }}" class="form-control @error('video_count')  is-invalid @enderror"/>
                                                @error('video_count')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.price') }} ({{ $currency }})</label>
                                                <input type="text" name="price" value="{{ !empty($subscription->price) ? $subscription->price : old('price') }}" class="form-control @error('price')  is-invalid @enderror" placeholder="{{ trans('public.0_for_free') }}"/>
                                                @error('price')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            
                                            <div class="form-group mt-15">
                                                <label class="input-label">Free video Access</label>
                                                <input type="text" name="free_video_count" value="{{ !empty($subscription->free_video_count) ? $subscription->free_video_count : old('free_video_count') }}" class="form-control @error('free_video_count')  is-invalid @enderror"/>
                                                @error('free_video_count')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label d-block">{{ trans('public.tags') }}</label>
                                                <input type="text" name="tags" data-max-tag="5" value="{{ !empty($subscription) ? implode(',',$subscriptionTags) : '' }}" class="form-control inputtags" placeholder="{{ trans('public.type_tag_name_and_press_enter') }} ({{ trans('admin/main.max') }} : 5)"/>
                                            </div>

                                            <!-- Advertisement Section -->
<div class="form-group mt-15">
    <label class="input-label d-block" style="font-weight: 700;font-size:20px">Advertisement</label>
    
    <div class="form-group mt-15">
        <label class="input-label">Ad Subtitle</label>
        <input type="text" name="ad_subtitle" value="{{ old('ad_subtitle', $subscription->extraDetails->ad_subtitle ?? '') }}" class="form-control @error('ad_subtitle') is-invalid @enderror" placeholder="Advertisement subtitle"/>
        @error('ad_subtitle')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group mt-15">
        <label class="input-label">Ad Title</label>
        <input type="text" name="ad_title" value="{{ old('ad_title', $subscription->extraDetails->ad_title ?? '') }}" class="form-control @error('ad_title') is-invalid @enderror" placeholder="Advertisement title"/>
        @error('ad_title')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group mt-15">
        <label class="input-label">Ad Description</label>
        <textarea name="ad_description" rows="3" class="form-control @error('ad_description') is-invalid @enderror" placeholder="Advertisement description">{{ old('ad_description', $subscription->extraDetails->ad_description ?? '') }}</textarea>
        @error('ad_description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- ==================== NEW FIELDS START ==================== -->

<!-- Home View Checkbox -->
<div class="form-group mt-15">
    <label class="input-label d-block" style="font-weight: 700;font-size:20px">Display Settings</label>
    
    <div class="form-group mt-15">
        <div class="custom-control custom-switch">
            <input type="checkbox" 
                   name="home_view" 
                   id="home_view" 
                   value="1"
                   {{ old('home_view', $subscription->home_view ?? 0) == 1 ? 'checked' : '' }}
                   class="custom-control-input">
            <label class="custom-control-label" for="home_view">
                Display on Home Page
            </label>
        </div>
        <div class="text-muted text-small mt-1">Enable this to show subscription on homepage</div>
        @error('home_view')
        <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mt-15">
        <label class="input-label">Total student learning</label>
        <input type="text" name="student_count" value="{{ !empty($subscription) ? $subscription->student_count : old('student_count') }}" class="form-control @error('student_count')  is-invalid @enderror"/>
        @error('student_count')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="form-group mt-15">
    <div class="custom-control custom-switch">
        <input type="checkbox" 
               name="status_toggle" 
               id="status_toggle" 
               value="1"
               {{ old('status', $subscription->status ?? 'is_draft') == 'active' ? 'checked' : '' }}
               class="custom-control-input"
               onchange="updateStatusValue(this)">
        <label class="custom-control-label" for="status_toggle">
            <span id="status_label">
                {{ old('status', $subscription->status ?? 'is_draft') == 'active' ? 'Published' : 'Draft' }}
            </span>
        </label>
    </div>
    
    <!-- Hidden field to store actual enum value -->
    <input type="hidden" 
           name="status" 
           id="status" 
           value="{{ old('status', $subscription->status ?? 'is_draft') }}">
    
    <div class="text-muted text-small mt-1">
        <span id="status_help">
            @if(old('status', $subscription->status ?? 'is_draft') == 'active')
                Subscription is published and visible on public site
            @else
                Subscription is in draft mode
            @endif
        </span>
    </div>
    
    @error('status')
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<script>
function updateStatusValue(checkbox) {
    const statusInput = document.getElementById('status');
    const statusLabel = document.getElementById('status_label');
    const statusHelp = document.getElementById('status_help');
    
    if (checkbox.checked) {
        // Published
        statusInput.value = 'active';
        statusLabel.textContent = 'Published';
        statusHelp.textContent = 'Subscription is visible on public site';
    } else {
        // Draft
        statusInput.value = 'is_draft';
        statusLabel.textContent = 'Draft';
        statusHelp.textContent = 'Subscription is in draft mode';
    }
}
</script>

<!-- Risk Information -->
<div class="form-group mt-15">
    <label class="input-label d-block" style="font-weight: 700;font-size:20px">Risk Information</label>
    
    <div class="form-group mt-15">
        <label class="input-label">Risk Title</label>
        <input type="text" 
               name="risk_title" 
               value="{{ old('risk_title', $subscription->extraDetails->risk_title ?? '') }}" 
               class="form-control @error('risk_title') is-invalid @enderror" 
               placeholder="Risk warning title"
               maxlength="255"/>
        @error('risk_title')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group mt-15">
        <label class="input-label">Risk Description</label>
        <textarea name="risk_description" 
                  rows="4" 
                  class="form-control @error('risk_description') is-invalid @enderror" 
                  placeholder="Detailed risk description">{{ old('risk_description', $subscription->extraDetails->risk_description ?? '') }}</textarea>
        @error('risk_description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Call to Action -->
<div class="form-group mt-15">
    <label class="input-label d-block" style="font-weight: 700;font-size:20px">Call to Action</label>
    
    <div class="form-group mt-15">
        <label class="input-label">CTA Button Text</label>
        <input type="text" 
               name="cta_text" 
               value="{{ old('cta_text', $subscription->extraDetails->cta_text ?? '') }}" 
               class="form-control @error('cta_text') is-invalid @enderror" 
               placeholder="e.g., Subscribe Now, Get Started, Join Today"
               maxlength="150"/>
        <div class="text-muted text-small mt-1">Text that will appear on the action button (Max 150 characters)</div>
        @error('cta_text')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>




                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.category') }}</label>
                                                    @php
                                                        // Get selected category_id: old() has priority
                                                        $selectedCategoryId = old('category_id', $subscription->category_id ?? null);
                                                    @endphp

                                                    <select id="categories" 
                                                            class="custom-select @error('category_id') is-invalid @enderror" 
                                                            name="category_id" 
                                                            required>
                                                        
                                                        <!-- Placeholder option -->
                                                        <option value="" {{ !$selectedCategoryId ? 'selected' : '' }} disabled>
                                                            {{ trans('public.choose_category') }}
                                                        </option>
                                                        
                                                        @foreach($categories as $category)
                                                            @if(!empty($category->subCategories) and count($category->subCategories))
                                                                <!-- Category with SubCategories -->
                                                                <optgroup label="{{ $category->title }}">
                                                                    @foreach($category->subCategories as $subCategory)
                                                                        <option value="{{ $subCategory->id }}" 
                                                                                {{ $selectedCategoryId == $subCategory->id ? 'selected' : '' }}>
                                                                            {{ $subCategory->title }}
                                                                        </option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @else
                                                                <!-- Category without SubCategories -->
                                                                <option value="{{ $category->id }}" 
                                                                        {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->title }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>

                                                    @error('category_id')
                                                        <div class="invalid-feedback d-block">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>

                                        </div>
                                    </div>
                                </section>

                                <!-- Extra Details -->
                                <section class="mt-3">
                                    <h2 class="section-title after-line">Extra Details</h2>
                                    
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Main Content</label>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Subtitle</label>
                                            <input type="text" name="subtitle" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->subtitle : old('subtitle') }}" class="form-control @error('subtitle') is-invalid @enderror" placeholder="Subtitle"/>
                                            @error('subtitle')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Heading Main</label>
                                            <input type="text" name="heading_main" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->heading_main : old('heading_main') }}" class="form-control @error('heading_main') is-invalid @enderror" placeholder="Main heading"/>
                                            @error('heading_main')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Heading Sub</label>
                                            <input type="text" name="heading_sub" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->heading_sub : old('heading_sub') }}" class="form-control @error('heading_sub') is-invalid @enderror" placeholder="Sub heading"/>
                                            @error('heading_sub')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mt-15">
                                            <label class="input-label">Subdescription</label>
                                            <textarea name="subdescription" rows="3" class="form-control @error('subdescription') is-invalid @enderror" placeholder="Subdescription">{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->subdescription : old('subdescription') }}</textarea>
                                            @error('subdescription')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mt-15">
                                            <label class="input-label">Additional Description</label>
                                            <textarea name="additional_description" rows="4" class="form-control @error('additional_description') is-invalid @enderror" placeholder="Additional description">{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->additional_description : old('additional_description') }}</textarea>
                                            @error('additional_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group mt-15">
                                        <label class="input-label">Is Featured Icon</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <button type="button" class="input-group-text admin-file-manager" data-input="is_featured" data-preview="holder">
                                                    <i class="fa fa-upload"></i>
                                                </button>
                                            </div>
                                            <input type="text" name="is_featured" id="is_featured" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->is_featured : old('is_featured') }}" class="form-control @error('is_featured') is-invalid @enderror"/>
                                            <div class="input-group-append">
                                                <button type="button" class="input-group-text admin-file-view" data-input="is_featured">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            </div>
                                            @error('is_featured')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Material Items -->
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Material Items</label>
                                       
                                        @php
                                            // Safely decode material_text array
                                            $materialTexts = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->material_text)) {
                                                    $materialTexts = $subscription->extraDetails->material_text;
                                                } elseif (is_string($subscription->extraDetails->material_text)) {
                                                    $decoded = json_decode($subscription->extraDetails->material_text, true);
                                                    $materialTexts = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                            
                                            // Safely decode material_icon array
                                            $materialIcons = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->material_icon)) {
                                                    $materialIcons = $subscription->extraDetails->material_icon;
                                                } elseif (is_string($subscription->extraDetails->material_icon)) {
                                                    $decoded = json_decode($subscription->extraDetails->material_icon, true);
                                                    $materialIcons = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                        @endphp
                                       
                                        @for($i = 0; $i < 4; $i++)
                                        <div class="row mb-3">
                                            <!-- Material Icon Field -->
                                            <div class="col-12 col-md-6">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="material_icon_{{ $i }}" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="material_icon[]" id="material_icon_{{ $i }}"
                                                           value="{{ isset($materialIcons[$i]) ? $materialIcons[$i] : old('material_icon.'.$i, '') }}"
                                                           class="form-control @error('material_icon.'.$i) is-invalid @enderror"
                                                           placeholder="Material Icon {{ $i + 1 }}"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view" data-input="material_icon_{{ $i }}">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('material_icon.'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                           
                                            <!-- Material Text Field -->
                                            <div class="col-12 col-md-6">
                                                <input type="text" name="material_text[]"
                                                       value="{{ isset($materialTexts[$i]) ? $materialTexts[$i] : old('material_text.'.$i, '') }}"
                                                       class="form-control @error('material_text.'.$i) is-invalid @enderror"
                                                       placeholder="Material Text {{ $i + 1 }}"/>
                                                @error('material_text.'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        @endfor
                                    </div>

                                    <!-- Comparison Plan -->
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Comparison Plan</label>
                                        
                                        <div class="row">
                                            <div class="col-12 col-md-4 mb-3">
                                                <div class="form-group">
                                                    <label class="input-label">Plan Duration</label>
                                                    <input type="text" name="plan_duration" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->plan_duration : old('plan_duration') }}" class="form-control @error('plan_duration') is-invalid @enderror"/>
                                                    @error('plan_duration')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                           
                                            <div class="col-12 col-md-4 mb-3">
                                                <div class="form-group">
                                                    <label class="input-label">Plan Type</label>
                                                    <input type="text" name="plan_type" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->plan_type : old('plan_type') }}" class="form-control @error('plan_type') is-invalid @enderror" />
                                                    @error('plan_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-4 mb-3">
                                                <div class="form-group">
                                                    <label class="input-label">Plan Price</label>
                                                    <input type="text" name="plan_price" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->plan_price : old('plan_price') }}" class="form-control @error('plan_price') is-invalid @enderror"/>
                                                    @error('plan_price')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                           
                                            <div class="col-12 col-md-4 mb-3">
                                                <div class="form-group">
                                                    <label class="input-label">Plan Cancel Text</label>
                                                    <input type="text" name="plan_cancel_text" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->plan_cancel_text : old('plan_cancel_text') }}" class="form-control @error('plan_cancel_text') is-invalid @enderror" />
                                                    @error('plan_cancel_text')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                       
                                        <div class="row">
                                            <div class="col-12 col-md-4">
                                                <div class="form-group mt-15">
                                                    <label class="input-label">Plan Movie/Video URL</label>
                                                    <input type="text"
                                                           name="plan_movie"
                                                           value="{{ old('plan_movie', $subscription->extraDetails->plan_movie ?? '') }}"
                                                           class="form-control @error('plan_movie') is-invalid @enderror"/>
                                                    @error('plan_movie')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-4">
                                                <div class="form-group mt-15">
                                                    <label class="input-label">Plan Duration Option</label>
                                                    <input type="text"
                                                           name="plan_duration_option"
                                                           value="{{ old('plan_duration_option', $subscription->extraDetails->plan_duration_option ?? '') }}"
                                                           class="form-control @error('plan_duration_option') is-invalid @enderror"/>
                                                    @error('plan_duration_option')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-4">
                                                <div class="form-group mt-15">
                                                    <label class="input-label">Price Suffix</label>
                                                    <input type="text"
                                                        name="price_suffix"
                                                        value="{{ old('price_suffix', $subscription->extraDetails->price_suffix ?? '') }}"
                                                        class="form-control @error('price_suffix') is-invalid @enderror"/>
                                                    @error('price_suffix')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 col-md-4 mb-3">
                                                <div class="form-group">
                                                    <label class="input-label">Plan Badge</label>
                                                    <input type="text" name="plan_badge" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->plan_badge : old('plan_badge') }}" class="form-control @error('plan_badge') is-invalid @enderror"/>
                                                    @error('plan_badge')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Plan Icon</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text admin-file-manager" data-input="plan_icon" data-preview="holder">
                                                        <i class="fa fa-upload"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="plan_icon" id="plan_icon" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->plan_icon : old('plan_icon') }}" class="form-control @error('plan_icon') is-invalid @enderror"/>
                                                <div class="input-group-append">
                                                    <button type="button" class="input-group-text admin-file-view" data-input="plan_icon">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                                @error('plan_icon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Price Icon</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text admin-file-manager" data-input="price_icon" data-preview="holder">
                                                        <i class="fa fa-upload"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="price_icon" id="price_icon" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->price_icon : old('price_icon') }}" class="form-control @error('price_icon') is-invalid @enderror"/>
                                                <div class="input-group-append">
                                                    <button type="button" class="input-group-text admin-file-view" data-input="price_icon">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                                @error('price_icon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Comparison Text</label>
                                            <textarea name="comparison_text" rows="3" class="form-control @error('comparison_text') is-invalid @enderror" placeholder="Plan comparison details">{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->comparison_text : old('comparison_text') }}</textarea>
                                            @error('comparison_text')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- What will you learn -->
                                    <div class="form-group mt-25 mb-10">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">What will you learn</label>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Learn Title</label>
                                            <input type="text" name="learn_title" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->learn_title : old('learn_title') }}" class="form-control @error('learn_title') is-invalid @enderror" placeholder="What you'll learn title"/>
                                            @error('learn_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mt-15">
                                            <label class="input-label">Learn Description</label>
                                            <textarea name="learn_description" rows="3" class="form-control @error('learn_description') is-invalid @enderror" placeholder="What you'll learn description">{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->learn_description : old('learn_description') }}</textarea>
                                            @error('learn_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        @php
                                            // Safely decode learn_text array
                                            $learnTexts = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->learn_text)) {
                                                    $learnTexts = $subscription->extraDetails->learn_text;
                                                } elseif (is_string($subscription->extraDetails->learn_text)) {
                                                    $decoded = json_decode($subscription->extraDetails->learn_text, true);
                                                    $learnTexts = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                            
                                            // Safely decode learn_icon array
                                            $learnIcons = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->learn_icon)) {
                                                    $learnIcons = $subscription->extraDetails->learn_icon;
                                                } elseif (is_string($subscription->extraDetails->learn_icon)) {
                                                    $decoded = json_decode($subscription->extraDetails->learn_icon, true);
                                                    $learnIcons = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                        @endphp
                                       
                                        @for($i = 0; $i < 4; $i++)
                                        <div class="row mb-3">
                                            <!-- Learn Icon Field -->
                                            <div class="col-12 col-md-6">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="learn_icon_{{ $i }}" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="learn_icon[]" id="learn_icon_{{ $i }}"
                                                           value="{{ isset($learnIcons[$i]) ? $learnIcons[$i] : old('learn_icon.'.$i, '') }}"
                                                           class="form-control @error('learn_icon.'.$i) is-invalid @enderror"
                                                           placeholder="Learn Icon {{ $i + 1 }}"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view" data-input="learn_icon_{{ $i }}">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('learn_icon.'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                           
                                            <!-- Learn Text Field -->
                                            <div class="col-12 col-md-6">
                                                <input type="text" name="learn_text[]"
                                                       value="{{ isset($learnTexts[$i]) ? $learnTexts[$i] : old('learn_text.'.$i, '') }}"
                                                       class="form-control @error('learn_text.'.$i) is-invalid @enderror"
                                                       placeholder="Learn Text {{ $i + 1 }}"/>
                                                @error('learn_text.'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        @endfor
                                    </div>

                                    <!-- Bonus -->
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Bonus</label>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Bonus Heading</label>
                                            <input type="text" name="bonus_heading" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->bonus_heading : old('bonus_heading') }}" class="form-control @error('bonus_heading') is-invalid @enderror" placeholder="Bonus section heading"/>
                                            @error('bonus_heading')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group mt-15">
                                            <label class="input-label">Bonus Icon</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text admin-file-manager" data-input="bonus_icon" data-preview="holder">
                                                        <i class="fa fa-upload"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="bonus_icon" id="bonus_icon" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->bonus_icon : old('bonus_icon') }}" class="form-control @error('bonus_icon') is-invalid @enderror"/>
                                                <div class="input-group-append">
                                                    <button type="button" class="input-group-text admin-file-view" data-input="bonus_icon">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </div>
                                                @error('bonus_icon')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Roadmap -->
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Your 6-Month Roadmap to Certification</label>
                                       
                                        @php
                                            // Safely decode certification_time array
                                            $certificationTime = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->certification_time)) {
                                                    $certificationTime = $subscription->extraDetails->certification_time;
                                                } elseif (is_string($subscription->extraDetails->certification_time)) {
                                                    $decoded = json_decode($subscription->extraDetails->certification_time, true);
                                                    $certificationTime = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                           
                                            // Safely decode certification_focus array
                                            $certificationFocus = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->certification_focus)) {
                                                    $certificationFocus = $subscription->extraDetails->certification_focus;
                                                } elseif (is_string($subscription->extraDetails->certification_focus)) {
                                                    $decoded = json_decode($subscription->extraDetails->certification_focus, true);
                                                    $certificationFocus = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                           
                                            // Safely decode certification_outcome array
                                            $certificationOutcome = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->certification_outcome)) {
                                                    $certificationOutcome = $subscription->extraDetails->certification_outcome;
                                                } elseif (is_string($subscription->extraDetails->certification_outcome)) {
                                                    $decoded = json_decode($subscription->extraDetails->certification_outcome, true);
                                                    $certificationOutcome = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                        @endphp
                                       
                                        @for($i = 0; $i < 3; $i++)
                                        <div class="row mb-3">
                                            <!-- Certification Time -->
                                            <div class="col-12 col-md-4">
                                                <input type="text" name="certification_time[]"
                                                       value="{{ isset($certificationTime[$i]) ? $certificationTime[$i] : old('certification_time.'.$i, '') }}"
                                                       class="form-control @error('certification_time.'.$i) is-invalid @enderror"
                                                       placeholder="Time Period {{ $i + 1 }}"/>
                                                @error('certification_time.'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                           
                                            <!-- Certification Focus -->
                                            <div class="col-12 col-md-4">
                                                <input type="text" name="certification_focus[]"
                                                       value="{{ isset($certificationFocus[$i]) ? $certificationFocus[$i] : old('certification_focus.'.$i, '') }}"
                                                       class="form-control @error('certification_focus.'.$i) is-invalid @enderror"
                                                       placeholder="Focus Area {{ $i + 1 }}"/>
                                                @error('certification_focus.'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                           
                                            <!-- Certification Outcome -->
                                            <div class="col-12 col-md-4">
                                                <input type="text" name="certification_outcome[]"
                                                       value="{{ isset($certificationOutcome[$i]) ? $certificationOutcome[$i] : old('certification_outcome.'.$i, '') }}"
                                                       class="form-control @error('certification_outcome.'.$i) is-invalid @enderror"
                                                       placeholder="Expected Outcome {{ $i + 1 }}"/>
                                                @error('certification_outcome.'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        @endfor
                                    </div>

                                    <!-- Rating -->
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Rating Options & Icons (5 Items)</label>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Rating Title</label>
                                            <input type="text" name="rate_title" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->rate_title : old('rate_title') }}" class="form-control @error('rate_title') is-invalid @enderror" placeholder="Rating section title"/>
                                            @error('rate_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        @php
                                            // Safely decode rate_options array
                                            $rateOptions = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->rate_options)) {
                                                    $rateOptions = $subscription->extraDetails->rate_options;
                                                } elseif (is_string($subscription->extraDetails->rate_options)) {
                                                    $decoded = json_decode($subscription->extraDetails->rate_options, true);
                                                    $rateOptions = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                            
                                            // Safely decode rate_icon array
                                            $rateIcons = [];
                                            if (!empty($subscription->extraDetails)) {
                                                if (is_array($subscription->extraDetails->rate_icon)) {
                                                    $rateIcons = $subscription->extraDetails->rate_icon;
                                                } elseif (is_string($subscription->extraDetails->rate_icon)) {
                                                    $decoded = json_decode($subscription->extraDetails->rate_icon, true);
                                                    $rateIcons = is_array($decoded) ? $decoded : [];
                                                }
                                            }
                                        @endphp
                                       
                                        @for($i = 0; $i < 5; $i++)
                                        <div class="row mb-3">
                                            <!-- Rate Option Field -->
                                            <div class="col-12 col-md-6">
                                                <input type="text" name="rate_options[]"
                                                       value="{{ isset($rateOptions[$i]) ? $rateOptions[$i] : old('rate_options.'.$i, '') }}"
                                                       class="form-control @error('rate_options.'.$i) is-invalid @enderror"
                                                       placeholder="Rating Option {{ $i + 1 }}"/>
                                                @error('rate_options.'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                           
                                            <!-- Rate Icon Field -->
                                            <div class="col-12 col-md-6">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="rate_icon_{{ $i }}" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="rate_icon[]" id="rate_icon_{{ $i }}"
                                                           value="{{ isset($rateIcons[$i]) ? $rateIcons[$i] : old('rate_icon.'.$i, '') }}"
                                                           class="form-control @error('rate_icon.'.$i) is-invalid @enderror"
                                                           placeholder="Rating Icon {{ $i + 1 }}"/>
                                                    <div class="input-group-append">
                                                        <button type="button" class="input-group-text admin-file-view" data-input="rate_icon_{{ $i }}">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('rate_icon.'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        @endfor
                                    </div>

                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Reviews</label>
                                            <div class="form-group mt-15">
                                                    <label class="input-label">Enter Review Count</label>
                                                    <input type="text" name="review_number" value="{{ !empty($subscription) ? $subscription->review_number : old('review_number') }}" class="form-control @error('review_number')  is-invalid @enderror"/>
                                                    @error('review_number')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                    </div>

                                    <!-- Advertisement -->
                                    <div class="form-group mt-15">
                                        <label class="input-label d-block" style="font-weight: 700;font-size:20px">Advertisement</label>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Ad Subtitle</label>
                                            <input type="text" name="ad_subtitle" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->ad_subtitle : old('ad_subtitle') }}" class="form-control @error('ad_subtitle') is-invalid @enderror" placeholder="Advertisement subtitle"/>
                                            @error('ad_subtitle')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Ad Title</label>
                                            <input type="text" name="ad_title" value="{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->ad_title : old('ad_title') }}" class="form-control @error('ad_title') is-invalid @enderror" placeholder="Advertisement title"/>
                                            @error('ad_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="form-group mt-15">
                                            <label class="input-label">Ad Description</label>
                                            <textarea name="ad_description" rows="3" class="form-control @error('ad_description') is-invalid @enderror" placeholder="Advertisement description">{{ !empty($subscription->extraDetails) ? $subscription->extraDetails->ad_description : old('ad_description') }}</textarea>
                                            @error('ad_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Category Filters -->
                                    <div class="form-group mt-15 {{ (!empty($subscriptionCategoryFilters) and count($subscriptionCategoryFilters)) ? '' : 'd-none' }}" id="categoriesFiltersContainer">
                                        <span class="input-label d-block">{{ trans('public.category_filters') }}</span>
                                        <div id="categoriesFiltersCard" class="row mt-3">

                                            @if(!empty($subscriptionCategoryFilters) and count($subscriptionCategoryFilters))
                                                @foreach($subscriptionCategoryFilters as $filter)
                                                    <div class="col-12 col-md-3">
                                                        <div class="webinar-category-filters">
                                                            <strong class="category-filter-title d-block">{{ $filter->title }}</strong>
                                                            <div class="py-10"></div>

                                                            @foreach($filter->options as $option)
                                                                <div class="form-group mt-3 d-flex align-items-center justify-content-between">
                                                                    <label class="text-gray font-14" for="filterOptions{{ $option->id }}">{{ $option->title }}</label>
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="filters[]" value="{{ $option->id }}" {{ ((!empty($subscriptionFilterOptions) && in_array($option->id,$subscriptionFilterOptions)) ? 'checked' : '') }} class="custom-control-input" id="filterOptions{{ $option->id }}">
                                                                        <label class="custom-control-label" for="filterOptions{{ $option->id }}"></label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                        </div>
                                    </div>
                                </section>

                                @if(!empty($subscription))
                                    <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('product.courses') }}</h2>
                                            <button id="subscriptionAddNewCourses" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('update.add_new_course') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if(!empty($subscriptionWebinars) and !$subscriptionWebinars->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th class="text-left">{{ trans('public.instructor') }}</th>
                                                                <th>{{ trans('public.price') }}</th>
                                                                <th>{{ trans('public.publish_date') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach($subscriptionWebinars as $subscriptionWebinar)
                                                                @if(!empty($subscriptionWebinar->webinar->title))
                                                                    <tr>
                                                                        <th>{{ $subscriptionWebinar->webinar->title }}</th>
                                                                        <td class="text-left">{{ $subscriptionWebinar->webinar->teacher->full_name }}</td>
                                                                        <td>{{  handlePrice($subscriptionWebinar->webinar->price) }}</td>
                                                                        <td>{{ dateTimeFormat($subscriptionWebinar->webinar->created_at,'j F Y | H:i') }}</td>

                                                                        <td>
                                                                            <button type="button" data-item-id="{{ $subscriptionWebinar->id }}" data-subscription-id="{{ !empty($subscription) ? $subscription->id : '' }}" class="edit-subscription-webinar btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                                <i class="fa fa-edit"></i>
                                                                            </button>

                                                                            @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/subscription-webinars/'. $subscriptionWebinar->id .'/delete', 'btnClass' => ' mt-1'])
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result',[
                                                        'file_name' => 'comment.png',
                                                        'title' => trans('update.subscription_webinar_no_result'),
                                                        'hint' => trans('update.subscription_webinar_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </section>
                                   
                                    @include('admin.subscriptions.create_includes.contents')
                                @endif

                                <section class="mt-30">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>
                                        <button id="subscriptionAddFAQ" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('public.add_faq') }}</button>
                                    </div>

                                    <div class="row mt-10">
                                        <div class="col-12">
                                            @if(!empty($faqs) and !$faqs->isEmpty())
                                                <div class="table-responsive">
                                                    <table class="table table-striped text-center font-14">

                                                        <tr>
                                                            <th>{{ trans('public.title') }}</th>
                                                            <th>{{ trans('public.answer') }}</th>
                                                            <th></th>
                                                        </tr>

                                                        @foreach($faqs as $faq)
                                                            <tr>
                                                                <th>{{ $faq->title }}</th>
                                                                <td>
                                                                    <button type="button" class="js-get-faq-description btn btn-sm btn-gray200">{{ trans('public.view') }}</button>
                                                                    <input type="hidden" value="{{ $faq->answer }}"/>
                                                                </td>

                                                                <td class="text-right">
                                                                    <button type="button" data-faq-id="{{ $faq->id }}" data-webinar-id="{{ !empty($subscription) ? $subscription->id : '' }}" class="edit-faq btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                        <i class="fa fa-edit"></i>
                                                                    </button>

                                                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/faqs/'. $faq->id .'/delete', 'btnClass' => ' mt-1'])
                                                                </td>
                                                            </tr>
                                                        @endforeach

                                                    </table>
                                                </div>
                                            @else
                                                @include('admin.includes.no-result',[
                                                    'file_name' => 'faq.png',
                                                    'title' => trans('public.faq_no_result'),
                                                    'hint' => trans('public.faq_no_result_hint'),
                                                ])
                                            @endif
                                        </div>
                                    </div>
                                </section>

                                <section class="mt-3">
                                    <h2 class="section-title after-line">{{ trans('public.message_to_reviewer') }}</h2>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mt-15">
                                                <textarea name="message_for_reviewer" rows="10" class="form-control">{{ (!empty($subscription) and $subscription->message_for_reviewer) ? $subscription->message_for_reviewer : old('message_for_reviewer') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <input type="hidden" name="draft" value="no" id="forDraft"/>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" id="saveAndPublish" class="btn btn-success">{{ !empty($subscription) ? trans('admin/main.save_and_publish') : trans('admin/main.save_and_continue') }}</button>

                                        @if(!empty($subscription))
                                            <button type="button" id="saveReject" class="btn btn-warning">{{ trans('public.reject') }}</button>

                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/subscriptions/'. $subscription->id .'/delete',
                                                    'btnText' => trans('public.delete'),
                                                    'hideDefaultClass' => true,
                                                    'btnClass' => 'btn btn-danger'
                                                    ])
                                        @endif
                                    </div>
                                </div>
                            </form>


                            @include('admin.subscriptions.modals.subscription-webinar')
                            @include('admin.subscriptions.modals.ticket')
                            @include('admin.subscriptions.modals.faq')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var titleLang = '{{ trans('admin/main.title') }}';
    </script>

    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/admin/js/subscriptions.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Auto-hide success alert after 5 seconds
            setTimeout(function() {
                $('.top-right-alert').fadeOut('slow');
            }, 5000);
            
            // Save and Publish button handler
            $('#saveAndPublish').on('click', function(e) {
                e.preventDefault();
                $('#forDraft').val('no');
                $('#webinarForm').submit();
            });
        });
    </script>
@endpush