@extends('web.default2'.'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
       <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/profile.min.css">
        <style>
        @media (max-width: 991px){
.homehide {
    display: none!important;
}
}
.nav-tabs .nav-item a.active:after {
    transform: translate(-50%, 0px ) !important;
}
    </style>

<script>
  gtag('event', 'conversion', {
      'send_to': 'AW-795191608/8cYUCN3vt5cZELjSlvsC',
      'transaction_id': ''
  });
</script>
@endpush

{{session()->put('my_test_key',url()->full())}}

@if(!(auth()->check()))

    @push('scripts_top')
    <script>
       // window.location.href = "/login";
    </script>
    @endpush

@endif

@section('content')
<style>
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

<div class="profile-cover-card">
        <img src="https://storage.googleapis.com/astrolok/webp/store/1/banner/Consult.webp" class="img-cover" alt="">
</div>
 <div class="profile-container">
        <div class="container mb-104">
            <div class="row">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="profile-card-has-mask bg-white py-16 rounded-24 w-100">
                        <div class="d-flex-center flex-column text-center px-16">

                            <div class="profile-avatar-card size-80 rounded-circle mt-32">
                                <img src="{{ config('app.img_dynamic_url') }}{{ $user->getAvatar(190) }}" alt="Ricardo Dave" class="img-cover rounded-circle">
                            </div>

                            <h4 class="mt-16 font-18 font-weight-bold">{{ $user["full_name"] }} </h4>
                                @include('web.default2.includes.webinar.rate',['rate' => $user["rating"]])
                                @if(!empty($user["bio"]))
                            <pre class="mt-10 font-18  text-dark-blue " style="font-family: var(--font-family-base) !important;">{{$user["bio"]}}</pre>
                    @endif
                                 @if($meeting)
                            <div class="mt-5">
                            <span class=" font-24 text-primary font-weight-500">{{ handlePrice($meeting->amount/30) }}</span><span class="text-dark-blue" style="font-size: small!important;"> / Min</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-8 col-lg-9 mt-32 mt-md-0">
                    <div class="profile-card-has-mask position-relative bg-white pt-24 pb-20 rounded-24">
                           <section class="rounded-lg border px-10 pb-35 pt-5 position-relative">
            <ul class="nav nav-tabs d-flex align-items-center " id="tabs-tab" role="tablist">
                <li class="nav-item ">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'active' : ''  }}" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">{{ trans('site.about') }}</a>
                </li>

                <li class="nav-item  ">
                    <a class="position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'appointments') ? 'active' : ''  }}" id="appointments-tab" data-toggle="tab" href="#appointments" role="tab" aria-controls="appointments" aria-selected="false">Book a Consultation</a>
                </li>
            </ul>

            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade px-20 px-lg-50 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'show active' : ''  }}" id="about" role="tabpanel" aria-labelledby="about-tab">
                    @include('web.default2.user.profile_tabs.about')
                </div>

                <div class="tab-pane fade  {{ (request()->get('tab') == 'appointments') ? 'show active' : ''  }}" id="appointments" role="tabpanel" aria-labelledby="appointments-tab">
                <div class="row">
                @include('web.default2.user.profile_tabs.appointments')
                </div></div>
            </div>
        </section>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('web.default2.user.send_message_modal')

@endsection

@push('scripts_bottom')
    <script>
        var unFollowLang = '{{ trans('panel.unfollow') }}';
        var followLang = '{{ trans('panel.follow') }}';
        var reservedLang = '{{ trans('meeting.reserved') }}';
        var availableDays = {{ json_encode($times) }};
        var messageSuccessSentLang = '{{ trans('site.message_success_sent') }}';
    </script>

    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-date.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-datepicker.js"></script>
    <script src="{{ config('app.js_css_url') }}/assets/default/js/parts/profile.min.js"></script>

    @if(!empty($user->live_chat_js_code) and !empty(getFeaturesSettings('show_live_chat_widget')))
        <script>
            (function () {
                "use strict"

                {!! $user->live_chat_js_code !!}
            })(jQuery)
        </script>
    @endif
@endpush
