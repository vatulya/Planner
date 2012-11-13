(function (window, document, $) {

    var modal = $('.modal-edit-group'),
        modalCreateH3 = modal.find('.header-create-group'),
        modalEditH3 = modal.find('.header-edit-group'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalDeleteGroup = modal.find('.button-delete-group'),
        modalBody = modal.find('.modal-body');

    var groupPlanning = $('.group-work-days-planning'),
        groupPlanningSelect = $('#group-planning'),
        groupPlanningBody = groupPlanning.find('.planning-body');

    var GroupSettings = {

        init: function() {
            GroupSettings.initMiniAjaxForms();
        },

        initMiniAjaxForms: function() {
            $('.mini-ajax-form').each(function(i, el) {
                var formEl = $(el);
                formEl.ajaxForm({
                    data: {format: 'json'},
                    beforeSubmit: function() {
                        document.Form.hideAllNotificationsMini(formEl);
                        document.Form.blockForm(formEl);
                    },
                    success: function(response) {
                        document.Form.unblockForm(formEl);
                        response = response.response;
                        if (response.status) {
                            document.Form.showSuccessMini(formEl, 2000);
                        } else {
                            document.Form.showErrorsMini(formEl);
                        }
                    },
                    error: function() {
                        document.Form.unblockForm(formEl);
                        document.Form.showErrorsMini(formEl);
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

        GroupSettings.init();

    });

})(this, this.document, this.jQuery);
