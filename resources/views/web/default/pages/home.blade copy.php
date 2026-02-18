@extends(getTemplate().'.layouts.app')

@push('styles_top')
<link rel="stylesheet" href="/assets/design_1/css/mobile-home-new.css">
<link rel="stylesheet" href="/public/assets/design_1/css/home_mobile_css/index.css">
<link rel="stylesheet" href="/assets/design_1/css/home_mobile_css/style.css">
<link rel="canonical" href="https://www.asttrolok.com" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

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
.english-slide {
    padding: 0 2px !important;
}
.home-frame10000016142{
  gap: 0px !important;
}
.english-slider {
  position: relative;
  overflow: hidden;
  width: 100%;
}
.home-rating1 svg.active {
    color: #ffc600;
    stroke: #ffc600;
    fill: #ffc600;
}

.home-frame427322500 {
    top: auto !important;
    /* left: auto !important; */
    width: 100% !important;
    align-items: center;
}

.home-frame10000016331 {
    top: 220px !important;
    left: 80%!important;
    width: 35px !important;
    height: 35px !important;
}
.grid-star {
   
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.home-rating1 {
    display: flex;
    align-items: center;
    gap: 2px;        /* stars ke beech gap */
    white-space: nowrap;
}
.home-text163 {
    margin-left: 2px;
}
.home-trust-rating-section {
    padding-left: 20px !important; /* adjust value as needed */
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
.english-slide {
    padding: 0 6px;   /* 👈 left-right gap (increase/decrease as needed) */
    box-sizing: border-box;
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
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 2;   /* sirf 3 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 90% !important;
}
.home-maskgroup1{
   width:100% !important ;
}
.home-layer71
 {
   left: 80% !important;

 }
.home-pexelsdavidbartus586687111 {
  width:100% !important ;
  left:0px !important;
  height: 238px;
}
.home-rectangle413 {
    width:100% !important ;
  left:0px !important;
  border-radius: 10px;
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

.frame1000001705-container1 {
  width: 100%;
  display: flex;
  align-items: center;
  flex-direction: column;
}

.frame1000001705-thq-frame1000001705-elm {
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 10px;
}

/* cards */
.frame1000001705-thq-frame1000001702-elm,
.frame1000001705-thq-frame1000001703-elm,
.frame1000001705-thq-frame1000001704-elm {
  display: flex;
  flex-direction: column;
  align-items: center;
  border-radius: 15px;
  padding: 10px;
}

/* colors */
.frame1000001705-thq-frame1000001702-elm { background:#e0ecfe; border:1px solid #73a1e5; }
.frame1000001705-thq-frame1000001703-elm { background:#dfffdc; border:1px solid #94c891; }
.frame1000001705-thq-frame1000001704-elm { background:#ffecce; border:1px solid #ddad63; }

/* text */
.frame1000001705-thq-text-elm1,
.frame1000001705-thq-text-elm4,
.frame1000001705-thq-text-elm7 {
  font-size: 13px;
  font-weight: 700;
  text-align: center;
  line-height: 1.3;
}

/* =========================
   MOBILE RESPONSIVE ONLY
   ========================= */
@media (max-width: 768px) {

  /* main row */
  .frame1000001705-thq-frame1000001705-elm {
    gap: 10px;
    padding: 0;
    flex-wrap: nowrap;
  }

  /* anchor ko card jaisa behave karvao */
  .frame1000001705-thq-frame1000001705-elm > a {
    flex: 0 0 32%;
    max-width: 32%;
    text-decoration: none;
    box-sizing: border-box;
  }

  /* cards */
  .frame1000001705-thq-frame1000001702-elm,
  .frame1000001705-thq-frame1000001703-elm,
  .frame1000001705-thq-frame1000001704-elm {
    width: 100%;
    padding: 8px;
    align-items: center;
  }

  /* images scale */
  .frame1000001705-thq-image6-elm,
  .frame1000001705-thq-image7-elm,
  .frame1000001705-thq-bgremovef45e38aa3ebgremoved17639745084171-elm {
    width: 42px;
    height: auto;
    margin-left: 10px;
  }

  /* text scale */
  .frame1000001705-thq-text-elm1,
  .frame1000001705-thq-text-elm4,
  .frame1000001705-thq-text-elm7 {
    font-size: 11px;
    line-height: 1.3;
    text-align: center;
  }

  .home-vector11 {
  top: 9.867218017578125px;
  /* left: 11.128875732421875px; */
  width: 35px;
  height: 30px !important;
  position: absolute;
}

}
/* Sirf text aur arrow ke beech responsive gap */
.home-frame10000016131 {
    padding-right: 60px; /* Arrow ke liye space */
}

.home-vector15 {
    left: auto !important; /* Remove left positioning */
    right: 15px !important; /* Arrow ko right side se fixed distance */
}

/* Mobile (max 480px) */
@media (max-width: 480px) {
    .home-frame10000016131 {
        padding-right: 50px;
    }
    
    .home-vector15 {
        right: 10px !important;
    }
}

/* Tablet (481px to 768px) */
@media (min-width: 481px) and (max-width: 768px) {
    .home-frame10000016131 {
        padding-right: 55px;
    }
    
    .home-vector15 {
        right: 12px !important;
    }
}

/* Desktop (769px and above) */
@media (min-width: 769px) {
    .home-frame10000016131 {
        padding-right: 70px;
    }
    
    .home-vector15 {
        right: 20px !important;
    }
}
</style>
@endpush

@section('content')
<div class="home-container1">
  <div class="home-home">

    {{-- ========== SECTION 1: PATHSHALA OFFERS SLIDER ========== --}}
   

    <div class="swiper myJoinSwiper">
    <div class="swiper-wrapper">

        @foreach($pathshalaOffers as $offer)
        <div class="swiper-slide mt-10">
            <div class="home-frame10000016141"style=" width: auto !important;">
                <div class="home-frame10000016131" style=" width: fit-content !important;">
                    <span class="home-text106" style="font-family:Inter !important;width: fit-content !important;">{{ $offer['title'] }}</span>
                    <span class="home-text107" style="font-family:Inter !important;">
                        <span class="home-text108">{{ $offer['subtitle'] }}{{ $offer['price'] }}</span>
                        
                        <span></span>
                    </span>
                   
                </div>
                 <a href="/subscriptions/asttrolok-pathshala"> <img src="public/Arrow.svg" alt="Vector1174" class="home-vector15" style="left:85% !important;top:30% !important;width:40px!important;height:40px!important;"></a>
            </div>
        </div>
        @php 
          break;
        @endphp
        @endforeach

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  var swiper = new Swiper(".myJoinSwiper", {
    slidesPerView: 1,
    spaceBetween: 15,
    loop: true,
    autoplay: {
      delay: 2500,
      disableOnInteraction: false,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
  });
</script>
   @include('web.default.includes.search')
    
    {{-- ========== SECTION 3: CATEGORIES ========== --}}
   

<section class="home-categories-section mt-10">

<div class="frame1000001705-container1">
    <div class="frame1000001705-thq-frame1000001705-elm">
     <a href="/classes">
      <div class="frame1000001705-thq-frame1000001702-elm">
        <img src="public/image605-3qq-200h.png" class="frame1000001705-thq-image6-elm" style="width: 54px !important;">
        <span class="frame1000001705-thq-text-elm1">
          Online<br>Courses
        </span>
      </div>
      </a>
       <a href="/consult-with-astrologers">
      <div class="frame1000001705-thq-frame1000001703-elm">
        <img src="public/image708-i77-200h.png" class="frame1000001705-thq-image7-elm" style="width: 59px !important;">
        <span class="frame1000001705-thq-text-elm4">
          Online<br>Consultation
        </span>
      </div>
</a>
       <a href="https://asttroveda.asttrolok.com/asttrolok/personalizedkundali">
      <div class="frame1000001705-thq-frame1000001704-elm">
        <div class="frame1000001705-thq-frame1000001699-elm">
          <img src="public/bgremovef45e38aa3ebgremoved17639745084171012-8tlm-200w.png"
               class="frame1000001705-thq-bgremovef45e38aa3ebgremoved17639745084171-elm ">
            <span class="frame1000001705-thq-text-elm1">
            Personalized<br>Reports
          </span>
        </div>
      </div>
      </a>

    </div>
  </div>
</section>


    <section class="home-categories-section mt-10">
      @foreach($categories_mobile as $category)
      <div>
        <a href="/categories{{ $category['link'] }}" style="
    display: flex;
    flex-direction: column;
    align-items: center;
">
          
          @if(isset($category['frame']))
          <div class="home-group813">
            <img src="{{ $category['frame'] }}" alt="{{ $category['name'] }}" class="home-ellipse11" />
          </div>
          @else
            <div class="home-group813">
              <img src="{{ $category['ellipse'] }}" alt="Ellipse" class="home-ellipse11" />
              @if(isset($category['group_inner_class']))
                <!-- <div class="{{ $category['group_inner_class'] }}"> -->
                  <img src="{{ $category['vector'] }}" alt="Vector" class="home-vector11" />
                <!-- </div> -->
              @else
                <img src="{{ $category['vector'] }}" alt="Vector" class="home-vector11" />
              @endif
            </div>
          @endif
          <span class="home-text101">{{ $category['name'] }}</span>
        </a>
      </div>
      @endforeach
    </section>


{{-- ========== SECTION 5: baner section ========== --}}
 <section class="home-baner-section mt-20">
  <style>
  .banner-slider {
    position: relative;
    overflow: hidden;
    width: 100%;
  }

  .banner-slides-wrapper {
    display: flex;
    transition: transform 0.5s ease;
    width: 300%; /* 3 slides */
  }

  .banner-slide {
    width: 100%;
    flex-shrink: 0;
  }

  .banner-controls {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    pointer-events: none;
  }

  .banner-btn {
    pointer-events: auto;
    background: rgba(0,0,0,0.4);
    color: #fff;
    border: none;
    padding: 6px 10px;
    cursor: pointer;
    border-radius: 50%;
    margin: 0 8px;
  }

  .banner-dots {
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 6px;
  }

  .banner-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
  }

  .banner-dot.active {
    background: #fff;
  }
  section.home-english-astrology-courses-section {
    margin-top: -100px;
}

.home-frame1000001641 {
  gap: clamp(6px, 2.5vw, 10px);
  width: 100%;
  height: clamp(140px, 42vw, 160px);
  display: flex;
  overflow: hidden;
  position: relative;
  align-items: flex-start;
  flex-direction: column;
}

/* background rectangle */
.home-rectangle415 {
  width: 100%;
  height: 100%;
  position: relative;
  border-radius: clamp(6px, 2.5vw, 10px);
}

/* overlay rectangle */
.home-rectangle17 {
  width: 100%;
  height: 90%;
  z-index: 1;
  position: absolute;
  border-radius: clamp(6px, 2.5vw, 10px);
}

/* main image */
.home-mainimage {
  width: clamp(110px, 38vw, 150px);
  height: clamp(95px, 34vw, 131px);
  position: absolute;
  top: clamp(8px, 3vw, 12px);
  right: clamp(8px, 3vw, 15px);
  z-index: 2;
  border-radius: clamp(10px, 4vw, 16px);
  background-size: cover;
  background-position: center;
  background-image: url(/assets/design_1/img/home_mobile_image/public/tq_3lt72lke-c-e1a9-200h.png);
}

/* text content */
.home-frame1000001640 {
  gap: clamp(2px, 1vw, 4px);
  width: clamp(130px, 45vw, 164px);
  display: flex;
  z-index: 3;
  position: absolute;
  top: clamp(14px, 5vw, 22px);
  left: clamp(12px, 4vw, 20px);
  flex-direction: column;
}
.home-text131 {
  color: rgba(21, 72, 131, 1);
  font-family: Inter, sans-serif;
  font-weight: 400;
  text-align: left;

  font-size: clamp(11px, 3.5vw, 14px);
  line-height: 1.4;

  padding: clamp(3px, 1.5vw, 5px) clamp(8px, 4vw, 12px);
  border-radius: clamp(18px, 10vw, 41px);

  border: 1px solid rgba(21, 72, 131, 1);
  width: fit-content;
  white-space: nowrap;
}
.home-text133 {
  color: rgba(21, 72, 131, 1);
  font-family: Inter, sans-serif;
  font-weight: 400;
  text-align: left;

  font-size: clamp(11px, 3.5vw, 14px);
  line-height: 1.4;

  padding: clamp(3px, 1.5vw, 5px) clamp(8px, 4vw, 12px);
  border-radius: clamp(18px, 10vw, 41px);

  border: 1px solid rgba(21, 72, 131, 1);
  width: fit-content;
  white-space: nowrap;
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


</style>

<div class="banner-slider" id="bannerSlider">
  <div class="banner-slides-wrapper">
    <!-- Slide 1 -->
    <div class="banner-slide">
      <div style="overflow-x: hidden;">
        <div class="home-group1631">
          <img src="/assets/design_1/img/home_mobile_image/public/rectangle41174-214w-200h.png" alt="Rectangle41174" class="home-rectangle411">
          <img src="/assets/design_1/img/home_mobile_image/public/image11174-y4d-400w.png" alt="image11174" class="home-image1">
          <img src="/assets/design_1/img/home_mobile_image/public/rectangle161174-b5t9-200h.png" alt="Rectangle161174" class="home-rectangle16">
          <div class="home-group1632">
            <div class="home-frame1000001621">
              <div class="home-group3681">
                <img src="/assets/design_1/img/home_mobile_image/public/ellipse51174-d0p-200h.png" alt="Ellipse51174" class="home-ellipse51">
                <div class="home-group3621">
                  <div class="home-group3651">
                    <img src="/assets/design_1/img/home_mobile_image/public/ellipse11174-szrt-200h.png" alt="Ellipse11174" class="home-ellipse12">
                    <img src="/assets/design_1/img/home_mobile_image/public/image21174-l2jd-200h.png" alt="image21174" class="home-image2">
                  </div>
                </div>
                <div class="home-group2131">
                  <img src="/assets/design_1/img/home_mobile_image/public/line21174-o1l.svg" alt="Line21174" class="home-line21">
                </div>
              </div>
              <div class="home-frame1000001620">
                <span class="home-text123">Alok Khandewal Ji</span>
                <span class="home-text124">50+ courses • 50K+ Students</span>
              </div>
            </div>
          </div>
          <span class="home-text125">
            <span>Gain Real Astrology Skills to Read</span><br>
            <span>Charts with Clarity</span>
          </span>
        
        </div>
        <div style="
    width: 12%;
    top: -138px;
    position: relative;
    left: 21%;
"
        >
      <img src="/assets/design_1/img/home_mobile_image/public/kundli11175-ec7h-200h.png" 
     alt="Kundli11175" 
     class="home-kundli1q" 
     style="width:100%; margin-right: 10px !important;">
      </div>
        
      </div>
    </div>

 

  
  </div>

  <div class="banner-controls d-none">
    <button class="banner-btn" data-dir="prev">&#10094;</button>
    <button class="banner-btn" data-dir="next">&#10095;</button>
  </div>

  <div class="banner-dots">
    <div class="banner-dot active" data-slide="0"></div>
    <div class="banner-dot" data-slide="1"></div>
    <div class="banner-dot" data-slide="2"></div>
  </div>
</div>

<script>
  (function () {
    const slider = document.getElementById('bannerSlider');
    const wrapper = slider.querySelector('.banner-slides-wrapper');
    const slides = slider.querySelectorAll('.banner-slide');
    const dots = slider.querySelectorAll('.banner-dot');
    const total = slides.length;
    let current = 0;

    function goToSlide(index) {
      current = (index + total) % total;
      wrapper.style.transform = 'translateX(' + (-current * 100) + '%)';
      dots.forEach(d => d.classList.remove('active'));
      dots[current].classList.add('active');
    }

    slider.querySelector('[data-dir="next"]').addEventListener('click', function () {
      goToSlide(current + 1);
    });

    slider.querySelector('[data-dir="prev"]').addEventListener('click', function () {
      goToSlide(current - 1);
    });

    dots.forEach(dot => {
      dot.addEventListener('click', function () {
        const n = parseInt(this.getAttribute('data-slide'), 10);
        goToSlide(n);
      });
    });

    // optional auto slide
    setInterval(function () {
      goToSlide(current + 1);
    }, 5000);
  })();
</script>

</section>


       <section class="home-english-astrology-courses-section">
          <div class="home-group822">
            <div class="home-frame10000016092">
              <div class="home-frame10000015472">
                <span class="home-text132">Hindi Courses</span>
                <a href="/classes?english=on" style="
                      right: 20px;
                      position: absolute;
                  ">
                <span class="home-text133">View All</span>
                </a>
              </div>
            </div>
          </div>
        </section>


    {{-- ========== SECTION 11: ENGLISH COURSES (DYNAMIC) ========== --}}
  
    <section class="home-english-courses-section mt-10">
      <div class="english-slider" id="englishSlider">
       
        <div class="english-slides-wrapper">
          @foreach($subscriptions as $subscription)
            @if(!empty($subscription))
             <div class="english-slide">
      <a href="{{ $subscription->getUrl() }}" class="card-link">
        <div class="home-group8031">
          <div class="home-group1621">
            <!-- Main Image -->
            <div class="home-group3691">
              <img src="https://storage.googleapis.com/astrolok/webp/store/1/1/Pathshala-min (1).webp" 
                   alt="{{ $subscription->title }}" 
                   class="home-rectangle512" 
                   loading="lazy" />
            </div>

            @php
              $progress = 2; // Progress percentage
            @endphp
            
            <!-- Duration Badge (using progress as placeholder) -->
            <!-- <div class="home-group172">
              <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" 
                   alt="Rectangle" 
                   class="home-rectangle102" />
              <span class="home-text159">{{ $progress }}% Complete</span>
            </div> -->

            <!-- Teacher Avatar Circle -->
            <div class="home-group3682">
              <img src="/assets/design_1/img/home_mobile_image/public/ellipse51175-840j-200h.png" 
                   alt="Ellipse" 
                   class="home-ellipse52" />
              <div class="home-group3622">
                <div class="home-group3652">
                  <img src="https://storage.googleapis.com/astrolok/store/1/astrologer_mobile/Alok Sir.jpg" 
                       alt="{{ $subscription->teacher->full_name ?? 'Mr.Alok Khandelwal' }}" 
                       class="home-ellipse13" 
                       loading="lazy" />
                </div>
              </div>
              <div class="home-group2132">
                <img src="/assets/design_1/img/home_mobile_image/public/line21175-0go.svg" 
                     alt="Line" 
                     class="home-line22" />
              </div>
            </div>
          </div>

          <!-- Card Content -->
          <div class="home-frame10000016161" style="left:10px;">
            <!-- Teacher Name with Badge -->
            <div class="home-frame10000016151">
              <span class="home-text160">{{ $subscription->teacher->full_name ?? 'Mr.Alok Khandelwal' }}</span>
              <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" 
                   alt="Verified" 
                   class="home-ellipse22" />
            </div>

            <!-- Title -->
            <span class="home-text161">{{ $subscription->title }}</span>

            <!-- Category & Stats -->
            <!-- <span class="home-text162">
              in {{ $subscription->category->title ?? 'Astrology' }} • {{ $subscription->sales_count ?? 0 }} Students
            </span> -->

            <!-- Rating & Price -->
            <div class="home-frame10000016142 mb-10" style="
        justify-content: space-between;">
                  <div class="home-rating1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>                           
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>                           

                    <span class="home-text163">{{ number_format($subscription->getRate() ?? 4.3, 1) }}</span>
                  </div>
              <span class="home-text164"style="font-size: 13px !important;">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
            </div>

            <!-- Start Learning Button -->
            <div class="home-framebutton1">
              <span class="home-text165">Join Now</span>
            </div>
          </div>
        </div>
      </a>
    </div>
            @endif
          @endforeach
           @if(!empty($hindiWebinars)) 
          @foreach($hindiWebinars as $hindiWebinar)
              <div class="english-slide">
                <!-- your existing card -->
                <div class="home-group8031">
                  <div class="home-group1621">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $hindiWebinar->getImageCover() }}" alt="Rectangle" class="home-rectangle61" />
                    <div class="home-group3691">
                      <img src="{{ config('app.img_dynamic_url') }}{{ $hindiWebinar->getImage() }}" alt="Rectangle" class="home-rectangle512" />
                    </div>
                    <div class="home-group172">
                      <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" alt="Rectangle" class="home-rectangle102" />
                     {{-- <span class="home-text159">{{ convertMinutesToHourAndMinute($hindiWebinar->duration) }}</span>--}}
                    </div>
                    <div class="home-group3682">
                      <img src="/assets/design_1/img/home_mobile_image/public/ellipse51175-840j-200h.png" alt="Ellipse" class="home-ellipse52" />
                      <div class="home-group3622">
                        <div class="home-group3652">
                          <img src="{{ config('app.img_dynamic_url') }}{{ $hindiWebinar->teacher->getAvatar() }}" alt="Ellipse" class="home-ellipse13" />
                        </div>
                      </div>
                      <div class="home-group2132">
                        <img src="/assets/design_1/img/home_mobile_image/public/line21175-0go.svg" alt="Line" class="home-line22" />
                      </div>
                    </div>
                  </div>
                  <div class="home-frame10000016161" style="left:10px;">
                    <div class="home-frame10000016151">
                      <span class="home-text160">{{ $hindiWebinar->teacher->full_name }}</span>
                      <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" 
                   alt="Verified" 
                   class="home-ellipse22" />
                    </div>
                    <span class="home-text161 line-clamp-3">{{ $hindiWebinar->title }}</span>
                    {{--<span class="home-text162">{{ $hindiWebinar->sales_count }}K views • 2 weeks ago</span>--}}
                    <div class="home-frame10000016142 mb-10" style="
                    justify-content: space-between; gap: 0px !important;">
                      <div class="home-rating1">
                        <!-- <img src="/assets/design_1/img/home_mobile_image/public/frame10000016131175-ss4h.svg" alt="Frame" class="home-frame10000016132" /> -->
                       <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> 

                        <span class="home-text163">{{ number_format($hindiWebinar->getRate(), 1) }}</span>
                                              </div>
                      @if(!empty($isRewardCourses) and !empty($hindiWebinar->points))
    {{-- Reward Course - Points --}}
    <span class="home-text164 text-warning" style="font-size: 13px !important;">
        {{ $hindiWebinar->points }} {{ trans('update.points') }}
    </span>

@elseif(!empty($hindiWebinar->price) and $hindiWebinar->price > 0)
    {{-- Paid Course --}}
    @if($hindiWebinar->bestTicket() < $hindiWebinar->price)
        {{-- Discount hai --}}
        <span class="home-text164" style="font-size: 13px !important;">
            {{ handlePrice($hindiWebinar->bestTicket(), true, true, false, null, true) }}
        </span>
       {{-- <span class="home-text164" style="font-size: 11px !important; text-decoration: line-through; margin-left: 5px; opacity: 0.6;">
            {{ handlePrice($hindiWebinar->price, true, true, false, null, true) }}
        </span>--}}
    @else
        {{-- Normal price --}}
        <span class="home-text164" style="font-size: 13px !important;">
            {{ handlePrice($hindiWebinar->price, true, true, false, null, true) }}
        </span>
    @endif

@else
    {{-- Free Course --}}
    <span class="home-text164" style="font-size: 13px !important;">
        {{ trans('public.free') }}
    </span>
@endif
                    </div>
                    <a href="{{ $hindiWebinar->getUrl() }}" class="home-framebutton1">
                      <span class="home-text165">Buy Now</span>
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>

        <!-- dots -->
        <div class="english-dots"></div>
      </div>
    </section>

    <script>
    //   (function () {
    //     const slider = document.getElementById('englishSlider');
    //     const wrapper = slider.querySelector('.english-slides-wrapper');
    //     const slides = slider.querySelectorAll('.english-slide');
    //     const perView = 2; // cards visible
    //     const totalSlides = slides.length;
    //     const totalPages = Math.max(1, Math.ceil(totalSlides / perView));

    //     const dotsContainer = slider.querySelector('.english-dots');

    //     // create dots
    //     for (let i = 0; i < totalPages; i++) {
    //       const dot = document.createElement('div');
    //       dot.className = 'english-dot' + (i === 0 ? ' active' : '');
    //       dot.dataset.page = i;
    //       dotsContainer.appendChild(dot);
    //     }

    //     const dots = dotsContainer.querySelectorAll('.english-dot');

    //     let currentPage = 0;
    //     let timer = null;
    //     const interval = 5000; // 5s

    //     function goTo(page) {
    //       currentPage = (page + totalPages) % totalPages;
    //       const index = currentPage * perView;
    //       const percent = (100 / perView) * index;
    //       wrapper.style.transform = 'translateX(' + (-percent) + '%)';

    //       dots.forEach(d => d.classList.remove('active'));
    //       dots[currentPage].classList.add('active');
    //     }

    //     function startAuto() {
    //       stopAuto();
    //       timer = setInterval(function () {
    //         goTo(currentPage + 1);
    //       }, interval);
    //     }

    //     function stopAuto() {
    //       if (timer) {
    //         clearInterval(timer);
    //         timer = null;
    //       }
    //     }

    //     dots.forEach(dot => {
    //       dot.addEventListener('click', function () {
    //         const page = parseInt(this.dataset.page, 10);
    //         goTo(page);
    //         startAuto(); // reset timer after manual click
    //       });
    //     });

    //     // pause on hover (optional, feels nicer)
    //     slider.addEventListener('mouseenter', stopAuto);
    //     slider.addEventListener('mouseleave', startAuto);

    //     // init
    //     goTo(0);
    //     startAuto();
    //   })();
    </script>
       {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
    <section class="home-consult-with-astrologers-section mt-20 mb-10">
      <div class="home-group819">
        <div class="home-frame10000016091">
          <div class="home-frame10000015471">
            <span class="home-text130">English Courses</span>
            <a href="/classes?hindi=on" style="
        right: 0;
        position: absolute;
         ">
            <span class="home-text131">View All</span>
            </a>
          </div>
        </div>
      </div>
    </section>
 {{-- ========== SECTION 12: English COURSES (DYNAMIC) ========== --}}
    <section class="home-english-courses-section mt-10">
      <div class="english-slider" id="hindiSlider">
      <div class="english-slides-wrapper">
     @if(!empty($englishclasses))
       @foreach($englishclasses as $englishclasse)
          <div class="english-slide">
            <div class="home-group8031">
              <div class="home-group1621">
                <img src="{{ config('app.img_dynamic_url') }}{{ $englishclasse->getImageCover() }}" alt="Rectangle" class="home-rectangle61" />
                <div class="home-group3691">
                  <img src="{{ config('app.img_dynamic_url') }}{{ $englishclasse->getImage() }}" alt="Rectangle" class="home-rectangle512" />
                </div>
                <div class="home-group172">
                  <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" alt="Rectangle" class="home-rectangle102" />
                  {{--<span class="home-text159">{{ convertMinutesToHourAndMinute($englishclasse->duration) }}</span>--}}
                </div>
                <div class="home-group3682">
                  <img src="/assets/design_1/img/home_mobile_image/public/ellipse51175-840j-200h.png" alt="Ellipse" class="home-ellipse52" />
                  <div class="home-group3622">
                    <div class="home-group3652">
                      <img src="{{ config('app.img_dynamic_url') }}{{ $englishclasse->teacher->getAvatar() }}" alt="Ellipse" class="home-ellipse13" />
                    </div>
                  </div>
                  <div class="home-group2132">
                    <img src="/assets/design_1/img/home_mobile_image/public/line21175-0go.svg" alt="Line" class="home-line22" />
                  </div>
                </div>
              </div>
              <div class="home-frame10000016161" style="left:10px;">
                <div class="home-frame10000016151">
                  <span class="home-text160">{{ $englishclasse->teacher->full_name }}</span>
                <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" 
                   alt="Verified" 
                   class="home-ellipse22" />
                </div>
                <span class="home-text161 line-clamp-3">{{ $englishclasse->title }}</span>
                {{--<span class="home-text162">{{ $englishclasse->sales_count }}K views • 2 weeks ago</span>--}}
                <div class="home-frame10000016142 mb-10" style="
    justify-content: space-between;  gap: 0px !important;">
                  <div class="home-rating1">
                    <!-- <img src="/assets/design_1/img/home_mobile_image/public/frame10000016131175-ss4h.svg" alt="Frame" class="home-frame10000016132" /> -->
                      <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> 

                        <span class="home-text163">{{ number_format($englishclasse->getRate(), 1) }}</span>
                      </div>
                      <span class="home-text164" style=" font-size: 13px !important;">{{ handlePrice($englishclasse->price, true, true, false, null, true) }}</span>
                    </div>
                    <a href="{{ $englishclasse->getUrl() }}" class="home-framebutton1">
                      <span class="home-text165">Buy Now</span>
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </div>

        <div class="english-dots"></div>
      </div>
    </section>

 <script>
//   (function () {
//     function initSlider(rootId) {
//       const slider = document.getElementById(rootId);
//       if (!slider) return;

//       const wrapper = slider.querySelector('.english-slides-wrapper');
//       const slides = slider.querySelectorAll('.english-slide');
//       const perView = 2;
//       const totalSlides = slides.length;
//       const totalPages = Math.max(1, Math.ceil(totalSlides / perView));
//       const dotsContainer = slider.querySelector('.english-dots');

//       dotsContainer.innerHTML = '';
//       for (let i = 0; i < totalPages; i++) {
//         const dot = document.createElement('div');
//         dot.className = 'english-dot' + (i === 0 ? ' active' : '');
//         dot.dataset.page = i;
//         dotsContainer.appendChild(dot);
//       }

//       const dots = dotsContainer.querySelectorAll('.english-dot');
//       let currentPage = 0;
//       let timer = null;
//       const interval = 5000;

//       function goTo(page) {
//         currentPage = (page + totalPages) % totalPages;
//         const index = currentPage * perView;
//         const percent = (100 / perView) * index;
//         wrapper.style.transform = 'translateX(' + (-percent) + '%)';

//         dots.forEach(d => d.classList.remove('active'));
//         dots[currentPage].classList.add('active');
//       }

//       function startAuto() {
//         stopAuto();
//         timer = setInterval(function () {
//           goTo(currentPage + 1);
//         }, interval);
//       }

//       function stopAuto() {
//         if (timer) {
//           clearInterval(timer);
//           timer = null;
//         }
//       }

//       dots.forEach(dot => {
//         dot.addEventListener('click', function () {
//           const page = parseInt(this.dataset.page, 10);
//           goTo(page);
//           startAuto();
//         });
//       });

//       slider.addEventListener('mouseenter', stopAuto);
//       slider.addEventListener('mouseleave', startAuto);

//       goTo(0);
//       startAuto();
//     }

//     initSlider('englishSlider'); // existing English courses
//     initSlider('hindiSlider');   // new Hindi webinars
//   })();
 </script>
    {{-- ========== SECTION 10: FEATURED BOOK ========== --}}
    <section class="home-featured-book-section mt-20">
      <div class="home-frame1000001641">
        <img src="/assets/design_1/img/home_mobile_image/public/rectangle41175-fsbk-200h.png" alt="Rectangle" class="home-rectangle415" />
        <img src="/assets/design_1/img/home_mobile_image/public/rectangle171175-e5m-400w.png" alt="Rectangle" class="home-rectangle17" />
        <div class="home-mainimage"></div>
        <div class="home-frame1000001640">
          <div class="home-frame1000001636">
            <span class="home-text155">Featured Book:</span>
          </div>
          <span class="home-text156">A Research Study of</span>
          <span class="home-text157">Astrology & Vision Loss</span>
          <span class="home-text158">300+ Pages • 500 Copy Sold</span>
        </div>
      </div>
    </section>




    

    {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
    <section class="home-consult-with-astrologers-section mt-20">
      <div class="home-group819 mb-10">
        <div class="home-frame10000016091">
          <div class="home-frame10000015471">
            <span class="home-text130">Consult with Astrologer</span>
            <a href="/consult-with-astrologers" style="
    right: 0;
    position: absolute;
">
            <span class="home-text131">View All</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    {{-- ========== SECTION 13: CONSULTANTS ========== --}}
   

   <section class="home-consultant-card-section mt-10">
  @php
    $validConsultants = $consultant->filter(function($instructor) {
        return !empty($instructor->meeting) && !$instructor->meeting->disabled && !empty($instructor->meeting->meetingTimes);
    })->take(10);
  @endphp
  
  @if($validConsultants->count() > 0)
    <!-- Swiper CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <div class="consultant-slider swiper" id="consultantSlider">
      <div class="swiper-wrapper">
        @foreach($validConsultants as $key => $instructor)
          @php
            $canReserve = !empty($instructor->meeting)
              && !$instructor->meeting->disabled
              && !empty($instructor->meeting->meetingTimes);
          @endphp

          <div class="swiper-slide">
            <a href="{{ $instructor->getProfileUrl() }}{{ $canReserve ? '?tab=appointments' : '' }}"
               class="btn1 btn-primary"
               style="width: 100%; font-weight: bold; height: auto; border-radius: 20px;">
              <div class="home-frame1000001631">
                <div class="home-frame10000016301">
                  <img src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar() }}"
                       class="home-rectangle6801" 
                       alt="{{ $instructor->full_name }}" />
                  <div class="home-frame10000016291">
                    <span class="home-text187" style="font-weight: 700;font-family:Inter !important;">{{ $instructor->full_name }}</span>

                    @if(!empty($instructor->bio))
                      <pre class="home-text188"style="font-family:Inter !important;">{{ $instructor->bio }}</pre>
                    @endif

                    <span class="home-text194">
                      <span style="color:#008C3A !important;font-weight: 500;font-family:Inter !important;"
>{{ handlePrice($instructor->meeting->amount/30, true, true, false, null, true) }}</span>
                      <span class="text-dark-blue font-12
" style="font-family:Inter !important;">/min</span>
                    </span>
                  </div>
                </div>

                <img src="/assets/design_1/img/home_mobile_image/public/frame10000016331177-bqkm.svg"
                     class="home-frame10000016331" 
                     alt="arrow" />
              </div>
            </a>
          </div>
        @endforeach
      </div>

       <div class="english-dots"></div>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const consultantSwiper = new Swiper('#consultantSlider', {
          // Slides per view
          slidesPerView: 2,
          spaceBetween: 10,
          
          breakpoints: {
            // Mobile (>=640px)
            640: {
              slidesPerView: 1,
              spaceBetween: 20
            },
            // Tablet (>=768px)
            768: {
              slidesPerView: 2,
              spaceBetween: 30
            },
            // Desktop (>=1024px)
            1024: {
              slidesPerView: 2,
              spaceBetween: 30
            }
          },

          autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
          },

          loop: true,
          
          speed: 800,
          
          effect: 'slide',

          pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
          },

          grabCursor: true,
          lazy: true,

          centeredSlides: false,
        });
      });
    </script>
  @endif
</section>

   
    <section class="home-additional-titles-section mt-20">
      <a href="/subscriptions/asttrolok-pathshala" class="home-link4">
      <img src="/assets/design_1/img/home_mobile_image/public/image 22-min.jpg" alt="image" class="home-image21s" style="width:100%; border-radius: 10px;" />
      </a>
    </section>

    {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
    <section class="home-consult-with-astrologers-section mt-20">
      <div class="home-group819">
        <div class="home-frame10000016091">
          <div class="home-frame10000015471">
            <span class="home-text130">Astrology Remedies</span>
            <a href="/remedies?sort=newest" style="
    right: 0;
    position: absolute;
">
            <span class="home-text131">View All</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    {{-- ========== SECTION 9: PDF DOWNLOAD CARDS ========== --}}

    @php $class = 10; @endphp

<section class="home-pdf-downloads-section mt-20">
  
  <div class="english-slider" id="remediesSlider">
   
    <div class="english-slides-wrapper">
      @foreach($remedies as $key => $card)
       <a href="/remedy/{{ $card['slug'] }}" class="home-link4">
        <div class="english-slide">
          <div class="home-group10">
            <div class="home-maskgroup{{ $key + 1 }}">
              <img src="{{ config('app.img_dynamic_url') }}{{ $card['thumbnail'] }}" alt="PDF" class="home-pexelsdavidbartus586687111" />
              <img src="/assets/design_1/img/home_mobile_image/public/rectangle41175-qyk-300h.png" alt="Overlay" class="home-rectangle413" />
            </div>
            <div class="home-group">
              <span class="home-text144"></span>
              <span class="home-text145" >

                <span class="line-clamp-3" style="width: 100% !important;">{{ $card['title'] }}</span><br>
              </span>
            </div>
            <div class="home-layer71">
              <img src="/assets/design_1/img/home_mobile_image/public/vector1175-iyoe.svg" alt="Icon" class="home-vector16" />
            </div>
            <img src="/assets/design_1/img/home_mobile_image/public/vector1175-8635.svg" alt="Vector" class="home-vector17" />
          </div>
        </div>
        @php $class++; @endphp
          </a>
      @endforeach
    </div>
  

    <div class="english-dots"></div>
  </div>
</section>

<script>
//   (function () {
//     function initSlider(rootId) {
//       const slider = document.getElementById(rootId);
//       if (!slider) return;

//       const wrapper = slider.querySelector('.english-slides-wrapper');
//       const slides = slider.querySelectorAll('.english-slide');
//       const perView = 2; // cards visible
//       const totalSlides = slides.length;
//       const totalPages = Math.max(1, Math.ceil(totalSlides / perView));
//       const dotsContainer = slider.querySelector('.english-dots');

//       dotsContainer.innerHTML = '';
//       for (let i = 0; i < totalPages; i++) {
//         const dot = document.createElement('div');
//         dot.className = 'english-dot' + (i === 0 ? ' active' : '');
//         dot.dataset.page = i;
//         dotsContainer.appendChild(dot);
//       }

//       const dots = dotsContainer.querySelectorAll('.english-dot');
//       let currentPage = 0;
//       let timer = null;
//       const interval = 5000;

//       function goTo(page) {
//         currentPage = (page + totalPages) % totalPages;
//         const index = currentPage * perView;
//         const percent = (100 / perView) * index;
//         wrapper.style.transform = 'translateX(' + (-percent) + '%)';

//         dots.forEach(d => d.classList.remove('active'));
//         dots[currentPage].classList.add('active');
//       }

//       function startAuto() {
//         stopAuto();
//         timer = setInterval(function () {
//           goTo(currentPage + 1);
//         }, interval);
//       }

//       function stopAuto() {
//         if (timer) {
//           clearInterval(timer);
//           timer = null;
//         }
//       }

//       dots.forEach(dot => {
//         dot.addEventListener('click', function () {
//           const page = parseInt(this.dataset.page, 10);
//           goTo(page);
//           startAuto();
//         });
//       });

//       slider.addEventListener('mouseenter', stopAuto);
//       slider.addEventListener('mouseleave', startAuto);

//       goTo(0);
//       startAuto();
//     }

//     initSlider('englishSlider');
//     initSlider('hindiSlider');
//     initSlider('remediesSlider'); // new remedies slider
//   })();
</script>



{{-- ========== SECTION 7: SECTION HEADERS ========== --}}
<section class="home-consult-with-astrologers-section mt-20">
  <div class="home-group819">
    <div class="home-frame10000016091">
      <div class="home-frame10000015471">
        <span class="home-text130">Latest Insta Feed</span>
        <a href="https://www.instagram.com/asttrolok/#" style="right: 0; position: absolute;">
          <span class="home-text131">Visit Page</span>
        </a>
      </div>
    </div>
  </div>
</section>

{{-- Instagram Feed Slider Section --}}
<style>
/* Instagram slider specific styling - match Hindi programs style */
#instaSlider .english-slide {
  flex: 0 0 50%;
  padding: 0 4px;
  box-sizing: border-box;
}

#instaSlider .home-link1 {
  display: block;
  width: 100%;
  height: 100%;
}

