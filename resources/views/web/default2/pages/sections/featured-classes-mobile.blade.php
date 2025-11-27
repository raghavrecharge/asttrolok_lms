<section class="featured-classes-mobile d-lg-none">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title mb-30">
                    <h2 class="font-weight-bold">{{ trans('home.featured_classes') }}</h2>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach($featureWebinars as $featureWebinar)
                <div class="col-12 col-sm-6 mb-30">
                    <div class="webinar-card">
                        <figure>
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
                        </figure>
                        <div class="webinar-card-body">
                            <a href="{{ $featureWebinar->getUrl() }}">
                                <h3 class="webinar-title">{{ $featureWebinar->title }}</h3>
                            </a>
                            <div class="webinar-meta mt-15">
                                <div class="d-flex align-items-center">
                                    <i class="far fa-user mr-5"></i>
                                    <span>{{ $featureWebinar->teacher->full_name }}</span>
                                </div>
                                @if($featureWebinar->reviews->isNotEmpty())
                                    <div class="stars-card d-flex align-items-center mt-10">
                                        @include('web.default2.includes.webinar.rate', ['rate' => $featureWebinar->getRate()])
                                        <span class="badge badge-info ml-10">
                                            {{ $featureWebinar->reviews->count() }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
