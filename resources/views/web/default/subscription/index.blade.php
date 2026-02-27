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
.frame1000001692-frame10000016981 {
  width:100% !important;
}
.frame1000001692-frame1000001690{
  gap:130px !important;
}
.frame1000001692-text127{
  width: 70% !important;
}
.frame1000001692-text131 {
    width: 60% !important;
}
.frame1000001692-text126 {
    width: 46% !important;
}
.frame1000001692-vector10 {
  width: 20% !important;
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
/* Parent container ke liye */
.frame1000001692-frame10000016981 {
  display: flex;
  flex-direction: column;
  gap: 16px; /* spacing between items */
}

/* Individual item containers ke liye */
.frame1000001692-frame10000016941,
.frame1000001692-frame10000016951,
.frame1000001692-frame10000016961,
.frame1000001692-frame10000016971 {
  display: flex;
  flex-direction: row;
  align-items: flex-start; /* Icons ko top/start se align karega */
  gap: 12px; /* Icon aur text ke beech spacing */
}

/* Icons ko fixed width de sakte hain consistency ke liye */
.frame1000001692svg16,
.frame1000001692-vector11,
.frame1000001692-vector12,
.frame1000001692-vector13 {
  width: 24px; /* ya jo bhi size chahiye */
  height: 24px;
  flex-shrink: 0; /* Icon shrink nahi hoga */
  align-self: flex-start; /* Extra assurance ke liye */
}
.frame1000001692svg16{
  height: 25px !important;
}
.frame1000001692-frame1000001683
{
  width: 100% !important;
  height: 120px !important;
}
.roadmap-table{
  font-size: 14px !important;
}
.frame1000001692-frame1000001677{
  top: 20px !important;
}
.frame1000001692-button5
{
  width: 160px !important;
}
.active-tab{
  border-bottom: 2px solid #45a049 !important;
}
.frame1000001692-frame1000001692 {
  gap:8px !important ;
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
.frame1000001692-frame427322594 {
  width: 100% !important;
  margin-top: 20px !important;
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
.frame1000001692-background-border1 {
      height: 248.205841px !important
}
.frame1000001692-group40183 {
   
    height: 243.205841px !important;
}
.frame1000001692-text168

 {
  line-height: 8.5px !important;
 }
 .frame1000001692-frame1000001681
{
  width: 100% !important ;
}
.course-detail-back {
    position: fixed;
    top: 15%;
}


.frame427322615usp .frame427322615-certification {
    padding: 6px 10px !important;
    transform: scale(0.85);   /* Box overall छोटा */
    transform-origin: top left;
    align-items: center;
}

.frame427322615usp .frame427322615-text113 {
    font-size: 20px !important; /* Text छोटा */
}

.frame427322615-certification .frame427322615-text113 {
    display: block;        /* Normal block */
    overflow: visible;     /* Text fully visible */
    text-overflow: unset;  /* No ... */
    white-space: normal;   /* Allow multiple lines */
    line-height: 20px;     /* Optional: better readability */
    min-height: auto;      /* Auto height */
    max-height: none;      /* No clamp */
}

.frame427322615-item4 {
  display: flex;
  align-items: flex-start;  /* Image top pe rahe */
  gap: 10px;
}

.frame427322615-text147 {
  white-space: nowrap;   /* Second span line break nahi lega */
  display: inline-flex;
  gap: 5px;
}

/* Second span ka font weight 400 */
.frame427322615-text147 span:last-child {
  font-weight: 400 !important;
}
/* Second span ka font weight fix */
.frame427322615-text147 span:last-child {
  font-weight: 400 !important;
}
 .navbar-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;         /* same padding for both */
        border-bottom: 2px solid transparent; /* default underline invisible */
    }

    .navbar-item.active {
        border-bottom: 2px solid rgba(50,160,40,1); /* green underline */
    }
 .roadmap-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid #E5E7EB;
    background: #FFFFFF;
    font-size: 16px;                         /* thoda bada text */
    color: #111827;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
}



.roadmap-table tbody td {
    padding: 24px 24px;                      /* row height ~ Figma jaisi */
    border-bottom: 1px solid #E5E7EB;
    vertical-align: top;
    font-weight: 400;
    color: #374151;
}

.roadmap-table tbody tr:last-child td {
    border-bottom: none;
}

.roadmap-table tbody td.month {
    white-space: nowrap;
    font-weight: 600;
}


.roadmap-table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 16px;                 /* rounded corners */
    overflow: hidden;                    /* radius apply hone ke लिए */
    border: 1px solid #E5E7EB;
    background: #FFFFFF;
    font-size: 15px;
    color: #111827;

    /* halka shadow – card feel */
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
}
.roadmap-table tbody td {
    padding: 22px 20px;                  /* pehle 18px tha, ab 22px */
    border-bottom: 1px solid #E5E7EB;
    vertical-align: top;
    font-weight: 400;
    color: #374151;
}
.frame427322615-frame427322593{
  width: 100% !important;
}
.frame427322615-frame427322543{
    width: 100% !important;
}
.frame1000001692-frame1000001683{
  width: 100% !important;
}
.roadmap-table tbody td {
    padding: 28px 20px;
}

