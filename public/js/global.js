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
    window.Text = Text;

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
    window.Popup = Popup;

    $(function() {

        $('#container').on('init', function(e) {
            $('#logout-link').tooltip({placement: 'bottom'});
            $('.no-action')
                .on('click', document.body, function(e) {e.stopPropagation();})
                .on('submit', document.body, function(e) {e.stopPropagation();})
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

    });

})(this, this.document, this.jQuery);
