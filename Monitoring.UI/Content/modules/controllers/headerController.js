'use strict'

webApp.controller('headerController', ['$scope', '$state', 'identityService',
	function($scope, $state, identityService){
		
		var identity = identityService.getIdentity();
		
		$scope.firstName = identity.FirstName;
		$scope.lastName = identity.LastName;
		
		$scope.logOut = function(){
			identityService.unsetIdentity();
			$state.go('logIn');
		}
	}
]);