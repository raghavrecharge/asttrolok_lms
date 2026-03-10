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
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ trans('admin/main.instructors') }}</h2>
            <p class="text-slate-500 text-sm mt-1">Manage and monitor platform teachers and organizations ({{ $users->total() }} Total)</p>
        </div>
        
        <form method="get" id="umFilterForm" class="flex items-center gap-3 flex-wrap">
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary">search</span>
                <input type="text" name="full_name" value="{{ request()->get('full_name') }}" class="w-full pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-primary/20 text-sm transition-all placeholder:text-slate-500 shadow-sm" placeholder="{{ trans('admin/main.search') }}">
            </div>

            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm h-10">
                <select name="status" class="bg-transparent border-none text-sm font-medium text-slate-600 focus:ring-0 cursor-pointer px-3 h-full" onchange="this.form.submit()">
                    <option value="">{{ trans('admin/main.all_status') }}</option>
                    <option value="active_verified" {{ request()->get('status') == 'active_verified' ? 'selected' : '' }}>{{ trans('admin/main.active_verified') }}</option>
                    <option value="inactive" {{ request()->get('status') == 'inactive' ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
                    <option value="ban" {{ request()->get('status') == 'ban' ? 'selected' : '' }}>{{ trans('admin/main.banned') }}</option>
                </select>
                <div class="w-px h-4 bg-slate-200"></div>
                <select name="organization_id" class="bg-transparent border-none text-sm font-medium text-slate-600 focus:ring-0 cursor-pointer px-3 h-full max-w-[150px]" onchange="this.form.submit()">
                    <option value="">{{ trans('admin/main.select_organization') }}</option>
                    @foreach($organizations as $organization)
                        <option value="{{ $organization->id }}" {{ request()->get('organization_id') == $organization->id ? 'selected' : '' }}>{{ $organization->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm h-10">
                <button type="button" onclick="toggleView('list')" id="btnList" class="p-1.5 rounded-lg bg-primary/10 text-primary h-full" title="List View">
                    <span class="material-symbols-outlined block">view_list</span>
                </button>
                <button type="button" onclick="toggleView('grid')" id="btnGrid" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 h-full" title="Grid View">
                    <span class="material-symbols-outlined block">grid_view</span>
                </button>
            </div>

            @can('admin_users_export_excel')
            <a href="{{ getAdminPanelUrl() }}/instructors/excel?{{ http_build_query(request()->all()) }}" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold h-10 px-4 rounded-xl flex items-center gap-2 transition-all shadow-sm shrink-0 no-underline text-sm">
                <span class="material-symbols-outlined text-lg text-primary">download</span>
                {{ trans('admin/main.export_xls') }}
            </a>
            @endcan
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-5 group hover:border-primary/30 transition-all cursor-default relative overflow-hidden">
            <div class="size-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary transition-transform group-hover:scale-110">
                <span class="material-symbols-outlined font-bold text-3xl">groups</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.total_instructors') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalInstructors }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-5 group hover:border-primary/30 transition-all cursor-default relative overflow-hidden">
            <div class="size-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 transition-transform group-hover:scale-110">
                <span class="material-symbols-outlined font-bold text-3xl">corporate_fare</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.organizations_instructors') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalOrganizationsInstructors }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-5 group hover:border-primary/30 transition-all cursor-default relative overflow-hidden">
            <div class="size-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 transition-transform group-hover:scale-110">
                <span class="material-symbols-outlined font-bold text-3xl">pending_actions</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.inactive_instructors') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $inactiveInstructors }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-5 group hover:border-red-100 transition-all cursor-default relative overflow-hidden">
            <div class="size-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 transition-transform group-hover:scale-110">
                <span class="material-symbols-outlined font-bold text-3xl">block</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.ban_instructors') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $banInstructors }}</h3>
            </div>
        </div>
    </div>

    {{-- LIST VIEW --}}
    <div id="viewList" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.id') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.name') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.classes_sales') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.appointments_sales') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{ trans('admin/main.wallet_charge') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-none">
                    @foreach($users as $user)
                    @php
                        $sc = 'bg-emerald-100 text-emerald-700'; $sl='Active';
                        if($user->ban && !empty($user->ban_end_at) && $user->ban_end_at > time()) { $sc='bg-rose-100 text-rose-700'; $sl='Banned'; }
                        elseif($user->status == 'inactive') { $sc='bg-amber-100 text-amber-700'; $sl='Inactive'; }
                        
                        $nm = $user->full_name ?? 'Instructor';
                        $parts = explode(' ', trim($nm));
                        $ini = strtoupper(substr($parts[0],0,1) . (count($parts)>1?substr($parts[count($parts)-1],0,1):''));
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-slate-500">#{{ $user->id }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-primary/10 flex flex-shrink-0 items-center justify-center overflow-hidden text-primary font-black text-xs">
                                    @if($user->avatar)
                                        <img src="{{ $user->getAvatar() }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ $ini }}
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 whitespace-nowrap">{{ $nm }}</span>
                                    <span class="text-[10px] font-medium text-slate-400 tracking-tight">{{ $user->email ?? 'no-email' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-800">{{ $user->classesSalesCount }}</span>
                                <span class="text-[10px] font-bold text-primary">{{ handlePrice($user->classesSalesSum) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                             <div class="flex flex-col">
                                <span class="text-sm font-black text-slate-800">{{ $user->meetingsSalesCount }}</span>
                                <span class="text-[10px] font-bold text-primary">{{ handlePrice($user->meetingsSalesSum) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-black text-slate-800 text-right">{{ handlePrice($user->getAccountingBalance()) }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="px-2.5 py-1 rounded-full {{ $sc }} text-[9px] font-black uppercase tracking-widest text-center">{{ $sl }}</span>
                                <span class="text-[8px] font-bold {{ ($user->verified ? 'text-emerald-500' : 'text-amber-500') }} uppercase tracking-tighter text-center">({{ $user->verified ? 'Verified' : 'Unverified' }})</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center">
                                @include('admin.includes.user_action_dropdown', ['user' => $user])
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- GRID VIEW --}}
    <div id="viewGrid" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($users as $user)
            @php
                 $sc = 'bg-emerald-500'; 
                 if($user->ban && !empty($user->ban_end_at) && $user->ban_end_at > time()) { $sc='bg-rose-500'; }
                 elseif($user->status == 'inactive') { $sc='bg-amber-500'; }
                 
                 $nm = $user->full_name ?? 'Instructor';
                 $parts = explode(' ', trim($nm));
                 $ini = strtoupper(substr($parts[0],0,1) . (count($parts)>1?substr($parts[count($parts)-1],0,1):''));
            @endphp
            <div class="group bg-white rounded-3xl p-6 shadow-sm border border-slate-200 transition-all hover:shadow-xl hover:border-primary/20 relative flex flex-col items-center text-center overflow-hidden">
                
                <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                    @include('admin.includes.user_action_dropdown', ['user' => $user])
                </div>

                <div class="relative mb-6">
                    <div class="size-24 rounded-3xl p-1.5 border-2 border-slate-50 overflow-hidden bg-white shadow-sm transition-transform group-hover:scale-105">
                         @if($user->avatar)
                            <img src="{{ $user->getAvatar() }}" class="w-full h-full rounded-2xl object-cover">
                        @else
                            <div class="w-full h-full rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 font-black text-2xl uppercase">{{ $ini }}</div>
                        @endif
                    </div>
                    <div class="absolute -bottom-1 -right-1 size-6 rounded-xl {{ $sc }} border-4 border-white shadow-md"></div>
                </div>
                
                <h3 class="font-black text-slate-800 text-lg mb-1 leading-tight">{{ $nm }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-6 px-4 truncate w-full">{{ $user->email ?? 'instructor@platform.com' }}</p>
                
                <div class="grid grid-cols-2 gap-3 w-full mb-6">
                    <div class="bg-slate-50 rounded-2xl p-3 flex flex-col items-center justify-center border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">Class Sales</span>
                        <span class="text-sm font-black text-slate-800">{{ $user->linksCount ?? $user->classesSalesCount }}</span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 flex flex-col items-center justify-center border border-slate-100">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-1">Balance</span>
                        <span class="text-sm font-black text-primary">{{ number_format($user->getAccountingBalance()) }}</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2 w-full mt-auto">
                    <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="flex-1 bg-primary text-white text-[10px] font-black uppercase tracking-widest py-3 rounded-2xl shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-95 transition-all text-center no-underline">
                        {{ trans('admin/main.edit') }} profile
                    </a>
                </div>

            </div>
            @endforeach
        </div>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="mt-8 px-6 py-5 bg-white rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
            Showing <span class="text-slate-800">{{ $users->firstItem() }}-{{ $users->lastItem() }}</span> of {{ $users->total() }}
        </p>
        <div class="um-tailwind-pagination">
            {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
    
    <!-- Hints -->
    <div class="mt-12 bg-white/50 backdrop-blur rounded-3xl border border-dashed border-slate-200 p-8">
        <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">lightbulb</span>
            {{ trans('admin/main.hints') }}
        </h5>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="space-y-2">
                <h6 class="text-xs font-black text-primary uppercase tracking-widest">{{ trans('admin/main.instructors_hint_title_1') }}</h6>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">{{ trans('admin/main.instructors_hint_description_1') }}</p>
            </div>
            <div class="space-y-2">
                <h6 class="text-xs font-black text-primary uppercase tracking-widest">{{ trans('admin/main.instructors_hint_title_2') }}</h6>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">{{ trans('admin/main.instructors_hint_description_2') }}</p>
            </div>
            <div class="space-y-2">
                <h6 class="text-xs font-black text-primary uppercase tracking-widest">{{ trans('admin/main.instructors_hint_title_3') }}</h6>
                <p class="text-[11px] text-slate-500 leading-relaxed font-medium">{{ trans('admin/main.instructors_hint_description_3') }}</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts_bottom')
<script>
    function toggleView(view) {
        const viewList = document.getElementById('viewList');
        const viewGrid = document.getElementById('viewGrid');
        const btnList = document.getElementById('btnList');
        const btnGrid = document.getElementById('btnGrid');

        if (view === 'list') {
            viewList.classList.remove('hidden');
            viewGrid.classList.add('hidden');
            btnList.classList.add('bg-primary/10', 'text-primary');
            btnList.classList.remove('text-slate-400');
            btnGrid.classList.remove('bg-primary/10', 'text-primary');
            btnGrid.classList.add('text-slate-400');
            localStorage.setItem('umInstructorView', 'list');
        } else {
            viewGrid.classList.remove('hidden');
            viewList.classList.add('hidden');
            btnGrid.classList.add('bg-primary/10', 'text-primary');
            btnGrid.classList.remove('text-slate-400');
            btnList.classList.remove('bg-primary/10', 'text-primary');
            btnList.classList.add('text-slate-400');
            localStorage.setItem('umInstructorView', 'grid');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const pref = localStorage.getItem('umInstructorView') || 'list';
        toggleView(pref);
    });
</script>
<style>
    .um-tailwind-pagination .pagination { display: flex; gap: 6px; margin: 0; padding: 0; list-style: none; }
    .um-tailwind-pagination .page-item .page-link { 
        display: flex; align-items: center; justify-content: center; 
        width: 36px; height: 36px; border-radius: 12px; border: 1px solid #f1f5f9; 
        color: #64748b; font-size: 13px; font-weight: 800; text-decoration: none; transition: all 0.2s;
    }
    .um-tailwind-pagination .page-item.active .page-link { background: #32A128; color: #fff; border-color: #32A128; box-shadow: 0 4px 12px rgba(50, 161, 40, 0.2); }
    .um-tailwind-pagination .page-item .page-link:hover { background: #f8fafc; color: #32A128; border-color: #32A128; }
</style>
@endpush
