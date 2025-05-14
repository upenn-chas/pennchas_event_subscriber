(function ($, Drupal) {
  Drupal.behaviors.addEvent2 = {
    attach: (contact, settings) => {
      let selectAll = $(
        '<div class="js-form-item js-form-type-checkbox checkbox form-check form-switch mb-3 js-form-item-field-college-houses-select-all form-item-field-college-houses"><input data-drupal-selector="edit-field-college-houses" type="checkbox" id="select-all-houses" name="" value="select_all" class="form-checkbox form-check-input"><label class="form-check-label" for="select-all-houses">All college houses</label></div>'
      );
      if (
        $("#edit-field-college-houses").find("input#select-all-houses")
          .length === 0
      ) {
        $("#edit-field-college-houses").prepend(selectAll);
      }
      $("input#select-all-houses").on("click", function () {
        let isChecked = $(this).is(":checked");
        $('input[type="checkbox"][name^="field_college_houses"]').prop(
          "checked",
          isChecked
        );
      });

      $('input[type="checkbox"][name^="field_college_houses"]').on(
        "click",
        () => {
          if (!$(this).is(":checked")) {
            $("input#select-all-houses").prop("checked", false);
          }
        }
      );

      // $("input#edit-field-college-houses-none").prop("name", "");
      // $("input#edit-field-college-houses-none").on('click', function() {
      //   let isChecked = $(this).is(":checked");
      //   $('input[type="checkbox"][name^="field_college_houses"]').prop("checked",isChecked);
      // })
      // $('input[type="checkbox"][name^="field_college_houses"]').on("click", function () {
      //   if(!$(this).is(":checked")) {
      //     $("input#edit-field-college-houses-none").prop("checked", false);
      //   }
      // });

    },
  };
})(jQuery, Drupal);
