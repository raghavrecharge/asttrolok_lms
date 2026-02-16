@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">Dashboard</a></div>
                <div class="breadcrumb-item">UPE Audit Log</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header"><h4>Filters</h4></div>
                <div class="card-body">
                    <form method="get" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Action</label>
                                <input type="text" name="action" class="form-control" value="{{ request('action') }}" placeholder="e.g. sale.created">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Entity Type</label>
                                <select name="entity_type" class="form-control">
                                    <option value="">All</option>
                                    @foreach(['sale','ledger_entry','discount','adjustment','subscription','referral','payment_request','installment_plan'] as $et)
                                        <option value="{{ $et }}" {{ request('entity_type') == $et ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$et)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Actor ID</label>
                                <input type="number" name="actor_id" class="form-control" value="{{ request('actor_id') }}" placeholder="User ID">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Actor</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Changes</th>
                                    <th>IP</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>
                                            @if($log->actor_id)
                                                <a href="{{ getAdminPanelUrl() }}/users/{{ $log->actor_id }}/edit" target="_blank">#{{ $log->actor_id }}</a>
                                            @else
                                                System
                                            @endif
                                        </td>
                                        <td><span class="badge badge-light">{{ $log->actor_role }}</span></td>
                                        <td><code class="small">{{ $log->action }}</code></td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $log->entity_type }}</span>
                                            #{{ $log->entity_id }}
                                        </td>
                                        <td>
                                            @if($log->old_state || $log->new_state)
                                                <button class="btn btn-xs btn-light" data-toggle="popover" data-html="true" data-trigger="click"
                                                    data-content="<strong>Old:</strong><pre class='small'>{{ json_encode($log->old_state, JSON_PRETTY_PRINT) }}</pre><strong>New:</strong><pre class='small'>{{ json_encode($log->new_state, JSON_PRETTY_PRINT) }}</pre>">
                                                    <i class="fas fa-code"></i>
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="small">{{ $log->ip_address ?? '-' }}</td>
                                        <td class="small">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No audit log entries</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $logs->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
<script>
    $(function(){ $('[data-toggle="popover"]').popover({ placement: 'left', container: 'body' }); });
</script>
@endpush
