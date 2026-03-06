@extends('web.default'.'.layouts.app')

@push('styles_top')

    <style data-tag="reset-style-sheet">
      html {  line-height: 1.15;}body {  margin: 0;}* {  box-sizing: border-box;  border-width: 0;  border-style: solid;  -webkit-font-smoothing: antialiased;}p,li,ul,pre,div,h1,h2,h3,h4,h5,h6,figure,blockquote,figcaption {  margin: 0;  padding: 0;}button {  background-color: transparent;}button,input,optgroup,select,textarea {  font-family: inherit;  font-size: 100%;  line-height: 1.15;  margin: 0;}button,select {  text-transform: none;}button,[type="button"],[type="reset"],[type="submit"] {  -webkit-appearance: button;  color: inherit;}button::-moz-focus-inner,[type="button"]::-moz-focus-inner,[type="reset"]::-moz-focus-inner,[type="submit"]::-moz-focus-inner {  border-style: none;  padding: 0;}button:-moz-focus,[type="button"]:-moz-focus,[type="reset"]:-moz-focus,[type="submit"]:-moz-focus {  outline: 1px dotted ButtonText;}a {  color: inherit;  text-decoration: inherit;}pre {  white-space: normal;}input {  padding: 2px 4px;}img {  display: block;}details {  display: block;  margin: 0;  padding: 0;}summary::-webkit-details-marker {  display: none;}[data-thq="accordion"] [data-thq="accordion-content"] {  max-height: 0;  overflow: hidden;  transition: max-height 0.3s ease-in-out;  padding: 0;}[data-thq="accordion"] details[data-thq="accordion-trigger"][open] + [data-thq="accordion-content"] {  max-height: 1000vh;}details[data-thq="accordion-trigger"][open] summary [data-thq="accordion-icon"] {  transform: rotate(180deg);}html { scroll-behavior: smooth  }
    </style>
    <style data-tag="default-style-sheet">
      html {
        font-family: Inter;
        font-size: 16px;
      }
      html, body {
    margin: 0;
    padding: 0;
     background-color: #F4FFF2 !important;
}
.frame1000001692-frame1000001690 {
  gap:90px !important ;
}
.buy-btn1 {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    padding: 12px 30px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 90%;
    max-width: 300px;
    background-color: #33ba7c;
    border: none;
    color: white;
}
 .frame1000001692-frame1000001681
{
  width: 100% !important ;
}

/* ── Free-content + locked-content card containers ── */
.frame1000001692-frame1000001680,
.frame1000001692-frame1000001684 {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 8px;
}
.frame1000001692-frame1000001680::-webkit-scrollbar,
.frame1000001692-frame1000001684::-webkit-scrollbar {
  height: 4px;
}
.frame1000001692-frame1000001680::-webkit-scrollbar-thumb,
.frame1000001692-frame1000001684::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 4px;
}

/* Individual video cards inside the containers */
.frame1000001692-frame1000001680 > div,
.frame1000001692-frame1000001684 > .frame1000001692-img3 {
  flex: 0 0 45%;
  min-width: 140px;
  max-width: 200px;
  scroll-snap-align: start;
  position: relative;
}
@media (min-width: 480px) {
  .frame1000001692-frame1000001680 > div,
  .frame1000001692-frame1000001684 > .frame1000001692-img3 {
    flex: 0 0 35%;
    max-width: 220px;
  }
}

.course-detail-back {
    position: fixed;
    top: 15%;
}



.frame1000001692-button5
{
  width: 160px !important;
}
.active-tab{
  border-bottom: 2px solid #45a049 !important;
}
.frame1000001692-text168

 {
  line-height: 8.5px !important;
 }
 
.frame1000001692-frame427322533{
  width: 95% !important;
}
.frame1000001692-frame1000001692
{
  width: 95% !important;
}
      body {
        font-weight: 400;
        font-style:normal;
        text-decoration: none;
        text-transform: none;
        letter-spacing: normal;
        line-height: 1.15;
        color: var(--dl-color-theme-neutral-dark);
        background: var(--dl-color-theme-neutral-light);

        fill: var(--dl-color-theme-neutral-dark);
      }
      .active-tab {
    border-bottom: 2px solid #000;
    font-weight: bold;
}
.frame1000001692-background12 {
    display: flex;
    justify-content: center;   /* horizontal center */
    align-items: center;       /* vertical center */
    text-align: center;
    margin-top: 2px;
}


.roadmap-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #E5E7EB;
    background: #FFFFFF;
    font-size: 12px;
    color: #111827;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
}


.roadmap-table thead th {
    text-align: left;
    padding: 16px 15px;
    font-weight: 600;
    color: #6B7280;
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
}
.roadmap-table tbody tr {
    background: #FFFFFF;
}

.roadmap-table tbody td.month {
    white-space: nowrap;
    font-weight: 600;
}

.roadmap-table tbody td {
    padding: 15px 15px;
    border-bottom: 1px solid #E5E7EB;
    vertical-align: top;
    font-weight: 400;
    color: #374151;
}
.course-detail-pathshala1 {
    filter: none !important;
    -webkit-filter: none !important;
    backdrop-filter: none !important;
}
.frame1000001692-frame4273224891 {
    display: flex;
    flex-direction: column;
    justify-content: center; /* टेक्स्ट को वर्टिकली सेंटर करेगा */
    align-items: flex-start; /* टेक्स्ट को बाएं तरफ रखेगा */
    height: 100px; /* जरूरत के हिसाब से बदलें */
}
.frame1000001692-text183 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    margin-top: 10px; /* या जितना चाहें बढ़ाएं */
}
.frame1000001692-text184 {
    font-size: 16px !important;
    margin-top: auto;
    white-space: nowrap;
    display: inline-block;
    margin-left: -5px; 
}
.cta-auto {
  display: inline-block;     
  width: auto;                
  padding: 8px 18px;         
  white-space: nowrap;     
}
.frame1000001692-frame427322598 {
  display: flex;              /* ✅ fix */
  flex-wrap: wrap;            /* 🔑 wrap allow */
  align-items: center;
  gap: clamp(6px, 3vw, 14px);
}
@media (max-width: 445px) {
  .frame1000001692-container19 {
    flex: 0 0 50%;
  }
}
@media (max-width: 768px) {

  .home-frame427322500 {
    position: relative !important;
    /* left: auto !important; */
    right: auto !important;

    width: 100% !important;
    max-width: 100%;

    margin-inline: auto;
    padding-inline: clamp(12px, 4vw, 20px);
    padding-block: clamp(10px, 3vw, 18px);

    display: flex;
    justify-content: center;
    align-items: center;
    box-sizing: border-box;

    margin-left: clamp(0px, 3vw, 16px) !important;
    width: 100% !important;
    height: auto !important;
    margin: 0 auto;
    justify-content: center;
    
  }
.frame1000001692-container19 {
  display: flex;
  align-items: center;
  gap: 7px;

  flex: 0 0 calc(33.333% - 12px) !important;  /* 👈 3 column */
  box-sizing: border-box;
}

  /* image responsive with clamp */
  .home-vector20 {
    width: clamp(160px, 70vw, 240px);
    height: auto;
    margin: clamp(6px, 2vw, 10px) auto 0;
    display: block;
  }

  /* text responsive */
  .home-text223 {
    font-size: clamp(11px, 3.5vw, 14px);
    line-height: 1.4;
    text-align: center;
    margin-inline: auto;
  }
}
  .home-text223 {
  font-size: clamp(11px, 3.5vw, 14px);
  line-height: 1.4;

  max-width: min(90vw, 520px);   
  margin-inline: auto;

  text-align: center;           
  white-space: normal;         
  word-break: normal;
}

