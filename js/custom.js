"use strict";

// MOSAIC GRID
$(function() {
    $('.grid').isotope({
      // Set itemSelector so .grid-sizer is not used in layout
      itemSelector: '.grid-item',
      percentPosition: true,
      masonry: {
        columnWidth: '.grid-sizer'
      }
    });
});

// SIGN-IN MODAL
$(function() {    
	$("#sign-in").click(function() {
	  $("#signinModal").modal('show');
	});
});

// USER IMAGE MODAL
$(function() {
    if ($('.userImgErr')[0]) {
        $("#userImageModal").modal('show');
    }
});

// SIDE MENU
$(function() { 
	// Attach a click event to .menu-btn that targets the .side-menu-basic
    // aside element (current class passed to show-dialog attribute in .menu-btn)
	$('.menu-btn').click(function(event) {
	  var sidemenu = $(this).data('show-dialog');
	  // Build the class for jQuery to use, and toggle it with .side-menu-shown to open it
	  $('.' + sidemenu).toggleClass('side-menu-shown');
	  $("#overlay").toggleClass("overlay");
	  $('.header-nav').css('box-shadow', 'none');
	  $('body').css('overflow', 'hidden'); // hides scroll bar
	});

	// Attach a click event to the close button
	$('.side-menu-basic .close-btn').on('click', function () {
	  // Removes "side-menu-shown" class from the aside to close it
	  $('.side-menu-basic').removeClass('side-menu-shown');
	  $("#overlay").removeClass("overlay");
	  $('.header-nav').css('box-shadow', '0px 2px 9px -1px rgba(0,0,0,0.2)');
	  $('body').css('overflow', 'auto');
	});
});

// SIDE MENU REGISTRATION KEY
$(function() {
    $("#info-uname").click(function() {
      $("#info-key1").slideToggle("fast");
    });    
    $("#info-pwd").click(function() {
      $("#info-key2").slideToggle("slow");
    });
});

$(function() {
    $("#info-uname-mobile").click(function() {
      $("#info-key1-mobile").slideToggle("fast");
    });    
    $("#info-pwd-mobile").click(function() {
      $("#info-key2-mobile").slideToggle("slow");
    });
});

// SEARCH FORM
$(function() {	
    // Add a click event to the search icon
	$('a[href="#search"]').on('click', function(event) {
		event.preventDefault();
		$("#overlay").toggleClass("overlay");
		$('.header-nav').css('box-shadow', 'none');
		$('body').css('overflow', 'hidden'); // hides scroll bar
		$('#search').addClass('open'); // adds class to div#search to show search form
	});

	$('#search, #search span.close-btn').on('click keyup', function(event) { // a click event and a keyup event
		if (event.target == this || event.target.className == 'close-btn' || event.keyCode == 27) { // Escape key
			$(this).removeClass('open');
			$("#overlay").removeClass('overlay');
			$('.header-nav').css('box-shadow', '0px 2px 9px -1px rgba(0,0,0,0.2)');
			$('body').css('overflow', 'auto');		
		}
	});
});

// NAVIGATION
$(function() {
    var showNav = false;
	$(window).scroll(function() {      
        var x = location.pathname;
        if (x == "/mini_blog/") {
          var hdr = $('header').height() + $('#welcome').height() + 120; // - 150
        } else {        
		  var hdr = $('header').height() - 20; // - 20 so nav fades in a little before the header is passed
        }

	    if ($(this).scrollTop() >= hdr) { // if the top of the window is below the header + #welcome section
	        if (!showNav) {
                $(".breadcrumb").css("margin-top","90px");
	        	$(".header-nav").css("box-shadow","0px 2px 9px -1px rgba(0,0,0,0.2)");
	            $(".header-nav").addClass("navbar-fixed-top")
	                .hide() // hides it
	                .fadeTo('slow','1'); // slowly fades it in
	            $(".header-nav .logo").css("display", "block"); /* overrides stylesheet */
                $(".jumbotron .logo").css("display", "none");
	            // Sets visibility flag to true 
	            showNav = true;
	        }
	    } else { // Else if the top of the window is higher than the height of the header
            $(".breadcrumb").removeAttr('style');
	    	$(".header-nav").removeAttr('style');
	        $(".header-nav").removeClass("navbar-fixed-top"); // removes "navbar-fixed-top"
	        $(".header-nav .logo").removeAttr('style');
            $(".jumbotron .logo").removeAttr('style');
			showNav = false;
	    }
	});
});

// PAGE SCROLL
$(function() {	
	$('a.page-scroll').bind('click', function(event) {
		var $anchor = $(this);
		$('html, body').stop().animate({
			scrollTop: $($anchor.attr('href')).offset().top
		}, 1500, 'easeInOutExpo');
		event.preventDefault();
	});
});

// PARALLAXAL BACKGROUND
$(function() {	
	var $window = $(window);
	$('section[data-type="background"]').each(function() {
		var $bgobj = $(this); // assigning the div object

		$(window).scroll(function() {

		// Scroll the background at var speed
		// yPos is a negative value because we're scrolling it UP!                
		var yPos = -($window.scrollTop() / $bgobj.data('speed'));

		// Put together our final background position
		var coords = '50% '+ yPos + 'px';

		// Move the background
		$bgobj.css({ backgroundPosition: coords });
		});
	});
});

// RELATED POSTS CAROUSEL
$(function() {	
    var items = [];
        $('.carousel[data-type="multi"] .item').each(function(n) {
          items[n] = $(this);
    });
    $('.carousel[data-type="multi"] .item').each(function() {    
        var itemToClone = $(this);
        for (var i=1;i<4;i++) {
          itemToClone = itemToClone.next();

          if (!itemToClone.length) {
            itemToClone = $(this).siblings(':first');
          }

          itemToClone.children(':first-child').clone()
            .addClass("cloneditem-"+(i))
            .appendTo($(this));
        }
    });
});

// SHOW LARGER USER IMAGE AND DETAILS
$(function() {
    $(document).on('mouseover', '.enlarge', function() {
        $(this).next(".largeImg").stop().slideToggle(200);
    });
    $(document).on('mouseout', '.enlarge', function() {
        $(this).next(".largeImg").css("display", "none");
    });
});

// DISABLE TEMPORARILY UNAVAILABLE FEATURES
$(function() {
    $('.unAvail').click(function(e) {
        e.preventDefault();
    });
});

// DISPLAY MESSAGE TEMPORARILY UNAVAILABLE FEATURES
$(function() {
//    $('.unavail').tooltip({
//    position: {my: 'center bottom', at: 'center top-10'},
//    disabled: true,
//    close: function(event, ui) {$(this).tooltip('disable');}
//    });
//
//    $('.unavail').on('click', function () {
//        $(this).tooltip('enable').tooltip('open');
//    });
    $('.unavail').popover("hide");
    $(document).on('click','.unavail',function() {
        $(this).popover('show');
    });
    $(document).on('mouseleave','.unavail',function() {
        $(this).popover('hide');
    });
});