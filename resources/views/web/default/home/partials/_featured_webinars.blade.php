@if(!empty($data))
<section class="home-featured-webinars-section mt-20 alert alert-warning p-3 rounded">
    <div class="featured-slider" id="featuredWebinarsSlider">
        <div class="featured-slides-wrapper">
            @foreach($data as $webinar)
            <div class="featured-slide">
                <a href="{{ $webinar->getUrl() ?? '#' }}" class="webinar-card">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImageCover() ?? '' }}" 
                         alt="{{ $webinar->title ?? '' }}" class="webinar-cover" loading="lazy" />
                    <div class="webinar-content">
                        <span class="webinar-title line-clamp-2">{{ $webinar->title ?? 'Webinar Title' }}</span>
                        <div class="webinar-meta">
                            <span>{{ $webinar->teacher->full_name ?? 'Teacher' }}</span>
                            <span class="webinar-price">{{ handlePrice($webinar->price ?? 0, true, true, false, null, true) }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        <div class="featured-dots"></div>
    </div>
</section>

@push('home_scripts')
<script>
initCustomSlider('featuredWebinarsSlider', 2, 4500);
</script>
@endpush
@else
<div class="alert alert-warning p-3 rounded">
    <strong>⚠️ Featured Webinars Missing</strong><br>
    Controller: $featureWebinars = Webinar::with('teacher')->limit(5)->get();
</div>
@endif