.roadmap-table-wrapper {
    margin-top: 16px;
    margin-bottom: 24px;
}
.roadmap-table thead th {
    text-align: left;
    padding: 16px 24px;                      /* header height */
    font-weight: 600;
    color: #6B7280;
    background: #F9FAFB;
    border-bottom: 1px solid #E5E7EB;
}

.frame427322615-text189 {
    top: 23px !important;
}

.frame427322615-text183 {
    top: 8px !important;
}

.roadmap-table tbody tr {
    background: #FFFFFF;         /* rows pure white */
}

.roadmap-table tbody td {
    padding: 18px 20px;
    border-bottom: 1px solid #E5E7EB;  /* row separator same grey */
    vertical-align: top;
    font-weight: 400;
    color: #374151;                     /* gray-700 body text */
}

.roadmap-table tbody tr:last-child td {
    border-bottom: none;
}

.roadmap-table tbody td.month {
    white-space: nowrap;
    font-weight: 600;                    /* bold month */
}
.frame1000001692-frame1000001682 {
  margin-top: 0px !important;
}

.frame427322615-group64 {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* 2 column */
    grid-gap: 10px; /* spacing optional */
}

.frame1000001692-text168 {
  top: 28px !important;
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

  max-width: min(90vw, 520px);   /* 👈 wrap yahin se hoga */
  margin-inline: auto;

  text-align: center;            /* overall center */
  white-space: normal;           /* wrap allow */
  word-break: normal;
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
  flex-wrap: wrap; 
  gap: clamp(8px, 3vw, 16px);
}

/* single item */
.frame1000001692-container19 {
  display: flex;
  align-items: center;
  gap: 6px;

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
       <link href="/asttroloknew/index.css" rel="stylesheet" />
      

      <div class="frame1000001692-container10 px-10">
        <div class="course-detail-group40181 ">
            <img
              src="{{ config('app.img_dynamic_url') }}{{ $subscription->getImage() }}"
              alt="{{$subscription->title}}"
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
                 {{$subscription->extraDetails->heading_main ?? '' }}
              </span>
              <br />
              <span style="
    color: forestgreen;
    font-size: larger;
">{{$subscription->extraDetails->heading_sub ?? '' }}</span>
              <br />
              <span>{{$subscription->extraDetails->heading_extra ?? '' }}</span>
            </span>
            <span class="frame1000001692-text106">
              <span class="frame1000001692-text107" style="font-size: 13px;">
                {{ $subscription->extraDetails->additional_description	  ?? '' }}
              </span>
              <br />
              <span style="
    color: forestgreen; font-size: 13px;
">{{ $subscription->extraDetails->extra_description  ?? '' }}</span>
            </span>
          </div>
          <div class="frame1000001692-group40183">
            <div class="frame1000001692-background-border1">
              <div class="frame1000001692-container11" style="">
                <div class="frame1000001692-background10" style="display:none;"></div>
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
                    $canSale = ($subscription->canSale() and !$hasBought);
                @endphp
                
                <!-- Join Button -->
                @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                    
                        <form action="/cart/store" method="post" style="flex:1 1 0;">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $subscription->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">
                            <a href="/subscriptions/direct-payment/{{ $subscription->slug }}"
           style="text-decoration:none;display:block;"> 
                            <button type="button" class="frame1000001692-button3 mt-10 btn-success buy_now js-course-direct-payment" style="display:none;">
                                <span class="frame1000001692-text112" >
                            {{ $subscription->extraDetails->cta_text }}

                                </span>
                            </button>
                            </a>
                        </form>
                
@endif
                <!-- Add to Cart Button -->
                {{--<form action="/cart/store" method="post" style="flex:1 1 0;">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $subscription->id }}">
                    <input type="hidden" name="item_name" value="webinar_id">
                    <button type="submit" class="frame1000001692-button1 btn-outline-danger">
                        <span class="frame1000001692-text110">
                            Add to Cart
                        </span>
                    </button>
                </form>--}}
              </div>
              <span class="frame1000001692-text113">Price</span>
              <img
                src="/public/course_detail/svg1176-4e6l.svg"
                alt="SVG1176"
                class="frame1000001692svg10"
              />
              <!-- <span class="frame1000001692-text114">₹2100</span> -->
              @php
                    $displayPrice = handleCoursePagePrice($subscription->price);
                @endphp

                  @if(!empty($activeSpecialOffer))
                       @php
                                                $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame1000001692-text114">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame1000001692-text114">{{ $displayPrice['price'] }}  </span>
                    @endif
<a href="/subscriptions/direct-payment/{{ $subscription->slug }}" style="text-decoration: none; color: inherit;">
    <span class="frame1000001692-text115" style="width:30% !important; text-align:left !important;">
        Pay Now
    </span>
