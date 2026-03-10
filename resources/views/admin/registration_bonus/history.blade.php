@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Registration Bonus / History</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_registration_bonus_export_excel')
                    <a href="{{ getAdminPanelUrl('/registration_bonus/export?'. http_build_query(request()->all())) }}" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-opacity-90 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl">download</span>
                        {{ trans('admin/main.export_xls') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            {{-- Metrics Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-rounded text-2xl">group</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ trans('update.achieved_users') }}</p>
                        <h3 class="text-xl font-black text-gray-900 mt-0.5 leading-none">{{ $achievedUsers }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                        <span class="material-symbols-rounded text-2xl">lock_open</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ trans('update.unlocked_bonus_users') }}</p>
                        <h3 class="text-xl font-black text-gray-900 mt-0.5 leading-none">{{ $unlockedBonusUsers }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-rounded text-2xl">payments</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ trans('update.total_bonus') }}</p>
                        <h3 class="text-xl font-black text-gray-900 mt-0.5 leading-none">{{ handlePrice($totalBonus) }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-rounded text-2xl">account_balance_wallet</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ trans('update.unlocked_bonus') }}</p>
                        <h3 class="text-xl font-black text-gray-900 mt-0.5 leading-none">{{ handlePrice($unlockedBonus) }}</h3>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <form method="get" class="mb-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-4 items-end">
                        <div class="form-group space-y-2 lg:col-span-1 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.search') }}</label>
                            <div class="relative group">
                                <input type="text" name="title" value="{{ request()->get('title') }}" class="w-full pl-11 pr-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none" placeholder="Search...">
                                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-xl">search</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.start_date') }}</label>
                            <input type="date" name="from" value="{{ request()->get('from') }}" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.end_date') }}</label>
                            <input type="date" name="to" value="{{ request()->get('to') }}" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                        </div>

                        @php
                            $filters = ['registration_date_asc', 'registration_date_desc', 'referred_users_asc', 'referred_users_desc', 'bonus_asc', 'bonus_desc'];
                        @endphp

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.filters') }}</label>
                            <div class="relative">
                                <select name="sort" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{ trans('admin/main.all') }}</option>
                                    @foreach($filters as $filter)
                                        <option value="{{ $filter }}" @if(request()->get('sort') == $filter) selected @endif>{{ trans('update.'.$filter) }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">sort</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2 lg:col-span-1 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.user') }}</label>
                            <select name="user_ids[]" multiple="multiple" class="form-control h-auto search-user-select2" data-placeholder="Search users">
                                @if(!empty($selectedUsers) and $selectedUsers->count() > 0)
                                    @foreach($selectedUsers as $user)
                                        <option value="{{ $user->id }}" selected>{{ $user->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('admin/main.role') }}</label>
                            <div class="relative">
                                <select name="role_id" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{ trans('admin/main.all') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (request()->get('role_id') == $role->id) ? 'selected' : '' }}>{{ $role->caption }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">unfold_more</span>
                            </div>
                        </div>

                        <div class="form-group space-y-2 mb-0">
                            <label class="text-xs font-bold text-gray-700 ml-1 uppercase tracking-wider">{{ trans('update.bonus_status') }}</label>
                            <div class="relative">
                                <select name="bonus_status" class="w-full px-4 py-2 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                                    <option value="">{{ trans('admin/main.all') }}</option>
                                    <option value="locked" {{ (request()->get('bonus_status') == 'locked') ? 'selected' : '' }}>{{ trans('update.locked') }}</option>
                                    <option value="unlocked" {{ (request()->get('bonus_status') == 'unlocked') ? 'selected' : '' }}>{{ trans('update.unlocked') }}</option>
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

            {{-- Registration Bonus History List --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-responsive text-decoration-none text-left">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('update.user_id') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.user') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.bonus') }}</th>
                                @if (!empty($registrationBonusSettings['unlock_registration_bonus_with_referral']) and !empty($registrationBonusSettings['number_of_referred_users']))
                                    <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.referred_users') }}</th>
                                    @if (!empty($registrationBonusSettings['enable_referred_users_purchase']))
                                        <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.referred_purchases') }}</th>
                                    @endif
                                @endif
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.registration_date') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.bonus_status') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($users as $user)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-bold text-gray-400">#{{ $user->id }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <img src="{{ $user->getAvatar() }}" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm" alt="{{ $user->full_name }}">
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 tracking-tight leading-tight">{{ $user->full_name }}</span>
                                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $user->role->caption }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm font-black text-gray-900 leading-none">{{ handlePrice($user->registration_bonus_amount ?? 0) }}</span>
                                    </td>
                                    @if (!empty($registrationBonusSettings['unlock_registration_bonus_with_referral']) and !empty($registrationBonusSettings['number_of_referred_users']))
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 bg-primary/5 text-primary rounded-lg font-bold text-xs">{{ $user->referred_users }}</span>
                                        </td>
                                        @if (!empty($registrationBonusSettings['enable_referred_users_purchase']))
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2 py-1 bg-primary/5 text-primary rounded-lg font-bold text-xs">{{ $user->referred_purchases }}</span>
                                            </td>
                                        @endif
                                    @endif
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ dateTimeFormat($user->created_at, 'j M Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-decoration-none">
                                        @if($user->bonus_status == 'unlocked')
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                {{ trans('update.unlocked') }}
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-100">
                                                {{ trans('update.locked') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_users_impersonate')
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate" target="_blank" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all inline-flex items-center justify-center shadow-none border-none outline-none" title="{{ trans('admin/main.login') }}">
                                                    <span class="material-symbols-rounded text-xl">user_shield</span>
                                                </a>
                                            @endcan

                                            @can('admin_users_edit')
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/edit" class="p-2 text-gray-400 hover:text-primary hover:bg-primary/5 rounded-xl transition-all inline-flex items-center justify-center shadow-none border-none outline-none" title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-xl">edit</span>
                                                </a>
                                            @endcan

                                            @can('admin_users_edit')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl("/users/{$user->id}/disable_registration_bonus"),
                                                    'tooltip' => trans('update.disable_registration_bonus'),
                                                    'btnIcon' => 'block',
                                                    'btnClass' => 'p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all shadow-none border-none outline-none'
                                                ])
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center">
                        {{ $users->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

