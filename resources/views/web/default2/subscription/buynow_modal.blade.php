<!--<div class="modal fade" id="myModal" role="dialog">-->
<!--    <div class="modal-dialog">-->
    
      <!-- Modal content-->
<!--      <div class="modal-content">-->
<!--        <div class="modal-header">-->
<!--          <button type="button" class="close" data-dismiss="modal">&times;</button>-->
<!--          <h4 class="modal-title">Modal Header</h4>-->
<!--        </div>-->
<!--        <div class="modal-body">-->
<!--          <p>Some text in the modal.</p>-->
<!--        </div>-->
<!--        <div class="modal-footer">-->
<!--          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>-->
<!--        </div>-->
<!--      </div>-->
      
<!--    </div>-->
<!--  </div>-->
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

                  
                                   {{--  <div class="d-flex justify-content-between align-items-center my-15 px-20">
                                        <h3 class="section-title after-line">Please login to access the content.</h3>
                                    </div> --}}

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
                            <!--<div class="mt-35">-->
                            <!--<h4 class="mb-20 font-14 font-weight-500 ">💼 714+ Registered Students</h4>-->
                            <!--<strong class="d-block text-secondary font-weight-bold">Become an Astrologer for Just ₹2,100/Month</strong>-->
                            <!--<div class="mt-20 d-flex align-items-center text-gray">-->
                            <!--    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download-cloud"><polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path></svg>-->
                            <!--    <span class="ml-5 font-14 font-weight-500">714+ Students already learning</span>-->
                            <!--  </div>-->
                            <!--  <div class="mt-20 d-flex align-items-center text-gray">-->
                            <!--    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>-->
                            <!--    <span class="ml-5 font-14 font-weight-500">Beginner to Expert Journey</span>-->
                            <!--  </div>-->
                            <!--   <div class="mt-20 d-flex align-items-center text-gray">-->
                            <!--        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>-->
                            <!--        <span class="ml-5 font-14 font-weight-500">Affordable Monthly Subscription</span>-->
                            <!--    </div>-->
                         

                                                    <!--</div>-->

                        </form>

                       

                        
                    

                    
                </div>
                          
                </div>
            </div>
        </div>
    </div>
</div>
