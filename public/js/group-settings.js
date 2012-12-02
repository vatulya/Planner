(function (window, document, $) {

    var modal = $('.modal-edit-group'),
        modalCreateH3 = modal.find('.header-create-group'),
        modalEditH3 = modal.find('.header-edit-group'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalDeleteGroup = modal.find('.button-delete-group'),
        modalBody = modal.find('.modal-body')
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
                            alert('Error! Something wrong.');
                        }
                    },
                    error: function(response) {
                        alert('Error! Something wrong.');
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
                        document.Form.showSuccess(formEl);
                        window.location.reload();
                    } else {
                        document.Form.showErrors(formEl);
                    }
                },
                error: function() {
                    document.Form.showErrors(formEl);
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
        },

        selectGroupPlanning: function() {
            groupPlanningSelect.css('background-color', '#FFFFFF');
            groupPlanningBody.html('');
            var option = $('option:selected', groupPlanningSelect);
            if (option.val() < 1) {
                return false;
            }
            groupPlanningSelect.css('background-color', '#' + option.data('group-color'));
            groupPlanningBody.html('Loading...');
            $.ajax({
                url: '/group-settings/get-group-planning',
                data: {
                    group: option.val()
                },
                success: function(response) {
                    groupPlanningBody.html(response);
                    groupPlanningSelect.blur();
//                    GroupSettings.initGroupPlanningForm(); ??? i don't know what is it :)
                },
                error: function(response) {
                    groupPlanningBody.html('<span class="alert alert-error">Error! Something wrong.</span>');
                }
            });
        },

        saveGroupPlanning: function() {
//            GroupSettings.blockFormGroupPlanning();
            var option = $('option:selected', groupPlanningSelect);
            if (option.val() < 1) {
                return false;
            }
            var data = {
                format: 'json',
                group: option.val(),
                group_planning: GroupSettings.getGroupPlanning(),
                group_pause: GroupSettings.getGroupPause()
            };
            $.ajax({
                url: '/group-settings/save-group-planning',
                data: data,
                success: function(response) {
//                    GroupSettings.unblockFormGroupPlanning();
                    response = response.response;
                    if (response.status) {
                        GroupSettings.showPlanningAlert('success', 'Success');
                        GroupSettings.selectGroupPlanning();
                    } else {
                        GroupSettings.showPlanningAlert('error', 'Error!');
                    }
                },
                error: function(response) {
//                    GroupSettings.unblockFormGroupPlanning();
                    GroupSettings.showPlanningAlert('error', 'Error!');
                }
            });
        },

        showPlanningAlert: function(status, message) {
            var alert = groupPlanningBody.find('.alert');
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
                        hour: el.find('.start-hour').val(),
                        min: el.find('.start-min').val()
                    },
                    time_end: {
                        hour: el.find('.end-hour').val(),
                        min: el.find('.end-min').val()
                    }
                }
                data[day.week_type + '-' + day.day_number] = day;
            });
            return data;
        },

        getGroupPause: function() {
            var data = {};
            var container = $('.pause-time');
            if (container.length) {
                data = {
                    pause_start: {
                        hour: container.find('.start-hour').val(),
                        min: container.find('.start-min').val()
                    },
                    pause_end: {
                        hour: container.find('.end-hour').val(),
                        min: container.find('.end-min').val()
                    }
                };
            }
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
                        alert('Error! Something wrong.');
                    }
                },
                error: function(response) {
                    alert('Error! Something wrong.');
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
                        alert('Error! Something wrong.');
                    }
                },
                error: function(response) {
                    alert('Error! Something wrong.');
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

        $(document.body).on('click', '.edit-group', function(e) {
            GroupSettings.editGroup(e.currentTarget);
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
        $(document.body).on('click', '.group-planning-save', function(e) {
            GroupSettings.saveGroupPlanning();
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

        GroupSettings.init();

    });

})(this, this.document, this.jQuery);
