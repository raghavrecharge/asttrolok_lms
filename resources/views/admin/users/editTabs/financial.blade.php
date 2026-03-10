<div id="financial-tab" class="tab-content space-y-8 animate-in fade-in slide-in-from-right-4 duration-500 hidden italic-none">
    <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id .'/financialUpdate' }}" method="Post" class="space-y-8 italic-none">
        {{ csrf_field() }}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 italic-none">
            
            <div class="space-y-6 italic-none">
                <!-- Account Type -->
                <div class="space-y-2 italic-none">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('financial.account_type') }}</label>
                    <div class="relative group italic-none">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg italic-none">account_balance</span>
                        <select name="bank_id" class="js-user-bank-input w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 appearance-none @error('bank_id') border-rose-500 @enderror italic-none">
                            <option selected disabled>{{ trans('financial.select_account_type') }}</option>
                            @foreach($userBanks as $userBank)
                                <option value="{{ $userBank->id }}" @if(!empty($user) and !empty($user->selectedBank) and $user->selectedBank->user_bank_id == $userBank->id) selected="selected" @endif data-specifications="{{ json_encode($userBank->specifications->pluck('name','id')->toArray()) }}">{{ $userBank->title }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-lg italic-none">expand_more</span>
                    </div>
                    @error('bank_id')
                        <p class="text-[10px] font-bold text-rose-500 mt-1 italic-none">{{ $message }}</p>
                    @enderror
                </div>

                <div class="js-bank-specifications-card space-y-4 italic-none">
                    @if(!empty($user) and !empty($user->selectedBank) and !empty($user->selectedBank->bank))
                        @foreach($user->selectedBank->bank->specifications as $specification)
                            @php
                                $selectedBankSpecification = $user->selectedBank->specifications->where('user_selected_bank_id', $user->selectedBank->id)->where('user_bank_specification_id', $specification->id)->first();
                            @endphp
                            <div class="space-y-2 italic-none">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ $specification->name }}</label>
                                <input type="text" name="bank_specifications[{{ $specification->id }}]" value="{{ (!empty($selectedBankSpecification)) ? $selectedBankSpecification->value : '' }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 italic-none"/>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Identity Scan -->
                <div class="space-y-2 italic-none">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('financial.identity_scan') }}</label>
                    <div class="flex gap-2 italic-none">
                        <div class="relative flex-1 group italic-none">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg italic-none">file_present</span>
                            <input type="text" name="identity_scan" id="identity_scan_input" value="{{ !empty($user->identity_scan) ? $user->identity_scan : old('identity_scan') }}" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 italic-none"/>
                        </div>
                        <button type="button" class="admin-file-manager size-12 flex-shrink-0 bg-primary/10 text-primary rounded-2xl flex items-center justify-center hover:bg-primary/20 transition-all italic-none" data-input="identity_scan_input" data-preview="holder">
                            <span class="material-symbols-outlined italic-none">upload</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-6 italic-none">
                 <!-- Address -->
                 <div class="space-y-2 italic-none">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('financial.address') }}</label>
                    <div class="relative group italic-none">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg italic-none">location_on</span>
                        <input type="text" name="address"
                               class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 italic-none"
                               value="{{ !empty($user) ? $user->address : old('address') }}"
                               placeholder="{{ trans('financial.address') }}"/>
                    </div>
                </div>

                <!-- Commission -->
                @if(!$user->isUser())
                    <div class="space-y-2 italic-none">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('admin/main.user_commission') }} (%)</label>
                        <div class="relative group italic-none">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg italic-none">percent</span>
                            <input type="text" name="commission"
                                   class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 italic-none"
                                   value="{{ !empty($user) ? $user->commission : old('commission') }}"
                                   placeholder="0"/>
                        </div>
                    </div>
                @endif

                 <!-- Bonus Amount -->
                 <div class="js-registration-bonus-field space-y-2 {{ $user->enable_registration_bonus ? '' : 'hidden' }} italic-none">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('update.registration_bonus_amount') }}</label>
                    <div class="relative group italic-none">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg italic-none">redeem</span>
                        <input type="text" name="registration_bonus_amount"
                               class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 italic-none"
                               value="{{ !empty($user) ? $user->registration_bonus_amount : old('registration_bonus_amount') }}"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Switches -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 italic-none">
            @foreach([
                ['name' => 'financial_approval', 'label' => trans('admin/main.financial_approval'), 'desc' => 'Auto-approve financial requests'],
                ['name' => 'enable_installments', 'label' => trans('update.enable_installments'), 'desc' => 'Allow purchasing via installments'],
                ['name' => 'installment_approval', 'label' => trans('update.installment_approval'), 'desc' => 'Auto-approve installment requests'],
                ['name' => 'disable_cashback', 'label' => trans('update.disable_cashback'), 'desc' => 'Disable cashback for this user'],
                ['name' => 'enable_registration_bonus', 'label' => trans('update.enable_registration_bonus'), 'desc' => 'Give welcome bonus on registration']
            ] as $switch)
                <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer group hover:bg-slate-100/50 transition-all italic-none">
                    <div class="flex flex-col italic-none">
                        <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest italic-none">{{ $switch['label'] }}</span>
                        <span class="text-[8px] font-bold text-slate-400 italic-none">{{ $switch['desc'] }}</span>
                    </div>
                    <div class="relative inline-flex items-center cursor-pointer italic-none">
                        <input type="hidden" name="{{ $switch['name'] }}" value="0">
                        <input type="checkbox" name="{{ $switch['name'] }}" id="{{ $switch['name'] }}Toggle" value="1" {{ ($user->{$switch['name']} or old($switch['name']) == 'on') ? 'checked' : '' }} class="sr-only peer italic-none">
                        <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:after:w-4 after:transition-all peer-checked:bg-primary italic-none"></div>
                    </div>
                </label>
            @endforeach
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 italic-none">
            <button type="button" onclick="closeEditPanel()" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all italic-none">Cancel</button>
            <button type="submit" class="px-8 py-2.5 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-95 transition-all italic-none">
                {{ trans('admin/main.submit') }}
            </button>
        </div>
    </form>
</div>
