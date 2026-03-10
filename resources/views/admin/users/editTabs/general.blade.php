<div id="general-tab" class="tab-content space-y-8 animate-in fade-in slide-in-from-right-4 duration-500 italic-none">
    <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id .'/update' }}" method="Post" class="space-y-8">
        {{ csrf_field() }}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 italic-none">
            <!-- Full Name -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">{{ trans('/admin/main.full_name') }}</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg">person</span>
                    <input type="text" name="full_name"
                           class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 @error('full_name') border-rose-500 bg-rose-50/10 @enderror italic-none"
                           value="{{ !empty($user) ? $user->full_name : old('full_name') }}"
                           placeholder="{{ trans('admin/main.create_field_full_name_placeholder') }}"/>
                </div>
                @error('full_name')
                    <p class="text-[10px] font-bold text-rose-500 mt-1 italic-none">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Selection -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">{{ trans('/admin/main.role_name') }}</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg">badge</span>
                    <select name="role_id" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 appearance-none @error('role_id') border-rose-500 bg-rose-50/10 @enderror italic-none">
                        <option disabled {{ empty($user) ? 'selected' : '' }}>{{ trans('admin/main.select_role') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" {{ (!empty($user) and $user->role_id == $role->id) ? 'selected' :''}}>{{ $role->caption }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-lg">expand_more</span>
                </div>
                @error('role_id')
                    <p class="text-[10px] font-bold text-rose-500 mt-1 italic-none">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">{{ trans('admin/main.email') }}</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg">mail</span>
                    <input type="email" name="email" value="{{ $user->email }}" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 @error('email') border-rose-500 bg-rose-50/10 @enderror italic-none">
                </div>
                @error('email')
                    <p class="text-[10px] font-bold text-rose-500 mt-1 italic-none">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">{{ trans('admin/main.mobile') }}</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors text-lg">smartphone</span>
                    <input type="text" name="mobile" value="{{ $user->mobile }}" class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-bold text-slate-700 @error('mobile') border-rose-500 bg-rose-50/10 @enderror italic-none">
                </div>
                @error('mobile')
                    <p class="text-[10px] font-bold text-rose-500 mt-1 italic-none">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Bio / About -->
        <div class="space-y-6 italic-none">
            <div class="space-y-2 italic-none">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('admin/main.bio') }}</label>
                <textarea name="bio" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-medium text-slate-700 placeholder:text-slate-300 italic-none">{{ $user->bio }}</textarea>
            </div>
            
            <div class="space-y-2 italic-none">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block italic-none">{{ trans('site.about') }}</label>
                <textarea name="about" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm font-medium text-slate-700 placeholder:text-slate-300 italic-none">{{ $user->about }}</textarea>
            </div>
        </div>

        <!-- Switches Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-full italic-none">
             <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer group hover:bg-slate-100/50 transition-all italic-none">
                <div class="flex flex-col italic-none">
                    <span class="text-xs font-black text-slate-700 uppercase tracking-widest italic-none">Verified Badge</span>
                    <span class="text-[9px] font-bold text-slate-400 italic-none">Enable blue checkmark on profile</span>
                </div>
                <div class="relative inline-flex items-center cursor-pointer italic-none">
                    <input type="checkbox" name="verified" value="1" {{ (!empty($user) and $user->verified) ? 'checked' : '' }} class="sr-only peer italic-none">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:after:w-5 after:transition-all peer-checked:bg-primary italic-none"></div>
                </div>
            </label>

            <label class="flex items-center justify-between p-4 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer group hover:bg-slate-100/50 transition-all italic-none">
                <div class="flex flex-col italic-none">
                    <span class="text-xs font-black text-slate-700 uppercase tracking-widest italic-none">Affiliate Status</span>
                    <span class="text-[9px] font-bold text-slate-400 italic-none">Allow user to join affiliate program</span>
                </div>
                <div class="relative inline-flex items-center cursor-pointer italic-none">
                    <input type="checkbox" name="affiliate" value="1" {{ (!empty($user) and $user->affiliate) ? 'checked' : '' }} class="sr-only peer italic-none">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:after:w-5 after:transition-all peer-checked:bg-primary italic-none"></div>
                </div>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 italic-none">
            <button type="button" onclick="closeEditPanel()" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-600 transition-all italic-none">Cancel</button>
            <button type="submit" class="px-8 py-2.5 bg-primary text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:shadow-primary/40 active:scale-95 transition-all italic-none">
                Save Changes
            </button>
        </div>
    </form>
</div>
