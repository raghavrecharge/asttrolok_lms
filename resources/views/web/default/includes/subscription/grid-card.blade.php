<style>
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 90% !important;
  }
  .home-rating1 svg.active {
    color: #ffc600;
    stroke: #ffc600;
    fill: #ffc600;
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
    gap: 2px;
    white-space: nowrap;
  }
  .home-text163 {
    margin-left: 2px;
  }
  .home-text164 {
    font-size: 13px !important;
    margin-left: clamp(6px, 4vw, 13px);
  }
  .home-frame10000016142 {
    gap: 0px !important;
  }
  .home-group1621 {
    width: 100%;
    max-width: 420px;
  }
  .home-frame10000016161 {
    left: 10px;
  }
  .english-slide {
    margin-bottom: 10px;
  }
</style>
<style>
  /* Existing styles... */
  
  /* ✅ Star Rating Fix */
  .home-rating1 {
    display: flex !important;
    align-items: center !important;
    gap: 2px !important;
    white-space: nowrap !important;
  }
  
  .home-rating1 svg {
    width: 12px !important;  /* Size बढ़ाया */
    height: 12px !important;
    display: inline-block !important;
  }
  
  .home-rating1 svg.active {
    color: #ffc600 !important;
    stroke: #ffc600 !important;
    fill: #ffc600 !important;
  }
  
  .grid-star {
    fill: #ffc600 !important;  /* Direct fill color */
    stroke: #ffc600 !important;
    stroke-width: 2 !important;
  }
  
  .home-text163 {
    margin-left: 4px !important;
    font-size: 12px !important;
    color: #333 !important;
    font-weight: 500 !important;
  }
  
  .home-text164 {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: #008c3a !important;
  }
</style>

<div class="english-slide">
  <a href="{{ $subscription->getUrl() }}">
    <div class="home-group8031">
      <div class="home-group1621">
        <!-- Main Image -->
        <div class="home-group3691">
          <img src="{{ config('app.img_dynamic_url') }}{{ $subscription->thumbnail }}" 
               alt="{{ $subscription->title }}" 
               class="home-rectangle512" 
               loading="lazy" />
        </div>

        <!-- Duration Badge (commented out like original) -->
        {{-- <div class="home-group172">
          <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" alt="Rectangle" class="home-rectangle102" />
          <span class="home-text159">{{ convertMinutesToHourAndMinute($subscription->duration ?? 0) }}</span>
        </div> --}}

        <!-- Teacher Avatar -->
        <div class="home-group3682">
          <img src="/assets/design_1/img/home_mobile_image/public/ellipse51175-840j-200h.png" alt="Ellipse" class="home-ellipse52" />
          <div class="home-group3622">
            <div class="home-group3652">
              <img src="https://storage.googleapis.com/astrolok/store/1/astrologer_mobile/Alok Sir.jpg" 
                   alt="{{ $subscription->teacher->full_name ?? 'Mr.Alok Khandelwal' }}" 
                   class="home-ellipse13" 
                   loading="lazy" />
            </div>
          </div>
          <div class="home-group2132">
            <img src="/assets/design_1/img/home_mobile_image/public/line21175-0go.svg" alt="Line" class="home-line22" />
          </div>
        </div>
      </div>

      <!-- Card Content -->
      <div class="home-frame10000016161">
        <div class="home-frame10000016151">
          <span class="home-text160">{{ $subscription->teacher->full_name ?? 'Mr.Alok Khandelwal' }}</span>
          <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" alt="Ellipse" class="home-ellipse22" />
        </div>
        
        <span class="home-text161 line-clamp-3 mb-5 mt-5">{{ $subscription->title }}</span>
        
        <div class="home-frame10000016142 mb-10" style="justify-content: space-between;">
          <div class="home-rating1">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>                           
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <span class="home-text163">{{ number_format($subscription->getRate() ?? 4.3, 1) }}</span>
          </div>
          <span class="home-text164">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
        </div>
        
        <a href="{{ $subscription->getUrl() }}" class="home-framebutton1">
          <span class="home-text165">Join Now</span>
        </a>
      </div>
    </div>
  </a>
</div>
