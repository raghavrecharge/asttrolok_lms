@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4338ca',
                        'primary-dark': '#3730a3',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        .premium-form-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        .form-group label {
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.875rem !important;
            margin-bottom: 0.5rem !important;
        }
        .form-control {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            height: 48px !important;
            padding: 0 16px !important;
            font-size: 0.95rem !important;
            transition: all 0.2s ease !important;
        }
        .form-control:focus {
            border-color: #4338ca !important;
            box-shadow: 0 0 0 4px rgba(67, 56, 202, 0.1) !important;
        }
        .select2-container--default .select2-selection--single {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            height: 48px !important;
            padding: 8px 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
        }
        .summernote-wrapper .note-editor {
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            overflow: hidden !important;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .font-variation-icon {
            font-variation-settings: 'FILL' 1, 'wght' 400;
        }
        body {
            background-color: #f8fafc;
        }
        /* Fix for breadcrumbs spacing in layout */
        .section-header { display: none; }
    </style>
@endpush

@section('content')
    <div class="p-6 lg:p-10 max-w-7xl mx-auto font-sans">
        <!-- Modern Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-3xl">confirmation_number</span>
                    {{ trans('admin/main.new_ticket') }}
                </h1>
                <p class="text-slate-500 mt-2 font-medium flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Support Admin Portal • Asttrolok STAFF
                </p>
            </div>
            <nav class="flex text-xs font-bold text-slate-400 gap-2 items-center bg-white px-5 py-3 rounded-2xl shadow-sm border border-slate-100 uppercase tracking-wider">
                <a href="{{ getAdminPanelUrl() }}" class="hover:text-primary transition-colors">{{ trans('admin/main.dashboard') }}</a>
                <span class="material-symbols-outlined text-sm leading-none">chevron_right</span>
                <span class="text-slate-900">{{ trans('admin/main.supports') }}</span>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Main Form Column -->
            <div class="lg:col-span-2 space-y-8">
                <div class="premium-form-card overflow-hidden">
                    <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-8 text-white relative overflow-hidden">
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/20">
                                <span class="material-symbols-outlined text-white text-3xl font-variation-icon">add_task</span>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">Open Support Case</h2>
                                <p class="text-slate-300 text-sm mt-0.5">Please provide as much detail as possible so we can assist better.</p>
                            </div>
                        </div>
                        <!-- Abstract Background Pattern -->
                        <div class="absolute right-0 top-0 w-64 h-64 bg-primary/20 rounded-full blur-3xl -mr-20 -mt-20"></div>
                    </div>

                    <div class="p-8">
                        <form action="{{ getAdminPanelUrl() }}/supports/{{ !empty($support) ? $support->id.'/update' : 'store' }}" method="Post">
                            {{ csrf_field() }}

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                                <!-- User Selection -->
                                <div class="form-group col-span-2 md:col-span-1">
                                    <label>{{ trans('admin/main.users') }}</label>
                                    <select name="user_id" class="form-control search-user-select2 block w-full !h-12"
                                            data-search-option="for_user_group"
                                            data-placeholder="{{ trans('public.search_user') }}">
                                        @if(!empty($toUser))
                                            <option value="{{ $toUser->id }}">{{ $toUser->full_name }}</option>
                                        @endif
                                    </select>
                                </div>

                                <!-- Department Selection -->
                                <div class="form-group col-span-2 md:col-span-1">
                                    <label>{{ trans('admin/main.department') }}</label>
                                    <select name="department_id" class="form-control block w-full @error('department_id') border-red-500 @enderror">
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" @if(!empty($support) and $support->department_id == $department->id) selected @endif>{{ $department->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                                </div>

                                <!-- Title/Subject -->
                                <div class="form-group col-span-2">
                                    <label>{{ trans('admin/main.title') }}</label>
                                    <input type="text" name="title" class="form-control block w-full @error('title') border-red-500 @enderror"
                                           placeholder="Briefly summarize the ticket objective..."
                                           value="{{ !empty($support) ? $support->title : old('title') }}"/>
                                    @error('title')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group mb-8">
                                <label>{{ trans('admin/main.description') }}</label>
                                <div class="summernote-wrapper">
                                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="8">{{ !empty($support) ? $support->message : old('message') }}</textarea>
                                </div>
                                @error('message')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                            </div>

                            <!-- Attachments -->
                            <div class="form-group mb-8">
                                <label>{{ trans('admin/main.attach') }} (Optional)</label>
                                <div class="flex items-center gap-4">
                                    <div class="flex-grow">
                                        <div class="relative group">
                                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 bg-slate-50 flex flex-col items-center justify-center hover:bg-slate-100 hover:border-primary/40 transition-all cursor-pointer admin-file-manager" data-input="attach" data-preview="holder">
                                                <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                                    <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors">upload_file</span>
                                                </div>
                                                <p class="text-xs font-bold text-slate-900 uppercase tracking-tight">Browse Files</p>
                                                <input type="hidden" name="attach" id="attach" value="{{ old('attach') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-40 h-[106px] flex items-center justify-center border border-slate-100 rounded-2xl bg-white p-2 overflow-hidden shadow-inner" id="holder">
                                        <span class="material-symbols-outlined text-slate-200 text-4xl">image_not_supported</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-100 mt-10">
                                <a href="{{ getAdminPanelUrl() }}/supports" class="px-8 py-3 rounded-xl font-bold text-slate-500 hover:bg-slate-50 transition-colors uppercase text-sm tracking-wider">
                                    Cancel
                                </a>
                                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-10 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 uppercase text-sm tracking-widest">
                                    {{ trans('admin/main.send') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info Guides -->
            <div class="space-y-6">
                <div class="premium-form-card p-8 group">
                    <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined font-variation-icon">shield</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Secure Data</h3>
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">
                        All information provided in this ticket is encrypted and visible only to authorized staff members.
                    </p>
                </div>

                <div class="premium-form-card p-8 group">
                    <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-6 group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined font-variation-icon">timer</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Typical Response</h3>
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">
                        The current average response time for support tickets is <span class="text-slate-900 font-bold">2-4 business hours</span>.
                    </p>
                </div>

                <div class="premium-form-card p-8 group">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined font-variation-icon">auto_stories</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Knowledge Base</h3>
                    <p class="text-sm text-slate-500 leading-relaxed font-medium">
                        Check our FAQs for immediate answers to common issues before submitting your case.
                    </p>
                    <a href="#" class="inline-flex items-center gap-2 mt-4 text-emerald-600 font-bold text-sm hover:gap-3 transition-all">
                        VIEW ARTICLES
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('textarea[name="message"]').summernote({
                height: 250,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endpush
