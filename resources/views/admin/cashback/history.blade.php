@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Cashback / History</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_cashback_transactions')
                    <a href="{{ getAdminPanelUrl('/cashback/history/excel?'. http_build_query(request()->all())) }}" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl text-gray-400">download</span>
                        {{ trans('admin/main.export_xls') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {{-- Total Users --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">group</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{trans('update.cashback_users')}}</p>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ $totalUsers }}</h3>
                    </div>
                </div>

                {{-- Total Purchase --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">shopping_cart</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{trans('update.total_purchase')}}</p>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ handlePrice($totalPurchase) }}</h3>
                    </div>
                </div>

                {{-- Total Cashback --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{trans('update.total_cashback')}}</p>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ handlePrice($totalCashback) }}</h3>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="get" class="mb-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-4 items-end">
                        <div class="form-group space-y-2 lg:col-span-1">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.search')}}</label>
                            <div class="relative group">
                                <input name="title" type="text" class="w-full pl-11 pr-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" value="{{ request()->get('title') }}" placeholder="Search...">
                                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-xl">search</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.start_date')}}</label>
                            <input type="date" name="from" value="{{ request()->get('from') }}" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>

                        <div class="form-group space-y-2">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.end_date')}}</label>
                            <input type="date" name="to" value="{{ request()->get('to') }}" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>

                        @php
                            $filters = ['purchase_amount_asc', 'purchase_amount_desc', 'cashback_amount_asc', 'cashback_amount_desc', 'last_cashback_asc', 'last_cashback_desc'];
                        @endphp
                        <div class="form-group space-y-2">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.filters')}}</label>
                            <div class="relative">
                                <select name="sort" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{trans('admin/main.all')}}</option>
                                    @foreach($filters as $filter)
                                        <option value="{{ $filter }}" @if(request()->get('sort') == $filter) selected @endif>{{trans('update.'.$filter)}}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">sort</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2 lg:col-span-1">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.user')}}</label>
                            <select name="user_ids[]" multiple="multiple" class="search-user-select2" data-placeholder="Search users">
                                @if(!empty($selectedUsers) and $selectedUsers->count() > 0)
                                    @foreach($selectedUsers as $user)
                                        <option value="{{ $user->id }}" selected>{{ $user->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group space-y-2">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('update.min_purchase_amount')}}</label>
                            <input type="text" name="min_purchase_amount" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" value="{{ request()->get('min_purchase_amount') }}">
                        </div>

                        <div class="form-group space-y-2">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('update.min_cashback_amount')}}</label>
                            <input type="text" name="min_cashback_amount" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" value="{{ request()->get('min_cashback_amount') }}">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-sm flex items-center justify-center gap-2">
                                <span class="material-symbols-rounded text-xl">filter_list</span>
                                {{trans('admin/main.show_results')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Lists --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.user') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.total_purchase') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.total_cashback') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.last_cashback') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($transactions as $transaction)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <img src="{{ $transaction->user->getAvatar() }}" class="w-10 h-10 rounded-xl object-cover border border-gray-100 shadow-sm" alt="{{ $transaction->user->full_name }}">
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-primary border-2 border-white flex items-center justify-center shadow-sm">
                                                    <span class="material-symbols-rounded text-white text-[10px]">person</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 line-clamp-1 group-hover:text-primary transition-colors tracking-tight">{{ $transaction->user->full_name }}</span>
                                                <span class="text-[10px] text-gray-400 font-bold font-mono tracking-widest uppercase truncate max-w-[150px]">{{ $transaction->user->email ?? $transaction->user->mobile }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700 tracking-tight">
                                        {{ $transaction->purchase_amount ? handlePrice($transaction->purchase_amount) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-primary tracking-tight">
                                        {{ handlePrice($transaction->total_cashback) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ dateTimeFormat($transaction->last_cashback, 'j M Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_users_impersonate')
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $transaction->user_id }}/impersonate" target="_blank" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all" title="{{ trans('admin/main.login') }}">
                                                    <span class="material-symbols-rounded text-xl">admin_panel_settings</span>
                                                </a>
                                            @endcan

                                            @can('admin_users_edit')
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $transaction->user_id }}/edit" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all" title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-xl">edit</span>
                                                </a>
                                            @endcan

                                            @can('admin_cashback_transactions')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl("/users/{$transaction->user_id}/disable_cashback_toggle"),
                                                    'tooltip' => $transaction->user->disable_cashback ? trans('update.enable_cashback') : trans('update.disable_cashback'),
                                                    'btnIcon' => $transaction->user->disable_cashback ? 'check_circle' : 'cancel',
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

                @if($transactions->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center">
                        {{ $transactions->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
