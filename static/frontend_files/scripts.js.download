jQuery(document).ready(function($) {

	$(".city_tab .tab_btn").click(function (e) {
		e.preventDefault();
		$(".city_tab .tab_btn").removeClass('active')
		if ($(this).hasClass('active') ) {
				$(this).removeClass('active')
			} else { 
				$(this).addClass('active');
				
			}
		var href = $(this).attr("href");
		//alert(href);
		$('.tab_content').removeClass('show_div');
		$(href).addClass('show_div');
		
		//$(href).show();		
		//jQuery('html, body').animate({scrollTop: $(href).offset().top}, 1000);
		//return false;
	});


	$("#city_wrap .city_feat ul li a").click(function (e) {
		e.preventDefault();
		$(this).toggleClass('active_btn')
		if ($("#city_wrap .city_feat ul a").hasClass('active_btn') ) {
			$("#city_wrap .city_filter .clear_all_btn").addClass('show_btn');
		} else { 
			
			$("#city_wrap .city_filter .clear_all_btn").removeClass('show_btn');
			
		}
		
	});
	
	$("#images_data a[data-fancybox]").click(function(e) {
        $(".fancybox-container button[data-fancybox-close]").click();
    });


	$("#city_wrap .district_block_1 .cap_2 .btn_wrap .view_btn").click(function (e) {
		e.preventDefault();
		$("#city_wrap .city_app_list").removeClass('active');
		$("#city_wrap > .container > .city_display_title").addClass('hide_div');
		$("#city_wrap .show_div > .city_display_title_left").addClass('hide_div')
		if ($(this).parent().parent().parent().parent().parent().parent().parent().hasClass('active') ) {
			$(this).parent().parent().parent().parent().parent().parent().parent().removeClass('active')
		} else { 
			$(this).parent().parent().parent().parent().parent().parent().parent().addClass('active');
			
		}
		var href = $(this).attr("href");
		$('.city_app_list_filtred').removeClass('show_div');
		$(href).addClass('show_div');
		
	
	});


	$("#city_wrap .city_app_list_filtred  .city_display_title_left .back_btn").click(function (e) {
		e.preventDefault();
		$("#city_wrap .city_app_list").removeClass('active');
		$("#city_wrap > .container > .city_display_title").removeClass('hide_div');
		$("#city_wrap .show_div > .city_display_title_left").removeClass('hide_div')
		$('.city_app_list_filtred').removeClass('show_div');
	});
	
	
	
	//dragToClose:false,
		//ClickAction:"toggleZoom",
	
	
	/* $('#app_rightbar_inn').stickyMojo({
		 footerID: '#footer', 
		 contentID: '#app_leftbar',
		 offsetTop: 280
	 }); */
	
	
	$("#menubtn").click(function(e) {
		e.preventDefault();
		$(this).toggleClass("open");
		$("body").toggleClass("menu_open");
		$("#header .head_right").slideToggle(400);		
		
		
	});	
	
	/*$("#home_slider .book_block .room_btn").click(function(e) {
		e.preventDefault();
		$('.book_dropdown').slideUp(400);
		$(this).parent().find('.book_dropdown').slideToggle(400);
	});	*/
	
	$("#home_slider .book_block .room_btn").click(function (e) {
			e.preventDefault();
			//$(this).parent().find('.book_dropdown').slideDown(400);
			
			if ($(this).parent().hasClass('open') ) {
				$(this).parent().removeClass('open');
				$('body').removeClass('book_overlay_open');
				
			} else { 
				$("#home_slider .book_block .input_wrap").removeClass("open");
				
				$(this).parent().addClass('open');
				$('body').addClass('book_overlay_open');
			}			
	});	
	
	$(".app_book_block .guest_opt .room_btn").click(function (e) {
			e.preventDefault();
			$(this).parent().find('.rooom_dropdown').slideToggle(400);
			
			if ($(this).parent().hasClass('open') ) {
				$(this).parent().removeClass('open');
				//$('body').removeClass('book_overlay_open');
				
			} else { 
				$(".app_book_block .guest_opt").removeClass("open");
				
				$(this).parent().addClass('open');
				//$('body').addClass('book_overlay_open');
			}			
	});	
	
	/*
	$(document).on('click', function (e) {
		if ($(e.target).closest(".choose_city_sel").length === 0) {
			$(".choose_city_sel").removeClass("open");
			
		}
	});
	
	$(document).on('click', function (e) {
		
		if ($(e.target).closest(".room_sel").length === 0) {
			$(".room_sel").removeClass("open");
			
			
		}
	});*/
	
	
	$(window).scroll(function(){
		if ($(window).scrollTop() >= 300) {
			$('.home #header').addClass('fixed-header');
			$('#topbar').addClass('fixed-topbar');
		}
		else {
			$('.home #header').removeClass('fixed-header');
			$('#topbar').removeClass('fixed-topbar');
		}
	});
	
	$("#topbar ul li a").on('click', function(e) {
     e.preventDefault();
     var target = $(this).attr('href');
     $('html, body').animate({
       scrollTop: ($(target).offset().top - 100)
     }, 1500);
  });
		
	var swiper = new Swiper("#home_slider .mySwiper", {
	 autoplay: {
        delay: 12000,
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
		  spaceBetween: 20,
		  autoplay:false,
		},
		// when window width is >= 480px
		480: {
		  slidesPerView: 1,
		  spaceBetween: 20,
		  autoplay:false,
		  slidesPerView: "auto",
		},
		// when window width is >= 640px
		640: {
		  slidesPerView: 3,
		  spaceBetween: 38,
		  autoplay:false,
		  slidesPerView: "auto",
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
	
	var swiper4 = new Swiper(".unique_city_slider .mySwiper2", {
		autoplay: {
		   delay: 3000,
		   disableOnInteraction: false,
		 },
		 loop:true,
		 pagination: {
		   el: ".swiper-pagination",
		   clickable:true,
		 },
		 navigation: {
			nextEl: ".unique_city_slider .swiper-button-next",
			prevEl: ".unique_city_slider .swiper-button-prev",
		  },
	   });
	
	   var swiper5 = new Swiper(".extended_services_slider .mySwiper2", {
		autoplay: {
		   delay: 3000,
		   disableOnInteraction: false,
		 },
		 pagination: {
		   el: ".swiper-pagination",
		   clickable:true,
		 },
	   });
	
	$(".nano").nanoScroller();

	$("#days_slider").ionRangeSlider({
        min: 0,
        max: 30,
        from: 7,
		postfix: " days"
    });

	$("#accordian .acc_box h3").click(function(e) {
		e.preventDefault();
		
		
		if ($(this).parent().hasClass('open') ) {
			$(this).parent().removeClass('open')
		} else { 
			
			$(".acc_box").removeClass("open");
			$(this).parent().addClass('open');
			
		}
		
		
		
	   });

	   $('.sel_filter').change(function(){
		$this = $(this);
		$("#city_wrap .city_filter .clear_all_btn").show();
		$('#city_wrap .city_feat ul li').hide();
		$('.'+$this.val()).show();
		console.log("showing "+$this.val()+" boxes");
	   });   

	   $('#city_wrap .city_filter .clear_all_btn').click(function(e){
		e.preventDefault();
		$(this).removeClass("show_btn");
		
		$("#city_wrap .city_feat ul li a").removeClass('active_btn')
	   });   

	   $("#city_wrap .city_app_block_1 .fav").click(function(e) {
			e.preventDefault();
			$(this).toggleClass("fav_active");
	   });
	
	
});