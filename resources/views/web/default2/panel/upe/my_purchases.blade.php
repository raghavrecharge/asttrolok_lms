@extends(getTemplate() .'.panel.layouts.panel_layout')

@section('content')
    <section>
        <h2 class="section-title">My Purchases</h2>

        <div class="panel-section-card py-20 px-25 mt-20">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <form method="get" class="d-flex">
                        <select name="status" class="form-control mr-10">
                            <option value="">All Statuses</option>
                            @foreach(['active','pending_payment','completed','refunded','partially_refunded','expired'] as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <div class="row">
            @forelse($sales as $sale)
                <div class="col-12 col-lg-6 mt-15">
                    <div class="panel-section-card py-20 px-25">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <h3 class="font-16 font-weight-bold text-dark-blue">
                                    @if($sale->product)
                                        {{ $sale->product->name }}
                                    @else
                                        Product #{{ $sale->product_id }}
                                    @endif
                                </h3>
                                <span class="font-12 text-gray">
                                    @if($sale->product)
                                        <span class="badge badge-circle-white px-5 py-2 font-10">{{ ucfirst($sale->product->product_type) }}</span>
                                    @endif
                                    <span class="badge badge-circle-white px-5 py-2 font-10">{{ ucfirst($sale->pricing_mode) }}</span>
                                </span>
                            </div>
                            <div class="text-right">
                                @php
                                    $statusClass = match($sale->status) {
                                        'active' => 'badge-primary',
                                        'pending_payment' => 'badge-warning',
                                        'refunded' => 'badge-danger',
                                        'partially_refunded' => 'badge-warning',
                                        'completed' => 'badge-secondary',
                                        default => 'badge-secondary',
                                    };
                                    $hasEmiDue = $sale->pricing_mode === 'installment'
                                        && $sale->installmentPlan
                                        && $sale->installmentPlan->schedules->whereIn('status', ['due', 'partial', 'overdue', 'upcoming'])->count() > 0;
                                @endphp
                                @if($sale->status === 'active' && $hasEmiDue)
                                    <span class="badge badge-primary px-10 py-5">Active</span>
                                    <span class="badge badge-warning px-10 py-5 ml-5">EMI Due</span>
                                @elseif($sale->status === 'active' && $sale->pricing_mode === 'installment' && $sale->installmentPlan)
                                    <span class="badge badge-primary px-10 py-5">Fully Paid</span>
                                @else
                                    <span class="badge {{ $statusClass }} px-10 py-5">{{ ucfirst(str_replace('_',' ',$sale->status)) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-15 d-flex align-items-center justify-content-between">
                            @php
                                $coursePrice = $sale->base_fee_snapshot;
                                $amountPaid = $balances[$sale->id] ?? 0;
                                $isFree = in_array($sale->sale_type, ['free', 'trial']) || ($coursePrice == 0 && $amountPaid == 0);
                                $isInstallment = $sale->pricing_mode === 'installment' && $sale->installmentPlan;
                                $totalDue = $isInstallment ? $sale->installmentPlan->total_amount : $coursePrice;
                            @endphp
                            <div>
                                <span class="font-14 text-gray">Amount:</span>
                                <span class="font-16 font-weight-bold">
                                    @if($coursePrice > 0)
                                        ₹{{ number_format($coursePrice, 2) }}
                                    @else
                                        Free
                                    @endif
                                </span>
                            </div>
                            <div>
                                @if($isFree)
                                    <span class="badge badge-info px-10 py-5 font-12">Free Access</span>
                                @elseif($isInstallment)
                                    <span class="font-14 text-gray">Paid:</span>
                                    <span class="font-16 font-weight-bold text-primary">₹{{ number_format($amountPaid, 2) }}</span>
                                    <span class="font-12 text-gray">/ ₹{{ number_format($totalDue, 2) }}</span>
                                @else
                                    <span class="font-14 text-gray">Paid:</span>
                                    <span class="font-16 font-weight-bold text-primary">₹{{ number_format($amountPaid, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-10 d-flex align-items-center justify-content-between">
                            <div>
                                @if(isset($accessResults[$sale->id]))
                                    @if($accessResults[$sale->id]->hasAccess)
                                        <span class="text-primary font-12"><i class="fa fa-check-circle"></i> Access: {{ ucfirst($accessResults[$sale->id]->accessType) }}</span>
                                    @else
                                        <span class="text-danger font-12"><i class="fa fa-times-circle"></i> No Access</span>
                                    @endif
                                @endif
                            </div>
                            <div>
                                @if($sale->valid_until)
                                    <span class="font-12 text-gray">
                                        Valid until: {{ \Carbon\Carbon::parse($sale->valid_until)->format('d M Y') }}
                                        @if(\Carbon\Carbon::parse($sale->valid_until)->isPast())
                                            <span class="text-danger">(expired)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="font-12 text-gray">Lifetime access</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-15 d-flex">
                            <a href="/panel/upe/purchases/{{ $sale->id }}" class="btn btn-sm btn-primary mr-10">View Details</a>
                            @if($sale->product && in_array($sale->product->product_type, ['course_video', 'webinar', 'course_live']) && isset($accessResults[$sale->id]) && $accessResults[$sale->id]->hasAccess)
                                @php $webinarForLink = \App\Models\Webinar::find($sale->product->external_id); @endphp
                                @if($webinarForLink && $webinarForLink->slug)
                                    <a href="/course/learning/{{ $webinarForLink->slug }}" target="_blank" class="btn btn-sm btn-success mr-10">
                                        <i class="fa fa-play-circle"></i> Learning Page
                                    </a>
                                @endif
                            @elseif($sale->product && $sale->product->product_type === 'subscription' && $sale->item)
                                <a href="{{ $sale->item->getLearningPageUrl() }}" target="_blank" class="btn btn-sm btn-success mr-10">
                                    <i class="fa fa-play-circle"></i> Learning Page
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 mt-15">
                    <div class="panel-section-card py-40 px-25 text-center">
                        <p class="text-gray font-16">No purchases found.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-20">
            {{ $sales->appends(request()->query())->links() }}
        </div>
    </section>
@endsection