</a>              <img
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
                                                $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
                                            @endphp
                                              <span id="priceWithDiscount"
                                                  class="frame1000001692-text117">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>
                      @else
                    <span class="frame1000001692-text117" style="right: 20px;">{{ $displayPrice['price'] }}  </span>
                    @endif
              <div class="frame1000001692-background12" style="align-items: center; justify-content:center;color:#1964b9">Start at your Ease and Cancel Anytime</div>
            </div>
            
            @if($hasBought)
              <a href="{{ $subscription->getLearningPageUrl() }}" style="text-decoration: none; color: inherit;">
            <div class="frame1000001692-background13" style="align-items: center; justify-content:center;">
              <img
                src="/public/course_detail/svg1178-8ycu.svg"
                alt="SVG1178"
                class="frame1000001692svg11" style="display:none;"
              />
              <style>
                .old-price {
                      font-style: italic;
                      font-weight: normal;
                      font-size: 16px;
                      text-decoration: line-through;
                      color: #fff; 
                      opacity: 0.85;
                      margin-left: 15px;
                  }
                  .new-price {
                      font-weight: 600;
                  }
              </style>
            
              <span class="frame1000001692-text118" style="width:auto !important; text-align:left !important; left:auto !important;">
                <!-- <span class="frame1000001692-text119">₹2100 per month</span> -->
                @if(!empty($activeSpecialOffer))
                       @php
                              $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
                              
                        @endphp
                        <span id="priceWithDiscount"
                            class="frame1000001692-text119">
                          {{ $priceWithDiscount['price'] }} Per Month  <span class="old-price">₹5999 /-</span>
                      </span>
                      @else
                    {{-- <span class="frame1000001692-text119 ">{{ $displayPrice['price'] }} Per Month  <span class="old-price">₹5999 /-</span></span> --}} 

                    <span class="frame1000001692-text119 ">Start Learning</span>
                    
                    @endif
                <br />
                
                <!-- <span>Cancel anytime</span> -->
              </span>
            </div>
</a>
@else            
            
            
            
            
            
            <a href="/subscriptions/direct-payment/{{ $subscription->slug }}" style="text-decoration: none; color: inherit;">
            <div class="frame1000001692-background13" style="align-items: center; justify-content:center;">
              <img
                src="/public/course_detail/svg1178-8ycu.svg"
                alt="SVG1178"
                class="frame1000001692svg11" style="display:none;"
              />
              <style>
                .old-price {
                      font-style: italic;
                      font-weight: normal;
                      font-size: 16px;
                      text-decoration: line-through;
                      color: #fff; 
                      opacity: 0.85;
                      margin-left: 15px;
                  }
                  .new-price {
                      font-weight: 600;
                  }
              </style>
            
              <span class="frame1000001692-text118" style="width:auto !important; text-align:left !important; left:auto !important;">
                <!-- <span class="frame1000001692-text119">₹2100 per month</span> -->
                @if(!empty($activeSpecialOffer))
                       @php
                              $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
                              
                        @endphp
                        <span id="priceWithDiscount"
                            class="frame1000001692-text119">
                          {{ $priceWithDiscount['price'] }} Per Month  <span class="old-price">₹5999 /-</span>
                      </span>
                      @else
                    {{-- <span class="frame1000001692-text119 ">{{ $displayPrice['price'] }} Per Month  <span class="old-price">₹5999 /-</span></span> --}} 

                    <span class="frame1000001692-text119 ">{{ $subscription->extraDetails->cta_text }}</span>
                    
                    @endif
                <br />
                
                <!-- <span>Cancel anytime</span> -->
              </span>
            </div>
</a>
@endif
          </div>
          <div class="frame1000001692-frame1000001678">
            <div class="frame1000001692-frame1000001677">
              <div class="frame1000001692-background14">
                <img
                  src="/public/course_detail/svg1178-4a9e.svg"
                  alt="SVG1178"
                  class="frame1000001692svg12"
                />
                <span class="frame1000001692-text122">
                  <span>{{ $subscription->extraDetails->plan_duration_option}}</span>
                  <br />
                  <span>{{ $subscription->extraDetails->plan_movie}}</span>
                </span>
                <span class="frame1000001692-text126">₹{{ $subscription->extraDetails->price_suffix}}+</span>
                <img
                  src="{{ asset($subscription->extraDetails->plan_icon) }}"
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
                  <span>{{ $subscription->extraDetails->plan_duration}}</span>
                  <br />
                  <span>{{ $subscription->extraDetails->plan_type}}</span>
                </span>
                <span class="frame1000001692-text131">{{ $subscription->extraDetails->plan_cancel_text}}</span>
                <span class="frame1000001692-text132">₹{{ $subscription->extraDetails->plan_price}}</span>
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
                <span class="frame1000001692-text134"style="left:5px !important;">VS</span>
              </div>
            </div>
            <span class="frame1000001692-text135" style="top:140px !important;">
              {{ $subscription->extraDetails->comparison_text}}
            </span>
          </div>
          <div class="frame1000001692-frame1000001679 mt-15">
            <div class="frame1000001692-frame10000017001">
              <div class="frame1000001692-frame10000016991">
                <span class="frame1000001692-text136">About This Course</span>
                <img
                  src="/public/course_detail/horizontaldivider1180-c0l-200h.png"
                  alt="HorizontalDivider1180"
                  class="frame1000001692-horizontal-divider2"
                />
              </div>
              <div class="frame1000001692-horizontal-border1" style="width: 100% !important;">
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
                <button class="frame1000001692-button5">
                  <img
                    src="/public/course_detail/svg1181-tq7m.svg"
                    alt="SVG1181"
                    class="frame1000001692svg15"
                  />
                  <span class="frame1000001692-text138" style="color:rgba(50, 160, 40, 1)
