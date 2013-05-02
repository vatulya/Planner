(function (window, document, $) {

    var modal = $('.modal-user-alerts'),
        modalBody = modal.find('.modal-body');

    var Alerts = {

        init: function() {
        },

        userAlerts: function(el) {
            el = $(el);
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    day: el.data('day-id')
                },
                success: function(response) {
                    modalBody.html(response);
                    //DaySettings.initEditAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
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
                    Alerts.initTotalAjaxForm();
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
                    url: '/alerts/delete-email',
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
                url: '/alerts/add-new-email',
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
        Alerts.init();
        $(document.body).on('click', '.user-alerts', function(e) {
            Alerts.userAlerts(e.currentTarget);
        });
        $(document.body).on('click', '.add-email', function(e) {
            Alerts.showAddEmailPopup(e.currentTarget);
        });
        $(document.body).on('click', '.remove-email', function(e) {
            Alerts.deleteEmail(e.currentTarget);
        });
        $('#submit-add-email').on('click', function(e) {
            Alerts.submitAddEmail();
        });

    });

})(this, this.document, this.jQuery);
