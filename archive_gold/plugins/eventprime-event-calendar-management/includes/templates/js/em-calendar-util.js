document.addEventListener('DOMContentLoaded', function() {
    $ = jQuery;
    var default_view;
    switch(em_calendar_data.view){
        case 'day': default_view = "dayGridDay"; break;
        case 'listweek': default_view = "listWeek"; break;
        case 'week': default_view = "dayGridWeek"; break;
        case 'month': default_view = "dayGridMonth"; break;
        default: default_view = "dayGridMonth";    
    }
    let hour12 = true;
    if(em_calendar_data.time_format == 'HH:mm'){
        hour12 = false;
    }
    var hide_calendar_rows = false;
    if(em_calendar_data.hide_calendar_rows == 1){
        hide_calendar_rows = true;
    }
    sessionStorage.setItem("curr_month", '');
    sessionStorage.setItem("curr_year", '');
    var calendarEl = document.getElementById('em_calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: [ 'interaction', 'dayGrid', 'momentPlugin', 'list' ],
        header: {
            left: '',
            center: 'prev,title,next',
            right: ''
        },
        timeZone: 'UTC',
        editable : true,
        defaultView: default_view,
        defaultDate: em_calendar_data.default_date,
        locale: em_calendar_data.locale,
        eventLimit: true, // allow "more" link when too many events
        nextDayThreshold: '00:00:00',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: hour12,
            meridiem: 'short'
        },
        firstDay: parseInt(em_calendar_data.week_start, 10),
        events: em_calendar_data.events,
        showNonCurrentDates: hide_calendar_rows,
        fixedWeekCount: hide_calendar_rows,
        titleFormat: function(date) {
            var titleDate = FullCalendarMoment.toMoment(date.date.marker, calendar).format(em_calendar_data.calendar_title_format);
            return titleDate;
        },
        columnHeaderFormat: function(date) {
            var columnDate = FullCalendarMoment.toMoment(date.date.marker, calendar).format(em_calendar_data.calendar_column_header_format);
            return columnDate;
        },
        eventRender: function(info) {
            if (info.event.extendedProps.hasOwnProperty('bg_color')) {
                info.el.style.backgroundColor = info.event.extendedProps.bg_color;
            }
            var textColor = '';
            if (info.event.extendedProps.hasOwnProperty('type_text_color')) {
                textColor = info.event.extendedProps.type_text_color;
            }
            if (info.event.extendedProps.hasOwnProperty('event_text_color')) {
                textColor = info.event.extendedProps.event_text_color;
            }
            if(textColor){
                var fc_time = info.el.querySelector('.fc-time');
                if(fc_time){
                    fc_time.style.color = textColor;
                    if(em_calendar_data.hide_time_on_front_calendar == 1){
                        fc_time.textContent = '';
                        fc_time.style.color = '';
                    }
                }
                var fc_title = info.el.querySelector('.fc-title');
                if(fc_title){
                    fc_title.style.color = textColor;
                }
                var fc_list_time = info.el.querySelector('.fc-list-item-time');
                if(fc_list_time){
                    fc_list_time.style.color = textColor;
                }
                var fc_list_title = info.el.querySelector('.fc-list-item-title');
                if(fc_list_title){
                    fc_list_title.style.color = textColor;
                }
            }
            /*if (info.event.extendedProps.image_url) {
                var ev_img = '<img src="' + info.event.extendedProps.image_url + '" width="100" height="100">';
                let elementHtml = info.el.innerHTML;
                info.el.innerHTML = ev_img + elementHtml;
            }*/
            let elementHtml = info.el.innerHTML;
            elementHtml += info.event.extendedProps.popup_html;
            info.el.innerHTML = elementHtml;
        },
        /* eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.open(info.event.url, "_blank");
            }
        }, */
        eventMouseEnter: function (info) {
            let pop_block = info.el.querySelector('.em_event_detail_popup');
            pop_block.style.display = 'block';
        },
        eventMouseLeave: function(info ){
            let pop_block = info.el.querySelector('.em_event_detail_popup');
            pop_block.style.display = 'none';
        },
        datesRender: function(info){
            $('#em_calendar .fc-toolbar .fc-center').addClass('em_bg');
            $('button.fc-button').click(function() {
                em_update_calendar_time_display();
            });
            var yearMonthDropdown = '';
            var curr_date = new Date();
            var curr_year = '';
            curr_year = sessionStorage.getItem("curr_year");
            if(!curr_year){
                curr_year = curr_date.getFullYear();
            }
            var year_drop = '<select class="select_year form-control">';
            for(var i = 5; i > 0; i--){
                var ye = curr_year-i;
                year_drop += '<option value="'+ye+'">'+ye+'</option>';
            }
            year_drop += '<option value="'+curr_year+'" selected>'+curr_year+'</option>';
            for(var i = 1; i < 6; i++){
                var ye = parseInt(curr_year, 10) + parseInt(i, 10);
                year_drop += '<option value="'+ye+'">'+ye+'</option>'
            }
            year_drop += '</select>';
            //$(".fc-right").append(year_drop);
            yearMonthDropdown += year_drop;
            var curr_month = '';
            curr_month = sessionStorage.getItem("curr_month");
            if(!curr_month){
                curr_month = curr_date.getMonth()+1;
            }
            var month_drop = '<select class="select_month form-control">';
            $.each(em_calendar_data.em_calendar_month_data, function(index, item){
                var monval = parseInt(index + 1);
                if(monval == curr_month){
                    month_drop += '<option value="'+monval+'" selected>'+item+'</option>';
                }
                else{
                    month_drop += '<option value="'+monval+'">'+item+'</option>';
                }
            })
            month_drop += '</select>';
            //$(".fc-right").append(month_drop);
            yearMonthDropdown += month_drop;
            //insert year and month dropdown
            calendar.el.querySelector(".fc-right").innerHTML = yearMonthDropdown;

            $(".select_month").on("change", function(event) {
                var monthDate = $(".select_year").val()+"-"+this.value+"-2";
                var mond = new Date(monthDate);
                sessionStorage.setItem("curr_month", this.value);
                calendar.gotoDate(mond);
            });
            $(".select_year").on("change", function(event) {
                var monthDate = this.value+"-"+$(".select_month").val()+"-2";
                var mond = new Date(monthDate);
                sessionStorage.setItem("curr_year", this.value);
                calendar.gotoDate(mond);
            });
        }
      });
  
      calendar.render();

    
    em_update_calendar_time_display();
    em_set_dominent_color();

    function columnHeaderFormat(format){
        var calFormat = { weekday: 'short' };
        if(format == 'dddd'){
            calFormat = { weekday: 'long' };
        }
        if(format == 'ddd D/M'){
            calFormat = { weekday: 'long', day: 'numeric', month: 'numeric', omitCommas: true };
        }
        if(format == 'ddd M/D'){
            calFormat = { weekday: 'long', month: 'numeric', day: 'numeric', omitCommas: true };
        }
        return calFormat;
    }
    
});
