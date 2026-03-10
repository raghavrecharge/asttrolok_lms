@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header mb-6 h-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-transparent p-0 border-none shadow-none text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle }}</h1>
                <p class="text-sm text-gray-500 mt-1 uppercase tracking-wider font-bold font-mono text-[10px]">Marketing & Loyalty / Newsletters / Dispatch Campaign</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ getAdminPanelUrl() }}/newsletters/history" class="px-4 py-2 bg-white text-gray-700 rounded-xl font-medium border border-gray-100 hover:bg-gray-50 transition-all shadow-sm text-sm">
                    {{ trans('admin/main.history') }}
                </a>
            </div>
        </div>

        <div class="section-body">
            @if(!empty(session()->has('send_email_error')))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 rounded-2xl flex gap-4 animate-in fade-in slide-in-from-top-4 duration-500 text-left">
                    <div class="w-10 h-10 rounded-xl bg-rose-500 flex items-center justify-center text-white shrink-0 shadow-sm">
                        <span class="material-symbols-rounded">error</span>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-black text-rose-900 uppercase tracking-tight">System Error</h4>
                        <p class="text-xs text-rose-700/80 font-bold leading-relaxed">{{ session()->get('send_email_error') }}</p>
                    </div>
                </div>
                @php session()->forget('send_email_error'); @endphp
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden text-left">
                <div class="p-8">
                    <form action="{{ getAdminPanelUrl() }}/newsletters/send" method="post" enctype="multipart/form-data" class="max-w-4xl mx-auto">
                        {{ csrf_field() }}

                        <div class="space-y-10">
                            {{-- Header Info --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                                <div class="space-y-6">
                                    <h3 class="text-xs font-black text-primary uppercase tracking-widest flex items-center gap-2">
                                        <span class="material-symbols-rounded text-lg">campaign</span>
                                        Campaign Essentials
                                    </h3>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('admin/main.title') }}</label>
                                        <input type="text" name="title" value="{{ old('title') }}" 
                                               class="w-full px-4 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-gray-300 @error('title') ring-2 ring-rose-500/20 @enderror" 
                                               placeholder="Enter campaign subject line..."/>
                                        @error('title') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('update.send_method') }}</label>
                                        <div class="relative group">
                                            <select name="send_method" class="js-newsletter-send-method w-full pl-4 pr-10 py-3 bg-gray-50 border-none rounded-2xl text-sm font-bold appearance-none focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                                <option value="send_to_all" {{ old('send_method') == 'send_to_all' ? 'selected' : '' }}>{{ trans('update.send_newsletter_to_all') }}</option>
                                                <option value="send_to_bcc" {{ old('send_method') == 'send_to_bcc' ? 'selected' : '' }}>{{ trans('update.send_newsletter_to_bcc') }}</option>
                                                <option value="send_to_excel" {{ old('send_method') == 'send_to_excel' ? 'selected' : '' }}>{{ trans('update.send_newsletter_to_excel') }}</option>
                                            </select>
                                            <span class="material-symbols-rounded absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none group-hover:text-primary transition-colors">unfold_more</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <h3 class="text-xs font-black text-primary uppercase tracking-widest flex items-center gap-2 mt-0 md:mt-0">
                                        <span class="material-symbols-rounded text-lg">person_add</span>
                                        Recipient Details
                                    </h3>

                                    <div class="js-newsletter-bcc-email {{ (old('send_method') != 'send_to_bcc') ? 'hidden' : '' }} form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('update.send_newsletter_bcc_email') }}</label>
                                        <div class="relative">
                                            <input type="text" name="bcc_email" value="{{ old('bcc_email') }}" 
                                                   class="w-full pl-11 pr-4 py-3 bg-gray-50 border-none rounded-2xl text-[13px] font-mono font-bold focus:ring-2 focus:ring-primary/20 transition-all placeholder:text-gray-300 @error('bcc_email') ring-2 ring-rose-500/20 @enderror" 
                                                   placeholder="email1@example.com, email2@..."/>
                                            <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg transition-colors leading-none">alternate_email</span>
                                        </div>
                                        @error('bcc_email') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="js-newsletter-excel {{ (old('send_method') != 'send_to_excel') ? 'hidden' : '' }} form-group space-y-2">
                                        <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest">{{ trans('update.send_newsletter_select_excel_file') }}</label>
                                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-100 hover:border-primary/20 hover:bg-white transition-all group">
                                            <input type="file" name="excel" class="hidden" id="excel_file">
                                            <label for="excel_file" class="flex-1 flex items-center gap-3 cursor-pointer">
                                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-gray-400 group-hover:text-emerald-600 shadow-sm border border-gray-100 transition-colors">
                                                    <span class="material-symbols-rounded text-xl leading-none">upload_file</span>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-black text-gray-700 uppercase tracking-tight leading-none mb-1">Upload Excel List</span>
                                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none">XLS or XLSX format</p>
                                                </div>
                                            </label>
                                        </div>
                                        @error('excel') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="bg-blue-50/50 rounded-2xl p-6 border border-blue-100 flex gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-blue-500 flex items-center justify-center text-white shrink-0 shadow-sm text-decoration-none">
                                            <span class="material-symbols-rounded text-xl leading-none">info</span>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-xs font-black text-blue-900 uppercase tracking-tight leading-none mb-1 mt-1">{{ trans('admin/main.hints') }}</p>
                                            <p class="text-[10px] text-blue-700/80 font-bold leading-relaxed uppercase tracking-widest">{{ trans('update.send_newsletter_description_hint') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="h-px bg-gray-100"></div>

                            {{-- Editor Section --}}
                            <div class="space-y-6">
                                <h3 class="text-xs font-black text-primary uppercase tracking-widest flex items-center gap-2">
                                    <span class="material-symbols-rounded text-lg">edit_note</span>
                                    Email Content
                                </h3>

                                <div class="form-group space-y-2">
                                    <label class="text-[10px] font-black text-gray-400 ml-1 uppercase tracking-widest leading-none">{{ trans('public.description') }}</label>
                                    <div class="@error('description') border-2 border-rose-500/20 rounded-2xl @enderror">
                                        <textarea id="summernote" name="description" class="summernote" placeholder="{{ trans('admin/main.description_placeholder') }}">{!! old('description') !!}</textarea>
                                    </div>
                                    @error('description') <p class="text-[10px] text-rose-500 font-bold mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="pt-8 border-t border-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <button type="submit" class="flex items-center gap-2 px-10 py-3.5 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-gray-800 transition-all shadow-lg active:scale-95 group">
                                        <span class="material-symbols-rounded text-xl group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform leading-none">send</span>
                                        {{ trans('admin/main.send') }}
                                    </button>
                                    <a href="{{ getAdminPanelUrl() }}/newsletters" class="px-8 py-3.5 bg-gray-100 text-gray-500 rounded-2xl font-black uppercase tracking-widest text-[10px] hover:bg-gray-200 transition-all active:scale-95">
                                        {{ trans('admin/main.cancel') }}
                                    </a>
                                </div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Instant Dispatch to Mail Server</p>
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
    <script src="/assets/default/js/admin/newsletter.min.js"></script>
@endpush
