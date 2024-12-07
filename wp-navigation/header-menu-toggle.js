jQuery(document).ready(function ($) {
    function toggleMegaMenu() {
     
      if ($(window).width() <= 1200 && $(window).width() >= 900) {
        $(".top-menu-main > .mega-menu > a").on("click", function (e) {
          var $this = $(this); // Current link
          var $submenu = $this.siblings(".mega-menu > .submenu");
  
          if ($submenu.hasClass("menu-open")) { $submenu.removeClass("menu-open").slideUp(); 
            return true;
          } else {
            e.preventDefault();
            $(".wp-block-navigation__submenu-container.menu-open, .mega-menu > .submenu.menu-open")
              .removeClass("menu-open")
              .slideUp();
              
            $submenu.addClass("menu-open").slideDown();
          }
        });
      }
    }
  
    toggleMegaMenu();
    $(window).on("resize", toggleMegaMenu);
  });
  
  jQuery(document).ready(function ($) {
    if ($(window).width() <= 900) {
     
      $('.top-menu-main ul ul').hide();
  
      $(".top-menu-main li.has-child > a").on("click", function (e) {
        var $this = $(this); 
        var $submenu = $this.siblings("ul");
  
        if ($submenu.is(":visible")) { return true; }
  
        e.preventDefault();
       
        $this.closest("ul").find("ul:visible").slideUp();
        $this.closest("ul").find("a").removeClass("active");
  
        $submenu.slideDown();
        $this.addClass("active");
      });
    }
  });
  
  jQuery(document).ready(function ($) {
      if ($(window).width() <= 900) {
          setTimeout(() => {
              $('.top-menu-main li').each(function (index) {
                  if ($(this).children('ul').length > 0 && $(this).children('ul').find('li').length > 0) {
                      if (!$(this).children('a, button').find('.lsubmenu-arrow').length) {
                        $(this).children('a, button').append(' <span class="lsubmenu-arrow">▼</span>');
                      }
                    }
                 });
          }, 800);
      }
  });
  
  
  
  
  
  