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
            var data;
            data.id           = $.trim(el.data('id'));
            data.user_id      = $.trim(el.data('user_id'));
            data.request_id   = $.trim(el.data('request_id'));
            data.request_date = $.trim(el.data('request_date'));
            data.status       = $.trim(el.data('status'));
            data.comment      = $.trim(el.data('comment'));
            return data;
        },

        openRequestDetails: function(el) {
            el = $(el);
            if ( ! OpenRequests.modal) {return;}
            var data = OpenRequests.getRequestData(el);
            OpenRequests.modal.find('.modal-header h3').html('Request for date: ' + data.request_date);
            OpenRequests.modal.find('#request_date').val(data.request_date);
            OpenRequests.modal.find('#user_id').val(data.user_id);
            OpenRequests.modal.find('#request_id').val(data.request_id);
            OpenRequests.modal.find('#comment').val(data.comment);
            OpenRequests.modal.find('#id').val(data.id);
            OpenRequests.modal.modal('show');
        }

    };

    $(function() {

        OpenRequests.init();

        $(document.body).on('click', '.open-request-date', function(e) {
            OpenRequests.openRequestDetails(e.currentTarget);
        });

    });

})(this, this.document, this.jQuery);
