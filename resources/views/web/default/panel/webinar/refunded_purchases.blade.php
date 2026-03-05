@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .refund-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border: 1px solid #fde8e8;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            position: relative;
        }
        .refund-card:hover {
            box-shadow: 0 15px 50px rgba(220,53,69,0.10);
            border-color: #dc3545;
        }
        .refund-card .image-container {
            width: 100%;
            height: 160px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 15px;
            position: relative;
        }
        .refund-card .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(30%);
        }
        .refund-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #dc3545;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .refund-card .course-title {
            font-size: 15px;
            font-weight: 700;
            color: #1f3b64;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
        }
        .refund-meta { font-size: 12px; color: #6c757d; margin-bottom: 4px; }
        .refund-amount { font-size: 16px; font-weight: 800; color: #dc3545; }
        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            border: 1px solid #f0f0f0;
        }
        .stat-icon { width:50px;height:50px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-right:15px; }
        .stat-label { font-size:13px;color:#6c757d;font-weight:500;display:block; }
        .stat-value { font-size:24px;font-weight:800;color:#1f3b64;display:block; }
        .bg-glass-danger { background:rgba(220,53,69,0.1);color:#dc3545; }
    </style>
@endpush

@section('content')
    <section>
        <h2 class="section-title">Refunded Courses</h2>
        <div class="mt-25">
            <div class="row">
                <div class="col-12 col-sm-4">
                    <div class="stat-card">
                        <div class="stat-icon bg-glass-danger">
                            <i data-feather="rotate-ccw" width="24" height="24"></i>
                        </div>
                        <div>
                            <span class="stat-value">{{ $refundedCount }}</span>
                            <span class="stat-label">Total Refunded</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-25">
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">Refund History</h2>
            <div class="purchase-tabs d-flex" style="gap:8px;">
                <a href="/panel/webinars/purchases" class="btn btn-outline-secondary btn-sm" style="border-radius:20px;font-weight:600;">My Purchases</a>
                <a href="/panel/webinars/purchases/refunded" class="btn btn-primary btn-sm" style="border-radius:20px;font-weight:600;">Refunded</a>
            </div>
        </div>

        @if($refunds->isNotEmpty())
            <div class="row mt-30">
                @foreach($refunds as $entry)
                    @php $item = $entry['item']; @endphp
                    @if(!empty($item))
                        <div class="col-12 col-lg-6 mt-15">
                            <div class="refund-card">
                                <div class="image-container">
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" alt="{{ $item->title }}">
                                    <span class="refund-badge">&#9746; Refunded</span>
                                </div>

                                <a href="{{ $item->getUrl() }}" class="course-title">{{ $item->title }}</a>

                                <div class="refund-meta">
                                    <i data-feather="calendar" width="13" height="13" class="mr-4"></i>
                                    Refunded on:
                                    <strong>
                                        {{ !empty($entry['refunded_at']) ? $entry['refunded_at']->format('j M Y') : 'N/A' }}
                                    </strong>
                                </div>

                                <div class="refund-meta">
                                    <i data-feather="user" width="13" height="13" class="mr-4"></i>
                                    Instructor:
                                    <strong>{{ optional($item->teacher)->full_name ?? 'N/A' }}</strong>
                                </div>

                                <div class="mt-10 d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="d-block font-11 text-gray">Original Amount</span>
                                        <span class="refund-amount">{{ handlePrice($entry['amount'], false) }}</span>
                                    </div>
                                    @if(!empty($item->teacher))
                                        <a href="{{ $item->getUrl() }}" class="btn btn-sm btn-outline-secondary" style="border-radius:12px;font-size:12px;">
                                            View Course
                                        </a>
                                    @endif
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
    </section>
@endsection
