(function ($, Drupal) {
  Drupal.behaviors.selectAllHouses = {
    attach: (context, settings) => {
      $("input#select-all-houses").prop("checked", true);
      $('input[type="checkbox"][name^="field_college_houses"]').prop("checked",true);
    },
  };
})(jQuery, Drupal);
