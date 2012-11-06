(function (window, document, $) {

    var Requests = {

        calendar: null,
        calendarContainer: null,

        init: function() {
            Requests.calendarContainer = $('#request-calendar-container');
            if (Requests.calendarContainer) {
                Requests.initCalendar();
                Requests.initBinds();
            }
        },

        initCalendar: function() {
            Calendar.render(Requests.calendarContainer.find('.module-calendar'));
        },

        initBinds: function() {
            Requests.calendarContainer.on('calendar-loaded', function(e, calendar) {
                Requests.calendar = calendar;
                Requests.refreshSelectedDates();
            });
            Requests.calendarContainer.on('calendar-selected-changed', function(e) {
                Requests.refreshSelectedDates();
            });
        },

        refreshSelectedDates: function() {
            var data = Calendar.getData(Requests.calendar);
            var span = Requests.calendarContainer.find('.request-calendar-selected-days');
            var selectedDates = data.selected_dates;
            if (typeof selectedDates == 'object') {
                selectedDates = selectedDates.join(', ');
            } else if (typeof selectedDates != 'string') {
                selectedDates = '';
            }
            span.html(selectedDates);
        }

    };

    $(function() {

        Requests.init();

    });

})(this, this.document, this.jQuery);
