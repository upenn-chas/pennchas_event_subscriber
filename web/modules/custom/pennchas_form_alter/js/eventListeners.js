(function ($, Drupal) {
  Drupal.behaviors.addEvent2 = {
    attach: (contact, settings) => {
      // let selectAll = $(
      //   '<div class="js-form-item form-item js-form-type-checkbox form-type--checkbox form-type--boolean checkbox form-check form-switch mb-3"><input type="checkbox" class="form-checkbox form-boolean form-boolean--type-checkbox form-check-input select-all-houses" id="select-all-houses"/> <label for="select-all-houses" class="form-item__label option"> All college houses </label></div>'
      // );
      // if (
      //   $("#edit-field-college-houses").find("input#select-all-houses")
      //     .length === 0
      // ) {
      //   $("#edit-field-college-houses").prepend(selectAll);
      // }
      // $("input#select-all-houses").on("click", function () {
      //   let isChecked = $(this).is(":checked");
      //   $('input[type="checkbox"][name^="field_college_houses"]').prop(
      //     "checked",
      //     isChecked
      //   );
      // });

      // $('input[type="checkbox"][name^="field_college_houses"]').on(
      //   "click",
      //   () => {
      //     if (!$(this).is(":checked")) {
      //       $("input#select-all-houses").prop("checked", false);
      //     }
      //   }
      // );

      $("input#edit-field-college-houses-select-all").prop("name", "");
      $("input#edit-field-college-houses-select-all").on('click', function() {
        let isChecked = $(this).is(":checked");
        $('input[type="checkbox"][name^="field_college_houses"]').prop("checked",isChecked);
      })
      $('input[type="checkbox"][name^="field_college_houses"]').on("click", function () {
        if(!$(this).is(":checked")) {
          $("input#edit-field-college-houses-select-all").prop("checked", false);
        }
      });

    },
  };
})(jQuery, Drupal);
