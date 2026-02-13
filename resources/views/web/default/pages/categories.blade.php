@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">
@endpush

@php

$cover_img['Astrology']="/store/1/Category/Astrology-mobile.jpg";
 $cover_img['Ayurveda']="/store/1/Category/Ayurveda-mobile.jpg";
$cover_img['Palmistry']="/store/1/Category/Palmistry-mobile.jpg";
$cover_img['Vastu']="/store/1/Category/Vastu-mobile.jpg";
$cover_img['Numerology']="/store/1/Category/Numerology-mobile.jpg";

@endphp

@section('content')
    <section class="cart-banner mobile-course-slider search-top-banner opacity-04 position-relative ">

        <div class="container">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 mb-15">{{ !empty($pageTitle1) ? $pageTitle1: $category->title}}</h1>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-20">

        @if(!empty($featureWebinars) and !$featureWebinars->isEmpty())
            <section class="mb-25 mb-lg-0">
                <h2 class="font-24 text-dark-blue">{{ trans('home.featured_webinars') }}</h2>
                <span class="font-14 text-gray font-weight-400">{{ trans('site.newest_courses_subtitle') }}</span>

                <div class="position-relative mt-20">
                    <div class="swiper-container">
                                <div class="swiper swiper-slider">
                                  <div class="swiper-wrapper">
                            @foreach($featureWebinars as $featureWebinar)
                                <div class="swiper-slide">
                                    @include('web.default.includes.webinar.grid-card',['webinar' => $featureWebinar->webinar])
                                </div>
                            @endforeach

                        </div>
                    </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <div class="swiper-pagination"></div>
                    </div>
                </div>

            </section>
        @endif

        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">

 <form id="filtersForm" class="consult-filter" style="display:block;" action="/{{ $page }}" method="get">

        @include('web.default.pages.includes.top_filters')
         </form>

                <div class="row ">

                    <div class="col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row mx-0">
                                @foreach($webinars as $webinar)

                                    <div class="col-md-6 col-lg-4 mt-20  mobilegrid">
                                        @include('web.default.includes.webinar.grid-card',['webinar' => $webinar])
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($webinars as $webinar)

                            <div class="col-12 col-lg-4 mt-20 ">
                                @include('web.default.includes.webinar.list-card',['webinar' => $webinar])
                                 </div>
                            @endforeach
                        @endif

                    </div>

                </div>

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
    .load-card-list {
        display:none;
    }
    .load-card-list.display {
        display: inline-block;
    }
    </style>

    <script   src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script   >
    $(document).ready(function(){
         $(".load-card-list").slice(0,10).show();
      $("#listloadMore").click(function(e){
        e.preventDefault();
        $(".load-card-list:hidden").slice(0,10).fadeIn("slow");
        console.log($(".load-card-list:hidden").length);
        if($(".load-card-list:hidden").length == 0){
           $("#listloadMore").fadeOut("slow");
          }
      });
      if($(".load-card-list").length < 9){
       $("#listloadMore").hide();

      }

      $(".loadid").slice(0,10).show();
      $("#loadMore").click(function(e){
        e.preventDefault();
        $(".loadid:hidden").slice(0,10).fadeIn("slow");

        if($(".loadid:hidden").length == 0){
           $("#loadMore").fadeOut("slow");

          }
      });
      if($(".loadid").length < 9){
           $("#loadMore").hide();

          }
    });
</script>
@endsection

@push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/categories.min.js"></script>
@endpush
