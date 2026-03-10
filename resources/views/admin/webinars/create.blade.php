@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-timepicker/bootstrap-timepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16A34A",
                        "primary-light": "#F0FDF4",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "body": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        .webinar-edit-page { font-family: 'Inter', sans-serif; }
        .webinar-edit-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
        
        /* Refined Sidebar Buttons for high-density UI */
        .nav-tab-btn {
            @apply flex items-center px-4 py-3 rounded-[20px] text-[14px] font-bold transition-all text-slate-500 hover:bg-slate-50 w-full text-left mb-1;
        }
        .nav-tab-btn.active {
            @apply bg-[#16A34A] text-white shadow-lg shadow-green-100/50;
        }
        .nav-tab-btn .material-symbols-outlined {
            @apply text-[22px] mr-3 w-6 flex justify-center items-center;
        }
        .nav-tab-btn.active .material-symbols-outlined { font-variation-settings: 'FILL' 1; }
        .nav-tab-btn.active .nav-chevron { @apply mr-0 ml-auto text-[18px]; }
        .nav-tab-btn:not(.active) .nav-chevron { display: none; }
        .nav-tab-btn span:not(.material-symbols-outlined) { @apply leading-none; padding-bottom: 2px; }

        .tab-section { display: none; }
        .tab-section.active { display: block; @apply animate-in fade-in slide-in-from-bottom-2 duration-300; }

        .form-control, .custom-select {
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 !important;
            height: auto !important;
        }
        .input-label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5 ml-1 !important; }
        
        .section-title.after-line {
            @apply text-lg font-black text-slate-900 tracking-tight flex items-center gap-4 mb-8 !important;
        }
        .section-title.after-line::after {
            content: ''; @apply flex-1 h-px bg-slate-100;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
@php
    $readyProgress = 0;
    if(!empty($webinar)) {
        if(!empty($webinar->title)) $readyProgress += 20;
        if(!empty($webinar->category_id)) $readyProgress += 20;
        if(!empty($webinar->thumbnail)) $readyProgress += 20;
        if(!empty($webinar->price) || $webinar->price == 0) $readyProgress += 20;
        if($webinar->chapters->count() > 0 || $webinar->files->count() > 0 || $webinar->sessions->count() > 0) $readyProgress += 20;
    }
@endphp
<form method="post" action="{{ getAdminPanelUrl() }}/webinars/{{ !empty($webinar) ? $webinar->id.'/update' : 'store' }}" id="webinarForm" class="webinar-form">
    {{ csrf_field() }}

<div class="webinar-edit-page bg-[#F8FAFC] min-h-[calc(100vh-64px)]">
    
    <!-- Top Sleek Header -->
    <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <a href="{{ getAdminPanelUrl('/webinars') }}" class="size-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-100 transition-all">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div class="h-8 w-px bg-slate-200"></div>
            <div>
                <p class="text-[10px] font-black text-primary uppercase tracking-[2px] leading-none mb-1">Course Builder</p>
                <h1 class="text-base font-black text-slate-900 truncate max-w-[400px]">
                    Building: {{ !empty($webinar->title) ? $webinar->title : 'Untitled Course' }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
             <a href="{{ (!empty($webinar) and $webinar->status == 'active') ? $webinar->getUrl() : 'javascript:void(0)' }}" target="{{ (!empty($webinar) and $webinar->status == 'active') ? '_blank' : '_self' }}" class="px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 shadow-sm flex items-center gap-2 transition-all {{ (!empty($webinar) and $webinar->status == 'active') ? '' : 'opacity-50 cursor-not-allowed' }}" title="{{ (!empty($webinar) and $webinar->status == 'active') ? 'View Live' : 'Publish first to preview live' }}">
                <span class="material-symbols-outlined text-lg">visibility</span>
                Live Preview
            </a>
            <button type="button" id="saveAndPublishTop" class="px-5 py-2.5 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-lg">publish</span>
                Publish Course
            </button>
        </div>
    </header>

    <div class="flex">
        <!-- Vertical Tabs Sidebar -->
        <aside class="w-[320px] bg-white border-r border-slate-200 p-6 flex flex-col gap-1 sticky top-[73px] h-[calc(100vh-73px)] overflow-y-auto shrink-0 no-scrollbar">
            <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-[2px] mb-4">Structure</h3>
            
            <button type="button" class="nav-tab-btn active tab-btn" data-tab="general">
                <span class="material-symbols-outlined">settings</span>
                <span>General Details</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>
            <button type="button" class="nav-tab-btn tab-btn" data-tab="landing">
                <span class="material-symbols-outlined">desktop_windows</span>
                <span>Landing Page</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>
            <button type="button" class="nav-tab-btn tab-btn" data-tab="curriculum">
                <span class="material-symbols-outlined">menu_book</span>
                <span>Curriculum</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>
            <button type="button" class="nav-tab-btn tab-btn" data-tab="mentor">
                <span class="material-symbols-outlined">person_pin</span>
                <span>Mentor Profile</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>
            <button type="button" class="nav-tab-btn tab-btn" data-tab="roadmap">
                <span class="material-symbols-outlined">map</span>
                <span>6-Month Roadmap</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>
            <button type="button" class="nav-tab-btn tab-btn" data-tab="what_you_get">
                <span class="material-symbols-outlined">card_giftcard</span>
                <span>What You Get</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>
            <button type="button" class="nav-tab-btn tab-btn" data-tab="pricing">
                <span class="material-symbols-outlined">payments</span>
                <span>Pricing & Plans</span>
                <span class="material-symbols-outlined nav-chevron">chevron_right</span>
            </button>

            <!-- Status Box at Bottom of Sidebar -->
            <div class="mt-auto pt-8">
                <div class="bg-primary/5 rounded-3xl p-5 border border-primary/10">
                    <div class="flex items-center gap-2 text-primary mb-2">
                        <span class="material-symbols-outlined text-[16px] font-[FILL]">monitoring</span>
                        <span class="text-[10px] font-black uppercase tracking-widest">Launch Readiness: {{ $readyProgress }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 h-1 rounded-full mb-3 overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-1000" style="width: {{ $readyProgress }}%"></div>
                    </div>
                    <p class="text-[11px] font-medium text-slate-500 leading-relaxed mb-0">
                        @if($readyProgress == 100)
                            Everything is ready! You can now publish this course.
                        @elseif($readyProgress >= 60)
                            Almost there. Just a few more details to go live.
                        @else
                            Start by completing the basic information and curriculum.
                        @endif
                    </p>
                </div>
            </div>
        </aside>

        <!-- Dynamic Content Area -->
        <main class="flex-1 p-8 md:p-12 overflow-y-auto">
            <div class="max-w-[1000px] mx-auto">
                        <!-- Tab 1: General Details -->
                        <div class="tab-section active" id="general">
                            <h2 class="section-title after-line">{{ trans('public.basic_information') }}</h2>

                                    <div class="row">
                                        <div class="col-12 col-md-5">

                                            @if(!empty(getGeneralSettings('content_translate')))
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('auth.language') }}</label>
                                                    <select name="locale" class="form-control {{ !empty($webinar) ? 'js-edit-content-locale' : '' }}">
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
                                             <label class="input-label d-block">Course Language</label>
                                             <select name="lang" class="form-control @error('type') is-invalid  @enderror">
                                                            <option value="EN" @if(!empty($webinar) and $webinar->lang =='EN') selected @endif>English</option>
                                                            <option value="HI"  @if(!empty($webinar) and $webinar->lang =='HI') selected @endif >Hindi</option>

                                                    </select>

                                            <div class="form-group mt-15 ">
                                                <label class="input-label d-block">{{ trans('panel.course_type') }}</label>

                                                <select name="type" id="courseTypeSelect" class="custom-select @error('type')  is-invalid @enderror">
                                                    <option value="webinar" @if((!empty($webinar) and $webinar->isWebinar()) or old('type') == \App\Models\Webinar::$webinar) selected @endif>Live Classes</option>
                                                    <option value="course" @if((!empty($webinar) and $webinar->isCourse()) or old('type') == \App\Models\Webinar::$course) selected @endif>Video Course</option>
                                                    <option value="upcoming" @if((!empty($webinar) and $webinar->isUpcoming()) or old('type') == \App\Models\Webinar::$upcoming) selected @endif>Upcoming Course</option>
                                                </select>

                                                @error('type')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            {{-- Upcoming Course Extra Fields --}}
                                            <div id="upcomingFields" class="upcoming-fields-wrapper" style="{{ (old('type') == 'upcoming' || (!empty($webinar) && $webinar->isUpcoming())) ? '' : 'display:none;' }}">
                                                <div class="form-group mt-15">
                                                    <label class="input-label">Launch Date</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="fa fa-calendar-alt"></i></span>
                                                        </div>
                                                        <input type="text" name="launch_date" value="{{ (!empty($webinar) and $webinar->launch_date) ? dateTimeFormat($webinar->launch_date, 'Y-m-d H:i', false, false, $webinar->timezone ?? null) : old('launch_date') }}" class="form-control @error('launch_date') is-invalid @enderror datetimepicker" placeholder="Select launch date"/>
                                                        @error('launch_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="form-group mt-15">
                                                    <label class="input-label">After Launch Course Type</label>
                                                    <select name="post_launch_type" class="custom-select @error('post_launch_type') is-invalid @enderror">
                                                        <option value="">-- Select --</option>
                                                        <option value="webinar" @if((!empty($webinar) && $webinar->post_launch_type == 'webinar') || old('post_launch_type') == 'webinar') selected @endif>Live Classes</option>
                                                        <option value="course" @if((!empty($webinar) && $webinar->post_launch_type == 'course') || old('post_launch_type') == 'course') selected @endif>Video Course</option>
                                                    </select>
                                                    @error('post_launch_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                               <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.h1') }}</label>
                                                <input type="text" name="h1" value="{{ !empty($webinar) ? $webinar->h1 : old('h1') }}" class="form-control @error('h1')  is-invalid @enderror" placeholder=""/>
                                                @error('h1')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.title') }}</label>
                                                <input type="text" name="title" value="{{ !empty($webinar) ? $webinar->title : old('title') }}" class="form-control @error('title')  is-invalid @enderror" placeholder=""/>
                                                @error('title')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            @if(false) {{-- Temporarily Hidden: Points --}}
                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('update.points') }}</label>
                                                <input type="number" name="points" value="{{ !empty($webinar) ? $webinar->points : old('points') }}" class="form-control @error('points')  is-invalid @enderror" placeholder="Empty means inactive this mode"/>
                                                @error('points')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            @endif

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('admin/main.class_url') }}</label>
                                                <input type="text" name="slug" value="{{ !empty($webinar) ? $webinar->slug : old('slug') }}" class="form-control @error('slug')  is-invalid @enderror" placeholder=""/>
                                                <div class="text-muted text-small mt-1">{{ trans('admin/main.class_url_hint') }}</div>
                                                @error('slug')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            @if(!empty($webinar) and $webinar->creator->isOrganization())
                                                <div class="form-group mt-15 ">
                                                    <label class="input-label d-block">{{ trans('admin/main.organization') }}</label>

                                                    <select name="organ_id" data-search-option="just_organization_role" class="form-control search-user-select2" data-placeholder="{{ trans('search_organization') }}">
                                                        <option value="{{ $webinar->creator->id }}" selected>{{ $webinar->creator->full_name }}</option>
                                                    </select>
                                                </div>
                                            @endif

                                            <div class="form-group mt-15 ">
                                                <label class="input-label d-block">{{ trans('admin/main.select_a_instructor') }}</label>

                                                <select name="teacher_id" data-search-option="except_user" class="form-control search-user-select22 @error('teacher_id') is-invalid @enderror"
                                                        data-placeholder="{{ trans('public.select_a_teacher') }}"
                                                >
                                                    @if(!empty($webinar))
                                                        <option value="{{ $webinar->teacher->id }}" selected>{{ $webinar->teacher->full_name }}</option>
                                                    @else
                                                        <option selected disabled>{{ trans('public.select_a_teacher') }}</option>
                                                    @endif
                                                </select>

                                                @error('teacher_id')
                                                <div class="invalid-feedback d-block">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.seo_title') }}</label>
                                                <input type="text" name="seo_title" value="{{ !empty($webinar) ? $webinar->seo_title : old('seo_title') }}" class="form-control @error('seo_title')  is-invalid @enderror"/>

                                                @error('seo_title')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.seo_description') }}</label>
                                                <input type="text" name="seo_description" value="{{ !empty($webinar) ? $webinar->seo_description : old('seo_description') }}" class="form-control @error('seo_description')  is-invalid @enderror"/>
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
                                                    <input type="text" name="thumbnail" id="thumbnail" value="{{ !empty($webinar) ? $webinar->thumbnail : old('thumbnail') }}" class="form-control @error('thumbnail')  is-invalid @enderror"/>
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

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.cover_image') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="input-group-text admin-file-manager" data-input="cover_image" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="image_cover" id="cover_image" value="{{ !empty($webinar) ? $webinar->image_cover : old('image_cover') }}" class="form-control @error('image_cover')  is-invalid @enderror"/>
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
                                                            <option value="{{ $source }}" @if(!empty($webinar) and $webinar->video_demo_source == $source) selected @endif>{{ trans('update.file_source_'.$source) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group mt-0">
                                                <label class="input-label font-12">{{ trans('update.path') }}</label>
                                                <div class="input-group js-video-demo-path-input">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="js-video-demo-path-upload input-group-text admin-file-manager {{ (empty($webinar) or empty($webinar->video_demo_source) or $webinar->video_demo_source == 'upload') ? '' : 'd-none' }}" data-input="demo_video" data-preview="holder">
                                                            <i class="fa fa-upload"></i>
                                                        </button>

                                                        <button type="button" class="js-video-demo-path-links rounded-left input-group-text input-group-text-rounded-left  {{ (empty($webinar) or empty($webinar->video_demo_source) or $webinar->video_demo_source == 'upload') ? 'd-none' : '' }}">
                                                            <i class="fa fa-link"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text" name="video_demo" id="demo_video" value="{{ !empty($webinar) ? $webinar->video_demo : old('video_demo') }}" class="form-control @error('video_demo')  is-invalid @enderror"/>
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
                                                <textarea id="summernote" name="description" class="form-control @error('description')  is-invalid @enderror" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! !empty($webinar) ? $webinar->description : old('description')  !!}</textarea>
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
                                                <label class="input-label">{{ trans('public.capacity') }}</label>
                                                <input type="number" name="capacity" value="{{ !empty($webinar) ? $webinar->capacity : old('capacity') }}" class="form-control @error('capacity')  is-invalid @enderror"/>
                                                @error('capacity')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="row mt-15">
                                                @if(empty($webinar) or (!empty($webinar) and $webinar->isWebinar()))
                                                    <div class="col-12 col-md-6 js-start_date {{ (!empty(old('type')) and old('type') != \App\Models\Webinar::$webinar) ? 'd-none' : '' }}">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ trans('public.start_date') }}</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="dateInputGroupPrepend">
                                                                        <i class="fa fa-calendar-alt "></i>
                                                                    </span>
                                                                </div>
                                                                <input type="text" name="start_date" value="{{ (!empty($webinar) and $webinar->start_date) ? dateTimeFormat($webinar->start_date, 'Y-m-d H:i', false, false, $webinar->timezone) : old('start_date') }}" class="form-control @error('start_date')  is-invalid @enderror datetimepicker" aria-describedby="dateInputGroupPrepend"/>
                                                                @error('start_date')
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if(empty($webinar) or (!empty($webinar) and $webinar->isCourse()))
                                                    <div class="col-12 col-md-6 js-start_date {{ (!empty(old('type')) and old('type') != \App\Models\Webinar::$webinar) ? 'd-none' : '' }}">
                                                        <div class="form-group">
                                                            <label class="input-label">Launch Date <br>(Only in Upcomming Video Course)</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text" id="dateInputGroupPrepend">
                                                                        <i class="fa fa-calendar-alt "></i>
                                                                    </span>
                                                                </div>
                                                                <input type="text" name="start_date" value="{{ (!empty($webinar) and $webinar->start_date) ? dateTimeFormat($webinar->start_date, 'Y-m-d H:i', false, false, $webinar->timezone) : old('start_date') }}" class="form-control @error('start_date')  is-invalid @enderror datetimepicker" aria-describedby="dateInputGroupPrepend"/>
                                                                @error('start_date')
                                                                <div class="invalid-feedback">
                                                                    {{ $message }}
                                                                </div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="col-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('public.duration') }} ({{ trans('public.minutes') }})</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="timeInputGroupPrepend">
                                                                    <i class="fa fa-clock"></i>
                                                                </span>
                                                            </div>

                                                            <input type="number" name="duration" value="{{ !empty($webinar) ? $webinar->duration : old('duration') }}" class="form-control @error('duration')  is-invalid @enderror"/>
                                                            @error('duration')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if(getFeaturesSettings('timezone_in_create_webinar'))
                                                @php
                                                    $selectedTimezone = getGeneralSettings('default_time_zone');

                                                    if (!empty($webinar) and !empty($webinar->timezone)) {
                                                        $selectedTimezone = $webinar->timezone;
                                                    }
                                                @endphp

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('update.timezone') }}</label>
                                                    <select name="timezone" class="form-control select2" data-allow-clear="false">
                                                        @foreach(getListOfTimezones() as $timezone)
                                                            <option value="{{ $timezone }}" @if($selectedTimezone == $timezone) selected @endif>{{ $timezone }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('timezone')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                </div>
                                            @endif

                                            @if(!empty($webinar) and $webinar->creator->isOrganization())
                                                <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                    <label class="" for="privateSwitch">{{ trans('webinars.private') }}</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="private" class="custom-control-input" id="privateSwitch" {{ (!empty($webinar) and $webinar->private) ? 'checked' :  '' }}>
                                                        <label class="custom-control-label" for="privateSwitch"></label>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="supportSwitch">{{ trans('panel.support') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="support" class="custom-control-input" id="supportSwitch" {{ !empty($webinar) && $webinar->support ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="supportSwitch"></label>
                                                </div>
                                            </div>

                                            @if(false) {{-- Temporarily Hidden: Certificate --}}
                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="includeCertificateSwitch">{{ trans('update.include_certificate') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="certificate" class="custom-control-input" id="includeCertificateSwitch" {{ !empty($webinar) && $webinar->certificate ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="includeCertificateSwitch"></label>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="cursor-pointer" for="downloadableSwitch">{{ trans('home.downloadable') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="downloadable" class="custom-control-input" id="downloadableSwitch" {{ !empty($webinar) && $webinar->downloadable ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="downloadableSwitch"></label>
                                                </div>
                                            </div>

                                            @if(false) {{-- Temporarily Hidden: Partner Instructor --}}
                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="partnerInstructorSwitch">{{ trans('public.partner_instructor') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="partner_instructor" class="custom-control-input" id="partnerInstructorSwitch" {{ !empty($webinar) && $webinar->partner_instructor ? 'checked' : ''  }}>
                                                    <label class="custom-control-label" for="partnerInstructorSwitch"></label>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="forumSwitch">{{ trans('update.course_forum') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="forum" class="custom-control-input" id="forumSwitch" {{ !empty($webinar) && $webinar->forum ? 'checked' : ''  }}>
                                                    <label class="custom-control-label" for="forumSwitch"></label>
                                                </div>
                                            </div>

                                            @if(false) {{-- Temporarily Hidden: Subscribe --}}
                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="subscribeSwitch">{{ trans('public.subscribe') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="subscribe" class="custom-control-input" id="subscribeSwitch" {{ !empty($webinar) && $webinar->subscribe ? 'checked' : ''  }}>
                                                    <label class="custom-control-label" for="subscribeSwitch"></label>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="privateSwitch">{{ trans('webinars.private') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="private" class="custom-control-input" id="privateSwitch" {{ (!empty($webinar) and $webinar->private) ? 'checked' : ''  }}>
                                                    <label class="custom-control-label" for="privateSwitch"></label>
                                                </div>
                                            </div>

                                            @if(false) {{-- Temporarily Hidden: Enable Waitlist --}}
                                            <div class="form-group mt-30 d-flex align-items-center justify-content-between">
                                                <label class="" for="privateSwitch">{{ trans('update.enable_waitlist') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="enable_waitlist" class="custom-control-input" id="enable_waitlistSwitch" {{ (!empty($webinar) and $webinar->enable_waitlist) ? 'checked' : ''  }}>
                                                    <label class="custom-control-label" for="enable_waitlistSwitch"></label>
                                                </div>
                                            </div>
                                            @endif

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('update.access_days') }}</label>
                                                <input type="text" name="access_days" value="{{ !empty($webinar) ? $webinar->access_days : old('access_days') }}" class="form-control @error('access_days')  is-invalid @enderror"/>
                                                @error('access_days')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                                <p class="mt-1">- {{ trans('update.access_days_input_hint') }}</p>
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.price') }} ({{ $currency }})</label>
                                                <input type="text" name="price" value="{{ (!empty($webinar) and !empty($webinar->price)) ? convertPriceToUserCurrency($webinar->price) : old('price') }}" class="form-control @error('price')  is-invalid @enderror" placeholder="{{ trans('public.0_for_free') }}"/>
                                                @error('price')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            @if(!empty($webinar) and $webinar->creator->isOrganization())
                                                <div class="form-group mt-15">
                                                    <label class="input-label">{{ trans('update.organization_price') }} ({{ $currency }})</label>
                                                    <input type="number" name="organization_price" value="{{ (!empty($webinar) and $webinar->organization_price) ? convertPriceToUserCurrency($webinar->organization_price) : old('organization_price') }}" class="form-control @error('organization_price')  is-invalid @enderror" placeholder=""/>
                                                    @error('organization_price')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                    @enderror
                                                    <p class="font-12 text-gray mt-1">- {{ trans('update.organization_price_hint') }}</p>
                                                </div>
                                            @endif

                                            @if(false) {{-- Temporarily Hidden: Partner Instructor Input --}}
                                            <div id="partnerInstructorInput" class="form-group mt-15 {{ (!empty($webinar) && $webinar->partner_instructor) ? '' : 'd-none' }}">
                                                <label class="input-label d-block">{{ trans('public.select_a_partner_teacher') }}</label>

                                                <select name="partners[]" multiple data-search-option="just_teacher_role" class="js-search-partner-user form-control {{ (!empty($webinar) && $webinar->partner_instructor) ? 'search-user-select22' : '' }}"
                                                        data-placeholder="{{ trans('public.search_instructor') }}"
                                                >
                                                    @if(!empty($webinarPartnerTeacher))
                                                        @foreach($webinarPartnerTeacher as $partner)
                                                            @if(!empty($partner) and $partner->teacher)
                                                                <option value="{{ $partner->teacher->id }}" selected>{{ $partner->teacher->full_name }}</option>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <option selected disabled>{{ trans('public.search_instructor') }}</option>
                                                    @endif
                                                </select>

                                                <div class="text-muted text-small mt-1">{{ trans('admin/main.select_a_partner_hint') }}</div>
                                            </div>
                                            @endif

                                            @if(false) {{-- Temporarily Hidden: Tags --}}
                                            <div class="form-group mt-15">
                                                <label class="input-label d-block">{{ trans('public.tags') }}</label>
                                                <input type="text" name="tags" data-max-tag="5" value="{{ !empty($webinar) ? implode(',',$webinarTags) : '' }}" class="form-control inputtags" placeholder="{{ trans('public.type_tag_name_and_press_enter') }} ({{ trans('admin/main.max') }} : 5)"/>
                                            </div>
                                            @endif

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.category') }}</label>

                                                <select id="categories" class="custom-select @error('category_id')  is-invalid @enderror" name="category_id" required>
                                                    <option {{ !empty($webinar) ? '' : 'selected' }} disabled>{{ trans('public.choose_category') }}</option>
                                                    @foreach($categories as $category)
                                                        @if(!empty($category->subCategories) and count($category->subCategories))
                                                            <optgroup label="{{  $category->title }}">
                                                                @foreach($category->subCategories as $subCategory)
                                                                    <option value="{{ $subCategory->id }}" {{ (!empty($webinar) and $webinar->category_id == $subCategory->id) ? 'selected' : '' }}>{{ $subCategory->title }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @else
                                                            <option value="{{ $category->id }}" {{ (!empty($webinar) and $webinar->category_id == $category->id) ? 'selected' : '' }}>{{ $category->title }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>

                                                @error('category_id')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            @if(false) {{-- Temporarily Hidden: Order --}}
                                            <div class="form-group mt-15">
                                                <label class="input-label">{{trans('admin/main.order')}}</label>
                                                <input type="number" name="order" value="{{ !empty($webinar) ? $webinar->order : old('order') }}" class="form-control @error('order')  is-invalid @enderror"/>
                                                @error('order')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>
                                            @endif
                        </div> <!-- End General Tab -->

                        <!-- Tab 2: Landing Page -->
                        <div class="tab-section" id="landing">
                            <h2 class="section-title after-line">Landing Page Details</h2>
                                         <!-- Extra Details -->
<!-- Add this section after the "Extra Details" comment in your form -->
<div class="form-group mt-15">
    <label class="input-label d-block" style="font-weight: 700;font-size:20px">Main Content</label>
            <div class="form-group mt-15">
                <label class="input-label">Subtitle</label>
                <input type="text" name="subtitle" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->subtitle : old('subtitle') }}" class="form-control @error('subtitle') is-invalid @enderror"/>
                @error('subtitle')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Heading Main</label>
                <input type="text" name="heading_main" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->heading_main : old('heading_main') }}" class="form-control @error('heading_main') is-invalid @enderror"/>
                @error('heading_main')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Heading Sub</label>
                <input type="text" name="heading_sub" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->heading_sub : old('heading_sub') }}" class="form-control @error('heading_sub') is-invalid @enderror"/>
                @error('heading_sub')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Heading Extra</label>
                <input type="text" name="heading_extra" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->heading_extra : old('heading_extra') }}" class="form-control @error('heading_extra') is-invalid @enderror"/>
                @error('heading_extra')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mt-15">
                <label class="input-label">Subdescription</label>
                <textarea name="subdescription" rows="3" class="form-control @error('subdescription') is-invalid @enderror">{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->subdescription : old('subdescription') }}</textarea>
                @error('subdescription')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mt-15">
                <label class="input-label">Additional Description</label>
                <textarea name="additional_description" rows="4" class="form-control @error('additional_description') is-invalid @enderror">{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->additional_description : old('additional_description') }}</textarea>
                @error('additional_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Extra Description</label>
                <textarea name="extra_description" rows="4" class="form-control @error('extra_description') is-invalid @enderror">{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->extra_description : old('extra_description') }}</textarea>
                @error('extra_description')
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
        <input type="text" name="is_featured" id="is_featured" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->is_featured : old('is_featured') }}" class="form-control @error('is_featured') is-invalid @enderror"/>
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
<div class="form-group mt-15">
    <label class="input-label">Upload free content thumbnail</label>
    <div class="input-group">
        <div class="input-group-prepend">
            <button type="button" class="input-group-text admin-file-manager" data-input="free_content_thumbnail" data-preview="holder">
                <i class="fa fa-upload"></i>
            </button>
        </div>
        <input type="text" name="free_content_thumbnail" id="free_content_thumbnail" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->free_content_thumbnail : old('free_content_thumbnail') }}" class="form-control @error('free_content_thumbnail') is-invalid @enderror"/>
        <div class="input-group-append">
            <button type="button" class="input-group-text admin-file-view" data-input="free_content_thumbnail">
                <i class="fa fa-eye"></i>
            </button>
        </div>
        @error('free_content_thumbnail')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-group mt-15">
    <label class="input-label d-block"style="font-weight: 700;font-size:20px">Material Items</label>
   
    @php
        // Decode JSON data to array for material_icon
        $materialIcons = [];
        if (!empty($webinar->extraDetails) && !empty($webinar->extraDetails->material_icon)) {
            $materialIcons = is_array($webinar->extraDetails->material_icon)
                ? $webinar->extraDetails->material_icon
                : json_decode($webinar->extraDetails->material_icon, true);
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
                       value="{{ $materialIcons[$i] ?? old('material_icon.'.$i, '') }}"
                       class="form-control @error('material_icon.'.$i) is-invalid @enderror"/>
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
                value="{{ !empty($webinar->extraDetails) && !empty($webinar->extraDetails->material_text) && isset($webinar->extraDetails->material_text[$i]) ? $webinar->extraDetails->material_text[$i] : old('material_text.'.$i) }}"
                class="form-control @error('material_text.'.$i) is-invalid @enderror"/>
            @error('material_text.'.$i)
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    @endfor
</div>
<!-- comparision plan  -->
<div class="form-group mt-15">
     <label class="input-label d-block" style="font-weight: 700;font-size:20px">Comparision Plan</label>
     <div class="row">
 
    <div class="col-12 col-md-4 mb-3">
        <div class="form-group">
            <label class="input-label">Plan Duration </label>
            <input type="text" name="plan_duration" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->plan_duration : old('plan_duration') }}" class="form-control @error('plan_duration') is-invalid @enderror"/>
            @error('plan_duration')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
   
 
    <div class="col-12 col-md-4 mb-3">
        <div class="form-group">
            <label class="input-label">Plan Type</label>
            <input type="text" name="plan_type" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->plan_type : old('plan_type') }}" class="form-control @error('plan_type') is-invalid @enderror" />
            @error('plan_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
      <div class="col-12 col-md-4 mb-3">
        <div class="form-group">
            <label class="input-label">Plan Price</label>
            <input type="text" name="plan_price" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->plan_price : old('plan_price') }}" class="form-control @error('plan_price') is-invalid @enderror"/>
            @error('plan_price')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
   
    <div class="col-12 col-md-4 mb-3">
        <div class="form-group">
            <label class="input-label">Plan Cancel Text</label>
            <input type="text" name="plan_cancel_text" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->plan_cancel_text : old('plan_cancel_text') }}" class="form-control @error('plan_cancel_text') is-invalid @enderror" />
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
                   value="{{ old('plan_movie', $webinar->extraDetails->plan_movie ?? '') }}"
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
                   value="{{ old('plan_duration_option', $webinar->extraDetails->plan_duration_option ?? '') }}"
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
                    value="{{ old('price_suffix', $webinar->extraDetails->price_suffix ?? '') }}"
                    class="form-control @error('price_suffix') is-invalid @enderror"/>
                @error('price_suffix')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
             
        </div>
        <div class="col-12 col-md-4 mb-3">
        <div class="form-group">
            <label class="input-label">Plan Badge</label>
            <input type="text" name="plan_badge" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->plan_badge : old('plan_badge') }}" class="form-control @error('plan_badge') is-invalid @enderror"/>
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
            <input type="text" name="plan_icon" id="plan_icon" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->plan_icon : old('plan_icon') }}" class="form-control @error('plan_icon') is-invalid @enderror"/>
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
    <div class="input-group">
            <div class="input-group-prepend">
                <button type="button" class="input-group-text admin-file-manager" data-input="price_icon" data-preview="holder">
                    <i class="fa fa-upload"></i>
                </button>
            </div>
            <input type="text" name="price_icon" id="price_icon" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->price_icon : old('price_icon') }}" class="form-control @error('price_icon') is-invalid @enderror"/>
            <div class="input-group-append">
                <button type="button" class="input-group-text admin-file-view" data-input="price_icon">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            @error('price_icon')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    <div class="form-group mt-15">
        <label class="input-label">Comparison Text</label>
        <textarea name="comparison_text" rows="3" class="form-control @error('comparison_text') is-invalid @enderror">{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->comparison_text : old('comparison_text') }}</textarea>
        @error('comparison_text')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group mt-15">
        <label class="input-label">Price Text</label>
        <input type="text" name="price_text" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->price_text : old('price_text') }}" class="form-control @error('price_text') is-invalid @enderror"/>
        @error('price_text')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Tab: What You Get -->
                                    <div class="what-you-get-header mb-4">
                                        <h2 class="font-weight-bold" style="font-size:24px; color:#1f2937;">What You Get</h2>
                                        <p style="color:#6b7280;">Configure how this section appears on your landing page.</p>
                                        <p style="color:#6b7280; font-size:13px;" class="mt-3">List the specific benefits and items students will receive each month.</p>
                                    </div>

                                    <div id="benefits_items_container">
                                        @php
                                            $learnTexts = [];
                                            if (!empty($webinar->extraDetails) && !empty($webinar->extraDetails->learn_text)) {
                                                $learnTexts = is_string($webinar->extraDetails->learn_text) ? json_decode($webinar->extraDetails->learn_text, true) : $webinar->extraDetails->learn_text;
                                                $learnTexts = is_array($learnTexts) ? $learnTexts : [];
                                            }
                                            $benefitsCount = max(count($learnTexts), 1);
                                        @endphp

                                        @for($i = 0; $i < $benefitsCount; $i++)
                                        <div class="benefit-item d-flex align-items-center mb-3 p-3 border rounded" style="border-color:#e5e7eb; background:#fff;">
                                            <div class="mr-3" style="color:#16a34a; font-size:20px;">
                                                <i class="far fa-check-circle"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="text" name="learn_text[]" value="{{ $learnTexts[$i] ?? '' }}" class="form-control border-0 bg-transparent p-0" placeholder="e.g. 10 Video Lessons + Assignments" style="color:#374151; font-weight:600; font-size:15px; box-shadow:none;" />
                                            </div>
                                            <div class="ml-3">
                                                <button type="button" class="btn btn-sm text-danger remove-benefit-item bg-transparent shadow-none">
                                                    <i class="fas fa-trash-alt" style="color:#d1d5db;"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endfor
                                    </div>

                                    <button type="button" id="add_benefit_item" class="btn btn-block" style="border: 1px dashed #e5e7eb; background: #f9fafb; color: #6b7280; font-weight: 600; padding: 10px; border-radius: 8px;">
                                        <i class="fas fa-plus mr-1"></i> Add Benefit Item
                                    </button>
