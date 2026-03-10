@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Store / Discounts</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_store_discounts_create')
                    <a href="{{ getAdminPanelUrl() }}/store/discounts/create" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-opacity-90 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl">add</span>
                        {{ trans('admin/main.add_new') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            {{-- Filters --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="get" class="mb-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-4 items-end">
                        <div class="form-group space-y-2 lg:col-span-1 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.search') }}</label>
                            <div class="relative group">
                                <input type="text" name="name" value="{{ request()->get('name') }}" class="w-full pl-11 pr-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Search name...">
                                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-xl">search</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.expiration_from') }}</label>
                            <input type="date" name="from" value="{{ request()->get('from') }}" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.expiration_to') }}</label>
                            <input type="date" name="to" value="{{ request()->get('to') }}" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>

                        <div class="form-group space-y-2 lg:col-span-1 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.filters') }}</label>
                            <div class="relative">
                                <select name="sort" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{ trans('admin/main.all_users_discount') }}</option>
                                    <option value="percent_asc" @if(request()->get('sort') == 'percent_asc') selected @endif>{{ trans('admin/main.percentage_ascending') }}</option>
                                    <option value="percent_desc" @if(request()->get('sort') == 'percent_desc') selected @endif>{{ trans('admin/main.percentage_descending') }}</option>
                                    <option value="created_at_asc" @if(request()->get('sort') == 'created_at_asc') selected @endif>{{ trans('admin/main.create_date_ascending') }}</option>
                                    <option value="created_at_desc" @if(request()->get('sort') == 'created_at_desc') selected @endif>{{ trans('admin/main.create_date_descending') }}</option>
                                    <option value="expire_at_asc" @if(request()->get('sort') == 'expire_at_asc') selected @endif>{{ trans('admin/main.expire_date_ascending') }}</option>
                                    <option value="expire_at_desc" @if(request()->get('sort') == 'expire_at_desc') selected @endif>{{ trans('admin/main.expire_date_descending') }}</option>
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">sort</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2 lg:col-span-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('update.product') }}</label>
                            <select name="product_ids[]" multiple="multiple" class="form-control h-auto search-product-select2" data-placeholder="{{ trans('update.search_product') }}">
                                @if(!empty($products) and $products->count() > 0)
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" selected>{{ $product->title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.status') }}</label>
                            <div class="relative">
                                <select name="status" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{ trans('admin/main.all_status') }}</option>
                                    <option value="active" @if(request()->get('status') == 'active') selected @endif>{{ trans('admin/main.active') }}</option>
                                    <option value="inactive" @if(request()->get('status') == 'inactive') selected @endif>{{ trans('admin/main.inactive') }}</option>
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">unfold_more</span>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="w-full py-2 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-sm flex items-center justify-center gap-2">
                                <span class="material-symbols-rounded text-xl">filter_list</span>
                                {{ trans('admin/main.show_results') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Lists --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="table-responsive text-decoration-none text-left">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('update.product') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.percentage') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.start_date') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.end_date') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.usable_times') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($discounts as $discount)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900 tracking-tight">{{ $discount->name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ $discount->product->getUrl() }}" target="_blank" class="text-sm font-medium text-gray-700 hover:text-primary transition-colors flex items-center gap-1">
                                            <span class="material-symbols-rounded text-lg text-gray-400">link</span>
                                            {{ $discount->product->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-1 bg-primary/5 text-primary rounded-lg font-bold text-xs">{{ $discount->percent ? $discount->percent . '%' : '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">{{ dateTimeFormat($discount->start_date, 'Y/m/d h:i:s') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">{{ dateTimeFormat($discount->end_date, 'Y/m/d h:i:s') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if(!empty($discount->count))
                                            <div class="flex flex-col">
                                                <span class="text-xs font-black text-gray-900 leading-none">{{ $discount->count }}</span>
                                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ trans('admin/main.remain') }} : {{ $discount->discountRemain() }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs font-bold text-gray-400 italic">{{ trans('update.unlimited') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($discount->status == 'active')
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                {{ trans('admin/main.active') }}
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                                {{ trans('admin/main.inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_store_discounts_edit')
                                                <a href="{{ getAdminPanelUrl() }}/store/discounts/{{ $discount->id }}/edit" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all inline-flex items-center justify-center" title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-xl">edit</span>
                                                </a>
                                            @endcan

                                            @can('admin_store_discounts_delete')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/store/discounts/'. $discount->id.'/delete',
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

                @if($discounts->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center">
                        {{ $discounts->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

