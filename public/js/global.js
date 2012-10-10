(function (window, document, $) {

    var Text = {

        truncate: function(el, chars){
            if (chars == undefined || chars < 1) {
                chars = 55;
            }
            var html = el.html();
            var end  = '...';
            if (html.length > chars) {
                html = html.substr(0, (chars + end.length)) + end;
                el.html(html);
            }
        }

    };
    document.Text = Text;

    var Popup = {

        container: undefined,
        header: undefined,
        body: undefined,

        offsetWidth: 80,
        offsetHeight: 0,

        init: function(el, data) {
            if ( ! el) {
                el = '#mainPopup';
            }
            this.container = $(el);
            this.header    = this.container.find('.modal-header h3');
            this.body      = this.container.find('.modal-body-p');
            return this;
        },

        render: function (html, header) {
            var span = $('<span class="popup-container"></span>');
            span.html(html);
            this.body.html(span);
            this.header.html(header);
            return this;
        },

        modal: function(options) {
            this.container.modal(options);
            var popup = this;
            popup.container.css('width', '5000px');
//            popup.container.css('height', '5000px');
            var width = (popup.body.find('.popup-container').outerWidth() * 1) + (popup.offsetWidth * 1);
            var height = (popup.body.find('.popup-container').outerHeight() * 1) + (popup.offsetHeight * 1);
            popup.container.css('width', width + 'px');
            popup.body.css('margin-bottom', height + 'px');
            popup.container.css('margin-left', '-' + (width / 2) + 'px');
            $('#container').trigger('popup-init');
            return this;
        },

        closePopovers: function() {
            $('.popover').remove();
        }

    };
    document.Popup = Popup;

    var Form = {

        hideAllNotifications: function(formEl) {
            formEl.find('.control-group').removeClass('error');
            formEl.find('.control-group').removeClass('success');
            formEl.find('.help-inline').html('');
            formEl.find('.alert').hide();
        },

        showErrors: function(formEl, errorsHash) {
            Form.hideAllNotifications(formEl);

            // TODO: need finish show error messages logic
            if (errorsHash != undefined && errorsHash.length > 0) {
                for (var k in errorsHash) {
                    for (var kk in errorsHash[k]) {
                        var errorDomain = errorsHash[k][kk];
                        var inputEl = $('#' + k);
                        if (inputEl != null) {
                            var rowDiv = inputEl.parents('.control-group, .form-actions');
                            if (rowDiv != null) {
                                rowDiv.addClass('error');
                                var errorTag = rowDiv.find('.help-inline');
                                if ( errorTag != null) {
                                    errorTag.html(errorDomain);
                                }
                            }
                        }
                    }
                }
            } else {
                formEl.find('.alert-error').html('Error!').show();
            }
        },

        showSuccess: function(formEl) {
            Form.hideAllNotifications(formEl);

            formEl.find('.alert-success').html('Success!').show();
        }

    };
    document.Form = Form;

    $(function() {

        $('#container').on('init', function(e) {
            $('#logout-link').tooltip({placement: 'bottom'});
            $('.show-tooltip').tooltip({placement: 'bottom'});
            $('.no-action')
                .on('click', document.body, function(e) {e.preventDefault();})
                .on('submit', document.body, function(e) {e.preventDefault();})
            ;
            $('.truncate').each(function(i, el) {
                el = $(el);
                chars = el.data('truncate-to') * 1;
                if (chars <= 0) {
                    chars = 20;
                }
                Text.truncate(el, chars);
            });
            $('#container').trigger('post-init');
        });

        $('#container').on('popup-init', function(e) {
            $('.modal-backdrop').on('click', function(e) {
                Popup.closePopovers();
            });
            $('#container').trigger('post-popup-init');
        });

        $('#container').trigger('init');

        $(document.body).on('click', '.button-submit', function() {
            var target = $(this).data('form');
            var form = $('#' + target);
            if (target && form.length) {
                form.submit();
            }
        });

    });

})(this, this.document, this.jQuery);