<!-- Bonus -->
 <div class="form-group mt-15">
     <label class="input-label d-block" style="font-weight: 700;font-size:20px">Bonus</label>
        <div class="form-group mt-15">
            <label class="input-label">Bonus Heading</label>
            <input type="text" name="bonus_heading" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->bonus_heading : old('bonus_heading') }}" class="form-control @error('bonus_heading') is-invalid @enderror"/>
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
                <input type="text" name="bonus_icon" id="bonus_icon" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->bonus_icon : old('bonus_icon') }}" class="form-control @error('bonus_icon') is-invalid @enderror"/>
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

 
<!-- Tab: Roadmap -->
                                    <div class="roadmap-header mb-4">
                                        <h2 class="font-weight-bold" style="font-size:24px; color:#1f2937;">6-Month Roadmap</h2>
                                        <p style="color:#6b7280;">Configure how this section appears on your landing page.</p>
                                        <p style="color:#6b7280; font-size:13px;" class="mt-3">Define the learning journey for your students over a 6-month period.</p>
                                    </div>

                                    <div class="roadmap-table-container border rounded" style="border-color:#e5e7eb;">
                                        <div class="row align-items-center font-weight-bold px-3 py-2 bg-light border-bottom" style="font-size:12px; color:#6b7280; text-transform:uppercase;">
                                            <div class="col-3">Month</div>
                                            <div class="col-4">Focus</div>
                                            <div class="col-4">Outcome</div>
                                            <div class="col-1 text-center"></div>
                                        </div>

                                        <div id="roadmap_items_container">
                                            @php
                                                $certificationTime = [];
                                                $certificationFocus = [];
                                                $certificationOutcome = [];
                                                if (!empty($webinar->extraDetails)) {
                                                    $certificationTime = is_string($webinar->extraDetails->certification_time) ? json_decode($webinar->extraDetails->certification_time, true) : $webinar->extraDetails->certification_time;
                                                    $certificationTime = is_array($certificationTime) ? $certificationTime : [];
                                                    $certificationFocus = is_string($webinar->extraDetails->certification_focus) ? json_decode($webinar->extraDetails->certification_focus, true) : $webinar->extraDetails->certification_focus;
                                                    $certificationFocus = is_array($certificationFocus) ? $certificationFocus : [];
                                                    $certificationOutcome = is_string($webinar->extraDetails->certification_outcome) ? json_decode($webinar->extraDetails->certification_outcome, true) : $webinar->extraDetails->certification_outcome;
                                                    $certificationOutcome = is_array($certificationOutcome) ? $certificationOutcome : [];
                                                }
                                                $roadmapCount = max(count($certificationTime), count($certificationFocus), count($certificationOutcome), 1);
                                            @endphp

                                            @for($i = 0; $i < $roadmapCount; $i++)
                                            <div class="row align-items-center px-3 py-3 border-bottom roadmap-item" style="border-color:#e5e7eb;">
                                                <div class="col-3">
                                                    <input type="text" name="certification_time[]" value="{{ $certificationTime[$i] ?? '' }}" class="form-control border-0 bg-transparent p-0" placeholder="e.g. 1-2 Month" style="font-weight:600; color:#374151; font-size:14px; box-shadow:none;" />
                                                </div>
                                                <div class="col-4">
                                                    <input type="text" name="certification_focus[]" value="{{ $certificationFocus[$i] ?? '' }}" class="form-control border-0 bg-transparent p-0" placeholder="e.g. Foundations" style="color:#6b7280; font-size:14px; box-shadow:none;" />
                                                </div>
                                                <div class="col-4">
                                                    <input type="text" name="certification_outcome[]" value="{{ $certificationOutcome[$i] ?? '' }}" class="form-control border-0 bg-transparent p-0" placeholder="e.g. Understand zodiac" style="color:#6b7280; font-size:14px; box-shadow:none;" />
                                                </div>
                                                <div class="col-1 text-center">
                                                    <button type="button" class="btn btn-sm text-danger remove-roadmap-item bg-transparent shadow-none">
                                                        <i class="fas fa-trash-alt" style="color:#d1d5db;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endfor
                                        </div>
                                    </div>

                                    <button type="button" id="add_roadmap_item" class="btn btn-block mt-3" style="border: 1px dashed #e5e7eb; background: #f9fafb; color: #6b7280; font-weight: 600; padding: 10px; border-radius: 8px;">
                                        <i class="fas fa-plus mr-1"></i> Add Milestone
                                    </button>
