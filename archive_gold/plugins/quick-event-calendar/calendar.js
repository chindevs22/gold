var calendar = {
    init: function () {
        /**
         * Set week days and get current date
         */
        var mon = 'Mon',
            tue = 'Tue',
            wed = 'Wed',
            thu = 'Thu',
            fri = 'Fri',
            sat = 'Sat',
            sun = 'Sun';

        var d = new Date();

        //var strDate = d.getFullYear() + "/" + (d.getMonth() + 1) + "/" + d.getDate();
        var yearNumber = (new Date).getFullYear();
        var strDate = yearNumber + "/" + (d.getMonth() + 1) + "/" + d.getDate();

        /**
         * Get current month and set as '.current-month' in title
         */
        var monthNumber = d.getMonth() + 1;

        function GetMonthName(monthNumber) {
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            return months[monthNumber - 1];
        }

        function setMonth(monthNumber, mon, tue, wed, thu, fri, sat, sun) {
            jQuery('.month').text(GetMonthName(monthNumber) + ' ' + yearNumber);
            jQuery('.month').attr('data-month', monthNumber);
            printDateNumber(monthNumber, mon, tue, wed, thu, fri, sat, sun);
        }

        setMonth(monthNumber, mon, tue, wed, thu, fri, sat, sun);

        jQuery('.qcc-btn-next').on('click', function (e) {
            var monthNumber = jQuery('.month').attr('data-month');
            if (monthNumber > 11) {
                jQuery('.month').attr('data-month', '0');
                var monthNumber = jQuery('.month').attr('data-month');
                yearNumber = yearNumber + 1;
                setMonth(parseInt(monthNumber) + 1, mon, tue, wed, thu, fri, sat, sun);
            } else {
                setMonth(parseInt(monthNumber) + 1, mon, tue, wed, thu, fri, sat, sun);
            }
            e.preventDefault();
        });

        jQuery('.qcc-btn-prev').on('click', function (e) {
            var monthNumber = jQuery('.month').attr('data-month');
            if (monthNumber < 2) {
                jQuery('.month').attr('data-month', '13');
                var monthNumber = jQuery('.month').attr('data-month');
                yearNumber = yearNumber - 1;
                setMonth(parseInt(monthNumber) - 1, mon, tue, wed, thu, fri, sat, sun);
            } else {
                setMonth(parseInt(monthNumber) - 1, mon, tue, wed, thu, fri, sat, sun);
            };
            e.preventDefault();
        });

        /**
         * Get all dates for current month
         */

        function printDateNumber(monthNumber, mon, tue, wed, thu, fri, sat, sun) {
            jQuery(jQuery('.qcc-calendar-container tbody tr')).each(function(index) {
                jQuery(this).empty();
            });

            jQuery(jQuery('.qcc-calendar-container thead tr')).each(function(index) {
                jQuery(this).empty();
            });

            function getDaysInMonth(month, year) {
                // Since no month has fewer than 28 days
                var date = new Date(year, month, 1);
                var days = [];
                while (date.getMonth() === month) {
                    days.push(new Date(date));
                    date.setDate(date.getDate() + 1);
                }
                return days;
            }

            i = 0;

            setDaysInOrder(mon, tue, wed, thu, fri, sat, sun);

            function setDaysInOrder(mon, tue, wed, thu, fri, sat, sun) {
                var monthDay = getDaysInMonth(monthNumber - 1, yearNumber)[0].toString().substring(0, 3);
                if (monthDay === 'Mon') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + mon + '</td><td>' + tue + '</td><td>' + wed + '</td><td>' + thu + '</td><td>' + fri + '</td><td>' + sat + '</td><td>' + sun + '</td>');
                } else if (monthDay === 'Tue') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + tue + '</td><td>' + wed + '</td><td>' + thu + '</td><td>' + fri + '</td><td>' + sat + '</td><td>' + sun + '</td><td>' + mon + '</td>');
                } else if (monthDay === 'Wed') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + wed + '</td><td>' + thu + '</td><td>' + fri + '</td><td>' + sat + '</td><td>' + sun + '</td><td>' + mon + '</td><td>' + tue + '</td>');
                } else if (monthDay === 'Thu') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + thu + '</td><td>' + fri + '</td><td>' + sat + '</td><td>' + sun + '</td><td>' + mon + '</td><td>' + tue + '</td><td>' + wed + '</td>');
                } else if (monthDay === 'Fri') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + fri + '</td><td>' + sat + '</td><td>' + sun + '</td><td>' + mon + '</td><td>' + tue + '</td><td>' + wed + '</td><td>' + thu + '</td>');
                } else if (monthDay === 'Sat') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + sat + '</td><td>' + sun + '</td><td>' + mon + '</td><td>' + tue + '</td><td>' + wed + '</td><td>' + thu + '</td><td>' + fri + '</td>');
                } else if (monthDay === 'Sun') {
                    jQuery('.qcc-calendar-container thead tr').append('<td>' + sun + '</td><td>' + mon + '</td><td>' + tue + '</td><td>' + wed + '</td><td>' + thu + '</td><td>' + fri + '</td><td>' + sat + '</td>');
                }
            }
            jQuery(getDaysInMonth(monthNumber - 1, yearNumber)).each(function (index) {
                var index = index + 1;
                if (index < 8) {
                    jQuery('.qcc-calendar-container tbody tr.1').append('<td date-year="' + yearNumber + '" date-month="' + monthNumber + '" date-day="' + index + '">' + index + '</td>');
                } else if (index < 15) {
                    jQuery('.qcc-calendar-container tbody tr.2').append('<td date-year="' + yearNumber + '" date-month="' + monthNumber + '" date-day="' + index + '">' + index + '</td>');
                } else if (index < 22) {
                    jQuery('.qcc-calendar-container tbody tr.3').append('<td date-year="' + yearNumber + '" date-month="' + monthNumber + '" date-day="' + index + '">' + index + '</td>');
                } else if (index < 29) {
                    jQuery('.qcc-calendar-container tbody tr.4').append('<td date-year="' + yearNumber + '" date-month="' + monthNumber + '" date-day="' + index + '">' + index + '</td>');
                } else if (index < 32) {
                    jQuery('.qcc-calendar-container tbody tr.5').append('<td date-year="' + yearNumber + '" date-month="' + monthNumber + '" date-day="' + index + '">' + index + '</td>');
                }
                i++;
            });
            var date = new Date();
            var month = date.getMonth() + 1;
            setCurrentDay(month);
            setEvent();
            displayEvent();
        }

        /**
         * Get current day and set as '.current-day'
         */
        function setCurrentDay(month) {
            var viewMonth = jQuery('.month').attr('data-month');
            if (parseInt(month, 10) === parseInt(viewMonth, 10)) {
                jQuery('.qcc-calendar-container tbody td[date-day="' + d.getDate() + '"]').addClass('qcc-current-day');
            }
        }

        /**
         * Add class '.active' on calendar date
         */
        jQuery('.qcc-calendar-container tbody td').on('click', function (e) {
            if (jQuery(this).hasClass('qcc-event')) {
                jQuery('.qcc-calendar-container tbody td').removeClass('active');
                jQuery(this).addClass('active');
            } else {
                jQuery('.qcc-calendar-container tbody td').removeClass('active');
            }
        });

        /**
         * Add '.event' class to all days that has an event
         */
        function setEvent() {
            jQuery('.qcc-day-event').each(function (i) {
                var eventYear = jQuery(this).attr('date-year'),
                    eventMonth = jQuery(this).attr('date-month'),
                    eventDay = jQuery(this).attr('date-day');

                jQuery('.qcc-calendar-container tbody tr td[date-year="' + eventYear + '"][date-month="' + eventMonth + '"][date-day="' + eventDay + '"]').addClass('qcc-event');
            });
        }

        /**
         * Get current day on click in calendar
         * and find day-event to display
         */
        function displayEvent() {
            jQuery('.qcc-calendar-container tbody td').on('click', function (e) {
                jQuery('.qcc-day-event').slideUp('fast');
                var monthEvent = jQuery(this).attr('date-month'),
                    yearEvent = jQuery(this).attr('date-year'),
                    dayEvent = jQuery(this).text();

                jQuery('.qcc-day-event[date-year="' + yearEvent + '"][date-month="' + monthEvent + '"][date-day="' + dayEvent + '"]').slideDown('fast');
            });
        }

        /**
         * Close day-event
         */
        jQuery('.qcc-close').on('click', function (e) {
            jQuery(this).parent().slideUp('fast');
            e.preventDefault();
        });
    }
};

jQuery(document).ready(function () {
    calendar.init();
});
