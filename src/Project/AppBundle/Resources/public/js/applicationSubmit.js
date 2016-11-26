$(document).ready(function () {
    $('.js-datepicker').datepicker({
        dateFormat: 'MM yy',
        minDate: 0
    });
    if($('#download').data('created') != 0) {
        $('#applicationSubmit_save').hide();
        $('#download').removeClass('hidden');
        $('#download').attr("href", $('#download').attr("href") + '/' + $('#download').data('created'));
    }

    $('#applicationSubmit_save').click(function () {
        var form = $('form[name="applicationSubmit"]');

        $.ajax({
            dataType: 'json',
            url: Routing.generate('applications-pdf-ajax'),
            type: "POST",
            data: form.serialize(),

            beforeSend: function() {
                $.notify({
                    icon: 'pe-7s-gift',
                    message: "Form generation pending!"

                },{
                    type: 'info',
                    timer: 100
                });
            },
            success: function (data) {
                if (data.error === true) {
                    $.notify({
                        icon: 'pe-7s-gift',
                        message: Translator.trans('error') + ': ' + data.message

                    },{
                        type: 'error',
                        timer: 100
                    });
                } else {
                    //success message modal
                    $.notify({
                        icon: 'pe-7s-gift',
                        message: "Successfully generated form!"

                    },{
                        type: 'info',
                        timer: 100
                    });
                    $('#applicationSubmit_save').hide();
                    $('#download').removeClass('hidden');
                    $('#download').attr("href", $('#download').attr("href") + '/' + data.filename);
                }
            },
            error: function(data) {
                $.notify({
                    icon: 'pe-7s-gift',
                    message: Translator.trans('error') + ': ' + Translator.trans('error_request')

                },{
                    type: 'info',
                    timer: 100
                });
            }
        });
    });
});
