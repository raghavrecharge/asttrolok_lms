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
        .rpt-page { font-family: 'Inter', sans-serif; }
        .rpt-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="rpt-page bg-background-light text-slate-900 p-4 md:p-8 space-y-8 h-full">

    {{-- TOP HEADER --}}
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-slate-800">Reports & Analytics</h1>
            <p class="text-sm text-slate-400 mt-1">Comprehensive overview of platform performance and student engagement.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                <button class="px-4 py-2 bg-primary text-white text-[10px] font-black rounded-lg uppercase tracking-widest transition-all">Today</button>
                <button class="px-4 py-2 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">7D</button>
                <button class="px-4 py-2 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">30D</button>
                <button class="px-4 py-2 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">Year</button>
            </div>
            <button class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all shadow-sm">
                <span class="material-symbols-outlined text-lg">download</span> Export
            </button>
        </div>
    </header>

    {{-- KPI GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 text-primary opacity-10 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-6xl">school</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Enrollments</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight">12,845</h3>
            <div class="mt-4 flex items-center gap-2">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[10px] font-black rounded-md">+12.5%</span>
                <span class="text-[10px] text-slate-400 font-bold">vs last month</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 text-emerald-500 opacity-10 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-6xl">group</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active Students</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight">8,210</h3>
            <div class="mt-4 flex items-center gap-2">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[10px] font-black rounded-md">+8.2%</span>
                <span class="text-[10px] text-slate-400 font-bold">on platform now</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 text-amber-500 opacity-10 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-6xl">trending_up</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg. Progress</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight">64.2%</h3>
            <div class="mt-4 flex items-center gap-2">
                <div class="w-full bg-slate-100 h-1.5 rounded-full">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: 64.2%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 text-purple-500 opacity-10 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-6xl">verified</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Completions</p>
            <h3 class="text-3xl font-black text-slate-800 tracking-tight">3,450</h3>
            <div class="mt-4 flex items-center gap-2">
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[10px] font-black rounded-md">+5.4%</span>
                <span class="text-[10px] text-slate-400 font-bold">certificates issued</span>
            </div>
        </div>
    </div>

    {{-- CHARTS ROW --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Trend Chart --}}
        <div class="lg:col-span-2 bg-white p-8 rounded-3xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-lg font-black text-slate-800">Enrollment Trend</h3>
                    <p class="text-xs text-slate-400 mt-1">Monthly enrollment growth over the past year.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="size-3 rounded-full bg-primary"></span>
                        <span class="text-[10px] font-bold text-slate-500">Sales</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="size-3 rounded-full bg-slate-200"></span>
                        <span class="text-[10px] font-bold text-slate-500">Target</span>
                    </div>
                </div>
            </div>
            <div class="h-64 flex items-end justify-between gap-1">
                @php $trend = [30, 45, 35, 60, 80, 55, 70, 90, 100, 85, 95, 110]; @endphp
                @foreach($trend as $key => $val)
                <div class="flex-1 flex flex-col items-center gap-3 group">
                    <div class="w-full bg-primary/20 rounded-t-lg relative group-hover:bg-primary transition-all cursor-pointer" style="height: {{ $val }}%">
                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[9px] font-black px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            {{ $val }} Users
                        </div>
                    </div>
                    <span class="text-[9px] font-black text-slate-400 uppercase">{{ date('M', mktime(0, 0, 0, $key + 1, 1)) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Distribution Chart --}}
        <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm flex flex-col">
            <h3 class="text-lg font-black text-slate-800 mb-2">Revenue Breakdown</h3>
            <p class="text-xs text-slate-400 mb-8">Income distribution by course category.</p>
            
            <div class="flex-1 flex items-center justify-center relative">
                <div class="size-48 rounded-full border-[20px] border-primary flex items-center justify-center">
                    <div class="size-44 rounded-full border-[20px] border-amber-500 absolute rotate-45"></div>
                    <div class="size-40 rounded-full border-[20px] border-slate-100 absolute"></div>
                    <div class="text-center z-10">
                        <p class="text-2xl font-black text-slate-800">74%</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Growth</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-primary"></span>
                        <span class="text-xs font-bold text-slate-700">Premium Courses</span>
                    </div>
                    <span class="text-xs font-black text-slate-800">45%</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold text-slate-700">Workshop Passes</span>
                    </div>
                    <span class="text-xs font-black text-slate-800">32%</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-purple-500"></span>
                        <span class="text-xs font-bold text-slate-700">Others</span>
                    </div>
                    <span class="text-xs font-black text-slate-800">23%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTTOM TABLES ROW --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Top Courses --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Top Performing Courses</h3>
                <button class="text-xs font-bold text-primary hover:underline">View All</button>
            </div>
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Course Name</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Rating</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Revenue</th>
                            <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $courses = [
                                ['name' => 'Vedic Astrology Mastery', 'rating' => 4.9, 'revenue' => '₹45,200', 'prog' => 85, 'color' => 'bg-primary'],
                                ['name' => 'Prashna Chart Analysis', 'rating' => 4.7, 'revenue' => '₹32,800', 'prog' => 72, 'color' => 'bg-amber-500'],
                                ['name' => 'Advanced Lal Kitab', 'rating' => 4.8, 'revenue' => '₹28,100', 'prog' => 64, 'color' => 'bg-purple-500'],
                                ['name' => 'Numerology 101', 'rating' => 4.6, 'revenue' => '₹18,500', 'prog' => 45, 'color' => 'bg-sky-500'],
                            ];
                        @endphp
                        @foreach($courses as $course)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-800">{{ $course['name'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-amber-400 text-sm" style="font-variation-settings: 'FILL' 1">star</span>
                                    <span class="text-xs font-black text-slate-700">{{ $course['rating'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-black text-slate-800">{{ $course['revenue'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <span class="text-[10px] font-bold text-slate-400">{{ $course['prog'] }}%</span>
                                    <div class="w-16 bg-slate-100 h-1 rounded-full">
                                        <div class="{{ $course['color'] }} h-1 rounded-full" style="width: {{ $course['prog'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Geographics Distribution --}}
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex flex-col">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest mb-6">Geographic Distribution</h3>
            <div class="flex-1 bg-slate-50 rounded-2xl flex items-center justify-center relative overflow-hidden">
                <span class="material-symbols-outlined text-9xl text-slate-200">public</span>
                {{-- Mock Map Pins --}}
                <div class="absolute top-1/4 left-1/3 size-3 bg-primary rounded-full ring-4 ring-primary/20 animate-pulse"></div>
                <div class="absolute top-1/2 left-2/3 size-3 bg-primary rounded-full ring-4 ring-primary/20 animate-pulse"></div>
                <div class="absolute bottom-1/3 left-1/2 size-3 bg-primary rounded-full ring-4 ring-primary/20 animate-pulse"></div>
            </div>
            <div class="mt-6 flex items-center justify-around">
                <div class="text-center">
                    <p class="text-xs font-black text-slate-800">Maharashtra</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">34% Users</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-black text-slate-800">Gujarat</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">22% Users</p>
                </div>
                <div class="text-center">
                    <p class="text-xs font-black text-slate-800">UP & Delhi</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">18% Users</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    {{-- Add Chart.js or other libraries if needed --}}
@endpush
