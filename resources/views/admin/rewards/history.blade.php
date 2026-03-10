@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Rewards / Points Ledger</p>
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden text-left">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.user') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.total_points') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.spent_points') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('update.available_points') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($rewards as $reward)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        @if(!empty($reward->user))
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl overflow-hidden bg-gray-100 border border-gray-100 group-hover:scale-105 transition-transform shadow-sm shrink-0">
                                                    <img src="{{ $reward->user->getAvatar() }}" alt="{{ $reward->user->full_name }}" class="w-full h-full object-cover">
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors leading-none mb-1">{{ $reward->user->full_name }}</span>
                                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none">
                                                        @if($reward->user->mobile)
                                                            {{ $reward->user->mobile }}
                                                        @elseif($reward->user->email)
                                                            {{ $reward->user->email }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100 shadow-sm">
                                            <span class="material-symbols-rounded text-sm">stars</span>
                                            <span class="text-xs font-black tracking-tighter">{{ $reward->total_points }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 rounded-full border border-rose-100">
                                            <span class="material-symbols-rounded text-sm">remove_circle_outline</span>
                                            <span class="text-xs font-black tracking-tighter">{{ $reward->spent_points }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <span class="text-sm font-black text-gray-900 tracking-tighter">{{ $reward->available_points }}</span>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Active Balance</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($rewards->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center text-decoration-none">
                        {{ $rewards->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
