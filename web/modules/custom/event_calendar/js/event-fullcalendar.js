(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.fullCalendarIntegration = {
    attach: function (context, settings) {
      $('.event-calendar').find('.js-drupal-fullcalendar').each( function() {
        let calendarEl = this;
        let viewIndex = parseInt(calendarEl.getAttribute("data-calendar-view-index"));
        let viewSettings = drupalSettings.fullCalendarView[viewIndex];
        var calendarOptions = JSON.parse(viewSettings.calendar_options);
        delete calendarOptions.events;
        calendarOptions.eventSources = [
          {
            url: 'api/events',
            method: 'GET',
            extraParams: {
              ajax: true,
            },
            failure: (e) => {
              console.log(e)
            }
          }
        ];
        calendarOptions.loading = (isLoading) => {
          if(isLoading) {
            $('.event-calendar .events-calendar_loader').css('display', 'flex');
          } else {
            $('.event-calendar .events-calendar_loader').css('display', 'none');
          }
        }
        calendarOptions.eventRender = function (cal) {
          cal.el.querySelector('.fc-title').innerHTML = cal.event.title;
        }
        calendarOptions.fixedWeekCount = false;
        drupalSettings.calendar[viewIndex].destroy();
        drupalSettings.calendar[viewIndex] = new FullCalendar.Calendar(calendarEl, calendarOptions);
        drupalSettings.calendar[viewIndex].render();
      });
    }
  }
  })(jQuery, Drupal, drupalSettings);