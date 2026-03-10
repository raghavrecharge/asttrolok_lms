<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
    <div class="space-y-6">
        <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2">
            <span class="material-symbols-rounded text-lg">target</span>
            {{ trans('update.target_products') }}
        </h3>

        <div class="form-group space-y-2">
            <label class="text-sm font-bold text-gray-700 ml-1 d-block">{{ trans('update.target_types') }}</label>
            <div class="relative">
                <select name="target_type" class="js-target-types-input w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none @error('target_types') ring-2 ring-rose-100 @enderror">
                    @foreach(\App\Models\CashbackRule::$targetTypes as $type)
                        <option value="{{ $type }}" @if(!empty($rule) and $rule->target_type == $type) selected @endif>{{ trans('update.target_types_'.$type) }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">category</span>
            </div>
            @error('target_types')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2 js-select-target-field {{ empty($rule) ? 'd-none' : '' }}">
            <label class="text-sm font-bold text-gray-700 ml-1 d-block">{{ trans('update.select_target') }}</label>
            @php
                $targets = [
                    'courses' => \App\Models\CashbackRule::$courseTargets,
                    'store_products' => \App\Models\CashbackRule::$productTargets,
                    'bundles' => \App\Models\CashbackRule::$bundleTargets,
                    'meetings' => \App\Models\CashbackRule::$meetingTargets,
                    'subscription_packages' => \App\Models\CashbackRule::$subscriptionTargets,
                    'registration_packages' => \App\Models\CashbackRule::$registrationPackagesTargets,
                ];
            @endphp
            <div class="relative">
                <select name="target" class="js-target-input w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none @error('target') ring-2 ring-rose-100 @enderror">
                    <option value="">{{ trans('update.select_target') }}</option>
                    @foreach($targets as $targetKey => $targetItems)
                        @foreach($targetItems as $target)
                            <option value="{{ $target }}" class="js-target-option js-target-option-{{ $targetKey }} {{ (!empty($rule) and $rule->target_type == $targetKey) ? '' : 'd-none' }}" @if(!empty($rule) and $rule->target == $target) selected @endif>{{ trans('update.'.$target) }}</option>
                        @endforeach
                    @endforeach
                </select>
                <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">list_alt</span>
            </div>
            @error('target')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase d-none">{{ $message }}</p>
            @enderror
        </div>

        @php
            $selectedCategoryIds = !empty($rule) ? $rule->categories->pluck('id')->toArray() : [];
        @endphp

        <div class="form-group space-y-2 js-specific-categories-field {{ (!empty($rule) and $rule->target == "specific_categories") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.specific_categories') }}</label>
            <select name="category_ids[]" id="categories" class="select2" multiple data-placeholder="{{ trans('public.choose_category') }}">
                @foreach($categories as $category)
                    @if(!empty($category->subCategories) and count($category->subCategories))
                        <optgroup label="{{  $category->title }}">
                            @foreach($category->subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}" {{ in_array($subCategory->id, $selectedCategoryIds) ? 'selected' : '' }}>{{ $subCategory->title }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategoryIds) ? 'selected' : '' }}>{{ $category->title }}</option>
                    @endif
                @endforeach
            </select>
            @error('category_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-6 border-l border-gray-50 pl-12">
        <h3 class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-2 pt-1 md:pt-0">
            <span class="material-symbols-rounded text-lg">fact_check</span>
            Specific Requirements
        </h3>

        <div class="form-group space-y-2 js-specific-instructors-field {{ (!empty($rule) and $rule->target == "specific_instructors") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{trans('update.specific_instructors')}}</label>
            <select name="instructor_ids[]" multiple="multiple" data-search-option="just_teacher_role" class="search-user-select2"
                    data-placeholder="{{trans('public.search_instructors')}}">
                @if(!empty($rule) and count($rule->instructors))
                    @foreach($rule->instructors as $instructor)
                        <option value="{{ $instructor->id }}" selected>{{ $instructor->full_name }}</option>
                    @endforeach
                @endif
            </select>
            @error('instructor_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2 js-specific-sellers-field {{ (!empty($rule) and $rule->target == "specific_sellers") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{trans('update.specific_sellers')}}</label>
            <select name="seller_ids[]" multiple="multiple" data-search-option="except_user" class="search-user-select2"
                    data-placeholder="{{trans('public.search_instructors')}}">
                @if(!empty($rule) and count($rule->sellers))
                    @foreach($rule->sellers as $seller)
                        <option value="{{ $seller->id }}" selected>{{ $seller->full_name }}</option>
                    @endforeach
                @endif
            </select>
            @error('seller_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2 js-specific-courses-field {{ (!empty($rule) and $rule->target == "specific_courses") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.specific_courses') }}</label>
            <select name="webinar_ids[]" multiple="multiple" class="search-webinar-select2"
                    data-placeholder="Search classes">
                @if(!empty($rule) and count($rule->webinars))
                    @foreach($rule->webinars as $webinar)
                        <option value="{{ $webinar->id }}" selected>{{ $webinar->title }}</option>
                    @endforeach
                @endif
            </select>
            @error('webinar_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2 js-specific-products-field {{ (!empty($rule) and $rule->target == "specific_products") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{trans('update.specific_products')}}</label>
            <select name="product_ids[]" multiple="multiple" class="search-product-select2"
                    data-placeholder="{{trans('update.search_product')}}">
                @if(!empty($rule) and count($rule->products))
                    @foreach($rule->products as $product)
                        <option value="{{ $product->id }}" selected>{{ $product->title }}</option>
                    @endforeach
                @endif
            </select>
            @error('product_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group space-y-2 js-specific-bundles-field {{ (!empty($rule) and $rule->target == "specific_bundles") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.specific_bundles') }}</label>
            <select name="bundle_ids[]" multiple="multiple" class="search-bundle-select2" data-placeholder="Search bundles">
                @if(!empty($rule) and count($rule->bundles))
                    @foreach($rule->bundles as $bundle)
                        <option value="{{ $bundle->id }}" selected>{{ $bundle->title }}</option>
                    @endforeach
                @endif
            </select>
            @error('bundle_ids')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        @php
            $selectedSubscriptionPackagesIds = !empty($rule) ? $rule->subscribes->pluck('id')->toArray() : [];
        @endphp
        <div class="form-group space-y-2 js-subscription-specific-packages-field {{ (!empty($rule) and $rule->target_type == "subscription_packages" and $rule->target == "specific_packages") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.specific_packages') }}</label>
            <select name="subscribe_ids[]" multiple="multiple" class="select2" data-placeholder="{{ trans('update.select_packages') }}">
                @if(!empty($subscriptionPackages) and $subscriptionPackages->count() > 0)
                    @foreach($subscriptionPackages as $subscriptionPackage)
                        <option value="{{ $subscriptionPackage->id }}" {{ in_array($subscriptionPackage->id, $selectedSubscriptionPackagesIds) ? 'selected' : '' }}>{{ $subscriptionPackage->title }}</option>
                    @endforeach
                @endif
            </select>
            @error('subscription_packages')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>

        @php
            $selectedRegistrationPackagesIds = !empty($rule) ? $rule->registrationPackages->pluck('id')->toArray() : [];
        @endphp
        <div class="form-group space-y-2 js-registration-specific-packages-field {{ (!empty($rule) and $rule->target_type == "registration_packages" and $rule->target == "specific_packages") ? '' : 'd-none' }}">
            <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.specific_packages') }}</label>
            <select name="registration_package_ids[]" multiple="multiple" class="select2" data-placeholder="{{ trans('update.select_packages') }}">
                @if(!empty($registrationPackages) and $registrationPackages->count() > 0)
                    @foreach($registrationPackages as $registrationPackage)
                        <option value="{{ $registrationPackage->id }}" {{ in_array($registrationPackage->id, $selectedRegistrationPackagesIds) ? 'selected' : '' }}>{{ $registrationPackage->title }} ({{ $registrationPackage->role }})</option>
                    @endforeach
                @endif
            </select>
            @error('registration_packages')
                <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 tracking-wide uppercase">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
