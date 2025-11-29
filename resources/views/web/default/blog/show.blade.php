@extends(getTemplate().'.layouts.app')
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
    <style>
        .cart-banner {
    padding: 10px 0;
}
.cart-banner {
    background-color: #ffffff;
}
.radius-20 {
    border-radius:20px !important;
}
            @media (max-width: 657px) {
  .homehide{
    display: none !important;
  }
}
    </style>
@endpush
@section('content')
    <section class="cart-banner position-relative  ">
        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center ">
                <div class="col-12 col-md-9 col-lg-7">
                    <div style="display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    align-items: center;
    justify-content: space-between;
}">
<a href="/blog"     >
<svg width="21" height="13" viewBox="0 0 21 13" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M19.8984 5.69112C20.3452 5.69112 20.7073 6.05327 20.7073 6.5C20.7073 6.94673 20.3452 7.30888 19.8984 7.30888V5.69112ZM0.529732 7.07197C0.213842 6.75608 0.213842 6.24392 0.529732 5.92803L5.67743 0.780334C5.99332 0.464446 6.50548 0.464446 6.82136 0.780334C7.13725 1.09622 7.13725 1.60838 6.82136 1.92427L2.24563 6.5L6.82136 11.0757C7.13725 11.3916 7.13725 11.9038 6.82136 12.2197C6.50548 12.5356 5.99332 12.5356 5.67743 12.2197L0.529732 7.07197ZM19.8984 7.30888H1.1017V5.69112H19.8984V7.30888Z" fill="#737373"/>
</svg></a>

                        <span class="mt-10 mt-md-20 font-16 font-weight-500 text-gray">
                            <a href="{{ $post->category->getUrl() }}" class="text-gray ">{{ $post->category->title }}</a>
                        </span>
<div class="js-share-blog">
<svg width="26" height="25" viewBox="0 0 26 25" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect x="0.707031" y="25" width="25" height="25" rx="12.5" transform="rotate(-90 0.707031 25)" fill="#929292"/>
<path d="M8.71975 10.8974C9.60482 10.8974 10.3223 11.6149 10.3223 12.5C10.3223 13.385 9.60482 14.1025 8.71975 14.1025C7.83468 14.1025 7.11719 13.385 7.11719 12.5C7.11719 11.6149 7.83468 10.8974 8.71975 10.8974Z" fill="white"/>
<path d="M13.2069 10.8974C14.092 10.8974 14.8095 11.6149 14.8095 12.5C14.8095 13.385 14.092 14.1025 13.2069 14.1025C12.3219 14.1025 11.6044 13.385 11.6044 12.5C11.6044 11.6149 12.3219 10.8974 13.2069 10.8974Z" fill="white"/>
<path d="M17.6941 10.8974C18.5792 10.8974 19.2967 11.6149 19.2967 12.5C19.2967 13.385 18.5792 14.1025 17.6941 14.1025C16.809 14.1025 16.0915 13.385 16.0915 12.5C16.0915 11.6149 16.809 10.8974 17.6941 10.8974Z" fill="white"/>
</svg>
</div>

                    </div>
                    <h1 class="font-30 font-weight-bold mt-20">{{ $post->title }}</h1>

                    <div class="d-flex flex-column flex-sm-row align-items-center align-sm-items-start justify-content-between">

                    </div>

                </div>
            </div>
        </div>
    </section>
    <section class="cart-banner position-relative text-center d-none" >
        <div class="container h-100">
            <div class="row h-100 align-items-center justify-content-center text-center">
                <div class="col-12 col-md-9 col-lg-7">

                    <h1 class="font-30 text-white font-weight-bold">{{ $post->title }}</h1>

                    <div class="d-flex flex-column flex-sm-row align-items-center align-sm-items-start justify-content-between">
                        @if(!empty($post->author))
                            <span class="mt-10 mt-md-20 font-16 font-weight-500 text-white">{{ trans('public.created_by') }}
                                @if($post->author->isTeacher())
                                    <a href="{{ $post->author->getProfileUrl() }}" target="_blank" class="text-white text-decoration-underline">{{ $post->author->full_name }}</a>
                                @elseif(!empty($post->author->full_name))
                                    <span class="text-white text-decoration-underline">{{ $post->author->full_name }}</span>
                                @endif
                        </span>
                        @endif

                        <span class="mt-10 mt-md-20 font-16 font-weight-500 text-white">{{ trans('public.in') }}
                            <a href="{{ $post->category->getUrl() }}" class="text-white text-decoration-underline">{{ $post->category->title }}</a>
                        </span>

                        <span class="mt-10 mt-md-20 font-16 font-weight-500 text-white">{{ dateTimeFormat($post->created_at, 'j M Y') }}</span>

                        <div class="js-share-blog d-flex align-items-center cursor-pointer mt-10 mt-md-20">
                            <div class="icon-box ">
                                <i data-feather="share-2" class="text-white" width="20" height="20"></i>
                            </div>
                            <div class="ml-5 font-16 font-weight-500 text-white">{{ trans('public.share') }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="container ">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="post-show">

                    <div class="post-img pb-30">
                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $post->image }}" alt="{{ $post->title }}">
                    </div>
       <div class="webinar-card-body w-100 d-flex flex-column" style="margin-top: -76px;padding-right: 14px;">
