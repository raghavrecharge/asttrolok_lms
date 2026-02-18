@if(!empty($hindiWebinars))
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
            @foreach($hindiWebinars as $hindiWebinar)
            <div class="english-slide">
                <div class="home-group8031">
                    <div class="home-group1621">
                        <img src="{{ config('app.img_dynamic_url') }}{{ $hindiWebinar->getImageCover() }}" alt="Rectangle" class="home-rectangle61" />
                        <div class="home-group3691">
                            <img src="{{ config('app.img_dynamic_url') }}{{ $hindiWebinar->getImage() }}" alt="Rectangle" class="home-rectangle512" />
                        </div>
                        <div class="home-group172">
                            <img src="/assets/design_1/img/home_mobile_image/public/rectangle101175-e28q.svg" alt="Rectangle" class="home-rectangle102" />
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
                            <img src="/assets/design_1/img/home_mobile_image/public/ellipse21175-kblq-200h.png" alt="Verified" class="home-ellipse22" />
                        </div>
                        <span class="home-text161 line-clamp-3">{{ $hindiWebinar->title }}</span>
                        <div class="home-frame10000016142 mb-10" style="justify-content: space-between; gap: 0px !important;">
                            <div class="home-rating1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <span class="home-text163">{{ number_format($hindiWebinar->getRate(), 1) }}</span>
                            </div>
                            @if(!empty($isRewardCourses) and !empty($hindiWebinar->points))
                                <span class="home-text164 text-warning" style="font-size: 13px !important;">
                                    {{ $hindiWebinar->points }} {{ trans('update.points') }}
                                </span>
                            @elseif(!empty($hindiWebinar->price) and $hindiWebinar->price > 0)
                                @if($hindiWebinar->bestTicket() < $hindiWebinar->price)
                                    <span class="home-text164" style="font-size: 13px !important;">
                                        {{ handlePrice($hindiWebinar->bestTicket(), true, true, false, null, true) }}
                                    </span>
                                @else
                                    <span class="home-text164" style="font-size: 13px !important;">
                                        {{ handlePrice($hindiWebinar->price, true, true, false, null, true) }}
                                    </span>
                                @endif
                            @else
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
        </div>
        <div class="english-dots"></div>
    </div>
</section>

@endif
