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
                        "primary": "#32A128",
                        "accent": "#eab308",
                        "background-light": "#f6f8f5",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"], "body": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .trend-page { font-family: 'Inter', sans-serif; }
        .trend-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .row:first-child .card:first-child { display: none !important; }
        .badge-premium { border-radius: 0.5rem; font-weight: 700; font-size: 0.7rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; }
    </style>
@endpush

@section('content')
<div class="trend-page bg-background-light text-slate-900 p-4 md:p-8 space-y-6">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20 shrink-0">
                <span class="material-symbols-outlined">trending_up</span>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">{{ trans('home.trending_categories') }}</h2>
                <p class="text-[10px] font-bold text-slate-400 border border-slate-200 uppercase tracking-widest mt-1">Featured & Trending Content</p>
            </div>
        </div>

        @can('admin_create_trending_categories')
            <a href="{{ getAdminPanelUrl() }}/categories/trends/create" 
               class="bg-primary hover:bg-emerald-700 text-white rounded-2xl px-5 h-12 flex items-center justify-center gap-2 font-bold shadow-lg shadow-primary/20 scale-hover transition-all">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                <span>{{ trans('admin/main.create_trend_category') }}</span>
            </a>
        @endcan
    </header>

    <!-- Data Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('admin/main.title') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('admin/main.trend_color') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-right">{{ trans('admin/main.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-last-of-type-tr-td-actions">
                    @foreach($trends as $trend)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-800">{{ $trend->category->title }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="size-6 rounded-lg shadow-sm border border-slate-200/50" style="background-color: {{ $trend->color }}"></div>
                                <span class="text-xs font-black text-slate-400 tracking-widest uppercase">{{ $trend->color }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('admin_edit_trending_categories')
                                    <a href="{{ getAdminPanelUrl() }}/categories/trends/{{ $trend->id }}/edit" 
                                       class="size-9 rounded-xl bg-primary/5 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all scale-hover"
                                       title="{{ trans('admin/main.edit') }}">
                                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                    </a>
                                @endcan
                                @can('admin_delete_trending_categories')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/categories/trends/'.$trend->id.'/delete','btnClass' => 'size-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all scale-hover border-none p-0'])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 flex items-center justify-center">
            {{ $trends->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Hints Area -->
    <div class="bg-primary/5 rounded-3xl border border-primary/10 p-6 md:p-8">
        <div class="flex items-start gap-4">
            <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0 transition-all hover:scale-110">
                <span class="material-symbols-outlined">lightbulb</span>
            </div>
            <div>
                <h4 class="text-lg font-black text-slate-800 tracking-tight">{{ trans('admin/main.hints') }}</h4>
                <div class="mt-3 space-y-4">
                    <div class="max-w-3xl">
                        <p class="text-[11px] font-black uppercase tracking-wider text-primary mb-1">{{ trans('admin/main.trend_hint_title_1') }}</p>
                        <p class="text-sm font-medium text-slate-500 leading-relaxed">{{ trans('admin/main.trend_hint_description_1') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection


