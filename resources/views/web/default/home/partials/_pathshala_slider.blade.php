@if(!empty($data))
<div class="swiper myJoinSwiper">
    <div class="swiper-wrapper">
        @foreach($data as $offer)
        <div class="swiper-slide mt-10">
            <div class="home-frame10000016141" style="width: auto !important;">
                <div class="home-frame10000016131" style="width: fit-content !important;">
                    <span class="home-text106" style="font-family:Inter !important;width: fit-content !important;">
                        {{ $offer['title'] ?? 'Title Missing' }}
                    </span>
                    <span class="home-text107" style="font-family:Inter !important;">
                        <span class="home-text108">
                            {{ $offer['subtitle'] ?? '' }} {{ $offer['price'] ?? '' }}
                        </span>
                    </span>
                </div>
                <a href="/subscriptions/asttrolok-pathshala">
                    <img src="public/Arrow.svg" alt="Vector1174" class="home-vector15" 
                         style="left:85% !important;top:30% !important;width:40px!important;height:40px!important;">
                </a>
            </div>
        </div>
        @php break; @endphp
        @endforeach
    </div>
</div>
<script>
var swiper = new Swiper(".myJoinSwiper", {
    slidesPerView: 1, spaceBetween: 15, loop: true,
    autoplay: { delay: 2500, disableOnInteraction: false },
    pagination: { el: ".swiper-pagination", clickable: true }
});
</script>
@endif
