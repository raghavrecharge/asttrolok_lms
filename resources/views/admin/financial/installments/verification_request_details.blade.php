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
    </style>
@endpush

@section('content')
@php
    $item = $order->getItem();
    $itemTitle = $item ? $item->title : 'N/A';
    $itemType = $order->getItemType() ?? 'course';
    $totalPrice = $order->getCompletePrice();
    $paidAmount = 0;
    $steps = $installment ? $installment->steps : collect();
    
    foreach($steps as $step) {
        $sp = $payments->where('step_id', $step->id)->where('status', 'paid')->first();
        if ($sp) $paidAmount += $step->getPrice($itemPrice);
    }
    if (!empty($installment->upfront)) {
        $upfrontPay = $payments->where('type', 'upfront')->first();
        if ($upfrontPay) $paidAmount += $installment->getUpfront($itemPrice);
    }
    $remainingAmount = max(0, $totalPrice - $paidAmount);
    $progressPct = $totalPrice > 0 ? round(($paidAmount / $totalPrice) * 100) : 0;
    $paidSteps = 0;
    $totalSteps = count($steps);
    foreach($steps as $step) {
        if ($payments->where('step_id', $step->id)->where('status', 'paid')->first()) $paidSteps++;
    }
    
    $names = explode(' ', $order->user->full_name);
    $initials = (isset($names[0][0]) ? strtoupper($names[0][0]) : '') . (isset($names[1][0]) ? strtoupper($names[1][0]) : '');
    
    $statusBadge = match($order->status) {
        'open' => ['bg-emerald-100 text-emerald-600', 'ACTIVE'],
        'pending_verification' => ['bg-amber-100 text-amber-600', 'PENDING'],
        'canceled' => ['bg-rose-100 text-rose-600', 'CANCELLED'],
        'refunded' => ['bg-slate-100 text-slate-500', 'REFUNDED'],
        default => ['bg-sky-100 text-sky-600', strtoupper($order->status)]
    };
    $overdueData = $order->getOrderOverdueCountAndAmount();
    if ($overdueData['count'] > 0) {
        $statusBadge = ['bg-red-100 text-red-600', 'DUE'];
    }
@endphp

