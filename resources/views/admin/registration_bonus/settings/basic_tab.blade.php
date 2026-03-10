@php
    $basicSetting = $settings->where('name', \App\Models\Setting::$registrationBonusSettingsName)->first();
    $basicValue = !empty($basicSetting) ? $basicSetting->value : null;

    if (!empty($basicValue)) {
        $basicValue = json_decode($basicValue, true);
    }
@endphp

<div class="max-w-4xl">
    <form action="{{ getAdminPanelUrl('/registration_bonus/settings') }}" method="post" class="space-y-8">
        {{ csrf_field() }}
        <input type="hidden" name="page" value="general">
        <input type="hidden" name="name" value="{{ \App\Models\Setting::$registrationBonusSettingsName }}">
        <input type="hidden" name="locale" value="{{ \App\Models\Setting::$defaultSettingsLocale }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Status & Logic --}}
            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900">{{ trans('admin/main.status') }}</span>
                        <span class="text-[10px] text-gray-500 tracking-tight">{{ trans('update.registration_bonus_setting_active_hint') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="value[status]" value="0">
                        <input type="checkbox" name="value[status]" id="statusSwitch" value="1" {{ (!empty($basicValue) and !empty($basicValue['status']) and $basicValue['status']) ? 'checked="checked"' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary transition-all"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900">{{ trans('update.unlock_registration_bonus_instantly') }}</span>
                        <span class="text-[10px] text-gray-500 tracking-tight">{{ trans('update.unlock_registration_bonus_instantly_hint') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="value[unlock_registration_bonus_instantly]" value="0">
                        <input type="checkbox" name="value[unlock_registration_bonus_instantly]" id="unlock_registration_bonus_instantlySwitch" value="1" {{ (!empty($basicValue) and !empty($basicValue['unlock_registration_bonus_instantly']) and $basicValue['unlock_registration_bonus_instantly']) ? 'checked="checked"' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary transition-all"></div>
                    </label>
                </div>

                <div class="js-unlock-registration-bonus-with-referral-field flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 {{ (!empty($basicValue) and !empty($basicValue['unlock_registration_bonus_instantly'])) ? 'hidden' : '' }}">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900">{{ trans('update.unlock_registration_bonus_with_referral') }}</span>
                        <span class="text-[10px] text-gray-500 tracking-tight">{{ trans('update.unlock_registration_bonus_with_referral_hint') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="value[unlock_registration_bonus_with_referral]" value="0">
                        <input type="checkbox" name="value[unlock_registration_bonus_with_referral]" id="unlock_registration_bonus_with_referralSwitch" value="1" {{ (!empty($basicValue) and !empty($basicValue['unlock_registration_bonus_with_referral']) and $basicValue['unlock_registration_bonus_with_referral']) ? 'checked="checked"' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary transition-all"></div>
                    </label>
                </div>

                <div class="js-enable-referred-users-purchase-field flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 {{ (!empty($basicValue) and !empty($basicValue['unlock_registration_bonus_instantly'])) ? 'hidden' : ((!empty($basicValue) and !empty($basicValue['unlock_registration_bonus_with_referral'])) ? '' : 'hidden') }}">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900">{{ trans('update.enable_referred_users_purchase') }}</span>
                        <span class="text-[10px] text-gray-500 tracking-tight">{{ trans('update.enable_referred_users_purchase_hint') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="value[enable_referred_users_purchase]" value="0">
                        <input type="checkbox" name="value[enable_referred_users_purchase]" id="enable_referred_users_purchaseSwitch" value="1" {{ (!empty($basicValue) and !empty($basicValue['enable_referred_users_purchase']) and $basicValue['enable_referred_users_purchase']) ? 'checked="checked"' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary transition-all"></div>
                    </label>
                </div>
            </div>

            {{-- Configuration Inputs --}}
            <div class="space-y-6">
                <div class="js-number-of-referred-users-field space-y-2 {{ (!empty($basicValue) and !empty($basicValue['unlock_registration_bonus_with_referral'])) ? '' : 'hidden' }}">
                    <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('update.number_of_referred_users') }}</label>
                    <div class="relative group">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">group_add</span>
                        <input type="number" name="value[number_of_referred_users]" value="{{ (!empty($basicValue) and !empty($basicValue['number_of_referred_users'])) ? $basicValue['number_of_referred_users'] : old('number_of_referred_users') }}" 
                               class="w-full pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                    </div>
                    <p class="text-[10px] text-gray-400 ml-1 italic">{{ trans('update.number_of_referred_users_hint') }}</p>
                </div>

                <div class="js-purchase-amount-for-unlocking-bonus-field space-y-2 {{ (!empty($basicValue) and !empty($basicValue['enable_referred_users_purchase'])) ? '' : 'hidden' }}">
                    <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('update.purchase_amount_for_unlocking_bonus') }}</label>
                    <div class="relative group">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">shopping_cart_checkout</span>
                        <input type="number" name="value[purchase_amount_for_unlocking_bonus]" value="{{ (!empty($basicValue) and !empty($basicValue['purchase_amount_for_unlocking_bonus'])) ? $basicValue['purchase_amount_for_unlocking_bonus'] : old('purchase_amount_for_unlocking_bonus') }}" 
                               class="w-full pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                    </div>
                    <p class="text-[10px] text-gray-400 ml-1 italic">{{ trans('update.purchase_amount_for_unlocking_bonus_hint') }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('update.registration_bonus_amount') }}</label>
                    <div class="relative group">
                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">payments</span>
                        <input type="number" name="value[registration_bonus_amount]" value="{{ (!empty($basicValue) and !empty($basicValue['registration_bonus_amount'])) ? $basicValue['registration_bonus_amount'] : old('registration_bonus_amount') }}" 
                               class="w-full pl-10 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                    </div>
                    <p class="text-[10px] text-gray-400 ml-1 italic">{{ trans('update.registration_bonus_amount_hint') }}</p>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('update.bonus_wallet') }}</label>
                    <div class="relative">
                        <select name="value[bonus_wallet]" class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                            <option value="income_wallet" {{ (!empty($basicValue) and !empty($basicValue['bonus_wallet']) and $basicValue['bonus_wallet'] == "income_wallet") ? 'selected' : '' }}>{{ trans('update.income_wallet') }}</option>
                            <option value="balance_wallet" {{ (!empty($basicValue) and !empty($basicValue['bonus_wallet']) and $basicValue['bonus_wallet'] == "balance_wallet") ? 'selected' : '' }}>{{ trans('update.balance_wallet') }}</option>
                        </select>
                        <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">unfold_more</span>
                    </div>
                    <p class="text-[10px] text-gray-400 ml-1 italic">{{ trans('update.bonus_wallet_hint') }}</p>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-50">
            <button type="submit" class="flex items-center gap-2 px-8 py-3 bg-primary text-white rounded-2xl font-bold hover:bg-opacity-90 transition-all shadow-sm">
                <span class="material-symbols-rounded text-xl leading-none">save</span>
                {{ trans('admin/main.submit') }}
            </button>
        </div>
    </form>
</div>
