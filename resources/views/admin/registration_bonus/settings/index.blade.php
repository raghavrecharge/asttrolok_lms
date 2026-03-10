@extends('admin.layouts.app')

@push('styles_top')
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Registration Bonus / Settings</p>
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50">
                    <ul class="flex flex-wrap gap-2 p-1.5 bg-gray-50 rounded-2xl w-fit" id="myTab3" role="tablist">
                        <li class="nav-item">
                            <a class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all active show" id="basic-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="basic" aria-selected="true">
                                <span class="material-symbols-rounded text-xl leading-none">settings</span>
                                {{ trans('admin/main.basic') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all text-gray-400 hover:text-gray-600" id="terms-tab" data-toggle="tab" href="#terms" role="tab" aria-controls="terms" aria-selected="false">
                                <span class="material-symbols-rounded text-xl leading-none">description</span>
                                {{ trans('update.terms') }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="p-6">
                    <div class="tab-content" id="myTabContent2">
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            @include('admin.registration_bonus.settings.basic_tab')
                        </div>
                        <div class="tab-pane fade" id="terms" role="tabpanel" aria-labelledby="terms-tab">
                            @include('admin.registration_bonus.settings.terms_tab')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/registration_bonus_settings.min.js"></script>
    <style>
        .nav-item a.active {
            background: white !important;
            color: var(--primary) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
        }
    </style>
@endpush
