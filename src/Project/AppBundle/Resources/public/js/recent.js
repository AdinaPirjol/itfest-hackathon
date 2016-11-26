$(document).ready(function () {
    $.ajax({
        url: Routing.generate('project-recent', {'_locale': Translator.locale}),
        type: 'GET',
        success: function (data) {
           // $("div#recent-projects").html(data);
        }
    });
});