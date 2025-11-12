@extends(getTemplate().'.layouts.app')

@push('styles_top')
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-home.css">
<link rel="canonical" href="https://www.asttrolok.com" />
<style>
    .rewardss{
     position: absolute;
     width: 100%;
     left: -24px !important;
     top: 8px !important;   
    }
    
    .news{
        border-radius: 0px 0px 10px 10px;
        background-color: #32ba7c;
        text-align: center;
    }
    .news-text{
        line-height: 1 !important;
    }
    
</style>
@endpush


@section('content')

@if(!empty($heroSectionData))

@if(!empty($heroSectionData['has_lottie']) and $heroSectionData['has_lottie'] == "1")
@push('scripts_bottom')
<script  src="{{ config('app.js_css_url') }}/assets/default/vendors/lottie/lottie-player.js"></script>
@endpush
@endif
<div>
    <!--<section class="container">-->
        <section class="">
            <div class="row">
                <div class="col-12 col-lg-12  mt-lg-0 ">
                    
                    <div class="feature-slider-container position-relative d-flex justify-content-center ">
                        <div class="swiper-container slider-home-banner-section2">
                            <div class="swiper-wrapper ">
                                @foreach ($HomeSlider as $key=> $value)
                                <div loading="eager" fetchpriority="high" decoding="async" class="mobile-home-slider swiper-slide slider-height" style="background-image: url('{{ config('app.img_dynamic_url') }}{{ $value->hero_background }}') ; background-size: cover!important; background-repeat: no-repeat!important;">
                                    
                                    <section class="slider-container  {{ ($heroSection == "2") ? 'slider-hero-section2' : '' }}" @if(empty($heroSectionData['is_video_background']))  @endif>
                                        
                                        @if($heroSection == "1")
                                        @if(!empty($heroSectionData['is_video_background']))
                                        <video playsinline autoplay muted loop id="homeHeroVideoBackground" class="img-cover">
                                            <source src="{{ $value->hero_background }}" type="video/mp4">
                                        </video>
                                        @endif
                                        
                                        <div class="mask"></div>
                                        @endif
                                        
                                        
                                        <div class="container user-select-none">
                                            
                                            @if($heroSection == "2")
                                            <div class="row slider-content align-items-center hero-section2 flex-column-reverse flex-md-row">
                                                
                                                
                                        <div class="col-12 col-md-5 col-lg-4 mobilehome  pr-0" >
                                            <div class="col-6 col-md-5 col-lg-4 px-0 mb-10">
                                                <h2 class="main-heading-home  font-weight-bold  mobiledesc" >{!! $value->title !!}</h2>
                                                <p class="main-text-home slide-hint text-gray mt-10 mb-10 mobiledesc" >{!! $value->description !!}{{--<br>
                                                    {{ $banner_disription1[$key] }} --}}</p>
                                                    
                                                    <a href="{{ $value->button_url }}" class="btn btn-primary rounded-pill " style="background-color:{{  $value->button_color }};border:none!important;box-shadow:inset 0 1px 0 rgb(169 111 33 / 24%), 0 1px 1px rgb(169 111 33 / 0%)!important; font-size:13px !important; padding: 0px 22px;" >{{  $value->button_text }}</a>
                                                    
                                                </div>
                                                <div class="col-6 col-md-5 col-lg-4 px-0" >
                                                    @if(!empty($heroSectionData['has_lottie']) and $heroSectionData['has_lottie'] == "1")
                                                    <lottie-player src="{{ $value->hero_vector }}" background="transparent" speed="1" class="w-100" loop autoplay></lottie-player>
                                                    @else
                                                    <img loading="eager" fetchpriority="high" decoding="async"  fetchpriority="high" loading="lazy" decoding="async"  src="{{ config('app.img_dynamic_url') }}{{ $value->hero_vector }}" alt="{{ $value->title }}" class="main-home-img img-cover" style="border-bottom-right-radius: 20px;">
                                                    @endif
                                                </div>
                                            </div>
                                            
                                        </div>
                                        @else
                                        <div class="text-center slider-content">
                                            <h1>{{ $value->title }}</h1>
                                            <div class="row h-100 align-items-center justify-content-center text-center">
                                                <div class="col-12 col-md-9 col-lg-7">
                                                    <p class="mt-30 slide-hint">{!! $value->description !!}</p>
                                                    
                                                    {{-- <form action="/search" method="get" class="d-inline-flex mt-30 mt-lg-50 w-100">
                                                        <div class="form-group d-flex align-items-center m-0 slider-search p-10 bg-white w-100">
                                                            <input type="text" name="search" class="form-control border-0 mr-lg-50" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                                            <button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                                        </div>
                                                    </form> --}}
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    
                                    
                                </section>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="swiper-pagination home-banner-swiper-pagination"></div>
                </div>
                {{-- @include('web.default.pages.includes.home_statistics') --}}
                @include('web.default.pages.includes.category_statics')
                
                @include('web.default.pages.includes.mobile_cat')
                
                
                
                <section class="home-sections container hide-mobile">
                    <div class="d-flex justify-content-between">
                        <div class="">
                            <h2 class="section-title">Astrologers</h2>
                        </div>
                        
                    </div>
                    
                    {{-- <div class="scroll-bar"> --}}
                        <div class="mx-0">
                            @php
                            $id_i=0;
                            @endphp
                            @foreach($consultant as $instructor)
                            @php
                            if($id_i==4)
                            {
                                break;
                            }
                            $canReserve = false;
                            if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->meetingTimes) ) {
                                $canReserve = true;
                            }
                            if($canReserve){
                                $id_i=$id_i+1;
                            }
                            @endphp
                            @if($canReserve)
                            <div class=" col-12 col-md-6 col-lg-4 " style="padding-right: 0px !important; padding-left: 0px !important; ">
                                @include('web.default.pages.instructor_card1',['instructor' => $instructor ])
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <center> <a href="/consult-with-astrologers" class="mt-20 btn btn-border-white mobile-btn" style="margin-left: 0px !important; font-size:16px !important; border: 2px solid #7e7e7e !important;">{{ trans('home.view_all') }}
                        </a></center>
                    </section>
                    
                    <div class="rounded-lg sidebar-ads1 m-10 mt-30" style="width:95%;">
                        <a href="{{$sidebanner['home1']['link']}}">
                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{$sidebanner['home1']['image']}}" class="w-100  shadow-sm rounded-lg" alt="Reserve a meeting - Course page">
                            
                        </a>
                    </div>
                   
                    
                    <section class="home-sections home-sections-swiper container hide-mobile" style=" margin-top: 36px;">
                        <div class="d-flex justify-content-between ">
                            <div >
                                <h2 class="section-title">Courses</h2>
                            </div>
                            <div class="mob-tab1">
                                <ul class="btn nav nav-tabs rounded-sm  d-flex align-items-center justify-content-between" id="tabs-tab" role="tablist">
                                    <li class="nav-item" style="margin: auto;">
                                        <a class="eng-1 position-relative font-14 text-white1 {{ (request()->get('tab','') == 'content') ? 'active' : '' }}" id="content-tab" data-toggle="tab"
                                        href="#content" role="tab" aria-controls="content"
                                        aria-selected="false">ENG</a>
                                    </li>
                            <li class="nav-item" style="margin: auto;">
                                <a  class="hindi-1 position-relative font-14 text-white1 {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'active' : '' }}" id="reviews-tab" data-toggle="tab"
                                href="#reviews" role="tab" aria-controls="reviews"
                                aria-selected="false">हिंदी</a>
                            </li>
                            
                            
                        </ul>
                    </div>
                </div>
                
                <div class="mt-10 position-relative">
                    <div class=" ">
                        <div class="row  pt-20">
                            <div class="tab-content " id="nav-tabContent">
                                <div class="tab-pane fade {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab" >
                                    <div class="row mx-0">
                                        @foreach($englishclasses as $englishclasse)
                                        <div class="col-md-6 col-lg-4 mt-20 loadid mobilegrid1">
                                            @include('web.default.includes.webinar.grid-card',['webinar' => $englishclasse])
                                        </div>
                                        @endforeach
                                        
                                        
                                    </div></div>
                                    <div class=" tab-pane fade  {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'show active' : '' }}" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                        <div class="row mx-0">
                                             @foreach($subscriptions as $subscription)
                                                 @if(!empty($subscription))
                                                       <div class="col-md-6 col-lg-4 mt-20 loadid mobilegrid1 ">
                                                <div class="webinar-card grid-card">
                                                    <figure>
                                                        
                                                        <div class="image-box str">
                                                            <div class="star-rating">
                                                                
                                                                <div class="radius-20  stars-card d-flex align-items-center shadow-sm " style="padding-left: 1px; padding-right:1px; padding-top:1px; ">
                                                                    
                                                                    
                                                                    
                                                                    
                                                                    
                                                                    <span class="radius-20 badge badge-primary1 rate1">
                                                                        
                                                                        <svg width="14" height="14" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <g clip-path="url(#clip0_263_410)">
                                                                                <path d="M14.6461 5.51542L9.78752 5.06481L7.85756 0.583302C7.72244 0.269504 7.27751 0.269504 7.1424 0.583302L5.21247 5.06484L0.353914 5.51542C0.0137192 5.54697 -0.123771 5.97011 0.132899 6.19558L3.79869 9.41594L2.7259 14.176C2.65078 14.5093 3.01073 14.7708 3.30448 14.5963L7.49999 12.1051L11.6955 14.5963C11.9893 14.7708 12.3492 14.5093 12.2741 14.176L11.2013 9.41594L14.8671 6.19558C15.1238 5.97011 14.9863 5.54697 14.6461 5.51542Z" fill="#FFDC64"></path>
                                                                                <path d="M7.85756 0.583302C7.72244 0.269504 7.27751 0.269504 7.1424 0.583302L5.21247 5.06484L0.353914 5.51542C0.0137192 5.54697 -0.123771 5.97011 0.132899 6.19558L3.79869 9.41594L2.7259 14.176C2.65078 14.5093 3.01073 14.7708 3.30448 14.5963L4.2409 14.0403C4.37051 8.70532 6.84931 4.94838 8.81185 2.7992L7.85756 0.583302Z" fill="#FFC850"></path>
                                                                            </g>
                                                                            <defs>
                                                                                <clipPath id="clip0_263_410">
                                                                                    <rect width="14" height="14" fill="white"></rect>
                                                                                </clipPath>
                                                                            </defs>
                                                                        </svg>
                                                                        4.1</span>
                                                                    </div>
                                                                </div>
                                                                <!--<span class="badge badge-danger hide">33% Offer</span>-->
                                                                
                                                                
                                                                <a href="{{ $subscription->getUrl() }}">
           
                                                                    <img loading="lazy"  loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/store/1/subscription/Astrology Learning Program.jpg" class="img-cover" alt="Astrology Learning Program">
                                                                </a>
                                                                <div class="d-flex justify-content-between mt-auto">
                                                                    <div class=" h-25 mx-15"></div>
                 <form action="/cart/store" method="post">
                     <input type="hidden" name="_token" value="Wo3QmQXQuMfiaofFmnPloNtVXMkwRXWHM5T8R8uO">
                     <input type="hidden" name="item_id" value="2068">
                     <input type="hidden" name="item_name" value="webinar_id">
                     
                     
                     <div class="dropdown dropdown-card" style="
    position: absolute;
    right: 0px;
    bottom: -12px;
    /* border: 0.1px solid black; */
    box-shadow: 0 5px 12px 0 rgba(0, 0, 0, 0.1);
    ">
    <button type="button" disabled="" class="btn btn-transparent dropdown-toggle1 js-course-add-to-cart-btn" id="" data-toggle="dropdown1" aria-haspopup="true" aria-expanded="false" style="height: 30px ;">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart ml-5 mr-10 shoping-cart"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        
    </button>
    
    <div class="dropdown-menu" aria-labelledby="navbarShopingCart">
        <div class="d-md-none border-bottom mb-20 pb-10 text-right">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close-dropdown"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </div>
        <div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" style="height: auto; overflow: hidden;"><div class="simplebar-content" style="padding: 0px;">
                <div class="d-flex align-items-center text-center py-50">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart mr-10 "><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="">Your cart is empty</span>
                </div>
            </div></div></div></div><div class="simplebar-placeholder" style="width: 0px; height: 0px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: hidden;"><div class="simplebar-scrollbar" style="height: 0px; display: none;"></div></div></div>
        </div>
    </div>
