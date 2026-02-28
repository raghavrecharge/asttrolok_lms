<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp
<head>
    @include(getTemplate().'.includes.metas')
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>

    <link href="{{ config('app.js_css_url') }}/assets/default/css/font.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets2/default/css/app.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/app.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/asttroloknew/assets/design_1/css/parts/theme/headers/header_1.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/toast/jquery.toast.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/app.css">
        <link rel="stylesheet" href="/assets/default/css/app.css">

    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/panel.css">

    @if($isRtl)
        <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/css/rtl-app.css">
    @endif

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeFontsSettings() !!}

        {!! getThemeColorsSettings() !!}

        <style>
    .header-logo {
        max-height: 50px;
    }
    @media (max-width: 768px) {
        .header-logo {
            max-height: 40px;
        }
        .logo-container {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
    }

    /* ── Panel mobile header ── */
    .panel-mobile-topbar {
        display: none;
    }
    @media (max-width: 991px) {
        /* Hide the shared green top-nav and navbar on mobile */
        #panel_app > .shared-header-wrapper {
            display: none !important;
        }
        /* Show the clean mobile header */
        .panel-mobile-topbar {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            padding: 10px 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: fixed !important; /* Make it sticky */
            top: 0;
            left: 0;
            right: 0;
            z-index: 1100; /* Higher than sidebar overlay if needed */
        }
        .panel-mobile-topbar__logo img {
            height: 28px; /* Reduced for better spacing */
            width: auto;
        }
        .panel-mobile-topbar__hamburger {
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Adjust spacing for icons */
        .panel-mobile-topbar__right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Overrides for Currency and Cart in Panel (White Header) */
        .panel-mobile-topbar .js-currency-select .text-white {
            color: #171347 !important;
        }
        .panel-mobile-topbar .js-currency-select .icons {
            stroke: #171347 !important;
        }
        .panel-mobile-topbar #navbarShopingCart svg {
            stroke: #171347 !important;
            color: #171347 !important;
        }
        .panel-mobile-topbar #navbarShopingCart.dropdown-toggle::after {
            border-top-color: #171347 !important;
        }
        .panel-mobile-topbar .badge-circle-primary {
            background-color: var(--primary) !important;
            color: #fff !important;
        }
        
        /* Ensure dropdowns appear below the sticky header */
        .panel-mobile-topbar .dropdown-menu {
            margin-top: 10px !important;
        }

        /* Reduce the top gap on mobile */
        .panel-content {
            padding-top: 70px !important; /* Adjust based on sticky header height */
        }
        #panel_app .container.mt-30 {
            margin-top: 0 !important;
        }

        /* --- Global Mobile UI Enhancements --- */
        
        /* Stat Cards: Stack them on very small screens, or use 2 columns */
        .stat-card {
            padding: 15px !important;
            margin-bottom: 10px;
        }
        .stat-card .stat-icon {
            width: 40px !important;
            height: 40px !important;
            margin-right: 10px !important;
        }
        .stat-card .stat-value {
            font-size: 18px !important;
        }
        .stat-card .stat-label {
            font-size: 11px !important;
        }

        /* Tighten side spacing on mobile */
        .container, .container-fluid {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
        .panel-content {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .content {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
        .row {
            margin-left: -5px !important;
            margin-right: -5px !important;
        }
        .row > [class*="col-"] {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }

        /* Force 2x2 grid for stat cards on mobile */
        @media (max-width: 575px) {
            .stat-card-row > div[class*="col-"] {
                flex: 0 0 50% !important;
                max-width: 50% !important;
                margin-bottom: 15px;
            }
            /* Adjust padding/margins inside stat-card for 2x2 fit */
            .stat-card {
                padding: 10px !important;
            }
            .stat-card .stat-icon {
                width: 32px !important;
                height: 32px !important;
                margin-right: 8px !important;
            }
            .stat-card .stat-icon i, .stat-card .stat-icon svg {
                width: 16px !important;
                height: 16px !important;
            }
            .stat-card .stat-value {
                font-size: 16px !important;
            }
            .stat-card .stat-label {
                font-size: 10px !important;
            }
        }

        /* Tables horizontal scroll prevention */
        .table-responsive {
            border: 0;
            margin-bottom: 0;
        }
        .custom-table th, .custom-table td {
            font-size: 12px !important;
            padding: 10px 8px !important;
        }
        
        /* Section titles */
        .section-title {
            font-size: 18px !important;
        }

        /* Hide Filter sections on mobile */
        .panel-filter-section {
            display: none !important;
        }
    }
</style>

    @if(!empty($generalSettings['preloading']) and $generalSettings['preloading'] == '1')
        @include('admin.includes.preloading')
    @endif

</head>
<body class="@if($isRtl) rtl @endif">

@php
    $isPanel = true;
@endphp

<div id="panel_app">

    {{-- Clean mobile header for panel (visible only on mobile) --}}
    <div class="panel-mobile-topbar">
        <a href="/" class="panel-mobile-topbar__logo">
            <img src="/assets/design_1/img/home_mobile_image/public/asttroloklogo11171-ou4-200h.png" alt="logo">
        </a>
        
        <div class="panel-mobile-topbar__right">
            {{-- Currency Selector --}}
            @include('web.default2.includes.top_nav.currency')

            <button class="panel-mobile-topbar__hamburger" id="panelMobileHamburger" aria-label="Open menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M3 7H21" stroke="#171347" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M3 12H21" stroke="#171347" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M3 17H21" stroke="#171347" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Shared header (visible on desktop, hidden on mobile via CSS above) --}}
    <div class="shared-header-wrapper">
        @include('web.default2.includes.top_nav')
        @include('web.default2.includes.navbar')
    </div>

    {{-- Hook mobile hamburger to panel sidebar --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('panelMobileHamburger');
        var sidebar = document.getElementById('panelSidebar');
        var overlay = document.getElementById('mobileSidebarOverlay');
        if (btn && sidebar) {
            btn.addEventListener('click', function() {
                sidebar.classList.add('mobile-open');
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }
    });
    </script>
       @if($authUser->isUser())
<div class="container mt-30">
    
    <div class="d-flex justify-content-end">
         
         @include(getTemplate(). '.panel.includes.sidebar1')
        @else
        <div class="container-fluid">
    <div class="d-flex justify-content-end">
         @include(getTemplate(). '.panel.includes.sidebar')

            @endif

<div class="panel-content">
        <div class="content">
            @yield('content')
        </div>
        </div>
    </div>

    @include('web.default.includes.advertise_modal.index')
    </div>
</div>

<script   src="{{ config('app.js_css_url') }}/assets/default/js/app.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/moment.min.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script   src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/vendors/toast/jquery.toast.min.js"></script>
<script   type="text/javascript" src="{{ config('app.js_css_url') }}/assets/default/vendors/simplebar/simplebar.min.js"></script>

<script  >
    var deleteAlertTitle = '{{ trans('public.are_you_sure') }}';
    var deleteAlertHint = '{{ trans('public.deleteAlertHint') }}';
    var deleteAlertConfirm = '{{ trans('public.deleteAlertConfirm') }}';
    var deleteAlertCancel = '{{ trans('public.cancel') }}';
    var deleteAlertSuccess = '{{ trans('public.success') }}';
    var deleteAlertFail = '{{ trans('public.fail') }}';
    var deleteAlertFailHint = '{{ trans('public.deleteAlertFailHint') }}';
    var deleteAlertSuccessHint = '{{ trans('public.deleteAlertSuccessHint') }}';
    var forbiddenRequestToastTitleLang = '{{ trans('public.forbidden_request_toast_lang') }}';
    var forbiddenRequestToastMsgLang = '{{ trans('public.forbidden_request_toast_msg_lang') }}';
</script>

@if(session()->has('toast'))
    <script  >
        (function () {
            "use strict";

            $.toast({
                heading: '{{ session()->get('toast')['title'] ?? '' }}',
                text: '{{ session()->get('toast')['msg'] ?? '' }}',
                bgColor: '@if(session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
                textColor: 'white',
                hideAfter: 10000,
                position: 'bottom-right',
                icon: '{{ session()->get('toast')['status'] }}'
            });
        })(jQuery)
    </script>
@endif

@stack('styles_bottom')
@stack('scripts_bottom')

<script   src="{{ config('app.js_css_url') }}/assets/default/js/parts/main.min.js"></script>
<script   src="{{ config('app.js_css_url') }}/assets/default/js/panel/public.min.js"></script>

@stack('scripts_bottom2')

<script  >

    @if(session()->has('registration_package_limited'))
    (function () {
        "use strict";

        handleLimitedAccountModal('{!! session()->get('registration_package_limited') !!}')
    })(jQuery)

    {{ session()->forget('registration_package_limited') }}
    @endif

    {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
</script>

</body>
</html>
