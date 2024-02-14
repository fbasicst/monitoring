'use strict';

webApp.controller('reportServiceItemsController', ['$scope', '$compile', 'identityService', 'reportService', 'planService', 'masterDataService', 'DTOptionsBuilder', 'DTColumnBuilder', 'ngDialog',
    function($scope, $compile, identityService, reportService, planService, masterDataService, DTOptionsBuilder, DTColumnBuilder, ngDialog) {

        var identity = identityService.getIdentity();
        $scope.filter = {};
        $scope.filter.year = moment().format('YYYY');
        $scope.filter.month = moment().format('M');

        $scope.setFilter = function(){
            $scope.dialogFilter = ngDialog.open({
                template: '../Template/Monitoring/ReportServiceItemsFilterDialog.html',
                className: 'ngdialog-theme-default wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
                cache: false
            });
        };

        planService.getAnalysis(identity).then(function (list){
            $scope.analysisList = list;
        });
        masterDataService.getObjectAreas(identity).then(function (data){
            $scope.areas = data;
            //Defaultno područje Split
            $scope.filter.area = [$scope.areas[10]];
            $scope.chosenFilters = angular.copy($scope.filter);
            //Početni reload data je ovdje jer želimo da se prvo postavi filter pa onda request
            $scope.reloadServiceItemsOptions();
        });

        $scope.searchAnnuallyPlans = function () {
            $scope.dialogFilter = ngDialog.close();
            $scope.chosenFilters = angular.copy($scope.filter);
            $scope.reloadServiceItemsOptions();
        };

        $scope.serviceItemsColumns = [
            DTColumnBuilder.newColumn('ServiceItemFull').withTitle('Vrsta nadzora'),
            DTColumnBuilder.newColumn('ServiceItemQuantitySum').withTitle('Suma brojeva odrađenih nadzora')
        ];
        $scope.reloadServiceItemsOptions = function () {
            $scope.filter.areaIds = _.pluck($scope.filter.area, 'Id');
            $scope.filter.analysisIds = _.pluck($scope.filter.analysis, 'Id');
            $scope.serviceItemsOptions = DTOptionsBuilder
                .fromFnPromise(function () { return reportService.getCompletedServiceItems(identity, $scope.filter); })
                .withPaginationType('full_numbers')
                .withOption('createdRow', createdRow);
        };
        function createdRow(row) {
            $compile(angular.element(row).contents())($scope);
        }
        $scope.reloadServiceItemsOptions();
    }
]);