</div>
</form>
</div>

<div class="progress">
    <span class="progress-bar" style="width: 8.4%"></span>
</div>

</div>

<figcaption class="webinar-card-body">
    
    <a href="{{ $subscription->getUrl() }}">
        
        <h3 class="mt-5 webinar-title webinartitle font-weight-bold font-16 text-dark-blue">{{ $subscription->title }}</h3>
    </a>
    <div class="user-inline-avatar d-flex align-items-center">
        <div class="avatar bg-gray200">
            <img loading="lazy"  loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/webp/store/1/astrologer_mobile/Alok Sir.webp" class="img-cover" alt="Mr.Alok Khandelwal">
        </div>
        <a href="/users/1015/astrologer-mr.alok-khandelwal" target="_blank" class="user-name ml-5 font-14">Mr.Alok Khandelwal</a>
    </div>
    <hr>
        <div class="webinar-price-box mt-5">
            <span class="real">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
            
            <a href="https://www.asttrolok.com/subscriptions/asttrolok-pathshala"> 
                
                <button type="submit" class="btn btn-primary rounded-pill buynow homehide1">BUY NOW</button>
            </a>
            
        </div>
    </figcaption>
</figure>
</div>
</div>
                                                  @endif
                                             @endforeach    
@foreach($hindiWebinars as $hindiWebinar)
<div class="col-md-6 col-lg-4 mt-20 loadid mobilegrid1">
    @include('web.default.includes.webinar.grid-card',['webinar' => $hindiWebinar])
