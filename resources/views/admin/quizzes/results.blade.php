@extends('admin.layouts.app')

@push('styles_top')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#32A128",
                        "accent": "#eab308",
                        "background-light": "#f6f8f5",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"], "body": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
    <style>
        .quiz-results-page { font-family: 'Inter', sans-serif; }
        .quiz-results-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .row:first-child .card:first-child { display: none !important; }
        .badge-premium { border-radius: 0.5rem; font-weight: 700; font-size: 0.7rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; }
    </style>
@endpush

@section('content')
<div class="quiz-results-page bg-background-light text-slate-900 p-4 md:p-8 space-y-6">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20 shrink-0">
                <span class="material-symbols-outlined">analytics</span>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">{{ trans('admin/main.quiz_results') }}</h2>
                <p class="text-[10px] font-bold text-slate-400 border border-slate-200 uppercase tracking-widest mt-1">Detailed Assessment Reports</p>
            </div>
        </div>

        @can('admin_quiz_result_export_excel')
            <a href="{{ getAdminPanelUrl() }}/quizzes/{{ $quiz_id }}/results/excel" 
               class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-2xl px-5 h-12 flex items-center justify-center gap-2 font-bold shadow-sm transition-all scale-hover">
                <span class="material-symbols-outlined text-[20px]">file_download</span>
                <span>{{ trans('admin/main.export_xls') }}</span>
            </a>
        @endcan
    </header>

    <!-- Data Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('admin/main.title') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('quiz.student') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('admin/main.instructor') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.grade') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.quiz_date') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-right">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-last-of-type-tr-td-actions">
                    @foreach($quizzesResults as $result)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800">{{ $result->quiz->title }}</span>
                                <span class="text-[10px] font-black text-primary uppercase tracking-tight mt-0.5 line-clamp-1 truncate max-w-[200px]">({{ $result->quiz->webinar->title }})</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-600">{{ $result->user->full_name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-500 line-clamp-1 truncate max-w-[150px]">{{ $result->quiz->teacher->full_name }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-black text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg ring-1 ring-slate-200">
                                {{ $result->user_grade }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-[11px] font-bold text-slate-500 whitespace-nowrap">{{ dateTimeformat($result->created_at, 'j F Y') }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @switch($result->status)
                                @case(\App\Models\QuizzesResult::$passed)
                                    <span class="badge-premium bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">{{ trans('quiz.passed') }}</span>
                                @break
                                @case(\App\Models\QuizzesResult::$failed)
                                    <span class="badge-premium bg-rose-50 text-rose-600 ring-1 ring-rose-200">{{ trans('quiz.failed') }}</span>
                                @break
                                @case(\App\Models\QuizzesResult::$waiting)
                                    <span class="badge-premium bg-amber-50 text-amber-600 ring-1 ring-amber-200">{{ trans('quiz.waiting') }}</span>
                                @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @can('admin_quizzes_results_delete')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/quizzes/result/'. $result->id.'/delete', 'btnClass' => 'size-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all scale-hover border-none p-0'])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-8 border-t border-slate-100 flex items-center justify-center">
            {{ $quizzesResults->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>
@endsection

