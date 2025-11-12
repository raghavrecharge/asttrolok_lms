@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-instructors-details.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
      <style>
        /* @media (max-width: 991px)
.homehide {
    display: none!important;
} */
    </style>
@endpush


{{session()->put('my_test_key',url()->full())}}

@if(!(auth()->check()))
    
    @push('scripts_top')
    <script  >
       // window.location.href = "/login";
    </script>
    @endpush
    
@endif

@section('content')
<style>

.abcak{
    
    border: 1px solid #33ba7c;
    padding: 5px 15px 5px 15px;
    border-radius: 25px;

}
    @media (max-width: 2561px) {
  .site-top-banner {
    height: 270px !important;
  }
}

@media (max-width: 1921px) {
  .site-top-banner {
    height: 530px !important;
  }
}

@media (max-width: 1441px) {
  .site-top-banner {
    height: 255px !important;
  }
}

@media (max-width: 1025px) {
  .site-top-banner {
    height: 350px !important;
  }
}

@media (max-width: 991px) {
  .site-top-banner {
    height: 100px !important;
  }
}
 
</style>
    {{-- <section class="site-top-banner position-relative">
        <!--<img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $user->getCover() }}" class="img-cover" alt=""/>-->
    </section> --}}
    <section class="container mt-0 mb-p bg-t">
        <div class="d-flex align-items-left align-items-start justify-content-between ">
            <div class="col-md-6 mt-20">
               <a href="/consult-with-astrologers" class="mt-10 back-button abcak"><img loading="lazy" src="/assets/default/img/profile/left-arrow.svg" alt="left-arrow"  class="verify-img1 mr-5"> Back</a>
            </div>
            <div class="ft-right mt-10">
                <div class="redius-20 stars-card d-flex align-items-end ft-right">
                  {{--  @include('web.default.includes.webinar.rate1',['rate' => $ratings[$user["id"]]]) --}}
                  @include('web.default.includes.webinar.rate1',['rate' => $user["rating"]])
                </div>
            </div>
        </div>
    </section>
    <section class=" mt-">
        <div class="rounded-lg1  px-25 py-20 px-lg-50 py-lg-35 position-relative  bg-white">
            <div class="profile-info-box d-flex align-items-start justify-content-between">
                <div class="user-details d-flex align-items-center">
                    <div class="user-profile-avatar bg-gray200">
                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $user->getAvatar(190) }}" class="img-cover" alt="{{ $user["full_name"] }}"/>

                        {{-- @if($user->offline)
                            <span class="user-circle-badge unavailable d-flex align-items-center justify-content-center">
                                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                            </span>
                        @elseif($user->verified)
                            <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                                <i data-feather="check" width="20" height="20" class="text-white"></i>
                            </span>
                        @endif --}}
                    </div>
                    <div class=" ml-lg-40">
                        <h1 class="user-title font-weight-bold text-dark-blue">{{ $user["full_name"] }} @if($user->offline)
                            <span class="user-circle-badge unavailable d-flex align-items-center justify-content-center">
                                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                            </span>
                            @elseif($user->verified)
                            <span class="user-circle-badge">
                                <img loading="lazy" src="/assets/default/img/profile/verify-user.svg" alt="Verify User Icon - Asttrolok"  class="verify-img"></span>
                            @endif
                          
                        </h1>
                        <span class="text-gray">{{ $user["headline"] }}</span>

                      
                        <div class="  font-13s text-gray text-left align-items-center">
                            @if(!empty($user["bio"]))
                            <pre class="mt-10  font-13s text-dark-blue " style="font-family: var(--font-family-base) !important;">{{$user["bio"]}}</pre>
                        {{-- <div class="stars-card d-flex align-items-center"> --}}
                            {{-- @include('web.default.includes.webinar.rate',['rate' => $ratings[$user["id"]]]) --}}
                        {{-- </div> --}}
                            @endif
                            @if(!empty($meeting->amount))
                            <div class="mt-5">
                            <span class=" price text-primary1 font-weight-500 ">{{  handlePrice($meeting->amount/30)  }}</span><span class="text-dark-blue" style="font-size: small!important;">  / min</span> 
                            </div>
                            @endif
                            <a href="#appointments" class="btn mt-20 bookbtn btn-primary  bookb">
                                <svg width="15" height="13" viewBox="0 0 15 13" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M12.8933 0H11.5933V1.3C11.5933 1.56 11.3767 1.73333 11.16 1.73333C10.9433 1.73333 10.7267 1.56 10.7267 1.3V0H3.79333V1.3C3.79333 1.56 3.57667 1.73333 3.36 1.73333C3.14333 1.73333 2.92667 1.56 2.92667 1.3V0H1.62667C0.976667 0 0.5 0.563333 0.5 1.3V2.86H14.3667V1.3C14.3667 0.563333 13.5867 0 12.8933 0ZM0.5 3.77V11.7C0.5 12.48 0.976667 13 1.67 13H12.9367C13.63 13 14.41 12.4367 14.41 11.7V3.77H0.5ZM4.35667 11.05H3.31667C3.14333 11.05 2.97 10.92 2.97 10.7033V9.62C2.97 9.44667 3.1 9.27333 3.31667 9.27333H4.4C4.57333 9.27333 4.74667 9.40333 4.74667 9.62V10.7033C4.70333 10.92 4.57333 11.05 4.35667 11.05ZM4.35667 7.15H3.31667C3.14333 7.15 2.97 7.02 2.97 6.80333V5.72C2.97 5.54667 3.1 5.37333 3.31667 5.37333H4.4C4.57333 5.37333 4.74667 5.50333 4.74667 5.72V6.80333C4.70333 7.02 4.57333 7.15 4.35667 7.15ZM7.82333 11.05H6.74C6.56667 11.05 6.39333 10.92 6.39333 10.7033V9.62C6.39333 9.44667 6.52333 9.27333 6.74 9.27333H7.82333C7.99667 9.27333 8.17 9.40333 8.17 9.62V10.7033C8.17 10.92 8.04 11.05 7.82333 11.05ZM7.82333 7.15H6.74C6.56667 7.15 6.39333 7.02 6.39333 6.80333V5.72C6.39333 5.54667 6.52333 5.37333 6.74 5.37333H7.82333C7.99667 5.37333 8.17 5.50333 8.17 5.72V6.80333C8.17 7.02 8.04 7.15 7.82333 7.15ZM11.29 11.05H10.2067C10.0333 11.05 9.86 10.92 9.86 10.7033V9.62C9.86 9.44667 9.99 9.27333 10.2067 9.27333H11.29C11.4633 9.27333 11.6367 9.40333 11.6367 9.62V10.7033C11.6367 10.92 11.5067 11.05 11.29 11.05ZM11.29 7.15H10.2067C10.0333 7.15 9.86 7.02 9.86 6.80333V5.72C9.86 5.54667 9.99 5.37333 10.2067 5.37333H11.29C11.4633 5.37333 11.6367 5.50333 11.6367 5.72V6.80333C11.6367 7.02 11.5067 7.15 11.29 7.15Z" fill="white"/>
