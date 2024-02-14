'use strict'

webApp.factory('objectService', ['$q', '$http', 
	function($q, $http){
		
		return {
			getCustomers: function(identity, searchString) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					SearchString: searchString
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/RemoteData/GetCustomersQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })				
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getContracts: function(identity, customerId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					CustomerId: customerId
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/RemoteData/GetContractsQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })		
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getContractPDF: function (contractBarcode) {

				var deferred = $q.defer();
				$http.get("../../../Monitoring.API/QueryHandlers/QueryHandlers/RemoteData/GetContractPdfQueryHandler.php?contractBarcode=" + contractBarcode, { responseType: 'arraybuffer' })
					.success(function (response) { deferred.resolve(response); })
					.error(function (err, status) { deferred.reject(err); });

				return deferred.promise;
			},
			
			getObjectTypes: function(identity) {
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Object/GetObjectTypesQueryHandler.php", identity)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			saveObject: function(identity, legal, generalInfo, plan, otherInfo) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					CustomerRemoteId: legal.customer.RemoteId,
					ContractBarcode: legal.contract ? legal.contract.Barcode : null,
					Name: generalInfo.name,
					StreetName: generalInfo.streetName,
					StreetNumber: generalInfo.streetNumber,
					CityId: generalInfo.city.Id,
					AreaId: generalInfo.area.Id,
					ObjectTypeId: generalInfo.objectType.Id,
					ContactPerson: otherInfo.contactPerson ? otherInfo.contactPerson : null,
					ContactPhone: otherInfo.contactPhone ? otherInfo.contactPhone : null,
					ContactMail: otherInfo.contactMail ? otherInfo.contactMail : null,
					Notes: otherInfo.notes ? otherInfo.notes : null,
					Departments: plan.department
				};

				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/SaveObjectCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			getObjectsListLazy: function(identity, query, isActive) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					StartFrom: query.startFrom,
					Count: query.count,
					OrderType: query.orderType,
					Search: query.search,
					IsActive: isActive
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Object/GetObjectsListLazyQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getObjectFull: function(identity, objectId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					ObjectId: objectId
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Object/GetObjectFullQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			saveObjectItemMonitoringAndPlan: function(identity, objectItemId, monitoringAndPlan) {				
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					ObjectItemId: objectItemId,
					ContractServiceTypeId: monitoringAndPlan.contractServiceType.Id,
					ServiceItemId: monitoringAndPlan.serviceItem.ServiceItemId,
					Quantity: monitoringAndPlan.quantity,
					Description: monitoringAndPlan.description,
					ScheduleLevelId: monitoringAndPlan.planLevel.value,
					MonthlyRepeatsCount: monitoringAndPlan.monthlyRepeats,
					IsValidFurther: monitoringAndPlan.validFurther.value,
					EndDate: monitoringAndPlan.endDateFormatted,
					Analysis: monitoringAndPlan.analysis,
					ScheduleMonths: monitoringAndPlan.scheduleMonths
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/SaveObjectItemMonitoringAndPlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			saveObjectItem: function(identity, objectId, department) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					ObjectId: objectId,
					Name: department.name,
					IsSeasonal: department.seasonal,
					LocationDescription: department.sublocation
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/SaveObjectItemCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
			
			deleteObjectItem: function(identity, objectItemId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					ObjectItemId: objectItemId
				};
				
				var deferred = $q.defer();				
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/DeleteObjectItemCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			getObjectForTransferDetails: function(identity, objectId) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,
					ObjectId: objectId
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/QueryHandlers/QueryHandlers/Object/GetObjectFullQueryHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			updateObjectGeneralInfo: function(identity, objectEditData) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,

					ObjectId: objectEditData.ObjectId,
					CustomerId: objectEditData.CustomerId,
					ContractBarcode: objectEditData.ContractBarcode,
					ObjectName: objectEditData.ObjectName,
					ObjectTypeId: objectEditData.ObjectTypeId,
					ObjectAreaId: objectEditData.ObjectAreaId,
					ObjectStreetName: objectEditData.ObjectStreetName,
					ObjectStreetNumber: objectEditData.ObjectStreetNumber,
					ObjectCityId: objectEditData.ObjectCityId,
					ContactPersonName: objectEditData.ContactPersonName,
					ContactPersonPhone: objectEditData.ContactPersonPhone,
					ContactPersonMail: objectEditData.ContactPersonMail,
					Notes: objectEditData.Notes,
					IsActive: objectEditData.IsActive
				};

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/UpdateObjectGeneralInfoCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},

			updateObjectMonitoringAndPlan: function(identity, monitoringEditData) {
				var requestObject = {
					UserId: identity.UserId,
					Environment: identity.Environment,

					ObjectItemMonitoringId: monitoringEditData.monitoringId,
					ContractServiceTypeId: monitoringEditData.contractServiceTypeId,
					ServiceItemId: monitoringEditData.serviceItemId,
					AnalysisIds: monitoringEditData.analysisIds,
					Quantity: monitoringEditData.quantity,
					Description: monitoringEditData.description,
					PlanLevelId: monitoringEditData.planLevelId,
					MonthlyRepeats: monitoringEditData.monthlyRepeats,
					ValudFurther: monitoringEditData.validFurtherValue,
					EndDate: monitoringEditData.endDateFormatted,
					Months: monitoringEditData.monthIds
				}

				var deferred = $q.defer();
				$http.post("../../../Monitoring.API/CommandHandlers/CommandHandlers/Object/UpdateObjectItemMonitoringAndPlanCommandHandler.php", requestObject)
					.success(function (response) { deferred.resolve(response); })
					.error(function (error) { deferred.reject(error); });

				return deferred.promise;
			},
		}
	}
]);