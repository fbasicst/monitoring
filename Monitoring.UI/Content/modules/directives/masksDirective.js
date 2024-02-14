'use strict'
webApp.directive('integermask', function () {

    return {
        require: "ngModel",
        link: function (scope, elem, attr, ctrl) {
            elem.inputmask('Regex', { regex: "^[0-9]*$" });
            elem.on('keyup', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
            elem.on('blur', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
        }
    };
});

webApp.directive('emailmask', function () {

    return {
        require: "ngModel",
        link: function (scope, elem, attr, ctrl) {
            elem.inputmask({ alias: "email", "clearIncomplete": true });
            elem.on('keyup', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
            elem.on('blur', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
        }
    };
});

webApp.directive('phonemask', function () {

    return {
        require: "ngModel",
        link: function (scope, elem, attr, ctrl) {
            elem.inputmask('Regex', { regex: "^[0-9+]*$" });
            elem.on('keyup', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
            elem.on('blur', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
        }
    };
});

webApp.directive('datemask', function () {

    return {
        require: "ngModel",
        link: function (scope, elem, attr, ctrl) {
            elem.inputmask('d.m.y', {
                "placeholder": 'DD.MM.GGGG', "clearIncomplete": true, "oncomplete": function () {
                    elem.trigger('blur');
                    elem.focus();
                }
            });
            elem.on('keyup', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
            elem.on('blur', function () {
                scope.$apply(function () {
                    ctrl.$setViewValue(elem.val());
                });
            });
        }
    };
});