</div>
@endforeach


</div></div></div>


</div>
</div>


</div>
<center> <a href="/classes" class="mt-20 btn btn-border-white mobile-btn" style="margin-left: 0px !important; font-size:16px !important; border: 2px solid #7e7e7e !important;">{{ trans('home.view_all') }}
</a></center>
</section>
</div>
</div> </section></div>

<div class="mobileteacher">
    @foreach($homeSections as $homeSection)
    @if($homeSection->name == \App\Models\HomeSection::$featured_classes and !empty($featureWebinars) and !$featureWebinars->isEmpty())
    <section class="home-sections Featured-section home-sections-swiper container">
        <div class="px-20 px-md-0">
            <h2 class="section-title">{{ trans('home.featured_classes') }}</h2>
            <p class="section-hint">{{ trans('home.featured_classes_hint') }}</p>
        </div>
        
        <div class="feature-slider-container position-relative d-flex justify-content-center mt-10">
            <div class="swiper-container features-swiper-container">
                <div class="swiper-wrapper py-10">
                    @foreach($featureWebinars as $feature)
                    <div class="swiper-slide">
                        
                        <a href="{{ $feature->webinar->getUrl() }}"><span>
                            <div class="feature-slider d-flex h-100" onclick="featurjquery({{ $feature->webinar->getUrl() }});" style="background-image: url('{{ config('app.img_dynamic_url') }}{{ $feature->webinar->getImageCover() }}')">
                                <div class="mask1"></div>
                                <div class="p-5 p-md-25 feature-slider-card">
                                    <div class="feature-price-box">
                                        @if(!empty($feature->webinar->price ) and $feature->webinar->price > 0)
                                        @if($feature->webinar->bestTicket() < $feature->webinar->price)
                                        <span class="real">{{ handlePrice($feature->webinar->bestTicket(), true, true, false, null, true) }}</span>
                                        @else
                                                        {{ handlePrice($feature->webinar->price, true, true, false, null, true) }}
                                                    @endif
                                                    @else
                                                    {{ trans('public.free') }}
                                                    @endif
                                                    
                                                    
                                                </div>
                                                <div class="d-flex flex-column feature-slider-body position-relative h-100">
                                                    @if($feature->webinar->bestTicket() < $feature->webinar->price)
                                                    <span class="badge badge-danger mb-2 ">{{ trans('public.offer',['off' => $feature->webinar->bestTicket(true)['percent']]) }}</span>
                                                    @endif
                                                    <a href="{{ $feature->webinar->getUrl() }}" class=" mt-auto">
                                                        <h3 class="card-title  ">{{ $feature->webinar->title }}</h3>
                                                    </a>
                                                    
                                                    <div class="user-inline-avatar mt-5 d-flex align-items-center">
                                                        <div class="avatar bg-gray200">
                                                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $feature->webinar->teacher->getAvatar() }}" class="img-cover" alt="{{ $feature->webinar->teacher->full_naem }}">
                                                        </div>
                                                        <a href="{{ $feature->webinar->teacher->getProfileUrl() }}" target="_blank" class="user-name font-14 ml-5">{{ $feature->webinar->teacher->full_name }}</a>
                                                    </div>
                                                    
                                                    <p class="mt-25 feature-desc text-gray">{{ $feature->description }}</p>
                                                    
                                                    @include('web.default.includes.webinar.rate',['rate' => $feature->webinar->getRate()])
                                                    
                                                    <div class="feature-footer d-flex align-items-center justify-content-between">
                                                        <div class="d-flex justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                    <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                                                    <span class="duration ml-5 text-dark-blue font-14">{{ convertMinutesToHourAndMinute($feature->webinar->duration) }} {{ trans('home.hours') }}</span>
                                                </div>
                                                
                                                <div class="vertical-line mx-10"></div>
                                                
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                                                    <span class="date-published ml-5 text-dark-blue font-14">{{ dateTimeFormat(!empty($feature->webinar->start_date) ? $feature->webinar->start_date : $feature->webinar->created_at,'j M Y') }}</span>
                                                </div>
                                            </div>
                                            
                                            
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </span> </a>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="swiper-pagination features-swiper-pagination"></div>
        </div>
    </section>
    @endif
    @endforeach
