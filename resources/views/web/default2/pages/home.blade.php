@extends('web.default2' . '.layouts.app')
@php

    $jsUrl = config('app.js_css_url');

@endphp
@push('styles_top')
    <link rel="canonical" href="https://www.asttrolok.com" />
<style>
    .best-selling-courses-section {

    margin-right: 0px;
    margin-left: 0px;
}
.blog-section__post-card-footer,
.bg-dark-20 {
    background: transparent !important;
}

.blog-section__post-card-footer span,
.blog-section__post-card-footer h3 {
    color: #fff !important;
}
.img-cover {

    border-radius: 24px;
}
.small-thumb {
    max-width: 350px;
    height: auto;
    margin-left:40px;
}
.webinar-card {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
    transform: none !important;
    transition: none !important;
    border-radius: 0 !important;
}
.swiper-pagination.swiper-pagination-clickable.swiper-pagination-bullets.swiper-pagination-horizontal {
    display: none;
}
.webinar-card *,
.webinar-card figure,
.webinar-card .image-box,
.webinar-card .news-box,
.webinar-card figcaption {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
    outline: none !important;
    transform: none !important;
}
.webinar-card h3 {
    text-align: center !important;
    margin-top: 15px;
}

.webinar-card .image-box::after {
    content: none !important;
    display: none !important;
    background: none !important;
}
    </style>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app.css">
    <style>
        .blog-section__post-card.one-large-col {
    height: 325px;
}
.blog-section__post-card.four-small-col {
    height: 155px;
}
.news-box img {
    width: 100%;
    height: 100%;
    object-fit: cover !important;
    padding: 0 !important;
}
    </style>
@endpush

@section('content')

<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

<div class="js-get-view-data-by-timeout-change container mt-24" data-container-id="listsContainer">
    <div class="row">

        <div class="col-12 col-lg-4 mt-28">
            <div class="position-relative courses-lists-filters">
                <div id="leftFiltersAccordion" class="position-relative bg-white py-16 rounded-24 z-index-2">
                    <div class="accordion pb-16 px-16 border-bottom-gray-100">

                        <div class="swiper leftSwiper rounded-24 overflow-hidden">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <a href="/course/predictive-astrology-course" target="_blank">
                                        <img src="{{ config('app.img_dynamic_url') }}/store/1/banner/Post-1-min.webp"
                                            class="img-cover rounded-24" alt="Ad 1">
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                     <a href="/course/learn-jaimini-astrology" target="_blank">
                                    <img src="{{ config('app.img_dynamic_url') }}/store/1/banner/Post-2-min.webp"
                                        class="img-cover rounded-24" alt="Ad 2">
                                     </a>
                                </div>

                            </div>
                            <div class="swiper-pagination"></div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 mt-28">
            <div id="listsContainer" class="" data-body=".js-lists-body" data-view-data-path="/classes">
                <div class="js-lists-body row">

                    <div class="col-12 col-md-6 col-lg-12 mt-24">
                        <div class="swiper rightSwiper rounded-24 overflow-hidden">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <a href="/subscriptions/asttrolok-pathshala" target="_blank">
                                    <img src="{{ config('app.img_dynamic_url') }}/store/1/Home/slider/Banner-1.webp"
                                        class="img-cover w-100 rounded-24" alt="Banner 1">
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="/consult-with-astrologers" target="_blank">
                                    <img src="{{ config('app.img_dynamic_url') }}/store/1/Home/slider/Banner-2.webp"
                                        class="img-cover w-100 rounded-24" alt="Banner 2">
                                    </a>
                                </div>
                                <div class="swiper-slide">
                                    <a href="/classes" target="_blank">
                                    <img src="{{ config('app.img_dynamic_url') }}/store/1/Home/slider/banner-3.webp"
                                        class="img-cover w-100 rounded-24" alt="Banner 3">
                                    </a>
                                </div>
                            </div>
                            <div class="swiper-pagination"></div>

                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mt-24">
                        <a href="/classes" class="text-decoration-none d-block">
                            <div class="course-grid-card-1 position-relative">
                                <div class="course-grid-card-1__mask"></div>
                                <div class="position-relative z-index-2">
                                    <div class="course-grid-card-1__image bg-gray-200" style="height: auto;">
                                        <img src="https://asttrolok.in/asttroloknew/assets/default/images/ast/01.png"
                                            class="img-cover" alt="Python for Data Science">
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mt-24">
                        <a href="/consult-with-astrologers" class="text-decoration-none d-block">
                            <div class="course-grid-card-1 position-relative">
                                <div class="course-grid-card-1__mask"></div>
                                <div class="position-relative z-index-2">
                                    <div class="course-grid-card-1__image bg-gray-200" style="height: auto;">
                                        <img src="https://asttrolok.in/asttroloknew/assets/default/images/ast/02.png"
                                            class="img-cover" alt="Full Stack JavaScript Development">
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mt-24">
                        <a href="https://asttroveda.asttrolok.com/asttrolok/personalizedkundali" class="text-decoration-none d-block"   target="_blank">
                            <div class="course-grid-card-1 position-relative">
                                <div class="course-grid-card-1__mask"></div>
                                <div class="position-relative z-index-2">
                                    <div class="course-grid-card-1__image bg-gray-200" style="height: auto;">
                                        <img src="https://asttrolok.in/asttroloknew/assets/default/images/ast/03.png"
                                            class="img-cover" alt="Accessibility in UI/UX Design">
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<script>
    // Left Side Slider
    const leftSwiper = new Swiper(".leftSwiper", {
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".leftSwiper .swiper-pagination",
            clickable: true,
        },
    });

    // Right Side Slider
    const rightSwiper = new Swiper(".rightSwiper", {
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".rightSwiper .swiper-pagination",
            clickable: true,
        },
        navigation: {
            nextEl: ".rightSwiper .swiper-button-next",
            prevEl: ".rightSwiper .swiper-button-prev",
        },
    });
