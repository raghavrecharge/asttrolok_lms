<div class="radius-20 stars-card d-flex align-items-center shadow-sm " style="padding-left: 1px; padding-right:1px; padding-top:1px;">
    @php
        $i = 5;
    @endphp

    @if((!empty($rate) and $rate > 0) or !empty($showRateStars))
        @if(empty($dontShowRate) or !$dontShowRate)
            <span class="radius-20 badge badge-primary1">

                    {{ $rate }}</span>
        @endif
        @while(--$i >= 5 - $rate)
            <i data-feather="star" width="15" height="15" class="active"></i>
        @endwhile
        @while($i-- >= 0)
            <i data-feather="star" width="10" height="10" class=""></i>
        @endwhile

    @endif
</div>
