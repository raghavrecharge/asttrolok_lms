<div class="row align-items-center flex gap-4 mt-2 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 group">
    <div class="flex-1 min-w-[200px] space-y-1">
        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.title') }}</label>
        <input type="text" name="steps[{{ !empty($step) ? $step->id : 'record' }}][title]" value="{{ (!empty($step) and !empty($step->translate($selectedLocale))) ? $step->translate($selectedLocale)->title : '' }}" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary"/>
    </div>

    <div class="w-32 space-y-1">
        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('update.deadline') }}</label>
        <input type="number" name="steps[{{ !empty($step) ? $step->id : 'record' }}][deadline]" value="{{ !empty($step) ? $step->deadline : '' }}" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary text-center"/>
    </div>

    <div class="flex-1 min-w-[250px] flex gap-2">
        <div class="flex-1 space-y-1">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.amount') }}</label>
            <input type="number" name="steps[{{ !empty($step) ? $step->id : 'record' }}][amount]" value="{{ !empty($step) ? $step->amount : '' }}" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2 text-sm focus:ring-primary focus:border-primary text-center"/>
        </div>

        <div class="w-32 space-y-1">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('update.amount_type') }}</label>
            <select name="steps[{{ !empty($step) ? $step->id : 'record' }}][amount_type]" class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 rounded-xl px-2 py-2 text-sm focus:ring-primary focus:border-primary appearance-none">
                <option value="fixed_amount" {{ (!empty($step) and $step->amount_type == 'fixed_amount') ? 'selected' : '' }}>{{ trans('update.fixed_amount') }}</option>
                <option value="percent" {{ (!empty($step) and $step->amount_type == 'percent') ? 'selected' : '' }}>{{ trans('update.percent') }}</option>
            </select>
        </div>
    </div>

    <div class="pt-5">
        <button type="button" class="js-remove-btn p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
            <span class="material-symbols-outlined text-[20px]">delete</span>
        </button>
    </div>
</div>
