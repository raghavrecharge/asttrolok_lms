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
                        "background-light": "#F7F9FC",
                        "background-dark": "#112210",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                        "body": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { display: none !important; }
        .schedule-row { display: none; }
        .schedule-row.active { display: table-row; }
        .expand-btn.active .material-symbols-outlined { transform: rotate(180deg); }
    </style>
@endpush

@section('content')
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 h-full">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Installment Plans</h2>
            <p class="text-sm text-slate-400 mt-1">Monitor and manage student payment schedules and collections.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" placeholder="Search plans or users..." class="pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all shadow-sm w-64">
            </div>
            <button onclick="document.getElementById('createPlanModal').classList.remove('hidden')" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-bold rounded-xl hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-lg">add</span>
                Create Plan
            </button>
            <button class="size-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm">
                <span class="material-symbols-outlined text-lg">notifications</span>
            </button>
        </div>
    </header>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5">
            <div class="size-16 rounded-full border-4 border-red-500 flex items-center justify-center bg-red-50">
                <span class="material-symbols-outlined text-2xl text-red-600" style="font-variation-settings: 'FILL' 1">priority_high</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Overdue Today</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $orders->where('overdue_count', '>', 0)->count() }} Plans</h3>
                <p class="text-[10px] text-red-500 font-semibold mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">warning</span> Action required immediately
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5">
            <div class="size-16 rounded-full border-4 border-orange-400 flex items-center justify-center bg-orange-50">
                <span class="material-symbols-outlined text-2xl text-orange-600" style="font-variation-settings: 'FILL' 1">event_upcoming</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Upcoming (Next 48h)</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $orders->where('upcoming_date', '!=', '')->count() }} Plans</h3>
                <p class="text-[10px] text-orange-500 font-semibold mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">schedule_send</span> Reminders being sent
                </p>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-5">
            <div class="size-16 rounded-full border-4 border-primary flex items-center justify-center bg-primary/5">
                <span class="material-symbols-outlined text-2xl text-primary" style="font-variation-settings: 'FILL' 1">trending_up</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Remaining</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ handlePrice($orders->sum(function($o){ return $o->getCompletePrice(); })) }}</h3>
                <p class="text-[10px] text-primary font-semibold mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">check_circle</span> On track for collection goal
                </p>
            </div>
        </div>
    </div>

    <!-- Tabs & Filters -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
            <button class="px-5 py-2 bg-slate-900 text-white text-xs font-black rounded-lg uppercase tracking-widest">Plans</button>
            <button class="px-5 py-2 text-slate-500 text-xs font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">Schedules</button>
            <button class="px-5 py-2 text-slate-500 text-xs font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2">
                Overdue
                <span class="px-1.5 py-0.5 bg-red-500 text-white text-[9px] font-black rounded-md">{{ $orders->where('overdue_count', '>', 0)->count() }}</span>
            </button>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Quick Trials:</span>
            <button class="px-3 py-1.5 bg-red-500 text-white text-[9px] font-black rounded-lg uppercase tracking-widest">Overdue</button>
            <button class="px-3 py-1.5 bg-orange-500 text-white text-[9px] font-black rounded-lg uppercase tracking-widest">Due Soon</button>
            <button class="px-3 py-1.5 bg-slate-200 text-slate-600 text-[9px] font-black rounded-lg uppercase tracking-widest">Reset</button>
            <div class="w-px h-6 bg-slate-200 mx-2"></div>
            <button class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">filter_list</span> Filters
            </button>
            <button class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 text-slate-600 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">
                <span class="material-symbols-outlined text-sm">view_column</span> Columns
            </button>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left" id="installmentTable">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest w-8"></th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Plan ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Course</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Total / Remaining</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Next Due Date</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($orders as $order)
                        @php
                            $overdueData = $order->getOrderOverdueCountAndAmount();
                            $totalPrice = $order->getCompletePrice();
                            $paidAmount = 0;
                            $steps = $order->installment ? $order->installment->steps : collect();
                            $itemPrice = $order->getItemPrice();
                            
                            foreach($steps as $step) {
                                $payment = \App\Models\InstallmentOrderPayment::where('installment_order_id', $order->id)
                                    ->where('step_id', $step->id)
                                    ->where('status', 'paid')
                                    ->first();
                                if ($payment) {
                                    $paidAmount += $step->getPrice($itemPrice);
                                }
                            }
                            $remainingAmount = $totalPrice - $paidAmount;
                            $progressPct = $totalPrice > 0 ? ($paidAmount / $totalPrice) * 100 : 0;
                            
                            $statusLabel = 'ACTIVE';
                            $statusClass = 'bg-slate-100 text-slate-500';
                            if ($overdueData['count'] > 0) {
                                $overdueDays = round($order->overdueDaysPast());
                                if ($overdueDays > 0) {
                                    $statusLabel = 'OVERDUE';
                                    $statusClass = 'bg-red-100 text-red-600';
                                } else {
                                    $statusLabel = 'DUE';
                                    $statusClass = 'bg-orange-100 text-orange-600';
                                }
                            } elseif ($order->isCompleted()) {
                                $statusLabel = 'PAID';
                                $statusClass = 'bg-emerald-100 text-emerald-600';
                            } elseif ($order->status == 'open') {
                                if (!empty($order->upcoming_date)) {
                                    $statusLabel = 'DUE';
                                    $statusClass = 'bg-orange-100 text-orange-600';
                                } else {
                                    $statusLabel = 'ACTIVE';
                                    $statusClass = 'bg-sky-100 text-sky-600';
                                }
                            }
                            
                            $item = $order->getItem();
                            $itemTitle = $item ? $item->title : 'N/A';
                        @endphp
                        <!-- Main Row -->
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors group cursor-pointer" onclick="toggleSchedule('schedule-{{ $order->id }}', this)">
                            <td class="pl-6 py-4">
                                <button class="expand-btn size-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center transition-transform hover:bg-primary/10 hover:text-primary">
                                    <span class="material-symbols-outlined text-lg transition-transform">expand_more</span>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary text-[10px] font-black">
                                        {{ strtoupper(substr($order->user->full_name ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="text-sm font-black text-slate-800 dark:text-white">INST-{{ $order->id }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $order->user->full_name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $order->user->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 truncate max-w-[200px]">{{ $itemTitle }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $order->installment->title ?? '' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ handlePrice($totalPrice) }}</span>
                                    @if($remainingAmount > 0)
                                        <span class="text-sm font-bold text-orange-500">{{ handlePrice($remainingAmount) }} Left</span>
                                    @else
                                        <span class="text-sm font-bold text-emerald-500">{{ handlePrice(0) }} Left</span>
                                    @endif
                                </div>
                                <div class="w-24 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden mx-auto mt-2">
                                    <div class="h-full bg-primary rounded-full transition-all" style="width: {{ min($progressPct, 100) }}%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if(!empty($order->upcoming_date))
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $order->upcoming_date }}</span>
                                        <span class="text-[10px] font-bold {{ ($order->days_left ?? 999) < 3 ? 'text-red-500' : 'text-orange-500' }} uppercase tracking-widest">
                                            @if(isset($order->days_left))
                                                in {{ $order->days_left }} days
                                            @endif
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full {{ $statusClass }} text-[9px] font-black uppercase tracking-widest">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-xs font-bold text-primary cursor-pointer hover:underline">VIEW SCHEDULE</span>
                                    @can('admin_installments_orders')
                                        <a href="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/details") }}" class="size-8 rounded-lg bg-slate-50 border border-slate-100 text-slate-400 hover:text-primary flex items-center justify-center transition-all" onclick="event.stopPropagation()">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Expandable Schedule Breakdown -->
                        <tr class="schedule-row" id="schedule-{{ $order->id }}">
                            <td colspan="8" class="px-0 py-0">
                                <div class="bg-slate-50/80 dark:bg-slate-800/30 border-t border-b border-slate-100 dark:border-slate-800 px-12 py-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                                            <span class="material-symbols-outlined text-primary text-lg">receipt_long</span>
                                            Installment Schedule Breakdown
                                        </h4>
                                        <a href="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/details") }}" class="text-[10px] font-black text-primary uppercase tracking-widest hover:underline flex items-center gap-1">
                                            View Transaction Log
                                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                        </a>
                                    </div>
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 dark:border-slate-700">
                                                <th class="pb-3 px-4">Sequence</th>
                                                <th class="pb-3 px-4">Due Date</th>
                                                <th class="pb-3 px-4">Amount</th>
                                                <th class="pb-3 px-4">Transaction ID</th>
                                                <th class="pb-3 px-4">Method</th>
                                                <th class="pb-3 px-4 text-right">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                            @php $seqNum = 0; @endphp
                                            @foreach($steps as $step)
                                                @php
                                                    $seqNum++;
                                                    $stepPayment = \App\Models\InstallmentOrderPayment::where('installment_order_id', $order->id)
                                                        ->where('step_id', $step->id)
                                                        ->first();
                                                    $stepPrice = $step->getPrice($itemPrice);
                                                    $dueAt = ($step->deadline * 86400) + $order->created_at;
                                                    $dueDate = date('M d, Y', $dueAt);
                                                    $isPaid = $stepPayment && $stepPayment->status === 'paid';
                                                    $isOverdue = !$isPaid && time() > $dueAt;
                                                    $isFuture = !$isPaid && time() <= $dueAt;
                                                    
                                                    $ordinalSuffix = match($seqNum) {
                                                        1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
                                                    };
                                                @endphp
                                                <tr class="hover:bg-white/50 transition-colors">
                                                    <td class="py-4 px-4">
                                                        <span class="text-sm font-bold text-primary">{{ $seqNum }}{{ $ordinalSuffix }} Installment</span>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $dueDate }}</span>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        <span class="text-sm font-black text-slate-800 dark:text-white">{{ handlePrice($stepPrice) }}</span>
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        @if($isPaid && $stepPayment->sale_id)
                                                            <span class="text-xs font-medium text-slate-500">TXN-{{ $stepPayment->sale_id }}</span>
                                                        @else
                                                            <span class="text-slate-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-4 px-4">
                                                        @if($isPaid)
                                                            <span class="text-xs font-medium text-slate-600">Credit Card</span>
                                                        @else
                                                            <span class="text-slate-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-4 px-4 text-right">
                                                        @if($isPaid)
                                                            <div class="flex items-center justify-end gap-2">
                                                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-600 text-[9px] font-black rounded-md uppercase tracking-widest">Paid</span>
                                                                <span class="text-xs font-bold text-primary cursor-pointer hover:underline">Receipt</span>
                                                            </div>
                                                        @elseif($isOverdue)
                                                            <div class="flex items-center justify-end gap-2">
                                                                <span class="px-2.5 py-1 bg-red-100 text-red-600 text-[9px] font-black rounded-md uppercase tracking-widest">Overdue</span>
                                                                <button class="px-3 py-1 bg-orange-500 text-white text-[9px] font-black rounded-md uppercase tracking-widest hover:bg-orange-600 transition-all">Remind</button>
                                                            </div>
                                                        @elseif($isFuture)
                                                            <div class="flex items-center justify-end gap-2">
                                                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 text-[9px] font-black rounded-md uppercase tracking-widest">Future</span>
                                                                <span class="text-xs font-medium text-slate-400 cursor-pointer hover:text-primary">Actions</span>
                                                            </div>
                                                        @else
                                                            <div class="flex items-center justify-end gap-2">
                                                                <span class="px-2.5 py-1 bg-orange-100 text-orange-600 text-[9px] font-black rounded-md uppercase tracking-widest">Due</span>
                                                                <button class="px-3 py-1 bg-orange-500 text-white text-[9px] font-black rounded-md uppercase tracking-widest hover:bg-orange-600 transition-all">Remind</button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-500">Rows per page:</span>
                    <select class="bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 px-2 py-1 focus:ring-0">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-slate-500">{{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }} records</span>
                    <div class="flex items-center gap-1">
                        @if($orders->onFirstPage())
                            <span class="size-8 rounded-lg bg-slate-50 text-slate-300 flex items-center justify-center cursor-not-allowed"><span class="material-symbols-outlined text-lg">chevron_left</span></span>
                        @else
                            <a href="{{ $orders->previousPageUrl() }}" class="size-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 transition-all"><span class="material-symbols-outlined text-lg">chevron_left</span></a>
                        @endif
                        @if($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}" class="size-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 transition-all"><span class="material-symbols-outlined text-lg">chevron_right</span></a>
                        @else
                            <span class="size-8 rounded-lg bg-slate-50 text-slate-300 flex items-center justify-center cursor-not-allowed"><span class="material-symbols-outlined text-lg">chevron_right</span></span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    function toggleSchedule(id, row) {
        const scheduleRow = document.getElementById(id);
        const btn = row.querySelector('.expand-btn');
        if (scheduleRow) {
            scheduleRow.classList.toggle('active');
            btn.classList.toggle('active');
        }
    }
