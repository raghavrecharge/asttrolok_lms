<section class="home-sections container mt-50">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="section-title">{{ trans('home.upcoming_courses') }}</h2>
            <p class="section-hint">{{ trans('home.upcoming_courses_hint') }}</p>
        </div>
        <a href="/classes?sort=upcoming" class="btn btn-border-white">{{ trans('home.view_all') }}</a>
    </div>

    <div class="mt-30">
        <div class="row">
            @foreach($courses as $course)
                <div class="col-12 col-md-6 col-lg-4 mt-20">
                    <div class="webinar-card">
                        <figure>
                            <div class="image-box">
                                <a href="{{ $course->getUrl() }}">
                                    <img src="{{ $course->getImage() }}"
                                         class="img-cover"
                                         alt="{{ $course->title }}">
                                </a>
                                <span class="badge badge-warning">{{ trans('public.upcoming') }}</span>
                            </div>
                        </figure>
                        <div class="webinar-card-body">
                            <div class="user-inline-avatar d-flex align-items-center">
                                <div class="avatar">
                                    <img src="{{ $course->teacher->getAvatar() }}"
                                         class="img-cover"
                                         alt="{{ $course->teacher->full_name }}">
                                </div>
                                <a href="{{ $course->teacher->getProfileUrl() }}"
                                   target="_blank"
                                   class="user-name ml-5 font-14">
                                    {{ $course->teacher->full_name }}
                                </a>
                            </div>
                            <a href="{{ $course->getUrl() }}">
                                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">
                                    {{ truncate($course->title, 60) }}
                                </h3>
                            </a>
                            <div class="webinar-price-box mt-15">
                                @if($course->price > 0)
                                    <span class="real">{{ handlePrice($course->price) }}</span>
                                @else
                                    <span class="real font-14">{{ trans('public.free') }}</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-15">
                                <span class="font-12 text-gray">
                                    <i class="far fa-clock mr-5"></i>
                                    {{ trans('public.start_date') }}: {{ dateTimeFormat($course->start_date, 'j M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