.frame1000001692-frame1000001690 {
  display: flex;
  flex-wrap: wrap; /* 🔑 wrap enable */
  gap: clamp(8px, 3vw, 16px);
}

/* single item */
.frame1000001692-container19 {
  display: flex;
  align-items: center;
  gap: 6px;

  /* 🔥 3 column layout */
  flex: 0 0 calc(33.333% - 12px);
  box-sizing: border-box;
}

/* icon size responsive */
.frame1000001692svg26 {
  width: clamp(16px, 4vw, 20px);
  height: auto;
}

/* text */
.frame1000001692-text272 {
  font-size: clamp(12px, 3.5vw, 14px);
  line-height: 1.3;
}

    </style>
    <link
      rel="stylesheet"
      href="https://unpkg.com/animate.css@4.1.1/animate.css"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=STIX+Two+Text:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap"
      data-tag="font"
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/@teleporthq/teleport-custom-scripts/dist/style.css"
    />
@endpush
{{ session()->put('my_test_key',url()->current()) }}

@section('content')
    <link rel="stylesheet" href="/public/course_detail/style.css" />
    <div>
      <link href="/public/course_detail/index.css" rel="stylesheet" />
      
      

      <div class="frame1000001692-container10 px-10">
       
        <div class="course-detail-group40181"style="">
            <img
              src="{{ config('app.img_dynamic_url') }}{{ $course->getImage() }}"
              alt="{{$course->title}}"
              class="course-detail-pathshala1" style="top:-40px !important;height:245px !important;"
            />
            <!-- <div class="course-detail-overlay-border-overlay-blur">
              <img
                src="/public/course_detail/svg1161-2qfe.svg"
                alt="SVG1161"
                class="course-detail-svg10"
              />
              <img
                src="/public/course_detail/image11161-bgmh-200w.png"
                alt="image11161"
                class="course-detail-image1"
              />
            </div> -->
          </div>
        <div class="frame1000001692-frame1000001692">
          <div class="frame1000001692-frame1000001675 mt-10">
            <span class="frame1000001692-text100">
              <span class="frame1000001692-text101">
                 {{$course->extraDetails->heading_main ?? '' }}
              </span>
              <br />
              <span style="
    color: forestgreen;
    font-size: larger;
">{{$course->extraDetails->heading_sub ?? '' }}</span>
              <br />
              <span>{{$course->extraDetails->heading_extra ?? '' }}</span>
            </span>
            <span class="frame1000001692-text106">
              <span class="frame1000001692-text107">
                {{ $course->extraDetails->additional_description	  ?? '' }}
              </span>
              <br />
              <span style="
    color: forestgreen;
">{{ $course->extraDetails->extra_description  ?? '' }}</span>
            </span>
          </div>
          <div class="frame1000001692-group40183">
            <div class="frame1000001692-background-border1">
              <div class="frame1000001692-container11">
                <div class="frame1000001692-background10"></div>
                <!-- <button class="frame1000001692-button1">
                  <span class="frame1000001692-text110">Add to Cart</span>
                </button>
                <button class="frame1000001692-button2">
                  <span class="frame1000001692-text111">Add to Wishlist</span>
                </button>
                <button class="frame1000001692-button3">
                  <span class="frame1000001692-text112">
                    Join Asttrolok Pathshala Now
                  </span>
                </button> -->
    @php
    $canSale = ($course->canSale() and !$hasBought);
@endphp

