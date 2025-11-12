<!--<div class="installment-card p-15 mt-20">-->
<div class=" p-15">
    <div class="row">
      {{--   <div class="col-8">
         <h4 class="font-16 font-weight-bold text-dark-blue">{{ $installment->main_title }}</h4>

            <div class="">
                <p class="text-gray font-14 text-ellipsis1">{{ nl2br($installment->description) }}</p>
            </div>
           

            @if(!empty($installment->capacity))
                @php
                    $reachedCapacityPercent = $installment->reachedCapacityPercent();
                @endphp

                @if($reachedCapacityPercent > 0)
                    <div class="mt-20 d-flex align-items-center">
                        <div class="progress card-progress flex-grow-1">
                            <span class="progress-bar rounded-sm {{ $reachedCapacityPercent > 50 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $reachedCapacityPercent }}%"></span>
                        </div>
                        <div class="ml-10 font-12 text-danger">{{ trans('update.percent_capacity_reached',['percent' => $reachedCapacityPercent]) }}</div>
                    </div>
                @endif
            @endif

            @if(!empty($installment->banner))
                <div class="mt-20">
                    <img src="{{ $installment->banner }}" alt="{{ $installment->main_title }}" class="img-fluid">
                </div>
            @endif

            @if(!empty($installment->options))
                <div class="mt-20">
                    @php
                        $installmentOptions = explode(\App\Models\Installment::$optionsExplodeKey, $installment->options);
                    @endphp

                    @foreach($installmentOptions as $installmentOption)
                        <div class="d-flex align-items-center mb-1">
                            <i data-feather="check" width="25" height="25" class="text-primary"></i>
                            <span class="ml-10 font-14 text-gray">{{ $installmentOption }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>   --}}

        <div class="col-12">
            <div class="installment-card__payments d-flex flex-column w-100 h-100" style="background-color: #ffffff;">
               
                @php
                    $totalPayments = $installment->totalPayments($itemPrice ?? 1);
                    $installmentTotalInterest = $installment->totalInterest($itemPrice, $totalPayments);
                @endphp

               {{-- <div class="d-flex align-items-center justify-content-center flex-column">
                    <span class="font-36 font-weight-bold text-primary">{{ handlePrice($totalPayments) }}</span>
                    <span class="mt-10 font-12 text-gray">{{ trans('update.total_payment') }} @if($installmentTotalInterest > 0)
                            ({{ trans('update.percent_interest',['percent' => $installmentTotalInterest]) }})
                        @endif</span>
                </div> --}}
<div class="price-box p-10 shadow-xs mb-20" style="border: 1px solid #e1e1e1;border-radius: 10px;"> 
<span class="font-30 font-weight-bold">Total Amount </span> 
<span class="f-right font-30 text-primary" style="    float: right;">{{ handlePrice($totalPayments) }}/-</span> 
</div>
                <div class="mt-25 mb-15">
                    <div class="installment-step d-flex align-items-center font-14 text-primary" style="font-weight: 600;">{{ !empty($installment->upfront) ? (trans('update.amount_upfront',['amount' => handlePrice($installment->getUpfront($itemPrice))]) . ($installment->upfront_type == "percent" ? " ({$installment->upfront}%)" : '')) : trans('update.no_upfront') }}</div>

                    @foreach($installment->steps as $installmentStep)
                        <div class="installment-step d-flex align-items-center font-14 text-gray" style="font-weight: 600;">{{ $installmentStep->getDeadlineTitle($itemPrice) }}</div>
                    @endforeach
                </div>
                <div class=" p-15 mt-20 mx-20 shadow-sm " style="border: 1px solid #e1e1e1;border-radius: 10px;">
                <p class="text-gray font-12 text-ellipsis">{!! nl2br($installment->description) !!}</p>
            </div>
                
                <form action="/cart/coupon/validate1" method="Post">
                    <div class="col-12 col-lg-12">
                    <section>
                    {{ csrf_field() }}
                    <div class="row" style="display: flex;justify-content: space-evenly;"> 
                    <div class="col-11 col-lg-9"> 
                    <div class="form-group" style="border-radius: 20px;">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-25 " style="border-radius: 20px !important;"
                         placeholder="{{ trans('cart.enter_your_code_here') }}" style="border-radius: 20px;">
                        <input type="hidden" name="web_id1" id="web_id1" value="{{$itemId}}" class="form-control mt-25" >
                        <input type="hidden" name="webinsta_id1" id="webinsta_id1" value="{{$installmentRow->id}}" class="form-control mt-25" >
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-5 col-lg-3 botton-1" style="position: absolute;margin-top: 3px;margin-right: -231px;">
                    <button type="submit" id="checkCoupon1" style="height: 35px !important; border-radius: 20px !important;" class="btn btn-sm btn-primary mt-25">{{ trans('cart.validate') }}</button></div></div>
                    </section></div>
                </form>
<div style="
    display: flex;
    justify-content: center;
">
                <a style="
    width: 138px;
" href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}#Payment-Option" class=" btn btn-primary btn-block mt-auto">Pay Now</a>
            </div></div>
        </div>
    </div>
</div>



@push('scripts_bottom')
<script type="text/javascript">

        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="/assets/default/js/parts/get-regions.min.js"></script>
    <script src="/assets/default/js/parts/cart.min.js"></script>
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush