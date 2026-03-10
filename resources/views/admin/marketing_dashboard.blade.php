@extends('admin.layouts.app')

@push('libraries_top')
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/admin/vendor/owl.carousel/owl.theme.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Marketing Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Real-time overview of your platform's promotional performance.</p>
            </div>
        </div>

        <!-- Metrics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-amber-500/20 transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-rounded text-3xl font-black">person_off</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">{{ trans('admin/main.users_without_purchases') }}</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-2 tracking-tight">{{ $usersWithoutPurchases }}</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-rose-500/20 transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-rounded text-3xl font-black">school</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">{{ trans('admin/main.teachers_without_class') }}</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-2 tracking-tight">{{ $teachersWithoutClass }}</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-primary/20 transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-rounded text-3xl font-black">star</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">{{ trans('admin/main.featured_classes') }}</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-2 tracking-tight">{{ $featuredClasses }}</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-emerald-500/20 transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-rounded text-3xl font-black">percent</span>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">{{ trans('admin/main.active_discounts') }}</p>
                    <h3 class="text-2xl font-black text-gray-900 mt-2 tracking-tight">{{ $activeDiscounts }}</h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Selling Classes -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary font-bold">
                            <span class="material-symbols-rounded">trending_up</span>
                        </div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight">{{ trans('admin/main.top_selling_classes') }}</h3>
                    </div>
                    <a href="{{ getAdminPanelUrl() }}/webinars?type=course&sort=sales_desc" class="px-4 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-100 transition-all border border-gray-100">
                        {{ trans('admin/main.view_more') }}
                    </a>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[400px]">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="sticky top-0 bg-white/80 backdrop-blur-md">
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">#</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-left">{{ trans('admin/main.name') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">{{ trans('admin/main.sales') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-right">{{ trans('admin/main.income') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($getTopSellingClasses['webinars'] as $getTopSellingClass)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-gray-400">#{{ $getTopSellingClass->id }}</td>
                                    <td class="px-6 py-4">
                                        <a href="{{ $getTopSellingClass->getUrl() }}" target="_blank" class="flex flex-col group/item max-w-[200px]">
                                            <span class="text-sm font-bold text-gray-900 truncate group-hover/item:text-primary transition-colors">{{ $getTopSellingClass->title }}</span>
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tight">{{ trans('webinars.'.$getTopSellingClass->type) }}</span>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-gray-900 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                                            {{ $getTopSellingClass->sales_count + $getTopSellingClasses['webinarPartTotal'][$getTopSellingClass->id]['count']+ $getTopSellingClasses['webinarInstallmentTotal'][$getTopSellingClass->id]['count']}}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black text-primary font-mono tracking-tight">
                                            {{ handlePrice($getTopSellingClass->sales_amount + $getTopSellingClasses['webinarPartTotal'][$getTopSellingClass->id]['amount']+ $getTopSellingClasses['webinarInstallmentTotal'][$getTopSellingClass->id]['amount']) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Selling Appointments -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 font-bold drop-shadow-sm">
                            <span class="material-symbols-rounded">calendar_today</span>
                        </div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight">{{ trans('admin/main.top_selling_appointments') }}</h3>
                    </div>
                    <a href="{{ getAdminPanelUrl() }}/consultants?sort=appointments_desc" class="px-4 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-100 transition-all border border-gray-100">
                        {{ trans('admin/main.view_more') }}
                    </a>
                </div>
                <div class="overflow-x-auto overflow-y-auto max-h-[400px]">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="sticky top-0 bg-white/80 backdrop-blur-md shadow-sm">
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 border-t border-t-gray-50">#</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 border-t border-t-gray-50 text-left">{{ trans('admin/main.consultant') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 border-t border-t-gray-50 text-center">{{ trans('admin/main.sales') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 border-t border-t-gray-50 text-right">Income</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($getTopSellingAppointments as $getTopSellingAppointment)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-gray-400">#{{ $getTopSellingAppointment->creator->id }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $getTopSellingAppointment->creator->getAvatar() }}" class="w-8 h-8 rounded-full border border-gray-200" alt="">
                                            <span class="text-sm font-bold text-gray-900">{{ $getTopSellingAppointment->creator->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-gray-900 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                                            {{ $getTopSellingAppointment->sales_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black text-emerald-600 font-mono tracking-tight">
                                            {{ handlePrice($getTopSellingAppointment->sales_amount) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 items-start">
            <!-- Top Selling Instructors -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-600 font-bold">
                            <span class="material-symbols-rounded">groups</span>
                        </div>
                        <h3 class="text-base font-black text-gray-900 tracking-tight">{{ trans('admin/main.top_selling_instructors') }}</h3>
                    </div>
                    <a href="{{ getAdminPanelUrl() }}/instructors?sort=sales_classes_desc" class="px-4 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-100 transition-all border border-gray-100">
                        {{ trans('admin/main.view_more') }}
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">ID</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-left">{{ trans('admin/main.name') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">Duration</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">{{ trans('admin/main.sales') }}</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-right">Income</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($getTopSellingTeachers['users'] as $getTopSellingTeacher)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-xs font-bold text-gray-400">#{{ $getTopSellingTeacher->id }}</td>
                                    <td class="px-6 py-4 text-left">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-primary/5 flex items-center justify-center text-[10px] font-black text-primary border border-primary/10">
                                                {{ strtoupper(substr($getTopSellingTeacher->full_name, 0, 2)) }}
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">{{ $getTopSellingTeacher->full_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-widest border border-gray-100 px-2 py-0.5 rounded-full bg-gray-50">
                                            {{ convertMinutesToHourAndMinute($getTopSellingTeacher->classes_durations) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-gray-900">
                                            {{ $getTopSellingTeacher->sales_count + $getTopSellingTeachers['usersdata'][$getTopSellingTeacher->id]['count']  }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-black text-purple-600 font-mono tracking-tight">
                                            {{ handlePrice($getTopSellingTeacher->sales_amount + $getTopSellingTeachers['usersdata'][$getTopSellingTeacher->id]['amount']) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Classes Statistics Chart -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="px-6 py-5 border-b border-gray-50 flex items-center gap-3 bg-gray-50/10">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 font-bold">
                        <span class="material-symbols-rounded">bar_chart</span>
                    </div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight">{{ trans('admin/main.classes_statistics') }}</h3>
                </div>
                <div class="p-8 flex-1 flex items-center justify-center min-h-[400px]">
                    <canvas id="classesStatisticsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Most Active Students -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 font-bold drop-shadow-sm">
                        <span class="material-symbols-rounded">grade</span>
                    </div>
                    <h3 class="text-base font-black text-gray-900 tracking-tight">{{ trans('admin/main.most_active_students') }}</h3>
                </div>
                <a href="{{ getAdminPanelUrl() }}/students?sort=register_desc" class="px-4 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-100 transition-all border border-gray-100">
                    {{ trans('admin/main.view_more') }}
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">#</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-left">{{ trans('admin/main.name') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">{{ trans('admin/main.purchased_classes') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">{{ trans('admin/main.reserved_appointments') }}</th>
                            <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50 text-right">{{ trans('admin/main.total_purchased_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($getMostActiveStudents as $getMostActiveStudent)
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 text-xs font-bold text-gray-400">#{{ $getMostActiveStudent->id }}</td>
                                <td class="px-6 py-4 text-left">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-[10px] font-black text-blue-600 border border-blue-100">
                                            {{ strtoupper(substr($getMostActiveStudent->full_name, 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-bold text-gray-900">{{ $getMostActiveStudent->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-black text-gray-900 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                                        {{ $getMostActiveStudent->purchased_classes }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-black text-gray-900 px-3 py-1 bg-gray-50 rounded-lg border border-gray-100">
                                        {{ $getMostActiveStudent->reserved_appointments }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-black text-blue-600 font-mono tracking-tight">
                                        {{ handlePrice($getMostActiveStudent->total_cost) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/chartjs/chart.min.js"></script>
    <script src="/assets/admin/vendor/owl.carousel/owl.carousel.min.js"></script>
    <script src="/assets/admin/js/marketing_dashboard.min.js"></script>

    <script>
        (function ($) {
            "use strict";

            @if(!empty($getClassesStatistics))
            makeClassesStatisticsChart('', @json($getClassesStatistics['labels']),@json($getClassesStatistics['data']));
            @endif

            @if(!empty($getNetProfitChart))
            makeNetProfitChart('Income', @json($getNetProfitChart['labels']),@json($getNetProfitChart['data']));
            @endif
        })(jQuery)
    </script>
@endpush
