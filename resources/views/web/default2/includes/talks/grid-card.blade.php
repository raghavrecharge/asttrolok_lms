<div class="webinar-card">
    <figure>
        <div class="image-box">

            <a href="{{ route('talks.show', $talk->id) }}">
                <img src="{{ $talk->thumbnail ?? '/assets/default/img/default-talk.png' }}" class="img-cover" alt="{{ $talk->topic }}">
            </a>
        </div>
<figcaption class="webinar-card-body">
            <div class="user-inline-avatar d-flex align-items-center">
    <div class="avatar bg-gray200">
        <img src="{{ $talk->speaker->avatar ?? '/assets/default/img/avatar.png' }}"
             class="img-cover"
             alt="">
    </div>
    <a href="#" target="_blank" class="user-name ml-5 font-14">
        {{ $talk->speaker->full_name ?? 'Guest Speaker' }}
    </a>
</div>

                        <a href="https://api.asttrolok.in/course/astroshiromani-2025">
                            <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue"> {{ clean($talk->topic,'title') }}</h3>
            </a>

                            <span class="d-block font-14 mt-5">in <a href="/categories/astrology/Astrology-Basic" target="_blank" class="text-decoration-underline">Astrology</a></span>

                                         <div class="webinar-price-box mt-5 d-flex justify-content-between align-items-center">

    <span class="real">Free</span>

    <a href="{{ route('talks.register', $talk->slug) }}" class="btn btn-primary">
        Register
    </a>
</div>
            <div class="d-flex justify-content-between mt-3">
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="15" height="15" class="webinar-icon"></i>
                    <span class="date-published font-14 ml-5">{{ \Carbon\Carbon::parse($talk->date_time)->format('d M Y') }}</span>
                </div>

                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="15" height="15" class="webinar-icon"></i>
                    <span class="duration font-14 ml-5">{{ \Carbon\Carbon::parse($talk->date_time)->format('h:i A') }}</span>
                </div>
            </div>
        </figcaption>

    </figure>
</div>
