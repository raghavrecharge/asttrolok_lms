@extends(getTemplate() .'.panel.layouts.panel_layout')

@push('styles_top')
<style>
/* ── Financial Summary — row colours ── */
.fs-table { width:100%; border-collapse:collapse; }
.fs-table th { background:#f8f9fc; padding:10px 14px; font-size:11px; font-weight:700; color:#777; text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid #eee; }
.fs-table td { padding:12px 14px; font-size:13px; color:#333; border-bottom:1px solid #f5f5f5; vertical-align:middle; }
.fs-table tr:last-child td { border-bottom:none; }
.fs-table tr.row-credit { background:#f6fff7; }
.fs-table tr.row-debit  { background:#fff6f6; }
.fs-table tr.row-refund { background:#f0f7ff; }
.fs-legend { display:flex; gap:14px; padding:10px 16px; background:#fafafa; border-bottom:1px solid #eee; flex-wrap:wrap; margin-bottom:0; }
.fs-legend-item { display:flex; align-items:center; gap:5px; font-size:11px; color:#666; }
.fs-dot { width:10px; height:10px; border-radius:2px; flex-shrink:0; }
.dir-badge { display:inline-flex; align-items:center; gap:3px; padding:3px 9px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; }
.dir-badge.credit { background:#d4edda; color:#155724; }
.dir-badge.debit  { background:#f8d7da; color:#721c24; }
.type-badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:600; text-transform:uppercase; }
.type-badge.course   { background:#e3f2fd; color:#1565c0; }
.type-badge.bundle   { background:#f3e5f5; color:#7b1fa2; }
.type-badge.part     { background:#fff3e0; color:#e65100; }
.type-badge.subscription { background:#e8f5e9; color:#2e7d32; }
.type-badge.installment_payment { background:#fff9c4; color:#f57f17; }
.amt-credit { color:#28a745; font-weight:700; }
.amt-debit  { color:#dc3545; font-weight:700; }
</style>
@endpush

@section('content')
    @if($accountings->count() > 0)
        <section>
            <h2 class="section-title">{{ trans('financial.financial_documents') }}</h2>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12 ">
                        <div class="table-responsive">
                            {{-- Legend --}}
                        <div class="fs-legend">
                            <div class="fs-legend-item"><span class="fs-dot" style="background:#d4edda"></span> Credit (Money In)</div>
                            <div class="fs-legend-item"><span class="fs-dot" style="background:#f8d7da"></span> Debit (Money Out)</div>
                        </div>
                        <table class="fs-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.title') }}</th>
                                    <th>{{ trans('public.description') }}</th>
                                    <th class="text-center">Direction</th>
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency }})</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($accountings as $accounting)
                                    @php
                                        $acc_isCredit = ($accounting->type == \App\Models\Accounting::$addiction);
                                        $acc_rowClass = $acc_isCredit ? 'row-credit' : 'row-debit';
                                    @endphp
                                    <tr class="{{ $acc_rowClass }}">
                                        <td class="text-left">
                                            <div class="d-flex flex-column">
                                                <div class="font-14 font-weight-500">
                                                    @if($accounting->is_cashback)
                                                        {{ trans('update.cashback') }}
                                                    @elseif(!empty($accounting->webinar_id) and !empty($accounting->webinar))
                                                        {{ $accounting->webinar->title }}
                                                    @elseif(!empty($accounting->bundle_id) and !empty($accounting->bundle))
                                                        {{ $accounting->bundle->title }}
                                                    @elseif(!empty($accounting->product_id) and !empty($accounting->product))
                                                        {{ $accounting->product->title }}
                                                    @elseif(!empty($accounting->meeting_time_id))
                                                        {{ trans('meeting.reservation_appointment') }}
                                                    @elseif(!empty($accounting->subscribe_id) and !empty($accounting->subscribe))
                                                        {{ $accounting->subscribe->title }}
                                                    @elseif(!empty($accounting->promotion_id) and !empty($accounting->promotion))
                                                        {{ $accounting->promotion->title }}
                                                    @elseif(!empty($accounting->registration_package_id) and !empty($accounting->registrationPackage))
                                                        {{ $accounting->registrationPackage->title }}
                                                    @elseif(!empty($accounting->installment_payment_id))
                                                        {{ trans('update.installment') }}
                                                    @elseif($accounting->store_type == \App\Models\Accounting::$storeManual)
                                                        {{ trans('financial.manual_document') }}
                                                    @elseif($accounting->type == \App\Models\Accounting::$addiction and $accounting->type_account == \App\Models\Accounting::$asset)
                                                        {{ trans('financial.charge_account') }}
                                                    @elseif($accounting->type == \App\Models\Accounting::$deduction and $accounting->type_account == \App\Models\Accounting::$income)
                                                        {{ trans('financial.payout') }}
                                                    @elseif($accounting->is_registration_bonus)
                                                        {{ trans('update.registration_bonus') }}
                                                    @else
                                                        ---
                                                    @endif
                                                </div>

                                                @if(!empty($accounting->gift_id) and !empty($accounting->gift))
                                                    <div class="text-gray font-12">{!! trans('update.a_gift_for_name_on_date',['name' => $accounting->gift->name, 'date' => dateTimeFormat($accounting->gift->date, 'j M Y H:i')]) !!}</div>
                                                @endif

                                                <div class="font-12 text-gray">
                                                    @if(!empty($accounting->webinar_id) and !empty($accounting->webinar))

                                                    @elseif(!empty($accounting->bundle_id) and !empty($accounting->bundle))

                                                    @elseif(!empty($accounting->product_id) and !empty($accounting->product))

                                                    @elseif(!empty($accounting->meeting_time_id) and !empty($accounting->meetingTime))
                                                        {{ $accounting->meetingTime->meeting->creator->full_name }}
                                                    @elseif(!empty($accounting->subscribe_id) and !empty($accounting->subscribe))
                                                        {{ $accounting->subscribe->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->subscribe->title : '' }}
                                                    @elseif(!empty($accounting->promotion_id) and !empty($accounting->promotion))
                                                        {{ $accounting->promotion->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->promotion->title : '' }}
                                                    @elseif(!empty($accounting->registration_package_id) and !empty($accounting->registrationPackage))
                                                        {{ $accounting->registrationPackage->id }}{{ ($accounting->is_cashback) ? '-'.$accounting->registrationPackage->title : '' }}
                                                    @elseif(!empty($accounting->installment_payment_id))
                                                        @php
                                                            $installmentItemTitle = "--";
                                                            $installmentOrderPayment = $accounting->installmentOrderPayment;

                                                            if (!empty($installmentOrderPayment)) {
                                                                $installmentOrder = $installmentOrderPayment->installmentOrder;
                                                                if (!empty($installmentOrder)) {
                                                                    $installmentItem = $installmentOrder->getItem();
                                                                    if (!empty($installmentItem)) {
                                                                        $installmentItemTitle = $installmentItem->title;
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        {{ $installmentItemTitle }}
                                                    @else
                                                        ---
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-left align-middle">
                                            <span class="font-weight-500 text-gray">{{ $accounting->description }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($acc_isCredit)
                                                <span class="dir-badge credit">▲ Credit</span>
                                            @else
                                                <span class="dir-badge debit">▼ Debit</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($acc_isCredit)
                                                <span class="amt-credit">+ {{ handlePrice($accounting->amount, false) }}</span>
                                            @else
                                                <span class="amt-debit">− {{ handlePrice($accounting->amount, false) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <span>{{ dateTimeFormat($accounting->created_at, 'j M Y') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    @endif

    {{-- LMS-038 FIX: Show payment history from $amount_paid (includes UPE sales) when available --}}
    @if(!empty($amount_paid) && count($amount_paid) > 0)
        <section class="mt-25">
            <h2 class="section-title">{{ trans('financial.payment_history') ?? 'Payment History' }}</h2>

            <div class="panel-section-card py-20 px-25 mt-20">
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                        {{-- Legend --}}
                        <div class="fs-legend">
                            <div class="fs-legend-item"><span class="fs-dot" style="background:#d4edda"></span> Credit / Payment</div>
                            <div class="fs-legend-item"><span class="fs-dot" style="background:#f8d7da"></span> Installment / Part</div>
                        </div>
                            <table class="fs-table">
                                <thead>
                                <tr>
                                    <th>{{ trans('public.title') }}</th>
                                    <th class="text-center">Direction</th>
                                    <th class="text-center">{{ trans('panel.amount') }}</th>
                                    <th class="text-center">{{ trans('public.type') }}</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($amount_paid as $payment)
                                    @php
                                        $ptype = $payment[5] ?? 'course';
                                        $prow  = in_array($ptype, ['part','installment_payment']) ? 'row-debit' : 'row-credit';
                                    @endphp
                                    <tr class="{{ $prow }}">
                                        <td class="text-left">
                                            <span class="font-weight-500">{{ $payment[2] ?? '---' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if(in_array($ptype, ['part','installment_payment']))
                                                <span class="dir-badge debit">▼ Paid</span>
                                            @else
                                                <span class="dir-badge credit">▲ Credit</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="amt-credit font-weight-bold">{{ handlePrice($payment[0] ?? 0) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="type-badge {{ $ptype }}">{{ ucfirst(str_replace('_',' ',$ptype)) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if(!empty($payment[1]))
                                                <span>{{ dateTimeFormat($payment[1], 'j M Y') }}</span>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @elseif($accountings->count() == 0)
        @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'financial.png',
            'title' => trans('financial.financial_summary_no_result'),
            'hint' => nl2br(trans('financial.financial_summary_no_result_hint')),
        ])
    @endif
    <div class="my-30">
        {{ $accountings->appends(request()->input())->links('vendor.pagination.panel') }}
    </div>
@endsection
