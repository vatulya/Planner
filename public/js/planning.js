(function (window, document, $) {

    var modal = $('.modal-edit-day'),
        modalEditH3 = modal.find('.header-edit-day'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalBody = modal.find('.modal-body');

    var DaySettings = {

        editDay: function(el) {
            el = $(el);
            modalEditH3.show();
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    day: el.data('day-id')
                },
                success: function(response) {
                    modalBody.html(response);
                    DaySettings.initEditAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
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
            $('.timepicker-start').timepicker({
                minuteStep: 10,
                template: 'modal',
                showSeconds: false,
                showMeridian: false,
                defaultTime: 'value'
            });
            $('.timepicker-end').timepicker({
                minuteStep: 10,
                template: 'modal',
                showSeconds: false,
                showMeridian: false,
                defaultTime: 'value'
            });
            $('.timepicker-exclude').timepicker({
                minuteStep: 60,
                template: 'modal',
                showSeconds: false,
                showMeridian: false,
                defaultTime: 'value'
            });
        },

        changeSelectedColor: function(el) {
            el = $(el);
            $('.day-status-color').removeClass('active');
            el.addClass('active');
            var color = $('#form-edit-day').find('#color');
            if (color) {
                color.val(el.data('color'));
            }
        }
    };

    $(function() {
        $(document.body).on('click', '.edit-day', function(e) {
            DaySettings.editDay(e.currentTarget);
        });
        $(document.body).on('click', '.day-status-color', function(e) {
            DaySettings.changeSelectedColor(e.currentTarget);
        });
    });

})(this, this.document, this.jQuery);

