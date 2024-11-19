(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.tbMegaMenuAction = {
    attach: function (context) {
      // Ensure this only runs once for each menu item.
      $(once('tb-megamenu-click', '.tb-megamenu .nav > li', context)).each(function () {
        const $menuItem = $(this);

        // Handle click events on menu items.
        $menuItem.children('a, .dropdown-toggle').on('click', function (event) {
          event.preventDefault();

          // Toggle the current item's open class.
          const isOpen = $menuItem.hasClass('open');
          $('.tb-megamenu .nav > li').removeClass('open'); // Close all open menus.
          if (!isOpen) {
            $menuItem.addClass('open'); // Open the clicked menu.
          }
        });
      });

      // Close the menu when clicking outside.
      $(document).on('click', function (event) {
        if (!$(event.target).closest('.tb-megamenu').length) {
          $('.tb-megamenu .nav > li').removeClass('open');
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings, once);
