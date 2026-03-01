@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app.css">

           <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/profile.min.css">
@endpush

@php
 $cover_img['Astrology']="/store/1/Category/Astrology-min.jpg";
 $cover_img['Ayurveda']="/store/1/Category/Ayurveda-min.jpg";
$cover_img['Palmistry']="/store/1/Category/Palmistry.jpg";
$cover_img['Vastu']="/store/1/Category/Vastu-min.jpg";
$cover_img['Numerology']="/store/1/Category/Numerology-min.jpg";

@endphp

@section('content')

    <div class="profile-cover-card">
        <img src="https://storage.googleapis.com/astrolok/webp/store/1/banner/course.webp" class="img-cover" alt="">
</div>
    <div class="container    "style="z-index: 9;max-width: 1140px !important;margin-top: -245px;background-color: #ffffff;position: relative;opacity: 1;border-radius: 24px;"
>
<h1 class="d-none">Courses</h1>
        @if(!empty($featureWebinars) and !$featureWebinars->isEmpty())
            <section class="mb-25 mb-lg-0">
                <h2 class="font-24 text-dark-blue">{{ trans('home.featured_webinars') }}</h2>
                <span class="font-14 text-gray font-weight-400">{{ trans('site.newest_courses_subtitle') }}</span>

                <div class="position-relative mt-20">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">

                            @foreach($featureWebinars as $featureWebinar)
                                <div class="swiper-slide">
                                    @include('web.default2.includes.webinar.grid-card',['webinar' => $featureWebinar->webinar])
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="swiper-pagination"></div>
                    </div>
                </div>

            </section>
        @endif

        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">
            <form action="{{ $sortFormAction }}" method="get" id="filtersForm">

                @include('web.default.pages.includes.top_filters', ['hideCategoryFilter' => true])

                <div class="row ">

                    <div class="col-12 col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row">
                                 @foreach($subscriptions as $subscription)
                                    @if(!empty($subscription))
                                      @if($category->title == 'Astrology')
                                        <div class="col-12 col-lg-4 mt-20 ">
                                            <div class="webinar-card">
                                                <figure>
                                                    <div class="image-box">

                                                        <a href="{{ $subscription->getUrl() }}">

                                                            <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $subscription->thumbnail }}" class="img-cover" alt="Astrology Learning Program">
                                                        </a>

                                                                        <div class="progress">
                                                                <span class="progress-bar" style="width: 2%"></span>
                                                            </div>

                                                                </div>

                                                    <figcaption class="webinar-card-body">
                                                        <div class="user-inline-avatar d-flex align-items-center">
                                                            <div class="avatar bg-gray200">
                                                                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/store/1/astrologer_mobile/Alok Sir.jpg" class="img-cover" alt="Mr.Alok Khandelwal">
                                                            </div>
                                                            <a href="/users/1015/astrologer-mr.alok-khandelwal" target="_blank" class="user-name ml-5 font-14">Mr.Alok Khandelwal</a>
                                                        </div>

                                                        <a href="{{ $subscription->getUrl() }}">

                                                            <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">{{ $subscription->title }}</h3>
                                                        </a>

                                                                        <span class="d-block font-14 mt-5">in <a href="/categories/astrology/Astrology-Basic" target="_blank" class="text-decoration-underline">Astrology</a></span>

                                                        <div class="stars-card d-flex align-items-center  mt-5">

                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>

                                                        <span class="badge badge-primary ml-10 rating-course"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> 4.3</span>
                                                        </div>

                                                        <div class="webinar-price-box mt-5">
                                                                                                    <span class="real">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>

                                                                                            </div>
                                                    </figcaption>
                                                </figure>
                                            </div>
                                        </div>
                                    @endif
                                  @endif
                                             @endforeach
                                @foreach($webinars as $webinar)

                                    <div class="col-md-6 col-lg-4  mt-20 ">
                                        @include('web.default2.includes.webinar.grid-card',['webinar' => $webinar])
                                    </div>
                                @endforeach

                                @if($webinars->isEmpty() && (empty($subscriptions) || $subscriptions->isEmpty() || $category->title != 'Astrology'))
                                    <div class="col-12 mt-30 mb-30 text-center">
                                        <div class="p-20">
                                            <i class="fas fa-search fa-3x text-gray mb-15" style="opacity:0.3"></i>
                                            <h4 class="font-18 text-gray font-weight-500">No courses found</h4>
                                            <p class="font-14 text-gray mt-5">Try changing or removing some filters</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($webinars as $webinar)
                                @include('web.default2.includes.webinar.list-card',['webinar' => $webinar])
                            @endforeach
                            @if($webinars->isEmpty())
                                <div class="col-12 mt-30 mb-30 text-center">
                                    <div class="p-20">
                                        <i class="fas fa-search fa-3x text-gray mb-15" style="opacity:0.3"></i>
                                        <h4 class="font-18 text-gray font-weight-500">No courses found</h4>
                                        <p class="font-14 text-gray mt-5">Try changing or removing some filters</p>
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>

                </div>

            </form>

        </section>
    </div>
<style>
        .course-teacher-card.instructors-list .off-label1 {
        position: absolute;
        top: 7px;
        right: 7px;
        border-radius: 15px 15px 15px 15px !important;
        z-index: 10;
    }
    .course-teacher-card.instructors-list .off-label {
        position: absolute;
        top: 7px;
        left: 7px;
        border-radius: 15px 15px 15px 15px !important;
        z-index: 10;
    }
    .loadid {
        display:none;

    }
    .loadid.display {
        display: inline-block;
    }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script >
    //   $(document).ready(function(){
    //   $(".loadid").slice(0,9).show();
    //   if($(".loadid").length < 9){

    //       $("#loadMore").fadeOut("hide");
    //   }
    //   $("#loadMore").click(function(e){
    //     e.preventDefault();
    //     $(".loadid:hidden").slice(0,9).fadeIn("slow");
    //     console.log($(".loadid").length);
    //     if($(".loadid:hidden").length == 0){
    //       $("#loadMore").fadeOut("slow");
    //       }

    //   });
    // })
    </script>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/categories.min.js"></script>
@endpush
