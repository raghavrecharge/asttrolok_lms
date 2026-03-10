@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Newsletters / Dispatch History</p>
            </div>
            <div class="flex items-center gap-3">
                @can('admin_newsletters_send')
                    <a href="{{ getAdminPanelUrl() }}/newsletters/send" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-xl font-medium hover:bg-opacity-90 transition-all shadow-sm">
                        <span class="material-symbols-rounded text-xl">send</span>
                        {{ trans('update.send_newsletter') }}
                    </a>
                @endcan
            </div>
        </div>

        <div class="section-body text-left">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="table-responsive text-decoration-none">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('update.send_method') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">{{ trans('admin/main.title') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('admin/main.description') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-center">{{ trans('update.email_count') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right pr-8">{{ trans('admin/main.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($newsletters as $newsletter)
                                <tr class="group hover:bg-gray-50/50 transition-all">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            @php
                                                $icon = 'groups';
                                                switch($newsletter->send_method) {
                                                    case 'send_to_all': $icon = 'hub'; break;
                                                    case 'send_to_bcc': $icon = 'alternate_email'; break;
                                                    case 'send_to_excel': $icon = 'table_chart'; break;
                                                }
                                            @endphp
                                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 group-hover:text-primary transition-colors border border-gray-100 shadow-sm">
                                                <span class="material-symbols-rounded text-lg">{{ $icon }}</span>
                                            </div>
                                            <span class="text-[11px] font-black text-gray-700 leading-tight uppercase tracking-tight italic">
                                                @switch($newsletter->send_method)
                                                    @case('send_to_all') {{ trans('update.send_newsletter_to_all') }} @break
                                                    @case('send_to_bcc') {{ trans('update.send_newsletter_to_bcc') }} @break
                                                    @case('send_to_excel') {{ trans('update.send_newsletter_to_excel') }} @break
                                                @endswitch
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900 tracking-tight group-hover:text-primary transition-colors italic">
                                        {{ $newsletter->title }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button type="button" data-item-id="{{ $newsletter->id }}" class="js-show-description px-4 py-1.5 bg-gray-50 text-gray-600 rounded-lg text-[10px] font-black uppercase tracking-widest border border-gray-100 hover:bg-primary/5 hover:text-primary hover:border-primary/20 transition-all active:scale-95">
                                            {{ trans('admin/main.show') }}
                                        </button>
                                        <input type="hidden" value="{{ nl2br($newsletter->description) }}">
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-primary tracking-tighter">
                                        {{ $newsletter->email_count }}
                                    </td>
                                    <td class="px-6 py-4 text-right pr-8">
                                        <div class="flex flex-col text-right">
                                            <span class="text-xs font-bold text-gray-700 leading-none mb-1">{{ dateTimeFormat($newsletter->created_at, 'j M Y') }}</span>
                                            <span class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ dateTimeFormat($newsletter->created_at, 'H:i') }}</span>
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

    {{-- Description Modal --}}
    <div class="modal fade" id="newsletterMessageModal" tabindex="-1" aria-labelledby="notificationMessageLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content overflow-hidden border-none rounded-3xl shadow-2xl">
                <div class="modal-header bg-gray-900 p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white border border-white/10">
                            <span class="material-symbols-rounded">description</span>
                        </div>
                        <h5 class="modal-title text-sm font-black text-white uppercase tracking-widest" id="notificationMessageLabel">{{ trans('admin/main.contacts_message') }}</h5>
                    </div>
                    <button type="button" class="text-white/40 hover:text-white transition-colors p-2" data-dismiss="modal" aria-label="Close">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <div class="modal-body p-8 text-gray-700 text-sm leading-relaxed overflow-y-auto max-h-[60vh] text-left">
                    {{-- Content injected via JS --}}
                </div>
                <div class="modal-footer bg-gray-50 p-6 border-t border-gray-100 flex justify-end">
                    <button type="button" class="px-6 py-2 bg-white text-gray-600 text-[10px] font-black uppercase tracking-widest rounded-xl border border-gray-200 hover:bg-gray-100 transition-all active:scale-95" data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/admin/newsletter.min.js"></script>
@endpush