!important ;">WHY CHOOSE THIS</span>
                </button>
              </div>
              <div id="tab1" class="tab-pane active" style="width: 100% !important;">
                <span class="frame1000001692-text139">
                    <span class="frame1000001692-text140">
                    {!! $subscription->description !!}</span>
                </span>
                </div>
                <div id="tab2" class="tab-pane" style="display:none;">
               <span class="frame427322615-text195" style="display:block; margin-bottom:4px;">
  Why Opt for the Monthly Subscription:
</span>
<span class="frame1000001692-text171" style="display:block; line-height:1.5;">
  Enjoy affordable, flexible learning with ongoing access to updated content and personalized mentorship,
  all without long-term commitment. Learn at your own pace while progressing from beginner to expert
</span>
                    <div class="frame1000001692-frame10000016981 mt-20">
                      <div class="frame1000001692-frame10000016941">
                        <img
                          src="/public/course_detail/svg1181-zupk.svg"
                          alt="SVG1181"
                          class="frame1000001692svg16"
                        />
                        <span class="frame1000001692-text142">
                          <span class="frame1000001692-text143">Affordable & Flexible:</span>
                          <span style="font-weight:400 !important;width:100% !important;">Pay monthly, with no large upfront fees.</span>
                        </span>
                      </div>
                      <div class="frame1000001692-frame10000016951">
                        <img
                          src="/public/course_detail/vector1182-z0a.svg"
                          alt="Vector1182"
                          class="frame1000001692svg16"
                        />
                        <span class="frame1000001692-text145">
                          <span class="frame1000001692-text146">Ongoing Learning:</span>
                          <span style="font-weight:400 !important;width:100% !important;">Access updated content and new modules regularly.</span>
                        </span>
                      </div>
                      <div class="frame1000001692-frame10000016961">
                        <img
                          src="/public/course_detail/vector1182-p3ue.svg"
                          alt="Vector1182"
                          class="frame1000001692svg16"
                        />
                        <span class="frame1000001692-text148">
                          <span class="frame1000001692-text149">Self-paced:</span>
                          <span style="font-weight:400 !important;width:100% !important;">
                                    Learn at your own speed with no pressure</span>
                        </span>
                      </div>
                      <div class="frame1000001692-frame10000016971">
                        <img
                          src="/public/course_detail/vector1182-zg9g.svg"
                          alt="Vector1182"
                          class="frame1000001692svg16"
                        />
                        <span class="frame1000001692-text151" style="width:100% !important;">
                          <span class="frame1000001692-text152">Scalable:</span>
                          <span style="font-weight:400 !important;width:100% !important;">Progress from beginner to advanced at your own pace</span>
                        </span>
                      </div>
                      <div class="frame1000001692-frame10000016971">
                        <img
                          src="/public/course_detail/vector1182-zg9g.svg"
                          alt="Vector1182"
                         class="frame1000001692svg16"
                        />
                        <span class="frame1000001692-text151"style="width:100% !important;">
                          <span class="frame1000001692-text152">No Long-Term Commitment:</span>
                          <span style="font-weight:400 !important;width:100% !important;">
                                  Pause or cancel anytime</span>
                        </span>
                      </div>
                     <div class="frame1000001692-frame10000016971"> 
  <img
    src="/public/course_detail/vector1182-zg9g.svg"
    alt="Vector1182"
    class="frame1000001692svg16"
  
  />
  <span class="frame1000001692-text151">
    <span class="frame1000001692-text152">Exclusive Mentorship:</span>
    <span style="font-weight:400 !important;width:100% !important;">Continuous access to personalized guidance</span>
  </span>
