$(document).ready(function () {
    $.ajax({
        url: Routing.generate('admin-report-chart', {'_locale': Translator.locale}),
        type: 'GET',
        success: function (data) {
            console.log(data);
            var dataPreferences = {
                series: [
                    [25, 30, 20, 25]
                ]
            };

            var optionsPreferences = {
                donut: true,
                donutWidth: 40,
                startAngle: 0,
                total: 100,
                showLabel: false,
                axisX: {
                    showGrid: false
                }
            };

            new Chartist.Pie('#chartPreferences', dataPreferences, optionsPreferences);

            new Chartist.Pie('#chartPreferences', {
                labels: ['62%','32%','6%'],
                series: [62, 32, 6]
            });
        }
    });
});