<!-- Rating -->


<div class="form-group mt-15">
    <label class="input-label d-block"style="font-weight: 700;font-size:20px">Rating Options & Icons (5 Items)</label>
    <div class="form-group mt-15">
    <label class="input-label">Rating Title</label>
    <input type="text" name="rate_title" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->rate_title : old('rate_title') }}" class="form-control @error('rate_title') is-invalid @enderror"/>
    @error('rate_title')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    </div>
    @php
        // Decode JSON data to array
        $rateIcons = [];
        if (!empty($webinar->extraDetails) && !empty($webinar->extraDetails->rate_icon)) {
            $rateIcons = is_array($webinar->extraDetails->rate_icon)
                ? $webinar->extraDetails->rate_icon
                : json_decode($webinar->extraDetails->rate_icon, true);
        }
    @endphp
   
    @for($i = 0; $i < 5; $i++)
    <div class="row mb-3">
        <!-- Rate Option Field -->
        <div class="col-12 col-md-6">
         
            <input type="text" name="rate_options[]"
                value="{{ !empty($webinar->extraDetails) && !empty($webinar->extraDetails->rate_options) && isset($webinar->extraDetails->rate_options[$i]) ? $webinar->extraDetails->rate_options[$i] : old('rate_options.'.$i) }}"
                class="form-control @error('rate_options.'.$i) is-invalid @enderror"/>
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
                       value="{{ $rateIcons[$i] ?? old('rate_icon.'.$i, '') }}"
                       class="form-control @error('rate_icon.'.$i) is-invalid @enderror"/>
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
<!-- Advertisement -->
  <div class="form-group mt-15">
     <label class="input-label d-block" style="font-weight: 700;font-size:20px">Advertisement</label>
       <div class="form-group mt-15">
                <label class="input-label">Ad Subtitle</label>
                <input type="text" name="ad_subtitle" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->ad_subtitle : old('ad_subtitle') }}" class="form-control @error('ad_subtitle') is-invalid @enderror"/>
                @error('ad_subtitle')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Ad Title</label>
                <input type="text" name="ad_title" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->ad_title : old('ad_title') }}" class="form-control @error('ad_title') is-invalid @enderror"/>
                @error('ad_title')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Ad Description</label>
                <textarea name="ad_description" rows="3" class="form-control @error('ad_description') is-invalid @enderror">{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->ad_description : old('ad_description') }}</textarea>
                @error('ad_description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group mt-15">
                <label class="input-label">Ad Image</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button type="button" class="input-group-text admin-file-manager" data-input="ad_img" data-preview="holder">
                            <i class="fa fa-upload"></i>
                        </button>
                    </div>
                    <input type="text" name="ad_img" id="ad_img" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->ad_img : old('ad_img') }}" class="form-control @error('ad_img') is-invalid @enderror"/>
                    <div class="input-group-append">
                        <button type="button" class="input-group-text admin-file-view" data-input="ad_img">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    @error('ad_img')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
  </div>

