$(document).ready(function () {
    $('#courseFilterList_save').click(function () {
        var form = $('form[name="courseFilterList"]');

        $.ajax({
            url: Routing.generate('filter-projects-ajax', {'_locale': Translator.locale}),
            type: 'POST',
            data: form.serialize(),
            beforeSend: function () {
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

    $('#courseFilterList_reset').on('click', function () {
        $('#courseFilterList_professor').val('').trigger('change');
        $('#courseFilterList_tag').val('').trigger('change');
        $('#courseFilterList_save').click();
    });

    $('.action-view').click(function () {
        window.location.href = $(this).data('href');
    });

    $('.action-apply').click(function () {
        var selector = $(this);
        var courseId = selector.data('project-id');
        var enabled = selector.data('enabled');

        $.ajax({
            url: Routing.generate('project-apply'),
            dataType: 'json',
            data: {
                'id': courseId,
                'enable': enabled
            },
            type: 'POST',
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



                    if (enabled == 1) {
                        $('#sub-' + courseId).show();
                        $('#unsub-' + courseId).hide();

                        $.notify({
                            icon: 'fa fa-gift',
                            message: 'Te-ai abonat cu succes!'

                        }, {
                            type: 'info',
                            timer: 100
                        });

                    } else {
                        $('#sub-' + courseId).hide();
                        $('#unsub-' + courseId).show();

                        $.notify({
                            icon: 'fa fa-gift',
                            message: 'Te-ai dezabonat cu succes!'

                        }, {
                            type: 'info',
                            timer: 100
                        });
                    }
                }
            },
            error: function (data) {
                console.log(data);
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

    $("input[name='createGroup[college]']").keyup(function () {

        $.ajax({
            url: Routing.generate('get-colleges', {'_locale': Translator.locale}),
            type: 'GET',
            dataType: 'json',
            data: {
                college: $("input[name='createGroup[college]']").val()
            },
            success: function (data) {
                var arr = [];
                if (data.length > 0) {
                    $.each(data, function (key, value) {
                        arr.push(value);
                    });

                    $("input[name='createGroup[college]']").autocomplete({
                        source: arr
                    });
                }
            }
        });
    });

});


function thumbsUp(id, obj) {


    $.ajax({
        url: Routing.generate('thumbs-up', {'_locale': Translator.locale, id: id}),
        type: 'GET',
        dataType: 'json',
        data: {
            id: id
        },
        success: function (data) {
            var rating = $(obj).closest('.rating');
            rating.val( parseInt(rating.val()) + 1);
        }
    });
}


function thumbsDown(id, obj) {


    $.ajax({
        url: Routing.generate('thumbs-down', {'_locale': Translator.locale, id: id}),
        type: 'GET',
        dataType: 'json',
        data: {
            id: id
        },
        success: function () {
            var rating = $(obj).closest('.rating');
            rating.val( parseInt(rating.val()) - 1);
        }
    });
}