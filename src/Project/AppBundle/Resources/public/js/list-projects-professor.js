$(document).ready(function () {
    $('a#delete-project').click(function (e) {
        e.preventDefault();

        var dialog =
            '<div id="dialog-confirm" title="Info">'
            + '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'
            + 'This items will be permanently deleted and cannot be recovered. Are you sure?</p>'
            + '</div>';

        $(dialog).dialog({
            resizable: false,
            height: 250,
            modal: true,
            buttons: {
                'Delete': function () {
                    $(this).dialog('close');

                    $.ajax({
                        url: Routing.generate('project-delete', {'_locale': Translator.locale, 'id': $('#delete-project').data('project-id') }),
                        type: 'POST',
                        beforeSend: function () {
                            //show loading modal
                        },
                        success: function (data) {
                            if (data.error === true) {
                                $.notify({
                                    icon: 'fa fa-exclamation-circle',
                                    message: Translator.trans('error') + ': ' + Translator.trans('error_request')

                                }, {
                                    type: 'danger',
                                    timer: 100
                                });
                            } else {
                                $.notify({
                                    icon: 'fa fa-gift',
                                    message: Translator.trans('ajax.delete_project')

                                }, {
                                    type: 'info',
                                    timer: 100
                                });

                                location.reload();
                            }
                        },
                        error: function () {
                            $.notify({
                                icon: 'fa fa-gift',
                                message: Translator.trans('error') + ': ' + Translator.trans('error_request')

                            }, {
                                type: 'danger',
                                timer: 100
                            });
                        }
                    });
                },
                'Cancel': function () {
                    $(this).dialog('close');
                }
            }
        });
    });
});