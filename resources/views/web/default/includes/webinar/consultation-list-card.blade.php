<div class="webinar-card list-card webinar-list webinar-list-2 d-flex mt-20 loadmorelist">
    <div class="image-box">

            <a href="#">
            <img loading="lazy" src="{{ config('app.img_dynamic_url') }}/store/1/Blog thumbnails/01.jpg" class="img-cover" alt="{{ $consultation->title }}">
        </a>
        <div class="d-flex justify-content-between mt-auto">
            <div class=" h-25 mx-15"></div>
        @include(getTemplate().'.includes.shopping-cart-dropdwon2')
           </div>
    </div>

    <div class="webinar-card-body w-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            @if($consultation->consultation_type == 'specific')
            <a href="#">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">Book a consultation with our consultant</h3>
            </a>
            @elseif($consultation->consultation_type == 'range')
            <a href="#">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">Find and book your preferred consultant on our portal, with price ranges</h3>
            </a>
            @else
            <a href="#">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">Book your preferred consultant through our portal</h3>
            </a>
            @endif
        </div>

        @if($consultation->consultation_type == 'specific' && $consultation->consultant_id)
            <div class="user-inline-avatar d-flex align-items-center mt-10">
                <div class="avatar bg-gray200">
                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ optional($userConsultants->firstWhere('id', $consultation->consultant_id ))->getAvatar() }}" class="img-cover" alt="{{optional($userConsultants->firstWhere('id', $consultation->consultant_id ))->full_name}}">
                </div>
                <a href="{{ optional($userConsultants->firstWhere('id', $consultation->consultant_id ))->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{optional($userConsultants->firstWhere('id', $consultation->consultant_id ))->full_name}}</a>
            </div>

            @include(getTemplate() . '.includes.webinar.rate3',['rate' => optional($userConsultants->firstWhere('id', $consultation->consultant_id ))->rating])
        @endif
        <div class="hrline mt-5"></div>
        <div class="d-flex justify-content-between mt-auto">
            @php
                $amount = optional(optional($userConsultants->firstWhere('id', $consultation->consultant_id))->meeting)->amount;
            @endphp
            <div class="d-flex align-items-center hide">
                @if($consultation->slot_time)
                    @if($consultation->slot_time == 'both')
                    <div class="d-flex align-items-center">
                        <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                        <span class="duration ml-5 font-14">15 mins or 30 mins </span>
                    </div>

                    <div class="vertical-line h-25 mx-15"></div>
                    @else
                    <div class="d-flex align-items-center">
                        <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>
                        <span class="duration ml-5 font-14">{{ $consultation->slot_time }} mins </span>

                    </div>

                    <div class="vertical-line h-25 mx-15"></div>
                   @endif
                @endif

            </div>

            <div class="webinar-price-box d-flex flex-column justify-content-center align-items-center">

                @if($consultation->consultation_type == 'specific' && $consultation->slot_time)
                    @if($consultation->slot_time == 'both')
                        <span class="real">₹{{ $amount / 2 }} - ₹{{ $amount }} /-</span>
                    @elseif($consultation->slot_time == 15)
                        <span class="real">₹{{ $amount / 2 }} /-</span>
                    @elseif($consultation->slot_time == 30)
                        <span class="real">₹{{ $amount }} /-</span>
                    @endif
                @endif

                @if($consultation->consultation_type == 'range' && $consultation->starting_price)
                    <span class="real">
                        {{ handlePrice($consultation->starting_price, true, true, false, null, true) }} -
                        {{ handlePrice($consultation->ending_price, true, true, false, null, true) }}
                    </span>
                @endif
        </div>

        </div>
    </div>
</div>
