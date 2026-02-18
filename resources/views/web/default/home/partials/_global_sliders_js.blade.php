<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ========== 1. PATHSHALA SWIPER (YOUR ORIGINAL) ==========
    var swiper = new Swiper(".myJoinSwiper", {
        slidesPerView: 1,
        spaceBetween: 15,
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

    // ========== 2. BANNER SLIDER (YOUR ORIGINAL) ==========
    (function () {
        const slider = document.getElementById('bannerSlider');
        if (!slider) return;
        
        const wrapper = slider.querySelector('.banner-slides-wrapper');
        const slides = slider.querySelectorAll('.banner-slide');
        const dots = slider.querySelectorAll('.banner-dot');
        const total = slides.length;
        let current = 0;

        function goToSlide(index) {
            current = (index + total) % total;
            wrapper.style.transform = 'translateX(' + (-current * 100) + '%)';
            dots.forEach(d => d.classList.remove('active'));
            dots[current].classList.add('active');
        }

        slider.querySelector('[data-dir="next"]')?.addEventListener('click', function () {
            goToSlide(current + 1);
        });

        slider.querySelector('[data-dir="prev"]')?.addEventListener('click', function () {
            goToSlide(current - 1);
        });

        dots.forEach(dot => {
            dot.addEventListener('click', function () {
                const n = parseInt(this.getAttribute('data-slide'), 10);
                goToSlide(n);
            });
        });

        setInterval(function () {
            goToSlide(current + 1);
        }, 5000);
    })();

    // ========== 3. ENGLISH SLIDER FUNCTION (YOUR ORIGINAL) ==========
    function initSlider(rootId, perView = 2) {
        const slider = document.getElementById(rootId);
        if (!slider) return;

        const wrapper = slider.querySelector('.english-slides-wrapper');
        const slides = slider.querySelectorAll('.english-slide');
        const totalSlides = slides.length;
        
        if (!totalSlides) return;
        
        const totalPages = Math.max(1, Math.ceil(totalSlides / perView));
        const dotsContainer = slider.querySelector('.english-dots');
        
        if (!dotsContainer) return;
        
        // Create dots
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('div');
            dot.className = `english-dot ${i === 0 ? 'active' : ''}`;
            dot.dataset.page = i;
            dotsContainer.appendChild(dot);
        }

        const dots = dotsContainer.querySelectorAll('.english-dot');
        let currentPage = 0;
        let timer = null;
        const interval = 5000;

        function goTo(page) {
            currentPage = (page + totalPages) % totalPages;
            const index = currentPage * perView;
            const percent = (100 / perView) * index;
            wrapper.style.transform = 'translateX(' + (-percent) + '%)';

            dots.forEach(d => d.classList.remove('active'));
            dots[currentPage].classList.add('active');
        }

        function startAuto() {
            stopAuto();
            timer = setInterval(function () {
                goTo(currentPage + 1);
            }, interval);
        }

        function stopAuto() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        dots.forEach(dot => {
            dot.addEventListener('click', function () {
                const page = parseInt(this.dataset.page, 10);
                goTo(page);
                startAuto();
            });
        });

        slider.addEventListener('mouseenter', stopAuto);
        slider.addEventListener('mouseleave', startAuto);

        goTo(0);
        startAuto();
    }

    // ========== 4. CONSULTANT SWIPER (YOUR ORIGINAL) ==========
    const consultantSwiper = new Swiper('#consultantSlider', {
        slidesPerView: 2,
        spaceBetween: 10,
        breakpoints: {
            640: { slidesPerView: 1, spaceBetween: 20 },
            768: { slidesPerView: 2, spaceBetween: 30 },
            1024: { slidesPerView: 2, spaceBetween: 30 }
        },
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
        },
        loop: true,
        speed: 800,
        effect: 'slide',
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
        },
        grabCursor: true,
        lazy: true,
        centeredSlides: false
    });

    // ========== INITIALIZE ALL SLIDERS ==========
    initSlider('englishSlider', 2);  // Subscriptions/English
    initSlider('hindiSlider', 2);    // Hindi Webinars  
    initSlider('remediesSlider', 2); // Remedies
    // Add more: initSlider('instaSlider', 2);
});
</script>
 <script>
      document.addEventListener('DOMContentLoaded', function() {
        const consultantSwiper = new Swiper('#consultantSlider', {
          // Slides per view
          slidesPerView: 2,
          spaceBetween: 10,
          
          breakpoints: {
            // Mobile (>=640px)
            640: {
              slidesPerView: 1,
              spaceBetween: 20
            },
            // Tablet (>=768px)
            768: {
              slidesPerView: 2,
              spaceBetween: 30
            },
            // Desktop (>=1024px)
            1024: {
              slidesPerView: 2,
              spaceBetween: 30
            }
          },

          autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
          },

          loop: true,
          
          speed: 800,
          
          effect: 'slide',

          pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true
          },

          grabCursor: true,
          lazy: true,

          centeredSlides: false,
        });
      });
    </script>

    <script>
  (function () {
    function initSlider(rootId, perView) {
      const slider = document.getElementById(rootId);
      if (!slider) return;

      const wrapper = slider.querySelector('.english-slides-wrapper');
      const slides = slider.querySelectorAll('.english-slide');
      const totalSlides = slides.length;
      if (!totalSlides) return;

      const totalPages = Math.max(1, Math.ceil(totalSlides / perView));
      const dotsContainer = slider.querySelector('.english-dots');
      if (!dotsContainer) return;

      dotsContainer.innerHTML = '';
      for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement('div');
        dot.className = 'english-dot' + (i === 0 ? ' active' : '');
        dot.dataset.page = i;
        dotsContainer.appendChild(dot);
      }

      const dots = dotsContainer.querySelectorAll('.english-dot');
      let currentPage = 0;
      let timer = null;
      const interval = 5000;

      function goTo(page) {
        currentPage = (page + totalPages) % totalPages;
        const index = currentPage * perView;
        const percent = (100 / perView) * index;
        wrapper.style.transform = 'translateX(' + (-percent) + '%)';

        dots.forEach(d => d.classList.remove('active'));
        dots[currentPage].classList.add('active');
      }

      function startAuto() {
        stopAuto();
        timer = setInterval(function () {
          goTo(currentPage + 1);
        }, interval);
      }

      function stopAuto() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }

      dots.forEach(dot => {
        dot.addEventListener('click', function () {
          const page = parseInt(this.dataset.page, 10);
          goTo(page);
          startAuto();
        });
      });

      slider.addEventListener('mouseenter', stopAuto);
      slider.addEventListener('mouseleave', startAuto);

      goTo(0);
      startAuto();
    }

    // Initialize all sliders with 2 cards per view
    initSlider('englishSlider', 2);
    initSlider('hindiSlider', 2);
    initSlider('remediesSlider', 2);
    initSlider('consultantSlider', 2);
    initSlider('instaSlider', 2); // Instagram feed slider
    initSlider('mediaSlider', 2);
    
    // Knowledge slider with 1 card per view
    initSlider('knowledgeSlider', 1);
  })();