<div class="d-flex flex-column">
    <!-- Waitlist Button -->
    @if(!$canSale and $course->canJoinToWaitlist())
        <button type="button" data-slug="{{ $course->slug }}" class="frame1000001692-button3 btn-success {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
            <span class="frame1000001692-text112">
                {{ trans('update.join_waitlist') }}
            </span>
        </button>
    
    <!-- Already Bought - Go to Learning Page -->
    @elseif($hasBought or !empty($course->getInstallmentOrder()))
        <a href="{{ $course->getLearningPageUrl1() }}" class="frame1000001692-button3 btn-success" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
            <span class="frame1000001692-text112">
                {{ trans('update.go_to_learning_page') }}
            </span>
        </a>
    
    <!-- Paid Course Options -->
    @elseif($course->price > 0)
        
        <!-- Subscribe Button -->
        @if($canSale and $course->subscribe)
            <a href="/subscribes/apply/{{ $course->slug }}" class="frame1000001692-button3 btn-outline-primary {{ !$canSale ? 'disabled' : '' }}" style="width:90%; margin-bottom:15px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                <span class="frame1000001692-text112">
                    {{ trans('public.subscribe') }}
                </span>
            </a>
        @endif

        <!-- Buy with Points Button -->
        @if($canSale and !empty($course->points))
            <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} frame1000001692-button3 btn-outline-warning {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow" style="width:90%; margin-bottom:15px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                <span class="frame1000001692-text112">
                    {!! trans('update.buy_with_n_points',['points' => $course->points]) !!}
                </span>
            </a>
        @endif

        <!-- Buy Now Button (Direct Payment) - PEHLE YEH -->
        @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
            <form action="/cart/store" method="post" style="width:90%; margin-bottom:15px;">
                @csrf
                <input type="hidden" name="item_id" value="{{ $course->id }}">
                <input type="hidden" name="item_name" value="webinar_id">
                <button type="submit" class="frame1000001692-button3 btn-success buy_now js-course-direct-payment" style="width:90%;">
                    <span class="frame1000001692-text112">
                        Buy Now
                    </span>
                </button>
            </form>
  <!-- Installment Button - BUY NOW KE BAAD -->
            @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                <a href="/course/{{ $course->slug }}/installments" class="frame1000001692-button3 btn-success installment-button" style="width:90%; top:180px!important;  margin-bottom:15px; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                    <span class="frame1000001692-text112">
                        {{ trans('update.pay_with_installments') }}
                    </span>
                </a>
            @endif
          
        @endif

        <!-- Add to Cart Button - SABSE LAST -->
        <form action="/cart/store" method="post" style="width:90%;">
            @csrf
            <input type="hidden" name="item_id" value="{{ $course->id }}">
            <input type="hidden" name="item_name" value="webinar_id">
            <button type="submit" class="frame1000001692-button1 btn-outline-danger {{ $canSale ? 'js-course-add-to-cart-btn' : ($course->cantSaleStatus($hasBought) .' disabled ') }}" style="width:90%;">
                <span class="frame1000001692-text110">
                    Add to Cart
                </span>
            </button>
        </form>

    <!-- Free Course Options -->
    @else
        @if($course->slug == 'learn-free-vedic-astrology-course-online')
            @if(empty($authUser))
                <a href="{{config('app.manual_base_url')}}/register-free" class="frame1000001692-button3 btn-success {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                    <span class="frame1000001692-text112">
                        {{ trans('public.enroll_on_webinar') }}
                    </span>
                </a>
            @else
                <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="frame1000001692-button3 btn-success {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                    <span class="frame1000001692-text112">
                        {{ trans('public.enroll_on_webinar') }}
                    </span>
                </a>
            @endif
        @elseif($course->slug == 'learn-free-astrology-course-english')
            @if(empty($authUser))
                <a href="{{config('app.manual_base_url')}}/register-free-english" class="frame1000001692-button3 btn-success {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                    <span class="frame1000001692-text112">
                        {{ trans('public.enroll_on_webinar') }}
                    </span>
                </a>
            @else
                <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="frame1000001692-button3 btn-success {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                    <span class="frame1000001692-text112">
                        {{ trans('public.enroll_on_webinar') }}
                    </span>
                </a>
            @endif
        @else
            <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="frame1000001692-button3 btn-success {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}" style="width:90%; text-decoration:none; display:flex; align-items:center; justify-content:center;">
                <span class="frame1000001692-text112">
                    {{ trans('public.enroll_on_webinar') }}
                </span>
            </a>
        @endif
    @endif
</div>
              </div>
              <span class="frame1000001692-text113">Price</span>
              <img
                src="/public/course_detail/svg1176-4e6l.svg"
                alt="SVG1176"
                class="frame1000001692svg10"
              />
              <!-- <span class="frame1000001692-text114">₹2100</span> -->
              @php
                    $displayPrice = handleCoursePagePrice($course->price);
                @endphp

                  @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame1000001692-text114">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame1000001692-text114">{{ $displayPrice['price'] }}  </span>
                    @endif
              @if(!$hasBought)
              <span class="frame1000001692-text115" style="width:30% !important;text-align:left !important;">Pay Now</span>
              @endif
              <img
                src="/public/course_detail/background1177-mhc-200h.png"
                alt="Background1177"
                class="frame1000001692-background11"
              />
              <img
                src="/public/course_detail/horizontaldivider1177-nc6e-400w.png"
                alt="HorizontalDivider1177"
                class="frame1000001692-horizontal-divider1"
              />
              <span class="frame1000001692-text116">Total Amount</span>
              <!-- <span class="frame1000001692-text117">₹2100</span> -->
               @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame1000001692-text117">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame1000001692-text117">{{ $displayPrice['price'] }}  </span>
                    @endif
              <div class="frame1000001692-background12" style="align-items: center; justify-content:center;color:#1964b9">Start at your Ease and Cancel Anytime</div>
            </div>
            <div class="frame1000001692-background13">
              <img
                src="/public/course_detail/svg1178-8ycu.svg"
                alt="SVG1178"
                class="frame1000001692svg11"
              />
              <span class="frame1000001692-text118">
                <!-- <span class="frame1000001692-text119">₹2100 per month</span> -->
                @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame1000001692-text119">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame1000001692-text119">{{ $displayPrice['price'] }}  </span>
                    @endif
                <br />
                <!-- <span>Cancel anytime</span> -->
              </span>
            </div>
          </div>
          {{--<div class="frame1000001692-frame1000001678">
            <div class="frame1000001692-frame1000001677">
              <div class="frame1000001692-background14">
                <img
                  src="/public/course_detail/svg1178-4a9e.svg"
                  alt="SVG1178"
                  class="frame1000001692svg12"
                />
                <span class="frame1000001692-text122">
                  <span>{{ $course->extraDetails->plan_duration_option}}</span>
                  <br />
                  <span>{{ $course->extraDetails->plan_movie}}</span>
                </span>
                <span class="frame1000001692-text126">₹{{ $course->extraDetails->price_suffix}}+</span>
                <img
                  src="{{ asset($course->extraDetails->plan_icon) }}"
                  alt="Vector1179"
                  class="frame1000001692-vector10"
                />
              </div>
              <div class="frame1000001692-background-border-shadow">
                <img
                  src="/public/course_detail/svg1179-w83j.svg"
                  alt="SVG1179"
                  class="frame1000001692svg13"
                />
                <span class="frame1000001692-text127">
                  <span>{{ $course->extraDetails->plan_duration}}</span>
                  <br />
                  <span>{{ $course->extraDetails->plan_type}}</span>
                </span>
                <span class="frame1000001692-text131">{{ $course->extraDetails->plan_cancel_text}}</span>
                <span class="frame1000001692-text132">₹{{ $course->extraDetails->plan_price}}</span>
                <div class="frame1000001692-background15">
                  <span class="frame1000001692-text133">Lifetime Saving</span>
                  <img
                    src="/public/course_detail/border1180-1u9b-200h.png"
                    alt="Border1180"
                    class="frame1000001692-border1"
                  />
                  <img
                    src="/public/course_detail/border1180-mizl-200h.png"
                    alt="Border1180"
                    class="frame1000001692-border2"
                  />
                </div>
              </div>
              <div class="frame1000001692-background-border2">
                <span class="frame1000001692-text134">VS</span>
              </div>
            </div>
            <span class="frame1000001692-text135">
              {{ $course->extraDetails->comparison_text}}
            </span>
          </div>--}}
          <div class="frame1000001692-frame1000001679">
            <div class="frame1000001692-frame10000017001">
              <div class="frame1000001692-frame10000016991">
                <span class="frame1000001692-text136">About This Course</span>
                <img
                  src="/public/course_detail/horizontaldivider1180-c0l-200h.png"
                  alt="HorizontalDivider1180"
                  class="frame1000001692-horizontal-divider2"
                />
              </div>
              <div class="frame1000001692-horizontal-border1" style="width:30% !important;">
                <button class="frame1000001692-button4">
                  <img
                    src="/public/course_detail/svg1181-a6d.svg"
                    alt="SVG1181"
                    class="frame1000001692svg14"
                  />
                  <span class="frame1000001692-text137">About</span>
                  <img
                    src="/public/course_detail/horizontalborder1181-xjac-200h.png"
                    alt="HorizontalBorder1181"
                    class="frame1000001692-horizontal-border2"
                  />
                </button>
                {{--<button class="frame1000001692-button5">
                  <img
                    src="/public/course_detail/svg1181-tq7m.svg"
                    alt="SVG1181"
                    class="frame1000001692svg15"
                  />
                  <span class="frame1000001692-text138" style="color:rgba(50, 160, 40, 1)
!important ;">WHY CHOOSE THIS</span>
                </button>--}}
              </div>
              <div id="tab1" class="tab-pane active">
                <span class="frame1000001692-text139">
                    <span class="frame1000001692-text140">
                    {!! $course->description !!}</span>
                </span>
                </div>
                <div id="tab2" class="tab-pane" style="display:none;">
              <div class="frame1000001692-frame10000016981">
                  @foreach($whyChooseUs as $item)
                <div class="frame1000001692-frame10000016941">
                  <img
                    src="/public/course_detail/svg1181-zupk.svg"
                    alt="SVG1181"
                    class="frame1000001692svg16"
                  />
                  <span class="frame1000001692-text142">
                    <span class="frame1000001692-text143">{{$item->title}}:</span>
                    <span>{{$item->answer}}</span>
                  </span>
                </div>
                           @endforeach  
              </div>
              </div>
            </div>
          </div>
          <script>
    document.querySelector('.frame1000001692-button4').addEventListener('click', function () {
        document.getElementById('tab1').style.display = 'block';
        document.getElementById('tab2').style.display = 'none';

        this.classList.add('active-tab');
        document.querySelector('.frame1000001692-button5').classList.remove('active-tab');
    });

    document.querySelector('.frame1000001692-button5').addEventListener('click', function () {
        document.getElementById('tab1').style.display = 'none';
        document.getElementById('tab2').style.display = 'block';

        this.classList.add('active-tab');
        document.querySelector('.frame1000001692-button4').classList.remove('active-tab');
    });
</script>

         
          @php
    // Collect only FREE video content
    $freeVideos = collect();

    // Add FREE sessions from course
    if(!empty($course->sessions) && $course->sessions->count() > 0) {
        foreach($course->sessions as $session) {
            // Only free sessions
            if(($session->accessibility ?? 'paid') == 'free') {
                $thumbnail = null;
                if(!empty($session->image)) {
                    $thumbnail = $session->image;
                }

                // Get chapter title properly
                $chapterTitle = 'Session';
                if(!empty($session->chapter)) {
                    if(is_object($session->chapter) && isset($session->chapter->title)) {
                        $chapterTitle = $session->chapter->title;
                    } elseif(is_string($session->chapter)) {
                        $chapterTitle = $session->chapter;
                    }
                }

                $freeVideos->push([
                    'type' => 'session',
                    'id' => $session->id,
                    'title' => $session->title,
                    'thumbnail' => $thumbnail,
                    'url' => '/course/'.$course->slug.'/session/'.$session->id,
                    'chapter' => $chapterTitle
                ]);
            }
        }
    }

    // Add FREE video files from course
    if(!empty($course->files) && $course->files->count() > 0) {
        foreach($course->files as $file) {
            if($file->file_type == 'video' || strpos($file->file_type ?? '', 'video') !== false) {
                // Only free files
                if(($file->accessibility ?? 'paid') == 'free') {
                    $thumbnail = null;
                    if(!empty($file->image)) {
                        $thumbnail = $file->image;
                    }

                    // Get chapter title properly
                    $chapterTitle = 'Video';
                    if(!empty($file->chapter)) {
                        if(is_object($file->chapter) && isset($file->chapter->title)) {
                            $chapterTitle = $file->chapter->title;
                        } elseif(is_string($file->chapter)) {
                            $chapterTitle = $file->chapter;
                        }
                    }

                    $freeVideos->push([
                        'type' => 'file',
                        'id' => $file->id,
                        'title' => $file->title,
                        'thumbnail' => $thumbnail,
                        'url' => '/course/'.$course->slug.'/file/'.$file->id,
                        'chapter' => $chapterTitle
                    ]);
                }
            }
        }
    }

    // ✅ Limit to maximum 3 videos
    $freeVideos = $freeVideos->take(3);
@endphp
@if($freeVideos->count() > 0)
    {{-- Mobile: list free videos (keeps the same visual structure as your static mobile markup) --}}
    @foreach($freeVideos as $index => $video)
        @php
            // use index 1/2/3 classes like your original HTML (we'll do 1/2 by modulo; adjust if you need more)
            $n = ($index % 3) + 1;
            // choose thumbnail or fallback
            $thumb = $video['thumbnail'] ?? '/public/course_detail/image21331-atht-200h.png';
            // safe values
            $title = $video['title'] ?? 'Untitled';
            $chapter = $video['chapter'] ?? 'Chapter';
            $url = $video['url'] ?? '#';
            $id = $video['id'] ?? '';
        @endphp
         <div class="frame1000001692-frame1000001681">
            <div class="frame1000001692-frame427322533">
              <span class="frame1000001692-text154" style="width:fit-content;">Free Course Content</span>
              <!-- <img
                src="/public/course_detail/horizontaldivider1183-6sjn-200h.png"
                alt="HorizontalDivider1183"
                class="frame1000001692-horizontal-divider3"
              /> -->
            </div>
            <div class="frame1000001692-frame1000001680">
              <div class="frame1000001692-group{{ 61 + $n }}">
                <a href="{{ $url }}" class="frame1000001692-text161 course-detail-watch-link js-play-video"
                           data-id="{{ $id }}" data-title="{{ $title }}" aria-label="Watch {{ $title }}">
                <div class="frame1000001692-video-cardcom1">
                  <div class="frame1000001692-img1">
                    <img
                      src="/public/public/image21659-jsxk-300w.png"
                      alt="{{ \Illuminate\Support\Str::limit($title, 60) }}"
                      class="frame1000001692-image2{{ $n }}"
                    />
                  </div>
                  <div class="frame1000001692-info1">
                    <div class="frame1000001692-title1">
                      <span class="frame1000001692-text155">
                        <span class="frame1000001692-text156">{{ $title }}</span>
                        <span class="frame1000001692-text157">:</span>
                        <br />
                        <span>{{ $video['type'] === 'session' ? 'Session' : 'Video' }}</span>
                      </span>
                      <span class="frame1000001692-text160">Chapter: {{ $chapter }}</span>
                    </div>
                    <div class="frame1000001692-link10">
                        
                            <span class="frame1000001692-text161">Watch Video</span>
                       
                      <!-- <span class="frame1000001692-text161">Watch Video</span> -->
                    </div>
                  </div>
                </div>
                <div class="frame1000001692-play1">
                  <img
                    src="/public/course_detail/rectangle1i118-sqdt-200h.png"
                    alt="Rectangle1I118"
                    class="frame1000001692-rectangle11"
                  />
                  <img
                    src="/public/course_detail/polygon1i118-utqo.svg"
                    alt="Polygon1I118"
                    class="frame1000001692-polygon11"
                  />
                </div>
              </div>
               </a>
              @endforeach
@endif

              <!-- <div class="frame1000001692-group63">
                <div class="frame1000001692-video-cardcom2">
                  <div class="frame1000001692-img2">
                    <img
                      src="/public/course_detail/image21185-jqf-200h.png"
                      alt="image21185"
                      class="frame1000001692-image22"
                    />
                  </div>
                  <div class="frame1000001692-info2">
                    <div class="frame1000001692-title2">
                      <span class="frame1000001692-text162">
                        <span class="frame1000001692-text163">Aries :</span>
                        <br />
                        <span>Personality Masterclass</span>
                      </span>
                      <span class="frame1000001692-text166">
                        Chapter-Zodiac Sign
                      </span>
                    </div>
                    <div class="frame1000001692-link11">
                      <span class="frame1000001692-text167">Watch Video</span>
                    </div>
                  </div>
                </div>
                <div class="frame1000001692-play2">
                  <img
                    src="/public/course_detail/rectangle1i118-ii9e-200h.png"
                    alt="Rectangle1I118"
                    class="frame1000001692-rectangle12"
                  />
                  <img
                    src="/public/course_detail/polygon1i118-ssed.svg"
                    alt="Polygon1I118"
                    class="frame1000001692-polygon12"
                  />
                </div>
              </div>
               -->
            </div>
          </div>
          <div class="frame1000001692-frame1000001683"style="width:100% !important;">
            <div class="frame1000001692-frame1000001682">
              <span class="frame1000001692-text168">What you will get</span>
              <!-- <img
                src="/public/course_detail/horizontaldivider1186-x23r-200h.png"
                alt="HorizontalDivider1186"
                class="frame1000001692-horizontal-divider4"
              /> -->
              <span class="frame1000001692-text169" style="top:40px !important;">
                What You’ll Learn &amp; Receive Each Month
              </span>
              <span class="frame1000001692-text170 ">
                <span class="frame1000001692-text171 ">
                  Every month unlocks a new set of lessons, assignments, and
                  real-chart practices. 
                </span>
                <span>You’ll receive:</span>
              </span>
            </div>
             @php
                $rawLearnText = $course->extraDetails->learn_text ?? null;
                $learnTexts = $course->extraDetails ? (is_array($rawLearnText) ? $rawLearnText : (json_decode($rawLearnText, true) ?? [])) : [];
                $rawLearnIcon = $course->extraDetails->learn_icon ?? null;
                $learnIcons = $course->extraDetails ? (is_array($rawLearnIcon) ? $rawLearnIcon : (json_decode($rawLearnIcon, true) ?? [])) : [];
            @endphp
            <div class="frame1000001692-group40182" style="width: 100% !important;">
              <img
                src="{{ asset($learnIcons[0] ?? null) }}"
                alt="BackgroundBorder1186"
                class="frame1000001692-background-border3"
              />
              <span class="frame1000001692-text173">
                <span>{!! nl2br(e($learnTexts[0] ?? null)) !!}</span>
              </span>
              <div class="frame1000001692-background-border4">
                <img
                  src="{{ asset($learnIcons[1] ?? null) }}"
                  alt="SVG1187"
                  class="frame1000001692svg17"
                />
              </div>
              <span class="frame1000001692-text177">
                <span>{!! nl2br(e($learnTexts[1] ?? null)) !!}</span>
              </span>
              <img
                src="{{ asset($learnIcons[2] ?? null) }}"
                alt="BackgroundBorder1187"
                class="frame1000001692-background-border5"
              />
              <img
                src="{{ asset($learnIcons[3] ?? null) }}"
                alt="BackgroundBorder1187"
                class="frame1000001692-background-border6"
              />
              <span class="frame1000001692-text181">
                {!! nl2br(e($learnTexts[2] ?? null)) !!}
              </span>
              <span class="frame1000001692-text182">
                {!! nl2br(e($learnTexts[3] ?? null)) !!}
              </span>
            </div>
          </div>
@php
    // Collect all video content directly from course object
    $allVideos = collect();

    // Add sessions from course (exclude free ones)
    if(!empty($course->sessions) && $course->sessions->count() > 0) {
        foreach($course->sessions as $session) {
            // Skip free sessions
            if(($session->accessibility ?? 'paid') == 'free') {
                continue;
            }

            $thumbnail = null;
            if(!empty($session->image)) {
                $thumbnail = $session->image;
            }

            $allVideos->push([
                'type' => 'session',
                'id' => $session->id,
                'title' => $session->title,
                'thumbnail' => $thumbnail,
                'can_view' => $hasBought,
                'url' => '/course/'.$course->slug.'/session/'.$session->id,
                'is_free' => false
            ]);
        }
    }

    // Add video files from course (exclude free ones)
    if(!empty($course->files) && $course->files->count() > 0) {
        foreach($course->files as $file) {
            if($file->file_type == 'video' || strpos($file->file_type ?? '', 'video') !== false) {
                // Skip free files
                if(($file->accessibility ?? 'paid') == 'free') {
                    continue;
                }

                $thumbnail = null;
                if(!empty($file->image)) {
                    $thumbnail = $file->image;
                }

                $allVideos->push([
                    'type' => 'file',
                    'id' => $file->id,
                    'title' => $file->title,
                    'thumbnail' => $thumbnail,
                    'can_view' => $hasBought,
                    'url' => '/course/'.$course->slug.'/file/'.$file->id,
                    'is_free' => false
                ]);
            }
        }
    }

    // Display all videos
    $displayVideos = $allVideos;
@endphp
{{-- MOBILE VIEW --}}

@if($displayVideos->count() > 0)
          
          <div class="frame1000001692-frame1000001684" style="height: auto !important;max-height:335px !important;">
            @php $cssIndex = 1; @endphp

            @foreach($displayVideos as $video)
                @php
                    // Cycle through 1–6 for the img blocks you have (img3..img8)
                    $blockIndex = (($loop->index) % 6) + 3; // 3,4,5,6,7,8
                    // And for ellipse/image/vector classes you already defined
                @endphp

            <div class="frame1000001692-img3">
              <div class="frame1000001692-frame4273224891">
                <span class="frame1000001692-text183">{{ $video['title'] }}</span>
                <div class="frame1000001692-link12">
                  <img
                    src="/public/course_detail/line11188-aaa.svg"
                    alt="Line11188"
                    class="frame1000001692-line11"
                  />
                  @if($video['can_view'])
                  <span class="frame1000001692-text184">Watch Video</span>
                   @else
   <span class="frame1000001692-text184">Enroll to Watch</span>
                   @endif
                </div>
              </div>
              <div class="frame1000001692-group661">
                <img
                  src="/public/course_detail/ellipse161188-bfq-200h.png"
                  alt="Ellipse161188"
                  class="frame1000001692-ellipse161"
                />
                <div class="frame1000001692-frame4273225031">
                  <img
                    src="/public/course_detail/image91189-f27k-200w.png"
                    alt="image91189"
                    class="frame1000001692-image91"
                  />
                </div>
                <img
                  src="/public/course_detail/ellipse151189-ma508-200h.png"
                  alt="Ellipse151189"
                  class="frame1000001692-ellipse151"
                />
              </div>
              <img
                src="/public/course_detail/vector1189-oi1.svg"
                alt="Vector1189"
                class="frame1000001692-vector14"
              />
            </div>
            @endforeach
          </div>
          @endif
@php
    $learningMaterialsExtraDescription = !empty($course->webinarExtraDescription)
        ? $course->webinarExtraDescription->where('type','learning_materials')
        : collect();

    $companyLogosExtraDescription = !empty($course->webinarExtraDescription)
        ? $course->webinarExtraDescription->where('type','company_logos')
        : collect();

    $requirementsExtraDescription = !empty($course->webinarExtraDescription)
        ? $course->webinarExtraDescription->where('type','requirements')
        : collect();

    $count = 0;
@endphp
          @php
                $learningMaterialsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','learning_materials') : null;
                $companyLogosExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','company_logos') : null;
                $requirementsExtraDescription = !empty($course->webinarExtraDescription) ? $course->webinarExtraDescription->where('type','requirements') : null;
                $count = 0;
                @endphp
@if($requirementsExtraDescription->isNotEmpty())
          <div class="frame1000001692-border3">
            <div class="frame1000001692-frame427322558">
              <div class="frame1000001692-frame427322545">
                <img
                  src="{{ asset($course->extraDetails->bonus_icon) }}"
                  alt="image21195"
                  class="frame1000001692-image23"
                />
                <span class="frame1000001692-text198" style="width: 100% !important;">
                  {{$course->extraDetails->bonus_heading	}}
                </span>
              </div>
              <div class="frame1000001692-frame427322557">
                <div class="frame1000001692-frame427322556"style="width:100% !important;">
                @foreach($requirementsExtraDescription as $requirementExtraDescription)
                    @if($count == 0)
                  <div class="frame1000001692-frame427322547"style="width:100% !important;">
                    <div class="frame1000001692-container12">
                      <img
                        src="{{ config('app.img_dynamic_url') }}{{ $requirementExtraDescription->img }}"
                        alt="Background1196"
                        class="frame1000001692-background16"
                      />
                    </div>
                    <div class="frame1000001692-frame427322546">
                      <span class="frame1000001692-text199">
                        {{ $requirementExtraDescription->value }}
                      </span>
                      <span class="frame1000001692-text200" style="width:90% !important;">
                        {{ $requirementExtraDescription->description }}.
                      </span>
                    </div>
                  </div>
                  @else
                  <div class="frame1000001692-horizontal-border3">
                    <div class="frame1000001692-frame427322549">
                      <div class="frame1000001692-container13">
                        <img
                          src="{{ config('app.img_dynamic_url') }}{{ $requirementExtraDescription->img }}"
                          alt="SVG1196"
                          class="frame1000001692svg18"
                        />
                      </div>
                      <div class="frame1000001692-frame427322548"style="width:100% !important;">
                        <span class="frame1000001692-text201">
                          {{ $requirementExtraDescription->value }}
                        </span>
                        <span class="frame1000001692-text202" style="width:100% !important;">
                          {{ $requirementExtraDescription->description }}.
                        </span>
                      </div>
                    </div>
                  </div>
                  @endif
                    @php $count++; @endphp
        
                @endforeach
                </div>
              </div>
            </div>
            <!-- <div class="frame1000001692-link18">
              <span class="frame1000001692-text209">
                Join Asttrolok Pathshala Now
              </span>
            </div> -->
            <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                     @if($hasBought or !empty($course->getInstallmentOrder()))
    <button type="button" 
            class="frame1000001692-link18 btn-success" 
            onclick="window.location.href='{{ $course->getLearningPageUrl1() }}'">
        <span class="frame1000001692-text209">
            Start Learning
        </span>
    </button>
@else
    <button type="button" class="frame1000001692-link18 btn-success buy_now js-course-direct-payment">
        <span class="frame1000001692-text209">
            Buy Now
        </span>
    </button>
@endif
                  </form>
          </div>
@endif

@if($course->teacher->id == 1015)
          <div class="frame1000001692-frame1000001687">
            <div class="frame1000001692-frame1000001686">
              <div class="frame1000001692-frame1000001685">
                <span class="frame1000001692-text210">
                  <span class="frame1000001692-text211">Meet Your Mentor:</span>
                  <span>Alok Khandelwal</span>
                   <img
                  src="/public/course_detail/horizontaldivider1201-7h1-200h.png"
                  alt="HorizontalDivider1201"
                  class="frame1000001692-horizontal-divider5"
                />
                </span>
               
                <span class="frame1000001692-text213">
                  <span class="frame1000001692-text214">
                    Founder of Asttrolok and a renowned Vedic Astrologer, Alok
                    Khandelwal has taught 50,000+ students across 70+ countries.
                    With degrees in Psychology and Economics and two decades of
                    teaching experience, he transforms ancient wisdom into
                    modern, applicable science
                  </span>
                  <br />
                  <br />
                  <span>
                    Astrology is not prediction it’s self-discovery through data
                    and consciousness.
                  </span>
                  <br />
                  <span>- Alok Khandelwal</span>
                  <br />
                  <br />
                </span>
              </div>
              <div class="frame1000001692-frame427322566">
                <div class="frame1000001692-frame427322565">
                  <div class="frame1000001692-frame427322561">
                    <div class="frame1000001692-background17">
                      <img
                        src="/public/course_detail/svg1202-9gn.svg"
                        alt="SVG1202"
                        class="frame1000001692svg22"
                      />
                    </div>
                    <div class="frame1000001692-frame427322560">
                      <span class="frame1000001692-text222">
                        MBA (Marketing)
                      </span>
                      <span class="frame1000001692-text223">
                        MA (Economics)
                      </span>
                    </div>
                  </div>
                  <div class="frame1000001692-frame427322564">
                    <div class="frame1000001692-background18">
                      <img
                        src="/public/course_detail/svg1203-thqu.svg"
                        alt="SVG1203"
                        class="frame1000001692svg23"
                      />
                    </div>
                    <span class="frame1000001692-text224">
                      Founder : Asttrolok
                    </span>
                  </div>
                </div>
                <div class="frame1000001692-frame427322563">
                  <img
                    src="/public/course_detail/vector1203-6r4.svg"
                    alt="Vector1203"
                    class="frame1000001692-vector20"
                  />
                  <div class="frame1000001692-frame427322562">
                    <span class="frame1000001692-text225">Jyotish Bhushan</span>
                    <span class="frame1000001692-text226">Jyotish Ratna</span>
                    <span class="frame1000001692-text227">Jyotish Rishi</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="frame1000001692-group28">
              <img
                src="/public/course_detail/ellipse101204-2ovl-300h.png"
                alt="Ellipse101204"
                class="frame1000001692-ellipse10"
              />
              <img
                src="/public/course_detail/ellipse111204-ld0r-300h.png"
                alt="Ellipse111204"
                class="frame1000001692-ellipse11"
              />
              <img
                src="/public/course_detail/bgremove9ad4f42e00bgremoved176035772783711204-db3i-300w.png"
                alt="bgremove9ad4f42e00bgremoved176035772783711204"
                class="frame1000001692-bgremove9ad4f42e00bgremoved17603577278371"
              />
            </div>
          </div>
 @endif         
          <div class="frame1000001692-frame1000001689">
            <div class="frame1000001692-border4">
              <div class="frame1000001692-frame427322576"style="width: 100% !important;">
                <div class="frame1000001692-frame427322577"style="width: 100% !important;">
                  <div class="frame1000001692-frame427322572"style="width: 100% !important;gap:0px !important">
                    <div class="frame1000001692-frame427322571"style="width:70% !important;">
                      <div class="frame1000001692-frame427322570">
                        <img
                          src="/public/course_detail/svg1205-tjri.svg"
                          alt="SVG1205"
                          class="frame1000001692svg24"
                        />
                        <span class="frame1000001692-text230">Risk Meter</span>
                      </div>
                      <div class="frame1000001692-background19" style="width:90% !important;">
                        <img
                          src="/public/course_detail/background1206-k154-200h.png"
                          alt="Background1206"
                          class="frame1000001692-background20"
                        />
                      </div>
                    </div>
                    <span class="frame1000001692-text231">~ Zero</span>
                  </div>
                  <div class="frame1000001692-frame427322574"style="gap:0px !important;">
                    <div class="frame1000001692-frame427322573">
                      <img
                        src="/public/course_detail/svg1206-fmxj.svg"
                        alt="SVG1206"
                        class="frame1000001692svg25"
                      />
                      <span class="frame1000001692-text232">
                        Risk Level: Zero
                      </span>
                    </div>
                    <span class="frame1000001692-text233">Cancel anytime</span>
                  </div>
                </div>
                <div class="frame1000001692-frame427322470"style="width: 100% !important;">
                  <div class="frame1000001692-frame427322575">
                    <span class="frame1000001692-text234">
                                          {{$course->extradetails->risk_title}}

                    </span>
                    <span class="frame1000001692-text235">
                                        {{$course->extradetails->risk_description}}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
           @php
            // Raw values
            $monthsRaw   = $course->extraDetails->certification_time ?? '[]';
            $focusRaw    = $course->extraDetails->certification_focus ?? '[]';
            $outcomeRaw  = $course->extraDetails->certification_outcome ?? '[]';

            // Function to remove extra quotes and brackets
            $clean = function ($value) {
                return trim($value, "[]\"'");
            };

            // Function to decode or fallback to comma explode
            $parse = function ($raw) use ($clean) {
                $data = is_array($raw) ? $raw : json_decode($raw, true);

                if (!is_array($data)) {
                    $data = array_filter(array_map('trim', explode(',', $raw)));
                }

                // Clean each value
                return array_values(array_map($clean, $data));
            };

            // Parse all fields
            $months  = $parse($monthsRaw);
            $focus   = $parse($focusRaw);
            $outcome = $parse($outcomeRaw);

            // Determine table row count
            $rows = max(count($months), count($focus), count($outcome));
        @endphp
          <div class="frame1000001692-frame1000001691">
            <div class="frame1000001692-frame427322578">
              <span class="frame1000001692-text236">
                {{ $course->extraDetails->certificate_title }}
              </span>
              <table class="roadmap-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Focus</th>
                    <th>Outcome</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < $rows; $i++)
                    <tr>
                        <td class="month">
                            {{ $months[$i] ?? '' }}
                        </td>
                        <td>
                            {{ $focus[$i] ?? '' }}
                        </td>
                        <td>
                            {{ $outcome[$i] ?? '' }}
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
            </div>
            <div class="frame1000001692-frame1000001690">
              <!-- <div class="frame1000001692-link19">
                <span class="frame1000001692-text258">
                  Start Your First Class Free
                </span>
              </div> -->
              <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                     @if($hasBought or !empty($course->getInstallmentOrder()))
    <button type="button" 
            class="frame1000001692-link19 btn-success cta-auto" 
            onclick="window.location.href='{{ $course->getLearningPageUrl1() }}'">
        <span class="frame1000001692-text258">
            Start Learning
        </span>
    </button>
