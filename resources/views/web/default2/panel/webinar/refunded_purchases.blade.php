@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
<style>
.refund-card {
    background: #fff;
    border-radius: 0.9375rem;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #fde8e8;
    margin-bottom: 25px;
    transition: all 0.3s ease;
    position: relative;
}
.refund-card:hover {
    box-shadow: 0 8px 30px rgba(220,53,69,0.10);
    border-color: #dc3545;
}
.refund-card .image-box {
    width: 100%;
    height: 160px;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 15px;
    position: relative;
}
.refund-card .image-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(25%);
}
.refund-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #dc3545;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.refund-title {
    font-size: 14px;
    font-weight: 700;
    color: #1f3b64;
    text-decoration: none;
    display: block;
    margin-bottom: 8px;
}
.refund-meta { font-size: 12px; color: #6c757d; margin-bottom: 4px; }
.refund-amount { font-size: 16px; font-weight: 800; color: #dc3545; }
.stat-card {
    background: #fff;
    border-radius: 0.9375rem;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
}
.stat-icon { width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-right:12px; }
.stat-label { font-size:12px;color:#6c757d;font-weight:500;display:block; }
.stat-value { font-size:22px;font-weight:800;color:#1f3b64;display:block; }
.bg-glass-danger { background:rgba(220,53,69,0.10);color:#dc3545; }
</style>
@endpush

@section('content')
<section class="dashboard">
    <div class="row">
        <div class="col-12 col-lg-8 mt-20">
            <h2 class="section-title">Refund History</h2>
        </div>
    </div>

    <div class="row mt-15">
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="stat-icon bg-glass-danger">
                    <i data-feather="rotate-ccw" width="22" height="22"></i>
                </div>
                <div>
                    <span class="stat-value">{{ $refundedCount }}</span>
                    <span class="stat-label">Total Refunded</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-30">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap mb-15">
                <h2 class="section-title mb-0">Refunded Courses</h2>
                <div class="d-flex" style="gap:8px;">
                    <a href="/panel/webinars/purchases" class="btn btn-outline-secondary btn-sm" style="border-radius:20px;font-weight:600;">My Purchases</a>
                    <a href="/panel/webinars/purchases/refunded" class="btn btn-primary btn-sm" style="border-radius:20px;font-weight:600;">Refunded</a>
                </div>
            </div>

            @if($refunds->isNotEmpty())
                <div class="row">
                    @foreach($refunds as $entry)
                        @php $item = $entry['item']; @endphp
                        @if(!empty($item))
                            <div class="col-12 col-md-6 col-lg-4 mt-15">
                                <div class="refund-card">
                                    <div class="image-box">
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" alt="{{ $item->title }}">
                                        <span class="refund-badge">&#9746; Refunded</span>
                                    </div>

                                    <a href="{{ $item->getUrl() }}" class="refund-title">{{ $item->title }}</a>

                                    <p class="refund-meta">
                                        <i data-feather="calendar" width="12" height="12" class="mr-4"></i>
                                        Refunded: <strong>{{ !empty($entry['refunded_at']) ? $entry['refunded_at']->format('j M Y') : 'N/A' }}</strong>
                                    </p>
                                    <p class="refund-meta">
                                        <i data-feather="user" width="12" height="12" class="mr-4"></i>
                                        Instructor: <strong>{{ optional($item->teacher)->full_name ?? 'N/A' }}</strong>
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between mt-10">
                                        <div>
                                            <span class="d-block font-11 text-gray">Original Amount</span>
                                            <span class="refund-amount">{{ handlePrice($entry['amount'], false) }}</span>
                                        </div>
                                        <a href="{{ $item->getUrl() }}" class="btn btn-sm btn-outline-secondary" style="border-radius:12px;font-size:11px;">
                                            View Course
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                @include(getTemplate() . '.includes.no-result', [
                    'file_name' => 'financial.png',
                    'title'     => 'No Refunded Courses',
                    'hint'      => 'You have not refunded any courses yet.',
                ])
            @endif
        </div>
    </div>
</section>
@endsection
