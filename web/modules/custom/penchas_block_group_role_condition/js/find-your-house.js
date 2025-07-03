(function ($) {
  'use strict';
  
  jQuery(document).ready(function(){
    jQuery('.block-find-your-house .select-btn').on('click', function(event){
      // console.log('askdhasd test ');
      event.stopPropagation();
      jQuery(this).parent().toggleClass('active');
    });

    jQuery(document).on('click', function(event){
      // Check if the click target is outside the menu
      if (!jQuery(event.target).closest('.block-find-your-house').length) {
          // Remove the active class
          jQuery('.block-find-your-house .active').removeClass('active');
      }
    });

    jQuery('.block-find-your-house .select-menu ul.options .option').on('click', function(){
      jQuery('.sBtn-text').html(jQuery(this).find('span').text());
      jQuery('#customer_find_your_house_group').val(jQuery(this).find('span').attr('data-group-url'));
    });
  });

}(jQuery));