<div class="radius-20  stars-card d-flex align-items-center" style="padding-left: 1px; padding-right:1px; padding-top:1px; display: flex !important;flex-direction: row-reverse;flex-wrap: nowrap;">

            <span class="radius-20 badge badge-primary1 rate1  shadow-sm " style="padding:7px; line-height: 1;background-color: white; ">

<svg width="15" height="10" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M6.00001 7C2.60239 7 0.106664 3.82863 0.106664 3.82863C-0.0355548 3.64791 -0.0355548 3.35217 0.106664 3.17137C0.106664 3.17137 2.60239 0 6.00001 0C9.39764 0 11.8933 3.17137 11.8933 3.17137C12.0356 3.35214 12.0356 3.64791 11.8933 3.82863C11.8933 3.82863 9.39761 7 6.00001 7Z" fill="#32BA7C"/>
<path d="M6 6C7.65685 6 9 4.88071 9 3.5C9 2.11929 7.65685 1 6 1C4.34315 1 3 2.11929 3 3.5C3 4.88071 4.34315 6 6 6Z" fill="white"/>
<path d="M6 5C7.10457 5 8 4.32843 8 3.5C8 2.67157 7.10457 2 6 2C4.89543 2 4 2.67157 4 3.5C4 4.32843 4.89543 5 6 5Z" fill="#2B6056"/>
<path d="M7.5 3C7.77614 3 8 2.77614 8 2.5C8 2.22386 7.77614 2 7.5 2C7.22386 2 7 2.22386 7 2.5C7 2.77614 7.22386 3 7.5 3Z" fill="white"/>
</svg>{{ $post->visit_count }} Views</span>

