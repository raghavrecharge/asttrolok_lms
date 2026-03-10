@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#32A128",
                        "background-light": "#F7F9FC",
                        "background-dark": "#112210",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "12px",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        .page-container { font-family: 'Inter', sans-serif; }
        .page-container .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child, .section-body > section.card, .section-filters { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Custom Tab Styling */
        .settings-tab.active {
            @apply bg-primary text-white shadow-lg shadow-primary/20;
        }
        .settings-tab:not(.active) {
            @apply text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800;
        }
    </style>
@endpush

@section('content')
<div class="page-container bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 p-4 md:p-8 space-y-8 h-full min-h-screen">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined font-bold">settings</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ $pageTitle }}</h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">Global Installment Configuration</p>
            </div>
        </div>
    </header>

    <!-- Content Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Sidebar Navigation -->
        <aside class="w-full md:w-64 border-b md:border-b-0 md:border-r border-slate-100 dark:border-slate-800 p-6 flex flex-col gap-2">
            <a href="#basic" data-toggle="tab" class="settings-tab active flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                <span class="material-symbols-outlined">tune</span>
                {{ trans('admin/main.basic') }}
            </a>
            <a href="#terms" data-toggle="tab" class="settings-tab flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all">
                <span class="material-symbols-outlined">gavel</span>
                {{ trans('update.terms_&_policies') }}
            </a>
        </aside>

        <!-- Main Form Area -->
        <main class="flex-1 p-6 md:p-10">
            <div class="tab-content" id="settingsTabContent">
                <div class="tab-pane fade show active" id="basic" role="tabpanel">
                    @include('admin.financial.installments.settings.basic_tab')
                </div>
                <div class="tab-pane fade" id="terms" role="tabpanel">
                    @include('admin.financial.installments.settings.terms_tab')
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.settings-tab').on('click', function(e) {
                e.preventDefault();
                $('.settings-tab').removeClass('active');
                $(this).addClass('active');
                $(this).tab('show');
            });
        });
    </script>
@endpush
