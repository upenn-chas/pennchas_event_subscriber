/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal, drupalSettings, once) {

  'use strict';


  jQuery(document).ready(function () {
    jQuery('.nav-link.nav-link--backend-login').parent().remove();
    setInterval(function () {
      const urlParams = new URLSearchParams(window.location.search);
      const c_type = urlParams.get('c_type');
      var currentUrl = window.location.href;
      var searchString = 'calendar?type=notices'; // Replace this with the string you're looking for in the URL
      // var searchString = 'calendar?type=notices'; // Replace this with the string you're looking for in the URL

      // if (currentUrl.includes(searchString)) {
      //     jQuery('#edit-type-exclude-notices').prop('checked', true);
      // }else{

      // }
      if(c_type){
        jQuery('.redirect-dropdown option[data-class="'+c_type+'"]').attr('selected','selected');  
      }
    }, 100);
    jQuery('.redirect-dropdown').on('change', function(){
      if (jQuery(this).val()) {
        window.location.href = jQuery(this).val();
      }
    });
    jQuery('.calendar-view-month .calendar-view-day__rows').each(function() {
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
      var body = '';

      var weekday = jQuery(this).closest('.calendar-view-day').find('time').data('weekday');
      var date = jQuery(this).closest('.calendar-view-day').find('time').text();
      var modalTitle = '<div class="title-weekday">'+ weekday +'</div>';
      modalTitle += '<div class="title-date"><b>'+ date +'</b></div>';
      jQuery(this).closest('.calendar-view-day__rows').find('li').each(function() {
        let titleContainer = jQuery(this).find('.title');
        let anchor = titleContainer.find('a');
        let eventTitle = anchor.contents().last().text().trim();
        let eventUrl = anchor.attr('href');
        var eventTime = jQuery(this).find('.title a span').text();
        body += '<div class="modal-event">';
        body += '<a class="event-title font-weight-bold" target="_blank" href="' + eventUrl + '">';
        body += '<span class="event-time">' + eventTime + '</span> ' + eventTitle;
        body += '</a>';
        body += '</div>';

      });
      openModalWithContent(modalTitle, body);
    });

    function openModalWithContent(title, content) {
      // Check if a modal already exists or create one
      var modal = jQuery('#ajax-modal');
      if (modal.length === 0) {
        // If modal doesn't exist, create one
        modal = jQuery('<div id="ajax-modal" class="modal calendar-modal"> \
          <div class="modal-dialog modal-dialog-centered" role="document">\
            <div class="modal-content"> \
            \
              <div class="modal-header d-flex flex-column justify-content-center border-0 p-0">\
                <div class="modal-title" id="modalTitle"></div>\
                  <span class="close-btn" aria-hidden="true">&times;</span>\
              </div>\
              <div id="modal-body"></div>\
            </div>\
          </div>\
        </div>');
        jQuery('body').append(modal);
      }
    
      // Append content to the modal body
      jQuery('#modalTitle').html(title);
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

    jQuery('.dropdown-toggle').on('keyup', function (event) {
      // console.debug(event.key);
      if (event.key === 'Enter' || event.key === ' ') {
        jQuery(this).parent().parent().children(".dropdown").removeClass('selected');
        jQuery(this).parent().addClass('selected');
      }
    });
    
    jQuery('.dropdown-toggle').on('show.bs.dropdown', function (e) {
      if(window.innerWidth >= 992) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
      }
    });

    // jQuery(".dropdown-toggle").on("click", function () {
    //   let $this = jQuery(this);
    //   if (window.innerWidth > 991) {
    //     jQuery(".dropdown-menu").addClass("hide").removeClass("show");
    //   }
    //   let $menu = $this.next(".dropdown-menu");

    //   // Toggle aria-expanded attribute
    //   let expanded = $this.attr("aria-expanded") === "true";

    //   let newExpanded = !expanded;
    //   $this.attr("aria-expanded", newExpanded.toString());

    //   // Show/hide menu based on new state
    //   if ($menu.length) {
    //     if (newExpanded) {
    //       $menu.addClass("show").removeClass("hide");
    //     } else {
    //       $menu.removeClass("show").addClass("hide");
    //     }
    //   }
    // });

    if (jQuery('body').hasClass('user-logged-in')) {
      var navWrapCount = jQuery('div.nav-wrap-cst').length;
      if (navWrapCount > 1) {
        jQuery('div.nav-wrap-cst').first().show();  
        jQuery('div.nav-wrap-cst').last().hide();
      }
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
      
        // Function to handle checkbox state and enable/disable radio buttons
      // function toggleRadioOptions() {
      //   var checkbox = jQuery('#edit-type-exclude-notices');
      //   var field_is_campus_wide_value = jQuery('#edit-field-is-campus-wide-value').val();
      //   var field_intended_audience_value = jQuery('#edit-field-intended-audience-value').val();
      //   var currentUrlWithoutParams = window.location.origin + window.location.pathname;
      //   if (checkbox.prop('checked')) {
      //     // var type = jQuery('#edit-type-notices').val();
      //     var type = jQuery('#edit-type-chas-event').val();
      //   } else {
      //     // var type = jQuery('#edit-type-chas-event').val();
      //     var type = jQuery('#edit-type-notices').val();
      //   }
      //   var url = currentUrlWithoutParams+'?type='+type+'&field_is_campus_wide_value='+field_is_campus_wide_value+'&field_intended_audience_value='+field_intended_audience_value;
      //   console.log('url: ', url);
      //   return url;
      // }
        
      //   // Call toggleRadioOptions on checkbox change
      // jQuery('#edit-type-exclude-notices').on('click',function() {
      //   var url = toggleRadioOptions();
      //   window.location.href = url;
      // });
  
      // Initial call to set the state based on the checkbox's current state
      // toggleRadioOptions();
  
      // jQuery('#edit-type-exclude-notices').on('change', function() {
    //   alert('asdkjhasd');
    //   // jQuery('#edit-type-all').prop('checked', false);
    //   // jQuery('#edit-type-all').prop('checked', false);
    //   // jQuery('#edit-type-chas-event').trigger('change');
    //   // jQuery('#edit-type-notices').trigger('change');
      
    //   // Check if the checkbox is checked
    //   // if (jQuery(this).prop('checked')) {
    //   //   // If checked, check the CHAS Event radio button
        
    //   //   jQuery('#edit-type-all').prop('checked', false);
  
    //   //   // Trigger change event for CHAS Event radio button
    //   // } else {
    //   //   // If unchecked, uncheck the CHAS Event radio button
    //   //   jQuery('#edit-type-all').prop('checked', true);
    //   //   jQuery('#edit-type-chas-event').prop('checked', false);
    //   //   // jQuery('#edit-type-exclude-notices').prop('checked', false);
    //   //   // Trigger change event for All radio button
    //   // }
    // });
    
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
    jQuery('.view-id-calendar .calendar-view-pager .pager__current, .view-id-room-reservation-calendar .calendar-view-pager .pager__current').find('div').text('Today');
    

    jQuery('.calendar-view-table thead th').eq(0).text('SUN');
    jQuery('.calendar-view-table thead th').eq(1).text('MON');
    jQuery('.calendar-view-table thead th').eq(2).text('TUE');
    jQuery('.calendar-view-table thead th').eq(3).text('WED');
    jQuery('.calendar-view-table thead th').eq(4).text('THU');
    jQuery('.calendar-view-table thead th').eq(5).text('FRI');
    jQuery('.calendar-view-table thead th').eq(6).text('SAT');

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

    $('.internal-links .dropdown').hover(function () {
      $('.internal-links .dropdown').each(function (index, element) {
        jQuery(element).removeClass("selected");
      });
      $(this).addClass("selected", 3000);
      $(this).children("a").trigger("focus");
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
  if (navigator.platform.toUpperCase().indexOf('MAC') >= 0) {
    document.documentElement.classList.add('mac_os');
  }
  
  const target = document.querySelector(".action-container + .view-content.row");
if (target) {
  OverlayScrollbars(target, { scrollbars: { autoHide: "never" } });
}


(function() {
  document.addEventListener('click', function(e) {
    var copyLink = e.target.closest('.copy-icon .copy-link');
    if (!copyLink) return;

    e.preventDefault(); // Prevent redirection

    // Get the link from href
    var textToCopy = copyLink.getAttribute('href');
    // alert(textToCopy);
    if (!textToCopy) {
      console.error("No href found in .copy-link");
      return;
    }

    // Try modern clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(textToCopy).then(() => {
        showTooltip(copyLink, "Copied!");
      }).catch(() => {
        fallbackCopyText(textToCopy, copyLink);
      });
    } else {
      fallbackCopyText(textToCopy, copyLink);
    }
  });

  function fallbackCopyText(text, element) {
    var tempInput = document.createElement('textarea');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);

    showTooltip(element, "LINK COPIED!");
  }

  function showTooltip(element, message) {
    var tooltip = element.closest('.copy-icon').querySelector('.copy-tooltip');
    if (!tooltip) return;

    tooltip.innerText = message;
    tooltip.classList.add('show');

    setTimeout(() => {
      tooltip.classList.remove('show');
      tooltip.innerText = "LINK COPIED!";
    }, 2000);
  }
})();














  
  Drupal.behaviors.penchas = {
    attach: function (context, settings) {

    }
  };

})(jQuery, Drupal, drupalSettings, once);