/* Center the dots */
#instaSlider .english-dots {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
  gap: 6px;
  margin-top: 12px;
  width: 100%;
}

#instaSlider .english-dot {
  width: 18px;
  height: 4px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.15);
  transition: background 0.3s, width 0.3s;
}

#instaSlider .english-dot.active {
  width: 26px;
  background: #1fb36a;
}

/* TrustIndex Feed Section */
.insta-trustindex-section {
  padding: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
  margin-top: 20px;
  width: 368px;
}

.insta-trustindex-section h2 {
  font-size: 24px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.insta-feed-container {
  width: 100%;
  max-width: 480px;
}

/* Instagram Button */
.insta-join-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  max-width: 360px;
  padding: 12px 16px;
  font-size: 16px;
  font-weight: 600;
  border-radius: 8px;
  background-color: #32A128;
  border: 1px solid #32A128;
  color: #fff;
  text-decoration: none;
  transition: all 0.3s ease;
  margin-top: 16px;
}

.insta-join-btn:hover {
  background-color: #2a8a22;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(50, 161, 40, 0.3);
}

.insta-join-btn svg {
  flex-shrink: 0;
}
</style>


<section  style="width:100%;" style="border-radius: 10px;">
  
    <script defer async src='https://cdn.trustindex.io/loader-feed.js?adb441f551c8621a0276a48c072'></script>
