<div class="{{ $className ?? ' mt-5' }}">
    @php
        $i = 5;
    @endphp

    @if((!empty($rate) and $rate > 0) or !empty($showRateStars))
        @if(empty($dontShowRate) or !$dontShowRate)
        
           <span class="frame427322615-text308"><i data-feather="star" width="15" height="15" class="active"></i> {{ $rate }}</span>
        @endif
    @endif
</div>
