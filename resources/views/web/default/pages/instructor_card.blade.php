@php
    $canReserve = false;
    if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->meetingTimes) and $instructor->meeting->meeting_times_count > 0) {
        $canReserve = true;
    }
@endphp
<style>
@media (min-width: 280px) and (max-width: 425px){
.image-insta1 {
    height: 190px;
}
}
</style>

<div class="rounded-lg shadow-sm   course-teacher-card instructors-list text-left mt-20 d-flex flex-column position-relative">
    @if(!empty($instructor->meeting) and $instructor->meeting->disabled)
        <!--<span class="px-15 py-10 bg-gray off-label text-white font-12">{{ trans('public.unavailable') }}</span>-->
    @elseif(!empty($instructor->meeting) and !empty($instructor->meeting->discount))
        <!--<span class="px-15 py-10 bg-danger off-label text-white font-12">{{ $instructor->meeting->discount }}% {{ trans('public.off') }}</span>-->
    @endif
    <div>
        
        <div class="col-10 col-md-6 col-lg-10" style="padding:0;">
            
            @if(!$canReserve)
            <span class="padding-1 bg-danger off-label text-white font-12 " style="top: 5px;left: 6px;"></span>
        @elseif($canReserve)
        <span class="padding-1 bg-primary  off-label text-white font-12" style="top: 5px;left: 6px;"></span>
        @endif</div>
   
        
    
    
    </div>

    <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class="text-left d-flex flex-column  justify-content-center">
        <div class="position-relative">
            <img loading="lazy"  src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(190) }}" class="img-cover image-insta1" style="border-radius: 7px 8px 0px 0px !important;" alt="{{ $instructor->full_name }}">

            @if($instructor->offline)
                {{-- <span class="user-circle-badge unavailable d-flex align-items-center justify-content-center">
                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                </span> --}}
            @elseif($instructor->verified)
                {{-- <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                    <i data-feather="check" width="20" height="20" class="text-white"></i>
                </span> --}}
            @endif
        </div>

            <div class="stars-card d-flex align-items-center justify-content-end rating-z " >
                <!--@include('web.default.includes.webinar.rate',['rate' => $instructor->rates()])-->
                {{--@include('web.default.includes.webinar.rate1',['rate' => $ratings[$instructor->id]])--}}
                @include('web.default.includes.webinar.rate1',['rate' => $instructor->rating])
            </div>
    
    
        <h3 class=" font-16 font-weight-bold text-dark-blue text-left" style="font-size:1rem !important; margin-left: 10px;">{{ $instructor->full_name }}</h3>
    </a>

    <div class=" font-14 text-gray text-left ">
        @if(!empty($instructor->bio))
        <pre class=" font-13  text-dark-blue " style="margin-left: 11px;font-family: var(--font-family-base) !important;line-height: 1.6;">{{$instructor->bio}}</pre>
        @endif
    </div>

    <!-- <div class="stars-card d-flex align-items-center mt-10">-->
    <!--    @include('web.default.includes.webinar.rate1',['rate' => $instructor->rates()])-->
    <!--</div> -->
    
    <!--<div class="d-flex align-items-center mt-20 >-->
    <!--    @foreach($instructor->getBadges() as $badge)-->
    <!--        <div class="mr-15 mt-5" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($badge->badge_id) ? nl2br($badge->badge->description) : nl2br($badge->description)) !!}">-->
    <!--            <img loading="lazy"  src="{{ !empty($badge->badge_id) ? $badge->badge->image : $badge->image }}" width="32" height="32" alt="{{ !empty($badge->badge_id) ? $badge->badge->title : $badge->title }}">-->
    <!--        </div>-->
    <!--    @endforeach-->
    <!--</div>-->

    <div class="mt-5">
        @if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->amount))
            @if(!empty($instructor->meeting->discount))
                <span class="font-20 text-primary font-weight-bold">{{ handlePrice($instructor->meeting->amount - (($instructor->meeting->amount * $instructor->meeting->discount) / 100)) }}</span>
                <span class="font-14 text-gray text-decoration-line-through ml-10">{{ handlePrice($instructor->meeting->amount) }}</span>
            @else
            <span class="font-20  font-weight-800 instr-price" >{{ handlePrice($instructor->meeting->amount/30) }}</span><span class="text-dark-blue font-12" > / Min</span> 
            @endif
        @else 
            <span class="py-10">&nbsp;</span>
        @endif
    </div>

    <div class="mt-10 d-flex flex-row align-items-center justify-content-center w-100" >
        <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class="btn btn-primary btn-block instr-btn">
            @if($canReserve)
            BOOK NOW
            @else
                {{ trans('public.view_profile') }}
            @endif
        </a>
    </div>
</div>
