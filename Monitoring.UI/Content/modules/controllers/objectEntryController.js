'use strict'

webApp.controller('objectEntryController', ['$scope', '$window', 'identityService', 'objectService', 'ngDialog', 'masterDataService', 'planService', '$ngBootbox', 
	function($scope, $window, identityService, objectService, ngDialog, masterDataService, planService, $ngBootbox) {
		
		var identity = identityService.getIdentity();
		$scope.step = 1;
        $scope.setStep = function(step){
           $scope.step = step;
        }
		$scope.legal = {};		
		$scope.generalInfo = {};		
		$scope.otherInfo = {};
		$scope.yesNoSelectOptions = [{ name: 'Da', value: true }, { name: 'Ne', value: false }];
		$scope.monthlyRepeats = [1,2,3,4,5];

		$scope.plan = {};
		$scope.plan.department = [];
		$scope.plan.department.seasonal = $scope.yesNoSelectOptions[1];

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
		
		$scope.searchCustomers = function(searchString) {
			if(searchString.length > 3){
				objectService.getCustomers(identity, searchString).then(function (data) {
					$scope.customers = data;
				});
			}
		}

		masterDataService.getCities(identity).then(function (data){
			$scope.cities = data;
		});
		masterDataService.getObjectAreas(identity).then(function (data){
			$scope.areas = data;
		});
		objectService.getObjectTypes(identity).then(function (types) {
			$scope.objectTypes = types;
		});
		planService.getContractServiceTypes(identity).then(function (data){
			$scope.contractServiceTypes = data;
		});
		planService.getServiceItems(identity).then(function (data){
			$scope.serviceItems = data;
		});
		planService.getAnalysis(identity).then(function (list){
			$scope.analysisList = list;
		});
		planService.getPlanScheduleLevels(identity).then(function (list){
			$scope.planLevels = list;
		});
		
		$scope.getContracts = function(customerId){
			//očistiti ui-select ugovora
			//if($scope.legal.contract != null ){
			//	console.log($scope.legal.contract);
			//}
			$scope.legal.contract = undefined;
			
			objectService.getContracts(identity, customerId).then(function (data){
				$scope.contracts = data;
			});
		}
		
		$scope.showPDF = function(){
			var fileURL = '';				
			objectService.getContractPDF($scope.legal.contract.Barcode)
				.then(function (result) {
					
					var file = new Blob([result], { type: 'application/pdf' });
					var fileURL = $window.URL.createObjectURL(file);
					$window.open(fileURL);
			});
			
			/* temp canceled
			$scope.dialogPdf = ngDialog.open({
				template: '../Template/Monitoring/PdfContractDialog.html',				
				className: 'ngdialog-theme-default',
                closeByDocument: false,
                closeByEscape: true,
                showClose: true,
                trapFocus: true,
                preserveFocus: true,
                scope: $scope,
			 	cache: false
			});*/
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
                scope: $scope
			});
		}
		
		$scope.saveDepartment = function(){
			$scope.plan.department.push({
				name: $scope.plan.department.name,
				seasonal: $scope.plan.department.seasonal.value,
				sublocation: $scope.plan.department.sublocation
			});
			//Vrati na defaultne vrijednosti
			$scope.plan.department.name = null;
			$scope.plan.department.seasonal = { name: 'Ne', value: false };
			$scope.plan.department.sublocation = null;
			$scope.dialogDepartment = ngDialog.close();
		}
		
		$scope.removeDepartment = function(item){
			var index = $scope.plan.department.indexOf(item);
			$scope.plan.department.splice(index, 1);			
		}
		
		$scope.removePlan = function(subitem, item){
			var itemIndex = $scope.plan.department.indexOf(item);
			var subitemIndex = $scope.plan.department[itemIndex].monitoring.indexOf(subitem);
			$scope.plan.department[itemIndex].monitoring.splice(subitemIndex, 1);
		}
		
		$scope.addMonitoring = function(index){
			$scope.index = index;
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

		$scope.saveMonitoring = function(index){			
			$scope.dialogMonitoring = ngDialog.close();
			//Pozovi drugi dijalog za plan			
			$scope.addPlan(index);
		}
		
		$scope.addPlan = function(index){
			$scope.index = index;
			$scope.plan.planLevel = $scope.planLevels[0];
			$scope.plan.validFurther = $scope.yesNoSelectOptions[0];
			$scope.plan.fixedDates = [];
			$scope.plan.monthlyRepeats = $scope.monthlyRepeats[0];
			
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
		
		$scope.backToMonitoring = function(index){
			$scope.addMonitoring(index);
			$scope.dialogPlan = ngDialog.close();
		}
		//Fiksni datumi
		$scope.addToScheduleDatesList = function () {
			//Zabrani unos duplih fiksnih datuma, i ne više od 15 datuma
			if(!_.contains($scope.plan.fixedDates, $scope.plan.scheduleFixDate) && $scope.plan.fixedDates.length <= 15) {
				$scope.plan.fixedDates.push($scope.plan.scheduleFixDate);
			}
		}
		$scope.removeFromScheduleDatesList = function (index) {
			$scope.plan.fixedDates.splice(index, 1);
		}
		
		$scope.savePlan = function(index){
			//save monitoring
			if($scope.plan.department[index].monitoring == undefined){
				$scope.plan.department[index].monitoring = [];
			}
			if($scope.plan.validFurther.value == true){
				$scope.plan.endDate = null;
				$scope.plan.endDateFormatted = null;
			}
			$scope.plan.department[index].monitoring.push({
				contractServiceType: $scope.plan.contractServiceType,
				serviceItem: $scope.plan.serviceItem,
				analysis: $scope.plan.analysis,
				quantity: $scope.plan.quantity,
				description: $scope.plan.description,
				
				level: $scope.plan.planLevel,
				months: _.where($scope.plan.month, { Value: true }),
				monthlyRepeats: $scope.plan.monthlyRepeats,
				fixedDates: $scope.plan.fixedDates,
				validFurther: $scope.plan.validFurther.value,
				endDate: $scope.plan.endDateFormatted
			});
			//save plan
			if($scope.plan.department[index].subplan == undefined){
				$scope.plan.department[index].subplan = [];
			}

			//Resetiranje polja u dijalozima: monitoringu i planu
			resetMonitoringAndPlanNgModels();
			$scope.dialogPlan = ngDialog.close();
		}
		
		//TO DO: export u neki template vanjski
		var optionsSuccess = {
			message: 'Uspješno spremljeno.',
			buttons: {
				 success: {
					 label: "Ok",
					 className: "btn waves-effect waves-button waves-float waves-info",											 
				}
			}
		}
		
		$scope.saveObject = function(){
			var optionsConfirm = {
				message: 'Spremiti objekt?',
				buttons: {
					 warning: {
						 label: "Odustani",
						 className: "btn waves-effect waves-button waves-float waves-warning",
					 },
					 saveAndNew: {
						 label: "Spremi i novi",
						 className: "btn waves-effect waves-button waves-float waves-info",
						 callback: function() { 
							objectService.saveObject(identity, $scope.legal, $scope.generalInfo, $scope.plan, $scope.otherInfo).then(function(success) {
									$ngBootbox.customDialog(optionsSuccess);
									
									//Spremi objekt i očisti sve unešeno
									//u formama
									$scope.legal = {};
									$scope.generalInfo = {};
									$scope.otherInfo = {};
									$scope.plan = {};
									$scope.plan.department = [];
									$scope.plan.department.seasonal = $scope.yesNoSelectOptions[1];
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
									$scope.plan.monthlyRepeats = $scope.monthlyRepeats[0];
									$scope.plan.validFurther = $scope.yesNoSelectOptions[0];
									
									$scope.setStep(1);									
								});								
							}
						},
					 saveAndKeep: {
						 label: "Spremi i zadrži",
						 className: "btn waves-effect waves-button waves-float waves-info",
						 callback: function() { 
							objectService.saveObject(identity, $scope.legal, $scope.generalInfo, $scope.plan, $scope.otherInfo).then(function(success) {
									$ngBootbox.customDialog(optionsSuccess);
									
									//Spremi objekt i očisti sve unešeno, osim Pravno i Opće informacije, I ostale info
									//u formama
									$scope.plan = {};
									$scope.plan.department = [];
									$scope.plan.department.seasonal = $scope.yesNoSelectOptions[1];
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
									$scope.plan.monthlyRepeats = $scope.monthlyRepeats[0];
									$scope.plan.validFurther = $scope.yesNoSelectOptions[0];
									
									$scope.setStep(1);									
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
			$scope.plan.fixedDates = [];
			$scope.plan.monthlyRepeats = 1;
			$scope.plan.validFurther = null;
			$scope.plan.endDate = null;
			$scope.plan.endDateFormatted = null;
			var i;
			for(i=0; i<$scope.plan.month.length; i++)
			{
				$scope.plan.month[i].Value = false;
			}
		}

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
		}
	}
]);







