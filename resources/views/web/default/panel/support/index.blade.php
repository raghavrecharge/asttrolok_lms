@extends(getTemplate() .'.panel.layouts.panel_layout')
@push('styles_top')
    <style>
        .stat-card {
            background: #fff;
            border-radius: 18px;
            padding: 18px 15px;
            display: flex;
            align-items: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            border-color: #43d477;
        }
        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .stat-icon i {
            width: 20px;
            height: 20px;
        }
        .stat-label {
            font-size: 12px;
            color: #8c98a4;
            font-weight: 600;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: 800;
            color: #1f3b64;
            display: block;
            line-height: 1.2;
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.08); color: #1f3b64; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.08); color: #ffc107; }
        .bg-glass-info { background: rgba(0, 123, 255, 0.08); color: #007bff; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.08); color: #2ecc71; }
        .bg-glass-danger { background: rgba(246, 59, 59, 0.08); color: #f63b3b; }

        .premium-table-container {
            background: #fff;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f8f8f8;
        }
        .custom-table {
            min-width: 700px;
        }
        .custom-table thead th {
            background: #f8faff;
            border: none;
            padding: 12px 10px;
            font-size: 12px;
            font-weight: 700;
            color: #1f3b64;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .custom-table tbody td {
            padding: 16px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f4f4f4;
            font-size: 14px;
            color: #1f3b64;
        }
        .custom-table .col-title { max-width: 200px; }
        .custom-table .col-scenario { max-width: 160px; }
        .custom-table .col-action { white-space: nowrap; }
        .status-badge {
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between">
            <h2 class="section-title">My Support Tickets</h2>
            <a href="{{ route('newsuportforasttrolok.create') }}" class="btn btn-primary d-flex align-items-center">
                <i data-feather="plus-circle" width="18" height="18" class="mr-5"></i>
                New Ticket
            </a>
        </div>

        {{-- Statistics --}}
        <div class="mt-25">
            <div class="row stat-card-row" style="margin-left: -10px; margin-right: -10px;">
                <div class="col-6 col-md-4 col-lg-2 mb-20 mb-lg-0" style="padding: 0 10px;">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="box"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                            <span class="stat-label">Total</span>
                        </div>
                    </div>
                </div>

                {{-- Pending --}}
                <div class="col-6 col-md-4 col-lg mb-20 mb-lg-0" style="padding: 0 10px;">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="clock" style="stroke-width: 3px;"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['pending'] }}</span>
                            <span class="stat-label">Pending</span>
                        </div>
                    </div>
                </div>

                {{-- Approved --}}
                <div class="col-6 col-md-4 col-lg mb-20 mb-lg-0" style="padding: 0 10px;">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="check-circle" style="stroke-width: 3px;"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['approved'] }}</span>
                            <span class="stat-label">Approved</span>
                        </div>
                    </div>
                </div>

                {{-- Completed --}}
                <div class="col-6 col-md-4 col-lg mb-20 mb-lg-0" style="padding: 0 10px;">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-info">
                            <i data-feather="check-square" style="stroke-width: 3px;"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['completed'] }}</span>
                            <span class="stat-label">Completed</span>
                        </div>
                    </div>
                </div>
                
                {{-- Rejected --}}
                <div class="col-6 col-md-4 col-lg mb-20 mb-lg-0" style="padding: 0 10px;">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-danger">
                            <i data-feather="x-circle" style="stroke-width: 3px;"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['rejected'] }}</span>
                            <span class="stat-label">Rejected</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="mt-20" style="background: linear-gradient(135deg, #f8faff 0%, #fff 100%); border-radius: 20px; border: 1px solid #e8edf5; padding: 22px 28px; box-shadow: 0 4px 24px rgba(31,59,100,0.06);">
            <form action="/panel/newsuportforasttrolok" method="get">
                <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px;">

                    {{-- From --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.from') }}
                        </label>
                        <div style="position:relative;width:150px;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="from" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('from'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('from','') }}"/>
                        </div>
                    </div>

                    {{-- To --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="calendar" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.to') }}
                        </label>
                        <div style="position:relative;width:150px;">
                            <div style="position:absolute;left:0;top:0;bottom:0;width:38px;background:#1f3b64;display:flex;align-items:center;justify-content:center;border-radius:9px 0 0 9px;z-index:1;">
                                <i data-feather="calendar" width="14" height="14" style="color:#fff;"></i>
                            </div>
                            <input type="text" name="to" autocomplete="off"
                                   class="form-control @if(!empty(request()->get('to'))) datepicker @else datefilter @endif"
                                   style="height:40px;padding-left:48px;font-size:12px;font-weight:600;color:#1f3b64;border:1.5px solid #e8edf5;border-radius:9px;box-shadow:0 2px 6px rgba(31,59,100,0.06);background:#fff;"
                                   value="{{ request()->get('to','') }}"/>
                        </div>
                    </div>

                    {{-- Course --}}
                    <div style="flex:1 1 200px;min-width:180px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="book" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('product.course') }}
                        </label>
                        <div style="position:relative;">
                            <select name="webinar_id" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($userPurchasedCourses as $course)
                                    <option value="{{ $course->id }}" @if(request()->get('webinar_id') == $course->id) selected @endif>{{ $course->title }}</option>
                                @endforeach
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Scenario --}}
                    <div style="flex:1 1 180px;min-width:160px;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="help-circle" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.scenario') }}
                        </label>
                        <div style="position:relative;">
                            <select name="support_scenario" class="select2" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;">
                                <option value="all">{{ trans('public.all') }}</option>
                                @foreach($scenarios as $scen)
                                    <option value="{{ $scen->slug }}" @if(request()->get('support_scenario') == $scen->slug) selected @endif>{{ $scen->title }}</option>
                                @endforeach
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div style="flex:0 0 auto;">
                        <label style="font-size:10px;font-weight:700;color:#8c98a4;text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;display:block;">
                            <i data-feather="sliders" width="11" height="11" style="vertical-align:middle;margin-right:3px;"></i> {{ trans('public.status') }}
                        </label>
                        <div style="position:relative;width:110px;">
                            <select name="status" style="width:100%;height:40px;border:1.5px solid #e8edf5;border-radius:9px;padding:0 30px 0 12px;font-size:12px;font-weight:600;color:#1f3b64;background:#fff;box-shadow:0 2px 6px rgba(31,59,100,0.06);appearance:none;-webkit-appearance:none;cursor:pointer;">
                                <option value="all">{{ trans('public.all') }}</option>
                                <option value="pending" {{ request()->get('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_review" {{ request()->get('status') === 'in_review' ? 'selected' : '' }}>In Review</option>
                                <option value="approved" {{ request()->get('status') === 'approved' ? 'selected' : '' }}>Complete</option>
                                <option value="rejected" {{ request()->get('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <div style="position:absolute;right:9px;top:50%;transform:translateY(-50%);pointer-events:none;color:#8c98a4;">
                                <i data-feather="chevron-down" width="13" height="13"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div style="flex:0 0 auto;">
                        <button type="submit" style="height:40px;background:linear-gradient(135deg,#43d477 0%,#2ecc71 100%);border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 14px rgba(67,212,119,0.25);white-space:nowrap;padding:0 20px;transition:all .2s;" onmouseover="this.style.boxShadow='0 6px 18px rgba(67,212,119,0.35)'" onmouseout="this.style.boxShadow='0 4px 14px rgba(67,212,119,0.25)'">
                            <i data-feather="search" width="13" height="13"></i>
                            {{ trans('public.show_results') }}
                        </button>
                    </div>

                </div>
            </form>
        </div> -->

        <div class="mt-30 premium-table-container">
            @if($supportRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table text-center custom-table">
                        <thead>
                            <tr>
                                <th class="d-none d-md-table-cell">Ticket #</th>
                                <th class="text-left col-title">Course</th>
                                <th class="text-left col-scenario d-none d-lg-table-cell">Scenario</th>
                                <th class="text-center col-scenario">Status</th>
                                <th class="text-center col-scenario d-none d-md-table-cell">Date</th>
                                <th class="col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportRequests as $request)
                            <tr>
                                <td class="d-none d-md-table-cell">
                                    <span class="font-weight-bold">#{{ $request->ticket_number }}</span>
                                </td>
                                <td class="text-left col-title">
                                    <div class="d-flex flex-column">
                                        @if($request->webinar)
                                            <span class="font-12 text-gray">{{ $request->webinar->title }}</span>
                                        @else
                                            <span class="font-12 text-gray">General Support</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-left col-scenario d-none d-lg-table-cell">
                                    <span class="font-13 text-gray">{{ $request->getScenarioLabel() }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $badgeClass = 'bg-glass-primary';
                                        if($request->status == 'pending') $badgeClass = 'bg-glass-warning';
                                        elseif($request->status == 'approved' || $request->status == 'executed') $badgeClass = 'bg-glass-success';
                                        elseif($request->status == 'rejected') $badgeClass = 'bg-glass-danger';
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $request->status == 'approved' || $request->status == 'executed' ? 'Approved' : ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="d-block font-13 font-weight-bold">{{ $request->created_at->format('d M Y') }}</span>
                                    <small class="text-gray">{{ $request->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="col-action">
                                    <a href="{{ route('newsuportforasttrolok.show', $request->ticket_number) }}" 
                                       class="btn btn-sm btn-border-white">
                                        View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-20">
                    {{ $supportRequests->appends(request()->input())->links('vendor.pagination.panel') }}
                </div>
            @else
                <div class="no-result text-center p-30">
                    <div class="no-result-icon mb-20">
                        <i data-feather="send" width="80" height="80" class="text-gray opacity-50"></i>
                    </div>
                    <h3 class="font-20 font-weight-bold text-dark-blue">No Support Tickets</h3>
                    <p class="text-gray mt-10">You haven't created any support tickets yet.</p>
                    <a href="{{ route('newsuportforasttrolok.create') }}" class="btn btn-primary mt-20">
                        Create Your First Ticket
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection