@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#16A34A",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>
    <style>
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section { display: none !important; }
    </style>
@endpush

@section('content')
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 min-h-[calc(100vh-100px)]">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="size-12 rounded-2xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/30">
                <span class="material-symbols-outlined text-2xl">settings</span>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ trans('admin/main.settings') }}</h1>
                <p class="text-sm font-bold text-slate-500 max-w-xl mt-1">{{ trans('admin/main.overview_hint') }}</p>
            </div>
        </div>
    </div>

    <!-- Settings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        @can('admin_settings_general')
            <a href="/admin/settings/general" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">build_circle</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.general_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.general_card_hint') }}</p>
                <div class="flex items-center text-primary text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

        @can('admin_settings_financial')
            <a href="/admin/settings/financial" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">account_balance</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.financial_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.financial_card_hint') }}</p>
                <div class="flex items-center text-emerald-600 text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

        @can('admin_settings_personalization')
            <a href="/admin/settings/personalization/page_background" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">palette</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.personalization_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.personalization_card_hint') }}</p>
                <div class="flex items-center text-purple-600 text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

        @can('admin_settings_notifications')
            <a href="/admin/settings/notifications" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">notifications_active</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.notifications_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.notifications_card_hint') }}</p>
                <div class="flex items-center text-amber-600 text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

        @can('admin_settings_seo')
            <a href="/admin/settings/seo" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-sky-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-sky-50 dark:bg-sky-500/10 text-sky-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">travel_explore</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.seo_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.seo_card_hint') }}</p>
                <div class="flex items-center text-sky-600 text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

        @can('admin_settings_customization')
            <a href="/admin/settings/customization" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">view_quilt</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.customization_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.customization_card_hint') }}</p>
                <div class="flex items-center text-rose-600 text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

        @can('admin_settings_sidebanner')
            <a href="/admin/settings/sidebanner" class="group flex flex-col bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="size-14 rounded-2xl bg-orange-50 dark:bg-orange-500/10 text-orange-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-3xl">view_sidebar</span>
                </div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-white mb-2">{{ trans('admin/main.sidebanner_card_title') }}</h3>
                <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-[90%] mb-6 flex-1">{{ trans('admin/main.sidebanner_card_hint') }}</p>
                <div class="flex items-center text-orange-600 text-sm font-bold mt-auto group-hover:gap-2 transition-all gap-1">
                    {{ trans('admin/main.change_setting') }}
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </div>
            </a>
        @endcan

    </div>
</div>
@endsection
