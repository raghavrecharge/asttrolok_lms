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
<div class="um-page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased h-full flex flex-col p-4 md:p-8 min-h-[calc(100vh-100px)]">

    <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $pageTitle }}</h2>
            <p class="text-slate-500 text-sm mt-1">Manage and monitor administrative staff roles ({{ $users->total() }} Total)</p>
        </div>
        
        <form method="get" id="umFilterForm" class="flex items-center gap-3 flex-wrap">
            <div class="relative group min-w-[250px]">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary">search</span>
                <input type="text" name="full_name" value="{{ request()->get('full_name') }}" class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 text-sm transition-all placeholder:text-slate-500 shadow-sm" placeholder="{{ trans('admin/main.search') }} By Name">
            </div>

            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm h-10">
                <select name="role_id" class="bg-transparent border-none text-sm font-medium text-slate-600 focus:ring-0 cursor-pointer px-3 h-full" onchange="this.form.submit()">
                    <option value="">{{ trans('admin/main.role') }} (All)</option>
                    @foreach($staffsRoles as $role)
                        <option value="{{ $role->id }}" @if(request()->get('role_id') == $role->id) selected @endif>{{ $role->caption }}</option>
                    @endforeach
                </select>
            </div>

            @can('admin_users_create')
            <a href="{{ getAdminPanelUrl() }}/users/create" class="bg-primary hover:bg-primary/90 text-white font-bold h-10 px-5 rounded-xl flex items-center gap-2 transition-all shadow-sm shrink-0 no-underline text-sm">
                <span class="material-symbols-outlined text-sm">add</span>
                Add Staff
            </a>
            @endcan
        </form>
    </div>

    {{-- LIST VIEW --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex-1">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.id') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Staff Member</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Access Role</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.register_date') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                    @php
                        $sc = 'bg-emerald-100 text-emerald-700'; $sl='Active';
                        if($user->ban && !empty($user->ban_end_at) && $user->ban_end_at > time()) { $sc='bg-rose-100 text-rose-700'; $sl='Banned'; }
                        elseif($user->status == 'inactive') { $sc='bg-amber-100 text-amber-700'; $sl='Inactive'; }
                        
                        $nm = $user->full_name ?? 'Staff';
                        $parts = explode(' ', trim($nm));
                        $ini = strtoupper(substr($parts[0],0,1) . (count($parts)>1?substr($parts[count($parts)-1],0,1):''));
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-slate-500">#{{ $user->id }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-xl bg-slate-100 flex flex-shrink-0 items-center justify-center overflow-hidden text-slate-400 font-black text-xs">
                                    @if($user->avatar)
                                        <img src="{{ $user->getAvatar() }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ $ini }}
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 whitespace-nowrap">{{ $nm }}</span>
                                    <span class="text-[10px] font-medium text-slate-400 tracking-tight">{{ $user->email ?? $user->mobile ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest">{{ $user->role->caption }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-500">{{ dateTimeFormat($user->created_at, 'j M Y') }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 rounded-full {{ $sc }} text-[9px] font-black uppercase tracking-widest text-center">{{ $sl }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                @can('admin_users_edit')
                                    <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="size-8 rounded-lg bg-slate-50 text-slate-400 hover:text-primary transition-colors flex items-center justify-center border border-slate-100" title="Edit">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="mt-6 px-4 py-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <p class="text-sm text-slate-600 font-medium">Showing <span class="font-bold border-b-2 border-primary/30 pb-0.5">{{ $users->firstItem() }}-{{ $users->lastItem() }}</span></p>
        <div class="um-tailwind-pagination">
            {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
    
</div>
@endsection

@push('scripts_bottom')
<style>
    .um-tailwind-pagination .pagination { display: flex; gap: 4px; margin: 0; padding: 0; list-style: none; }
    .um-tailwind-pagination .page-item .page-link { 
        display: flex; align-items: center; justify-content: center; 
        width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; 
        color: #475569; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.2s;
    }
    .um-tailwind-pagination .page-item.active .page-link { background: #32A128; color: #fff; border-color: #32A128; }
    .um-tailwind-pagination .page-item .page-link:hover { background: #f8fafc; color: #32A128; }
</style>
@endpush