<!-- 
  <a href="https://www.instagram.com/asttrolok/#" class="insta-join-btn">
    <svg width="24px" height="24px"depkind="http://www.w3.org/20=(1) 0 0 24 24" fill="currentColor" aria-hidden="true">
      <path d="M21.66 10.44l-.98 4.18c-.84 3.61-2.5 5.07-5.62 4.77-.5-.04-1.04-.13-1.62-.27l-1.68-.4c-4.17-.99-5.46-3.05-4.48-7.23l.98-4.19c.2-.85.44-1.59.74-2.2 1.17-2.42 3.16-3.07 6.5-2.28l1.67.39c4.19.98 5.47 3.05 4.49 7.23z" opacity=".4"></path>
      <path d="M15.06 19.39c-.62.42-1.4.77-2.35 1.08l-1.58.52c-3.97 1.28-6.06.21-7.35-3.76L2.5 13.28c-1.28-3.97-.22-6.07 3.75-7.35l1.58-.52c.41-.13.8-.24 1.17-.31-.3.61-.54 1.35-.74 2.2l-.98 4.19c-.98 4.18.31 6.24 4.48 7.23l1.68.4c.58.14 1.12.23 1.62.27zM17.49 10.51c-.06 0-.12-.01-.19-.02l-4.85-1.23a.75.75 0 01.37-1.45l4.85 1.23a.748.748 0 01-.18 1.47z"></path>
      <path d="M14.56 13.89c-.06 0-.12-.01-.19-.02l-2.91-.74a.75.75 0 01.37-1.45l2.91.74c.4.1.64.51.54.91-.08.34-.38.56-.72.56z"></path>
    </svg>
    <span>Join Us on Instagram</span>
  </a> -->
