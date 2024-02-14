'use strict'

webApp.factory('authorizationService', ['$http', '$q', function ($http, $q) {

	return {

		logIn: function (credentials) {

			if (!credentials || !(credentials.username && credentials.password))
				return $q.reject();

			var requestObject = {
				UserName: credentials.username,
				Password: credentials.password
			};

			var deferred = $q.defer();
			$http.post("../../../Monitoring.API/Authorization/LogInHandler.php", requestObject)
				.success(function (response) { deferred.resolve(response); })
				.error(function (error) { deferred.reject(error); });

			return deferred.promise;
		}

	};
}]);