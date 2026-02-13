<div class="webinar-card list-card webinar-list webinar-list-2 d-flex mt-20 loadmorelist">
    <div class="image-box">

            <a href="{{ $product->getUrl() }}">
            <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $product->getImage() }}" class="img-cover" alt="{{ $product->title }}">
        </a>
        <div class="d-flex justify-content-between mt-auto">
            <div class=" h-25 mx-15"></div>
        @include(getTemplate().'.includes.shopping-cart-dropdwon2')
           </div>
    </div>

    <div class="webinar-card-body w-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">

            <a href="{{ $product->getUrl() }}">
                <h3 class="mt-15 webinar-title font-weight-bold font-16 text-dark-blue">{{ clean($product->title,'title') }}</h3>
            </a>
        </div>

        @if(!empty($product->category))
            <span class="d-block font-14 mt-10 hide">{{ trans('public.in') }} <a href="{{ $product->category->getUrl() }}" target="_blank" class="text-decoration-underline">{{ $product->category->title }}</a></span>
        @endif
     <div class="user-inline-avatar d-flex align-items-center mt-10">
            <div class="avatar bg-gray200">
                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $product->creator->getAvatar() }}" class="img-cover" alt="{{ $product->creator->full_name }}">
            </div>
            <a href="{{ $product->creator->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $product->creator->full_name }}</a>
        </div>

        @include(getTemplate() . '.includes.webinar.rate3',['rate' => $product->rate])
        <div class="hrline mt-5"></div>
        <div class="d-flex justify-content-between mt-auto">

            <div class="d-flex align-items-center hide">
                @if($product->delivery_estimated_time)
                <div class="d-flex align-items-center">
                    <i data-feather="clock" width="20" height="20" class="webinar-icon"></i>

                    <span class="duration ml-5 font-14">{{ $product->delivery_estimated_time }} Days</span>
                </div>

                <div class="vertical-line h-25 mx-15"></div>
               @endif
                <div class="d-flex align-items-center">
                    <i data-feather="calendar" width="20" height="20" class="webinar-icon"></i>
                    <span class="date-published ml-5 font-14">{{ dateTimeFormat($product->created_at,'j M Y') }}</span>
                </div>
            </div>

            <div class="webinar-price-box d-flex flex-column justify-content-center align-items-center">
            @if(!empty($product->price) and $product->price > 0)
                        <span class="real">{{ handlePrice($product->price, true, true, false, null, true) }} /-</span>
                @else
                    <span class="real font-14">{{ trans('public.free') }}</span>
                @endif
            </div>

            <div class="d-flex align-items-center homehide1">
            <a href="{{ $product->getUrl() }}">
                <button type="submit" class="btn btn-primary rounded-pill buynow">BUY NOW</button>
                </a>
            </div>
        </div>
    </div>
</div>
