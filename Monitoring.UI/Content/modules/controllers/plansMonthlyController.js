'use strict'

webApp.controller('plansMonthlyController', ['$scope', '$compile', '$filter', '$window', 'identityService', 'planService', 'objectService', 'masterDataService', 'DTOptionsBuilder', 'DTColumnBuilder', 'ngDialog',
    function($scope, $compile, $filter, $window, identityService, planService, objectService, masterDataService, DTOptionsBuilder, DTColumnBuilder, ngDialog){

        var identity = identityService.getIdentity();

        //Filter
        $scope.filter = {};
        $scope.filter.month = moment().format('M');
        $scope.filter.year = moment().format('YYYY');

        $scope.setFilter = function(){
            $scope.dialogFilter = ngDialog.open({
                template: '../Template/Monitoring/PlansMonthlyFilterDialog.html',
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

        masterDataService.getObjectAreas(identity).then(function (data){
            $scope.areas = data;
            //Tomo je izričito htio da u početnom filteru bude samo jedno područje
            //pa sam postavio Split
            $scope.filter.area = [$scope.areas[10]];
            $scope.chosenFilters = angular.copy($scope.filter);
            //Početni reload data je ovdje jer želimo da se prvo postavi filter pa onda request
            $scope.reloadData();
        });

        $scope.searchMonthlyPlans = function () {
            $scope.dialogFilter = ngDialog.close();
            $scope.chosenFilters = angular.copy($scope.filter);
            $scope.reloadData();
        };

        //Datatable - start
        $scope.query = {
            "startFrom": 0,
            "count": 10,
            //"orderBy": "lastName, firstName",
            "orderType": "ASC",
            "search": ""
        };

        $scope.planColumns = [
            DTColumnBuilder.newColumn('ObjectId').withTitle('ID objekta'),
            DTColumnBuilder.newColumn('CustomerName').withTitle('Komitent'),
            DTColumnBuilder.newColumn('ObjectName').withTitle('Naziv objekta'),
            DTColumnBuilder.newColumn('ObjectFullAddress').withTitle('Adresa objekta'),
            DTColumnBuilder.newColumn(null).withTitle('Datum obrade - korisnik').renderWith(userDatesHtml)
        ];

        $scope.reloadData = function () {
            $scope.planOptions = DTOptionsBuilder
                .newOptions()
                .withFnServerData(serverData)
                .withDataProp('data')
                .withOption('serverSide', true)
                .withOption('paging', true)
                .withOption('destroy', true)
                .withPaginationType('full_numbers')
                .withOption('createdRow', createdRow)
                .withDisplayLength(10)
                .withOption('fnRowCallback', function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:lt(5)', nRow).unbind('click');
                    $('td:lt(5)', nRow).bind('click', function () {
                        $scope.getObjectDetailsFull(aData.ObjectId);
                    });
                    return nRow;
                });
        };

        function createdRow(row) {
            $compile(angular.element(row).contents())($scope);
        }

        var searchFirst = true;

        function serverData(sSource, aoData, fnCallback, oSettings) {

            var draw = aoData[0].value;
            var order = aoData[2].value;
            var start = aoData[3].value;
            var length = aoData[4].value;
            var search = aoData[5].value;

            $scope.query.startFrom = start;
            $scope.query.count = length;
            $scope.query.orderType = order[0].dir;
            //$scope.query.orderBy = order[0].column;
            $scope.query.search = search.value;

            if ($scope.query.search != null && $scope.query.search != '' && $scope.query.search.length < 3) {
                searchFirst = true;
                return;
            }
            else if (searchFirst && $scope.query.search != null && $scope.query.search != '' && $scope.query.search.length == 3) {
                $scope.query.startFrom = 0;
                searchFirst = false;
            }
            var areaIds = _.pluck($scope.filter.area, 'Id');
            planService.getObjectsForPlanMonthlyLazy(identity, $scope.query, $scope.filter, areaIds).then(function (result) {

                var records = {
                    'draw': draw,
                    'recordsTotal': result.Total,
                    'recordsFiltered': result.Filtered,
                    'iTotalDisplayRecords': result.Total,
                    'data': result.Records
                };
                fnCallback(records);
            });
        }

        function userDatesHtml(record) {
            var html = '';
            angular.forEach(record.ObjectPlanUserSchedules, function(value, key){
                html+= ''+ $filter('dateTimeFormat')(value.PlanScheduleDate) + ' - ' + value.PlanUser + '<br>';
            });
            return html;
        }

        $scope.getObjectDetailsFull = function(objectId){
            objectService.getObjectForTransferDetails(identity, objectId).then(function (data) {

                $scope.objectFull = data;

                $scope.detailsDialog = ngDialog.open({
                    template: '../Template/Monitoring/ObjectDetailsFullDialog.html',
                    className: 'ngdialog-theme-default extra-wide-dialog',
                    closeByDocument: false,
                    closeByEscape: true,
                    showClose: true,
                    trapFocus: true,
                    preserveFocus: true,
                    scope: $scope,
                    cache: false
                });
            });
        };

        $scope.getPlanMonthlyPdf = function () {
            var areaIds = _.pluck($scope.filter.area, 'Id');
            planService.printPlanMonthlyPdf(identity, $scope.filter, areaIds)
                .then(function (result) {
                    var file = new Blob([result], { type: 'application/pdf' });
                    var fileURL = $window.URL.createObjectURL(file);
                    $window.open(fileURL);
                });
        }
    }
]);