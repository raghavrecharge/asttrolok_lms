@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-4 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Newsletters / Subscriber Emails</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_users_export_excel')
                    <a href="{{ getAdminPanelUrl() }}/newsletters/excel" class="flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm text-sm">
                        <span class="material-symbols-rounded text-xl text-gray-400">download</span>
                        {{ trans('admin/main.export_xls') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.email') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.created_at') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($newsletters as $newsletter)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-primary/5 flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                                <span class="material-symbols-rounded text-xl">alternate_email</span>
                                            </div>
                                            <span class="font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors">{{ $newsletter->email }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-700 leading-none mb-1">{{ dateTimeFormat($newsletter->created_at, 'Y M j') }}</span>
                                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ dateTimeFormat($newsletter->created_at, 'H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex justify-end items-center gap-2">
                                            @can('admin_newsletters_delete')
                                                @include('admin.includes.delete_button',[
                                                    'url' => getAdminPanelUrl().'/newsletters/'.$newsletter->id.'/delete',
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

                @if($newsletters->hasPages())
                    <div class="px-6 py-6 border-t border-gray-50 flex justify-center text-decoration-none">
                        {{ $newsletters->appends(request()->input())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

