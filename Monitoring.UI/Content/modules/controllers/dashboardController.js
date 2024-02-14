'user strict'

webApp.controller('dashboardController', ['$scope', 'identityService', 'masterDataService',
	function($scope, identityService, masterDataService){
		
		var identity = identityService.getIdentity();
	
		masterDataService.getObjectsSavedAmount(identity).then(function(data){
			$scope.chart = {};
			$scope.chart.data = _.pluck(data, 'ObjectsAmount');
			$scope.chart.labels = _.pluck(data, 'ObjectTypeName');
			$scope.chart.colours = ['#72C02C', '#3498DB', '#717984', '#F1C40F'];

			$scope.chart.options = {
				tooltipTemplate: function (item) {
					return item.label;
				},                                                
				responsive: true,
				maintainAspectRatio: false,
				legend: {
					display: true,
					position: "bottom"
				}		
			}
		});

		masterDataService.getObjectsSavedByUser(identity).then(function (data) {

			$scope.lineChart = {};

			$scope.lineChart.labels = data.DatesListFormatted;
			$scope.lineChart.series =  _.pluck(data.UsersList, 'UserFullName');
			$scope.lineChart.data = data.ObjectsAmountsList;

			$scope.lineChart.options = {
				responsive: true,
				maintainAspectRatio: true,
				legend: {
					display: true,
					position: "bottom"
				}
			}
		});

		var year = moment().format('YYYY');
		var month = moment().format('M');
		masterDataService.getObjectsCompletedByUser(month, year).then(function (data) {
			$scope.userCompletedObjects = _.sortBy(data, 'Percentage');
		});
	}
]);