</script>
@endsection

{{-- CREATE INSTALLMENT PLAN MODAL --}}
<div id="createPlanModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-8 relative">
        <button onclick="document.getElementById('createPlanModal').classList.add('hidden')" class="absolute top-4 right-4 size-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-all">
            <span class="material-symbols-outlined">close</span>
        </button>

        <h3 class="text-xl font-black text-slate-800 dark:text-white">Create Installment Plan</h3>
        <p class="text-xs text-slate-400 mt-1">Set up a new payment schedule for a student.</p>

        <form action="{{ getAdminPanelUrl('/financial/installments/purchases/store') }}" method="post" class="mt-8 space-y-6">
            @csrf
            
            {{-- Student & Course --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Student Name</label>
                    <select name="user_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 search-user-select2">
                        {{-- Select2 handles this --}}
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Target Course</label>
                    <select name="webinar_id" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 search-webinar-select2">
                        {{-- Select2 handles this --}}
                    </select>
                </div>
            </div>

            {{-- Financials --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Down Payment</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
                        <input type="number" name="upfront" value="0.00" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold h-11 pl-8 focus:ring-4 focus:ring-primary/10">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Installments</label>
                    <input type="number" name="count" value="2" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Interval (Days)</label>
                    <input type="number" name="interval" value="30" class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Internal Tracking Ref / Notes</label>
                <textarea name="notes" placeholder="e.g. Special scholarship case..." class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl text-sm font-medium p-4 focus:ring-4 focus:ring-primary/10 min-h-[100px]"></textarea>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="document.getElementById('createPlanModal').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-bold hover:bg-slate-200 transition-all">Cancel</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span> Create Plan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (jQuery().select2) {
                $('.search-user-select2').select2({
                    placeholder: "Search Student...",
                    dropdownParent: $('#createPlanModal'),
                    ajax: {
                        url: '{{ getAdminPanelUrl('/users/search') }}',
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: $.map(data.users, function (item) {
                                    return { text: item.full_name, id: item.id }
                                })
                            };
                        }
                    }
                });
                $('.search-webinar-select2').select2({
                    placeholder: "Search Course...",
                    dropdownParent: $('#createPlanModal'),
                    ajax: {
                        url: '{{ getAdminPanelUrl('/webinars/search') }}',
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: $.map(data.webinars, function (item) {
                                    return { text: item.title, id: item.id }
                                })
                            };
                        }
                    }
                });
            }
        });
    </script>
@endpush

