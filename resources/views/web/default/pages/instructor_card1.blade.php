@php
    $canReserve = false;
    if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->meetingTimes) ) {
        $canReserve = true;
    }
@endphp

<div class="rounded-lg shadow-sm mt-20 px-15 course-teacher-card instructors-list text-left d-flex align-items-left flex-column position-relative">
    <div class="row can-reserve">
        
        <div class="col-10 col-md-6 col-lg-10" style="padding:0;">
            
            @if(!$canReserve)
            <span class="padding-1 bg-danger off-label text-white font-12 "></span>
        @elseif($canReserve)
        <span class="padding-1 bg-primary  off-label text-white font-12"></span>
        @endif</div>
   
        
         <div class="col-2 col-md-6 col-lg-2" style="padding:0;">
            <div class="stars-card d-flex align-items-center ">
                <!--@include('web.default.includes.webinar.rate',['rate' => $instructor->rates()])-->
                {{-- @include('web.default.includes.webinar.rate1',['rate' => $ratings[$instructor->id]]) --}}
                @include('web.default.includes.webinar.rate1',['rate' => $instructor->rating])
            </div>
    
    </div>
    </div>
    @if(!empty($instructor->meeting) and !empty($instructor->meeting->discount))
        <span class="px-15 py-10 bg-danger off-label1 text-white font-12">{{ $instructor->meeting->discount }}% {{ trans('public.off') }}</span>
    @endif
<div class="row margin-31">
    <div class="col-4 col-md-6 col-lg-4" style="padding:0;">
    
        <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class="text-left d-flex flex-column align-items-left justify-content-left">
            <div class="position-relative">
                
                <img src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(190) }}" class="rounded-lg1 image-insta img-cover" alt="{{ $instructor->full_name }}">
    
                {{-- @if($instructor->offline)
                    <span class="user-circle-badge unavailable d-flex align-items-left justify-content-left">
                    <i data-feather="slash" width="20" height="20" class="text-white"></i>
                    </span>
                @elseif($instructor->verified)
                    <span class="user-circle-badge has-verified d-flex align-items-left justify-content-left">
                        <i data-feather="check" width="20" height="20" class="text-white"></i>
                    </span>
                @endif --}}
                
            </div>
        
    
            
        </a>
    </div>
    
    <div class="col-8 col-md-6 col-lg-8" style="display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    justify-content: center;" >
        
        <h3 class="font-16 font-weight-bold text-dark-blue text-left mr-50">{{ $instructor->full_name }}</h3>
        <div class=" font-14 text-gray text-left ">
            @if(!empty($instructor->bio))
            <pre class=" font-13  text-dark-blue " style="font-family: var(--font-family-base) !important;">{{$instructor->bio}}</pre>
            @endif
        </div>
           
           
    
        <!--<div class="d-flex align-items-left mt-20 >-->
        <!--    @foreach($instructor->getBadges() as $badge)-->
        <!--        <div class="mr-15 mt-5" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($badge->badge_id) ? nl2br($badge->badge->description) : nl2br($badge->description)) !!}">-->
        <!--            <img src="{{ !empty($badge->badge_id) ? $badge->badge->image : $badge->image }}" width="32" height="32" alt="{{ !empty($badge->badge_id) ? $badge->badge->title : $badge->title }}">-->
        <!--        </div>-->
        <!--    @endforeach-->
        <!--</div>-->
        <div class="row ">
    <div class="col-6 col-md-6 col-lg-6">
        <div class="">
            @if(!empty($instructor->meeting) and !$instructor->meeting->disabled and !empty($instructor->meeting->amount))
                @if(!empty($instructor->meeting->discount))
                    <span class="font-20 text-primary font-weight-bold">{{ handlePrice($instructor->meeting->amount - (($instructor->meeting->amount * $instructor->meeting->discount) / 100)) }}</span>
                    <span class="font-14 text-gray text-decoration-line-through ml-10">{{ handlePrice($instructor->meeting->amount) }}</span>
                @else
                    <span class="font-20 text-primary font-weight-500">{{ handlePrice($instructor->meeting->amount/30)}}</span><span class="text-dark-blue font-12" > / Min</span> 
                @endif
            @else
                <span class="py-10">&nbsp;</span>
            @endif
        </div>
    
        
    </div>
    <div class="col-6 col-md-6 col-lg-6" style="padding-bottum:5px;">
        <div class=" align-items-right justify-content-right w-100" >
            <a href="{{ $instructor->getProfileUrl() }}{{ ($canReserve) ? '?tab=appointments' : '' }}" class="btn1 btn-primary btn-block" style="font-weight: bold; height: 30px;">
                @if($canReserve)
                BOOK NOW
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
