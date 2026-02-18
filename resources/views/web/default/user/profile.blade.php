@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/persian-datepicker/persian-datepicker.min.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/css-stars.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/mobile-instructors-details.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #F4FFF2;
        }
        section.px-10 {
    border-right: none !important;
}
   
.consultant-stats-wrapper {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-top: 20px;
}

.consultant-stat-card {
    flex: 1;
    background: #FFFFFF;
    border-radius: 18px;
     padding: 0px 6px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-shadow: 0 6px 20px rgba(107, 119, 154, 0.12);
}

.stat-icon {
    width: 54px;
    height: 54px;
    border-radius: -0px 0px 20px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

/* light gradient backgrounds similar to screenshot */
.stat-icon-blue {
    background: linear-gradient(180deg, #E4F5FF 0%, #F5FBFF 100%);
}

.stat-icon-pink {
    background: linear-gradient(180deg, #FFE7F0 0%, #FFF5FA 100%);
}

.stat-icon-gold {
    background: linear-gradient(180deg, #FFF0D9 0%, #FFF8EB 100%);
}

.stat-value {
    font-family: "Inter", sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}

.stat-label {
    font-family: "Inter", sans-serif;
    font-size: 12px;
    font-weight: 500;
    color: #6B7280;
}

/* mobile */
@media (max-width: 768px) {
    .consultant-stats-wrapper {
        gap: 8px;
    }
    .consultant-stat-card {
        padding: 12px 6px 10px;
    }
    .stat-value {
        font-size: 18px;
    }
    .stat-label {
        font-size: 11px;
    }
}


        .abcak {
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
.tab-pane {
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}

        /* Tab Navigation Styles */
        .nav-tabs {
            border-bottom: 1px solid #6B779A33;
            background-color: transparent !important;
            padding: 0;
            position: relative;
            border-right: none !important;
            overflow-x: hidden !important;
        }

        .nav-tabs .nav-item {
            margin-bottom: 0;
            margin-right: 30px;
            border-right: none !important;
        }

        .nav-tabs .nav-link {
            border: none !important;
            padding: 12px 5px;
            color: #4A5568;
            transition: all 0.3s ease;
            background-color: transparent;
            font-size: 18px;
            font-weight: 500;
            border-radius: 0;
            position: relative;
        }

        .nav-tabs .nav-link:hover {
            color: #33ba7c;
            background-color: transparent;
        }

        .nav-tabs .nav-link.active {
            color: #33ba7c !important;
            background-color: transparent !important;
            font-weight: 600;
        }

        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #33ba7c;
            border-radius: 0;
            z-index: 1;
        }
        
        /* Remove any unwanted borders and scrollbars */
        .nav-tabs::after,
        .nav-tabs::before {
            display: none !important;
        }

        /* Hide scrollbar for tab container */
        section.px-10 {
            overflow-x: hidden !important;
        }

        .tab-content {
            overflow-x: hidden !important;
            margin-top: 0px !important;
        }
        .consultant-stat-card {
    display: flex;
    flex-direction: column;
    align-items: center; 
}

.stat-icon {
    margin-top: -8px;
}
.stat-icon-gold {
    margin-top: -12px; 
}
.stat-icon-pink {
       margin-top: -14px;
}
.stat-icon-blue{
    margin-top: -14px;
}

        /* Tab Content */
        .tab-content {
            margin-top: 30px;
            background-color: #F4FFF2;
        }

        .tab-pane {
            display: none;
            animation: fadeIn 0.3s ease-in;
            padding-left: 0px !important;
            padding-right: 0px !important;

        }


        .tab-pane.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

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

        .tab-pane li {
            position: relative;
            padding-left: 15px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .nav-tabs .nav-item {
                margin-right: 10px;
            }
            
            .nav-tabs .nav-link {
                white-space: nowrap;
                padding: 10px 15px;
                font-size: 14px;
            }
        }
    </style>
    
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

<section class="container mt-0 mb-p bg-t" style="background-color: #F4FFF2 !important;">
   <div class="d-flex justify-content-between align-items-center ">
    <div class="col-md-6 mt-10">
        <a href="/consult-with-astrologers" class="mt-10 back-button abcak" style="border-radius: 5px !important;width:5px !important">
            <img loading="lazy" src="/assets/default/img/profile/left-arrow.svg" alt="left-arrow" class="verify-img1 mr-5">
        </a>
    </div>
    <!-- <div class="ft-right ">
        <div class="redius-20 stars-card d-flex align-items-end ft-right">
            <img loading="lazy" src="/assets/design_1/img/instructors/public2/icon1172-mkti.svg" alt="3-dot" class="verify-img1 mr-5">
        </div>
    </div> -->
</div>

</section>

<section class="mt-">
    <div class="rounded-lg1 px-25 py-20 px-lg-50 py-lg-35 position-relative bg-white" style="background: #F4FFF2 !important;">
        <div class="profile-info-box d-flex align-items-start justify-content-between">
            <div class="user-details d-flex align-items-center">
                <div class="user-profile-avatar bg-gray200" style="border-radius: 50% !important;">
                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $user->getAvatar(190) }}" style="border-radius: 50%;" class="img-cover" alt="{{ $user['full_name'] }}"/>
                </div>
                <div class="mx-auto text-center" style="width: 100%;">
                    <h1 class="user-title font-weight-bold text-dark-blue">
                        {{ $user["full_name"] }} 
                        @if($user->offline)
                            <span class="user-circle-badge unavailable d-flex align-items-center justify-content-center">
                                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                            </span>
                        @elseif($user->verified)
                        @endif
                    </h1>
                    <span class="text-gray">{{ $user["headline"] }}</span>

                    <div class="font-13s text-gray text-center align-items-center justify-content-center d-flex flex-column">
                        @if(!empty($user["bio"]))
                            <pre class="mt-10 font-13s text-dark-blue" style="font-family: 'Inter', sans-serif !important;">{{$user["bio"]}}</pre>
                        @endif
                        @if(!empty($meeting->amount))
                            <div class="mt-5">
                                <span class="price text-primary1 font-weight-500">{{ handlePrice($meeting->amount/30) }}</span>
                                <span class="text-dark-blue" style="font-size: small!important;"> / min</span>
                            </div>
                        @endif
                    </div>

                    <div class="w-100 mt-10 d-none align-items-center justify-content-center justify-content-lg-start">
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

    <div class="consultant-stats-wrapper">
    <div class="consultant-stat-card">
        <div class="stat-icon stat-icon-blue">
            <img src="/assets/design_1/img/instructors/public2/icon1076-zpqb.svg" alt="Consultations">
        </div>
        <div class="stat-value">1000+</div>
        <div class="stat-label">Consultations</div>
    </div>

    <div class="consultant-stat-card">
        <div class="stat-icon stat-icon-pink">
            <img src="/assets/design_1/img/instructors/public2/icon1076-gtwi.svg" alt="Experience">
        </div>
        <div class="stat-value">10 Yrs</div>
        <div class="stat-label">Experience</div>
    </div>

    <div class="consultant-stat-card">
        <div class="stat-icon stat-icon-gold">
            <img src="/assets/design_1/img/instructors/public2/icon1076-sku.svg" alt="Ratings">
        </div>
        <div class="stat-value">4.5</div>
        <div class="stat-label">Ratings</div>
    </div>
</div>

</section>


<div class="uderline"></div>

<div class="container mt-0">
    <section class="px-10 position-relative">
        {{-- Tab Navigation --}}
       <ul class="nav nav-tabs d-flex align-items-center px-20 px-lg-50 pb-15 mt-0"
    id="tabs-tab"
    role="tablist"
    style="margin-left: -20px;">
            <li class="nav-item mr-20 mr-lg-50 mt-30">
                <a class="nav-link position-relative text-dark-blue font-weight-500 font-16 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'active' : '' }}" 
                   id="about-tab" 
                   href="#about" 
                   role="tab" 
                   aria-controls="about" 
                   aria-selected="{{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'true' : 'false' }}">
                    {{ trans('site.about') }}
                </a>
            </li>

            <li class="nav-item mr-20 mr-lg-50 mt-30">
                <a class="nav-link position-relative text-dark-blue font-weight-500 font-16 {{ (request()->get('tab') == 'appointments') ? 'active' : '' }}" 
                   id="appointments-tab" 
                   href="#appointments" 
                   role="tab" 
                   aria-controls="appointments" 
                   aria-selected="{{ (request()->get('tab') == 'appointments') ? 'true' : 'false' }}">
                    Book a Consultation
                </a>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content" id="nav-tabContent">
            {{-- About Tab Pane --}}
            <div class="tab-pane fade px-20 px-lg-50 {{ (empty(request()->get('tab')) or request()->get('tab') == 'about') ? 'show active' : '' }}" 
                 id="about" 
                 role="tabpanel" 
                 aria-labelledby="about-tab">
                @include('web.default.user.profile_tabs.about')
            </div>

            {{-- Appointments Tab Pane --}}
            <div class="tab-pane fade px-20 px-lg-50 {{ (request()->get('tab') == 'appointments') ? 'show active' : '' }}" 
                 id="appointments" 
                 role="tabpanel" 
                 aria-labelledby="appointments-tab">
                @include('web.default.user.profile_tabs.appointments')
            </div>
        </div>
    </section>
</div>

@include('web.default.user.send_message_modal')

@endsection

@push('scripts_bottom')
    <script>
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

    {{-- Tab Switching JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all tab links
            const tabLinks = document.querySelectorAll('#tabs-tab .nav-link');
            const tabPanes = document.querySelectorAll('.tab-content .tab-pane');
            
            // Tab switching function
            function switchTab(targetId) {
                // Remove active from all
                tabLinks.forEach(tab => {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                });
                tabPanes.forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Add active to target
                const targetLink = document.querySelector(`#tabs-tab a[href="${targetId}"]`);
                const targetPane = document.querySelector(targetId);
                
                if (targetLink && targetPane) {
                    targetLink.classList.add('active');
                    targetLink.setAttribute('aria-selected', 'true');
                    targetPane.classList.add('show', 'active');
                    
                    // Scroll to tabs
                    targetLink.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }
            
            // Add click event to all tabs
            tabLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    
                    switchTab(targetId);
                    
                    // Update URL without reload
                    const url = new URL(window.location);
                    url.searchParams.set('tab', targetId.replace('#', ''));
                    window.history.pushState({}, '', url);
                });
            });
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const activeTab = urlParams.get('tab') || 'about';
                switchTab('#' + activeTab);
            });
            
            // Check URL params on page load
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');
            
            if (activeTab && activeTab === 'appointments') {
                switchTab('#appointments');
            }
        });
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