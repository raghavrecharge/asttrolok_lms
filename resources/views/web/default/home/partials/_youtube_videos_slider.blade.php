   {{-- ========== SECTION 7: SECTION HEADERS ========== --}}
    <section class="home-consult-with-astrologers-section mt-20 mb-10">
      <div class="home-group819">
        <div class="home-frame10000016091">
          <div class="home-frame10000015471">
            <span class="home-text130">YouTube Feed</span>
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