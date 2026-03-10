@php
    $termsSetting = $settings->where('name', \App\Models\Setting::$installmentsTermsSettingsName)->first();

    $termsValue = (!empty($termsSetting) and !empty($termsSetting->translate($selectedLocale))) ? $termsSetting->translate($selectedLocale)->value : null;

    if (!empty($termsValue)) {
        $termsValue = json_decode($termsValue, true);
    }
@endphp

<form action="{{ getAdminPanelUrl('/financial/installments/settings') }}" method="post" class="space-y-8">
    {{ csrf_field() }}
    <input type="hidden" name="page" value="general">
    <input type="hidden" name="name" value="{{ \App\Models\Setting::$installmentsTermsSettingsName }}">

    <div class="row">
        <div class="col-12 col-md-6">
            @if(!empty(getGeneralSettings('content_translate')))
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('auth.language') }}</label>
                    <select name="locale" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary appearance-none js-edit-content-locale">
                        @foreach($userLanguages as $lang => $language)
                            <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', (!empty($termsValue) and !empty($termsValue['locale'])) ? $termsValue['locale'] : app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">description</span>
            {{ trans('admin/main.description') }}
        </h3>
        <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700">
            <textarea name="value[terms_description]" required class="summernote form-control">{{ (!empty($termsValue) and !empty($termsValue['terms_description'])) ? $termsValue['terms_description'] : '' }}</textarea>
        </div>
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">This content will be shown to users during the installment application process.</p>
    </div>

    <!-- Footer Actions -->
    <div class="pt-10 border-t border-slate-100 dark:border-slate-800 flex justify-end">
        <button type="submit" class="px-8 py-3 bg-primary text-white font-black text-sm rounded-2xl shadow-lg shadow-primary/30 hover:shadow-primary/40 active:scale-95 transition-all uppercase tracking-widest">
            {{ trans('admin/main.submit') }}
        </button>
    </div>
</form>
