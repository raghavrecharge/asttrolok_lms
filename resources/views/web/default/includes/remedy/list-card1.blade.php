<div class="webinar-card webinar-list webinar-list-2 d-flex mt-20">

    <div class="col-8 col-md-6 col-lg-8 webinar-card-body w-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ $remedy->getUrl() }}">
                <h3 class=" webinar-title1 font-weight-bold font-16 text-dark-blue">{{ clean($remedy->title,'title') }}</h3>
            </a>

        </div>
        <div style="max-height: 95px; overflow:hidden;font-size: 11px;">
        <p class="duration font-14 ml-5">{!! $remedy->description !!}</p>
        </div>

        @include(getTemplate() . '.includes.remedy.rate',['rate' => $remedy->getRate()])

    </div>

    <div class="col-4 col-md-6 col-lg-4 py-15">

        <a href="{{ $remedy->getUrl() }}">
            <img src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}" class="img-cover" alt="{{ $remedy->title }}" style="border-radius:10px;">
        </a>

    </div>
</div>