<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-6 h-full">

    <!-- Breadcrumbs & Header -->
    <header class="space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-400">
            <a href="{{ getAdminPanelUrl('/financial/installments') }}" class="hover:text-primary transition-colors no-underline">Management</a>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <a href="{{ getAdminPanelUrl('/financial/installments/purchases') }}" class="hover:text-primary transition-colors no-underline">Installments</a>
            <span class="material-symbols-outlined text-sm">chevron_right</span>
            <span class="text-primary">INST-{{ $order->id }}</span>
        </div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl('/financial/installments/purchases') }}" class="size-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary transition-all shadow-sm no-underline">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-2xl font-black tracking-tight text-slate-800 dark:text-white">Plan Details</h1>
            </div>
            <div class="flex items-center gap-3">
                <button class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                    <span class="material-symbols-outlined text-lg">download</span> Export PDF
                </button>
                <a href="{{ getAdminPanelUrl("/financial/installments/{$order->installment_id}/edit") }}" class="flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-bold hover:bg-slate-800 transition-all shadow-lg">
                    <span class="material-symbols-outlined text-lg">edit</span> Edit Plan
                </a>
            </div>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT SIDEBAR: Student Card + Financial Summary -->
        <div class="space-y-6">

            <!-- Student Profile Card with Green Gradient -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="h-32 bg-gradient-to-br from-primary via-emerald-500 to-teal-400 relative rounded-t-2xl">
                    <div class="absolute bottom-0 left-6 translate-y-1/2">
                        <div class="size-16 rounded-full bg-white flex items-center justify-center text-lg font-black text-primary shadow-xl border-4 border-white">
                            {{ $initials ?: 'U' }}
                        </div>
                    </div>
                </div>
                <div class="pt-12 pb-6 px-6">
                    <h3 class="text-lg font-black text-slate-800">{{ $order->user->full_name }}</h3>
                    <p class="text-primary text-xs font-bold mt-0.5">Student ID: AM-{{ $order->user->id }}</p>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Email</span>
                            <span class="font-bold text-slate-700 text-xs">{{ $order->user->email }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Course</span>
                            <span class="font-bold text-slate-700 text-xs truncate max-w-[160px]">{{ $itemTitle }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span>
                            <span class="px-2.5 py-0.5 rounded-full {{ $statusBadge[0] }} text-[9px] font-black uppercase tracking-widest">{{ $statusBadge[1] }}</span>
                        </div>
                    </div>

                    <!-- Quick Action Icons -->
                    <div class="flex items-center gap-3 mt-6 pt-4 border-t border-slate-100">
                        <button class="size-11 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-all">
                            <span class="material-symbols-outlined text-lg">mail</span>
                        </button>
                        <button class="size-11 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-all">
                            <span class="material-symbols-outlined text-lg">call</span>
                        </button>
                        <button class="size-11 rounded-full border border-slate-200 flex items-center justify-center text-slate-400 hover:text-primary hover:border-primary transition-all">
                            <span class="material-symbols-outlined text-lg">chat</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <h4 class="text-[10px] font-black text-primary uppercase tracking-widest">Financial Summary</h4>

                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold text-slate-600">Payment Progress</span>
                    <span class="text-sm font-black text-slate-800">{{ $progressPct }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all" style="width: {{ $progressPct }}%"></div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="bg-slate-50 rounded-xl p-4 text-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total</p>
                        <p class="text-lg font-black text-slate-800 mt-1">{{ handlePrice($totalPrice) }}</p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4 text-center">
                        <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Paid</p>
                        <p class="text-lg font-black text-emerald-600 mt-1">{{ handlePrice($paidAmount) }}</p>
                    </div>
                </div>

                <div class="bg-rose-50 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-rose-500 uppercase tracking-widest">Remaining Balance</p>
                        <p class="text-xl font-black text-rose-600 mt-1">{{ handlePrice($remainingAmount) }}</p>
                    </div>
                    <span class="material-symbols-outlined text-rose-400 text-2xl">trending_up</span>
                </div>
            </div>

            <!-- Verification Actions -->
            @if($order->status == "pending_verification")
            <div class="bg-white rounded-2xl border-2 border-primary/20 p-6 shadow-xl shadow-primary/5 space-y-3">
                <h3 class="text-sm font-black flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">verified</span>
                    Pending Approval
                </h3>
                <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/approve") }}")' class="w-full py-3 rounded-xl bg-primary text-white font-black text-sm flex items-center justify-center gap-2 hover:bg-primary/90 transition-all shadow-lg shadow-primary/20 no-underline">
                    <span class="material-symbols-outlined text-lg">check_circle</span> Approve
                </a>
                <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/reject") }}")' class="w-full py-3 rounded-xl bg-slate-100 text-red-600 font-black text-sm flex items-center justify-center gap-2 hover:bg-red-50 transition-all no-underline">
                    <span class="material-symbols-outlined text-lg">cancel</span> Reject
                </a>
            </div>
            @endif

            <!-- Uploaded Files -->
            @if($attachments->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">attach_file</span> Uploaded Files
                </h4>
                <div class="space-y-3">
                    @foreach($attachments as $attachment)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-primary/30 transition-all">
                        <span class="text-xs font-bold text-slate-700 truncate">{{ $attachment->title }}</span>
                        <a href="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/attachments/{$attachment->id}/download") }}" class="size-8 rounded-lg bg-white flex items-center justify-center text-slate-400 hover:text-primary shadow-sm no-underline">
                            <span class="material-symbols-outlined text-lg">download</span>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT AREA: Payment Schedule + Activity Log -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Payment Schedule -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-800">Payment Schedule</h3>
                    <button onclick="document.getElementById('logPaymentModal').classList.remove('hidden')" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-lg">add</span> Log Payment
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                                <th class="px-6 py-4">Installment</th>
                                <th class="px-6 py-4 text-center">Due Date</th>
                                <th class="px-6 py-4 text-center">Amount</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @if(!empty($installment->upfront))
                                @php $upfrontPayment = $payments->where('type', 'upfront')->first(); @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-5">
                                        <span class="text-sm font-black text-slate-800">Upfront Payment</span>
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm font-bold text-slate-600">—</td>
                                    <td class="px-6 py-5 text-center text-sm font-black text-slate-800">{{ handlePrice($installment->getUpfront($itemPrice)) }}</td>
                                    <td class="px-6 py-5 text-center">
                                        @if(!empty($upfrontPayment))
                                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-600 text-[9px] font-black uppercase tracking-widest">Paid</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-600 text-[9px] font-black uppercase tracking-widest">Due</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        @if(!empty($upfrontPayment))
                                            <span class="material-symbols-outlined text-slate-400 text-lg cursor-pointer hover:text-primary">download</span>
                                        @endif
                                        <span class="material-symbols-outlined text-slate-300 text-lg cursor-pointer hover:text-slate-500 ml-2">more_vert</span>
                                    </td>
                                </tr>
                            @endif

                            @foreach($installment->steps as $index => $step)
                                @php
                                    $stepPayment = $payments->where('step_id', $step->id)->where('status', 'paid')->first();
                                    $dueAt = ($step->deadline * 86400) + $order->created_at;
                                    $isOverdue = ($dueAt < time() && empty($stepPayment));
                                    $isPaid = !empty($stepPayment);
                                    $isFuture = !$isPaid && $dueAt > time();
                                    $ordinal = match($index + 1) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors {{ $isOverdue ? 'bg-red-50/30' : '' }}">
                                    <td class="px-6 py-5">
                                        <span class="text-sm font-black text-slate-800">{{ ($index + 1) }}{{ $ordinal }} Installment</span>
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm font-bold {{ $isOverdue ? 'text-red-500' : 'text-slate-600' }}">
                                        {{ date('M d, Y', $dueAt) }}
                                    </td>
                                    <td class="px-6 py-5 text-center text-sm font-black text-slate-800">
                                        {{ handlePrice($step->getPrice($itemPrice)) }}
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        @if($isPaid)
                                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-600 text-[9px] font-black uppercase tracking-widest">Paid</span>
                                        @elseif($isOverdue)
                                            <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-[9px] font-black uppercase tracking-widest">Overdue</span>
                                        @else
                                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest">Future</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-right">
                                        @if($isPaid)
                                            <span class="material-symbols-outlined text-slate-400 text-lg cursor-pointer hover:text-primary">download</span>
                                        @else
                                            <span class="material-symbols-outlined text-slate-300 text-lg cursor-pointer hover:text-orange-500">notifications</span>
                                        @endif
                                        <span class="material-symbols-outlined text-slate-300 text-lg cursor-pointer hover:text-slate-500 ml-2">more_vert</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-black text-slate-800 mb-6">Activity Log</h3>

                <div class="space-y-6">
                    @foreach($payments->where('status', 'paid')->sortByDesc('created_at')->take(5) as $pmt)
                    <div class="flex items-start gap-4">
                        <div class="size-3 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-black text-slate-800">Payment Received</p>
                                <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($pmt->created_at)->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Payment of <strong class="text-primary">{{ handlePrice($pmt->amount ?? 0) }}</strong> was paid.
                                @if($pmt->sale_id) Transaction ID: <code class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded">TXN-{{ $pmt->sale_id }}</code> @endif
                            </p>
                        </div>
                    </div>
                    @endforeach

                    @if($overdueData['count'] > 0)
                    <div class="flex items-start gap-4">
                        <div class="size-3 rounded-full bg-orange-500 mt-1.5 shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-black text-slate-800">Reminder Sent</p>
                                <span class="text-xs text-slate-400">Yesterday</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Automatic email reminder sent for {{ $overdueData['count'] }} overdue installment(s).</p>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-start gap-4">
                        <div class="size-3 rounded-full bg-slate-300 mt-1.5 shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-black text-slate-800">Plan Created</p>
                                <span class="text-xs text-slate-400">{{ dateTimeFormat($order->created_at, 'M d, Y') }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Installment plan initialized by <strong>Admin</strong> for {{ $itemTitle }}.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Payment Modal -->
<div id="logPaymentModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-8 relative">
        <button onclick="document.getElementById('logPaymentModal').classList.add('hidden')" class="absolute top-4 right-4 size-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-all">
            <span class="material-symbols-outlined">close</span>
        </button>

        <h3 class="text-xl font-black text-slate-800">Log Payment</h3>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Plan #INST-{{ $order->id }}</p>

        <form method="POST" action="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/log-payment") }}" class="mt-8 space-y-6">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Select Installment</label>
                    <select name="step_id" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                        @if(!empty($installment->upfront))
                            @php $upfrontPaid = $payments->where('type', 'upfront')->where('status', 'paid')->first(); @endphp
                            @if(!$upfrontPaid)
                                <option value="upfront">Upfront Payment - {{ handlePrice($installment->getUpfront($itemPrice)) }}</option>
                            @endif
                        @endif
                        @foreach($installment->steps as $index => $step)
                            @php $sp = $payments->where('step_id', $step->id)->where('status', 'paid')->first(); @endphp
                            @if(!$sp)
                                <option value="{{ $step->id }}">{{ ($index + 1) }}{{ match($index + 1) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' } }} Installment - {{ handlePrice($step->getPrice($itemPrice)) }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Payment Method</label>
                    <select name="payment_method" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                        <option>Credit Card</option>
                        <option>Bank Transfer</option>
                        <option>UPI</option>
                        <option>Cash</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Transaction ID</label>
                <input type="text" name="transaction_id" placeholder="Enter reference number..." class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Notes (Optional)</label>
                <textarea name="notes" placeholder="Any additional details..." class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-4 focus:ring-primary/10 focus:border-primary" rows="3"></textarea>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="button" onclick="document.getElementById('logPaymentModal').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmAndSubmit(url) {
        if(confirm('Are you sure you want to proceed with this action?')) {
            window.location.href = url;
        }
    }
</script>
@endsection
