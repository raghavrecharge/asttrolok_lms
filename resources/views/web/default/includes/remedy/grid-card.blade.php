<div class="webinar-card remediescss grid-card">
    <figure>
        <div class="image-box str">

            <a href="{{ $remedy->getUrl() }}">
                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $remedy->getImage() }}" class="img-cover" alt="{{ $remedy->title }}">
            </a>

        </div>

        <figcaption class="webinar-card-body">

            <a href="{{ $remedy->getUrl() }}">
                <h3 class="mt-5 webinar-title webinartitle font-weight-bold font-16 text-dark-blue">{{ clean($remedy->title,'title') }}</h3>
            </a>
            <div style="max-height: 26px; overflow:hidden;font-size: 9px;">

        </div>
<hr>

        </figcaption>
    </figure>
</div>
