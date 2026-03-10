@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Cashback</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_cashback_rules')
                    <a href="{{ getAdminPanelUrl() }}/cashback/rules/create" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-opacity-90 transition-all shadow-sm">
                        <span class="material-symbols-rounded text-xl">add_circle</span>
                        {{ trans('update.new_rule') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            {{-- Stats --}}
            @include('admin.cashback.rules.lists.stats')

            {{-- Filters --}}
            @include('admin.cashback.rules.lists.filters')

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.target_type') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.amount') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('public.paid_amount') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.users') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($rules as $rule)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-primary/5 flex items-center justify-center text-primary overflow-hidden border border-primary/10">
                                                <span class="material-symbols-rounded text-xl group-hover:scale-110 transition-transform tracking-tight">recurrency</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 line-clamp-1 group-hover:text-primary transition-colors tracking-tight">{{ $rule->title }}</span>
                                                <div class="flex items-center gap-2 mt-0.5 text-[10px] text-gray-400 font-bold font-mono tracking-widest uppercase">
                                                    <span>{{ $rule->start_date ? dateTimeFormat($rule->start_date, 'Y M j') : 'Anytime' }}</span>
                                                    <span class="text-gray-200">/</span>
                                                    <span>{{ $rule->end_date ? dateTimeFormat($rule->end_date, 'Y M j') : trans('update.unlimited') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-600 rounded-lg border border-gray-200 uppercase tracking-widest">
                                            {{ trans('update.target_types_'.$rule->target_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-bold text-primary tracking-tight">
                                            {{ ($rule->amount_type == 'percent') ? $rule->amount.'%' : handlePrice($rule->amount) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700 tracking-tight">
                                        {{ handlePrice(0) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-bold text-gray-700 tracking-tight">0</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($rule->enable)
                                            <span class="inline-flex items-center gap-1 text-emerald-600 px-3 py-1 bg-emerald-50 rounded-full border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ trans('admin/main.active') }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-gray-400 px-3 py-1 bg-gray-50 rounded-full border border-gray-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ trans('admin/main.inactive') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_cashback_rules')
                                                <a href="{{ getAdminPanelUrl("/cashback/rules/{$rule->id}/edit") }}" 
                                                   class="p-2 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-all"
                                                   title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-xl">edit_note</span>
                                                </a>
                                            @endcan

                                            @can('admin_cashback_rules')
                                                <a href="{{ getAdminPanelUrl('/cashback/rules/'. $rule->id.'/statusToggle') }}" 
                                                   class="p-2 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-xl transition-all"
                                                   title="{{ $rule->enable ? trans('admin/main.inactive') : trans('admin/main.active') }}">
                                                    <span class="material-symbols-rounded text-xl">{{ $rule->enable ? 'visibility_off' : 'visibility' }}</span>
                                                </a>
                                            @endcan

                                            @can('admin_cashback_rules')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl('/cashback/rules/'. $rule->id.'/delete'),
                                                    'btnClass' => 'p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all'
                                                ])
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($rules->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center">
                        {{ $rules->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
