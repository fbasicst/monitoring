'use strict'

webApp.controller("logInController", ['$scope', '$state', 'authorizationService', 'identityService',
	function($scope, $state, authorizationService, identityService){
		
		$scope.credentials = {};			
		$scope.failedLogIn = false;
		
		$scope.logIn = function (){
			$scope.failedLogIn = false;
			authorizationService.logIn($scope.credentials).then(function (userData){
				identityService.setIdentity(userData);
				$state.go('monitoring.dashboard');
			},
			function() {
				$scope.failedLogIn = true;
			});
		}
	}
]);