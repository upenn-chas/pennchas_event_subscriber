(function ($, Drupal) {
	Drupal.behaviors.noticeSelectAll = {
    	attach: (contact, settings) => {

		$("input#edit-field-groups-select-all").prop("name", "");
		$("input#edit-field-groups-select-all").on('click', function() {
			let isChecked = $(this).is(":checked");
			$('input[type="checkbox"][name^="field_groups"]').prop("checked",isChecked);
		})
      	$('input[type="checkbox"][name^="field_groups"]').on("click", function () {
			if(!$(this).is(":checked")) {
				$("input#edit-field-groups-select-all").prop("checked", false);
			}
		});
    },
  };
})(jQuery, Drupal);
