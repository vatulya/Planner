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
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        Checking.checkUser(response.data);
                    } else {
                        alert(response.message);
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
                success: function(response) {
                    Checking.userCheckingModalContainer.html(response);
                    Checking.userCheckingModalContainer.data('user', user);
                    Checking.loadUserWorkData(user, date);
                    Checking.userCheckingModal.modal('show');
                }
            });
        },

        loadUserWorkData: function(user, date) {
            $.ajax({
                url: '/checking/get-user-work-data',
                data: {
                    format: 'json',
                    user: user,
                    date: date
                },
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        Checking.showUserWorkData(response.data);
                    } else {
                        alert(response.message);
                    }
                }
            });
        },

        showUserWorkData: function(data)
        {
            var plan = window.Text.secondsToParts(data.work_hours_plan);
            var done = window.Text.secondsToParts(data.work_hours_done);
            var overtime = window.Text.secondsToParts(data.work_hours_overtime);

            plan = plan[0] + 'h ' + plan[1] + 'm ' + plan[2] + 's';
            done = done[0] + 'h ' + done[1] + 'm ' + done[2] + 's';
            overtime = overtime[0] + 'h ' + overtime[1] + 'm ' + overtime[2] + 's';

            Checking.userCheckingModal.find('.work-hours-plan').html(plan);
            Checking.userCheckingModal.find('.work-hours-done').html(done);
            Checking.userCheckingModal.find('.work-hours-overtime').html(overtime);
        },

        toggleEditCheckData: function(el, isEdit) {
            el = $(el);
            var row = $('#checkin-' + el.data('checkin-id'));
            if (row.length) {
                if (isEdit) {
                    row.addClass('edit');
                } else {
                    row.removeClass('edit');
                }
            }
        },

        getEditCheckData: function() {
            var data = {
                user: undefined,
                date: undefined,
                checks: []
            };
            var selectedDate = Checking.userCheckingModalContainer.find('#selected-date');
            var selectedUser = Checking.userCheckingModalContainer.find('#selected-user');
            if (selectedDate.length && selectedUser.length) {
                data.date = selectedDate.data('selected-date');
                data.user = selectedUser.data('selected-user');
                Checking.userCheckingModalContainer.find('.user-check-row.edit').each(function(i, el) {
                    el = $(el);
                    var row = {
                        id: el.data('checkin-id'),
                        check_in: {
                            hours: el.find('.check-in-hour').val(),
                            mins: el.find('.check-in-min').val()
                        },
                        check_out: {
                            hours: el.find('.check-out-hour').val(),
                            mins: el.find('.check-out-min').val()
                        }
                    };
                    data.checks.push(row);
                });
            }
            return data;
        },

        saveCheckData: function() {
            var data = Checking.getEditCheckData();
            if (data.checks.length) {
                data.format = 'json';
                $.ajax({
                    url: '/checking/save-user-checks',
                    data: data,
                    success: function(response) {
                        response = response.response;
                        if (response.status) {
                            Checking.loadUserCheckingHistory(data.user, data.date);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(response) {
                        alert('Error! Something wrong.');
                    }
                });
            }
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
        $(document.body).on('click', '.edit-check-data', function(e) {
            Checking.toggleEditCheckData(e.currentTarget, true);
        });
        $(document.body).on('click', '.cancel-edit-check-data', function(e) {
            Checking.toggleEditCheckData(e.currentTarget, false);
        });
        $(document.body).on('click', '#save-checkin-data-button', function(e) {
            Checking.saveCheckData();
        });

    });

})(this, this.document, this.jQuery);
