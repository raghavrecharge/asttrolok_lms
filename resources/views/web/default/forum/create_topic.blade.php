@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/vendors/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #2563eb 0%, #1f3b64 100%);
            --secondary-bg: #f8fafc;
            --border-color: #e5e7eb;
            --text-main: #1f3b64;
        }

        .ct-form-card {
            background: #fff !important; 
            border-radius: 20px !important; 
            padding: 32px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important; 
            border: 1px solid #f0f0f0 !important;
            margin-bottom: 24px;
        }

        .ct-form-card .form-group label {
            font-size: 14px !important; 
            font-weight: 600 !important; 
            color: var(--text-main) !important; 
            margin-bottom: 10px !important;
            display: flex !important;
            align-items: center;
            gap: 8px;
        }

        .ct-form-card .form-control {
            border-radius: 12px !important; 
            border: 1.5px solid var(--border-color) !important;
            padding: 12px 16px !important; 
            font-size: 15px !important;
            transition: all 0.2s ease;
            height: auto;
        }

        .ct-form-card select.form-control { height: 50px !important; }

        .ct-form-card .form-control:focus {
            border-color: #2563eb !important; 
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1) !important;
            outline: none;
        }

        /* Summernote Forced Light Mode & Icon Fix */
        @font-face {
            font-family: "summernote";
            src: url("/assets/vendors/summernote/font/summernote.eot");
            src: url("/assets/vendors/summernote/font/summernote.eot#iefix") format("embedded-opentype"),
                 url("/assets/vendors/summernote/font/summernote.woff2") format("woff2"),
                 url("/assets/vendors/summernote/font/summernote.woff") format("woff"),
                 url("/assets/vendors/summernote/font/summernote.ttf") format("truetype");
        }

        .ct-form-card .note-editor.note-frame {
            border-radius: 16px !important; 
            border: 1.5px solid var(--border-color) !important; 
            overflow: hidden;
            background: #fff !important;
            color: #334155 !important;
        }
        
        /* Force editable area to be light */
        .ct-form-card .note-editable {
            background-color: #ffffff !important;
            color: #1e293b !important;
            min-height: 300px;
        }

        .ct-form-card .note-toolbar { 
            background: #f8fafc !important; 
            border-bottom: 1px solid #eef2f7 !important; 
            padding: 10px !important;
        }

        /* Standardize toolbar icons to be visible */
        .ct-form-card .note-btn {
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            color: #475569 !important;
            margin-right: 4px !important;
            padding: 6px 10px !important;
        }
        
        /* Font Fix for Summernote Icons */
        .note-btn [class^="note-icon-"], .note-btn [class*=" note-icon-"] {
            font-family: "summernote" !important;
            font-style: normal !important;
            font-weight: normal !important;
            text-decoration: inherit;
        }

        .ct-form-card .note-btn:hover { background: #f1f5f9 !important; }
        .ct-form-card .note-btn.active { background: #e2e8f0 !important; }

        /* Dropdown menus in Summernote */
        .note-dropdown-menu {
            background: #fff !important;
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }
        .note-dropdown-item:hover { background: #f8fafc !important; }

        /* Attachments Section */
        .attachment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .btn-add-attachment {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #10b981;
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(16,185,129,0.2);
        }
        .btn-add-attachment:hover {
            transform: scale(1.05);
            background: #059669;
        }

        .file-list-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }
        .file-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            position: relative;
        }
        .file-item:hover { border-color: #cbd5e1; background: #f1f5f9; }
        .file-icon {
            width: 38px;
            height: 38px;
            background: #fff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            font-size: 18px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .file-info { flex: 1; min-width: 0; }
        .file-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }
        .file-size { font-size: 11px; color: #64748b; }
        .remove-file {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 22px;
            height: 22px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .ct-footer-bar {
            background: #fff !important; 
            border-radius: 20px !important; 
            padding: 24px 32px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important; 
            border: 1px solid #f0f0f0 !important;
            display: flex !important; 
            align-items: center !important; 
            justify-content: space-between !important;
            flex-wrap: wrap !important; 
            gap: 16px !important;
        }
        .ct-publish-btn {
            background: linear-gradient(135deg, #43d477 0%, #2ecc71 100%) !important;
            border: none !important; 
            color: #fff !important; 
            padding: 14px 36px !important; 
            border-radius: 14px !important;
            font-weight: 700 !important; 
            font-size: 15px !important; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex !important; 
            align-items: center !important; 
            gap: 10px;
        }
        .ct-publish-btn:hover {
            box-shadow: 0 12px 28px rgba(46,204,113,0.3); 
            transform: translateY(-3px); 
            color: #fff !important;
        }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">{{ !empty($topic) ? trans('update.edit_topic') : trans('update.new_topic') }}</h2>
        <p class="text-gray font-14 mt-5 mb-20">{{ trans('update.new_topic_hint') }}</p>

        <form action="{{ !empty($topic) ? $topic->getEditUrl() : '/forums/create-topic' }}" method="post">
            {{ csrf_field() }}

            <div class="ct-form-card">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>{{ trans('update.topic_title') }}</label>
                            <input type="text" name="title" value="{{ !empty($topic) ? $topic->title : old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="{{ trans('update.topic_title_placeholder') }}">
                            @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(!empty($error125))
                            <snap style="color: red;">{{ $error125 }}</snap>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label>{{ trans('update.forums') }}</label>
                            <select name="forum_id" class="form-control @error('forum_id') is-invalid @enderror">
                                <option selected disabled>{{ trans('admin/main.choose_category') }}</option>
                                @foreach($forums as $forum)
                                    @if(!empty($forum->subForums) and count($forum->subForums))
                                        @php
                                            $showOptgroup = false;
                                            foreach($forum->subForums as $subForum) {
                                                if($subForum->checkUserCanCreateTopic() and !$subForum->close) {
                                                    $showOptgroup = true;
                                                }
                                            }
                                        @endphp
                                        @if($showOptgroup)
                                            <optgroup label="{{ $forum->title }}">
                                                @foreach($forum->subForums as $subForum)
                                                    @if($subForum->checkUserCanCreateTopic() and !$subForum->close)
                                                        <option value="{{ $subForum->id }}" {{ ((!empty($topic) and $topic->forum_id == $subForum->id) or (request()->get('forum_id') == $subForum->id)) ? 'selected' : '' }}>{{ $subForum->title }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @elseif($forum->checkUserCanCreateTopic() and !$forum->close)
                                        <option value="{{ $forum->id }}" {{ (request()->get('forum_id') == $forum->id) ? 'selected' : '' }}>{{ $forum->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('forum_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label>{{ trans('public.description') }}</label>
                            <textarea id="summernote" name="description" class="form-control @error('description') is-invalid @enderror">{!! !empty($topic) ? $topic->description : old('description') !!}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12 col-md-12">
                        <div id="topicImagesInputs" class="create-topic-attachments form-group mt-10">
                            <div class="attachment-header">
                                <label class="mb-0">
                                    <i class="fas fa-paperclip mr-2"></i> {{ trans('update.attachments') }}
                                </label>
                                <button type="button" class="btn-add-attachment" id="addAttachmentBtn" title="Add Attachment">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            {{-- Hidden file inputs container --}}
                            <div id="fileInputsContainer" class="d-none">
                                <input type="file" name="attachments[]" class="attachment-input" id="base_attachment">
                            </div>

                            <div id="fileListContainer" class="file-list-container">
                                {{-- Dynamically filled --}}
                                @if(!empty($topic) and !empty($topic->attachments) and count($topic->attachments))
                                    @foreach($topic->attachments as $topicAttachment)
                                        @php
                                            $fileName = basename($topicAttachment->path);
                                        @endphp
                                        <div class="file-item shadow-sm" data-path="{{ $topicAttachment->path }}">
                                            <div class="file-icon">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name" title="{{ $fileName }}">{{ $fileName }}</span>
                                                <span class="file-size">Existing Attachment</span>
                                            </div>
                                            {{-- Existing attachments might need a different removal logic depending on backend, 
                                                 but for now we'll allow removing it from view --}}
                                            <input type="hidden" name="existing_attachments[]" value="{{ $topicAttachment->path }}">
                                            <div class="remove-file" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            @error('attachments')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="ct-footer-bar mt-20">
                <div class="ct-terms">
                    <strong>{{ trans('update.terms_and_rules_confirmation') }}</strong>
                    <p class="mb-0 mt-3" style="font-size:12px;">{{ trans('update.terms_and_rules_confirmation_hint') }}</p>
                </div>
                <button type="submit" class="ct-publish-btn">
                    <i data-feather="send" width="16" height="16"></i>
                    {{ trans('update.publish_topic') }}
                </button>
            </div>
        </form>
    </section>
@endsection

@push('scripts_bottom')
    <script src="{{ config('app.js_css_url') }}/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="{{ config('app.js_css_url') }}/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Summernote in Light Mode
            if (jQuery().summernote) {
                $('#summernote').summernote({
                    placeholder: 'Topic Description Placeholder',
                    height: 300,
                    styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']], 
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['fullscreen', 'codeview', 'help']],
                    ],
                    callbacks: {
                        onChange: function(contents, $editable) {
                            // Sync content if needed
                        }
                    }
                });
            }

            // Attachments Management
            const addAttachmentBtn = $('#addAttachmentBtn');
            const fileInputsContainer = $('#fileInputsContainer');
            const fileListContainer = $('#fileListContainer');
            let fileCounter = 0;

            addAttachmentBtn.on('click', function() {
                const id = 'attachment_' + Date.now();
                const newInput = $('<input type="file" name="attachments[]" class="attachment-input d-none" id="' + id + '">');
                fileInputsContainer.append(newInput);
                newInput.click();

                newInput.on('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const fileName = file.name;
                        const fileSize = formatFileSize(file.size);
                        
                        // Create preview item
                        const item = $(`
                            <div class="file-item shadow-sm" id="item_${id}">
                                <div class="file-icon">
                                    <i class="fas ${getFileIcon(fileName)}"></i>
                                </div>
                                <div class="file-info">
                                    <span class="file-name" title="${fileName}">${fileName}</span>
                                    <span class="file-size">${fileSize}</span>
                                </div>
                                <div class="remove-file" onclick="removeAttachment('${id}')">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        `);
                        fileListContainer.append(item);
                    } else {
                        newInput.remove();
                    }
                });
            });

            window.removeAttachment = function(id) {
                $('#' + id).remove();
                $('#item_' + id).remove();
            };

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function getFileIcon(filename) {
                const ext = filename.split('.').pop().toLowerCase();
                const icons = {
                    'pdf': 'fa-file-pdf',
                    'doc': 'fa-file-word',
                    'docx': 'fa-file-word',
                    'xls': 'fa-file-excel',
                    'xlsx': 'fa-file-excel',
                    'png': 'fa-file-image',
                    'jpg': 'fa-file-image',
                    'jpeg': 'fa-file-image',
                    'zip': 'fa-file-archive',
                    'rar': 'fa-file-archive'
                };
                return icons[ext] || 'fa-file-alt';
            }
            
            // Re-initialize Feather Icons if any are used
            if (window.feather) {
                feather.replace();
            }
        });
    </script>
@endpush
