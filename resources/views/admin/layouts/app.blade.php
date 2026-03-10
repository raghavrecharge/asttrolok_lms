<html lang="{{ app()->getLocale() }}">
@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];

    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $pageTitle ?? '' }} </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- General CSS File -->
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"/>
    <link rel="stylesheet" href="/assets/default/vendors/toast/jquery.toast.min.css">

    @stack('libraries_top')

    <link rel="stylesheet" href="/assets/admin/css/style.css">
    <link rel="stylesheet" href="/assets/admin/css/custom.css">
    <link rel="stylesheet" href="/assets/admin/css/components.css">
    @if($isRtl)
        <link rel="stylesheet" href="/assets/admin/css/rtl.css">
    @endif
    <link rel="stylesheet" href="/assets/admin/vendor/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">

    @stack('styles_top')
    @stack('scripts_top')

    <style>
        {!! !empty(getCustomCssAndJs('css')) ? getCustomCssAndJs('css') : '' !!}

        {!! getThemeColorsSettings(true) !!}

        /* --- New Dashboard Aesthetic --- */
        body { background: #f8fafc !important; font-family: 'Inter', sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto; }
        .main-content { background: #f8fafc !important; padding-top: 80px; padding-left: 280px; transition: all 0.3s; }
        .main-sidebar { background: #fff !important; border-right: 1px solid #f1f5f9; box-shadow: none !important; width: 260px !important; }
        .sidebar-menu li.active > a { 
            background: #f0fdf4 !important; /* light green */
            color: #16a34a !important; /* green text */
            border-radius: 12px; 
            margin: 0 10px;
            font-weight: 700;
        }
        .sidebar-menu li.active > a i { color: #16a34a !important; }
        .sidebar-menu li a { color: #475569; font-weight: 600; font-size: 0.95rem; margin: 0 10px; transition: all 0.2s; border-radius: 12px; padding: 12px 18px !important; display: flex !important; align-items: center !important; }
        .sidebar-menu li a:hover:not(.active) { background: #f8fafc; color: #1e293b; }
        .sidebar-menu .menu-header { color: #94a3b8; font-weight: 800; font-size: 0.75rem; letter-spacing: 1px; padding: 25px 25px 10px !important; text-transform: uppercase; }
        .navbar-bg { display: none !important; }
        .main-navbar { position: fixed; top: 0; right: 0; left: 260px; z-index: 1030; height: 75px; background: transparent !important; border-bottom: 1px solid #f1f5f9; box-shadow: none !important; transition: all 0.3s; }
        .main-navbar .nav-link, .main-navbar h4, .main-navbar .text-dark, .main-navbar .nav-link-user .d-lg-inline-block { color: #1e293b !important; }
        .main-navbar .material-symbols-outlined { color: #64748b !important; }
        .main-navbar .form-inline input::placeholder { color: #94a3b8; }
        .main-navbar .form-inline input { color: #334155 !important; }
        .main-navbar .form-inline div { background: #f8fafc !important; border: 1px solid #f1f5f9 !important; }
        .navbar-bg { background-color: transparent !important; height: 115px !important; }
        
        @media (max-width: 1024px) {
            .main-navbar { left: 0; padding-left: 20px !important; }
            .main-content { padding-left: 20px; }
        }
        
        /* Material Symbols Integration */
        .material-symbols-outlined {
          font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
          display: inline-block;
          vertical-align: middle;
        }

        /* ========== GLOBAL COMPACT SPACING ========== */

        /* Tighter main content padding */
        .main-content { padding: 75px 20px 20px 275px !important; }
        .main-content .section { padding: 0 !important; }
        .main-content .section .section-body { padding: 0 !important; }

        /* Compact cards */
        .card { border-radius: 12px !important; margin-bottom: 12px !important; border: 1px solid #e2e8f0 !important; box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important; }
        .card .card-header { padding: 12px 16px !important; min-height: auto !important; }
        .card .card-body { padding: 12px 16px !important; }
        .card .card-footer { padding: 10px 16px !important; }

        /* Compact tables */
        .table th { padding: 8px 12px !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; letter-spacing: 0.5px !important; color: #64748b !important; background: #f8fafc !important; border-bottom: 2px solid #e2e8f0 !important; white-space: nowrap !important; }
        .table td { padding: 8px 12px !important; font-size: 13px !important; vertical-align: middle !important; border-bottom: 1px solid #f1f5f9 !important; }
        .table tbody tr:hover { background: #f8fafc !important; }
        .table-striped tbody tr:nth-of-type(odd) { background: #fafbfc !important; }
        .table-bordered th, .table-bordered td { border: 1px solid #e2e8f0 !important; }

        /* Reduce section header spacing */
        .section-header { padding: 8px 0 !important; margin-bottom: 8px !important; }
        .section-header h1 { font-size: 1.25rem !important; font-weight: 800 !important; }

        /* Compact form groups */
        .form-group { margin-bottom: 12px !important; }
        .form-group label, .form-group .input-label { font-size: 12px !important; font-weight: 600 !important; margin-bottom: 4px !important; }
        .form-control { padding: 6px 12px !important; height: 38px !important; font-size: 13px !important; border-radius: 8px !important; }
        textarea.form-control { height: auto !important; }
        .custom-select { height: 38px !important; padding: 6px 12px !important; font-size: 13px !important; border-radius: 8px !important; }

        /* Compact buttons */
        .btn { padding: 6px 16px !important; font-size: 13px !important; border-radius: 8px !important; }
        .btn-lg { padding: 8px 20px !important; }
        .btn-sm { padding: 4px 10px !important; font-size: 11px !important; }

        /* Compact pagination */
        .pagination { margin: 8px 0 !important; }
        .pagination .page-link { padding: 4px 10px !important; font-size: 12px !important; }

        /* Compact breadcrumb */
        .breadcrumb { padding: 6px 0 !important; margin-bottom: 8px !important; font-size: 12px !important; }

        /* Reduce row gutters */
        .row { margin-left: -8px !important; margin-right: -8px !important; }
        .row > [class*="col-"] { padding-left: 8px !important; padding-right: 8px !important; }

        /* Compact alert/info boxes */
        .alert { padding: 8px 14px !important; font-size: 13px !important; border-radius: 8px !important; margin-bottom: 10px !important; }

        /* Compact modal */
        .modal-header { padding: 12px 16px !important; }
        .modal-body { padding: 16px !important; }
        .modal-footer { padding: 10px 16px !important; }

        /* Tighter badge */
        .badge { padding: 3px 8px !important; font-size: 10px !important; font-weight: 700 !important; border-radius: 6px !important; }

        /* Compact nav tabs */
        .nav-tabs .nav-link { padding: 6px 14px !important; font-size: 13px !important; }

        /* Remove excessive margins on headings inside content */
        .section-body h1, .section-body h2, .section-body h3 { margin-bottom: 8px !important; }
        .section-body p { margin-bottom: 6px !important; }

        /* Sidebar compact */
        .sidebar-menu li { margin-bottom: 2px !important; }
        .sidebar-menu li a { padding: 10px 16px !important; font-size: 14px !important; }

        @media (max-width: 1024px) {
            .main-content { padding: 75px 12px 12px 12px !important; }
        }

        /* --- Skeleton Loader --- */
        .skeleton {
            background: #eee;
            background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
            border-radius: 5px;
            background-size: 200% 100%;
            animation: 1.5s shine linear infinite;
        }

        @keyframes shine {
            to {
                background-position-x: -200%;
            }
        }
    </style>
</head>
<body class="@if($isRtl) rtl @endif">

<div id="app">
    <div class="main-wrapper">
        @include('admin.includes.navbar')

        @include('admin.includes.sidebar')

        <div class="main-content">

            @yield('content')

        </div>
    </div>

    <div class="modal fade" id="fileViewModal" tabindex="-1" aria-labelledby="fileViewModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <img src="" class="w-100" height="350px" alt="">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('public.close') }}</button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- General JS Scripts -->
<script src="/assets/admin/vendor/jquery/jquery-3.3.1.min.js"></script>
<script src="/assets/admin/vendor/poper/popper.min.js"></script>
<script src="/assets/admin/vendor/bootstrap/bootstrap.min.js"></script>
<script src="/assets/admin/vendor/nicescroll/jquery.nicescroll.min.js"></script>
<script src="/assets/admin/vendor/moment/moment.min.js"></script>
<script src="/assets/admin/js/stisla.js"></script>
<script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>

<script>
    (function () {
        "use strict";

        window.csrfToken = $('meta[name="csrf-token"]');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.adminPanelPrefix = '{{ getAdminPanelUrl() }}';

        @if(session()->has('toast'))
        $.toast({
            heading: '{{ session()->get('toast')['title'] ?? '' }}',
            text: '{{ session()->get('toast')['msg'] ?? '' }}',
            bgColor: '@if(session()->get('toast')['status'] == 'success') #43d477 @else #f63c3c @endif',
            textColor: 'white',
            hideAfter: 10000,
            position: 'bottom-right',
            icon: '{{ session()->get('toast')['status'] }}'
        });
        @endif
    })(jQuery);
</script>

<script src="/assets/admin/vendor/daterangepicker/daterangepicker.min.js"></script>
<script src="/assets/default/vendors/select2/select2.min.js"></script>

<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<!-- Template JS File -->
<script src="/assets/admin/js/scripts.js"></script>

@stack('styles_bottom')
@stack('scripts_bottom')

<script>
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

<script src="/assets/admin/js/custom.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    window.confirmDelete = function(url) {
        Swal.fire({
            title: deleteAlertTitle,
            text: deleteAlertHint,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f63c3c',
            cancelButtonColor: '#64748b',
            confirmButtonText: deleteAlertConfirm,
            cancelButtonText: deleteAlertCancel
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>
<script>
    {!! !empty(getCustomCssAndJs('js')) ? getCustomCssAndJs('js') : '' !!}
</script>
</body>
</html>
