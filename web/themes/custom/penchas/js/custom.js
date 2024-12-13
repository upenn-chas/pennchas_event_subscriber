/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal, drupalSettings, once) {

  'use strict';


  jQuery(document).ready(function () {
    // jQuery(".dashboard-accordions .contents").on("click", function () {
    //   console.log(' m clicke 15d');
    //   jQuery(this).on('click', function () {
    //     console.log(' m clicked');
    //   });
    // });


    /*custom accordion starts */

    function adjustContentWidth() {
      // Get the width of the .dashboard-accordions div
      const dashboardWidth = jQuery(".dashboard-accordions .col-12").width();

      // Set the width of all .content divs
      jQuery(".dashboard-accordions .field_child_dashboard_blocks").css("width", dashboardWidth);
    }
    adjustContentWidth();

    jQuery(window).on("resize", function () {
      adjustContentWidth();
    });

    jQuery(".dashboard-accordions .field_child_dashboard_blocks").hide();
    jQuery(".dashboard-accordions .field_child_dashboard_blocks.open .contents").show();

    jQuery(".dashboard-accordions .contents").on("click", function () {
      // const parentBlock = jQuery(this).closest(".block");
      const parentBlock = jQuery(this).parent(".block");
      if (parentBlock.hasClass("open")) {
        parentBlock.removeClass("open").find(".field_child_dashboard_blocks").slideUp();
      } else {
        // Close all other blocks
        jQuery(".dashboard-accordions .block").removeClass("open").find(".field_child_dashboard_blocks").slideUp();

        // Open the clicked block
        parentBlock.addClass("open").find(".field_child_dashboard_blocks").slideDown();
      }
    });

    /*custom accordion ends */

    var $section1 = jQuery('#block-penchas-attentionnewandreturningstudents');
    var $section2 = jQuery('#block-penchas-attentionnewandreturningstudents22');

    function adjustSection2Top() {
      // Get the height of section1
      var section1Height = $section1.outerHeight();

      // Set the top value of section2 dynamically
      $section2.css('top', section1Height + 'px');
    }

    // Run the function when the page loads
    adjustSection2Top();

    // Adjust dynamically when window is resized or content changes
    jQuery(window).on('resize', adjustSection2Top);

    var div1Height = jQuery('#block-penchas-attentionnewandreturningstudents').outerHeight();
    var div2Height = jQuery('#block-penchas-attentionnewandreturningstudents22').outerHeight();

    var totalHeight = 0;
    if (div1Height > 0) {
      totalHeight += div1Height;
    }

    if (div2Height > 0) {
      totalHeight += div2Height;
    }



    jQuery('header').css('top', totalHeight);

    jQuery('#view-display-id-my_pending_events').hide();
    jQuery('.events-tab-blocks li span').on('click', function () {
      var clicked_element = jQuery(this).attr('data-wrap');
      jQuery('.container .event-content').hide();
      jQuery('#view-display-id-' + clicked_element).show();
    });

    jQuery('.reserve-tab-blocks li span').on('click', function () {
      var clicked_element = jQuery(this).attr('data-wrap');
      jQuery('.container .reserved-room-content').hide();
      jQuery('#view-display-id-' + clicked_element).show();
    });

    jQuery('select[name="field_number_of_residents_value"]').on('change', function () {
      console.log('testing control');
      jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-min"]').val('');
      jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-max"]').val('');
      jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-value"]').val('');

      if (jQuery(this).val() == 1) {
        jQuery('select[name="field_number_of_residents_value_1_op"]').val('<=');
        jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-value"]').val('300');
      }
      else if (jQuery(this).val() == 2) {
        jQuery('select[name="field_number_of_residents_value_1_op"]').val('between');
        jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-min"]').val('300');
        jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-max"]').val('600');
      } else if (jQuery(this).val() == 3) {
        jQuery('select[name="field_number_of_residents_value_1_op"]').val('>');
        jQuery('input[data-drupal-selector="edit-field-number-of-residents-value-1-value"]').val('600');
      }
    });
  });

  // Custom code here
  $(document).ready(function () {
    if (window.outerWidth < 991) {

      $('.events').slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 1,
        arrows: false,
        slidesToScroll: 1,
        responsive: [
          {
            breakpoint: 991,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 1
            }
          },
          {
            breakpoint: 575,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          },
        ]
      });
    }


    $('.internal-links .dropdown').hover(function () {
      $(this).addClass("selected", 3000);
    }, function () {
      $(this).removeClass("selected", 3000);
    });

  });

  var $slider = $('.two-column-slider');

  if ($slider.length) {
    var currentSlide;
    var slidesCount;
    var sliderCounter = document.createElement('div');
    sliderCounter.classList.add('slider__counter');

    var updateSliderCounter = function (slick, currentIndex) {
      currentSlide = slick.slickCurrentSlide() + 1;
      slidesCount = slick.slideCount;

      // Add leading zero if the number is less than 10
      var formattedCurrentSlide = currentSlide < 10 ? '0' + currentSlide : currentSlide;
      var formattedSlidesCount = slidesCount < 10 ? '0' + slidesCount : slidesCount;

      $(sliderCounter).text(formattedCurrentSlide + '/' + formattedSlidesCount);
    };

    $slider.on('init', function (event, slick) {
      $slider.append(sliderCounter);
      updateSliderCounter(slick);
    });

    $slider.on('afterChange', function (event, slick, currentSlide) {
      updateSliderCounter(slick, currentSlide);
    });

    $slider.slick();
  }

  $('.offer-slider').slick({
    dots: false,
    infinite: true,
    speed: 300,
    slidesToShow: 3,
    arrows: true,
    slidesToScroll: 3,
    centerPadding: '0', // Adjusts how much of the next slide is visible
    responsive: [
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          centerPadding: '20px', // Adjust for smaller screens
        }
      },
      {
        breakpoint: 575,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          centerPadding: '30px', // Adjust for smallest screens
        }
      },
    ]
  });
  Drupal.behaviors.penchas = {
    attach: function (context, settings) {

    }
  };

})(jQuery, Drupal, drupalSettings, once);
