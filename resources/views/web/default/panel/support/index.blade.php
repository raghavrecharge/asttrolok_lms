@extends(getTemplate() .'.panel.layouts.panel_layout')
@push('styles_top')
    <style>
        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #f0f0f0;
            height: 100%;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            border-color: #1f3b64;
        }
        .stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .stat-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
            display: block;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 800;
            color: #1f3b64;
            display: block;
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.1); color: #1f3b64; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .bg-glass-info { background: rgba(0, 123, 255, 0.1); color: #007bff; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.1); color: #43d477; }

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
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-primary">
                            <i data-feather="box" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['total'] }}</span>
                            <span class="stat-label">Total</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-warning">
                            <i data-feather="clock" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['pending'] }}</span>
                            <span class="stat-label">Pending</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-20 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-info">
                            <i data-feather="eye" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['in_review'] }}</span>
                            <span class="stat-label">In Review</span>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3 mt-20 mt-md-0">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-success">
                            <i data-feather="check-circle" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $stats['approved'] }}</span>
                            <span class="stat-label">Approved</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-30 premium-table-container">
            @if($supportRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table text-center custom-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th class="text-left col-title">Title/Course</th>
                                <th class="text-left col-scenario">Scenario</th>
                                <th class="text-center col-scenario">Status</th>
                                <th class="text-center col-scenario">Date</th>
                                <th class="col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportRequests as $request)
                            <tr>
                                <td>
                                    <span class="font-weight-bold">#{{ $request->ticket_number }}</span>
                                </td>
                                <td class="text-left col-title">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark-blue">{{ $request->title }}</span>
                                        @if($request->webinar)
                                            <span class="font-12 text-gray"><i data-feather="book" width="12" height="12" class="mr-5"></i>{{ $request->webinar->title }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-left col-scenario">
                                    <span class="font-13 text-gray">{{ $request->getScenarioLabel() }}</span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = 'bg-primary text-white';
                                        if($request->status == 'pending') $badgeClass = 'bg-warning text-white';
                                        elseif($request->status == 'approved' || $request->status == 'executed') $badgeClass = 'bg-success text-white';
                                        elseif($request->status == 'rejected') $badgeClass = 'bg-danger text-white';
                                    @endphp
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td>
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
                    {{ $supportRequests->links() }}
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