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
                        "primary": "#16A34A",
                        "primary-light": "#F0FDF4",
                        "background-light": "#F8FAFC",
                        "background-dark": "#0F172A",
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
        .courses-page { font-family: 'Inter', sans-serif; }
        .courses-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card { display: none !important; }
        
        .sidebar-item {
            @apply flex items-center gap-3 px-4 py-3 rounded-2xl text-[13px] font-bold transition-all text-slate-500 hover:bg-slate-100 hover:text-slate-900;
        }
        .sidebar-item.active {
            @apply bg-primary/10 text-primary hover:bg-primary/20;
        }
        
        .filter-input {
            @apply w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-xl text-xs font-bold text-slate-700 focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all !important;
        }
        .filter-label { @apply text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5 ml-1; }
    </style>
@endpush

@section('content')
<div class="courses-page bg-background-light dark:bg-background-dark min-h-[calc(100vh-100px)]">
    <div class="flex flex-col lg:flex-row h-full">
        
        <!-- Sticky Sidebar for Desktop -->
        <aside class="w-full lg:w-[320px] bg-white border-r border-slate-200 p-6 flex flex-col gap-8 sticky top-0 h-auto lg:h-[calc(100vh-64px)] overflow-y-auto shrink-0">
            
            <!-- Quick Branding/Title in Sidebar -->
            <div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[2px] mb-4">Course Discovery</h3>
                <nav class="flex flex-col gap-1">
                    <a href="{{ request()->url() }}?type=course" class="sidebar-item {{ request()->get('status') == '' ? 'active' : '' }}">
                        <span class="material-symbols-outlined">grid_view</span>
                        All Courses
                    </a>
                    <a href="{{ request()->url() }}?type=course&status=active" class="sidebar-item {{ request()->get('status') == 'active' ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-green-500">check_circle</span>
                        Published
                    </a>
                    <a href="{{ request()->url() }}?type=course&status=pending" class="sidebar-item {{ request()->get('status') == 'pending' ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-amber-500">schedule</span>
                        Pending Review
                    </a>
                    <a href="{{ request()->url() }}?type=course&status=is_draft" class="sidebar-item {{ request()->get('status') == 'is_draft' ? 'active' : '' }}">
                        <span class="material-symbols-outlined text-slate-400">draft</span>
                        Drafts
                    </a>
                </nav>
            </div>

            <!-- Detailed Filters -->
            <div class="space-y-6">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[2px]">Advanced Filters</h3>
                <form method="get" class="space-y-4">
                    <input type="hidden" name="type" value="course">
                    <input type="hidden" name="status" value="{{ request()->get('status') }}">
                    
                    <div>
                        <label class="filter-label">Search</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                            <input id="courseSearchInput" name="title" value="{{ request()->get('title') }}" class="filter-input pl-10" placeholder="Title, Instructor..." type="text"/>
                        </div>
                    </div>

                    <div>
                        <label class="filter-label">Category</label>
                        <select name="category_id" class="filter-input">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @if(request()->get('category_id') == $category->id) selected @endif>{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="filter-label">Instructor</label>
                        <select name="teacher_ids[]" class="filter-input">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @if(in_array($teacher->id, (array)request()->get('teacher_ids'))) selected @endif>{{ $teacher->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 bg-primary text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">Apply Filters</button>
                        <a href="{{ request()->url() }}?type=course" class="size-10 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-slate-200 transition-all"><span class="material-symbols-outlined">refresh</span></a>
                    </div>
                </form>
            </div>

            <!-- Status Box (Dynamic System Health) -->
            <div class="mt-auto pt-6">
                <div class="bg-primary/5 rounded-3xl p-5 border border-primary/10">
                    <div class="flex items-center gap-2 text-primary mb-2">
                        <span class="material-symbols-outlined text-[16px] font-[FILL]">check_circle</span>
                        <span class="text-[10px] font-black uppercase tracking-widest">System Health: {{ $systemHealth }}%</span>
                    </div>
                    <p class="text-[11px] font-medium text-slate-500 leading-relaxed mb-0">
                        @if($systemHealth >= 80)
                            Your course catalog is highly optimized for conversion.
                        @elseif($systemHealth >= 50)
                            Your catalog is healthy, but some courses are still pending.
                        @else
                            Action required: Many courses are currently pending or in draft.
                        @endif
                    </p>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-6 md:p-10">
            
            <!-- Sleek Header -->
            <header class="flex items-center justify-between mb-10">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-primary text-white flex items-center justify-center shadow-lg shadow-primary/20">
                        <span class="material-symbols-outlined text-2xl">book</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Courses Management</h1>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-1">Manage, Edit and Track Performance</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ getAdminPanelUrl() }}/webinars/create" class="px-6 py-3 bg-primary text-white rounded-2xl text-[13px] font-black uppercase tracking-widest hover:bg-primary/90 shadow-lg shadow-primary/30 transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">add</span>
                        Create Course
                    </a>
                </div>
            </header>

            <!-- KPI Row (Simplified) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                @php
                    $kpis = [
                        ['label' => 'Total Courses', 'value' => $totalWebinars, 'icon' => 'menu_book', 'color' => 'primary'],
                        ['label' => 'Pending', 'value' => $totalPendingWebinars, 'icon' => 'pending_actions', 'color' => 'amber-500'],
                        ['label' => 'Active Students', 'value' => $totalSales, 'icon' => 'group', 'color' => 'blue-500'],
                        ['label' => 'In Progress', 'value' => $inProgressWebinars, 'icon' => 'monitoring', 'color' => 'purple-500'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                    <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $kpi['label'] }}</p>
                        <div class="flex items-center justify-between gap-2 text-slate-800">
                             <span class="text-xl font-black">{{ $kpi['value'] }}</span>
                             <span class="material-symbols-outlined text-{{ $kpi['color'] }} opacity-50">{{ $kpi['icon'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Courses Grid -->
            <div id="coursesListContainer" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                <!-- Skeleton State (Hidden by default, shown during refresh/filter) -->
                <template id="courseSkeleton">
                    <div class="bg-white rounded-[32px] border border-slate-200 overflow-hidden flex flex-col p-6 space-y-4">
                        <div class="aspect-[16/10] skeleton rounded-3xl w-full"></div>
                        <div class="h-4 skeleton w-3/4 rounded-lg"></div>
                        <div class="h-3 skeleton w-1/2 rounded-lg"></div>
                        <div class="flex gap-2 mt-4">
                            <div class="size-8 skeleton rounded-full"></div>
                            <div class="h-4 skeleton flex-1 rounded-lg"></div>
                        </div>
                    </div>
                </template>

                @foreach($webinars as $webinar)
                    @php
                        $statusClass = 'text-slate-400'; $statusLabel = 'Draft'; $icon = 'draft';
                        if($webinar->status == \App\Models\Webinar::$active) { 
                            $statusClass = 'text-primary'; $statusLabel = 'Active'; $icon = 'check_circle';
                        } elseif($webinar->status == 'pending') { 
                            $statusClass = 'text-amber-500'; $statusLabel = 'Pending'; $icon = 'schedule';
                        }
                    @endphp
                    <div class="bg-white rounded-[32px] border border-slate-200 overflow-hidden flex flex-col group transition-all hover:shadow-2xl hover:shadow-slate-200/50 hover:-translate-y-2 duration-500">
                        <div class="aspect-[16/10] relative overflow-hidden bg-slate-50">
                            <img src="{{ $webinar->thumbnail }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                            
                            <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
                                <span class="bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-full text-[9px] font-black text-slate-900 shadow-sm tracking-tight border border-slate-100">ID: COURSE-{{ $webinar->id }}</span>
                                <div class="p-1.5 bg-white/95 backdrop-blur-sm rounded-xl shadow-sm border border-slate-100 {{ $statusClass }}">
                                    <span class="material-symbols-outlined text-[18px] font-[FILL]">{{ $icon }}</span>
                                </div>
                            </div>

                            <div class="absolute bottom-4 left-4 right-4 capitalize">
                                <span class="text-[9px] font-black text-primary bg-primary/10 border border-primary/20 px-2 py-0.5 rounded-lg uppercase tracking-wider mb-2 inline-block backdrop-blur-md">{{ $webinar->category->title ?? 'General' }}</span>
                                <h3 class="text-white text-sm font-black leading-snug line-clamp-2">{{ $webinar->title }}</h3>
                            </div>
                        </div>

                        <div class="p-6 flex-1 flex flex-col">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="size-8 rounded-full bg-slate-100 overflow-hidden border-2 border-slate-50 shadow-sm">
                                    <img src="{{ $webinar->teacher->getAvatar() ?? '/assets/default/img/avatar.png' }}" class="w-full h-full object-cover" alt="">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11px] font-black text-slate-800 truncate">{{ $webinar->teacher->full_name ?? 'Instructor' }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">Course Mentor</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-6">
                                <div class="bg-slate-50/50 p-2.5 rounded-2xl border border-slate-100/50 group-hover:bg-slate-50 transition-colors">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-[1px] mb-1">Students</p>
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[14px] text-blue-500 font-[FILL]">group</span>
                                        <span class="text-xs font-black text-slate-700">{{ $webinar->sales->count() }}</span>
                                    </div>
                                </div>
                                <div class="bg-slate-50/50 p-2.5 rounded-2xl border border-slate-100/50 group-hover:bg-slate-50 transition-colors">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-[1px] mb-1">Revenue</p>
                                    <div class="flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[14px] text-emerald-500 font-[FILL]">payments</span>
                                        <span class="text-xs font-black text-emerald-600">{{ currencySign() }}{{ handlePrice($webinar->sales->sum('total_amount'), false) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-auto flex items-center justify-between pt-4 border-t border-slate-50">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Enrolment Fee</span>
                                    <span class="text-base font-black text-slate-900">{{ $webinar->price > 0 ? currencySign().number_format($webinar->price) : 'Free Admission' }}</span>
                                </div>
                                <div class="flex gap-2">
                                     <a href="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/edit" class="size-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-primary hover:text-white hover:shadow-lg hover:shadow-primary/30 transition-all duration-300" title="Edit Course Content">
                                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                    </a>
                                    <a href="{{ getAdminPanelUrl() }}/webinars/{{ $webinar->id }}/statistics" class="size-9 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-primary hover:text-white hover:shadow-lg hover:shadow-primary/30 transition-all duration-300" title="View Analytics">
                                        <span class="material-symbols-outlined text-[18px]">analytics</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modern Pagination -->
            <div class="mt-12 flex flex-col sm:flex-row items-center justify-between gap-6 py-8 border-t border-slate-200">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest bg-white px-5 py-2.5 rounded-full border border-slate-100 shadow-sm">
                    Displaying <span class="text-slate-900 font-black">{{ $webinars->firstItem() ?? 0 }} - {{ $webinars->lastItem() ?? 0 }}</span> of <span class="text-slate-900 font-black">{{ $webinars->total() }}</span> unique courses
                </div>
                <div>
                    {{ $webinars->appends(request()->input())->links('pagination::tailwind') }}
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

</div>
@endsection
