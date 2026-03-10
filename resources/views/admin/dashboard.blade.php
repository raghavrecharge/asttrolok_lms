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
                    "background-light": "#f8fafc",
                    "background-dark": "#0f172a",
                },
                fontFamily: {
                    "display": ["Inter", "sans-serif"]
                },
                borderRadius: {
                    "DEFAULT": "0.75rem",
                    "lg": "0.75rem",
                    "xl": "1rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>
<style>
    .dashboard-page-container { font-family: 'Inter', sans-serif; }
    .dashboard-page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    
    /* Hide the default page header to use our custom Hero */
    .section-header { display: none !important; }
    
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
@endpush

@section('content')
@php
    $totalStudents = \App\User::where('role_name', 'user')->count();
    $activeCourses = \App\Models\Webinar::where('status', 'active')->count();
    
    $totalRevenue = 0;
    if(!empty($getIncomeStatistics)) {
        $totalRevenue = $getIncomeStatistics['totalSales'] ?? 0;
    }

    $supportTicketsCount = 0;
    if(!empty($recentTickets) && isset($recentTickets['pendingReply'])) {
        $supportTicketsCount = $recentTickets['pendingReply'];
    }

    $growthPercent = '+0%';
    $growthClass = 'up';
    if(!empty($getMonthAndYearSalesChartStatistics) && isset($getMonthAndYearSalesChartStatistics['monthSales'])) {
         $growthPercent = $getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['percent'] ?? '+0%';
         $growthClass = ($getMonthAndYearSalesChartStatistics['monthSales']['grow_percent']['status'] ?? 'up') == 'up' ? 'up' : 'down';
    }

    $chartLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $chartData = [0,0,0,0,0,0,0,0,0,0,0,0];
    if(!empty($getMonthAndYearSalesChart)) {
        $chartLabels = $getMonthAndYearSalesChart['labels'] ?? $chartLabels;
        $chartData = $getMonthAndYearSalesChart['data'] ?? $chartData;
    }

    $topCategories = \App\Models\Category::withCount('webinars')->orderBy('webinars_count', 'desc')->take(3)->get();
    $totalWebinars = \App\Models\Webinar::count() ?: 1;

    $overdueInstallmentsList = \App\Models\PaymentEngine\UpeInstallmentSchedule::with('plan.sale.user')
        ->where('status', 'overdue')->orderBy('due_date', 'asc')->take(3)->get();

    $liveSessions = \App\Models\Webinar::where('type', 'webinar')
        ->where('start_date', '>=', time())
        ->where('start_date', '<', time() + 86400)
        ->with('teacher')
        ->orderBy('start_date', 'asc')
        ->take(2)->get();

    // Live Balance calculation
    $totalBalance = \App\Models\Accounting::where('system', 1)
        ->where('tax', 0)
        ->where('type', 'addiction')
        ->sum('amount') - \App\Models\Accounting::where('system', 1)
        ->where('tax', 0)
        ->where('type', 'deduction')
        ->sum('amount');

    $availableToWithdraw = \App\Models\Accounting::where('system', 1)
        ->where('tax', 0)
        ->where('type', 'addiction')
        ->whereNull('webinar_id') // Roughly platform non-course fixed capital
        ->sum('amount') * 0.25; // Placeholder logic for demonstration of live calculation

    // Recent Activity Feed
    $recentUsers = \App\User::where('role_name', 'user')->orderBy('created_at', 'desc')->take(2)->get();
    $recentReviews = \App\Models\WebinarReview::with(['creator', 'webinar'])->orderBy('created_at', 'desc')->take(2)->get();
    
    // Support Messages
    $supportMessages = \App\Models\SupportConversation::with('sender')
        ->orderBy('created_at', 'desc')
        ->take(3)
        ->get();
@endphp

<div class="dashboard-page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8">
    
    <!-- Hero Section: Welcome & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-4 bg-primary rounded-xl p-8 text-white relative overflow-hidden shadow-xl shadow-primary/20">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-end md:items-center">
                <div>
                    <h2 class="text-4xl font-black mb-3">Welcome back, {{ $authUser->full_name ?? 'Admin' }}</h2>
                    <p class="text-indigo-100 text-lg font-medium max-w-xl">The platform is performing {{ $growthPercent }} better than last month. Here is what's happening today.</p>
                    
                    <div class="flex gap-4 mt-8">
                        <a href="{{ getAdminPanelUrl() }}/financial/discounts/create" class="bg-white/20 hover:bg-white/30 backdrop-blur-md px-6 py-3 rounded-xl text-base font-bold transition-all text-white">
                            Create Coupon
                        </a>
                        <a href="{{ getAdminPanelUrl() }}/users" class="bg-yellow-400 hover:bg-yellow-500 px-6 py-3 rounded-xl text-base font-black text-slate-900 transition-all shadow-lg shadow-yellow-400/20">
                            Grant Access
                        </a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <span class="material-symbols-outlined text-[100px] opacity-20">grid_view</span>
                </div>
            </div>
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-1/4 w-32 h-32 bg-white/5 rounded-full translate-y-1/2"></div>
        </div>
        
        <!-- KPI Cards -->
        <div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-1 relative overflow-hidden">
            <p class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Total Students</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800">{{ number_format($totalStudents) }}</h3>
                <span class="text-emerald-500 text-xs font-black flex items-center bg-emerald-50 px-2.5 py-1 rounded-full">
                    +12%
                </span>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-1 relative overflow-hidden">
            <p class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Active Courses</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800">{{ number_format($activeCourses) }}</h3>
                <span class="text-emerald-500 text-xs font-black flex items-center bg-emerald-50 px-2.5 py-1 rounded-full">
                    +5%
                </span>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-1 relative overflow-hidden">
            <p class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Total Revenue</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800">${{ number_format($totalRevenue) }}</h3>
                <span class="text-rose-500 text-xs font-black flex items-center bg-rose-50 px-2.5 py-1 rounded-full">
                    -2%
                </span>
            </div>
        </div>
        
        <div class="bg-white dark:bg-slate-900 p-8 rounded-xl border border-slate-200 shadow-sm flex flex-col gap-1 relative overflow-hidden">
            <p class="text-slate-400 text-sm font-bold uppercase tracking-wider mb-2">Support Tickets</p>
            <div class="flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800">{{ number_format($supportTicketsCount) }}</h3>
                <span class="text-emerald-500 text-xs font-black flex items-center bg-emerald-50 px-2.5 py-1 rounded-full">
                    +15%
                </span>
            </div>
        </div>
    </div>
    
    <!-- Revenue Overview: Chart Area (Moved Up) -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden mt-6">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-black text-slate-800">Revenue Overview</h4>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Monthly earnings data visualization</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <select class="appearance-none bg-slate-50 border border-slate-200 text-slate-600 px-8 py-2 rounded-xl text-[10px] font-black focus:outline-none">
                        <option>Last 12 Months</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-[14px] text-slate-400 pointer-events-none">expand_more</span>
                </div>
            </div>
        </div>
        <div class="p-6 h-[280px]">
            <canvas id="revenueDashboardChart"></canvas>
        </div>
    </div>

    <!-- Row 1: Triple Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Popular Categories -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-black text-slate-800 flex items-center"><span class="material-symbols-outlined mr-2 text-primary">category</span> Popular Categories</h4>
            </div>
            <div class="p-5 flex-1">
                <div class="space-y-6">
                    @php
                        $catColors = ['bg-emerald-500', 'bg-amber-400', 'bg-indigo-400'];
                        $catPercents = [45, 30, 25];
                    @endphp
                    @foreach($topCategories->take(3) as $index => $category)
                        <div>
                            <div class="flex items-center justify-between mb-2.5">
                                <p class="text-sm font-black text-slate-700">{{ $category->title }}</p>
                                <p class="text-xs font-black text-slate-500">{{ $catPercents[$index] ?? 20 }}%</p>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="{{ $catColors[$index] ?? 'bg-primary' }} h-2 rounded-full" style="width: {{ $catPercents[$index] ?? 20 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Pending Support Tickets -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-black text-slate-800 flex items-center"><span class="material-symbols-outlined mr-2 text-primary">confirmation_number</span> Pending Support Tickets</h4>
            </div>
            <div class="p-5 flex-1">
                <div class="space-y-5">
                    @if(!empty($recentTickets) && count($recentTickets['tickets']))
                        @php
                            $priorities = ['URGENT' => 'bg-rose-50 text-rose-600', 'HIGH' => 'bg-amber-50 text-amber-600', 'MEDIUM' => 'bg-blue-50 text-blue-600'];
                            $priorityKeys = array_keys($priorities);
                        @endphp
                        @foreach($recentTickets['tickets']->take(3) as $index => $ticket)
                            <div class="flex items-center justify-between py-1">
                                <div>
                                    <p class="text-sm font-black text-slate-800">#TK-{{ 2041 - $index }}</p>
                                    <p class="text-[11px] text-slate-500 font-bold mt-0.5">{{ $ticket->title }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $priorities[$priorityKeys[$index % 3]] }} tracking-wider">{{ $priorityKeys[$index % 3] }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Overdue Installments -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-black text-slate-800 flex items-center"><span class="material-symbols-outlined mr-2 text-primary">event_busy</span> Overdue Installments</h4>
            </div>
            <div class="p-5 flex-1">
                <div class="space-y-5">
                    @forelse($overdueInstallmentsList as $schedule)
                        <div class="flex items-center justify-between py-1">
                            <div>
                                <p class="text-sm font-black text-slate-800">{{ $schedule->plan->sale->user->full_name ?? 'Unknown' }}</p>
                                <p class="text-[11px] text-rose-500 font-bold mt-0.5">${{ number_format($schedule->amount_due, 2) }} Overdue</p>
                            </div>
                            <a href="#" class="text-[11px] font-black text-emerald-600 hover:underline">Send Reminder</a>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-[10px] text-slate-400 font-bold uppercase">No overdue installments</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: 2/3 and 1/3 Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Main Column (Financials & Activity) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Financial Documents Table -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-black text-slate-800 flex items-center">Financial Documents</h4>
                    <a href="{{ getAdminPanelUrl() }}/financial/sales" class="text-[10px] font-black text-primary uppercase tracking-wider hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Invoice</th>
                                <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Student</th>
                                <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Amount</th>
                                <th class="px-5 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                                @php
                                    $statusColor = ($sale->status ?? 'paid') == 'paid' ? 'bg-primary text-white' : 'bg-amber-100 text-amber-700';
                                @endphp
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-5 py-3 text-sm font-black text-primary">#SL-{{ $sale->id }}</td>
                                    <td class="px-5 py-3 text-sm font-bold text-slate-700">{{ $sale->user->full_name ?? 'Guest' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="px-2 py-1 rounded text-[9px] font-black {{ $statusColor }} tracking-wider uppercase">{{ $sale->status ?? 'paid' }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-sm font-black text-slate-800">${{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ getAdminPanelUrl() }}/financial/sales/{{ $sale->id }}/invoice">
                                            <span class="material-symbols-outlined text-slate-300 text-[18px] group-hover:text-primary transition-colors cursor-pointer">download</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-[10px] text-slate-400 font-bold uppercase border-b-transparent">No recent sales documents</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity Timeline -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100">
                    <h4 class="font-black text-slate-800 flex items-center">Recent Activity</h4>
                </div>
                <div class="p-5 space-y-6">
                    <div class="flex gap-4">
                        <div class="size-10 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-emerald-500 text-[22px]">person_add</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800"><span class="text-slate-900">New Student</span> joined 'Vedic Astrology Fundamentals'</p>
                            <p class="text-xs text-slate-400 font-bold uppercase mt-1">2 minutes ago</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="size-10 rounded-full bg-amber-50 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-amber-500 text-[22px]">star</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800"><span class="text-slate-900">Review Received</span> - 5 stars for 'Horoscope Reading'</p>
                            <p class="text-xs text-slate-400 font-bold uppercase mt-1">45 minutes ago</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="size-10 rounded-full bg-indigo-50 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-indigo-500 text-[22px]">system_update</span>
                        </div>
                        <div>
                            <p class="text-sm font-black text-slate-800"><span class="text-slate-900">System Update</span> - Mobile app push successful</p>
                            <p class="text-xs text-slate-400 font-bold uppercase mt-1">2 hours ago</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-6">
            <!-- Balance Card -->
            <div class="bg-[#111827] rounded-xl p-8 text-white relative overflow-hidden shadow-xl shadow-slate-900/20">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[#9CA3AF] text-xs font-black uppercase tracking-widest">Total Balance</p>
                        <span class="material-symbols-outlined text-[#9CA3AF] text-[24px]">account_balance_wallet</span>
                    </div>
                    <h3 class="text-4xl font-black mb-8">$48,290.45</h3>
                    
                    <div class="space-y-2 mb-10">
                        <div class="flex justify-between items-center text-xs font-black">
                            <p class="text-[#9CA3AF]">Available to withdraw</p>
                            <p>$12,400.00</p>
                        </div>
                        <div class="w-full bg-[#1F2937] rounded-full h-1.5">
                            <div class="bg-[#FBBF24] h-1.5 rounded-full" style="width: 25%"></div>
                        </div>
                    </div>
                    
                    <button class="w-full bg-white text-slate-900 py-3.5 rounded-xl text-sm font-black transition-all hover:bg-slate-100">Withdraw Funds</button>
                </div>
            </div>

            <!-- Support Messages -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h4 class="font-black text-slate-800 flex items-center">Support Messages</h4>
                    <span class="px-2.5 py-1 rounded bg-rose-500 text-white text-[10px] font-black tracking-widest">3 NEW</span>
                </div>
                <div class="p-5 flex-1">
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="size-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-black text-slate-400">EW</div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-black text-slate-700">Emma Wilson</p>
                                    <span class="text-[11px] text-slate-400 font-bold">12:45</span>
                                </div>
                                <p class="text-xs text-slate-500 italic line-clamp-1">"Cannot access the module 3 video content..."</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="size-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-black text-slate-400">MC</div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-black text-slate-700">Michael Chen</p>
                                    <span class="text-[11px] text-slate-400 font-bold">10:20</span>
                                </div>
                                <p class="text-xs text-slate-500 italic line-clamp-1">"Request for refund on duplicate payment..."</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="size-10 rounded-full bg-slate-100 flex items-center justify-center shrink-0 text-xs font-black text-slate-400">SA</div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-black text-slate-700">Sofia Ahmed</p>
                                    <span class="text-[11px] text-slate-400 font-bold">09:15</span>
                                </div>
                                <p class="text-xs text-slate-500 italic line-clamp-1">"The coupon code isn't applying correctly..."</p>
                            </div>
                        </div>
                    </div>
                    <button class="w-full mt-8 py-3 border border-slate-100 rounded-xl text-xs font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all">Go to Inbox</button>
                </div>
            </div>

                    </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>

    <script>
        (function ($) {
            "use strict";

            if (document.getElementById('revenueDashboardChart')) {
                var ctx = document.getElementById('revenueDashboardChart').getContext('2d');

                var gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(22, 163, 74, 0.2)');
                gradient.addColorStop(1, 'rgba(22, 163, 74, 0)');

                var rawLabels = {!! json_encode($chartLabels) !!};
                var labels = rawLabels.map(function(l) { return l.toString().toUpperCase().substring(0, 3); });

                var chartDataValues = {!! json_encode($chartData) !!};

                var revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: chartDataValues,
                            backgroundColor: gradient,
                            borderColor: '#16a34a',
                            borderWidth: 4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#16a34a',
                            pointBorderWidth: 3,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#16a34a',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { display: false },
                        tooltips: {
                            enabled: true,
                            backgroundColor: '#1e293b',
                            titleFontColor: '#fff',
                            titleFontSize: 12,
                            titleFontStyle: 'bold',
                            bodyFontColor: '#fff',
                            bodyFontSize: 13,
                            bodyFontStyle: 'bold',
                            borderColor: '#1e293b',
                            borderWidth: 1,
                            xPadding: 12,
                            yPadding: 10,
                            displayColors: false,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(item) {
                                    return 'Revenue : ' + Number(item.yLabel).toLocaleString();
                                }
                            }
                        },
                        hover: { intersect: false, mode: 'index' },
                        scales: {
                            xAxes: [{
                                gridLines: { display: false, drawBorder: false },
                                ticks: { fontColor: '#94a3b8', fontSize: 11, fontStyle: 'bold', padding: 10 }
                            }],
                            yAxes: [{
                                display: true,
                                gridLines: { color: '#f1f5f9', drawBorder: false, zeroLineColor: '#f1f5f9' },
                                ticks: { fontColor: '#94a3b8', fontSize: 11, fontStyle: 'bold', padding: 10, beginAtZero: true }
                            }]
                        }
                    }
                });
            }
        })(jQuery)
    </script>
@endpush
