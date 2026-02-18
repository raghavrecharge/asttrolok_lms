@extends('web.default2'.'.layouts.app')

@push('styles_top')
<link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app.css">
@endpush

@section('content')
    @if((!empty($webinars) and count($webinars)) or (!empty($products) and count($products)) or (!empty($teachers) and count($teachers)) or (!empty($organizations) and count($organizations) ) or (!empty($remedies) and count($remedies) ) or (!empty($subscriptions) and count($subscriptions) ))
    
        <section class="site-top-banner search-top-banner opacity-04 position-relative" style="background-color: var(--secondary);">

            <div class="container h-100">
                <div class="row h-100 align-items-center justify-content-center text-center">
                    <div class="col-12 col-md-9 col-lg-7">
                        <div class="top-search-form">
                            <h1 class="text-white font-30">{!! nl2br(trans('site.result_find',['count' => $resultCount , 'search' => request()->get('search')])) !!}</h1>

                            <div class="search-input bg-white p-10 flex-grow-1">
                                <form action="/search" method="get">
                                    <div class="form-group d-flex align-items-center m-0">
                                        <input type="text" name="search" class="form-control border-0" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                        <button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
       @foreach($subscriptions as $subscription)
    @if(!empty($subscription))
    @if($subscription->private == 0)
        <div class="col-12 col-lg-4 mt-20">
            @include('web.default2.includes.subscription.grid-card',['subscription' => $subscription])
        </div>
    @endif
    @endif
@endforeach

            @if(!empty($webinars) and count($webinars))
                <section class="mt-50">
                    <h2 class="font-24 font-weight-bold text-secondary">{{ trans('webinars.webinars') }}</h2>

                    <div class="row">
                        @foreach($webinars as $webinar)
                            <div class="col-md-6 col-lg-4 mt-30">
                                @include('web.default2.includes.webinar.grid-card',['webinar' => $webinar])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($remedies) and count($remedies))
                <section class="mt-50">
                    <h2 class="font-24 font-weight-bold text-secondary">Remedies</h2>

                    <div class="row">
                        @foreach($remedies as $remedy)
                            <div class="col-md-6 col-lg-4 mt-30">
                                @include('web.default2.includes.remedy.grid-card',['remedy' => $remedy])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($products) and count($products))
                <section class="mt-50">
                    <h2 class="font-24 font-weight-bold text-secondary">{{ trans('update.products') }}</h2>

                    <div class="row">
                        @foreach($products as $product)
                            <div class="col-md-6 col-lg-4 mt-30">
                                @include('web.default2.products.includes.card',['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if(!empty($teachers) and count($teachers))
                <section class="mt-50">

                    <h2 class="font-24 font-weight-bold text-secondary">Instructors</h2>

                    <div class="row">
                        @foreach($teachers as $teacher)
                            <div class="col-6 col-md-3 col-lg-2 mt-30 mx-10" style="background-color: #fff;padding: 25px;border-radius: 10px;">
                                <div class="user-search-card text-center d-flex flex-column align-items-center justify-content-center">
                                    <div class="user-avatar">
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $teacher->getAvatar() }}" class="img-cover rounded-circle" alt="{{ $teacher->full_name }}">
                                    </div>
                                    <a href="{{ $teacher->getProfileUrl() }}">
                                        <h4 class="font-16 font-weight-bold text-dark-blue mt-10">{{ $teacher->full_name }}</h4>
                                        <span class="d-block font-14 text-gray mt-5">{{ $teacher->bio }}</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
    @else

        <div class="no-result status-failed my-50 d-flex align-items-center justify-content-center flex-column">
            <div class="no-result-logo">
                <img src="{{ config('app.js_css_url') }}/assets/default/img/no-results/search.png" alt="">
            </div>
            <div class="container">
                <div class="row h-100 align-items-center justify-content-center text-center">
                    <div class="col-12 col-md-9 col-lg-7">
                        <div class="d-flex align-items-center flex-column mt-30 text-center w-100">
                            <h2>{{ trans('site.no_result_search') }}</h2>
                            <p class="mt-5 text-center">{!! trans('site.no_result_search_hint',['search' => request()->get('search')]) !!}</p>

                            <div class="search-input bg-white p-10 mt-20 flex-grow-1 shadow-sm rounded-pill w-100">
                                <form action="/search" method="get">
                                    <div class="form-group d-flex align-items-center m-0">
                                        <input type="text" name="search" class="form-control border-0" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                        <button type="submit" class="btn btn-primary rounded-pill">{{ trans('home.find') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
