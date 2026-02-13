@extends('web.default2'.'.layouts.app')
<script>console.log($appFooter);</script>
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
    <meta name="robots" content="noindex, nofollow" />
@endpush

@section('content')
    <section class="site-top-banner search-top-banner opacity-04 position-relative">
        <img src="https://storage.googleapis.com/astrolok/store/1/Remedies/shloka/Mantra Remedies - Banner - 1351X401.jpg" loading="lazy"class="img-cover" alt="" />

    </section>

    <div class="container mt-30">

        <section class="mt-lg-50 pt-lg-20 mt-md-40 pt-md-40">
            <form action="/classes" method="get" id="filtersForm">

                <div class="row mt-20">
                    <div class="col-12 col-lg-12">

                        @if(empty(request()->get('card')) or request()->get('card') == 'grid')
                            <div class="row">
                                @foreach($remedies as $remedy)
                                    <div class="col-6 col-lg-3 mt-20">
                                        @include('web.default2.includes.remedy.grid-card',['remedy' => $remedy])
                                    </div>
                                @endforeach
                            </div>

                        @elseif(!empty(request()->get('card')) and request()->get('card') == 'list')

                            @foreach($remedies as $remedy)
                                @include('web.default2.includes.remedy.list-card',['remedy' => $remedy])
                            @endforeach
                        @endif

                    </div>

                </div>

            </form>
            <div class="mt-50 pt-30">
                {{ $remedies->appends(request()->input())->links('vendor.pagination.panel') }}
            </div>
        </section>
    </div>

@endsection

@push('scripts_bottom')
    <script defer src="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script defer src="{{ config('app.js_css_url') }}/assets2/default/js/parts/categories.min.js"></script>
@endpush
