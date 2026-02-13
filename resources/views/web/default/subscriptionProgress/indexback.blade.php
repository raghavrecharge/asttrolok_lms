@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video-js.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/video/video-js.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-course-detailes.css">
    
    <style>
        .course-description p{
            font-family: 'main-font-family' !important;
        }
        .course-description p span{
            font-family: 'main-font-family' !important;
        }
        .course-description span{
            font-family: 'main-font-family' !important;
        }
        .course-content-sidebar .course-img.has-video .course-video-icon {
   
    width: 50px;
    height: 50px;
}




/*******************************
* MODAL AS LEFT/RIGHT SIDEBAR
* Add "left" or "right" in modal parent div, after class="modal".
* Get free snippets on bootpen.com
*******************************/
	.modal.left .modal-dialog,
	.modal.right .modal-dialog {
		position: fixed;
		right: -100%;
		margin: auto;
		width: 320px;
		height: 100%;
		-webkit-transform: translateX(100%);
		    -ms-transform: translateX(100%);
		     -o-transform: translateX(100%);
		        transform: translateX(100%);
	}
.afterpop{
    
    transition: all 2s  !important;
    transition-timing-function: ease-in  !important;
   -webkit-transform: translateX(0%) !important;
		    -ms-transform: translateX(0%) !important;
		     -o-transform: translateX(0%) !important;
		        transform: translateX(0%) !important;
		        
}
	.modal.left .modal-content,
	.modal.right .modal-content {
		height: 100%;
		overflow-y: auto;
	}
	
	.modal.left .modal-body,
	.modal.right .modal-body {
		padding: 15px 15px 80px;
	}


        
/*Right*/
	.modal.right.fade .modal-dialog {
	    
		right: 0px;
		        
	}
	
	.modal.right.fade.in .modal-dialog {
		right: 0;
		transition: all .5s;
	}

/* ----- MODAL STYLE ----- */
	.modal-content {
		border-radius: 0;
		border: none;
	}

	.modal-header {
		border-bottom-color: #EEEEEE;
		background-color: #FAFAFA;
	}

/* ----- v CAN BE DELETED v ----- */




    </style>
@endpush
{{ session()->put('my_test_key',url()->current())}}

@section('content')
    <section class=" course-cover-container {{ empty($activeSpecialOffer) ? 'not-active-special-offer' : '' }}">
        <img src="{{ asset('assets/default/img/course/video-banner.svg') }}" class=" course-cover-img" alt="{{ $course->title }}"/>
        
        <div class="cover-content pt-40 pt-80">
            <div class="container position-relative">
              {{--  @if(!empty($activeSpecialOffer))
                    @include('web.default.course.special_offer')
                @endif --}}
                {{-- <h1 class="font-30 course-title text-center py-10">{{ clean($course->title, 't') }}</h1> --}}
                <div class="row">
                    <div class="col-12 col-lg-6 course-section-top">
                        {{-- <div class="course-img text-center {{ $course->video_demo ? 'has-video' :'' }}"> --}}

                        {{-- <img src="{{ config('app.img_dynamic_url') }}{{ $course->getImage() }}" class="img-cover" alt="webinar Demo Video"> --}}

                        @if($course->video_demo)
                            <div id="webinarDemoVideoBtn"
                                 data-video-path="{{ $course->video_demo_source == 'upload' ?  url($course->video_demo) : $course->video_demo }}"
                                 data-video-source="{{ $course->video_demo_source }}"
                                 class="mt-5 course-video-icon cursor-pointer d-flex align-items-center justify-content-center">
                               <img src="{{ asset('assets/default/img/course/video-thumbnail.svg') }}" class="img-cover viedo-thumbnais" alt="webinar Demo Video">
                            </div>
                            <h2 class="font-30 video-course-title">
                            Play this video
                        </h2>
                        @else
                        <div class="mt-80">
                        </div>
                        @endif
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $course->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">
                            @php
                                $canSale = ($course->canSale() and !$hasBought);
                        @endphp
                        {{-- @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                                        <button type="button" class="btn-primary btn  buy_now mt-20 js-course-direct-payment">{{ trans('update.buy_now') }}</button>
                        @endif --}}
                       </form>
                       <div class="pt-80 d-flex align-items-left align-items-start justify-content-between ">
                        <div class="">
                        <h3 class="font-18 text-dark-blue font-weight-bold">{{ clean($course->title, 't') }}</h3>
                    </div>
                    <div class="">
                    <img src="{{ asset('assets/default/img/course/save-icon.svg') }}" class="img-cover save-icon" alt="webinar Demo Video">
                    </div>
                </div>
             {{--   @include('web.default.includes.webinar.rate3',['rate' => $course->getRate()]) --}}
                @include('web.default.includes.webinar.rate4',['rate' => $course->course_rate])
                <div class=" abouthide" id="abouthide" style=" max-height: 176px;overflow: hidden;">
                <p class="duration course-description font-16 ml-5">
                    <div class="mt-20">
                        <h2 class=" after-line font-14" style="
    font-weight: 600;
