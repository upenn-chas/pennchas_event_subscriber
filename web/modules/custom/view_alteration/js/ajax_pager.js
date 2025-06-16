(function ($, Drupal) {
  Drupal.behaviors.ajaxViewPager = {
    attach: function (context, settings) {
      // Check if the view block is in the layout and initialize AJAX pager.
      // $('.view-display-id-my_events_block .pager', context).once('ajax-view-pager').each(function () {
      //   // Reattach AJAX pager for the view block.
      //   var pager = $(this);
      //   pager.find('a').each(function () {
      //     $(this).attr('data-drupal-selector', 'ajax-pager-link');
      //   });
      // });
    }
  };
})(jQuery, Drupal);
