ko.bindingHandlers.datepicker = {
    init: function(element, valueAccessor, allBindingsAccessor) {
        //initialize datepicker with some optional options
        var options = allBindingsAccessor().datepickerOptions || {};
        $(element).datepicker(options);

        //handle the field changing
        ko.utils.registerEventHandler(element, "change", function() {
            var observable = valueAccessor();
            observable($(element).datepicker("getDate"));
        });

        //handle disposal (if KO removes by the template binding)
        ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
            $(element).datepicker("destroy");
        });

    },
    update: function(element, valueAccessor) {
        var value = ko.utils.unwrapObservable(valueAccessor()),
            current = $(element).datepicker("getDate");

        if (value - current !== 0) {
            $(element).datepicker("setDate", value);
        }
    }
};

var ViewModel = function() {
    var self = this;

    self.hourOptions = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];

    self.formatTimeOption = function(hour) {
        if (hour < 10) return "0" + hour;
        return hour.toString();
    };

    self.startDate = ko.observable(null);
    self.endDate = ko.observable(null);

    self.startDateHour = ko.computed({
        read: function() {
            return new Date(self.startDate()).getHours();
        },
        write: function(value) {
            var newDate = new Date(self.startDate());
            newDate.setHours(value);
            self.startDate(newDate);
        }
    });

    self.endDateHour = ko.computed({
        read: function() {
            return new Date(self.endDate()).getHours();
        },
        write: function(value) {
            var newDate = new Date(self.endDate());
            newDate.setHours(value);
            self.endDate(newDate);
        }
    });
};