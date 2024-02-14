'use strict'

webApp.controller('planWeeklyEditController', ['$scope', '$state', '$compile', '$filter', '$window', 'identityService', 'planService', 'masterDataService', 'objectService', 'DTOptionsBuilder', 'DTColumnBuilder', 'ngDialog', '$ngBootbox',
	function ($scope, $state, $compile, $filter, $window, identityService, planService, masterDataService, objectService, DTOptionsBuilder, DTColumnBuilder, ngDialog, $ngBootbox){
	
		var identity = identityService.getIdentity();
        $scope.filter = {};
        $scope.chosenFilters = {};
        $scope.objectIdsList = [];

        $scope.getPlanWeeklyInfo = function (pullFromCloud) {
            planService.getPlanWeeklyInfo(identity, $state.params.planId).then(function (data) {
                $scope.planInfo = data;

                //Ako je plan uploadan na web, i ako je postavljen pullFromCloud na true, povuci ažuriranja s weba,
                //a ako ne onda samo reload optiona dt-a
                if($scope.planInfo.IsUploaded == true && pullFromCloud == true) {
                    $scope.pullCloudData();
                }
                else {
                    $scope.reloadPlanOptions();
                }
            });
        };
        //Ovo je prvi poziv kod ulaska u ekran
        $scope.getPlanWeeklyInfo(false);

        //PlanItem Datatable - no lazy
        $scope.planColumns = [
            DTColumnBuilder.newColumn(null).withTitle('#').notSortable().renderWith(statusSymbol),
            DTColumnBuilder.newColumn('PlanStatusDescription').withTitle('Status plana'),
            DTColumnBuilder.newColumn('ScheduleDate').withTitle('Datum obrade').renderWith(
                function (data) {
                    return $filter('dateTimeFormat')(data);
                }
            ),
            DTColumnBuilder.newColumn('CustomerName').withTitle('Komitent'),
            DTColumnBuilder.newColumn('ObjectName').withTitle('Naziv objekta'),
            DTColumnBuilder.newColumn('ObjectFullAddress').withTitle('Adresa objekta'),
            DTColumnBuilder.newColumn('HasFinishNotes').withTitle('Bilješke').notSortable().renderWith(hasFinishNotes),
            DTColumnBuilder.newColumn(null).withTitle('Akcije').notSortable().renderWith(actionsHtml2)
        ];

        $scope.reloadPlanOptions = function () {
            $scope.planOptions = DTOptionsBuilder
                .fromFnPromise(function () { return planService.getPlanWeeklyItemsList(identity, $state.params.planId ); })
                .withPaginationType('full_numbers')
                .withOption('createdRow', createdRow2)
                .withOption('responsive', true)
                .withOption('fnRowCallback', function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:lt(6)', nRow).unbind('click');
                    $('td:lt(6)', nRow).bind('click', function () {
                        $scope.getPlanItemDetails(aData.PlanItemId);
                    });
                    return nRow;
                });
        }

        function createdRow2(row) {
            $compile(angular.element(row).contents())($scope);
        }

        function actionsHtml2(record) {
            var htmlAction = "";
            htmlAction +=
                '<button class="btn btn-sm waves-effect waves-button waves-float waves-danger" ng-disabled="planInfo.IsLocked == true" title="Briši iz plana" ng-click="deleteFromPlan(' + record.PlanItemId + ', ' + record.ObjectId + ')">' +
                    '<span class="glyphicon glyphicon-trash"></span>' +
                '</button> ';
            htmlAction +=
                '<button class="btn btn-sm waves-effect waves-button waves-float waves-primary" ng-disabled="planInfo.IsLocked == true" title="Ažuriraj status" ng-click="updatePlanItemWeeklyStatus(' + record.PlanItemId + ', ' + record.PlanStatusId + ', \'' + record.PlanItemFinishNotes + '\')">' +
                    '<span class="fa fa-flag-checkered"></span>' +
                '</button> ';

            return htmlAction;
        }

        function statusSymbol(record){
            if (record.PlanStatusEnum == 'COMPLETED')
                return '<span class="glyphicon glyphicon-ok text-green text-bold pull-right"></span>';
            else if (record.PlanStatusEnum == 'DELAYED' || record.PlanStatusEnum == 'CANCELED')
                return '<span class="glyphicon glyphicon-remove text-red text-bold pull-right"></span>';
            return '';
        }

        function hasFinishNotes(hasFinishNotes) {
            return hasFinishNotes
                ? '<span class="glyphicon glyphicon-alert text-orange"></span>'
                : '';
        }

        $scope.deleteFromPlan = function (planItemId, objectId) {
            var optionsConfirm = {
                message: 'Jeste li sigurni da želite ukloniti objekt iz plana?',
                buttons: {
                    warning: {
                        label: "Odustani",
                        className: "btn waves-effect waves-button waves-float waves-warning",
                    },
                    success: {
                        label: "Ukloni",
                        className: "btn waves-effect waves-button waves-float waves-info",
                        callback: function() {
                            planService.deleteObjectFromPlan(identity, planItemId, objectId, $state.params.planId).then(function (success) {
                                
                                    $scope.getPlanWeeklyInfo(false);
                                    $scope.reloadData();
                                    $scope.reloadPlanOptions();
                                    $ngBootbox.customDialog(optionsSuccess);
                                },
                                function (error) {
                                    $ngBootbox.customDialog(optionsError);
                                });
                        }
                    }
                }
            };
            $ngBootbox.customDialog(optionsConfirm);
        }

        $scope.getPlanItemDetails = function (planItemId) {
            planService.getPlanItemDetailsFull(identity, planItemId).then(function (data) {
                $scope.planItemDetails = data;

                $scope.monitoringDialog = ngDialog.open({
                    template: '../Template/Monitoring/PlanItemDetailsDialog.html',
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
        }
        
        $scope.pullCloudData = function () {
            planService.syncPlanFromCloud(identity, $state.params.planId).then(function () {
                $scope.getPlanWeeklyInfo(false);
            });
        };
        //Datatable end
	
		//Objects Datatable
		$scope.query = {
            "startFrom": 0,
            "count": 10,
            //"orderBy": "lastName, firstName",
            "orderType": "ASC",
            "search": ""
        }
			
		$scope.objectColumns = [
            DTColumnBuilder.newColumn('ObjectId').withTitle('ID objekta'),
            DTColumnBuilder.newColumn('CustomerName').withTitle('Komitent'),
            DTColumnBuilder.newColumn('ObjectName').withTitle('Naziv objekta'),
            DTColumnBuilder.newColumn('ObjectFullAddress').withTitle('Adresa objekta'),
            DTColumnBuilder.newColumn('PlansAssignedFull').withTitle('Planova dodijeljeno u mjesecu'),
            DTColumnBuilder.newColumn(null).withTitle('#').notSortable().renderWith(quickAddAction),
            DTColumnBuilder.newColumn(null).withTitle('Akcije').notSortable().renderWith(actionsHtml)
        ];

        $scope.objectOptions = DTOptionsBuilder
            .newOptions()
            .withFnServerData(serverData)
            .withDataProp('data')
            .withOption('serverSide', true)
            .withOption('paging', true)
            .withOption('destroy', true)
            .withPaginationType('full_numbers')
            .withOption('createdRow', createdRow)
            .withDisplayLength(50)
			.withOption('responsive', true)
            .withOption('fnRowCallback', function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:lt(4)', nRow).unbind('click');
                $('td:lt(4)', nRow).bind('click', function () {
                    $scope.getObjectForTransferDetails(aData.ObjectId);
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

            planService.getObjectItemPlansListLazy(identity, $scope.query, $scope.filter, $state.params.planId).then(function (result) {

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
			$scope.objectOptions = DTOptionsBuilder
			.newOptions()
			.withFnServerData(serverData)
			.withDataProp('data')
			.withOption('serverSide', true)
			.withOption('paging', true)
			.withOption('destroy', true)
			.withPaginationType('full_numbers')
			.withOption('createdRow', createdRow)
			.withDisplayLength(50)
			.withOption('responsive', true)
            .withOption('fnRowCallback', function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                $('td:lt(4)', nRow).unbind('click');
                $('td:lt(4)', nRow).bind('click', function () {
                    $scope.getObjectForTransferDetails(aData.ObjectId);
                });
                return nRow;
            });
        }

        function createdRow(row) {
            $compile(angular.element(row).contents())($scope);
        }

        function actionsHtml(record) {
            var htmlAction = "";
            htmlAction +=
                  '<button class="btn btn-sm waves-effect waves-button waves-float waves-indigo" title="Dodaj u tjedni plan" ng-click="addToPlan(' + record.ObjectId + ')">' +
                       '<span class="fa fa-upload"></span><span class="text-small"> Dodaj u plan</span>' +
                  '</button> ';

            return htmlAction;
        }

        function quickAddAction(record) {
            var htmlAction = "";
            htmlAction +=
                '<label class="ui-checkbox">' +
                //TODO dupli objectId?
                '<input name="checkbox" type="checkbox" ng-model="departmentChecked' + record.ObjectId + '" ng-change="addRemoveItem(departmentChecked' + record.ObjectId + ', ' + record.ObjectId + ')">' +
                '<span></span></label> ';

            return htmlAction;

        }

        $scope.addRemoveItem = function (checked, value) {
            if (checked) {
                $scope.objectIdsList.push(value);
            }
            else {
                var index = $scope.objectIdsList.indexOf(value);
                $scope.objectIdsList.splice(index, 1);
            }
        };

        $scope.groupAdd = function () {

        }

        $scope.getObjectForTransferDetails = function(objectId){
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
        }
        //Datatable end

        //Popuni ui selecte za dialog filtera
        planService.getContractServiceTypes(identity).then(function (data){
            $scope.contractServiceTypes = data;
        });
        planService.getServiceItems(identity).then(function (data){
            $scope.serviceItems = data;
        });
        planService.getAnalysis(identity).then(function (list){
            $scope.analysisList = list;
        });
        masterDataService.getObjectAreas(identity).then(function (data){
            $scope.areas = data;
        });
        masterDataService.getCities(identity).then(function (data){
            $scope.cities = data;
        });
        objectService.getObjectTypes(identity).then(function (types) {
            $scope.objectTypes = types;
        });

        $scope.setFilter = function(){
            $scope.dialogFilter = ngDialog.open({
                template: '../Template/Monitoring/PlanItemsWeeklyFilterDialog.html',
                className: 'ngdialog-theme-default wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
                cache: false
            });
        }

        $scope.searchObjectItemPlans = function () {
            $scope.dialogFilter = ngDialog.close();
            $scope.chosenFilters = angular.copy($scope.filter);
            $scope.reloadData();
        }
        //Filter end

        //Postavljanje objekata za podatke o prebacivanju
        $scope.transferData = {};
        $scope.transferData.planWeeklyId = $state.params.planId;
        
        $scope.addToPlan = function(selectedObjectId){
            //$scope.transferData.scheduleDate = $scope.planInfo.StartDate;
            $scope.transferData.selectedObjectId = selectedObjectId;
            $scope.transferData.notes = null;

            $scope.dialogAddToPlan = ngDialog.open({
                template: '../Template/Monitoring/PlanWeeklyAddObjectItemPlanDialog.html',
                className: 'ngdialog-theme-default wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
                cache: false
            });
        }
        
        $scope.saveObjectToPlan = function () {
 
            $scope.dialogAddToPlan = ngDialog.close();
            planService.saveObjectToPlanWeekly(identity, $scope.transferData).then(function () {

                $scope.reloadData();
                $scope.reloadPlanOptions();
                //Ovdje svakako neće povlačiti s weba ažuriranja,
                //jer se ne smije dodavati u plan ako je plan uploaded = true
                $scope.getPlanWeeklyInfo(false);
                $ngBootbox.customDialog(optionsSuccess);
            },
            function (error) {
                $ngBootbox.customDialog(optionsError);
            })
        }

        $scope.addGroupToPlanDialog = function(){
            $scope.dialogAddGroupToPlan = ngDialog.open({
                template: '../Template/Monitoring/PlanWeeklyAddObjectItemPlanDialog.html',
                className: 'ngdialog-theme-default wide-dialog',
                controller: 'planWeeklyAddObjectItemPlanGroupDialogController',
                data: { objectIdsList: $scope.objectIdsList },
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
                cache: false
            });
        }
        //Transfer end

        //Update plan item statuses
        $scope.planStatus = {};

        planService.getPlanStatusesList(identity).then(function (data) {
            $scope.planStatusesList = data;
        });
        $scope.yesNoSelectOptions = [{ name: 'Da', value: true }, { name: 'Ne', value: false }];

        $scope.updatePlanWeeklyItemsStatuses = function () {
            $scope.planStatus.lockPlan = $scope.yesNoSelectOptions[1].value;
            
            $scope.dialogUpdatePlan = ngDialog.open({
                template: '../Template/Monitoring/PlanItemsWeeklyStatusesUpdateDialog.html',
                className: 'ngdialog-theme-default wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
                cache: false
            });
        }

        $scope.savePlanItemsWeeklyStatuses = function(){
            planService.updatePlanWeeklyItemsStatuses(identity, $state.params.planId, $scope.planStatus).then(function (response) {

                $scope.dialogUpdatePlan = ngDialog.close();
                //Ako lokalno ažuriramo status objekta,
                //ne želimo da nam ponovo povuče s weba status
                $scope.getPlanWeeklyInfo(false);
                $scope.reloadPlanOptions();
                $ngBootbox.customDialog(optionsLockSuccess(response));
            },
            function (error) {
                $scope.dialogUpdatePlan = ngDialog.close();
                $ngBootbox.customDialog(optionsError);
            })
        },
        //Update plan item statuses - end

        //Update plan item status
        $scope.updatePlanItemWeeklyStatus = function (id, statusId, finishNotes) {
            $scope.planItemStatus = {
                statusId : statusId,
                finishnotes: finishNotes === "null" ? null : finishNotes
            };

            $scope.planItemId = id;
            $scope.dialogUpdatePlanItem = ngDialog.open({
                template: '../Template/Monitoring/PlanItemWeeklyStatusUpdateDialog.html',
                className: 'ngdialog-theme-default wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
                cache: false
            });
        }

        $scope.savePlanItemWeeklyStatus = function(id) {
            planService.updatePlanItemWeeklyStatus(identity, $scope.planItemId, $scope.planItemStatus.statusId, $scope.planItemStatus.finishnotes, $state.params.planId).then(function (success) {

                    $scope.dialogUpdatePlan = ngDialog.close();
                    //Ako lokalno ažuriramo status objekta,
                    //ne želimo da nam ponovo povuče s weba status
                    $scope.getPlanWeeklyInfo(false);
                    $scope.reloadPlanOptions();
                    $scope.reloadData();
                    $ngBootbox.customDialog(optionsSuccess);
                },
                function (error) {
                    $scope.dialogUpdatePlan = ngDialog.close();
                    $ngBootbox.customDialog(optionsError);
                })
        }
        //Update plan item status - end

        $scope.lockPlan = function(){
            var optionsConfirm = {
                message: 'Jeste li sigurni da želite zaključati plan?',
                buttons: {
                    warning: {
                        label: "Odustani",
                        className: "btn waves-effect waves-button waves-float waves-warning",
                    },
                    success: {
                        label: "Zaključaj",
                        className: "btn waves-effect waves-button waves-float waves-info",
                        callback: function() {
                            planService.lockPlanWeekly(identity, $state.params.planId).then(function (response) {
                                $scope.getPlanWeeklyInfo(false);
                                $ngBootbox.customDialog(optionsLockSuccess(response));
                            },
                            function (error) {
                                $ngBootbox.customDialog(optionsError);
                            });
                        }
                    }
                }
            };
            $ngBootbox.customDialog(optionsConfirm);
        }

        $scope.printPlanWeeklyPdf = function(){
            planService.printPlanWeeklyPdf(identity, $state.params.planId)
                .then(function (result) {

                    var file = new Blob([result], { type: 'application/pdf' });
                    var fileURL = $window.URL.createObjectURL(file);
                    $window.open(fileURL);
                })
        };

        $scope.pushToCloud = function(){
            var optionsConfirm = {
                message: 'Jeste li sigurni da želite poslati plan korisniku?',
                buttons: {
                    warning: {
                        label: "Odustani",
                        className: "btn waves-effect waves-button waves-float waves-warning",
                    },
                    success: {
                        label: "Pošalji",
                        className: "btn waves-effect waves-button waves-float waves-info",
                        callback: function() {
                            planService.syncPlanToWeb(identity, $state.params.planId).then(function (success) {
                                $scope.getPlanWeeklyInfo(false);
                                $ngBootbox.customDialog(optionsSuccess);
                            },
                            function (error) {
                                $ngBootbox.customDialog(optionsError);
                            });
                        }
                    }
                }
            };
            $ngBootbox.customDialog(optionsConfirm);
        };

		$scope.back = function(){
			$state.go('monitoring.plansWeekly');
		};

        //TODO: export u neki template vanjski
        var optionsSuccess = {
            message: 'Uspješno spremljeno.',
            buttons: {
                success: {
                    label: "Ok",
                    className: "btn waves-effect waves-button waves-float waves-info",
                }
            }
        };

        var optionsLockSuccess = function (archivedObjects) {
            var successMessage = 'Uspješno spremljeno.<br>';
            if(archivedObjects.length > 0) {
                successMessage += '<br>POPIS ARHIVIRANIH OBJEKATA:<br>';
                angular.forEach(archivedObjects, function (value, key) {
                    successMessage += (key + 1) + '. ' + value.CustomerName + ' - ' + value.Name + ', OIB: ' + value.Oib + '<br>';
                });
            }
            return {
                message: successMessage,
                buttons: {
                    success: {
                        label: "Ok",
                        className: "btn waves-effect waves-button waves-float waves-info",
                    }
                }};
        };

        //TODO: export u neki template vanjski
        var optionsError = {
            message: 'Došlo je do greške!',
            buttons: {
                warning: {
                    label: "Ok",
                    className: "btn waves-effect waves-button waves-float waves-danger",
                }
            }
        }

        //TODO: export u neki template vanjski
        var optionsSuccessDelete = {
            message: 'Uspješno obrisano.',
            buttons: {
                success: {
                    label: "Ok",
                    className: "btn waves-effect waves-button waves-float waves-info",
                }
            }
        }
	}
]);