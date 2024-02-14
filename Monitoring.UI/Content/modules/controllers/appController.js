'use strict'

webApp.controller("appController", ['$scope',
	function($scope) {

		$scope.initWaves=function() {
			Waves.displayEffect()
		}
	}
]);