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
            @apply px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2 flex items-center gap-2 whitespace-nowrap;
        }
        .nav-tab-btn.active {
            @apply border-primary text-primary bg-primary/5;
        }
        .nav-tab-btn:not(.active) {
            @apply border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50;
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
                    <span class="text-slate-600">Personalization</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Personalization Settings</h1>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[600px]">
            
            {{-- Horizontal Tabs --}}
            <div class="border-b border-slate-100 overflow-x-auto no-scrollbar bg-white sticky top-0 z-10">
                <div class="flex px-4">
                    @php
                        $items = ['page_background','home_sections','home_hero','home_hero2','home_video_or_image_box',
                                    'panel_sidebar','find_instructors','reward_program','become_instructor_section',
                                    'theme_colors', 'theme_fonts', 'forums_section', 'navbar_button','cookie_settings', 'mobile_app', 'maintenance_settings',
                                    'others_personalization', 'statistics'
                                 ];
                                 
                        $icons = [
                            'page_background' => 'wallpaper', 'home_sections' => 'view_comfy', 'home_hero' => 'panorama',
                            'home_hero2' => 'image', 'home_video_or_image_box' => 'smart_display', 'panel_sidebar' => 'chrome_reader_mode',
                            'find_instructors' => 'engineering', 'reward_program' => 'workspace_premium', 'become_instructor_section' => 'co_present',
                            'theme_colors' => 'format_paint', 'theme_fonts' => 'text_format', 'forums_section' => 'forum',
                            'navbar_button' => 'smart_button', 'cookie_settings' => 'cookie', 'mobile_app' => 'smartphone',
                            'maintenance_settings' => 'construction', 'others_personalization' => 'tune', 'statistics' => 'monitoring'
                        ];
                    @endphp

                    @foreach($items as $item)
                        <a class="nav-tab-btn {{ ($item == $name) ? 'active' : '' }}" href="{{ getAdminPanelUrl() }}/settings/personalization/{{ $item }}">
                            <span class="material-symbols-outlined text-[18px] opacity-70">{{ $icons[$item] ?? 'settings' }}</span>
                            <span>{{ trans('admin/main.'.$item) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="p-6 md:p-10">
                <div class="tab-content">
                    @include('admin.settings.personalization.'.$name,['itemValue' => (!empty($values)) ? $values : ''])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
@endpush

@push('scripts_bottom')

@endpush
