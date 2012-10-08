(function (window, document, $) {

    var modal = $('.modal-edit-group'),
        modalCreateH3 = modal.find('.create-group'),
        modalEditH3 = modal.find('.edit-group'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalBody = modal.find('.modal-body');

    var GroupSettings = {

        editGroup: function(el) {
            el = $(el);
            modalCreateH3.hide();
            modalEditH3.show();
            modalTmplGroupName.html(el.html());
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    group: el.data('group-id')
                },
                success: function(response) {
                    modalBody.html(response);
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
        }

    };

    $(function() {

        $(document.body).on('click', '.edit-group', function(e) {
            GroupSettings.editGroup(e.currentTarget);
        });
        $('.edit-group-form').ajaxForm({
            data: {format: 'json'},
            success: function(response) {
                if (response.status) {
                    window.location.reload();
                } else {
                    // TODO: show error
                }
            },
            error: function() {
                // TODO: show error
            }
        });

    });

})(this, this.document, this.jQuery);