</svg>

                             <span class="ml-5 font-12"> Book a Consultation </span>
                                </a>
                        </div>
                        

                        <!--<div class="w-100 mt-10 d-none d-flex align-items-center justify-content-center justify-content-lg-start">-->
                        <div class="w-100 mt-10 d-none  align-items-center justify-content-center justify-content-lg-start">
                            <div class="d-flex flex-column followers-status">
                                <span class="font-20 font-weight-bold text-dark-blue">{{ $userFollowers->count() }}</span>
                                <span class="font-14 text-gray">{{ trans('panel.followers') }}</span>
                            </div>

                            <div class="d-flex flex-column ml-25 pl-5 following-status">
                                <span class="font-20 font-weight-bold text-dark-blue">{{ $userFollowing->count() }}</span>
                                <span class="font-14 text-gray">{{ trans('panel.following') }}</span>
                            </div>
                        </div>

                        <div class="user-reward-badges d-none flex-wrap align-items-center mt-15">
                        <!--<div class="user-reward-badges d-flex flex-wrap align-items-center mt-15">-->
                            @if(!empty($userBadges))
                                @foreach($userBadges as $userBadge)
                                    <div class="mr-15" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($userBadge->badge_id) ? nl2br($userBadge->badge->description) : nl2br($userBadge->description)) !!}">
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ !empty($userBadge->badge_id) ? $userBadge->badge->image : $userBadge->image }}" width="32" height="32" alt="{{ !empty($userBadge->badge_id) ? $userBadge->badge->title : $userBadge->title }}">
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!--<div class="user-actions d-flex flex-column">-->
                <div class="user-actions d-none flex-column">
                    <button type="button" id="followToggle" data-user-id="{{ $user['id'] }}" class="btn btn-{{ (!empty($authUserIsFollower) and $authUserIsFollower) ? 'danger' : 'primary' }} btn-sm">
                        @if(!empty($authUserIsFollower) and $authUserIsFollower)
                            {{ trans('panel.unfollow') }}
                        @else
                            {{ trans('panel.follow') }}
                        @endif
                    </button>

                    @if($user->public_message)
                        <button type="button" class="js-send-message btn btn-border-white rounded btn-sm mt-15">{{ trans('site.send_message') }}</button>
                    @endif
                </div>
            </div>
<div class=" homehide">
            <!--<div class="mt-40 border-top"></div>-->

            <!--<div class="row mt-30 w-100 d-flex align-items-center justify-content-around">-->
            <!--    <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">-->
            <!--        <div class="state-icon orange p-15 rounded-lg">-->
            <!--            <img loading="lazy" src="/assets/default/img/profile/students.svg" alt="">-->
            <!--        </div>-->
            <!--        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $user->students_count }}</span>-->
            <!--        <span class="font-14 text-gray">{{ trans('quiz.students') }}</span>-->
            <!--    </div>-->

            <!--    <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">-->
            <!--        <div class="state-icon blue p-15 rounded-lg">-->
            <!--            <img loading="lazy" src="/assets/default/img/profile/webinars.svg" alt="">-->
            <!--        </div>-->
            <!--        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ count($webinars) }}</span>-->
            <!--        <span class="font-14 text-gray">{{ trans('webinars.classes') }}</span>-->
            <!--    </div>-->

            <!--    <div class="col-6 col-md-3 mt-20 mt-md-0 user-profile-state d-flex flex-column align-items-center">-->
            <!--        <div class="state-icon green p-15 rounded-lg">-->
            <!--            <img loading="lazy" src="/assets/default/img/profile/reviews.svg" alt="">-->
            <!--        </div>-->
            <!--        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $user->reviewsCount() }}</span>-->
            <!--        <span class="font-14 text-gray">{{ trans('product.reviews') }}</span>-->
            <!--    </div>-->


            <!--    <div class="col-6 col-md-3 mt-20 mt-md-0 user-profile-state d-flex flex-column align-items-center">-->
            <!--        <div class="state-icon royalblue p-15 rounded-lg">-->
            <!--            <img loading="lazy" src="/assets/default/img/profile/appointments.svg" alt="">-->
            <!--        </div>-->
            <!--        <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $appointments }}</span>-->
            <!--        <span class="font-14 text-gray">{{ trans('site.appointments') }}</span>-->
            <!--    </div>-->

            <!--</div>-->
            </div>
        </div>
    </section>
  <style>
.tab-pane li:after {
    content: "";
    width: 9px;
    height: 9px;
    background-color: #3f3f3f;
    border-radius: 50%;
    position: absolute;
    left: 0;
    top: 5px;
}
.tab-pane  li {
    position: relative;
    padding-left: 15px;
}
</style>
    <section class="container abouts">
      
        @if(!empty($user->about))
        <div class="mt-10">
            <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.about') }}</h3>

            <div class="mt-5 abouthide" id="abouthide" style=" max-height: 55px;overflow: hidden;">
                {!! nl2br($user->about) !!}
               
            </div>