@else
    <button type="button" class="frame1000001692-link19 btn-success buy_now js-course-direct-payment cta-auto">
        <span class="frame1000001692-text258">
            {{ $course->extraDetails->cta_text }}
        </span>
    </button>
@endif
                  </form>
              <div class="frame1000001692-group56">
                <div class="frame1000001692-background21">
                  <div class="frame1000001692-paragraph-background">
                    <img
                      src="/public/course_detail/image31209-s3zh-200h.png"
                      alt="image31209"
                      class="frame1000001692-image3"
                    />
                  </div>
                </div>
                <div class="frame1000001692-background22">
                  <div class="frame1000001692-overlay">
                    <span class="frame1000001692-text259">
                      Carrer in 6 Months
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @if(!empty($course->faqs) and $course->faqs->count() > 0)
          <div class="frame427322615-frame427322593">
            <div class="frame427322615-frame427322592">
              <span class="frame1000001692-text260">
                Frequently Asked Questions
              </span>
              <img
                src="/public/course_detail/horizontaldivider1210-gpn1-200h.png"
                alt="HorizontalDivider1210"
                class="frame1000001692-horizontal-divider7"
              />
            </div>

            </div>

<div class="" style="width: 100%;">
    <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
        @php
        // ✅ Filter only FAQ type items
        $filteredFaqs = $course->faqs->where('type', 'faq');
        $count_faq = 1;
        @endphp
        
        @foreach($filteredFaqs as $faq)
        <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
            <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{$count_faq}}">
                <div href="#collapseFaq{{$count_faq}}" 
                     aria-controls="collapseFaq{{$count_faq}}" 
                     class="frame1000001692-frame427322582 align-items-center justify-content-between" 
                     role="button" 
                     data-toggle="collapse" 
                     data-parent="#accordion" 
                     aria-expanded="true">
                    <span class="frame1000001692-text262">
                        {{ clean(optional($faq)->title,'title') }}
                    </span>
                    <img
                        src="/public/course_detail/buttontogglesectionsvg1212-q875.svg"
                        alt="ButtontogglesectionSVG1212"
                        class="frame1000001692-buttontogglesection-svg1"
                    />
                </div>
            </div>
            <div id="collapseFaq{{$count_faq}}" 
                 aria-labelledby="faq_{{$count_faq}}" 
                 class="collapse" 
                 role="tabpanel">
                <div class="panel-collapse text-gray" style="font-size: 12px;">
                    {{ clean($faq->answer,'answer') }}
                </div>
            </div>
        </div>
        @php
        $count_faq++;
        @endphp
        @endforeach
    </div>
