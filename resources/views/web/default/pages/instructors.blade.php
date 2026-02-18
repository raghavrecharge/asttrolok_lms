@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-instructors.css">
    <link rel="stylesheet" href="assets/default/css/category_slider.css">
        <link rel="stylesheet" href="/public/assets/design_1/css/home_mobile_css/index.css">

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


    {{-- Search bar --}}
   
    
 @include('web.default.includes.search')
         

   {{-- Cards grid: 2 per row --}}
<div class="consultation-page-frame1000001658">
  @foreach($consult as $instructor2)
    @if($instructor2->consultant == 1)
      <div class="consultation-page-frame100000163210">
        <a href="{{ $instructor2->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" 
           class="card-clickable-overlay"></a>
        <div class="consultation-page-frame100000163010">
          <img
            src="{{ config('app.img_dynamic_url') }}{{ $instructor2->getAvatar(190) }}"
            alt="{{ $instructor2->full_name }}"
            class="consultation-page-rectangle68010 "
          />
          
          <div class="consultation-page-frame100000162910">
              <span class="consultation-page-text108" style="font-weight: 700 !important;">
                {{ $instructor2->full_name }}
              </span>

              @php
                  $lines = explode("\n", $instructor2->bio);
              @endphp
              <span class="consultation-page-text109">
                @foreach($lines as $line)
                  <span>{{ trim($line) }}</span><br>
                @endforeach
              </span>

              <span class="consultation-page-text115">
                @if(!empty($instructor2->meeting) && !$instructor2->meeting->disabled && !empty($instructor2->meeting->amount))
                  @if(!empty($instructor2->meeting->discount))
           <span class="font-20 font-weight-bold" 

                      {{ handlePrice($instructor2->meeting->amount - (($instructor2->meeting->amount * $instructor2->meeting->discount) / 100)) }}
                    </span>
                    <span class="font-14 text-gray text-decoration-line-through ml-10">
                      {{ handlePrice($instructor2->meeting->amount) }}
                    </span>
                  @else
                    <span class="font-20 text-primary font-weight-500" style="color:#008C3A !important;">
                      {{ handlePrice($instructor2->meeting->amount/30) }}
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
  style="width: 40px; height: auto; margin-top: 8px;"
/>

      </div>
    @endif
  @endforeach
</div>

  </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/instructors.min.js"></script>
@endpush
