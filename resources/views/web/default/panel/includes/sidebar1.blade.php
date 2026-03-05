@php
    $getPanelSidebarSettings = getPanelSidebarSettings();
    $isConsultant = ($authUser->isTeacher() && !empty($authUser->consultant) && $authUser->consultant == 1);
@endphp
{{-- xs-panel-nav removed: main site header shown on all screens via panel_layout.blade.php --}}

<style>
    .panel-sidebar {
        position: relative;
        top: 35px;
        left: 0;
        bottom: 0;
        width: 254px;
        height: 100%;
        box-shadow: 18px 0 35px 0 rgba(0, 0, 0, 0.02);
        background-color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    @media (min-width: 1200px) {
        .container-xl, .container-lg, .container-md, .container-sm, .container {
            max-width: 1265px !important;
        }
    }
    .pratul.sticky {
        position: fixed;
        height: 100%;
        border-bottom: 1px solid #ececec;
        background-color: #ffffff;
    }
    .navbar.sticky {
        position: fixed;
        top: 0;
        width: 100%;
        border-bottom: 1px solid #ececec;
    }
    .panel-sidebar .sidebar-menu {
        height: calc(100% - 60px) !important;
        overflow: auto;
        padding-bottom: 35px;
    }

    /* ── Mobile Sidebar Drawer (overrides panel.css) ── */
    @media (max-width: 991px) {
        .panel-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: -280px !important;
            width: 280px !important;
            height: 100vh !important;
            z-index: 9999 !important;
            background: #ffffff !important;
            box-shadow: 4px 0 24px rgba(0,0,0,0.15) !important;
            transition: left 0.32s cubic-bezier(0.4,0,0.2,1) !important;
            overflow-y: auto !important;
            padding: 20px 15px 0 !important;
        }
        .panel-sidebar.mobile-open {
            left: 0 !important;
        }
        .pratul.sticky {
            position: relative !important;
            height: auto !important;
        }
        /* Hide the X close button from panel.css */
        .panel-sidebar .panel-sidebar-close {
            display: none !important;
        }
        /* Overlay backdrop */
        .mobile-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 9998;
            backdrop-filter: blur(2px);
        }
        .mobile-sidebar-overlay.active {
            display: block;
        }
    }


</style>

{{-- Mobile sidebar overlay backdrop --}}
<div class="mobile-sidebar-overlay" id="mobileSidebarOverlay"></div>

