(function (window, document, $) {

    var UserSettings = {

        init: function() {
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
            el.popover({
                html: true,
                placement: placement,
                trigger: 'manual',
                title: 'Edit field "' + el.data('field-title') + '"',
                content: html
            });
            el.popover('show');
        },

        getEditFieldPopoverHtml: function(el) {
            var html = '';
            var type = '';
            if (el.hasClass('editable-birthday')) {
                html = $('#popover-edit-field-birthday-html');
                var date = el.data('field-value');
                date = date.split('-');
                html.find('.input-edit-field.input-date-day').attr('value', date[2]);
                html.find('.input-edit-field.input-date-month').attr('value', date[1]);
                html.find('.input-edit-field.input-date-year').attr('value', date[0]);
                type = 'birthday';
            } else {
                html = $('#popover-edit-field-html');
                html.find('.input-edit-field').val(el.data('field-value'));
                html.find('.input-edit-field').attr('value', el.data('field-value'));
                html.find('.input-edit-field').attr('name', el.data('field-name'));
            }
            var container = html.find('.popover-container');
            Form.setDataEl(container, 'user', el.parents('tr').data('user'));
            Form.setDataEl(container, 'type', type);
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
                        alert('Error! Something wrong.');
                    }
                },
                error: function(response) {
                    UserSettings.unlockEditFieldForm(el);
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
        }

    };

    $(function() {

        UserSettings.init();
        $(document.body).on('click', '.editable', function(e) {
            UserSettings.editField(e.currentTarget);
        });
        $(document.body).on('click', '.submit-popover-edit-field', function(e) {
            UserSettings.saveField(e.currentTarget);
        });

    });

})(this, this.document, this.jQuery);
