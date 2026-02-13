<div class="modal fade" id="buynow_modal" tabindex="-1" aria-labelledby="buynow_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class="mt-25 position-relative">

                <div class="modal-video-lists mt-15">

                                        <div class="rounded-lg shadow-smx  col-12 col-lg-8 mt-25 mt-lg-0" style="margin-right: auto;margin-left: auto;">

                                            <div class="px-20 pb-30">
                        <form action="/cart/store" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="item_id" value="{{ $subscription->id }}">
                            <input type="hidden" name="item_name" value="webinar_id">

                            @if(!empty($subscription->tickets))
                                @foreach($subscription->tickets as $ticket)

                                    <div class="form-check mt-20">
                                        <input class="form-check-input" @if(!$ticket->isValid()) disabled @endif type="radio" data-discount="{{ $ticket->discount }}" value="{{ ($ticket->isValid()) ? $ticket->id : '' }}"
                                               name="ticket_id"
                                               id="courseOff{{ $ticket->id }}">
                                        <label class="form-check-label d-flex flex-column cursor-pointer" for="courseOff{{ $ticket->id }}">
                                            <span class="font-16 font-weight-500 text-dark-blue">{{ $ticket->title }} @if(!empty($ticket->discount))
                                                    ({{ $ticket->discount }}% {{ trans('public.off') }})
                                                @endif</span>
                                            <span class="font-14 text-gray">{{ $ticket->getSubTitle() }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            @endif

                            @if($subscription->price > 0)
                                <div id="priceBox" class="d-flex align-items-center justify-content-center mt-20 {{ !empty($activeSpecialOffer) ? ' flex-column ' : '' }}">
                                    <div class="text-center">
                                        @php
                                            $realPrice = handleCoursePagePrice($subscription->price);
                                        @endphp
                                        <span id="realPrice" data-value="{{ $subscription->price }}"
                                              data-special-offer="{{ !empty($activeSpecialOffer) ? $activeSpecialOffer->percent : ''}}"
                                              class=" @if(!empty($activeSpecialOffer)) font-20 text-gray text-decoration-line-through @else font-30 text-primary @endif">
                                            To unlock the videos, pay {{ $realPrice['price'] }} now
                                        </span>

                                        @if(!empty($realPrice['tax']) and empty($activeSpecialOffer))
                                            <span class="d-block font-14 text-gray">+ {{ $realPrice['tax'] }} {{ trans('cart.tax') }}</span>
                                        @endif
                                    </div>

                                    @if(!empty($activeSpecialOffer))
                                        <div class="text-center">
                                            @php
                                                $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
                                            @endphp
                                            <span id="priceWithDiscount"
                                                  class="d-block font-30 text-primary">
                                                {{ $priceWithDiscount['price'] }}
                                            </span>

                                            @if(!empty($priceWithDiscount['tax']))
                                                <span class="d-block font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} {{ trans('cart.tax') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex align-items-center justify-content-center mt-20">
                                    <span class="font-36 text-primary">{{ trans('public.free') }}</span>
                                </div>
                            @endif

                            @php
                                $canSale = ($subscription->canSale() and !$hasBought);
                            @endphp

                            <div class="mt-20 d-flex flex-column">
                                @if(!$canSale and $subscription->canJoinToWaitlist())
                                    <button type="button" data-slug="{{ $subscription->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                                @elseif($hasBought or !empty($subscription->getInstallmentOrder()))
                                    <a href="{{ $subscription->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }} </a>
                                @elseif($subscription->price > 0 && 1==2)
                                    <button type="button" class="btn btn-primary {{ $canSale ? 'js-course-add-to-cart-btn' : ($subscription->cantSaleStatus($hasBought) .' disabled ') }}">
                                        @if(!$canSale)
                                            {{ trans('update.disabled_add_to_cart') }}
                                        @else
                                            {{ trans('public.add_to_cart') }}
                                        @endif
                                    </button>

                                    @if($canSale and $subscription->subscribe)
                                        <a href="/subscribes/apply/{{ $subscription->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                                    @endif

                                    @if($canSale and !empty($subscription->points))
                                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                                            {!! trans('update.buy_with_n_points',['points' => $subscription->points]) !!}
                                        </a>
                                    @endif

                                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))
                                        <button type="button" class="btn btn-outline-danger buy_now mt-20 js-course-direct-payment" style="font-size: 22px !important;">
                                           Pay Now
                                        </button>
                                    @endif

                                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                                        <a href="/course/{{ $subscription->slug }}/installments" class="btn btn-outline-primary mt-20" >
                                            {{ trans('update.pay_with_installments') }}
                                        </a>
                                    @endif
                                @else
                                    <a href="/subscriptions/direct-payment/{{ $subscription->slug }}" class="btn btn-primary {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}" style="font-size: 22px !important;">Pay Now</a>
                                @endif
                            </div>

                        </form>

                </div>

                </div>
            </div>
        </div>
    </div>
</div>
