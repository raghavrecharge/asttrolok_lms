@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 h-full min-h-screen">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-600/20">
                <span class="material-symbols-outlined">person_check</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ trans('update.verified_users') }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Pre-Approved Installment Access</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ getAdminPanelUrl("/financial/installments/verified_users/export") }}" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">download</span>
                {{ trans('admin/main.export_xls') }}
            </a>
        </div>
    </header>

    <!-- KPI Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border-l-4 border-emerald-500 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl flex items-center justify-center text-emerald-600">
                <span class="material-symbols-outlined text-3xl font-bold">verified_user</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Verified User Count</p>
                <h3 class="text-2xl font-black text-emerald-600">{{ $users->total() }}</h3>
                <p class="text-[10px] text-emerald-500 font-bold mt-1 uppercase tracking-tighter">Bypassing manual verification</p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border-l-4 border-primary shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-3xl font-bold">payments</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Aggregate Plan Value</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ handlePrice($users->sum('totalAmount')) }}</h3>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 font-bold">Verified User</th>
                        <th class="px-6 py-4 font-bold">Joined / Rank</th>
                        <th class="px-6 py-4 font-bold text-center">Purchases / Plans</th>
                        <th class="px-6 py-4 font-bold text-center">Unpaid / Steps</th>
                        <th class="px-6 py-4 font-bold text-center">Delinquency</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <!-- User -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->full_name }}" class="size-10 rounded-full border-2 border-emerald-100 dark:border-emerald-900/50 object-cover">
                                        <span class="absolute bottom-0 right-0 size-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $user->full_name }}</span>
                                        <span class="text-[10px] font-semibold text-slate-400 truncate max-w-[150px]">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Info -->
                            <td class="px-6 py-4 text-xs">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-600 dark:text-slate-300">{{ dateTimeFormat($user->created_at, 'j M Y') }}</span>
                                    <span class="font-bold text-slate-400 uppercase tracking-tighter">Member Since</span>
                                </div>
                            </td>

                            <!-- Pricing -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ handlePrice($user->getPurchaseAmounts()) }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Val: {{ handlePrice($user->totalAmount) }}</span>
                                </div>
                            </td>

                            <!-- Unpaid -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-700 dark:text-slate-300">{{ $user->unpaidStepsCount }} Unpaid</span>
                                    @if($user->unpaidStepsAmount)
                                        <span class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter">{{ handlePrice($user->unpaidStepsAmount) }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Overdue -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black {{ $user->overdueCount > 0 ? 'text-red-500' : 'text-slate-400' }}">{{ $user->overdueCount }} Overdue</span>
                                    @if($user->overdueAmount)
                                        <span class="text-[10px] font-bold text-red-400 uppercase tracking-tighter">{{ handlePrice($user->overdueAmount) }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-slate-400">
                                    @can('admin_users_edit')
                                        <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="p-2 rounded-lg hover:text-primary hover:bg-primary/10 transition-all" title="Edit User">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                    @endcan
                                    
                                    <div class="relative group/menu inline-block">
                                        <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                        </button>
                                        <div class="absolute right-0 bottom-full mb-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 invisible group-hover/menu:visible opacity-0 group-hover/menu:opacity-100 transition-all">
                                            <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/users/{$user->id}/disable_installment_approval") }}")' class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
                                                <span class="material-symbols-outlined text-sm">cancel</span> Revoke Verification
                                            </a>
                                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                            @can('admin_users_impersonate')
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate" target="_blank" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                                    <span class="material-symbols-outlined text-sm">login</span> Login
                                                </a>
                                            @endcan
                                            @can('admin_support_send')
                                                <a href="{{ getAdminPanelUrl() }}/supports/create?user_id={{ $user->id }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-primary hover:bg-primary/5">
                                                    <span class="material-symbols-outlined text-sm">mail</span> Message
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest">No users currently have pre-approved status</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $users->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

<script>
    function confirmAndSubmit(url) {
        if(confirm('Are you sure you want to revoke verification for this user? They will need to verify again for future plans.')) {
            window.location.href = url;
        }
    }
</script>
@endsection