</section>

<script>
  (function () {
    function initSlider(rootId, perView) {
      const slider = document.getElementById(rootId);
      if (!slider) return;

      const wrapper = slider.querySelector('.english-slides-wrapper');
      const slides = slider.querySelectorAll('.english-slide');
      const totalSlides = slides.length;
      if (!totalSlides) return;

      const totalPages = Math.max(1, Math.ceil(totalSlides / perView));
      const dotsContainer = slider.querySelector('.english-dots');
      if (!dotsContainer) return;

      dotsContainer.innerHTML = '';
      for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement('div');
        dot.className = 'english-dot' + (i === 0 ? ' active' : '');
        dot.dataset.page = i;
        dotsContainer.appendChild(dot);
      }

      const dots = dotsContainer.querySelectorAll('.english-dot');
      let currentPage = 0;
      let timer = null;
      const interval = 5000;

      function goTo(page) {
        currentPage = (page + totalPages) % totalPages;
        const index = currentPage * perView;
        const percent = (100 / perView) * index;
        wrapper.style.transform = 'translateX(' + (-percent) + '%)';

        dots.forEach(d => d.classList.remove('active'));
        dots[currentPage].classList.add('active');
      }

      function startAuto() {
        stopAuto();
        timer = setInterval(function () {
          goTo(currentPage + 1);
        }, interval);
      }

      function stopAuto() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }

      dots.forEach(dot => {
        dot.addEventListener('click', function () {
          const page = parseInt(this.dataset.page, 10);
          goTo(page);
          startAuto();
        });
      });

      slider.addEventListener('mouseenter', stopAuto);
      slider.addEventListener('mouseleave', startAuto);

      goTo(0);
      startAuto();
    }

    // Initialize all sliders with 2 cards per view
    initSlider('englishSlider', 2);
    initSlider('hindiSlider', 2);
    initSlider('remediesSlider', 2);
    initSlider('consultantSlider', 2);
    initSlider('instaSlider', 2); // Instagram feed slider
    initSlider('mediaSlider', 2);
    
    // Knowledge slider with 1 card per view
    initSlider('knowledgeSlider', 1);
  })();
