@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">

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
@endpush

@section('content')

    <section class="cart-banner mob-ban search-top-banner opacity-04 position-relative">

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">
                        <h1 class="text-white font-30 mb-15">{{ $title }} </h1>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mob-cat container">

        <div class="mt-35">
            <div class="mob-tab">
                        <ul class=" nav nav-tabs bg-secondary rounded-sm p-15 d-flex align-items-center justify-content-between" id="tabs-tab" role="tablist">
                            <li class="nav-item" style="margin: auto;">
                                <a class="position-relative font-14 text-white {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'active' : '' }}" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false">Astrologers</a>
                            </li>
                            <li class="nav-item" style="margin: auto;">
                                <a  class="position-relative font-14 text-white  {{ (request()->get('tab','') == 'content') ? 'active' : '' }}" id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false">Instructors</a>
                            </li>

                        </ul>
            </div>
    <section>
        @php

            $to_day=date("l");
        @endphp

            <div id="instructorsList" class=" mt-20">

                <div class="tab-content " id="nav-tabContent">
                    <div class="tab-pane fade  {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'reviews') ? 'show active' : '' }}" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                        <div class="row">
                            @foreach($consult as $instructor2)
                                @php
                                    $canReserve2 = false;
                                    if(!empty($instructor2->meeting) and !$instructor2->meeting->disabled and !empty($instructor2->meeting->meetingTimes) and $instructor2->meeting->meeting_times_count > 0) {
                                        $canReserve2 = true;
                                    }

                                     if($canReserve2){

                                @endphp
                                @if($instructor2->consultant == 1)

                                        <div class="col-12 col-md-6 col-lg-4 ">
                                            @include('web.default2.pages.instructor_card1',['instructor' => $instructor2])
                                        </div>
                                        @endif
                                    @php
                                        }
                                    @endphp
                            @endforeach

                            @foreach($consult as $instructor1)
                                @php
                                    $canReserve1 = false;
                                    if(!empty($instructor1->meeting) and !$instructor1->meeting->disabled and !empty($instructor1->meeting->meetingTimes) and $instructor1->meeting->meeting_times_count > 0) {
                                        $canReserve1 = true;
                                    }
                                    if(!$canReserve1){
                                @endphp
                                    @if($instructor1->consultant == 1)
                                    <div class="col-12 col-md-6 col-lg-4 ">
                                        @include('web.default2.pages.instructor_card1',['instructor' => $instructor1])
                                    </div>
                                    @endif
                                     @php
                                     }
                                    @endphp
                            @endforeach
                                </div>

            <br><br>
            <div class="px-20 px-md-0">
        <h3 class="section-title">Why stress
over your concerns when the solution is just a Book away?</h3>
<br>
        <p class="section-hint">Asttrolok connects
you with a diverse team of 100+ astrologers ready to address your problems
through online consultation. Whether it's matters of love, finance, Vastu,
career, luck, marriage, or more, life's journey is a mix of highs and lows.
While we relish the good times, challenges can leave us feeling anxious and
disheartened, affecting our relationships.<br><br>Asttrolok's exceptional astrology consultant services are
designed to provide solutions to the challenges you face in various aspects of
life. Some issues are influenced by cosmic factors determined at birth, such as
specific dashas like Shani Dasha or Rahu Dasha, which can lead to confidence
loss, financial troubles, and relationship woes. Asttrolok offers solutions to
navigate these challenges with the help of experienced astrologers.

<br><br>
Our team consists of knowledgeable astrologers specializing in
Vedic astrology, Numerology, Vastu, and more. Connect with these experts
through online chat to seek guidance and solutions tailored to your unique
situation.
<br>
<br>
Astrology extends beyond problem-solving—it assists in various
life events. Planning a wedding and need an auspicious muhurat? Consult with an
astrologer. Naming your baby or deciding on the right time for their mundan
ceremony? Expert astrologers can guide you. Curious about which gemstone suits
your rashi? An astrologer's advice can help you choose the perfect one.
<br>

Asttrolok offers paid online chat services with astrologers.
Simply search for "online consultation. Our goal is to ensure 100%,
offering services like kundali and match making. Embrace the power of astrology
to navigate life's journey with confidence and clarity.</p>
    </div>
                            </div>

                            <div class=" tab-pane fade  {{ (request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                <div class="row">
                                    @foreach($instructors as $instructor)
                                        @if($instructor->consultant == 0)
                                            <div class="col-12 col-md-6 col-lg-4 ">
                                                @include('web.default2.pages.instructor_card',['instructor' => $instructor])
                                            </div>
                                        @endif
                                    @endforeach

                            </div></div>

                </div>

            </div>

        </section>

                    </div>

        @if(1==2 and !empty($bestRateInstructors) and !$bestRateInstructors->isEmpty() and (empty(request()->get('sort')) or !in_array(request()->get('sort'),['top_rate','top_sale'])))
            <section class="mt-30 pt-30">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="font-24 text-dark-blue">{{ trans('site.best_rated_instructors') }}</h2>
                        <span class="font-14 text-gray">{{ trans('site.best_rated_instructors_subtitle') }}</span>
                    </div>

                    <a href="/{{ $page }}?sort=top_rate" class="btn btn-border-white">{{ trans('home.view_all') }}</a>
                </div>

                <div class="position-relative mt-20">
                    <div id="bestRateInstructorsSwiper" class="swiper-container px-12">
                        <div class="swiper-wrapper pb-20">

                            @foreach($bestRateInstructors as $bestRateInstructor)
                                <div class="swiper-slide">
                                    @include('web.default2.pages.instructor_card',['instructor' => $bestRateInstructor])
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <div class="swiper-pagination best-rate-swiper-pagination"></div>
                    </div>
                </div>

            </section>
        @endif

        @if(1==2 and !empty($bestSalesInstructors) and !$bestSalesInstructors->isEmpty() and (empty(request()->get('sort')) or !in_array(request()->get('sort'),['top_rate','top_sale'])))
            <section class="mt-50 pt-50">
                <div class="d-flex justify-content-between">
                    <div>
                        <h2 class="font-24 text-dark-blue">{{ trans('site.top_sellers') }}</h2>
                        <span class="font-14 text-gray">{{ trans('site.top_sellers_subtitle') }}</span>
                    </div>

                    <a href="/{{ $page }}?sort=top_sale" class="btn btn-border-white">{{ trans('home.view_all') }}</a>
                </div>

                <div class="position-relative mt-20">
                    <div id="topSaleInstructorsSwiper" class="swiper-container px-12">
                        <div class="swiper-wrapper pb-20">

                            @foreach($bestSalesInstructors as $bestSalesInstructor)
                                <div class="swiper-slide">
                                    @include('web.default2.pages.instructor_card',['instructor' => $bestSalesInstructor])
                                </div>
                            @endforeach

                        </div>
                    </div>

                    <div class="d-flex justify-content-center">
                        <div class="swiper-pagination best-sale-swiper-pagination"></div>
                    </div>
                </div>

            </section>
        @endif
    </div>

@endsection

@push('scripts_bottom')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script >
//   $(document).ready(function(){
//   $(".loadid").slice(0,10).show();
//   $("#loadMore").click(function(e){
//     e.preventDefault();
//     $(".loadid:hidden").slice(0,10).fadeIn("slow");

//     if($(".loadid:hidden").length == 0){
//       $("#loadMore").fadeOut("slow");

//       }
//   });
// })
</script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/instructors.min.js"></script>
@endpush
