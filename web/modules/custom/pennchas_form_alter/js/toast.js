(function ($, Drupal) {
    Drupal.behaviors.toastMessage = {
      attach: function (context, settings) {
        $(document).ajaxComplete(function () {
          let toasts = $('.toast');
          if (toasts.length > 0) {
            toasts.addClass('show'); // Ensure the toast message is visible
            
            // Optional: Auto-hide after 10 seconds
            setTimeout(() => {
                toasts.removeClass('show');
            }, 10000);
          }
        });
      }
    };
  })(jQuery, Drupal);
  