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
        .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
        
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
        form label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1 ml-1 !important; }
        form .form-control { 
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 !important;
            height: auto !important;
        }
        form .btn-primary { 
            @apply px-6 py-3 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all border-none !important;
        }
        
        .custom-switch-indicator { @apply bg-slate-200 border-none !important; }
        .custom-switch-input:checked ~ .custom-switch-indicator { @apply bg-primary !important; }

        .card-hint { @apply bg-white border border-slate-200 rounded-3xl p-6 transition-all shadow-sm hover:border-primary/20; }

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
                    <span class="text-slate-600">SEO & Metas</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">SEO Metas</h1>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden min-h-[600px]">
            
            {{-- Horizontal Tabs --}}
            <div class="border-b border-slate-100 overflow-x-auto no-scrollbar bg-white sticky top-0 z-10">
                <div class="flex px-4">
                    <a class="nav-tab-btn active" id="extra_meta_tags-tab" data-toggle="tab" href="#extra_meta_tags" role="tab" aria-controls="extra_meta_tags" aria-selected="true">
                        {{ trans('update.extra_meta_tags') }}
                    </a>
                    @foreach(\App\Models\Setting::$pagesSeoMetas as $page)
                        <a class="nav-tab-btn" id="{{ $page }}-tab" data-toggle="tab" href="#{{ $page }}" role="tab" aria-controls="{{ $page }}" aria-selected="true">
                            {{ trans('admin/main.seo_metas_'.$page) }}
                        </a>
                    @endforeach
                </div>
            </div>

            @php
                $itemValue = (!empty($settings) and !empty($settings['seo_metas'])) ? $settings['seo_metas']->value : '';
                if (!empty($itemValue) and !is_array($itemValue)) {
                    $itemValue = json_decode($itemValue, true);
                }
            @endphp

            <div class="p-6 md:p-10">
                <div class="tab-content" id="myTabContent2">
                    
                    <!-- Extra Meta Tags -->
                    <div class="tab-pane fade show active" id="extra_meta_tags" role="tabpanel">
                        <div class="max-w-4xl">
                            <form action="{{ getAdminPanelUrl() }}/settings/seo_metas/store" method="post" class="space-y-6">
                                {{ csrf_field() }}
                                <div class="space-y-2">
                                    <label>{{ trans('update.extra_meta_tags') }}</label>
                                    <textarea name="value[extra_meta_tags]" rows="6" class="form-control">{{ (!empty($itemValue) and !empty($itemValue['extra_meta_tags'])) ? $itemValue['extra_meta_tags'] : '' }}</textarea>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                        <div class="flex items-start gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="material-symbols-outlined text-[18px] text-primary">info</span>
                                            <p class="text-[11px] text-slate-500 font-bold leading-relaxed">{{ trans('update.extra_meta_tags_hint1') }}</p>
                                        </div>
                                        <div class="flex items-start gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="material-symbols-outlined text-[18px] text-primary">info</span>
                                            <p class="text-[11px] text-slate-500 font-bold leading-relaxed">{{ trans('update.extra_meta_tags_hint2') }}</p>
                                        </div>
                                        <div class="flex items-start gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="material-symbols-outlined text-[18px] text-primary">info</span>
                                            <p class="text-[11px] text-slate-500 font-bold leading-relaxed">{{ trans('update.extra_meta_tags_hint3') }}</p>
                                        </div>
                                        <div class="flex items-start gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                            <span class="material-symbols-outlined text-[18px] text-primary">info</span>
                                            <p class="text-[11px] text-slate-500 font-bold leading-relaxed">{{ trans('update.extra_meta_tags_hint4') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @foreach(\App\Models\Setting::$pagesSeoMetas as $page)
                        <div class="tab-pane fade" id="{{ $page }}" role="tabpanel">
                            <div class="max-w-2xl">
                                <form action="{{ getAdminPanelUrl() }}/settings/seo_metas/store" method="post" class="space-y-8">
                                    {{ csrf_field() }}
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="space-y-2">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="value[{{ $page }}][title]" value="{{ (!empty($itemValue) and !empty($itemValue[$page])) ? $itemValue[$page]['title'] : old('title') }}" class="form-control w-full @error('title') border-red-500 @enderror"/>
                                            @error('title')<div class="text-red-500 text-[10px] font-bold mt-1 italic">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="space-y-2">
                                            <label>{{ trans('public.description') }}</label>
                                            <textarea name="value[{{ $page }}][description]" rows="4" class="form-control w-full @error('description') border-red-500 @enderror">{{ (!empty($itemValue) and !empty($itemValue[$page])) ? $itemValue[$page]['description'] : old('description') }}</textarea>
                                            @error('description')<div class="text-red-500 text-[10px] font-bold mt-1 italic">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="flex items-center gap-6 py-2">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 ml-1">{{ trans('admin/main.indexing_control') }}</span>
                                                <span class="text-xs font-bold text-slate-700 leading-none">Search Status</span>
                                            </div>
                                            <div class="flex items-center bg-slate-50 px-4 py-2 rounded-2xl gap-4 border border-slate-100">
                                                <span class="text-[11px] font-bold text-slate-400">{{ trans('admin/main.no_index') }}</span>
                                                <input type="hidden" name="value[{{ $page }}][robot]" value="noindex">
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="value[{{ $page }}][robot]" id="{{ $page }}Robot" value="index" {{ (!empty($itemValue) and !empty($itemValue[$page]) and (empty($itemValue[$page]['robot']) or $itemValue[$page]['robot'] != 'noindex')) ? 'checked' : '' }} class="sr-only peer">
                                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none ring-0 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                                </label>
                                                <span class="text-[11px] font-bold text-primary">{{ trans('admin/main.index') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-4">
                                        <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Hints Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card-hint">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">lightbulb</span>
                    </div>
                    <h4 class="text-sm font-black text-slate-800 tracking-tight uppercase">{{ trans('admin/main.seo_metas_hint_title_1') }}</h4>
                </div>
                <p class="text-xs font-bold text-slate-500 leading-relaxed">{{ trans('admin/main.seo_metas_hint_description_1') }}</p>
            </div>
            <div class="card-hint">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">tips_and_updates</span>
                    </div>
                    <h4 class="text-sm font-black text-slate-800 tracking-tight uppercase">{{ trans('admin/main.seo_metas_hint_title_2') }}</h4>
                </div>
                <p class="text-xs font-bold text-slate-500 leading-relaxed">{{ trans('admin/main.seo_metas_hint_description_2') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