</div>

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
     
        <div style="width:100%;">
          @include('web.default'.'.subscription.newtab.freecontent')
        </div>

         <div class="frame1000001692-frame1000001683"> 
  <div class="frame1000001692-frame1000001682">
    <span class="frame1000001692-text168">What you will get</span>
    <!-- <img
      src="/public/course_detail/horizontaldivider1186-x23r-200h.png"
      alt="HorizontalDivider1186"
      class="frame1000001692-horizontal-divider4"
    /> -->
    <span class="frame1000001692-text169 mt-10">
      What You’ll Learn &amp; Receive Each Month
    </span>
    <span class="frame1000001692-text170">
      <span class="frame1000001692-text171">
        Every month unlocks a new set of lessons, assignments, and
        real-chart practices
      </span>
      <span>You’ll receive:</span>
    </span>
  </div>
 
  
 
 @include('web.default'.'.subscription.newtab.content')
 
          @php
                $learningMaterialsExtraDescription = !empty($subscription->webinarExtraDescription) ? $subscription->webinarExtraDescription->where('type','learning_materials') : null;
                $companyLogosExtraDescription = !empty($subscription->webinarExtraDescription) ? $subscription->webinarExtraDescription->where('type','company_logos') : null;
                $requirementsExtraDescription = !empty($subscription->webinarExtraDescription) ? $subscription->webinarExtraDescription->where('type','requirements') : null;
                $count = 0;
                @endphp
   <div class="frame1000001692-border3 mt-10">
    <div class="frame1000001692-frame427322558">
        <div class="frame1000001692-frame427322545">
            <img
                src="/public/public/image22871-und-200h.png"
                alt="Why Students Trust Asttrolok"
                class="frame1000001692-image23"
            />
            <span class="frame1000001692-text198"style="width:70% !important;">
                Why Students Across 70 Countries Trust Asttrolok
            </span>
        </div>

        <div class="frame1000001692-frame427322557">
            <div class="frame1000001692-frame427322556" style="width:100% !important;">

                {{-- 1: Affordable & Flexible --}}
                <div class="frame1000001692">
                    <div class="frame1000001692-frame427322549">
                        <div class="frame1000001692-container13">
                            <img
                                src="/public/public/svg2871-if4v.svg"
                                alt="Affordable & Flexible"
                                class="frame1000001692svg18"
                            />
                        </div>
                        <div class="frame1000001692-frame427322548" style="width: 100% !important;">
                            <span class="frame1000001692-text201">
                                Affordable & Flexible
                            </span>
                            <span class="frame1000001692-text202" style="width: 90% !important;">
                                Study under Alok Khandelwal and gain authentic, practical astrology knowledge.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 2: Structured Learning --}}
                <div class="frame1000001692-horizontal-border3">
                    <div class="frame1000001692-frame427322549">
                        <div class="frame1000001692-container13">
                            <img
                                src="/public/public/svg2872-zls6.svg"
                                alt="Structured Learning"
                                class="frame1000001692svg18"
                            />
                        </div>
                        <div class="frame1000001692-frame427322548"style="width: 100% !important;">
                            <span class="frame1000001692-text201">
                                Structured Learning
                            </span>
                            <span class="frame1000001692-text202"style="width: 90% !important;">
                                Step-by-step modules that build your confidence.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 3: Expert Mentorship --}}
                <div class="frame1000001692-horizontal-border3">
                    <div class="frame1000001692-frame427322549">
                        <div class="frame1000001692-container13">
                            <img
                                src="/public/public/svg2872-kdgf.svg"
                                alt="Expert Mentorship"
                                class="frame1000001692svg18"
                            />
                        </div>
                        <div class="frame1000001692-frame427322548"style="width: 100% !important;">
                            <span class="frame1000001692-text201">
                                Expert Mentorship
                            </span>
                            <span class="frame1000001692-text202"style="width: 90% !important;">
                                Direct guidance from Alok Khandelwal and team.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 4: Global Community --}}
                <div class="frame1000001692-horizontal-border3">
                    <div class="frame1000001692-frame427322549">
                        <div class="frame1000001692-container13">
                            <img
                                src="/public/public/svg2872-i4.svg"
                                alt="Global Community"
                                class="frame1000001692svg18"
                            />
                        </div>
                        <div class="frame1000001692-frame427322548"style="width: 100% !important;">
                            <span class="frame1000001692-text201">
                                Global Community
                            </span>
                            <span class="frame1000001692-text202"style="width: 90% !important;">
                                Learn alongside 700+ active students worldwide.
                            </span>
                        </div>
                    </div>
                </div>

                {{-- 5: Career Ready --}}
                <div class="frame1000001692-horizontal-border3">
                    <div class="frame1000001692-frame427322549">
                        <div class="frame1000001692-container13">
                            <img
                                src="/public/public/svg2871-if4v.svg"
                                alt="Career Ready"
                                class="frame1000001692svg18"
                            />
                        </div>
                        <div class="frame1000001692-frame427322548"style="width:100% !important;">
                            <span class="frame1000001692-text201">
                                Career Ready
                            </span>
                            <span class="frame1000001692-text202"style="width:90% !important;">
                                Start consulting professionally within 6 months.
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Button: Start One‑Month Free Trial / Join Pathshala --}}
    <div class="frame1000001692-link18">
        @if($hasBought)
        <a href="{{ $subscription->getLearningPageUrl() }}"
           style="text-decoration:none;display:block;"> 
            <span class="frame1000001692-text209">
                           Start Learning
            </span>
        </a>
        @else
       <a href="/subscriptions/direct-payment/{{ $subscription->slug }}"
           style="text-decoration:none;display:block;"> 
            <span class="frame1000001692-text209">
                            {{ $subscription->extraDetails->cta_text }}
            </span>
        </a>
        @endif
    </div>
