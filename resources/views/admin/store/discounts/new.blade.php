@extends('admin.layouts.app')

@push('styles_top')
    <style>
        .select2-container--default .select2-selection--multiple,
        .select2-container--default .select2-selection--single {
            border: none !important;
            background-color: #f9fafb !important;
            border-radius: 0.75rem !important;
            padding: 0.25rem 0.5rem !important;
            min-height: 42px !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--focus .select2-selection--single {
            box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2) !important;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ !empty($discount) ? trans('admin/main.edit') : trans('update.new_discount') }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Store / Discount Configuration</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/store/discounts" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-medium hover:bg-gray-50 transition-all shadow-sm text-sm">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <form action="{{ getAdminPanelUrl() }}/store/discounts/{{ !empty($discount) ? $discount->id.'/update' : 'store' }}" method="Post">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Configuration -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                    <span class="material-symbols-rounded text-lg">edit_note</span>
                                </div>
                                <h3 class="text-base font-bold text-gray-900">Discount Details</h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('admin/main.title') }}</label>
                                    <input type="text" name="name" value="{{ !empty($discount) ? $discount->name : old('name') }}" 
                                           class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('name') is-invalid @enderror"
                                           placeholder="{{ trans('admin/main.name_placeholder') }}">
                                    @error('name')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('update.product') }}</label>
                                    <select name="product_id" class="w-full search-product-select2 @error('product_id') is-invalid @enderror" data-placeholder="Search and Select Product">
                                        @if(!empty($discount) and !empty($discount->product))
                                            <option value="{{ $discount->product->id }}" selected>{{ $discount->product->title }}</option>
                                        @endif
                                    </select>
                                    @error('product_id')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('admin/main.discount_percentage') }}</label>
                                        <div class="relative group">
                                            <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">percent</span>
                                            <input type="number" name="percent" value="{{ !empty($discount) ? $discount->percent : old('percent') }}" 
                                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('percent') is-invalid @enderror"
                                                   maxlength="3" min="0" max="100">
                                        </div>
                                        @error('percent')
                                            <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('admin/main.usable_times') }}</label>
                                        <div class="relative group">
                                            <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">count</span>
                                            <input type="number" name="count" value="{{ !empty($discount) ? $discount->count : old('count') }}" 
                                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all @error('count') is-invalid @enderror"
                                                   placeholder="Unlimited if empty">
                                        </div>
                                        @error('count')
                                            <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Side Options -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                            <div class="p-6 border-b border-gray-50 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                                    <span class="material-symbols-rounded text-lg">schedule</span>
                                </div>
                                <h3 class="text-base font-bold text-gray-900">Duration & Status</h3>
                            </div>
                            <div class="p-6 space-y-5">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('admin/main.start_date') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">calendar_month</span>
                                        <input type="text" name="start_date" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all datetimepicker @error('start_date') is-invalid @enderror"
                                               autocomplete="off"
                                               value="{{ !empty($discount) ? dateTimeFormat($discount->start_date,'Y-m-d H:i',false) : old('start_date') }}">
                                    </div>
                                    @error('start_date')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('admin/main.end_date') }}</label>
                                    <div class="relative group">
                                        <span class="material-symbols-rounded absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-primary transition-colors">event_busy</span>
                                        <input type="text" name="end_date" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all datetimepicker @error('end_date') is-invalid @enderror"
                                               autocomplete="off"
                                               value="{{ !empty($discount) ? dateTimeFormat($discount->end_date,'Y-m-d H:i',false) : old('end_date') }}">
                                    </div>
                                    @error('end_date')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase ml-1 tracking-wider">{{ trans('admin/main.status') }}</label>
                                    <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all outline-none @error('status') is-invalid @enderror">
                                        <option value="active" {{ !empty($discount) and $discount->status == 'active' ? 'selected' : '' }}>{{ trans('panel.active') }}</option>
                                        <option value="inactive" {{ !empty($discount) and $discount->status == 'inactive' ? 'selected' : '' }}>{{ trans('panel.inactive') }}</option>
                                    </select>
                                    @error('status')
                                        <div class="text-rose-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="p-6 bg-gray-50 border-t border-gray-100 flex flex-col gap-3">
                                <button type="submit" class="w-full bg-primary text-white py-3 rounded-2xl font-bold flex items-center justify-center gap-2 hover:bg-opacity-90 transition-all shadow-sm">
                                    <span class="material-symbols-rounded">save</span>
                                    {{ trans('admin/main.submit') }}
                                </button>
                                <a href="{{ getAdminPanelUrl() }}/store/discounts" class="w-full bg-white text-gray-600 py-3 rounded-2xl font-bold border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-all text-decoration-none">
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

