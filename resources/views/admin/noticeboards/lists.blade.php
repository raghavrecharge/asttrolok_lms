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
        .noticeboard-page { font-family: 'Inter', sans-serif; }
        .noticeboard-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .card:first-child { display: none !important; }
        .form-control-premium { @apply bg-white border border-slate-200 rounded-2xl px-5 py-3 text-base font-semibold text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:ring-4 focus:ring-primary/10 focus:border-primary w-full shadow-sm; }
        .badge-premium { border-radius: 0.5rem; font-weight: 800; font-size: 0.75rem; padding: 0.4rem 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
@endpush

@section('content')
<div class="noticeboard-page bg-background-light text-slate-900 p-4 md:p-8 space-y-8">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="size-12 rounded-2xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20 shrink-0">
                <span class="material-symbols-outlined text-[28px]">campaign</span>
            </div>
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-800">{{ $pageTitle }}</h1>
                <p class="text-sm font-bold text-slate-400 border border-slate-200 inline-block px-2 uppercase tracking-widest mt-1">Broadcast important announcements</p>
            </div>
        </div>

        @can('admin_noticeboards_send')
            <a href="{{ getAdminPanelUrl() }}/{{ (!empty($isCourseNotice) and $isCourseNotice) ? 'course-noticeboards' : 'noticeboards' }}/send" class="bg-primary hover:bg-primary/90 text-white px-8 py-3.5 rounded-2xl flex items-center justify-center gap-3 font-black transition-all shadow-xl shadow-primary/20 transform hover:-translate-y-1">
                <span class="material-symbols-outlined text-[24px]">send</span>
                <span>{{ trans('admin/main.send_noticeboard') }}</span>
            </a>
        @endcan
    </header>

    <!-- Filters -->
    <section class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden p-6 md:p-10">
        <form method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('admin/main.search') }}</label>
                <input type="text" name="search" value="{{ request()->get('search') }}" class="form-control-premium" placeholder="Search notices...">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('admin/main.start_date') }}</label>
                <input type="date" name="from" value="{{ request()->get('from') }}" class="form-control-premium text-center">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('admin/main.end_date') }}</label>
                <input type="date" name="to" value="{{ request()->get('to') }}" class="form-control-premium text-center">
            </div>

            @if(!empty($isCourseNotice) and $isCourseNotice)
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('admin/main.sender') }}</label>
                <select name="sender_id" class="form-control-premium search-user-select2 appearance-none">
                    @if(!empty($sender))
                        <option value="{{ $sender->id }}" selected>{{ $sender->full_name }}</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('update.color') }}</label>
                <select name="color" class="form-control-premium appearance-none">
                    <option value="">{{ trans('admin/main.all') }}</option>
                    @foreach(\App\Models\CourseNoticeboard::$colors as $color)
                        <option value="{{ $color }}" @if(request()->get('color') == $color) selected @endif>{{ trans('update.course_noticeboard_color_'.$color) }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('admin/main.sender') }}</label>
                <select name="sender" class="form-control-premium appearance-none">
                    <option value="">Select Sender</option>
                    <option value="admin" @if(request()->get('sender') == 'admin') selected @endif>{{ trans('admin/main.admin_role') }}</option>
                    <option value="organizations" @if(request()->get('sender') == 'organizations') selected @endif>{{ trans('admin/main.organizations') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 px-1">{{ trans('admin/main.types') }}</label>
                <select name="type" class="form-control-premium appearance-none">
                    <option value="">{{ trans('admin/main.all_types') }}</option>
                    @foreach(\App\Models\Noticeboard::$adminTypes as $type)
                        <option value="{{ $type }}" @if(request()->get('type') == $type) selected @endif>{{ trans('public.'.$type) }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="md:col-span-1 lg:col-span-1 xl:col-span-1 md:flex md:items-end">
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white w-full h-14 rounded-2xl flex items-center justify-center gap-3 font-black transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[24px]">search</span>
                    <span>{{ trans('admin/main.show_results') }}</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Content Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest">{{ trans('admin/main.title') }}</th>
                        @if(!empty($isCourseNotice) and $isCourseNotice)
                            <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest">{{ trans('admin/main.course') }}</th>
                        @endif
                        <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest text-center">{{ trans('notification.sender') }}</th>
                        <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest text-center">{{ trans('site.message') }}</th>
                        <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest text-center">
                            @if(!empty($isCourseNotice) and $isCourseNotice)
                                {{ trans('update.color') }}
                            @else
                                {{ trans('admin/main.type') }}
                            @endif
                        </th>
                        <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest text-center">{{ trans('admin/main.created_at') }}</th>
                        <th class="px-8 py-6 text-xs font-black uppercase text-slate-400 tracking-widest text-right">{{ trans('admin/main.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-last-of-type-tr-td-actions">
                    @foreach($noticeboards as $noticeboard)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-5">
                            <span class="text-base font-black text-slate-800 leading-tight group-hover:text-primary transition-colors">{{ $noticeboard->title }}</span>
                        </td>
                        
                        @if(!empty($isCourseNotice) and !empty($noticeboard->webinar))
                            <td class="px-8 py-5">
                                <a href="{{ getAdminPanelUrl() }}/webinars/{{ $noticeboard->webinar->id }}/edit" target="_blank" class="text-sm font-bold text-primary hover:underline">
                                    #{{ $noticeboard->webinar->id }}-{{ truncate($noticeboard->webinar->title, 24) }}
                                </a>
                            </td>
                        @endif

                        <td class="px-8 py-5 text-center">
                            <span class="text-sm font-black text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl">
                                @if(!empty($isCourseNotice))
                                    {{ $noticeboard->creator ? $noticeboard->creator->full_name : '-' }}
                                @else
                                    {{ $noticeboard->sender }}
                                @endif
                            </span>
                        </td>

                        <td class="px-8 py-5 text-center">
                            <button type="button" data-item-id="{{ $noticeboard->id }}" class="js-show-description bg-white border border-slate-200 hover:border-primary hover:text-primary text-slate-600 px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-2 mx-auto shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                {{ trans('admin/main.show') }}
                            </button>
                            <input type="hidden" value="{{ nl2br($noticeboard->message) }}">
                        </td>

                        <td class="px-8 py-5 text-center">
                            @php
                                $typeLabels = [
                                    'all' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                    'students' => 'bg-blue-50 text-blue-600 border border-blue-100',
                                    'instructors' => 'bg-amber-50 text-amber-600 border border-amber-100',
                                    'organizations' => 'bg-purple-50 text-purple-600 border border-purple-100',
                                ];
                                $currentType = $noticeboard->type;
                                $labelClass = $typeLabels[$currentType] ?? 'bg-slate-50 text-slate-600 border border-slate-100';
                            @endphp
                            <span class="badge-premium {{ $labelClass }}">
                                @if(!empty($isCourseNotice) and $isCourseNotice)
                                    {{ trans('update.course_noticeboard_color_'.$noticeboard->color) }}
                                @else
                                    {{ trans('admin/main.notification_'.$noticeboard->type) }}
                                @endif
                            </span>
                        </td>

                        <td class="px-8 py-5 text-center">
                            <span class="text-sm font-bold text-slate-500">{{ dateTimeFormat($noticeboard->created_at,'j M Y | H:i') }}</span>
                        </td>

                        <td class="px-8 py-5 text-right">
                            <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('admin_noticeboards_edit')
                                    <a href="{{ getAdminPanelUrl() }}/{{ (!empty($isCourseNotice) and $isCourseNotice) ? 'course-noticeboards' : 'noticeboards' }}/{{ $noticeboard->id }}/edit" 
                                       class="size-10 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-slate-200 transition-all transform hover:scale-110" 
                                       title="{{ trans('admin/main.edit') }}">
                                        <span class="material-symbols-outlined text-[22px]">edit</span>
                                    </a>
                                @endcan

                                @can('admin_notifications_delete')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/'. ((!empty($isCourseNotice) and $isCourseNotice) ? "course-noticeboards" : "noticeboards" .'/'. $noticeboard->id).'/delete', 'btnClass' => 'size-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all transform hover:scale-110 border-none p-0'])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-10 border-t border-slate-100 flex items-center justify-center bg-slate-50/50">
            {{ $noticeboards->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>

<!-- Discussion Modal -->
<div class="modal fade" id="notificationMessageModal" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-[3rem] border-none shadow-2xl overflow-hidden p-10">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-[28px]">description</span>
                    </div>
                    <h5 class="text-2xl font-black text-slate-800">{{ trans('admin/main.message') }}</h5>
                </div>
                <button type="button" class="size-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-all border-none" data-dismiss="modal">
                    <span class="material-symbols-outlined text-[24px]">close</span>
                </button>
            </div>
            
            <div class="modal-body p-0 rounded-3xl bg-slate-50 border border-slate-100 p-10 text-base font-medium text-slate-700 leading-relaxed mb-8 shadow-inner italic">
                <!-- Content injected via JS -->
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" class="bg-white border-2 border-slate-200 text-slate-600 px-10 h-14 rounded-2xl font-black transition-all hover:bg-slate-50 hover:border-slate-300" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/noticeboards.min.js"></script>
@endpush

