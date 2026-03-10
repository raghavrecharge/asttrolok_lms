@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16A34A",
                        "primary-light": "#F0FDF4",
                        "accent": "#eab308",
                        "background-light": "#f8fafc",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"], "body": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .settings-page { font-family: 'Inter', sans-serif; }
        .settings-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
        
        .nav-tab-btn {
            @apply px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2 flex items-center gap-2 whitespace-nowrap !important;
        }
        .nav-tab-btn.active {
            @apply border-primary text-primary bg-primary/5 !important;
        }
        .nav-tab-btn:not(.active) {
            @apply border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50 !important;
        }

        /* Inner Form Overrides for Premium Look */
        form .form-label, form label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1 !important; }
        form .form-control { 
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 !important;
            height: auto !important;
        }
        form .btn-primary { 
            @apply px-6 py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all border-none !important;
        }
        
        .custom-switch-indicator { @apply bg-slate-200 border-none !important; }
        .custom-switch-input:checked ~ .custom-switch-indicator { @apply bg-primary !important; }
        
        .select2-container--default .select2-selection--multiple, 
        .select2-container--default .select2-selection--single { 
            @apply bg-slate-50 border-slate-100 rounded-2xl !important; 
            min-height: 48px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            @apply leading-[48px] px-4 text-sm font-bold text-slate-700 !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single {
            @apply border-primary ring-4 ring-primary/10 !important;
        }

        .input-group-text { @apply bg-slate-100 border-slate-100 rounded-l-2xl px-4 text-slate-500 !important; }
        .input-group .form-control { @apply rounded-l-none !important; }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="settings-page bg-slate-50 text-slate-900 p-4 md:p-8 space-y-6">

    <div class="max-w-[1400px] mx-auto space-y-6">
        
        {{-- Breadcrumbs & Title --}}
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-2">
                    <a href="{{ getAdminPanelUrl() }}" class="hover:text-primary no-underline">Dashboard</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <a href="{{ getAdminPanelUrl('/settings') }}" class="hover:text-primary no-underline">Settings</a>
                    <span class="material-symbols-outlined text-[12px]">chevron_right</span>
                    <span class="text-slate-600">Financial Configuration</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Financial Settings</h1>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[600px]">
            
            {{-- Horizontal Tabs --}}
            <div class="border-b border-slate-100 overflow-x-auto no-scrollbar bg-white sticky top-0 z-10">
                <div class="flex px-4">
                    <a class="nav-tab-btn @if(empty(request()->get('tab'))) active @endif px-6 py-4" id="basic-tab" data-toggle="tab" href="#basic" role="tab">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span> {{ trans('admin/main.basic') }}
                    </a>
                    <a class="nav-tab-btn @if(request()->get('tab') == 'offline_banks') active @endif px-6 py-4" id="offline_banks-tab" href="{{ getAdminPanelUrl('/settings/financial?tab=offline_banks') }}">
                        <span class="material-symbols-outlined text-[18px]">museum</span> {{ trans('admin/main.offline_banks_credits') }}
                    </a>
                    @can('admin_payment_channel')
                        <a class="nav-tab-btn @if(request()->get('tab') == 'payment_channels') active @endif px-6 py-4" id="payment_channels-tab" data-toggle="tab" href="#payment_channels" role="tab">
                            <span class="material-symbols-outlined text-[18px]">credit_card</span> {{ trans('admin/main.payment_channels') }}
                        </a>
                    @endcan
                    <a class="nav-tab-btn px-6 py-4" id="referral-tab" data-toggle="tab" href="#referral" role="tab">
                        <span class="material-symbols-outlined text-[18px]">group_add</span> {{ trans('admin/main.referral') }}
                    </a>
                    <a class="nav-tab-btn @if(request()->get('tab') == 'currency') active @endif px-6 py-4" id="currency-tab" href="{{ getAdminPanelUrl('/settings/financial?tab=currency') }}">
                        <span class="material-symbols-outlined text-[18px]">currency_exchange</span> {{ trans('admin/main.currency') }}
                    </a>
                    <a class="nav-tab-btn @if(request()->get('tab') == 'user_banks') active @endif px-6 py-4" id="user_banks-tab" href="{{ getAdminPanelUrl('/settings/financial?tab=user_banks') }}">
                        <span class="material-symbols-outlined text-[18px]">account_balance</span> {{ trans('update.user_banks') }}
                    </a>
                </div>
            </div>

            <div class="p-6 md:p-10">
                <div class="tab-content" id="myTabContent2">
                    @include('admin.settings.financial.basic',['itemValue' => (!empty($settings) and !empty($settings['financial'])) ? $settings['financial']->value : ''])

                    @if(request()->get('tab') == "offline_banks")
                        @include('admin.settings.financial.offline_banks.index',['itemValue' => (!empty($settings) and !empty($settings['offline_banks'])) ? $settings['offline_banks']->value : ''])
                    @endif

                    @can('admin_payment_channel')
                        @include('admin.settings.financial.payment_channel.lists')
                    @endcan

                    @include('admin.settings.financial.referral',['itemValue' => (!empty($settings) and !empty($settings['referral'])) ? $settings['referral']->value : ''])

                    @if(request()->get('tab') == "currency")
                        @include('admin.settings.financial.currency',['itemValue' => (!empty($settings) and !empty($settings[\App\Models\Setting::$currencySettingsName])) ? $settings[\App\Models\Setting::$currencySettingsName]->value : ''])
                    @endif

                    @if(request()->get('tab') == "user_banks")
                        @include('admin.settings.financial.user_banks.index',['itemValue' => (!empty($settings) and !empty($settings['user_banks'])) ? $settings['user_banks']->value : ''])
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
@endpush
