var slmsCalendar = null;
(function ($){

    let calendarEl = document.getElementById('slms-calendar');
    if(calendarEl) {
        slmsCalendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap',
            nowIndicator: true,
            firstDay: 1,
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            navLinks: false, // can click day/week names to navigate views
            editable: false,
            selectable: true,
            dayMaxEvents: false, // allow "more" link when too many events
            displayEventEnd: true,
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
            },
            longPressDelay: 1,
            height: "auto",
            eventClick: function(info) {
                let extra = info.event.extendedProps;

            },
            events: slms_calendar.events
        });

        slmsCalendar.render();
    }

})(jQuery);