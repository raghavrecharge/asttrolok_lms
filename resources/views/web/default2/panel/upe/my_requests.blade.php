@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class="section-title">My Requests</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="table-responsive">
                <table class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th class="text-left">Product</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>{{ $req->id }}</td>
                                <td>
                                    <span class="badge badge-circle-white font-12 px-5 py-2">
                                        {{ ucfirst(str_replace('_',' ',$req->request_type)) }}
                                    </span>
                                </td>
                                <td class="text-left">
                                    @if($req->sale && $req->sale->product)
                                        {{ $req->sale->product->name }}
                                    @elseif($req->sale_id)
                                        Sale #{{ $req->sale_id }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($req->status) {
                                            'pending' => 'badge-warning',
                                            'verified' => 'badge-info',
                                            'approved' => 'badge-primary',
                                            'executed' => 'badge-primary',
                                            'rejected' => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                        $statusText = match($req->status) {
                                            'pending' => 'Under Review',
                                            'verified' => 'Verified',
                                            'approved' => 'Approved',
                                            'executed' => 'Completed',
                                            'rejected' => 'Rejected',
                                            default => ucfirst($req->status),
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} px-10 py-5">{{ $statusText }}</span>
                                </td>
                                <td class="font-12">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y H:i') }}</td>
                                <td>
                                    @if($req->status === 'rejected' && $req->rejected_reason)
                                        <span class="text-danger font-12" title="{{ $req->rejected_reason }}">
                                            <i class="fa fa-info-circle"></i> {{ \Illuminate\Support\Str::limit($req->rejected_reason, 30) }}
                                        </span>
                                    @elseif($req->status === 'executed' && $req->execution_result)
                                        <span class="text-primary font-12"><i class="fa fa-check"></i> Done</span>
                                    @elseif($req->payload)
                                        @if(!empty($req->payload['amount']))
                                            <span class="font-12">₹{{ number_format($req->payload['amount'], 2) }}</span>
                                        @endif
                                        @if(!empty($req->payload['reason']))
                                            <span class="font-12 text-gray ml-5">{{ \Illuminate\Support\Str::limit($req->payload['reason'], 30) }}</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-gray py-20">No requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-15">
                {{ $requests->links() }}
            </div>
        </div>
    </section>
@endsection
