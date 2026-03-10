@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/chartjs/chart.min.css"/>
    <style>
        :root {
            --accent-green: #16a34a;
            --accent-green-light: #f0fdf4;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }

        .stats-header { margin-bottom: 2rem; }
        .stats-header h1 { font-size: 24px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
        .course-badge { background: var(--accent-green-light); color: var(--accent-green); padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; }

        /* Summary Cards */
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
        .summary-card {
            background: #fff; padding: 24px; border-radius: 16px; border: 1px solid var(--border-color);
            display: flex; flex-direction: column; gap: 12px; transition: all 0.2s;
        }
        .summary-card:hover { border-color: var(--accent-green); }
        .summary-card .icon-wrap {
            width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .icon-students { background: #f0fdf4; color: #16a34a; }
        .icon-reviews { background: #fff7ed; color: #ea580c; }
        .icon-sales { background: #eff6ff; color: #2563eb; }
        .icon-revenue { background: #ecfdf5; color: #059669; }

        .summary-card .label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .value { font-size: 24px; font-weight: 700; color: var(--text-dark); }

        /* Section Layout */
        .section-card { background: #fff; border-radius: 20px; border: 1px solid var(--border-color); padding: 24px; margin-bottom: 24px; }
        .section-card h3 { font-size: 16px; font-weight: 700; color: var(--text-dark); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
        .section-card h3::before { content: ''; width: 4px; height: 16px; background: var(--accent-green); border-radius: 2px; }

        /* Chart Grid */
        .charts-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .chart-card { background: #fff; border-radius: 16px; border: 1px solid var(--border-color); padding: 20px; }
        .chart-card h4 { font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 16px; opacity: 0.8; }
        .chart-wrap { height: 180px; position: relative; }

        /* Big Chart Row */
        .big-charts-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .big-chart-card { background: #fff; border-radius: 20px; border: 1px solid var(--border-color); padding: 24px; height: 320px; }

        /* Table */
        .custom-table-card { padding: 0; overflow: hidden; }
        .custom-table-card .header-wrap { padding: 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { background: #f8fafc; padding: 16px 24px; text-align: left; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        .custom-table td { padding: 16px 24px; border-bottom: 1px solid var(--border-color); font-size: 14px; color: #334155; }
        .user-info { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; background: #f1f5f9; }
        .user-name { font-weight: 600; color: var(--text-dark); display: block; }
        .user-email { font-size: 12px; color: var(--text-muted); }
        
        .progress-pill { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: #475569; }
        .progress-pill.done { background: #dcfce7; color: #166534; }

        .pagination-wrap { padding: 24px; display: flex; justify-content: center; }
    </style>
@endpush

@section('content')
    <div class="p-4">
        {{-- Header --}}
        <div class="stats-header">
            <span class="course-badge">COURSE ANALYSIS</span>
            <h1 class="mt-2 text-primary">{{ $webinar->title }}</h1>
            <nav aria-label="breadcrumb">
         <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-primary/30 transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="size-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center transition-transform group-hover:rotate-6">
                    <span class="material-symbols-outlined text-2xl font-[FILL]">group</span>
                </div>
                <span class="text-[10px] font-bold text-green-500 bg-green-50 px-2 py-0.5 rounded-full">+12%</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Students</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $studentsCount }}</p>
        </div>
        
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-blue-500/30 transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="size-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center transition-transform group-hover:rotate-6">
                    <span class="material-symbols-outlined text-2xl font-[FILL]">star</span>
                </div>
                <div class="flex items-center gap-1 text-yellow-400">
                    <span class="material-symbols-outlined text-sm font-[FILL]">star</span>
                    <span class="text-xs font-bold">{{ number_format($webinar->getRate(), 1) }}</span>
                </div>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Avg. Rating</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $webinar->reviews->count() }} <span class="text-sm font-medium text-slate-400">Reviews</span></p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-purple-500/30 transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="size-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center transition-transform group-hover:rotate-6">
                    <span class="material-symbols-outlined text-2xl font-[FILL]">shopping_cart</span>
                </div>
                <span class="text-[10px] font-bold text-primary bg-primary/10 px-2 py-0.5 rounded-full">Live</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Sales</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ $salesCount }}</p>
        </div>

        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm group hover:border-orange-500/30 transition-all">
            <div class="flex justify-between items-start mb-4">
                <div class="size-12 rounded-2xl bg-orange-500/10 text-orange-600 flex items-center justify-center transition-transform group-hover:rotate-6">
                    <span class="material-symbols-outlined text-2xl font-[FILL]">payments</span>
                </div>
                <span class="material-symbols-outlined text-orange-400/30 text-3xl">trending_up</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Net Revenue</p>
            <p class="text-3xl font-black text-slate-900 dark:text-white">{{ currencySign() }}{{ handlePrice($salesAmount, false) }}</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Revenue Overview</h3>
                    <p class="text-xs text-slate-400">Monthly breakdown of income generated</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-[10px] font-bold rounded-lg hover:bg-primary hover:text-white transition-all">YEAR</button>
                    <button class="px-3 py-1.5 bg-primary text-white text-[10px] font-bold rounded-lg">MONTH</button>
                </div>
            </div>
            <div class="h-[350px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-8">Student Engagement</h3>
            <div class="space-y-6">
                <div>
                    <div class="flex justify-between text-xs font-bold mb-2">
                        <span class="text-slate-500">Course Progress</span>
                        <span class="text-primary">78%</span>
                    </div>
                    <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-primary rounded-full transition-all duration-1000" style="width: 78%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-bold mb-2">
                        <span class="text-slate-500">Quiz Completion</span>
                        <span class="text-blue-500">64%</span>
                    </div>
                    <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full transition-all duration-1000" style="width: 64%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-bold mb-2">
                        <span class="text-slate-500">Assignment Pass Rate</span>
                        <span class="text-purple-500">92%</span>
                    </div>
                    <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full transition-all duration-1000" style="width: 92%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-12 border-t border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-yellow-400/10 text-yellow-600 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl font-[FILL]">emoji_events</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Learner Mood</p>
                        <p class="text-lg font-black text-slate-800 dark:text-white">Very Positive</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Students -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/20">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Enrollments</h3>
                <p class="text-xs text-slate-400">Showing the latest students joined this course</p>
            </div>
            <a href="#" class="px-6 py-2.5 bg-white dark:bg-slate-800 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-700 hover:border-primary hover:text-primary transition-all shadow-sm">View All Students</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Learner</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Progress</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Purchase Date</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($sessionEnrolledStudents as $enrolledStudent)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/10 transition-colors">
                            <td class="px-8 py-4">
                                <div class="flex items-center gap-4">
                                    <img src="{{ $enrolledStudent->user->getAvatar() }}" class="size-10 rounded-full bg-slate-100 object-cover border-2 border-white dark:border-slate-800 shadow-sm">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $enrolledStudent->user->full_name }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $enrolledStudent->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <div class="w-24">
                                    <div class="flex justify-between text-[10px] font-bold mb-1">
                                        <span class="text-slate-400">{{ $enrolledStudent->user->getProcess($webinar->id) }}%</span>
                                    </div>
                                    <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full" style="width: {{ $enrolledStudent->user->getProcess($webinar->id) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <p class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ dateTimeFormat($enrolledStudent->created_at, 'j M Y, H:i') }}</p>
                            </td>
                            <td class="px-8 py-4">
                                <span class="px-3 py-1 bg-green-500/10 text-green-600 text-[10px] font-bold rounded-full">Enrolled</span>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <button class="size-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-primary transition-all">
                                    <span class="material-symbols-outlined text-lg">more_vert</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-12 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">group_off</span>
                                <p class="text-sm text-slate-400 font-medium">No students enrolled in this course yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>
    <script src="/assets/default/js/panel/course_statistics.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(50, 161, 40, 0.2)');
        gradient.addColorStop(1, 'rgba(50, 161, 40, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthRevenueChart->labels) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($monthRevenueChart->data) !!},
                    borderColor: '#32A128',
                    borderWidth: 3,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#32A128',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        titleColor: '#94a3b8',
                        padding: 12,
                        cornerRadius: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 10, weight: '600' } }
                    },
                    y: {
                        grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 10, weight: '600' } }
                    }
                }
            }
        });
    });
</script>
@endpush
