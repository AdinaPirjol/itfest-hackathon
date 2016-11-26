$(document).ready(function () {
    $('#projectFilterList_save').click(function () {
        var form = $('form[name="projectFilterList"]');

        $.ajax({
            url: Routing.generate('filter-projects-ajax', {'_locale': Translator.locale}),
            type: 'POST',
            data: form.serialize(),
            beforeSend: function() {
                //show loading modal
            },
            success: function (data) {
                if (data.error === true) {
                    $.notify({
                        icon: 'fa fa-gift',
                        message: Translator.trans('error') + ': ' + Translator.trans('error_request')

                    }, {
                        type: 'danger',
                        timer: 100
                    });
                } else {
                    $("div#projects-list").html(data.projects);
                }
            },
            error: function() {
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

    $('#projectFilterList_reset').on('click', function () {
        $('#projectFilterList_professor').val('').trigger('change');
        $('#projectFilterList_tag').val('').trigger('change');
        $('#projectFilterList_save').click();
    });

    $('.action-view').click(function() {
       window.location.href = $(this).data('href');
    });

    $('.action-apply').click(function() {
        var selector = $(this);

        $.ajax({
            url: Routing.generate('project-apply', {'_locale': Translator.locale, 'id': selector.data('project-id')}),
            type: 'POST',
            beforeSend: function() {
                //show loading modal
            },
            success: function (data) {
                if (data.error === true) {
                    $.notify({
                        icon: 'fa fa-exclamation-circle',
                        message: Translator.trans('error') + ': ' + data.message

                    }, {
                        type: 'danger',
                        timer: 100
                    });
                } else {
                    $.notify({
                        icon: 'fa fa-gift',
                        message: data.message

                    }, {
                        type: 'info',
                        timer: 100
                    });
                }
            },
            error: function() {
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