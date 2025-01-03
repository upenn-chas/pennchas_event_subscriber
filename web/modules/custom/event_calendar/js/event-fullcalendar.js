(function ($, Drupal, drupalSettings) {
  $(document).ready(() => {
    $(".event-calendar")
      .find(".js-drupal-fullcalendar")
      .each(function () {
        let calendarEl = this;
        let viewIndex = parseInt(
          calendarEl.getAttribute("data-calendar-view-index")
        );
        let viewSettings = drupalSettings.fullCalendarView[viewIndex];
        let calendarOptions = JSON.parse(viewSettings.calendar_options);
        // delete calendarOptions.events;
        
        calendarOptions.events = (info, successCallback, failureCallback) => {
          console.log(info);
          let startDate = info.start.toISOString().split("T")[0];
          let endDate = info.end.toISOString().split("T")[0];

          let startDateInput = $('.views-exposed-form input[name="start"]');
          let endDateInput = $('.views-exposed-form input[name="end"]');

          let existingStartDate = startDateInput.val();
          let existingEndDate = endDateInput.val();

          if (
            existingStartDate !== startDate ||
            existingEndDate !== endDate
          ) {
            startDateInput.val(startDate);
            endDateInput.val(endDate).change();
          }
        };
        calendarOptions.datesSet = (info) => {
          getEvents(info);
        };

        // calendarOptions.eventSources = [
        //   {
        //     events: (info, successCallback, failureCallback) => {
        //       console.log(info);
        //       let startDate = info.start.toISOString().split("T")[0];
        //       let endDate = info.end.toISOString().split("T")[0];

        //       let startDateInput = $('.views-exposed-form input[name="start"]');
        //       let endDateInput = $('.views-exposed-form input[name="end"]');

        //       let existingStartDate = startDateInput.val();
        //       let existingEndDate = endDateInput.val();

        //       if (
        //         existingStartDate !== startDate ||
        //         existingEndDate !== endDate
        //       ) {
        //         startDateInput.val(startDate);
        //         endDateInput.val(endDate).change();
        //       }
        //     },
        //     // url: "/views/ajax",
        //     // method: "GET",
        //     // extraParams: {
        //     //   _wrapper_format: "drupal_ajax",
        //     //   ...getParameters(),
        //     // },
        //     // failure: (e) => {
        //     //   console.log(e);
        //     // },
        //   },
        // ];
        calendarOptions.loading = (isLoading) => {
          if (isLoading) {
            $(".event-calendar .events-calendar_loader").css("display", "flex");
          } else {
            $(".event-calendar .events-calendar_loader").css("display", "none");
          }
        };
        calendarOptions.eventRender = function (cal) {
          cal.el.querySelector(".fc-title").innerHTML = cal.event.title;
        };
        calendarOptions.fixedWeekCount = false;
        // console.log(drupalSettings, Object.keys(drupalSettings), drupalSettings.calendar)
        // drupalSettings.fullCalendarView[viewIndex].destroy();
        if(drupalSettings.calendar) {
          drupalSettings.calendar[viewIndex].destroy();
        } else {
          drupalSettings.calendar = [];
        }
        drupalSettings.calendar[viewIndex] = new FullCalendar.Calendar(
          calendarEl,
          calendarOptions
        );
        drupalSettings.calendar[viewIndex].render();
      });

    function getParameters() {
      let data = {};
      $.each(settings.views.ajaxViews, (i, ele) => {
        data = JSON.parse(JSON.stringify(ele));
      });
      let inputs = $(".views-exposed-form").serializeArray();
      $.each(inputs, (i, field) => {
        if (field.name !== "start" && field.name !== "end") {
          data[field.name] = data[field.value];
        }
      });
      // inputs.each(() => {
      //   console.log(this);
      //   // data[ele.name] = data[ele.value]
      // })
      // console.log(data);
      return data;
    }

    function getEvents(info) {
      let startDate = info.start.toISOString().split("T")[0];
      let endDate = info.end.toISOString().split("T")[0];
      console.log(startDate, endDate);

      let startDateInput = $('.views-exposed-form input[name="start"]');
      let endDateInput = $('.views-exposed-form input[name="end"]');

      let existingStartDate = startDateInput.val();
      let existingEndDate = endDateInput.val();

      if (existingStartDate !== startDate || existingEndDate !== endDate) {
        startDateInput.val(startDate);
        endDateInput.val(endDate).change();
      }
    }
  });
  // Drupal.behaviors.fullCalendarIntegration = {
  //   attach: function (context, settings) {

  //

  //   },
  // };
})(jQuery, Drupal, drupalSettings);
