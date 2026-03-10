<div class="compact-form">
    <div class="compact-form-row">
        <div class="compact-field">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.upfront') }}</label>
            <input type="number" name="upfront" value="{{ !empty($installment) ? $installment->upfront : old('upfront') }}" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary @error('upfront') border-red-500 @enderror"/>
            @error('upfront')
                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="compact-field">
            <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.upfront_type') }}</label>
            <select name="upfront_type" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary appearance-none">
                <option value="fixed_amount" {{ (!empty($installment) and $installment->upfront_type == 'fixed_amount') ? 'selected' : '' }}>{{ trans('update.fixed_amount') }}</option>
                <option value="percent" {{ (!empty($installment) and $installment->upfront_type == 'percent') ? 'selected' : '' }}>{{ trans('update.percent') }}</option>
            </select>
            @error('upfront_type')
                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Installment Steps --}}
    <div class="mt-4 space-y-2">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">analytics</span>
                <h5 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider">{{ trans('update.payment_steps') }}</h5>
            </div>

            <button type="button" class="js-add-btn flex items-center gap-2 px-4 py-2 bg-primary text-white text-[10px] font-black uppercase tracking-widest rounded-lg shadow-lg shadow-primary/20 hover:shadow-primary/30 transition-all active:scale-95">
                <i class="fa fa-plus"></i>
                {{ trans('update.add_step') }}
            </button>
        </div>

        <div class="space-y-2 mt-2">
            @if(!empty($installment) and !empty($installment->steps))
                @foreach($installment->steps as $stepRow)
                    @include('admin.financial.installments.create.includes.installment_step_inputs',['step' => $stepRow])
                @endforeach
            @endif

            <div id="installmentStepsMainRow" class="d-none">
                @include('admin.financial.installments.create.includes.installment_step_inputs')
            </div>
        </div>
    </div>
</div>
