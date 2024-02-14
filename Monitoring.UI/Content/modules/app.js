'use strict';

var webApp = angular.module('webApp', ['ui.router', 'LocalStorageModule', 'ngAnimate', 'angular-loading-bar', 'datatables', 
'ngSanitize', 'ui.select', 'ngDialog', 'ngBootbox', 'chart.js']);

webApp.config(['$stateProvider', '$urlRouterProvider', '$httpProvider',
	function($stateProvider, $urlRouterProvider, $httpProvider){
    
		$urlRouterProvider.otherwise('/monitoring');
		
		$stateProvider			
			.state("logIn", {
				url:"/",
				templateUrl: "../../views/template/logIn.html",
				publicAccess: true
			})
			.state('monitoring', {
				url: '/monitoring',
				views: {
					'': { templateUrl: '../../views/template/layout.html' },
					'main@monitoring': { templateUrl: '../../views/template/monitoring/main.html' },
					'menu@monitoring': { templateUrl: '../../views/template/monitoring/menu.html' },
					'header@monitoring': { templateUrl: '../../views/template/monitoring/header.html' }                
				}
			})
			.state("monitoring.dashboard", {
				url:"/dashboard",
				templateUrl: "../../views/template/monitoring/dashboard.html"		
			})
			.state("monitoring.objectEntry", {
				url:"/objectEntry",
				templateUrl: "../../views/template/monitoring/objectEntry.html"
			})
			.state("monitoring.objectList", {
				url:"/objectList",
				templateUrl: "../../views/template/monitoring/objects.html"
			})
			.state("monitoring.archivedObjectList", {
				url:"/archivedObjectList",
				templateUrl: "../../views/template/monitoring/objectsArchived.html"
			})
			.state("monitoring.objectedit", {
				url:"/objectList/{status}/{objectId}",
				templateUrl: "../../views/template/monitoring/objectEdit.html"
			})
			.state("monitoring.plansWeekly", {
				url:"/plansWeekly",
				templateUrl: "../../views/template/monitoring/plansWeekly.html"
			})
			.state("monitoring.planWeeklyEdit", {
				url:"/plansWeekly/{planId}",
				templateUrl: "../../views/template/monitoring/planWeeklyEdit.html"
			})
			.state("monitoring.plansMonthly", {
				url:"/plansMonthly",
				templateUrl: "../../views/template/monitoring/plansMonthly.html"
			})
			.state("monitoring.plansAnnually", {
				url:"/plansAnnually",
				templateUrl: "../../views/template/monitoring/plansAnnually.html"
			})
			.state("monitoring.reportServiceItems", {
				url:"/reportServiceItems",
				templateUrl: "../../views/template/monitoring/reportServiceItems.html"
			})
			;			
			
		$httpProvider.interceptors.push('authInterceptorService');

}]);