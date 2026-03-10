@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{trans('admin/main.referral_history')}}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Referrals / History</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_referrals_export')
                    <a href="{{ getAdminPanelUrl() }}/referrals/excel?type=history" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl text-gray-400">download</span>
                        {{ trans('admin/main.export_xls') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Referred Users --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">diversity_3</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">{{trans('admin/main.referred_users')}}</p>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ $affiliatesCount }}</h3>
                    </div>
                </div>

                {{-- Affiliate Users --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">badge</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">{{trans('admin/main.affiliate_users')}}</p>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ $affiliateUsersCount }}</h3>
                    </div>
                </div>

                {{-- Registration Amount --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">payments</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">{{trans('admin/main.registeration_amount')}}</p>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ handlePrice($allAffiliateAmounts) }}</h3>
                    </div>
                </div>

                {{-- Commission Amount --}}
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex items-center gap-5 transition-all hover:shadow-md group">
                    <div class="w-14 h-14 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-rounded text-3xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">{{trans('admin/main.total_commission_amount')}}</p>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ handlePrice($allAffiliateCommissionAmounts) }}</h3>
                    </div>
                </div>
            </div>

            {{-- Lists --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="table-responsive text-decoration-none text-left">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.affiliate_user') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.referred_user') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.affiliate_registration_amount') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.affiliate_user_commission') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.referred_user_amount') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($affiliates as $affiliate)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4 font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors italic">
                                        {{ $affiliate->affiliateUser->full_name }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-700 tracking-tight">
                                        {{ $affiliate->referredUser->full_name }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-bold text-emerald-600 tracking-tight">{{ handlePrice($affiliate->getAffiliateRegistrationAmountsOfEachReferral()) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-bold text-primary tracking-tight">{{ handlePrice($affiliate->getTotalAffiliateCommissionOfEachReferral()) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-bold text-gray-500 tracking-tight">{{ handlePrice($affiliate->getReferredAmount()) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest font-mono">{{ dateTimeFormat($affiliate->created_at, 'Y M j | H:i') }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($affiliates->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center">
                        {{ $affiliates->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>

            {{-- Hints --}}
            <div class="mt-8 bg-blue-50/30 rounded-3xl p-8 border border-blue-100/50">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center text-white shadow-sm">
                        <span class="material-symbols-rounded text-xl">lightbulb</span>
                    </div>
                    <h5 class="text-sm font-black text-gray-900 uppercase tracking-widest">{{trans('admin/main.hints')}}</h5>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{trans('admin/main.total_user_hint')}}</p>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed">{{trans('admin/main.total_user_desc')}}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{trans('admin/main.total_affiliate_hint')}}</p>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed">{{trans('admin/main.total_affiliate_desc')}}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{trans('admin/main.total_aff_amount_hint')}}</p>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed">{{trans('admin/main.total_aff_amount_desc')}}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{trans('admin/main.total_aff_commission_hint')}}</p>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed">{{trans('admin/main.total_aff_commission_desc')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