</div>
          </div>
          @endif
          
          <div class="frame1000001692-frame427322594 mt-10" style="width:100% !important;">
            <span class="frame1000001692-text267">Reviews</span>
            <img
              src="/public/course_detail/horizontaldivider1214-67ye-200h.png"
              alt="HorizontalDivider1214"
              class="frame1000001692-horizontal-divider8"
            />
          </div>
          @include('web.default.home.partials._rating_review')
          <div class="frame1000001692-group53">
            <div class="frame1000001692-frame427322599"style="width:100% !important;">
              <span class="frame1000001692-text271">
                {{$course->extraDetails->rate_title	}}
              </span>
              <div class="frame1000001692-frame427322598">
                                @php
              $rawRateTexts = $course->extraDetails->rate_options ?? null;
              $rateTexts = $course->extraDetails ? (is_array($rawRateTexts) ? $rawRateTexts : (json_decode($rawRateTexts, true) ?? [])) : [];
              $rawRateIcons = $course->extraDetails->rate_icon ?? null;
              $rateIcons = $course->extraDetails ? (is_array($rawRateIcons) ? $rawRateIcons : (json_decode($rawRateIcons, true) ?? [])) : [];

              // sirf pehle 3 items
              $rateTexts = array_slice($rateTexts, 0, 5);
              $rateIcons = array_slice($rateIcons, 0, 5);
              @endphp

                  @foreach(array_map(null, $rateTexts, $rateIcons) as [$text, $icon])
                      <div class="frame1000001692-container19">
                            <img
                              src="{{ asset($icon) }}"
                              alt="SVG1422"
                              class="frame1000001692svg26"
                            />
                            <span class="frame1000001692-text272">{!! nl2br(e($text)) !!}</span>
                          </div>
                  @endforeach
              </div>
            </div>
        </div>
          <div class="frame1000001692-background24 mb-10 mt-20">
            <div class="frame1000001692-frame427322601"></div>
            <div class="frame1000001692-frame427322603">
              <div class="frame1000001692-frame427322602">
                <span class="frame1000001692-text277">
                  <span class="frame1000001692-text278" style="font-size: 11px !important;">
                   {{$course->extraDetails->ad_subtitle	}}</span>
                </span>
                <span class="frame1000001692-text280 mt-10"style="width:95% !important;">
                  <span>{{$course->extraDetails->ad_title}}</span>
                </span>
                <div class="frame1000001692-group57 mt-10">
                  <!-- <div class="frame1000001692-link20">
                    <span class="frame1000001692-text284">Enroll Now</span>
                  </div> -->
                  <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $course->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                     @if($hasBought or !empty($course->getInstallmentOrder()))
    <button type="button" 
            class="frame1000001692-link20 btn-success" 
            onclick="window.location.href='{{ $course->getLearningPageUrl1() }}'">
        <span class="frame1000001692-text284">
            Start Learning
        </span>
    </button>
