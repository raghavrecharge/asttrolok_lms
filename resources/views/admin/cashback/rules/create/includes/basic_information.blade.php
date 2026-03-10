<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
    <div class="space-y-6">
        <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-rounded text-lg">info</span>
            {{ trans('public.basic_information') }}
        </h3>

        @if(!empty(getGeneralSettings('content_translate')))
            <div class="form-group space-y-2">
                <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('auth.language') }}</label>
                <div class="relative">
                    <select name="locale" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none {{ !empty($rule) ? 'js-edit-content-locale' : '' }}">
                        @foreach($userLanguages as $lang => $language)
                            <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }} {{ (!empty($definedLanguage) and is_array($definedLanguage) and in_array(mb_strtolower($lang), $definedLanguage)) ? '('. trans('panel.content_defined') .')' : '' }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
                @error('locale')
                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                @enderror
            </div>
        @else
            <input type="hidden" name="locale" value="{{ getDefaultLocale() }}">
        @endif

        <div class="form-group space-y-2">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('public.title') }}</label>
            <input type="text" name="title" value="{{ !empty($rule) ? $rule->title : old('title') }}" 
                   class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('title') ring-2 ring-rose-100 @enderror" 
                   placeholder="Rule Title"/>
            @error('title')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.users') }}</label>
            <div class="relative group">
                <select name="users_ids[]" class="search-user-select2" multiple data-placeholder="{{ trans('public.search_user') }}">
                    @if(!empty($rule) and !empty($rule->users))
                        @foreach($rule->users as $ruleUser)
                            <option value="{{ $ruleUser->id }}" selected>{{ $ruleUser->full_name }}</option>
                        @endforeach
                    @endif
                </select>
                <div class="mt-2 flex items-center gap-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100/50 group-focus-within:border-blue-200 transition-all">
                    <span class="material-symbols-rounded text-blue-500 text-lg">info</span>
                    <p class="text-[10px] text-blue-600 font-bold leading-tight">{{ trans('update.cashback_users_hint') }}</p>
                </div>
            </div>
            @error('users_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        @php
            $selectedGroupIds = !empty($rule) ? $rule->userGroups->pluck('id')->toArray() : [];
        @endphp

        <div class="form-group space-y-2">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.user_group') }}</label>
            <div class="relative">
                <select name="group_ids[]" class="select2" multiple data-placeholder="{{ trans('admin/main.select_users_group') }}">
                    @foreach($userGroups as $userGroup)
                        <option value="{{ $userGroup->id }}" {{ in_array($userGroup->id, $selectedGroupIds) ? 'selected' : '' }}>{{ $userGroup->name }}</option>
                    @endforeach
                </select>
                <div class="mt-2 flex items-center gap-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100/50">
                    <span class="material-symbols-rounded text-blue-500 text-lg">groups</span>
                    <p class="text-[10px] text-blue-600 font-bold leading-tight">{{ trans('update.cashback_user_groups_hint') }}</p>
                </div>
            </div>
            @error('group_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-6 border-l border-gray-50 pl-12">
        <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-rounded text-lg">calendar_month</span>
            Duration & Lifecycle
        </h3>

        <div class="form-group space-y-2">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.start_date') }}</label>
            <div class="relative">
                <input type="text" name="start_date" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all datetimepicker @error('start_date') ring-2 ring-rose-100 @enderror"
                       autocomplete="off"
                       value="{{ (!empty($rule) and !empty($rule->start_date)) ? dateTimeFormat($rule->start_date, 'Y-m-d H:i', false) : dateTimeFormat(time(), 'Y-m-d H:i', false) }}"/>
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event_available</span>
            </div>
            <div class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ trans('update.cashback_start_date_hint') }}</p>
            </div>
            @error('start_date')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.end_date') }}</label>
            <div class="relative">
                <input type="text" name="end_date" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all datetimepicker @error('end_date') ring-2 ring-rose-100 @enderror"
                       autocomplete="off"
                       value="{{ (!empty($rule) and !empty($rule->end_date)) ? dateTimeFormat($rule->end_date, 'Y-m-d H:i', false) : old('end_date') }}"/>
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg">event_busy</span>
            </div>
            <div class="mt-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">{{ trans('update.cashback_end_date_hint') }}</p>
            </div>
            @error('end_date')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
