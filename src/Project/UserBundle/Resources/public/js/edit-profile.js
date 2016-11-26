$(document).ready(function () {
    $('#fos_user_change_password_form_save').click(function () {
        var form = $('form[name="fos_user_change_password_form"]');

        $.ajax({
            url: Routing.generate('user-change-password', {'_locale': Translator.locale}),
            type: 'POST',
            data: form.serialize(),
            beforeSend: function() {
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
                            message: Translator.trans('error') + ': ' + Translator.trans('error_request')
                        }, {
                            type: 'danger',
                            timer: 100
                        });
                    }
                } else {
                    $.notify({
                        icon: 'fa fa-gift',
                        message: Translator.trans('ajax.change_password')

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

    $('#changeUser_save').click(function () {
        var form = $('form[name="changeUser"]');

        $.ajax({
            url: Routing.generate('user-update-profile', {'_locale': Translator.locale}),
            type: 'POST',
            data: form.serialize(),
            beforeSend: function() {
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
                        message: Translator.trans('ajax.edit_profile')

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