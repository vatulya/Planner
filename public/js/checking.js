(function (window, document, $) {

    var Checking = {

        checkUser: function(user) {
            var userEl = $('.user-row.user-id-' + user.id);
            if (userEl.length > 0) {
                if (user.check_in == undefined) {user.check_in = '';}
                if (user.check_out == undefined) {user.check_out = '';}
                if (user.check_out != '') {
                    userEl.removeClass('checked-in');
                    userEl.addClass('checked-out');
                    userEl.addClass('success');
                } else if (user.check_in != '') {
                    userEl.removeClass('checked-out');
                    userEl.addClass('checked-in');
                    userEl.addClass('success');
                } else {
                    userEl.removeClass('checked-in');
                    userEl.removeClass('checked-out');
                    userEl.removeClass('success');
                }
                userEl.find('.user-check-in').html(user.check_in);
                userEl.find('.user-check-out').html(user.check_out);
            }
        },

        userCheck: function(el, check) {
            el = $(el);
            $.ajax({
                url: '/checking/user-check',
                data: {
                    format: 'json',
                    user: el.data('user-id'),
                    check: check
                },
                success: function(data) {
                    data = data.response;
                    if (data.status) {
                        Checking.checkUser(data.data);
                    } else {
                        window.showError();
                    }
                }
            });
        }

    };

    $(function() {

        $(document.body).on('click', '.btn-user-check-in', function(e) {
            Checking.userCheck(e.currentTarget, 'in');
        });
        $(document.body).on('click', '.btn-user-check-out', function(e) {
            Checking.userCheck(e.currentTarget, 'out');
        });

    });

})(this, this.document, this.jQuery);
