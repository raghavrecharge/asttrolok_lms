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
                        "background-light": "#f6f8f5",
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
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 h-full">

    <!-- Title & Header Actions -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined">receipt_long</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ trans('update.installment_plans') }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Configuration & Plan Management</p>
            </div>
        </div>
        <div class="flex items-center gap-4">
            @can('admin_installments_create')
                <a href="{{ getAdminPanelUrl() }}/financial/installments/create" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-md shadow-primary/20">
                    <span class="material-symbols-outlined text-sm">add</span>
                    {{ trans('update.new_installment_plan') }}
                </a>
            @endcan
        </div>
    </header>

    <!-- Plans Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-[11px] uppercase tracking-wider border-b border-slate-100 dark:border-slate-800">
                        <th class="px-6 py-4 font-bold">{{ trans('admin/main.title') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ trans('update.upfront') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ trans('update.number_of_installments') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ trans('update.amount_of_installments') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ trans('admin/main.capacity') }}</th>
                        <th class="px-6 py-4 font-bold text-center">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-4 font-bold text-right">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($installments as $installment)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $installment->title }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase font-black tracking-tighter mt-0.5">{{ trans('update.target_types_'.$installment->target_type) }}</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Created: {{ dateTimeFormat($installment->created_at, 'j M Y') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                    {{ ($installment->upfront_type == 'percent') ? $installment->upfront.'%' : handlePrice($installment->upfront) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-bold">
                                    {{ $installment->steps_count }} Steps
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $stepsFixedAmount = $installment->steps->where('amount_type', 'fixed_amount')->sum('amount');
                                    $stepsPercents = $installment->steps->where('amount_type', 'percent')->sum('amount');
                                @endphp
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-bold text-primary">{{ $stepsFixedAmount ? handlePrice($stepsFixedAmount) : '' }}</span>
                                    @if($stepsPercents)
                                        <span class="text-[10px] font-bold text-slate-400">{{ $stepsFixedAmount ? '+ ' : '' }}{{ $stepsPercents }}%</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-sm font-medium text-slate-600 dark:text-slate-400">
                                    {{ $installment->capacity ?? 'Unlimited' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($installment->enable)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-widest">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-400 uppercase tracking-widest">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('admin_promotion_edit')
                                        <a href="{{ getAdminPanelUrl("/financial/installments/{$installment->id}/edit") }}" class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-all" title="{{ trans('admin/main.edit') }}">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                    @endcan
                                    @can('admin_promotion_delete')
                                        @include('admin.includes.delete_button',['url' => getAdminPanelUrl('/financial/installments/'. $installment->id.'/delete'), 'btnClass' => 'p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all'])
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($installments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800">
                {{ $installments->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
