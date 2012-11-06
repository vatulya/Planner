(function (window, document, $) {

    var Calendar = {

        getCalendar: function(el) {
            el = $(el);
            var calendar = el.parents('.module-calendar');
            return calendar;
        },

        getData: function(el) {
            var data = {};
            data.selected_dates     = el.data('selected-dates');
            data.old_selected_dates = el.data('old-selected-dates');
            if (typeof data.selected_dates == 'undefined' || data.selected_dates == '') {
                data.selected_dates == [];
            } else {
                data.selected_dates = data.selected_dates.split(',');
            }
            if (typeof data.old_selected_dates == 'undefined' || data.old_selected_dates == '') {
                data.old_selected_dates == [];
            } else {
                data.old_selected_dates = data.old_selected_dates.split(',');
            }
            return data;
        },

        refreshData: function(el) {
            var selected_dates = Calendar.getData(el).selected_dates;
            el.find('.calendar-day-cell').each(function(i, el) {
                el = $(el);
                var d = el.data('date');
                if (el.hasClass('selected') && !$.inArray(d, selected_dates))
                selected_dates.push(d);
            });
            selected_dates = selected_dates.join(',');
            el.data('selected-dates', selected_dates);
            el.attr('data-selected-dates', selected_dates);
        },

        render: function(el) {
            Calendar.refresh(el);
            Calendar.bind(el);
        },

        refresh: function(el) {
            var data = Calendar.getData(el);
            data.container_id       = el.data('container-id');
            data.show_date          = el.data('show-date');
            data.change_direction   = el.data('change-direction');
            data.change_type        = el.data('change-type');
            data.set_show_date      = el.data('set-show-date');
            data.format             = 'html';

            $.ajax({
                url: '/calendar/',
                data: data,
                success: function(response) {
                    var container = $('#' + el.data('container-id'));
                    var parent = el.parent();
                    parent.html(response);
                    el = container.find('.module-calendar');
                    container.trigger('calendar-loaded', [el]);
                },
                error: function(response) {
                    alert('Calendar is not loaded. Error.');
                }
            });
        },

        bind: function(el) {
            var container = $('#' + el.data('container-id'));
            if ( ! container.length) return;
            $(container).on('click', '.calendar-day-cell', function(e) {
                Calendar.toggleSelected(e.target);
            });
            $(container).on('click', '.calendar-change-date', function(e) {
                Calendar.changeDate(e.currentTarget);
            });
            $(container).on('change', '.calendar-change-date-select', function(e) {
                Calendar.selectDate(e.currentTarget);
            });
        },

        toggleSelected: function(el) {
            el = $(el);
            el.toggleClass('selected');
            Calendar.refreshData(el.parents('.module-calendar'));
            el.trigger('calendar-selected-changed');
        },

        changeDate: function(el) {
            el = $(el);
            var calendar = Calendar.getCalendar(el);
            calendar.data('change-direction', el.data('change-direction'));
            calendar.data('change-type', el.data('change-type'));
            Calendar.refresh(calendar);
        },

        selectDate: function(el) {
            el = $(el);
            var calendar = Calendar.getCalendar(el);
            var month = calendar.find('.calendar-select-month').val();
            var year = calendar.find('.calendar-select-year').val();
            calendar.data('set-show-date', year + '-' + month + '-01');
            calendar.attr('date-set-data-show-date', year + '-' + month + '-01');
            Calendar.refresh(calendar);
        }

    };
    window.Calendar = Calendar;

    $(function() {
    });

})(this, this.document, this.jQuery);
