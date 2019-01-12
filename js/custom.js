"use strict";

// MOSAIC GRID
$(function() {
//  $('.grid').isotope({
//    // Set itemSelector so .grid-sizer is not used in layout.
//    itemSelector: '.grid-item',
//    percentPosition: true,
//    masonry: {
//      columnWidth: '.grid-sizer'
//    }
//  });
    
  // Initialize Isotope.
  var $grid = $('.grid').isotope({
    // Set itemSelector so .grid-sizer is not used in layout.
    itemSelector: '.grid-item',
    percentPosition: true,
    masonry: {
      columnWidth: '.grid-sizer'
    }
  });
    
  // Layout Isotope after each image loads.
  $grid.imagesLoaded().progress(function() {
    $grid.isotope('layout');
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
  $('#userImageModal').on('hidden.bs.modal', function() {
    $('.imgErr').remove();
    $('#userImageForm')[0].reset();
  });
});

// SIDE MENU
$(function() { 
  // Attach a click event to .menu-btn that targets the .side-menu-basic
  // aside element (current class passed to show-dialog attribute in .menu-btn).
  $('.menu-btn').click(function() {
    var sidemenu = $(this).data('show-dialog');
    // Build the class for jQuery to use, and toggle it with .side-menu-shown to open it.
    $('.' + sidemenu).toggleClass('side-menu-shown');
    $("#overlay").toggleClass("overlay");
    $('.header-nav').css('box-shadow', 'none');
    $('body').css('overflow', 'hidden'); // hide the scroll bar
  });

  // Attach a click event to the close button.
  $('.side-menu-basic .close-btn').on('click', function() {
    // Remove the "side-menu-shown" class from the aside to close it.
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
    // Add a click event to the search icon.
  $('a[href="#search"]').on('click', function(event) {
    event.preventDefault();
    $("#overlay").toggleClass("overlay");
    $('.header-nav').css('box-shadow', 'none');
    $('body').css('overflow', 'hidden'); // hide the scroll bar
    $('#search').addClass('open'); // add the class to div#search to show the search form
  });

  $('#search, #search span.close-btn').on('click keyup', function(event) { // note a click event and a keyup event
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
    // Get the pathname of the URL.
    var x = location.pathname;
    // If the home page (index)
    if (x == "/mini_blog/") {
      // The scroll distance will be the height of the header element + the height of the welcome section + 120px.
      var hdr = $('header').height() + $('#welcome').height() + 120;
    } else {
      // For other pages, the scroll distance will be just the height of the header element - 20px.
      var hdr = $('header').height() - 20; // - 20 so the nav fades in a little before the header is passed
    }
      
    // If the top of the scrollbar (window) is leveled with or passed the set height of the scroll distance, make adjustments.
    if ($(this).scrollTop() >= hdr) {
      if (!showNav) {
        $(".breadcrumb").css("margin-top","90px");
        $(".header-nav").css("box-shadow","0px 2px 9px -1px rgba(0,0,0,0.2)");
        $(".header-nav").addClass("navbar-fixed-top")
          .hide() // hides it
          .fadeTo('slow','1'); // slowly fades it in
        $(".header-nav .logo").css("display", "block"); /* overrides the stylesheet */
        $(".jumbotron .logo").css("display", "none");
          // Set navigation visibility flag to true. 
          showNav = true;
      }
    // Else if the top of the scrollbar is higher than the set height of the scroll distance.
    } else {
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
  // Bind a click event to the .page-scroll link. 
  $('a.page-scroll').bind('click', function(event) {
    var $anchor = $(this);
    $('html, body').stop().animate({ // stop at the matched element
      scrollTop: $($anchor.attr('href')).offset().top // .animate(properties, duration, easing)
    }, 1500, 'easeInOutExpo');
    event.preventDefault();
  });
});

// PARALLAXAL BACKGROUND
$(function() {
  // Save the window object to a variable.
  var $window = $(window);
  //  Assign the section element (object) with a data-type of "background" to a variable.
  $('section[data-type="background"]').each(function() {
    var $bgobj = $(this);
    // Call the window object's scroll function.
    $window.scroll(function() {
    // The speed to scroll the background is set in the section element's data-speed attribute.
    // yPos is a negative value because we're scrolling it UP!
        
    // - (number of pixels scrolled divided by 5)
    var yPos = -($window.scrollTop() / $bgobj.data('speed'));

    // Put together the final background position.
    var coords = '50% '+ yPos + 'px';

    // Move the background.
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

// SHOW MORE/SHOW LESS REPLIES
$(function() {
    $(".parent").each(function() {
        // Get each comment's replies.
        var children = $(this).find("article");
        // If a comment has more than 3 replies, hide all of its replies,
        // except the first 3.
        if (children.length > 3) {
            children.hide().css("display", "none");
            $(this).find("article:lt(3)").show().css("display", "block");
            // The Show more replies and Show fewer replies options should only
            // be seen when there are more than 3 replies.
            $(this).find(".showMore").show().css("display", "block");
        }
    });
});
    
$(function() { 
    // Set a variable to specify the number of replies to show at a time.
    var show = 3;
    // Select and show the first 3 articles (child comments).
    $(document).on('click', '.showMore', function() {
        // Get the number of replies.       
        var replies = $(this).parent().find('article').length;
        show = $(this).parent().find('article:visible').length + show;
        // If there are more replies than the number of show, show up to the next 3 replies.
        if(show < replies) {
            $(this).parent().find('article:lt(' + show + ')').show().css("display", "block");
        } else {
            $(this).parent().find('article:lt(' + replies + ')').show().css("display", "block");
            $(this).next('.showLess').show().css("display", "block");
            $(this).hide().css("display", "none");
        }
    });
    
    $(document).on('click', '.showLess', function() {
        $(this).parent().find('article').not(':lt(3)').hide().css("display", "none");
        $(this).hide().css("display", "none");
        $(this).prev().show().css("display", "block");
        
        $('html, body').animate({
            scrollTop: ($(this).parent().offset().top) - 80
        },500);
    });
});

// SHOW CHILD COMMENT FORM
$(function() {
    // Hide all child comment forms by default.
    $('.child-comment-form-wrapper').hide();
    $(document).on('click', '.reply', function(event) {
        event.preventDefault();
        var $thisId = this.id; // get the id of the current .reply link
        var commentForm = $('#form-id' + $thisId); // the identified form
        commentForm.show();
        if ($(this).hasClass("closed")) {
            $(this).removeClass("closed").addClass("opened");
            $(".reply").not(this).addClass("closed");
            $('.child-comment-form-wrapper').hide();
            commentForm.show();
         } else {         
            $(this).removeClass("opened").addClass("closed");
            commentForm.hide();
         }
    });
});

// SHOW LARGER USER IMAGE AND DETAILS
$(function() {
    // When the mouse is on a user's image, reveal a larger image and description of the user.
    $(document).on('mouseover', '.enlarge', function() {
        $(this).next(".largeImg").stop().slideToggle(200);
    });
    $(document).on('mouseout', '.enlarge', function() {
        $(this).next(".largeImg").css("display", "none");
    });
});

// DISABLE TEMPORARILY UNAVAILABLE FEATURES
$(function() {
    // If an .unavail element is clicked, invoke a popup. 
    $('.unavail').popover("hide");
    $(document).on('click', '.unavail', function(event) {
        event.preventDefault();
        $(this).popover('show');
    });
    $(document).on('mouseleave', '.unavail', function() {
        $(this).popover('hide');
    });
});

// DISPLAY LOADER
function showLoader() {
    $('#loader').show();
}

// RETURN TO PREVIOUS PAGE
function goBack() {
    window.history.back();
}