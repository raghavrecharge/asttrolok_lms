"use strict";

$("#slider1").owlCarousel({
  items: 3,
  nav: false,
   touchDrag: true,
  navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
  responsive: {
            0: {
                items: 1
            },
            768: {
                items: 1
            },
            1000: {
                items: 2
            }
        }
});

$("#slider2").owlCarousel({
  items: 3,
  nav: false,
  navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
  responsive: {
            0: {
                items: 2
            },
            768: {
                items: 2
            },
            1000: {
                items: 3,
      dots: true,
      mouseDrag: true
            }
        }
});