@else
    <button type="button" class="frame1000001692-link20 btn-success buy_now js-course-direct-payment">
        <span class="frame1000001692-text284">
            Enroll Now
        </span>
    </button>
@endif
                  </form>
                  <a href="/contact" style="text-decoration: none;">
                  <div class="frame1000001692-link21"style="@if($hasBought) left: 100px; @endif">
                    <span class="frame1000001692-text285">Know more</span>
                  </div>
                  </a>
                  
                </div>
              </div>
              <span class="frame1000001692-text286 mt-10" style="font-size: 13px !important;">
                Don’t wait Seats for this month’s batch are filling fast!
              </span>
            </div>
          </div>
  @php
    $canSale = ($course->canSale() and !$hasBought);
@endphp

@if($hasBought or !empty($course->getInstallmentOrder()))
    <a href="{{ $course->getLearningPageUrl() }}" 
       class="btn btn-primary btn-sm px-25 buy-btn1"
       style="position: fixed !important; 
              bottom: 70px !important; 
              left: 50% !important; 
              transform: translateX(-50%) !important; 
              z-index: 99999 !important; 
              background-color: rgba(50, 160, 40, 1) !important;
              border: none !important;
              color: white !important;
              width: 70% !important;
              max-width: 250px !important;
              padding: 12px 20px !important;
              font-size: 14px !important;
              font-weight: bold !important;
              border-radius: 8px !important;
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
              text-decoration: none !important; 
              display: flex !important; 
              align-items: center !important; 
              justify-content: center !important;">
        Start Learning
    </a>
