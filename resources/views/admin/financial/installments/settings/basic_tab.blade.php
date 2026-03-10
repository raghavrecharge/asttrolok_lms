@php
    $basicSetting = $settings->where('name', \App\Models\Setting::$installmentsSettingsName)->first();
    $basicValue = !empty($basicSetting) ? $basicSetting->value : null;

    if (!empty($basicValue)) {
        $basicValue = json_decode($basicValue, true);
    }
@endphp

<form action="{{ getAdminPanelUrl('/financial/installments/settings') }}" method="post" class="space-y-8">
    {{ csrf_field() }}
    <input type="hidden" name="page" value="general">
    <input type="hidden" name="name" value="{{ \App\Models\Setting::$installmentsSettingsName }}">
    <input type="hidden" name="locale" value="{{ \App\Models\Setting::$defaultSettingsLocale }}">

    <!-- Status Toggles Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">toggle_on</span>
            Access & Visibility
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Active Status -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('admin/main.active') }}</label>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.installment_setting_active_hint') }}</p>
                </div>
                <div class="relative inline-block w-10 h-6">
                    <input type="hidden" name="value[status]" value="0">
                    <input type="checkbox" name="value[status]" value="1" {{ (!empty($basicValue) and !empty($basicValue['status']) and $basicValue['status']) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-primary transition-all duration-300">
                    <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                </div>
            </div>

            <!-- Display Button -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.display_installment_button') }}</label>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.display_installment_button_hint') }}</p>
                </div>
                <div class="relative inline-block w-10 h-6">
                    <input type="hidden" name="value[display_installment_button]" value="0">
                    <input type="checkbox" name="value[display_installment_button]" value="1" {{ (!empty($basicValue) and !empty($basicValue['display_installment_button']) and $basicValue['display_installment_button']) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-primary transition-all duration-300">
                    <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                </div>
            </div>

            <!-- Disable Course Access -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block text-red-600 dark:text-red-400">{{ trans('update.disable_course_access_when_user_have_an_overdue_installment') }}</label>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.disable_course_access_when_user_have_an_overdue_installment_hint') }}</p>
                </div>
                <div class="relative inline-block w-10 h-6">
                    <input type="hidden" name="value[disable_course_access_when_user_have_an_overdue_installment]" value="0">
                    <input type="checkbox" name="value[disable_course_access_when_user_have_an_overdue_installment]" value="1" {{ (!empty($basicValue) and !empty($basicValue['disable_course_access_when_user_have_an_overdue_installment']) and $basicValue['disable_course_access_when_user_have_an_overdue_installment']) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-red-500 transition-all duration-300">
                    <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                </div>
            </div>

            <!-- Disable All Access -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block text-red-600 dark:text-red-400">{{ trans('update.disable_all_courses_access_when_user_have_an_overdue_installment') }}</label>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.disable_all_courses_access_when_user_have_an_overdue_installment_hint') }}</p>
                </div>
                <div class="relative inline-block w-10 h-6">
                    <input type="hidden" name="value[disable_all_courses_access_when_user_have_an_overdue_installment]" value="0">
                    <input type="checkbox" name="value[disable_all_courses_access_when_user_have_an_overdue_installment]" value="1" {{ (!empty($basicValue) and !empty($basicValue['disable_all_courses_access_when_user_have_an_overdue_installment']) and $basicValue['disable_all_courses_access_when_user_have_an_overdue_installment']) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-red-500 transition-all duration-300">
                    <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Numerical Config Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">calendar_today</span>
            Delinquency Intervals
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.overdue_interval_days') }}</label>
                <input type="number" name="value[overdue_interval_days]" value="{{ (!empty($basicValue) and !empty($basicValue['overdue_interval_days'])) ? $basicValue['overdue_interval_days'] : old('overdue_interval_days') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary">
                <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.overdue_interval_days_hint') }}</p>
            </div>
            
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.reminder_before_overdue_days') }}</label>
                <input type="number" name="value[reminder_before_overdue_days]" value="{{ (!empty($basicValue) and !empty($basicValue['reminder_before_overdue_days'])) ? $basicValue['reminder_before_overdue_days'] : old('reminder_before_overdue_days') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary">
                <p class="text-[10px] text-slate-400 font-semibold uppercase font-bold">{{ trans('update.reminder_before_overdue_days_hint') }}</p>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block text-red-600 dark:text-red-400">{{ trans('update.reminder_after_overdue_days') }}</label>
                <input type="number" name="value[reminder_after_overdue_days]" value="{{ (!empty($basicValue) and !empty($basicValue['reminder_after_overdue_days'])) ? $basicValue['reminder_after_overdue_days'] : old('reminder_after_overdue_days') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-red-500 focus:border-red-500">
                <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.reminder_after_overdue_days_hint') }}</p>
            </div>
        </div>
    </div>

    <!-- Layout Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">view_quilt</span>
            Interface Layout
        </h3>
        <div class="max-w-md space-y-2">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.installment_plans_position') }}</label>
            <select name="value[installment_plans_position]" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary appearance-none">
                <option value="top_of_page" {{ (!empty($basicValue) and !empty($basicValue['installment_plans_position']) and $basicValue['installment_plans_position'] == "top_of_page") ? 'selected' : '' }}>{{ trans('update.top_of_page') }}</option>
                <option value="bottom_of_page" {{ (!empty($basicValue) and !empty($basicValue['installment_plans_position']) and $basicValue['installment_plans_position'] == "bottom_of_page") ? 'selected' : '' }}>{{ trans('update.bottom_of_page') }}</option>
            </select>
            <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_plans_position_hint') }}</p>
        </div>
    </div>

    <!-- Footer Actions -->
    <div class="pt-10 border-t border-slate-100 dark:border-slate-800 flex justify-end">
        <button type="submit" class="px-8 py-3 bg-primary text-white font-black text-sm rounded-2xl shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 transition-all uppercase tracking-widest">
            {{ trans('admin/main.submit') }}
        </button>
    </div>
</form>
