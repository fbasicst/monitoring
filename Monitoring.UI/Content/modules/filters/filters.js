'use strict'

webApp.filter('boolFilter', [ function () {
    return function (item) {
        return (item == true || item == 'true') ? 'Da' : 'Ne'
    };
}]);

webApp.filter('dateTimeFormat',[ function () {

    return function (input, includeTime) {
        if(input != null)
        input = moment(input).format('DD.MM.YYYY')
        includeTime = includeTime || false;
        if (includeTime)
            input = moment(input).format('DD.MM.YYYY')
        else if (input == null || input == 'null')
            return '';
        return input;
    };

}]);

webApp.filter('monthName', [function() {

    return function (monthNumber) {
        var monthNames = [
            'Siječanj',
            'Veljača',
            'Ožujak',
            'Travanj',
            'Svibanj',
            'Lipanj',
            'Srpanj',
            'Kolovoz',
            'Rujan',
            'Listopad',
            'Studeni',
            'Prosinac'
        ];
        return monthNames[monthNumber - 1];
    }
}]);