@else
    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
        <button type="button" 
                data-toggle="modal" 
                data-target="#buynow_modal" 
                class="btn btn-primary btn-sm px-25 buy-btn1"
                style="position: fixed !important; 
                       bottom: 70px !important; 
                       left: 50% !important; 
                       transform: translateX(-50%) !important; 
                       z-index: 99999 !important;
                       background-color: rgba(50, 160, 40, 1) !important;
                       border: none !important;
                       color: white !important;
                       width: 30% !important;
                       max-width: 250px !important;
                       padding: 12px 20px !important;
                       font-size: 14px !important;
                       font-weight: bold !important;
                       border-radius: 8px !important;
                       box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;">
            BUY NOW
        </button>
    @else
        <button type="button" 
                data-toggle="modal" 
                data-target="#buynow_modal" 
                class="btn btn-primary btn-sm px-25 buy-btn1"
                style="position: fixed !important; 
                       bottom: 70px !important; 
                       left: 50% !important; 
                       transform: translateX(-50%) !important; 
                       z-index: 99999 !important;
                       background-color: rgba(50, 160, 40, 1) !important;
                       border: none !important;
                       color: white !important;
                       width: 30% !important;
                       max-width: 200px !important;
                       padding: 12px 20px !important;
                       font-size: 14px !important;
                       font-weight: bold !important;
                       border-radius: 8px !important;
                       box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;">
            Buy Now
        </button>
    @endif
