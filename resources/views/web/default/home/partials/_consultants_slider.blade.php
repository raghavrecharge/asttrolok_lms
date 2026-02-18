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