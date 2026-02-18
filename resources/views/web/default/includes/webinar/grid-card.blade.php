<style>
  .line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 2;   /* sirf 3 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
        width: 90% !important;

}
@media (min-width: 280px) and (max-width: 425px) {
    svg.grid-star {
        display: block;
    }
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
    gap: 2px;        /* stars ke beech gap */
    white-space: nowrap;
}
.home-text163 {
    margin-left: 2px;
}
.home-text164
{
      font-size: 13px !important;
}
 .home-frame10000016142 {
    gap: 0px !important;
 }
.home-group1621{
    width: 100%;
    max-width: 420px; /* jitni chahiye utni value badha do */
}

.english-slide {
  margin-bottom: 10px; /* या 25px, 30px जितना चाहिए */
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
      <a href="{{ $webinar->getUrl() }}">
                <!-- your existing card -->
                <div class="home-group8031">
                  <div class="home-group1621">
                    <!-- <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImageCover() }}" alt="Rectangle" class="home-rectangle61" /> -->
                    <div class="home-group3691">
                      <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImage() }}" alt="Rectangle" class="home-rectangle512" />
                    </div>
                    <div class="home-group172">
                      <!-- <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" alt="Rectangle" class="home-rectangle102" /> -->
                      {{--<span class="home-text159">{{ convertMinutesToHourAndMinute($webinar->duration) }}</span>--}}
                    </div>
                    <div class="home-group3682">
                      <img src="/assets/design_1/img/home_mobile_image/public/ellipse51175-840j-200h.png" alt="Ellipse" class="home-ellipse52" />
                      <div class="home-group3622">
                        <div class="home-group3652">
                          <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->teacher->getAvatar() }}" alt="Ellipse" class="home-ellipse13" />
                        </div>
                      </div>
                      <div class="home-group2132">
                        <img src="/assets/design_1/img/home_mobile_image/public/line21175-0go.svg" alt="Line" class="home-line22" />
                      
                      </div>
                    </div>
                  </div>
                  <div class="home-frame10000016161 " style="left:10px;">
                    <div class="home-frame10000016151">
                      <span class="home-text160">{{ $webinar->teacher->full_name }}</span>
                      <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" alt="Ellipse" class="home-ellipse22" />
                    </div>
                    <span class="home-text161 line-clamp-3 mb-5 mt-5">{{ $webinar->title }}</span>
                    {{--<span class="home-text162">{{ $webinar->sales_count }}K views • 2 weeks ago</span>--}}
                    <div class="home-frame10000016142 mb-10 " style="
    justify-content: space-between;">
                      <div class="home-rating1 ">
                        <!-- <img src="/assets/design_1/img/home_mobile_image/public/frame10000016131175-ss4h.svg" alt="Frame" class="home-frame10000016132" /> -->
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>                           
<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>                           

<span class="home-text163" >{{ number_format($webinar->getRate(), 1) }}</span>
                      </div>
                      @if(!empty($isRewardCourses) and !empty($webinar->points))
                                {{-- Reward Course - Show Points --}}
                                <span class="home-text164 text-warning" style="font-size: 13px;">
                                    {{ $webinar->points }} {{ trans('update.points') }}
                                </span>

                            @elseif(!empty($webinar->price) and $webinar->price > 0)
                                {{-- Paid Course --}}
                                @if($webinar->bestTicket() < $webinar->price)
                                    {{-- With Discount --}}
                                    <span class="home-text164" style="font-size: 13px;">
                                        {{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}
                                    </span>
                                   {{--  <span class="home-text164" style="font-size: 11px; text-decoration: line-through; margin-left: 5px; opacity: 0.6;">
                                        {{ handlePrice($webinar->price, true, true, false, null, true) }}
                                    </span> --}}
                                @else
                                    {{-- Regular Price --}}
                                    <span class="home-text164" style="font-size: 13px;">
                                        {{ handlePrice($webinar->price, true, true, false, null, true) }}
                                    </span>
                                @endif

                            @else
                                {{-- Free Course --}}
                                <span class="home-text164" style="font-size: 13px;">
                                    {{ trans('public.free') }}
                                </span>
                            @endif
                    </div>
                    <a href="{{ $webinar->getUrl() }}" class="home-framebutton1">
                      <span class="home-text165">Buy Now</span>
                    </a>
                  </div>
                </div>
      </a>
              </div>
