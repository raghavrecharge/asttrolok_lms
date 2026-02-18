@php
    $canReserve = false;
    if (
        !empty($instructor->meeting) and
        !$instructor->meeting->disabled and
        !empty($instructor->meeting->meetingTimes) and
        $instructor->meeting->meeting_times_count > 0
    ) {
        $canReserve = true;
    }
@endphp

    <div class="card" style="cursor: pointer;" onclick="window.location.href='{{ $instructor->getProfileUrl() }}{{ $instructor->full_name ? '?tab=appointments' : '' }}'">
        <div class="profile-image">
            @if (!$canReserve)
                            <span class="px-10 py-10 bg-danger off-label text-white font-12 "
                                style="
                position: absolute;
                top: 5px;
                left: 10px;
                border-radius: 16px;
            "></span>
                        @elseif($canReserve)
                            <span class="px-10 py-10 bg-primary  off-label text-white font-12 "style="
                position: absolute;
                top: 5px;
                left: 10px;
                border-radius: 16px;
            "></span>
            @endif
            @if (!empty($instructor->meeting) and !empty($instructor->meeting->discount))
                <span class="px-15 py-10 bg-danger off-label1 text-white font-12 ">{{ $instructor->meeting->discount }}%
                    {{ trans('public.off') }}</span>
            @endif
            <img src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(190) }}" class="img-cover"
                alt="{{ $instructor->full_name }}">
        </div>

        <h1 class="name">{{ $instructor->full_name }}</h1>
        <p class="specialization" style="min-height: 40px;">
            @if (!empty($instructor->bio))
                <span class=" "
                    style="font-family: var(--font-family-base) !important;font-size: 13px;  min-height: 40px;  color: #666666;">
                    {{ $instructor->bio }}</span>
            @endif
        </p>

        @if (!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->amount))
            @if (!empty($instructor->meeting->discount))
                <div class="pricing">
                    <span class="price">{{ handlePrice($instructor->meeting->amount / 30) }} / </span>
                    <span class="per-min">Per Min</span>
                </div>
            @else
                <div class="pricing">
                    <span class="price">{{ handlePrice($instructor->meeting->amount / 30) }} / </span>
                    <span class="per-min">Per Min</span>
                </div>
            @endif
        @else
            <div class="pricing">
                <span class="price">&nbsp;</span>

            </div>

        @endif

        @include('web.default2.includes.webinar.rate1', ['rate' => $instructor->rating])

        <a href="{{ $instructor->getProfileUrl() }}{{ $instructor->full_name ? '?tab=appointments' : '' }}"
            class="book-btn"
            style="padding: 10px!important;height: 35px;border-radius: 83.15px;opacity: 1;font-size: 13px;">
            @if ($canReserve)
                Book Now
            @else
                {{ trans('public.view_profile') }}
            @endif
        </a>
    </div>
