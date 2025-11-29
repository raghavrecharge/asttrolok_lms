@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">

@endpush

@section('content')

    <section class="cart-banner search-top-banner opacity-04 position-relative">

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 mb-15">Courses</h1>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-30 ">

        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">
            <form action="/classes" method="get" id="filtersForm">

                <div class="row mt-20">
                    <div class="col-12 col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row">
                                <div class="col-12 col-lg-4 mt-20 ">
                                        <div class="webinar-card">
    <figure>
        <div class="image-box">

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

            <div class="webinar-price-box mt-5">
                                                        <span class="real">₹2100</span>

                                                </div>
        </figcaption>
    </figure>
</div>
                                    </div>
                                @foreach($webinars as $webinar)

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

    </script>

@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/categories.min.js"></script>
@endpush
