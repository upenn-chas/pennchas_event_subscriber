(function ($, Drupal) {
    $(document).ready(function () {
      $('.event-calendar').fullCalendar({
        eventRender: function (event, element) {
            console.log(element)
          element.html('<div class="custom-event">' +
            '<h3>' + event.title + '</h3>' +
            '<p>tytyty</p>' +
            '</div>');
        }
      });
    });
  })(jQuery, Drupal);