'use strict'

webApp.controller('plansWeeklyController', ['$scope', '$compile', '$state', '$filter', 'identityService', 'planService', 'masterDataService', 'DTOptionsBuilder', 'DTColumnBuilder', 'ngDialog', '$ngBootbox',
	function($scope, $compile, $state, $filter, identityService, planService, masterDataService, DTOptionsBuilder, DTColumnBuilder, ngDialog, $ngBootbox){

		var identity = identityService.getIdentity();
		$scope.yesNoSelectOptions = [{ name: 'Da', value: true }, { name: 'Ne', value: false }];

		//Get data for plan creation
		$scope.plan = {};
		$scope.plan.userCreated = {};
		$scope.plan.userControlled = {};

		$scope.plan.userCreated.firstName = identity.FirstName;
		$scope.plan.userCreated.lastName = identity.LastName;
		$scope.plan.userCreated.id = identity.UserId;

		masterDataService.getUsersFromRole(identity, 'SAMPLER_MONITORING').then(function(data){
			$scope.planUsersList = data;
		});

		masterDataService.getUsersFromRole(identity, 'LEAD_MONITORING').then(function(data){
			$scope.plan.userControlled = data[0];
		});

		$scope.setMaxDate = function(date){
			//Prvo postavi datum isteka 5 dana iza starta
			$scope.plan.expirationDate = moment(date, 'DD.MM.YYYY').add(4, 'days').format('DD.MM.YYYY');

			//Ako datum isteka prolazi u sljedeći mjesec, postavi expiration kraj mjeseca
			if(moment($scope.plan.expirationDate, 'DD.MM.YYYY').format('MM') != moment(date, 'DD.MM.YYYY').format('MM')){
				$scope.plan.expirationDate = moment(date, 'DD.MM.YYYY').endOf('month').format('DD.MM.YYYY');
			}
			//Maximalni datum za izabrati u expiration je samo unutar danog mjeseca
			$scope.maxDate = moment(date, 'DD.MM.YYYY').endOf('month').format('DD.MM.YYYY');
		}

		$scope.createPlan = function(){
			//Pronađi sljedeći ponedjeljak
			var weekDayToFind = moment().day('Monday').weekday();
			var searchDate = moment();
			while (searchDate.weekday() !== weekDayToFind){
			  searchDate.add(1, 'day');
			}

			$scope.plan.creationDate = searchDate.format('DD.MM.YYYY');
			$scope.plan.expirationDate = searchDate.add(4, 'days').format('DD.MM.YYYY');
			$scope.setMaxDate($scope.plan.creationDate);
			$scope.plan.label = 'PI-5.7.-V.3./02';

			$scope.dialogPlan = ngDialog.open({
				template: '../Template/Monitoring/PlanCreateDialog.html',
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

		$scope.savePlanWeekly = function(){
			$scope.dialogPlan = ngDialog.close();

			$scope.plan.creationDate = moment($scope.plan.creationDate,'DD.MM.YYYY').format('YYYY-MM-DD');
			$scope.plan.expirationDate = moment($scope.plan.expirationDate,'DD.MM.YYYY').format('YYYY-MM-DD');
			$scope.plan.attachedUserIds = _.pluck($scope.plan.attachedUsers, 'UserId');
			planService.savePlanWeekly(identity, $scope.plan).then(function(success){

				$scope.reloadData();

				//Pronađi sljedeći ponedjeljak
				var weekDayToFind = moment().day('Monday').weekday();
				var searchDate = moment();
				while (searchDate.weekday() !== weekDayToFind){
				  searchDate.add(1, 'day');
				}

				$scope.plan.creationDate = searchDate.format('DD.MM.YYYY');
				$scope.plan.expirationDate = searchDate.add(4, 'days').format('DD.MM.YYYY');
				$scope.setMaxDate($scope.plan.creationDate);
				$scope.plan.label = 'PI-5.7.-V.3./02';
				$scope.plan.attachedUsers = [];
			},
			function(error){
				$ngBootbox.customDialog(optionsError);
			});
		}

		$scope.checkDates = function(startDate, endDate){
			if(moment(startDate, 'DD.MM.YYYY') <= moment(endDate, 'DD.MM.YYYY')){
				return false;
			}
			return true;
		}
		//Plan creation - end

		//Filter
		$scope.filter = {};
		$scope.filter.month = moment().format('M');
		$scope.filter.year = moment().format('YYYY');

		$scope.chosenFilters = angular.copy($scope.filter);

		planService.getPlanStatusesList(identity).then(function (data) {
			$scope.planStatusesList = data;
		});

		$scope.setFilter = function(){
			$scope.dialogFilter = ngDialog.open({
				template: '../Template/Monitoring/PlansWeeklyFilterDialog.html',
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

		$scope.searchWeeklyPlans = function () {
			$scope.dialogFilter = ngDialog.close();
			$scope.chosenFilters = angular.copy($scope.filter);
			$scope.reloadData();
		}

		$scope.query = {
            "startFrom": 0,
            "count": 10,
            //"orderBy": "lastName, firstName",
            "orderType": "ASC",
            "search": ""
        }

		$scope.plansColumns = [
		    DTColumnBuilder.newColumn('PlanUser').withTitle('Izvršitelj plana'),
            DTColumnBuilder.newColumn('StartDate').withTitle('Početak plana'),
            DTColumnBuilder.newColumn('EndDate').withTitle('Istek plana'),
            DTColumnBuilder.newColumn('DaysAmount').withTitle('Broj dana'),
            DTColumnBuilder.newColumn('ObjectsAmount').withTitle('Broj objekata'),
			DTColumnBuilder.newColumn('ObjectsCompleted').withTitle('Objekata odrađeno'),
            DTColumnBuilder.newColumn('Label').withTitle('Oznaka'),
            DTColumnBuilder.newColumn(null).withTitle('Akcije').notSortable().renderWith(actionsHtml)
        ];

        $scope.plansOptions = DTOptionsBuilder
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
                    $state.go('monitoring.planWeeklyEdit', { planId: aData.PlanId });
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

            planService.getPlansWeeklyListLazy(identity, $scope.query, $scope.filter).then(function (result) {

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
			$scope.plansOptions = DTOptionsBuilder
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
					$state.go('monitoring.planWeeklyEdit', { planId: aData.PlanId });
				});
				return nRow;
			});
        }

        function createdRow(row) {
            $compile(angular.element(row).contents())($scope);
        }

        function actionsHtml(record) {

            var htmlAction = "";
			if(record.IsLocked == false) {

				if(record.ObjectsAmount != 0 && record.ObjectsPending == 0) {
					//Omogući zaključavanje direktno iz popisa
					htmlAction +=
						'<button class="btn btn-sm waves-effect waves-button waves-float waves-indigo" title="Zaključaj plan" ng-click="lockPlan(' + record.PlanId + ')">' +
							'<span class="glyphicon glyphicon-lock"></span>' +
						'</button> ';
				}
				htmlAction +=
					'<button class="btn btn-sm waves-effect waves-button waves-float waves-danger" title="Briši plan" ng-click="deletePlan(' + record.PlanId + ')">' +
					'<span class="glyphicon glyphicon-trash"></span>' +
					'</button> ';
			}
			else {
				htmlAction +=
					'<button class="btn btn-sm waves-effect waves-button waves-float waves-grey"><span class="glyphicon glyphicon-lock"></span><span class="text-small"> Plan zaključan</span></button>';
			}

            return htmlAction;
        }

		$scope.deletePlan = function (planId) {
			var optionsConfirm = {
				message: 'Jeste li sigurni da želite obrisati plan?',
				buttons: {
					warning: {
						label: "Odustani",
						className: "btn waves-effect waves-button waves-float waves-warning",
					},
					success: {
						label: "Obriši",
						className: "btn waves-effect waves-button waves-float waves-info",
						callback: function() {
							planService.deletePlanWeekly(identity, planId).then(function (success) {
								$scope.reloadData();
								$ngBootbox.customDialog(optionsSuccessDelete);

							},function (error) {
								$ngBootbox.customDialog(optionsError);
							});
						}
					}
				}
			};
			$ngBootbox.customDialog(optionsConfirm);
		}

		$scope.lockPlan = function (planId) {
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
							planService.lockPlanWeekly(identity, planId).then(function (response) {
								$scope.reloadData();
								$ngBootbox.customDialog(optionsLockSuccess(response));

							},function (error) {
								$ngBootbox.customDialog(optionsError);
							});
						}
					}
				}
			};
			$ngBootbox.customDialog(optionsConfirm);
		}

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
		var optionsSuccess = {
			message: 'Uspješno spremljeno.',
			buttons: {
				success: {
					label: "Ok",
					className: "btn waves-effect waves-button waves-float waves-info",
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
	}
]);