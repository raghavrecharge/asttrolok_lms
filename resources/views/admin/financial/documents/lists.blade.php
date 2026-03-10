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
                    fontFamily: { "display": ["Inter", "sans-serif"], "body": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .wlt-page { font-family: 'Inter', sans-serif; }
        .wlt-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .detail-panel { transition: transform 0.3s ease; transform: translateX(100%); }
        .detail-panel.open { transform: translateX(0); }
        .select2-container--default .select2-selection--multiple, .select2-container--default .select2-selection--single { border-radius: 0.75rem; border-color: #e2e8f0; padding: 2px 8px; height: auto !important; }
        .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: #32A128; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 32px; font-size: 0.875rem; color: #475569; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    </style>
@endpush

@section('content')
@php
    $totalBalance = 0; $netChange = 0;
    if ($documents->count() > 0) {
        foreach ($documents as $doc) {
            if ($doc->type == \App\Models\Accounting::$addiction) { $totalBalance += $doc->amount; $netChange += $doc->amount; }
            elseif ($doc->type == \App\Models\Accounting::$deduction) { $totalBalance -= $doc->amount; $netChange -= $doc->amount; }
        }
    }
    $activeWallets = $documents->pluck('user_id')->unique()->count();
@endphp

<div class="wlt-page bg-background-light text-slate-900 p-4 md:p-8 space-y-6 h-full">

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <div class="size-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-lg">account_balance</span>
                </div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Static</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Wallet Balance</p>
            <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-1">{{ handlePrice(abs($totalBalance)) }}</h3>
            <p class="text-[10px] text-emerald-500 font-bold mt-1">↑ +4.3% vs last month</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <div class="size-8 rounded-lg bg-sky-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-sky-500 text-lg">swap_vert</span>
                </div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Static</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Net Balance Change</p>
            <h3 class="text-2xl font-black text-{{ $netChange >= 0 ? 'emerald' : 'red' }}-600 tracking-tight mt-1">
                {{ $netChange >= 0 ? '+' : '' }}{{ handlePrice($netChange) }}
            </h3>
            <p class="text-[10px] text-slate-400 mt-1">↑ Calculated from {{ number_format($documents->total()) }} txn</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <div class="size-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-500 text-lg">group</span>
                </div>
                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Static</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Wallets</p>
            <h3 class="text-2xl font-black text-slate-800 tracking-tight mt-1">{{ number_format($activeWallets) }}</h3>
            <p class="text-[10px] text-slate-400 mt-1">88% of total registered users</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Daily Fluctuations</p>
                <span class="text-[9px] font-bold text-slate-400 ml-auto">30 Oct</span>
            </div>
            <p class="text-[10px] font-bold text-slate-500 mb-2">Balance Trend</p>
            <div class="flex items-end gap-1 h-12">
                @for($i = 0; $i < 7; $i++)
                <div class="flex-1 bg-primary/{{ rand(10,80) }} rounded-sm" style="height: {{ rand(30,100) }}%"></div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <form action="{{ getAdminPanelUrl() }}/financial/documents" method="get" class="mb-0">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Search User</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                        <select name="user[]" multiple="multiple" class="w-full search-user-select2 pl-9">
                            @if(request()->get('user', null))
                                @foreach(request()->get('user') as $userId)
                                    <option value="{{ $userId }}" selected="selected">{{ $users[$userId]->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Transaction Type</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">filter_list</span>
                        <select name="type" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 pl-9 focus:ring-4 focus:ring-primary/10 focus:border-primary">
                            <option value="all" @if(request()->get('type') == 'all') selected @endif>All Types</option>
                            <option value="addiction" @if(request()->get('type') == 'addiction') selected @endif>{{ trans('admin/main.addiction') }}</option>
                            <option value="deduction" @if(request()->get('type') == 'deduction') selected @endif>{{ trans('admin/main.deduction') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Date Range</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">calendar_today</span>
                        <input type="text" name="from" value="{{ request()->get('from') }}" class="w-full bg-white border border-slate-200 rounded-xl text-sm font-bold h-11 pl-9 focus:ring-4 focus:ring-primary/10 focus:border-primary datefilter" placeholder="Oct 01, 2023 - Oct 31, 2023">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full bg-primary text-white px-5 py-3 rounded-xl text-sm font-bold transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 hover:bg-primary/90">
                        <span class="material-symbols-outlined text-lg">filter_alt</span> Apply Filters
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Transaction Logs Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Detailed Transaction Logs</h3>
                <p class="text-[10px] text-slate-400 mt-0.5">History of all credits and debits</p>
            </div>
            <button class="flex items-center gap-1.5 px-4 py-2 text-slate-500 text-xs font-bold hover:bg-slate-50 rounded-xl transition-all border border-slate-200">
                <span class="material-symbols-outlined text-sm">download</span> Export CSV
            </button>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Transaction ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Type</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Amount</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Balance After</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Date</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($documents as $document)
                    @php
                        $isCredit = $document->type == \App\Models\Accounting::$addiction;
                        $typeBadge = $isCredit ? ['bg-primary/10 text-primary', 'TOPUP'] : ['bg-red-50 text-red-500', 'PAYMENT'];
                        if ($document->is_cashback) $typeBadge = ['bg-amber-50 text-amber-600', 'REFUND'];
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer" 
                        onclick="openDetail(this)"
                        data-id="{{ $document->id }}"
                        data-user-name="{{ $document->user->full_name ?? '—' }}"
                        data-user-email="{{ $document->user->email ?? '—' }}"
                        data-user-avatar="{{ $document->user->getAvatar() }}"
                        data-user-balance="{{ handlePrice($document->user->getAccountingBalance() ?? 0) }}"
                        data-amount="{{ ($isCredit ? '+' : '-') . handlePrice($document->amount) }}"
                        data-type="{{ $typeBadge[1] }}"
                        data-is-credit="{{ $isCredit ? 'true' : 'false' }}">
                        <td class="px-6 py-5 text-xs font-bold text-slate-400">#TRX-{{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-5">
                            @if(!empty($document->user))
                            <div class="flex items-center gap-3">
                                <img src="{{ $document->user->getAvatar() }}" class="size-9 rounded-full border-2 border-slate-100 object-cover" alt="">
                                <div>
                                    <a href="{{ getAdminPanelUrl("/users/{$document->user_id}/edit") }}" class="text-sm font-bold text-slate-800 hover:text-primary transition-colors no-underline">{{ $document->user->full_name }}</a>
                                    <p class="text-[10px] text-slate-400">UID: AST-{{ $document->user_id }}</p>
                                </div>
                            </div>
                            @else
                                <span class="text-sm text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="px-3 py-1 rounded-full {{ $typeBadge[0] }} text-[9px] font-black uppercase tracking-widest">{{ $typeBadge[1] }}</span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <span class="text-sm font-black {{ $isCredit ? 'text-primary' : 'text-red-500' }}">
                                {{ $isCredit ? '+' : '-' }}{{ handlePrice($document->amount) }}
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right text-sm font-bold text-slate-700">
                            {{ handlePrice(abs($totalBalance)) }}
                        </td>
                        <td class="px-6 py-5 text-right">
                            <p class="text-xs font-bold text-slate-700">{{ dateTimeFormat($document->created_at, 'M d, Y') }}</p>
                            <p class="text-[10px] text-slate-400">{{ dateTimeFormat($document->created_at, 'h:i A') }}</p>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="material-symbols-outlined text-slate-300 text-lg hover:text-primary cursor-pointer">more_vert</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-primary font-bold">Showing 1 to {{ $documents->count() }} of {{ number_format($documents->total()) }} transactions</span>
            <div class="flex items-center gap-1">
                @if(!$documents->onFirstPage())
                    <a href="{{ $documents->previousPageUrl() }}" class="px-3 py-1 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-lg no-underline">Previous</a>
                @endif
                <span class="px-3 py-1 bg-primary text-white text-xs font-black rounded-lg">{{ $documents->currentPage() }}</span>
                @if($documents->hasMorePages())
                    <a href="{{ $documents->nextPageUrl() }}" class="px-3 py-1 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-lg no-underline">Next</a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- TRANSACTION DETAIL SLIDE-OUT PANEL --}}
<div id="detailOverlay" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50" onclick="closeDetail()"></div>
<div id="detailPanel" class="detail-panel fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 overflow-y-auto">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-lg font-black text-slate-800">Transaction Details</h3>
        <button onclick="closeDetail()" class="size-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <div class="p-6 space-y-5" id="detailContent">
        <div class="bg-slate-50 rounded-xl p-4 flex items-center gap-3">
            <img id="dtlUserAvatar" src="/store/default_images/default_avatar.png" class="size-12 rounded-full border-2 border-slate-200" alt="">
            <div>
                <p class="text-sm font-black text-slate-800" id="dtlUserName">User Name</p>
                <p class="text-[10px] text-slate-400" id="dtlUserEmail">user@email.com</p>
                <p class="text-[10px] text-primary font-bold" id="dtlUserBalance">Balance: ₹0</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Status</p>
                <div class="flex items-center gap-1.5 mt-1"><span class="size-2 rounded-full bg-emerald-500"></span><span class="text-sm font-bold text-slate-700">Completed</span></div>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Reference ID</p>
                <p class="text-sm font-bold text-slate-700" id="dtlRefId">TRX-000001</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Type</p>
                <p class="text-sm font-bold text-slate-700 flex items-center gap-1" id="dtlType">TOPUP</p>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Processed By</p>
                <p class="text-sm font-bold text-slate-700">System</p>
            </div>
        </div>

        <div id="dtlAmountBox" class="bg-emerald-50 rounded-xl p-5 text-center mt-4">
            <p id="dtlAmountLabel" class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-2">Transaction Amount</p>
            <p class="text-3xl font-black text-emerald-600" id="dtlAmount">+₹5,000.00</p>
        </div>

        <div>
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Support Notes</label>
            <textarea placeholder="Add internal notes for this transaction..." class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium p-3 focus:ring-4 focus:ring-primary/10 focus:border-primary" rows="3"></textarea>
        </div>

        <button class="w-full py-3 bg-primary text-white rounded-xl text-sm font-bold flex items-center justify-center gap-2 hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-lg">chat</span> Message User
        </button>
        <div class="grid grid-cols-2 gap-3">
            <button class="py-2.5 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">Send Receipt</button>
            <button class="py-2.5 border border-red-200 rounded-xl text-xs font-bold text-red-500 hover:bg-red-50 transition-all">Process Refund</button>
        </div>
    </div>
</div>

<script>
function openDetail(row) {
    const data = row.dataset;
    document.getElementById('detailOverlay').classList.remove('hidden');
    document.getElementById('detailPanel').classList.add('open');
    
    document.getElementById('dtlRefId').textContent = 'TRX-' + String(data.id).padStart(6, '0');
    document.getElementById('dtlUserName').textContent = data.userName;
    document.getElementById('dtlUserEmail').textContent = data.userEmail;
    document.getElementById('dtlUserAvatar').src = data.userAvatar;
    document.getElementById('dtlUserBalance').textContent = 'Balance: ' + data.userBalance;
    document.getElementById('dtlAmount').textContent = data.amount;
    document.getElementById('dtlType').textContent = data.type;

    const amountBox = document.getElementById('dtlAmountBox');
    const amountLabel = document.getElementById('dtlAmountLabel');
    const amountText = document.getElementById('dtlAmount');

    if (data.isCredit === 'true') {
        amountBox.className = 'bg-emerald-50 rounded-xl p-5 text-center mt-4';
        amountLabel.className = 'text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-2';
        amountText.className = 'text-3xl font-black text-emerald-600';
    } else {
        amountBox.className = 'bg-red-50 rounded-xl p-5 text-center mt-4';
        amountLabel.className = 'text-[9px] font-black text-red-500 uppercase tracking-widest mb-2';
        amountText.className = 'text-3xl font-black text-red-600';
    }
}
function closeDetail() {
    document.getElementById('detailOverlay').classList.add('hidden');
    document.getElementById('detailPanel').classList.remove('open');
}
</script>
@endsection

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            if (jQuery().select2) {
                $('.search-user-select2').select2({
                    placeholder: "Name, Email or ID",
                    allowClear: true
                });
            }
        });
    </script>
@endpush
