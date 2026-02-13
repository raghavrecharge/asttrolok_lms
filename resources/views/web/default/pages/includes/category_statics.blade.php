@php
    $statisticsSettings = getStatisticsSettings();
@endphp

@if(!empty($statisticsSettings['enable_statistics']))
    @if(!empty($statisticsSettings['display_default_statistics']) and !empty($homeDefaultStatistics))

    @elseif(!empty($homeCustomStatistics))

    @else
        <div class="my-40"></div>
    @endif
@else
    <div class="my-40"></div>
@endif
