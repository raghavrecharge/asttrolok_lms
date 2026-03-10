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
            <div class="size-10 rounded-xl bg-red-600 flex items-center justify-center text-white shadow-lg shadow-red-600/20">
                <span class="material-symbols-outlined font-bold">priority_high</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ trans('update.overdue_installments') }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Active Delinquency Monitoring</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ getAdminPanelUrl("/financial/installments/overdue/export") }}" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">download</span>
                {{ trans('admin/main.export_xls') }}
            </a>
        </div>
    </header>

    <!-- KPI Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border-l-4 border-red-500 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-red-50 dark:bg-red-900/20 rounded-xl flex items-center justify-center text-red-600">
                <span class="material-symbols-outlined text-3xl font-bold">error</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Delinquent Items</p>
                <h3 class="text-2xl font-black text-red-600">{{ $orders->total() }}</h3>
                <p class="text-[10px] text-red-500 font-bold mt-1 flex items-center gap-1 uppercase tracking-tighter">
                    Requiring immediate follow-up
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border-l-4 border-amber-500 shadow-sm flex items-center gap-5">
            <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-xl flex items-center justify-center text-amber-600">
                <span class="material-symbols-outlined text-3xl font-bold">history</span>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Earliest Delinquency</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white">
                    {{ $orders->min('overdue_date') ? dateTimeFormat($orders->min('overdue_date'), 'j M Y') : 'N/A' }}
                </h3>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 font-bold">User</th>
                        <th class="px-6 py-4 font-bold text-left">Plan / ID</th>
                        <th class="px-6 py-4 font-bold text-center">Product Path</th>
                        <th class="px-6 py-4 font-bold text-center">Overdue Amount</th>
                        <th class="px-6 py-4 font-bold text-center">Missed Date</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-red-50/20 dark:hover:bg-red-900/10 transition-colors group">
                            <!-- User -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <img src="{{ $order->user->getAvatar() }}" alt="{{ $order->user->full_name }}" class="size-10 rounded-full border-2 border-red-100 dark:border-red-900/50 object-cover">
                                        <span class="absolute -top-1 -right-1 size-4 bg-red-600 text-white flex items-center justify-center rounded-full text-[8px] font-black border-2 border-white dark:border-slate-900">!</span>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $order->user->full_name }}</span>
                                        <span class="text-[10px] font-semibold text-red-500 truncate max-w-[150px] uppercase tracking-tighter">{{ $order->user->mobile }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Plan -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">#ORD-{{ $order->id }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $order->installment->title }}</span>
                                </div>
                            </td>

                            <!-- Product -->
                            <td class="px-6 py-4 text-center">
                                @if(!empty($order->webinar_id))
                                    <div class="flex flex-col items-center">
                                        <a href="{{ !empty($order->webinar) ? $order->webinar->getUrl() : '#' }}" target="_blank" class="text-xs font-bold text-primary hover:underline truncate max-w-[150px]">{{ !empty($order->webinar) ? $order->webinar->title : 'Course' }}</a>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Webinar</span>
                                    </div>
                                @elseif(!empty($order->bundle_id))
                                    <div class="flex flex-col items-center">
                                        <a href="{{ !empty($order->bundle) ? $order->bundle->getUrl() : '#' }}" target="_blank" class="text-xs font-bold text-primary hover:underline truncate max-w-[150px]">{{ !empty($order->bundle) ? $order->bundle->title : 'Bundle' }}</a>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Bundle</span>
                                    </div>
                                @else
                                    <span class="text-xs font-medium text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Pricing -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-red-600">
                                        @if($order->amount_type == 'percent')
                                            {{ handlePrice(($order->getItemPrice() * $order->amount) / 100) }}
                                        @else
                                            {{ handlePrice($order->amount) }}
                                        @endif
                                    </span>
                                    @if($order->amount_type == 'percent')
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">({{ $order->amount }}% of Item)</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Missed Date -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-xs font-black text-red-500">{{ dateTimeFormat($order->overdue_date, 'j M Y') }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ dateTimeFormatForHumans($order->overdue_date,true,null,1) }}</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/details") }}" class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-all" title="View Details">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    
                                    <div class="relative group/menu inline-block">
                                        <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                            <span class="material-symbols-outlined text-[20px] text-slate-400">more_vert</span>
                                        </button>
                                        <div class="absolute right-0 bottom-full mb-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 invisible group-hover/menu:visible opacity-0 group-hover/menu:opacity-100 transition-all">
                                            <a href="{{ getAdminPanelUrl() }}/supports/create?user_id={{ $order->user_id }}" target="_blank" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-primary hover:bg-primary/5">
                                                <span class="material-symbols-outlined text-sm">mail</span> Send Reminder
                                            </a>
                                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                            <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/cancel") }}")' class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
                                                <span class="material-symbols-outlined text-sm">cancel</span> Cancel Order
                                            </a>
                                            <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/refund") }}")' class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
                                                <span class="material-symbols-outlined text-sm">assignment_return</span> Refund Order
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">No overdue installments currently flagged</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $orders->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

<script>
    function confirmAndSubmit(url) {
        if(confirm('This action is serious and may impact user access. Proceed?')) {
            window.location.href = url;
        }
    }
</script>
@endsection
