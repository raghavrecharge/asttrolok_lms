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
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Support Center Overview</h2>
            <p class="text-slate-500 text-sm mt-1">Real-time performance metrics and ticket volume analytics.</p>
        </div>
        <a href="{{ getAdminPanelUrl() }}/supports/create" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded-xl font-semibold text-sm transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">add</span>
            New Ticket
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow">
            <p class="text-sm font-medium text-slate-500">Total Pending Support</p>
            <div class="flex items-end gap-2 mt-2">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($supportTicketsCount) }}</h3>
                <span class="text-xs font-bold text-primary flex items-center gap-0.5 mb-1.5">
                    <span class="material-symbols-outlined text-xs">trending_up</span>
                    +5.2%
                </span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow">
            <p class="text-sm font-medium text-slate-500">Total Students</p>
            <div class="flex items-end gap-2 mt-2">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($totalStudents) }}</h3>
                <span class="text-xs font-bold text-primary flex items-center gap-0.5 mb-1.5 border px-1 ml-2 rounded text-[10px] bg-primary/10">ALL</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow">
            <p class="text-sm font-medium text-slate-500">Platform Revenue</p>
            <div class="flex items-end gap-2 mt-2">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">${{ number_format($totalRevenue) }}</h3>
                <span class="text-xs font-bold text-slate-400 mb-1.5">Live</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow">
            <p class="text-sm font-medium text-slate-500">Agents Online</p>
            <div class="flex items-end gap-2 mt-2">
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white">12/15</h3>
                <div class="flex -space-x-2 mb-1">
                    <div class="size-6 rounded-full border-2 border-white bg-slate-200"></div>
                    <div class="size-6 rounded-full border-2 border-white bg-slate-300"></div>
                    <div class="size-6 rounded-full border-2 border-white bg-slate-400"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ticket Volume Trend -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow">
            <div class="flex items-center justify-between mb-6">
                <h4 class="font-bold text-slate-900 dark:text-white">Ticket Volume Trend</h4>
                <select class="text-xs border-slate-200 dark:border-slate-700 bg-transparent rounded px-2 py-1 focus:ring-primary/20">
                    <option>Last 12 Months</option>
                </select>
            </div>
            <div class="h-64 relative">
                <canvas id="revenueDashboardChart"></canvas>
            </div>
        </div>

        <!-- Tickets by Scenario -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow">
            <div class="flex items-center justify-between mb-6">
                <h4 class="font-bold text-slate-900 dark:text-white">Topics Split</h4>
            </div>
            <div class="space-y-6">
                @php
                    $catColors = ['bg-primary', 'bg-amber-400', 'bg-indigo-400', 'bg-slate-400'];
                    $catPercents = [45, 30, 15, 10];
                @endphp
                @foreach($topCategories->take(4) as $index => $category)
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600 dark:text-slate-400">{{ $category->title }}</span>
                        <span class="font-semibold">{{ $catPercents[$index] ?? 10 }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div class="{{ $catColors[$index] ?? 'bg-primary' }} h-full rounded-full" style="width: {{ $catPercents[$index] ?? 10 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Priority Tickets Table -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-800 soft-shadow overflow-hidden mt-6">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h4 class="font-bold text-slate-900 dark:text-white">Priority Pending Tickets</h4>
            <a href="{{ getAdminPanelUrl() }}/supports" class="text-primary text-sm font-semibold hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Ticket ID</th>
                        <th class="px-6 py-4 font-semibold">User</th>
                        <th class="px-6 py-4 font-semibold">Department</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @if(!empty($recentTickets) && count($recentTickets['tickets']))
                        @foreach($recentTickets['tickets']->take(5) as $ticket)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">#TK-{{ $ticket->id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-7 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-[10px]">{{ substr($ticket->user->full_name ?? 'U', 0, 2) }}</div>
                                    <span>{{ $ticket->user->full_name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $ticket->department->title ?? 'General' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400 uppercase">{{ $ticket->status }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ getAdminPanelUrl() }}/supports/{{ $ticket->id }}/conversation" class="text-primary font-semibold hover:bg-primary/5 px-3 py-1.5 rounded-lg border border-primary/20 transition-all">Reply</a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No pending tickets. Good job!</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>            </div>
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
