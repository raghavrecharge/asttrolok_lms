<div class="webinar-card webinar-list webinar-list-2 d-flex mt-20">

    <div class="col-7 col-md-6 col-lg-8 webinar-card-body w-100 d-flex flex-column" style="max-height: 128px;">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ $post->getUrl() }}">
                <h3 class=" webinar-title1 font-weight-bold font-16 text-dark-blue">{{ clean($post->title,'title') }}</h3>
            </a>

        </div>
        <div style="max-height: 100px; overflow:hidden;font-size: 11px;">
        <p class="duration font-14 ml-5">{!! $post->description !!}</p>
        </div>

    </div>

    <div class="col-5 col-md-6 col-lg-4 py-15" style="padding-left:0;">

        <a href="{{ $post->getUrl() }}">
            <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $post->image }}" class="img-cover" alt="{{ $post->title }}" style="border-radius:10px; object-fit: unset;">
        </a>

    </div>
</div>
