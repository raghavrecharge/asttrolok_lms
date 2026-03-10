@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ trans('admin/main.promotions') }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Financial / Promotions</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_promotion_create')
                    <a href="{{ getAdminPanelUrl() }}/financial/promotions/create" class="flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-gray-800 transition-all shadow-md active:scale-95 group">
                        <span class="material-symbols-rounded text-xl group-hover:rotate-90 transition-transform leading-none">add</span>
                        {{ trans('admin/main.create') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body text-left">
            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 group hover:border-primary/20 transition-all border-dashed">
                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors border border-gray-100 shadow-sm leading-none">
                        <span class="material-symbols-rounded text-3xl">campaign</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] mb-1 leading-none">{{ trans('admin/main.total_promotions') }}</p>
                        <h3 class="text-2xl font-black text-gray-900 leading-none tracking-tighter">{{ $promotions->total() }}</h3>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 group hover:border-amber-500/20 transition-all border-dashed">
                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-amber-500 transition-colors border border-gray-100 shadow-sm leading-none">
                        <span class="material-symbols-rounded text-3xl">stars</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.15em] mb-1 leading-none">Popular Plans</p>
                        <h3 class="text-2xl font-black text-gray-900 leading-none tracking-tighter">{{ $promotions->where('is_popular', true)->count() }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.icon') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.sale_count') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.price') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('public.days') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.is_popular') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.created_at') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($promotions as $promotion)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="w-10 h-10 rounded-xl overflow-hidden bg-white p-1 border border-gray-100 shadow-sm group-hover:scale-110 transition-transform">
                                            <img src="{{ $promotion->icon }}" class="w-full h-full object-contain" alt="">
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors italic leading-none">{{ $promotion->title }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-[10px] font-black border border-gray-200 uppercase tracking-tighter">
                                            <span class="material-symbols-rounded text-sm text-gray-400 leading-none">shopping_bag</span>
                                            {{ $promotion->sales->count() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-primary tracking-tighter">
                                        {{ handlePrice($promotion->price) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-600 italic">
                                        {{ $promotion->days }} {{ trans('public.day') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($promotion->is_popular)
                                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100 italic">{{ trans('admin/main.popular') }}</span>
                                        @else
                                            <span class="text-gray-200 material-symbols-rounded text-xl leading-none">circle</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-700 leading-none mb-1">{{ dateTimeFormat($promotion->created_at, 'j M Y') }}</span>
                                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">{{ dateTimeFormat($promotion->created_at, 'H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_promotion_edit')
                                                <a href="{{ getAdminPanelUrl() }}/financial/promotions/{{ $promotion->id }}/edit" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all" data-toggle="tooltip" title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-xl leading-none">edit</span>
                                                </a>
                                            @endcan

                                            @can('admin_promotion_delete')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/financial/promotions/'.$promotion->id.'/delete',
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

                @if($promotions->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center text-decoration-none">
                        {{ $promotions->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>

            <!-- Hint Cards -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:scale-125 transition-all duration-700 text-primary">
                        <span class="material-symbols-rounded text-[160px]">rocket_launch</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-primary text-white flex items-center justify-center mb-6 shadow-lg shadow-primary/20">
                        <span class="material-symbols-rounded text-2xl">rocket_launch</span>
                    </div>
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight mb-3 leading-none italic">{{ trans('admin/main.promotions_list_hint_title_1') }}</h4>
                    <p class="text-xs leading-relaxed text-gray-500 font-bold uppercase tracking-widest text-[10px] opacity-70 italic">{{ trans('admin/main.promotions_list_hint_description_1') }}</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:scale-125 transition-all duration-700 text-amber-500">
                        <span class="material-symbols-rounded text-[160px]">stars</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center mb-6 shadow-lg shadow-amber-500/20">
                        <span class="material-symbols-rounded text-2xl">stars</span>
                    </div>
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight mb-3 leading-none italic">{{ trans('admin/main.promotions_list_hint_title_2') }}</h4>
                    <p class="text-xs leading-relaxed text-gray-500 font-bold uppercase tracking-widest text-[10px] opacity-70 italic">{{ trans('admin/main.promotions_list_hint_description_2') }}</p>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 opacity-[0.03] group-hover:scale-125 transition-all duration-700 text-indigo-500">
                        <span class="material-symbols-rounded text-[160px]">visibility</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500 text-white flex items-center justify-center mb-6 shadow-lg shadow-indigo-500/20">
                        <span class="material-symbols-rounded text-2xl">visibility</span>
                    </div>
                    <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight mb-3 leading-none italic">{{ trans('admin/main.promotions_list_hint_title_3') }}</h4>
                    <p class="text-xs leading-relaxed text-gray-500 font-bold uppercase tracking-widest text-[10px] opacity-70 italic">{{ trans('admin/main.promotions_list_hint_description_3') }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection

