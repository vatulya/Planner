(function (window, document, $) {

    var modal = $('.modal-edit-day'),
        modalEditH3 = modal.find('.header-edit-day'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalBody = modal.find('.modal-body');
        userCheckingModalContainer = modal.find('.user-checking-container');

    var DaySettings = {

        editDay: function(el) {
            el = $(el);
            modalEditH3.show();
            modalBody.html('Loading...');
            modal.modal();
            userCheckingModalContainer.html('');
            modal.find('.work-hours-plan').html('');
            modal.find('.work-hours-done').html('');
            modal.find('.work-hours-overtime').html('');
            $.ajax({
                url: el.attr('href'),
                data: {
                    day: el.data('day-id')
                },
                success: function(response) {
                    modalBody.html(response);
                    //DaySettings.initEditAjaxForm();
                    DaySettings.loadUserCheckingHistory(el.data('user-id'), el.data('date'));
                    DaySettings.loadUserWorkData(el.data('user-id'), el.data('date'));
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
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
                    userCheckingModalContainer.html(response);
                    modal.find('.edit-check-data').html('');
                    //Checking.userCheckingModalContainer.data('user', user);
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
                        DaySettings.showUserWorkData(response.data);
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

            modal.find('.work-hours-plan').html(plan);
            modal.find('.work-hours-done').html(done);
            modal.find('.work-hours-overtime').html(overtime);
        },

        selectSecondStatus: function(el) {
            return true;
            el = $(el);
            var oldStatus = $('#select-second-status-button').html();
            $('#select-second-status-button').html(el.data('toggle'));
            el.data('toggle', oldStatus)  ;
        },

        saveDay: function() {
            var data = {
                format: 'json',
                user_id:  $('.form-edit-day').data('user-id'),
                date:    $('.form-edit-day').data('date'),
                group_id: $('.form-edit-day').data('group-id'),
                day_status_data: DaySettings.getDayStatusData()
            };
            $.ajax({
                url: '/planning/save-day-form',
                data: data,
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        $.ajax({
                            url: '/overview/recalculate-history-week-for-user-by-date',
                            data: data,
                            success: function(response) {
                                response = response.response;
                                if (response.status) {
                                    window.location.reload();
                                } else {
                                    modalBody.html('Error! Recalculate history error. Try again later.');
                                }
                            },
                            error: function(response) {
                                modalBody.html('Error! Recalculate History error. Try again later.');
                            }
                        });
                    } else {
                        alert('Error! Something wrong.');
                    }
                },
                error: function(response) {
                    alert('Error! Something wrong.');
                }
            });
        },

        getDayStatusData: function() {
            var data = {};
            $('.work-time').each(function(i, el) {
                el = $(el);
                var statusId = el.data('status-id');
                var status = {
                    statusId: statusId,
                    time_start: {
                        hour: el.find('.start-hour').val(),
                        min: el.find('.start-min').val()
                    },
                    time_end: {
                        hour: el.find('.end-hour').val(),
                        min: el.find('.end-min').val()
                    }
                }
                data[statusId] = status;
            });
            return data;
        },

        applyTimeFullDay: function(el) {
            el = $(el);
            $('#time_start2').attr('value',  $('#time_start').attr('value'));
            $('#time_end2').attr('value',  $('#time_end').attr('value'));
        },

        changeSelectedColor: function(el) {
            el = $(el);
            $('.day-status-color').removeClass('active');
            el.addClass('active');
            var color = $('#form-edit-day').find('#color');
            if (color) {
                color.val(el.data('color'));
                $('#status2').val( el.data('status'));
                //    alert($('#status1').val + el.data('color'));
            }
        },
        checkHourInput: function(el) {
            el = $(el);
            var inputValue = el.attr('value');
            if (inputValue !== '') {
                inputValue = inputValue * 1
                if (! inputValue) {
                    inputValue = 0;
                }
                if (inputValue > 23 ) {
                    el.attr('value',23);
                } else if  (inputValue <= 0) {
                    el.attr('value',0);
                } else {
                    el.attr('value',inputValue);
                }
            }
        },
        checkMinuteInput: function(el) {
            el = $(el);
            var inputValue = el.attr('value');
            if (inputValue !== '') {
                inputValue = inputValue * 1
                if (! inputValue) {
                    inputValue = 0;
                }
                if (inputValue > 59 ) {
                    el.attr('value',59);
                } else if  (inputValue <= 0) {
                    el.attr('value',0);
                } else {
                    el.attr('value',inputValue);
                }
            }
        },
        saveRequest: function(el) {
            var data;
            var selectedDates = [];
            selectedDates[0] = $(el).data('request-date');
            data = {
                selected_dates: selectedDates,
                user: $(el).data('user-id'),
                format: 'json'
            };
            if (selectedDates.length) {
                $.ajax({
                    url: '/requests/save-request',
                    data: data,
                    success: function(response) {
                        response = response.response;
                        if (response.status) {
                            alert('Request Sended.');
                            window.location.reload();
                        } else {
                            alert('Error! Something wrong.');
                        }
                    },
                    error: function(response) {
                        alert('Error! Something wrong.');
                    }
                });
            }
        },

        rejectRequest: function(el) {
            var data;
            var selectedDates = $(el).data('request-date');
            data = {
                request_date: selectedDates,
                user_id: $(el).data('user-id'),
                format: 'json',
                date: selectedDates,
                status: 'refunded'
            };
            if (selectedDates.length) {
                $.ajax({
                    url: '/open-requests/set-status/',
                    data: data,
                    success: function(response) {
                        response = response.response;
                        if (response) {
                            $.ajax({
                                url: '/overview/recalculate-history-week-for-user-by-date',
                                data: data,
                                success: function(response) {
                                    response = response.response;
                                    if (response.status) {
                                        alert('Request Sended.');
                                        window.location.reload();
                                    } else {
                                        modalBody.html('Error! Recalculate history error. Try again later.');
                                    }
                                },
                                error: function(response) {
                                    modalBody.html('Error! Something wrong.');
                                }
                            });
                        } else {
                            alert('Error! Something wrong.');
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
        $(document.body).on('click', '.edit-day', function(e) {
            DaySettings.editDay(e.currentTarget);
        });
        $(document.body).on('click', '#apply-time-full-day', function(e) {
            DaySettings.applyTimeFullDay(e.currentTarget);
        });
        $(document.body).on('click', '#form-edit-day', function(e) {
            DaySettings.saveDay(e.currentTarget);
        });
        $(document.body).on('click', '.day-status-color', function(e) {
            DaySettings.changeSelectedColor(e.currentTarget);
        });
        $(document.body).on('blur', '.set-time-hour-field', function(e) {
            DaySettings.checkHourInput(e.currentTarget);
        });
        $(document.body).on('blur', '.set-time-mins-field', function(e) {
            DaySettings.checkMinuteInput(e.currentTarget);
        });
        $(document.body).on('click', '.sendRequest', function(e) {
            DaySettings.saveRequest(e.currentTarget);
        });
        $(document.body).on('click', '.sendRefund', function(e) {
            DaySettings.rejectRequest(e.currentTarget);
        });
    });

})(this, this.document, this.jQuery);

