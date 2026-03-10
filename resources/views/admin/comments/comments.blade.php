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
        .comments-page { font-family: 'Inter', sans-serif; }
        .comments-page .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .section-header, .section-body > .row:first-child .card:first-child { display: none !important; }
        .kpi-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .kpi-card:hover { transform: translateY(-5px); }
        .form-control-premium { @apply bg-white border border-slate-200 rounded-2xl px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none transition-all placeholder:text-slate-400 focus:ring-4 focus:ring-primary/10 focus:border-primary w-full shadow-sm; }
        .badge-premium { border-radius: 0.5rem; font-weight: 700; font-size: 0.7rem; padding: 0.35rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; }
    </style>
@endpush

@section('content')
<div class="comments-page bg-background-light text-slate-900 p-4 md:p-8 space-y-6">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="size-10 rounded-xl bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/20 shrink-0">
                <span class="material-symbols-outlined">forum</span>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-800">{{ $pageTitle }}</h1>
                <p class="text-[10px] font-bold text-slate-400 border border-slate-200 uppercase tracking-widest mt-1">Manage User Feedback & Discussions</p>
            </div>
        </div>
    </header>

    <!-- KPI Section -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kpi-card bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[28px]">comment</span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.total_comments') }}</p>
                    <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ $totalComments }}</h3>
                </div>
            </div>
        </div>
        <div class="kpi-card bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm text-emerald-600">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-emerald-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[28px]">visibility</span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.published_comments') }}</p>
                    <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ $publishedComments }}</h3>
                </div>
            </div>
        </div>
        <div class="kpi-card bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm text-amber-600">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-amber-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[28px]">hourglass_empty</span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.pending_comments') }}</p>
                    <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ $pendingComments }}</h3>
                </div>
            </div>
        </div>
        <div class="kpi-card bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm text-rose-600">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-rose-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[28px]">report</span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.comments_reports') }}</p>
                    <h3 class="text-xl font-black text-slate-800 mt-0.5">{{ $commentReports }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <section class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden p-6 md:p-8">
        <form method="get" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('admin/main.search') }}</label>
                <input type="text" name="title" value="{{ request()->get('title') }}" class="form-control-premium" placeholder="Search comments...">
            </div>
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('admin/main.date') }}</label>
                <input type="date" name="date" value="{{ request()->get('date') }}" class="form-control-premium">
            </div>
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('admin/main.status') }}</label>
                <select name="status" class="form-control-premium appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%2364748b%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22m6 8 4 4 4-4%22%2F%3E%3C%2Fsvg%3E');">
                    <option value="">{{ trans('admin/main.all_status') }}</option>
                    <option value="pending" @if(request()->get('status') == 'pending') selected @endif>{{ trans('admin/main.pending') }}</option>
                    <option value="active" @if(request()->get('status') == 'active') selected @endif>{{ trans('admin/main.published') }}</option>
                </select>
            </div>

            @if($page == 'webinars')
            <div class="lg:col-span-2 xl:col-span-1">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('admin/main.class') }}</label>
                <select name="webinar_ids[]" multiple="multiple" class="form-control-premium search-webinar-select2">
                    @if(!empty($webinars) and $webinars->count() > 0)
                        @foreach($webinars as $webinar)
                            <option value="{{ $webinar->id }}" selected>{{ $webinar->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            @elseif($page == 'bundles')
            <div class="lg:col-span-2 xl:col-span-1">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('update.bundle') }}</label>
                <select name="bundle_ids[]" multiple="multiple" class="form-control-premium search-bundle-select2">
                    @if(!empty($bundles) and $bundles->count() > 0)
                        @foreach($bundles as $bundle)
                            <option value="{{ $bundle->id }}" selected>{{ $bundle->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            @elseif($page == 'blog')
            <div class="lg:col-span-2 xl:col-span-1">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('admin/main.blog') }}</label>
                <select name="post_ids[]" multiple="multiple" class="form-control-premium search-blog-select2">
                    @if(!empty($blog) and $blog->count() > 0)
                        @foreach($blog as $post)
                            <option value="{{ $post->id }}" selected>{{ $post->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            @elseif($page == 'products')
            <div class="lg:col-span-2 xl:col-span-1">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('update.products') }}</label>
                <select name="product_ids[]" multiple="multiple" class="form-control-premium search-product-select2">
                    @if(!empty($products) and $products->count() > 0)
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" selected>{{ $product->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            @endif

            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1">{{ trans('admin/main.user') }}</label>
                <select name="user_ids[]" multiple="multiple" class="form-control-premium search-user-select2">
                    @if(!empty($users) and $users->count() > 0)
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" selected>{{ $user->full_name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="lg:col-span-1 md:flex md:items-end">
                <button type="submit" class="bg-primary hover:bg-primary/90 text-white w-full h-11 rounded-2xl flex items-center justify-center gap-2 font-black transition-all shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                    <span>{{ trans('admin/main.show_results') }}</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.comment') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.created_date') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">{{ trans('admin/main.user') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider">
                            @if($page == 'webinars')
                                {{ trans('admin/main.class') }}
                            @elseif($page == 'bundles')
                                {{ trans('update.bundle') }}
                            @elseif($page == 'blog')
                                {{ trans('admin/main.blog') }}
                            @elseif($page == 'products')
                                {{ trans('update.product') }}
                            @endif
                        </th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.type') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-center">{{ trans('admin/main.status') }}</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase text-slate-400 tracking-wider text-right">{{ trans('admin/main.action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 italic-last-of-type-tr-td-actions">
                    @foreach($comments as $comment)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <button type="button" class="js-show-description bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                {{ trans('admin/main.show') }}
                            </button>
                            <input type="hidden" value="{!! nl2br($comment->comment) !!}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-xs font-bold text-slate-500">{{ dateTimeFormat($comment->created_at, 'j M Y | H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ $comment->user->getProfileUrl() }}" target="_blank" class="text-xs font-bold text-primary border-b border-primary/20 hover:border-primary transition-all">{{ $comment->user->full_name }}</a>
                        </td>
                        <td class="px-6 py-4 max-w-[200px]">
                            <a href="{{ $comment->$itemRelation->getUrl() }}" target="_blank" class="text-xs font-bold text-slate-700 hover:text-primary transition-all line-clamp-1">
                                {{ $comment->$itemRelation->title }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">
                                {{ (empty($comment->reply_id)) ? trans('admin/main.main_comment') : trans('admin/main.replied') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($comment->status == 'pending')
                                <span class="badge-premium bg-amber-50 text-amber-600 ring-1 ring-amber-200">{{ trans('admin/main.pending') }}</span>
                            @else
                                <span class="badge-premium bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200">{{ trans('admin/main.published') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                @can('admin_comments_status')
                                    <a href="{{ getAdminPanelUrl() }}/comments/{{ $page }}/{{ $comment->id }}/toggle" 
                                       class="size-9 rounded-xl flex items-center justify-center transition-all scale-hover {{ $comment->status == 'pending' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}" 
                                       title="{{ trans('admin/main.'.(($comment->status == 'pending') ? 'publish' : 'pending')) }}">
                                        <span class="material-symbols-outlined text-[20px]">{{ $comment->status == 'pending' ? 'visibility' : 'visibility_off' }}</span>
                                    </a>
                                @endcan

                                @can('admin_comments_reply')
                                    <a href="{{ getAdminPanelUrl() }}/comments/{{ $page }}/{{ !empty($comment->reply_id) ? $comment->reply_id : $comment->id }}/reply" 
                                       class="size-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-all scale-hover" 
                                       title="{{ trans('admin/main.reply') }}">
                                        <span class="material-symbols-outlined text-[20px]">reply</span>
                                    </a>
                                @endcan

                                @can('admin_comments_edit')
                                    <a href="{{ getAdminPanelUrl() }}/comments/{{ $page }}/{{ $comment->id }}/edit" 
                                       class="size-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center transition-all scale-hover" 
                                       title="{{ trans('admin/main.edit') }}">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                @endcan

                                @can('admin_comments_delete')
                                    @include('admin.includes.delete_button',['url' => getAdminPanelUrl().'/comments/'. $page .'/'.$comment->id.'/delete', 'btnClass' => 'size-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all scale-hover border-none p-0'])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-8 border-t border-slate-100 flex items-center justify-center">
            {{ $comments->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>
    </div>

</div>

<!-- Discussion Modal -->
<div class="modal fade" id="contactMessage" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-[2.5rem] border-none shadow-2xl overflow-hidden p-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">chat</span>
                    </div>
                    <h5 class="text-xl font-black text-slate-800">{{ trans('admin/main.message') }}</h5>
                </div>
                <button type="button" class="size-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition-all border-none" data-dismiss="modal">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            
            <div class="modal-body p-0 rounded-2xl bg-slate-50 border border-slate-100 p-6 text-sm font-medium text-slate-600 leading-relaxed mb-6 whitespace-pre-wrap">
                <!-- Content injected via JS -->
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" class="bg-white border border-slate-200 text-slate-600 px-8 h-12 rounded-2xl font-bold transition-all hover:bg-slate-50" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/comments.min.js"></script>
@endpush

