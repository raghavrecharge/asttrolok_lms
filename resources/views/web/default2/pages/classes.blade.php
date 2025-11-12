@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
    <!--<link rel="canonical" href="https://www.asttrolok.com/classes" />-->
@endpush

@section('content')
    <!--<section class="site-top-banner search-top-banner opacity-04 position-relative">-->
    <section class="cart-banner search-top-banner opacity-04 position-relative">
        <!--<img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ getPageBackgroundSettings('categories') }}" class="img-cover" alt="{{ $pageTitle }} "/>-->
        

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 mb-15">Courses</h1>
                        <!--<h1 class="text-white font-30 mb-15">{{ $pageTitle }} </h1>-->
                        <!--<span class="course-count-badge py-5 px-10 text-white rounded">{{ $coursesCount }} {{ trans('product.courses') }}</span>-->

                        <!--<div class="search-input bg-white p-10 flex-grow-1">-->
                        <!--    <form action="/search" method="get">-->
                        <!--        <div class="form-group d-flex align-items-center m-0">-->
                        <!--            <input type="text" name="search" class="form-control border-0" placeholder="{{ trans('home.slider_search_placeholder') }}"/>-->
                        <!--            <button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>-->
                        <!--        </div>-->
                        <!--    </form>-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-30 ">

        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">
            <form action="/classes" method="get" id="filtersForm">

               {{-- @include('web.default2.pages.includes.top_filters') --}}

                <div class="row mt-20">
                    <div class="col-12 col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row">
                                <div class="col-12 col-lg-4 mt-20 ">
                                        <div class="webinar-card">
    <figure>
        <div class="image-box">
                            <!--<span class="badge badge-danger">25% Offer</span>-->
            
           
            <a href="https://www.asttrolok.com/subscriptions/asttrolok-pathshala">
           
                <img loading="lazy" decoding="async" src="https://storage.googleapis.com/astrolok/store/1/subscription/Astrology Learning Program.jpg" class="img-cover" alt="Astrology Learning Program">
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

          
           
            <a href="https://www.asttrolok.com/subscriptions/asttrolok-pathshala">
            
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">Asttrolok Pathshala</h3>
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
            

            <!--<div class="d-flex justify-content-between mt-5">-->
            <!--    <div class="d-flex align-items-center">-->
            <!--        <i data-feather="clock" width="15" height="15" class="webinar-icon"></i>-->
            <!--        <span class="duration font-14 ml-5">12:30 Hours</span>-->
            <!--    </div>-->

            <!--    <div class="vertical-line mx-15"></div>-->

            <!--    <div class="d-flex align-items-center">-->
            <!--        <i data-feather="calendar" width="15" height="15" class="webinar-icon"></i>-->
            <!--        <span class="date-published font-14 ml-5">13 Jul 2023</span>-->
            <!--    </div>-->
            <!--</div>-->

            <div class="webinar-price-box mt-5">
                                                        <span class="real">₹2100</span>
                        <!--<span class="off ml-10">₹20,000</span>-->
                                                </div>
        </figcaption>
    </figure>
</div>
                                    </div>
                                @foreach($webinars as $webinar)
                                    <!--<div class="col-12 col-lg-4 mt-20 loadid">-->
                                    <div class="col-12 col-lg-4 mt-20 ">
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


                    <!--<div class="col-12 col-lg-4 homehide">-->
                    <!--    <div class="mt-20 p-20 rounded-sm shadow-lg border border-gray300 filters-container">-->

                    <!--        <div class="">-->
                    <!--            <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('public.type') }}</h3>-->

                    <!--            <div class="pt-10">-->
                    <!--                @foreach(['bundle','webinar','course','text_lesson'] as $typeOption)-->
                    <!--                    <div class="d-flex align-items-center justify-content-between mt-20">-->
                    <!--                        <label class="cursor-pointer" for="filterLanguage{{ $typeOption }}">-->
                    <!--                            @if($typeOption == 'bundle')-->
                    <!--                                {{ trans('update.bundle') }}-->
                    <!--                            @else-->
                    <!--                                {{ trans('webinars.'.$typeOption) }}-->
                    <!--                            @endif-->
                    <!--                        </label>-->
                    <!--                        <div class="custom-control custom-checkbox">-->
                    <!--                            <input type="checkbox" name="type[]" id="filterLanguage{{ $typeOption }}" value="{{ $typeOption }}" @if(in_array($typeOption, request()->get('type', []))) checked="checked" @endif class="custom-control-input">-->
                    <!--                            <label class="custom-control-label" for="filterLanguage{{ $typeOption }}"></label>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <!--                @endforeach-->
                    <!--            </div>-->
                    <!--        </div>-->

                    <!--        <div class="mt-25 pt-25 border-top border-gray300">-->
                    <!--            <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('site.more_options') }}</h3>-->

                    <!--            <div class="pt-10">-->
                    <!--                @foreach(['subscribe','certificate_included','with_quiz','featured'] as $moreOption)-->
                    <!--                    <div class="d-flex align-items-center justify-content-between mt-20">-->
                    <!--                        <label class="cursor-pointer" for="filterLanguage{{ $moreOption }}">{{ trans('webinars.show_only_'.$moreOption) }}</label>-->
                    <!--                        <div class="custom-control custom-checkbox">-->
                    <!--                            <input type="checkbox" name="moreOptions[]" id="filterLanguage{{ $moreOption }}" value="{{ $moreOption }}" @if(in_array($moreOption, request()->get('moreOptions', []))) checked="checked" @endif class="custom-control-input">-->
                    <!--                            <label class="custom-control-label" for="filterLanguage{{ $moreOption }}"></label>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <!--                @endforeach-->
                    <!--            </div>-->
                    <!--        </div>-->


                    <!--        <button type="submit" class="btn btn-sm btn-primary btn-block mt-30">{{ trans('site.filter_items') }}</button>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>

            </form>
          <!--  <div style="display: flex;align-items: center;justify-content: center;" class="mt-30 ">-->
          <!--      <a  id="loadMore" class="btn btn-border-white mb-2" >View More</a>-->
          <!--</div>-->
            <!--<div class="mt-50 pt-30">-->
            {{--    {{ $webinars->appends(request()->input())->links('vendor.pagination.panel') }}--}}
            <!--</div>-->
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
    //   $("#loadMore").click(function(e){
    //     e.preventDefault();
    //     $(".loadid:hidden").slice(0,9).fadeIn("slow");
    //     console.log($(".loadid:hidden").length);
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
