<div class="row">
    <div class="col-12 col-md-8">
        <div class="space-y-2">

            <!-- Verification Status -->
            <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block" for="verificationSwitch">{{ trans('update.verification') }}</label>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.installment_verification_hint') }}</p>
                </div>
                <div class="relative inline-block w-10 h-6">
                    <input type="checkbox" name="verification" id="verificationSwitch" {{ (!empty($installment) && $installment->verification) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-primary transition-all duration-300">
                    <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                </div>
            </div>

            <!-- Verification Fields (Conditional) -->
            <div class="js-verification-fields space-y-2 {{ (!empty($installment) && $installment->verification) ? '' : 'd-none' }}">
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.verification_description') }}</label>
                    <textarea name="verification_description" class="summernote @error('verification_description') border-red-500 @enderror" data-height="180">{{ (!empty($installment)) ? $installment->verification_description :'' }}</textarea>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_verification_description_hint') }}</p>
                    @error('verification_description')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <!-- Banner -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.verification_banner') }}</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                <button type="button" class="px-4 py-3 bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 admin-file-manager" data-input="verification_banner" data-preview="holder">
                                    <span class="material-symbols-outlined text-sm">upload</span>
                                </button>
                                <input type="text" name="verification_banner" id="verification_banner" value="{{ !empty($installment) ? $installment->verification_banner : old('verification_banner') }}" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 @error('verification_banner') text-red-500 @enderror"/>
                                <button type="button" class="px-4 py-3 text-slate-400 admin-file-view" data-input="verification_banner">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_verification_banner_hint') }}</p>
                        @error('verification_banner')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Video -->
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.verification_video') }}</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                                <button type="button" class="px-4 py-3 bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 admin-file-manager" data-input="verification_video" data-preview="holder">
                                    <span class="material-symbols-outlined text-sm">upload</span>
                                </button>
                                <input type="text" name="verification_video" id="verification_video" value="{{ !empty($installment) ? $installment->verification_video : old('verification_video') }}" class="flex-1 bg-transparent border-none px-4 py-3 text-sm focus:ring-0 @error('verification_video') text-red-500 @enderror"/>
                                <button type="button" class="px-4 py-3 text-slate-400 admin-file-view" data-input="verification_video">
                                    <span class="material-symbols-outlined text-sm">visibility</span>
                                </button>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ trans('update.installment_verification_video_hint') }}</p>
                        @error('verification_video')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Request Uploads -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block" for="request_uploadsSwitch">{{ trans('update.request_uploads') }}</label>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.installment_request_uploads_hint') }}</p>
                    </div>
                    <div class="relative inline-block w-10 h-6">
                        <input type="checkbox" name="request_uploads" id="request_uploadsSwitch" {{ (!empty($installment) && $installment->request_uploads) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-primary transition-all duration-300">
                        <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                    </div>
                </div>

                <!-- Bypass Verification -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 flex items-start justify-between gap-4">
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block" for="bypassSwitch">{{ trans('update.bypass_verification_for_verified_users') }}</label>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase mt-1">{{ trans('update.installment_bypass_verification_for_verified_users_hint') }}</p>
                    </div>
                    <div class="relative inline-block w-10 h-6">
                        <input type="checkbox" name="bypass_verification_for_verified_users" id="bypassSwitch" {{ (!empty($installment) && $installment->bypass_verification_for_verified_users) ? 'checked' : '' }} class="peer appearance-none w-10 h-6 bg-slate-200 dark:bg-slate-700 rounded-full cursor-pointer checked:bg-primary transition-all duration-300">
                        <span class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-4 pointer-events-none"></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
