<div class="stars-card d-flex align-items-center {{ $className ?? ' mt-5' }}">
    @php
        $i = 5;
    @endphp

    @if((!empty($rate) and $rate > 0) or !empty($showRateStars))
        @while(--$i >= 5 - $rate)
            <i data-feather="star" width="15" height="15" class="active grid-star"></i>
        @endwhile
        @while($i-- >= 0)
            <i data-feather="star" width="15" height="15" class="grid-star"></i>
        @endwhile

        @if(empty($dontShowRate) or !$dontShowRate)
        
            <span class="badge badge-primary ml-10 rating-course"><i data-feather="star" width="15" height="15" class="active"></i> {{ $rate }}</span>
        @endif
    @endif
</div>
