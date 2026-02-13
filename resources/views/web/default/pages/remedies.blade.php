@extends(getTemplate().'.layouts.app')
<script  >console.log($appFooter);</script>
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-courses.css">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-remedies.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

     <style>
     .webinar-card  .image-box {
        position: relative;
        width: 100%;
        height: 350px !important;
    }

    .remediescss .image-box {
        position: relative;
        width: 100%;
        height: 350px !important;
    }
    @media (min-width: 320px) and (max-width: 424px) {
    .webinar-card.grid-card .image-box {
        position: relative;
        width: 100%;
        height: 350px !important;
    }
}
@media (min-width: 424px) and (max-width: 660px) {
    .webinar-card.grid-card .image-box {
        position: relative;
        width: 100%;
        height: 350px !important;
    }
}
</style>
@endpush

@section('content')
    <section class="mobile-home-slider site-top-banner search-top-banner opacity-04 position-relative"style="height: 135px !important ;">
        <img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/Remedies/shloka/Mantra Remedies - Banner - 1351X401.jpg" class="banner-redius img-cover" style="height: auto;" alt=""/>

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mt-30">
         <form action="/remedies{{$slug??''}}" method="get" id="filtersForm">

        @include('web.default.pages.includes.top_filters_remedies')
         </form>

        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">

                <div class="row mt-20">
                    <div class="col-12 col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row">
                                @foreach($remedies as $remedy)
                                    <div class="col-12 col-lg-4 mt-20 loadid mobilegrid">
                                        @include('web.default.includes.remedy.grid-card',['remedy' => $remedy])
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($remedies as $remedy)
                            <div class="mt-20 load-card-list">
                            @include('web.default.includes.remedy.list-card',['remedy' => $remedy])
                             </div>
                                 @endforeach
                        @endif

                    </div>

                </div>

            <div class="mt-50 pt-30">
                {{ $remedies->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        </section>
    </div>

@endsection

@push('scripts_bottom')
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/select2/select2.min.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/categories.min.js"></script>
     <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/instructors.min.js"></script>
@endpush
