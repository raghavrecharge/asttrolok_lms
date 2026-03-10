<div class="compact-form">
    @if(!empty(getGeneralSettings('content_translate')))
        <div class="compact-field">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('auth.language') }}</label>
            <select name="locale" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary appearance-none {{ !empty($installment) ? 'js-edit-content-locale' : '' }}">
                @foreach($userLanguages as $lang => $language)
                    <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }} {{ (!empty($definedLanguage) and is_array($definedLanguage) and in_array(mb_strtolower($lang), $definedLanguage)) ? '('. trans('panel.content_defined') .')' : '' }}</option>
                @endforeach
            </select>
            @error('locale')
                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>
    @else
        <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
    @endif

    <div class="compact-form-row">
        <!-- Title & Main Title -->
        <div class="compact-field">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('public.title') }}</label>
            <input type="text" name="title" value="{{ !empty($installment) ? $installment->title : old('title') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary @error('title') border-red-500 @enderror" placeholder=""/>
            <p class="text-[10px] text-slate-400 font-semibold">{{ trans('update.installment_title_hint') }}</p>
            @error('title')
                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="compact-field">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.main_title') }}</label>
            <input type="text" name="main_title" value="{{ !empty($installment) ? $installment->main_title : old('main_title') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary @error('main_title') border-red-500 @enderror" placeholder=""/>
            <p class="text-[10px] text-slate-400 font-semibold">{{ trans('update.installment_main_title_hint') }}</p>
            @error('main_title')
                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Description -->
    <div class="compact-field">
        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('public.description') }}</label>
        <textarea name="description" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary @error('description') border-red-500 @enderror">{{ !empty($installment) ? $installment->description : old('description') }}</textarea>
        <p class="text-[10px] text-slate-400 font-semibold">{{ trans('update.installment_description_hint') }}</p>
        @error('description')
            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Banner -->
    <div class="compact-field">
        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.banner') }}</label>
        <div class="flex items-center gap-2">
            <div class="flex-1 flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
                <button type="button" class="px-4 py-3 bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 admin-file-manager" data-input="banner" data-preview="holder">
                    <span class="material-symbols-outlined text-sm">upload</span>
                </button>
                <input type="text" name="banner" id="banner" value="{{ !empty($installment) ? $installment->banner : old('banner') }}" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 @error('banner') text-red-500 @enderror"/>
                <button type="button" class="px-4 py-3 text-slate-400 admin-file-view" data-input="banner">
                    <span class="material-symbols-outlined text-sm">visibility</span>
                </button>
            </div>
        </div>
        <p class="text-[10px] text-slate-400 font-semibold">{{ trans('update.installment_banner_hint') }}</p>
        @error('banner')
            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Options -->
    <div id="installmentOptionsCard" class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between">
            <h5 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">{{ trans('update.options') }}</h5>
            <button type="button" class="js-add-btn px-4 py-2 bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-primary hover:text-white transition-all">
                <i class="fa fa-plus mr-1"></i> {{ trans('update.add_option') }}
            </button>
        </div>
                        </button>
                        <input type="text" name="banner" id="banner" value="{{ !empty($installment) ? $installment->banner : old('banner') }}" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 @error('banner') text-red-500 @enderror"/>
                        <button type="button" class="px-4 py-3 text-slate-400 admin-file-view" data-input="banner">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                        </button>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_banner_hint') }}</p>
                @error('banner')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Options -->
            <div id="installmentOptionsCard" class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <h5 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">{{ trans('update.options') }}</h5>
                    <button type="button" class="js-add-btn px-4 py-2 bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-primary hover:text-white transition-all">
                        <i class="fa fa-plus mr-1"></i> {{ trans('update.add_option') }}
                    </button>
                </div>

                <div class="space-y-2">
                    @if(!empty($installment) and !empty($installment->options))
                        @php
                            $installmentOptions = explode(\App\Models\Installment::$optionsExplodeKey, $installment->options);
                        @endphp
                        @foreach($installmentOptions as $k => $option)
                            <div class="flex items-center gap-2">
                                <input type="text" name="installment_options[]" class="flex-1 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary" value="{{ $option }}"/>
                                <button type="button" class="js-remove-btn p-3 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                    <span class="material-symbols-outlined text-sm">delete</span>
                                </button>
                            </div>
                        @endforeach
                    @endif

                    <div id="installmentOptionsMainRow" class="d-none">
                        <div class="flex items-center gap-2 mt-2">
                            <input type="text" name="installment_options[]" class="flex-1 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary"/>
                            <button type="button" class="js-remove-btn p-3 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition-all">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_options_hint') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Capacity -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('public.capacity') }}</label>
                    <input type="number" name="capacity" value="{{ !empty($installment) ? $installment->capacity : old('capacity') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary @error('capacity') border-red-500 @enderror" placeholder="Empty means inactive this mode"/>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_capacity_hint') }}</p>
                    @error('capacity')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- User Group -->
                @php
                    $selectedGroupIds = !empty($installment) ? $installment->userGroups->pluck('group_id')->toArray() : [];
                @endphp
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('admin/main.user_group') }}</label>
                    <select name="group_ids[]" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary select2" multiple data-placeholder="{{ trans('admin/main.select_users_group') }}">
                        @foreach($userGroups as $userGroup)
                            <option value="{{ $userGroup->id }}" {{ in_array($userGroup->id, $selectedGroupIds) ? 'selected' : '' }}>{{ $userGroup->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_user_group_hint') }}</p>
                    @error('group_ids')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Start Date -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('admin/main.start_date') }}</label>
                    <div class="flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <span class="px-4 py-3 text-slate-400"><span class="material-symbols-outlined text-sm">calendar_today</span></span>
                        <input type="text" name="start_date" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 datetimepicker" autocomplete="off" value="{{ (!empty($installment) and !empty($installment->start_date)) ? dateTimeFormat($installment->start_date, 'Y-m-d H:i', false) : old('start_date') }}"/>
                    </div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_start_date_hint') }}</p>
                    @error('start_date')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('admin/main.end_date') }}</label>
                    <div class="flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                        <span class="px-4 py-3 text-slate-400"><span class="material-symbols-outlined text-sm">calendar_today</span></span>
                        <input type="text" name="end_date" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 datetimepicker" autocomplete="off" value="{{ (!empty($installment) and !empty($installment->end_date)) ? dateTimeFormat($installment->end_date, 'Y-m-d H:i', false) : old('end_date') }}"/>
                    </div>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_end_date_hint') }}</p>
                    @error('end_date')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>
    </div>
</div>
