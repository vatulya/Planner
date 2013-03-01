(function (window, document, $) {

    var Calendar = {

        init: function() {
        },

        // THIS IS TEMPORARY METHOD. NEED MOVE IT TO GLOBAL
        setDataEl: function(el, key, value) {
            if (el.length) {
                el.data(key, value);
                el.attr('data-' + key, value);
            }
        },

        getCalendar: function(el) {
            el = $(el);
            var calendar = el.parents('.module-calendar');
            return calendar;
        },

        getData: function(el) {
            var data = {};
            data.container_id       = el.data('container-id');
            data.selected_dates     = el.data('selected-dates');
            data.old_selected_dates = el.data('old-selected-dates');
            data.blocked_dates      = el.data('blocked-dates');
            data.show_date          = el.data('show-date');
            data.editable           = el.data('editable');
            data.max_select         = el.data('max-select');
            if (typeof data.selected_dates == 'undefined' || data.selected_dates == '') {
                data.selected_dates = [];
            } else {
                data.selected_dates = data.selected_dates.split(',');
            }
            if (typeof data.old_selected_dates == 'undefined' || data.old_selected_dates == '') {
                data.old_selected_dates = [];
            } else {
                data.old_selected_dates = data.old_selected_dates.split(',');
            }
            if (typeof data.blocked_dates == 'undefined' || data.blocked_dates == '') {
                data.blocked_dates = [];
            } else {
                data.blocked_dates = data.blocked_dates.split(',');
            }
            if (data.editable) {
                data.editable = 1;
            } else {
                data.editable = 0;
            }
            if (data.max_select > 0) {
                data.max_select = data.max_select;
            } else {
                data.max_select = 0;
            }
            return data;
        },

        setData: function(el, data) {
            var value;
            if (typeof data.container_id != 'undefined' && data.container_id != '') {
                value = data.container_id;
                Calendar.setDataEl(el, 'container-id', value);
            }
            if (typeof data.selected_dates != 'undefined' && data.selected_dates.length) {
                value = data.selected_dates.join(',');
                Calendar.setDataEl(el, 'selected-dates', value);
            }
            if (typeof data.old_selected_dates != 'undefined' && data.old_selected_dates.length) {
                value = data.old_selected_dates.join(',');
                Calendar.setDataEl(el, 'old-selected-dates', value);
            }
            if (typeof data.blocked_dates != 'undefined' && data.blocked_dates.length) {
                value = data.blocked_dates.join(',');
                Calendar.setDataEl(el, 'blocked-dates', value);
            }
            if (typeof data.show_date != 'undefined' && data.show_date != '') {
                value = data.show_date;
                Calendar.setDataEl(el, 'show-date', value);
            }
            if (typeof data.editable != 'undefined' && data.editable != '') {
                value = data.editable;
                Calendar.setDataEl(el, 'editable', value);
            }
            if (typeof data.max_select != 'undefined' && data.max_select != '') {
                value = data.max_select;
                Calendar.setDataEl(el, 'max-select', value);
            }
        },

        render: function(el) {
            if (el.length) {
                Calendar.refresh(el);
                Calendar.bind(el);
            }
        },

        refresh: function(el) {
            var data = Calendar.getData(el);
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
                    Calendar.checkSelectedLimit(el);
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

            container.undelegate('.calendar-day-cell', 'click');
            container.undelegate('.calendar-change-date', 'click');
            container.undelegate('.calendar-change-date-select', 'change');

            $(container).on('click', '.calendar-day-cell', function(e) {
                if (el.data('editable')) {
                    Calendar.toggleSelected(e.target);
                }
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
            if (el.hasClass('blocked')) {
                return; // You can't edit blocked dates
            }
            if (el.hasClass('past-blocked')) {
                return; // You can't edit past dates
            }
            if (el.hasClass('old-selected')) {
                return; // You can't edit old seleted dates
            }
            if (el.hasClass('temporary-blocked')) {
                return; // You can't edit this cell temporary. See 'select_limit'
            }
            var date, data, calendar, i;
            calendar = Calendar.getCalendar(el);
            el.toggleClass('selected');
            data = Calendar.getData(calendar);
            date = el.data('date');
            i = $.inArray(date, data.selected_dates);
            if (el.hasClass('selected')) {
                if (i === -1) {
                    data.selected_dates.push(date);
                }
            } else {
                if (i > -1) {
                    data.selected_dates.splice(i, 1);
                }
            }
            calendar.data('selected-dates', data.selected_dates.join(','));
            calendar.attr('data-selected-dates', data.selected_dates.join(','));
            Calendar.checkSelectedLimit(calendar);
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
        },

        checkSelectedLimit: function(calendar) {
            var data = Calendar.getData(calendar);
            if (data.max_select > 0) {
                if (data.max_select <= data.selected_dates.length) {
                    calendar.find('.calendar-day-cell').not('.selected').addClass('temporary-blocked');
                } else {
                    calendar.find('.calendar-day-cell').removeClass('temporary-blocked');
                }
            }
        }

    };
    window.Calendar = Calendar;

    $(function() {

        Calendar.init();

    });

})(this, this.document, this.jQuery);
