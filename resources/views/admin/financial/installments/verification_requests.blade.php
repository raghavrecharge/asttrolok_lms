@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "12px",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
@endpush

@section('content')
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 h-full min-h-screen">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                <span class="material-symbols-outlined">rule</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ trans('update.verification_requests') }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Awaiting Admin Approval</p>
            </div>
        </div>
    </header>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 font-bold">User</th>
                        <th class="px-6 py-4 font-bold text-left">Installment Plan</th>
                        <th class="px-6 py-4 font-bold text-center">Product</th>
                        <th class="px-6 py-4 font-bold text-center">Total / Upfront</th>
                        <th class="px-6 py-4 font-bold text-center">Request Date</th>
                        <th class="px-6 py-4 font-bold text-center">Status</th>
                        <th class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <!-- User -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $order->user->getAvatar() }}" alt="{{ $order->user->full_name }}" class="size-10 rounded-full border-2 border-slate-100 dark:border-slate-800 object-cover">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $order->user->full_name }}</span>
                                        <span class="text-[10px] font-semibold text-slate-400 truncate max-w-[150px]">{{ $order->user->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Plan -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $order->installment->title }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ trans('update.target_types_'.$order->installment->target_type) }}</span>
                                </div>
                            </td>

                            <!-- Product -->
                            <td class="px-6 py-4 text-center">
                                @if(!empty($order->webinar_id))
                                    <div class="flex flex-col items-center">
                                        <a href="{{ !empty($order->webinar) ? $order->webinar->getUrl() : '#' }}" target="_blank" class="text-xs font-bold text-primary hover:underline">#{{ $order->webinar_id }}</a>
                                        <span class="text-[10px] font-bold text-slate-400">Course</span>
                                    </div>
                                @elseif(!empty($order->bundle_id))
                                    <div class="flex flex-col items-center">
                                        <a href="{{ !empty($order->bundle) ? $order->bundle->getUrl() : '#' }}" target="_blank" class="text-xs font-bold text-primary hover:underline">#{{ $order->bundle_id }}</a>
                                        <span class="text-[10px] font-bold text-slate-400">Bundle</span>
                                    </div>
                                @else
                                    <span class="text-xs font-medium text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Pricing -->
                            <td class="px-6 py-4 text-center">
                                @php $itemPrice = $order->getItemPrice(); @endphp
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-black text-slate-800 dark:text-white">{{ handlePrice($order->installment->totalPayments($itemPrice)) }}</span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Upfront: {{ ($order->installment->upfront_type == 'percent') ? $order->installment->upfront.'%' : handlePrice($order->installment->upfront) }}</span>
                                </div>
                            </td>

                            <!-- Date -->
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ dateTimeFormat($order->created_at, 'j M Y') }}</span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 text-center">
                                @if($order->status == "pending_verification")
                                    <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest animate-pulse">Pending</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-400 text-[10px] font-black uppercase tracking-widest">{{ $order->status }}</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/details") }}" class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-all" title="View Details">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    
                                    <div class="relative group/menu inline-block">
                                        <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                                            <span class="material-symbols-outlined text-[20px] text-slate-400">more_vert</span>
                                        </button>
                                        <div class="absolute right-0 bottom-full mb-2 w-48 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl py-2 z-50 invisible group-hover/menu:visible opacity-0 group-hover/menu:opacity-100 transition-all">
                                            @if($order->status == "pending_verification")
                                                <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/approve") }}")' class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-emerald-600 hover:bg-emerald-50">
                                                    <span class="material-symbols-outlined text-sm">check_circle</span> Approve
                                                </a>
                                                <a href="javascript:void(0)" onclick='confirmAndSubmit("{{ getAdminPanelUrl("/financial/installments/orders/{$order->id}/reject") }}")' class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
                                                    <span class="material-symbols-outlined text-sm">cancel</span> Reject
                                                </a>
                                            @endif
                                            <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                            <a href="{{ getAdminPanelUrl() }}/users/{{ $order->user_id }}/edit" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                                                <span class="material-symbols-outlined text-sm">edit</span> Edit User
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-bold uppercase tracking-widest">No pending requests found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $orders->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>

<script>
    function confirmAndSubmit(url) {
        if(confirm('Are you sure you want to proceed?')) {
            window.location.href = url;
        }
    }
</script>
@endsection
