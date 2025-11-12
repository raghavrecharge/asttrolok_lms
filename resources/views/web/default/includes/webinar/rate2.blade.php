<div class="radius-20 stars-card d-flex align-items-center shadow-sm " style="padding-left: 1px; padding-right:1px; padding-top:1px;">
    @php
        $i = 5;
    @endphp

    @if((!empty($rate) and $rate > 0) or !empty($showRateStars))
        @if(empty($dontShowRate) or !$dontShowRate)
            <span class="radius-20 badge badge-primary1">
                {{-- <i data-feather="star" width="14" height="14" class="active mt-1"></i>  --}}
                {{-- <svg width="14" height="14" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_263_410)">
                    <path d="M14.6461 5.51542L9.78752 5.06481L7.85756 0.583302C7.72244 0.269504 7.27751 0.269504 7.1424 0.583302L5.21247 5.06484L0.353914 5.51542C0.0137192 5.54697 -0.123771 5.97011 0.132899 6.19558L3.79869 9.41594L2.7259 14.176C2.65078 14.5093 3.01073 14.7708 3.30448 14.5963L7.49999 12.1051L11.6955 14.5963C11.9893 14.7708 12.3492 14.5093 12.2741 14.176L11.2013 9.41594L14.8671 6.19558C15.1238 5.97011 14.9863 5.54697 14.6461 5.51542Z" fill="#FFDC64"/>
                    <path d="M7.85756 0.583302C7.72244 0.269504 7.27751 0.269504 7.1424 0.583302L5.21247 5.06484L0.353914 5.51542C0.0137192 5.54697 -0.123771 5.97011 0.132899 6.19558L3.79869 9.41594L2.7259 14.176C2.65078 14.5093 3.01073 14.7708 3.30448 14.5963L4.2409 14.0403C4.37051 8.70532 6.84931 4.94838 8.81185 2.7992L7.85756 0.583302Z" fill="#FFC850"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_263_410">
                    <rect width="14" height="14" fill="white"/>
                    </clipPath>
                    </defs>
                    </svg> --}}
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
