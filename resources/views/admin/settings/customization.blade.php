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
            @apply px-6 py-4 text-[11px] font-black uppercase tracking-widest transition-all border-b-2 flex items-center gap-2 whitespace-nowrap !important;
        }
        .nav-tab-btn.active {
            @apply border-primary text-primary bg-primary/5 !important;
        }
        .nav-tab-btn:not(.active) {
            @apply border-transparent text-slate-400 hover:text-slate-600 hover:bg-slate-50 !important;
        }

        /* Inner Form Overrides for Premium Look */
        form .form-control { 
            @apply w-full px-6 py-4 bg-slate-900 border-none rounded-2xl transition-all text-sm font-medium text-emerald-400 !important;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace !important;
            height: auto !important;
        }
        form .btn-primary { 
            @apply px-6 py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all border-none !important;
        }
        
        .code-editor-container { @apply bg-slate-950 rounded-3xl p-6 shadow-2xl; }

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
                    <span class="text-slate-600">Custom CSS & JS</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customization Settings</h1>
            </div>
        </div>

        @php
            $itemValue = (!empty($settings) and !empty($settings['custom_css_js'])) ? $settings['custom_css_js']->value : '';
            if (!empty($itemValue) and !is_array($itemValue)) {
                $itemValue = json_decode($itemValue, true);
            }
        @endphp

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[600px]">
            
            {{-- Horizontal Tabs --}}
            <div class="border-b border-slate-100 overflow-x-auto no-scrollbar bg-white sticky top-0 z-10">
                <div class="flex px-4">
                    <a class="nav-tab-btn active" id="css-tab" data-toggle="tab" href="#css" role="tab" aria-controls="css" aria-selected="true">
                        <span class="material-symbols-outlined text-[18px]">css</span> CSS
                    </a>
                    <a class="nav-tab-btn" id="js-tab" data-toggle="tab" href="#js" role="tab" aria-controls="js" aria-selected="true">
                        <span class="material-symbols-outlined text-[18px]">javascript</span> JS
                    </a>
                </div>
            </div>

            <div class="p-6 md:p-10">
                <div class="tab-content" id="myTabContent2">
                    
                    <div class="tab-pane fade active show" id="css" role="tabpanel">
                        <form action="{{ getAdminPanelUrl() }}/settings/custom_css_js/store" method="post" class="space-y-8">
                            {{ csrf_field() }}
                            <div class="code-editor-container space-y-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2.5 rounded-full bg-red-500/80 shadow-sm shadow-red-500/20"></span>
                                        <span class="size-2.5 rounded-full bg-amber-500/80 shadow-sm shadow-amber-500/20"></span>
                                        <span class="size-2.5 rounded-full bg-green-500/80 shadow-sm shadow-green-500/20"></span>
                                        <span class="text-[10px] font-black text-slate-500 uppercase ml-3 tracking-widest">Global Stylesheet Injection</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-500/50 uppercase tracking-tighter">css_editor.v1</span>
                                </div>
                                <textarea name="value[css]" class="form-control" rows="18" placeholder="/* Enter your custom CSS here */">{{ (!empty($itemValue) and !empty($itemValue['css'])) ? $itemValue['css'] : '' }}</textarea>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="js" role="tabpanel">
                        <form action="{{ getAdminPanelUrl() }}/settings/custom_css_js/store" method="post" class="space-y-8">
                            {{ csrf_field() }}
                            <div class="code-editor-container space-y-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2.5 rounded-full bg-red-500/80 shadow-sm shadow-red-500/20"></span>
                                        <span class="size-2.5 rounded-full bg-amber-500/80 shadow-sm shadow-amber-500/20"></span>
                                        <span class="size-2.5 rounded-full bg-green-500/80 shadow-sm shadow-green-500/20"></span>
                                        <span class="text-[10px] font-black text-slate-500 uppercase ml-3 tracking-widest">Global Javascript Injection</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-amber-500/50 uppercase tracking-tighter">js_injector.v1</span>
                                </div>
                                <textarea name="value[js]" class="form-control" rows="18" placeholder="// Enter your custom JavaScript here">{{ (!empty($itemValue) and !empty($itemValue['js'])) ? $itemValue['js'] : '' }}</textarea>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/select2/select2.min.js"></script>
@endpush

