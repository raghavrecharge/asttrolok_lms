@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Cashback / Transactions</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_cashback_transactions')
                    <a href="{{ getAdminPanelUrl('/cashback/transactions/excel?'. http_build_query(request()->all())) }}" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl text-gray-400">download</span>
                        {{ trans('admin/main.export_xls') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            {{-- Filters --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="get" class="mb-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4 items-end">
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
                            $filters = ['purchase_amount_asc', 'purchase_amount_desc', 'cashback_amount_asc', 'cashback_amount_desc', 'date_asc', 'date_desc'];
                        @endphp
                        <div class="form-group space-y-2">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.filters')}}</label>
                            <div class="relative text-gray-700">
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
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{trans('admin/main.type')}}</label>
                            <div class="relative">
                                <select name="target_type" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{trans('admin/main.all')}}</option>
                                    @foreach(\App\Models\CashbackRule::$targetTypes as $type)
                                        <option value="{{ $type }}" @if(request()->get('target_type') == $type) selected @endif>{{ trans('update.target_types_'.$type) }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">category</span>
                            </div>
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
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('update.product') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.amount') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.cashback_amount') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.date') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.status') }}</th>
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
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-primary border-2 border-white flex items-center justify-center">
                                                    <span class="material-symbols-rounded text-white text-[10px]">person</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 line-clamp-1 group-hover:text-primary transition-colors tracking-tight">{{ $transaction->user->full_name }}</span>
                                                <span class="text-[10px] text-gray-400 font-bold font-mono tracking-widest uppercase">{{ $transaction->user->email ?? $transaction->user->mobile }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col max-w-[200px]">
                                            @if(!empty($transaction->webinar_id))
                                                <a href="{{ !empty($transaction->webinar) ? $transaction->webinar->getUrl() : '#' }}" target="_blank" class="font-bold text-gray-900 group-hover:text-primary transition-colors truncate">
                                                    {{ !empty($transaction->webinar) ? $transaction->webinar->title : "Item #{$transaction->webinar_id}" }}
                                                </a>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ trans('update.target_types_courses') }}</span>
                                            @elseif(!empty($transaction->bundle_id))
                                                <a href="{{ !empty($transaction->bundle) ? $transaction->bundle->getUrl() : '#' }}" target="_blank" class="font-bold text-gray-900 group-hover:text-primary transition-colors truncate">
                                                    {{ !empty($transaction->bundle) ? $transaction->bundle->title : "Item #{$transaction->bundle_id}" }}
                                                </a>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ trans('update.target_types_bundles') }}</span>
                                            @elseif(!empty($transaction->product_id))
                                                <a href="{{ !empty($transaction->product) ? $transaction->product->getUrl() : '#' }}" target="_blank" class="font-bold text-gray-900 group-hover:text-primary transition-colors truncate">
                                                    {{ !empty($transaction->product) ? $transaction->product->title : "Item #{$transaction->product_id}" }}
                                                </a>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ trans('update.target_types_store_products') }}</span>
                                            @elseif(!empty($transaction->meeting_time_id))
                                                <span class="font-bold text-gray-900 truncate">{{ trans('admin/main.meeting') }} #{{ $transaction->meeting_time_id }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ trans('update.target_types_meetings') }}</span>
                                            @elseif(!empty($transaction->subscribe_id))
                                                <span class="font-bold text-gray-900 truncate">{{ trans('admin/main.purchased_subscribe') }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ trans('update.target_types_subscription_packages') }}</span>
                                            @elseif(!empty($transaction->registration_package_id))
                                                <span class="font-bold text-gray-900 truncate text-xs">{{ trans('update.purchased_registration_package') }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ trans('update.target_types_registration_packages') }}</span>
                                            @else
                                                <span class="text-gray-400">---</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-700 tracking-tight">
                                        {{ $transaction->purchase_amount ? handlePrice($transaction->purchase_amount) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-primary tracking-tight">
                                        {{ handlePrice($transaction->amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ dateTimeFormat($transaction->created_at, 'j M Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($transaction->system)
                                            <span class="inline-flex items-center gap-1 text-rose-600 px-3 py-1 bg-rose-50 rounded-full border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ trans('admin/main.refund') }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-emerald-600 px-3 py-1 bg-emerald-50 rounded-full border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span class="text-[10px] font-bold uppercase tracking-wider">{{ trans('update.successful') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_cashback_transactions')
                                                @if(!$transaction->system)
                                                    @include('admin.includes.delete_button',[
                                                        'url' => getAdminPanelUrl('/cashback/transactions/'. $transaction->id .'/refund'),
                                                        'tooltip' => trans('admin/main.refund'),
                                                        'btnIcon' => 'visibility_off',
                                                        'btnClass' => 'p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all'
                                                    ])
                                                @endif
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