">{{ trans('product.Webinar_description') }}</h2>
                        <div class="mt-15 course-description">
                            {!! clean($course->description) !!}
                        </div>
                    </div>
                    </p>
                <div id="gradiant1" style="
    width: 100%;
    height: 80px;
    /* background-color: white; */
    position: absolute;
    bottom: 134px;
    background-image: linear-gradient(#ffffff30, white);
"></div>
</div>
<div class="readmore">
            <a  id="readmore" onclick="myFunction();">Read More</a>
                        </div>
                    <!--    <div class="mt-5">-->
                    <!--        <div class="stars-card d-flex align-items-center pd-star">-->
    
                    <!--            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>-->
                    <!--        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>-->
                    <!--        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>-->
                    <!--        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>-->
                    <!--                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star grid-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>-->
                
                        
                    <!--<span class="badge badge-primary ml-10 rating-course"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg> 4.9</span>-->
                    <!--</div>-->
                    <!--        Born brought up and educated in kolkata I am associated with Asttrolok since the past 2 years as a Student of Astrology. A keen interest to learn new things has me delving into a variety of subjects such as graphology music philisophy and spirituality. I have been also teaching meditation for the past 5 years and have been associated with the Art of Living foundation as a faculty. With a sincere will to help people with this knowledge I offer you my service. Jai Gurudev.-->
                    <!--        <div class="read-more-Wd3" id="864:10410">Read More</div>-->
                    <!--    </div>-->
                        <div class="pt-50  align-items-left align-items-start justify-content-between ">
                            <div class="d-flex frame-427322372-acu" id="1017:948">
                                <div class="auto-group-eond-RX3" id="DPMWvH8SZiH7QxWwGEzUkM">
                                <img class="frame-427322370-8wF" src="{{ asset('assets/default/img/course/play-button.svg') }}" alt="play-button" id="1017:938"/>
                                @if($course->id == 2070 or $course->id == 2069)
                                <div class="on-demand-videos-XwT" id="864:10414">Live<br/>Course</div>
                                @else
                                <div class="on-demand-videos-XwT" id="864:10414">Video<br/>Course</div>
                                @endif
                                </div>
                                <div class=" auto-group-eond-RX3 ml-10" id="DPMX32SCYt7QUQ5s3KEond">
                                <img class="frame-427322370-8wF" src="{{ asset('assets/default/img/course/green-video-icon (2).svg') }}" alt="green-video-icon" id="1017:939"/>
                                <div class="on-demand-videos-XwT" id="864:10422">
                                Certified
                                
                                <br/>
                                Course
                                
                                </div>
                                </div>
                                <div class="auto-group-eond-RX3 ml-10" id="DPMX8BxG9LFjFzh6H3sZ17">
                                <img class="frame-427322370-8wF" src="{{ asset('assets/default/img/course/ind-ruppes.svg') }}" alt="ind-ruppes" id="864:10420"/>
                                @if($activeSpecialOffer)
                                @php
                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                @endphp
                                <div class="item-17999--e2R" id="864:10423">{{ $priceWithDiscount['price'] }}/-</div>
                                @else
                                <div class="item-17999--e2R" id="864:10423">{{$course->price}}/-</div>
                                @endif
                                </div>
                            </div>
                            </div>
                    </div>
                    
                    </div>
                    {{-- <div class="col-12 col-lg-6 text-center course-section-top">
                        <div class="course-img {{ $course->video_demo ? 'has-video' :'' }}">

                        <img src="{{ config('app.img_dynamic_url') }}{{ $course->getImageCover() }}" class="img-cover" alt="webinar Demo Video">
                        
                    </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="container course-content-section course-content-top {{ $course->type }} {{ ($hasBought or $course->isWebinar()) ? 'has-progress-bar' : '' }}">
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="course-content-body mt-20 user-select-none">
                    <div class="course-body-on-cover text-white">
                        @if(url()->current()!='https://lms.asttrolok.com/course/Free-Astrology-Course')
                        
                     {{--   <h1 class="font-30 course-title">
                            {{ clean($course->title, 't') }}
                        </h1>

                    @if(!empty($course->category))
                            <span class="d-block font-16 mt-20">{{ trans('public.in') }} <a href="{{ $course->category->getUrl() }}" target="_blank" class="font-weight-500 text-decoration-underline text-white">{{ $course->category->title }}</a></span>
                        @endif

                        <div class="d-flex align-items-center">
                            @include('web.default.includes.webinar.rate',['rate' => $course->course_rate])
                            <span class="ml-10 mt-15 font-14">({{ $course->reviews->pluck('creator_id')->count() }} {{ trans('public.ratings') }})</span>
                        </div>

                        <div class="mt-15">
                            <span class="font-14">{{ trans('public.created_by') }}</span>
                            <a href="{{ $course->teacher->getProfileUrl() }}" target="_blank" class="text-decoration-underline text-white font-14 font-weight-500">{{ $course->teacher->full_name }}</a>
                        </div> 
--}}
                        @php
                            $percent = $course->getProgress();
                        @endphp

                        @if($hasBought or $percent)

                            {{-- <div class="mt-30 d-flex align-items-center">
                                <div class="progress course-progress flex-grow-1 shadow-xs rounded-sm">
                                    <span class="progress-bar rounded-sm bg-warning" style="width: {{ $percent }}%"></span>
                                </div>

                                <span class="ml-15 font-14 font-weight-500">
                                    @if($hasBought and (!$course->isWebinar() or $course->isProgressing()))
                                        {{ trans('public.course_learning_passed',['percent' => $percent]) }}
                                    @elseif(!is_null($course->capacity))
                                        {{ $course->sales_count }}/{{ $course->capacity }} {{ trans('quiz.students') }}
                                    @else
                                        {{ trans('public.course_learning_passed',['percent' => $percent]) }}
                                    @endif
                                </span>
                            </div> --}}
                        @endif
                        @else
                        <div class="course-body-on-cover text-white" style="min-height: 240px;"></div>
                        @endif
                    </div>

                    <div class="mt-20">
                        <ul class="nav nav-tabs bg-secondary rounded-sm p-15 d-flex align-items-center justify-content-between" id="tabs-tab" role="tablist">
                            <li class="nav-item">
                                <a class="position-relative font-14 {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'content') ? 'active' : '' }}" id="content-tab" data-toggle="tab"
                                   href="#content" role="tab" aria-controls="content"
                                   aria-selected="false">{{ trans('product.content') }} ({{ $webinarContentCount }})</a>
                            </li>
                            <li class="nav-item">
                                <a class="position-relative font-14{{ (request()->get('tab','') == 'information') ? 'active' : '' }}" id="information-tab"
                                   data-toggle="tab" href="#information" role="tab" aria-controls="information"
                                   aria-selected="true">{{ trans('product.information') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="position-relative font-14 {{ (request()->get('tab','') == 'reviews') ? 'active' : '' }}" id="reviews-tab" data-toggle="tab"
                                   href="#reviews" role="tab" aria-controls="reviews"
                                   aria-selected="false">{{ trans('product.reviews') }} ({{ $course->reviews->count() > 0 ? $course->reviews->pluck('creator_id')->count() : 0 }})</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade {{ ( request()->get('tab','') == 'information') ? 'show active' : '' }} " id="information" role="tabpanel"
                                 aria-labelledby="information-tab">
                                @include(getTemplate().'.course.tabs.information')
                            </div>
                            
                            <div class="tab-pane fade {{ (empty(request()->get('tab','')) or request()->get('tab','') == 'content') ? 'show active' : '' }}" id="content" role="tabpanel" aria-labelledby="content-tab">
                                @include(getTemplate().'.course.tabs.content')
                            </div>
                           
                            <div class="tab-pane fade {{ (request()->get('tab','') == 'reviews') ? 'show active' : '' }}" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                                @include(getTemplate().'.course.tabs.reviews')
                            </div>
                             
                        </div>

                    </div>
                    
                </div>
            </div>

            <div class="course-content-sidebar col-12 col-lg-4 mt-25 mt-lg-0 homehide">
                <div class="rounded-lg shadow-sm">
                    

                    <div class="px-20 pb-30">
                        <form action="{{ (auth()->check()) ? '/cart/store' : '/course/buy-now' }}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $course->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">

                            @if(!empty($course->tickets))
                                @foreach($course->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio" data-discount="{{ $ticket->discount }}" value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                                               name="ticket_id"
                                               id="courseOff{{ $ticket->id }}">
                                        <label class="form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                                            <span class="font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                                @endif</span>
                                            <span class="font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if($course->price > 0)
                                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class="text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($course->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $course->price }}"
                                              data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : ''}}"
                                              class=" @if(!empty($activeSpecialOffer)) font-16 text-gray text-decoration-line-through @else font-30 text-primary @endif">
                                            {{ $realPrice['price'] }}
                                        </span>

                                        @if(!empty($realPrice['tax']) and empty($activeSpecialOffer))
                                            <span class=" font-14 text-gray">+ {{ $realPrice['tax'] }} {{ trans('cart.tax') }}</span>
                                        @endif
                                    </div>

                                    @if(!empty($activeSpecialOffer))
                                        <div class="text-center">
                                            @php
                                                $priceWithDiscount = handleCoursePagePrice($course->getPrice());
                                            @endphp
                                            <span id="priceWithDiscount"
                                                  class=" font-30 text-primary">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>

                                            @if(!empty($priceWithDiscount['tax']))
                                                <span class="font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} {{ trans('cart.tax') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex align-items-center justify-content-center mt-20">
                                    <span class="font-36 text-primary">{{ trans('public.free') }}</span>
                                </div>
                            @endif

                            @php
                                $canSale = ($course->canSale() and !$hasBought);
                            @endphp

                            <div class="mt-20 d-flex flex-column">
                                @if(!$canSale and $course->canJoinToWaitlist())
                                    <button type="button" data-slug="{{ $course->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                                @elseif($hasBought or !empty($course->getInstallmentOrder()))
                                    <a href="{{ $course->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
                                @elseif($course->price > 0)
                                    <button type="button" class="btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($course->cantSaleStatus($hasBought) .' disabled ') }}" >
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>

                                    @if($canSale and $course->subscribe)
                                        <a href="/subscribes/apply/{{ $course->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($course->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $course->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                                    
                                    @if(auth()->check())
                                       <button type="button" class="btn btn-outline-danger buy_now mt-20 js-course-direct-payment">
                                           {{ trans('update.buy_now') }}
                                       </button>
                                        @else
                                   
                                       <button type="submit" class="btn btn-outline-danger buy_now mt-20">
                                           {{ trans('update.buy_now') }}
                                       </button>
                                       @endif
                                   @endif

                                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                                        <a href="/course/{{ $course->slug }}/installments" class="btn btn-outline-primary mt-20">
                                            {{ trans('update.pay_with_installments') }}
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ $canSale ? '/course/'. $course->slug .'/free' : '#' }}" class="btn btn-primary {{ (!$canSale) ? (' disabled ' . $course->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                                @endif
                            </div>

                        </form>

                        @if(!empty(getOthersPersonalizationSettings('show_guarantee_text')) and getOthersPersonalizationSettings('show_guarantee_text'))
                            <!--<div class="mt-20 d-flex align-items-center justify-content-center text-gray">-->
                            <!--    <i data-feather="thumbs-up" width="20" height="20"></i>-->
                            <!--    <span class="ml-5 font-14">{{ getOthersPersonalizationSettings('guarantee_text') }}</span>-->
                            <!--</div>-->
                        @endif

                        <div class="mt-35">
                            <strong class="d-block text-secondary font-weight-bold">{{ trans('webinars.this_webinar_includes',['classes' => trans('webinars.'.$course->type)]) }}</strong>
                            @if($course->isDownloadable())
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="download-cloud" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.downloadable_content') }}</span>
                                </div>
                            @endif

                            @if($course->quizzes->where('certificate', 1)->count() > 0)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="award" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.official_certificate') }}</span>
                                </div>
                            @endif

                            @if($course->quizzes->where('status', \App\models\Quiz::ACTIVE)->count() > 0)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="file-text" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.online_quizzes_count',['quiz_count' => $course->quizzes->where('status', \App\models\Quiz::ACTIVE)->count()]) }}</span>
                                </div>
                            @endif

                            @if($course->support)
                                <div class="mt-20 d-flex align-items-center text-gray">
                                    <i data-feather="headphones" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.instructor_support') }}</span>
                                </div>
                            @endif
                        </div>

                        <!--<div class="mt-40 p-10 rounded-sm border row align-items-center favorites-share-box">-->
                        <!--    @if($course->isWebinar())-->
                        <!--        <div class="col">-->
                        <!--            <a href="{{ $course->addToCalendarLink() }}" target="_blank" class="d-flex flex-column align-items-center text-center text-gray">-->
                        <!--                <i data-feather="calendar" width="20" height="20"></i>-->
                        <!--                <span class="font-12">{{ trans('public.reminder') }}</span>-->
                        <!--            </a>-->
                        <!--        </div>-->
                        <!--    @endif-->

                        <!--    <div class="col">-->
                        <!--        <a href="/favorites/{{ $course->slug }}/toggle" id="favoriteToggle" class="d-flex flex-column align-items-center text-gray">-->
                        <!--            <i data-feather="heart" class="{{ !empty($isFavorite) ? 'favorite-active' : '' }}" width="20" height="20"></i>-->
                        <!--            <span class="font-12">{{ trans('panel.favorite') }}</span>-->
                        <!--        </a>-->
                        <!--    </div>-->

                        <!--    <div class="col">-->
                        <!--        <a href="#" class="js-share-course d-flex flex-column align-items-center text-gray">-->
                        <!--            <i data-feather="share-2" width="20" height="20"></i>-->
                        <!--            <span class="font-12">{{ trans('public.share') }}</span>-->
                        <!--        </a>-->
                        <!--    </div>-->
                        <!--</div>-->

                        <!--<div class="mt-30 text-center">-->
                        <!--    <button type="button" id="webinarReportBtn" class="font-14 text-gray btn-transparent">{{ trans('webinars.report_this_webinar') }}</button>-->
                        <!--</div>-->
                    </div>
                </div>

                {{-- Cashback Alert --}}
                @include('web.default.includes.cashback_alert',['itemPrice' => $course->price])

                {{-- Gift Card --}}
                @if($course->canSale() and !empty(getGiftsGeneralSettings('status')) and !empty(getGiftsGeneralSettings('allow_sending_gift_for_courses')))
                    <a href="/gift/course/{{ $course->slug }}" class="d-flex align-items-center mt-30 rounded-lg border p-15">
                        <div class="size-40 d-flex-center rounded-circle bg-gray200">
                            <i data-feather="gift" class="text-gray" width="20" height="20"></i>
                        </div>
                        <div class="ml-5">
                            <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.gift_this_course') }}</h4>
                            <p class="font-12 text-gray">{{ trans('update.gift_this_course_hint') }}</p>
                        </div>
                    </a>
                @endif

                @if($course->teacher->offline)
                    <div class="rounded-lg shadow-sm mt-35 d-flex">
                        <div class="offline-icon offline-icon-left d-flex align-items-stretch">
                            <div class="d-flex align-items-center">
                                <img src="{{ config('app.js_css_url') }}/assets/default/img/profile/time-icon.png" alt="offline">
                            </div>
                        </div>

                        <div class="p-15">
                            <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
                            <p class="font-14 font-weight-500 text-gray mt-15">{{ $course->teacher->offline_message }}</p>
                        </div>
                    </div>
                @endif

                <div class="rounded-lg shadow-sm mt-35 px-25 py-20">
                    <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('webinars.'.$course->type) .' '. trans('webinars.specifications') }}</h3>

                    <div class="mt-30">
                        @if($course->isWebinar())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <i data-feather="calendar" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.start_date') }}:</span>
                                </div>
                                <span class="font-14">{{ dateTimeFormat($course->start_date, 'j M Y | H:i') }}</span>
                            </div>
                        @endif

                        <!--<div class="mt-20 d-flex align-items-center justify-content-between text-gray">-->
                        <!--    <div class="d-flex align-items-center">-->
                        <!--        <i data-feather="user" width="20" height="20"></i>-->
                        <!--        <span class="ml-5 font-14 font-weight-500">{{ trans('public.capacity') }}:</span>-->
                        <!--    </div>-->
                        <!--    @if(!is_null($course->capacity))-->
                        <!--        <span class="font-14">{{ $course->capacity }} {{ trans('quiz.students') }}</span>-->
                        <!--    @else-->
                        <!--        <span class="font-14">{{ trans('update.unlimited') }}</span>-->
                        <!--    @endif-->
                        <!--</div>-->

                        <!--<div class="mt-20 d-flex align-items-center justify-content-between text-gray">-->
                        <!--    <div class="d-flex align-items-center">-->
                        <!--        <i data-feather="clock" width="20" height="20"></i>-->
                        <!--        <span class="ml-5 font-14 font-weight-500">{{ trans('public.duration') }}:</span>-->
                        <!--    </div>-->
                        <!--    <span class="font-14">{{ convertMinutesToHourAndMinute(!empty($course->duration) ? $course->duration : 0) }} {{ trans('home.hours') }}</span>-->
                        <!--</div>-->

                        <!--<div class="mt-20 d-flex align-items-center justify-content-between text-gray">-->
                        <!--    <div class="d-flex align-items-center">-->
                        <!--        <i data-feather="users" width="20" height="20"></i>-->
                        <!--        <span class="ml-5 font-14 font-weight-500">{{ trans('quiz.students') }}:</span>-->
                        <!--    </div>-->
                        <!--    @if(url()->current()=='https://lms.asttrolok.com/course/Free-Astrology-Course')-->
                        <!--    <span class="font-14">3200</span>-->
                            
                        <!--    @else-->
                        <!--    <span class="font-14">{{ $course->sales_count }}</span>-->
                            
                        <!--    @endif-->
                            
                        <!--</div>-->

                        @if($course->isWebinar())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.sessions') }}:</span>
                                </div>
                                <!--<span class="font-14">{{ $course->sessions->count() }}</span>-->
                            </div>
                        @endif

                        @if($course->isTextCourse())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('webinars.text_lessons') }}:</span>
                                </div>
                                <span class="font-14">{{ $course->textLessons->count() }}</span>
                            </div>
                        @endif

                        @if($course->isCourse() or $course->isTextCourse())
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.files') }}:</span>
                                </div>
                                <span class="font-14">{{ $course->files->count() }}</span>
                            </div>

                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <img src="{{ config('app.js_css_url') }}/assets/default/img/icons/sessions.svg" width="20" alt="sessions">
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('public.created_at') }}:</span>
                                </div>
                                <span class="font-14">{{ dateTimeFormat($course->created_at,'j M Y') }}</span>
                            </div>
                        @endif

                        @if(!empty($course->access_days))
                            <div class="mt-20 d-flex align-items-center justify-content-between text-gray">
                                <div class="d-flex align-items-center">
                                    <i data-feather="alert-circle" width="20" height="20"></i>
                                    <span class="ml-5 font-14 font-weight-500">{{ trans('update.access_period') }}:</span>
                                </div>
                                <span class="font-14">{{ $course->access_days }} {{ trans('public.days') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- organization --}}
                @if($course->creator_id != $course->teacher_id)
                    @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $course->creator])
                @endif
                {{-- teacher --}}
                @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $course->teacher])

                @if($course->webinarPartnerTeacher->count() > 0)
                    @foreach($course->webinarPartnerTeacher as $webinarPartnerTeacher)
                        @include('web.default.course.sidebar_instructor_profile', ['courseTeacher' => $webinarPartnerTeacher->teacher])
                    @endforeach
                @endif
                {{-- ./ teacher --}}

                {{-- tags --}}
                @if($course->tags->count() > 0)
                    <div class="rounded-lg tags-card shadow-sm mt-35 px-25 py-20">
                        <h3 class="sidebar-title font-16 text-secondary font-weight-bold">{{ trans('public.tags') }}</h3>

                        <div class="d-flex flex-wrap mt-20">
                            @foreach($course->tags as $tag)
                                <a href="" class="tag-item bg-gray200 p-5 font-14 text-gray font-weight-500 rounded">{{ $tag->title }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="row">
                       
                            <!--<div class="rounded-lg sidebar-ads mt-35 col-12">-->
                            <!--    <a href="https://lms.asttrolok.com/course/Astromani_2023">-->
                            <!--        <img src="/store/1/default_images/banners/Astromany-course.jpg" class="img-cover rounded-lg" alt="">-->
                            <!--    </a>-->
                            <!--</div>-->
                            <!--  <div class="rounded-lg sidebar-ads mt-35 col-12">-->
                            <!--    <a href="https://lms.asttrolok.com/course/Professional-Astrology-Course">-->
                            <!--        <img src="/store/1/default_images/banners/Asttrology-course.jpg" class="img-cover rounded-lg" alt="">-->
                            <!--    </a>-->
                            <!--</div>-->
                            
{{--                             
         <div class="col-12 col-lg-12 mt-20">
             <div class="webinar-card">
    <figure>
        <div class="image-box">
           <span class="badge badge-primary">Course</span>
            
            <a href="https://lms.asttrolok.com/course/{{ $astromani_23->slug }}">
                <img src="{{ config('app.img_dynamic_url') }}{{ $astromani_23->thumbnail }}" class="img-cover" alt="{{ $astromani_23->slug }}">
            </a>

            </div>

        <figcaption class="webinar-card-body">
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $astromani_23->teacher->avatar }}" class="img-cover" alt="{{ $astromani_23->full_name }}">
                </div>
                <a href="{{ $astromani_23->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $astromani_23->teacher->full_name }}</a>
            </div>

            <a href="https://lms.asttrolok.com/course/{{ $astromani_23->slug }}">
                <h3 class="mt-15  font-weight-bold font-16 text-dark-blue">{{ clean($astromani_23->title,'title') }}</h3>
            </a>
           
        </figcaption>
    </figure>
     </div>
         </div>
         
         <div class="col-12 col-lg-12 mt-20">
             <div class="webinar-card">
    <figure>
        <div class="image-box">
           <span class="badge badge-primary">Course</span>
            
            <a href="https://lms.asttrolok.com/course/{{ $course_Professional->slug }}">
                <img src="{{ config('app.img_dynamic_url') }}{{ $course_Professional->thumbnail }}" class="img-cover" alt="{{ $course_Professional->slug }}">
            </a>

            </div>

        <figcaption class="webinar-card-body">
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $course_Professional->teacher->avatar }}" class="img-cover" alt="{{ $course_Professional->full_name }}">
                </div>
                <a href="{{ $course_Professional->teacher->getProfileUrl() }}" target="_blank" class="user-name ml-5 font-14">{{ $course_Professional->teacher->full_name }}</a>
            </div>

            <a href="https://lms.asttrolok.com/course/{{ $course_Professional->slug }}">
                <h3 class="mt-15  font-weight-bold font-16 text-dark-blue">{{ clean($course_Professional->title,'title') }}</h3>
            </a>
           
        </figcaption>
    </figure>
     </div>
         </div>
         
          --}}
         
         
         
         
         
           </div>
                
                
                {{-- ads --}}
                <!--@if(!empty($advertisingBannersSidebar) and count($advertisingBannersSidebar))-->
                <!--    <div class="row">-->
                <!--        @foreach($advertisingBannersSidebar as $sidebarBanner)-->
                <!--            <div class="rounded-lg sidebar-ads mt-35 col-{{ $sidebarBanner->size }}">-->
                <!--                <a href="{{ $sidebarBanner->link }}">-->
                <!--                    <img src="{{ $sidebarBanner->image }}" class="img-cover rounded-lg" alt="{{ $sidebarBanner->title }}">-->
                <!--                </a>-->
                <!--            </div>-->
                <!--        @endforeach-->
                <!--    </div>-->

                <!--@endif-->
            </div>
        </div>

        {{-- Ads Bannaer --}}
        @if(!empty($advertisingBanners) and count($advertisingBanners))
            <div class="mt-30 mt-md-50">
                <div class="row">
                    @foreach($advertisingBanners as $banner)
                        <div class="col-{{ $banner->size }}">
                            <a href="{{ $banner->link }}">
                                <img src="{{ config('app.img_dynamic_url') }}{{ $banner->image }}" class="img-cover rounded-sm" alt="{{ $banner->title }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        {{-- ./ Ads Bannaer --}}
    </section>

    <div id="webinarReportModal" class="d-none">
        <h3 class="section-title after-line font-20 text-dark-blue">{{ trans('product.report_the_course') }}</h3>

        <form action="/course/{{ $course->id }}/report" method="post" class="mt-25">

            <div class="form-group">
                <label class="text-dark-blue font-14">{{ trans('product.reason') }}</label>
                <select id="reason" name="reason" class="form-control">
                    <option value="" selected disabled>{{ trans('product.select_reason') }}</option>

                    @foreach(getReportReasons() as $reason)
                        <option value="{{ $reason }}">{{ $reason }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback"></div>
            </div>

            <div class="form-group">
                <label class="text-dark-blue font-14" for="message_to_reviewer">{{ trans('public.message_to_reviewer') }}</label>
                <textarea name="message" id="message_to_reviewer" class="form-control" rows="10"></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <p class="text-gray font-16">{{ trans('product.report_modal_hint') }}</p>

            <div class="mt-30 d-flex align-items-center justify-content-end">
                <button type="button" class="js-course-report-submit btn btn-sm btn-primary">{{ trans('panel.report') }}</button>
                <button type="button" class="btn btn-sm btn-danger ml-10 close-swl">{{ trans('public.close') }}</button>
            </div>
        </form>
    </div>
<div class="container demo">
	
	
	<div class="text-center d-none">
	
		<button type="button" class="btn btn-demo" data-toggle="modal" data-target="#myModal2">
			Right Sidebar Modal
		</button>
	</div>


	
	<!-- Modal -->
	<div class="modal right fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" style="z-index: 52022;">
		<div class="modal-dialog" role="document">
			<div class="modal-content">

				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					
				</div>

				<div class="modal-body">
					<div class="h-100">
            <div class="navbar-shopping-cart h-100" data-simplebar>
                @if(!empty($userCarts) and count($userCarts) > 0)
                    <div class="mb-auto">
                        @foreach($userCarts as $cart)
                            @php
                                $cartItemInfo = $cart->getItemInfo();
                            @endphp

                            @if(!empty($cartItemInfo))
                                <div class="navbar-cart-box d-flex align-items-center">

                                    <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank" class="navbar-cart-img">
                                        <img src="{{ config('app.img_dynamic_url') }}{{ $cartItemInfo['imgPath'] }}" alt="product title" class="img-cover"/>
                                    </a>

                                    <div class="navbar-cart-info">
                                        <a href="{{ $cartItemInfo['itemUrl'] }}" target="_blank">
                                            <h4>{{ $cartItemInfo['title'] }}</h4>
                                        </a>
                                        <div class="price mt-20">
                                            @if(!empty($cartItemInfo['discountPrice']))
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true) }}</span>
                                                <span class="off ml-15">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @else
                                                <span class="text-primary font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true) }}</span>
                                            @endif

                                            @if(!empty($cartItemInfo['quantity']))
                                                <span class="font-12 text-warning font-weight-500 ml-10">({{ $cartItemInfo['quantity'] }} {{ trans('update.product') }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="navbar-cart-actions">
                        <div class="navbar-cart-total mt-15 border-top d-flex align-items-center justify-content-between">
                            <strong class="total-text">{{ trans('cart.total') }}</strong>
                            <strong class="text-primary font-weight-bold">{{ !empty($totalCartsPrice) ? handlePrice($totalCartsPrice, true, true, false, null, true) : 0 }}</strong>
                        </div>

                        <a href="/cart/" class="btn btn-sm btn-primary btn-block mt-50 mt-md-15">{{ trans('cart.go_to_cart') }}</a>
                    </div>
                @else
                    <div class="d-flex align-items-center text-center py-50">
                        <i data-feather="shopping-cart" width="20" height="20" class="mr-10"></i>
                        <span class="">{{ trans('cart.your_cart_empty') }}</span>
                    </div>
                @endif
            </div>
        </div>
				</div>

			</div><!-- modal-content -->
		</div><!-- modal-dialog -->
	</div><!-- modal -->
	
	
</div><!-- container -->
@if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
<button type="button" data-toggle="modal" data-target="#buynow_modal" class=" btn btn-primary btn-sm disabled px-25 buy-btn1" >
    BUY NOW
</button>

@endif

    @include('web.default.course.share_modal')
    @include('web.default.course.buy_with_point_modal')
    @include('web.default.course.login_modal')
     @include('web.default.course.pop_up')
    @include('web.default.course.buynow_modal')
   
@endsection

@push('scripts_bottom')
@if(empty($authUser))
<script>
       
         function myFunction() {
             
   var dots = document.getElementById("abouthide");
   var gradiant1 = document.getElementById("gradiant1");
   var moreText = document.getElementById("readmore");
   if (dots.style.overflow == "hidden") {
     dots.style.overflow = "unset";
     dots.style.maxHeight = "100%";
     gradiant1.style.display = "none";
     moreText.text = "Read less";
   } else {
     dots.style.overflow = "hidden";
     dots.style.maxHeight = "176px";
     gradiant1.style.display = "block";
     
     moreText.text = "Read more";
   }
 }
//      setTimeout(function() {
//     $('#textpop').modal();
// }, 5000);
</script>
@endif
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/time-counter-down.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/barrating/jquery.barrating.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/7.10.2/video.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/videojs-youtube/3.0.1/Youtube.min.js"></script>
     <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/1212youtube.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/video/vimeo.js"></script>
<script>
    function buy_course(){
        // alert('');
        $('.buy_now').click();
    }
   
</script>
@if(Session::has('addtocart'))
<script>

$("#myModal2").modal('show');
  $('.modal-dialog').addClass('afterpop');
    // $('.btn-demo').click();
    // $('.modal-dialog').addClass('afterpop');
</script>
@endif
@php 
    Illuminate\Support\Facades\Session::forget('addtocart');  
@endphp
<!--<?php   //unset($_SESSION['addtocart']); ?>-->
    <script>
        var webinarDemoLang = '{{ trans('webinars.webinar_demo') }}';
        var replyLang = '{{ trans('panel.reply') }}';
        var closeLang = '{{ trans('public.close') }}';
        var saveLang = '{{ trans('public.save') }}';
        var reportLang = '{{ trans('panel.report') }}';
        var reportSuccessLang = '{{ trans('panel.report_success') }}';
        var reportFailLang = '{{ trans('panel.report_fail') }}';
        var messageToReviewerLang = '{{ trans('public.message_to_reviewer') }}';
        var copyLang = '{{ trans('public.copy') }}';
        var copiedLang = '{{ trans('public.copied') }}';
        var learningToggleLangSuccess = '{{ trans('public.course_learning_change_status_success') }}';
        var learningToggleLangError = '{{ trans('public.course_learning_change_status_error') }}';
        var notLoginToastTitleLang = '{{ trans('public.not_login_toast_lang') }}';
        var notLoginToastMsgLang = '{{ trans('public.not_login_toast_msg_lang') }}';
        var notAccessToastTitleLang = '{{ trans('public.not_access_toast_lang') }}';
        var notAccessToastMsgLang = '{{ trans('public.not_access_toast_msg_lang') }}';
        var canNotTryAgainQuizToastTitleLang = '{{ trans('public.can_not_try_again_quiz_toast_lang') }}';
        var canNotTryAgainQuizToastMsgLang = '{{ trans('public.can_not_try_again_quiz_toast_msg_lang') }}';
        var canNotDownloadCertificateToastTitleLang = '{{ trans('public.can_not_download_certificate_toast_lang') }}';
        var canNotDownloadCertificateToastMsgLang = '{{ trans('public.can_not_download_certificate_toast_msg_lang') }}';
        var sessionFinishedToastTitleLang = '{{ trans('public.session_finished_toast_title_lang') }}';
        var sessionFinishedToastMsgLang = '{{ trans('public.session_finished_toast_msg_lang') }}';
        var sequenceContentErrorModalTitle = '{{ trans('update.sequence_content_error_modal_title') }}';
        var courseHasBoughtStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasBoughtStatusToastMsgLang = '{{ trans('site.you_bought_webinar') }}';
        var courseNotCapacityStatusToastTitleLang = '{{ trans('public.request_failed') }}';
        var courseNotCapacityStatusToastMsgLang = '{{ trans('cart.course_not_capacity') }}';
        var courseHasStartedStatusToastTitleLang = '{{ trans('cart.fail_purchase') }}';
        var courseHasStartedStatusToastMsgLang = '{{ trans('update.class_has_started') }}';
        var joinCourseWaitlistLang = '{{ trans('update.join_course_waitlist') }}';
        var joinCourseWaitlistModalHintLang = "{{ trans('update.join_course_waitlist_modal_hint') }}";
        var joinLang = '{{ trans('footer.join') }}';
        var nameLang = '{{ trans('auth.name') }}';
        var emailLang = '{{ trans('auth.email') }}';
        var phoneLang = '{{ trans('public.phone') }}';
        var captchaLang = '{{ trans('site.captcha') }}';
    </script>
<script>
// $('#myModal21').modal();
// $("#myModal21").modal('show');
//   $('.modal-dialog').addClass('afterpop');
    // $('.btn-demo').click();
    // $('.modal-dialog').addClass('afterpop');
</script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/comment.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/video_player_helpers.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/webinar_show.min.js"></script>
@endpush


