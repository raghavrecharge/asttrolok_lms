@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Cashback / {{ !empty($rule) ? 'Edit' : 'New' }} Rule</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl('/cashback/rules') }}" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm">
                    <span class="material-symbols-rounded text-xl text-gray-400">arrow_back</span>
                    {{ trans('admin/main.back') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <form method="post" action="{{ getAdminPanelUrl('/cashback/rules/'. (!empty($rule) ? $rule->id.'/update' : 'store')) }}">
                        {{ csrf_field() }}

                        <div class="space-y-12">
                            {{-- Basic Information --}}
                            <div class="relative">
                                @include('admin.cashback.rules.create.includes.basic_information')
                            </div>

                            <div class="h-px bg-gray-50 w-full"></div>

                            {{-- Target Products --}}
                            <div class="relative">
                                @include('admin.cashback.rules.create.includes.target_products')
                            </div>

                            <div class="h-px bg-gray-50 w-full"></div>

                            {{-- Payment --}}
                            <div class="relative">
                                @include('admin.cashback.rules.create.includes.payment')
                            </div>

                            <div class="h-px bg-gray-50 w-full"></div>

                            {{-- Footer Actions --}}
                            <div class="flex flex-col md:flex-row justify-between items-center gap-6 pt-4">
                                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 w-full md:w-auto">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-primary shadow-sm border border-gray-100">
                                            <span class="material-symbols-rounded text-xl">power_settings_new</span>
                                        </div>
                                        <div>
                                            <label class="text-sm font-bold text-gray-700 cursor-pointer mb-0" for="statusSwitch">{{ trans('admin/main.active') }}</label>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider leading-tight">Toggle Rule Status</p>
                                        </div>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer ml-4">
                                        <input type="checkbox" name="enable" id="statusSwitch" class="sr-only peer" {{ (!empty($rule) && $rule->enable) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                                    <button type="submit" class="flex items-center justify-center gap-2 px-8 py-3 bg-gray-900 text-white rounded-2xl font-bold hover:bg-gray-800 transition-all shadow-md active:scale-95">
                                        <span class="material-symbols-rounded text-xl">save</span>
                                        {{ trans('admin/main.save_change') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/default/js/admin/cashback_create_rule.min.js"></script>
@endpush
