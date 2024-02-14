'use strict'

webApp.controller('objectEditController', ['$scope', '$state', '$window', '$filter', 'identityService', 'objectService', 'planService', '$ngBootbox', 'ngDialog',
	function($scope, $state, $window, $filter, identityService, objectService, planService, $ngBootbox, ngDialog) {
		
		var identity = identityService.getIdentity();
		$scope.getObjectFull = function(){
			objectService.getObjectFull(identity, $state.params.objectId).then(function (data){
				$scope.objectFull = data;
			});
		};
		$scope.getObjectFull();
		$scope.back = function(){
			if($state.params.status == 'active')
				$state.go('monitoring.objectList');
			if($state.params.status == 'archived')
				$state.go('monitoring.archivedObjectList');
		};
		$scope.showPDF = function(){
			var fileURL = '';
			objectService.getContractPDF($scope.objectFull.GeneralInfo.ContractBarcode)
				.then(function (result) {

				var file = new Blob([result], { type: 'application/pdf' });
				var fileURL = $window.URL.createObjectURL(file);
				$window.open(fileURL);
			});
		}
		planService.getContractServiceTypes(identity).then(function (data){
			$scope.contractServiceTypes = data;
		});
		planService.getServiceItems(identity).then(function (data){
			$scope.serviceItems = data;
		});
		planService.getAnalysis(identity).then(function (list){
			$scope.analysisList = list;
		});
		$scope.yesNoSelectOptions = [{ name: 'Da', value: true }, { name: 'Ne', value: false }];
		$scope.monthlyRepeats = [1,2,3,4,5];

		planService.getPlanScheduleLevels(identity).then(function (list){
			$scope.planLevels = list;
		});
			
		$scope.plan = {};
		$scope.plan.department = {};
		$scope.plan.department.seasonal = { name: 'Ne', value: false };		
		$scope.plan.month = [
			{ Id: 1, Value: false, Name: 'Siječanj' },
		 	{ Id: 2, Value: false, Name: 'Veljača' },
			{ Id: 3, Value: false, Name: 'Ožujak' },
			{ Id: 4, Value: false, Name: 'Travanj' },
			{ Id: 5, Value: false, Name: 'Svibanj' },
			{ Id: 6, Value: false, Name: 'Lipanj' },
			{ Id: 7, Value: false, Name: 'Srpanj' },
			{ Id: 8, Value: false, Name: 'Kolovoz' },
			{ Id: 9, Value: false, Name: 'Rujan' },
			{ Id: 10, Value: false, Name: 'Listopad' },
			{ Id: 11, Value: false, Name: 'Studeni' },
			{ Id: 12, Value: false, Name: 'Prosinac' }
		];
		$scope.plan.fixedDates = [];
		$scope.plan.monthlyRepeats = $scope.monthlyRepeats[0];

		$scope.editGeneralInfo = function(){

			$scope.dialogEditObject = ngDialog.open({
				template: '../Template/Monitoring/ObjectEditGeneralInfoDialog.html',
				controller: 'objectEditGeneralInfoDialogController',
				data: { objectFull: $scope.objectFull },
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
		
		$scope.addMonitoring = function(departmentId){
			$scope.plan.planLevel = $scope.planLevels[0];
			$scope.plan.validFurther = $scope.yesNoSelectOptions[0];
			$scope.plan.fixedDates = [];

			if(departmentId != null) {
				$scope.departmentId = departmentId;
			}
			$scope.dialogMonitoring = ngDialog.open({
				template: '../Template/Monitoring/ObjectEntryAddMonitoringDialog.html',				
				className: 'ngdialog-theme-default extra-wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
				cache: false
			});
		}		
		$scope.saveMonitoring = function(){			
			$scope.dialogMonitoring = ngDialog.close();
			//Pozovi drugi dijalog za plan			
			$scope.addPlan();
		}		
		$scope.addPlan = function(){

			$scope.dialogPlan = ngDialog.open({
				template: '../Template/Monitoring/ObjectEntryAddPlanDialog.html',				
				className: 'ngdialog-theme-default extra-wide-dialog',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
				cache: false
			});
		}		
		$scope.backToMonitoring = function(){
			$scope.addMonitoring();
			$scope.dialogPlan = ngDialog.close();
		}
		//Fiksni datumi - CANCELED
		$scope.addToScheduleDatesList = function () {
			//Zabrani unos duplih fiksnih datuma, i ne više od 15 datuma
			if(!_.contains($scope.plan.fixedDates, $scope.plan.scheduleFixDate) && $scope.plan.fixedDates.length <= 15) {
				$scope.plan.fixedDates.push($scope.plan.scheduleFixDate);
			}
		}
		$scope.removeFromScheduleDatesList = function (index) {
			$scope.plan.fixedDates.splice(index, 1);
		}

		$scope.savePlan = function(){
			$scope.dialogPlan = ngDialog.close();
			$scope.plan.scheduleMonths = _.filter($scope.plan.month, function(month){ return month.Value == true; });
			objectService.saveObjectItemMonitoringAndPlan(identity, $scope.departmentId, $scope.plan).then(function(){
				//TO DO: export u neki template vanjski

				resetMonitoringAndPlanNgModels();
				$scope.getObjectFull();
				$ngBootbox.customDialog(optionsSuccess);
			});
		}
		
		$scope.deleteMonitoring = function(monitoringId){
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
								planService.deleteObjectItemMonitoringAndPlan(identity, monitoringId).then(function(success){
									$scope.getObjectFull();
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
		
		function resetMonitoringAndPlanNgModels(){
			$scope.plan.contractServiceType = null;
			$scope.plan.serviceItem = null;
			$scope.plan.analysis = null;
			$scope.plan.quantity = null;
			$scope.plan.description = null;
			$scope.plan.planLevel = null;
			$scope.plan.validFurther = null;
			$scope.plan.monthlyRepeats = 1;
			$scope.plan.endDate = null;
			$scope.plan.endDateFormatted = null;
			$scope.plan.scheduleMonths = null;
			var i;
			for(i=0; i<$scope.plan.month.length; i++)
			{
				$scope.plan.month[i].Value = false;
			}
		}		
		
		//Dodavanje odjela
		$scope.defaultDepartments = [
			'Delikatese',
			'Kuhinja',
			'Pekara'
		].sort();
		
		//Omogućavanje slobodnog unosa u ui-select
		$scope.getDepartmentNames = function(search){
			var newItems = $scope.defaultDepartments.slice();
			if (search && newItems.indexOf(search) === -1) {
				newItems.unshift(search);
			}
			return newItems;
		}
		
		$scope.addDepartment = function(){
			$scope.dialogDepartment = ngDialog.open({
				template: '../Template/Monitoring/ObjectEntryAddDepartmentDialog.html',				
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
		
		$scope.saveDepartment = function(){
			$scope.plan.department = {
				name: $scope.plan.department.name,
				seasonal: $scope.plan.department.seasonal.value,
				sublocation: $scope.plan.department.sublocation
			};
			
			objectService.saveObjectItem(identity, $state.params.objectId, $scope.plan.department).then(function(){
				//Vrati na defaultne vrijednosti
				$scope.plan.department.name = null;
				$scope.plan.department.seasonal = { name: 'Ne', value: false };
				$scope.plan.department.sublocation = null;
				
				$scope.getObjectFull();
				$ngBootbox.customDialog(optionsSuccess);
			});
			$scope.dialogDepartment = ngDialog.close();
		}
		
		//Brisanje odjela
		$scope.removeDepartment = function(departmentId){
			var optionsConfirm = {
				message: 'Jeste li sigurni da želite obrisati odjel?',
				buttons: {
					 warning: {
						 label: "Odustani",
						 className: "btn waves-effect waves-button waves-float waves-warning",
					 },
					 success: {
						 label: "Obriši",
						 className: "btn waves-effect waves-button waves-float waves-info",
						 callback: function() {
								objectService.deleteObjectItem(identity, departmentId).then(function(success){
									$scope.getObjectFull();
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

		//Edit monitoringa i plana
		$scope.monitoringAndPlanEditModel = {};
		$scope.editMonitoring = function (departmentIndex, monitoringIndex) {
			$scope.departmentIndex = departmentIndex;
			$scope.monitoringIndex = monitoringIndex;
			var monitoringAndPlan = angular.copy($scope.objectFull.Departments[$scope.departmentIndex].Monitorings[$scope.monitoringIndex]);

			//Load monitoring
			$scope.monitoringAndPlanEditModel.monitoringId = monitoringAndPlan.Id;
			$scope.monitoringAndPlanEditModel.contractServiceTypeId = monitoringAndPlan.ServiceTypeId;
			$scope.monitoringAndPlanEditModel.serviceItemId = monitoringAndPlan.ServiceItemId;
			$scope.monitoringAndPlanEditModel.analysis = monitoringAndPlan.Analysis;
			$scope.monitoringAndPlanEditModel.quantity = monitoringAndPlan.Quantity;
			$scope.monitoringAndPlanEditModel.description = monitoringAndPlan.Description;
			//Load plan
			$scope.monitoringAndPlanEditModel.planLevelId = monitoringAndPlan.ScheduleLevelId;
			$scope.monitoringAndPlanEditModel.planLevelEnumdescription = monitoringAndPlan.ScheduleLevelEnum;
			$scope.monitoringAndPlanEditModel.monthlyRepeats = monitoringAndPlan.MonthlyRepeats;
			$scope.monitoringAndPlanEditModel.validFurtherValue = monitoringAndPlan.ValidFurther;
			$scope.monitoringAndPlanEditModel.endDate = $filter('dateTimeFormat')(monitoringAndPlan.EndDate);
			$scope.monitoringAndPlanEditModel.endDateFormatted = monitoringAndPlan.EndDate;
			$scope.monitoringAndPlanEditModel.month = angular.copy($scope.plan.month);

			angular.forEach(monitoringAndPlan.ScheduleDates, function (value) {
				$scope.monitoringAndPlanEditModel.month[value.Month - 1].Value = true;
			});

			$scope.dialogEditMonitoring = ngDialog.open({
				template: '../Template/Monitoring/ObjectItemMonitoringEdit.html',
				className: 'ngdialog-theme-default extra-wide-dialog',
				closeByDocument: false,
				closeByEscape: true,
				showClose: true,
				trapFocus: true,
				preserveFocus: true,
				scope: $scope,
				cache: false
			});
		};
		$scope.saveMonitoringEdit = function(){
			$scope.dialogEditMonitoring = ngDialog.close();
			//Pozovi drugi dijalog za plan
			$scope.editPlan();
		};
		$scope.editPlan = function(){
			$scope.dialogPlanEdit = ngDialog.open({
				template: '../Template/Monitoring/ObjectItemPlanEdit.html',
				className: 'ngdialog-theme-default extra-wide-dialog',
				closeByDocument: false,
				closeByEscape: true,
				showClose: true,
				trapFocus: true,
				preserveFocus: true,
				scope: $scope,
				cache: false
			});
		};
		$scope.backToMonitoringEdit = function() {
			$scope.editMonitoring($scope.departmentIndex, $scope.monitoringIndex);
			$scope.dialogPlanEdit = ngDialog.close();
		};
		$scope.savePlanEdit = function() {
			$scope.monitoringAndPlanEditModel.analysisIds = _.pluck($scope.monitoringAndPlanEditModel.analysis, 'Id');
			$scope.monitoringAndPlanEditModel.monthIds = _.pluck(_.where($scope.monitoringAndPlanEditModel.month, { Value: true }), 'Id');
			$scope.dialogPlan = ngDialog.close();

			objectService.updateObjectMonitoringAndPlan(identity, $scope.monitoringAndPlanEditModel).then(function(){
				$scope.getObjectFull();
				$ngBootbox.customDialog(optionsSuccess);
			},
			function () {
				$ngBootbox.customDialog(optionsError);
			});
		};

		$scope.checkValidPlanDialog = function () {

			if (!$scope.plan.planLevel || !$scope.plan.validFurther) {
				return true;
			}
			if ($scope.plan.planLevel.enumdescription == 'MONTHLY' &&
				(!$scope.plan.month[0].Value && !$scope.plan.month[1].Value &&
				!$scope.plan.month[2].Value && !$scope.plan.month[3].Value &&
				!$scope.plan.month[4].Value && !$scope.plan.month[5].Value &&
				!$scope.plan.month[6].Value && !$scope.plan.month[7].Value &&
				!$scope.plan.month[8].Value && !$scope.plan.month[9].Value &&
				!$scope.plan.month[10].Value && !$scope.plan.month[11].Value)) {
				return true;
			}
			if ($scope.plan.planLevel.enumdescription == 'FIXED_DATES' && $scope.plan.fixedDates.length == 0) {
				return true;
			}

			return false;
		};

		$scope.checkValidPlanEditDialog = function () {

			if (!$scope.monitoringAndPlanEditModel.planLevelId || !$scope.monitoringAndPlanEditModel.validFurtherValue) {
				return true;
			}
			if ($scope.monitoringAndPlanEditModel.planLevelEnumdescription == 'MONTHLY' &&
				(!$scope.monitoringAndPlanEditModel.month[0].Value && !$scope.monitoringAndPlanEditModel.month[1].Value &&
				!$scope.monitoringAndPlanEditModel.month[2].Value && !$scope.monitoringAndPlanEditModel.month[3].Value &&
				!$scope.monitoringAndPlanEditModel.month[4].Value && !$scope.monitoringAndPlanEditModel.month[5].Value &&
				!$scope.monitoringAndPlanEditModel.month[6].Value && !$scope.monitoringAndPlanEditModel.month[7].Value &&
				!$scope.monitoringAndPlanEditModel.month[8].Value && !$scope.monitoringAndPlanEditModel.month[9].Value &&
				!$scope.monitoringAndPlanEditModel.month[10].Value && !$scope.monitoringAndPlanEditModel.month[11].Value)) {
				return true;
			}
			//NOT IN USE
			// if ($scope.monitoringAndPlanEditModel.planLevelEnumdescription == 'FIXED_DATES' && $scope.plan.fixedDates.length == 0) {
			// 	return true;
			// }

			return false;
		};

		$scope.updateObjectItemMonitoringStatus = function (monitoringId, isActive) {
			var message = isActive == true ? 'deaktivirati' : 'aktivirati';
			var optionsConfirm = {
				message: 'Jeste li sigurni da želite ' + message + ' plan?',
				buttons: {
					warning: {
						label: "Odustani",
						className: "btn waves-effect waves-button waves-float waves-warning",
					},
					success: {
						label: "Potvrdi",
						className: "btn waves-effect waves-button waves-float waves-info",
						callback: function() {
							planService.updateObjectItemMonitoringStatus(monitoringId).then(function(){
									$scope.getObjectFull();
									$ngBootbox.customDialog(optionsSuccess);
								},
								function () {
									$ngBootbox.customDialog(optionsError);
								});
						}
					}
				}
			};
			$ngBootbox.customDialog(optionsConfirm);
		};

		//TODO staviti u zajednički file
		var optionsSuccess = {
			message: 'Uspješno spremljeno.',
			buttons: {
				success: {
					label: "Ok",
					className: "btn waves-effect waves-button waves-float waves-info",
				}
			}
		}

		var optionsError = {
			message: 'Došlo je do greške!',
			buttons: {
				success: {
					label: "Ok",
					className: "btn waves-effect waves-button waves-float waves-danger",
				}
			}
		}
	}
]);