'use strict';

webApp.factory('reportService', ['$q', '$http',
    function($q, $http){

        return {
            getCompletedServiceItems: function(identity, filter) {
                var requestObject = {
                    UserId: identity.UserId,
                    Environment: identity.Environment,
                    AreaIds: filter.areaIds,
                    Month: filter.month,
                    Year: filter.year,
                    AnalysisIds: filter.analysisIds
                };

                var deferred = $q.defer();
                $http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Report/GetReportServiceItemsQueryHandler.php", requestObject)
                    .success(function (response) { deferred.resolve(response); })
                    .error(function (error) { deferred.reject(error); });

                return deferred.promise;
            },
        }
    }
]);