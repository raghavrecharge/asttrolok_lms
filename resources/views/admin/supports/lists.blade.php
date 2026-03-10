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
    .um-page-container { font-family: 'Inter', sans-serif; }
    .um-page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
</style>
@endpush

@section('content')
<div class="um-page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased h-full flex flex-col p-4 md:p-8 space-y-8">

    {{-- KPI GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Tickets Pending Today --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tickets Pending Today</p>
            <div class="mt-3 flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800 tracking-tight">{{ $pendingReplySupports }}</h3>
                <span class="px-2 py-1 bg-emerald-100 text-emerald-600 text-[10px] font-black rounded-md flex items-center gap-0.5">
                    +12% <span class="material-symbols-outlined text-xs">trending_up</span>
                </span>
            </div>
        </div>
        {{-- Avg. Resolution Time --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg. Resolution Time</p>
            <div class="mt-3 flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800 tracking-tight">3.4h</h3>
                <span class="px-2 py-1 bg-red-100 text-red-600 text-[10px] font-black rounded-md flex items-center gap-0.5">
                    -5% <span class="material-symbols-outlined text-xs">trending_down</span>
                </span>
            </div>
        </div>
        {{-- Customer Satisfaction --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer Satisfaction</p>
            <div class="mt-3 flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800 tracking-tight">94%</h3>
                <div class="size-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                    <span class="material-symbols-outlined text-xl">construction</span>
                </div>
            </div>
        </div>
        {{-- Agents Online --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Agents Online</p>
            <div class="mt-3 flex items-end justify-between">
                <h3 class="text-4xl font-black text-slate-800 tracking-tight">12/15</h3>
                <div class="flex -space-x-2">
                    <div class="size-8 rounded-full bg-primary/20 border-2 border-white flex items-center justify-center text-[9px] font-black text-primary">AB</div>
                    <div class="size-8 rounded-full bg-sky-200 border-2 border-white flex items-center justify-center text-[9px] font-black text-sky-600">CD</div>
                    <div class="size-8 rounded-full bg-amber-200 border-2 border-white flex items-center justify-center text-[9px] font-black text-amber-600">EF</div>
                    <div class="size-8 rounded-full bg-slate-200 border-2 border-white flex items-center justify-center text-[9px] font-black text-slate-500">+9</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <form method="get" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Scenario</label>
                <select name="scenario" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                    <option value="">All Scenarios</option>
                    <option value="course_extension" {{ request()->get('scenario') == 'course_extension' ? 'selected' : '' }}>Course Extension</option>
                    <option value="offline_cash_payment" {{ request()->get('scenario') == 'offline_cash_payment' ? 'selected' : '' }}>Offline Payment</option>
                    <option value="refund_payment" {{ request()->get('scenario') == 'refund_payment' ? 'selected' : '' }}>Refund</option>
                    <option value="installment_restructure" {{ request()->get('scenario') == 'installment_restructure' ? 'selected' : '' }}>Installment Restructure</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Status</label>
                <select name="status" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                    <option value="">All Status</option>
                    <option value="open" @if(request()->get('status') == 'open') selected @endif>{{ trans('admin/main.open') }}</option>
                    <option value="replied" @if(request()->get('status') == 'replied') selected @endif>{{ trans('admin/main.pending_reply') }}</option>
                    <option value="supporter_replied" @if(request()->get('status') == 'supporter_replied') selected @endif>{{ trans('admin/main.replied') }}</option>
                    <option value="close" @if(request()->get('status') == 'close') selected @endif>{{ trans('admin/main.closed') }}</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Course Filter</label>
                <select name="course" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                    <option value="">All Courses</option>
                </select>
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Search User</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" name="title" value="{{ request()->get('title') }}" class="w-full pl-10 pr-4 bg-white border border-slate-200 rounded-xl h-11 text-sm font-medium text-slate-700 focus:ring-4 focus:ring-primary/10 focus:border-primary" placeholder="Name or email...">
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-xs font-black rounded-xl uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-sm">filter_list</span> Filter
            </button>
            <div class="flex items-center bg-slate-100 rounded-xl p-1">
                <button type="button" class="px-4 py-2 bg-white text-slate-700 text-[10px] font-black rounded-lg uppercase tracking-widest shadow-sm">List View</button>
                <button type="button" class="px-4 py-2 text-slate-400 text-[10px] font-black rounded-lg uppercase tracking-widest hover:text-slate-600 transition-all">Kanban View</button>
            </div>
        </div>
    </form>

    {{-- LIST TABLE --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Ticket ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Scenario</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Course</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Created Date</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($supports as $support)
                    @php
                        $statusBadge = match($support->status) {
                            'close' => ['bg-rose-100 text-rose-600', 'Closed'],
                            'replied', 'open' => ['bg-amber-100 text-amber-600', 'Pending'],
                            'supporter_replied' => ['bg-emerald-100 text-emerald-600', 'Replied'],
                            default => ['bg-sky-100 text-sky-600', ucfirst($support->status)]
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-sm font-black text-primary">#TK-{{ $support->id }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="size-9 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 overflow-hidden">
                                    @if($support->user->avatar)
                                        <img src="{{ $support->user->getAvatar() }}" alt="" class="size-9 rounded-full object-cover">
                                    @else
                                        <span class="text-xs font-black text-primary">{{ strtoupper(substr($support->user->full_name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex flex-col">
                                    <a href="{{ $support->user->getProfileUrl() }}" target="_blank" class="text-sm font-bold text-slate-800 hover:text-primary transition-colors no-underline">{{ $support->user->full_name }}</a>
                                    <span class="text-[10px] text-slate-400">{{ $support->user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600">{{ $support->title }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-slate-600">{{ $support->department->title }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full {{ $statusBadge[0] }} text-[9px] font-black uppercase tracking-widest">{{ $statusBadge[1] }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-sm font-medium text-slate-600">{{ (!empty($support->created_at)) ? dateTimeFormat($support->created_at, 'M d, Y') : '-' }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('admin_supports_reply')
                                    <a href="{{ getAdminPanelUrl() }}/supports/{{ $support->id }}/conversation" class="text-xs font-bold text-primary hover:underline flex items-center gap-1 no-underline">
                                        View Ticket <span class="material-symbols-outlined text-sm">open_in_new</span>
                                    </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($supports->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-white flex items-center justify-between">
            <span class="text-xs text-primary font-bold">Showing {{ $supports->count() }} of {{ $supports->total() }} tickets</span>
            <div class="flex items-center gap-2">
                @if(!$supports->onFirstPage())
                    <a href="{{ $supports->previousPageUrl() }}" class="size-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 transition-all"><span class="material-symbols-outlined text-lg">chevron_left</span></a>
                @endif
                <span class="px-3 py-1 bg-primary text-white text-xs font-black rounded-lg">Page {{ $supports->currentPage() }}</span>
                @if($supports->hasMorePages())
                    <a href="{{ $supports->nextPageUrl() }}" class="size-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 transition-all"><span class="material-symbols-outlined text-lg">chevron_right</span></a>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