</script>

   {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
    <section class="home-consult-with-astrologers-section mt-20 mb-10">
      <div class="home-group819">
        <div class="home-frame10000016091">
          <div class="home-frame10000015471">
            <span class="home-text130">You Tube Feed</span>
            <a href="https://www.youtube.com/channel/UCpTpt23TwNia1DV831JZgDg"  target="black" style="
    right: 0;
    position: absolute;
">
            <span class="home-text131">Visit Page</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    
<style>

/* only knowledge slider: 1 card per view */
#knowledgeSlider .english-slide {
  flex: 0 0 100%;
  box-sizing: border-box;
}

</style>
 @if($videos)
<section class="home-knowledge-feed-section mt-10">
  <div class="english-slider" id="knowledgeSlider">
    <div class="english-slides-wrapper">
     
        
      
      @foreach($videos as $video)
     
        <div class="english-slide">
           <a href="{{ $video['url'] }}" target="blank">
          <div class="home-knowledge-feed-card">
            <div class="home-link3">
              <div class="home-ytimagehqdefaultjpg"
                   style="background-image: url('{{ $video['thumbnail'] }}');">
              </div>

              <div class="home-ytdthumbnailoverlaytimestatusrenderer-img45minutes7">
               <span class="home-text226">{{ $video['duration'] }}</span>
              </div>
            </div>

            <span class="home-text228">
              {{ $video['title'] }}
            </span>
          </div>
           </a>
        </div>
     
      @endforeach
     
    </div>

    <div class="english-dots"></div>
  </div>
