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