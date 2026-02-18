<style>
/* ========== MOBILE FIRST RESPONSIVE - सभी Devices पर Perfect ========== */

/* BASE RESPONSIVE */
* { box-sizing: border-box; }
html { font-size: 16px; scroll-behavior: smooth; }
body { 
    margin: 0; padding: 0; 
    font-family: 'Inter', sans-serif; 
    line-height: 1.5; 
    overflow-x: hidden;
}

/* ========== CONTAINER RESPONSIVE ========== */
.home-container1, .home-home {
    width: 100vw;
    max-width: 100%;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* ========== SLIDER RESPONSIVE ========== */
.myJoinSwiper, .english-slider, .consultant-slider, .banner-slider {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    padding: 0 8px;
    margin: 0 auto;
}

/* ========== CARDS RESPONSIVE ========== */
.english-slide, .consultant-slide {
    flex: 0 0 48%;
    padding: 0 4px;
    box-sizing: border-box;
}

.home-group8031, .home-group10 {
    width: 100%;
    height: auto;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* ========== TEXT RESPONSIVE ========== */
.home-text160 { 
    font-size: clamp(12px, 3.5vw, 14px); 
    font-weight: 600; 
    line-height: 1.3;
}
.home-text161 { 
    font-size: clamp(14px, 4vw, 16px); 
    line-height: 1.3; 
    margin: 8px 0;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ========== STARS & PRICE ========== */
.home-rating1 {
    gap: clamp(2px, 1vw, 4px);
    align-items: center;
}
.grid-star {
    width: clamp(10px, 2.5vw, 12px);
    height: clamp(10px, 2.5vw, 12px);
}
.home-text163 { font-size: clamp(12px, 3vw, 14px); margin-left: 4px; }
.home-text164 { 
    font-size: clamp(13px, 3.5vw, 15px); 
    font-weight: 600;
}

/* ========== BUTTONS ========== */
.home-framebutton1 {
    width: 100%;
    padding: clamp(10px, 3vw, 14px);
    background: linear-gradient(135deg, #1fb36a, #32a128);
    border-radius: 12px;
    text-align: center;
    margin-top: auto;
}
.home-text165 {
    color: white;
    font-size: clamp(13px, 3.5vw, 15px);
    font-weight: 600;
}

/* ========== CATEGORIES RESPONSIVE ========== */
.home-categories-section {
    display: flex;
    flex-wrap: wrap;
    gap: clamp(8px, 2vw, 12px);
    justify-content: center;
    padding: 20px 12px;
    width: 100%;
}
.frame1000001705-thq-frame1000001705-elm > a {
    flex: 1 1 calc(33.333% - 8px);
    min-width: 100px;
    max-width: 140px;
}

/* ========== RESPONSIVE BREAKPOINTS ========== */

/* MOBILE PORTRAIT (320px - 480px) */
@media (max-width: 480px) {
    .english-slide, .consultant-slide { flex: 0 0 100%; padding: 0 2px; }
    .home-frame10000016161 { padding: 12px !important; left: 8px !important; }
    .home-ellipse11 { width: 45px !important; height: 45px !important; }
    .home-vector11 { width: 30px; height: 25px !important; }
    section.home-categories-section { gap: 6px !important; }
}

/* MOBILE LANDSCAPE (481px - 768px) */
@media (min-width: 481px) and (max-width: 768px) {
    .english-slide, .consultant-slide { flex: 0 0 48%; }
    .frame1000001705-thq-frame1000001705-elm > a { flex: 1 1 calc(33% - 10px); }
    .home-frame10000016131 { padding-right: 55px; }
}

/* TABLET (769px - 1024px) */
@media (min-width: 769px) and (max-width: 1024px) {
    .english-slide, .consultant-slide { flex: 0 0 33.333%; }
    .home-frame10000016141 { width: 85%; }
    .home-frame10000016131 { padding-right: 70px; }
}

/* DESKTOP (1025px+) */
@media (min-width: 1025px) {
    .home-container1 { max-width: 1200px; margin: 0 auto; }
    .english-slide, .consultant-slide { flex: 0 0 25%; }
    .home-frame10000016141 { width: 70%; }
}

/* ========== PERFECT IMAGE RESPONSIVE ========== */
.home-rectangle61, .home-rectangle512, .home-pexelsdavidbartus586687111 {
    width: 100% !important;
    height: auto;
    object-fit: cover;
    aspect-ratio: 16/9;
}

.home-ellipse52, .home-ellipse13 {
    width: clamp(35px, 8vw, 45px) !important;
    height: clamp(35px, 8vw, 45px) !important;
    object-fit: cover;
}

/* ========== ARROW RESPONSIVE ========== */
.home-vector15 {
    right: clamp(10px, 4vw, 20px) !important;
    width: clamp(35px, 8vw, 45px) !important;
    height: clamp(35px, 8vw, 45px) !important;
}

/* ========== PERFECT SPACING ========== */
section { margin-bottom: clamp(20px, 6vw, 40px); }
.mt-10 { margin-top: clamp(20px, 5vw, 40px); }
.mb-10 { margin-bottom: clamp(15px, 4vw, 25px); }

/* ========== PREVENT HORIZONTAL SCROLL ========== */
* { max-width: 100%; }
img { max-width: 100%; height: auto; }
</style>