<!-- Risk & CTA -->
<div class="form-group mt-15">
    <label class="input-label d-block" style="font-weight: 700;font-size:20px">Risk & CTA</label>
    <div class="form-group mt-15">
        <label class="input-label">Risk Title</label>
        <input type="text" name="risk_title" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->risk_title : old('risk_title') }}" class="form-control @error('risk_title') is-invalid @enderror"/>
        @error('risk_title')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group mt-15">
        <label class="input-label">Risk Description</label>
        <textarea name="risk_description" rows="3" class="form-control @error('risk_description') is-invalid @enderror">{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->risk_description : old('risk_description') }}</textarea>
        @error('risk_description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group mt-15">
        <label class="input-label">CTA Text</label>
        <input type="text" name="cta_text" value="{{ !empty($webinar->extraDetails) ? $webinar->extraDetails->cta_text : old('cta_text') }}" class="form-control @error('cta_text') is-invalid @enderror"/>
        @error('cta_text')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
</div>

<!-- extra Details -->

                                    <div class="form-group mt-15 {{ (!empty($webinarCategoryFilters) and count($webinarCategoryFilters)) ? '' : 'd-none' }}" id="categoriesFiltersContainer">
                                        <span class="input-label d-block">{{ trans('public.category_filters') }}</span>
                                        <div id="categoriesFiltersCard" class="row mt-3">

                                            @if(!empty($webinarCategoryFilters) and count($webinarCategoryFilters))
                                                @foreach($webinarCategoryFilters as $filter)
                                                    <div class="col-12 col-md-3">
                                                        <div class="webinar-category-filters">
                                                            <strong class="category-filter-title d-block">{{ $filter->title }}</strong>
                                                            <div class="py-10"></div>

                                                            @foreach($filter->options as $option)
                                                                <div class="form-group mt-3 d-flex align-items-center justify-content-between">
                                                                    <label class="text-gray font-14" for="filterOptions{{ $option->id }}">{{ $option->title }}</label>
                                                                    <div class="custom-control custom-checkbox">
                                                                        <input type="checkbox" name="filters[]" value="{{ $option->id }}" {{ ((!empty($webinarFilterOptions) && in_array($option->id,$webinarFilterOptions)) ? 'checked' : '') }} class="custom-control-input" id="filterOptions{{ $option->id }}">
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

                                @if(false) {{-- Temporarily Hidden: Pricing Plans (original condition: !empty($webinar)) --}}
                                    <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('admin/main.price_plans') }}</h2>
                                            <button id="webinarAddTicket" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('admin/main.add_price_plan') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">

                                                @if(!empty($tickets) and !$tickets->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th>{{ trans('public.discount') }}</th>
                                                                <th>{{ trans('public.capacity') }}</th>
                                                                <th>{{ trans('public.date') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach($tickets as $ticket)
                                                                <tr>
                                                                    <th scope="row">{{ $ticket->title }}</th>
                                                                    <td>{{ $ticket->discount }}%</td>
                                                                    <td>{{ $ticket->capacity }}</td>
                                                                    <td>{{ dateTimeFormat($ticket->start_date,'j F Y') }} - {{ (new DateTime())->setTimestamp($ticket->end_date)->format('j F Y') }}</td>
                                                                    <td>
                                                                        <button type="button" data-ticket-id="{{ $ticket->id }}" data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}" class="edit-ticket btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                            <i class="fa fa-edit"></i>
                                                                        </button>

                                                                        @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/tickets/'. $ticket->id .'/delete', 'btnClass' => ' mt-1'])
                                                                    </td>
                                                                </tr>
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result',[
                                                        'file_name' => 'ticket.png',
                                                        'title' => trans('public.ticket_no_result'),
                                                        'hint' => trans('public.ticket_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </section>
                                @endif

                                @if(!empty($webinar))
                                    @include('admin.webinars.create_includes.contents')

                                    @if(false) {{-- Temporarily Hidden: Prerequisites --}}
                                    <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('public.prerequisites') }}</h2>
                                            <button id="webinarAddPrerequisites" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('public.add_prerequisites') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if(!empty($prerequisites) and !$prerequisites->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th class="text-left">{{ trans('public.instructor') }}</th>
                                                                <th>{{ trans('public.price') }}</th>
                                                                <th>{{ trans('public.publish_date') }}</th>
                                                                <th>{{ trans('public.forced') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach($prerequisites as $prerequisite)
                                                                @if(!empty($prerequisite->prerequisiteWebinar->title))
                                                                    <tr>
                                                                        <th>{{ $prerequisite->prerequisiteWebinar->title }}</th>
                                                                        <td class="text-left">{{ $prerequisite->prerequisiteWebinar->teacher->full_name }}</td>
                                                                        <td>{{  handlePrice($prerequisite->prerequisiteWebinar->price) }}</td>
                                                                        <td>{{ dateTimeFormat($prerequisite->prerequisiteWebinar->created_at,'j F Y | H:i') }}</td>
                                                                        <td>{{ $prerequisite->required ? trans('public.yes') : trans('public.no') }}</td>

                                                                        <td>
                                                                            <button type="button" data-prerequisite-id="{{ $prerequisite->id }}" data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}" class="edit-prerequisite btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                                <i class="fa fa-edit"></i>
                                                                            </button>

                                                                            @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/prerequisites/'. $prerequisite->id .'/delete', 'btnClass' => ' mt-1'])
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result',[
                                                        'file_name' => 'comment.png',
                                                        'title' => trans('public.prerequisites_no_result'),
                                                        'hint' => trans('public.prerequisites_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </section>
                                    @endif
<!-- FAQ Section -->
<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>
        <button id="webinarAddFAQ" type="button" class="btn btn-primary btn-sm mt-3">
            {{ trans('public.add_faq') }}
        </button>
    </div>

    <div class="row mt-10">
        <div class="col-12">
            @php
                $faqs = !empty($webinar) ? $webinar->faqs()->where('type', 'faq')->get() : collect();
            @endphp
           
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
                                    <button type="button" class="js-get-faq-description btn btn-sm btn-gray200">
                                        {{ trans('public.view') }}
                                    </button>
                                    <input type="hidden" value="{{ $faq->answer }}"/>
                                </td>

                                <td class="text-right">
                                    <button type="button"
                                            data-faq-id="{{ $faq->id }}"
                                            data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}"
                                            class="edit-faq btn-transparent text-primary mt-1"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="{{ trans('admin/main.edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    @include('admin.includes.delete_button',[
                                        'url' => getAdminPanelUrl().'/faqs/'. $faq->id .'/delete',
                                        'btnClass' => ' mt-1'
                                    ])
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

<!-- Why Choose Us Section -->
@if(false) {{-- Temporarily Hidden: Why Choose Us --}}
<section class="mt-30">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="section-title after-line">Why Choose Us</h2>
        <button id="webinarAddWhyChooseUs" type="button" class="btn btn-primary btn-sm mt-3">
           Add Why Choose Us
        </button>
    </div>

    <div class="row mt-10">
        <div class="col-12">
            @php
                $whyChooseUsList = !empty($webinar) ? $webinar->faqs()->where('type', 'why_choose_us')->get() : collect();
            @endphp

            @if(!empty($whyChooseUsList) and !$whyChooseUsList->isEmpty())
                <div class="table-responsive">
                    <table class="table table-striped text-center font-14">
                        <tr>
                            <th>{{ trans('public.title') }}</th>
                            <th>{{ trans('public.answer') }}</th>
                            <th></th>
                        </tr>

                        @foreach($whyChooseUsList as $whyChooseItem)
                            <tr>
                                <th>{{ $whyChooseItem->title }}</th>
                                <td>
                                    <button type="button" class="js-get-faq-description btn btn-sm btn-gray200">
                                        {{ trans('public.view') }}
                                    </button>
                                    <input type="hidden" value="{{ $whyChooseItem->answer }}"/>
                                </td>

                                <td class="text-right">
                                    <button type="button"
                                            data-faq-id="{{ $whyChooseItem->id }}"
                                            data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}"
                                            class="edit-why-choose-us btn-transparent text-primary mt-1"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="{{ trans('admin/main.edit') }}">
                                        <i class="fa fa-edit"></i>
                                    </button>

                                    @include('admin.includes.delete_button',[
                                        'url' => getAdminPanelUrl().'/faqs/'. $whyChooseItem->id .'/delete',
                                        'btnClass' => ' mt-1'
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @else
                @include('admin.includes.no-result',[
                    'file_name' => 'faq.png',
                    'title' => trans('public.why_choose_us_no_result'),
                    'hint' => trans('public.why_choose_us_no_result_hint'),
                ])
            @endif
        </div>
    </div>
</section>
@endif
                                    @foreach(\App\Models\WebinarExtraDescription::$types as $webinarExtraDescriptionType)
                                    @if(!in_array($webinarExtraDescriptionType, ['learning_materials', 'company_logos'])) {{-- Temporarily Hidden: Learning Materials & Company Logos --}}
                                        <section class="mt-30">
                                            <div class="d-flex justify-content-between align-items-center">
                                                @if(trans('update.'.$webinarExtraDescriptionType)=='Requirements')
                                                <h2 class="section-title after-line">Bonuses</h2>
                                                <button id="add_new_{{ $webinarExtraDescriptionType }}" type="button" class="btn btn-primary btn-sm mt-3">New bonus</button>
                                           @else
                                           <h2 class="section-title after-line">{{ trans('update.'.$webinarExtraDescriptionType) }}</h2>
                                                <button id="add_new_{{ $webinarExtraDescriptionType }}" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('update.add_'.$webinarExtraDescriptionType) }}</button>

                                           @endif

                                            </div>

                                            @php
                                                $webinarExtraDescriptionValues = $webinar->webinarExtraDescription->where('type',$webinarExtraDescriptionType);
                                            @endphp

                                            <div class="row mt-10">
                                                <div class="col-12">
                                                    @if(!empty($webinarExtraDescriptionValues) and count($webinarExtraDescriptionValues))
                                                        <div class="table-responsive">
                                                            <table class="table table-striped text-center font-14">

                                                                <tr>
                                                                    @if($webinarExtraDescriptionType == \App\Models\WebinarExtraDescription::$COMPANY_LOGOS)
                                                                        <th>{{ trans('admin/main.icon') }}</th>
                                                                    @else
                                                                        <th>{{ trans('public.title') }}</th>
                                                                    @endif
                                                                    <th></th>
                                                                </tr>

                                                                @foreach($webinarExtraDescriptionValues as $extraDescription)
                                                                    <tr>
                                                                        @if($webinarExtraDescriptionType == \App\Models\WebinarExtraDescription::$COMPANY_LOGOS)
                                                                            <td>
                                                                                <img src="{{ $extraDescription->value }}" class="webinar-extra-description-company-logos" alt="">
                                                                            </td>
                                                                        @else
                                                                            <td>{{ $extraDescription->value }}</td>
                                                                        @endif

                                                                        <td class="text-right">
                                                                            <button type="button" data-item-id="{{ $extraDescription->id }}" data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}" class="edit-extraDescription btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                                <i class="fa fa-edit"></i>
                                                                            </button>

                                                                            @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/webinar-extra-description/'. $extraDescription->id .'/delete', 'btnClass' => ' mt-1'])
                                                                        </td>
                                                                    </tr>
                                                                @endforeach

                                                            </table>
                                                        </div>
                                                    @else
                                                        @include('admin.includes.no-result',[
                                                             'file_name' => 'faq.png',
                                                             'title' => trans("update.{$webinarExtraDescriptionType}_no_result"),
                                                             'hint' => trans("update.{$webinarExtraDescriptionType}_no_result_hint"),
                                                        ])
                                                    @endif
                                                </div>
                                            </div>
                                        </section>
                                    @endif
                                    @endforeach

                                    <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('public.quiz_certificate') }}</h2>
                                            <button id="webinarAddQuiz" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('public.add_quiz') }}</button>
                                        </div>
                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if(!empty($webinarQuizzes) and !$webinarQuizzes->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th>{{ trans('public.questions') }}</th>
                                                                <th>{{ trans('public.total_mark') }}</th>
                                                                <th>{{ trans('public.pass_mark') }}</th>
                                                                <th>{{ trans('public.certificate') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach($webinarQuizzes as $webinarQuiz)
                                                                <tr>
                                                                    <th>{{ $webinarQuiz->title }}</th>
                                                                    <td>{{ $webinarQuiz->quizQuestions->count() }}</td>
                                                                    <td>{{ $webinarQuiz->quizQuestions->sum('grade') }}</td>
                                                                    <td>{{ $webinarQuiz->pass_mark }}</td>
                                                                    <td>{{ $webinarQuiz->certificate ? trans('public.yes') : trans('public.no') }}</td>
                                                                    <td>
                                                                        <button type="button" data-webinar-quiz-id="{{ $webinarQuiz->id }}" data-webinar-id="{{ !empty($webinar) ? $webinar->id : '' }}" class="edit-webinar-quiz btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                                                            <i class="fa fa-edit"></i>
                                                                        </button>

                                                                        @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/webinar-quiz/'. $webinarQuiz->id .'/delete', 'btnClass' => ' mt-1'])
                                                                    </td>
                                                                    @endforeach
                                                                </tr>

                                                        </table>
                                                    </div>
                                                @else
                                                    @include('admin.includes.no-result',[
                                                        'file_name' => 'cert.png',
                                                        'title' => trans('public.quizzes_no_result'),
                                                        'hint' => trans('public.quizzes_no_result_hint'),
                                                    ])
                                                @endif
                                            </div>
                                        </div>
                                    </section>
                                @endif

                                @if(false) {{-- Temporarily Hidden: Message to Reviewer --}}
                                <section class="mt-3">
                                    <h2 class="section-title after-line">{{ trans('public.message_to_reviewer') }}</h2>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group mt-15">
                                                <textarea name="message_for_reviewer" rows="10" class="form-control">{{ (!empty($webinar) && $webinar->message_for_reviewer) ? $webinar->message_for_reviewer : old('message_for_reviewer') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                                @endif

                                <input type="hidden" name="draft" value="no" id="forDraft"/>

                                <div class="row">
                                    <div class="col-12">
                                        <button type="button" id="saveAndPublish" class="btn btn-success">{{ !empty($webinar) ? trans('admin/main.save_and_publish') : trans('admin/main.save_and_continue') }}</button>

                                        @if(!empty($webinar))
                                            <button type="button" id="saveReject" class="btn btn-warning">{{ ($webinar->status == "active") ? trans('update.unpublish') : trans('public.reject') }}</button>

                                            @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/webinars/'. $webinar->id .'/delete',
                                                    'btnText' => trans('public.delete'),
                                                    'hideDefaultClass' => true,
                                                    'btnClass' => 'btn btn-danger'
                                                    ])
                                        @endif
                                    </div>
                                </div>
            </div> <!-- End max-w-1000px -->
        </main> <!-- End Main Content area -->
    </div> <!-- End flex container -->
</div> <!-- End webinar-edit-page container -->
</form>
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var titleLang = '{{ trans('admin/main.title') }}';
        var zoomJwtTokenInvalid = '{{ trans('admin/main.teacher_zoom_jwt_token_invalid') }}';
        var editChapterLang = '{{ trans('public.edit_chapter') }}';
        var requestFailedLang = '{{ trans('public.request_failed') }}';
        var thisLiveHasEndedLang = '{{ trans('update.this_live_has_been_ended') }}';
        var quizzesSectionLang = '{{ trans('quiz.quizzes_section') }}';
        var filePathPlaceHolderBySource = {
            upload: '{{ trans('update.file_source_upload_placeholder') }}',
            youtube: '{{ trans('update.file_source_youtube_placeholder') }}',
            vimeo: '{{ trans('update.file_source_vimeo_placeholder') }}',
            external_link: '{{ trans('update.file_source_external_link_placeholder') }}',
            secure_host_link: 'Paste the secure host link',
            google_drive: '{{ trans('update.file_source_google_drive_placeholder') }}',
            dropbox: '{{ trans('update.file_source_dropbox_placeholder') }}',
            iframe: '{{ trans('update.file_source_iframe_placeholder') }}',
            s3: '{{ trans('update.file_source_s3_placeholder') }}',
        }
    </script>

    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>

    <script src="/assets/default/js/admin/quiz.min.js"></script>
    <script src="/assets/admin/js/webinar.min.js"></script>

    <script>
        $(document).ready(function () {
            var $typeSelect = $('#courseTypeSelect');
            var $upcomingFields = $('#upcomingFields');
            var $startDateBlocks = $('.js-start_date');

            function toggleUpcomingFields() {
                var selectedType = $typeSelect.val();

                if (selectedType === 'upcoming') {
                    $upcomingFields.show();
                    $startDateBlocks.hide();
                } else if (selectedType === 'webinar') {
                    $upcomingFields.hide();
                    $startDateBlocks.show();
                } else {
                    $upcomingFields.hide();
                    $startDateBlocks.hide();
                }
            }

            toggleUpcomingFields();
            $typeSelect.on('change', toggleUpcomingFields);

            // Tab Switching Logic
            $('.tab-btn').on('click', function() {
                const tabId = $(this).data('tab');
                
                // Update buttons
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                // Update sections
                $('.tab-section').removeClass('active');
                $('#' + tabId).addClass('active');
                
                // Save active tab to localStorage
                localStorage.setItem('active_course_tab', tabId);
            });

            // Restore active tab
            const activeTab = localStorage.getItem('active_course_tab');
            if (activeTab) {
                $(`.tab-btn[data-tab="${activeTab}"]`).trigger('click');
            }

            // Mentor Image Preview
            $('#mentor_image').on('change', function() {
                const url = $(this).val();
                if (url) {
                    $('#mentor_image_preview').attr('src', url);
                }
            });

            // Form Submit Buttons
            $('#saveAndPublish').on('click', function (e) {
                e.preventDefault();
                $('#webinarForm').append('<input type="hidden" name="draft" value="publish">');
                $('#webinarForm').submit();
            });

            $('#saveReject').on('click', function (e) {
                e.preventDefault();
                $('#saveRejectTop').click();
            });
        });
    </script>
@endpush