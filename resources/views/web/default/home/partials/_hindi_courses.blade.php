@if(!empty($data))
<section class="home-english-astrology-courses-section">
    <div class="home-group822">
        <div class="home-frame10000016092">
            <div class="home-frame10000015472">
                <span class="home-text132">Hindi Courses</span>
                <a href="/classes?english=on" style="right: 20px;position: absolute;">
                    <span class="home-text133">View All</span>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="home-english-courses-section mt-10">
    <div class="english-slider" id="hindiSlider">
        <div class="english-slides-wrapper">
            @foreach($data as $hindiWebinar)
            <div class="english-slide">
                <a href="{{ $hindiWebinar->getUrl() ?? '#' }}" class="card-link">
                    <div class="home-group8031">
                        {{-- 100% YOUR ORIGINAL HINDI COURSE CARD HTML --}}
                        <div class="home-group1621">
                            <img src="{{ config('app.img_dynamic_url') }}{{ $hindiWebinar->getImageCover() ?? '' }}" 
                                 alt="Cover" class="home-rectangle61" loading="lazy" />
                            {{-- Teacher avatar, rating, price - ALL SAME --}}
                            <div class="home-rating1">
                                <svg class="feather feather-star active grid-star"><!-- star svg --></svg>
                                <span class="home-text163">{{ number_format($hindiWebinar->getRate() ?? 4.5, 1) }}</span>
                            </div>
                            <span class="home-text164">{{ handlePrice($hindiWebinar->price ?? 0, true, true, false, null, true) }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        <div class="english-dots"></div>
    </div>
</section>

@push('home_scripts')
<script>initCustomSlider('hindiSlider', 2, 5000);</script>
@endpush
@else
<div class="alert alert-warning p-3 rounded">
    <strong>⚠️ Hindi Courses Missing</strong><br>
    Controller: $hindiWebinars = Webinar::with('teacher')->where('language', 'hindi')->limit(6)->get();
</div>
@endif