</div>
          <div class="frame1000001692-frame1000001687 mt-10">
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
          <div class="frame1000001692-frame1000001689">
           
            <div class="frame1000001692-border4">
              <div class="frame1000001692-frame427322576"style="width: 100% !important;">
                <div class="frame1000001692-frame427322577" style="width: 100% !important;">
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
                      <div class="frame1000001692-background19"style="width:90% !important;">
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
                <div class="frame1000001692-frame427322470" style="width: 100% !important;">
                  <div class="frame1000001692-frame427322575">
                    <span class="frame1000001692-text234">
                     {{$subscription->extradetails->risk_title}}
                    </span>
                    <span class="frame1000001692-text235">{{$subscription->extradetails->risk_description}}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
           @php
            // Raw values
            $monthsRaw   = $subscription->extraDetails->certification_time ?? '[]';
            $focusRaw    = $subscription->extraDetails->certification_focus ?? '[]';
            $outcomeRaw  = $subscription->extraDetails->certification_outcome ?? '[]';

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
                {{ $subscription->extraDetails->certificate_title }}
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
                      <input type="hidden" name="item_id" value="{{ $subscription->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                      @if($hasBought)
                       <a href="{{ $subscription->getLearningPageUrl() }}"
           style="text-decoration:none;display:block;"> 
                      <button type="button" class="frame1000001692-link19 btn-success buy_now js-course-direct-payment">
                          <span class="frame1000001692-text258">
                         Start Learning

                          </span>
                      </button>
                      </a>
                      @else
                      <a href="/subscriptions/direct-payment/{{ $subscription->slug }}"
           style="text-decoration:none;display:block;"> 
                      <button type="button" class="frame1000001692-link19 btn-success buy_now js-course-direct-payment">
                          <span class="frame1000001692-text258">
                            {{ $subscription->extraDetails->cta_text }}

                          </span>
                      </button>
                      </a>
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
          @if(!empty($subscription->faqs) and $subscription->faqs->count() > 0)
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
              <!-- <div class="frame1000001692-container17">
                <div class="frame1000001692-input mt-10 mb-">
                  <div class="frame1000001692-frame427322581">
                    <div class="frame1000001692-search">
                      <img
                        src="/public/course_detail/magnifyglasssvgfill1211-94fj.svg"
                        alt="magnifyglasssvgfill1211"
                        class="frame1000001692-magnifyglasssvgfill"
                      />
                    </div>
                    <div class="frame1000001692-container18">
                      <span class="frame1000001692-text261">
                        Ask me anything about astrology...
                      </span>
                    </div>
                  </div>
                </div>
              </div> -->
            </div>

            </div>

 <div class="" style="
    width: 100%;
