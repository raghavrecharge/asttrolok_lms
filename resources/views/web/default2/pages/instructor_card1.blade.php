@php
    $canReserve = false;
    if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->meetingTimes) and $instructor->meeting->meeting_times_count > 0) {
        $canReserve = true;
    }
@endphp

<div class="rounded-lg shadow-sm mt-20 px-25 py-15 course-teacher-card instructors-list text-left d-flex align-items-left flex-column position-relative">
    <div class="row">
        <div class="col-11 col-md-6 col-lg-11" style="padding:0;"></div>
         <div class="col-1 col-md-6 col-lg-1" style="padding:0;">
    @if(!$canReserve)
        <span class="px-10 py-10 bg-danger off-label text-white font-12"></span>
    @elseif($canReserve)
    <span class="px-10 py-10 bg-primary  off-label text-white font-12"></span>
    @endif
    </div>
    </div>
    @if(!empty($instructor->meeting) and !empty($instructor->meeting->discount))
        <span class="px-15 py-10 bg-danger off-label1 text-white font-12">{{ $instructor->meeting->discount }}% {{ trans('public.off') }}</span>
    @endif
<div class="row">
    <div class="col-3 col-md-6 col-lg-3" style="padding:0;">

        <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class="text-left d-flex flex-column align-items-left justify-content-left">
            <div class=" teacher-avatar mt-5 position-relative">

                <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(190) }}" class="img-cover" alt="{{ $instructor->full_name }}">

                @if($instructor->offline)
                    <span class="user-circle-badge unavailable d-flex align-items-left justify-content-left">
                    <i data-feather="slash" width="20" height="20" class="text-white"></i>
                    </span>
                @elseif($instructor->verified)
                    <span class="user-circle-badge has-verified d-flex align-items-left justify-content-left">
                        <i data-feather="check" width="20" height="20" class="text-white"></i>
                    </span>
                @endif

            </div>
        <div class="stars-card d-flex align-items-center mt-10">

            @include('web.default2.includes.webinar.rate1',['rate' => $instructor->rating])
        </div>

        </a>
    </div>

    <div class="col-9 col-md-6 col-lg-9" >
        <h3 class="font-16 font-weight-bold text-dark-blue text-left  ml-10">{{ $instructor->full_name }}</h3>
        <div class="mt-5 font-14 text-gray text-left  ml-10">
            @if(!empty($instructor->bio))
            <pre class="mt-10 font-13  text-dark-blue " style="font-family: var(--font-family-base) !important;">{{$instructor->bio}}</pre>
            @endif
        </div>

            @php
            if(isset($ratings[$instructor->id]))
            {

            @endphp

        @php
           }

            @endphp

        <div class="row ">
    <div class="col-7 col-md-7 col-lg-7">
        <div class="mt-15 pl-10">
            @if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->amount))
                @if(!empty($instructor->meeting->discount))
                    <span class="font-20 text-primary font-weight-bold">{{ handlePrice($instructor->meeting->amount - (($instructor->meeting->amount * $instructor->meeting->discount) / 100)) }}</span>
                    <span class="font-14 text-gray text-decoration-line-through ml-10">{{ handlePrice($instructor->meeting->amount) }}</span>
                @else
                    <span class="font-20 text-primary font-weight-500">{{ handlePrice($instructor->meeting->amount/30) }}</span><span class="text-dark-blue" style="font-size: small!important;"> / Min</span>
                @endif
            @else
                <span class="py-10">&nbsp;</span>
            @endif
        </div>

    </div>
    <div class="col-5 col-md-5 col-lg-5" style="padding:0;">
        <div class=" align-items-right justify-content-right w-100" >
            <a href="{{ $instructor->getProfileUrl() }}{{ $instructor->full_name ? '?tab=appointments' : '' }}" class="btn btn-primary btn-block" style="padding: 0px!important;height: 36px;">
                @if($canReserve)
                Book Now
                @else
                    {{trans('public.view_profile')}}
                @endif
            </a>
        </div>
    </div>
    </div>
    </div>

</div>

</div>
