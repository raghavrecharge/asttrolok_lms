@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/select2/select2.min.css">
    <style>
        .select2-container {
            z-index: 1212 !important;
        }

        /* Progress Modal Custom Styles */
        #progressDetailModal .modal-content {
            border-radius: 20px !important;
            border: none !important;
            box-shadow: 0 15px 50px rgba(0,0,0,0.15) !important;
        }
        #progressDetailModal .modal-body {
            padding: 30px 25px !important;
        }
        .text-dark-blue {
            color: #1f3b64 !important;
        }
        .text-gray {
            color: #8c98a4 !important;
        }
        .font-18 { font-size: 18px !important; }
        .font-16 { font-size: 16px !important; }
        .font-14 { font-size: 14px !important; }
        .font-12 { font-size: 12px !important; }
        .font-11 { font-size: 11px !important; }
        .font-weight-bold { font-weight: 700 !important; }
        .progress-item-container {
            background: #f8f9fb;
            border-radius: 10px;
            border: 1px solid #f0f0f0;
            padding: 10px;
            margin-bottom: 8px;
        }
        .custom-modal-progress {
            height: 3px !important;
            border-radius: 10px !important;
            background: rgba(0,0,0,0.05) !important;
            overflow: hidden;
        }
        .custom-modal-progress .progress-bar {
            height: 100% !important;
            border-radius: 10px !important;
        }

        /* Progress Trigger Table Styling */
        .table-progress-container {
            cursor: pointer;
            padding: 8px 10px;
            background: #f8f9fb;
            border-radius: 10px;
            transition: all 0.2s ease;
            border: 1px solid #f0f0f0;
            display: inline-block;
            width: 100%;
            max-width: 150px;
            text-align: left;
        }
        .table-progress-container:hover {
            background: #f0f3ff;
            border-color: #d0d7ff;
        }
        .table-progress-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }
        .table-progress-label .text {
            font-size: 10px;
            font-weight: 700;
            color: #1f3b64;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .table-progress-label .percent {
            font-size: 11px;
            font-weight: 800;
            color: #43d477;
        }
        .table-progress-bar {
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .table-progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #43d477 0%, #28a745 100%);
            border-radius: 10px;
            transition: width 0.5s ease;
        }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $webinar->title }} - {{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{trans('admin/main.dashboard')}}</a></div>
                <div class="breadcrumb-item"><a>{{ $pageTitle }}</a></div>
            </div>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ trans('admin/main.total_students') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $totalStudents }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-success">
                    <i class="fas fa-briefcase"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ trans('update.active_students') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $totalActiveStudents }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-warning">
                    <i class="fas fa-info-circle"></i></div>
                <div class="card-wrap">
                    <div class="card-header">
                        <h4>{{ trans('update.expire_students') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $totalExpireStudents }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6 col-12">
            <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                    <i class="fas fa-ban"></i></div>
                <div class="card-wrap">
                   {{-- <div class="card-header">
                        <h4>{{ trans('update.average_learning') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $averageLearning }}
                    </div>--}}
                </div>
            </div>
        </div>
    </div>

    <section class="card">
        <div class="card-body">
            <form method="get" class="mb-0">

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.search') }}</label>
                            <input name="full_name" type="text" class="form-control" value="{{ request()->get('full_name') }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                            <div class="input-group">
                                <input type="date" id="from" class="text-center form-control" name="from" value="{{ request()->get('from') }}" placeholder="Start Date">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                            <div class="input-group">
                                <input type="date" id="to" class="text-center form-control" name="to" value="{{ request()->get('to') }}" placeholder="End Date">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.filters') }}</label>
                            <select name="sort" data-plugin-selectTwo class="form-control populate">
                                <option value="">{{ trans('admin/main.filter_type') }}</option>
                                <option value="rate_asc" @if(request()->get('sort') == 'rate_asc') selected @endif>{{ trans('update.rate_ascending') }}</option>
                                <option value="rate_desc" @if(request()->get('sort') == 'rate_desc') selected @endif>{{ trans('update.rate_descending') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.users_group') }}</label>
                            <select name="group_id" data-plugin-selectTwo class="form-control populate">
                                <option value="">{{ trans('admin/main.select_users_group') }}</option>
                                @foreach($userGroups as $userGroup)
                                    <option value="{{ $userGroup->id }}" @if(request()->get('group_id') == $userGroup->id) selected @endif>{{ $userGroup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div> -->

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.role') }}</label>
                            <select name="role_id" class="form-control">
                                <option value="">{{ trans('admin/main.all_roles') }}</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @if($role->id == request()->get('role_id')) selected @endif>{{ $role->caption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.status') }}</label>
                            <select name="status" data-plugin-selectTwo class="form-control populate">
                                <option value="">{{ trans('admin/main.all_status') }}</option>
                                <option value="active" @if(request()->get('status') == 'active') selected @endif>{{ trans('admin/main.active') }}</option>
                                <option value="expire" @if(request()->get('status') == 'expire') selected @endif>{{ trans('panel.expired') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mt-1">
                            <label class="input-label mb-4"> </label>
                            <input type="submit" class="text-center btn btn-primary w-100" value="{{ trans('admin/main.show_results') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <div class="card">
        <div class="card-header">
            @can('admin_users_export_excel')
                <div class="text-right w-100">
                    <button type="button" class="btn btn-primary trigger-export-modal">{{ trans('admin/main.export_xls') }}</button>
                </div>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive text-center">
                <table class="table table-striped font-14">
                    <tr>
                        <th class="text-left">ID</th>
                        <th class="text-left">{{ trans('admin/main.name') }}</th>

                        <th>{{ trans('update.learning') }}</th>

                        <th width="120">{{ trans('admin/main.actions') }}</th>
                    </tr>

                    @foreach($students as $student)

                        <tr>
                            <td class="text-left">{{ $student->id ?? '-' }}</td>
                            <td class="text-left">
                                <div class="d-flex align-items-center">
                                    <figure class="avatar mr-2">
                                        <img src="{{ $student->getAvatar() }}" alt="{{ $student->full_name }}">
                                    </figure>
                                    <div class="media-body ml-1">
                                        <div class="mt-0 mb-1 font-weight-bold">{{ $student->full_name }}</div>

                                        @if($student->mobile)
                                            <div class="text-primary text-small font-600-bold">{{ $student->mobile }}</div>
                                        @endif

                                        @if($student->email)
                                            <div class="text-primary text-small font-600-bold">{{ $student->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>

                                 @php
                                            $Progress = 0;
                                            $totalVideos =0;
                                            $webinar_id = request()->route('id');
                                            $access_content = DB::table('subscription_access')
            ->where('subscription_id',  (int) $webinar_id)
            ->where('user_id', (int) $student->id)
            ->first();
            
            $video_limit=($access_content->access_content_count)+5;

                                          $totalVideos =$video_limit;
                                          $watchedVideos = \App\Models\SubscriptionCourseProgress::where('subscription_id', (int) $webinar_id)
                                            ->where('user_id',(int) $student->id)
                                            ->sum('watch_percentage');

                                         $slugs = \App\Models\subscription::where('id', (int) $webinar_id)->where('status', 'active')->first();

                                        @endphp

                                        @if($totalVideos)
                                            @php
                                                $Progress = (int) ($watchedVideos/ $totalVideos);

                                            @endphp
                                        @endif
                                    @if(!empty($student->id))
                                        <div class="table-progress-container trigger-progress-modal" data-student-id="{{ $student->id }}">
                                            <div class="table-progress-label">
                                                <span class="text">Progress</span>
                                                <span class="percent">{{ $Progress }}%</span>
                                            </div>
                                            <div class="table-progress-bar">
                                                <div class="table-progress-bar-fill" style="width: {{ $Progress }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="table-progress-container">
                                            <div class="table-progress-label">
                                                <span class="text">Progress</span>
                                                <span class="percent">{{ $Progress }}%</span>
                                            </div>
                                            <div class="table-progress-bar">
                                                <div class="table-progress-bar-fill" style="width: {{ $Progress }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                            </td>

                            <td class="text-center mb-2" width="120">
                                @if(!empty($student->id))
                                    {{-- null id => Gift recipient who has not registered yet --}}
                                    @can('admin_users_impersonate')
                                        <a href="{{ getAdminPanelUrl() }}/users/{{ $student->id }}/impersonate" target="_blank" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.login') }}">
                                            <i class="fa fa-user-shield"></i>
                                        </a>
                                    @endcan

                                    @can('admin_users_edit')
                                        <a href="{{ getAdminPanelUrl() }}/users/{{ $student->id }}/edit" class="btn-transparent  text-primary" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/main.edit') }}">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    @endcan

                                  <!--  @can('admin_webinar_students_delete')
                                        @if(!$student->access_to_purchased_item)
                                            @include('admin.includes.delete_button',[
                                                'url' => getAdminPanelUrl().'/enrollments/'. $student->sale_id .'/enable-access',
                                                'tooltip' => trans('update.enable-student-access'),
                                                'btnIcon' => 'fa-check'
                                            ])
                                        @else
                                            @include('admin.includes.delete_button',[
                                                        'url' => getAdminPanelUrl().'/enrollments/'. $student->sale_id .'/block-access',
                                                        'tooltip' => trans('update.block_access'),
                                                    ])
                                        @endif
                                    @endcan -->
                                @endif
                            </td>

                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="card-footer text-center">
            {{ $students->appends(request()->input())->links('pagination::bootstrap-4') }}
        </div>

    </div>

    <section class="card">
        <div class="card-body">
            <div class="section-title ml-0 mt-0 mb-3"><h5>{{trans('admin/main.hints')}}</h5></div>
            <div class="row">
                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_1')}}</div>
                        <div class=" text-small font-600-bold">{{trans('admin/main.students_hint_description_1')}}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_2')}}</div>
                        <div class=" text-small font-600-bold">{{trans('admin/main.students_hint_description_2')}}</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="media-body">
                        <div class="text-primary mt-0 mb-1 font-weight-bold">{{trans('admin/main.students_hint_title_3')}}</div>
                        <div class="text-small font-600-bold">{{trans('admin/main.students_hint_description_3')}}</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <div id="addStudentToCourseModal" class="d-none">
        <h3 class="section-title after-line">{{ trans('update.add_student_to_course') }}</h3>
        <div class="mt-25">
            <form action="{{ getAdminPanelUrl() }}/enrollments/store" method="post">
                <input type="hidden" name="webinar_id" value="{{ $webinar->id }}">

                <div class="form-group">
                    <label class="input-label d-block">{{ trans('admin/main.user') }}</label>
                    <select name="user_id" class="form-control user-search" data-placeholder="{{ trans('public.search_user') }}">

                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="d-flex align-items-center justify-content-end mt-3">
                    <button type="button" class="js-save-manual-add btn btn-sm btn-primary">{{ trans('public.save') }}</button>
                    <button type="button" class="close-swl btn btn-sm btn-danger ml-2">{{ trans('public.close') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Progress Detail Modal -->
    <div class="modal fade" id="progressDetailModal" tabindex="-1" role="dialog" aria-labelledby="progressDetailModalLabel" aria-hidden="true" style="z-index: 1060 !important;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="mb-20">
                        <h5 class="font-18 font-weight-bold text-dark-blue mb-1">Subscription Progress Breakdown</h5>
                        <p class="text-gray font-12 mb-0" id="modalCourseTitle">Loading subscription details...</p>
                    </div>

                    <div id="progressDetailModalLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3 text-gray font-14">Fetching detailed progress...</p>
                    </div>

                    <div id="progressDetailModalContent" class="d-none overflow-auto" style="max-height: 450px;">
                        <div id="chaptersContainer"></div>
                    </div>

                    <div id="progressDetailModalError" class="d-none text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                        <p class="text-dark-blue font-14 font-weight-500" id="modalErrorMessage">Failed to load progress details.</p>
                        <button class="btn btn-sm btn-outline-primary mt-3 px-4" onclick="location.reload()">Retry</button>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger px-4" data-dismiss="modal" style="border-radius: 12px; font-weight: 700;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Progress Modal -->
    <div class="modal fade" id="exportProgressModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content text-center py-4 px-3" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative;">
                
                {{-- Minimize Button --}}
                <button type="button" class="btn btn-sm btn-light position-absolute minimize-export-modal" style="top: 15px; right: 15px; border-radius: 50%; width: 32px; height: 32px; padding: 0; line-height: 1;">
                    <i class="fas fa-minus text-secondary"></i>
                </button>

                <div class="modal-body">
                    <h5 class="text-dark-blue font-weight-bold mb-3" id="exportModalTitle">Exporting Students List</h5>
                    <p class="text-gray mb-4 font-14" id="exportProgressText">Please wait while we process the records. Do not close this window.</p>
                    
                    <div id="exportProgressLoading" class="mb-4">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    
                    <div id="exportProgressBarContainer" class="progress mb-2 d-none" style="height: 12px; border-radius: 10px; background-color: #f0f0f0;">
                        <div id="exportProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="exportProgressPercentage" class="text-primary font-weight-bold font-14 d-none">0%</div>

                    <div id="exportProgressError" class="d-none mt-3 p-3 rounded" style="background-color: #fff3f3; border: 1px solid #ffdcdc;">
                        <i class="fas fa-exclamation-triangle text-danger font-24 mb-2"></i>
                        <p class="text-danger font-14 mb-0" id="exportErrorMessage">Failed to export data.</p>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-3" data-dismiss="modal">Close</button>
                    </div>

                    <div id="exportProgressSuccess" class="d-none mt-3 p-3 rounded" style="background-color: #f3fff6; border: 1px solid #dcffe4;">
                        <i class="fas fa-check-circle text-success font-24 mb-2"></i>
                        <p class="text-success font-14 mb-0">Export completed successfully! Your download will begin shortly.</p>
                        <button type="button" class="btn btn-sm btn-outline-success mt-3" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/select2/select2.min.js"></script>

    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
    </script>

    {{-- <script src="/assets/default/js/admin/webinar_students.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            $('body').on('click', '.trigger-progress-modal', function(e) {
                e.preventDefault();
                var studentId = $(this).data('student-id');
                var subscriptionId = '{{ $webinar->id }}';
                
                $('#progressDetailModal').modal('show');
                $('#progressDetailModalLoading').removeClass('d-none');
                $('#progressDetailModalContent').addClass('d-none');
                $('#progressDetailModalError').addClass('d-none');
                $('#chaptersContainer').html('');

                $.ajax({
                    url: '/admin/subscriptions/' + subscriptionId + '/students/' + studentId + '/progress',
                    type: 'GET',
                    success: function(response) {
                        $('#progressDetailModalLoading').addClass('d-none');
                        $('#progressDetailModalContent').removeClass('d-none');
                        $('#modalCourseTitle').text(response.title);

                        var html = '';
                        if (response.webinars && response.webinars.length > 0) {
                            response.webinars.forEach(function(webinar) {
                                html += '<div class="mb-4 text-left">';
                                html += '    <h6 class="font-16 font-weight-bold text-primary mb-3 text-left" style="border-bottom: 2px solid #f0f0f0; padding-bottom: 8px;">' + webinar.title + '</h6>';
                                
                                if (webinar.chapters && webinar.chapters.length > 0) {
                                    webinar.chapters.forEach(function(chapter) {
                                        html += '<div class="mb-3 ml-2">';
                                        html += '    <h6 class="font-14 font-weight-bold text-dark-blue mb-2 text-left">' + chapter.title + '</h6>';
                                        
                                        chapter.items.forEach(function(item) {
                                            var percentage = parseInt(item.percentage);
                                            var barClass = percentage >= 100 ? 'bg-primary' : 'bg-gray';
                                            if (barClass === 'bg-gray') barClass = 'bg-secondary';

                                            html += '<div class="progress-item-container">';
                                            html += '    <div class="d-flex align-items-center justify-content-between mb-1">';
                                            html += '        <span class="font-12 text-dark-blue">' + item.title + '</span>';
                                            html += '        <span class="font-11 font-weight-bold ' + (percentage >= 100 ? 'text-primary' : 'text-gray') + '">' + percentage + '%</span>';
                                            html += '    </div>';
                                            html += '    <div class="progress custom-modal-progress">';
                                            html += '        <div class="progress-bar ' + barClass + '" role="progressbar" style="width: ' + percentage + '%" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100"></div>';
                                            html += '    </div>';
                                            html += '</div>';
                                        });
                                        html += '</div>';
                                    });
                                } else {
                                    html += '<p class="text-center text-gray py-2">No content found for this webinar.</p>';
                                }
                                html += '</div>';
                            });
                        } else {
                            html = '<p class="text-center text-gray py-4">No webinars found for this subscription.</p>';
                        }
                        $('#chaptersContainer').html(html);
                    },
                    error: function(xhr) {
                        $('#progressDetailModalLoading').addClass('d-none');
                        $('#progressDetailModalError').removeClass('d-none');
                        var msg = 'Failed to load progress details.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                        $('#modalErrorMessage').text(msg);
                    }
                });
            });

            // --- Multi-Export Tray Logic ---
            window.activeExports = window.activeExports || {};
            var currentOpenExportId = null;

            function updateExportTray() {
                var exportKeys = Object.keys(window.activeExports);
                var trayWrapper = $('#exportDownloadsTray');
                var trayList = $('#exportDownloadsList');
                
                if (exportKeys.length === 0) {
                    trayWrapper.hide();
                    return;
                }
                
                trayWrapper.show();
                trayList.empty();
                
                var hasActive = false;
                
                exportKeys.forEach(function(exportId) {
                    var exp = window.activeExports[exportId];
                    var statusIcon = '<i class="fas fa-spinner fa-spin text-primary"></i>';
                    var statusColor = 'bg-primary';
                    var statusText = exp.percentage + '%';
                    
                    if (exp.status === 'completed') {
                        statusIcon = '<i class="fas fa-check text-success"></i>';
                        statusColor = 'bg-success';
                        statusText = 'Done';
                    } else if (exp.status === 'error') {
                        statusIcon = '<i class="fas fa-exclamation-triangle text-danger"></i>';
                        statusColor = 'bg-danger';
                        statusText = 'Failed';
                    } else {
                        hasActive = true; 
                    }

                    var itemHtml = `
                        <a href="javascript:void(0)" class="dropdown-item resume-export" data-export-id="${exportId}">
                            <div class="dropdown-item-icon ${statusColor} text-white d-flex align-items-center justify-content-center">
                                ${statusIcon}
                            </div>
                            <div class="dropdown-item-desc">
                                ${exp.title}
                                <div class="time text-dark">${statusText} ${exp.status === 'processing' ? `(${exp.processed}/${exp.total})` : ''}</div>
                                ${exp.status === 'completed' ? `<div class="time text-success mt-1"><i class="fas fa-download"></i> Click to download</div>` : ''}
                            </div>
                        </a>
                    `;
                    trayList.prepend(itemHtml);
                });
                
                // Toggle notification beep
                if (hasActive) {
                    $('#exportDownloadsTray .nav-link').addClass('beep');
                } else {
                    $('#exportDownloadsTray .nav-link').removeClass('beep');
                }
            }

            function syncModalWithExportState(exportId) {
                if (currentOpenExportId !== exportId) return;
                var exp = window.activeExports[exportId];
                if (!exp) return;

                $('#exportModalTitle').text(exp.title);
                
                if (exp.status === 'initializing') {
                    $('#exportProgressLoading').removeClass('d-none');
                    $('#exportProgressBarContainer, #exportProgressPercentage, #exportProgressError, #exportProgressSuccess').addClass('d-none');
                    $('#exportProgressText').text('Initializing export...');
                    $('#exportProgressBar').css('width', '0%');
                    $('#exportProgressPercentage').text('0%');
                } 
                else if (exp.status === 'processing') {
                    $('#exportProgressLoading, #exportProgressError, #exportProgressSuccess').addClass('d-none');
                    $('#exportProgressBarContainer, #exportProgressPercentage').removeClass('d-none');
                    
                    $('#exportProgressText').text('Processing ' + exp.processed + ' of ' + exp.total + ' records...');
                    $('#exportProgressBar').removeClass('bg-success').addClass('bg-primary').css('width', exp.percentage + '%');
                    $('#exportProgressPercentage').removeClass('text-success').addClass('text-primary').text(exp.percentage + '%');
                }
                else if (exp.status === 'completed') {
                    $('#exportProgressLoading, #exportProgressBarContainer, #exportProgressPercentage, #exportProgressError').addClass('d-none');
                    $('#exportProgressSuccess').removeClass('d-none');
                    $('#exportProgressText').text('Export complete.');
                }
                else if (exp.status === 'error') {
                    $('#exportProgressLoading, #exportProgressBarContainer, #exportProgressPercentage, #exportProgressSuccess').addClass('d-none');
                    $('#exportProgressError').removeClass('d-none');
                    $('#exportErrorMessage').text(exp.errorMsg || 'Failed to export data.');
                    $('#exportProgressText').text('Export failed.');
                }
            }

            // Click Minimize Button
            $('body').on('click', '.minimize-export-modal', function() {
                $('#exportProgressModal').modal('hide');
                currentOpenExportId = null; 
            });

            // Click Resume from Tray
            $('body').on('click', '.resume-export', function() {
                var exportId = $(this).data('export-id');
                var exp = window.activeExports[exportId];
                
                if (exp.status === 'completed' && !exp.downloaded) {
                    exp.downloaded = true;
                    window.location.href = exp.downloadUrl;
                }
                
                currentOpenExportId = exportId;
                syncModalWithExportState(exportId);
                $('#exportProgressModal').modal('show');
            });


            // --- Core Export Logic ---
            $('body').on('click', '.trigger-export-modal', function(e) {
                e.preventDefault();
                var subscriptionId = '{{ $webinar->id }}';
                var filters = '{{ http_build_query(request()->all()) }}';
                var exportId = 'export_' + subscriptionId + '_' + Date.now();
                var title = 'Subscription ' + subscriptionId + ' Students';
                
                // Initialize State
                window.activeExports[exportId] = {
                    id: exportId,
                    title: title,
                    status: 'initializing',
                    percentage: 0,
                    processed: 0,
                    total: 0,
                    downloaded: false
                };
                
                currentOpenExportId = exportId;
                syncModalWithExportState(exportId);
                updateExportTray();
                $('#exportProgressModal').modal('show');

                // Step 1: Initialize Export
                $.ajax({
                    url: '/admin/subscriptions/' + subscriptionId + '/students/export-init?' + filters,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var exp = window.activeExports[exportId];
                            exp.total = response.total_records;
                            var chunkSize = response.chunk_size;
                            
                            if (exp.total === 0) {
                                exp.status = 'error';
                                exp.errorMsg = 'No records found to export.';
                                syncModalWithExportState(exportId);
                                updateExportTray();
                                setTimeout(function() { $('#exportProgressModal').modal('hide'); }, 2000);
                                return;
                            }

                            exp.status = 'processing';
                            syncModalWithExportState(exportId);
                            updateExportTray();
                            
                            processExportChunk(exportId, subscriptionId, filters, 0, chunkSize);
                        }
                    },
                    error: function(xhr) {
                        handleExportError(exportId, xhr);
                    }
                });
            });

            function processExportChunk(exportId, subscriptionId, filters, offset, limit) {
                var exp = window.activeExports[exportId];
                if (!exp) return;

                $.ajax({
                    url: '/admin/subscriptions/' + subscriptionId + '/students/export-process?' + filters + '&offset=' + offset + '&limit=' + limit,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var newOffset = offset + limit;
                            exp.processed = Math.min(newOffset, exp.total);
                            exp.percentage = Math.min(Math.round((exp.processed / exp.total) * 100), 100);
                            
                            syncModalWithExportState(exportId);
                            updateExportTray();

                            if (newOffset < exp.total) {
                                // Process next chunk
                                processExportChunk(exportId, subscriptionId, filters, newOffset, limit);
                            } else {
                                // All chunks processed, trigger download
                                finishExport(exportId, subscriptionId);
                            }
                        }
                    },
                    error: function(xhr) {
                        handleExportError(exportId, xhr);
                    }
                });
            }

            function finishExport(exportId, subscriptionId) {
                var exp = window.activeExports[exportId];
                if (!exp) return;

                exp.status = 'completed';
                exp.percentage = 100;
                exp.downloadUrl = '/admin/subscriptions/' + subscriptionId + '/students/export-download';
                
                syncModalWithExportState(exportId);
                updateExportTray();

                // Trigger actual file download automatically once
                if(currentOpenExportId === exportId) {
                    exp.downloaded = true;
                    window.location.href = exp.downloadUrl;
                    setTimeout(function() {
                        if(currentOpenExportId === exportId) $('#exportProgressModal').modal('hide');
                    }, 3000);
                }
            }

            function handleExportError(exportId, xhr) {
                var exp = window.activeExports[exportId];
                if (!exp) return;

                exp.status = 'error';
                var msg = 'An error occurred during export.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                exp.errorMsg = msg;

                syncModalWithExportState(exportId);
                updateExportTray();
            }
        });
    </script>
@endpush
