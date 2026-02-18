<div class="rating stars-card ">
       @php
        $i = 5;
    @endphp
     @if((!empty($rate) and $rate > 0) or !empty($showRateStars))
                <div class="stars">
                      @while(--$i >= 5 - $rate)
                      <i data-feather="star" width="15" height="15" class="active" style="margin-top:2px;"></i>
                     @endwhile
                      @while($i-- >= 0)
                     <i data-feather="star" width="15" height="15" class="" style="margin-top:2px;"></i>
                    @endwhile

                </div>
                   @if(empty($dontShowRate) or !$dontShowRate)
                <span class="rating-badge">{{ $rate }}</span>
                  @endif
            @endif
            </div>


