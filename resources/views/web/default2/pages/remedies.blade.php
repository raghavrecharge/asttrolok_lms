@extends('web.default2'.'.layouts.app')
<script>console.log($appFooter);</script>
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/select2/select2.min.css">
           <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/profile.min.css">
   <meta name="robots" content="index, follow">
@endpush

@section('content')
   <div class="profile-cover-card">
        <img src="https://storage.googleapis.com/astrolok/webp/store/1/banner/Remedies.webp" class="img-cover" alt="">
</div>

    <div class="container " style="z-index: 9;max-width: 1140px !important;margin-top: -245px;background-color: #ffffff;position: relative;opacity: 1;border-radius: 24px;"
>

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
