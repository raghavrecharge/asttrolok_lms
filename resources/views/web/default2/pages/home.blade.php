
@extends('web.default2'.'.layouts.app')
@php
    $imgUrl = config('app.img_dynamic_url');
    $jsUrl = config('app.js_css_url');
    
@endphp
@push('styles_top')
<link rel="stylesheet" href="{{ $jsUrl }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
<link rel="stylesheet" href="{{ $jsUrl }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.css">
  <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/grid_card_1.min.css">
   <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/upcoming_courses.min.css">
      <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/home.css">

<!--<link rel="canonical" href="https://www.asttrolok.com" />-->
<style>
.rounded-16 {
    border-radius: 16px !important;
}
    .rewardss{
     position: absolute;
    width: 100%;
    left: -24px !important;
    top: 8px !important;   
}
@media (min-width: 658px) {
  .homeshow {
    display: none !important;
}
}
.news-box::after{
    background-image:linear-gradient(to bottom, rgba(6, 6, 6, 0), rgba(0, 0, 0, 0.1)) !important;
}
</style>
@endpush

@section('content')

@if(!empty($heroSectionData))

@if(!empty($heroSectionData['has_lottie']) and $heroSectionData['has_lottie'] == "1")
@push('scripts_bottom')
<script src="{{ $jsUrl }}/assets2/default/vendors/lottie/lottie-player.js"></script>
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
                                <div loading="eager" fetchpriority="high" decoding="async"  class="swiper-slide slider-height" style="background-image: url('{{ getOptimizedImage($value->hero_background) }}') ;background-size: cover!important;
background-repeat: no-repeat!important;">
                                    

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
            
            <div class="col-12 col-md-7 col-lg-8 deskdesc">
                <div style="margin-left:100px;">
                    @if($key ==0)
                    <h1 class="text-secondary font-weight-bold" style="font-size:53px;">{!! nl2br( $value->title ) !!}</h1>
                    @else
                                                <h2 class="text-secondary font-weight-bold" style="font-size:53px;">{!! nl2br( $value->title ) !!}</h2>
                                                @endif
                                                <div >
                                                    {{-- @if(empty($authUser))
                                                        
                                                        <p class="slide-hint text-gray mt-20" style="font-size: 16.497px;margin-left:10px;line-height: 1.5;">{!! nl2br($heroSectionData['description']) !!} </p><br>
                                                        <a href="/register" class="btn btn-primary rounded-pill">Join Now</a>
                                                        @else
                                                        
                                                        <p class="slide-hint text-gray mt-20" style="font-size: 16.497px;margin-left:10px; line-height: 1.5;">{!! nl2br($heroSectionData['description']) !!}</p><br>
                                                        <a href="/classes?sort=newest" class="btn btn-primary rounded-pill">Explore</a>
                                                        @endif --}}
                                                        <p class="slide-hint text-gray mt-20" style="font-size: 16.497px;margin-left:10px; line-height: 1.5;">{!! $value->description !!}</p>
                                                        <br>
                                                        @if($key ==1)
                                                        <a href="{{ $value->button_url }}" class="btn btn-primary rounded-pill" style="background-color:#a96f21;border:none!important;box-shadow:inset 0 1px 0 rgb(169 111 33 / 24%), 0 1px 1px rgb(169 111 33 / 0%)!important;" >{{  $value->button_text }}</a>
                                                        @else
                                                        <a href="{{ $value->button_url }}" class="btn btn-primary rounded-pill"  >{{  $value->button_text }}</a>
                                                        @endif
                                                    </div>
                                                    
                                                    
                                                    {{-- <form action="/search" method="get" class="d-inline-flex mt-30 mt-lg-30 w-100">
                                                        <div class="form-group d-flex align-items-center m-0 slider-search p-10 bg-white w-100">
                                                            <input type="text" name="search" class="form-control border-0 mr-lg-50" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                                            <button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                                        </div>
                                                    </form> --}}
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-5 col-lg-4 mobilehome " style="height: 382px;">
                                               
                                                    @if(!empty($heroSectionData['has_lottie']) and $heroSectionData['has_lottie'] == "1")
                                                    <lottie-player src="{{ $value->hero_vector }}" background="transparent" speed="1" class="w-100" loop autoplay></lottie-player>
                                                    @else
                                                    <img loading="eager" fetchpriority="high" decoding="async"  src="{{ getOptimizedImage($value->hero_vector) }}" alt="{{ $value->title }}"  class="img-cover"style="max-height: 384px;max-width: 413px;">
                                                    @endif
                                                    
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
                    {{-- @include('web.default2.pages.includes.home_statistics') --}}
                    @include('web.default2.pages.includes.category_statics')
                    
                </div>
            </div> </section></div>
            
          

