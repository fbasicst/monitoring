'use strict'

webApp.factory('identityService', ['$q', 'localStorageService',
	function ($q, localStorageService) {
		
		return {

			setIdentity: function(userData) {
				localStorageService.set('identityData', userData);
			},
			
			getIdentity: function() {
				return localStorageService.get('identityData');
			},
			
			unsetIdentity: function() {
				localStorageService.remove('identityData');
			}		
		}
	}
]);