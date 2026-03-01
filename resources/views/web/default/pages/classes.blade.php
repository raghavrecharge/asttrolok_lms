@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-instructors.css">
    <link rel="stylesheet" href="assets/default/css/category_slider.css">
    <link rel="stylesheet" href="/public/assets/design_1/css/home_mobile_css/index.css">
<link rel="stylesheet" href="/assets/design_1/css/home_mobile_css/style.css">

@endpush

<style>

/* FINAL OVERRIDE: search bar ko chhota karo */
.consult-banner {
 
    width: 100%;
    margin: 12px auto 0;
    padding: 12px 16px;
    border-radius: 16px;

    background-image: url("/assets/design_1/img/instructors/public/tq_31gyia7qxt-7k8g-3600h.png");
    background-size: cover;
    background-position: center;

    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}




.consult-banner-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.consult-banner-line1 {
    font-size: 16px;
    font-weight: 400;
}

.consult-banner-line2 {
    font-size: 20px;
    font-weight: 700;
}

.consult-banner-line3 {
    font-size: 15px;
    font-weight: 400;
}

.consult-banner-line3 .price {
    font-weight: 700;
}

.consult-banner-arrow {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
/* Search bar ke upar thoda space */
.checkbox-button.primary-selected {
    max-width: 380px;
    width: 100%;
    margin: 16px auto 12px;   /* yahi value gap control karegi */
}
.english-slider {
  position: relative;
  overflow: hidden;
  width: 100%;
}

.english-slides-wrapper {
  display: flex;
  transition: transform 0.5s ease;
}

.english-slide {
  flex: 0 0 50%; /* 2 visible */
  box-sizing: border-box;
}

/* dots like screenshot */
.english-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  margin-top: 8px;
}

.english-dot {
  width: 18px;       /* pill effect with border-radius */
  height: 4px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.15);
  transition: background 0.3s, width 0.3s;
}

.english-dot.active {
  width: 26px;
  background: #1fb36a; /* adjust to your green */
}


.consultant-slider {
  position: relative;
  overflow: hidden;
  width: 100%;
}

.consultant-slides-wrapper {
  display: flex;
  transition: transform 0.5s ease;
}

.consultant-slide {
  flex: 0 0 50%; /* 2 visible */
  box-sizing: border-box;
  padding: 0 4px; /* optional spacing between cards */
}
</style>
<style>
.myJoinSwiper { width: 100%; padding: 10px 0; }
.swiper-slide { display: flex; justify-content: center; }
.home-frame10000016141 { width: 100%; padding: 20px; border-radius: 15px; box-shadow: 0px 3px 8px rgba(0,0,0,0.1); }
@media (min-width: 768px) { .home-frame10000016141 { width: 70%; } }

html { line-height: 1.15; scroll-behavior: smooth; font-family: Inter; font-size: 16px; }
body { margin: 0; font-weight: 400; color: var(--dl-color-theme-neutral-dark); background: var(--dl-color-theme-neutral-light); }
* { box-sizing: border-box; border-width: 0; border-style: solid; -webkit-font-smoothing: antialiased; }
p, li, ul, pre, div, h1, h2, h3, h4, h5, h6, figure, blockquote, figcaption { margin: 0; padding: 0; }
button { background-color: transparent; }
button, input, optgroup, select, textarea { font-family: inherit; font-size: 100%; line-height: 1.15; margin: 0; }
a { color: inherit; text-decoration: inherit; }
img { display: block; }

.english-slider {
  position: relative;
  overflow: hidden;
  width: 100%;
}

.english-slides-wrapper {
  display: flex;
  transition: transform 0.5s ease;
}

.english-slide {
  flex: 0 0 50%; /* 2 visible */
  box-sizing: border-box;
}

/* dots like screenshot */
.english-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  margin-top: 8px;
}

.english-dot {
  width: 18px;       /* pill effect with border-radius */
  height: 4px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.15);
  transition: background 0.3s, width 0.3s;
}

.english-dot.active {
  width: 26px;
  background: #1fb36a; /* adjust to your green */
}


.consultant-slider {
  position: relative;
  overflow: hidden;
  width: 100%;
}

.consultant-slides-wrapper {
  display: flex;
  transition: transform 0.5s ease;
}

.consultant-slide {
  flex: 0 0 50%; /* 2 visible */
  box-sizing: border-box;
  padding: 0 4px; /* optional spacing between cards */
}

/* dots */
.consultant-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  margin-top: 8px;
}

.consultant-dot {
  width: 18px;
  height: 4px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.15);
  transition: background 0.3s, width 0.3s;
}

.consultant-dot.active {
  width: 26px;
  background: #1fb36a; /* match your theme */
}

.home-ellipse11 {
  top: 0px;
  left: 0px;
  width: 50px !important;
  height: 50px !important;
  position: relative;
  /* border-color: rgba(231, 231, 231, 1);
  border-style: solid; */
  border-width: 1.2244898080825806px;
}
section.home-categories-section {
    display: flex;
    gap: 0% !important;
    width: 100%;
    justify-content: space-around;
}

.home-english-courses-section {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 5px;
}

.english-slide {
    width: 100%;
}
</style>
@section('content')
<div class="consultation-page-container1">
  <div class="consultation-page-consultation-page px-20">


    @php
        $canReserve = false;
        if(!empty($instructor->meeting) && !$instructor->meeting->disabled && !empty($instructor->meeting->meetingTimes)) {
            $canReserve = true;
        }
    @endphp

    {{-- Black banner --}}
   <div class="consult-banner mb-20">
    <div class="consult-banner-text">
        <div class="consult-banner-line1">
            Build expert skills in Vedic astrology
        </div>
        <div class="consult-banner-line2">
            Learn Astrology
        </div>
        <div class="consult-banner-line3">
          Explore Courses
        </div>
    </div>

    </div>


    {{-- Search bar --}}
   
   @include('web.default.includes.search')
@php
  

   $currentFilter = request()->get('filter') ?? 'live';
    @endphp

    <section class="home-course-filters-section">
       <form id="filtersForm" class="consult-filter" style="display:block;" action="/{{ $page }}" method="get">

        @include('web.default.pages.includes.top_filters')
         </form>
    </section>
    @php
        $hasAnyNonSubFilter = request()->get('categories') || request()->get('hindi') || request()->get('english') || request()->get('recordedclasses') || request()->get('liveClasses') || request()->get('upcomingFilter') || request()->get('free') || request()->get('discount') || request()->get('search');
        $subscriptionOnly = request()->get('subscription') == 'on';
        $showSubscriptions = $subscriptionOnly || !$hasAnyNonSubFilter;
        $showWebinars = !$subscriptionOnly;
    @endphp
    <section class="home-english-courses-section mt-5">
           
          @if($showSubscriptions)
          @foreach($subscriptions as $subscription)
            @if(!empty($subscription))
            @if($subscription->private == 0)
              @include(getTemplate().'.includes.subscription.grid-card',['subscription' => $subscription])
            @endif
            @endif
          @endforeach
          @endif
          @if($showWebinars && !empty($webinars))
            @foreach($webinars as $webinar)
              @include(getTemplate().'.includes.webinar.grid-card',['webinar' => $webinar])
            @endforeach
          @endif

          @if(($showWebinars && $webinars->isEmpty() && !$showSubscriptions) || ($subscriptionOnly && (empty($subscriptions) || $subscriptions->isEmpty())))
            <div class="col-12 mt-20 mb-20 text-center">
                <p class="font-14 text-gray">No courses found. Try changing or removing some filters.</p>
            </div>
          @endif
        
    </section>
</div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/instructors.min.js"></script>
@endpush
