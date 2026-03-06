@extends('web.default2.layouts.app')

@push('styles_top')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" data-tag="font" />
<style>
.ps-section {
    background: #f5f5f5;
    padding: 50px 0 60px;
    font-family: 'Inter', sans-serif;
}
.ps-wrapper {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    align-items: flex-start;
    gap: 0;
}

/* Banner */
.ps-banner {
    flex: 0 0 36%;
    max-width: 36%;
    border-radius: 18px;
    overflow: hidden;
}
.ps-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    min-height: 480px;
}

/* Cards area */
.ps-cards {
    flex: 1;
    display: flex;
    gap: 0;
}
.ps-card {
    flex: 1;
    padding: 32px 30px 28px;
    display: flex;
    flex-direction: column;
}

/* Green pill badge */
.ps-badge {
    display: inline-block;
    border: 1.5px solid #32a028;
    color: #32a028;
    font-size: 11px;
    font-weight: 600;
    padding: 5px 16px;
    border-radius: 20px;
    margin-bottom: 16px;
    line-height: 1.3;
}

/* Title */
.ps-title {
    font-size: 26px;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0 0 14px;
    line-height: 1.2;
}

/* Description */
.ps-desc {
    font-size: 13.5px;
    line-height: 1.7;
    color: #444;
    margin: 0 0 22px;
}
.ps-desc strong {
    font-weight: 700;
    color: #1a1a1a;
}

/* Feature list */
.ps-features {
    list-style: none;
    padding: 0;
    margin: 0 0 24px;
}
.ps-features li {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    padding: 6px 0;
    padding-left: 18px;
    position: relative;
    line-height: 1.5;
}
.ps-features li::before {
    content: '';
    position: absolute;
    left: 0;
    top: 12px;
    width: 0;
    height: 0;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
    border-left: 7px solid #32a028;
}

/* CTA button */
.ps-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #32a028;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    padding: 12px 28px;
    border-radius: 25px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: background .2s ease, transform .15s ease;
    margin-top: 4px;
    width: fit-content;
}
.ps-cta:hover {
    background: #278a1e;
    color: #fff;
    text-decoration: none;
    transform: scale(1.03);
}
.ps-cta svg {
    width: 16px;
    height: 16px;
    fill: #fff;
}

/* Stats row */
.ps-stats {
    display: flex;
    gap: 24px;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #e5e5e5;
}
.ps-stat {
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.ps-stat__num {
    font-size: 22px;
    font-weight: 800;
    color: #1a1a1a;
}
.ps-stat__label {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a1a;
}
.ps-stat__sub {
    font-size: 11px;
    color: #888;
    font-style: italic;
}

/* Divider between cards */
.ps-divider {
    width: 1px;
    background: #e0e0e0;
    align-self: stretch;
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .ps-wrapper {
        flex-direction: column;
        gap: 30px;
    }
    .ps-banner {
        flex: none;
        max-width: 100%;
    }
    .ps-banner img {
        min-height: 300px;
    }
    .ps-cards {
        flex-direction: row;
        background: #fff;
        border-radius: 12px;
    }
}
@media (max-width: 768px) {
    .ps-section {
        padding: 24px 0 36px;
    }
    .ps-cards {
        flex-direction: column;
    }
    .ps-divider {
        width: 100%;
        height: 1px;
    }
    .ps-card {
        padding: 24px 20px 20px;
    }
    .ps-title {
        font-size: 22px;
    }
    .ps-stats {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
@endpush

@section('content')

<section class="ps-section">
    <div class="ps-wrapper">

        {{-- LEFT: Banner image --}}
        <div class="ps-banner">
            @php
                $bannerImage = $beginner ? $beginner->getImage() : '';
                $bannerImage = str_replace('webp/', '', $bannerImage);
            @endphp
            <img src="{{ config('app.img_dynamic_url') }}{{ $bannerImage }}" alt="Asttrolok Pathshala" />
        </div>

        {{-- RIGHT: Two course cards --}}
        <div class="ps-cards">

            {{-- ========== PATHSHALA 1 ========== --}}
            <div class="ps-card">
                <span class="ps-badge">Become an Astrologer for Just &#8377;2,100/Month</span>

                <h2 class="ps-title">Asttrolok Pathshala</h2>

                <p class="ps-desc">
                    Asttrolok Pathshala is a monthly subscription-based learning program designed to turn astrology enthusiasts into certified practitioners. With structured modules, practical sessions, and one-on-one mentor guidance, you'll master the logic behind <strong>Vedic astrology not superstition</strong>
                </p>

                <ul class="ps-features">
                    <li>Certificate: Awarded on completion</li>
                    <li>Duration: Learn at your own pace</li>
                    <li>Language: Hindi + English</li>
                    <li>Study Material: Included</li>
                </ul>

                @if($hasBoughtBeginner)
                    <a href="{{ $beginner->getLearningPageUrl1() }}" class="ps-cta">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="#fff" stroke-width="2" fill="none"/><line x1="16.5" y1="16.5" x2="21" y2="21" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        Start Learning
                    </a>
                @else
                    <a href="/course/astrology-basic-level" class="ps-cta">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="#fff" stroke-width="2" fill="none"/><line x1="16.5" y1="16.5" x2="21" y2="21" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        Enroll Now
                    </a>
                @endif

                <div class="ps-stats">
                    <div class="ps-stat">
                        <span class="ps-stat__num">| 714+</span>
                        <div>
                            <span class="ps-stat__label">Students</span><br>
                            <span class="ps-stat__sub">already learning</span>
                        </div>
                    </div>
                    <div class="ps-stat">
                        <span class="ps-stat__num">| &#8377;2,100</span>
                        <div>
                            <span class="ps-stat__label">/ Month</span><br>
                            <span class="ps-stat__sub">Beginner to Expert Journey</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vertical divider --}}
            <div class="ps-divider"></div>

            {{-- ========== PATHSHALA 2 ========== --}}
            <div class="ps-card">
                <span class="ps-badge">Become an Astrologer for Just &#8377;2,100/Month</span>

                <h2 class="ps-title">Pathshala 2</h2>

                <p class="ps-desc">
                    Pathshala 2 is a deep dive into essential Dasha systems - Vimshottari, Yogini, and Chara - progressing toward advanced, multi-layered chart analysis. This monthly, subscription-based learning program blends expert-led video lessons, real-world case studies, and assessments to help you develop professional-level astrology skills.
                </p>

                <ul class="ps-features">
                    <li>Certificate: Awarded on completion</li>
                    <li>Duration: Learn at your own pace</li>
                    <li>Language: Hindi + English</li>
                    <li>Study Material: Included</li>
                </ul>

                @if($hasBoughtAdvanced)
                    <a href="{{ $advanced->getLearningPageUrl1() }}" class="ps-cta">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="#fff" stroke-width="2" fill="none"/><line x1="16.5" y1="16.5" x2="21" y2="21" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        Continue Learning
                    </a>
                @else
                    <a href="/course/astrology-intermediate-level" class="ps-cta">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="#fff" stroke-width="2" fill="none"/><line x1="16.5" y1="16.5" x2="21" y2="21" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        Enroll Now
                    </a>
                @endif
            </div>

        </div>{{-- /.ps-cards --}}

    </div>{{-- /.ps-wrapper --}}
</section>

@endsection