">

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
          @php
          $count_faq = 1;
          @endphp
           @foreach($subscription->faqs as $faq)
        <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{$count_faq}}">
                        <div href="#collapseFaq{{$count_faq}}" aria-controls="collapseFaq{{$count_faq}}" class="frame1000001692-frame427322582 align-items-center justify-content-between" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span class="frame1000001692-text262">
                              {{ clean(optional($faq)->title,'title') }}</span>
                            <img
                            src="/public/course_detail/buttontogglesectionsvg1212-q875.svg"
                            alt="ButtontogglesectionSVG1212"
                            class="frame1000001692-buttontogglesection-svg1"
                            />
                        </div>
                    </div>
                    <div id="collapseFaq{{$count_faq}}" aria-labelledby="fBeginners Curious About Astrologyaq_18" class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray" style="font-size: 12px;">
 {{ clean($faq->answer,'answer') }}                        </div>
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
          
          <div class="frame1000001692-frame427322594 mt-10">
            <span class="frame1000001692-text267">Reviews</span>
            <img
              src="/public/course_detail/horizontaldivider1214-67ye-200h.png"
              alt="HorizontalDivider1214"
              class="frame1000001692-horizontal-divider8"
            />
          </div>
            <div class="frame1000001692-frame427322500 mt-20" style="margin: auto;">
            <span class="frame1000001692-text268">
              by 7,438 learners for course quality, clarity &amp; mentor support
            </span>
            <span class="frame1000001692-text269">{{ $subscription->review_number }}</span>
            <img
              src="/public/course_detail/vector1215-9bfd.svg"
              alt="Vector1215"
              class="frame1000001692-vector21"
            />
            <img
              src="/public/course_detail/vector1215-cma7.svg"
              alt="Vector1215"
              class="frame1000001692-vector22"
            />
            <img
              src="/public/course_detail/vector1215-lrcw.svg"
              alt="Vector1215"
              class="frame1000001692-vector23"
            />
            <img
              src="/public/course_detail/vector1215-vy0c.svg"
              alt="Vector1215"
              class="frame1000001692-vector24"
            />
            <img
              src="/public/course_detail/vector1215-xupo.svg"
              alt="Vector1215"
              class="frame1000001692-vector25"
            />
            <img
              src="/public/course_detail/vector1215-byw.svg"
              alt="Vector1215"
              class="frame1000001692-vector26"
            />
            <img
              src="/public/course_detail/vector1215-xgqf.svg"
              alt="Vector1215"
              class="frame1000001692-vector27"
            />
            <img
              src="/public/course_detail/vector1216-8qok.svg"
              alt="Vector1216"
              class="frame1000001692-vector28"
            />
            <img
              src="/public/course_detail/vector1216-zdca.svg"
              alt="Vector1216"
              class="frame1000001692-vector29"
            />
            <img
              src="/public/course_detail/vector1216-a26a.svg"
              alt="Vector1216"
              class="frame1000001692-vector30"
            />
            <img
              src="/public/course_detail/vector1216-2ilh.svg"
              alt="Vector1216"
              class="frame1000001692-vector31"
            />
            <img
              src="/public/course_detail/vector1216-od1g.svg"
              alt="Vector1216"
              class="frame1000001692-vector32"
            />
            <img
              src="/public/course_detail/vector1216-vtdi.svg"
              alt="Vector1216"
              class="frame1000001692-vector33"
            />
            <img
              src="/public/course_detail/vector1216-b4fs.svg"
              alt="Vector1216"
              class="frame1000001692-vector34"
            />
            <img
              src="/public/course_detail/vector1216-4ho.svg"
              alt="Vector1216"
              class="frame1000001692-vector35"
            />
            <img
              src="/public/course_detail/vector1216-3ac.svg"
              alt="Vector1216"
              class="frame1000001692-vector36"
            />
            <img
              src="/public/course_detail/vector1216-bdy.svg"
              alt="Vector1216"
              class="frame1000001692-vector37"
            />
            <img
              src="/public/course_detail/vector1217-spf8.svg"
              alt="Vector1217"
              class="frame1000001692-vector38"
            />
            <img
              src="/public/course_detail/vector1217-h4n8.svg"
              alt="Vector1217"
              class="frame1000001692-vector39"
            />
            <img
              src="/public/course_detail/vector1217-et2.svg"
              alt="Vector1217"
              class="frame1000001692-vector40"
            />
            <img
              src="/public/course_detail/vector1217-278r.svg"
              alt="Vector1217"
              class="frame1000001692-vector41"
            />
            <img
              src="/public/course_detail/vector1217-4bpw.svg"
              alt="Vector1217"
              class="frame1000001692-vector42"
            />
            <img
              src="/public/course_detail/vector1217-7xir.svg"
              alt="Vector1217"
              class="frame1000001692-vector43"
            />
            <img
              src="/public/course_detail/vector1217-2u3o.svg"
              alt="Vector1217"
              class="frame1000001692-vector44"
            />
            <img
              src="/public/course_detail/vector1217-bq1c.svg"
              alt="Vector1217"
              class="frame1000001692-vector45"
            />
            <img
              src="/public/course_detail/vector1217-qtth.svg"
              alt="Vector1217"
              class="frame1000001692-vector46"
            />
            <img
              src="/public/course_detail/vector1217-czo.svg"
              alt="Vector1217"
              class="frame1000001692-vector47"
            />
            <img
              src="/public/course_detail/vector1218-aon.svg"
              alt="Vector1218"
              class="frame1000001692-vector48"
            />
            <img
              src="/public/course_detail/vector1218-jq9.svg"
              alt="Vector1218"
              class="frame1000001692-vector49"
            />
            <img
              src="/public/course_detail/vector1218-s6rj.svg"
              alt="Vector1218"
              class="frame1000001692-vector50"
            />
            <img
              src="/public/course_detail/vector1218-sw6l.svg"
              alt="Vector1218"
              class="frame1000001692-vector51"
            />
            <img
              src="/public/course_detail/vector1218-9zqq.svg"
              alt="Vector1218"
              class="frame1000001692-vector52"
            />
            <img
              src="/public/course_detail/vector1218-v1es.svg"
              alt="Vector1218"
              class="frame1000001692-vector53"
            />
            <img
              src="/public/course_detail/vector1218-hs8v.svg"
              alt="Vector1218"
              class="frame1000001692-vector54"
            />
            <img
              src="/public/course_detail/vector1218-vf68.svg"
              alt="Vector1218"
              class="frame1000001692-vector55"
            />
            <img
              src="/public/course_detail/vector1218-6r4a.svg"
              alt="Vector1218"
              class="frame1000001692-vector56"
            />
            <img
              src="/public/course_detail/vector1218-6s6.svg"
              alt="Vector1218"
              class="frame1000001692-vector57"
            />
            <img
              src="/public/course_detail/vector1219-qem5.svg"
              alt="Vector1219"
              class="frame1000001692-vector58"
            />
            <img
              src="/public/course_detail/vector1219-n0hf.svg"
              alt="Vector1219"
              class="frame1000001692-vector59"
            />
            <img
              src="/public/course_detail/vector1219-mx3.svg"
              alt="Vector1219"
              class="frame1000001692-vector60"
            />
            <img
              src="/public/course_detail/vector1219-nzak.svg"
              alt="Vector1219"
              class="frame1000001692-vector61"
            />
            <img
              src="/public/course_detail/vector1219-taa9.svg"
              alt="Vector1219"
              class="frame1000001692-vector62"
            />
            <div class="frame1000001692-background23">
              <span class="frame1000001692-text270">Outstanding</span>
            </div>
          </div>
          <div class="frame1000001692-group53"style="width:100% !important;">
            <div class="frame1000001692-frame427322599">
              <span class="frame1000001692-text271">
                {{$subscription->extraDetails->rate_title	}}
              </span>
              <div class="frame1000001692-frame427322598">
                                @php
        $rawRT = $subscription->extraDetails->rate_options ?? null;
        $rateTexts = $subscription->extraDetails ? (is_array($rawRT) ? $rawRT : (json_decode($rawRT, true) ?? [])) : [];
        $rawRI = $subscription->extraDetails->rate_icon ?? null;
        $rateIcons = $subscription->extraDetails ? (is_array($rawRI) ? $rawRI : (json_decode($rawRI, true) ?? [])) : [];

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
          <div class="frame1000001692-background24 mt-30" >
            <div class="frame1000001692-frame427322601"></div>
            <div class="frame1000001692-frame427322603">
              <div class="frame1000001692-frame427322602">
                <span class="frame1000001692-text277">
                  <span class="frame1000001692-text278" style="font-size: 11px !important;">
                   {{$subscription->extraDetails->ad_subtitle	}}</span>
                </span>
                <span class="frame1000001692-text280 mt-10"style="width:95% !important;">
                  <span>{{$subscription->extraDetails->ad_title}}</span>
                </span >
                <div class="frame1000001692-group57 mt-10">
                  <!-- <div class="frame1000001692-link20">
                    <span class="frame1000001692-text284">Enroll Now</span>
                  </div> -->
                  <form action="/cart/store" method="post" style="flex:1 1 0;">
                      @csrf
                      <input type="hidden" name="item_id" value="{{ $subscription->id }}">
                      <input type="hidden" name="item_name" value="webinar_id">
                     {{-- Enroll Button with conditional Know more styling --}}