<div class="panel-sidebar" id="panelSidebar">
     <div class="pratul sticky pt-5 pb-25 pl-25">
    <button class="btn-transparent panel-sidebar-close sidebarNavToggle">
        <i data-feather="x" width="24" height="24"></i>
    </button>

    <div class="d-flex sidebar-user-stats" style="min-width: 215px;">
        @if($authUser->isUser() || $isConsultant)

         <div class="sidebar-user-stat-item d-flex flex-column">

        </div>

         @else

          <div class="sidebar-user-stat-item d-flex flex-column">
            <strong class="text-center">{{ $authUser->webinars()->count() }}</strong>
            <span class="font-12">{{ trans('panel.classes') }}</span>
        </div>

          @endif

        @if(!$authUser->isUser() && !$isConsultant)
            <div class="border-left mx-30"></div>
        @endif

        @if($authUser->isUser() || $isConsultant)
            <div class="sidebar-user-stat-item d-flex flex-column">

            </div>
        @else
            <div class="sidebar-user-stat-item d-flex flex-column">
                <strong class="text-center">{{ $authUser->followers()->count() }}</strong>
                <span class="font-12">{{ trans('panel.followers') }}</span>
            </div>
        @endif
    </div>

    <ul id="panel-sidebar-scroll" class="sidebar-menu  pt-10 @if(!empty($authUser->userGroup)) has-user-group @endif @if(empty($getPanelSidebarSettings) or empty($getPanelSidebarSettings['background'])) without-bottom-image @endif" @if((!empty($isRtl) and $isRtl)) data-simplebar-direction="rtl" @endif>

        <li class="sidenav-item {{ (request()->is('panel')) ? 'sidenav-item-active' : '' }}">
            <a href="/panel" class="d-flex align-items-center">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.dashboard')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.dashboard') }}</span>
            </a>
        </li>

        @if($authUser->isOrganization() && !$isConsultant)
            <li class="sidenav-item {{ (request()->is('panel/instructors') or request()->is('panel/manage/instructors*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#instructorsCollapse" role="button" aria-expanded="false" aria-controls="instructorsCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.teachers')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('public.instructors') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/instructors') or request()->is('panel/manage/instructors*')) ? 'show' : '' }}" id="instructorsCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ (request()->is('panel/instructors/new')) ? 'active' : '' }}">
                            <a href="/panel/manage/instructors/new">{{ trans('public.new') }}</a>
                        </li>
                        <li class="mt-5 {{ (request()->is('panel/manage/instructors')) ? 'active' : '' }}">
                            <a href="/panel/manage/instructors">{{ trans('public.list') }}</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="sidenav-item {{ (request()->is('panel/students') or request()->is('panel/manage/students*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#studentsCollapse" role="button" aria-expanded="false" aria-controls="studentsCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.students')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('quiz.students') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/students') or request()->is('panel/manage/students*')) ? 'show' : '' }}" id="studentsCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ (request()->is('panel/manage/students/new')) ? 'active' : '' }}">
                            <a href="/panel/manage/students/new">{{ trans('public.new') }}</a>
                        </li>
                        <li class="mt-5 {{ (request()->is('panel/manage/students')) ? 'active' : '' }}">
                            <a href="/panel/manage/students">{{ trans('public.list') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        <li class="sidenav-item {{ (request()->is('panel/webinars') or request()->is('panel/webinars/*') or request()->is('panel/upe/purchases*') or request()->is('panel/upe/installments*')) ? 'sidenav-item-active' : '' }}">
            <a class="d-flex align-items-center" data-toggle="collapse" href="#webinarCollapse" role="button" aria-expanded="false" aria-controls="webinarCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.webinars')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.webinars') }}</span>
            </a>

            <div class="collapse {{ (request()->is('panel/webinars') or request()->is('panel/webinars/*') or request()->is('panel/upe/purchases*') or request()->is('panel/upe/installments*')) ? 'show' : '' }}" id="webinarCollapse">
                <ul class="sidenav-item-collapse">
                    @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                        <li class="mt-5 {{ (request()->is('panel/webinars/new')) ? 'active' : '' }}">
                            <a href="/panel/webinars/new">{{ trans('public.new') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/webinars')) ? 'active' : '' }}">
                            <a href="/panel/webinars">{{ trans('panel.my_classes') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/webinars/invitations')) ? 'active' : '' }}">
                            <a href="/panel/webinars/invitations">{{ trans('panel.invited_classes') }}</a>
                        </li>
                    @endif

                    @if(!empty($authUser->organ_id))
                        <li class="mt-5 {{ (request()->is('panel/webinars/organization_classes')) ? 'active' : '' }}">
                            <a href="/panel/webinars/organization_classes">{{ trans('panel.organization_classes') }}</a>
                        </li>
                    @endif

                    @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                        <li class="mt-5 {{ (request()->is('panel/webinars/comments')) ? 'active' : '' }}">
                            <a href="/panel/webinars/comments">{{ trans('panel.my_class_comments') }}</a>
                        </li>
                    @endif

                   <li class="mt-5 {{ (request()->is('panel/upe/purchases*')) ? 'active font-weight-bold' : '' }}">
                        <a href="/panel/upe/purchases">My Purchases</a>
                    </li>
                    <li class="mt-5 {{ (request()->is('panel/webinars/purchases/refunded')) ? 'active font-weight-bold' : '' }}">
                        <a href="/panel/webinars/purchases/refunded">Refunded Courses</a>
                    </li>
                    <li class="mt-5 {{ (request()->is('panel/upe/installments*')) ? 'active' : '' }}">
                        <a href="/panel/upe/installments">EMI Plans</a>
                    </li>
                </ul>
            </div>
        </li>

        @if(!empty(getFeaturesSettings('upcoming_courses_status')))
            <li class="sidenav-item {{ (request()->is('panel/upcoming_courses') or request()->is('panel/upcoming_courses/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#upcomingCoursesCollapse" role="button" aria-expanded="false" aria-controls="upcomingCoursesCollapse">
                <span class="sidenav-item-icon mr-10">
                    <i data-feather="film" class="img-cover"></i>
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.upcoming_courses') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/upcoming_courses') or request()->is('panel/upcoming_courses/*')) ? 'show' : '' }}" id="upcomingCoursesCollapse">
                    <ul class="sidenav-item-collapse">
                        @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                            <li class="mt-5 {{ (request()->is('panel/upcoming_courses/new')) ? 'active' : '' }}">
                                <a href="/panel/upcoming_courses/new">{{ trans('public.new') }}</a>
                            </li>

                            <li class="mt-5 {{ (request()->is('panel/upcoming_courses')) ? 'active' : '' }}">
                                <a href="/panel/upcoming_courses">{{ trans('update.my_upcoming_courses') }}</a>
                            </li>
                        @endif

                        <li class="mt-5 {{ (request()->is('panel/upcoming_courses/followings')) ? 'active' : '' }}">
                            <a href="/panel/upcoming_courses/followings">{{ trans('update.following_courses') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if(($authUser->isOrganization() or $authUser->isTeacher()) && !$isConsultant)
            <li class="sidenav-item {{ (request()->is('panel/bundles') or request()->is('panel/bundles/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#bundlesCollapse" role="button" aria-expanded="false" aria-controls="bundlesCollapse">
                <span class="sidenav-item-icon assign-fill mr-10">
                    @include('web.default.panel.includes.sidebar_icons.bundles')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.bundles') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/bundles') or request()->is('panel/bundles/*')) ? 'show' : '' }}" id="bundlesCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ (request()->is('panel/bundles/new')) ? 'active' : '' }}">
                            <a href="/panel/bundles/new">{{ trans('public.new') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/bundles')) ? 'active' : '' }}">
                            <a href="/panel/bundles">{{ trans('update.my_bundles') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if(getFeaturesSettings('webinar_assignment_status'))
            <li class="sidenav-item {{ (request()->is('panel/assignments') or request()->is('panel/assignments/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#assignmentCollapse" role="button" aria-expanded="false" aria-controls="assignmentCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.assignments')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.assignments') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/assignments') or request()->is('panel/assignments/*')) ? 'show' : '' }}" id="assignmentCollapse">
                    <ul class="sidenav-item-collapse">

                        <li class="mt-5 {{ (request()->is('panel/assignments/my-assignments')) ? 'active' : '' }}">
                            <a href="/panel/assignments/my-assignments">{{ trans('update.my_assignments') }}</a>
                        </li>

                        @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                            <li class="mt-5 {{ (request()->is('panel/assignments/my-courses-assignments')) ? 'active' : '' }}">
                                <a href="/panel/assignments/my-courses-assignments">{{ trans('update.students_assignments') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

        <li class="sidenav-item {{ (request()->is('panel/meetings') or request()->is('panel/meetings/*')) ? 'sidenav-item-active' : '' }}">
            <a class="d-flex align-items-center" data-toggle="collapse" href="#meetingCollapse" role="button" aria-expanded="false" aria-controls="meetingCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.requests')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.meetings') }}</span>
            </a>

            <div class="collapse {{ (request()->is('panel/meetings') or request()->is('panel/meetings/*')) ? 'show' : '' }}" id="meetingCollapse">
                <ul class="sidenav-item-collapse">

                    <li class="mt-5 {{ (request()->is('panel/meetings/reservation')) ? 'active' : '' }}">
                        <a href="/panel/meetings/reservation">{{ trans('public.my_reservation') }}</a>
                    </li>

                    @if(($authUser->isOrganization() || $isConsultant))
                        <li class="mt-5 {{ (request()->is('panel/meetings/requests')) ? 'active' : '' }}">
                            <a href="/panel/meetings/requests">{{ trans('panel.requests') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/meetings/settings')) ? 'active' : '' }}">
                            <a href="/panel/meetings/settings">{{ trans('panel.settings') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>

        <li class="sidenav-item {{ (request()->is('panel/quizzes') or request()->is('panel/quizzes/*')) ? 'sidenav-item-active' : '' }}">
            <a class="d-flex align-items-center" data-toggle="collapse" href="#quizzesCollapse" role="button" aria-expanded="false" aria-controls="quizzesCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.quizzes')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.quizzes') }}</span>
            </a>

            <div class="collapse {{ (request()->is('panel/quizzes') or request()->is('panel/quizzes/*')) ? 'show' : '' }}" id="quizzesCollapse">
                <ul class="sidenav-item-collapse">
                    @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                        <li class="mt-5 {{ (request()->is('panel/quizzes/new')) ? 'active' : '' }}">
                            <a href="/panel/quizzes/new">{{ trans('quiz.new_quiz') }}</a>
                        </li>
                        <li class="mt-5 {{ (request()->is('panel/quizzes')) ? 'active' : '' }}">
                            <a href="/panel/quizzes">{{ trans('public.list') }}</a>
                        </li>
                        <li class="mt-5 {{ (request()->is('panel/quizzes/results')) ? 'active' : '' }}">
                            <a href="/panel/quizzes/results">{{ trans('public.results') }}</a>
                        </li>
                    @endif

                    <li class="mt-5 {{ (request()->is('panel/quizzes/my-results')) ? 'active' : '' }}">
                        <a href="/panel/quizzes/my-results">{{ trans('public.my_results') }}</a>
                    </li>

                    <li class="mt-5 {{ (request()->is('panel/quizzes/opens')) ? 'active' : '' }}">
                        <a href="/panel/quizzes/opens">{{ trans('public.not_participated') }}</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="sidenav-item {{ (request()->is('panel/certificates') or request()->is('panel/certificates/*')) ? 'sidenav-item-active' : '' }}" style="display:none;">
            <a class="d-flex align-items-center" data-toggle="collapse" href="#certificatesCollapse" role="button" aria-expanded="false" aria-controls="certificatesCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.certificate')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.certificates') }}</span>
            </a>

            <div class="collapse {{ (request()->is('panel/certificates') or request()->is('panel/certificates/*')) ? 'show' : '' }}" id="certificatesCollapse">
                <ul class="sidenav-item-collapse">
                    @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                        <li class="mt-5 {{ (request()->is('panel/certificates')) ? 'active' : '' }}">
                            <a href="/panel/certificates">{{ trans('public.list') }}</a>
                        </li>
                    @endif

                    <li class="mt-5 {{ (request()->is('panel/certificates/achievements')) ? 'active' : '' }}">
                        <a href="/panel/certificates/achievements">{{ trans('quiz.achievements') }}</a>
                    </li>

                    <li class="mt-5">
                        <a href="/certificate_validation">{{ trans('site.certificate_validation') }}</a>
                    </li>

                    <li class="mt-5 {{ (request()->is('panel/certificates/webinars')) ? 'active' : '' }}">
                        <a href="/panel/certificates/webinars">{{ trans('update.course_certificates') }}</a>
                    </li>

                </ul>
            </div>
        </li>

        @if($authUser->checkCanAccessToStore())
            <li class="sidenav-item {{ (request()->is('panel/store') or request()->is('panel/store/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#storeCollapse" role="button" aria-expanded="false" aria-controls="storeCollapse">
                <span class="sidenav-item-icon assign-fill mr-10">
                    @include('web.default.panel.includes.sidebar_icons.store')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.store') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/store') or request()->is('panel/store/*')) ? 'show' : '' }}" id="storeCollapse">
                    <ul class="sidenav-item-collapse">
                        @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                            <li class="mt-5 {{ (request()->is('panel/store/products/new')) ? 'active' : '' }}">
                                <a href="/panel/store/products/new">{{ trans('update.new_product') }}</a>
                            </li>

                            <li class="mt-5 {{ (request()->is('panel/store/products')) ? 'active' : '' }}">
                                <a href="/panel/store/products">{{ trans('update.products') }}</a>
                            </li>

                            @php
                                $sellerProductOrderWaitingDeliveryCount = $authUser->getWaitingDeliveryProductOrdersCount();
                            @endphp

                            <li class="mt-5 {{ (request()->is('panel/store/sales')) ? 'active' : '' }}">
                                <a href="/panel/store/sales">{{ trans('panel.sales') }}</a>

                                @if($sellerProductOrderWaitingDeliveryCount > 0)
                                    <span class="d-inline-flex align-items-center justify-content-center font-weight-500 ml-15 panel-sidebar-store-sales-circle-badge">{{ $sellerProductOrderWaitingDeliveryCount }}</span>
                                @endif
                            </li>

                        @endif

                        <li class="mt-5 {{ (request()->is('panel/store/purchases')) ? 'active' : '' }}">
                            <a href="/panel/store/purchases">My Products</a>
                        </li>

                        @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                            <li class="mt-5 {{ (request()->is('panel/store/products/comments')) ? 'active' : '' }}">
                                <a href="/panel/store/products/comments">{{ trans('update.product_comments') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

        <li class="sidenav-item {{ (request()->is('panel/financial') or request()->is('panel/financial/*')) ? 'sidenav-item-active' : '' }}">
            <a class="d-flex align-items-center" data-toggle="collapse" href="#financialCollapse" role="button" aria-expanded="false" aria-controls="financialCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.financial')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.financial') }}</span>
            </a>

            <div class="collapse {{ (request()->is('panel/financial') or request()->is('panel/financial/*')) ? 'show' : '' }}" id="financialCollapse">
                <ul class="sidenav-item-collapse">

                    @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
                        <li class="mt-5 {{ (request()->is('panel/financial/sales')) ? 'active' : '' }}">
                            <a href="/panel/financial/sales">{{ trans('financial.sales_report') }}</a>
                        </li>
                    @endif

                    <li class="mt-5 {{ (request()->is('panel/financial/summary')) ? 'active' : '' }}">
                        <a href="/panel/financial/summary">{{ trans('financial.financial_summary') }}</a>
                    </li>

                    @if((($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant) and getRegistrationPackagesGeneralSettings('status'))
                        <li class="mt-5 {{ (request()->is('panel/financial/registration-packages')) ? 'active' : '' }}">
                            <a href="{{ route('panelRegistrationPackagesLists') }}">{{ trans('update.registration_packages') }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>

        <li class="sidenav-item {{ (request()->is('panel/wallet*')) ? 'sidenav-item-active' : '' }}">
            <a href="/panel/wallet" class="d-flex align-items-center">
                <span class="sidenav-item-icon mr-10">
                    <i data-feather="credit-card" stroke="#1f3b64" stroke-width="1.5" width="24" height="24" class="webinar-icon"></i>
                </span>
                <span class="font-14 text-dark-blue font-weight-500">My Wallet</span>
            </a>
        </li>

        <li class="sidenav-item {{ (request()->is('panel/support') or request()->is('panel/support/*') or request()->is('panel/upe/requests*')) ? 'sidenav-item-active' : '' }}">
            <a class="d-flex align-items-center" data-toggle="collapse" href="#supportCollapse" role="button" aria-expanded="false" aria-controls="supportCollapse">
                <span class="sidenav-item-icon assign-fill mr-10">
                    @include('web.default.panel.includes.sidebar_icons.support')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.support') }}</span>
            </a>

            <div class="collapse {{ (request()->is('panel/support') or request()->is('panel/support/*') or request()->is('panel/upe/requests*')) ? 'show' : '' }}" id="supportCollapse">
                <ul class="sidenav-item-collapse">
                    <!-- <li class="mt-5 {{ (request()->is('panel/support/new')) ? 'active' : '' }}">
                        <a href="/panel/support/new">{{ trans('public.new') }}</a>
                    </li> -->
                    <li class="mt-5 {{ (request()->is('panel/support/newsuportforasttrolok/new-suport')) ? 'active' : '' }}">
                        <a href="/panel/support/newsuportforasttrolok/new-suport">Create Support System</a>
                    </li>
                     <li class="mt-5 {{ (request()->is('panel/support/newsuportforasttrolok')) ? 'active' : '' }}">
                        <a href="/panel/support/newsuportforasttrolok">Support List</a>
                    </li>
                     <!-- <li class="mt-5 {{ (request()->is('panel/upe/requests*')) ? 'active' : '' }}">
                        <a href="/panel/upe/requests">My Requests</a>
                    </li> -->
                </ul>
            </div>
        </li>

        @if((!$authUser->isUser() && !$isConsultant) or (!empty($referralSettings) and $referralSettings['status'] and $authUser->affiliate) or (!empty(getRegistrationBonusSettings('status')) and $authUser->enable_registration_bonus))
            <li class="sidenav-item {{ (request()->is('panel/marketing') or request()->is('panel/marketing/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#marketingCollapse" role="button" aria-expanded="false" aria-controls="marketingCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.marketing')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.marketing') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/marketing') or request()->is('panel/marketing/*')) ? 'show' : '' }}" id="marketingCollapse">
                    <ul class="sidenav-item-collapse">
                        @if(!$authUser->isUser() && !$isConsultant)
                            <li class="mt-5 {{ (request()->is('panel/marketing/special_offers')) ? 'active' : '' }}">
                                <a href="/panel/marketing/special_offers">{{ trans('panel.discounts') }}</a>
                            </li>
                            <li class="mt-5 {{ (request()->is('panel/marketing/promotions')) ? 'active' : '' }}">
                                <a href="/panel/marketing/promotions">{{ trans('panel.promotions') }}</a>
                            </li>
                        @endif

                        @if(!empty($referralSettings) and $referralSettings['status'] and $authUser->affiliate)
                            <li class="mt-5 {{ (request()->is('panel/marketing/affiliates')) ? 'active' : '' }}">
                                <a href="/panel/marketing/affiliates">{{ trans('panel.affiliates') }}</a>
                            </li>
                        @endif

                        @if(!empty(getRegistrationBonusSettings('status')) and $authUser->enable_registration_bonus)
                            <li class="mt-5 {{ (request()->is('panel/marketing/registration_bonus')) ? 'active' : '' }}">
                                <a href="/panel/marketing/registration_bonus">{{ trans('update.registration_bonus') }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

        @if(getFeaturesSettings('forums_status'))
            <li class="sidenav-item {{ (request()->is('panel/forums') or request()->is('panel/forums/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#forumsCollapse" role="button" aria-expanded="false" aria-controls="forumsCollapse">
                <span class="sidenav-item-icon assign-fill mr-10">
                    @include('web.default.panel.includes.sidebar_icons.forums')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.forums') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/forums') or request()->is('panel/forums/*')) ? 'show' : '' }}" id="forumsCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ (request()->is('/forums/create-topic')) ? 'active' : '' }}">
                            <a href="/forums/create-topic">{{ trans('update.new_topic') }}</a>
                        </li>
                        <li class="mt-5 {{ (request()->is('panel/forums/topics')) ? 'active' : '' }}">
                            <a href="/panel/forums/topics">{{ trans('update.my_topics') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/forums/posts')) ? 'active' : '' }}">
                            <a href="/panel/forums/posts">{{ trans('update.my_posts') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/forums/bookmarks')) ? 'active' : '' }}">
                            <a href="/panel/forums/bookmarks">{{ trans('update.bookmarks') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if($authUser->isTeacher() && !$isConsultant)
            <li class="sidenav-item {{ (request()->is('panel/blog') or request()->is('panel/blog/*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#blogCollapse" role="button" aria-expanded="false" aria-controls="blogCollapse">
                <span class="sidenav-item-icon assign-fill mr-10">
                    @include('web.default.panel.includes.sidebar_icons.blog')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.articles') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/blog') or request()->is('panel/blog/*')) ? 'show' : '' }}" id="blogCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ (request()->is('panel/blog/posts/new')) ? 'active' : '' }}">
                            <a href="/panel/blog/posts/new">{{ trans('update.new_article') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/blog/posts')) ? 'active' : '' }}">
                            <a href="/panel/blog/posts">{{ trans('update.my_articles') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/blog/comments')) ? 'active' : '' }}">
                            <a href="/panel/blog/comments">{{ trans('panel.comments') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @if(($authUser->isOrganization() || $authUser->isTeacher()) && !$isConsultant)
            <li class="sidenav-item {{ (request()->is('panel/noticeboard*') or request()->is('panel/course-noticeboard*')) ? 'sidenav-item-active' : '' }}">
                <a class="d-flex align-items-center" data-toggle="collapse" href="#noticeboardCollapse" role="button" aria-expanded="false" aria-controls="noticeboardCollapse">
                <span class="sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.noticeboard')
                </span>

                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.noticeboard') }}</span>
                </a>

                <div class="collapse {{ (request()->is('panel/noticeboard*') or request()->is('panel/course-noticeboard*')) ? 'show' : '' }}" id="noticeboardCollapse">
                    <ul class="sidenav-item-collapse">
                        <li class="mt-5 {{ (request()->is('panel/noticeboard')) ? 'active' : '' }}">
                            <a href="/panel/noticeboard">{{ trans('public.history') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/noticeboard/new')) ? 'active' : '' }}">
                            <a href="/panel/noticeboard/new">{{ trans('public.new') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/course-noticeboard')) ? 'active' : '' }}">
                            <a href="/panel/course-noticeboard">{{ trans('update.course_notices') }}</a>
                        </li>

                        <li class="mt-5 {{ (request()->is('panel/course-noticeboard/new')) ? 'active' : '' }}">
                            <a href="/panel/course-noticeboard/new">{{ trans('update.new_course_notices') }}</a>
                        </li>
                    </ul>
                </div>
            </li>
        @endif

        @php
            $rewardSetting = getRewardsSettings();
        @endphp

        @if(!empty($rewardSetting) and $rewardSetting['status'] == '1')
            <li class="sidenav-item {{ (request()->is('panel/rewards')) ? 'sidenav-item-active' : '' }}">
                <a href="/panel/rewards" class="d-flex align-items-center">
                <span class="sidenav-item-icon assign-strock mr-10">
                    @include('web.default.panel.includes.sidebar_icons.rewards')
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('update.rewards') }}</span>
                </a>
            </li>
        @endif

        <li class="sidenav-item {{ (request()->is('panel/notifications')) ? 'sidenav-item-active' : '' }}">
            <a href="/panel/notifications" class="d-flex align-items-center">
            <span class="sidenav-notification-icon sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.notifications')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.notifications') }}</span>
            </a>
        </li>

        <li class="sidenav-item {{ (request()->is('panel/setting')) ? 'sidenav-item-active' : '' }}">
            <a href="/panel/setting" class="d-flex align-items-center">
                <span class="sidenav-setting-icon sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.setting')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.settings') }}</span>
            </a>
        </li>

        @if($authUser->isTeacher() or $authUser->isOrganization())
            <li class="sidenav-item ">
                <a href="{{ $authUser->getProfileUrl() }}" class="d-flex align-items-center">
                <span class="sidenav-item-icon assign-strock mr-10">
                    <i data-feather="user" stroke="#1f3b64" stroke-width="1.5" width="24" height="24" class="mr-10 webinar-icon"></i>
                </span>
                    <span class="font-14 text-dark-blue font-weight-500">{{ trans('public.my_profile') }}</span>
                </a>
            </li>
        @endif

        <li class="sidenav-item">
            <a href="/logout" class="d-flex align-items-center">
                <span class="sidenav-logout-icon sidenav-item-icon mr-10">
                    @include('web.default.panel.includes.sidebar_icons.logout')
                </span>
                <span class="font-14 text-dark-blue font-weight-500">{{ trans('panel.log_out') }}</span>
            </a>
        </li>
        <li class="sidenav-item">

        </li>
    </ul>

    </div>
</div>

<script>
(function() {
    var sidebar   = document.getElementById('panelSidebar');
    var overlay   = document.getElementById('mobileSidebarOverlay');
    var toggleBtn = document.getElementById('navHamburgerBtn');
    var closeBtns = document.querySelectorAll('.sidebarNavToggle');

    function openSidebar() {
        if (sidebar)  sidebar.classList.add('mobile-open');
        if (overlay)  overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar)  sidebar.classList.remove('mobile-open');
        if (overlay)  overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', openSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    closeBtns.forEach(function(btn) {
        btn.addEventListener('click', closeSidebar);
    });

    // Close on link click inside sidebar (mobile)
    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var href = link.getAttribute('href');
                var isCollapse = link.getAttribute('data-toggle') === 'collapse';
                
                // If it's a submenu toggle or an anchor, don't close the sidebar
                if (window.innerWidth < 992 && !isCollapse && href && !href.startsWith('#')) {
                    closeSidebar();
                }
            });
        });
    }
})();
</script>
