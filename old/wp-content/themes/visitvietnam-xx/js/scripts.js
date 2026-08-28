jQuery(document).ready(function($) {
	
	$("#menubtn").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("open");
		$("body").toggleClass("menu_open");
		$("#header .head_right").slideToggle(400);		
		
		
	});	
	
		
	var swiper = new Swiper("#home_slider .mySwiper", {
	 autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".swiper-pagination",
		clickable:true,
      },
    });	
	
	
	var swipe2 = new Swiper(".explore_slider", {
      slidesPerView: 4,
      spaceBetween: 38,
	  loop: true,
	  allowTouchMove: true,
      navigation: {
        nextEl: "#explore .swiper-button-next",
        prevEl: "#explore .swiper-button-prev",
      },
	  autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
	  breakpoints: {
		// when window width is >= 320px
		320: {
		  slidesPerView: 1,
		  spaceBetween: 20
		},
		// when window width is >= 480px
		480: {
		  slidesPerView: 1,
		  spaceBetween: 20
		},
		// when window width is >= 640px
		640: {
		  slidesPerView: 3,
		  spaceBetween: 38
		},
		1024: {
		  slidesPerView: 4,
		  spaceBetween: 38
		}
	  }
    });
	
	var swiper3 = new Swiper(".we_make_slider .mySwiper2", {
	 autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".swiper-pagination",
		clickable:true,
      },
    });	
	
	
});