@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
@endpush

@php
 $cover_img['Astrology']="/store/1/Category/Astrology-min.jpg";
 $cover_img['Ayurveda']="/store/1/Category/Ayurveda-min.jpg";
$cover_img['Palmistry']="/store/1/Category/Palmistry.jpg";
$cover_img['Vastu']="/store/1/Category/Vastu-min.jpg";
$cover_img['Numerology']="/store/1/Category/Numerology-min.jpg";

@endphp

@section('content')
    <section class="cart-banner  search-top-banner opacity-04 position-relative">

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 mb-15">{{ !empty($pageTitle1) ? $pageTitle1: $category->title }}</h1>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-30">

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

                <div class="row mt-20">

                    <div class="col-12 col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row">
                                @foreach($webinars as $webinar)

                                    <div class="col-md-6 col-lg-4 mt-20 ">
                                        @include('web.default2.includes.webinar.grid-card',['webinar' => $webinar])
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($webinars as $webinar)
                                @include('web.default2.includes.webinar.list-card',['webinar' => $webinar])
                            @endforeach
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

    <script  src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@endsection

@push('scripts_bottom')
    <script defer src="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script defer src="{{ config('app.js_css_url') }}/assets2/default/js/parts/categories.min.js"></script>
@endpush
