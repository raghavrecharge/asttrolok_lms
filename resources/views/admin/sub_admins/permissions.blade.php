@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}/sub-admins">Sub-Admins</a></div>
                <div class="breadcrumb-item">Permissions</div>
            </div>
        </div>

        @if(session('msg'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('msg') }}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <div class="section-body">
            {{-- Sub-Admin Info Card --}}
            <div class="row">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3" style="background: #6777ef; border-radius:50%; width:45px; height:45px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-user-shield text-white" style="font-size:18px;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $subAdmin->full_name }}</h6>
                                        <small class="text-muted">{{ $subAdmin->email }}</small>
                                    </div>
                                </div>
                                <div>
                                    @if($subAdmin->ban)
                                        <span class="badge badge-danger">Inactive</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ getAdminPanelUrl() }}/sub-admins/{{ $subAdmin->id }}/permissions" method="POST">
                @csrf

                {{-- Quick Actions --}}
                <div class="card">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <div>
                                <strong>Permission Assignment</strong>
                                <p class="text-muted mb-0 font-13">Select which menus and actions this sub-admin can access.</p>
                            </div>
                            <div class="d-flex" style="gap: 8px;">
                                <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-check-double mr-1"></i> Select All
                                </button>
                                <button type="button" id="deselectAll" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i> Deselect All
                                </button>
                                <span class="btn btn-sm btn-light" id="selectedCount">
                                    <strong>0</strong> selected
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Permission Sections --}}
                <div class="row">
                    @foreach($sections as $section)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card section-card">
                                <div class="card-header py-3" style="background: #f8f9fa; border-bottom: 2px solid #6777ef;">
                                    <div class="d-flex align-items-center w-100">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   id="section_{{ $section->id }}"
                                                   value="{{ $section->id }}"
                                                   class="custom-control-input section-parent-cb"
                                                   data-section-id="{{ $section->id }}"
                                                   {{ in_array($section->id, $activePermissions) ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold cursor-pointer" for="section_{{ $section->id }}">
                                                {{ $section->caption }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($section->children) && $section->children->count() > 0)
                                    <div class="card-body py-2">
                                        @foreach($section->children as $child)
                                            <div class="custom-control custom-checkbox py-1">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       id="section_{{ $child->id }}"
                                                       value="{{ $child->id }}"
                                                       class="custom-control-input section-child-cb"
                                                       data-parent-id="{{ $section->id }}"
                                                       {{ in_array($child->id, $activePermissions) ? 'checked' : '' }}>
                                                <label class="custom-control-label cursor-pointer font-13" for="section_{{ $child->id }}">
                                                    {{ $child->caption }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-save mr-1"></i> Save Permissions
                            </button>
                            <a href="{{ getAdminPanelUrl() }}/sub-admins" class="btn btn-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts_bottom')
<script>
    (function() {
        function updateCount() {
            var count = document.querySelectorAll('input[name="permissions[]"]:checked').length;
            document.getElementById('selectedCount').innerHTML = '<strong>' + count + '</strong> selected';
        }

        // Select All
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function(cb) {
                cb.checked = true;
            });
            updateCount();
        });

        // Deselect All
        document.getElementById('deselectAll').addEventListener('click', function() {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function(cb) {
                cb.checked = false;
            });
            updateCount();
        });

        // Parent checkbox toggles all children
        document.querySelectorAll('.section-parent-cb').forEach(function(parentCb) {
            parentCb.addEventListener('change', function() {
                var sectionId = this.dataset.sectionId;
                var checked = this.checked;
                document.querySelectorAll('.section-child-cb[data-parent-id="' + sectionId + '"]').forEach(function(childCb) {
                    childCb.checked = checked;
                });
                updateCount();
            });
        });

        // Child checkbox: if all children unchecked, uncheck parent; if any checked, check parent
        document.querySelectorAll('.section-child-cb').forEach(function(childCb) {
            childCb.addEventListener('change', function() {
                var parentId = this.dataset.parentId;
                var siblings = document.querySelectorAll('.section-child-cb[data-parent-id="' + parentId + '"]');
                var anyChecked = false;
                siblings.forEach(function(s) { if (s.checked) anyChecked = true; });
                var parentEl = document.getElementById('section_' + parentId);
                if (parentEl) parentEl.checked = anyChecked;
                updateCount();
            });
        });

        // Track all checkbox changes
        document.querySelectorAll('input[name="permissions[]"]').forEach(function(cb) {
            cb.addEventListener('change', updateCount);
        });

        // Initial count
        updateCount();
    })();
</script>
@endpush
