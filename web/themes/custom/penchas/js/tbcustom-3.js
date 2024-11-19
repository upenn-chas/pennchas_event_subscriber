// /**
//  * @file
//  * Defines Javascript behaviors for MegaMenu frontend.
//  */

// (function ($, Drupal, drupalSettings, once) {
//   "use strict";

//   Drupal.TBMegaMenu = Drupal.TBMegaMenu || {};
//   // console.log('testing');
//   Drupal.TBMegaMenu.oldWindowWidth = 0;
//   Drupal.TBMegaMenu.displayedMenuMobile = false;
//   Drupal.TBMegaMenu.supportedScreens = [980];
//   Drupal.TBMegaMenu.focusableElements = 'a:not([disabled]), button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), details:not([disabled]), [tabindex]:not([disabled]):not([tabindex="-1"])';
//   Drupal.TBMegaMenu.menuResponsive = function () {
//     var windowWidth = window.innerWidth ? window.innerWidth : $(window).width();
//     var navCollapse = $('.tb-megamenu').children('.nav-collapse');
//     if (windowWidth < Drupal.TBMegaMenu.supportedScreens[0]) {
//       navCollapse.addClass('collapse');
//       if (Drupal.TBMegaMenu.displayedMenuMobile) {
//         navCollapse.css({height: 'auto', overflow: 'visible'});
//       } else {
//         navCollapse.css({height: 0, overflow: 'hidden'});
//       }
//     } else {
//       // If width of window is greater than 980 (supported screen).
//       navCollapse.removeClass('collapse');
//       if (navCollapse.height() <= 0) {
//         navCollapse.css({height: 'auto', overflow: 'visible'});
//       }
//     }
//   };

//   Drupal.behaviors.tbMegaMenuAction = {
//     attach: function (context, settings) {
//       // var button = $(context).find('.tb-megamenu-button').once('tb-megamenu-action');
//       // $(once('tb-megamenu', '.tb-megamenu', context)).each(function () {
//         $('.tb-megamenu-item').click(function () {
//           console.log('asdkjhasd');
//         if (parseInt($(this).parent().children('.nav-collapse').height())) {
//           $(this).parent().children('.nav-collapse').css({height: 0, overflow: 'hidden'});
//           Drupal.TBMegaMenu.displayedMenuMobile = false;
//         }
//         else {
//           $(this).parent().children('.nav-collapse').css({height: 'auto', overflow: 'visible'});
//           Drupal.TBMegaMenu.displayedMenuMobile = true;
//         }
//       });


//       var isTouch = 'ontouchstart' in window && !(/hp-tablet/gi).test(navigator.appVersion);
//       if (!isTouch) {
//         $(document).ready(function ($) {
//           $('.nav > li .close-icn, li.mega .close-icn').click(function (event) {
//             $(this).closest('li').removeClass('open');
//           });
//           var mm_duration = 0;
//           $('.tb-megamenu').each(function () {
//             if ($(this).data('duration')) {
//               mm_duration = $(this).data('duration');
//             }
//           });
//           var mm_timeout = mm_duration ? 100 + mm_duration : 500;

//           $('.mega .nav-link').click(function (event) {
//             if (event.target !== this){
//               console.log('hello');

//               return;
//             }
//             var $this = $(this).parent();
//             if ($this.hasClass('mega')) {
//               $this.addClass('animating');
//               clearTimeout($this.data('animatingTimeout'));
//               $this.data('animatingTimeout', setTimeout(function () {
//                 $this.removeClass('animating');
//               }, mm_timeout));
//             }
//               $('.nav > li, li.mega').not(this).removeClass('open');
//               $this.addClass('open');
//           });
//         });
//       }

//       $(window).resize(function () {
//         var windowWidth = window.innerWidth ? window.innerWidth : $(window).width();
//         if (windowWidth != Drupal.TBMegaMenu.oldWindowWidth) {
//           Drupal.TBMegaMenu.oldWindowWidth = windowWidth;
//           Drupal.TBMegaMenu.menuResponsive();
//         }
//       });
//     }
//   };


// })(jQuery, Drupal, drupalSettings, once);

Drupal.TBMegaMenu = Drupal.TBMegaMenu || {};

(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.TBMegaMenu.oldWindowWidth = 0;
  Drupal.TBMegaMenu.displayedMenuMobile = false;
  Drupal.TBMegaMenu.supportedScreens = [980];
  Drupal.TBMegaMenu.menuResponsive = function () {
    console.log('amsdhasd');
    var windowWidth = window.innerWidth ? window.innerWidth : $(window).width();
    var navCollapse = $('.tb-megamenu').children('.nav-collapse');
    if (windowWidth < Drupal.TBMegaMenu.supportedScreens[0]) {
      navCollapse.addClass('collapse');
      if (Drupal.TBMegaMenu.displayedMenuMobile) {
        navCollapse.css({height: 'auto', overflow: 'visible'});
      } else {
        navCollapse.css({height: 0, overflow: 'hidden'});
      }
    } else {
      // If width of window is greater than 980 (supported screen).
      navCollapse.removeClass('collapse');
      if (navCollapse.height() <= 0) {
        navCollapse.css({height: 'auto', overflow: 'visible'});
      }
    }
  };

  Drupal.behaviors.tbMegaMenuAction = {
    attach: function (context, settings) {
      var button = $(context).find('.tb-megamenu-nav');
      $(button).click(function () {
        console.log('asdsad');
        if (parseInt($(this).parent().children('.nav-collapse').height())) {
          $(this).parent().children('.nav-collapse').css({height: 0, overflow: 'hidden'});
          Drupal.TBMegaMenu.displayedMenuMobile = false;
        }
        else {
          $(this).parent().children('.nav-collapse').css({height: 'auto', overflow: 'visible'});
          Drupal.TBMegaMenu.displayedMenuMobile = true;
        }
      });


      var isTouch = 'ontouchstart' in window && !(/hp-tablet/gi).test(navigator.appVersion);
      if (!isTouch) {
        $(document).ready(function ($) {
          $('.nav > li .close-icn, li.mega .close-icn').click(function (event) {
            $(this).closest('li').removeClass('open');
          });
          var mm_duration = 0;
          $('.tb-megamenu').each(function () {
            if ($(this).data('duration')) {
              mm_duration = $(this).data('duration');
            }
          });
          var mm_timeout = mm_duration ? 100 + mm_duration : 500;

          $('.mega .nav-link').click(function (event) {
            if (event.target !== this){
              // console.log('hello');

              return;
            }
            var $this = $(this).parent();
            if ($this.hasClass('mega')) {
              $this.addClass('animating');
              clearTimeout($this.data('animatingTimeout'));
              $this.data('animatingTimeout', setTimeout(function () {
                $this.removeClass('animating');
              }, mm_timeout));
            }
              $('.nav > li, li.mega').not(this).removeClass('open');
              $this.addClass('open');
          });
        });
      }

      $(window).resize(function () {
        var windowWidth = window.innerWidth ? window.innerWidth : $(window).width();
        if (windowWidth != Drupal.TBMegaMenu.oldWindowWidth) {
          Drupal.TBMegaMenu.oldWindowWidth = windowWidth;
          Drupal.TBMegaMenu.menuResponsive();
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
