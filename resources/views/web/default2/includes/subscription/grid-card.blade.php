<style>
/* ========================================
   PERFECT IMAGE COVER FIX
   ======================================== */

/* Image Box - Proper Dimensions */
.webinar-card .image-box {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 60%; /* 5:3 ratio for better visibility */
    overflow: hidden;
    background-color: #f5f5f5;
    border-radius: 8px 8px 0 0;
}

/* Link takes full space */
.webinar-card .image-box > a {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: block;
}

/* Image Cover - Perfect Fit */
.webinar-card .image-box img,
.webinar-card .image-box img.img-cover {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    object-position: center center !important;
    border-radius: 8px 8px 0 0 !important;
}

/* Progress Bar */
.webinar-card .progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background-color: rgba(0, 0, 0, 0.1);
    z-index: 2;
}

.webinar-card .progress-bar {
    height: 100%;
    background-color: #1cb34f;
}

/* ========================================
   CARD & BODY
   ======================================== */

.webinar-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    background-color: #fff;
}

.webinar-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.webinar-card figure {
    margin: 0;
}

.webinar-card figcaption.webinar-card-body {
    padding: 16px;
    background-color: #fff;
}

/* ========================================
   USER AVATAR
   ======================================== */

.user-inline-avatar {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-inline-avatar .avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.user-inline-avatar .avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-inline-avatar .user-name {
    color: #374253;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

.user-inline-avatar .user-name:hover {
    color: #1a73e8;
}

/* ========================================
   TITLE & CATEGORY
   ======================================== */

.webinar-card .webinar-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e3a5f;
    line-height: 1.5;
    margin: 10px 0 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.webinar-card a {
    text-decoration: none;
}

.webinar-card a:hover .webinar-title {
    color: #1a73e8;
}

.webinar-card .d-block.font-14 {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 10px;
}

.webinar-card .text-decoration-underline {
    color: #1a73e8;
    text-decoration: underline;
}

/* ========================================
   STAR RATING
   ======================================== */

.stars-card {
    display: flex;
    align-items: center;
    gap: 3px;
    margin: 8px 0;
}

.stars-card svg.active.grid-star,
.stars-card svg.active {
    color: #ffc107;
    fill: #ffc107;
    stroke: #ffc107;
}

.stars-card svg.grid-star {
    color: #e0e0e0;
    fill: none;
    stroke: #e0e0e0;
}

.stars-card svg {
    width: 16px;
    height: 16px;
}

/* ========================================
   RATING BADGE
   ======================================== */

.rating-course {
    background-color: #4caf50;
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 10px;
}

.rating-course svg.active {
    fill: white;
    stroke: white;
    width: 14px;
    height: 14px;
}

/* ========================================
   PRICE BOX
   ======================================== */

.webinar-price-box {
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.webinar-price-box .real {
    font-size: 20px;
    font-weight: 700;
    color: #059669;
}

.webinar-price-box .off {
    font-size: 15px;
    color: #9ca3af;
    text-decoration: line-through;
}

/* ========================================
   RESPONSIVE
   ======================================== */

/* Mobile */
@media (max-width: 767px) {
    .webinar-card .image-box {
        padding-bottom: 56.25%; /* 16:9 for mobile */
    }
    
    .webinar-card figcaption.webinar-card-body {
        padding: 14px;
    }
}

/* Tablet */
@media (min-width: 768px) and (max-width: 991px) {
    .webinar-card .image-box {
        padding-bottom: 58%;
    }
}

/* Desktop */
@media (min-width: 992px) {
    .webinar-card .image-box {
        padding-bottom: 60%; /* 5:3 ratio */
    }
}

/* Large Desktop */
@media (min-width: 1200px) {
    .webinar-card .image-box {
        padding-bottom: 62%;
    }
}

/* Show full image without cropping */
.webinar-card .image-box img {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: contain !important; /* CONTAIN instead of COVER */
    object-position: center center !important;
    background-color: #f5f5f5; /* Background color visible */
}

</style>

<div class="webinar-card">
    <figure>
        <div class="image-box" style="height: 100% !important;">
            {{-- Subscription Link with Image --}}
            <a href="{{ $subscription->getUrl() }}">
                <img loading="lazy" 
                     decoding="async" 
                     src="{{ !empty($subscription->image) ? config('app.img_dynamic_url') . $subscription->image : 'https://storage.googleapis.com/astrolok/webp/store/1/1/Pathshala-min (1).webp' }}" 
                     class="img-cover" 
                     alt="{{ $subscription->title }}" >
            </a>

            {{-- Progress Bar (if needed) --}}
            @if(!empty($subscription->progress))
                <div class="progress">
                    <span class="progress-bar" style="width: {{ $subscription->progress }}%"></span>
                </div>
            @endif
        </div>

        <figcaption class="webinar-card-body">
            {{-- Teacher/Creator Avatar and Name --}}
            <div class="user-inline-avatar d-flex align-items-center">
                <div class="avatar bg-gray200">
                    <img loading="lazy" 
                         decoding="async" 
                         src="{{ !empty($subscription->teacher) ? config('app.img_dynamic_url') . $subscription->teacher->getAvatar() : 'https://storage.googleapis.com/astrolok/store/1/astrologer_mobile/Alok Sir.jpg' }}" 
                         class="img-cover" 
                         alt="{{ $subscription->teacher->full_name ?? 'Mr.Alok Khandelwal' }}">
                </div>
                <a href="{{ !empty($subscription->teacher) ? $subscription->teacher->getProfileUrl() : '/users/1015/astrologer-mr.alok-khandelwal' }}" 
                   target="_blank" 
                   class="user-name ml-5 font-14">
                    {{ $subscription->teacher->full_name ?? 'Mr.Alok Khandelwal' }}
                </a>
            </div>

            {{-- Subscription Title --}}
            <a href="{{ $subscription->getUrl() }}">
                <h3 class="mt-5 webinar-title font-weight-bold font-16 text-dark-blue">
                    {{ $subscription->title }}
                </h3>
            </a>

            {{-- Category --}}
            @if(!empty($subscription->category))
                <span class="d-block font-14 mt-5">
                    in 
                    <a href="{{ $subscription->category->getUrl() }}" 
                       target="_blank" 
                       class="text-decoration-underline">
                        {{ $subscription->category->title }}
                    </a>
                </span>
            @else
                <span class="d-block font-14 mt-5">
                    in 
                    <a href="/categories/astrology/Astrology-Basic" 
                       target="_blank" 
                       class="text-decoration-underline">
                        Astrology
                    </a>
                </span>
            @endif

            {{-- Star Rating --}}
            <div class="stars-card d-flex align-items-center mt-5">
                @php
                    $rating = $subscription->getRate() ?? 4.3;
                    $fullStars = floor($rating);
                @endphp

                {{-- Full Stars --}}
                @for($i = 0; $i < $fullStars; $i++)
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active grid-star">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                @endfor

                {{-- Empty Stars --}}
                @for($i = $fullStars; $i < 5; $i++)
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star grid-star">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                @endfor

                {{-- Rating Badge --}}
                <span class="badge badge-primary ml-10 rating-course" style="display:inline-flex; align-items:center; gap:4px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star active">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" style="margin-top:-2px;"></polygon>
                    </svg> 
                    {{ number_format($rating, 1) }}
                </span>
            </div>

            {{-- Price Box --}}
            <div class="webinar-price-box mt-5">
                @if(!empty($subscription->price) && $subscription->price > 0)
                    <span class="real">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
                    
                    @if(!empty($subscription->discount) && $subscription->discount > 0)
                        <span class="off ml-10">{{ handlePrice($subscription->price, true, true, false, null, true) }}</span>
                    @endif
                @else
                    <span class="real">{{ trans('public.free') }}</span>
                @endif
            </div>
        </figcaption>
    </figure>
</div>