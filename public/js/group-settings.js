(function (window, document, $) {

    var modal = $('.modal-edit-group'),
        modalCreateH3 = modal.find('.header-create-group'),
        modalEditH3 = modal.find('.header-edit-group'),
        modalTmplGroupName = modal.find('.tmpl-group-name'),
        modalDeleteGroup = modal.find('.button-delete-group'),
        modalBody = modal.find('.modal-body');

    var groupPlanning = $('.group-work-days-planning'),
        groupPlanningSelect = $('#group-planning'),
        groupPlanningBody = groupPlanning.find('.planning-body');

    var GroupSettings = {

        editGroup: function(el) {
            el = $(el);
            modalCreateH3.hide();
            modalEditH3.show();
            modalDeleteGroup.show().data('group-id', (el.data('group-id'))).data('group-name', el.data('group-name'));
            modalTmplGroupName.html(el.data('group-name'));
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                data: {
                    group: el.data('group-id')
                },
                success: function(response) {
                    modalBody.html(response);
                    GroupSettings.initEditAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
        },

        createGroup: function(el) {
            el = $(el);
            modalCreateH3.show();
            modalEditH3.hide();
            modalDeleteGroup.hide();
            modalBody.html('Loading...');
            modal.modal();
            $.ajax({
                url: el.attr('href'),
                success: function(response) {
                    modalBody.html(response);
                    GroupSettings.initEditAjaxForm();
                },
                error: function(response) {
                    modalBody.html('Error! Something wrong.');
                }
            });
        },

        deleteGroup: function(el) {
            el = $(el);
            var groupName = el.data('group-name');
            var groupId = el.data('group-id');
            if (groupId && groupName && confirm('Delete group "' + groupName + '"? Are you sure?')) {
                $.ajax({
                    url: el.attr('href'),
                    data: {
                        format: 'json',
                        group: groupId
                    },
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
        },

        changeSelectedColor: function(el) {
            el = $(el);
            $('.group-color-variation').removeClass('active');
            el.addClass('active');
            var color = $('#form-edit-group').find('#color');
            if (color) {
                color.val(el.data('color'));
            }
        },

        selectGroupPlanning: function() {
            groupPlanningSelect.css('background-color', '#FFFFFF');
            groupPlanningBody.html('');
            var option = $('option:selected', groupPlanningSelect);
            if (option.val() < 1) {
                return false;
            }
            groupPlanningSelect.css('background-color', '#' + option.data('group-color'));
            groupPlanningBody.html('Loading...');
            $.ajax({
                url: '/group-settings/get-group-planning',
                data: {
                    group: option.val()
                },
                success: function(response) {
                    groupPlanningBody.html(response);
                    groupPlanningSelect.blur();
                    GroupSettings.initGroupPlanningForm();
                },
                error: function(response) {
                    groupPlanningBody.html('<span class="alert alert-error">Error! Something wrong.</span>');
                }
            });
        }

    };

    $(function() {

        $(document.body).on('click', '.edit-group', function(e) {
            GroupSettings.editGroup(e.currentTarget);
        });
        $(document.body).on('click', '.create-group', function(e) {
            GroupSettings.createGroup(e.currentTarget);
        });
        $(document.body).on('click', '.group-color-variation', function(e) {
            GroupSettings.changeSelectedColor(e.currentTarget);
        });
        $(document.body).on('click', '.button-delete-group', function(e) {
            GroupSettings.deleteGroup(e.currentTarget);
        });
        $(document.body).on('change', '#group-planning', function(e) {
            GroupSettings.selectGroupPlanning();
        });

    });

})(this, this.document, this.jQuery);
