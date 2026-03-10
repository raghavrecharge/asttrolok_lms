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
                    "display": ["Inter", "sans-serif"]
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "2xl": "12px",
                    "full": "9999px"
                },
            },
        },
    }
</script>
<style>
    /* Scope that font family so it primarily affects our new container */
    .um-page-container { font-family: 'Inter', sans-serif; }
    .um-page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    
    /* Hide platform default cards that clutter the top */
    .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { display: none !important; }
    
    /* Compact form sections */
    .compact-form-card {
        @apply bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden p-4;
    }
    .compact-section-heading {
        @apply text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-800;
    }
    .compact-grid {
        @apply grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4;
    }
    .compact-form-row {
        @apply grid grid-cols-1 md:grid-cols-2 gap-4;
    }
    .compact-field {
        @apply space-y-1;
    }
    
    /* Improve table layout and text handling */
    .um-page-container table { table-layout: fixed; }
    .um-page-container td { vertical-align: middle; }
    .um-page-container .user-info-cell { 
        max-width: 256px; 
        word-wrap: break-word; 
        hyphens: auto; 
    }
    
    /* Adjust default Laravel pagination to look OK */
    .um-tailwind-pagination .pagination { display: flex; gap: 4px; margin: 0; padding: 0; list-style: none; }
    .um-tailwind-pagination .page-item .page-link { 
        display: flex; align-items: center; justify-content: center; 
        width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; 
        color: #475569; font-size: 14px; font-weight: 500; text-decoration: none; padding: 0; 
    }
    .um-tailwind-pagination .page-item.active .page-link { background: #32A128; color: #fff; border-color: #32A128; }
    .um-tailwind-pagination .page-item.disabled .page-link { opacity: 0.5; pointer-events: none; }
</style>
@endpush

@section('content')
<div class="um-page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased h-full flex flex-col p-4 md:p-8">

    @include('admin.includes.filter_header', [
        'title' => 'Users Management',
        'subtitle' => 'Manage and monitor platform learners and instructors (' . $users->total() . ' Total)',
        'actions' => [
            '<div class="flex items-center bg-white border border-slate-200 rounded-2xl p-1 shadow-sm">
                <button type="button" onclick="toggleView(\'grid\')" id="btnGrid" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 transition-all" title="Grid View">
                    <span class="material-symbols-outlined text-xl block">grid_view</span>
                </button>
                <button type="button" onclick="toggleView(\'list\')" id="btnList" class="p-2 rounded-xl bg-primary text-white shadow-lg shadow-primary/20 transition-all" title="List View">
                    <span class="material-symbols-outlined text-xl block">format_list_bulleted</span>
                </button>
            </div>',
            Gate::allows('admin_users_create') ? '
            <a href="' . getAdminPanelUrl() . '/users/create" class="bg-primary hover:bg-primary/90 text-white font-bold h-[50px] px-6 rounded-2xl flex items-center gap-2 transition-all shadow-lg shadow-primary/20 shrink-0 no-underline">
                <span class="material-symbols-outlined text-xl">add</span>
                Add New User
            </a>' : ''
        ]
    ])

    <!-- Main Content Area -->
    <div id="viewList" class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto min-w-[1200px]">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-100">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">User ID</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] w-64">User Info</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] w-80">Email</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Role</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] text-center">Balance</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] text-center">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Joined Date</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em] text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($users as $user)
                    @php
                        $walB = 0;
                        try { $walB = app(\App\Services\PaymentEngine\WalletService::class)->balance($user->id); } catch(\Exception $e) {}
                        
                        $sc = 'bg-emerald-100 text-emerald-600'; $sl='ACTIVE';
                        if($user->ban && !empty($user->ban_end_at) && $user->ban_end_at > time()) { $sc='bg-rose-100 text-rose-600'; $sl='SUSPENDED'; }
                        elseif($user->status == 'inactive') { $sc='bg-amber-100 text-amber-600'; $sl='PENDING'; }
                        
                        $rc = 'bg-indigo-50 text-indigo-500'; $rn='Learner';
                        if($user->role_name=='teacher') { $rc='bg-purple-50 text-purple-500'; $rn='Instructor'; }
                        elseif($user->role_name=='admin') { $rc='bg-slate-100 text-slate-500'; $rn='Admin'; }
                        
                        $nm = $user->full_name ?? 'User';
                        $parts = explode(' ', trim($nm));
                        $ini = strtoupper(substr($parts[0],0,1) . (count($parts)>1?substr($parts[count($parts)-1],0,1):''));
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-4 text-xs font-black text-slate-400">#AST-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-8 py-4 user-info-cell">
                            <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="flex items-center gap-4 group no-underline">
                                <div class="size-10 rounded-[14px] bg-slate-100 flex flex-shrink-0 items-center justify-center overflow-hidden text-slate-400 font-black text-sm shadow-sm ring-4 ring-white group-hover:ring-primary/20 transition-all">
                                    @if($user->avatar && !str_contains($user->avatar, 'default_profile') && !str_contains($user->avatar, 'default-avatar'))
                                        <img src="{{ $user->getAvatar() }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        {{ $ini }}
                                    @endif
                                </div>
                                <span class="text-sm font-black text-slate-800 group-hover:text-primary transition-colors">{{ $nm }}</span>
                            </a>
                        </td>
                        <td class="px-8 py-4 text-sm font-medium text-slate-400">{{ $user->email ?? '-' }}</td>
                        <td class="px-8 py-4"><span class="px-3 py-1 rounded-xl {{ $rc }} text-[10px] font-black uppercase tracking-widest">{{ $rn }}</span></td>
                        <td class="px-8 py-4 text-sm font-black text-slate-800 text-center">{{ number_format($walB) }}</td>
                        <td class="px-8 py-4 text-center"><span class="px-3 py-1 rounded-xl {{ $sc }} text-[10px] font-black uppercase tracking-widest">{{ $sl }}</span></td>
                        <td class="px-8 py-4 text-sm font-bold text-slate-500">{{ $user->created_at ? date('M d, Y', strtotime($user->created_at)) : '-' }}</td>
                        <td class="px-8 py-4 text-center">
                            @include('admin.includes.user_action_dropdown', ['user' => $user])
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grid View -->
    <div id="viewGrid" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($users as $user)
            @php
                $walB = 0;
                try { $walB = app(\App\Services\PaymentEngine\WalletService::class)->balance($user->id); } catch(\Exception $e) {}
                
                $sd = 'bg-green-100 text-green-800 border-white'; $sdi='check'; $sbc='border-primary/20 group-hover:border-primary/50';
                if($user->ban && !empty($user->ban_end_at) && $user->ban_end_at > time()) { $sd='bg-red-100 text-red-800 border-white'; $sdi='close'; $sbc='border-red-200 group-hover:border-red-400'; }
                elseif($user->status == 'inactive') { $sd='bg-orange-100 text-orange-800 border-white'; $sdi='schedule'; $sbc='border-orange-200 group-hover:border-orange-400'; }
                
                $rc = 'bg-blue-100 text-blue-600'; $rn='Learner';
                if($user->role_name=='teacher') { $rc='bg-primary/10 text-primary'; $rn='Instructor'; }
                elseif($user->role_name=='admin') { $rc='bg-slate-100 text-slate-600'; $rn='Admin'; }
                
                $nm = $user->full_name ?? 'User';
                $parts = explode(' ', trim($nm));
                $ini = strtoupper(substr($parts[0],0,1) . (count($parts)>1?substr($parts[count($parts)-1],0,1):''));
            @endphp
            <div class="group bg-white rounded-xl p-5 shadow-sm border border-slate-200 transition-all hover:shadow-md hover:border-primary/30 relative flex flex-col items-center text-center">
                <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                    @include('admin.includes.user_action_dropdown', ['user' => $user])
                </div>
                
                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="no-underline group/profile flex flex-col items-center">
                    <div class="relative mb-4">
                        <div class="size-20 rounded-full p-1 border-2 {{ $sbc }} transition-colors group-hover/profile:border-primary">
                            @if($user->avatar && !str_contains($user->avatar, 'default_profile') && !str_contains($user->avatar, 'default-avatar'))
                                <img src="{{ $user->getAvatar() }}" class="w-full h-full rounded-full object-cover">
                            @else
                                <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xl">{{ $ini }}</div>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1">
                            <span class="flex size-6 items-center justify-center rounded-full {{ $sd }} border-2 shadow-sm">
                                <span class="material-symbols-outlined text-sm font-bold">{{ $sdi }}</span>
                            </span>
                        </div>
                    </div>
                    
                    <h3 class="font-bold text-slate-900 mb-1 group-hover/profile:text-primary transition-colors">{{ $nm }}</h3>
                    <p class="text-xs text-slate-500 mb-3 w-full px-4 truncate">{{ $user->email ?? 'no-email@platform.com' }}</p>
                </a>
                
                <div class="flex gap-2 mb-4">
                    <span class="px-2 py-0.5 {{ $rc }} rounded-full text-[10px] font-bold uppercase tracking-wider">{{ $rn }}</span>
                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full text-[10px] font-bold uppercase tracking-wider">#AST-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                
                <div class="w-full pt-4 mt-auto border-t border-slate-100 flex justify-between items-center">
                    <div class="text-left">
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Wallet</p>
                        <p class="text-sm font-bold text-slate-900">₹{{ number_format($walB) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase font-semibold text-slate-400">Joined</p>
                        <p class="text-sm font-medium text-slate-600">{{ $user->created_at ? date('M d, Y', strtotime($user->created_at)) : '-' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
    {{-- Pagination Wrapper --}}
    @if($users->hasPages())
    <div class="mt-6 px-4 py-4 bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <p class="text-sm text-slate-600">Showing <span class="font-bold">{{ $users->firstItem() }}-{{ $users->lastItem() }}</span> of <span class="font-bold">{{ $users->total() }}</span> users</p>
        <div class="um-tailwind-pagination flex items-center gap-2">
            {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
    
</div>
@endsection

@push('scripts_bottom')
<script>
    // View preference logic
    const viewList = document.getElementById('viewList');
    const viewGrid = document.getElementById('viewGrid');
    const btnList = document.getElementById('btnList');
    const btnGrid = document.getElementById('btnGrid');

    function toggleView(view) {
        if (view === 'list') {
            viewList.classList.remove('hidden');
            viewGrid.classList.add('hidden');
            
            btnList.classList.add('bg-primary/10', 'text-primary');
            btnList.classList.remove('text-slate-400', 'hover:text-slate-600');
            
            btnGrid.classList.add('text-slate-400', 'hover:text-slate-600');
            btnGrid.classList.remove('bg-primary/10', 'text-primary');
            
            localStorage.setItem('umViewPreference', 'list');
        } else {
            viewGrid.classList.remove('hidden');
            viewList.classList.add('hidden');
            
            btnGrid.classList.add('bg-primary/10', 'text-primary');
            btnGrid.classList.remove('text-slate-400', 'hover:text-slate-600');
            
            btnList.classList.add('text-slate-400', 'hover:text-slate-600');
            btnList.classList.remove('bg-primary/10', 'text-primary');
            
            localStorage.setItem('umViewPreference', 'grid');
        }
    }

    // Load preference on mount
    document.addEventListener('DOMContentLoaded', () => {
        const pref = localStorage.getItem('umViewPreference') || 'list';
        toggleView(pref);
    });
</script>

<style>
/* Reset some Bootstrap leaking into the tailwind space */
.um-page-container * { box-sizing: border-box; }
.um-page-container input:focus, .um-page-container select:focus { outline: none !important; box-shadow: 0 0 0 2px rgba(50, 161, 40, 0.2) !important; border-color: #32A128 !important; }

/* Improve table layout and text handling */
.um-page-container table { table-layout: fixed; }
.um-page-container td { vertical-align: middle; }
.um-page-container .user-info-cell { max-width: 256px; word-wrap: break-word; hyphens: auto; }

/* Adjust default Laravel pagination to look OK */
.um-tailwind-pagination .pagination { display: flex; gap: 4px; margin: 0; padding: 0; list-style: none; }
.um-tailwind-pagination .page-item .page-link { 
    display: flex; align-items: center; justify-content: center; 
    width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; 
    color: #475569; font-size: 14px; font-weight: 500; text-decoration: none; padding: 0; 
}
.um-tailwind-pagination .page-item.active .page-link { background: #32A128; color: #fff; border-color: #32A128; }
.um-tailwind-pagination .page-item.disabled .page-link { opacity: 0.5; pointer-events: none; }
</style>
@endpush
