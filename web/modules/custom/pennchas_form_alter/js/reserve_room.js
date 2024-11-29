(function($, Drupal) {
    'use strict';
    Drupal.behaviors.reserveRoomBookDatetime = {
        attach: (context) => {
            $('.field--name-field-event-schedule').on('change', 'input, select', (event) => {
                event.preventDefault();
                if($('#field-event-schedule-values').data('validation')) {
                    return;
                }
                const currEleName = $(event.currentTarget).attr('name');
                let namePrefix = currEleName.match(/field_event_schedule\[\d\]/);
                const fieldNamePrefix = namePrefix[0];
                const room = $('#edit-field-room').val();
                const startDate = $('input[name="'+ fieldNamePrefix +'[time_wrapper][value][date]"]').val();
                const startTime = $('input[name="'+ fieldNamePrefix +'[time_wrapper][value][time]"]').val();
                const endTime = $('input[name="'+ fieldNamePrefix +'[time_wrapper][end_value][time]"]').val();
                if(room && room !== '_none' && startDate && startTime && endTime) {
                    $('#field-event-schedule-values').data('validation', true);
                    let form = $('form.node-form');
                    let formData = form.serialize();
                    const url = new URL(window.location.href);
                    const urlFrag = url.pathname.split('/');
                    var ajax = Drupal.ajax({
                        url: Drupal.url('group/'+ urlFrag[2] +'/check-room-availability'),
                        method: 'POST',
                        beforeSend: (xhr, settings) => {
                            settings.data += '&' + formData
                        }
                    })
                    ajax.options.complete = (data) => {
                        $('#field-event-schedule-values').data('validation', false);
                    }
                    ajax.execute();
                }
            })
        }
    }
})(jQuery, Drupal);