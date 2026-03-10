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
            <div class="size-10 rounded-xl bg-slate-600 flex items-center justify-center text-white shadow-lg shadow-slate-600/20">
                <span class="material-symbols-outlined">auto_stories</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ trans('update.overdue_history') }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Historical Delinquency Records</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ getAdminPanelUrl("/financial/installments/overdue_history/export") }}" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 text-sm font-bold shadow-sm hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">download</span>
                {{ trans('admin/main.export_xls') }}
            </a>
        </div>
    </header>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 font-bold">User</th>
                        <th class="px-6 py-4 font-bold text-left">Plan / ID</th>
                        <th class="px-6 py-4 font-bold text-center">Amount</th>
                        <th class="px-6 py-4 font-bold text-center">Due / Paid</th>
                        <th class="px-6 py-4 font-bold text-center">Resolution</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <!-- User -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $order->user->getAvatar() }}" alt="{{ $order->user->full_name }}" class="size-10 rounded-full border-2 border-slate-100 dark:border-slate-800 object-cover opacity-75">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $order->user->full_name }}</span>
                                        <span class="text-[10px] font-semibold text-slate-400 truncate max-w-[150px]">{{ $order->user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Plan -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-600 dark:text-slate-400">#ORD-{{ $order->id }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ $order->installment->title }}</span>
                                </div>
                            </td>

                            <!-- Pricing -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">
                                        @if($order->amount_type == 'percent')
                                            {{ handlePrice(($order->getItemPrice() * $order->amount) / 100) }}
                                        @else
                                            {{ handlePrice($order->amount) }}
                                        @endif
                                    </span>
                                </div>
                            </td>

                            <!-- Dates -->
                            <td class="px-6 py-4 text-center text-xs">
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-red-500">{{ dateTimeFormat($order->overdue_date, 'j M Y') }}</span>
                                    <span class="font-bold text-emerald-500">{{ !empty($order->paid_at) ? dateTimeFormat($order->paid_at, 'j M Y') : 'UNRESOLVED' }}</span>
                                </div>
                            </td>

                            <!-- Resolution -->
                            <td class="px-6 py-4 text-center">
                                @php
                                    $time = !empty($order->paid_at) ? $order->paid_at : time();
                                    $days = round(($time - $order->overdue_date) / 86400);
                                @endphp
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black {{ $days > 7 ? 'text-red-600' : 'text-amber-600' }}">{{ $days }} Days Late</span>
                                    @if(!empty($order->paid_at))
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-black uppercase rounded mt-1">Settled</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[8px] font-black uppercase rounded mt-1">Open</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/details") }}" class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-all font-bold text-xs flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                        Details
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest">No historical delinquency data available</td>
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
@endsection
