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

                                     {{--       <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('update.required_points') }}</label>
                                                <input type="text" name="points" value="{{ !empty($subscription) ? $subscription->points : old('points') }}" class="form-control @error('points')  is-invalid @enderror" placeholder="Empty means inactive this mode"/>
                                                <div class="text-muted text-small mt-1">{{ trans('update.product_points_hint') }}</div>
                                                @error('points')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div> --}}

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

                                            <div class="form-group mt-15 ">
                                                <label class="input-label d-block">{{ trans('admin/main.select_a_instructor') }}</label>

                                                <select name="teacher_id" data-search-option="just_teacher_role" class="form-control search-user-select2"
                                                        data-placeholder="{{ trans('public.select_a_teacher') }}"
                                                >
                                                    @if(!empty($subscription))
                                                        <option value="{{ $subscription->teacher->id }}" selected>{{ $subscription->teacher->full_name }}</option>
                                                    @else
                                                        <option selected disabled>{{ trans('public.select_a_teacher') }}</option>
                                                    @endif
                                                </select>

                                                @error('teacher_id')
                                                <div class="invalid-feedback">
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

                                          {{--  <div class="form-group mt-3 d-flex align-items-center justify-content-between">
                                                <label class="" for="subscribeSwitch">{{ trans('public.subscribe') }}</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" name="subscribe" class="custom-control-input" id="subscribeSwitch" {{ !empty($subscription) && $subscription->subscribe ? 'checked' : ''  }}>
                                                    <label class="custom-control-label" for="subscribeSwitch"></label>
                                                </div>
                                            </div> --}}

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
                                                <input type="text" name="price" value="{{ !empty($subscription) ? $subscription->price : old('price') }}" class="form-control @error('price')  is-invalid @enderror" placeholder="{{ trans('public.0_for_free') }}"/>
                                                @error('price')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label d-block">{{ trans('public.tags') }}</label>
                                                <input type="text" name="tags" data-max-tag="5" value="{{ !empty($subscription) ? implode(',',$subscriptionTags) : '' }}" class="form-control inputtags" placeholder="{{ trans('public.type_tag_name_and_press_enter') }} ({{ trans('admin/main.max') }} : 5)"/>
                                            </div>

                                            <div class="form-group mt-15">
                                                <label class="input-label">{{ trans('public.category') }}</label>

                                                <select id="categories" class="custom-select @error('category_id')  is-invalid @enderror" name="category_id" required>
                                                    <option {{ !empty($subscription) ? '' : 'selected' }} disabled>{{ trans('public.choose_category') }}</option>
                                                    @foreach($categories as $category)
                                                        @if(!empty($category->subCategories) and count($category->subCategories))
                                                            <optgroup label="{{  $category->title }}">
                                                                @foreach($category->subCategories as $subCategory)
                                                                    <option value="{{ $subCategory->id }}" {{ (!empty($subscription) and $subscription->category_id == $subCategory->id) ? 'selected' : '' }}>{{ $subCategory->title }}</option>
                                                                @endforeach
                                                            </optgroup>
                                                        @else
                                                            <option value="{{ $category->id }}" {{ (!empty($subscription) and $subscription->category_id == $category->id) ? 'selected' : '' }}>{{ $category->title }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>

                                                @error('category_id')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>

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
                                    {{--<section class="mt-30">
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
                                                                        <button type="button" data-ticket-id="{{ $ticket->id }}" data-webinar-id="{{ !empty($subscription) ? $subscription->id : '' }}" class="edit-ticket btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
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
                                    </section>--}}
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
                                    {{--<section class="mt-40">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('product.products') }}</h2>
                                            <button id="subscriptionAddNewProducts" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('update.add_new_product1') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if(!empty($subscriptionWebinars) and !$subscriptionWebinars->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.title') }}</th>
                                                                <th class="text-left">{{ trans('public.creator') }}</th>
                                                                <th>{{ trans('public.price') }}</th>
                                                                <th>{{ trans('public.publish_date') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach($subscriptionWebinars as $subscriptionWebinar)
                                                                @if(!empty($subscriptionWebinar->product->title))
                                                                    <tr>
                                                                        <th>{{ $subscriptionWebinar->product->title }}</th>
                                                                        <td class="text-left">{{ $subscriptionWebinar->product->creator->full_name }}</td>
                                                                        <td>{{  handlePrice($subscriptionWebinar->product->price) }}</td>
                                                                        <td>{{ dateTimeFormat($subscriptionWebinar->product->created_at,'j F Y | H:i') }}</td>

                                                                        <td>
                                                                            <button type="button" data-item-id="{{ $subscriptionWebinar->id }}" data-subscription-id="{{ !empty($subscription) ? $subscription->id : '' }}" class="edit-subscription-products btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
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
                                     <section class="mt-40">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('product.consultations') }}</h2>
                                            <button id="subscriptionAddNewConsultation" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('update.add_new_consultation') }}</button>
                                        </div>

                                        <div class="row mt-10">
                                            <div class="col-12">
                                                @if(!empty($subscriptionWebinars) and !$subscriptionWebinars->isEmpty())
                                                    <div class="table-responsive">
                                                        <table class="table table-striped text-center font-14">

                                                            <tr>
                                                                <th>{{ trans('public.consultation_type') }}</th>
                                                                <th class="text-left">{{ trans('public.consultant_name') }}</th>
                                                                <th>{{ trans('public.range') }}</th>
                                                                 <th>{{ trans('public.slot_time') }}</th>
                                                                <th></th>
                                                            </tr>

                                                            @foreach($subscriptionWebinars as $subscriptionWebinar)
                                                                @if(!empty($subscriptionWebinar->consultation_type))
                                                                    <tr>
                                                                        <th>{{ $subscriptionWebinar->consultation_type }}</th>
                                                                        @if($subscriptionWebinar->consultation_type == 'specific')
                                                                        <td class="text-left">{{optional($userConsultants->firstWhere('id', $subscriptionWebinar->consultant_id ))->full_name}}</td>
                                                                        @else
                                                                        <td class="text-left"></td>
                                                                        @endif
                                                                        <td>{{ $subscriptionWebinar->starting_price}} - {{ $subscriptionWebinar->ending_price }}</td>
                                                                        @if($subscriptionWebinar->slot_time == 'both')
                                                                        <td>30min ,15min</td>
                                                                        @else
                                                                        <td>{{$subscriptionWebinar->slot_time}} min</td>
                                                                         @endif
                                                                        <td>
                                                                            <button type="button" data-item-id="{{ $subscriptionWebinar->id }}" data-subscription-id="{{ !empty($subscription) ? $subscription->id : '' }}" class="edit-subscription-consultation btn-transparent text-primary mt-1" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
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
                                    </section> --}}

                                   {{-- <section class="mt-30">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>
                                            <button id="webinarAddFAQ" type="button" class="btn btn-primary btn-sm mt-3">{{ trans('public.add_faq') }}</button>
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
                                    </section>--}}
                                @endif

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
@endpush
