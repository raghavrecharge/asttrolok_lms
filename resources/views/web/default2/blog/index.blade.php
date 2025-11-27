@extends('web.default2'.'.layouts.app')
@section('head')
    @if($blog->lastPage() > 1)
        @if($blog->currentPage() > 1)
            <link rel="prev" href="{{ $blog->previousPageUrl() }}">
        @endif

        @if($blog->hasMorePages())
            <link rel="next" href="{{ $blog->nextPageUrl() }}">
        @endif
    @endif

    <link rel="canonical" href="{{ $blog->url($blog->currentPage()) }}">
@endsection
@section('content')

    <section class="site-top-banner search-top-banner opacity-04 position-relative" >
        <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ getPageBackgroundSettings('blog') }}" class="img-cover" alt="{{ $pageTitle }}"/>

        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">
                    <div class="top-search-categories-form">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container mt-10 mt-md-40">
        <div class="row">
            <div class="col-12 col-lg-8">
                <h1 class=" font-30 mb-15">{{ $pageTitle }}</h1>
                <div class="row">
                    @foreach($blog as $post)
                        <div class="col-12 col-md-4 col-lg-6">
                            <div class="mt-30">
                                @include('web.default.blog.grid-list',['post' => $post])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 col-lg-4">

                <div class="p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('categories.categories') }}</h3>

                    <div class="pt-15">
                        @foreach($blogCategories as $blogCategory)
                            <a href="{{ route('blog.category', ['category' => $blogCategory->slug]) }}" class="font-14 text-dark-blue d-block mt-15">{{ $blogCategory->title }}</a>
                        @endforeach

                    </div>
                </div>

                <div class="p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('site.popular_posts') }}</h3>

                    <div class="pt-15">

                        @foreach($popularPosts as $popularPost)
                            <div class="popular-post d-flex align-items-start mt-20">
                                <div class="popular-post-image rounded">
                                    <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $popularPost->image }}" class="img-cover rounded" alt="{{ $popularPost->title }}">
                                </div>
                                <div class="popular-post-content d-flex flex-column ml-10">
                                    <a href="{{ $popularPost->getUrl() }}">
                                        <h3 class="text-dark-blue font-14">{{ truncate($popularPost->title,50) }}</h3>
                                    </a>
                                    <span class="mt-auto font-12 text-gray">{{ dateTimeFormat($popularPost->created_at, 'j M Y') }}</span>
                                </div>
                            </div>
                        @endforeach

                        <a href="/blog" class="btn btn-sm btn-primary btn-block mt-30">{{ trans('home.view_all') }} {{ trans('site.posts') }}</a>
                    </div>
                </div>

                <div class="p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">Popular Courses</h3>

                    <div class="pt-15">
                        @php
                        $courses = array( 2033, 2036, 2063);
                        @endphp

                        @foreach($popularWebinars as $popularWebinar)

                            @if(in_array($popularWebinar->id, $courses))
                            <div class="popular-post d-flex align-items-start mt-20">
                                <div class="popular-post-image rounded">
                                    <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{$popularWebinar->thumbnail}}" class="img-cover rounded" alt="{{$popularWebinar->title}}">
                                </div>
                                <div class="popular-post-content d-flex flex-column ml-10">
                                    <a href="/course/{{$popularWebinar->slug}}">
                                        <h3 class="text-dark-blue font-14">{{ truncate($popularWebinar->title,50) }}</h3>
                                    </a>
                                    <span class="mt-auto font-12 text-gray">{{$popularWebinar->bestTicket()}}</span>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        <a href="/classes?sort=newest" class="btn btn-sm btn-primary btn-block mt-30">{{ trans('home.view_all') }} Courses</a>
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-20 mt-md-50 pt-30">
            {{ $blog->appends(request()->input())->links('vendor.pagination.panel') }}
        </div>

    </section>
@endsection
