<div class="webinar-card">
    <figure>
        <div class="image-box">

            @if(!empty($talk->offer_percent))
                <span class="badge badge-danger">{{ $talk->offer_percent }}% OFF</span>
            @elseif(!empty($talk->featured))
                <span class="badge badge-warning">Featured</span>
            @elseif($talk->status == 'upcoming')
                <span class="badge badge-primary">Upcoming</span>
            @elseif($talk->status == 'live')
                <span class="badge badge-secondary">Live</span>
            @else
                <span class="badge badge-secondary">Completed</span>
            @endif

            <a href="{{ route('talks.show', $talk->id) }}">
                <img src="{{ $talk->getImage() }}" class="img-cover" alt="{{ $talk->topic }}">
            </a>

            @if(!empty($talk->progress))
                <div class="progress">
                    <span class="progress-bar" style="width: {{ $talk->progress }}%"></span>
                </div>
            @endif

            @if($talk->status == 'upcoming')
                <a href="{{ $talk->addToCalendarLink() ?? '#' }}" target="_blank" class="webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class="webinar-icon"></i>
                </a>
            @endif
        </div>

        <figcaption class="webinar-card-body">

            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img src="{{ $talk->teacher->getAvatar() ?? '/assets/default/img/avatar.png' }}" class="img-cover" alt="{{ $talk->teacher->full_name ?? 'Speaker' }}">
                </div>
                <a href="{{ $talk->teacher->getProfileUrl() ?? '#' }}" target="_blank" class="user-name ml-5 font-14">
                    {{ $talk->teacher->full_name ?? 'Guest Speaker' }}
                </a>
            </div>

            <a href="{{ route('talks.show', $talk->id) }}">
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">
                    {{ clean($talk->topic,'title') }}
                </h3>
            </a>

            @if(!empty($talk->category))
                <span class="d-block font-14 mt-5">
                    In <a href="#" class="text-decoration-underline">{{ $talk->category->title }}</a>
                </span>
            @endif

            @if(!empty($talk->rating))
                @include(getTemplate() . '.includes.webinar.rate', ['rate' => $talk->rating])
            @endif

            <div class="d-flex justify-content-between mt-5">
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="15" height="15" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ \Carbon\Carbon::parse($talk->date)->format('d M Y') }}</span>
                </div>

                <div class="vertical-line mx-15"></div>

                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="15" height="15" class="webinar-icon"></i>
                    <span class="duration font-14 ml-5">{{ $talk->time }}</span>
                </div>
            </div>

            <div class="webinar-price-box mt-5">
                @if(!empty($talk->price) && $talk->price > 0)
                    <span class="real">{{ handlePrice($talk->price, true, true, false, null, true) }}</span>
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>