</section>
 @endif



  {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
      <section class="home-consult-with-astrologers-section mt-20">
        <div class="home-group819">
          <div class="home-frame10000016091">
            <div class="home-frame10000015471">
              <span class="home-text130">Media Coverage</span>
              <a href="/classes?english=on" style="right: 0;position: absolute;">
              <!-- <span class="home-text131">Visit Page</span> -->
              </a>
            </div>
          </div>
        </div>
      </section>

      <section class="home-now-playing-section mt-20" style="width: 100% !important;">
    @if(count($channels) > 0)
      <div class="channels-scroll">
        @foreach($channels as $key => $channel)
          <a href="{{ $channel['link'] }}" class="channel-item" target="_blank">
            <div class="home-frame1000001645">
              <div class="home-frame10000016441">
                <div class="home-image3">
                  <img src="{{ $channel['icon'] }}" 
                      alt="{{ $channel['name'] }}" 
                      class="home-electronicspsjsvgfill1">
                </div>
              </div>
              <span class="home-text231">{{ $channel['name'] }}</span>
            </div>
          </a>
        @endforeach
    </div>

    <style>
      .channels-scroll {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 20px 0;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
      }

      .channels-scroll::-webkit-scrollbar {
        height: 6px;
      }

      .channels-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }

      .channels-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
      }

      .channel-item {
        flex: 0 0 auto;
        width: 120px;
        text-decoration: none;
      }

      .home-frame1000001645 {
        transition: transform 0.3s ease;
      }

      .home-frame1000001645:hover {
        transform: scale(1.1);
      }
    </style>
  @endif
