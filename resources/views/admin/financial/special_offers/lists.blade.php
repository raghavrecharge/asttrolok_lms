@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ trans('admin/main.special_offers') }}</h1>
                <p class="text-sm text-gray-500 mt-1">Configure time-limited discounts for specific courses or packages.</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_product_discount_create')
                    <a href="{{ getAdminPanelUrl() }}/financial/special_offers/create" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-bold hover:bg-opacity-90 transition-all shadow-sm">
                        <span class="material-symbols-rounded text-xl">add</span>
                        {{ trans('admin/main.create') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-rounded text-2xl">local_offer</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ trans('admin/main.total_offers') }}</p>
                        <h3 class="text-xl font-black text-gray-900 leading-none mt-1">{{ $specialOffers->total() }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-rounded text-2xl">check_circle</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Active</p>
                        <h3 class="text-xl font-black text-gray-900 leading-none mt-1">{{ $specialOffers->where('status', 'active')->count() }}</h3>
                    </div>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 items-end">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('admin/main.search') }}</label>
                        <div class="relative group">
                            <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">search</span>
                            <input type="text" name="name" value="{{ request()->get('name') }}" class="w-full pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium" placeholder="Search by name...">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('admin/main.type') }}</label>
                        <select name="type" class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none font-medium">
                            <option value="">{{ trans('update.all_types') }}</option>
                            @php
                                $types = [
                                    'courses' => 'webinar_id',
                                    'bundles' => 'bundle_id',
                                    'subscription_packages' => 'subscribe_id',
                                    'registration_packages' => 'registration_package_id',
                                ];
                            @endphp
                            @foreach($types as $type => $typeItem)
                                <option value="{{ $typeItem }}" {{ (request()->get('type') == $typeItem) ? 'selected' : '' }}>{{ trans('update.'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('admin/main.status') }}</label>
                        <select name="status" class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none font-medium text-gray-700">
                            <option value="">{{ trans('admin/main.all_status') }}</option>
                            <option value="active" {{ request()->get('status') == 'active' ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                            <option value="inactive" {{ request()->get('status') == 'inactive' ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-gray-900 text-white py-2 rounded-xl font-bold text-sm hover:bg-black transition-all shadow-sm">
                            {{ trans('admin/main.filter') }}
                        </button>
                        <a href="{{ getAdminPanelUrl() }}/financial/special_offers" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-200 transition-all">
                            <span class="material-symbols-rounded text-lg leading-none mt-1">refresh</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1000px]">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50">{{ trans('admin/main.item') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">{{ trans('admin/main.percentage') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">Duration</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50 text-center">{{ trans('admin/main.status') }}</th>
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50 text-right">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($specialOffers as $specialOffer)
                                <tr class="group hover:bg-gray-50/50 transition-colors text-center">
                                    <td class="px-6 py-4 text-left">
                                        <span class="text-sm font-bold text-gray-900">{{ $specialOffer->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-left">
                                        <div class="flex flex-col">
                                            @if(!empty($specialOffer->webinar_id))
                                                <span class="text-sm font-bold text-gray-900 leading-tight">{{ $specialOffer->webinar->title }}</span>
                                                <span class="text-[10px] font-bold text-primary uppercase tracking-tight">{{ trans('admin/main.course') }}</span>
                                            @elseif($specialOffer->bundle_id)
                                                <span class="text-sm font-bold text-gray-900 leading-tight">{{ $specialOffer->bundle->title }}</span>
                                                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-tight">{{ trans('update.bundle') }}</span>
                                            @elseif($specialOffer->subscribe_id)
                                                <span class="text-sm font-bold text-gray-900 leading-tight">{{ $specialOffer->subscribe->title }}</span>
                                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-tight">{{ trans('public.subscribe') }}</span>
                                            @elseif($specialOffer->registration_package_id)
                                                <span class="text-sm font-bold text-gray-900 leading-tight">{{ $specialOffer->registrationPackage->title }}</span>
                                                <span class="text-[10px] font-bold text-blue-600 uppercase tracking-tight">{{ trans('update.registration_package') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/5 text-primary rounded-lg text-sm font-black border border-primary/10">
                                            {{ $specialOffer->percent ? $specialOffer->percent . '%' : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="text-[11px] font-bold text-gray-700">From: {{ dateTimeFormat($specialOffer->from_date, 'Y M j') }}</span>
                                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-tight">To: {{ dateTimeFormat($specialOffer->to_date, 'Y M j') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($specialOffer->status == 'active')
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                {{ trans('admin/main.active') }}
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-50 text-gray-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-gray-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                                                {{ trans('admin/main.inactive') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            @can('admin_product_discount_edit')
                                                <a href="{{ getAdminPanelUrl() }}/financial/special_offers/{{ $specialOffer->id }}/edit" class="w-8 h-8 flex items-center justify-center bg-white text-gray-400 hover:text-primary hover:bg-primary/5 rounded-lg border border-gray-100 transition-all shadow-sm" data-toggle="tooltip" title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-lg">edit</span>
                                                </a>
                                            @endcan

                                            @can('admin_product_discount_delete')
                                                @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/financial/special_offers/'. $specialOffer->id.'/delete','btnClass' => 'w-8 h-8 flex items-center justify-center bg-white text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg border border-gray-100 transition-all shadow-sm', 'noBtnText' => true])
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($specialOffers->hasPages())
                    <div class="p-6 border-t border-gray-50 bg-gray-50/30">
                        {{ $specialOffers->appends(request()->input())->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
@endsection

