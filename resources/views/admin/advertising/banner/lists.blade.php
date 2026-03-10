@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Advertising / Banners</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_advertising_banners_create')
                    <a href="{{ getAdminPanelUrl() }}/advertising/banners/new" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-opacity-90 transition-all shadow-sm">
                        <span class="material-symbols-rounded text-xl">add</span>
                        {{ trans('admin/main.new_banner') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            <!-- Metrics Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-primary/20 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-110 transition-transform text-decoration-none">
                        <span class="material-symbols-rounded text-2xl">imagesmode</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Total Banners</p>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ $banners->total() }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-emerald-500/20 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform text-decoration-none">
                        <span class="material-symbols-rounded text-2xl">visibility</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Published</p>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ $publishedBanners }}</h3>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:border-rose-500/20 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 group-hover:scale-110 transition-transform text-decoration-none">
                        <span class="material-symbols-rounded text-2xl">visibility_off</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-none mb-1">Unpublished</p>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">{{ $unpublishedBanners }}</h3>
                    </div>
                </div>
            </div>

            <!-- Banner Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.position') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.banner_size') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.published') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.created_at') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($banners as $banner)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 overflow-hidden border border-gray-100">
                                                @if($banner->image)
                                                    <img src="{{ $banner->image }}" class="w-full h-full object-cover" alt="">
                                                @else
                                                    <span class="material-symbols-rounded text-xl">image</span>
                                                @endif
                                            </div>
                                            <span class="font-bold text-gray-900 line-clamp-1 italic tracking-tight">{{ $banner->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[10px] font-bold px-2.5 py-1 bg-gray-50 text-gray-500 rounded-lg border border-gray-100 uppercase tracking-widest font-mono">
                                            {{ $banner->position }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xs font-black text-primary uppercase tracking-tighter">
                                            {{ \App\Models\AdvertisingBanner::$size[$banner->size] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($banner->published)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                {{ trans('admin/main.yes') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                                {{ trans('admin/main.no') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-700">{{ dateTimeFormat($banner->created_at, 'Y M j') }}</span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ dateTimeFormat($banner->created_at, 'H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_advertising_banners_edit')
                                                <a href="{{ getAdminPanelUrl() }}/advertising/banners/{{ $banner->id }}/edit" 
                                                   class="p-2 text-gray-400 hover:text-primary hover:bg-primary/10 rounded-xl transition-all"
                                                   title="{{ trans('admin/main.edit') }}">
                                                    <span class="material-symbols-rounded text-xl">edit_note</span>
                                                </a>
                                            @endcan

                                            @can('admin_advertising_banners_delete')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/advertising/banners/'. $banner->id.'/delete',
                                                    'btnClass' => 'p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all'
                                                ])
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($banners->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center text-decoration-none">
                        {{ $banners->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