</script>

<style>
.img-cover {
    width: 100%;

    object-fit: cover;
}
.swiper {
    width: 100%;
    height: 100%;
}
</style>

    @foreach ($homeSections as $homeSection)
        @if (
            $homeSection->name == \App\Models\HomeSection::$hindi_classes and
                !empty($hindiWebinars) and
                !$hindiWebinars->isEmpty())

            <div class="container">
                <div class="best-selling-courses-section position-relative"
                    style="background-image: url(https://asttrolok.in/asttroloknew/store/landing_builder/landing_1/5/topselling_background_pVh.png);background-color: #32A128;">

                    <div class="best-selling-courses-section__floating-icon d-flex-center">
                        <img src="https://asttrolok.in/asttroloknew/store/landing_builder/landing_13/375/best_selling_overlay_fIs.png"
                            alt="icon">
                    </div>

                    <div class="row h-100">
                        <div class="col-12 col-md-6 col-lg-3 position-relative h-100  pt-lg-48">
                            <h2 class="font-32 text-white mr-8">Hindi Courses
                                </h2>

                            <p class="mt-20 text-white opacity-70 mr-8 font-16">Explore our top-selling courses, chosen
                                by thousands of learners who’ve enrolled and benefited. These bestsellers reflect what’s
                                most in-demand and valuable across our platform.</p>

                            <a href="/classes?hindi=on" target="_blank"
                                class="btn-flip-effect btn-flip-effect__no-side d-inline-flex align-items-center font-16 gap-8 font-weight-bold text-white mt-16"
                                data-text="View More">
                                <span class="btn-flip-effect__text">View More</span>
                                <svg width="24px" height="24px" class="icons text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"
                                        stroke-width="1.5" d="M14.43 5.93L20.5 12l-6.07 6.07M3.5 12h16.83"></path>
                                </svg> </a>
                        </div>

                        @foreach ($hindiWebinars->take(3) as $hindiWebinar)
                            @include('web.default2.includes.webinar.grid-card-new', [
                                'webinar' => $hindiWebinar,
                            ])
                        @endforeach

                    </div>
                </div>
            </div>

            <div class="position-relative d-flex" style="height: 100px"></div>
            <div class="hybrid-information-section-2-images-text-section position-relative">
                <div class="container position-relative h-100">
                    <div class="row h-100  ">
                        @foreach ($subscriptions as $subscription)
                            <div class="col-12 col-lg-5">
                                <div class="d-flex justify-content-center align-items-start flex-column h-100">
                                   <div class="d-inline-flex-center py-8 px-16 rounded-8 bg-success-20 font-12"
     style="color:#32A128; border:1px solid #32A128;">
                                        Become an Astrologer for Just ₹2,100/Month</div>
                                   <a href="{{ $subscription->getUrl() }}" class="text-decoration-none  d-block"><h2 class="mt-12 font-32 text-dark">{!! $subscription->title !!}</h2></a>

                                    <p class="mt-20 font-16 text-gray-500">{!! $subscription->description !!}</p>
                                    <a href="{{ $subscription->getUrl() }}" target="_blank"
                                        class="btn-flip-effect btn  btn-xlg gap-8 text-white mt-24"
                                        data-text="Enroll Now"style="background-color:#32A128;border:1px solid #32A128;">
                                        <svg width="24px" height="24px" class="icons"
                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            aria-hidden="true">
                                            <path d="M11.5 21a9.5 9.5 0 100-19 9.5 9.5 0 000 19z" opacity=".4">
                                            </path>
                                            <path
                                                d="M21.3 22c-.18 0-.36-.07-.49-.2l-1.86-1.86a.706.706 0 010-.99c.27-.27.71-.27.99 0l1.86 1.86c.27.27.27.71 0 .99-.14.13-.32.2-.5.2z">
                                            </path>
                                        </svg>
                                        <span class="btn-flip-effect__text">Enroll Now</span>
                                    </a>
                                    <div class="d-flex align-items-center mt-60 gap-54">
                                        <div class="hybrid-information-section-2-images-text-section__statistic-item">
                                            <div class="d-flex align-items-center gap-8">
                                                <span class="line"style="background-color:#32A128"></span>
                                                <span class="font-24 font-weight-bold" style="font:Gilroy">714+</span>
                                            </div>
                                            <h4 class="font-16 text-dark" style="margin-top:6px;">Students</h4>
                                         <p class="font-14 text-gray-500" style="margin-top:2px;">already learning</p>

                                        </div>
                                        <div class="hybrid-information-section-2-images-text-section__statistic-item">
                                            <div class="d-flex align-items-center gap-8">
                                                <span class="line"style="background-color:#32A128"></span>
                                                <span
                                                    class="font-24 font-weight-bold">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
                                            </div>
                                            <h4 class="font-16 text-dark" style="margin-top:6px;">/ Month</h4>
                                            <p class="font-14 text-gray-500" style="margin-top:2px;">Beginner to Expert Journey</p>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-1"></div>
                            <div class="col-12 col-lg-6 h-100 mt-32 mt-lg-0 px-24 px-lg-48">
                                <div
                                    class="hybrid-information-section-2-images-text-section__main-image position-relative rounded-32">
                                    <!-- <a href="{{ $subscription->getUrl() }}" class="text-decoration-none d-block">
                                    <img src="https://storage.googleapis.com/astrolok/store/1/new-one/Astttrolok-Hindi.jpg"
                                        alt="{{ $subscription->title }}" class="img-cover rounded-32">
                                    </a> -->
                                    <a href="{{ $subscription->getUrl() }}" class="text-decoration-none d-block">
                                        @if(!empty($subscription->home_banner))
                                            <img src="{{ config('app.img_dynamic_url') }}{{ $subscription->home_banner }}"
                                                alt="{{ $subscription->title }}" 
                                                class="img-cover rounded-32">
                                        @else
                                            <img src="https://storage.googleapis.com/astrolok/store/1/new-one/Astttrolok-Hindi.jpg"
                                                alt="{{ $subscription->title }}" 
                                                class="img-cover rounded-32">
                                        @endif
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @endif

        @if (
            $homeSection->name == \App\Models\HomeSection::$english_classes and
                !empty($englishclasses) and
                !$englishclasses->isEmpty())
            <div class="position-relative d-flex" style="height: 100px"> </div>
            <div class="container">
                <div class="best-rated-courses-section position-relative"
                    style="background-image: url(https://asttrolok.in/asttroloknew/store/landing_builder/landing_1/9/topselling_background_xZD.png)">

                    <div class="best-rated-courses-section__floating-icon d-flex-center">
                        <img src="https://asttrolok.in/asttroloknew/store/landing_builder/landing_13/379/best_rated_overlay_5IF.png"
                            alt="icon">
                    </div>

                    <div class="row h-100">
                        <div class="col-12 col-md-6 col-lg-3 position-relative h-100 pt-lg-48">
                            <h2 class="font-32 text-white mr-8">English Courses by Alok Ji</h2>

                            <p class="mt-20 text-white opacity-70 font-16 mr-8">Master the timeless knowledge of Vedic
                                Science in simple English! Learn astrology, philosophy, and spirituality with clarity
                                and confidence</p>

                            <a href="classes" target="_blank"
                                class="btn-flip-effect btn-flip-effect__no-side d-inline-flex font-16 align-items-center gap-8 font-weight-bold text-white mt-16"
                                data-text="View More">
                                <span class="btn-flip-effect__text">View More</span>
                                <svg width="24px" height="24px" class="icons text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"
                                        stroke-width="1.5" d="M14.43 5.93L20.5 12l-6.07 6.07M3.5 12h16.83"></path>
                                </svg> </a>
                        </div>

                        @foreach ($englishclasses->take(3) as $englishclasse)
                            @include('web.default2.includes.webinar.grid-card-new', [
                                'webinar' => $englishclasse,
                            ])
                        @endforeach

                    </div>

                </div>
            </div>

        @endif
    @endforeach

        <div class="position-relative d-flex" style="height: 100px"> </div>
        <div class="container ">
            <div class="best-selling-courses-section position-relative"
                style="background-image: url(/store/landing_builder/landing_1/5/topselling_background_pVh.png);background-color: #32A128;margin-right: -48px;margin-left: -48px;">

                <div class="best-selling-courses-section__floating-icon d-flex-center">
                    <img src="https://asttrolok.in/asttroloknew/store/landing_builder/landing_13/375/best_selling_overlay_fIs.png"
                        alt="icon">
                </div>

                <div class="row h-100">
                    <div class="col-12 col-md-6 col-lg-3 position-relative h-100 pt-lg-48">
                        <h2 class="font-32 text-white mr-8">Consult With Us</h2>

                        <p class="mt-20 text-white opacity-70 mr-8 font-16">Connect instantly with experienced astrologers
                            from across India, fluent in Hindi and English, specialising in Vedic astrology, numerology,
                            Vastu and related fields.</p>

                        <a href="/consult-with-astrologers" target="_blank"
                            class="btn-flip-effect btn-flip-effect__no-side d-inline-flex align-items-center font-16 gap-8 font-weight-bold text-white mt-16"
                            data-text="View More">
                            <span class="btn-flip-effect__text">View More</span>
                            <svg width="24px" height="24px" class="icons text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10"
                                    stroke-width="1.5" d="M14.43 5.93L20.5 12l-6.07 6.07M3.5 12h16.83"></path>
                            </svg> </a>
                    </div>

                    @foreach ($consultant->take(3) as $instructor)

                        <div class="col-12 col-md-6 col-lg-3 mt-24 ">
                            <div class="course-grid-card-1 position-relative" >
                                <a href="users/{{ $instructor->id }}/{{ $instructor->full_name }}" class="text-decoration-none d-block"></a>
                                <div class="course-grid-card-1__image mt-20"
                                    style="background-color: #FEEBE7; height: 170px; position: relative;">
                                                <h3 class="font-28 font-weight-bold text-secondary text-center1 swiper-container1-title"
                                    style="font-size: 24px; padding-top: 25px; margin-left: 10%;">
                                    {{ $instructor->full_name }}
                                </h3>

                                <span class="font-weight-500 text-center1 swiper-container1-desc"
                                    style="display: block; color: #000; font-size: 16px; height: 60px;
                                        overflow: hidden; text-overflow: ellipsis; margin-left: 10%; margin-top: 4px;">
                                    <pre style="font-family: var(--font-family-base) !important; margin:0; white-space: pre-line;">{{ trim($instructor->bio) }}</pre>
                                </span>

                                </div>

                                <img src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(108) }}"
                                    alt="{{ $instructor->full_name }}"
                                    style="
        width: 140px;
        height: 140px;
        border-radius: 50%;
        object-fit: cover;
        position: absolute;
        top: 123px;
        left: 25%;
        transform: translateX(-50%);
        z-index: 10;
        border: 4px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        marging-top:80px;
        margin-left: 10%;
     ">

                                <div class="course-grid-card-1__body d-flex flex-column"
                                    style="
        position: relative;
        padding-top: 90px;
        padding-bottom: 20px;
        background: white;
        border-radius: 0 0 12px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 240px;
     ">

                                    <div class="" style="margin: 0 20px; width:100%;">
                                        <div
                                            style="font-family: var(--font-family-base); display: flex; align-items: center; justify-content: flex-start; gap: 4px; margin-top: 10px;">
                                            <span
                                                style="color: #32A128; font-size: 30px; font-weight: 600;;margin-left: 10%;">{{ handlePrice($instructor->meeting->amount/ 30)  }}</span>
                                            <span style="color: #000000; font-size: 16px; font-weight: 700;">/ min</span>
                                        </div>
                                        <div style="font-size: 14px; color: #000; ">
                                        </div>

                                        <div class="stars-card d-flex  mt-10"
                                            style="gap: 2px;margin-left: 10%;align-items: center; justify-content: flex-start; ">
                                            @include('web.default2.includes.webinar.rate', [
                                                'rate' => $instructor->rating,
                                            ])
                                        </div>
                                    </div>

                                    <div class="d-flex "
                                        style="width: 100%; margin-top: 12px; align-items: center; justify-content: flex-start; ">
                                        <a href="users/{{ $instructor->id }}/{{ $instructor->full_name }}" class="btn btn-sm btn-primary fw-bold"
                                            style="background-color: #32A128;
                  border-radius: 20px;
                  border-color: #32A128;
                  font-weight: 700;
                  padding: 8px 35px;
                  min-width: 250px;
                  display: inline-block;
                  text-align: center;;margin-left: 10%;">
                                            Book Now
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    <div class="position-relative d-flex" style="height: 100px"></div>

    @foreach ($homeSections as $homeSection)

        @if ($homeSection->name == \App\Models\HomeSection::$remedies and !empty($remedies) and !$remedies->isEmpty())
            <div class="container">
                <div class="upcoming-courses-section position-relative "
                    style="background-image: url(https://asttrolok.in/asttroloknew/https://asttrolok.in/asttroloknew/store/landing_builder/landing_13/389/upcoming_bg_lMn.svg)">

                    <div class="d-flex-center flex-column text-center">
                        <div class="d-flex-center py-8 px-16 rounded-8  bg-success-20 font-12  d-none"style="color:#32A128; border:1px solid #32A128;">
                            Stay Tuned</div>

                        <h2 class="mt-8 font-32 text-dark">Remedies</h2>

                        <p class="mt-16 font-16 text-gray-500">Greatest Remedies are here to help you</p>
                    </div>

                    <div class="row">
                        @foreach ($remedies as $remedy)
                            <div class="col-12 col-md-6 col-lg-3 mt-28">
                                <div class="upcoming-course-card position-relative"><a href="{{ $remedy->getUrl() }}"
                                        target="_blank" class="text-decoration-none d-block">
                                        <div class="upcoming-course-card__image position-relative rounded-16 bg-gray-100">
                                            <img src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}"
                                                alt="" class="img-cover rounded-16">
                                        </div>
                                    </a>
                                    <div class="upcoming-course-card__body position-relative px-12 pb-12">
                                        <div
                                            class="upcoming-course-card__content d-flex flex-column bg-white py-12 rounded-16"style="height: 100%;">
                                            <div class="d-flex align-items-center mb-16 px-12">

                                                <div class="d-flex flex-column ml-4">
                                                    <a href="{{ $remedy->getUrl() }}" target="_blank"
                                                        class="font-14 font-weight-bold text-dark"
                                                        onclick="event.stopPropagation()">{{ clean($remedy->title, 'title') }}</a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    <div class="d-flex-center flex-column mt-40">
                        <a href="/remedies?sort=newest" target="_blank"
                            class="btn-flip-effect btn  btn-xlg gap-8 text-white" data-text="View More"style=" border:1px solid #32A128;background-color:#32A128">
                            <svg width="24px" height="24px" class="icons" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M16.75 3.56V2c0-.41-.34-.75-.75-.75s-.75.34-.75.75v1.5h-6.5V2c0-.41-.34-.75-.75-.75s-.75.34-.75.75v1.56c-2.7.25-4.01 1.86-4.21 4.25-.02.29.22.53.5.53h16.92c.29 0 .53-.25.5-.53-.2-2.39-1.51-4-4.21-4.25z">
                                </path>
                                <path
                                    d="M20 9.84c.55 0 1 .45 1 1V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5v-6.16c0-.55.45-1 1-1h16z"
                                    opacity=".4"></path>
                                <path
                                    d="M8.5 15c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.09-.1-.16-.21-.21-.33A.995.995 0 017.5 14c0-.13.03-.26.08-.38s.12-.23.21-.33c.1-.09.21-.16.33-.21a1 1 0 01.76 0c.12.05.23.12.33.21.09.1.16.21.21.33.05.12.08.25.08.38s-.03.26-.08.38-.12.23-.21.33c-.1.09-.21.16-.33.21-.12.05-.25.08-.38.08zM12 15c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.18-.19-.29-.45-.29-.71 0-.26.11-.52.29-.71.1-.09.21-.16.33-.21.24-.11.52-.11.76 0 .12.05.23.12.33.21.18.19.29.45.29.71 0 .26-.11.52-.29.71-.1.09-.21.16-.33.21-.12.05-.25.08-.38.08zM8.5 18.5c-.13 0-.26-.03-.38-.08s-.23-.12-.33-.21c-.18-.19-.29-.45-.29-.71 0-.26.11-.52.29-.71.1-.09.21-.16.33-.21a1 1 0 01.76 0c.12.05.23.12.33.21.18.19.29.45.29.71 0 .26-.11.52-.29.71-.1.09-.21.16-.33.21-.12.05-.25.08-.38.08z">
                                </path>
                            </svg>
                            <span class="btn-flip-effect__text">View More</span>
                        </a>
                    </div>

                </div>
            </div>
        @endif

        @if ($homeSection->name == \App\Models\HomeSection::$blog and !empty($blog) and !$blog->isEmpty())

            <div class="blog-section position-relative"
                style="background-image: url(https://asttrolok.in/asttroloknew/store/landing_builder/landing_1/13/blog_background_e5l.html)">

                <div class="container">
                    <div class="d-flex-center flex-column text-center">
                        <div
                            class="d-inline-flex-center py-8 px-16 rounded-8  bg-success-20 font-12 d-none"style=" border:1px solid #32A128;color:#32A128">
                            Read More</div>

                        <h2 class="mt-12 font-32 text-dark">Blog and Articles</h2>

                        <p class="mt-16 font-16 text-gray-500">Stay informed with expert-written articles, tips, and
                            insights to support your learning journey daily</p>
                    </div>

                    <div class="row mt-4">
                        @php
                            $blogcount = 0;
                        @endphp
                        @foreach ($blog as $post)
                       

                            @if ($blogcount == 0)
                                <div class="col-12 col-lg-6 mt-24">
                                    <a href="{{ $post->getUrl() }}" class="text-decoration-none d-block">
                                        <div class="blog-section__post-card position-relative rounded-24 one-large-col">
                                            <div class="position-relative">
                                                <img src="{{ config('app.img_dynamic_url') }}{{ $post->image }}"
                                                    alt="{{ $post->title }}"
                                                    class="blog-section__post-card-img img-cover rounded-24">
                                            </div>

                                            <div class="blog-section__post-card-footer p-16">
                                                <div class="d-flex flex-column justify-content-end w-100 h-100">
                                                    <h3 class="font-16 text-white  d-none">{{ $post->title }}</h3>

                                                    <div
                                                        class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between mt-12">
                                                        <div class="d-flex align-items-center  ">
                                                            <div class="size-36 rounded-circle bg-gray-100 d-none">
                                                                <img src="{{ $post->author->image }}"
                                                                    alt="{{ $post->author->full_name }}"
                                                                    class="img-cover rounded-circle">
                                                            </div>
                                                            <div class="ml-4 d-none">
                                                                <h5 class="font-14 text-white">
                                                                    {{ $post->author->full_name }}</h5>
                                                                <p class="font-12 text-white mt-2">
                                                                    {{ $post->comments_count }}</p>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="position-relative d-inline-flex align-items-center rounded-16 px-12 py-10 bg-dark-20">
                                                            <div class="d-flex align-items-center">
                                                                <svg width="16px" height="16px"
                                                                    class="icons text-white"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-miterlimit="10" stroke-width="1.5"
                                                                        d="M8 2v3M16 2v3M3.5 9.09h17M21 8.5V17c0 3-1.5 5-5 5H8c-3.5 0-5-2-5-5V8.5c0-3 1.5-5 5-5h8c3.5 0 5 2 5 5z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M11.995 13.7h.01M8.294 13.7h.01M8.294 16.7h.01">
                                                                    </path>
                                                                </svg> 
                                                             <span class="ml-4 font-14 text-white">{{ date('d M Y', $post->created_at) }}</span>

                                                            </div>

                                                            <div class="blog-section__post-card-footer-divider"></div>

                                                            <div class="d-flex align-items-center">
                                                                <svg width="16px" height="16px"
                                                                    class="icons text-white"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="1.5"
                                                                        d="M22 12c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="1.5"
                                                                        d="M15.71 15.18l-3.1-1.85c-.54-.32-.98-1.09-.98-1.72v-4.1">
                                                                    </path>
                                                                </svg> <span class="ml-4 font-14 text-white">10</span>
                                                            </div>

                                                            <div class="blog-section__post-card-footer-divider"></div>

                                                            <div class="d-flex align-items-center">
                                                                <svg width="16px" height="16px"
                                                                    class="icons text-white"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor"
                                                                    aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-miterlimit="10" stroke-width="1.5"
                                                                        d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M15.996 11h.01M11.995 11h.01M7.995 11h.008">
                                                                    </path>
                                                                </svg> <span class="ml-4 font-14 text-white">0</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="row">
                                        @php
                                            $blogcount++;
                                        @endphp
                                    @else
                                        @include('web.default2.blog.grid-list-new', ['post' => $post])
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex-center flex-column text-center mt-24">

                <a href="/blog" class="btn-flip-effect btn  btn-xlg text-white gap-8" data-text="Blog Posts"
                    style="background-color:#32A128; border:1px solid #32A128;">
                    <svg width="24px" height="24px" class="icons" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path
                            d="M21.66 10.44l-.98 4.18c-.84 3.61-2.5 5.07-5.62 4.77-.5-.04-1.04-.13-1.62-.27l-1.68-.4c-4.17-.99-5.46-3.05-4.48-7.23l.98-4.19c.2-.85.44-1.59.74-2.2 1.17-2.42 3.16-3.07 6.5-2.28l1.67.39c4.19.98 5.47 3.05 4.49 7.23z"
                            opacity=".4"></path>
                        <path
                            d="M15.06 19.39c-.62.42-1.4.77-2.35 1.08l-1.58.52c-3.97 1.28-6.06.21-7.35-3.76L2.5 13.28c-1.28-3.97-.22-6.07 3.75-7.35l1.58-.52c.41-.13.8-.24 1.17-.31-.3.61-.54 1.35-.74 2.2l-.98 4.19c-.98 4.18.31 6.24 4.48 7.23l1.68.4c.58.14 1.12.23 1.62.27zM17.49 10.51c-.06 0-.12-.01-.19-.02l-4.85-1.23a.75.75 0 01.37-1.45l4.85 1.23a.748.748 0 01-.18 1.47z">
                        </path>
                        <path
                            d="M14.56 13.89c-.06 0-.12-.01-.19-.02l-2.91-.74a.75.75 0 01.37-1.45l2.91.74c.4.1.64.51.54.91-.08.34-.38.56-.72.56z">
                        </path>
                    </svg>
                    <span class="btn-flip-effect__text text-white">Blog Posts</span>
                </a>

            </div>

            </div>
            </div>
             <div class="container">

                  <div class="d-flex-center flex-column text-center">
                        <div
                            class="d-inline-flex-center py-8 px-16 rounded-8  bg-success-20 font-12  d-none"style=" border:1px solid #32A128;color:#32A128">
                            Student Speak</div>

                        <h2 class="mt-12 font-32 text-dark">Real Stories of transformation and growth</h2>

                    </div>
                    <script defer async src="https://cdn.trustindex.io/loader.js?76d20565517c6153b51678c18ab"></script>

                    <div class="d-flex-center flex-column text-center mt-80">

                        <h2 class="mt-12 mb-10 font-32 text-dark">Join Us on Instagram</h2>

                    </div>
                     <script defer async src='https://cdn.trustindex.io/loader-feed.js?adb441f551c8621a0276a48c072'></script>
                        <div class="d-flex-center flex-column text-center mt-24">

                <a href="https://www.instagram.com/asttrolok/#" class="btn-flip-effect btn btn-success btn-xlg text-white gap-8" data-text="Join Us on Instagram"
                    style="background-color:#32A128; border:1px solid #32A128;">
                    <svg width="24px" height="24px" class="icons" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path
                            d="M21.66 10.44l-.98 4.18c-.84 3.61-2.5 5.07-5.62 4.77-.5-.04-1.04-.13-1.62-.27l-1.68-.4c-4.17-.99-5.46-3.05-4.48-7.23l.98-4.19c.2-.85.44-1.59.74-2.2 1.17-2.42 3.16-3.07 6.5-2.28l1.67.39c4.19.98 5.47 3.05 4.49 7.23z"
                            opacity=".4"></path>
                        <path
                            d="M15.06 19.39c-.62.42-1.4.77-2.35 1.08l-1.58.52c-3.97 1.28-6.06.21-7.35-3.76L2.5 13.28c-1.28-3.97-.22-6.07 3.75-7.35l1.58-.52c.41-.13.8-.24 1.17-.31-.3.61-.54 1.35-.74 2.2l-.98 4.19c-.98 4.18.31 6.24 4.48 7.23l1.68.4c.58.14 1.12.23 1.62.27zM17.49 10.51c-.06 0-.12-.01-.19-.02l-4.85-1.23a.75.75 0 01.37-1.45l4.85 1.23a.748.748 0 01-.18 1.47z">
                        </path>
                        <path
                            d="M14.56 13.89c-.06 0-.12-.01-.19-.02l-2.91-.74a.75.75 0 01.37-1.45l2.91.74c.4.1.64.51.54.91-.08.34-.38.56-.72.56z">
                        </path>
                    </svg>
                    <span class="btn-flip-effect__text text-white">Join Us on Instagram</span>
                </a>

            </div>

             <script src="https://elfsightcdn.com/platform.js" async></script>
                    <div class="elfsight-app-932af542-0dfe-45e9-9aa0-b2cbe27a277e" data-elfsight-app-lazy></div>

                      </div>

        @endif

        <section class="container">
            <div class="row">
                <div class="col-12 col-lg-12 mt-25 mt-lg-0 ">

                    @if (
                        $homeSection->name == \App\Models\HomeSection::$featured_classes and
                            !empty($featureWebinars) and
                            !$featureWebinars->isEmpty())
                        <section class="home-sections Featured-section home-sections-swiper container homehide">
                            <div class="px-20 px-md-0">
                                <h2 class="section-title">{{ trans('home.featured_classes') }}</h2>
                                <p class="section-hint">{{ trans('home.featured_classes_hint') }}</p>
                            </div>

                            <div class="feature-slider-container position-relative d-flex justify-content-center mt-10">
                                <div class="swiper-container features-swiper-container pb-25">
                                    <div class="swiper-wrapper py-10">
                                        @foreach ($featureWebinars as $feature)
                                            <div class="swiper-slide ">

                                                <a href="{{ $feature->webinar->getUrl() }}"><span>
                                                        <div class="feature-slider d-flex h-100"
                                                            onclick="featurjquery({{ $feature->webinar->getUrl() }});"
                                                            style="background-image: url('{{ config('app.img_dynamic_url') }}{{ $feature->webinar->getImageCover() }}')">
                                                            <div class="mask"></div>
                                                            <div class="p-5 p-md-25 feature-slider-card">
                                                                <div
                                                                    class="d-flex flex-column feature-slider-body position-relative h-100">
                                                                    @if ($feature->webinar->bestTicket() < $feature->webinar->price)
                                                                        <span
                                                                            class="badge badge-danger mb-2 ">{{ trans('public.offer', ['off' => $feature->webinar->bestTicket(true)['percent']]) }}</span>
                                                                    @endif
                                                                    <a href="{{ $feature->webinar->getUrl() }}">
                                                                        <h3 class="card-title mt-1">
                                                                            {{ $feature->webinar->title }}</h3>
                                                                    </a>

                                                                    <div
                                                                        class="user-inline-avatar mt-15 d-flex align-items-center">
                                                                        <div class="avatar bg-gray200">
                                                                            <img src="{{ config('app.img_dynamic_url') }}{{ $feature->webinar->teacher->getAvatar() }}"
                                                                                class="img-cover"
                                                                                alt="{{ $feature->webinar->teacher->full_naem }}">
                                                                        </div>
                                                                        <a href="{{ $feature->webinar->teacher->getProfileUrl() }}"
                                                                            target="_blank"
                                                                            class="user-name font-14 ml-5">{{ $feature->webinar->teacher->full_name }}</a>
                                                                    </div>

                                                                    <p class="mt-25 feature-desc text-gray">
                                                                        {{ $feature->description }}</p>

                                                                    @include(
                                                                        'web.default2.includes.webinar.rate',
                                                                        ['rate' => $feature->webinar->getRate()]
                                                                    )

                                                                    <div
                                                                        class="feature-footer mt-auto d-flex align-items-center justify-content-between">
                                                                        <div class="d-flex justify-content-between">
                                                                            <div class="d-flex align-items-center">
                                                                                <i data-feather="clock" width="20"
                                                                                    height="20"
                                                                                    class="webinar-icon"></i>
                                                                                <span
                                                                                    class="duration ml-5 text-dark-blue font-14">{{ convertMinutesToHourAndMinute($feature->webinar->duration) }}
                                                                                    {{ trans('home.hours') }}</span>
                                                                            </div>

                                                                            <div class="vertical-line mx-10"></div>

                                                                            <div class="d-flex align-items-center">
                                                                                <i data-feather="calendar" width="20"
                                                                                    height="20"
                                                                                    class="webinar-icon"></i>
                                                                                <span
                                                                                    class="date-published ml-5 text-dark-blue font-14">{{ dateTimeFormat(!empty($feature->webinar->start_date) ? $feature->webinar->start_date : $feature->webinar->created_at, 'j M Y') }}</span>
                                                                            </div>
                                                                        </div>

                                                                        <div class="feature-price-box">
                                                                            @if (!empty($feature->webinar->price) and $feature->webinar->price > 0)
                                                                                @if ($feature->webinar->bestTicket() < $feature->webinar->price)
                                                                                    <span
                                                                                        class="real">{{ handlePrice($feature->webinar->bestTicket(), true, true, false, null, true) }}</span>
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

                </div>
        </section>
    @endforeach
    <section class="home-sections  home-sections-swiper container Remedies mt-80">
         <div class="d-flex-center flex-column text-center  mt-80">
                        <div  class="d-inline-flex-center py-8 px-16 rounded-8  bg-success-20 font-12 text-success  d-none" style=" border:1px solid #32A128;color:#32A128 !important;" >
                           Coverage of Media Presence</div>

                        <h2 class="mt-12 font-32 text-dark">Asttrolok's presence in news logs</h2>

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
                                                      
                                                        <img src="/assets/default/images/News/ZeeNews.webp" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;height:100%;"alt="img">
