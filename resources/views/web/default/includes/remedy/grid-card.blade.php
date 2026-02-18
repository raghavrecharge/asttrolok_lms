<div class="webinar-card remediescss grid-card">
    <figure>
        <div class="image-box str" style="height: auto !important;">

            <a href="{{ $remedy->getUrl() }}">
                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}" class="img-cover" alt="{{ $remedy->title }}">
            </a>

        </div>

        <figcaption class="webinar-card-body">

            <a href="{{ $remedy->getUrl() }}">
                <h3 class="mt-5 webinar-title webinartitle font-weight-bold font-16 text-dark-blue">{{ clean($remedy->title,'title') }}</h3>
            </a>
       

        </figcaption>
    </figure>
</div>
