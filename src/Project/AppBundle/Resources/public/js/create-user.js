$(document).ready(function () {
    $('#createUser_save').click(function () {
        var form = $('form[name="createUser"]');

        $.ajax({
            url: Routing.generate('admin-create-user-ajax', {'_locale': Translator.locale}),
            type: 'POST',
            data: form.serialize(),
            beforeSend: function () {
                //show loading modal
            },
            success: function (data) {
                if (data.error === true) {
                    if ($.isArray(data.message)) {
                        $.each(data.message, function (key, value) {
                            $.notify({
                                icon: 'fa fa-exclamation-circle',
                                message: Translator.trans('error') + ': ' + value
                            }, {
                                type: 'danger',
                                timer: 100
                            });
                        });
                    } else {
                        $.notify({
                            icon: 'fa fa-exclamation-circle',
                            message: Translator.trans('error') + ': ' + data.message

                        }, {
                            type: 'danger',
                            timer: 100
                        });
                    }
                } else {
                    $.notify({
                        icon: 'fa fa-gift',
                        message: Translator.trans('ajax.add_user')

                    }, {
                        type: 'info',
                        timer: 100
                    });
                }
            },
            error: function () {
                $.notify({
                    icon: 'fa fa-exclamation-circle',
                    message: Translator.trans('error') + ': ' + Translator.trans('error_request')

                }, {
                    type: 'danger',
                    timer: 100
                });
            }
        });
    });
});