</script>
   
<style>

/* only knowledge slider: 1 card per view */
#knowledgeSlider .english-slide {
  flex: 0 0 100%;
  box-sizing: border-box;
}

</style>

<script>
(function () {
  // 0) Helper: clear all intervals (THIS WILL STOP any setInterval on page)
  // It's a blunt tool but effective if leftover timers are causing autoplay.
  (function clearAllIntervals() {
    try {
      // Get highest interval id, then clear all from 0..maxId
      const highestId = window.setInterval(() => {}, 9999);
      for (let i = 0; i <= highestId; i++) {
        try { window.clearInterval(i); } catch (e) {}
      }
      console.info('[slider-fix] cleared intervals up to', highestId);
    } catch (e) {
      console.warn('[slider-fix] clearAllIntervals failed', e);
    }
  })();

  // 1) Disable common autoplay data attributes (for third-party sliders)
  document.querySelectorAll('[data-autoplay],[data-ride],[data-slick]').forEach(el => {
    try {
      el.setAttribute('data-autoplay', 'false');
      el.removeAttribute('data-ride');
      el.removeAttribute('data-slick');
    } catch (e) {}
  });

  // 2) Remove CSS animations from potential wrappers (prevents CSS-driven motion)
  document.querySelectorAll('.english-slides-wrapper, .slides-wrapper, .slider-wrapper, .slick-track').forEach(el => {
    el.style.animation = 'none';
    el.style.webkitAnimation = 'none';
    el.style.transition = el.style.transition; // keep transitions if you want smooth manual translate
    el.style.transform = el.style.transform; // preserve current transform
  });

  // 3) If using Swiper/Slick/Owl, attempt to stop them (best-effort)
  try {
    if (window.jQuery) {
      try {
        // Stop slick autoplay if present
        if (typeof window.jQuery('.slick-slider').slick === 'function') {
          window.jQuery('.slick-slider').slick('slickPause');
          console.info('[slider-fix] called slickPause');
        }
      } catch (e) {}
      try {
        // Stop Owl autoplay
        if (typeof window.jQuery('.owl-carousel').trigger === 'function') {
          window.jQuery('.owl-carousel').trigger('stop.owl.autoplay');
          console.info('[slider-fix] triggered stop.owl.autoplay');
        }
      } catch (e) {}
    }
    if (window.Swiper && window.mySwipers) {
      // If your code stored swipers in global, try to stop them
      (window.mySwipers || []).forEach(s => {
        try { s.autoplay && s.autoplay.stop && s.autoplay.stop(); console.info('[slider-fix] Stopped Swiper instance'); } catch(e){}
      });
    }
  } catch (e) {}

  // 4) Reinitialize your slider in manual-only mode (dots + touch)
  function initManualOnlySlider(rootId, perView) {
    const slider = document.getElementById(rootId);
    if (!slider) return;

    const wrapper = slider.querySelector('.english-slides-wrapper');
    const slides = slider.querySelectorAll('.english-slide');
    if (!wrapper || !slides || slides.length === 0) return;

    // remove any inline autoplay-starting attributes or functions
    try {
      slider.removeAttribute('data-autoplay');
      slider.removeAttribute('autoplay');
    } catch (e) {}

    const totalSlides = slides.length;
    const totalPages = Math.max(1, Math.ceil(totalSlides / perView));

    // build dots if not present or reset
    let dotsContainer = slider.querySelector('.english-dots');
    if (!dotsContainer) {
      dotsContainer = document.createElement('div');
      dotsContainer.className = 'english-dots';
      slider.appendChild(dotsContainer);
    }
    dotsContainer.innerHTML = '';
    for (let i = 0; i < totalPages; i++) {
      const dot = document.createElement('div');
      dot.className = 'english-dot' + (i === 0 ? ' active' : '');
      dot.dataset.page = i;
      dotsContainer.appendChild(dot);
    }

    const dots = dotsContainer.querySelectorAll('.english-dot');
    let currentPage = 0;

    function goTo(page) {
      currentPage = (page + totalPages) % totalPages;
      const index = currentPage * perView;
      const percent = (100 / perView) * index;
      wrapper.style.transform = 'translateX(' + (-percent) + '%)';
      dots.forEach(d => d.classList.remove('active'));
      dots[currentPage] && dots[currentPage].classList.add('active');
    }

    // dot click
    dots.forEach(dot => {
      dot.addEventListener('click', function () {
        const page = parseInt(this.dataset.page, 10);
        goTo(page);
      });
    });

    // touch swipe
    let startX = 0;
    slider.addEventListener('touchstart', function (e) {
      startX = e.touches[0].clientX;
    }, {passive: true});

    slider.addEventListener('touchend', function (e) {
      const endX = e.changedTouches[0].clientX;
      const diff = startX - endX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) goTo(currentPage + 1);
        else goTo(currentPage - 1);
      }
    }, {passive: true});

    // ensure wrapper has no CSS animation class that might move it
    wrapper.classList.remove('autoplaying', 'animating');
    wrapper.style.animation = 'none';

    // initial position
    goTo(0);

    // debug
    console.info('[slider-fix] initialized manual slider for', rootId, 'pages:', totalPages);
  }

  // call for your sliders
  const slidersToInit = [
    { id: 'englishSlider', perView: 2 },
    { id: 'hindiSlider', perView: 2 },
    { id: 'remediesSlider', perView: 2 },
    { id: 'consultantSlider', perView: 2 },
    { id: 'mediaSlider', perView: 2 },
    { id: 'knowledgeSlider', perView: 1 }
  ];
  slidersToInit.forEach(s => initManualOnlySlider(s.id, s.perView));

  // final debug scan: list elements that look like sliders and their inline styles
  document.querySelectorAll('.english-slides-wrapper, .slides-wrapper, .slick-track').forEach(el => {
    console.info('[slider-fix] wrapper inline style:', el.getAttribute('style'));
  });

})();
</script>