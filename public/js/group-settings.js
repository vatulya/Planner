(function (window, document, $) {

    var modal = $('.modal-edit-group'),
        modalCreateH3 = modal.find('.header-create-group'),
        modalEditH3 = modal.find('.header-edit-group'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalDeleteGroup = modal.find('.button-delete-group'),
        modalBody = modal.find('.modal-body')
        ;

    var modalStatus = $('.modal-edit-status'),
        modalTmplGroupNameStatus = modalStatus.find('.tmpl-group-name'),
        modalBodyStatus = modalStatus.find('.modal-body')
        ;

    var modalInterval = $('.modal-edit-interval'),
        modalCreateH3Interval = modalInterval.find('.header-create-interval'),
        modalEditH3Interval = modalInterval.find('.header-edit-interval'),
        modalBodyInterval = modalInterval.find('.modal-body')
        ;

    var modalPauseInterval = $('.modal-edit-pause-interval'),
        modalCreateH3PauseInterval = modalPauseInterval.find('.header-create-pause-interval'),
        modalEditH3PauseInterval = modalPauseInterval.find('.header-edit-pause-interval'),
        modalBodyPauseInterval = modalPauseInterval.find('.modal-pause-body')
        ;

    var groupPlanning = $('.group-work-days-planning'),
        groupPlanningSelect = $('#group-planning'),
        groupPlanningBody = groupPlanning.find('.planning-body');

    var GroupSettings = {

        calendarContainer: $('#exceptions-calendar-container'),
        calendar: null,


        init: function() {
            GroupSettings.initMiniAjaxForms();
            GroupSettings.initCalendar();
        },

        initMiniAjaxForms: function() {
            $('.mini-ajax-form').each(function(i, el) {
                var formEl = $(el);
                formEl.ajaxForm({
                    data: {format: 'json'},
                    beforeSubmit: function() {
                        window.Form.hideAllNotificationsMini(formEl);
                        window.Form.blockForm(formEl);
                    },
                    success: function(response) {
                        window.Form.unblockForm(formEl);
                        response = response.response;
                        if (response.status) {
                            window.Form.showSuccessMini(formEl, 2000);
                        } else {
                            window.Form.showErrorsMini(formEl);
                        }
                    },
                    error: function() {
                        window.Form.unblockForm(formEl);
                        window.Form.showErrorsMini(formEl);
                    }
                });
            });
        },

        editGroup: function(el) {
            el = $(el);
            modalCreateH3.hide();
            modalEditH3.show();
            modalDeleteGroup.show().data('group-id', (el.data('group-id'))).data('group-name', el.data('group-name'));
            modalTmplGroupName.html(el.data('group-name'));
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    group: el.data('group-id')
                },
                success: function(response) {
                    modalBody.html(response);
                    GroupSettings.initEditAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
        },

        editStatus: function(el) {
            el = $(el);
            modalTmplGroupName.html(el.data(''));
            modalBodyStatus.html('Loading...');
            modalStatus.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    status_id: el.data('status-id')
                },
                success: function(response) {
                    modalBodyStatus.html(response);
                    GroupSettings.initEditStatusAjaxForm();
                },
                error: function(response) {
                    modalBodyStatus.html('Error! Something wrong.');
                }
            });
        },

        editInterval: function(el) {
            el = $(el);
            modalBodyInterval.html('Loading...');
            modalCreateH3Interval.hide();
            modalEditH3Interval.show();
            modalInterval.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    interval_id: el.data('interval-id')
                },
                success: function(response) {
                    modalBodyInterval.html(response);
                    GroupSettings.initEditIntervalAjaxForm();
                },
                error: function(response) {
                    modalBodyInterval.html('Error! Something wrong.');
                }
            });
        },

        setWorkPlanInterval: function(el) {
            el = $(el);
            $.ajax({
                url: el.attr('href'),
                data: {
                    current_interval_id: el.data('current-interval-id'),
                    interval_id: el.data('interval-id'),
                    day_number: el.data('day-number'),
                    week_type: el.data('week-type'),
                    group_id: el.data('group-id'),
                    user_id: el.data('user-id'),
                    format: 'json'
                },
                success: function(response) {
                    GroupSettings.selectGroupPlanning();
                    //modalBodyInterval.html(response);
                    //GroupSettings.initEditIntervalAjaxForm();
                },
                error: function(response) {
                    //modalBodyInterval.html('Error! Something wrong.');
                }
            });
        },

        setPausePlanInterval: function(el) {
            el = $(el);
            $.ajax({
                url: el.attr('href'),
                data: {
                    planning_id: el.data('planning-id'),
                    pause_id: el.data('pause-id'),
                    pause_delete: el.data('pause-delete'),
                    format: 'json'
                },
                success: function(response) {
                    GroupSettings.selectGroupPlanning();
                },
                error: function(response) {
                }
            });
        },

        createInterval: function(el) {
            el = $(el);
            modalBodyInterval.html('Loading...');
            modalCreateH3Interval.show();
            modalEditH3Interval.hide();
            modalInterval.modal();
            $.ajax({
                url: el.attr('href'),
                data: {},
                success: function(response) {
                    modalBodyInterval.html(response);
                    GroupSettings.initEditIntervalAjaxForm();
                },
                error: function(response) {
                    modalBodyInterval.html('Error! Something wrong.');
                }
            });
        },

        editPauseInterval: function(el) {
            el = $(el);
            modalBodyPauseInterval.html('Loading...');
            modalCreateH3PauseInterval.hide();
            modalEditH3PauseInterval.show();
            modalPauseInterval.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    interval_id: el.data('pause-interval-id')
                },
                success: function(response) {
                    modalBodyPauseInterval.html(response);
                    GroupSettings.initEditPauseIntervalAjaxForm();
                },
                error: function(response) {
                    modalBodyPauseInterval.html('Error! Something wrong.');
                }
            });
        },

        createPauseInterval: function(el) {
            el = $(el);
            modalBodyPauseInterval.html('Loading...');
            modalCreateH3PauseInterval.show();
            modalEditH3PauseInterval.hide();
            modalPauseInterval.modal();
            $.ajax({
                url: el.attr('href'),
                data: {},
                success: function(response) {
                    modalBodyPauseInterval.html(response);
                    GroupSettings.initEditPauseIntervalAjaxForm();
                },
                error: function(response) {
                    modalBodyPauseInterval.html('Error! Something wrong.');
                }
            });
        },

        createGroup: function(el) {
            el = $(el);
            modalCreateH3.show();
            modalEditH3.hide();
            modalDeleteGroup.hide();
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                success: function(response) {
                    modalBody.html(response);
                    GroupSettings.initEditAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
        },

        deleteGroup: function(el) {
            el = $(el);
            var groupName = el.data('group-name');
            var groupId = el.data('group-id');
            if (groupId && groupName && confirm('Delete group "' + groupName + '"? Are you sure?')) {
                $.ajax({
                    url: el.attr('href'),
                    data: {
                        format: 'json',
                        group: groupId
                    },
                    success: function(response) {
                        response = response.response;
                        if (response.status) {
                            window.location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(response) {
                        alert('Error! Something wrong. AJAX error. Please try again later.');
                    }
                });
            }
        },

        initEditAjaxForm: function() {
            var formEl = $('#form-edit-group');
            $('#form-edit-group').ajaxForm({
                data: {format: 'json'},
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        window.Form.showSuccess(formEl);
                        window.location.reload();
                    } else {
                        window.Form.showErrors(formEl);
                    }
                },
                error: function() {
                    window.Form.showErrors(formEl);
                }
            });
        },

        initEditStatusAjaxForm: function() {
            var formEl = $('#form-edit-status');
            $(".is-holiday input[type='hidden']").attr('value',$('#is_holiday').attr('value'));
            $('#form-edit-status').ajaxForm({
                data: {format: 'json'},
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        window.Form.showSuccess(formEl);
                        window.location.reload();
                    } else {
                        window.Form.showErrors(formEl);
                    }
                },
                error: function() {
                    window.Form.showErrors(formEl);
                }
            });
        },

        initEditIntervalAjaxForm: function() {
            var formEl = $('#form-interval-status');
            $('#form-interval-status').ajaxForm({
                data: {format: 'json'},
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        window.Form.showSuccess(formEl);
                        window.location.reload();
                    } else {
                        window.Form.showErrors(formEl);
                    }
                },
                error: function() {
                    window.Form.showErrors(formEl);
                }
            });
        },

        initEditPauseIntervalAjaxForm: function() {
            var formEl = $('#form-pause-interval-status');
            $('#form-pause-interval-status').ajaxForm({
                data: {format: 'json'},
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        window.Form.showSuccess(formEl);
                        window.location.reload();
                    } else {
                        window.Form.showErrors(formEl);
                    }
                },
                error: function() {
                    window.Form.showErrors(formEl);
                }
            });
        },

        changeSelectedColor: function(el) {
            el = $(el);
            $('.group-color-variation').removeClass('active');
            el.addClass('active');
            var color = $('#form-edit-group').find('#color');
            if (color) {
                color.val(el.data('color'));
            }
            var color_hex = $('#form-interval-status').find('#color_hex');
            if (color_hex) {
                color_hex.val(el.data('color'));
            }
            var colorStatus = $('#form-edit-status').find('#color');
            if (colorStatus) {
                colorStatus.val(el.data('color'));
            }
        },

        selectGroupPlanning: function() {
            groupPlanningSelect.css('background-color', '#FFFFFF');
            //groupPlanningBody.html('');
            var option = $('option:selected', groupPlanningSelect);
            if (option.val() < 1) {
                return false;
            }
            groupPlanningSelect.css('background-color', '#' + option.data('group-color'));
            //groupPlanningBody.html('Loading...');
            $.ajax({
                url: '/group-settings/get-group-planning',
                data: {
                    group: option.val()
                },
                success: function(response) {
                    groupPlanningBody.html(response);
                    groupPlanningSelect.blur();
                    GroupSettings.initGroupPlanningForm();
                },
                error: function(response) {
                    groupPlanningBody.html('<span class="alert alert-error">Error! Something wrong. AJAX error. Please try later.</span>');
                }
            });
        },

        initGroupPlanningForm: function()
        {
            groupPlanningBody.find('.user-planning .week-day').each(function(i, el) {
                el = $(el);
                var enable = el.find('.enable-day');
                if (enable.length) {
                    GroupSettings.toggleUserDayEnable(enable);
                }
            });
        },

        toggleUserDayEnable: function(enableEl)
        {
            enableEl = $(enableEl);
            var weekDay = enableEl.parents('.week-day');
            if (weekDay.length) {
                if (enableEl.is(':checked')) {
                    weekDay.addClass('user-day-enabled');
                } else {
                    weekDay.removeClass('user-day-enabled');
                }
            }
        },

        saveGroupPlanning: function() {
//            GroupSettings.blockFormGroupPlanning();
            var container = $('.group-planning');
            var option = $('option:selected', groupPlanningSelect);
            if (option.val() < 1) {
                return false;
            }
            var data = {
                format: 'json',
                group: option.val(),
                group_planning: GroupSettings.getGroupPlanning()
            };
            $.ajax({
                url: '/group-settings/save-group-planning',
                data: data,
                success: function(response) {
//                    GroupSettings.unblockFormGroupPlanning();
                    response = response.response;
                    if (response.status) {
                        GroupSettings.showPlanningAlert(container, 'success', 'Success');
                        GroupSettings.selectGroupPlanning();
                    } else {
                        GroupSettings.showPlanningAlert(container, 'error', response.message);
                    }
                },
                error: function(response) {
//                    GroupSettings.unblockFormGroupPlanning();
                    GroupSettings.showPlanningAlert(container, 'error', 'Error! Something wrong. AJAX error. Please try later.');
                }
            });
        },

        saveUserPlanning: function(el) {
            el = $(el);
            var container = $('#' + el.data('container'));
            if ( ! container.length) {
                return;
            }
            var data = {
                format: 'json',
                group: container.data('group'),
                user: container.data('user'),
                user_planning: GroupSettings.getUserPlanning(container)
            };
            $.ajax({
                url: '/group-settings/save-user-planning',
                data: data,
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        GroupSettings.showPlanningAlert(container, 'success', 'Success');
                        GroupSettings.selectGroupPlanning();
                    } else {
                        GroupSettings.showPlanningAlert(container, 'error', response.message);
                    }
                },
                error: function(response) {
//                    GroupSettings.unblockFormGroupPlanning();
                    GroupSettings.showPlanningAlert(container, 'error', 'Error! Something wrong. AJAX error. Please try later.');
                }
            });
        },

        showPlanningAlert: function(container, status, message) {
            var alert = container.find('.alert');
            alert.removeClass('alert-success').removeClass('alert-error');
            alert.addClass('alert-' + status);
            alert.html(message);
            alert.show();
        },

        hidePlanningAlert: function() {
            groupPlanningBody.find('.alert').hide();
        },

        getGroupPlanning: function() {
            var data = {};
            $('.form-group-planning-day').each(function(i, el) {
                el = $(el);
                var day = {
                    day_number: el.data('day-number'),
                    week_type: el.data('week-type'),
                    time_start: {
                        hour: el.find('.work-time .start-hour').val(),
                        min: el.find('.work-time .start-min').val()
                    },
                    time_end: {
                        hour: el.find('.work-time .end-hour').val(),
                        min: el.find('.work-time .end-min').val()
                    },
                    pause_start: {
                        hour: el.find('.pause-time .start-hour').val(),
                        min: el.find('.pause-time .start-min').val()
                    },
                    pause_end: {
                        hour: el.find('.pause-time .end-hour').val(),
                        min: el.find('.pause-time .end-min').val()
                    }
                }
                data[day.week_type + '-' + day.day_number] = day;
            });
            return data;
        },

        getUserPlanning: function(container) {
            var data = {};
            container.find('.form-user-planning-day').each(function(i, el) {
                el = $(el);
                var day = {
                    day_number: el.data('day-number'),
                    week_type: el.data('week-type'),
                    enabled: el.find('.enable-day').is(':checked') * 1,
                    time_start: {
                        hour: el.find('.work-time .start-hour').val(),
                        min: el.find('.work-time .start-min').val()
                    },
                    time_end: {
                        hour: el.find('.work-time .end-hour').val(),
                        min: el.find('.work-time .end-min').val()
                    },
                    pause_start: {
                        hour: el.find('.pause-time .start-hour').val(),
                        min: el.find('.pause-time .start-min').val()
                    },
                    pause_end: {
                        hour: el.find('.pause-time .end-hour').val(),
                        min: el.find('.pause-time .end-min').val()
                    }
                }
                data[day.week_type + '-' + day.day_number] = day;
            });
            return data;
        },

        initCalendar: function() {
            GroupSettings.calendarContainer.on('calendar-loaded', function(e, calendar) {
                GroupSettings.calendar = calendar;
                GroupSettings.refreshSelectedDates();
            });
            GroupSettings.calendarContainer.on('calendar-selected-changed', function(e) {
                GroupSettings.refreshSelectedDates();
            });
        },

        refreshSelectedDates: function() {
            var data = Calendar.getData(GroupSettings.calendar);
            var modal = $('#edit-group-exceptions-modal');
            var span = modal.find('.exceptions-text');
            var selectedDates = data.selected_dates;
            if (typeof selectedDates == 'object') {
                selectedDates = selectedDates.join(', ');
            } else if (typeof selectedDates != 'string') {
                selectedDates = '';
            }
            span.html(selectedDates);
        },

        submitGroupExceptions: function() {
            var data = Calendar.getData(GroupSettings.calendar);
            var modal = $('#edit-group-exceptions-modal');
            data.max_free_people = modal.find('.max_free_people').val();
            data.group = modal.find('.group-id').val();
            data.edit_dates = modal.find('.edit-dates').val();
            if (data.edit_dates != '') {
                data.edit_dates = data.edit_dates.split(',');
            } else {
                data.edit_dates = [];
            }
            data.format = 'json';
            $.ajax({
                url: '/group-settings/save-group-exceptions',
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
        },

        showEditExceptionsPopup: function(el) {
            el = $(el);
            var modal = $('#edit-group-exceptions-modal');
            var body = modal.find('.modal-body');
            var max_free_people = 0;
            if (el.data('max_free_people') > 0) {
                max_free_people = el.data('max_free_people');
            }
            body.find('.max_free_people').val(max_free_people);
            body.find('.exceptions').val(el.data('selected-dates'));
            modal.find('.group-id').val(el.data('group'));
            modal.find('.edit-dates').val(el.data('edit-dates'));

            var calendar = body.find('.module-calendar');
            Form.setDataEl(calendar, 'old-selected-dates', el.data('old-selected-dates'));
            Form.setDataEl(calendar, 'selected-dates', el.data('selected-dates'));
            Form.setDataEl(calendar, 'show-date', el.data('show-date'));
            Calendar.render(calendar);

            modal.modal();
        },

        showCreateHolidayPopup: function(el) {
            el = $(el);
            var modal = $('#add-group-holidays-modal');
            var body = modal.find('.modal-body');
            modal.find('.group-id').val(el.data('group'));
            modal.find('.holiday_name').val('');

            var calendar = body.find('.module-calendar');
            Form.setDataEl(calendar, 'old-selected-dates', el.data('old-selected-dates'));
            Form.setDataEl(calendar, 'selected-dates', '');
            Form.setDataEl(calendar, 'show-date', '');
            Calendar.render(calendar);

            modal.modal();
        },

        submitGroupHolidays: function() {
            var modal, calendar, data;
            modal             = $('#add-group-holidays-modal');
            calendar          = modal.find('.module-calendar');
            data              = Calendar.getData(calendar);
            data.group        = modal.find('.group-id').val();
            data.holiday_name = modal.find('.holiday_name').val();
            data.format       = 'json';
            $.ajax({
                url: '/group-settings/save-group-holidays',
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
        },

        deleteGroupHoliday: function(el) {
            el = $(el);
            var text = el.data('holiday-name');
            text = 'Delete holiday "' + text + '"? Are you sure?';
            if (confirm(text)) {
                var data = {};
                data.holiday = el.data('holiday');
                data.format = 'json';
                $.ajax({
                    url: '/group-settings/delete-group-holidays',
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
        },

        saveDefaultTotalFreeHours: function() {
            var data = {};
            data.default_total_free_hours = $('#default_total_free_hours').val();
            data.format = 'json';
            $.ajax({
                url: '/group-settings/save-default-total-free-hours',
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

    };

    $(function() {

        $(document.body).on('click', '.edit-group', function(e) {
            GroupSettings.editGroup(e.currentTarget);
        });
        $(document.body).on('click', '.edit-status', function(e) {
            GroupSettings.editStatus(e.currentTarget);
        });
        $(document.body).on('click', '.edit-interval', function(e) {
            GroupSettings.editInterval(e.currentTarget);
        });
        $(document.body).on('click', '.set-work-plan-interval', function(e) {
            GroupSettings.setWorkPlanInterval(e.currentTarget);
        });
        $(document.body).on('click', '.set-pause-plan-interval', function(e) {
            GroupSettings.setPausePlanInterval(e.currentTarget);
        });
        $(document.body).on('click', '.create-interval', function(e) {
            GroupSettings.createInterval(e.currentTarget);
        });
        $(document.body).on('click', '.edit-pause-interval', function(e) {
            GroupSettings.editPauseInterval(e.currentTarget);
        });
        $(document.body).on('click', '.create-pause-interval', function(e) {
            GroupSettings.createPauseInterval(e.currentTarget);
        });
        $(document.body).on('click', '.create-group', function(e) {
            GroupSettings.createGroup(e.currentTarget);
        });
        $(document.body).on('click', '.group-color-variation', function(e) {
            GroupSettings.changeSelectedColor(e.currentTarget);
        });
        $(document.body).on('click', '.button-delete-group', function(e) {
            GroupSettings.deleteGroup(e.currentTarget);
        });
        $(document.body).on('change', '#group-planning', function(e) {
            GroupSettings.selectGroupPlanning();
        });
        $(document.body).on('change', '.enable-day', function(e) {
            GroupSettings.toggleUserDayEnable(e.currentTarget);
        });
        $(document.body).on('click', '.group-planning-save', function(e) {
            GroupSettings.saveGroupPlanning();
        });
        $(document.body).on('click', '.user-planning-save', function(e) {
            GroupSettings.saveUserPlanning(e.currentTarget);
        });
        $(document.body).on('click', '.create-group-exception, .edit-group-exception', function(e) {
            GroupSettings.showEditExceptionsPopup(e.currentTarget);
        });
        $(document.body).on('click', '.create-holiday', function(e) {
            GroupSettings.showCreateHolidayPopup(e.currentTarget);
        });
        $(document.body).on('click', '.remove-holiday', function(e) {
            GroupSettings.deleteGroupHoliday(e.currentTarget);
        });
        $('#submit-group-exceptions').on('click', function(e) {
            GroupSettings.submitGroupExceptions();
        });
        $('#submit-group-holidays').on('click', function(e) {
            GroupSettings.submitGroupHolidays();
        });
        $('.save-default-total-free-hours').on('click', function(e) {
            GroupSettings.saveDefaultTotalFreeHours();
        });

        GroupSettings.init();

    });

})(this, this.document, this.jQuery);