</a>
                                                </div>
                                                <figcaption class="webinar-card-body">

                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-black">Zee News</h3>

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
                                                       
                                                        <img src="/assets/default/images/News/ABP.webp" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;height:100%;"alt="img">
</a>
                                                </div>
                                                <figcaption class="webinar-card-body">

                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">ABP News</h3>

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
                                                        
                                                        <img src="/assets/default/images/News/TOI.webp" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;height:100%;"alt="img">
</a>
                                                </div>
                                                <figcaption class="webinar-card-body">

                                                        <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">TOI</h3>

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
                                                   
                                                    <img src="/assets/default/images/News/swar.webp" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;height:100%;"alt="img">
</a>
                                            </div>
                                            <figcaption class="webinar-card-body">

                                                    <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">Sugarmint</h3>

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
                                                      
                                                    <img src="/assets/default/images/News/moneycontrol1.png" class="img-cover" loading="lazy" style="object-fit: contain; padding: 40px;height:100%;"alt="img">
</a>
                                            </div>
                                            <figcaption class="webinar-card-body">

                                                    <h3 class="mt-5 font-weight-bold font-16 text-dark-blue">Moneycontrol</h3>

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
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/parallax/parallax.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/home.min.js"></script>
    <script>
        function featurjquery(urls) {
            window.location.href = urls;
        }
    </script>
    <script>
        function changeImageAndShowPopup(data) {
            console.log('data', data);
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