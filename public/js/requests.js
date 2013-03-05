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
                Requests.refreshHolidays();
            });
            Requests.calendarContainer.on('calendar-selected-changed', function(e) {
                Requests.refreshSelectedDates();
            });
            $(document.body).on('click', '#save-request', function(e) {
                Requests.saveRequest();
            });
        },

        refreshHolidays: function() {
            var holidays = [];
            var holidaysTitles = {};
            if (window.calendarHolidays != undefined && typeof window.calendarHolidays == 'object') {
                holidays = window.calendarHolidays;
                holidaysTitles = window.calendarHolidaysTitles;
            }
            Requests.calendarContainer.find('.calendar-day-cell').each(function(i, el) {
                el = $(el);
                var date = el.data('date');
                if ($.inArray(date, holidays) > -1) {
                    el.addClass('holiday');
                    el.addClass('blocked');
                    if (holidaysTitles[date] != undefined) {
                        el.addClass('show-tooltip');
                        Form.setDataEl(el, 'original-title', holidaysTitles[date]);
                        Form.setDataEl(el, 'placement', 'bottom');
                        el.tooltip();
                    }
                }
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
        },

        saveRequest: function() {
            var selectedDates, data;
            selectedDates = Calendar.getData(Requests.calendar).selected_dates;
            data = {
                selected_dates: selectedDates,
                format: 'json'
            };
            if (selectedDates.length) {
                $.ajax({
                    url: PLANNER.BASE_URL + '/requests/save-request',
                    data: data,
                    success: function(response) {
                        response = response.response;
                        if (response.status) {
                            window.location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(response) {
                        alert('Error! Something wrong. AJAX error. Please try later.');
                    }
                });
            }
        }

    };

    $(function() {

        Requests.init();

    });

})(this, this.document, this.jQuery);