@if($hasBought)
    {{-- User enrolled - Show "Start Learning" button --}}
    <a href="{{ $subscription->getLearningPageUrl() }}"
       style="text-decoration:none;display:block;"> 
        <button type="button" class="frame1000001692-link20 btn-success buy_now js-course-direct-payment">
            <span class="frame1000001692-text284">
                Start Learning
            </span>
        </button>
    </a>
@else
    {{-- User not enrolled - Show "Enroll Now" button --}}
    <a href="/subscriptions/direct-payment/{{ $subscription->slug }}"
       style="text-decoration:none;display:block;"> 
        <button type="button" class="frame1000001692-link20 btn-success buy_now js-course-direct-payment">
            <span class="frame1000001692-text284">
                Enroll Now
            </span>
        </button>
    </a>
@endif
</form>

{{-- Know more link - with conditional left positioning --}}
<a href="/contact" style="text-decoration: none;">
    <div class="frame1000001692-link21" style="@if($hasBought) left: 100px; @endif">
        <span class="frame1000001692-text285">Know more</span>
    </div>
</a>
                </div>
              </div>
              <span class="frame1000001692-text286 mt-10" style="font-size: 12px !important;">
                Don’t wait Seats for this month’s batch are filling fast!
              </span>
            </div>
          </div>

          {{--  
@php
    $canSale = ($subscription->canSale() and !$hasBought);
    $directUrl = url('/subscriptions/direct-payment/' . $subscription->slug);
@endphp

@if($hasBought or !empty($subscription->getInstallmentOrder()))
    <a href="{{ $subscription->getLearningPageUrl() }}"
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
              box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
              text-decoration: none !important;
              display: flex !important;
              align-items: center !important;
              justify-content: center !important;">
        {{ trans('update.go_to_learning_page') }}
    </a>
@else
    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
        <a href="{{ $directUrl }}"
           class="btn btn-primary btn-sm px-25 buy-btn1"
           style="position: fixed !important;
                  bottom: 70px !important;
                  left: 50% !important;
                  transform: translateX(-50%) !important;
                  z-index: 99999 !important;
                  background-color: rgba(50, 160, 40, 1) !important;
                  border: none !important;
                  color: white !important;
                  width: 50% !important;
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
            {{ $subscription->extraDetails->cta_text }}
        </a>
    @else
        <a href="{{ $directUrl }}"
           class="btn btn-primary btn-sm px-25 buy-btn1"
           style="position: fixed !important;
                  bottom: 70px !important;
                  left: 50% !important;
                  transform: translateX(-50%) !important;
                  z-index: 99999 !important;
                  background-color: rgba(50, 160, 40, 1) !important;
                  border: none !important;
                  color: white !important;
                  width: 50% !important;
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
            {{ $subscription->extraDetails->cta_text }}
        </a>
    @endif
@endif
--}}

        </div>
      </div>
      <link rel="canonical" href="https://untitled-jnw2x3.teleporthq.app/" />
    </div>
@endsection




<div class="modal fade " id="playVideo" tabindex="-1" aria-labelledby="playVideoLabel" aria-modal="false" style="display:none;">
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
                      <!-- <iframe src="https://iframe.mediadelivery.net/embed/759/eb1c4f77-0cda-46be-b47d-1118ad7c2ffe?autoplay=true" style="width:100%;height:400px;">  -->

                      </iframe></div>
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
<script>
    function playVideo(){
        // alert('');
       $("#playVideo").modal('show')
    }

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