<section class="home-english-courses-section mt-10">
<div class="english-slider" id="hindiSlider">
    
    <div class="english-slides-wrapper">
     
@if(!empty($englishclasses)) 
@foreach($englishclasses as $englishWebinar)
<div class="english-slide">
    <!-- your existing card -->
    <div class="home-group8031">
        <div class="home-group1621">
            <img src="{{ config('app.img_dynamic_url') }}{{ $englishWebinar->getImageCover() }}" alt="Rectangle" class="home-rectangle61" />
            <div class="home-group3691">
                <img src="{{ config('app.img_dynamic_url') }}{{ $englishWebinar->getImage() }}" alt="Rectangle" class="home-rectangle512" />
            </div>
            <div class="home-group172">
                <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" alt="Rectangle" class="home-rectangle102" />
                {{-- <span class="home-text159">{{ convertMinutesToHourAndMinute($englishWebinar->duration) }}</span>--}}
            </div>
            <div class="home-group3682">
                      <img src="/assets/design_1/img/home_mobile_image/public/ellipse51175-840j-200h.png" alt="Ellipse" class="home-ellipse52" />
                      <div class="home-group3622">
                          <div class="home-group3652">
                              <img src="{{ config('app.img_dynamic_url') }}{{ $englishWebinar->teacher->getAvatar() }}" alt="Ellipse" class="home-ellipse13" />
                            </div>
                        </div>
                        <div class="home-group2132">
                            <img src="/assets/design_1/img/home_mobile_image/public/line21175-0go.svg" alt="Line" class="home-line22" />
                      </div>
                    </div>
                </div>
                <div class="home-frame10000016161" style="left:10px;">
                    <div class="home-frame10000016151">
                        <span class="home-text160">{{ $englishWebinar->teacher->full_name }}</span>
                        <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" 
                        alt="Verified" 
                        class="home-ellipse22" />
                    </div>
                    <span class="home-text161 line-clamp-3">{{ $englishWebinar->title }}</span>
                    {{--<span class="home-text162">{{ $englishWebinar->sales_count }}K views • 2 weeks ago</span>--}}
                    <div class="home-frame10000016142 mb-10" style="
                    justify-content: space-between; gap: 0px !important;">
                      <div class="home-rating1">
                          <!-- <img src="/assets/design_1/img/home_mobile_image/public/frame10000016131175-ss4h.svg" alt="Frame" class="home-frame10000016132" /> -->
                          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
                          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>    
                          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> 
                          
                          <span class="home-text163">{{ number_format($englishWebinar->getRate(), 1) }}</span>
                        </div>
                        @if(!empty($isRewardCourses) and !empty($englishWebinar->points))
                        {{-- Reward Course - Points --}}
                        <span class="home-text164 text-warning" style="font-size: 13px !important;">
                            {{ $englishWebinar->points }} {{ trans('update.points') }}
                        </span>

                        @elseif(!empty($englishWebinar->price) and $englishWebinar->price > 0)
                        {{-- Paid Course --}}
                        @if($englishWebinar->bestTicket() < $englishWebinar->price)
                        {{-- Discount hai --}}
                        <span class="home-text164" style="font-size: 13px !important;">
                            {{ handlePrice($englishWebinar->bestTicket(), true, true, false, null, true) }}
                        </span>
                        {{-- <span class="home-text164" style="font-size: 11px !important; text-decoration: line-through; margin-left: 5px; opacity: 0.6;">
                            {{ handlePrice($englishWebinar->price, true, true, false, null, true) }}
                        </span>--}}
                        @else
                        {{-- Normal price --}}
                        <span class="home-text164" style="font-size: 13px !important;">
                            {{ handlePrice($englishWebinar->price, true, true, false, null, true) }}
                        </span>
                        @endif
                        
                        @else
                        {{-- Free Course --}}
                        <span class="home-text164" style="font-size: 13px !important;">
                            {{ trans('public.free') }}
                        </span>
                        @endif
                    </div>
                    <a href="{{ $englishWebinar->getUrl() }}" class="home-framebutton1">
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