@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/public/assets/design_1/css/home_mobile_css/index.css">
<link rel="stylesheet" href="/assets/design_1/css/home_mobile_css/style.css">
 <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-remedies.css">
@endpush

<style>
    html, body {
    margin: 0;
    padding: 0;
     background-color: #F4FFF2 !important;
}
/* FINAL OVERRIDE: search bar ko chhota karo */
.consult-banner {
  
    width: 100%;
    margin: 12px auto 0;
    padding: 12px 16px;
    border-radius: 16px;

    /* Image हटाओ, gradient लगाओ */
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
/* Search input ke upar halka gap */
.search-box {
    margin-top: 4px;          /* 4px, 6px, 8px se play kar sakti ho */
    text-align: center !important;  /* jo already chahiye */
}

/* Icons ko same line me rakhne ke liye margin hata do */
.search-btn,
.close-btn {
    margin-top: 0 !important;
}
/* Inner wrapper: icons + input ek line me */
.search-wrapper {
    justify-content: center !important;
}
.search-box {
    width: auto !important;  
    text-align: center !important; /* text center */
}
.search-box {
    text-align: center !important;   /* horizontal center */
}

/* Sirf placeholder ke लिए (कुछ browsers में जरूरत पड़ती है) */
.search-box::placeholder {
    text-align: center;
}
/* Icons left/right fixed width */
.search-btn{
 
    width: 35px;
    height: 35px;
    display: flex;
    align-items: left;
    justify-content: center;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;

}
.close-btn {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    padding: 0;
    margin: 0;
}

.search-btn img,
.close-btn img {
    width: 18px;
    height: 18px;
}

/* Input center me */
.search-box {
    flex: 1;                   /* beech ka area le */
    border: none;
    background: transparent;
    font-size: 14px;
    line-height: 1.2;
    padding: 0;
    margin: 0;
    text-align: center;        /* text + placeholder center */
    outline: none;
}



/* Icons ko bhi flex center */
.search-btn,
.close-btn {
  margin-top: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* 2) पूरे main wrapper के ऊपर कोई extra margin ना रहे */
.consultation-page-container1,
.consultation-page-consultation-page {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* 3) सबसे ऊपर वाले video block का margin कम/0 कर दो */
.consultation-page-ytdrichitemrendererytdrichgridmedia {
    margin-top: 8px !important;   /* चाहो तो 0 भी कर सकती हो */
}

/* 4) Black banner के ऊपर का gap लगभग खत्म */
.consultation-page-frame1000001614 {
    margin-top: 4px !important;   /* अगर बहुत कम चाहिए तो 0 कर दो */
}

/* 5) Search bar के ऊपर भी बहुत space न रहे */
.checkbox-button.primary-selected {
    margin-top: 8px !important;
}
/* ---- BASIC RESET / CONTAINER ---- */

.consultation-page-container1 {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: stretch;
}

.consultation-page-consultation-page {
  width: 100%;
  min-height: 100vh;
  background-color: rgba(244, 255, 241, 1);
}

/* ---- TOP VIDEO SECTION (simple responsive block) ---- */

.consultation-page-ytdrichitemrendererytdrichgridmedia {
  max-width: 380px;
  margin: 16px auto;
  border-radius: 8px;
  overflow: hidden;
}

.consultation-page-ytdthumbnail img {
  width: 100%;
  display: block;
  border-radius: 8px;
}

/* ---- BLACK BANNER ("You can consult with") ---- */

.consultation-page-frame1000001614 {
  max-width: 380px;
  width: 100%;
  margin: 12px auto 0;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid rgba(21, 186, 6, 1);
  background-color: #000;
  color: #fff;
}

.consultation-page-frame1000001613 {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.consultation-page-text249,
.consultation-page-text253 {
  color: #fff;
}

/* ---- SEARCH BAR ---- */

.checkbox-button.primary-selected {
  max-width: 380px;
  width: 100%;
  margin: 12px auto;
}

.consultation-page-frame5 {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  border-radius: 999px;
  border: 1px solid rgba(218, 218, 218, 1);
  background-color: var(--dl-color-default-gray6, #f2f2f2);
}

.search-wrapper {
    display: flex;
    align-items: center;
    justify-content: center; /* Center the content horizontally */
    flex: 1;
}

.search-box {
    
    border: none;
    margin-top: auto;
    background: transparent;
    flex-grow: 1;
    font-size: 15px;
    outline: none;
    text-align: center; /* Center text inside input */
}

.search-btn,
.close-btn {
    border: none;
    background: transparent;
    display: flex;
    align-items: center;
    margin-left: 8px; /* Optional: space between input and icon */
}

    .search-btn img,
    .close-btn img {
        margin-top: 20px;
        width: 18px;
        height: 18px;
    }

/* ---- CARDS GRID: 2 PER ROW ---- */

.consultation-page-frame1000001658 {
  max-width: 380px;
  width: 100%;
  margin: 12px auto 24px;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: space-between;
}

.consultation-page-frame100000163210 {
  flex: 0 0 calc(50% - 6px);
  max-width: calc(50% - 6px);
  box-sizing: border-box;
  display: flex;
  padding: 10px;
  border-radius: 21px;
  background-color: #fff;
  box-shadow: 2px 2px 10px rgba(0,0,0,0.15);
  position: relative;
}

.consultation-page-frame100000163010 {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

.consultation-page-rectangle68010 {
  width: 100%;
  height: 154px;
  object-fit: cover;
  border-radius: 21px;
}

.consultation-page-frame100000162910 {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.consultation-page-text108 {
  font-size: 16px;
  color: #222b45;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
/* Card ko clickable banana - overlay method */
.consultation-page-frame100000163210 {
  flex: 0 0 calc(50% - 6px);
  max-width: calc(50% - 6px);
  box-sizing: border-box;
  display: flex;
  padding: 10px;
  border-radius: 21px;
  background-color: #fff;
  box-shadow: 2px 2px 10px rgba(0,0,0,0.15);
  position: relative;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* Invisible overlay - pure card ko clickable banata hai */
.card-clickable-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 10;
  border-radius: 21px;
}

/* Hover effect */
.consultation-page-frame100000163210:hover {
  transform: translateY(-4px);
  box-shadow: 2px 4px 15px rgba(0,0,0,0.25);
}
.consultation-page-text109 {
  font-size: 12px;
  color: #6a769a;
}

.consultation-page-text115 {
  font-size: 16px;
  color: #008c3a;
}

.consultation-page-frame100000163310 {
  position: absolute;
  right: 10px;
  bottom: 5px;
  width: 25px;
  height: 25px;
}



/* ---- RESPONSIVE TWEAKS ---- */

@media (min-width: 576px) {
  .consultation-page-frame1000001658 {
    max-width: 540px;
  }
}

@media (min-width: 768px) {
  .consultation-page-frame1000001658,
  .checkbox-button.primary-selected,
  .consultation-page-frame1000001614 {
    max-width: 720px;
  }
}
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
    gap: 10px;
}

.english-slide {
    width: 100%;
}

 html,
body {
    /* margin: 2px !important; */
    padding: 6px !important;
    background-color: #F4FFF2 !important;
}
.consult-banner {
  
    width: 100%;
    margin: 12px auto 0;
    padding: 12px 16px;
    border-radius: 16px;

    /* Image हटाओ, gradient लगाओ */
    background-image: url("/assets/design_1/img/instructors/public/tq_31gyia7qxt-7k8g-3600h.png");
    background-size: cover;
    background-position: center;

    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
.img-cover {
    height: auto !important;
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
.home-text165 {
    color: #fff !important;
}
.english-slide {
    margin-bottom: 7px !important;
}
.home-frame1000001631 {

    width: 48% !important;
    padding: 1px !important;
}

@media (min-width: 424px) and (max-width: 660px) {
    .webinar-card.grid-card .image-box {
        margin: 0 !important; 
        height: 0px !important;    
    }
}

</style>
@section('content')

    @if((!empty($webinars) and count($webinars)) or (!empty($products) and count($products)) or (!empty($teachers) and count($teachers)) or (!empty($organizations) and count($organizations) ) or (!empty($remedies) and count($remedies) ) or (!empty($subscriptions) and count($subscriptions) ))
      
    <div class="container">
        

                {{-- 🖤 Black text banner (consult-banner) --}}
                <div class="consult-banner mt-10">
                    <div class="consult-banner-text">
                        <div class="consult-banner-line1">
                            You can consult with
                        </div>
                        <div class="consult-banner-line2">
                            Certified Astrologer
                        </div>
                        <div class="consult-banner-line3">
                            Starting from <span class="price">₹50/-</span> per min.
                        </div>
                    </div>
                </div>

                 @include('web.default.includes.search')
    </div>
        <div class="container">
           
 @if(!empty($subscriptions) and count($subscriptions))
    <section class="mt-100">
        <h2 class="font-24 font-weight-bold text-secondary">Subscriptions</h2>

        <div class="row mt-10">
            @foreach($subscriptions as $subscription)
            @if($subscription->private == 0)
                @include('web.default.includes.subscription.grid-card',['subscription' => $subscription])
            @endif
            @endforeach
        </div>
    </section>
@endif
            @if(!empty($webinars) and count($webinars))
                <section class="mt-100">
                    <h2 class="font-24 font-weight-bold text-secondary">{{ trans('webinars.webinars') }}</h2>

                    <div class="row">
                        @foreach($webinars as $webinar)
                                @include('web.default.includes.webinar.grid-card',['webinar' => $webinar])
                        @endforeach
                    </div>
                </section>
            @endif
            
            @if(!empty($remedies) and count($remedies))
                <section class="mt-50">
                    <h2 class="font-24 font-weight-bold text-secondary">Remedies</h2>

                    <div class="row">
                        @foreach($remedies as $remedy)
                             <div class="col-12 col-lg-4 mt-20 loadid mobilegrid">
                                    @include('web.default.includes.remedy.grid-card',['remedy' => $remedy])
                                </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($products) and count($products))
                <section class="mt-50">
                    <h2 class="font-24 font-weight-bold text-secondary">{{ trans('update.products') }}</h2>

                    <div class="row">
                        @foreach($products as $product)
                            <div class="col-md-6 col-lg-4 mt-30">
                                @include('web.default.products.includes.card',['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

  @if(!empty($teachers) and count($teachers))
    <section class="home-sections home-sections-swiper container position-relative mt-50">
        <div class="row align-items-center mb-20">
            <div class="col-12 text-center">
                <h2 class="font-36 font-weight-bold text-dark">
                    <span style="padding-bottom: 1px; border-bottom:2px solid #32ba7c;">
                        {{ trans('panel.users') }}
                    </span>
                </h2>
            </div>
        </div>

      <div class="consultation-page-frame1000001658">
    @foreach($teachers as $teacher)
        <div class="consultation-page-frame100000163210">
            <a href="{{ $teacher->getProfileUrl() }}" 
               class="card-clickable-overlay"></a>
            <div class="consultation-page-frame100000163010">
                <img
                    src="{{ config('app.img_dynamic_url') }}{{ $teacher->getAvatar(190) }}"
                    alt="{{ $teacher->full_name }}"
                    class="consultation-page-rectangle68010" />
                
                <div class="consultation-page-frame100000162910">
                    <span class="consultation-page-text108" style="font-weight: 700 !important;">
                        {{ $teacher->full_name }}
                    </span>

                    @php
                        $lines = explode("\n", $teacher->bio);
                    @endphp
                    <span class="consultation-page-text109">
                        @foreach($lines as $line)
                            <span>{{ trim($line) }}</span><br>
                        @endforeach
                    </span>

                    <span class="consultation-page-text115">
                        @if(!empty($teacher->meeting) && !$teacher->meeting->disabled && !empty($teacher->meeting->amount))
                            @if(!empty($teacher->meeting->discount))
                                <span class="font-20 font-weight-bold">
                                    {{ handlePrice($teacher->meeting->amount - (($teacher->meeting->amount * $teacher->meeting->discount) / 100)) }}
                                </span>
                                <span class="font-14 text-gray text-decoration-line-through ml-10">
                                    {{ handlePrice($teacher->meeting->amount) }}
                                </span>
                            @else
                                <span class="font-20 text-primary font-weight-500" style="color:#008C3A !important;">
                                    {{ handlePrice($teacher->meeting->amount/30) }}
                                </span>
                                <span class="text-dark-blue font-12"> / Min</span>
                            @endif
                        @else
                            <span class="py-10">&nbsp;</span>
                        @endif
                    </span>
                </div>
            </div>

            <img
                src="assets/design_1/img/instructors/public/frame10000016339457-kp2r.svg"
                alt="Frame10000016339457"
                class="consultation-page-frame100000163310"
                style="width: 40px; height: auto; margin-top: 8px;" />
        </div>
    @endforeach
</div>

    </section>
@endif




            @if(!empty($organizations) and count($organizations))
                <section class="mt-50">
                    <h2 class="font-24 font-weight-bold text-secondary">{{ trans('home.organizations') }}</h2>

                    <div class="row">

                        @foreach($organizations as $organization)
                            <div class="col-md-6 col-lg-3 mt-30">
                                <a href="{{ $organization->getProfileUrl() }}" class="home-organizations-card d-flex flex-column align-items-center justify-content-center">
                                    <div class="home-organizations-avatar">
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $organization->getAvatar() }}" class="img-cover rounded-circle" alt="{{ $organization->full_name }}">
                                    </div>
                                    <div class="mt-25 d-flex flex-column align-items-center justify-content-center">
                                        <h3 class="home-organizations-title">{{ $organization->full_name }}</h3>
                                        <p class="home-organizations-desc mt-10">{{ $organization->bio }}</p>
                                        <span class="home-organizations-badge badge mt-15">{{ $organization->getActiveWebinars(true) }} {{ trans('product.courses') }}</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @else

        <div class="no-result status-failed my-50 d-flex align-items-center justify-content-center flex-column">
            <div class="no-result-logo">
                <img loading="lazy" src="{{ config('app.js_css_url') }}/assets/default/img/no-results/search.png" alt="">
            </div>
            <div class="container">
                <div class="row h-100 align-items-center justify-content-center text-center">
                    <div class="col-12 col-md-9 col-lg-7">
                        <div class="d-flex align-items-center flex-column mt-30 text-center w-100">
                            <h2>{{ trans('site.no_result_search') }}</h2>
                            <p class="mt-5 text-center">{!! trans('site.no_result_search_hint',['search' => request()->get('search')]) !!}</p>

                            <div class="search-input bg-white p-10 mt-20 flex-grow-1 shadow-sm rounded-pill w-100">
                                <form action="/search" method="get">
                                    <div class="form-group d-flex align-items-center m-0">
                                        <input type="text" name="search" class="form-control border-0" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                        <button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