@endif

        </div>
      </div>
      <link rel="canonical" href="https://untitled-jnw2x3.teleporthq.app/" />
    </div>
    @include('web.default.course.share_modal')
    @include('web.default.course.buy_with_point_modal')
    @include('web.default.course.login_modal')
    @include('web.default.course.buynow_modal')
@endsection

<div class="modal fade " id="playVideo" tabindex="-1" aria-labelledby="playVideoLabel" aria-modal="true" style="display:none;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line">Class 1 - Complete Guide to Aries</h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="mt-25 position-relative">
                <div class="px-20">

                    <div class="js-modal-video-content">
                      <!-- <iframe src="https://iframe.mediadelivery.net/embed/759/eb1c4f77-0cda-46be-b47d-1118ad7c2ffe?autoplay=true" style="width:100%;height:400px;"> </iframe> -->
                      </div>
                </div>

                <div class="modal-video-lists mt-15">

                                    </div>
            </div>
        </div>
    </div>
   {{-- @endforeach --}}
</div>
@push('scripts_bottom')

    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/1212youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/video/vimeo.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/webinar_show.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('option1_btn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        btn.classList.add('loadingbar','danger');
        btn.disabled = true;
        document.getElementById('option1_direct_form').submit();
    });
});

</script>
<script>
    $('#playVideo').on('hidden.bs.modal', function () {
    let iframe = $(this).find('iframe');
    let src = iframe.attr('src');
    iframe.attr('src', '');
    iframe.attr('src', src); // reset so video stops
});
</script>


@php
    Illuminate\Support\Facades\Session::forget('addtocart');
@endphp

    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var reportFailLang = '{{ trans('panel.report_fail') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var canNotTryAgainQuizToastTitleLang = '{{ trans('public.can_not_try_again_quiz_toast_lang') }}';
        var canNotTryAgainQuizToastMsgLang = '{{ trans('public.can_not_try_again_quiz_toast_msg_lang') }}';
        var canNotDownloadCertificateToastTitleLang = '{{ trans('public.can_not_download_certificate_toast_lang') }}';
        var canNotDownloadCertificateToastMsgLang = '{{ trans('public.can_not_download_certificate_toast_msg_lang') }}';
        var sessionFinishedToastTitleLang = '{{ trans('public.session_finished_toast_title_lang') }}';
        var sessionFinishedToastMsgLang = '{{ trans('public.session_finished_toast_msg_lang') }}';
        var sequenceContentErrorModalTitle = '{{ trans('update.sequence_content_error_modal_title') }}';
        var courseHasBoughtStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasBoughtStatusToastMsgLang = '{{ trans('site.you_bought_webinar') }}';
        var courseNotCapacityStatusToastTitleLang = '{{ trans('public.request_failed') }}';
        var courseNotCapacityStatusToastMsgLang = '{{ trans('cart.course_not_capacity') }}';
        var courseHasStartedStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasStartedStatusToastMsgLang = '{{ trans('update.class_has_started') }}';
        var joinCourseWaitlistLang = '{{ trans('update.join_course_waitlist') }}';
        var joinCourseWaitlistModalHintLang = "{{ trans('update.join_course_waitlist_modal_hint') }}";
        var joinLang = '{{ trans('footer.join') }}';
        var nameLang = '{{ trans('auth.name') }}';
        var emailLang = '{{ trans('auth.email') }}';
        var phoneLang = '{{ trans('public.phone') }}';
        var captchaLang = '{{ trans('site.captcha') }}';
    </script>
<script>

</script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/webinar_show.min.js"></script>
    <script type="text/javascript" src="https://asttrolok.in/asttroloknew/assets/design_1/js/app.min.js"></script>
        <script src="https://asttrolok.in/asttroloknew/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
    <script src="https://asttrolok.in/asttroloknew/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="https://asttrolok.in/asttroloknew/assets/design_1/js/parts/range_slider_helpers.min.js"></script>
    <script src="https://asttrolok.in/asttroloknew/assets/design_1/js/parts/swiper_slider.min.js"></script>
@endpush