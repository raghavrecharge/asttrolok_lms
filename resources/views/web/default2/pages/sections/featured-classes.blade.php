{{-- Featured Classes Desktop Section --}}
<section class="home-sections home-sections-swiper container find-instructor-section position-relative">
    <div class="d-flex justify-content-between">
        <div>
            <h2 class="section-title">{{ trans('home.featured_classes') }}</h2>
            <p class="section-hint">{{ trans('home.featured_classes_hint') }}</p>
        </div>
        <a href="/classes?sort=featured" class="btn btn-border-white">{{ trans('home.view_all') }}</a>
    </div>

    <div class="mt-10 position-relative">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @foreach($featureWebinars as $featureWebinar)
                    <div class="swiper-slide">
                        <div class="webinar-card">
                            <figure>
                                <div class="image-box">
                                    <a href="{{ $featureWebinar->getUrl() }}">
                                        <img src="{{ $featureWebinar->getImage() }}" 
                                             class="img-cover" 
                                             alt="{{ $featureWebinar->title }}">
                                    </a>
                                    @if($featureWebinar->bestTicket())
                                        <span class="badge badge-primary">
                                            {{ handlePrice($featureWebinar->bestTicket()->price) }}
                                        </span>
                                    @endif
                                </div>
                            </figure>
                            <div class="webinar-card-body">
                                <div class="user-inline-avatar d-flex align-items-center">
                                    <div class="avatar">
                                        <img src="{{ $featureWebinar->teacher->getAvatar() }}" 
                                             class="img-cover" 
                                             alt="{{ $featureWebinar->teacher->full_name }}">
                                    </div>
                                    <a href="{{ $featureWebinar->teacher->getProfileUrl() }}" 
                                       target="_blank" 
                                       class="user-name ml-5 font-14">
                                        {{ $featureWebinar->teacher->full_name }}
                                    </a>
                                </div>
                                <a href="{{ $featureWebinar->getUrl() }}">
                                    <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">
                                        {{ truncate($featureWebinar->title, 60) }}
                                    </h3>
                                </a>
                                <div class="webinar-price-box mt-15">
                                    @if($featureWebinar->price > 0)
                                        @if($featureWebinar->bestTicket())
                                            <span class="real">{{ handlePrice($featureWebinar->bestTicket()->price) }}</span>
                                            @if($featureWebinar->bestTicket()->discount > 0)
                                                <span class="off ml-10">{{ $featureWebinar->bestTicket()->discount }}%</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="real font-14">{{ trans('public.free') }}</span>
                                    @endif
                                </div>
                                @if($featureWebinar->reviews->isNotEmpty())
                                    <div class="stars-card d-flex align-items-center mt-15">
                                        @include('web.default2.includes.webinar.rate', ['rate' => $featureWebinar->getRate()])
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>