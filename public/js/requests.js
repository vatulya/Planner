(function (window, document, $) {

    var calendar;

    var Requests = {

        init: function() {
            calendar = $('#request-calendar');
            Calendar(calendar);
        }

    };

    $(function() {

        Requests.init();

    });

})(this, this.document, this.jQuery);
