(function ($) {
  'use strict';
  Drupal.behaviors.find_your_house = {
      attach: function (context, settings) {
          jQuery("#block-penchas-findyourhouse .select-menu ul.options li").on("click", function (event) {
            jQuery('select#customer_find_your_house_group').val(jQuery(this).find('span').attr('data-group-url'));
            // alert('jhagsd');
            jQuery('#block-penchas-findyourhouse .select-menu').removeClass('active');
          });
      }
  };

}(jQuery));
