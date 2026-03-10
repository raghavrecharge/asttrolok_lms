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
        .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .select2-container--default .select2-selection--multiple { border-radius: 0.75rem; border-color: #e2e8f0; padding: 2px 8px; }
        .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: #32A128; }
    </style>
@endpush

@section('content')
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 h-full">

    <!-- Title & Header Actions -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">account_balance_wallet</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ $pageTitle }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Financial Oversight Dashboard</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden md:flex bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-1.5 items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-400">
                <span class="size-2 rounded-full @if($pageType == 'requests') bg-amber-500 @else bg-primary @endif"></span>
                @if($pageType == 'requests') Pending Reviews @else Historical Ledger @endif
            </div>
            @can('admin_offline_payments_export_excel')
                <a href="{{ getAdminPanelUrl() }}/financial/offline_payments/excel?{{ http_build_query(request()->all()) }}" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-md shadow-primary/20">
                    <span class="material-symbols-outlined text-sm">download</span>
                    Export Excel
                </a>
            @endcan
        </div>
    </header>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="get" class="mb-0">
            <input type="hidden" name="page_type" value="{{ $pageType }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.search')}}</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
                        <input type="text" class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary focus:border-primary transition-all outline-none" name="search" value="{{ request()->get('search') }}" placeholder="Reference #...">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.start_date')}}</label>
                        <input type="date" class="w-full py-2 px-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary outline-none text-slate-600 dark:text-slate-300" name="from" value="{{ request()->get('from') }}">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.end_date')}}</label>
                        <input type="date" class="w-full py-2 px-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-primary outline-none text-slate-600 dark:text-slate-300" name="to" value="{{ request()->get('to') }}">
                    </div>
                </div>

                @if($pageType == 'history')
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.status')}}</label>
                        <select name="status" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-300 focus:ring-primary outline-none">
                            <option value="">{{trans('admin/main.all_status')}}</option>
                            <option value="approved" @if(request()->get('status') == 'approved') selected @endif>{{trans('admin/main.approved')}}</option>
                            <option value="reject" @if(request()->get('status') == 'reject') selected @endif>{{trans('admin/main.rejected')}}</option>
                        </select>
                    </div>
                @endif

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.role')}}</label>
                    <select name="role_id" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-300 focus:ring-primary outline-none">
                        <option value="">{{trans('admin/main.all_roles')}}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @if($role->id == request()->get('role_id')) selected @endif>{{ $role->caption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.user')}}</label>
                    <select name="user_ids[]" multiple="multiple" class="w-full search-user-select2">
                        @if(!empty($users) and $users->count() > 0)
                            @foreach($users as $user_filter)
                                <option value="{{ $user_filter->id }}" selected>{{ $user_filter->full_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-500 uppercase">{{trans('admin/main.bank')}}</label>
                    <select name="account_type" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-600 dark:text-slate-300 focus:ring-primary outline-none">
                        <option value="">{{trans('admin/main.all_banks')}}</option>
                        @foreach($offlineBanks as $offlineBank)
                            <option value="{{ $offlineBank->id }}" @if(request()->get('account_type') == $offlineBank->id) selected @endif>{{ $offlineBank->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1 flex gap-2 w-full col-span-1 @if($pageType == 'history') lg:col-span-1 @else lg:col-span-2 @endif justify-end">
                    <button class="bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" type="button" onclick="window.location.href='{{ request()->url() }}?page_type={{ $pageType }}'">
                        <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                    </button>
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-xl text-sm font-bold transition-all shadow-md shadow-primary/10 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                        Analyze
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-800 dark:text-white">Transactional Drill-down</h4>
                <p class="text-xs text-slate-400">Verifying offline transfers and wallet topups</p>
            </div>
        </div>
        
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-800/50">
                    <tr class="border-b border-slate-100 dark:border-slate-700">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('admin/main.user')}}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('admin/main.amount')}}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('admin/main.bank')}} / Ref</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('admin/main.phone')}}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('update.attachment')}}</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('admin/main.transaction_time')}}</th>
                        
                        @if($pageType == 'history')
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{trans('admin/main.status')}}</th>
                        @endif
                        
                        @if($pageType == 'requests')
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">{{trans('admin/main.actions')}}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @if($offlinePayments->count() > 0)
                        @foreach($offlinePayments as $offlinePayment)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded shadow-sm bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($offlinePayment->user->full_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $offlinePayment->user->full_name }}</p>
                                            <p class="text-[10px] text-slate-500 font-mono">{{ $offlinePayment->user->role->caption }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-sm font-bold text-slate-800 dark:text-white">
                                    {{ currencySign() }}{{ handlePrice($offlinePayment->amount, false) }}
                                </td>
                                
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ !empty($offlinePayment->offlineBank->title) ? $offlinePayment->offlineBank->title : '-' }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        Ref: {{ $offlinePayment->reference_number }}
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400 font-mono">
                                    {{ $offlinePayment->user->mobile }}
                                </td>
                                
                                <td class="px-6 py-4">
                                    @if(!empty($offlinePayment->attachment))
                                        <a href="{{ $offlinePayment->getAttachmentPath() }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">description</span>
                                            {{ trans('public.view') }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs font-medium">---</span>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4">
                                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">{{ dateTimeFormat($offlinePayment->pay_date, 'd M Y') }}</p>
                                    <p class="text-[10px] text-slate-400">{{ dateTimeFormat($offlinePayment->pay_date, 'H:i') }}</p>
                                </td>
                                
                                @if($pageType == 'history')
                                    <td class="px-6 py-4">
                                        @if($offlinePayment->status == 'approved')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 uppercase tracking-tighter border border-green-200">{{ trans('financial.approved') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-tighter border border-red-200">{{ trans('public.rejected') }}</span>
                                        @endif
                                    </td>
                                @endif
                                
                                @if($pageType == 'requests')
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if($offlinePayment->status == \App\Models\OfflinePayment::$waiting)
                                            @can('admin_offline_payments_approved')
                                                <button onclick="document.getElementById('approveForm-{{ $offlinePayment->id }}').submit();" class="p-1.5 hover:bg-green-100 border border-transparent hover:border-green-200 rounded shadow-none hover:shadow-sm text-green-600 transition-all mr-1" title="{{ trans('financial.approve') }}">
                                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                                </button>
                                                <form id="approveForm-{{ $offlinePayment->id }}" action="{{ getAdminPanelUrl() }}/financial/offline_payments/{{ $offlinePayment->id }}/approved" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="_method" value="DELETE">
                                                </form>
                                            @endcan
                                            @can('admin_offline_payments_reject')
                                                <button onclick="document.getElementById('rejectForm-{{ $offlinePayment->id }}').submit();" class="p-1.5 hover:bg-red-100 border border-transparent hover:border-red-200 rounded shadow-none hover:shadow-sm text-red-500 transition-all" title="{{ trans('public.reject') }}">
                                                    <span class="material-symbols-outlined text-[18px]">cancel</span>
                                                </button>
                                                <form id="rejectForm-{{ $offlinePayment->id }}" action="{{ getAdminPanelUrl() }}/financial/offline_payments/{{ $offlinePayment->id }}/reject" method="POST" style="display: none;">
                                                    @csrf
                                                    <input type="hidden" name="_method" value="DELETE">
                                                </form>
                                            @endcan
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2 text-slate-300">inbox</span>
                                <p>No offline payments found.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20">
            {{ $offlinePayments->appends(request()->input())->links('pagination::tailwind') }}
        </div>
    </div>

    <!-- Hints Section -->
    <div class="bg-primary/5 p-6 rounded-2xl border border-primary/20 mt-8">
        <h5 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 mb-4">
            <span class="material-symbols-outlined text-primary">lightbulb</span>
            {{trans('admin/main.hints')}}
        </h5>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="text-primary mt-0 mb-1 font-bold text-sm">{{trans('admin/main.offline_payment_hint_title_1')}}</div>
                <div class="text-xs text-slate-600 dark:text-slate-400 font-medium leading-relaxed">{{trans('admin/main.offline_payment_hint_description_1')}}</div>
            </div>
            <div>
                <div class="text-primary mt-0 mb-1 font-bold text-sm">{{trans('admin/main.offline_payment_hint_title_2')}}</div>
                <div class="text-xs text-slate-600 dark:text-slate-400 font-medium leading-relaxed">{{trans('admin/main.offline_payment_hint_description_2')}}</div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (jQuery().select2) {
                $('.search-user-select2').select2({
                    placeholder: "Search User",
                    allowClear: true
                });
            }
        });
    </script>
@endpush