@endif

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
                @if($count_homesection==2)
                <section class="container">
                    <div class="row">
                        <div class="col-12 col-lg-12 mt-25 mt-lg-0 ">
                            @endif
                            
                            @if($count_homesection==3)
                            <section class="container">
                                <div class="row">
                                    <div class="col-12 col-lg-12 mt-25 mt-lg-0 ">
                                        @endif
                                        
                                        
                                      @if($homeSection->name == \App\Models\HomeSection::$featured_classes and !empty($featureWebinars) and !$featureWebinars->isEmpty())  
                                        <section class="home-sections Featured-section home-sections-swiper container homehide">
                                            <div class="px-20 px-md-0">
                                                <h2 class="section-title">{{ trans('home.featured_classes') }}</h2>
                                                <p class="section-hint">{{ trans('home.featured_classes_hint') }}</p>
                                            </div>
                                            
                                            <div class="feature-slider-container position-relative d-flex justify-content-center mt-10">
                                                <div class="swiper-container features-swiper-container pb-25">
                                                    <div class="swiper-wrapper py-10">
                                                        @foreach($featureWebinars as $feature)
                                                        <div class="swiper-slide ">
                                                            
                                                            <a href="{{ $feature->webinar->getUrl() }}"><span>
                                                                <div class="feature-slider d-flex h-100"  onclick="featurjquery({{ $feature->webinar->getUrl() }});" style="background-image: url('{{ $imgUrl }}{{ $feature->webinar->getImageCover() }}')">
                                <div class="mask"></div>
                                <div class="p-5 p-md-25 feature-slider-card">
                                    <div class="d-flex flex-column feature-slider-body position-relative h-100">
                                        @if($feature->webinar->bestTicket() < $feature->webinar->price)
                                        <span class="badge badge-danger mb-2 ">{{ trans('public.offer',['off' => $feature->webinar->bestTicket(true)['percent']]) }}</span>
                                        @endif
                                        <a href="{{ $feature->webinar->getUrl() }}">
                                            <h3 class="card-title mt-1">{{ $feature->webinar->title }}</h3>
                                        </a>
                                        
                                        <div class="user-inline-avatar mt-15 d-flex align-items-center">
                                            <div class="avatar bg-gray200">
                                                <img src="{{ getOptimizedImage($feature->webinar->teacher->getAvatar()) }}" class="img-cover" loading="lazy"  alt="{{ $feature->webinar->teacher->full_naem }}">
                                            </div>
                                            <a href="{{ $feature->webinar->teacher->getProfileUrl() }}" target="_blank" class="user-name font-14 ml-5">{{ $feature->webinar->teacher->full_name }}</a>
                                        </div>
                                        
                                        <p class="mt-25 feature-desc text-gray">{{ $feature->description }}</p>
                                        
                                        @include('web.default2.includes.webinar.rate',['rate' => $feature->webinar->getRate()])
                                        
                                        <div class="feature-footer mt-auto d-flex align-items-center justify-content-between">
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
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </span></a>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="swiper-pagination features-swiper-pagination"></div>
        </div>
    </section>
    @endif
    
    @if($homeSection->name == \App\Models\HomeSection::$english_classes and !empty($englishclasses) and !$englishclasses->isEmpty())
    <section class="home-sections home-sections-swiper container" style=" margin-top: 36px;">
        <div class="d-flex justify-content-between ">
            <div >
                <h2 class="section-title">{{ trans('home.english_classes') }}</h2>
                <p class="section-hint">{{ trans('home.english_classes_hint') }}</p>
            </div>
            
            <a href="/classes?english=on" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container latest-bundle-swiper ">
                <div class="swiper-wrapper py-20">
                    @foreach($englishclasses as $englishclasse)
                    <div class="swiper-slide">
                        @include('web.default2.includes.webinar.grid-card',['webinar' => $englishclasse])
                    </div>
                    @endforeach
                    
                </div>
            </div>
            
            <div class="d-flex justify-content-center">
                <div class="swiper-pagination bundle-webinars-swiper-pagination"></div>
            </div>
            <!--<a href="/classes?english=on" class="btn btn-border-white" style="float: right;">{{ trans('home.view_all') }}</a>-->
        </div>
    </section>
    
    
    @endif
    
    {{-- Upcoming Course --}}
    @if($homeSection->name == \App\Models\HomeSection::$upcoming_courses and !empty($upcomingCourses) and !$upcomingCourses->isEmpty())
    <section class="home-sections home-sections-swiper container">
        <div class="d-flex justify-content-between ">
            <div>
                <h2 class="section-title">{{ trans('update.upcoming_courses') }}</h2>
                <p class="section-hint">{{ trans('update.upcoming_courses_home_section_hint') }}</p>
            </div>
            
            <a href="/upcoming_courses?sort=newest" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container upcoming-courses-swiper px-12">
                <div class="swiper-wrapper py-20">
                    @foreach($upcomingCourses as $upcomingCourse)
                    <div class="swiper-slide">
                        @include('web.default2.includes.webinar.upcoming_course_grid_card',['upcomingCourse' => $upcomingCourse])
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
            
            <a href="/classes?sort=newest" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container latest-webinars-swiper px-12">
                <div class="swiper-wrapper py-20">
                    @foreach($latestWebinars as $latestWebinar)
                    <div class="swiper-slide">
                        @include('web.default2.includes.webinar.grid-card',['webinar' => $latestWebinar])
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
            
            <a href="/classes?sort=best_rates" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container best-rates-webinars-swiper px-12">
                <div class="swiper-wrapper py-20">
                    @foreach($bestRateWebinars as $bestRateWebinar)
                    <div class="swiper-slide">
                        @include('web.default2.includes.webinar.grid-card',['webinar' => $bestRateWebinar])
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
                                    <img src="{{ $imgUrl }}{{ $trend->getIcon() }}" width="10" class="img-cover" loading="lazy" alt="{{ $trend->category->title }}">
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
                    
                    <img src="{{ $imgUrl }}{{ $banner1->image }}" class="img-cover rounded-sm" loading="lazy" alt="{{ $banner1->title }}">
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    @if($homeSection->name == \App\Models\HomeSection::$best_sellers and !empty($bestSaleWebinars) and !$bestSaleWebinars->isEmpty())
    <section class="home-sections container">
        <div class="d-flex justify-content-between">
            <div>
                <h2 class="section-title">{{ trans('home.best_sellers') }}</h2>
                <p class="section-hint">{{ trans('home.best_sellers_hint') }}</p>
            </div>
            
            <a href="/classes?sort=bestsellers" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container best-sales-webinars-swiper px-12">
                <div class="swiper-wrapper py-20">
                    @foreach($bestSaleWebinars as $bestSaleWebinar)
                    <div class="swiper-slide">
                        @include('web.default2.includes.webinar.grid-card',['webinar' => $bestSaleWebinar])
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
            
            <a href="/classes?discount=on" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container has-discount-webinars-swiper px-12">
                <div class="swiper-wrapper py-20">
                    @foreach($hasDiscountWebinars as $hasDiscountWebinar)
                    <div class="swiper-slide">
                        @include('web.default2.includes.webinar.grid-card',['webinar' => $hasDiscountWebinar])
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
    
    @if($homeSection->name == \App\Models\HomeSection::$hindi_classes and !empty($hindiWebinars) and !$hindiWebinars->isEmpty())
    <section class="home-sections home-sections-swiper container">
        <div class="d-flex justify-content-between">
            <div >
                <h2 class="section-title">{{ trans('home.hindi_classes') }}</h2>
                <p class="section-hint">{{ trans('home.hindi_classes_hint') }}</p>
            </div>
            <a href="/classes?hindi=on" class="btn btn-border-white mobile-btn"  style="float: right;">{{ trans('home.view_all') }}</a>
            <!--<a href="/classes?hindi=on" class="btn btn-border-white">{{ trans('home.view_all') }}</a>-->
        </div>
        
        <div class="mt-10 position-relative">
            <div class="swiper-container free-webinars-swiper px-12">
                <div class="swiper-wrapper py-20">
                   @foreach($subscriptions as $subscription)
                      @if(!empty($subscription))
                        <div class="swiper-slide swiper-slide-prev " style="width: 341.333px; margin-right: 16px;">
                        <div class="webinar-card">
                            <figure>
                                <div class="image-box">
                            <!--<span class="badge badge-danger">23% Offer</span>-->
                            
                            <a href="{{ $subscription->getUrl() }}">
                                <img src="https://storage.googleapis.com/astrolok/store/1/subscription/Astrology Learning Program.jpg" class="img-cover" loading="lazy" alt="Astrology Learning Program">
                            </a>
                            
                            
                            <div class="progress">
                                <span class="progress-bar" style="width: 2.75%"></span>
                            </div>
                            
                        </div>
                        
                        <figcaption class="webinar-card-body">
                            <div class="user-inline-avatar d-flex align-items-center">
                                <div class="avatar bg-gray200">
                                    <img src="https://storage.googleapis.com/astrolok/store/1/astrologer_mobile/Alok Sir.jpg" class="img-cover" loading="lazy" alt="Mr.Alok Khandelwal">
                                </div>
                                <a href="/users/1015/astrologer-mr.alok-khandelwal" target="_blank" class="user-name ml-5 font-14">Mr.Alok Khandelwal</a>
                            </div>
                            
                            
                            <a href="{{ $subscription->getUrl() }}">
                                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">{{ $subscription->title }} </h3>
                            </a>
                            
                            <span class="d-block font-14 mt-5">in <a href="/categories/astrology/Astrology-Basic" target="_blank" class="text-decoration-underline">Astrology</a></span>
                            
                            <div class="stars-card d-flex align-items-center  mt-5">
                                
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                
                                
                                <span class="badge badge-primary ml-10 rating-course"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> 4.5</span>
                            </div>
                           
                            <div class="webinar-price-box mt-5">
                                <span class="real">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
                                <!--<span class="off ml-10">₹84,000</span>-->
                            </div>
                        </figcaption>
                    </figure>
                </div>
            </div>
                      @endif
                    @endforeach
            @foreach($hindiWebinars as $hindiWebinar)
            <div class="swiper-slide">
                @include('web.default2.includes.webinar.grid-card',['webinar' => $hindiWebinar])
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
            
            @if($homeSection->name == \App\Models\HomeSection::$free_classes and !empty($freeWebinars) and !$freeWebinars->isEmpty())
            <section class="home-sections home-sections-swiper container">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="section-title">{{ trans('home.free_classes') }}</h2>
                        <p class="section-hint">{{ trans('home.free_classes_hint') }}</p>
                    </div>
                    
                    <a href="/classes?free=on" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
                </div>
                
                <div class="mt-10 position-relative">
                    <div class="swiper-container free-webinars-swiper px-12">
                        <div class="swiper-wrapper py-20">
                            
                            @foreach($freeWebinars as $freeWebinar)
                            <div class="swiper-slide">
                                @include('web.default2.includes.webinar.grid-card',['webinar' => $freeWebinar])
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
                                @include('web.default2.products.includes.card',['product' => $newProduct])
                            </div>
                            @endforeach
                            
                        </div>
                    </div>
                  
                </div>
            </section>
            @endif
            
            @if($homeSection->name == \App\Models\HomeSection::$testimonials and !empty($testimonials) and !$testimonials->isEmpty())
            
           
        
            <!--<div class="position-relative home-sections testimonials-container" style=" background-size: contain; background-image: url('{{ $imgUrl }}/store/1/default_images/home_sections_banners/Testimonial Background.png')"style="background-image: url('{{ $imgUrl }}/store/1/default_images/home_sections_banners/Testimonial Background.png')">-->
                <section class="home-sections  home-sections-swiper container Remedies">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h2 class="section-title">Coverage of Media Presence</h2>
                            <p class="section-hint">#Asttrolok's presence in news logs</p>
                            <!--  <h2 class="section-title">{{ trans('home.organizations') }}</h2>-->
                            <!--<p class="section-hint">{{ trans('home.organizations_hint') }}</p>-->
                        </div>
                        <!--<a href="/organizations" class="btn btn-border-white">{{ trans('home.all_organizations') }}</a>-->
                        <!--<a href="/remedies?sort=newest" class="btn btn-border-white">All Remedies</a>  -->
                    </div>
                    
                    <div class="position-relative mt-20">
                        <div class="swiper-container news-swiper-container px-12">
                            <div class="swiper-wrapper py-20">
                                <div class="swiper-slide">
                                    <div class="">
                                        <div class="webinar-card">
                                            <figure>
                                                <div class="image-box news-box">
                                                    <a href="https://zeenews.india.com/lifestyle/homeandkitchen/vastu-tips-for-holi-2024-7-things-you-must-avoid-to-let-go-of-negative-energy-from-home-2733462.html" target="_blank">
                                                        <img src="{{ $jsUrl }}/assets/default/img/home/news/Zee_news.svg.png" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body">
                                                    <a href="https://zeenews.india.com/lifestyle/homeandkitchen/vastu-tips-for-holi-2024-7-things-you-must-avoid-to-let-go-of-negative-energy-from-home-2733462.html" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">Zee News</h3>
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
                                                <div class="image-box news-box">
                                                    <a href="https://news.abplive.com/astro/vedic-science-vastu-shastra-in-improving-health-and-wellness-1680193" target="_blank">
                                                        <img src="{{ $jsUrl }}/assets/default/img/home/news/abp News-min.png" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body">
                                                    <a href="https://news.abplive.com/astro/vedic-science-vastu-shastra-in-improving-health-and-wellness-1680193" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">ABP News</h3>
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
                                                <div class="image-box news-box">
                                                    <a href="https://timesofindia.indiatimes.com/astrology/vastu-feng-shui/from-enhancing-natural-light-to-creating-a-sacred-space-vastu-remedies-for-enhancing-positive-personality-traits/articleshow/109692065.cms" target="_blank">
                                                        <img src="{{ $jsUrl }}/assets/default/img/home/news/TOI-min.png" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body">
                                                    <a href="https://timesofindia.indiatimes.com/astrology/vastu-feng-shui/from-enhancing-natural-light-to-creating-a-sacred-space-vastu-remedies-for-enhancing-positive-personality-traits/articleshow/109692065.cms" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">TOI</h3>
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
                                               <div class="image-box news-box">
                                                   <a href="https://www.moneycontrol.com/news/technology/what-the-stars-foretell-lok-sabha-elections-are-boom-time-for-astrologers-12706862.html" target="_blank">
                                                       <img src="{{ $jsUrl }}/assets/default/img/home/news/Money Control-min.png" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;"alt="img">
                                                    </a>
                                                </div>
                                                <figcaption class="webinar-card-body">
                                                    <a href="https://www.moneycontrol.com/news/technology/what-the-stars-foretell-lok-sabha-elections-are-boom-time-for-astrologers-12706862.html" target="_blank">
                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">Moneycontrol</h3>
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
                                            <div class="image-box news-box">
                                                <a href="https://sugermint.com/alok-khandelwal/" target="_blank">
                                                    <img src="{{ $jsUrl }}/assets/default/img/home/news/sugar-mint.png" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;"alt="img">
                                                </a>
                                            </div>
                                            <figcaption class="webinar-card-body">
                                                <a href="https://sugermint.com/alok-khandelwal/" target="_blank">
                                                    <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">Sugarmint</h3>
                                                </a>
                                            </figcaption>
                                        </figure>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            <div class="swiper-pagination organization-swiper-pagination"></div>
                        </div>
                    </div>
                </section> 
                
                <div class="position-relative testimonials-container mt-responsive">
                    
                    <!--<div id="parallax1" class="ltr">-->
                        <!--    <div data-depth="0.2" class="gradient-box left-gradient-box"></div>-->
                    <!--</div>-->
                    
                    <section class="container home-sections home-sections-swiper">
                        <div class="text-center">
                            <h2 class="section-title">{{ trans('home.testimonials') }}</h2>
                            <p class="section-hint">{{ trans('home.testimonials_hint') }}</p>
                        </div>
                        
                       {{-- <div class="position-relative">
                            <div class="swiper-container testimonials-swiper px-12">
                                <div class="swiper-wrapper">

                                    @foreach($testimonials as $testimonial)
                                    <div class="swiper-slide">
                                        <div class="testimonials-card position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm shadow bg-white text-center">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="testimonials-user-avatar">
                                                    <img src="{{ $imgUrl }}{{ $testimonial->user_avatar }}" alt="{{ $testimonial->user_name }}" loading="lazy" class="img-cover rounded-circle">
                                                </div>
                                                <h4 class="font-16 font-weight-bold text-secondary mt-30">{{ $testimonial->user_name }}</h4>
                                                <span class="d-block font-14 text-gray">{{ $testimonial->user_bio }}</span>
                                                @include('web.default2.includes.webinar.rate',['rate' => $testimonial->rate, 'dontShowRate' => true])
                                            </div>
                                            <div  class="mt-25 testimonials-p scrollbar-width-thin">
                                                
                                                <p class=" text-gray font-14 pr-5">{!! nl2br($testimonial->comment) !!}</p>
                                            </div>
                                            <div class="bottom-gradient"></div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                            </div>
                            
                            <div class="d-flex justify-content-center">
                                <div class="swiper-pagination testimonials-swiper-pagination"></div>
                        </div>
                    </div> --}}
                    
                    <script defer async src='https://cdn.trustindex.io/loader.js?c02e64f55c4f71585a56331cf63'></script>
                </section>
           
        </div>
        @endif
        
        @if($homeSection->name == \App\Models\HomeSection::$testimonials and !empty($testimonials) and !$testimonials->isEmpty())
        <!--<div class="position-relative home-sections testimonials-container">-->
            <!--<script src="https://static.elfsight.com/platform/platform.js" data-use-service-core defer></script>-->
            <!--<div class="elfsight-app-dbf2d1b5-428f-4b95-8b4d-2a56fd433bf1" data-elfsight-app-lazy></div>-->
            
        <!--</div>-->
        @endif
        
        @if($homeSection->name == \App\Models\HomeSection::$testimonials and !empty($testimonials) and !$testimonials->isEmpty())
        
        <section class="home-sections container ">
            <style>
                    .blog-grid-card{
                        min-height:340px !important;
                    }
                    .blog-grid-image  {
                      height: 127.66px !important;
                    }
                     @media (min-width: 900px) {
  
                 .video-section {
                     width: 269px;
                    height: -webkit-fill-available;
                 }
                     }
                 @media (width: 600px) {
  
                 .video-section {
                     min-width: 269px;
                    height: -webkit-fill-available;
                 }
                 }
                </style>
                <div class="d-flex justify-content-between">
                   
                </div>
                
                <div class="row mt-35">
                    
                    @foreach ($testimonial_video as $post)
                    <div class="col-12 col-md-3 mt-20 mt-lg-0 video-section-mobilehide">
                        <div class="video-section" >
                            {{-- {!!$post!!} --}}
                            <a href="#" class="" data-toggle="modal" data-target="#import" onclick="changeImageAndShowPopup('{{$post['link']}}')"><img src="{{ $imgUrl }}{{$post['image']}}" class="  shadow-sm rounded-lg" style="height: auto;width: 100%;" loading="lazy" alt="{{$post['title']}}"></a>
                            
                        </div>
                    </div>
                    @endforeach
                    
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
                                            <img src="{{ $imgUrl }}{{ $subscribe->icon }}" loading="lazy" class="img-cover" alt="">
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
            
            @if($homeSection->name == \App\Models\HomeSection::$find_instructors and !empty($findInstructorSection))
            
            
            <section class="home-sections home-sections-swiper container find-instructor-section position-relative homehide">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class="font-36 font-weight-bold text-dark">{{ $findInstructorSection['title'] ?? '' }}</h2>
                            <p class="font-16 font-weight-normal text-gray mt-10">{{ $findInstructorSection['description'] ?? '' }}</p>
                            
                            <div class="mt-35 d-flex align-items-center">
                                @if(!empty($findInstructorSection['button1']))
                                <a href="{{ $findInstructorSection['button1']['link'] }}" class="btn btn-primary">{{ $findInstructorSection['button1']['title'] }}</a>
                                @endif
                                
                                @if(!empty($findInstructorSection['button2']))
                                <a href="{{ $findInstructorSection['button2']['link'] }}" class="btn btn-outline-primary ml-15">{{ $findInstructorSection['button2']['title'] }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <div class="position-relative ">
                            <img src="{{ $imgUrl }}{{ $findInstructorSection['image'] }}" class="find-instructor-section-hero" loading="lazy" alt="{{ $findInstructorSection['title'] }}">
                            <img src="{{ $jsUrl }}/assets/default/img/home/circle-4.png" class="find-instructor-section-circle" loading="lazy" alt="circle">
                            <img src="{{ $jsUrl }}/assets/default/img/home/dot.png" class="find-instructor-section-dots" loading="lazy" alt="dots">
                            
                            <div class="example-instructor-card bg-white rounded-sm shadow-lg  p-5 p-md-15 d-flex align-items-center">
                                <div class="example-instructor-card-avatar">
                                    <img src="{{ $jsUrl }}/assets/default/img/home/toutor_finder.svg" class="img-cover rounded-circle"  loading="lazy" alt="user name">
                                </div>
                                
                                <div class="flex-grow-1 ml-15">
                                    <span class="font-14 font-weight-bold text-secondary d-block">{{ trans('update.looking_for_an_instructor') }}</span>
                                    <span class="text-gray font-12 font-weight-500">{{ trans('update.find_the_best_instructor_now') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            
            @endif
            
            @if($homeSection->name == \App\Models\HomeSection::$reward_program and !empty($rewardProgramSection))
            <section class="home-sections home-sections-swiper container reward-program-section position-relative">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="position-relative reward-program-section-hero-card">
                            <img src="{{ $imgUrl }}{{ $rewardProgramSection['image'] }}" class="rewardss" loading="lazy" alt="{{ $rewardProgramSection['title'] }}">
                            
                            <div class="example-reward-card bg-white rounded-sm shadow-lg p-5 p-md-5 d-flex align-items-center">
                                <div class="example-reward-card-medal">
                                    <img src="{{ $jsUrl }}/assets/default/img/rewards/medal.png" class="img-cover rounded-circle" loading="lazy" alt="medal">
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
            
            @if($homeSection->name == \App\Models\HomeSection::$become_instructor and !empty($becomeInstructorSection))
            
            <section class="home-sections home-sections-swiper container find-instructor-section position-relative homehide">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-6">
                        <div class="">
                            <h2 class="font-36 font-weight-bold text-dark">{{ $becomeInstructorSection['title'] ?? '' }}</h2>
                            <p class="font-16 font-weight-normal text-gray mt-10">{{ $becomeInstructorSection['description'] ?? '' }}</p>
                            
                            <div class="mt-35 d-flex align-items-center">
                                @if(!empty($becomeInstructorSection['button1']))
                                <a href="{{ empty($authUser) ? '/login' : (($authUser->isUser()) ? $becomeInstructorSection['button1']['link'] : '/panel/financial/registration-packages') }}" class="btn btn-primary">{{ $becomeInstructorSection['button1']['title'] }}</a>
                                @endif
                                
                                @if(!empty($becomeInstructorSection['button2']))
                                <a href="{{ empty($authUser) ? '/login' : (($authUser->isUser()) ? $becomeInstructorSection['button2']['link'] : '/panel/financial/registration-packages') }}" class="btn btn-outline-primary ml-15">{{ $becomeInstructorSection['button2']['title'] }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 col-lg-6 mt-20 mt-lg-0">
                        <div class="position-relative ">
                            <img src="{{ $imgUrl }}{{ $becomeInstructorSection['image'] }}" class="find-instructor-section-hero" loading="lazy" alt="{{ $becomeInstructorSection['title'] }}">
                            <img src="{{ $jsUrl }}/assets/default/img/home/circle-4.png" class="find-instructor-section-circle" loading="lazy" alt="circle">
                            <img src="{{ $jsUrl }}/assets/default/img/home/dot.png" class="find-instructor-section-dots" loading="lazy" alt="dots">
                            
                            <div class="example-instructor-card bg-white rounded-sm shadow-lg border p-5 p-md-15 d-flex align-items-center">
                                <div class="example-instructor-card-avatar">
                                    <img src="{{ $jsUrl }}/assets/default/img/home/become_instructor.svg" class="img-cover rounded-circle" loading="lazy" alt="user name">
                                </div>
                                
                                <div class="flex-grow-1 ml-15">
                                    <span class="font-14 font-weight-bold text-secondary d-block">{{ trans('update.become_an_instructor') }}</span>
                                    <span class="text-gray font-12 font-weight-500">{{ trans('update.become_instructor_tagline') }}</span>
                                </div>
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
                            <img src="{{ $imgUrl }}{{ $forumSection['image'] }}" class="find-instructor-section-hero" loading="lazy" alt="{{ $forumSection['title'] }}">
                            <img src="{{ $jsUrl }}/assets/default/img/home/circle-4.png" class="find-instructor-section-circle" loading="lazy" alt="circle">
                            <img src="{{ $jsUrl }}/assets/default/img/home/dot.png" class="find-instructor-section-dots" loading="lazy" alt="dots">
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
                <div class="container home-video-container d-flex flex-column align-items-center justify-content-center position-relative" style="background-image: url('{{ $imgUrl }}{{ $boxVideoOrImage['background'] ?? '' }}')">
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
            
            
            @if($homeSection->name == \App\Models\HomeSection::$consultant and !empty($consultant) and !$consultant->isEmpty())
            <section class="home-sections container ">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <h2 class="section-title">Astrologers</h2>
                        <p class="section-hint">#Discover your path with top astrologers – <b>Book an astrology consultation</b></p>
                    </div>
                    
                    <a href="/consult-with-astrologers" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
                </div>
                
                <!--<div class="position-relative mt-20 ltr">-->
                    <!--    <div class="owl-carousel customers-testimonials instructors-swiper-container ">-->
                        
                        <div class="deckteacher teacher-swiper-container position-relative d-flex justify-content-center mt-0">
                            <div class="swiper-container teacher-swiper-container pb-25">
                                <div class="swiper-wrapper py-0">
                                    @foreach($consultant as $instructor)
                                    <div class="swiper-slide">
                                        <div class="rounded-lg shadow-sm mt-15  p-5 course-teacher-card d-flex align-items-center flex-column">
                                            <div class="teacher-avatar mt-15">
                                                <img src="{{ $imgUrl }}{{ $instructor->getAvatar(108) }}" class="img-cover" loading="lazy" alt="{{ $instructor->full_name }}">
                                                <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check text-white">
                                                        <polyline points="20 6 9 17 4 12"></polyline>
                                                    </svg>
                                                </span>
                                            </div>
                                            <h3 class="mt-10 font-16 font-weight-bold text-secondary text-center swiper-container1-title">{{ $instructor->full_name }}</h3>
                                            <span class="mt-5 font-14 font-weight-500 text-gray text-center swiper-container1-desc">{{ $instructor->bio }}</span>
                                            <div class="stars-card d-flex align-items-center mt-10">
                                                {{--@include('web.default2.includes.webinar.rate',['rate' => $instructor->rates()]) --}}
                                                @include('web.default2.includes.webinar.rate',['rate' => $instructor->rating])
                                            </div>
                                            <div class="stars-card d-none align-items-center  mt-15">
                                                @php
                                                $i = 5;
                                                @endphp
                                                @while(--$i >= 5 - $instructor->rates())
                                                <i data-feather="star" width="13" height="13" class="active"></i>
                                                @endwhile
                                                @while($i-- >= 0)
                                                <i data-feather="star" width="13" height="13" class=""></i>
                                                @endwhile
                                            </div>
                                            <div class="my-15   align-items-center text-center  w-100">
                                                <a href="{{ $instructor->getProfileUrl() }}?tab=appointments" class="btn btn-sm btn-primary swiper-container1-btn">Book a Consultation</a>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach  
                                  
                                </div>
                            </div>
                            <div class="swiper-pagination teacher-swiper-pagination ast-pagination"></div>
                        </div>
                        
                    </section>
                    
                    @endif
                    
                    
                    
    @if($homeSection->name == \App\Models\HomeSection::$instructors and !empty($instructors) and !$instructors->isEmpty())
                   
        
        <section class="home-sections container homehide">
            <div class="d-flex justify-content-between">
                <div class="">
                    <h2 class="section-title">{{ trans('home.instructors') }}</h2>
                    <p class="section-hint">{{ trans('home.instructors_hint') }}</p>
                </div>
                
                <a href="/consult-with-astrologers?tab=content" class="btn btn-border-white mobile-btn">{{ trans('home.view_all') }}</a>
            </div>
            
            <div class="position-relative mt-20 ltr">
                <div class="owl-carousel customers-testimonials instructors-swiper-container">
                    
                    @foreach($instructors as $instructor)
                    <div class="item">
                        <div class="shadow-effect">
                            <div class="instructors-card d-flex flex-column align-items-center justify-content-center">
                                <div class="instructors-card-avatar">
                                    <img src="{{ $imgUrl }}{{ $instructor->getAvatar(108) }}" alt="{{ $instructor->full_name }}" loading="lazy" class="rounded-circle img-cover">
                                </div>
                                <div class="instructors-card-info mt-10 text-center">
                                    <a href="{{ $instructor->getProfileUrl() }}" target="_blank">
                                        <h3 class="font-16 font-weight-bold text-dark-blue">{{ $instructor->full_name }}</h3>
                                    </a>
                                    
                                    <p class="font-14 text-gray mt-5">{{ $instructor->bio }}</p>
                                    <div class="stars-card d-flex align-items-center justify-content-center mt-10">
                                        @php
                                        $i = 5;
                                        @endphp
                                        @while(--$i >= 5 - $instructor->rating)
                                        <!--<i data-feather="star" width="20" height="20" class="active"></i>-->
                                        @endwhile
                                        @while($i-- >= 0)
                                        <!--<i data-feather="star" width="20" height="20" class=""></i>-->
                                        @endwhile
                                    </div>
                                    
                                    <!--@if(!empty($instructor->hasMeeting()))-->
                                    <!--    <a href="{{ $instructor->getProfileUrl() }}?tab=appointments" class="btn btn-primary btn-sm rounded-pill mt-15">{{ trans('home.reserve_a_live_class') }}</a>-->
                                    <!--@else-->
                                    <!--    <a href="{{ $instructor->getProfileUrl() }}" class="btn btn-primary btn-sm rounded-pill mt-15">{{ trans('public.profile') }}</a>-->
                                    <!--@endif-->
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
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
                        <img src="{{ $imgUrl }}{{ $banner2->image }}" class="img-cover rounded-sm" loading="lazy" alt="{{ $banner2->title }}">
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
     
        @if($homeSection->name == \App\Models\HomeSection::$organizations and !empty($organizations) and !$organizations->isEmpty())
        <section class="d-none home-sections home-sections-swiper container homehide">
            <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="section-title">Organizations</h2>
                        <p class="section-hint">Greatest organizations are here to help you</p>
                        <h2 class="section-title">{{ trans('home.organizations') }}</h2>
                        <p class="section-hint">{{ trans('home.organizations_hint') }}</p>
                    </div>
                    <a href="/organizations" class="btn btn-border-white">{{ trans('home.all_organizations') }}</a>
                    <!--<a href="/organizations" class="btn btn-border-white">All Remedies</a>-->
                </div>
                
                <div class="position-relative mt-20">
                    <div class=" px-12">
                        <div class="swiper-wrapper py-20">
                            
                            @foreach($organizations as $organization)
                            <div class="swiper-slide">
                                <div class="home-organizations-card d-flex flex-column align-items-center justify-content-center">
                                    <div class="home-organizations-avatar">
                                        <img src="{{ $imgUrl }}{{ $organization->getAvatar(120) }}" class="img-cover rounded-circle" loading="lazy" alt="{{ $organization->full_name }}">
                                    </div>
                                    <a href="{{ $organization->getProfileUrl() }}" class="mt-25 d-flex flex-column align-items-center justify-content-center">
                                        <h3 class="home-organizations-title">{{ $organization->full_name }}</h3>
                                        <p class="home-organizations-desc mt-10">{{ $organization->bio }}</p>
                                        <span class="home-organizations-badge badge mt-15">{{ $organization->webinars_count }} {{ trans('panel.classes') }}</span>
                                        <span class="home-organizations-badge badge mt-15">0 Courses</span>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                            
                        </div>
                    </div>
                    
                </div>
            </section>
            @endif
             <?php //print_r($remedies);?>
            @if($homeSection->name == \App\Models\HomeSection::$remedies and !empty($remedies) and !$remedies->isEmpty())
            
            
           <div class="container">
            <div class="upcoming-courses-section position-relative " style="background-image: url(https://asttrolok.in/asttroloknew/store/landing_builder/landing_13/389/upcoming_bg_lMn.svg)">

                <div class="d-flex-center flex-column text-center">
                                            <!--<div class="d-flex-center py-8 px-16 rounded-8 border-primary bg-primary-20 font-12 text-primary">Upcoming</div>-->
                    
                                            <h2 class="mt-8 font-32 text-dark">Remedies</h2>
                    
                                            <p class="mt-16 font-16 text-gray-500">Greatest Remedies are here to help you</p>
                                    </div>

                <div class="row">
                     @foreach($remedies as $remedy)
                    
                    <div class="col-12 col-md-6 col-lg-3 mt-28">
     <div class="upcoming-course-card position-relative"><a href="{{ $remedy->getUrl() }}"  class="text-decoration-none d-block">
    <div class="upcoming-course-card__image position-relative rounded-16 bg-gray-100">
        <img src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}" alt="" class="img-cover rounded-16">
    </div></a>
    <div class="upcoming-course-card__body position-relative px-12 pb-12">
        <div class="upcoming-course-card__content d-flex flex-column bg-white py-12 rounded-16">
            <div class="d-flex align-items-center mb-16 px-12">
               

                <div class="d-flex flex-column ml-4 mt-5">
                        <a href="{{ $remedy->getUrl() }}"  class="font-14 font-weight-bold text-dark" >{{ clean($remedy->title,'title') }}</a>

                                    </div> </div>
            </div>
        </div>
    </div>
    </div>
    
     @endforeach
    
                </div>

                                    <div class="d-flex-center flex-column mt-40">
                        <a href="/remedies?sort=newest"  class="btn-flip-effect btn btn-primary btn-xlg gap-8 text-white" data-text="View More">
                                                            <svg width="24px" height="24px" class="icons" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
  <path d="M16.75 3.56V2c0-.41-.34-.75-.75-.75s-.75.34-.75.75v1.5h-6.5V2c0-.41-.34-.75-.75-.75s-.75.34-.75.75v1.56c-2.7.25-4.01 1.86-4.21 4.25-.02.29.22.53.5.53h16.92c.29 0 .53-.25.5-.53-.2-2.39-1.51-4-4.21-4.25z"></path>
  <path d="M20 9.84c.55 0 1 .45 1 1V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5v-6.16c0-.55.45-1 1-1h16z" opacity=".4"></path>
  <path d="M8.5 15c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.09-.1-.16-.21-.21-.33A.995.995 0 017.5 14c0-.13.03-.26.08-.38s.12-.23.21-.33c.1-.09.21-.16.33-.21a1 1 0 01.76 0c.12.05.23.12.33.21.09.1.16.21.21.33.05.12.08.25.08.38s-.03.26-.08.38-.12.23-.21.33c-.1.09-.21.16-.33.21-.12.05-.25.08-.38.08zM12 15c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.18-.19-.29-.45-.29-.71 0-.26.11-.52.29-.71.1-.09.21-.16.33-.21.24-.11.52-.11.76 0 .12.05.23.12.33.21.18.19.29.45.29.71 0 .26-.11.52-.29.71-.1.09-.21.16-.33.21-.12.05-.25.08-.38.08zM8.5 18.5c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.18-.19-.29-.45-.29-.71 0-.26.11-.52.29-.71.1-.09.21-.16.33-.21a1 1 0 01.76 0c.12.05.23.12.33.21.18.19.29.45.29.71 0 .26-.11.52-.29.71-.1.09-.21.16-.33.21-.12.05-.25.08-.38.08z"></path>
</svg>                            
                            <span class="btn-flip-effect__text">View More</span>
                        </a>
                    </div>
                
            </div>
        </div>
     {{--
            <section class="home-sections  home-sections-swiper container Remedies">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="section-title">Remedies</h2>
                        <p class="section-hint">Greatest Remedies are here to help you</p>
                      
                    </div>
                    <a href="/remedies?sort=newest" class="btn btn-border-white">All Remedies</a>  
                </div>
                
                <div class="position-relative mt-20">
                    <div class="swiper-container organization-swiper-container px-12">
                        <div class="swiper-wrapper py-20">
                          
                            @foreach($remedies as $remedy)
                            <div class="swiper-slide">
                                <!--<div class="home-organizations-card d-flex flex-column align-items-center justify-content-center">-->
                                    <div class="">
                                        @include('web.default2.includes.remedy.grid-card',['remedy' => $remedy])
                                    </div>
                                    
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-center">
                        <div class="swiper-pagination organization-swiper-pagination"></div>
                    </div>
                </div>
            </section>
                --}}
            @endif
            
            
            @if($homeSection->name == \App\Models\HomeSection::$blog and !empty($blog) and !$blog->isEmpty())
            <section class="home-sections container homehide">
                <style>
                    .blog-grid-card{
                            min-height:340px !important;
                        }
                        .blog-grid-image  {
                        height: 127.66px !important;
                        }
                    </style>
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="section-title">{{ trans('home.blog') }}</h2>
                        <p class="section-hint">{{ trans('home.blog_hint') }}</p>
                    </div>
                    
                    <a href="/blog" class="btn btn-border-white">{{ trans('home.all_blog') }}</a>
                </div>
                
                <div class="row mt-35">
                    
                    @foreach($blog as $post)
                    <div class="col-12 col-md-3 col-lg-3 mt-20 mt-lg-0">
                        @include('web.default2.blog.grid-list',['post' =>$post])
                        </div>
                        @endforeach
                        
                    </div>
                    
                </section>
                @endif
                
                
                @if($count_homesection==2)
            </div>
            <div class="col-12 col-lg-4 mt-25 mt-lg-0 homehide">
                <div class="d-flex justify-content-between mt-15">
                    <div>
                    </div>
                    
                </div>
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
                                        <img src="{{ $imgUrl }}{{ $instructor->getAvatar(108) }}" class="img-cover" loading="lazy" alt="{{ $instructor->full_name }}">
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
                {{-- <img src="/store/1/maxresdefault.jpg" class="w-100  shadow-sm rounded-lg" loading="lazy" alt="Reserve a meeting - Course page"> --}}
                <iframe width="-webkit-fill-available" id="videoiframe" height="300" allow="autoplay"  title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    @include('web.default2.course.home_popup')
    @endsection
    
    @push('scripts_bottom')
    <script>
        //      setTimeout(function() {
            //     $('#home_popup').modal();
            // }, 5000);
        </script>
    <script defer src="{{ $jsUrl }}/assets2/default/vendors/swiper/swiper-bundle.min.js" ></script>
    <script defer  src="{{ $jsUrl }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script defer src="{{ $jsUrl }}/assets2/default/vendors/parallax/parallax.min.js"></script>
    <script defer src="{{ $jsUrl }}/assets2/default/js/parts/home.min.js"></script>
    <script>
        function featurjquery(urls){
            window.location.href = urls;
        }
        </script>
    <script>
        
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
