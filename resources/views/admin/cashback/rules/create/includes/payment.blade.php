<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
    <div class="space-y-6">
        <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-rounded text-lg">payments</span>
            {{ trans('update.payment') }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group space-y-2">
                <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.amount') }}</label>
                <div class="relative group">
                    <input type="number" name="amount" value="{{ !empty($rule) ? $rule->amount : old('amount') }}" 
                           class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('amount') ring-2 ring-rose-100 @enderror"/>
                    <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-lg text-rose-500">sell</span>
                </div>
                @error('amount')
                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group space-y-2">
                <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.amount_type') }}</label>
                <div class="relative">
                    <select name="amount_type" class="js-amount-type-select w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none">
                        <option value="fixed_amount" {{ (!empty($rule) and $rule->amount_type == 'fixed_amount') ? 'selected' : '' }}>{{ trans('update.fixed_amount') }}</option>
                        <option value="percent" {{ (!empty($rule) and $rule->amount_type == 'percent') ? 'selected' : '' }}>{{ trans('update.percent') }}</option>
                    </select>
                    <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">analytics</span>
                </div>
                @error('amount_type')
                    <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="js-apply-cashback-per-item p-4 bg-gray-50 rounded-2xl border border-gray-100 transition-all {{ (empty($rule) or $rule->amount_type == 'fixed_amount') ? '' : 'd-none' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-primary shadow-sm border border-gray-100">
                        <span class="material-symbols-rounded text-xl">inventory_2</span>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-gray-700 cursor-pointer" for="perItemSwitch">{{ trans('update.apply_cashback_per_item') }}</label>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-tight">{{ trans('update.apply_cashback_per_item_hint') }}</p>
                    </div>
                </div>
                <div class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="apply_cashback_per_item" id="perItemSwitch" class="sr-only peer" {{ (!empty($rule) && $rule->apply_cashback_per_item) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6 border-l border-gray-50 pl-12">
        <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-rounded text-lg">settings_suggest</span>
            Limits & Thresholds
        </h3>

        <div class="js-max-amount-field form-group space-y-2 {{ (!empty($rule) and $rule->amount_type == 'percent') ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.max_amount') }}</label>
            <div class="relative group">
                <input type="number" name="max_amount" value="{{ !empty($rule) ? $rule->max_amount : old('max_amount') }}" 
                       class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('max_amount') ring-2 ring-rose-100 @enderror"/>
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-lg">vertical_align_top</span>
            </div>
            <div class="mt-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100/50">
                <p class="text-[10px] text-blue-600 font-bold leading-tight">{{ trans('update.cashback_max_amount_hint') }}</p>
            </div>
            @error('max_amount')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2 text-rose-500">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.min_amount') }}</label>
            <div class="relative group">
                <input type="number" name="min_amount" value="{{ !empty($rule) ? $rule->min_amount : old('min_amount') }}" 
                       class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('min_amount') ring-2 ring-rose-100 @enderror"/>
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary transition-colors text-lg text-rose-500">vertical_align_bottom</span>
            </div>
            <div class="mt-2 p-3 bg-blue-50/50 rounded-xl border border-blue-100/50">
                <p class="text-[10px] text-blue-600 font-bold leading-tight">{{ trans('update.cashback_min_amount_hint') }}</p>
            </div>
            @error('min_amount')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

