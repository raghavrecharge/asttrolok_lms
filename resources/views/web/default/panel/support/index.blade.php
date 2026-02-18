@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">My Support Tickets</h2>
            <a href="{{ route('newsuportforasttrolok.create') }}" class="btn btn-sm btn-primary mt-3 mt-md-0">
                <i class="fa fa-plus mr-1"></i>
                New Ticket
            </a>
        </div>

        {{-- Statistics --}}
        <div class="row mt-4">
            <div class="col-sm-6 col-lg-3 mt-25 mt-lg-0">
                <div class="stats-item bg-white rounded p-15 d-flex align-items-center">
                    <div class="icon-box bg-primary">
                        <i class="fa fa-ticket text-white"></i>
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 font-weight-bold text-primary">{{ $stats['total'] }}</span>
                        <span class="font-16 text-gray">Total</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mt-25 mt-lg-0">
                <div class="stats-item bg-white rounded p-15 d-flex align-items-center">
                    <div class="icon-box bg-warning">
                        <i class="fa fa-clock text-white"></i>
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 font-weight-bold text-warning">{{ $stats['pending'] }}</span>
                        <span class="font-16 text-gray">Pending</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mt-25 mt-lg-0">
                <div class="stats-item bg-white rounded p-15 d-flex align-items-center">
                    <div class="icon-box bg-info" style="background-color: #70c3fa !important;">
                        <i class="fa fa-eye text-white"></i>
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 font-weight-bold text-info">{{ $stats['in_review'] }}</span>
                        <span class="font-16 text-gray">In Review</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 mt-25 mt-lg-0">
                <div class="stats-item bg-white rounded p-15 d-flex align-items-center">
                    <div class="icon-box bg-success" style="background-color: #4b9702 !important;" >
                        <i class="fa fa-check text-white"></i>
                    </div>
                    <div class="d-flex flex-column ml-15">
                        <span class="font-30 font-weight-bold text-success">{{ $stats['approved'] }}</span>
                        <span class="font-16 text-gray">Approved</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-white">
            @if($supportRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table text-center custom-table">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th class="text-left">Title</th>
                                <th class="text-left">Scenario</th>
                                <th class="text-left">Course</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($supportRequests as $request)
                            <tr>
                                <td>
                                    <span class="text-primary font-weight-500">{{ $request->ticket_number }}</span>
                                </td>
                                <td class="text-left">
                                    <span class="d-block">{{ $request->title }}</span>
                                </td>
                                <td class="text-left">
                                    <span class="d-block">{{ $request->getScenarioLabel() }}</span>
                                </td>
                                <td class="text-left">
                                    <span class="d-block font-weight-500">{{ $request->webinar?->title }}</span>
                                    <small class="text-gray">{{ $request->webinar?->creator?->full_name }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $request->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="d-block">{{ $request->created_at->format('d M Y') }}</span>
                                    <small class="text-gray">{{ $request->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('newsuportforasttrolok.show', $request->ticket_number) }}" 
                                       class="btn btn-sm btn-primary">View
                                        <!-- <i class="fa fa-eye"></i> -->
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $supportRequests->links() }}
                </div>
            @else
                <div class="no-result text-center p-5">
                    <div class="no-result-icon mb-3">
                        <i class="fa fa-ticket fa-5x text-gray"></i>
                    </div>
                    <h3 class="text-dark-blue">No Support Tickets</h3>
                    <p class="text-gray mt-3">You haven't created any support tickets yet.</p>
                    <a href="{{ route('newsuportforasttrolok.create') }}" class="btn btn-primary mt-3">
                        <i class="fa fa-plus mr-1"></i>
                        Create Your First Ticket
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('styles_top')
<style>
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .icon-box i {
        font-size: 24px;
    }
</style>
@endpush