'use strict'

webApp.factory('planService', ['$q', '$http', 
	function($q, $http){
		
		return {
			getContractServiceTypes: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetContractServiceTypesQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getServiceItems: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetServiceItemsQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getAnalysis: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetAnalysisListQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getPlanScheduleLevels: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanScheduleLevelsListQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			deleteObjectItemMonitoringAndPlan: function(identity, monitoringId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					MonitoringId: monitoringId
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Monitoring/DeleteObjectItemMonitoringAndPlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			// Not in use
			getPlanLevels: function(identity) {	

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanLevelsListQueryHandler.php", identity)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			savePlanWeekly: function(identity, plan) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					AttachedUserIds: plan.attachedUserIds,
					CreationDate: plan.creationDate,
					ExpirationDate: plan.expirationDate,
					Label: plan.label,
					UserIdControlled: plan.userControlled.UserId
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/SavePlanWeeklyCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getPlansWeeklyListLazy: function(identity, query, filter) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,

					StartFrom: query.startFrom,
					Count: query.count,
					OrderType: query.orderType,
					Search: query.search,

					Month: filter.month,
					Year: filter.year,
					PlanUserId: filter.planUser ? filter.planUser.UserId : null,
					ObjectsAmount: filter.objectsAmount
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlansWeeklyListLazyQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getObjectItemPlansListLazy: function(identity, query, filter, planWeeklyId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					StartFrom: query.startFrom,
					Count: query.count,
					OrderType: query.orderType,
					Search: query.search,

					PlanId: planWeeklyId,

					AreaId: filter.area ? filter.area.Id : null,
					CityId: filter.city ? filter.city.Id : null,
					ObjectTypeId: filter.objectType ? filter.objectType.Id : null,
					ContractServiceTypeId: filter.contractServiceType ? filter.contractServiceType.Id : null,
					ServiceItemId: filter.serviceItem ? filter.serviceItem.ServiceItemId : null,
					AnalysisId: filter.analysis ? filter.analysis.Id : null
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetObjectsForTransferListLazyQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getPlanWeeklyInfo: function(identity, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanWeeklyInfoQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			saveObjectToPlanWeekly: function(identity, transferData) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanWeeklyId: transferData.planWeeklyId,
					ScheduleDate: transferData.scheduleDateFormatted,
					SelectedObjectId: transferData.selectedObjectId,
					Notes: transferData.notes
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/SaveObjectToPlanWeeklyCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getPlanWeeklyItemsList: function(identity, planWeeklyId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanWeeklyId: planWeeklyId
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanWeeklyItemsListQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getPlanStatusesList: function(identity) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanStatusesListQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			updatePlanWeeklyItemsStatuses: function(identity, planId, planStatus) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId,
					PlanStatusId: planStatus.status.Id,
					PlanStatusEnum: planStatus.status.EnumDescription,
					LockPlan: planStatus.lockPlan
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/UpdatePlanWeeklyItemsStatusesCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			updatePlanItemWeeklyStatus: function(identity, planItemId, planStatusId, finishNotes, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanItemId: planItemId,
					PlanStatusId: planStatusId,
					PlanStatusFinishNotes: finishNotes,
					PlanId: planId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/UpdatePlanItemWeeklyStatusCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			deletePlanWeekly: function(identity, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/DeletePlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			lockPlanWeekly: function(identity, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/LockPlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			deleteObjectFromPlan: function(identity, planItemId, objectId, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId,
					ObjectId: objectId,
					PlanItemId: planItemId
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/DeleteObjectFromPlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			printPlanWeeklyPdf: function (identity, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanPdfQueryHandler.php", requestObject, { responseType: 'arraybuffer' })
					.success(function (response) { deferred.resolve(response); })
					.error(function (err, status) { deferred.reject(err); });

				return deferred.promise;
			},

			getPlanItemDetailsFull: function(identity, planItemId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanItemId: planItemId
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanItemDetailsFullQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			syncPlanToWeb: function(identity, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/SyncPlanToWebCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			syncPlanFromCloud: function(identity, planId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					PlanId: planId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/SyncPlanFromCloudCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			saveObjectsGroupToPlan: function(identity, objectIdsList, transferData) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					ObjectIdsList: objectIdsList,
					PlanWeeklyId: transferData.planWeeklyId,
					ScheduleDate: transferData.scheduleDateFormatted,
					Notes: transferData.notes
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Plan/SaveObjectsGroupToPlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getObjectsForPlanMonthlyLazy: function(identity, query, filter, areaIds) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					StartFrom: query.startFrom,
					Count: query.count,
					OrderType: query.orderType,
					Search: query.search,
					Year: filter.year,
					Month: filter.month,
					AreaIds: areaIds
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetObjectsForPlanMonthlyLazyQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getObjectsForPlanAnnuallyLazy: function(identity, query, filter, areaIds) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					StartFrom: query.startFrom,
					Count: query.count,
					OrderType: query.orderType,
					Search: query.search,
					Year: filter.year,
					AreaIds: areaIds
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetObjectsForPlanAnnuallyLazyQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			printPlanMonthlyPdf: function (identity, filter, areaIds) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					Year: filter.year,
					Month: filter.month,
					AreaIds: areaIds
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanMonthlyPdfQueryHandler.php", requestObject, { responseType: 'arraybuffer' })
					.success(function (response) { deferred.resolve(response); })
					.error(function (err, status) { deferred.reject(err); });

				return deferred.promise;
			},

			printPlanAnnuallyPdf: function (identity, filter, areaIds) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					Year: filter.year,
					Month: filter.month,
					AreaIds: areaIds
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Plan/GetPlanAnnuallyPdfQueryHandler.php", requestObject, { responseType: 'arraybuffer' })
					.success(function (response) { deferred.resolve(response); })
					.error(function (err, status) { deferred.reject(err); });

				return deferred.promise;
			},

			updateObjectItemMonitoringStatus: function (objectItemMonitoringId) {
				var requestObject = {
					ObjectItemMonitoringId: objectItemMonitoringId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/UpdateObjectItemMonitoringStatusCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (err, status) { deferred.reject(err); });

				return deferred.promise;
			}
		}
	}
]);