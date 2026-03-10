<div class="row">
    <div class="col-12 col-md-8">
        <div class="space-y-2">

            <!-- Target Types -->
            <div class="space-y-2">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.target_types') }}</label>
                <select name="target_type" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary appearance-none js-target-types-input @error('target_types') border-red-500 @enderror">
                    @foreach(\App\Models\Installment::$targetTypes as $type)
                        <option value="{{ $type }}" @if(!empty($installment) and $installment->target_type == $type) selected @endif>{{ trans('update.target_types_'.$type) }}</option>
                    @endforeach
                </select>
                @error('target_types')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Select Target (Conditional) -->
            <div class="space-y-2 js-select-target-field {{ empty($installment) ? 'd-none' : '' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.select_target') }}</label>
                @php
                    $targets = [
                        'courses' => \App\Models\Installment::$courseTargets,
                        'store_products' => \App\Models\Installment::$productTargets,
                        'bundles' => \App\Models\Installment::$bundleTargets,
                        'meetings' => \App\Models\Installment::$meetingTargets,
                        'subscription_packages' => \App\Models\Installment::$subscriptionTargets,
                        'registration_packages' => \App\Models\Installment::$registrationPackagesTargets,
                    ];
                @endphp
                <select name="target" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary appearance-none js-target-input @error('target') border-red-500 @enderror">
                    <option value="">{{ trans('update.select_target') }}</option>
                    @foreach($targets as $targetKey => $targetItems)
                        @foreach($targetItems as $target)
                            <option value="{{ $target }}" class="js-target-option js-target-option-{{ $targetKey }} {{ (!empty($installment) and $installment->target_type == $targetKey) ? '' : 'd-none' }}" @if(!empty($installment) and $installment->target == $target) selected @endif>{{ trans('update.'.$target) }}</option>
                        @endforeach
                    @endforeach
                </select>
                @error('target')
                    <p class="text-xs text-red-500 font-bold mt-1 d-none">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Categories (Conditional) -->
            @php
                $selectedCategoryIds = !empty($installment) ? $installment->categories->pluck('id')->toArray() : [];
            @endphp
            <div class="space-y-2 js-specific-categories-field {{ (!empty($installment) and $installment->target == "specific_categories") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.specific_categories') }}</label>
                <select name="category_ids[]" id="categories" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary select2" multiple data-placeholder="{{ trans('public.choose_category') }}">
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
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Instructors (Conditional) -->
            <div class="space-y-2 js-specific-instructors-field {{ (!empty($installment) and $installment->target == "specific_instructors") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{trans('update.specific_instructors')}}</label>
                <select name="instructor_ids[]" multiple="multiple" data-search-option="just_teacher_role" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary search-user-select2" data-placeholder="{{trans('public.search_instructors')}}">
                    @if(!empty($installment) and count($installment->instructors))
                        @foreach($installment->instructors as $instructor)
                            <option value="{{ $instructor->id }}" selected>{{ $instructor->full_name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('instructor_ids')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Sellers (Conditional) -->
            <div class="space-y-2 js-specific-sellers-field {{ (!empty($installment) and $installment->target == "specific_sellers") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{trans('update.specific_sellers')}}</label>
                <select name="seller_ids[]" multiple="multiple" data-search-option="except_user" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary search-user-select2" data-placeholder="{{trans('public.search_instructors')}}">
                    @if(!empty($installment) and count($installment->sellers))
                        @foreach($installment->sellers as $seller)
                            <option value="{{ $seller->id }}" selected>{{ $seller->full_name }}</option>
                        @endforeach
                    @endif
                </select>
                @error('seller_ids')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Courses (Conditional) -->
            <div class="space-y-2 js-specific-courses-field {{ (!empty($installment) and $installment->target == "specific_courses") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.specific_courses') }}</label>
                <select name="webinar_ids[]" multiple="multiple" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary search-webinar-select2" data-placeholder="Search classes">
                    @if(!empty($installment) and count($installment->webinars))
                        @foreach($installment->webinars as $webinar)
                            <option value="{{ $webinar->id }}" selected>{{ $webinar->title }}</option>
                        @endforeach
                    @endif
                </select>
                @error('webinar_ids')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Products (Conditional) -->
            <div class="space-y-2 js-specific-products-field {{ (!empty($installment) and $installment->target == "specific_products") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{trans('update.specific_products')}}</label>
                <select name="product_ids[]" multiple="multiple" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary search-product-select2" data-placeholder="{{trans('update.search_product')}}">
                    @if(!empty($installment) and count($installment->products))
                        @foreach($installment->products as $product)
                            <option value="{{ $product->id }}" selected>{{ $product->title }}</option>
                        @endforeach
                    @endif
                </select>
                @error('product_ids')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specific Bundles (Conditional) -->
            <div class="space-y-2 js-specific-bundles-field {{ (!empty($installment) and $installment->target == "specific_bundles") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.specific_bundles') }}</label>
                <select name="bundle_ids[]" multiple="multiple" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary search-bundle-select2" data-placeholder="Search bundles">
                    @if(!empty($installment) and count($installment->bundles))
                        @foreach($installment->bundles as $bundle)
                            <option value="{{ $bundle->id }}" selected>{{ $bundle->title }}</option>
                        @endforeach
                    @endif
                </select>
                @error('bundle_ids')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Subscription Specific Package (Conditional) -->
            @php
                $selectedSubscriptionPackagesIds = !empty($installment) ? $installment->subscribes->pluck('id')->toArray() : [];
            @endphp
            <div class="space-y-2 js-subscription-specific-packages-field {{ (!empty($installment) and $installment->target_type == "subscription_packages" and $installment->target == "specific_packages") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.specific_packages') }}</label>
                <select name="subscribe_ids[]" multiple="multiple" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary select2" data-placeholder="{{ trans('update.select_packages') }}">
                    @if(!empty($subscriptionPackages) and $subscriptionPackages->count() > 0)
                        @foreach($subscriptionPackages as $subscriptionPackage)
                            <option value="{{ $subscriptionPackage->id }}" {{ in_array($subscriptionPackage->id, $selectedSubscriptionPackagesIds) ? 'selected' : '' }}>{{ $subscriptionPackage->title }}</option>
                        @endforeach
                    @endif
                </select>
                @error('subscription_packages')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Registration Specific Package (Conditional) -->
            @php
                $selectedRegistrationPackagesIds = !empty($installment) ? $installment->registrationPackages->pluck('id')->toArray() : [];
            @endphp
            <div class="space-y-2 js-registration-specific-packages-field {{ (!empty($installment) and $installment->target_type == "registration_packages" and $installment->target == "specific_packages") ? '' : 'd-none' }}">
                <label class="text-sm font-bold text-slate-700 dark:text-slate-300 block">{{ trans('update.specific_packages') }}</label>
                <select name="registration_package_ids[]" multiple="multiple" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-sm focus:ring-primary focus:border-primary select2" data-placeholder="{{ trans('update.select_packages') }}">
                    @if(!empty($registrationPackages) and $registrationPackages->count() > 0)
                        @foreach($registrationPackages as $registrationPackage)
                            <option value="{{ $registrationPackage->id }}" {{ in_array($registrationPackage->id, $selectedRegistrationPackagesIds) ? 'selected' : '' }}>{{ $registrationPackage->title }} ({{ $registrationPackage->role }})</option>
                        @endforeach
                    @endif
                </select>
                @error('registration_packages')
                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>
    </div>
</div>
