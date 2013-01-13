(function (window, document, $) {
    var modal = $('.modal-year-total'),
        modalEditH3 = modal.find('.header--year-total'),
        modalBody = modal.find('.modal-body-year-total');

    var Overview = {

        init: function() {

        },
        getEditFieldPopoverHtml: function(el) {
            var html = '';
            var type = '';
                html = $('#popover-edit-field-html');
                html.find('.input-edit-field')
                    .val(el.data('field-value'))
                    .attr('value', el.data('field-value'))
                    .attr('name', el.data('field-name'));

            var container = html.find('.popover-container');
            Form.setDataEl(container, 'user', el.data('field-user'));
            Form.setDataEl(container, 'group', el.data('field-group'));
            Form.setDataEl(container, 'year', el.data('field-year'));
            Form.setDataEl(container, 'week', el.data('field-week'));

            html = html.html();
            return html;
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
            var html = Overview.getEditFieldPopoverHtml(el);
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

        saveField: function(el) {
            el = $(el);
            var data = Overview.getEditFieldPopoverData(el);
            Overview.lockEditFieldForm(el);
            data.format = 'json';
            $.ajax({
                url: '/overview/update-history-hour',
                data: data,
                success: function(response) {
                    Overview.unlockEditFieldForm(el);
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
                            alert('Error! Something wrong.');
                        }
                    }
                },
                error: function(response) {
                    Overview.unlockEditFieldForm(el);
                    alert('Error! Something wrong.');
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

        getEditFieldPopoverData: function(el) {
            var data = {};
            var container = el.parents('.popover-container');
            if (container) {

                    var field = container.find('.input-edit-field');
                    var key = field.attr('name');
                    data.field = key;
                    data.value = field.val();
            }
            data.user = container.data('user');
            data.group = container.data('group');
            data.year = container.data('year');
            data.week = container.data('week');
            return data;
        },

        showYearTotals: function(el) {
            el = $(el);
            modalEditH3.show();
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                data: {},
                success: function(response) {
                    modalBody.html(response);
                    Overview.initTotalAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
        },

        showAddEmailPopup: function(el) {
            el = $(el);
            var modal = $('#add-email-modal');
            modal.modal();
        },

        deleteEmail: function(el) {
            el = $(el);
            var text = el.data('email');
            text = 'Delete email "' + text + '" from automail list? Are you sure?';
            if (confirm(text)) {
                var data = {};
                data.email = el.data('email-id');
                data.format = 'json';
                $.ajax({
                    url: '/overview/delete-email',
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
        },

        submitAddEmail: function() {
            var modal, calendar, data;
            modal             = $('#add-email-modal');
            var data = {};
            data.new_email = modal.find('.new_email').val();
            data.format       = 'json';
            $.ajax({
                url: '/overview/add-new-email',
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

        initTotalAjaxForm: function() {
            var formEl = $('#form-edit-day');
            $('#form-edit-day').ajaxForm({
                data: {format: 'json'},
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        //document.Form.showSuccess(formEl);
                        window.location.reload();
                    } else {
                        document.Form.showErrors(formEl);
                    }
                },
                error: function() {
                    document.Form.showErrors(formEl);
                }
            });
        }

    };

    $(function() {

        Overview.init();
        $(document.body).on('click', '.year-total', function(e) {
            Overview.showYearTotals(e.currentTarget);
        });
        $(document.body).on('click', '.editable', function(e) {
            Overview.editField(e.currentTarget);
        });
        $(document.body).on('click', '.submit-popover-edit-field', function(e) {
            Overview.saveField(e.currentTarget);
        });
        $(document.body).on('click', '.add-email', function(e) {
            Overview.showAddEmailPopup(e.currentTarget);
        });
        $(document.body).on('click', '.remove-email', function(e) {
            Overview.deleteEmail(e.currentTarget);
        });
        $('#submit-add-email').on('click', function(e) {
            Overview.submitAddEmail();
        });
    });

})(this, this.document, this.jQuery);
