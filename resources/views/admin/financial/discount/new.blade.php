@extends('admin.layouts.app')

@push('styles_top')
    <style>
        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            border: none !important;
            background-color: #f9fafb !important;
            border-radius: 1rem !important;
            padding: 0.5rem 0.75rem !important;
            min-height: 48px !important;
            font-weight: 700 !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single {
            box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.05) !important;
        }
    </style>
@endpush

@section('content')
    <section class="section text-left">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Financial / Discounts & Coupons / Configuration</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/financial/discounts" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-gray-50 transition-all shadow-sm active:scale-95">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ getAdminPanelUrl() }}/financial/discounts/{{ !empty($discount) ? $discount->id.'/update' : 'store' }}" method="Post">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Configuration -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Info Card -->
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3 bg-gray-50/50">
                                <div class="w-8 h-8 rounded-xl bg-gray-900/5 flex items-center justify-center text-gray-900">
                                    <span class="material-symbols-rounded text-lg">edit_note</span>
                                </div>
                                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ trans('admin/main.basic_information') }}</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.title') }}</label>
                                    <div class="relative">
                                        <input type="text" name="title" value="{{ !empty($discount) ? $discount->title : old('title') }}" 
                                               class="w-full px-5 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-gray-300 italic @error('title') is-invalid @enderror"
                                               placeholder="e.g., BLACK FRIDAY SPECIAL">
                                    </div>
                                    @error('title')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.code') }}</label>
                                        <div class="relative group">
                                            <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">qr_code</span>
                                            <input type="text" name="code" value="{{ !empty($discount) ? $discount->code : old('code') }}" 
                                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-mono font-black focus:ring-2 focus:ring-primary/20 transition-all uppercase placeholder:text-gray-300 italic @error('code') is-invalid @enderror"
                                                   placeholder="CODE2024">
                                        </div>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none italic ml-1">{{ trans('admin/main.discount_code_hint') }}</p>
                                        @error('code')
                                            <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.type') }}</label>
                                        <div class="relative group">
                                            <select name="discount_type" class="w-full pl-4 pr-10 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer js-discount-type">
                                                <option value="percentage" {{ (empty($discount) or (!empty($discount) and $discount->discount_type == 'percentage')) ? 'selected' : '' }}>{{ trans('admin/main.percentage') }}</option>
                                                <option value="fixed_amount" {{ (!empty($discount) and $discount->discount_type == 'fixed_amount') ? 'selected' : '' }}>{{ trans('update.fixed_amount') }}</option>
                                            </select>
                                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-hover:text-primary transition-colors">unfold_more</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Percentage Inputs -->
                                    <div class="form-group space-y-2 js-percentage-inputs {{ (!empty($discount) and $discount->discount_type == 'fixed_amount') ? 'hidden' : '' }}">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.discount_percentage') }} (%)</label>
                                        <div class="relative group">
                                            <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">percent</span>
                                            <input type="number" name="percent" value="{{ !empty($discount) ? $discount->percent : old('percent') }}" 
                                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all @error('percent') is-invalid @enderror"
                                                   placeholder="0">
                                        </div>
                                        @error('percent')
                                            <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group space-y-2 js-percentage-inputs {{ (!empty($discount) and $discount->discount_type == 'fixed_amount') ? 'hidden' : '' }}">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.max_amount') }} ({{ $currency }})</label>
                                        <div class="relative group">
                                            <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">arrow_upward</span>
                                            <input type="number" name="max_amount" value="{{ !empty($discount) ? $discount->max_amount : old('max_amount') }}" 
                                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all"
                                                   placeholder="{{ trans('update.discount_max_amount_placeholder') }}">
                                        </div>
                                    </div>

                                    <!-- Fixed Amount Input -->
                                    <div class="form-group space-y-2 js-fixed-amount-inputs {{ (empty($discount) or $discount->discount_type == 'percentage') ? 'hidden' : '' }}">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.amount') }} ({{ $currency }})</label>
                                        <div class="relative group">
                                            <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">payments</span>
                                            <input type="number" name="amount" value="{{ !empty($discount) ? $discount->amount : old('amount') }}" 
                                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all @error('amount') is-invalid @enderror"
                                                   placeholder="{{ trans('update.discount_amount_placeholder') }}">
                                        </div>
                                        @error('amount')
                                            <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Targeting Card -->
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3 bg-gray-50/50">
                                <div class="w-8 h-8 rounded-xl bg-gray-900/5 flex items-center justify-center text-gray-900">
                                    <span class="material-symbols-rounded text-lg">target</span>
                                </div>
                                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Targeting & Constraints</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.source') }}</label>
                                        <div class="relative group">
                                            <select name="source" class="w-full pl-4 pr-10 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer js-discount-source">
                                                @foreach(\App\Models\Discount::$discountSource as $source)
                                                    <option value="{{ $source }}" {{ (!empty($discount) and $discount->source == $source) ? 'selected' : '' }}>{{ trans('update.discount_source_'.$source) }}</option>
                                                @endforeach
                                            </select>
                                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-hover:text-primary transition-colors">unfold_more</span>
                                        </div>
                                    </div>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.users') }} (Optional)</label>
                                        <select name="user_id" class="w-full search-user-select2" data-placeholder="{{ trans('update.discount_users_placeholder') }}">
                                            @if(!empty($userDiscounts) && $userDiscounts->count() > 0)
                                                @foreach($userDiscounts as $userDiscount)
                                                    <option value="{{ $userDiscount->user_id }}" selected>{{ $userDiscount->user->full_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <!-- Dynamic Inputs -->
                                <div class="form-group space-y-2 js-courses-input {{ (empty($discount) or $discount->source != \App\Models\Discount::$discountSourceCourse) ? 'hidden' : '' }}">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.courses') }}</label>
                                    <select name="webinar_ids[]" multiple="multiple" class="form-control search-webinar-select2" data-placeholder="{{ trans('admin/main.search_webinar') }}">
                                        @if(!empty($discount) and !empty($discount->discountCourses))
                                            @foreach($discount->discountCourses as $discountCourse)
                                                @if(!empty($discountCourse->course))
                                                    <option value="{{ $discountCourse->course->id }}" selected>{{ $discountCourse->course->title }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group space-y-2 js-bundles-input {{ (!empty($discount) and $discount->source == \App\Models\Discount::$discountSourceBundle) ? '' : 'hidden' }}">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('update.bundles') }}</label>
                                    <select name="bundle_ids[]" multiple="multiple" class="form-control search-bundle-select2" data-placeholder="{{ trans('update.search_bundle') }}">
                                        @if(!empty($discount) and !empty($discount->discountBundles))
                                            @foreach($discount->discountBundles as $discountBundle)
                                                @if(!empty($discountBundle->bundle))
                                                    <option value="{{ $discountBundle->bundle->id }}" selected>{{ $discountBundle->bundle->title }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group space-y-2 js-categories-input {{ (empty($discount) or $discount->source != \App\Models\Discount::$discountSourceCategory) ? 'hidden' : '' }}">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.categories') }}</label>
                                    <select name="category_ids[]" multiple="multiple" class="form-control search-category-select2" data-placeholder="{{ trans('update.search_categories') }}">
                                        @if(!empty($discount) and !empty($discount->discountCategories))
                                            @foreach($discount->discountCategories as $discountCategory)
                                                @if(!empty($discountCategory->category))
                                                    <option value="{{ $discountCategory->category->id }}" selected>{{ $discountCategory->category->title }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group space-y-2 js-products-input {{ (empty($discount) or $discount->source != \App\Models\Discount::$discountSourceProduct) ? 'hidden' : '' }}">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('update.product_type') }}</label>
                                    <div class="relative group">
                                        <select name="product_type" class="w-full pl-4 pr-10 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                            <option value="all">{{ trans('admin/main.all') }}</option>
                                            <option value="physical" {{ (!empty($discount) and $discount->product_type == 'physical') ? 'selected' : '' }}>{{ trans('update.physical') }}</option>
                                            <option value="virtual" {{ (!empty($discount) and $discount->product_type == 'virtual') ? 'selected' : '' }}>{{ trans('update.virtual') }}</option>
                                        </select>
                                        <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-hover:text-primary transition-colors">unfold_more</span>
                                    </div>
                                </div>

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.groups') }}</label>
                                    <select name="group_ids[]" class="form-control select2 @error('group_ids') is-invalid @enderror" multiple data-placeholder="{{ trans('update.discount_user_group_placeholder') }}">
                                        @if(!empty($userGroups))
                                            @foreach($userGroups as $userGroup)
                                                <option value="{{ $userGroup->id }}" @if(!empty($discountGroupIds) and in_array($userGroup->id, $discountGroupIds)) selected @endif>{{ $userGroup->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('group_ids')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Controls -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3 bg-gray-50/50">
                                <div class="w-8 h-8 rounded-xl bg-gray-900/5 flex items-center justify-center text-gray-900">
                                    <span class="material-symbols-rounded text-lg">calendar_month</span>
                                </div>
                                <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Usage & Expiration</h3>
                            </div>
                            <div class="p-8 space-y-6">
                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.usable_times') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">tag</span>
                                        <input type="number" name="count" value="{{ !empty($discount) ? $discount->count : old('count') }}" 
                                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all @error('count') is-invalid @enderror"
                                               placeholder="e.g., 500">
                                    </div>
                                    @error('count')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('update.minimum_order') }} ({{ $currency }})</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">shopping_cart_checkout</span>
                                        <input type="number" name="minimum_order" value="{{ !empty($discount) ? $discount->minimum_order : old('minimum_order') }}" 
                                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all text-left"
                                               placeholder="0.00">
                                    </div>
                                </div>

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.expiration') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors leading-none">history</span>
                                        <input type="text" name="expired_at" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-black focus:ring-2 focus:ring-primary/20 transition-all datetimepicker @error('expired_at') is-invalid @enderror"
                                               autocomplete="off"
                                               value="{{ !empty($discount) ? dateTimeFormat($discount->expired_at, 'Y-m-d H:i', false) : '' }}">
                                    </div>
                                    @error('expired_at')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-black uppercase tracking-widest">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group pt-2">
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-2xl border border-gray-100 group transition-all hover:bg-white hover:border-amber-500/20 shadow-sm border-dashed">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-400 group-hover:text-amber-500 shadow-sm border border-gray-100 transition-colors">
                                                <span class="material-symbols-rounded text-xl">person_add</span>
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="text-xs font-black text-gray-900 leading-none uppercase tracking-tight cursor-pointer mb-1" for="forFirstPurchaseSwitch">First Purchase Only</label>
                                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest leading-none italic">New Customers Only</p>
                                            </div>
                                        </div>
                                        <div class="relative flex items-center">
                                            <input type="hidden" name="for_first_purchase" value="0">
                                            <input type="checkbox" name="for_first_purchase" id="forFirstPurchaseSwitch" value="1" {{ (!empty($discount) and $discount->for_first_purchase) ? 'checked="checked"' : '' }} class="hidden peer">
                                            <label for="forFirstPurchaseSwitch" class="w-11 h-6 bg-gray-200 peer-checked:bg-amber-500 rounded-full relative cursor-pointer transition-all duration-300 shadow-inner">
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-8 bg-gray-50 border-t border-gray-100 space-y-4 text-left">
                                <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-2 hover:bg-gray-800 transition-all shadow-xl active:scale-95">
                                    <span class="material-symbols-rounded text-xl leading-none">task_alt</span>
                                    {{ trans('admin/main.submit') }}
                                </button>
                                <a href="{{ getAdminPanelUrl() }}/financial/discounts" class="w-full bg-white text-gray-400 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-gray-200 flex items-center justify-center hover:bg-gray-100 transition-all italic">
                                    {{ trans('admin/main.cancel') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/discount.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-select2').select2({
                width: '100%',
                containerCssClass: 'stitch-select2'
            });
        });
    </script>
@endpush
