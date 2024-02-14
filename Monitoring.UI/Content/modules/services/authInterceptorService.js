'use strict'

webApp.factory('authInterceptorService', ['$q', 'identityService',
	function($q, identityService){

		return {
			
			request: function(config){
				var identity = identityService.getIdentity();
				if(identity)
					config.headers.AuthorizationToken = identity.Token ;

				return config;
			}
			
		};	
	}
]);