(function (window, document, $) {

    var modal = $('.modal-edit-day'),
        modalEditH3 = modal.find('.header-edit-day'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalBody = modal.find('.modal-body');

    var DaySettings = {

        editDay: function(el) {
            el = $(el);
            if (el.data('status') != 3) {  //no popup for yellow status
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
            }
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

        applyTimeFullDay: function(el) {
            el = $(el);
            $('#time_start2').attr('value',  $('#time_start').attr('value'));
            $('#time_end2').attr('value',  $('#time_end').attr('value'));
        }
    };

    $(function() {
        $(document.body).on('click', '.edit-day', function(e) {
            DaySettings.editDay(e.currentTarget);
        });
        $(document.body).on('click', '#apply-time-full-day', function(e) {
            DaySettings.applyTimeFullDay(e.currentTarget);
        });
        $(document.body).on('click', '.day-status-color', function(e) {
            DaySettings.changeSelectedColor(e.currentTarget);
        });
    });

})(this, this.document, this.jQuery);

