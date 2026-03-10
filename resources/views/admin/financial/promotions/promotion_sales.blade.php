@extends('admin.layouts.app')

@section('content')
    <section class="section text-left">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ trans('admin/main.promotion_sales') }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Financial / Promotions / Sales Ledger</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/financial/promotions" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-gray-50 transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.full_name') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.webinar') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center pr-8">{{ trans('admin/main.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($promotionSales as $promotionSale)
                                <tr class="group hover:bg-gray-50/50 transition-all border-none">
                                    <td class="px-6 py-4 border-none">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-primary/5 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm border border-primary/10">
                                                <span class="material-symbols-rounded text-lg">campaign</span>
                                            </div>
                                            <span class="font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors italic leading-none uppercase text-xs">
                                                {{ !empty($promotionSale->promotion) ? $promotionSale->promotion->title : trans('update.deleted_promotion') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border-none">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 tracking-tight leading-none mb-1">{{ !empty($promotionSale->buyer) ? $promotionSale->buyer->full_name : trans('update.deleted_user') }}</span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none">{{ !empty($promotionSale->buyer) ? $promotionSale->buyer->role_name : '' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border-none">
                                        @if(!empty($promotionSale->webinar))
                                            <a href="{{ $promotionSale->webinar->getUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-primary/70 transition-colors group/link leading-none">
                                                <span class="truncate max-w-[200px]">{{ $promotionSale->webinar->title }}</span>
                                                <span class="material-symbols-rounded text-sm opacity-0 group-hover/link:opacity-100 transition-opacity">open_in_new</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic font-bold uppercase tracking-widest leading-none opacity-50">{{ trans('update.deleted_item') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center pr-8 border-none font-bold text-gray-700 italic">
                                        {{ dateTimeFormat($promotionSale->created_at, 'j M Y | H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($promotionSales->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center text-decoration-none">
                        {{ $promotionSales->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

