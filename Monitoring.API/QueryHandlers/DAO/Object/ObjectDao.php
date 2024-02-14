<?php
	namespace API\QueryHandlers\DAO\Object;
	
	class ObjectDao
	{
		public function GetObjectTypes()
		{
			$query = "SELECT OT.id AS Id,
							 OT.name AS Name 
					  FROM objecttypes OT
					  ORDER BY OT.Name ASC";

			return $query;
		}
		
		public function GetObjectsListLazy($startFrom, $count, $orderType, $search, $isActive)
		{
			$query = "SELECT O.name AS ObjectName,
							 O.id AS ObjectId,
							 CU.name AS CustomerName,
							 CO.barcode AS ContractBarcode, 
							 O.streetname AS ObjectStreetName,
							 O.streetnumber AS ObjectStreetNumber,
							 CI.name AS ObjectCityName
					  FROM objects O
					  INNER JOIN customers CU ON CU.id = O.customerid 
					  LEFT JOIN contracts CO ON CO.id = O.contractid 
					  INNER JOIN cities CI ON CI.id = O.cityid
					  WHERE O.active = $isActive 
					  		AND (O.name LIKE '%".$search."%' 
							OR CU.name LIKE '%".$search."%' 
							OR CO.barcode LIKE '%".$search."%')
					  LIMIT ".$startFrom.", ".$count.";";

			return $query;
		}
		
		public function GetObjectsListLazyCount($search, $isActive)
		{
			$query = "SELECT COUNT(*)
					  FROM objects O
					  INNER JOIN customers CU ON CU.id = O.customerid 
					  LEFT JOIN contracts CO ON CO.id = O.contractid 
					  INNER JOIN cities CI ON CI.id = O.cityid
					  WHERE O.active = $isActive 
					  		AND (O.name LIKE '%".$search."%' OR CU.name LIKE '%".$search."%' OR CO.barcode LIKE '%".$search."%')";

			return $query;
		}
		
		public function GetObjectFull($objectId)
		{
			$query = "SELECT O.name AS ObjectName,
							 O.id AS ObjectId,
							 O.streetname AS ObjectStreetName,
							 O.streetnumber AS ObjectStreetNumber,
							 CI.name AS ObjectCityName,
							 CI.id AS ObjectCityId,
							 CI.postalcode AS PostalCode,
							 CI.post AS PostName,
							 OT.name AS ObjectTypeName,
							 OT.id AS ObjectTypeId,
							 OA.name AS ObjectAreaName,
							 OA.id AS ObjectAreaId,
							 O.contactpersonname AS ContactPersonName,
							 O.contactpersonphone AS ContactPersonPhone,
							 O.contactpersonemail AS ContactPersonMail,
							 O.notes AS Notes,
							 O.active AS IsActive,
							 
							 CU.id AS CustomerId,
							 CU.remoteid AS CustomerRemoteId,
							 CU.name AS CustomerName,
							 CU.name AS CustomerName,
							 CU.oib AS CustomerOib,
							 CU.address AS CustomerAddress,
						 	 CU.streetnumber AS CustomerStreetNumber,
							 CU.postalcode AS CustomerPostalCode,
							 CU.postname AS CustomerPostName,
							 
							 CO.barcode AS ContractBarcode,
							 CO.remoteid AS ContractRemoteId
					  FROM objects O
					  INNER JOIN customers CU ON CU.id = O.customerid 
					  LEFT JOIN contracts CO ON CO.id = O.contractid 
					  INNER JOIN cities CI ON CI.id = O.cityid
					  INNER JOIN objecttypes OT ON OT.id = O.objecttypeid 
					  INNER JOIN areas OA ON OA.id = O.areaid 
					  WHERE O.id = ".$objectId.";";

			return $query;
		}
		
		public function GetObjectItems($objectId)
		{
			$query = "SELECT OI.id AS Id,
							 OI.name AS Name,
							 OI.seasonal AS Seasonal,
							 OI.sublocation AS LocationDescription
					  FROM objectitems OI
					  WHERE OI.objectid = ".$objectId." 
					  ORDER BY OI.id ASC;";

			return $query;
		}
		
		public function GetObjectItemMonitoringsAndPlans($objectItemId)
		{
			$query = "SELECT OIM.id AS Id,
							 OIM.quantity AS Quantity,
							 OIM.description AS Description,
							 OIM.active AS IsActive,
							 CST.statusname AS ServiceTypeName,
							 CST.id AS ServiceTypeId,
							 SI.name AS ServiceItemName,
							 SI.id AS ServiceItemId,
							 S.name AS ServiceName,
							 OIP.validfurther AS ValidFurther,
							 OIP.monthlyrepeats AS MonthlyRepeats,
							 OIP.enddate AS EndDate,
							 SL.enumdescription AS ScheduleLevelEnum,
							 SL.id AS ScheduleLevelId,
							 OIP.id AS PlanId,
							 (SELECT COUNT(*) FROM planitemobjectitemmonitorings PIOIM WHERE PIOIM.objectitemmonitoringid = OIM.id) AS MonitoringCopiedCount

					  FROM objectitemmonitorings OIM
					  INNER JOIN contractservicetype CST ON CST.id = OIM.contractservicetypeid
					  INNER JOIN serviceitems SI ON SI.id = OIM.serviceitemid 
					  INNER JOIN services S ON S.id = SI.serviceid 
					  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id 
					  INNER JOIN schedulelevels SL ON SL.id = OIP.schedulelevelid 
					  WHERE OIM.objectitemid = ".$objectItemId." 
					  ORDER BY OIM.id ASC;";
			
			return $query;
		}
		
		public function GetObjectItemMonitoringAnalysis($objectItemMonitoringId)
		{
			$query = "SELECT A.name AS Name, 
							 A.id AS Id
					  FROM objectitemmonitoringanalysisrel OIMAR
					  INNER JOIN analysis A ON A.id = OIMAR.analysisid
					  WHERE OIMAR.objectitemmonitoringid = ".$objectItemMonitoringId.";";
			
			return $query;
		}
		
		public function GetObjectItemPlanScheduleDates($objectItemPlanId)
		{
			$query = "SELECT OIPS.schedulemonth AS Month 
					  FROM objectitemplanschedules OIPS
					  WHERE OIPS.objectitemplanid = ".$objectItemPlanId.";";
			
			return $query;
		}

		public function GetObjectItemPlanScheduleFixedDates($objectItemPlanId)
		{
			$query = "SELECT OIPS.scheduledate AS Date 
					  FROM objectitemplanschedules OIPS
					  WHERE OIPS.objectitemplanid = ".$objectItemPlanId.";";

			return $query;
		}

		public function GetObjectItemPlanScheduleDatesFromMonitoring($objectItemMonitoringId)
		{
			$query = "SELECT OIPS.schedulemonth AS Month 
					  FROM objectitemplanschedules OIPS
					  INNER JOIN objectitemplans OIP ON OIP.id = OIPS.objectitemplanid
					  WHERE OIP.objectitemmonitoringid = $objectItemMonitoringId;";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetObjectTypesForMonth($month, $areaId)
		{
			$query = "SELECT DISTINCT OT.id AS Id,
							 OT.name AS Name 
					  FROM objecttypes OT
					  INNER JOIN objects O ON O.objecttypeid = OT.id
					  INNER JOIN objectitems OI ON OI.objectid = O.id
					  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  WHERE OIPS.schedulemonth = $month				  
					  AND O.areaid = $areaId
					  ORDER BY OT.Name ASC";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetObjectTypesForActiveObjects($areaId)
		{
			$query = "SELECT DISTINCT OT.id AS Id,
							 OT.name AS Name 
					  FROM objecttypes OT
					  INNER JOIN objects O ON O.objecttypeid = OT.id					  
					  WHERE O.active = TRUE 			  
					  AND O.areaid = $areaId
					  ORDER BY OT.Name ASC";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetMonitoringsCountFromObjectMonthly($objectId, $month)
		{
			$query = "SELECT COUNT(*)
					  FROM 
					  (
						  SELECT DISTINCT OIM.id
						  FROM objectitemmonitorings OIM
						  INNER JOIN objectitems OI ON OI.id = OIM.objectitemid
						  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
						  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						  WHERE OI.objectid = $objectId 
						  AND OIPS.schedulemonth = $month
					  ) t;";

			return $query;
		}

		//TODO uvjet gdje je misec ,godina i aktivni plan
		public function GetObjectItemsForMonth($objectId, $month)
		{
			$query = "SELECT DISTINCT OI.id AS Id,
							 OI.name AS Name,
							 OI.seasonal AS Seasonal,
							 OI.sublocation AS LocationDescription
					  FROM objectitems OI 
					  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  WHERE OI.objectid = $objectId
					  AND OIPS.schedulemonth = $month 
					  ORDER BY OI.id ASC;";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetMonitoringsCountFromObjectItemMonthly($objectItemId, $month)
		{
			$query = "SELECT COUNT(*)
					  FROM 
					  (
						  SELECT DISTINCT OIM.id
						  FROM objectitemmonitorings OIM						  
						  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
						  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						  WHERE OIM.objectitemid = $objectItemId 
						  AND OIPS.schedulemonth = $month
					  ) t;";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetObjectItemMonitoringsForMonth($objectItemId, $month)
		{
			$query = "SELECT DISTINCT OIM.id AS Id,
									  OIM.quantity AS Quantity,
									  OIM.description AS Description,
									  CST.statusname AS ServiceTypeName,
									  CST.id AS ServiceTypeId,
									  SI.name AS ServiceItemName,
									  SI.id AS ServiceItemId,
									  S.name AS ServiceName

					  FROM objectitemmonitorings OIM
					  INNER JOIN contractservicetype CST ON CST.id = OIM.contractservicetypeid
					  INNER JOIN serviceitems SI ON SI.id = OIM.serviceitemid 
					  INNER JOIN services S ON S.id = SI.serviceid 
					  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id 
					  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  
					  WHERE OIM.objectitemid = $objectItemId
					  AND OIPS.schedulemonth = $month
					  ORDER BY OIM.id ASC;";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetMonitoringsCountFromObject($objectId)
		{
			$query = "SELECT COUNT(*)
					  FROM 
					  (
						  SELECT DISTINCT OIM.id
						  FROM objectitemmonitorings OIM
						  INNER JOIN objectitems OI ON OI.id = OIM.objectitemid
						  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
						  WHERE OI.objectid = $objectId 
					  ) t;";

			return $query;
		}

		//TODO!!! uvjet gdje je misec ,godina i aktivni plan
		public function GetMonitoringsCountFromObjectItem($objectItemId)
		{
			$query = "SELECT COUNT(*)
					  FROM 
					  (
						  SELECT DISTINCT OIM.id
						  FROM objectitemmonitorings OIM						  
						  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
						  WHERE OIM.objectitemid = $objectItemId 
					  ) t;";

			return $query;
		}


	}