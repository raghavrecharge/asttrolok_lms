@extends(getTemplate().'.layouts.app')

@push('styles_top')
<link rel="stylesheet" href="/assets/design_1/css/mobile-home-new.css">
<link rel="stylesheet" href="/public/assets/design_1/css/home_mobile_css/index.css">
<link rel="stylesheet" href="/assets/design_1/css/home_mobile_css/style.css">
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
@include('web.default.home.partials._all_css')
@endpush

@section('content')
<div class="home-container">
    <div class="home-home">

        @include('web.default.home.partials._pathshala_slider', ['data' => $pathshalaOffers ?? []])
        @include('web.default.includes.search')
        @include('web.default.home.partials._personilize_section')
        @include('web.default.home.partials._categories_mobile', ['data' => $categories_mobile ?? []])
        @include('web.default.home.partials._banner_slider')
        @include('web.default.home.partials._hindi_courses_header')
        @include('web.default.home.partials._hindi_courses_slider', ['data' => $hindiWebinars ?? []])
        @include('web.default.home.partials._english_courses_header')
        @include('web.default.home.partials._english_courses_slider', ['data' => $englishclasses ?? []])
        @include('web.default.home.partials._featured_booked', ['featuredBook' => $featuredBook ?? []])
        @include('web.default.home.partials._consultants_slider', ['data' => $consultant ?? []])
        @include('web.default.home.partials.pathshala_banner')
        @include('web.default.home.partials._remedies_slider', ['data' => $remedies ?? []])        
        @include('web.default.home.partials.instagram_sescion')
        @include('web.default.home.partials._youtube_videos_slider', ['data' => $youtubeVideos ?? []])
        @include('web.default.home.partials._youtube_channels_slider', ['data' => $channels ?? []])
        @include('web.default.home.partials._google_reviews')
        @include('web.default.home.partials._rating_review')

    </div>
</div>

@include('web.default.home.partials._all_js')
@endsection
