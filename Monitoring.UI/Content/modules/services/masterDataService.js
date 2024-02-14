'use strict'

webApp.factory('masterDataService', ['$q', '$http', 
	function($q, $http){
		
		return {
			getObjectsSavedAmount: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/MasterData/GetObjectsSavedAmountQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getCities: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/MasterData/GetCitiesQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getObjectAreas: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();
				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/MasterData/GetObjectAreasQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getUsersFromRole: function(identity, roleName) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					RoleName: roleName
				};

				var deferred = $q.defer();
				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/MasterData/GetUsersFromRoleQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getObjectsSavedByUser: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				}

				var deferred = $q.defer();

				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/MasterData/GetRecentSavedObjectsByUserQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getObjectsCompletedByUser: function(month, year) {
				var requestObject = {
					Month: month,
					Year: year
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/MasterData/GetCompletedObjectsByUserQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
		}
	}]
);