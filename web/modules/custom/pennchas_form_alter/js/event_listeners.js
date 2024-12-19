(function ($, Drupal) {
	Drupal.behaviors.addEvent = {
    	attach: (contact, settings) => {

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
