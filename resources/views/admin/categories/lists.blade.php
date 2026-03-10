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
        .category-page { font-family: 'Inter', sans-serif; }
        .category-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .row:first-child .card:first-child { display: none !important; }
        .badge-premium { border-radius: 0.5rem; font-weight: 700; font-size: 0.7rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; }
    </style>
@endpush

@section('content')
<div class="category-page bg-background-light text-slate-900 p-4 md:p-8 space-y-6">

    <!-- Header & Stats Area -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20 shrink-0">
                <span class="material-symbols-outlined">category</span>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">{{ trans('admin/main.categories') }}</h2>
                <p class="text-[10px] font-bold text-slate-400 border border-slate-200 uppercase tracking-widest mt-1">Classification & Taxonomy Management</p>
            </div>
        </div>

        @can('admin_categories_create')
            <a href="{{ getAdminPanelUrl() }}/categories/create" 
               class="bg-primary hover:bg-emerald-700 text-white rounded-2xl px-5 h-12 flex items-center justify-center gap-2 font-bold shadow-lg shadow-primary/20 scale-hover transition-all">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                <span>{{ trans('categories.new_category') }}</span>
            </a>
        @endcan
    </header>

    <!-- Data Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider">Icon</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider">Order</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider">Title</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">Sub-Categories</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">Classes</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">Teachers</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-last-of-type-tr-td-actions">
                    @foreach($categories as $category)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center p-2 group-hover:bg-primary/10 transition-colors">
                                <img src="{{ $category->icon }}" class="w-full h-full object-contain" alt="">
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-400">{{ $category->order }}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-800">{{ $category->title }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge-premium bg-blue-50 text-blue-600">{{ $category->subCategories->count() }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge-premium bg-emerald-50 text-emerald-600">{{ count($category->getCategoryCourses()) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="badge-premium bg-amber-50 text-amber-600">{{ count($category->getCategoryInstructorsIdsHasMeeting()) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('admin_categories_edit')
                                    <a href="{{ getAdminPanelUrl() }}/categories/{{ $category->id }}/edit" 
                                       class="size-9 rounded-xl bg-primary/5 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all scale-hover"
                                       title="Edit Item">
                                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                    </a>
                                @endcan
                                @can('admin_categories_delete')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/categories/'.$category->id.'/delete', 'deleteConfirmMsg' => trans('update.category_delete_confirm_msg'), 'btnClass' => 'size-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all scale-hover border-none p-0'])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 flex items-center justify-center">
            {{ $categories->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>
@endsection