</section>



    {{--<section class="home-now-playing-section mt-20">
  <div class="english-slider" id="mediaSlider">
    <div class="english-slides-wrapper">
      @foreach($channels as $key => $channel)
        <div class="english-slide">
          <div class="home-frame1000001645">
            <div class="home-frame10000016441">
              <div class="home-image3">
                <img src="{{ $channel['icon'] }}" alt="{{ $channel['name'] }}" class="home-electronicspsjsvgfill1">
              </div>
              <div class="home-img2"></div>
            </div>
            <span class="home-text231">{{ $channel['name'] }}</span>
          </div>
        </div>
      @endforeach
    </div>

    <div class="english-dots"></div>
  </div>
</section>--}}

<script>
(function () {
  // 0) Helper: clear all intervals (THIS WILL STOP any setInterval on page)
  // It's a blunt tool but effective if leftover timers are causing autoplay.
  (function clearAllIntervals() {
    try {
      // Get highest interval id, then clear all from 0..maxId
      const highestId = window.setInterval(() => {}, 9999);
      for (let i = 0; i <= highestId; i++) {
        try { window.clearInterval(i); } catch (e) {}
      }
      console.info('[slider-fix] cleared intervals up to', highestId);
    } catch (e) {
      console.warn('[slider-fix] clearAllIntervals failed', e);
    }
  })();

  // 1) Disable common autoplay data attributes (for third-party sliders)
  document.querySelectorAll('[data-autoplay],[data-ride],[data-slick]').forEach(el => {
    try {
      el.setAttribute('data-autoplay', 'false');
      el.removeAttribute('data-ride');
      el.removeAttribute('data-slick');
    } catch (e) {}
  });

  // 2) Remove CSS animations from potential wrappers (prevents CSS-driven motion)
  document.querySelectorAll('.english-slides-wrapper, .slides-wrapper, .slider-wrapper, .slick-track').forEach(el => {
    el.style.animation = 'none';
    el.style.webkitAnimation = 'none';
    el.style.transition = el.style.transition; // keep transitions if you want smooth manual translate
    el.style.transform = el.style.transform; // preserve current transform
  });

  // 3) If using Swiper/Slick/Owl, attempt to stop them (best-effort)
  try {
    if (window.jQuery) {
      try {
        // Stop slick autoplay if present
        if (typeof window.jQuery('.slick-slider').slick === 'function') {
          window.jQuery('.slick-slider').slick('slickPause');
          console.info('[slider-fix] called slickPause');
        }
      } catch (e) {}
      try {
        // Stop Owl autoplay
        if (typeof window.jQuery('.owl-carousel').trigger === 'function') {
          window.jQuery('.owl-carousel').trigger('stop.owl.autoplay');
          console.info('[slider-fix] triggered stop.owl.autoplay');
        }
      } catch (e) {}
    }
    if (window.Swiper && window.mySwipers) {
      // If your code stored swipers in global, try to stop them
      (window.mySwipers || []).forEach(s => {
        try { s.autoplay && s.autoplay.stop && s.autoplay.stop(); console.info('[slider-fix] Stopped Swiper instance'); } catch(e){}
      });
    }
  } catch (e) {}

  // 4) Reinitialize your slider in manual-only mode (dots + touch)
  function initManualOnlySlider(rootId, perView) {
    const slider = document.getElementById(rootId);
    if (!slider) return;

    const wrapper = slider.querySelector('.english-slides-wrapper');
    const slides = slider.querySelectorAll('.english-slide');
    if (!wrapper || !slides || slides.length === 0) return;

    // remove any inline autoplay-starting attributes or functions
    try {
      slider.removeAttribute('data-autoplay');
      slider.removeAttribute('autoplay');
    } catch (e) {}

    const totalSlides = slides.length;
    const totalPages = Math.max(1, Math.ceil(totalSlides / perView));

    // build dots if not present or reset
    let dotsContainer = slider.querySelector('.english-dots');
    if (!dotsContainer) {
      dotsContainer = document.createElement('div');
      dotsContainer.className = 'english-dots';
      slider.appendChild(dotsContainer);
    }
    dotsContainer.innerHTML = '';
    for (let i = 0; i < totalPages; i++) {
      const dot = document.createElement('div');
      dot.className = 'english-dot' + (i === 0 ? ' active' : '');
      dot.dataset.page = i;
      dotsContainer.appendChild(dot);
    }

    const dots = dotsContainer.querySelectorAll('.english-dot');
    let currentPage = 0;

    function goTo(page) {
      currentPage = (page + totalPages) % totalPages;
      const index = currentPage * perView;
      const percent = (100 / perView) * index;
      wrapper.style.transform = 'translateX(' + (-percent) + '%)';
      dots.forEach(d => d.classList.remove('active'));
      dots[currentPage] && dots[currentPage].classList.add('active');
    }

    // dot click
    dots.forEach(dot => {
      dot.addEventListener('click', function () {
        const page = parseInt(this.dataset.page, 10);
        goTo(page);
      });
    });

    // touch swipe
    let startX = 0;
    slider.addEventListener('touchstart', function (e) {
      startX = e.touches[0].clientX;
    }, {passive: true});

    slider.addEventListener('touchend', function (e) {
      const endX = e.changedTouches[0].clientX;
      const diff = startX - endX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) goTo(currentPage + 1);
        else goTo(currentPage - 1);
      }
    }, {passive: true});

    // ensure wrapper has no CSS animation class that might move it
    wrapper.classList.remove('autoplaying', 'animating');
    wrapper.style.animation = 'none';

    // initial position
    goTo(0);

    // debug
    console.info('[slider-fix] initialized manual slider for', rootId, 'pages:', totalPages);
  }

  // call for your sliders
  const slidersToInit = [
    { id: 'englishSlider', perView: 2 },
    { id: 'hindiSlider', perView: 2 },
    { id: 'remediesSlider', perView: 2 },
    { id: 'consultantSlider', perView: 2 },
    { id: 'mediaSlider', perView: 2 },
    { id: 'knowledgeSlider', perView: 1 }
  ];
  slidersToInit.forEach(s => initManualOnlySlider(s.id, s.perView));

  // final debug scan: list elements that look like sliders and their inline styles
  document.querySelectorAll('.english-slides-wrapper, .slides-wrapper, .slick-track').forEach(el => {
    console.info('[slider-fix] wrapper inline style:', el.getAttribute('style'));
  });

})();
</script>

      {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
      <section class="home-consult-with-astrologers-section mt-20">
        <div class="home-group819">
          <div class="home-frame10000016091">
            <div class="home-frame10000015471">
              <span class="home-text130">Trusted by Millions</span>
              <a href="https://www.google.com/search?q=ASttrolok" style="
      right: 0;
      position: absolute;
  ">
              <span class="home-text131">write now</span>
              </a>
            </div>
          </div>
        </div>
      </section>

      {{-- testi Cards Section --}}
    <section class="home-knowledge-feed-section mt-10">
      <div class="home-frame1000001656">
          <script defer async src="https://cdn.trustindex.io/loader.js?76d20565517c6153b51678c18ab"></script>
      </div>
    </section>

  
      
    {{-- ========== SECTION 5: FEATURED VIDEO CARD ========== --}}
    @if(!empty($featureWebinars) && $featureWebinars->isNotEmpty())
    @php $feature = $featureWebinars->first(); @endphp
    <div class="home-group1631">
      <img src="/assets/design_1/img/home_mobile_image/public/rectangle41174-214w-200h.png" alt="Rectangle" class="home-rectangle411">
      <img src="{{ config('app.img_dynamic_url') }}{{ $feature->webinar->getImageCover() }}" alt="Video" class="home-image1">
      <img src="/assets/design_1/img/home_mobile_image/public/rectangle161174-b5t9-200h.png" alt="Rectangle" class="home-rectangle16">
      <div class="home-group1632">
        <div class="home-frame1000001621">
          <div class="home-group3681">
            <img src="/assets/design_1/img/home_mobile_image/public/ellipse51174-d0p-200h.png" alt="Ellipse" class="home-ellipse51">
            <div class="home-group3621">
              <div class="home-group3651">
                <img src="/assets/design_1/img/home_mobile_image/public/ellipse11174-szrt-200h.png" alt="Ellipse" class="home-ellipse12">
                <img src="{{ config('app.img_dynamic_url') }}{{ $feature->webinar->teacher->getAvatar() }}" alt="Avatar" class="home-image2">
              </div>
            </div>
            <div class="home-group2131">
              <img src="/assets/design_1/img/home_mobile_image/public/line21174-o1l.svg" alt="Line" class="home-line21">
            </div>
          </div>
          <div class="home-frame1000001620">
            <span class="home-text123">{{ $feature->webinar->teacher->full_name }}</span>
            <span class="home-text124">250+ courses • 210M Students</span>
          </div>
        </div>
      </div>
      <span class="home-text125"><span>{{ $feature->webinar->title }}</span></span>
      <div class="home-group171">
        <img src="/assets/design_1/img/home_mobile_image/public/rectangle101174-p47r.svg" alt="Rectangle" class="home-rectangle101">
        <span class="home-text129">{{ convertMinutesToHourAndMinute($feature->webinar->duration) }}</span>
      </div>
    </div>
    @endif



  







    

<style>
/* Section overall spacing */
.home-trustindex-section {
    /* padding: 16px 16px; top/bottom 16px, left/right 16px */
    box-sizing: border-box;
    width: 100%;
    margin-top: 10px;
}

/* Inner frame responsive */
.home-frame1000001656 {
    width: 100%;
    max-width: 480px; /* mobile friendly max width */
    margin: 0 auto; /* center on screen */
}

/* Optional: adjust iframe/content inside TrustIndex if it overflows */
.home-frame1000001656 iframe,
.home-frame1000001656 > * {
    width: 100% !important;
    height: auto !important;
    display: block;
}
</style>




    {{-- Now Playing Video Section --}}

   
<!-- <section style="padding: 16px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 24px;">

    <script defer async src="https://cdn.trustindex.io/loader-feed.js?adb441f551c8621a0276a48c072"></script>
    <div style="width: 100%; max-width: 480px;">
    </div>

    <a href="https://www.instagram.com/asttrolok/#"
       style="
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            max-width: 360px;
            padding: 12px 16px;
            font-size: 16px;
            border-radius: 8px;
            background-color: #32A128;
            border: 1px solid #32A128;
            color: #fff;
            text-decoration: none;
       "
       class="btn-flip-effect">
       
        <svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M21.66 10.44l-.98 4.18c-.84 3.61-2.5 5.07-5.62 4.77-.5-.04-1.04-.13-1.62-.27l-1.68-.4c-4.17-.99-5.46-3.05-4.48-7.23l.98-4.19c.2-.85.44-1.59.74-2.2 1.17-2.42 3.16-3.07 6.5-2.28l1.67.39c4.19.98 5.47 3.05 4.49 7.23z" opacity=".4"></path>
            <path d="M15.06 19.39c-.62.42-1.4.77-2.35 1.08l-1.58.52c-3.97 1.28-6.06.21-7.35-3.76L2.5 13.28c-1.28-3.97-.22-6.07 3.75-7.35l1.58-.52c.41-.13.8-.24 1.17-.31-.3.61-.54 1.35-.74 2.2l-.98 4.19c-.98 4.18.31 6.24 4.48 7.23l1.68.4c.58.14 1.12.23 1.62.27zM17.49 10.51c-.06 0-.12-.01-.19-.02l-4.85-1.23a.75.75 0 01.37-1.45l4.85 1.23a.748.748 0 01-.18 1.47z"></path>
            <path d="M14.56 13.89c-.06 0-.12-.01-.19-.02l-2.91-.74a.75.75 0 01.37-1.45l2.91.74c.4.1.64.51.54.91-.08.34-.38.56-.72.56z"></path>
        </svg>

        <span>Join Us on Instagram</span>
    </a>

</section> -->

@endsection

@push('scripts_bottom')

    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/vendors/parallax/parallax.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/home.min.js"></script>
    <script  src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>

    <script >
        document.addEventListener("DOMContentLoaded", () => {
          const bgDivs = document.querySelectorAll("[data-bg]");
          const options = { rootMargin: "0px 0px 200px 0px" };

          const lazyLoad = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
              if (entry.isIntersecting) {
                const div = entry.target;
                div.style.backgroundImage = `url('${div.dataset.bg}')`;
                div.style.backgroundSize = "cover";
                div.style.backgroundRepeat = "no-repeat";
                observer.unobserve(div);
              }
            });
          }, options);

          bgDivs.forEach(div => lazyLoad.observe(div));
        });
        </script>
    <script >

        function featurjquery(urls){
            window.location.href = urls;
        }
        </script>
    <script >

        function changeImageAndShowPopup(data) {
            console.log('data',data);
            var imageElement = document.getElementById('videoiframe');
            imageElement.src = data;
            document.getElementById('popup').style.display = 'block';
        }
        function closePopup() {
            // Hide the popup
            document.getElementById('popup').style.display = 'none';
        }
        </script>
@endpush

  </body>
</html>