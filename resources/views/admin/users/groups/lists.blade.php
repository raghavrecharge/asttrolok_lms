@extends('admin.layouts.app')

@push('styles_top')
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#32A128",
                    "background-light": "#F7F9FC",
                    "background-dark": "#112210",
                },
                fontFamily: {
                    "display": ["Inter"]
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>
<style>
    .um-page-container { font-family: 'Inter', sans-serif; }
    .um-page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
</style>
@endpush

@section('content')
<div class="um-page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased h-full flex flex-col p-4 md:p-8">

    <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ trans('admin/main.user_groups') }}</h2>
            <p class="text-slate-500 text-sm mt-1">Manage and organize platform user groups and access levels</p>
        </div>
        
        <div class="flex items-center gap-3 italic-none">
            @can('admin_user_groups_create')
                <a href="{{ getAdminPanelUrl() }}/users/groups/new" class="bg-primary hover:bg-primary/90 text-white font-bold h-10 px-6 rounded-xl flex items-center gap-2 transition-all shadow-sm shrink-0 no-underline text-xs uppercase tracking-widest italic-none">
                    <span class="material-symbols-outlined text-lg">add</span>
                    {{ trans('admin/main.add_new') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden italic-none">
        <div class="overflow-x-auto italic-none">
            <table class="w-full text-left border-collapse italic-none">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">#</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.name') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.users_count') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.commission') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.discount') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-none">
                    @foreach($groups as $group)
                    <tr class="hover:bg-slate-50 transition-colors italic-none">
                        <td class="px-6 py-4 text-sm font-medium text-slate-500 italic-none">#{{ $group->id }}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-800 italic-none">{{ $group->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 bg-slate-50 text-slate-600 rounded-lg text-xs font-black italic-none">{{ $group->users->count() }}</span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-black text-primary italic-none">
                            {{ $group->commission ?? 0 }}%
                        </td>
                        <td class="px-6 py-4 text-center text-sm font-black text-indigo-600 italic-none">
                            {{ $group->discount ?? 0 }}%
                        </td>
                        <td class="px-6 py-4 text-center italic-none">
                            <span class="px-2.5 py-1 rounded-full {{ ($group->status == 'active') ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} text-[9px] font-black uppercase tracking-widest italic-none">
                                {{ trans('admin/main.'.$group->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center italic-none">
                            <div class="flex items-center justify-center gap-2 italic-none">
                                @can('admin_user_groups_edit')
                                    <a href="{{ getAdminPanelUrl() }}/users/groups/{{ $group->id }}/edit" class="size-8 rounded-lg bg-slate-50 text-slate-400 hover:text-primary transition-colors flex items-center justify-center border border-slate-100 italic-none" title="Edit">
                                        <span class="material-symbols-outlined text-lg italic-none">edit</span>
                                    </a>
                                @endcan

                                @can('admin_user_groups_delete')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/users/groups/'.$group->id.'/delete' , 'btnClass' => 'size-8 rounded-lg bg-slate-50 text-slate-400 hover:text-rose-600 transition-colors flex items-center justify-center border border-slate-100', 'deleteConfirmMsg' => trans('update.user_group_delete_confirm_msg')])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
