@extends('web.default2'.'.layouts.app')
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.css">
@endpush
@section('content')
    <section class="cart-banner position-relative text-center">
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

    <section class="container mt-10 mt-md-40">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="post-show mt-30">
<strong style="float: right;margin-bottom:10px;">Views: {{ $post->visit_count }} </strong>
                    <div class="post-img pb-30">
                        <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $post->image }}" alt="{{ $post->title }}">
                    </div>

                    {!! nl2br($post->content) !!}

                    @php

                        $str_arr1 = explode ("/", $_SERVER['REQUEST_URI']);

                        if(in_array("what-is-pitru-paksha-and-why-it-is-observed", $str_arr1)){
                        @endphp

                        <script async src="https://www.vbt.io/ext/vbtforms.js?lang=en" charset="utf-8"></script>
<style type="text/css">

	#vboutEmbedFormWrapper-110767 a,#vboutEmbedFormWrapper-110767 div,#vboutEmbedFormWrapper-110767 dl,#vboutEmbedFormWrapper-110767 dt,#vboutEmbedFormWrapper-110767 em,#vboutEmbedFormWrapper-110767 fieldset,
	#vboutEmbedFormWrapper-110767 form,#vboutEmbedFormWrapper-110767 h1,#vboutEmbedFormWrapper-110767 h2,#vboutEmbedFormWrapper-110767 h3,#vboutEmbedFormWrapper-110767 h4,#vboutEmbedFormWrapper-110767 h5,#vboutEmbedFormWrapper-110767 h6,
	#vboutEmbedFormWrapper-110767 hr,#vboutEmbedFormWrapper-110767 img,#vboutEmbedFormWrapper-110767 label,#vboutEmbedFormWrapper-110767 li,#vboutEmbedFormWrapper-110767 ol,#vboutEmbedFormWrapper-110767 p,#vboutEmbedFormWrapper-110767 pre,
	#vboutEmbedFormWrapper-110767 span,#vboutEmbedFormWrapper-110767 strong,#vboutEmbedFormWrapper-110767 table,#vboutEmbedFormWrapper-110767 tbody,#vboutEmbedFormWrapper-110767 td,#vboutEmbedFormWrapper-110767 tfoot,
	#vboutEmbedFormWrapper-110767 th,#vboutEmbedFormWrapper-110767 thead,#vboutEmbedFormWrapper-110767 tr,#vboutEmbedFormWrapper-110767 ul {border:0;margin:0;padding:0;font-size:100%}
	#vboutEmbedFormWrapper-110767 {height:100%}
	#vboutEmbedFormWrapper-110767 b,#vboutEmbedFormWrapper-110767 strong{font-weight:bold}
	#vboutEmbedFormWrapper-110767 img{color:transparent;font-size:0;vertical-align:middle;-ms-interpolation-mode:bicubic}
	#vboutEmbedFormWrapper-110767 li{display:list-item}
	#vboutEmbedFormWrapper-110767 table{border-collapse:collapse;border-spacing:0}
	#vboutEmbedFormWrapper-110767 th,#vboutEmbedFormWrapper-110767 td,#vboutEmbedFormWrapper-110767 caption{font-weight:normal;vertical-align:top;text-align:left}

	#vboutEmbedFormWrapper-110767 form {display:block; position:relative; text-align:left; padding:10px 0 10px 3%; max-width: 500px;}
	#vboutEmbedFormWrapper-110767 h1 {font-weight:bold; padding:0; margin:15px 0; font-size:1.4em;}
	#vboutEmbedFormWrapper-110767 input, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield {border:1px solid #999; -webkit-appearance:none;}
	#vboutEmbedFormWrapper-110767 input[type=checkbox]{-webkit-appearance:checkbox;}
	#vboutEmbedFormWrapper-110767 input[type=radio]{-webkit-appearance:radio;}
	#vboutEmbedFormWrapper-110767 .button, #vboutEmbedFormWrapper-110767 input[type=submit], #vboutEmbedFormWrapper-110767 .vfb-submit button {clear:both; background-color: #aaa; border: 0 none; border-radius:4px; color: #FFFFFF; cursor: pointer; display: inline-block; font-size:15px; font-weight: bold; margin: 0 5px 10px 0; padding: 0 22px; text-align: center; text-decoration: none; vertical-align: top; white-space: nowrap; width: auto;}
	#vboutEmbedFormWrapper-110767 .button:hover, #vboutEmbedFormWrapper-110767 input[type=submit]:hover, #vboutEmbedFormWrapper-110767 .vfb-submit button:hover {background-color:#777;}
	#vboutEmbedFormWrapper-110767 .small-meta {font-size: 11px;}
	#vboutEmbedFormWrapper-110767 .vboutEmbedFormRow {padding-bottom: 10px}

	#vboutEmbedFormWrapper-110767 label, #vboutEmbedFormWrapper-110767 .vfb-description{font-size:14px;width:130px;text-align:right;color:#33475b;float:none;width:auto;font-weight:500;text-align:left;line-height:20px;padding-top:0;margin-bottom:4px;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif}
	#vboutEmbedFormWrapper-110767 .vfb-description{font-size:12px;}

	#vboutEmbedFormWrapper-110767 input, #vboutEmbedFormWrapper-110767 textarea, #vboutEmbedFormWrapper-110767 select, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield, #vboutEmbedFormWrapper-110767 .vbt-checkboxselectinput-headbox, #vboutEmbedFormWrapper-110767 .vbt-checkboxselectinput-box{padding:9px 10px;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:16px;font-weight:normal;line-height:22px;color:#33475b;background-color:#f5f8fa;border:1px solid #cbd6e2;box-sizing:border-box;-webkit-border-radius:3px;-moz-border-radius:3px;-ms-border-radius:3px;border-radius:3px}
	#vboutEmbedFormWrapper-110767 input, #vboutEmbedFormWrapper-110767 textarea, #vboutEmbedFormWrapper-110767 select, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield, #vboutEmbedFormWrapper-110767 .vbt-checkboxselectinput-headbox{height:40px;}
	#vboutEmbedFormWrapper-110767 input, #vboutEmbedFormWrapper-110767 textarea, #vboutEmbedFormWrapper-110767 select, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield, #vboutEmbedFormWrapper-110767 .vbt-checkboxselectinput{width:90%;max-width:500px;}
	#vboutEmbedFormWrapper-110767 input, #vboutEmbedFormWrapper-110767 textarea, #vboutEmbedFormWrapper-110767 select, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield {display:inline-block;}
	#vboutEmbedFormWrapper-110767 input[type=checkbox],#vboutEmbedFormWrapper-110767 input[type=radio]{cursor:pointer;width:auto;height:auto;padding:0;margin:3px 5px 3px 0px;line-height:normal;border:none}
	#vboutEmbedFormWrapper-110767 input[type=file]{background-color:#fff;padding:initial;border:initial;line-height:initial;box-shadow:none;height:27px}
	#vboutEmbedFormWrapper-110767 input:-moz-placeholder{color:#bfbfbf}
	#vboutEmbedFormWrapper-110767 input::-webkit-input-placeholder{color:#bfbfbf}
	#vboutEmbedFormWrapper-110767 input input,#vboutEmbedFormWrapper-110767 input textarea{transition:border 0.2s linear}
	#vboutEmbedFormWrapper-110767 input:focus{outline:none;border-color:rgba(82,168,236,0.8)}
	#vboutEmbedFormWrapper-110767 textarea{height:auto}
	#vboutEmbedFormWrapper-110767 select[multiple]{height:inherit}
	#vboutEmbedFormWrapper-110767 input.error,#vboutEmbedFormWrapper-110767 textarea.error{border-color:#c87872}
	#vboutEmbedFormWrapper-110767 input.error:focus,#vboutEmbedFormWrapper-110767 textarea.error:focus{border-color:#b9554d}
	#vboutEmbedFormWrapper-110767 .vboutEmbedFormErrorMessage { color: #b9554d; margin: 0; display: block; }
	#vboutEmbedFormWrapper-110767 .vbt-checkboxselectinput-box{width:100%;}
	#vboutEmbedFormWrapper-110767 ul{list-style:none}
	#vboutEmbedFormWrapper-110767 input[type=submit], #vboutEmbedFormWrapper-110767 .vfb-submit button,#vboutEmbedFormWrapper-110767 input[type="text"],#vboutEmbedFormWrapper-110767 input[type="email"],#vboutEmbedFormWrapper-110767 input[type="phone"],#vboutEmbedFormWrapper-110767 input[type="number"],#vboutEmbedFormWrapper-110767 input[type="tel"],#vboutEmbedFormWrapper-110767 input[type="date"],#vboutEmbedFormWrapper-110767 textarea, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield{-webkit-appearance:none;-moz-appearance:none}
	#vboutEmbedFormWrapper-110767 input[type=submit], #vboutEmbedFormWrapper-110767 .vfb-submit button{font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;margin:0;cursor:pointer;display:inline-block;font-weight:700;line-height:12px;position:relative;text-align:center;background-color:#ff7a59;border-color:#ff7a59;color:#fff;border-radius:3px;border-style:solid;border-width:1px;font-size:14px;padding:12px 24px}
	#vboutEmbedFormWrapper-110767 input[type=submit]:hover,#vboutEmbedFormWrapper-110767 input[type=submit]:focus, #vboutEmbedFormWrapper-110767 .vfb-submit button:hover,#vboutEmbedFormWrapper-110767 .vfb-submit button:focus{background-color:#ff8f73;border-color:#ff8f73}
	#vboutEmbedFormWrapper-110767 input[type=submit]:active, #vboutEmbedFormWrapper-110767 .vfb-submit button:active{background-color:#e66e50;border-color:#e66e50}
	#vboutEmbedFormWrapper-110767 input[type=submit],#vboutEmbedFormWrapper-110767 input[type=submit]:hover,#vboutEmbedFormWrapper-110767 input[type=submit]:hover:not(.inactive),#vboutEmbedFormWrapper-110767 input[type=submit]:focus,#vboutEmbedFormWrapper-110767 input[type=submit]:active,#vboutEmbedFormWrapper-110767 input[type=submit]:active:not(.inactive):not(.link),
	#vboutEmbedFormWrapper-110767 .vfb-submit button,#vboutEmbedFormWrapper-110767 .vfb-submit button:hover,#vboutEmbedFormWrapper-110767 .vfb-submit button:hover:not(.inactive),#vboutEmbedFormWrapper-110767 .vfb-submit button:focus,#vboutEmbedFormWrapper-110767 .vfb-submit button:active,#vboutEmbedFormWrapper-110767 .vfb-submit button:active:not(.inactive):not(.link) {background-image: none;background-color: #A01694;border-color: #A01694;color: #ffffff;}

	#vboutEmbedFormWrapper-110767 form, #vboutEmbedFormWrapper-110767 label, #vboutEmbedFormWrapper-110767 input, #vboutEmbedFormWrapper-110767 select, #vboutEmbedFormWrapper-110767 textarea, #vboutEmbedFormWrapper-110767 .vbt-birthdayfield { font-family: helvetica; }

	#vboutEmbedFormWrapper-110767 form, #vboutEmbedFormWrapper-110767 label { color: #33475b; }

	#vboutEmbedFormWrapper-110767 .iti{display: block;}
	#vboutEmbedFormWrapper-110767 input[type=tel]{padding-left: 48px!important;}
	#vboutEmbedFormWrapper-110767 fieldset{display: block;display: contents;}

	#vboutEmbedFormWrapper-110767 form {position: relative;}
	#vboutEmbedFormWrapper-110767 .vbtMultiStepsActive .vbf-step, #vboutEmbedFormWrapper-110767 .vbtMultiStepsActive .vbf-submit { display: none !important; }
	#vboutEmbedFormWrapper-110767 .vbtMultiSteps .vbf-previous, #vboutEmbedFormWrapper-110767 .vbtMultiSteps .vbf-next { display: none !important; }
	#vboutEmbedFormWrapper-110767 .vbf-step.active { display: block !important; }
	#vboutEmbedFormWrapper-110767 .vbf-previous.active, #vboutEmbedFormWrapper-110767 .vbf-next.active, #vboutEmbedFormWrapper-110767 .vbf-submit.active { display: inline-block !important; }
	#vboutEmbedFormWrapper-110767 .vfb-submit {display: flex;justify-content: space-between;}
	#vboutEmbedFormWrapper-110767 .vfb-submit button {margin-right: 0;margin-left: 0;}
	#vboutEmbedFormWrapper-110767 .vfb-submit.vfb-submit-space-between { display: flex !important; justify-content: space-between !important; }
	#vboutEmbedFormWrapper-110767 .vfb-submit.vfb-submit-space-between button { margin: 0 !important; }
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar {display: none;position: relative;padding-top: 15px;padding-bottom: 30px;margin: 0 auto;}
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar .vbf-stepbar-progress { background-color: #d3d3d3; width: 100%; border-radius: 125px; overflow: hidden; }
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar .vbf-stepbar-progressbar { background: #20b2aa; border-radius: 125px; width: 0%; transition: width 0.5s linear; }
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar.vbf-steps-bar-lines {display: block;}
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar.vbf-steps-bar-lines .vbf-stepbar-progress {height: 12px;}
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar.vbf-steps-bar-lines .vbf-stepbar-progressbar {height: 100%;}
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar.vbf-steps-bar-lines .vbf-stepbar-dots {display: none;}

	#vboutEmbedFormWrapper-110767 .vbf-steptitle{font-size: 20px; font-family: arial; font-weight: bold; color: #777777; padding-bottom: 15px; }
	#vboutEmbedFormWrapper-110767 .vbf-stepdescription{font-size: 16px; font-family: arial; font-weight: normal; color: #999999; padding-bottom: 15px; }

	#vboutEmbedFormWrapper-110767 .vbf-steps-bar {padding-top: 15px; padding-bottom: 15px; max-width: 1200px;}
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar .vbf-stepbar-progress {background-color: #d3d3d3;}
	#vboutEmbedFormWrapper-110767 .vbf-steps-bar .vbf-stepbar-progressbar {background-color: #20b2aa;}

</style>

<script>(function(t){var n="_vbtefso";t[n]=t[n]||{};t[n][110767]="eyJkZXBlbmRlbnRGaWVsZHMiOltdLCJlcnJvck1lc3NhZ2VzIjp7ImVycm9ycyI6IlBsZWFzZSBjaGVjayB0aGUgZXJyb3JzIGluIHRoZSBmb3JtLiIsInJlcXVpcmVkIjoiVGhpcyBmaWVsZCBpcyByZXF1aXJlZC4iLCJtaXNzaW5nIjoiUGxlYXNlIHNlbGVjdCBhbiBvcHRpb24uIiwiaW52YWxpZCI6IkludmFsaWQgdmFsdWUgZm9yIHRoaXMgZmllbGQuIiwiaW52YWxpZF9lbWFpbCI6IlBsZWFzZSBlbnRlciBhIHZhbGlkIGVtYWlsIGFkZHJlc3MhIiwiZmlsZV9taXNzaW5nIjoiUGxlYXNlIHNlbGVjdCBmaWxlLiIsImZpbGVfZXh0ZW5zaW9uX2ludmFsaWQiOiJGaWxlIGV4dGVuc2lvbiBub3QgYWxsb3dlZC4iLCJmaWxlX3NpemVfZXhjZWVkZWQiOiJGaWxlIHNpemUgZXhjZWVkZWQgbWF4IHNpemUuIiwiZXJyb3IiOiJFcnJvciBvY2N1cnJlZCEifX0="})(window);</script>
<div id="vboutEmbedFormWrapper-110767">
	<form action="https://www.vbt.io/embedcode/submit/110767/?_format=page" target="_blank"  id="vboutEmbedForm-110767" name="vboutEmbedForm-110767" data-vboutform="110767" class="" method="post" enctype="multipart/form-data">
		<h2>Fill Out Your Details Today & Get Your Custom Pitra Paksh Remedy Report</h2>

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
            <div class="col-12 col-lg-4">
                @if(!empty($post->author) and !empty($post->author->full_name))
                    <div class="rounded-lg shadow-sm mt-35 p-20 course-teacher-card d-flex align-items-center flex-column">
                        <div class="teacher-avatar mt-5">
                            <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $post->author->getAvatar(100) }}" class="img-cover" alt="{{ $post->author->full_name }}">
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
                                    <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $popularPost->image }}" class="img-cover rounded" alt="{{ $popularPost->title }}">
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
                        $courses = array(2069,2033, 2050, 2055, 2045, 2063);
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
                          <img loading="lazy" decoding="async" src="{{ config('app.img_dynamic_url') }}{{ $instructor->getAvatar(108) }}" class="img-cover" alt="{{ $instructor->full_name }}">
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

<script src="{{ config('app.js_css_url') }}/assets2/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/owl-carousel2/owl.carousel.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/vendors/parallax/parallax.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/home.min.js"></script>

    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets2/default/js/parts/blog.min.js"></script>
@endpush
