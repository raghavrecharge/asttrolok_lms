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
        .quiz-page { font-family: 'Inter', sans-serif; }
        .quiz-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > section.card, .section-body > .row:first-child .card:first-child { display: none !important; }
        .kpi-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .kpi-card:hover { transform: translateY(-5px); }
        .badge-premium { border-radius: 0.5rem; font-weight: 700; font-size: 0.7rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; }
        
        /* Custom form overrides */
        .form-control-premium { @apply bg-white border border-slate-200 rounded-2xl px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:ring-4 focus:ring-primary/10 focus:border-primary w-full shadow-sm; }
    </style>
@endpush

@section('content')
<div class="quiz-page bg-background-light text-slate-900 p-4 md:p-8 space-y-8">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20 shrink-0">
                <span class="material-symbols-outlined">quiz</span>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">{{ trans('admin/main.quizzes') }}</h2>
                <p class="text-[10px] font-bold text-slate-400 border border-slate-200 uppercase tracking-widest mt-1">Assessment & Grading Control</p>
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            @can('admin_quizzes_lists_excel')
                <a href="{{ getAdminPanelUrl() }}/quizzes/excel?{{ http_build_query(request()->all()) }}" 
                   class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 rounded-2xl px-5 h-12 flex items-center justify-center gap-2 font-bold shadow-sm transition-all scale-hover">
                    <span class="material-symbols-outlined text-[20px]">file_download</span>
                    <span>{{ trans('admin/main.export_xls') }}</span>
                </a>
            @endcan

            @can('admin_quizzes_create')
                <a href="{{ getAdminPanelUrl() }}/quizzes/create" 
                   class="bg-primary hover:bg-emerald-700 text-white rounded-2xl px-5 h-12 flex items-center justify-center gap-2 font-bold shadow-lg shadow-primary/20 scale-hover transition-all">
                    <span class="material-symbols-outlined text-[20px]">post_add</span>
                    <span>{{ trans('quiz.new_quiz') }}</span>
                </a>
            @endcan
        </div>
    </header>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="kpi-card bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="size-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                <span class="material-symbols-outlined text-[28px]">article</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.total_quizzes') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalQuizzes }}</h3>
            </div>
        </div>

        <div class="kpi-card bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="size-14 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                <span class="material-symbols-outlined text-[28px]">verified</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.active_quizzes') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalActiveQuizzes }}</h3>
            </div>
        </div>

        <div class="kpi-card bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="size-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <span class="material-symbols-outlined text-[28px]">group</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.total_students') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalStudents }}</h3>
            </div>
        </div>

        <div class="kpi-card bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5">
            <div class="size-14 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                <span class="material-symbols-outlined text-[28px]">rule</span>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('admin/main.total_passed_students') }}</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $totalPassedStudents }}</h3>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-[2.5rem] p-6- md:p-8 border border-slate-200 shadow-sm p-8">
        <form action="{{ getAdminPanelUrl() }}/quizzes" method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.search') }}</label>
                <input type="text" name="title" value="{{ request()->get('title') }}" class="form-control-premium" placeholder="Search Quiz Title...">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.start_date') }}</label>
                <input type="date" name="from" value="{{ request()->get('from') }}" class="form-control-premium">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.end_date') }}</label>
                <input type="date" name="to" value="{{ request()->get('to') }}" class="form-control-premium">
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.filters') }}</label>
                <select name="sort" class="form-control-premium cursor-pointer">
                    <option value="">{{ trans('admin/main.filter_type') }}</option>
                    <option value="have_certificate" @if(request()->get('sort') == 'have_certificate') selected @endif>{{ trans('admin/main.quizzes_have_certificate') }}</option>
                    <option value="students_count_asc" @if(request()->get('sort') == 'students_count_asc') selected @endif>{{ trans('admin/main.students_ascending') }}</option>
                    <option value="students_count_desc" @if(request()->get('sort') == 'students_count_desc') selected @endif>{{ trans('admin/main.students_descending') }}</option>
                    <option value="passed_count_asc" @if(request()->get('sort') == 'passed_count_asc') selected @endif>{{ trans('admin/main.passed_students_ascending') }}</option>
                    <option value="passed_count_desc" @if(request()->get('sort') == 'passed_count_desc') selected @endif>{{ trans('admin/main.passes_students_descending') }}</option>
                    <option value="grade_avg_asc" @if(request()->get('sort') == 'grade_avg_asc') selected @endif>{{ trans('admin/main.grades_average_ascending') }}</option>
                    <option value="grade_avg_desc" @if(request()->get('sort') == 'grade_avg_desc') selected @endif>{{ trans('admin/main.grades_average_descending') }}</option>
                    <option value="created_at_asc" @if(request()->get('sort') == 'created_at_asc') selected @endif>{{ trans('admin/main.create_date_ascending') }}</option>
                    <option value="created_at_desc" @if(request()->get('sort') == 'created_at_desc') selected @endif>{{ trans('admin/main.create_date_descending') }}</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.instructor') }}</label>
                <select name="teacher_ids[]" multiple="multiple" class="form-control search-user-select2" data-placeholder="Search teachers">
                    @if(!empty($teachers))
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" selected>{{ $teacher->full_name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.class') }}</label>
                <select name="webinar_ids[]" multiple="multiple" class="form-control search-webinar-select2" data-placeholder="Search classes">
                    @if(!empty($webinars))
                        @foreach($webinars as $webinar)
                            <option value="{{ $webinar->id }}" selected>{{ $webinar->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('admin/main.status') }}</label>
                <select name="status" class="form-control-premium cursor-pointer">
                    <option value="">{{ trans('admin/main.all_status') }}</option>
                    <option value="active" @if(request()->get('status') == 'active') selected @endif>{{ trans('admin/main.active') }}</option>
                    <option value="inactive" @if(request()->get('status') == 'inactive') selected @endif>{{ trans('admin/main.inactive') }}</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-primary hover:bg-emerald-700 text-white rounded-2xl h-12 w-full font-bold shadow-lg shadow-primary/20 transition-all scale-hover">
                    {{ trans('admin/main.show_results') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Container -->
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('admin/main.title') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-left">{{ trans('admin/main.instructor') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.question_count') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.students_count') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.average_grade') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.certificate') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-right">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($quizzes as $quiz)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800">{{ $quiz->title }}</span>
                                @if(!empty($quiz->webinar))
                                    <span class="text-[10px] font-black text-primary uppercase tracking-tight mt-0.5 line-clamp-1 truncate max-w-[200px]">{{ $quiz->webinar->title }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-slate-600">{{ $quiz->teacher->full_name }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-xs font-bold text-slate-800">{{ $quiz->quizQuestions->count() }}</span>
                                @if(($quiz->display_limited_questions and !empty($quiz->display_number_of_questions)))
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-tight">{{ trans('public.active') }}: {{ $quiz->display_number_of_questions }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-xs font-bold text-slate-800">{{ $quiz->quizResults->pluck('user_id')->count() }}</span>
                                <span class="text-[9px] font-black text-primary uppercase tracking-tight">{{ trans('admin/main.passed') }}: {{ $quiz->quizResults->where('status','passed')->count() }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-xs font-black text-slate-800 bg-slate-100 px-2 py-1 rounded-lg">
                                {{ round($quiz->quizResults->avg('user_grade'),2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-center items-center">
                            <div class="flex justify-center">
                                @if($quiz->certificate)
                                    <span class="size-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[16px]">verified</span>
                                    </span>
                                @else
                                    <span class="size-6 rounded-lg bg-slate-50 text-slate-300 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[16px]">close</span>
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($quiz->status === \App\Models\Quiz::ACTIVE)
                                <span class="badge-premium bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">{{ trans('admin/main.active') }}</span>
                            @else
                                <span class="badge-premium bg-amber-50 text-amber-600 ring-1 ring-amber-200">{{ trans('admin/main.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @can('admin_quizzes_results')
                                    <a href="{{ getAdminPanelUrl() }}/quizzes/{{ $quiz->id }}/results" 
                                       class="size-9 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all scale-hover" title="{{ trans('admin/main.quiz_results') }}">
                                        <span class="material-symbols-outlined text-[18px]">analytics</span>
                                    </a>
                                @endcan

                                @can('admin_quizzes_edit')
                                    <a href="{{ getAdminPanelUrl() }}/quizzes/{{ $quiz->id }}/edit" 
                                       class="size-9 rounded-xl bg-primary/5 text-primary flex items-center justify-center hover:bg-primary hover:text-white transition-all scale-hover" title="{{ trans('admin/main.edit') }}">
                                        <span class="material-symbols-outlined text-[18px]">edit_note</span>
                                    </a>
                                @endcan

                                @can('admin_quizzes_edit')
                                    <a href="{{ getAdminPanelUrl() }}/quizzes/{{ $quiz->id }}/copy" 
                                       class="size-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all scale-hover" title="Copy">
                                        <span class="material-symbols-outlined text-[18px]">content_copy</span>
                                    </a>
                                @endcan

                                @can('admin_quizzes_delete')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/quizzes/'.$quiz->id.'/delete' , 'btnClass' => 'size-9 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all scale-hover border-none p-0'])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-8 border-t border-slate-100 flex items-center justify-center">
            {{ $quizzes->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>
@endsection


@push('scripts_bottom')

@endpush
