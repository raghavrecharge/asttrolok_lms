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