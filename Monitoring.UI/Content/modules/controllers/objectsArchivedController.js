'use strict';

webApp.controller('objectsArchivedController', ['$scope', '$compile', '$state', 'identityService', 'objectService', 'DTOptionsBuilder', 'DTColumnBuilder', '$ngBootbox',
    function($scope, $compile, $state, identityService, objectService, DTOptionsBuilder, DTColumnBuilder, $ngBootbox) {

        var identity = identityService.getIdentity();
        $scope.query = {
            "startFrom": 0,
            "count": 10,
            //"orderBy": "lastName, firstName",
            "orderType": "ASC",
            "search": ""
        };

        $scope.objectsColumns = [
            DTColumnBuilder.newColumn('ObjectId').withTitle('ID objekta'),
            DTColumnBuilder.newColumn('ObjectName').withTitle('Naziv objekta'),
            DTColumnBuilder.newColumn('CustomerName').withTitle('Komitent'),
            DTColumnBuilder.newColumn('ContractBarcode').withTitle('Barkod ugovora').notSortable(),
            DTColumnBuilder.newColumn('ObjectStreet').withTitle('Ulica'),
            DTColumnBuilder.newColumn('ObjectCityName').withTitle('Grad')
        ];

        $scope.objectsOptions = DTOptionsBuilder
            .newOptions()
            .withFnServerData(serverData)
            .withDataProp('data')
            .withOption('serverSide', true)
            .withOption('paging', true)
            .withOption('destroy', true)
            .withPaginationType('full_numbers')
            .withOption('createdRow', createdRow)
            .withDisplayLength(10)
            .withOption('responsive', true)
            .withOption('fnRowCallback', function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:lt(6)', nRow).unbind('click');
                $('td:lt(6)', nRow).bind('click', function () {
                    $state.go('monitoring.objectedit', { objectId: aData.ObjectId, status: 'archived' });
                });
                return nRow;
            });

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
            var isActive = false;
            objectService.getObjectsListLazy(identity, $scope.query, isActive).then(function (result) {

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

        $scope.reloadData = function () {
            $scope.objectsOptions = DTOptionsBuilder
                .newOptions()
                .withFnServerData(serverData)
                .withDataProp('data')
                .withOption('serverSide', true)
                .withOption('paging', true)
                .withOption('destroy', true)
                .withPaginationType('full_numbers')
                .withOption('createdRow', createdRow)
                .withDisplayLength(10)
                .withOption('responsive', true)
                .withOption('fnRowCallback', function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:lt(6)', nRow).unbind('click');
                    $('td:lt(6)', nRow).bind('click', function () {
                        $state.go('monitoring.objectedit', { objectId: aData.ObjectId, status: 'archived' });
                    });
                    return nRow;
                });
        }

        function createdRow(row) {
            $compile(angular.element(row).contents())($scope);
        }

        //TO DO: export u neki template vanjski
        var optionsSuccess = {
            message: 'Uspješno obrisano.',
            buttons: {
                success: {
                    label: "Ok",
                    className: "btn waves-effect waves-button waves-float waves-info",
                }
            }
        }

        $scope.deleteObject = function(objectId){
            var optionsConfirm = {
                message: 'Jeste li sigurni da želite obrisati objekt?',
                buttons: {
                    warning: {
                        label: "Odustani",
                        className: "btn waves-effect waves-button waves-float waves-warning",
                    },
                    success: {
                        label: "Obriši",
                        className: "btn waves-effect waves-button waves-float waves-info",
                        callback: function() {
                            objectService.deleteObject(identity, objectId).then(function(success){
                                $scope.reloadData();
                                $ngBootbox.customDialog(optionsSuccess);
                            });
                        }
                    }
                }
            };
            $ngBootbox.customDialog(optionsConfirm);
        };

        //NOT IN USE
        function actionsHtml(record) {
            var htmlAction = "";
            htmlAction +=
                '<button class="btn btn-sm waves-effect waves-button waves-float waves-danger" title="Brisanje objekta" ng-click="deleteObject(' + record.ObjectId + ')">' +
                '<span class="glyphicon glyphicon-trash"></span>' +
                '</button> ';

            return htmlAction;
        }

    }
]);
