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

        selectSecondStatus: function(el) {
            return true;
            el = $(el);
            var oldStatus = $('#select-second-status-button').html();
            $('#select-second-status-button').html(el.data('toggle'));
            el.data('toggle', oldStatus)  ;
        },

        initEditAjaxForm: function() {
            var formEl = $('#form-edit-day');
            $('#form-edit-day').ajaxForm({
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
            $('.timepicker-start2').timepicker({
                minuteStep: 10,
                template: 'modal',
                showSeconds: false,
                showMeridian: false,
                defaultTime: 'value'
            });
            $('.timepicker-end2').timepicker({
                minuteStep: 10,
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
               // $('#status1').value = el.data('color');
            //    alert($('#status1').val + el.data('color'));
            }
        }
    };

    $(function() {
        $(document.body).on('click', '.edit-day', function(e) {
            DaySettings.editDay(e.currentTarget);
        });
        $(document.body).on('click', '#select-second-status-button', function(e) {
            DaySettings.selectSecondStatus(e.currentTarget);
        });
        $(document.body).on('click', '.day-status-color', function(e) {
            DaySettings.changeSelectedColor(e.currentTarget);
        });
    });

})(this, this.document, this.jQuery);