<!--            <div id="gradiant1" style="-->
<!--    width: 100%;-->
<!--    height: 80px;-->
<!--    /* background-color: white; */-->
<!--    position: absolute;-->
<!--    bottom: 134px;-->
<!--    background-image: linear-gradient(#ffffff30, white);-->
<!--"></div>-->
            <div class="readmore">
            <a  id="readmore" onclick="myFunction();">Read More</a>
                        </div>
        </div>
    @endif
       
    </section>
    <div class="uderline"></div>
    <div class="container mt-0">
        <section class=" px-10  position-relative">
            <ul  class="nav nav-tabs mobilehiden d-flex align-items-center px-20 px-lg-50 pb-15" id="tabs-tab" role="tablist">
                <li class="nav-item mr-20 mr-lg-50 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'active' : ''  }}" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">{{ trans('site.about') }}</a>
                </li>
                <!--<li class="nav-item mr-20 mr-lg-50 mt-30">-->
                <!--    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'webinars') ? 'active' : ''  }}" id="webinars-tab" data-toggle="tab" href="#webinars" role="tab" aria-controls="webinars" aria-selected="false">{{ trans('panel.classes') }}</a>-->
                <!--</li>-->

                @if($user->isOrganization())
                    <!--<li class="nav-item mr-20 mr-lg-50 mt-30">-->
                    <!--    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'instructors') ? 'active' : ''  }}" id="instructors-tab" data-toggle="tab" href="#instructors" role="tab" aria-controls="instructors" aria-selected="false">{{ trans('home.instructors') }}</a>-->
                    <!--</li>-->
                @endif

                @if(!empty(getStoreSettings('status')) and getStoreSettings('status'))
                    <!--<li class="nav-item mr-20 mr-lg-50 mt-30">-->
                    <!--    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'products') ? 'active' : ''  }}" id="webinars-tab" data-toggle="tab" href="#products" role="tab" aria-controls="products" aria-selected="false">{{ trans('update.products') }}</a>-->
                    <!--</li>-->
                @endif

                <!--<li class="nav-item mr-20 mr-lg-50 mt-30">-->
                <!--    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'posts') ? 'active' : ''  }}" id="webinars-tab" data-toggle="tab" href="#posts" role="tab" aria-controls="posts" aria-selected="false">{{ trans('update.articles') }}</a>-->
                <!--</li>-->

                @if(!empty(getFeaturesSettings('forums_status')) and getFeaturesSettings('forums_status'))
                    <!--<li class="nav-item mr-20 mr-lg-50 mt-30">-->
                    <!--    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'forum') ? 'active' : ''  }}" id="webinars-tab" data-toggle="tab" href="#forum" role="tab" aria-controls="forum" aria-selected="false">{{ trans('update.forum') }}</a>-->
                    <!--</li>-->
                @endif

                <!--<li class="nav-item mr-20 mr-lg-50 mt-30">-->
                <!--    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'badges') ? 'active' : ''  }}" id="badges-tab" data-toggle="tab" href="#badges" role="tab" aria-controls="badges" aria-selected="false">{{ trans('site.badges') }}</a>-->
                <!--</li>-->

                <li class="nav-item mr-20 mr-lg-50 mt-30">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'appointments') ? 'active' : ''  }}" id="appointments-tab" data-toggle="tab" href="#appointments" role="tab" aria-controls="appointments" aria-selected="false">Book a Consultation</a>
                </li>
            </ul>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane  fade px-20 px-lg-50 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'show active' : ''  }}" id="about" role="tabpanel" aria-labelledby="about-tab">
                    @include('web.default.user.profile_tabs.about')
                </div>

                <div class="tab-pane fade " id="webinars" role="tabpanel" aria-labelledby="webinars-tab">
                    @include('web.default.user.profile_tabs.webinars')
                </div>

                @if($user->isOrganization())
                    <div class="tab-pane  fade" id="instructors" role="tabpanel" aria-labelledby="instructors-tab">
                        @include('web.default.user.profile_tabs.instructors')
                    </div>
                @endif

                <div class="tab-pane fade" id="posts" role="tabpanel" aria-labelledby="posts-tab">
                    @include('web.default.user.profile_tabs.posts')
                </div>

                @if(!empty(getFeaturesSettings('forums_status')) and getFeaturesSettings('forums_status'))
                    <div class="tab-pane fade" id="forum" role="tabpanel" aria-labelledby="forum-tab">
                        @include('web.default.user.profile_tabs.forum')
                    </div>
                @endif

                @if(!empty(getStoreSettings('status')) and getStoreSettings('status'))
                    <div class="tab-pane fade" id="products" role="tabpanel" aria-labelledby="products-tab">
                        @include('web.default.user.profile_tabs.products')
                    </div>
                @endif

                <div class="tab-pane fade" id="badges" role="tabpanel" aria-labelledby="badges-tab">
                    @include('web.default.user.profile_tabs.badges')
                </div>

                <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'appointments') ? 'show active' : ''  }}" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                    @include('web.default.user.profile_tabs.appointments')
                </div>
            </div>
        </section>
    </div>

    @include('web.default.user.send_message_modal')

@endsection

@push('scripts_bottom')

    <script  >
         
        var unFollowLang = '{{ trans('panel.unfollow') }}';
        var followLang = '{{ trans('panel.follow') }}';
        var reservedLang = '{{ trans('meeting.reserved') }}';
        var availableDays = {{ json_encode($times) }};
        var messageSuccessSentLang = '{{ trans('site.message_success_sent') }}';
        function myFunction() {
            
  var dots = document.getElementById("abouthide");
  var moreText = document.getElementById("readmore");
  if (dots.style.overflow == "hidden") {
    dots.style.overflow = "unset";
    dots.style.maxHeight = "100%";
    moreText.text = "Read less";
  } else {
    dots.style.overflow = "hidden";
    dots.style.maxHeight = "55px";
    
    moreText.text = "Read more";
  }
}
    </script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-date.js"></script>
    <script   src="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-datepicker.js"></script>

    <script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/profile.min.js"></script>

    @if(!empty($user->live_chat_js_code) and !empty(getFeaturesSettings('show_live_chat_widget')))
        <script  >
            (function () {
                "use strict"

                {!! $user->live_chat_js_code !!}
            })(jQuery)
           
        </script>
    @endif
@endpush
