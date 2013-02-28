(function (window, document, $) {

    var UserSettings = {

        popup: null,

        init: function() {
            var popup = $('#edit-form-modal');
            if (popup.length) {
                UserSettings.popup = popup;
            }
            UserSettings.initCreateUserForm();
        },

        editForm: function(el) {
            el = $(el);
            var html = UserSettings.getEditFormPopupHtml(el);
            UserSettings.popup.find('.modal-header h3').html(el.data('field-title'));
            UserSettings.popup.find('.modal-body').html(html);
            UserSettings.popup.modal('show');
            var focus = UserSettings.popup.find('.for-focus');
            if (focus) { focus.focus().val(focus.val()); };
        },

        getEditFormPopupHtml: function(el) {
            var html = '';
            var type = '';
            if (el.hasClass('editable-groups')) {
                html = UserSettings.getEditableGroupHtml(el);
                type = 'groups';
            }
            var container = html.find('.popup-container');
            Form.setDataEl(container, 'user', el.parents('tr').data('user'));
            Form.setDataEl(container, 'type', type);
            UserSettings.checkGroupAdminCheckbox(html);
            html = html.html();
            return html;
        },

        getEditableGroupHtml: function(el) {
            var html = $('#popup-edit-form-groups-html');
            html.find('.group-checkbox').removeAttr('checked');
            var groups = el.data('field-value');
            groups = groups.split(';'); // 1:0;2:1;3:0; means id=1 : isAdmin=0 ; id=2 : isAdmin=1 ; id=3 : isAdmin=0
            for (i in groups) {
                var group = groups[i];
                group = group.split(':'); // ID:ADMIN
                html.find('.group-id-' + group[0]).attr('checked', 'checked');
                if (group[1] * 1 > 0) {
                    html.find('.group-admin-' + group[0]).attr('checked', 'checked');
                }
            };
            return html;
        },

        saveForm: function(el) {
            var container = UserSettings.popup.find('.popup-container');
            var data = {};
            if (container.data('type') == 'groups') {
                data = UserSettings.getEditFormPopupData(container);
            }
            UserSettings.lockPopup();
            data.user = container.data('user');
            data.format = 'json';
            $.ajax({
                url: '/user-settings/save-user-groups',
                data: data,
                success: function(response) {
                    UserSettings.unlockPopup();
                    response = response.response;
                    if (response.status) {
                        window.location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(response) {
                    UserSettings.unlockPopup();
                    alert('Error! Something wrong. AJAX error. Please try later.');
                }
            });
        },

        lockPopup: function() {
            UserSettings.popup.find('input, button, select, textarea').attr('disabled', 'disabled').addClass('locked');
        },

        unlockPopup: function() {
            UserSettings.popup.find('.locked').removeAttr('disabled').removeClass('.locked');
        },

        getEditFormPopupData: function(container) {
            var data = {};
            var checked = [];
            var groups = container.find('.group-id');
            groups.each(function(i, el) {
                el = $(el);
                if (el.attr('checked')) {
                    var id = el.data('group-id');
                    var admin = container.find('.group-admin-' + id);
                    if (admin.length > 0 && admin.attr('checked')) {
                        admin = '1';
                    } else {
                        admin = '0';
                    }
                    checked.push(id + ':' + admin);
                }
            });
            data.groups = checked;
            return data;
        },

        editField: function(el) {
            el = $(el);
            Popup.closePopovers();
            if (el.hasClass('popover-showed')) {
                $('.popover-showed').removeClass('popover-showed');
                return;
            } else {
                $('.popover-showed').removeClass('popover-showed');
                el.addClass('popover-showed');
            }
            var placement = el.data('placement');
            if (typeof placement == "undefined" || ! placement.length) {
                placement = 'bottom';
            }
            var html = UserSettings.getEditFieldPopoverHtml(el);
            var title = 'Edit field "' + el.data('field-title') + '"';
            el.popover({
                html: true,
                placement: placement,
                trigger: 'manual',
                title: title,
                content: html
            });
            el.popover('show');
            var focus = $('.popover .popover-content .for-focus');
            if (focus) { focus.focus().val(focus.val()); };
        },

        getEditableBirthdayHtml: function(el) {
            var html = $('#popover-edit-field-birthday-html');
            var date = el.data('field-value');
            date = date.split('-');
            html.find('.input-edit-field.input-date-day').attr('value', date[2]);
            html.find('.input-edit-field.input-date-month').attr('value', date[1]);
            html.find('.input-edit-field.input-date-year').attr('value', date[0]);
            return html;
        },

        getEditablePasswordHtml: function(el) {
            var html = $('#popover-edit-field-password-html');
            return html;
        },

        getEditFieldPopoverHtml: function(el) {
            var html = '';
            var type = '';
            if (el.hasClass('editable-birthday')) {
                html = UserSettings.getEditableBirthdayHtml(el);
                type = 'birthday';
            } else if (el.hasClass('editable-password')) {
                html = UserSettings.getEditablePasswordHtml(el);
                type = 'password';
            } else {
                html = $('#popover-edit-field-html');
                html.find('.input-edit-field')
                    .val(el.data('field-value'))
                    .attr('value', el.data('field-value'))
                    .attr('name', el.data('field-name'));
            }
            var container = html.find('.popover-container');
            Form.setDataEl(container, 'user', el.parents('tr').data('user'));
            Form.setDataEl(container, 'type', type);

            if (el.hasClass('edit-full-name')) {
                html.find('.additional-menu').show();
            } else {
                html.find('.additional-menu').hide();
            }

            html = html.html();
            return html;
        },

        getEditFieldPopoverData: function(el) {
            var data = {};
            var container = el.parents('.popover-container');
            if (container) {
                var type = container.data('type');
                if (type == 'birthday') {
                    var birth_day   = container.find('.input-edit-field.input-date-day').val();
                    var birth_month = container.find('.input-edit-field.input-date-month').val();
                    var birth_year  = container.find('.input-edit-field.input-date-year').val();
                    data.field = type;
                    data.value = birth_year + '-' + birth_month + '-' + birth_day;
                } else if (type == 'password') {
                    var new_password   = container.find('.input-edit-field.new_password').val();
                    var new_password_repeat = container.find('.input-edit-field.new_password_repeat').val();
                    data.field = type;
                    data.value = [new_password, new_password_repeat];
                } else {
                    var field = container.find('.input-edit-field');
                    var key = field.attr('name');
                    data.field = key;
                    data.value = field.val();
                }
            }
            data.user = container.data('user');
            return data;
        },

        saveField: function(el) {
            el = $(el);
            var data = UserSettings.getEditFieldPopoverData(el);
            UserSettings.lockEditFieldForm(el);
            data.format = 'json';
            $.ajax({
                url: '/user-settings/save-user-field',
                data: data,
                success: function(response) {
                    UserSettings.unlockEditFieldForm(el);
                    response = response.response;
                    if (response.status) {
                        window.location.reload();
                    } else {
                        var errors = '';
                        if (response.data.length) {
                            for (i in response.data) {
                                errors += response.data[i] + ' ';
                            }
                            alert('Error! ' + errors);
                        } else {
                            alert(response.message);
                        }
                    }
                },
                error: function(response) {
                    UserSettings.unlockEditFieldForm(el);
                    alert('Error! Something wrong. AJAX error. Please try later.');
                }
            });
        },

        lockEditFieldForm: function(el) {
            var container = el.parents('.popover-container');
            if (container) {
                var field = container.find('.input-edit-field');
                var button = container.find('.submit-popover-edit-field');
                field.attr('disabled', 'disabled');
                button.attr('disabled', 'disabled');
            }
        },

        unlockEditFieldForm: function(el) {
            var container = el.parents('.popover-container');
            if (container) {
                var field = container.find('.input-edit-field');
                var button = container.find('.submit-popover-edit-field');
                field.removeAttr('disabled');
                button.removeAttr('disabled');
            }
        },

        checkGroupAdminCheckbox: function(container) {
            container.find('.group-checkbox.group-id').each(function(i, el) {
                el = $(el);
                var id = el.data('group-id');
                var adminCheckbox = container.find('.group-checkbox.group-admin-' + id);
                if (adminCheckbox) {
                    if (el.attr('checked')) {
                        adminCheckbox.removeAttr('disabled');
                    } else {
                        adminCheckbox.attr('disabled', 'disabled');
                    }
                }
            });
        },

        showCreateUserForm: function() {
            var modal = $('#create-user-form-modal');
            if (modal.length > 0 ) {
                modal.modal('show');
            }
        },

        initCreateUserForm: function() {
            var formEl = $('#submit-create-user-form');
            formEl.ajaxForm({
                data: {format: 'json'},
                beforeSubmit: function() {
                    window.Form.hideAllNotifications(formEl);
                    window.Form.blockForm(formEl);
                },
                success: function(response) {
                    window.Form.unblockForm(formEl);
                    response = response.response;
                    if (response.status) {
                        window.location.reload();
//                        window.Form.showSuccess(formEl, 2000);
                    } else {
                        window.Form.showErrors(formEl, response.data);
                    }
                },
                error: function() {
                    window.Form.unblockForm(formEl);
                    window.Form.showErrors(formEl);
                }
            });
        },

        setAdmin: function(el) {
            el = $(el);
            var container = el.parents('.popover-container');
            if (container.length > 0) {
                if ( ! confirm('Are you sure?')) {
                    return;
                }
                var data = {
                    user: container.data('user'),
                    format: 'json'
                };
                $.ajax({
                    url: '/user-settings/set-admin',
                    data: data,
                    beforeSubmit: function() {
                        el.attr('disabled', 'disabled');
                    },
                    success: function(response) {
                        el.removeAttr('disabled');
                        response = response.response;
                        if (response.status) {
                            if (response.data.isAdmin) {
                                el.addClass('active');
                            } else {
                                el.removeClass('active');
                            }
                        }
                    },
                    error: function() {
                        el.removeAttr('disabled');
                    }
                });
            }
        },

        deleteUser: function(el) {
            el = $(el);
            var container = el.parents('.popover-container');
            if (container.length > 0) {
                if ( ! confirm('Delete user. Are you sure?')) {
                    return;
                }
                var data = {
                    user: container.data('user'),
                    format: 'json'
                };
                $.ajax({
                    url: '/user-settings/delete-user',
                    data: data,
                    beforeSubmit: function() {
                        el.attr('disabled', 'disabled');
                    },
                    success: function(response) {
                        el.removeAttr('disabled');
                        response = response.response;
                        if (response.status) {
                            window.location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        el.removeAttr('disabled');
                        alert('Error! Something wrong. AJAX error. Please try later.');
                    }
                });
            }
        }

    };

    $(function() {

        UserSettings.init();
        $(document.body).on('click', '.editable', function(e) {
            UserSettings.editField(e.currentTarget);
        });
        $(document.body).on('click', '.editable-popup', function(e) {
            UserSettings.editForm(e.currentTarget);
        });
        $(document.body).on('click', '.submit-popover-edit-field', function(e) {
            UserSettings.saveField(e.currentTarget);
        });
        $(document.body).on('click', '.submit-edit-form', function(e) {
            UserSettings.saveForm(e.currentTarget);
        });
        $(document.body).on('click', '.group-checkbox.group-id', function(e) {
            UserSettings.checkGroupAdminCheckbox(UserSettings.popup);
        });
        $(document.body).on('click', '#create-user', function(e) {
            UserSettings.showCreateUserForm();
        });
        $(document.body).on('click', '.set-admin', function(e) {
            UserSettings.setAdmin(e.currentTarget);
        });
        $(document.body).on('click', '.delete-user', function(e) {
            UserSettings.deleteUser(e.currentTarget);
        });
        $(document.body).on('mouseover', '.change-role-icon', function(e) {
            $(e.currentTarget).parents('.show-tooltip').tooltip('hide');
        });
        $(document.body).on('mouseout', '.change-role-icon', function(e) {
            var p = $(e.currentTarget).parent('.show-tooltip');
            if (p.is(':hover')) {
                p.tooltip('show');
            }
        });

    });

})(this, this.document, this.jQuery);
