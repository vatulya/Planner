(function (window, document, $) {

    var OpenRequests = {

        modal: null,

        init: function() {
            var modal = $('#edit-request-details-modal');
            if (modal.length) {
                OpenRequests.modal = modal;
            }
        },

        getRequestData: function(el) {
            el = $(el);
            var data = {};
            data.id           = $.trim(el.data('id'));
            data.user_id      = $.trim(el.data('user-id'));
            data.request_id   = $.trim(el.data('request-id'));
            data.request_date = $.trim(el.data('request-date'));
            data.status       = $.trim(el.data('status'));
            data.comment      = $.trim(el.data('comment'));
            return data;
        },

        setRequestData: function(el, data) {
            el = $(el);
            OpenRequests.setElData(el, 'id', data.id);
            OpenRequests.setElData(el, 'user-id', data.user_id);
            OpenRequests.setElData(el, 'request-id', data.request_id);
            OpenRequests.setElData(el, 'request-date', data.request_date);
            OpenRequests.setElData(el, 'status', data.status);
            OpenRequests.setElData(el, 'comment', data.comment);
        },
        setElData: function(el, key, value) {
            el.data(key, value);
            el.attr('data-' + key, value);
        },

        openRequestDetailsHideAlert: function() {
            var alert = OpenRequests.modal.find('.alert');
            if (alert.length) {
                alert.hide();
            }
        },

        openRequestDetailsShowAlert: function(type, message) {
            var alert = OpenRequests.modal.find('.alert');
            if (alert.length) {
                alert.removeClass('alert-success').removeClass('.alert-error');
                alert.addClass('alert-' + type);
                alert.find('.alert-message').html(message);
                alert.show();
            }
        },

        openRequestDetails: function(el) {
            el = $(el);
            if ( ! OpenRequests.modal) {return;}
            var data = OpenRequests.getRequestData(el);
            var container = OpenRequests.modal.find('.modal-body');
            OpenRequests.modal.find('.modal-header h3').html('Request for date: ' + data.request_date);
            OpenRequests.setRequestData(container, data);
            OpenRequests.modal.find('.request-details-status').html(data.status.toUpperCase());
            OpenRequests.modal.find('.request-details-comment').html(data.comment);
            OpenRequests.openRequestDetailsHideAlert();
            OpenRequests.modal.modal('show');
        },

        openRequestExtremely: function(el) {
            el = $(el);
            var modal = $('#additional-request-modal');
            modalBody = modal.find('.modal-body')
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                //url: el.attr('href'),
                url: 'open-requests/get-user-requests/',
                data: {
                    user_id: el.data('user-id')
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

        setStatus: function(el) {
            el = $(el);
            var status = el.data('status');
            if ( ! status) {return;}
            var comment = OpenRequests.modal.find('.request-details-textarea').val();
            comment = $.trim(comment);
            if (status == 'reject' && ! comment.length) {
                OpenRequests.openRequestDetailsShowAlert('error', 'Comment is required for status Reject.');
                return;
            }
            var data = OpenRequests.modal.find('.modal-body');
            data = OpenRequests.getRequestData(data);
            data.status = status;
            data.comment = comment;
            data.format = 'json';
            $.ajax({
                url: '/open-requests/set-status/',
                data: data,
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        OpenRequests.openRequestDetailsShowAlert('success', 'Saved');
                        window.location.reload();
                    } else {
                        OpenRequests.openRequestDetailsShowAlert('error', response.message);
                    }
                },
                error: function(response) {
                    OpenRequests.openRequestDetailsShowAlert('error', 'Error! Something wrong. AJAX error. Please try later.');
                }
            });
        },

        setStatusExtremely: function(el) {
            el = $(el);
            if (el.data('new-date')) {
                var requestDate = $('#new-extremely-request-date').attr('value');
            } else {
                var requestDate = el.data('request-date');
            }
            var modal = $('#additional-request-modal');
            modalBody = modal.find('.modal-body')
            $.ajax({
                url: '/open-requests/set-status/',
                data: {
                    status: el.data('status'),
                    format: 'json',
                    type: 'extremely_approve',
                    id: el.data('id'),
                    user_id: el.data('user-id'),
                    request_date: requestDate
                },
                success: function(response) {
                    response = response.response;
                    if (response.status) {
                        modalBody.html('Success, Saved');
                        window.location.reload();
                    } else {
                        modalBody.html('Error! Something wrong. AJAX error. Please try later.');
                    }
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong. AJAX error. Please try later.');
                }
            });

        }

    };

    $(function() {

        OpenRequests.init();

        $(document.body).on('click', '.open-request-date', function(e) {
            OpenRequests.openRequestDetails(e.currentTarget);
        });
        $(document.body).on('click', '.open-request-extremely', function(e) {
            OpenRequests.openRequestExtremely(e.currentTarget);
        });
        $(document.body).on('click', '.change-open-request-status', function(e) {
            OpenRequests.setStatus(e.currentTarget);
        });
        $(document.body).on('click', '.extremely-approve-request', function(e) {
            OpenRequests.setStatusExtremely(e.currentTarget);
        });

    });

})(this, this.document, this.jQuery);
