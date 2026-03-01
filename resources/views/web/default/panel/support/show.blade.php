@extends(getTemplate() .'.panel.layouts.panel_layout')
@push('styles_top')
    <style>
        .premium-detail-container {
            background: #fff;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border: 1px solid #f8f8f8;
        }
        .detail-item {
            margin-bottom: 20px;
        }
        .detail-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            font-size: 15px;
            color: #1f3b64;
            font-weight: 700;
        }
        .message-box {
            background: #f8faff;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #eee;
            color: #1f3b64;
            line-height: 1.6;
        }
        .scenario-badge {
            background: rgba(31, 59, 100, 0.05);
            color: #1f3b64;
            padding: 10px 20px;
            border-radius: 12px;
            display: inline-block;
            font-weight: 700;
        }
        .attachment-card {
            border: 1px solid #eee;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .attachment-card:hover {
            border-color: #1f3b64;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .bg-glass-primary { background: rgba(31, 59, 100, 0.08); color: #1f3b64; }
        .bg-glass-warning { background: rgba(255, 193, 7, 0.08); color: #ffc107; }
        .bg-glass-info { background: rgba(0, 123, 255, 0.08); color: #007bff; }
        .bg-glass-success { background: rgba(67, 212, 119, 0.08); color: #2ecc71; }
        .bg-glass-danger { background: rgba(246, 59, 59, 0.08); color: #f63b3b; }

        .history-card {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }
        .history-status-indicator {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 12px;
        }
        .history-badge {
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 15px;
        }
        .history-badge i {
            margin-right: 8px;
            stroke-width: 2.5px;
        }
        .history-author {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }
        .history-notes {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            margin-top: 10px;
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
        }
    </style>
@endpush
@section('content')
    <section>
        <div class="d-flex align-items-center justify-content-between mb-25">
            <h2 class="section-title mb-0">Ticket #{{ $supportRequest->ticket_number }}</h2>
            <a href="{{ route('newsuportforasttrolok.index') }}" class="btn btn-border-white d-flex align-items-center">
                <i data-feather="arrow-left" width="18" height="18" class="mr-5"></i>
                Back to Tickets
            </a>
        </div>

        <div class="premium-detail-container mt-25">
            
            {{-- Payment Success Message --}}
            @if($supportRequest->support_scenario === 'offline_cash_payment' && $supportRequest->status === 'executed')
                <div class="glass-alert-success mb-30 p-25 rounded-20 d-flex align-items-center" style="background: rgba(67, 212, 119, 0.1); border: 1px solid rgba(67, 212, 119, 0.2); border-left: 5px solid #43d477;">
                    <div class="mr-20">
                        <i data-feather="check-circle" width="40" height="40" class="text-success"></i>
                    </div>
                    <div>
                        <h4 class="font-18 font-weight-bold text-dark-blue mb-5">Payment Successfully Applied! 🎉</h4>
                        <p class="mb-0 text-gray">Your payment of <strong>₹{{ number_format($supportRequest->cash_amount ?? 0, 2) }}</strong> has been verified. You now have full access to current course.</p>
                    </div>
                </div>
            @endif
            
            {{-- Ticket Header --}}
            <div class="d-flex align-items-center justify-content-between mb-30 pb-20 border-bottom">
                <div>
                    <h3 class="font-20 font-weight-bold text-dark-blue">{{ $supportRequest->title }}</h3>
                    <div class="mt-5 d-flex align-items-center">
                        <span class="scenario-badge mr-15">
                            <i data-feather="tag" width="14" height="14" class="mr-5"></i>
                            {{ $supportRequest->getScenarioLabel() }}
                        </span>
                        <span class="text-gray font-14"><i data-feather="calendar" width="14" height="14" class="mr-5"></i>{{ $supportRequest->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
                <div>
                    @php
                        $badgeClass = 'bg-glass-primary';
                        if($supportRequest->status == 'pending') $badgeClass = 'bg-glass-warning';
                        elseif($supportRequest->status == 'approved' || $supportRequest->status == 'executed') $badgeClass = 'bg-glass-success';
                        elseif($supportRequest->status == 'rejected') $badgeClass = 'bg-glass-danger';
                    @endphp
                    <span class="badge {{ $badgeClass }} font-14 px-20 py-10" style="border-radius: 12px;">
                        {{ $supportRequest->status == 'approved' || $supportRequest->status == 'executed' || $supportRequest->status == 'verified' ? 'Approved' : ucfirst(str_replace('_', ' ', $supportRequest->status)) }}
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Status History</span>
                        <div class="mt-10">
                            @if(in_array($supportRequest->status, ['rejected', 'approved', 'executed', 'closed', 'verified', 'completed']))
                                <div class="history-card">
                                    <div class="history-status-indicator">
                                        <div class="history-badge">
                                            @if($supportRequest->status === 'rejected')
                                                <i data-feather="x-circle" width="18" height="18" class="text-danger"></i>
                                                <span class="text-danger">Rejected</span>
                                            @elseif(in_array($supportRequest->status, ['approved', 'executed', 'verified']))
                                                <i data-feather="check-circle" width="18" height="18" style="color: #2ecc71;"></i>
                                                <span style="color: #2d3748;">Approved</span>
                                            @else
                                                <i data-feather="info" width="18" height="18" class="text-primary"></i>
                                                <span class="text-primary">{{ ucfirst($supportRequest->status) }}</span>
                                            @endif
                                        </div>
                                        <div class="history-author">
                                            by {{ $supportRequest->supportHandler?->full_name ?? $supportRequest->subAdmin?->full_name ?? 'Asttrolok' }}
                                        </div>
                                    </div>

                                    @if($supportRequest->rejection_reason || $supportRequest->approval_remarks || $supportRequest->execution_notes)
                                        <div class="history-notes">
                                            <i data-feather="message-square" width="12" height="12" class="mr-5" style="opacity: 0.7;"></i>
                                            {{ $supportRequest->rejection_reason ?? $supportRequest->approval_remarks ?? $supportRequest->execution_notes }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="badge bg-glass-warning">Awaiting Review</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Course / Service</span>
                        <div class="d-flex align-items-center mt-10">
                            <i data-feather="book-open" width="20" height="20" class="text-primary mr-10"></i>
                            <div>
                                <span class="detail-value d-block">{{ $supportRequest->webinar?->title ?? 'N/A' }}</span>
                                <small class="text-gray">by {{ $supportRequest->webinar?->creator?->full_name ?? 'Instructor' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="detail-item">
                        <span class="detail-label">Payment Mode</span>
                        <div class="d-flex align-items-center mt-10">
                            <i data-feather="credit-card" width="20" height="20" class="text-primary mr-10"></i>
                            <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $supportRequest->purchase_status)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message Content --}}
            <div class="mt-30 message-box">
                <span class="detail-label mb-10">Description / Request Message</span>
                @php
                    $displayDescription = $supportRequest->description;
                    if (empty($displayDescription)) {
                        $displayDescription = $supportRequest->relative_description 
                            ?? $supportRequest->extension_reason 
                            ?? $supportRequest->free_course_reason 
                            ?? $supportRequest->correction_reason
                            ?? $supportRequest->service_details
                            ?? $supportRequest->temporary_access_reason
                            ?? $supportRequest->mentor_change_reason;
                    }
                @endphp
                <p class="mb-0">{!! nl2br(e($displayDescription ?? 'No description provided.')) !!}</p>
                
                @if($supportRequest->support_scenario === 'course_extension')
                    <div class="mt-15 pt-15 border-top">
                        <span class="badge bg-glass-primary mr-10">Extension: {{ $supportRequest->extension_days }} Days</span>
                        <small class="text-gray">Reason: {{ $supportRequest->extension_reason }}</small>
                    </div>
                @elseif($supportRequest->support_scenario === 'offline_cash_payment')
                     <div class="mt-15 pt-15 border-top row">
                        <div class="col-6 col-md-3">
                            <span class="detail-label">Amount Paid</span>
                            <span class="detail-value">₹{{ number_format($supportRequest->cash_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="detail-label">Date</span>
                            <span class="detail-value">{{ $supportRequest->payment_date }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Attachments --}}
            @if($supportRequest->attachments && count($supportRequest->attachments) > 0)
                <div class="mt-40">
                    <h5 class="font-16 font-weight-bold text-dark-blue mb-20"><i data-feather="paperclip" width="18" height="18" class="mr-5"></i>Attachments</h5>
                    <div class="row">
                        @foreach($supportRequest->attachments as $attachment)
                            @php
                                $extension = pathinfo($attachment, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            @endphp
                            <div class="col-6 col-md-3 mb-20">
                                <a href="{{ asset('store/' . $attachment) }}" target="_blank" class="attachment-card text-decoration-none">
                                    @if($isImage)
                                        <img src="{{ asset('storage/' . $attachment) }}" 
                                             class="img-fluid rounded-10 mb-10" 
                                             style="height: 60px; object-fit: cover;"
                                             alt="{{ basename($attachment) }}">
                                    @else
                                        <div class="mb-10 text-primary">
                                            <i data-feather="file-text" width="30" height="30"></i>
                                        </div>
                                    @endif
                                    <span class="font-12 text-dark-blue text-truncate w-100 px-5">{{ basename($attachment) }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>
@endsection