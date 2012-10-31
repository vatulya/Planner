(function (window, document, $) {

    function Calendar(el) {

        function getData(el) {
            var data = {};
            data.show_date = el.data('show-date');
            return data;
        }
        var data = getData(el);

        function loadCalendar(el, data) {
            data.format = 'html';
            $.ajax({
                url: '/calendar/',
                data: data,
                success: function(response) {
                    el.html(response);
                    el.trigger('calendar-loaded');
                },
                error: function(response) {
                    alert('Calendar is not loaded. Error.');
                }
            });
        }
        loadCalendar(el, data);

    };
    window.Calendar = Calendar;

    $(function() {

    });

})(this, this.document, this.jQuery);
