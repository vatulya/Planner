(function (window, document, $) {

    var Checking = {

        today: null,

        userCheckingModal: null,
        userCheckingModalContainer: null,

        calendar: null,
        calendarContainer: null,

        init: function() {
            Checking.today = $('#date-today').val();

            Checking.userCheckingModal = $('#show-user-checking-modal');
            Checking.userCheckingModalContainer = Checking.userCheckingModal.find('.user-checking-container');

            Checking.calendarContainer = $('#user-checking-calendar-container');
            if (Checking.calendarContainer) {
                Checking.initCalendar();
                Checking.initCalendarBinds();
            }
        },

        initCalendar: function() {
            Calendar.render(Checking.calendarContainer.find('.module-calendar'));
        },

        initCalendarBinds: function() {
            Checking.calendarContainer.on('calendar-loaded', function(e, calendar) {
                Checking.calendar = calendar;
            });
            $(Checking.calendarContainer).on('click', '.calendar-day-cell', function(e) {
                Checking.refreshSelectedDate(e.currentTarget);
            });
        },

        refreshSelectedDate: function(el) {
            el = $(el);
            var date = el.data('date');
            var user = Checking.userCheckingModalContainer.data('user');
            Calendar.setData(Checking.calendar, {blocked_dates: [date]});
            Calendar.refresh(Checking.calendar);
            Checking.loadUserCheckingHistory(user, date);
        },

        checkUser: function(user) {
            var userEl = $('.user-row.user-id-' + user.id);
            if (userEl.length > 0) {
                if (user.check_in == undefined) {user.check_in = '';}
                if (user.check_out == undefined) {user.check_out = '';}
                if (user.check_out != '') {
                    userEl.removeClass('checked-in');
                    userEl.addClass('checked-out');
                    userEl.addClass('success');
                } else if (user.check_in != '') {
                    userEl.removeClass('checked-out');
                    userEl.addClass('checked-in');
                    userEl.addClass('success');
                } else {
                    userEl.removeClass('checked-in');
                    userEl.removeClass('checked-out');
                    userEl.removeClass('success');
                }
                userEl.find('.user-check-in').html(user.check_in);
                userEl.find('.user-check-out').html(user.check_out);
            }
        },

        userCheck: function(el, check) {
            el = $(el);
            $.ajax({
                url: '/checking/user-check',
                data: {
                    format: 'json',
                    user: el.data('user-id'),
                    group: el.data('group-id'),
                    check: check
                },
                success: function(data) {
                    data = data.response;
                    if (data.status) {
                        Checking.checkUser(data.data);
                    } else {
                        alert(data.message);
                    }
                }
            });
        },

        showUserCheckingHistory: function(user) {
            Calendar.setData(Checking.calendar, {blocked_dates: [Checking.today], show_date: Checking.today});
            Calendar.refresh(Checking.calendar);
            Checking.loadUserCheckingHistory(user, Checking.today);
        },

        loadUserCheckingHistory: function(user, date) {
            $.ajax({
                url: '/checking/get-user-checking-history',
                data: {
                    format: 'html',
                    user: user,
                    date: date
                },
                success: function(data) {
                    Checking.userCheckingModalContainer.html(data);
                    Checking.userCheckingModalContainer.data('user', user);
                    Checking.userCheckingModal.modal('show');
                }
            });
        }

    };

    $(function() {

        Checking.init();

        $(document.body).on('click', '.btn-user-check-in', function(e) {
            Checking.userCheck(e.currentTarget, 'in');
        });
        $(document.body).on('click', '.btn-user-check-out', function(e) {
            Checking.userCheck(e.currentTarget, 'out');
        });
        $(document.body).on('click', '.show-history .user-full-name', function(e) {
            var el = $(e.currentTarget);
            if (el.data('is-admin')) {
                Checking.userCheckingModal.addClass('is-admin');
            } else {
                Checking.userCheckingModal.removeClass('is-admin');
            }
            Checking.showUserCheckingHistory(el.data('user-id'));
        });

    });

})(this, this.document, this.jQuery);