</div>

@endif


{{-- Statistics --}}
{{-- @foreach($homeSections as $homeSection)
    
    
    @endforeach --}}
    <?php
$count_homesection=0;
?>
    @foreach($homeSections as $homeSection)
    <?php $count_homesection++; ?>
    @if($count_homesection==1)
    <section class="container" >
        <div class="row">
            <div class="col-12 col-lg-12 mt-25 mt-lg-0 mobilefirst">
                
                @endif
                
                                    {{-- Upcoming Course --}}
                                    @if($homeSection->name == \App\Models\HomeSection::$upcoming_courses and !empty($upcomingCourses) and !$upcomingCourses->isEmpty())
                                    <section class="home-sections home-sections-swiper container">
                                        <div class="d-flex justify-content-between ">
                                            <div>
                                                <h2 class="section-title">{{ trans('update.upcoming_courses') }}</h2>
                                                <p class="section-hint">{{ trans('update.upcoming_courses_home_section_hint') }}</p>
                                            </div>
                                            
                                            <a href="/upcoming_courses?sort=newest" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                                        </div>
                                        
                                        <div class="mt-10 position-relative">
                                            <div class="swiper-container upcoming-courses-swiper px-12">
                                                <div class="swiper-wrapper py-20">
                                                    @foreach($upcomingCourses as $upcomingCourse)
                                                    <div class="swiper-slide">
                                                        @include('web.default.includes.webinar.upcoming_course_grid_card',['upcomingCourse' => $upcomingCourse])
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-center">
                                                <div class="swiper-pagination upcoming-courses-swiper-pagination"></div>
                                            </div>
                                        </div>
                                    </section>
                                    @endif
                                    
                                    @if($homeSection->name == \App\Models\HomeSection::$latest_classes and !empty($latestWebinars) and !$latestWebinars->isEmpty())
                                    <section class="home-sections home-sections-swiper container">
                                        <div class="d-flex justify-content-between ">
                                            <div>
                                                <h2 class="section-title">{{ trans('home.latest_classes') }}</h2>
                                                <p class="section-hint">{{ trans('home.latest_webinars_hint') }}</p>
                                            </div>
                                            
                                            <a href="/classes?sort=newest" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                                        </div>
                                        
                                        <div class="mt-10 position-relative">
                                            <div class="swiper-container latest-webinars-swiper px-12">
                                                <div class="swiper-wrapper py-20">
                                                    @foreach($latestWebinars as $latestWebinar)
                                                    <div class="swiper-slide">
                                                        @include('web.default.includes.webinar.grid-card',['webinar' => $latestWebinar])
                                                    </div>
                                                    @endforeach
                                                    
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-center">
                                                <div class="swiper-pagination latest-webinars-swiper-pagination"></div>
                                            </div>
                                        </div>
                                    </section>
                                    @endif
                                    
                                    @if($homeSection->name == \App\Models\HomeSection::$best_rates and !empty($bestRateWebinars) and !$bestRateWebinars->isEmpty())
                                    <section class="home-sections home-sections-swiper container">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h2 class="section-title">{{ trans('home.best_rates') }}</h2>
                                                <p class="section-hint">{{ trans('home.best_rates_hint') }}</p>
                                            </div>
                                            
                                            <a href="/classes?sort=best_rates" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                                        </div>
                                        
                                        <div class="mt-10 position-relative">
                                            <div class="swiper-container best-rates-webinars-swiper px-12">
                                                <div class="swiper-wrapper py-20">
                                                    @foreach($bestRateWebinars as $bestRateWebinar)
                                                    <div class="swiper-slide">
                                                        @include('web.default.includes.webinar.grid-card',['webinar' => $bestRateWebinar])
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-center">
                                                <div class="swiper-pagination best-rates-webinars-swiper-pagination"></div>
                                            </div>
                                        </div>
                                    </section>
                                    @endif
                                    
                                    @if($homeSection->name == \App\Models\HomeSection::$trend_categories and !empty($trendCategories) and !$trendCategories->isEmpty())
                                    <section class="home-sections home-sections-swiper container">
                                        <h2 class="section-title">{{ trans('home.trending_categories') }}</h2>
                                        <p class="section-hint">{{ trans('home.trending_categories_hint') }}</p>
                                        
                                        
                                        <div class="swiper-container trend-categories-swiper px-12 mt-40">
                                            <div class="swiper-wrapper py-20">
                                                @foreach($trendCategories as $trend)
                                                <div class="swiper-slide">
                                                    <a href="{{ $trend->category->getUrl() }}">
                                                        <div class="trending-card d-flex flex-column align-items-center w-100">
                                                            <div class="trending-image d-flex align-items-center justify-content-center w-100" style="background-color: {{ $trend->color }}">
                                                                <div class="icon mb-3">
                                                                    <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $trend->getIcon() }}" width="10" class="img-cover" alt="{{ $trend->category->title }}">
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="item-count px-10 px-lg-20 py-5 py-lg-10">{{ $trend->category->webinars_count }} {{ trans('product.course') }}</div>
                                                            
                                                            <h3>{{ $trend->category->title }}</h3>
                                                        </div>
                                                    </a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-center">
                                            <div class="swiper-pagination trend-categories-swiper-pagination"></div>
                                        </div>
                                    </section>
                                    @endif
                                    
                                    {{-- Ads Bannaer --}}
                                    @if($homeSection->name == \App\Models\HomeSection::$full_advertising_banner and !empty($advertisingBanners1) and count($advertisingBanners1))
                                    <div class="home-sections container">
                                        <div class="row">
                                            @foreach($advertisingBanners1 as $banner1)
                                            <div class="col-{{ $banner1->size }}">
                                                <a href="{{ $banner1->link }}">
                                                    
                                                    <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $banner1->image }}" class="img-cover rounded-sm" alt="{{ $banner1->title }}">
                                                </a>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                    {{-- ./ Ads Bannaer --}}
                                    
                                    @if($homeSection->name == \App\Models\HomeSection::$best_sellers and !empty($bestSaleWebinars) and !$bestSaleWebinars->isEmpty())
                                    <section class="home-sections container">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h2 class="section-title">{{ trans('home.best_sellers') }}</h2>
                                                <p class="section-hint">{{ trans('home.best_sellers_hint') }}</p>
                                            </div>
                                            
                                            <a href="/classes?sort=bestsellers" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                                        </div>
                                        
                                        <div class="mt-10 position-relative">
                                            <div class="swiper-container best-sales-webinars-swiper px-12">
                                                <div class="swiper-wrapper py-20">
                                                    @foreach($bestSaleWebinars as $bestSaleWebinar)
                                                                    <div class="swiper-slide">
                                                                        @include('web.default.includes.webinar.grid-card',['webinar' => $bestSaleWebinar])
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="d-flex justify-content-center">
                                                                <div class="swiper-pagination best-sales-webinars-swiper-pagination"></div>
                                                            </div>
                                                        </div>
                                                    </section>
                                                    
                
                
                @endif
                
                @if($homeSection->name == \App\Models\HomeSection::$discount_classes and !empty($hasDiscountWebinars) and !$hasDiscountWebinars->isEmpty())
                <section class="home-sections container">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h2 class="section-title">{{ trans('home.discount_classes') }}</h2>
                            <p class="section-hint">{{ trans('home.discount_classes_hint') }}</p>
                        </div>
                        
                        <a href="/classes?discount=on" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                    </div>
                    
                    <div class="mt-10 position-relative">
                        <div class="swiper-container has-discount-webinars-swiper px-12">
                            <div class="swiper-wrapper py-20">
                                @foreach($hasDiscountWebinars as $hasDiscountWebinar)
                                <div class="swiper-slide">
                                    @include('web.default.includes.webinar.grid-card',['webinar' => $hasDiscountWebinar])
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            <div class="swiper-pagination has-discount-webinars-swiper-pagination"></div>
                        </div>
                    </div>
                </section>
                @endif
                
                
                
                @if($homeSection->name == \App\Models\HomeSection::$free_classes and !empty($freeWebinars) and !$freeWebinars->isEmpty())
                <section class="home-sections home-sections-swiper container">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h2 class="section-title">{{ trans('home.free_classes') }}</h2>
                            <p class="section-hint">{{ trans('home.free_classes_hint') }}</p>
                        </div>
                        
                        <a href="/classes?free=on" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                    </div>
                    
                    <div class="mt-10 position-relative">
                        <div class="swiper-container free-webinars-swiper px-12">
                            <div class="swiper-wrapper py-20">
                                
                                @foreach($freeWebinars as $freeWebinar)
                                <div class="swiper-slide">
                                    @include('web.default.includes.webinar.grid-card',['webinar' => $freeWebinar])
                                </div>
                                @endforeach

                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        <div class="swiper-pagination free-webinars-swiper-pagination"></div>
                    </div>
                </div>
            </section>
            @endif
            
            @if($homeSection->name == \App\Models\HomeSection::$store_products and !empty($newProducts) and !$newProducts->isEmpty())
            <section class="home-sections home-sections-swiper container">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="section-title">{{ trans('update.store_products') }}</h2>
                        <p class="section-hint">{{ trans('update.store_products_hint') }}</p>
                    </div>
                    
                    <a href="/products" class="btn btn-border-white">{{ trans('update.all_products') }}</a>
                </div>
                
                <div class="mt-10 position-relative">
                    <div class="swiper-container new-products-swiper px-12">
                        <div class="swiper-wrapper py-20">
                            
                            @foreach($newProducts as $newProduct)
                            <div class="swiper-slide">
                                @include('web.default.products.includes.card',['product' => $newProduct])
                            </div>
                            @endforeach
                            
                        </div>
                    </div>
                    
              
                </div>
            </section>
            @endif
            
           
            
         
            
           
            
            
            @if($homeSection->name == \App\Models\HomeSection::$subscribes and !empty($subscribes) and !$subscribes->isEmpty())
            <div class="home-sections position-relative subscribes-container pe-none user-select-none">
                <div id="parallax4" class="ltr d-none d-md-block">
                    <div data-depth="0.2" class="gradient-box left-gradient-box"></div>
                </div>
                
                <section class="container home-sections home-sections-swiper">
                    <div class="text-center">
                        <h2 class="section-title">{{ trans('home.subscribe_now') }}</h2>
                        <p class="section-hint">{{ trans('home.subscribe_now_hint') }}</p>
                    </div>
                    
                    <div class="position-relative mt-30">
                        <div class="swiper-container subscribes-swiper px-12">
                            <div class="swiper-wrapper py-20">
                                
                                @foreach($subscribes as $subscribe)
                                @php
                                $subscribeSpecialOffer = $subscribe->activeSpecialOffer();
                                @endphp
                                
                                <div class="swiper-slide">
                                    <div class="subscribe-plan position-relative bg-white d-flex flex-column align-items-center rounded-sm shadow pt-50 pb-20 px-20">
                                        @if($subscribe->is_popular)
                                        <span class="badge badge-primary badge-popular px-15 py-5">{{ trans('panel.popular') }}</span>
                                        @elseif(!empty($subscribeSpecialOffer))
                                        <span class="badge badge-danger badge-popular px-15 py-5">{{ trans('update.percent_off', ['percent' => $subscribeSpecialOffer->percent]) }}</span>
                                        @endif
                                        
                                        <div class="plan-icon">
                                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $subscribe->icon }}" class="img-cover" alt="">
                                        </div>
                                        
                                        <h3 class="mt-20 font-30 text-secondary">{{ $subscribe->title }}</h3>
                                        <p class="font-weight-500 text-gray mt-10">{{ $subscribe->description }}</p>
                                        
                                        <div class="d-flex align-items-start mt-30">
                                            @if(!empty($subscribe->price) and $subscribe->price > 0)
                                            @if(!empty($subscribeSpecialOffer))
                                            <div class="d-flex align-items-end line-height-1">
                                                <span class="font-36 text-primary">{{ handlePrice($subscribe->getPrice()) }}</span>
                                                <span class="font-14 text-gray ml-5 text-decoration-line-through">{{ handlePrice($subscribe->price) }}</span>
                                            </div>
                                            @else
                                            <span class="font-36 text-primary line-height-1">{{ handlePrice($subscribe->price) }}</span>
                                            @endif
                                            @else
                                            <span class="font-36 text-primary line-height-1">{{ trans('public.free') }}</span>
                                            @endif
                                        </div>
                                        
                                        <ul class="mt-20 plan-feature">
                                            <li class="mt-10">{{ $subscribe->days }} {{ trans('financial.days_of_subscription') }}</li>
                                            <li class="mt-10">
                                                @if($subscribe->infinite_use)
                                                {{ trans('update.unlimited') }}
                                                @else
                                                {{ $subscribe->usable_count }}
                                                @endif
                                                <span class="ml-5">{{ trans('update.subscribes') }}</span>
                                            </li>
                                        </ul>
                                        
                                        @if(auth()->check())
                                        <form action="/panel/financial/pay-subscribes" method="post" class="w-100">
                                                    {{ csrf_field() }}
                                                    <input name="amount" value="{{ $subscribe->price }}" type="hidden">
                                                    <input name="id" value="{{ $subscribe->id }}" type="hidden">
                                                    
                                                    <div class="d-flex align-items-center mt-50 w-100">
                                                        <button type="submit" class="btn btn-primary {{ !empty($subscribe->has_installment) ? '' : 'btn-block' }}">{{ trans('update.purchase') }}</button>

                                                        @if(!empty($subscribe->has_installment))
                                                            <a href="/panel/financial/subscribes/{{ $subscribe->id }}/installments" class="btn btn-outline-primary flex-grow-1 ml-10">{{ trans('update.installments') }}</a>
                                                            @endif
                                                        </div>
                                                    </form>
                                                    @else
                                                    <a href="/login" class="btn btn-primary btn-block mt-50">{{ trans('update.purchase') }}</a>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        
                                    </div>
                                  
                                </div>
                            </section>
                         
                        </div>
                        @endif
                        
                        
            
        @if($homeSection->name == \App\Models\HomeSection::$reward_program and !empty($rewardProgramSection))
            <section class="home-sections home-sections-swiper container reward-program-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="position-relative reward-program-section-hero-card">
                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $rewardProgramSection['image'] }}" class="rewardss" alt="{{ $rewardProgramSection['title'] }}">
                            
                            <div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-5 d-flex align-items-center">
                                <div class="example-reward-card-medal">
                                    <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" alt="medal">
                                </div>
                                
                                <div class="flex-grow-1 ml-15">
                                    <span class="font-14 font-weight-bold text-secondary d-block">{{ trans('update.you_got_50_points') }}</span>
                                    <span class="text-gray font-12 font-weight-500">{{ trans('update.for_completing_the_course') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <div class="">
                            <h2 class="font-36 font-weight-bold text-dark">{{ $rewardProgramSection['title'] ?? '' }}</h2>
                            <p class="font-16 font-weight-normal text-gray mt-10">{{ $rewardProgramSection['description'] ?? '' }}</p>
                            
                            <div class="mt-35 d-flex align-items-center">
                                @if(!empty($rewardProgramSection['button1']))
                                <a href="{{ $rewardProgramSection['button1']['link'] }}" class="btn btn-primary">{{ $rewardProgramSection['button1']['title'] }}</a>
                                @endif
                                
                                @if(!empty($rewardProgramSection['button2']))
                                <a href="{{ $rewardProgramSection['button2']['link'] }}" class="btn btn-outline-primary ml-15">{{ $rewardProgramSection['button2']['title'] }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
            
            
            
            @if($homeSection->name == \App\Models\HomeSection::$forum_section and !empty($forumSection))
            <section class="home-sections home-sections-swiper container find-instructor-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <div class="position-relative ">
                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $forumSection['image'] }}" class="find-instructor-section-hero" alt="{{ $forumSection['title'] }}">
                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/circle-4.png" class="find-instructor-section-circle" alt="circle">
                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/dot.png" class="find-instructor-section-dots" alt="dots">
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class="font-36 font-weight-bold text-dark">{{ $forumSection['title'] ?? '' }}</h2>
                            <p class="font-16 font-weight-normal text-gray mt-10">{{ $forumSection['description'] ?? '' }}</p>
                            
                            <div class="mt-35 d-flex align-items-center">
                                @if(!empty($forumSection['button1']))
                                <a href="{{ $forumSection['button1']['link'] }}" class="btn btn-primary">{{ $forumSection['button1']['title'] }}</a>
                                @endif
                                
                                @if(!empty($forumSection['button2']))
                                <a href="{{ $forumSection['button2']['link'] }}" class="btn btn-outline-primary ml-15">{{ $forumSection['button2']['title'] }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif
            
            @if($homeSection->name == \App\Models\HomeSection::$video_or_image_section and !empty($boxVideoOrImage))
            <section class="home-sections home-sections-swiper position-relative">
                <div class="home-video-mask"></div>
                <div class="container home-video-container d-flex flex-column align-items-center justify-content-center position-relative" style="background-image: url('{{ config('app.img_dynamic_url') }}{{ $boxVideoOrImage['background'] ?? '' }}')">
                    <a href="{{ $boxVideoOrImage['link'] ?? '' }}" class="home-video-play-button d-flex align-items-center justify-content-center position-relative">
                        <i data-feather="play" width="36" height="36" class=""></i>
                    </a>
                    
                    <div class="mt-50 pt-10 text-center">
                        <h2 class="home-video-title">{{ $boxVideoOrImage['title'] ?? '' }}</h2>
                        <p class="home-video-hint mt-10">{{ $boxVideoOrImage['description'] ?? '' }}</p>
                    </div>
                </div>
            </section>
            @endif
            
            
            
        
        
        
        
        {{-- Ads Bannaer --}}
        @if($homeSection->name == \App\Models\HomeSection::$half_advertising_banner and !empty($advertisingBanners2) and count($advertisingBanners2))
        <div class="home-sections container">
            <div class="row">
                @foreach($advertisingBanners2 as $banner2)
                <div class="col-{{ $banner2->size }}">
                    <a href="{{ $banner2->link }}">
                        <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $banner2->image }}" class="img-cover rounded-sm" alt="{{ $banner2->title }}">
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- ./ Ads Bannaer --}}
        
        
            
            
            
            @if($homeSection->name == \App\Models\HomeSection::$remedies and !empty($remedies) and !$remedies->isEmpty())
           
            <section class="home-sections  hide-mobile">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <h2 class="section-title">Remedies</h2>
                    </div>
                    
                    <a href="/remedies?sort=newest" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}<img loading="lazy"  loading="lazy" decoding="async" width="20px" class="ml-5" src="/assets/default/mobile/right-arrow.png" alt="Right Arrow Icon - Asttrolok"></a>
                </div>
                
                {{-- <div class="scroll-bar1"> --}}
                    <div>
                        @foreach($remedies as $remedy)
                        
                        <div class="col-12 col-md-6 col-lg-4 px-0 loadid">
                            @include('web.default.includes.remedy.list-card1',['remedy' => $remedy]  )
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
                
                
                @if($homeSection->name == \App\Models\HomeSection::$blog and !empty($blog) and !$blog->isEmpty())
                <section class="home-sections  home-sections-swiper ">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h2 class="section-title">Coverage of Media Presence</h2>
                           
                        </div>
                       
                    </div>
                    
                    <div class="position-relative mt-20">
                        <div class="swiper-container news-swiper-container px-12">
                            <div class="swiper-wrapper py-20">
                                <div class="swiper-slide">
                                    <div class="">
                                        <div class="webinar-card">
                                            <figure>
                                                <div class="image-box" style="    height: 95px !important;">
                                                    <a href="https://zeenews.india.com/lifestyle/homeandkitchen/vastu-tips-for-holi-2024-7-things-you-must-avoid-to-let-go-of-negative-energy-from-home-2733462.html" target="_blank">
                                                        <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/news/Zee_news.svg.png" class="img-cover" style="object-fit: contain; padding: 5px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body news">
                                                    <a href="https://zeenews.india.com/lifestyle/homeandkitchen/vastu-tips-for-holi-2024-7-things-you-must-avoid-to-let-go-of-negative-energy-from-home-2733462.html" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-white news-text">Zee News</h3>
                                                    </a>
                                                </figcaption>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            <div class="swiper-slide">
                                        <div class="">
                                            <div class="webinar-card">
                                                <figure>
                                                    <div class="image-box" style="    height: 95px !important;">
                                                        <a href="https://news.abplive.com/astro/vedic-science-vastu-shastra-in-improving-health-and-wellness-1680193" target="_blank">
                                                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/news/abp News-min.png" class="img-cover" style="object-fit: contain; padding: 5px;"alt="img">
                                                        </a>
                                                    </div>
                                                    <figcaption class="webinar-card-body news">
                                                        <a href="https://news.abplive.com/astro/vedic-science-vastu-shastra-in-improving-health-and-wellness-1680193" target="_blank">
                                                            <h3 class="mt-5 font-weight-bold font-16 text-white news-text">ABP News</h3>
                                                        </a>
                                                    </figcaption>
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="">
                                            <div class="webinar-card">
                                                <figure>
                                                    <div class="image-box" style="    height: 95px !important;">
                                                        <a href="https://timesofindia.indiatimes.com/astrology/vastu-feng-shui/from-enhancing-natural-light-to-creating-a-sacred-space-vastu-remedies-for-enhancing-positive-personality-traits/articleshow/109692065.cms" target="_blank">
                                                            <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/news/TOI-min.png" class="img-cover" style="object-fit: contain; padding: 5px;"alt="img">
                                                        </a>
                                                    </div>
                                                    <figcaption class="webinar-card-body news">
                                                        <a href="https://timesofindia.indiatimes.com/astrology/vastu-feng-shui/from-enhancing-natural-light-to-creating-a-sacred-space-vastu-remedies-for-enhancing-positive-personality-traits/articleshow/109692065.cms" target="_blank">
                                                            <h3 class="mt-5 font-weight-bold font-16 text-white news-text">TOI</h3>
                                                        </a>
                                                    </figcaption>
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="">
                                       <div class="webinar-card">
                                            <figure>
                                                <div class="image-box" style="    height: 95px !important;">
                                                    <a href="https://www.moneycontrol.com/news/technology/what-the-stars-foretell-lok-sabha-elections-are-boom-time-for-astrologers-12706862.html" target="_blank">
                                                        <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/news/Money Control-min.png" class="img-cover" style="object-fit: contain; padding: 5px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body news">
                                                    <a href="https://www.moneycontrol.com/news/technology/what-the-stars-foretell-lok-sabha-elections-are-boom-time-for-astrologers-12706862.html" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-white news-text">Moneycontrol</h3>
                                                    </a>
                                                </figcaption>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                                <div class="swiper-slide">
                                    <div class="">
                                        <div class="webinar-card">
                                            <figure>
                                                <div class="image-box" style="    height: 95px !important;">
                                                    <a href="https://sugermint.com/alok-khandelwal/" target="_blank">
                                                        <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.js_css_url') }}/assets/default/img/home/news/sugar-mint.png" class="img-cover" style="object-fit: contain; padding: 5px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body news">
                                                    <a href="https://sugermint.com/alok-khandelwal/" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-white news-text">Sugarmint</h3>
                                                    </a>
                                                </figcaption>
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            
                                
                            </div>
                        </div>
                        
                      
                    </div>
                </section> 
                
                
            <section class="home-sections  hide-mobile">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <h2 class="section-title">{{ trans('home.blog') }}</h2>
                    </div>
                    
                </div>
                
                {{-- <div class="scroll-bar1"> --}}
                    <div>
                        @foreach($blog as $post)
                        
                        <div class="col-12 col-md-6 col-lg-4 px-0 loadid">
                            @include('web.default.blog.list-card1',['post' => $post]  )
                        </div>
                        @endforeach
                    </div>
                    <center> <a href="/blog" class="mt-20 btn btn-border-white mobile-btn" style="margin-left: 0px !important; font-size:16px !important; border: 2px solid #7e7e7e !important;">{{ trans('home.view_all') }}
                    </a></center>
                </section>
                
                @endif
               
                @if($count_homesection==2)
            </div>
             
        </div> 
    </section>
            @endif
            @if($count_homesection==3)
            
            
        </div>
        <div class="col-12 col-lg-4 mt-25 mt-lg-0 ">
           
            
            <div class="deckteacher teacher-swiper-container position-relative d-flex justify-content-center mt-0">
                <div class="swiper-container teacher-swiper-container pb-25">
                    <div class="swiper-wrapper py-0">
                        {{--    @foreach($consultant as $instructor)
                            <div class="swiper-slide">
                                <div class="rounded-lg shadow-sm mt-15  p-5 course-teacher-card d-flex align-items-center flex-column">
                                    <div class="teacher-avatar mt-15">
                                        <img loading="lazy"  loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(108) }}" class="img-cover" alt="{{ $instructor->full_name }}">
                                        <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check text-white">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-10 font-16 font-weight-bold text-secondary text-center swiper-container1-title">{{ $instructor->full_name }}</h3>
                                    <span class="mt-5 font-14 font-weight-500 text-gray text-center swiper-container1-desc">{{ $instructor->bio }}</span>
                                    <div class="stars-card d-none align-items-center  mt-15">
                                        @php
                                        $i = 5;
                                        @endphp
                                        @while(--$i >= 5 - $instructor->rating)
                                        <i data-feather="star" width="13" height="13" class="active"></i>
                                        @endwhile
                                        @while($i-- >= 0)
                                        <i data-feather="star" width="13" height="13" class=""></i>
                                        @endwhile
                                    </div>
                                    <div class="my-15   align-items-center text-center  w-100">
                                        <a href="{{ $instructor->getProfileUrl() }}" class="btn btn-sm btn-primary swiper-container1-btn">Book a Consultantion</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach  --}}
                            
                            
                            
                            
                        </div>
                    </div>
                </div>
                
                
            </div>
        </section>
        @endif
        @endforeach
        <div class="modal fade" id="import" tabindex="-1" aria-labelledby="import" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered">
                
            <div class="modal-content py-20" id="videolink">
                {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25" style="float: right;"></i>
                </button> --}}
                {{-- <img loading="lazy"  loading="lazy" decoding="async" src="/store/1/maxresdefault.jpg" class="w-100  shadow-sm rounded-lg" alt="Reserve a meeting - Course page"> --}}
                <iframe width="-webkit-fill-available" id="videoiframe" height="300" allow="autoplay"  title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    
 {{--   @include('web.default2.course.home_popup')
    @include('web.default2.course.pop_up')  --}}
    @endsection
    
    @push('scripts_bottom')
  
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/parallax/parallax.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/home.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>
    
    <script >
        document.addEventListener("DOMContentLoaded", () => {
          const bgDivs = document.querySelectorAll("[data-bg]");
          const options = { rootMargin: "0px 0px 200px 0px" };
        
          const lazyLoad = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                const div = entry.target;
                div.style.backgroundImage = `url('${div.dataset.bg}')`;
                div.style.backgroundSize = "cover";
                div.style.backgroundRepeat = "no-repeat";
                observer.unobserve(div);
              }
            });
          }, options);
        
          bgDivs.forEach(div => lazyLoad.observe(div));
        });
        </script>
    <script >
    
    
    
    
        function featurjquery(urls){
            window.location.href = urls;
        }
        </script>
    <script >
        
        function changeImageAndShowPopup(data) {
            console.log('data',data);
            var imageElement = document.getElementById('videoiframe');
            imageElement.src = data; 
            document.getElementById('popup').style.display = 'block';
        }
        function closePopup() {
            // Hide the popup
            document.getElementById('popup').style.display = 'none';
        }
        </script>
@endpush