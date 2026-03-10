@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{trans('admin/main.affiliate_users')}}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Referrals / Users</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_referrals_export')
                    <a href="{{ getAdminPanelUrl() }}/referrals/excel?type=users" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl text-gray-400">download</span>
                        {{ trans('admin/main.export_xls') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            {{-- Lists --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div class="table-responsive text-decoration-none text-left">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.user') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.role') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.user_group') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.referral_code') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.registration_income') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.aff_sales_commission') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($affiliates as $affiliate)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <img src="{{ $affiliate->affiliateUser->getAvatar() }}" class="w-10 h-10 rounded-xl object-cover border border-gray-100 shadow-sm" alt="{{ $affiliate->affiliateUser->full_name }}">
                                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-primary border-2 border-white flex items-center justify-center shadow-sm">
                                                    <span class="material-symbols-rounded text-white text-[10px]">person</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 line-clamp-1 group-hover:text-primary transition-colors tracking-tight">{{ $affiliate->affiliateUser->full_name }}</span>
                                                <span class="text-[10px] text-gray-400 font-bold font-mono tracking-widest uppercase truncate max-w-[150px]">{{ $affiliate->affiliateUser->email ?? $affiliate->affiliateUser->mobile }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                            @if($affiliate->affiliateUser->isUser()) bg-blue-50 text-blue-600 @elseif($affiliate->affiliateUser->isTeacher()) bg-emerald-50 text-emerald-600 @elseif($affiliate->affiliateUser->isOrganization()) bg-purple-50 text-purple-600 @else bg-gray-50 text-gray-600 @endif">
                                            @if($affiliate->affiliateUser->isUser()) Student @elseif($affiliate->affiliateUser->isTeacher()) Teacher @elseif($affiliate->affiliateUser->isOrganization()) Organization @else User @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-600 tracking-tight">
                                        {{ !empty($affiliate->affiliateUser->getUserGroup()) ? $affiliate->affiliateUser->getUserGroup()->name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold font-mono tracking-widest uppercase">{{ !empty($affiliate->affiliateUser->affiliateCode) ? $affiliate->affiliateUser->affiliateCode->code : '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-emerald-600 tracking-tight">
                                        {{ handlePrice($affiliate->getTotalAffiliateRegistrationAmounts()) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-primary tracking-tight">
                                        {{ handlePrice($affiliate->getTotalAffiliateCommissions()) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($affiliate->affiliateUser->affiliate)
                                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto" title="{{ trans('admin/main.yes') }}">
                                                <span class="material-symbols-rounded text-xl">check_circle</span>
                                            </span>
                                        @else
                                            <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto" title="{{ trans('admin/main.no') }}">
                                                <span class="material-symbols-rounded text-xl">cancel</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <a href="{{ getAdminPanelUrl() }}/users/{{ $affiliate->affiliateUser->id }}/edit" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all inline-flex items-center justify-center" title="{{ trans('admin/main.edit') }}">
                                            <span class="material-symbols-rounded text-xl">edit</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($affiliates instanceof \Illuminate\Pagination\LengthAwarePaginator && $affiliates->hasPages())
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{trans('admin/main.registration_income_hint')}}</p>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed">{{trans('admin/main.registration_income_desc')}}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">{{trans('admin/main.aff_sales_commission_hint')}}</p>
                        <p class="text-[11px] font-bold text-gray-500 leading-relaxed">{{trans('admin/main.aff_sales_commission_desc')}}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
