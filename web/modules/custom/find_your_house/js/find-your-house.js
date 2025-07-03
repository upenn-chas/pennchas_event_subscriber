(function ($) {
  'use strict';
  
  jQuery(document).ready(function(){
    jQuery('#block-penchas-findyourhouse .select-btn').on('click', function(event){
      // console.log('askdhasd test ');
      event.stopPropagation();
      jQuery(this).parent().toggleClass('active');
    });

    jQuery(document).on('click', function(event){
      // Check if the click target is outside the menu
      if (!jQuery(event.target).closest('#block-penchas-findyourhouse').length) {
          // Remove the active class
          jQuery('#block-penchas-findyourhouse .active').removeClass('active');
      }
    });

    jQuery('#block-penchas-findyourhouse .select-menu ul.options .option').on('click', function(){
      jQuery('.sBtn-text').html(jQuery(this).find('span').text());
      jQuery('#customer_find_your_house_group').val(jQuery(this).find('span').attr('data-group-url'));
      // jQuery('#block-penchas-findyourhouse .select-menu').toggleClass('active');
    });
  });
  Drupal.behaviors.find_your_house = {
      attach: function (context, settings) {
          // jQuery("#block-penchas-findyourhouse .select-menu ul.options li").on("click", function (event) {
          //   jQuery('select#customer_find_your_house_group').val(jQuery(this).find('span').attr('data-group-url'));
          //   // alert('jhagsd');
          //   jQuery('#block-penchas-findyourhouse .select-menu').addClass('closed').removeClass('active');
          // });
          
      }
  };

}(jQuery));
