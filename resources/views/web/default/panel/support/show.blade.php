@extends(getTemplate() .'.panel.layouts.panel_layout')
<style>
    .badge-light-success {
  background-color: #d4edda !important;
  color: #155724;
}

</style>
@section('content')
    <section>
        <div class="d-flex align-items-start align-items-md-center justify-content-between flex-column flex-md-row">
            <h2 class="section-title">Support Ticket Details</h2>
            <a href="{{ route('newsuportforasttrolok.index') }}" class="btn btn-sm btn-primary mt-3 mt-md-0">
                <i class="fa fa-list mr-1"></i>
                All Tickets
            </a>
        </div>

        <div class="mt-25 rounded-sm shadow py-20 px-10 px-lg-25 bg-white">
            
            {{-- Payment Success Message for Offline Cash Payment --}}
            @if($supportRequest->support_scenario === 'offline_cash_payment' && $supportRequest->status === 'executed')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fa fa-check-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-1">Payment Successful! 🎉</h5>
                            <p class="mb-2">Your offline cash payment of <strong>₹{{ number_format($supportRequest->cash_amount, 2) }}</strong> has been verified and approved.</p>
                            <p class="mb-0">You now have full access to <strong>{{ $supportRequest->webinar->title }}</strong>. You can start learning immediately!</p>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="text-center mb-4">
                    <a href="{{ route('webinar', $supportRequest->webinar->id) }}" class="btn btn-success btn-lg">
                        <i class="fa fa-play-circle mr-2"></i> Start Learning Now
                    </a>
                    <a href="{{ route('user.purchased_courses') }}" class="btn btn-outline-primary btn-lg ml-2">
                        <i class="fa fa-list mr-2"></i> My Courses
                    </a>
                </div>
                
                <hr>
            @endif
            
            {{-- Ticket Header --}}
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h3 class="mb-2">{{ $supportRequest->title }}</h3>
                    <p class="text-gray mb-0">
                        <strong>Ticket Number:</strong> 
                        <span class="text-primary">{{ $supportRequest->ticket_number }}</span>
                    </p>
                </div>
                <div>
                    <span class="btn btn-sm btn-primary mt-3 mt-md-0">
                        {{ ucfirst($supportRequest->status) }}
                    </span>
                </div>
            </div>

            {{-- Request Information --}}
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-4">
                        <label class="font-weight-500 text-dark-blue d-block mb-2">Requester</label>
                        <p class="mb-0">{{ $supportRequest->getRequesterName() }}</p>
                        <small class="text-gray">{{ $supportRequest->getRequesterEmail() }}</small>
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-500 text-dark-blue d-block mb-2">Course</label>
                        <p class="mb-0">{{ $supportRequest->webinar?->title }}</p>
                        <small class="text-gray">by {{ $supportRequest->webinar?->creator->full_name }}</small>
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-500 text-dark-blue d-block mb-2">Support Scenario</label>
                        <p class="mb-0">{{ $supportRequest->getScenarioLabel() }}</p>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="mb-4">
                        <label class="font-weight-500 text-dark-blue d-block mb-2">Flow Type</label>
                        <p class="mb-0">
                            <span class="badge badge-info" style="color: #000000ff;">{{ $supportRequest->getFlowTypeLabel() }}</span>
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-500 text-dark-blue d-block mb-2">Purchase Status</label>
                        <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $supportRequest->purchase_status)) }}</p>
                        @if($supportRequest->course_purchased_at)
                            <small class="text-gray">
                                Purchased: {{ $supportRequest->course_purchased_at }}
                            </small>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-500 text-dark-blue d-block mb-2">Date</label>
                        <p class="mb-0">{{ $supportRequest->created_at }}</p>
                    </div>
                </div>
            </div>

            {{-- Scenario Specific Data --}}


            
            @if($supportRequest->support_scenario)
            <div class="mt-4 p-3 border rounded" style="background-color: #f8f9fa;">
                <h5 class="mb-3">Scenario Details</h5>
                
                @if($supportRequest->support_scenario === 'course_extension')
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Extension Days:</strong> {{ $supportRequest->extension_days }} days
                        </div>
                        <div class="col-md-6">
                            <strong>Reason:</strong> {{ $supportRequest->extension_reason }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'temporary_access')
                    <!-- <div class="row">
                        <div class="col-md-6">
                            <strong>Pending Amount:</strong> ₹{{ number_format($supportRequest->pending_amount, 2) }}
                        </div>
                        <div class="col-md-6">
                            <strong>Expected Payment:</strong> {{ $supportRequest->expected_payment_date }}
                        </div>
                    </div> -->
                     @if($supportRequest->temporary_access_days)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Temporary Access Duration:</strong> {{ $supportRequest->temporary_access_days }} Days
                        </div>
                    </div>
                     @endif
                    @if($supportRequest->temporary_access_reason)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Reason for Temporary Access:</strong> {{ $supportRequest->temporary_access_reason }}
                        </div>
                    </div>
                     @endif
                @endif

                @if($supportRequest->support_scenario === 'mentor_access')
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Reason:</strong> {{ $supportRequest->mentor_change_reason }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'relatives_friends_access')
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Description:</strong> {{ $supportRequest->relative_description }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'free_course_grant')
                    <div>
                        <strong>Reason:</strong>
                        <p>{{ $supportRequest->free_course_reason }}</p>
                        @if($supportRequest->is_special_case)
                            <span class="badge badge-warning">Special Case</span>
                        @endif
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'offline_cash_payment')
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Amount:</strong> ₹{{ number_format($supportRequest->cash_amount, 2) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Date:</strong> {{ $supportRequest->payment_date }}
                        </div>
                        <div class="col-md-3">
                            <strong>Receipt:</strong> {{ $supportRequest->payment_receipt_number ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Location:</strong> {{ $supportRequest->payment_location }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'installment_restructure')
                    <div class="row">
                        <!-- <div class="col-md-4">
                            <strong>Installments:</strong> {{ $supportRequest->requested_installments }}
                        </div>
                        <div class="col-md-4">
                            <strong>Per Installment:</strong> ₹{{ number_format($supportRequest->installment_amount, 2) }}
                        </div> -->
                        <div class="col-md-12 mt-2">
                            <strong>Reason:</strong> {{ $supportRequest->restructure_reason }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'new_service_access')
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Service:</strong> {{ $supportRequest->requested_service }}
                        </div>
                        <div class="col-md-12 mt-2">
                            <strong>Details:</strong>
                            <p>{{ $supportRequest->service_details }}</p>
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'refund_payment')
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Reason:</strong> {{ $supportRequest->refund_reason }}
                        </div>
                        <div class="col-md-4">
                            <strong>Account:</strong> {{ $supportRequest->bank_account_number }}
                        </div>
                        <div class="col-md-4">
                            <strong>IFSC:</strong> {{ $supportRequest->ifsc_code }}
                        </div>
                        <div class="col-md-4">
                            <strong>Holder:</strong> {{ $supportRequest->account_holder_name }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'post_purchase_coupon')
                    <div class="row">
                       
                        <div class="col-md-12 mt-2">
                            <strong>Reason:</strong> {{ $supportRequest->coupon_apply_reason }}
                        </div>
                    </div>
                @endif

                @if($supportRequest->support_scenario === 'wrong_course_correction')
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Reason:</strong> {{ $supportRequest->correction_reason }}
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- Description --}}
            @if($supportRequest->description)
                 <div class="mt-4">
                <label class="font-weight-500 text-dark-blue d-block mb-2">Message</label>
                <div class="p-3 border rounded" style="background-color: #f8f9fa;">
                    {!! nl2br(e($supportRequest->description)) !!}
                </div>
            </div>
            @endif
           

            {{-- Attachments --}}
            @if($supportRequest->attachments)
            <div class="mt-4">
                <label class="font-weight-500 text-dark-blue d-block mb-2">Attachments ({{ is_array($supportRequest->attachments) ? count($supportRequest->attachments) : 0 }})</label>
                
                @if(is_array($supportRequest->attachments) && count($supportRequest->attachments) > 0)
                <div class="row">
                    @foreach($supportRequest->attachments as $attachment)
                        @php
                            $extension = pathinfo($attachment, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        @endphp
                        <div class="col-12 col-md-4 mb-3">
                            <a href="{{ asset('store/' . $attachment) }}" target="_blank" 
                               class="d-block p-3 border rounded text-center hover-shadow" 
                               style="text-decoration: none; transition: all 0.3s;">
                                @if($isImage)
                                    <img src="{{ asset('storage/' . $attachment) }}" 
                                         class="img-fluid mb-2" 
                                         style="max-height: 100px; object-fit: cover;"
                                         alt="{{ basename($attachment) }}">
                                @else
                                    <i class="fa fa-file-{{ $extension === 'pdf' ? 'pdf' : 'alt' }}-o fa-3x text-primary mb-2"></i>
                                @endif
                                <p class="mb-0 text-truncate small">{{ basename($attachment) }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>
                @else
                    <p class="text-gray">No attachments</p>
                @endif
            </div>
            @endif



            {{-- 
            @if($supportRequest->logs && count($supportRequest->logs) > 0)
            <div class="mt-4">
                <label class="font-weight-500 text-dark-blue d-block mb-3">Activity Timeline</label>
                <div class="timeline">
                    @foreach($supportRequest->logs as $log)
                    <div class="d-flex mb-3 pb-3 border-bottom">
                        <div class="mr-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fa fa-check"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-1">
                                <strong>{{ ucfirst($log->action) }}</strong>
                                @if($log->user)
                                    <span class="text-gray">by {{ $log->user->full_name }}</span>
                                @endif
                            </p>
                            @if($log->remarks)
                                <p class="mb-1 text-gray">{{ $log->remarks }}</p>
                            @endif
                            <small class="text-gray">{{ $log->created_at }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            Activity Timeline --}}

        </div>
    </section>
@endsection