</div>
</div>

                    {!! nl2br($post->content) !!}

                    @php

                        $str_arr1 = explode ("/", $_SERVER['REQUEST_URI']);

                        if(in_array("what-is-pitru-paksha-and-why-it-is-observed", $str_arr1)){
                        @endphp

                        <script async src="https://www.vbt.io/ext/vbtforms.js?lang=en" charset="utf-8"></script>
<style type="text/css">

</style>

<script>(function(t){var n="_vbtefso";t[n]=t[n]||{};t[n][110767]="eyJkZXBlbmRlbnRGaWVsZHMiOltdLCJlcnJvck1lc3NhZ2VzIjp7ImVycm9ycyI6IlBsZWFzZSBjaGVjayB0aGUgZXJyb3JzIGluIHRoZSBmb3JtLiIsInJlcXVpcmVkIjoiVGhpcyBmaWVsZCBpcyByZXF1aXJlZC4iLCJtaXNzaW5nIjoiUGxlYXNlIHNlbGVjdCBhbiBvcHRpb24uIiwiaW52YWxpZCI6IkludmFsaWQgdmFsdWUgZm9yIHRoaXMgZmllbGQuIiwiaW52YWxpZF9lbWFpbCI6IlBsZWFzZSBlbnRlciBhIHZhbGlkIGVtYWlsIGFkZHJlc3MhIiwiZmlsZV9taXNzaW5nIjoiUGxlYXNlIHNlbGVjdCBmaWxlLiIsImZpbGVfZXh0ZW5zaW9uX2ludmFsaWQiOiJGaWxlIGV4dGVuc2lvbiBub3QgYWxsb3dlZC4iLCJmaWxlX3NpemVfZXhjZWVkZWQiOiJGaWxlIHNpemUgZXhjZWVkZWQgbWF4IHNpemUuIiwiZXJyb3IiOiJFcnJvciBvY2N1cnJlZCEifX0="})(window);</script>
<div id="vboutEmbedFormWrapper-110767">
	<form action="https://www.vbt.io/embedcode/submit/110767/?_format=page" target="_blank"  id="vboutEmbedForm-110767" name="vboutEmbedForm-110767" data-vboutform="110767" class="" method="post" enctype="multipart/form-data">
		<h1>Fill Out Your Details Today & Get Your Custom Pitra Paksh Remedy Report</h1>

		<div id="vboutEmbedFormResponse-110767" style="display: none;"></div>
		<fieldset>
<div class="vbf-step">
    <div class="vboutEmbedFormRow">
        <label class="title" for="custom-358666">Full Name<span class="required-asterisk">*</span></label>
        <div class="vboutEmbedFormField">
            <input type="text" name="vbout_EmbedForm[field][358666]" id="custom-358666" value="" class="vfb-text  required  "    /></div></div><div class="vboutEmbedFormRow"><label class="title" for="custom-358669">Number<span class="required-asterisk">*</span></label><div class="vboutEmbedFormField"><input type="tel" name="vbout_EmbedForm[field][358669]" id="custom-358669" value="" class="vfb-text  required  validate-phone "    data-countrylist="no" /></div></div><div class="vboutEmbedFormRow"><label class="title" for="custom-358668">Email<span class="required-asterisk">*</span></label><div class="vboutEmbedFormField"><input type="email" name="vbout_EmbedForm[field][358668]" id="custom-358668" value="" class="vfb-text  required  validate-email "    /></div></div><div class="vboutEmbedFormRow"><label class="title" for="custom-745200">Birth Time<span class="required-asterisk">*</span></label><div class="vboutEmbedFormField"><input type="time" name="vbout_EmbedForm[field][745200]" id="custom-745200" value="" class="vfb-text  required  "    /></div></div><div class="vboutEmbedFormRow"><label class="title" for="custom-744763">Birth Date<span class="required-asterisk">*</span></label><div class="vboutEmbedFormField"><input type="text" name="vbout_EmbedForm[field][744763]" id="custom-744763" value="" class="vfb-text vfb-date vboutEmbedFormDatePicker  required " data-format="d-m-Y"   /></div></div><div class="vboutEmbedFormRow"><label class="title" for="custom-744766">Birth Place<span class="required-asterisk">*</span></label><div class="vboutEmbedFormField"><input type="text" name="vbout_EmbedForm[field][744766]" id="custom-744766" value="" class="vfb-text  required  "    /></div></div></div>
			<div style="margin: 10px 0;">
				<div class="vboutEmbedFormRow vfb-submit ">
									<button type="submit" class="vbf-submit">Submit</button>
				</div>
			</div>
		</fieldset>
	</form>
</div>

                        @php

                        }

                    @endphp

                </div>

                @if($post->enable_comment)
                    @include('web.default.includes.comments',[
                            'comments' => $post->comments,
                            'inputName' => 'blog_id',
                            'inputValue' => $post->id
                        ])
                @endif

            </div>

            <div class="col-12 col-lg-4 homehide">
                @if(!empty($post->author) and !empty($post->author->full_name))
                    <div class="rounded-lg shadow-sm mt-35 p-20 course-teacher-card d-flex align-items-center flex-column">
                        <div class="teacher-avatar mt-5">
                            <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $post->author->getAvatar(100) }}" class="img-cover" alt="{{ $post->author->full_name }}">
                        </div>
                        <h3 class="mt-10 font-20 font-weight-bold text-secondary">{{ $post->author->full_name }}</h3>

                        @if(!empty($post->author->role))
                            <span class="mt-5 font-weight-500 font-14 text-gray">{{ $post->author->role->caption }}</span>
                        @endif

                        <div class="mt-25 d-flex align-items-center  w-100">
                            <a href="/blog?author={{ $post->author->id }}" class="btn btn-sm btn-primary btn-block px-15">{{ trans('public.author_posts') }}</a>
                        </div>
                    </div>
                @endif

                <div class="p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class="category-filter-title font-16 font-weight-bold text-dark-blue">{{ trans('categories.categories') }}</h3>

                    <div class="pt-15">
                        @foreach($blogCategories as $blogCategory)
                            <a href="{{ $blogCategory->getUrl() }}" class="font-14 text-dark-blue d-block mt-15">{{ $blogCategory->title }}</a>
                        @endforeach
                    </div>
                </div>

                <div class="p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">{{ trans('site.recent_posts') }}</h3>

                    <div class="pt-15">

                        @foreach($popularPosts as $popularPost)
                            <div class="popular-post d-flex align-items-start mt-20">
                                <div class="popular-post-image rounded">
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $popularPost->image }}" class="img-cover rounded" alt="{{ $popularPost->title }}">
                                </div>
                                <div class="popular-post-content d-flex flex-column ml-10">
                                    <a href="{{ $popularPost->getUrl() }}">
                                        <h3 class="font-14 text-dark-blue">{{ truncate($popularPost->title,40) }}</h3>
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
                        $courses = array(2033, 2050, 2055, 2045, 2063);
                        @endphp

                        @foreach($popularWebinars as $popularWebinar)

                            @if(in_array($popularWebinar->id, $courses))
                            <div class="popular-post d-flex align-items-start mt-20">
                                <div class="popular-post-image rounded">
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{$popularWebinar->thumbnail}}" class="img-cover rounded" alt="{{$popularWebinar->title}}">
                                </div>
                                <div class="popular-post-content d-flex flex-column ml-10">
                                    <a href="/course/{{$popularWebinar->slug}}">
                                        <h3 class="text-dark-blue font-14">{{ truncate($popularWebinar->title,50) }}</h3>
                                    </a>
                                    <span class="mt-auto font-12 text-gray">{{ handlePrice($popularWebinar->bestTicket(), true, true, false, null, true) }}</span>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        <a href="/classes?sort=newest" class="btn btn-sm btn-primary btn-block mt-30">{{ trans('home.view_all') }} Courses</a>
                    </div>
                </div>

                <div class="p-20 mt-30 rounded-sm shadow-lg border border-gray300">
                    <h3 class="category-filter-title font-20 font-weight-bold text-dark-blue">Consultants</h3>

                    <div class="pt-15">
                <div class="col-12  mt-25 mt-lg-0 " >

         <div class="deckteacher teacher-swiper-container1 position-relative d-flex justify-content-center mt-0">
            <div class="swiper-container teacher-swiper-container1 pb-25">
               <div class="swiper-wrapper py-0">
                   @foreach($consultant as $instructor)
                   <div class="swiper-slide" >
                    <div class="rounded-lg shadow-sm mt-15  p-5 course-teacher-card d-flex align-items-center flex-column">
                       <div class="teacher-avatar mt-15">
                          <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(108) }}" class="img-cover" alt="{{ $instructor->full_name }}">
                          <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                             <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check text-white">
                                <polyline points="20 6 9 17 4 12"></polyline>
                             </svg>
                          </span>
                       </div>
                       <h3 class="mt-10 font-16 font-weight-bold text-secondary text-center swiper-container1-title">{{ $instructor->full_name }}</h3>
                       <span class="mt-5 font-14 font-weight-500 text-gray text-center swiper-container1-desc">{{ $instructor->bio }}</span>
                       <div class="stars-card d-none align-items-center  mt-15">
                                                @php
                                                    $i = 5;
                                                @endphp
                                                @while(--$i >= 5 - $instructor->rates())
                                                    <i data-feather="star" width="13" height="13" class="active"></i>
                                                @endwhile
                                                @while($i-- >= 0)
                                                    <i data-feather="star" width="13" height="13" class=""></i>
                                                @endwhile
                           </div>
                       <div class="my-15   align-items-center text-center  w-100">
                           <a href="{{ $instructor->getProfileUrl() }}" class="btn btn-sm btn-primary swiper-container1-btn">Book a Meeting</a>
                       </div>
                    </div>
                 </div>
                 @endforeach

               </div>
            </div>
            <div class="swiper-pagination teacher-swiper-pagination1 ast-pagination"></div>
         </div>

    </div>
    </div></div>

            </div>
        </div>
    </section>

    @include('web.default.blog.share_modal')
@endsection

@push('scripts_bottom')
    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
    </script>

<script src="{{ config('app.js_css_url') }}/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/parallax/parallax.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/home.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/blog.min.js"></script>
@endpush
