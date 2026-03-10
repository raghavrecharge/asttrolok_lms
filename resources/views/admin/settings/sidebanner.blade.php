@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
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
        
        /* Inner Form Overrides for Premium Look */
        form label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1 !important; }
        form .form-control { 
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 !important;
            height: auto !important;
        }
        form .btn-primary { 
            @apply px-6 py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all border-none !important;
        }

        .input-group-text { @apply bg-slate-100 border-slate-100 rounded-l-2xl px-4 text-slate-500 !important; }
        .input-group .form-control { @apply rounded-l-none !important; }
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
                    <span class="text-slate-600">Sidebar Banner</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sidebar Promotion Settings</h1>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-6 md:p-10 min-h-[400px]">
            <div class="max-w-4xl">
                <div class="tab-content" id="myTabContent2">
                    @include('admin.settings.sidebanner.socials',['itemValue' => (!empty($settings) and !empty($settings['sidebanner'])) ? $settings['sidebanner']->value : ''])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/js/admin/settings/general.min.js"></script>
@endpush

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
    <script src="/assets/default/js/admin/settings/general.min.js"></script>
@endpush

