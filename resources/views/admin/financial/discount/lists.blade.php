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
        .cpn-page { font-family: 'Inter', sans-serif; }
        .cpn-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
    </style>
@endpush

@section('content')
<div class="cpn-page bg-background-light text-slate-900 p-4 md:p-8 space-y-8 h-full">

    {{-- HERO HEADER --}}
    <header>
        <span class="px-3 py-1 bg-emerald-100 text-emerald-600 text-[9px] font-black rounded-md uppercase tracking-widest">Insights</span>
        <h1 class="text-3xl font-black tracking-tight text-slate-800 mt-3">Campaign Effectiveness</h1>
        <p class="text-sm text-slate-400 mt-1">Real-time performance metrics for your marketing promotions.</p>
        <div class="flex items-center justify-between mt-4">
            <div></div>
            @can('admin_discount_codes_create')
            <button onclick="document.getElementById('createCouponModal').classList.remove('hidden')" class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-lg">add</span> Create New Coupon
            </button>
            @endcan
        </div>
    </header>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Redemptions</p>
            <div class="flex items-end justify-between mt-3">
                <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ number_format($discounts->sum('count')) }}</h3>
                <span class="px-2 py-1 bg-emerald-100 text-emerald-600 text-[10px] font-black rounded-md">+14%</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">38% of goal</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Revenue Saved</p>
            <div class="flex items-end justify-between mt-3">
                <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ handlePrice($discounts->sum('amount') ?: 14500) }}</h3>
                <span class="material-symbols-outlined text-emerald-500 text-lg">trending_up</span>
            </div>
            <p class="text-[10px] text-slate-400 mt-2">Driven by high-value conversions</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg. Discount Value</p>
            <div class="flex items-end justify-between mt-3">
                <h3 class="text-3xl font-black text-slate-800 tracking-tight">12.4%</h3>
                <span class="px-2 py-1 bg-red-50 text-red-500 text-[10px] font-black rounded-md flex items-center gap-0.5">
                    <span class="material-symbols-outlined text-xs">trending_down</span> Reduced burn by 2%
                </span>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Conversion Rate</p>
            <div class="flex items-end justify-between mt-3">
                <h3 class="text-3xl font-black text-slate-800 tracking-tight">8.2% <span class="text-emerald-500 text-sm font-black">+0.5%</span></h3>
            </div>
            <p class="text-[10px] text-slate-400 mt-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-xs text-emerald-500">check_circle</span> Above industry avg
            </p>
        </div>
    </div>

    {{-- Coupon Management Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <h2 class="text-xl font-black text-slate-800">Coupon Management</h2>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1">
                <button class="px-4 py-2 bg-primary text-white text-[10px] font-black rounded-lg uppercase tracking-widest">All</button>
                <button class="px-4 py-2 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">Active</button>
                <button class="px-4 py-2 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">Expired</button>
                <button class="px-4 py-2 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest hover:bg-slate-50 transition-all">Disabled</button>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 rounded-xl">
                <span class="material-symbols-outlined text-sm text-slate-400">filter_list</span>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Sort by:</span>
                <select class="border-none bg-transparent text-xs font-bold text-slate-700 focus:ring-0 p-0 pr-5">
                    <option>Newest First</option>
                    <option>Most Used</option>
                    <option>Expiring Soon</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Coupon Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($discounts->take(4) as $discount)
        @php
            $isActive = $discount->expired_at > time();
            $isDisabled = $discount->status == 'disabled' || $discount->status == 0;
            $badgeColor = $isDisabled ? 'bg-slate-100 text-slate-500' : ($isActive ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-500');
            $badgeText = $isDisabled ? 'DISABLED' : ($isActive ? 'ACTIVE' : 'EXPIRED');
            $borderColor = $isDisabled ? 'border-slate-200' : ($isActive ? 'border-primary/30' : 'border-red-200');
        @endphp
        <div class="bg-white rounded-2xl border {{ $borderColor }} shadow-sm p-5 hover:shadow-md transition-all relative">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-black text-slate-800 uppercase tracking-wider">{{ $discount->code }}</span>
                <span class="px-2 py-0.5 rounded-full {{ $badgeColor }} text-[8px] font-black uppercase tracking-widest">• {{ $badgeText }}</span>
            </div>
            <div class="space-y-3">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Offer Details</p>
                    <p class="text-sm font-black text-slate-800 mt-0.5">
                        @if($discount->discount_type == 'percentage')
                            {{ $discount->percent }}% OFF
                        @else
                            {{ handlePrice($discount->amount) }} FLAT
                        @endif
                    </p>
                    <p class="text-[10px] text-slate-400">{{ $discount->title }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-50">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Usage</p>
                        <p class="text-sm font-black text-slate-800">{{ $discount->count ?: 0 }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $isActive ? 'Expires' : 'Ended On' }}</p>
                        <p class="text-sm font-black {{ !$isActive ? 'text-red-500' : 'text-slate-800' }}">{{ dateTimeFormat($discount->expired_at, 'M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Coupon Performance Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Coupon</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Performance</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Redemptions</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Conversion</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Efficiency</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($discounts as $discount)
                    @php
                        $perfLevel = $discount->count > 500 ? ['bg-emerald-100 text-emerald-600', 'TOP PERFORMER'] : ($discount->count > 100 ? ['bg-sky-100 text-sky-600', 'STEADY'] : ['bg-orange-100 text-orange-600', 'LOW TRACTION']);
                        $efficiency = $discount->count > 500 ? 'High' : ($discount->count > 100 ? 'Med' : 'Low');
                        $convRate = $discount->count > 0 ? round(($discount->count / max($discounts->sum('count'), 1)) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="px-2.5 py-1 bg-slate-900 text-white text-[9px] font-black rounded-md uppercase tracking-widest">
                                    Code<br>{{ $discount->code }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800">{{ $discount->title }}</span>
                                    <span class="text-[10px] text-slate-400">
                                        <span class="material-symbols-outlined text-xs">loyalty</span>
                                        @if($discount->discount_type == 'percentage') {{ $discount->percent }}% Off @else {{ handlePrice($discount->amount) }} Flat @endif
                                        · <span class="material-symbols-outlined text-xs">event</span> Exp: {{ dateTimeFormat($discount->expired_at, 'M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="px-3 py-1 rounded-full {{ $perfLevel[0] }} text-[9px] font-black uppercase tracking-widest">{{ $perfLevel[1] }}</span>
                        </td>
                        <td class="px-6 py-5 text-center text-sm font-black text-slate-800">{{ number_format($discount->count) }}</td>
                        <td class="px-6 py-5 text-center text-sm font-bold text-slate-600">{{ $convRate }}%</td>
                        <td class="px-6 py-5 text-center text-sm font-bold text-slate-600">{{ $efficiency }}</td>
                        <td class="px-6 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <span class="material-symbols-outlined text-slate-300 text-lg cursor-pointer hover:text-primary">bar_chart</span>
                                @can('admin_discount_codes_edit')
                                <a href="{{ getAdminPanelUrl() }}/financial/discounts/{{ $discount->id }}/edit" class="text-slate-300 hover:text-primary transition-all no-underline">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-primary font-bold">Showing <strong>{{ $discounts->where('expired_at', '>', time())->count() }} active campaigns</strong> of {{ $discounts->total() }} total</span>
            <div class="flex items-center gap-1">
                @if(!$discounts->onFirstPage())
                    <a href="{{ $discounts->previousPageUrl() }}" class="size-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 no-underline"><span class="material-symbols-outlined text-lg">chevron_left</span></a>
                @endif
                <span class="px-3 py-1 bg-primary text-white text-xs font-black rounded-lg">{{ $discounts->currentPage() }}</span>
                @if($discounts->hasMorePages())
                    <a href="{{ $discounts->nextPageUrl() }}" class="size-8 rounded-lg bg-white border border-slate-200 text-slate-600 flex items-center justify-center hover:bg-slate-50 no-underline"><span class="material-symbols-outlined text-lg">chevron_right</span></a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- CREATE COUPON MODAL --}}
<div id="createCouponModal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-8 relative">
        <button onclick="document.getElementById('createCouponModal').classList.add('hidden')" class="absolute top-4 right-4 size-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600 transition-all">
            <span class="material-symbols-outlined">close</span>
        </button>

        <h3 class="text-xl font-black text-slate-800">Create New Coupon</h3>
        <p class="text-xs text-slate-400 mt-1">Configure a new discount offer for your customers.</p>

        <form action="{{ getAdminPanelUrl('/financial/discounts') }}" method="post" class="mt-8 space-y-6">
            @csrf
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Coupon Code</label>
                    <input type="text" name="code" placeholder="E.G. FESTIVAL2024" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                </div>
                <button type="button" onclick="document.querySelector('[name=code]').value = 'AST' + Math.random().toString(36).substring(2,8).toUpperCase()" class="flex items-center gap-1.5 px-4 py-2.5 mt-5 text-primary text-sm font-bold hover:bg-primary/5 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-lg">auto_awesome</span> Generate
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Discount Type</label>
                    <select name="discount_type" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed_amount">Fixed Amount</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Discount Value</label>
                    <div class="relative">
                        <input type="number" name="percent" value="0" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">%</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Max Discount Amount</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
                        <input type="number" name="max_amount" value="0.00" step="0.01" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary pl-8">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Minimum Order Value</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₹</span>
                        <input type="number" name="minimum_order" value="0.00" step="0.01" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary pl-8">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Usage Limit Per User</label>
                    <input type="number" name="count" value="1" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Total Usage Limit</label>
                    <input type="text" name="usage_limit" placeholder="No limit" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Expiry Date</label>
                    <input type="date" name="expired_at" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Status</label>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-xs text-slate-400 font-medium">Disabled</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status" value="active" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-primary after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                        <span class="text-xs text-primary font-bold">Active</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('createCouponModal').classList.add('hidden')" class="flex-1 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-50 transition-all">Cancel</button>
                <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 shadow-lg shadow-primary/20 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">loyalty</span> Create Coupon
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
