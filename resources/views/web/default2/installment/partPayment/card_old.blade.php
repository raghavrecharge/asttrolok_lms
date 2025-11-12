<div class="installment-card p-15 mt-20">
    <div class="row">
        <div class="col-md-8">
           @if($item->slug != 'astrology-basic-level' && $item->slug != '3-days-astrology-workshop')
           <h4 class="font-16 font-weight-bold text-dark-blue">{{ $installment->main_title }}</h4>

            <div class="">
                <p class="text-gray font-14 text-ellipsis1">{!!nl2br($installment->description) !!}</p>
            </div>@endif
            
               
            @if(!empty($installment->capacity))
                @php
                    $reachedCapacityPercent = $installment->reachedCapacityPercent();
                @endphp

                @if($reachedCapacityPercent > 0)
                    <!--<div class="mt-20 d-flex align-items-center">-->
                    <!--    <div class="progress card-progress flex-grow-1">-->
                    <!--        <span class="progress-bar rounded-sm {{ $reachedCapacityPercent > 50 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $reachedCapacityPercent }}%"></span>-->
                    <!--    </div>-->
                    <!--    <div class="ml-10 font-12 text-danger">{{ trans('update.percent_capacity_reached',['percent' => $reachedCapacityPercent]) }}</div>-->
                    <!--</div>-->
                @endif
            @endif

            @if(!empty($installment->banner))
                <div class="mt-20">
                    <img src="{{ config('app.img_dynamic_url') }}{{ $installment->banner }}" alt="{{ $installment->main_title }}" class="img-fluid">
                </div>
                @else
                
                <div class="default-package-icon mt-20">
                <img src="/assets2/default/img/become-instructor/default.png" class="img-cover" alt="{{ trans('update.installment_overview') }}" width="176" height="144">
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
        </div>

        <div class="col-md-4 p-0 pr-15">
            <div class="installment-card__payments d-flex flex-column w-100 h-100">

                @php
                    $totalPayments = $installment->totalPayments($itemPrice ?? 1);
                    $installmentTotalInterest = $installment->totalInterest($itemPrice, $totalPayments);
                @endphp

                <div class="d-flex align-items-center justify-content-center flex-column">
                    <span class="font-36 font-weight-bold text-primary">{{ handlePrice($totalPayments) }}</span>
                    <span class="mt-10 font-12 text-gray">{{ trans('update.total_payment') }} @if($installmentTotalInterest > 0)
                            ({{ trans('update.percent_interest',['percent' => $installmentTotalInterest]) }})
                        @endif</span>
                </div>

               {{-- <div class="mt-25 mb-15">
                    <div class="installment-step d-flex align-items-center font-12 text-gray">{{ !empty($installment->upfront) ? (trans('update.amount_upfront',['amount' => handlePrice($installment->getUpfront($itemPrice))]) . ($installment->upfront_type == "percent" ? " ({$installment->upfront}%)" : '')) : trans('update.no_upfront') }}</div>

                    @foreach($installment->steps as $installmentStep)
                        <div class="installment-step d-flex align-items-center font-12 text-gray">{{ $installmentStep->getDeadlineTitle($itemPrice) }}</div>
                    @endforeach
                </div> --}}
                
               @if($item->slug != 'astrology-basic-level' && $item->slug != '3-days-astrology-workshop')
                <form action="/cart/coupon/validate1" method="Post">
                    {{ csrf_field() }}
                    <div class="row"> 
                    <div class="col-12 col-lg-9"> 
                    <div class="form-group">
                        <input type="text" name="coupon" id="coupon_input" class="form-control mt-25"
                         placeholder="{{ trans('cart.enter_your_code_here') }}">
                        <input type="hidden" name="web_id1" id="web_id1" value="{{$itemId}}" class="form-control mt-25" >
                        <input type="hidden" name="webinsta_id1" id="webinsta_id1" value="{{$installmentRow->id}}" class="form-control mt-25" >
                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                    </div>
                    </div><div class="col-12 col-lg-3 ">
                    <button type="submit" id="checkCoupon1" class="btn btn-sm btn-primary mt-25">{{ trans('cart.validate') }}</button></div></div>
                </form>
                @endif
                <div id="Payment-Option" class=" bg-gray200 mt-30 rounded-lg border p-15">
            
         <!--<h2 class="section-title">Payment Option</h2>-->
          <form action="/payments/payment-request" method="post" id="razor-pay-request" class=" mt-25 " >
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id ?? 0 }}">
            <input type="hidden" name="installment_id" value="{{ $installment->id ?? null }}">
             <input type="hidden" name="discountId" value="{{!empty($discountId) ? $discountId : 0}}"  class="form-control mt-25 mb-25 " required>
             <input type="hidden" name="price" value="<?php echo (number_format(((($installments->first()->upfront)*$itemPrice) /100), 2, '.', '')); ?>">
             <input type="hidden" name="item" value="{{!empty($item) ? $item->id : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 " >
             <input type="hidden" name="totalDiscount" value="{{!empty($totalDiscount) ? $totalDiscount : 0}}"  placeholder="totalDiscount" class="form-control mt-25 mb-25 " >
            <input type="hidden" name="item_type" value="{{!empty($itemType) ? $itemType : null}}"  placeholder="Contact Number" class="form-control mt-25 mb-25 ">
            <div class="form-group">
                            
                            <input name="name" type="text" value="{{ auth()->check() ? auth()->user()->full_name :'' }}" id='customer_name'  placeholder="Name" class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                           
                            <input name="email" id='customer_email'  placeholder="Email"  type="text" value="{{ auth()->check() ? auth()->user()->email :'' }}"  class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <input name="number" id='customer_number'  placeholder="Contact Number" type="text" value="{{ auth()->check() ? auth()->user()->mobile :'' }}" class="form-control @error('number') is-invalid @enderror">
                            @error('number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            @if($item->slug=='astrology-basic-level' || $item->slug=='3-days-astrology-workshop')
                            <input name="amount" id='amount'  placeholder="Amount" type="text" value="{{$item->slug=='astrology-basic-level' || $item->slug=='3-days-astrology-workshop'?$totalPayments:null}}" class="form-control @error('number') is-invalid @enderror"  readonly >
                            @else
                            <input name="amount" id='amount'  placeholder="Amount" type="text" value="" class="form-control @error('number') is-invalid @enderror"   >
                            @endif
                            @error('amount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
            <!--<input type="text" name="name" id='customer_name'  placeholder="Name" class="form-control mt-25 " >-->
            <!--<input type="email" name="email" id='customer_email'  placeholder="Email" class="form-control mt-25 " >-->
            <!--<input type="number" name="number" id='customer_number'  placeholder="Contact Number" class="form-control mt-25 mb-25" > -->
            

          

               {{-- <div class="col-12 col-lg-6 mb-20 charge-account-radio ">
                   
                    <label for="offline" class="rounded-sm p-15 p-lg-15 d-flex " style="flex-wrap: nowrap;  align-items: center; justify-content: flex-start;  flex-direction: row;background-color:#fff;">
                       <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit" style="display: block;    visibility: visible;">
                        <img src="{{ config('app.img_dynamic_url') }}/assets/default/img/activity/wallet.png" width="120" height="60" alt="">
                        <div>
                         <p class="mt-30 mt-lg-10 font-weight-500 text-dark-blue">
                               <span class="font-weight-bold font-14">Wallet</span>
                            <p class="mt-5"></p>
                        </p>

                        <!--<p class="font-weight-500 text-dark-blue">-->
                        <!--   Purchase with your fingertips. Look for us the next time you're paying from a mobile app, and checkout faster on thousands of mobile websites.-->
                        <!--</p>-->
                        
                        </div>

                        
                    </label>
                </div> --}}
            </div>

            @if(!empty($invalidChannels))
                <div class="d-flex align-items-center mt-30 rounded-lg border p-15">
                    <div class="size-40 d-flex-center rounded-circle bg-gray200">
                        <i data-feather="info" class="text-gray" width="20" height="20"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.disabled_payment_gateways') }}</h4>
                        <p class="font-12 text-gray">{{ trans('update.disabled_payment_gateways_hint') }}</p>
                    </div>
                </div>

                <div class="row mt-20">
                    @foreach($invalidChannels as $invalidChannel)
                        <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                            <div class="disabled-payment-channel bg-white border rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

<button type="button" id="razor-pay-now"  class="{{$item->slug=='astrology-basic-level' || $item->slug=='3-days-astrology-workshop'?'':'d-none'}} btn btn-sm btn-primary loading">{{ trans('public.start_payment') }}</button>

        </form>
        </div>
 <!--<a href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}#Payment-Option" class=" btn btn-primary btn-block mt-auto">{{ trans('update.pay_with_installments') }}</a>-->
                <!--<a href="/installments/{{ $installment->id }}?item={{ $itemId }}&item_type={{ $itemType }}&{{ http_build_query(request()->all()) }}" target="_blank" class="btn btn-primary btn-block mt-25">{{ trans('update.pay_with_installments') }}</a>-->
            </div>
        </div>
    </div>
</div>
@push('scripts_bottom')
<script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <!--<script src="{{ config('app.js_css_url') }}/assets/default/js/parts/cart1.min.js"></script>-->
@endpush
