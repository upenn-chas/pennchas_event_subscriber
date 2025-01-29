/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal, drupalSettings, once) {

  'use strict';


  jQuery(document).ready(function () {
    setInterval(function () {
      const urlParams = new URLSearchParams(window.location.search);
      const c_type = urlParams.get('c_type');
      if(c_type){
        jQuery('.redirect-dropdown option[data-class="'+c_type+'"]').attr('selected','selected');  
      }
    }, 100);
    jQuery('.redirect-dropdown').on('change', function(){
      if (jQuery(this).val()) {
        window.location.href = jQuery(this).val();
      }
    });
    jQuery('.calendar-view-day__rows').each(function() {
      var jQueryliElements = jQuery(this).find('li');
      var liCount = jQueryliElements.length;
    
      // If there are multiple <li> elements, show the first one and a "More" button
      if (liCount > 1) {
        // Display the first <li> content
        jQuery(this).find('li:first').show();
        jQuery(this).find('li').not(':first').hide();
        // Display the "More" button with the count of  remaining <li> elements
        jQuery(this).find('li:first').after('<div id="more-button-container"><span class="use-ajax" id="more-btn">' + (liCount - 1) + ' more</span></div>');
      } else {
        // If only one <li>, just show it
        jQuery(this).find('li').show();
      }
    });

    jQuery(document).on('click', '#more-btn', function() { 
      var allLiTitles = '';

      var eventDate = jQuery(this).closest('.calendar-view-day').find('time').text();
      jQuery(this).closest('.calendar-view-day__rows').find('li').each(function() {
        var title = jQuery(this).find('.title').text();
        var eventTime = jQuery(this).find('.field_event_schedule_start_end_value').text();
        allLiTitles += '<p>' + eventTime + '<strong> ' + title + '</strong>' + '</p>';
      });
      var modalContent = '<h2>' + eventDate + '</h2>' + allLiTitles;
      openModalWithContent(modalContent);
    });

    function openModalWithContent(content) {
      // Check if a modal already exists or create one
      var modal = jQuery('#ajax-modal');
      if (modal.length === 0) {
        // If modal doesn't exist, create one
        modal = jQuery('<div id="ajax-modal" class="modal calendar-modal"><div class="modal-content"><span class="close-btn">&times;</span><div id="modal-body"></div></div></div>');
        jQuery('body').append(modal);
      }
    
      // Append content to the modal body
      jQuery('#modal-body').html(content);
    
      // Display the modal
      modal.show();
    
      // Close modal when the close button is clicked
      jQuery('.close-btn').on('click', function() {
        modal.hide();
      });
    
      // Close modal when clicking outside of it
      jQuery(window).on('click', function(event) {
        if (event.target === modal[0]) {
          modal.hide();
        }
      });
    }
    
    // if('#views-exposed-form-calendar-page-1'){
    //   jQuery(this).find('.form-radios').hide();
    // }
    // When the General Events/Notice checkbox is clicked
    // jQuery('#edit-type-exclude-notices').on('change', function() {
    //   // Check if the checkbox is checked
    //   if (jQuery(this).prop('checked')) {
    //     // If checked, check the CHAS Event radio button
    //     jQuery('#edit-type-chas-event').prop('checked', true);
    //     jQuery('#edit-type-all').prop('checked', false);
    //   } else {
    //     // If unchecked, uncheck the CHAS Event radio button
    //     jQuery('#edit-type-all').prop('checked', true);
    //     jQuery('#edit-type-chas-event').prop('checked', false);
    //   }
    // });
    // const urlParams = new URLSearchParams(window.location.search);
    //   const type = urlParams.get('type');
    //   if(type == 'chas_event'){
    //     jQuery('#edit-type-chas-event').prop('checked', true);
    //     jQuery('#edit-type-exclude-notices').prop('checked', true);
    //   }else{
    //     jQuery('#edit-type-chas-event').prop('checked', false);
    //     jQuery('#edit-type-exclude-notices').prop('checked', false);
    //   }
      // When the General Events/Notice checkbox is clicked
    jQuery('#edit-type-exclude-notices').on('change', function() {
      alert('asdkjhasd');
      // jQuery('#edit-type-all').prop('checked', false);
      // jQuery('#edit-type-all').prop('checked', false);
      // jQuery('#edit-type-chas-event').trigger('change');
      // jQuery('#edit-type-notices').trigger('change');
      
      // Check if the checkbox is checked
      // if (jQuery(this).prop('checked')) {
      //   // If checked, check the CHAS Event radio button
        
      //   jQuery('#edit-type-all').prop('checked', false);
  
      //   // Trigger change event for CHAS Event radio button
      // } else {
      //   // If unchecked, uncheck the CHAS Event radio button
      //   jQuery('#edit-type-all').prop('checked', true);
      //   jQuery('#edit-type-chas-event').prop('checked', false);
      //   // jQuery('#edit-type-exclude-notices').prop('checked', false);
      //   // Trigger change event for All radio button
      // }
    });
    
    // Check if the ol element with the class node_search-results is empty
    if (jQuery('.node_search-results').children().length === 0) {
        // If it's empty, display a no result found message
        jQuery('.node_search-results').html('<li class="list-group-item">No results found</li>');
    }
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
