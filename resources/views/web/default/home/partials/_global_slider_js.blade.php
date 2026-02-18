<script>
function initCustomSlider(sliderId, slidesPerView = 2, interval = 5000) {
    const slider = document.getElementById(sliderId);
    const wrapper = slider.querySelector('.slides-wrapper, .english-slides-wrapper');
    const slides = slider.querySelectorAll('.slide, .english-slide');
    const totalSlides = slides.length;
    const totalPages = Math.ceil(totalSlides / slidesPerView);
    
    const dotsContainer = slider.querySelector('.dots, .english-dots');
    
    // Create dots
    for (let i = 0; i < totalPages; i++) {
        const dot = document.createElement('div');
        dot.className = `dot ${i === 0 ? 'active' : ''}`;
        dot.dataset.page = i;
        dotsContainer.appendChild(dot);
    }
    
    let currentPage = 0;
    let timer = null;
    
    function goTo(page) {
        currentPage = (page + totalPages) % totalPages;
        const index = currentPage * slidesPerView;
        const percent = (100 / slidesPerView) * index;
        wrapper.style.transform = `translateX(${-percent}%)`;
        
        dotsContainer.querySelectorAll('.dot').forEach((d, idx) => {
            d.classList.toggle('active', idx === currentPage);
        });
    }
    
    function startAuto() {
        timer = setInterval(() => goTo(currentPage + 1), interval);
    }
    
    function stopAuto() {
        if (timer) clearInterval(timer);
    }
    
    dotsContainer.querySelectorAll('.dot').forEach((dot, idx) => {
        dot.addEventListener('click', () => {
            goTo(idx);
            startAuto();
        });
    });
    
    slider.addEventListener('mouseenter', stopAuto);
    slider.addEventListener('mouseleave', startAuto);
    
    goTo(0);
    startAuto();
}
</script>]
<script>
  (function () {
    const slider = document.getElementById('bannerSlider');
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

    slider.querySelector('[data-dir="next"]').addEventListener('click', function () {
      goToSlide(current + 1);
    });

    slider.querySelector('[data-dir="prev"]').addEventListener('click', function () {
      goToSlide(current - 1);
    });

    dots.forEach(dot => {
      dot.addEventListener('click', function () {
        const n = parseInt(this.getAttribute('data-slide'), 10);
        goToSlide(n);
      });
    });

    // optional auto slide
    setInterval(function () {
      goToSlide(current + 1);
    }, 5000);
  })();
</script>