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
                template: 'false',
                showSeconds: false,
                showMeridian: false,
                defaultTime: 'value'
            });
            $('.timepicker-end2').timepicker({
                minuteStep: 10,
                template: 'false',
                showSeconds: false,
                showMeridian: false,
                defaultTime: 'value'
            });
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
        $(document.body).on('blur', '.set-time-hour-field', function(e) {
            DaySettings.checkHourInput(e.currentTarget);
        });
        $(document.body).on('blur', '.set-time-mins-field', function(e) {
            DaySettings.checkMinuteInput(e.currentTarget);
        });
        $(document.body).on('click', '.sendRequest', function(e) {
            DaySettings.saveRequest(e.currentTarget);
        });
    });

})(this, this.document, this.jQuery);

