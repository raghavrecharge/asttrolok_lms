<div class="modal fade" id="buynow_modal" tabindex="-1" aria-labelledby="buynow_modal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content py-20">
            <div class="d-flex align-items-center justify-content-between px-20">
                <h3 class="section-title after-line"></h3>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i data-feather="x" width="25" height="25"></i>
                </button>
            </div>

            <div class=" position-relative">

                <div class="modal-video-lists mt-15">

                                        <div class="rounded-lg col-12 col-lg-8 mt-25 mt-lg-0" style="margin-right: auto;margin-left: auto;">

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
                               id="subscriptionOff{{ $ticket->id }}">
                        <label class="form-check-label d-flex flex-column cursor-pointer" for="subscriptionOff{{ $ticket->id }}">
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
                            <span class=" font-14 text-gray">+ {{ $realPrice['tax'] }} {{ trans('cart.tax') }}</span>
                        @endif
                    </div>

                    @if(!empty($activeSpecialOffer))
                        <div class="text-center">
                            @php
                                $priceWithDiscount = handleCoursePagePrice($subscription->getPrice());
                            @endphp
                            <span id="priceWithDiscount"
                                  class=" font-30 text-primary">
                                {{ $priceWithDiscount['price'] }}/-
                            </span>

                            @if(!empty($priceWithDiscount['tax']))
                                <span class="font-14 text-gray">+ {{ $priceWithDiscount['tax'] }} {{ trans('cart.tax') }}</span>
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

            <div class=" d-flex flex-column">
                @if(!$canSale and $subscription->canJoinToWaitlist())
                    <button type="button" data-slug="{{ $subscription->slug }}" class="btn btn-primary {{ (!empty($authUser)) ? 'js-join-waitlist-user' : 'js-join-waitlist-guest' }}">{{ trans('update.join_waitlist') }}</button>
                @elseif($hasBought or !empty($subscription->getInstallmentOrder()))
                    <a href="{{ $subscription->getLearningPageUrl() }}" class="btn btn-primary">{{ trans('update.go_to_learning_page') }}</a>
                @elseif($subscription->price > 0)

                    @if($canSale and $subscription->subscribe)
                        <a href="/subscribes/apply/{{ $subscription->slug }}" class="btn btn-outline-primary btn-subscribe mt-20 @if(!$canSale) disabled @endif">{{ trans('public.subscribe') }}</a>
                    @endif

                    @if($canSale and !empty($subscription->points))
                        <a href="{{ !(auth()->check()) ? '/login' : '#' }}" class="{{ (auth()->check()) ? 'js-buy-with-point' : '' }} btn btn-outline-warning mt-20 {{ (!$canSale) ? 'disabled' : '' }}" rel="nofollow">
                            {!! trans('update.buy_with_n_points',['points' => $subscription->points]) !!}
                        </a>
                    @endif

                    @if($canSale and !empty(getFeaturesSettings('direct_classes_payment_button_status')))

                        <a href="/subscriptions/direct-payment/{{ $subscription->slug }}" class="btn btn-primary mt-20 {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}"  style="font-size: 22px !important;">Pay Now</a>

                    @endif

                    @if(!empty($installments) and count($installments) and getInstallmentsSettings('display_installment_button'))
                        <a href="/subscription/{{ $subscription->slug }}/installments" class="btn btn-outline-primary mt-20" >

<svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 10px !important;">
<path d="M12.25 1.88125H11.6667V0.627045C11.6667 0.28097 11.4053 0 11.0833 0H10.5C10.178 0 9.9167 0.28097 9.9167 0.627045V1.88125H4.0833V0.627045C4.0833 0.28097 3.82204 0 3.5 0H2.9167C2.59467 0 2.3333 0.28097 2.3333 0.627045V1.88125H1.75C0.785172 1.88125 0 2.72531 0 3.7625V13.1687C0 14.2059 0.785172 15.05 1.75 15.05H12.25C13.2148 15.05 14 14.2059 14 13.1687V3.7625C14 2.72531 13.2148 1.88125 12.25 1.88125ZM12.8333 13.1687C12.8333 13.5143 12.5714 13.7958 12.25 13.7958H1.75C1.4286 13.7958 1.1667 13.5143 1.1667 13.1687V6.29594H12.8333V13.1687Z" fill="white"/>
<path d="M6.67978 13.0189C6.76256 13.1142 6.87866 13.1687 7 13.1687C7.12134 13.1687 7.23798 13.1142 7.32086 13.0189L9.21666 10.8241C9.33512 10.6867 9.36545 10.4873 9.2972 10.3155C9.22777 10.1443 9.07022 10.0333 8.8958 10.0333H7.5833V7.525C7.5833 7.17881 7.32204 6.89795 7 6.89795C6.67796 6.89795 6.4167 7.17881 6.4167 7.525V10.0333H5.1042C4.93031 10.0333 4.77287 10.1443 4.70281 10.3155C4.67888 10.3764 4.6667 10.4397 4.6667 10.5036C4.6667 10.6203 4.70686 10.7351 4.78334 10.8241L6.67978 13.0189Z" fill="white"/>
</svg>
   {{ trans('update.pay_with_installments') }}
                        </a>
                    @endif

                @else
                    @if($subscription->slug == 'learn-free-vedic-astrology-subscription-online' )
                    <a href="/register-free" class=" btn btn-primary {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}">{{ trans('public.enroll_on_webinar') }}</a>
                    @else

                   <a href="/subscriptions/direct-payment/{{ $subscription->slug }}" class="btn btn-primary {{ (!$canSale) ? (' disabled ' . $subscription->cantSaleStatus($hasBought)) : '' }}" style="font-size: 22px !important;">Pay Now</a>

                    @endif
                @endif

            </div>

        </form>

                </div>

                </div>
            </div>
        </div>
    </div>
</div>
