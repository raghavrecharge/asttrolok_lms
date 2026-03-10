<div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $title ?? 'Management' }}</h2>
        <p class="text-slate-500 text-sm mt-1">{{ $subtitle ?? '' }}</p>
    </div>
    
    <form method="get" id="umFilterForm" class="flex items-center gap-3 flex-wrap">
        {{-- Search Input --}}
        <div class="relative group min-w-[300px]">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
            <input type="text" name="search" value="{{ request()->get('search') ?? request()->get('q') ?? request()->get('full_name') ?? request()->get('title') }}" 
                   class="w-full pl-12 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-primary/10 text-sm transition-all placeholder:text-slate-400 shadow-sm" 
                   placeholder="Search...">
        </div>

        <div class="flex items-center bg-white border border-slate-200 rounded-2xl p-1 shadow-sm">
            {{-- Dropdown 1: Usually Role or Category --}}
            @if(isset($filters['role']))
            <div class="relative flex items-center">
                <select name="{{ $filters['role']['name'] ?? 'role_id' }}" class="bg-transparent border-none text-sm font-semibold text-slate-600 focus:ring-0 cursor-pointer pl-4 pr-10 py-2 appearance-none z-10" onchange="this.form.submit()">
                    <option value="">{{ $filters['role']['label'] ?? 'All Roles' }}</option>
                    @foreach($filters['role']['options'] as $value => $label)
                        <option value="{{ $value }}" {{ (request()->get($filters['role']['name'] ?? 'role_id') == $value || request()->get('role') == $value) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 text-slate-400 pointer-events-none text-lg">expand_more</span>
            </div>
            <div class="w-px h-5 bg-slate-200 mx-2"></div>
            @endif

            {{-- Dropdown 2: Status --}}
            @if(isset($filters['status']))
            <div class="relative flex items-center">
                <select name="{{ $filters['status']['name'] ?? 'status' }}" class="bg-transparent border-none text-sm font-semibold text-slate-600 focus:ring-0 cursor-pointer pl-4 pr-10 py-2 appearance-none z-10" onchange="this.form.submit()">
                    <option value="">{{ $filters['status']['label'] ?? 'All Status' }}</option>
                    @foreach($filters['status']['options'] as $value => $label)
                        <option value="{{ $value }}" {{ (request()->get($filters['status']['name'] ?? 'status') == $value || request()->get('status_filter') == $value) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 text-slate-400 pointer-events-none text-lg">expand_more</span>
            </div>
            <div class="w-px h-5 bg-slate-200 mx-2"></div>
            @endif

            {{-- Date Filter --}}
            <div class="relative flex items-center" id="dateRangePicker">
                <button type="button" class="flex items-center gap-3 px-5 py-2 text-sm font-semibold text-slate-600 hover:text-primary transition-colors dropdown-toggle" data-toggle="dropdown">
                    <span class="material-symbols-outlined text-xl text-slate-400">calendar_month</span>
                    <span id="selectedDateRangeLabel">
                        @if(request()->get('from') && request()->get('to'))
                            {{ request()->get('from') }} - {{ request()->get('to') }}
                        @else
                            Date Range
                        @endif
                    </span>
                </button>
                <div class="dropdown-menu p-4 shadow-xl border-none rounded-3xl min-w-[200px]">
                    <div class="space-y-2">
                        <button type="button" onclick="setDateRange('today')" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 rounded-xl transition-colors">Today</button>
                        <button type="button" onclick="setDateRange('7days')" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 rounded-xl transition-colors">Last 7 Days</button>
                        <button type="button" onclick="setDateRange('30days')" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 rounded-xl transition-colors">Last 30 Days</button>
                        <button type="button" onclick="setDateRange('90days')" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 rounded-xl transition-colors">Last 90 Days</button>
                        <div class="pt-2 border-t border-slate-100 mt-2">
                             <a href="javascript:void(0)" onclick="resetFilters()" class="w-full text-center block px-4 py-2 text-xs font-bold text-rose-500 hover:bg-rose-50 rounded-xl transition-colors">Reset Filters</a>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="from" id="filter_from" value="{{ request()->get('from') }}">
                <input type="hidden" name="to" id="filter_to" value="{{ request()->get('to') }}">
            </div>
        </div>

        @if(isset($actions))
            @foreach($actions as $action)
                {!! $action !!}
            @endforeach
        @endif
    </form>
</div>

<script>
function setDateRange(range) {
    const fromInput = document.getElementById('filter_from');
    const toInput = document.getElementById('filter_to');
    const today = new Date();
    let fromDate = new Date();

    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };

    toInput.value = formatDate(today);

    switch(range) {
        case 'today':
            fromDate = today;
            break;
        case '7days':
            fromDate.setDate(today.getDate() - 7);
            break;
        case '30days':
            fromDate.setDate(today.getDate() - 30);
            break;
        case '90days':
            fromDate.setDate(today.getDate() - 90);
            break;
    }

    fromInput.value = formatDate(fromDate);
    document.getElementById('umFilterForm').submit();
}

function resetFilters() {
    const form = document.getElementById('umFilterForm');
    form.querySelectorAll('input:not([type="hidden"]), select').forEach(el => el.value = '');
    form.querySelectorAll('input[type="hidden"]').forEach(el => el.value = '');
    form.submit();
}
</script>
