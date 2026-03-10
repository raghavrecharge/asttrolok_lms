@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ trans('admin/main.special_offers') }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ !empty($specialOffer) ? 'Edit existing special offer details.' : 'Create a new time-limited discount for products.' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/financial/special_offers" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-medium hover:bg-gray-50 transition-all shadow-sm">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl mx-auto">
                <div class="p-8 border-b border-gray-50 bg-gray-50/30">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-rounded text-2xl">{{ !empty($specialOffer) ? 'edit_square' : 'add_circle' }}</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-black text-gray-900">{{ !empty($specialOffer) ? trans('admin/main.edit') : trans('admin/main.new') }} {{ trans('admin/main.special_offer') }}</h2>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Configuration Details</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form action="{{ getAdminPanelUrl() }}/financial/special_offers/{{ !empty($specialOffer) ? $specialOffer->id.'/update' : 'store' }}" method="Post">
                        {{ csrf_field() }}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <!-- Name -->
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.name') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">label</span>
                                        <input type="text" name="name" value="{{ !empty($specialOffer) ? $specialOffer->name : old('name') }}" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium @error('name') ring-2 ring-rose-500/20 @enderror" placeholder="{{ trans('admin/main.name_placeholder') }}">
                                    </div>
                                    @error('name')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Type -->
                                @php
                                    $types = [
                                        'courses' => 'webinar_id',
                                        'bundles' => 'bundle_id',
                                        'subscription_packages' => 'subscribe_id',
                                        'registration_packages' => 'registration_package_id',
                                    ];
                                @endphp
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.type') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">category</span>
                                        <select name="type" class="js-offer-type w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium outline-none appearance-none @error('type') ring-2 ring-rose-500/20 @enderror">
                                            @foreach($types as $type => $typeItem)
                                                <option value="{{ $type }}" {{ (!empty($specialOffer) and !empty($specialOffer->{$typeItem})) ? 'selected' : '' }}>{{ trans('update.'.$type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('type')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Conditional Fields -->
                                <div class="js-course-field space-y-2 {{ (empty($specialOffer) or !empty($specialOffer->webinar_id)) ? '' : 'hidden' }}">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.class') }}</label>
                                    <select name="webinar_id" class="form-control search-webinar-select2 @error('webinar_id') is-invalid @enderror" data-placeholder="{{ trans('update.search_and_select_class') }}">
                                        @if(!empty($specialOffer) and !empty($specialOffer->webinar))
                                            <option value="{{ $specialOffer->webinar->id }}" selected>{{ $specialOffer->webinar->title }}</option>
                                        @endif
                                    </select>
                                    @error('webinar_id')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="js-bundle-field space-y-2 {{ (!empty($specialOffer) and !empty($specialOffer->bundle_id)) ? '' : 'hidden' }}">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.bundle') }}</label>
                                    <select name="bundle_id" class="form-control search-bundle-select2 @error('bundle_id') is-invalid @enderror" data-placeholder="{{ trans('update.search_and_select_bundle') }}">
                                        @if(!empty($specialOffer) and !empty($specialOffer->bundle))
                                            <option value="{{ $specialOffer->bundle->id }}" selected>{{ $specialOffer->bundle->title }}</option>
                                        @endif
                                    </select>
                                    @error('bundle_id')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="js-subscribe-field space-y-2 {{ (!empty($specialOffer) and !empty($specialOffer->subscribe_id)) ? '' : 'hidden' }}">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('public.subscribe') }}</label>
                                    <select name="subscribe_id" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium outline-none appearance-none @error('subscribe_id') ring-2 ring-rose-500/20 @enderror">
                                        <option value="">{{ trans('update.select_subscribe') }}</option>
                                        @foreach($subscribes as $subscribe)
                                            <option value="{{ $subscribe->id }}" {{ (!empty($specialOffer) and $specialOffer->subscribe_id == $subscribe->id) ? 'selected' : '' }}>{{ $subscribe->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('subscribe_id')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="js-registration_package-field space-y-2 {{ (!empty($specialOffer) and !empty($specialOffer->registration_package_id)) ? '' : 'hidden' }}">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('update.registration_package') }}</label>
                                    <select name="registration_package_id" class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium outline-none appearance-none @error('registration_package_id') ring-2 ring-rose-500/20 @enderror">
                                        <option value="">{{ trans('update.select_registration_package') }}</option>
                                        @foreach($registrationPackages as $registration_package)
                                            <option value="{{ $registration_package->id }}" {{ (!empty($specialOffer) and $specialOffer->registration_package_id == $registration_package->id) ? 'selected' : '' }}>{{ $registration_package->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('registration_package_id')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Discount Percentage -->
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.discount_percentage') }} (%)</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">percent</span>
                                        <input type="number" name="percent" value="{{ !empty($specialOffer) ? $specialOffer->percent : old('percent') }}" min="0" max="100" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-bold @error('percent') ring-2 ring-rose-500/20 @enderror">
                                    </div>
                                    @error('percent')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Start Date -->
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.from_date') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">calendar_today</span>
                                        <input type="text" name="from_date" value="{{ !empty($specialOffer) ? dateTimeFormat($specialOffer->from_date,'Y-m-d H:i',false) : old('from_date') }}" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium datetimepicker @error('from_date') ring-2 ring-rose-500/20 @enderror" autocomplete="off">
                                    </div>
                                    @error('from_date')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- End Date -->
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.to_date') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">event_upcoming</span>
                                        <input type="text" name="to_date" value="{{ !empty($specialOffer) ? dateTimeFormat($specialOffer->to_date,'Y-m-d H:i',false) : old('to_date') }}" class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary/20 transition-all font-medium datetimepicker @error('to_date') ring-2 ring-rose-500/20 @enderror" autocomplete="off">
                                    </div>
                                    @error('to_date')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <label class="text-sm font-bold text-gray-700 ml-1">{{ trans('admin/main.status') }}</label>
                                    <div class="relative group flex p-1 bg-gray-50 rounded-2xl">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="status" value="active" class="sr-only peer" {{ (!empty($specialOffer) and $specialOffer->status == \App\Models\SpecialOffer::$active) || empty($specialOffer) ? 'checked' : '' }}>
                                            <div class="py-2.5 text-center rounded-xl text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-emerald-600 peer-checked:shadow-sm text-gray-400">
                                                {{ trans('panel.active') }}
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="status" value="inactive" class="sr-only peer" {{ !empty($specialOffer) and $specialOffer->status == \App\Models\SpecialOffer::$inactive ? 'checked' : '' }}>
                                            <div class="py-2.5 text-center rounded-xl text-xs font-bold transition-all peer-checked:bg-white peer-checked:text-rose-600 peer-checked:shadow-sm text-gray-400">
                                                {{ trans('panel.inactive') }}
                                            </div>
                                        </label>
                                    </div>
                                    @error('status')
                                        <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest ml-1 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 flex justify-end gap-3 border-t border-gray-50 pt-8">
                            <button type="submit" class="px-10 py-3 bg-primary text-white rounded-2xl font-black shadow-lg shadow-primary/20 hover:scale-[1.02] transition-all active:scale-95">
                                {{ trans('admin/main.submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/special_offers.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            background-color: #f9fafb !important;
            border: none !important;
            border-radius: 1rem !important;
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 1rem !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            color: #111827 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 48px !important;
            right: 12px !important;
        }
    </style>
@endsection
