<div class="mt-10 radius-20 stars-card d-flex align-items-center padding-left: 1px; padding-right:1px; padding-top:1px;">
    @php
        $i = 5;
    @endphp

    @if((!empty($rate) and $rate > 0) or !empty($showRateStars))
         <span class="" style="padding-bottom: 2px;
    padding-top: 2px;
    padding-left: 3px;
    padding-right: 4px;
    font-size: 13px;
    display: flex;
    flex-direction: row;
    align-items: center;
}">
                 
                    {{ $rate }}</span>
        @while(--$i >= 5 - $rate)
            <i data-feather="star" width="16" height="16" class="active" style="padding-left:1px;"></i>
        @endwhile
        @while($i-- >= 0)
            <i data-feather="star" width="16" height="16" class="" style="padding-left:1px;"></i>
        @endwhile
        @if(empty($dontShowRate) or !$dontShowRate)
           
        @endif

        
    @endif
</div>
