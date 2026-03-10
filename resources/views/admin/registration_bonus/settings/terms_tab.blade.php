@php
    $termsSetting = $settings->where('name', \App\Models\Setting::$registrationBonusTermsSettingsName)->first();
    $termsValue = (!empty($termsSetting) and !empty($termsSetting->translate($selectedLocale))) ? $termsSetting->translate($selectedLocale)->value : null;

    if (!empty($termsValue)) {
        $termsValue = json_decode($termsValue, true);
    }
@endphp

<div class="max-w-4xl">
    <form action="{{ getAdminPanelUrl('/registration_bonus/settings') }}" method="post" class="space-y-8">
        {{ csrf_field() }}
        <input type="hidden" name="page" value="general">
        <input type="hidden" name="name" value="{{ \App\Models\Setting::$registrationBonusTermsSettingsName }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-6">
                @if(!empty(getGeneralSettings('content_translate')))
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('auth.language') }}</label>
                        <div class="relative">
                            <select name="locale" class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none js-edit-content-locale">
                                @foreach($userLanguages as $lang => $language)
                                    <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', (!empty($termsValue) and !empty($termsValue['locale'])) ? $termsValue['locale'] : app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">language</span>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
                @endif

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-700 uppercase ml-1">{{ trans('public.image') }}</label>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-2xl border border-gray-100 border-dashed group hover:border-primary transition-colors cursor-pointer admin-file-manager" data-input="term_image" data-preview="holder">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-gray-400 group-hover:text-primary border border-gray-100 overflow-hidden" id="holder">
                                @if(!empty($termsValue) and !empty($termsValue['term_image']))
                                    <img src="{{ $termsValue['term_image'] }}" class="w-full h-full object-contain">
                                @else
                                    <span class="material-symbols-rounded text-2xl">image</span>
                                @endif
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-gray-700 uppercase tracking-tighter">Term Illustration</span>
                                <span class="text-[10px] text-gray-400 font-bold tracking-tight">Select or upload term image</span>
                            </div>
                        </div>
                        <div class="relative group">
                            <input type="text" name="value[term_image]" id="term_image" value="{{ (!empty($termsValue) and !empty($termsValue['term_image'])) ? $termsValue['term_image'] : old('term_image') }}" 
                                   class="w-full px-4 py-2 bg-gray-50 border-none rounded-xl text-xs font-mono focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                                   placeholder="Browse file system...">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary admin-file-view shadow-none border-none outline-none leading-none flex items-center" data-input="term_image">
                                <span class="material-symbols-rounded text-lg leading-none">open_in_new</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Repeater Items --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-1">
                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('update.terms') }} Items</label>
                    <button type="button" class="add-btn flex items-center justify-center p-1.5 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-all border-none outline-none shadow-none">
                        <span class="material-symbols-rounded text-xl leading-none">add</span>
                    </button>
                </div>

                <div id="addAccountTypes" class="space-y-4">
                    @if(!empty($termsValue) and !empty($termsValue['items']) and is_array($termsValue['items']))
                        @foreach($termsValue['items'] as $key => $item)
                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 relative group animate-in fade-in slide-in-from-top-2 duration-300">
                                <button type="button" class="remove-btn absolute -right-2 -top-2 w-7 h-7 bg-white text-rose-500 rounded-full shadow-sm border border-rose-100 flex items-center justify-center hover:bg-rose-50 transition-all z-10">
                                    <span class="material-symbols-rounded text-lg">close</span>
                                </button>
                                <div class="space-y-3">
                                    <div class="space-y-1.5 font-bold uppercase tracking-tighter">
                                        <label class="text-[10px] text-gray-500 ml-1">{{ trans('admin/main.icon') }}</label>
                                        <div class="relative group">
                                            <input type="text" name="value[items][{{ $key }}][icon]" id="icon_{{ $key }}" value="{{ $item['icon'] ?? '' }}" 
                                                   class="w-full pl-10 pr-4 py-2 bg-white border-none rounded-xl text-[11px] font-mono focus:ring-2 focus:ring-primary/20 transition-all outline-none border border-gray-50">
                                            <button type="button" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary admin-file-manager shadow-none border-none outline-none flex items-center justify-center" data-input="icon_{{ $key }}">
                                                <span class="material-symbols-rounded text-lg">upload_file</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5 font-bold uppercase tracking-tighter">
                                        <label class="text-[10px] text-gray-500 ml-1">{{ trans('admin/main.title') }}</label>
                                        <input type="text" name="value[items][{{ $key }}][title]" value="{{ $item['title'] ?? '' }}" 
                                               class="w-full px-4 py-2 bg-white border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none border border-gray-100">
                                    </div>
                                    <div class="space-y-1.5 font-bold uppercase tracking-tighter">
                                        <label class="text-[10px] text-gray-500 ml-1">{{ trans('public.description') }}</label>
                                        <input type="text" name="value[items][{{ $key }}][description]" value="{{ $item['description'] ?? '' }}" 
                                               class="w-full px-4 py-2 bg-white border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none border border-gray-100">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
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

{{-- Template for JS repeater --}}
<div class="main-row hidden">
    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 relative group mb-4">
        <button type="button" class="remove-btn absolute -right-2 -top-2 w-7 h-7 bg-white text-rose-500 rounded-full shadow-sm border border-rose-100 flex items-center justify-center hover:bg-rose-50 transition-all z-10">
            <span class="material-symbols-rounded text-lg">close</span>
        </button>
        <div class="space-y-3 font-bold uppercase tracking-tighter">
            <div class="space-y-1.5">
                <label class="text-[10px] text-gray-500 ml-1">{{ trans('admin/main.icon') }}</label>
                <div class="relative">
                    <input type="text" name="value[items][record][icon]" id="icon_record" value="" 
                           class="w-full pl-10 pr-4 py-2 bg-white border-none rounded-xl text-[11px] font-mono focus:ring-2 focus:ring-primary/20 transition-all outline-none border border-gray-50">
                    <button type="button" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary admin-file-manager shadow-none border-none outline-none flex items-center justify-center" data-input="icon_record">
                        <span class="material-symbols-rounded text-lg">upload_file</span>
                    </button>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] text-gray-500 ml-1">{{ trans('admin/main.title') }}</label>
                <input type="text" name="value[items][record][title]" class="w-full px-4 py-2 bg-white border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none border border-gray-100">
            </div>
            <div class="space-y-1.5">
                <label class="text-[10px] text-gray-500 ml-1">{{ trans('public.description') }}</label>
                <input type="text" name="value[items][record][description]" class="w-full px-4 py-2 bg-white border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none border border-gray-100">
            </div>
        </div>
    </div>
</div>

@push('scripts_bottom')
    <script src="/assets/default/js/admin/settings/site_bank_accounts.min.js"></script>
@endpush
