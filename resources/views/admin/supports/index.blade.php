@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Support Tickets Management</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item">Support Tickets</div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total</h4>
                        </div>
                        <div class="card-body">{{ $stats['total'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Pending</h4>
                        </div>
                        <div class="card-body">{{ $stats['pending'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-info">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>In Review</h4>
                        </div>
                        <div class="card-body">{{ $stats['in_review'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Approved</h4>
                        </div>
                        <div class="card-body">{{ $stats['approved'] }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Rejected</h4>
                        </div>
                        <div class="card-body">{{ $stats['rejected'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-body">
            <section class="card">
                <div class="card-body">
                    <form method="get" class="mb-0">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">Search</label>
                                    <input type="text" name="search" value="{{ request()->get('search') }}" class="form-control" placeholder="Ticket # or Title">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">Status</label>
                                    <select name="status" data-plugin-selectTwo class="form-control populate">
                                        <option value="">All Status</option>
                                        <option value="pending" {{ request()->get('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_review" {{ request()->get('status') == 'in_review' ? 'selected' : '' }}>In Review</option>
                                        <option value="approved" {{ request()->get('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ request()->get('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="executed" {{ request()->get('status') == 'executed' ? 'selected' : '' }}>Executed</option>
                                        <option value="closed" {{ request()->get('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">Scenario</label>
                                    <select name="scenario" data-plugin-selectTwo class="form-control populate">
                                        <option value="">All Scenarios</option>
                                        <option value="course_extension" {{ request()->get('scenario') == 'course_extension' ? 'selected' : '' }}>Course Extension</option>
                                        <option value="temporary_access" {{ request()->get('scenario') == 'temporary_access' ? 'selected' : '' }}>Temporary Access</option>
                                        <option value="mentor_access" {{ request()->get('scenario') == 'mentor_access' ? 'selected' : '' }}>Mentor Access</option>
                                        <option value="refund_payment" {{ request()->get('scenario') == 'refund_payment' ? 'selected' : '' }}>Refund Payment</option>
                                        <option value="offline_cash_payment" {{ request()->get('scenario') == 'offline_cash_payment' ? 'selected' : '' }}>Offline Payment</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">Date</label>
                                    <input type="date" class="form-control" name="date" value="{{ request()->get('date') }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mt-1">
                                    <label class="input-label mb-4"> </label>
                                    <input type="submit" class="text-center btn btn-primary w-100" value="Show Results">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </section>

            <section class="card">
                <div class="card-body">
                    <div class="table-responsive text-center">
                        <table class="table table-striped font-14">
                            <tr>
                                <th>Ticket #</th>
                                <th class="text-left">Title</th>
                                <th class="text-center" style="width: 200px;">Category</th>
                                <th class="text-center" style="width: 150px;">Priority</th>
                                <th class="text-center">Created Date</th>
                                <th class="text-left">User</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>

                            @foreach($supportRequests as $request)
                            <tr data-ticket="{{ $request->id }}">
                                <td>
                                    <a href="{{ route('admin.support.show', $request->id) }}">
                                        <strong class="text-primary">{{ $request->ticket_number }}</strong>
                                    </a>
                                </td>

                                <td class="text-left">{{ $request->title }}</td>

                                <td class="text-center">
                                    <select class="form-control form-control-sm quick-update-select" data-field="scenario" style="border-radius: 8px; font-weight: 600; font-size: 11px; height: 32px; border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <option value="">Awaiting</option>
                                        <option value="course_extension" {{ $request->support_scenario == 'course_extension' ? 'selected' : '' }}>Extension</option>
                                        <option value="temporary_access" {{ $request->support_scenario == 'temporary_access' ? 'selected' : '' }}>Temp Access</option>
                                        <option value="mentor_access" {{ $request->support_scenario == 'mentor_access' ? 'selected' : '' }}>Mentor</option>
                                        <option value="refund_payment" {{ $request->support_scenario == 'refund_payment' ? 'selected' : '' }}>Refund</option>
                                        <option value="offline_cash_payment" {{ $request->support_scenario == 'offline_cash_payment' ? 'selected' : '' }}>Cash/Offline</option>
                                        <option value="installment_restructure" {{ $request->support_scenario == 'installment_restructure' ? 'selected' : '' }}>EMI Restruct</option>
                                        <option value="mentor_change" {{ $request->support_scenario == 'mentor_change' ? 'selected' : '' }}>Mentor Change</option>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <select class="form-control form-control-sm quick-update-select" data-field="priority" style="border-radius: 8px; font-weight: 600; font-size: 11px; height: 32px; border: 1px solid #e2e8f0; background: #f8fafc;">
                                        <option value="low" {{ $request->priority == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ $request->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ $request->priority == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ $request->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </td>

                                <td class="text-center">{{ \Carbon\Carbon::parse($request->created_at)->format('j M Y | H:i') }}</td>

                                <td class="text-left">
                                    @if($request->user)
                                        <a href="{{ $request->user->getProfileUrl() }}" target="_blank">
                                            {{ $request->user->full_name }}
                                        </a>
                                    @else
                                        {{ $request->guest_name }}<br>
                                        <small class="text-muted">{{ $request->guest_email }}</small>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if($request->status == 'pending')
                                        <span class="text-warning font-weight-bold">Pending</span>
                                    @elseif($request->status == 'in_review')
                                        <span class="text-info font-weight-bold">In Review</span>
                                    @elseif($request->status == 'approved')
                                        <span class="text-success font-weight-bold">Approved</span>
                                    @elseif($request->status == 'rejected')
                                        <span class="text-danger font-weight-bold">Rejected</span>
                                    @else
                                        <span class="text-primary font-weight-bold">{{ ucfirst($request->status) }}</span>
                                    @endif
                                </td>

                                <td class="text-center" width="100">
                                    <a href="{{ route('admin.support.show', $request->id) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       data-toggle="tooltip" 
                                       data-placement="top" 
                                       title="View Details">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    {{ $supportRequests->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
            </section>
        </div>
    </section>
@endsection

@push('scripts_bottom')
<script>
    $(document).ready(function() {
        $('.quick-update-select').on('change', function() {
            var $this = $(this);
            var ticketId = $this.closest('tr').data('ticket');
            var field = $this.data('field');
            var value = $this.val();
            var data = {};
            data[field] = value;
            data['_token'] = '{{ csrf_token() }}';

            $this.css('opacity', '0.5').prop('disabled', true);

            $.post('{{ getAdminPanelUrl() }}/support/' + ticketId + '/quick-update', data, function(response) {
                if (response.status === 'success') {
                    iziToast.success({
                        title: 'Success',
                        message: response.message,
                        position: 'topRight'
                    });
                } else {
                    iziToast.error({
                        title: 'Error',
                        message: response.message,
                        position: 'topRight'
                    });
                }
            }).fail(function() {
                iziToast.error({
                    title: 'Error',
                    message: 'Failed to update ticket',
                    position: 'topRight'
                });
            }).always(function() {
                $this.css('opacity', '1').prop('disabled', false);
            });
        });
    });
</script>
@endpush
