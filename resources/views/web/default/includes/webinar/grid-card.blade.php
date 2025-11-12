<div class="webinar-card grid-card">
    <figure>
       
        <div class="image-box str">
            <div class="star-rating">
               {{-- @include(getTemplate() . '.includes.webinar.rate1',['rate' => $webinar->getRate()]) --}}
               @include(getTemplate() . '.includes.webinar.rate1',['rate' => $webinar->course_rate])
                </div>
            @if($webinar->bestTicket() < $webinar->price)
                <span class="badge badge-danger hide">{{ trans('public.offer',['off' => number_format($webinar->bestTicket(true)['percent'])]) }}</span>
            @elseif(empty($isFeature) and !empty($webinar->feature))
                <span class="badge badge-warning hide">{{ trans('home.featured') }}</span>
            @elseif($webinar->type == 'webinar')
                @if($webinar->start_date > time())
                    <span class="badge badge-primary hide">{{  trans('panel.not_conducted') }}</span>
                @elseif($webinar->isProgressing())
                    <span class="badge badge-secondary hide">{{ trans('webinars.in_progress') }}</span>
                @else
                    <span class="badge badge-secondary hide">{{ trans('public.finished') }}</span>
                @endif
            @elseif(!empty($webinar->type))
                <span class="badge badge-primary hide">{{ trans('webinars.'.$webinar->type) }}</span>
            @endif

           {{-- @if($webinar->slug == "astromani-2024" or $webinar->slug == "astroshiromani-2024" or $webinar->slug == "astrology-basic-level")
            <a href="/landingpage/{{ $webinar->slug }}">
            @else --}}
            <a href="{{ $webinar->getUrl() }}">
           {{-- @endif --}}
                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImage() }}" class="img-cover" alt="{{ $webinar->title }}">
            </a>
            <div class="d-flex justify-content-between mt-auto">
                <div class=" h-25 mx-15"></div>
                 <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $webinar->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">
                       
            @include(getTemplate().'.includes.shopping-cart-webinar')
            </form>
               </div>

            @if($webinar->checkShowProgress())
                <div class="progress">
                    <span class="progress-bar" style="width: {{ $webinar->getProgress() }}%"></span>
                </div>
            @endif

            @if($webinar->type == 'webinar')
                <a href="{{ $webinar->addToCalendarLink() }}" target="_blank" class="webinar-notify d-flex align-items-center justify-content-center">
                    <i data-feather="bell" width="20" height="20" class="webinar-icon"></i>
                </a>
            @endif
        </div>

        <figcaption class="webinar-card-body">
          {{--  @if($webinar->slug == "astromani-2024" or $webinar->slug == "astroshiromani-2024" or $webinar->slug == "astrology-basic-level")
            <a href="/landingpage/{{ $webinar->slug }}">
            @else --}}
            <a href="{{ $webinar->getUrl() }}">
           {{-- @endif --}}
                <h3 class="mt-5 webinar-title webinartitle font-weight-bold font-16 text-dark-blue">{{ clean($webinar->title,'title') }}</h3>
            </a>
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $webinar->teacher->getAvatar() }}" class="img-cover" alt="{{ $webinar->teacher->full_name }}">
                </div>
                <a href="{{ $webinar->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $webinar->teacher->full_name }}</a>
            </div>
            <hr />
          
{{-- 
            @if(!empty($webinar->category))
                <span class="d-block font-14 mt-5">{{ trans('public.in') }} <a href="{{ $webinar->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $webinar->category->title }}</a></span>
            @endif --}}

            <!--@include(getTemplate() . '.includes.webinar.rate',['rate' => $webinar->getRate()])-->
           

            <!--<div class="d-flex justify-content-between mt-5">-->
            <!--    <div class="d-flex align-items-center">-->
            <!--        <i data-feather="clock" width="15" height="15" class="webinar-icon"></i>-->
            <!--        <span class="duration font-14 ml-5">{{ convertMinutesToHourAndMinute($webinar->duration) }} {{ trans('home.hours') }}</span>-->
            <!--    </div>-->

            <!--    <div class="vertical-line mx-15"></div>-->

            <!--    <div class="d-flex align-items-center">-->
            <!--        <i data-feather="calendar" width="15" height="15" class="webinar-icon"></i>-->
            <!--        <span class="date-published font-14 ml-5">{{ dateTimeFormat(!empty($webinar->start_date) ? $webinar->start_date : $webinar->created_at,'j M Y') }}</span>-->
            <!--    </div>-->
            <!--</div>-->

            <div class="webinar-price-box mt-5">
            @if(!empty($isRewardCourses) and !empty($webinar->points))
                    <span class="text-warning real font-14">{{ $webinar->points }} {{ trans('update.points') }}</span>
                @elseif(!empty($webinar->price) and $webinar->price > 0)
                    @if($webinar->bestTicket() < $webinar->price)
                        <span class="real">{{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }} /-</span>
                        <span class="off ml-10 hide">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                    @else
                        <span class="real">{{ handlePrice($webinar->price, true, true, false, null, true) }} /-</span>
                    @endif
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
              {{--  @if($webinar->slug == "astromani-2024" or $webinar->slug == "astroshiromani-2024" or $webinar->slug == "astrology-basic-level")
            <a href="/landingpage/{{ $webinar->slug }}">
            @else --}}
            <a href="{{ $webinar->getUrl() }}">
            {{-- @endif --}}
                    <button type="submit" class="btn btn-primary rounded-pill buynow homehide1">{{isset($hasBoughtCourse)?(in_array($webinar->id,$hasBoughtCourse)?'DETAILS':'BUY NOW'):'BUY NOW'}}</button>
                    </a>
                
            </div>
        </figcaption>
    </figure>
</div>
