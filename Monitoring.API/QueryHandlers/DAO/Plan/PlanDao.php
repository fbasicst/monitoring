<?php
	namespace API\QueryHandlers\DAO\Plan;

	class PlanDao
	{
		public function GetContractServiceTypes()
		{
			$query = "SELECT CST.id AS Id,
							 CST.statusname AS Name 
					  FROM contractservicetype CST
					  WHERE active = true 
					  ORDER BY CST.id ASC;";
			
			return $query;			
		}
		
		public function GetServiceItems()
		{
			$query = "SELECT * FROM 
					 (
						SELECT SI.id AS ServiceItemId,
							 SI.name AS ServiceItemName, 
							 S.name AS ServiceName,
							 (SELECT COUNT(*) FROM objectitemmonitorings OIM WHERE OIM.serviceitemid = SI.id) AS Amount
						FROM serviceitems SI
						INNER JOIN services S ON SI.serviceid = S.id 
						WHERE SI.active = true
					 ) subQ
					 ORDER BY subQ.Amount DESC, subQ.ServiceItemName ASC;";
			
			return $query;
		}
		
		public function GetAnalysisList()
		{
			$query = "SELECT * FROM 
					 (
						SELECT A.id AS Id,
							   A.name AS Name,
							  (SELECT COUNT(*) FROM objectitemmonitoringanalysisrel OIMAR WHERE OIMAR.analysisid = A.id) AS Amount
						FROM analysis A
						WHERE A.active = true
					 ) subQ
					 ORDER BY subQ.Amount DESC, subQ.Name ASC;";
			
			return $query;
		}

		//NOT IN USE
		public function GetPlanLevelsList()
		{
			$query = "SELECT PL.id AS Id, 
							 PL.description AS Description, 
							 PL.enumdescription AS EnumDescription, 
							 PL.label AS Label 
					  FROM planlevels PL 
					  WHERE PL.active = true;";
			
			return $query;
		}

		public function GetPlanScheduleLevelsList()
		{
			$query = "SELECT SL.description AS name,
							 SL.enumdescription AS enumdescription,
							 SL.id AS value
					  FROM schedulelevels SL
					  WHERE SL.active = true;";

			return $query;
		}
		
		public function GetPlansListLazy($startFrom, $count, $orderType, $search, $month, $year, $planUserId, $objectsAmount)
		{
			$query = "SELECT P.id AS PlanId,
                    		 P.startdate AS StartDate,
                    		 P.enddate AS EndDate,
                    		 P.daysamount AS DaysAmount,
                    		 P.objectsamount AS ObjectsAmount,
                    		 (SELECT COUNT(*) FROM planitems PIs WHERE PIs.planid = P.id AND PIs.planstatusid = 
							 	(SELECT PSss.id FROM planstatuses PSss WHERE PSss.enumdescription = 'COMPLETED')
							 ) AS ObjectsCompleted,
							 (SELECT COUNT(*) FROM planitems PIs WHERE PIs.planid = P.id AND PIs.planstatusid = 
							 	(SELECT PSss.id FROM planstatuses PSss WHERE PSss.enumdescription = 'PENDING')
							 ) AS ObjectsPending,
                    		 P.label AS Label,
                    		 P.locked AS IsLocked,
			    	         U.firstname AS PlanUserFirstName,
	                         U.lastname AS PlanUserLastName 
                      FROM plans P
			          INNER JOIN planusersrel PUR ON PUR.planid = P.id 
                      INNER JOIN monitoring_common.users U ON PUR.userid = U.id 
			          WHERE (U.firstname LIKE '%".$search."%' OR 
			                 U.lastname LIKE '%".$search."%') ";

			if(!empty($month))
			{
				$query .= "AND P.month = $month ";
			}

			if(!empty($year))
			{
				$query .= "AND P.year = $year ";
			}

			if(!empty($planUserId))
			{
				$query .= "AND U.id = $planUserId ";
			}

			if(!empty($objectsAmount))
			{
				$query .= "AND P.objectsamount > $objectsAmount ";
			}

			$query .= "LIMIT ".$startFrom.", ".$count.";";

			return $query;
		}
		
		public function GetPlansWeeklyListLazyCount($search, $month, $year, $planUserId, $objectsAmount)
		{
		    $query = "SELECT COUNT(*)
                      FROM plans P
                      INNER JOIN planlevels PL ON P.planlevelid = PL.id 
		              INNER JOIN planusersrel PUR ON PUR.planid = P.id
		              INNER JOIN monitoring_common.users U ON PUR.userid = U.id 
		              WHERE (U.firstname LIKE '%".$search."%' OR 
			                 U.lastname LIKE '%".$search."%') ";

			if(!empty($month))
			{
				$query .= "AND P.month = $month ";
			}

			if(!empty($year))
			{
				$query .= "AND P.year = $year ";
			}

			if(!empty($planUserId))
			{
				$query .= "AND U.id = $planUserId ";
			}

			if(!empty($objectsAmount))
			{
				$query .= "AND P.objectsamount > $objectsAmount ";
			}
		
		    return $query;
		}

		public function GetObjectsForTransferListLazy($startFrom, $count, $orderType, $search, $areaId, $cityId, $objectTypeId, $contractServiceTypeId, $serviceItemId, $analysisId, $month, $year)
		{
			$query = "SELECT DISTINCT O.id AS ObjectId,
 					  C.name AS CustomerName,
 					  O.name AS ObjectName,
 				  	  O.streetname AS ObjectStreetName,
				      O.streetnumber AS ObjectStreetNumber,
				      CT.name AS ObjectCityName,
				      CT.postalcode AS ObjectCityPostalCode,
				      CT.post AS ObjectCityPost,
				      (SELECT SUM(OIP.monthlyrepeats) 
						  FROM objectitemmonitorings OIM
						  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
						  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						  INNER JOIN objectitems OI ON OI.id = OIM.objectitemid
						  WHERE OI.objectid = O.id
						  AND OIM.active = TRUE 
						  AND (OIP.validfurther = TRUE OR OIP.enddate >= '$year-$month-01')
						  AND OIPS.schedulemonth = $month) AS TotalPlansInMonth,
					  COALESCE((SELECT PIMS.assignednumber						
						  FROM planitemmonthlyschedules PIMS
						  WHERE PIMS.objectid = O.id
						  AND PIMS.month = $month
						  AND PIMS.year = $year), 0) AS PlansAssigned
				     
					  FROM objects O
					  LEFT JOIN objectitems OI ON O.id = OI.objectid
					  LEFT JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  LEFT JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  LEFT JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
					  LEFT JOIN objectitemmonitoringanalysisrel OIMAR ON OIMAR.objectitemmonitoringid = OIM.id
					  INNER JOIN customers C ON C.id = O.customerid
					  INNER JOIN cities CT ON CT.id = O.cityid   
					  LEFT JOIN planitemmonthlyschedules PIMS ON PIMS.objectid = O.id  
									 
					  WHERE (O.name LIKE '%".$search."%' OR C.name LIKE '%".$search."%') 
					  AND O.active = true 
					  AND OIPS.schedulemonth = $month 
					  AND ((SELECT COALESCE((SELECT PIMS.assignednumber						
						  FROM planitemmonthlyschedules PIMS
						  WHERE PIMS.objectid = O.id
						  AND PIMS.month = $month
						  AND PIMS.year = $year), 0)) 
						  < 
						  (SELECT SUM(OIPs.monthlyrepeats)
							FROM objectitemmonitorings OIMs
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
							INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							WHERE OIs.objectid = O.id
							AND OIPSs.schedulemonth = $month
							AND OIMs.active = TRUE
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-$month-01')))
					  AND O.id NOT IN 
							(SELECT PIs.objectid
							FROM planitems PIs 
							INNER JOIN plans Ps ON Ps.id = PIs.planid
							INNER JOIN planstatuses PSs ON PSs.id = PIs.planstatusid
							WHERE PSs.enumdescription = 'CANCELED') ";

			if(!empty($areaId))
			{
				$query .= "AND O.areaid = $areaId ";
			}
			if(!empty($cityId))
			{
				$query .= "AND O.cityid = $cityId ";
			}
			if(!empty($objectTypeId))
			{
				$query .= "AND O.objecttypeid = $objectTypeId ";
			}
			if(!empty($contractServiceTypeId))
			{
				$query .= "AND OIM.contractservicetypeid = $contractServiceTypeId ";
			}
			if(!empty($serviceItemId))
			{
				$query .= "AND OIM.serviceitemid = $serviceItemId ";
			}
			if(!empty($analysisId))
			{
				$query .= "AND OIMAR.analysisid = $analysisId ";
			}

			$query .= "LIMIT ".$startFrom.", ".$count.";";

			return $query;
		}

		public function GetObjectsForTransferListLazyCount($search, $areaId, $cityId, $objectTypeId, $contractServiceTypeId, $serviceItemId, $analysisId, $month, $year)
		{
			$query = "SELECT COUNT(*)
                      FROM (SELECT DISTINCT O.id AS ObjectId,
 					  C.name AS CustomerName,
 					  O.name AS ObjectName,
 				  	  O.streetname AS ObjectStreetName,
				      O.streetnumber AS ObjectStreetNumber,
				      CT.name AS ObjectCityName,
				      CT.postalcode AS ObjectCityPostalCode,
				      CT.post AS ObjectCityPost,
				      (SELECT SUM(OIP.monthlyrepeats) 
						  FROM objectitemmonitorings OIM
						  INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
						  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						  INNER JOIN objectitems OI ON OI.id = OIM.objectitemid
						  WHERE OI.objectid = O.id
						  AND OIM.active = TRUE 
						  AND (OIP.validfurther = TRUE OR OIP.enddate >= '$year-$month-01')
						  AND OIPS.schedulemonth = $month) AS TotalPlansInMonth,
					  COALESCE((SELECT PIMS.assignednumber						
						  FROM planitemmonthlyschedules PIMS
						  WHERE PIMS.objectid = O.id
						  AND PIMS.month = $month
						  AND PIMS.year = $year), 0) AS PlansAssigned
				     
					  FROM objects O
					  LEFT JOIN objectitems OI ON O.id = OI.objectid
					  LEFT JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  LEFT JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  LEFT JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
					  LEFT JOIN objectitemmonitoringanalysisrel OIMAR ON OIMAR.objectitemmonitoringid = OIM.id
					  INNER JOIN customers C ON C.id = O.customerid
					  INNER JOIN cities CT ON CT.id = O.cityid   
					  LEFT JOIN planitemmonthlyschedules PIMS ON PIMS.objectid = O.id  
									 
					  WHERE (O.name LIKE '%".$search."%' OR C.name LIKE '%".$search."%') 
					  AND O.active = true 
					  AND OIPS.schedulemonth = $month 
					  AND ((SELECT COALESCE((SELECT PIMS.assignednumber						
						  FROM planitemmonthlyschedules PIMS
						  WHERE PIMS.objectid = O.id
						  AND PIMS.month = $month
						  AND PIMS.year = $year), 0)) 
						  < 
						  (SELECT SUM(OIPs.monthlyrepeats)
							FROM objectitemmonitorings OIMs
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
							INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							WHERE OIs.objectid = O.id
							AND OIPSs.schedulemonth = $month
							AND OIMs.active = TRUE
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-$month-01')))
					  AND O.id NOT IN 
							(SELECT PIs.objectid
							FROM planitems PIs 
							INNER JOIN plans Ps ON Ps.id = PIs.planid
							INNER JOIN planstatuses PSs ON PSs.id = PIs.planstatusid
							WHERE PSs.enumdescription = 'CANCELED') ";

			if(!empty($areaId))
			{
				$query .= "AND O.areaid = $areaId ";
			}
			if(!empty($cityId))
			{
				$query .= "AND O.cityid = $cityId ";
			}
			if(!empty($objectTypeId))
			{
				$query .= "AND O.objecttypeid = $objectTypeId ";
			}
			if(!empty($contractServiceTypeId))
			{
				$query .= "AND OIM.contractservicetypeid = $contractServiceTypeId ";
			}
			if(!empty($serviceItemId))
			{
				$query .= "AND OIM.serviceitemid = $serviceItemId ";
			}
			if(!empty($analysisId))
			{
				$query .= "AND OIMAR.analysisid = $analysisId ";
			}

			$query .= ") t";

			return $query;
		}

		public function GetPlanWeeklyInfo($planId)
		{
			$query = "SELECT P.id AS PlanId,
                    		 P.startdate AS StartDate,
                    		 P.enddate AS EndDate,
                    		 P.month AS Month, 
                    		 P.year AS Year, 
                    		 P.daysamount AS DaysAmount,
                    		 P.objectsamount AS ObjectsAmount,
                    		 (SELECT COUNT(*) FROM planitems PIs WHERE PIs.planid = P.id AND PIs.planstatusid = 
							 	(SELECT PSss.id FROM planstatuses PSss WHERE PSss.enumdescription = 'COMPLETED')
							 ) AS ObjectsCompleted,
							 (SELECT COUNT(*) FROM planitems PIs WHERE PIs.planid = P.id AND PIs.planstatusid = 
							 	(SELECT PSss.id FROM planstatuses PSss WHERE PSss.enumdescription = 'PENDING')
							 ) AS ObjectsPending,
                    		 P.label AS Label,
                    		 P.locked AS IsLocked,
                    		 P.uploaded AS IsUploaded,
			    	         U.firstname AS PlanUserFirstName,
	                         U.lastname AS PlanUserLastName,
	                         PL.label AS PlanLevelLabel,
	                         (SELECT CONCAT(Us.firstname, ' ', Us.lastname) FROM monitoring_common.users Us WHERE Us.id = P.useridinsert) AS PlanUserCreated,
    						 (SELECT CONCAT(Us.firstname, ' ', Us.lastname) FROM monitoring_common.users Us WHERE Us.id = P.useridcontrolled) AS PlanUserControlled
                      FROM plans P 
			          INNER JOIN planusersrel PUR ON PUR.planid = P.id 
                      INNER JOIN monitoring_common.users U ON PUR.userid = U.id 
                      INNER JOIN planlevels PL ON PL.id = P.planlevelid 
                      WHERE P.id = $planId;";

			return $query;
		}

		public function GetPlanItemsList($planId, $scheduleDate = null)
		{
			$query = "SELECT DISTINCT 
							PI.id AS PlanItemId,
						    PI.scheduledate AS ScheduleDate,
						    PI.notes AS PlanItemNotes,
							PS.id AS PlanStatusId,
							PS.description AS PlanStatusDescription,
							PS.enumdescription AS PlanStatusEnum,
							PI.finishnotes AS PlanItemFinishNotes,
						    O.id AS ObjectId,
						    C.name AS CustomerName,
						    C.oib AS CustomerOib,
						    O.name AS ObjectName,
						    O.streetname AS ObjectStreetName,
						    O.streetnumber AS ObjectStreetNumber,
						    CT.name AS ObjectCityName,
						    CT.postalcode AS ObjectCityPostalCode,
						    CT.post AS ObjectCityPost,
						    O.contactpersonname AS ContactPerson,
						    O.contactpersonphone AS ContactPhone
				     
				     FROM planitems PI 
				     INNER JOIN planstatuses PS ON PS.id = PI.planstatusid
					 INNER JOIN objects O ON O.id = PI.objectid 
					 INNER JOIN customers C ON C.id = O.customerid 
					 INNER JOIN cities CT ON CT.id = O.cityid 
					 
					 WHERE PI.planid = $planId ";

			if(isset($scheduleDate))
			{
				$query .= " AND PI.scheduledate = '$scheduleDate'";
			}

			return $query;
		}

		public function GetPlanStatusesList()
		{
			$query = "SELECT PS.id AS Id,
								 PS.description AS Description,
								 PS.enumdescription AS EnumDescription
					  FROM planstatuses PS
					  WHERE PS.active = true
					  ORDER BY PS.id;";

			return $query;
		}

		public function GetPlanItemDetails($planItemId)
		{
			$query = "SELECT O.name AS ObjectName,
							 O.id AS ObjectId,
							 O.streetname AS ObjectStreetName,
							 O.streetnumber AS ObjectStreetNumber,
							 CI.name AS ObjectCityName,
							 CI.postalcode AS PostalCode,
							 CI.post AS PostName,
							 OT.name AS ObjectTypeName,
							 OA.name AS ObjectAreaName,
							 O.contactpersonname AS ContactPersonName,
							 O.contactpersonphone AS ContactPersonPhone,
							 O.contactpersonemail AS ContactPersonMail,
							 O.notes AS Notes,
							 
							 CU.name AS CustomerName,
							 CU.name AS CustomerName,
							 CU.oib AS CustomerOib,
							 CU.address AS CustomerAddress,
							 CU.streetnumber AS CustomerStreetNumber,
							 CU.postalcode AS CustomerPostalCode,
							 CU.postname AS CustomerPostName,
							 
							 CO.barcode AS ContractBarcode
						 
					  FROM planitems PI
					  INNER JOIN objects O ON O.id = PI.objectid
				  	  INNER JOIN customers CU ON CU.id = O.customerid 
				  	  LEFT JOIN contracts CO ON CO.id = O.contractid 
				  	  INNER JOIN cities CI ON CI.id = O.cityid
				  	  INNER JOIN objecttypes OT ON OT.id = O.objecttypeid 
				  	  INNER JOIN areas OA ON OA.id = O.areaid 
				  
				  	  WHERE PI.id = $planItemId";

			return $query;
		}

		public function GetPlanItemObjectItems($planItemId)
		{
			$query = "SELECT PIOI.id AS Id,
							 PIOI.name AS Name,
							 PIOI.seasonal AS Seasonal,
							 PIOI.sublocation AS LocationDescription
					  FROM planitemobjectitems PIOI
					  WHERE PIOI.planitemid = $planItemId;";

			return $query;
		}

		public function GetPlanItemObjectItemMonitoringsAndPlans($planitemObjectItemId)
		{
			$query = "SELECT PIOIM.id AS Id,
							 PIOIM.quantity AS Quantity,
							 PIOIM.description AS Description,
							 CST.statusname AS ServiceTypeName,
							 SI.name AS ServiceItemName,
							 S.name AS ServiceName,
							 PIOIP.validfurther AS ValidFurther,
							 PIOIP.enddate AS EndDate,
							 PIOIP.monthlyrepeats AS MonthlyRepeats,
							 SL.enumdescription AS ScheduleLevelEnum,
							 PIOIP.id AS PlanId
							 
					  FROM planitemobjectitemmonitorings PIOIM
						
					  INNER JOIN contractservicetype CST ON CST.id = PIOIM.contractservicetypeid
					  INNER JOIN serviceitems SI ON SI.id = PIOIM.serviceitemid 
					  INNER JOIN services S ON S.id = SI.serviceid 
					  INNER JOIN planitemobjectitemplans PIOIP ON PIOIP.planitemobjectitemmonitoringid = PIOIM.id 
					  INNER JOIN schedulelevels SL ON SL.id = PIOIP.schedulelevelid 
						
					  WHERE PIOIM.planitemobjectitemid = $planitemObjectItemId";

			return $query;
		}

		public function GetPlanItemObjectItemMonitoringAnalysis($planItemObjectItemMonitoringId)
		{
			$query = "SELECT A.name AS Name
					  FROM planitemobjectitemmonitoringanalysisrel PIOIMAR
					  INNER JOIN analysis A ON A.id = PIOIMAR.analysisid
					  WHERE PIOIMAR.planitemobjectitemmonitoringid = $planItemObjectItemMonitoringId;";

			return $query;
		}

		public function GetPlanItemObjectItemPlanScheduleDates($planItemObjectItemPlanId)
		{
			$query = "SELECT PIOIPS.schedulemonth AS Month 
					  FROM planitemobjectitemplanschedules PIOIPS
					  WHERE PIOIPS.planitemobjectitemplanid = $planItemObjectItemPlanId;";

			return $query;
		}

		public function GetPlanItemObjectItemPlanScheduleFixedDates($planItemObjectItemPlanId)
		{
			$query = "SELECT PIOIPS.scheduledate AS Date 
					  FROM planitemobjectitemplanschedules PIOIPS
					  WHERE PIOIPS.planitemobjectitemplanid = $planItemObjectItemPlanId;";

			return $query;
		}

		public function GetMonitoringsCountFromPlanItem($planItemId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitemobjectitemmonitorings PIOIM
					  INNER JOIN planitemobjectitems PIOI ON PIOI.id = PIOIM.planitemobjectitemid
					  WHERE PIOI.planitemid = $planItemId ";

			return $query;
		}

		public function GetMonitoringsCountFromPlanItemObjectItem($planItemObjectItemId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitemobjectitemmonitorings PIOIM
					  WHERE PIOIM.planitemobjectitemid = $planItemObjectItemId";

			return $query;
		}

		public function GetPlanItemsScheduleDates($planId)
		{
			$query = "SELECT DISTINCT PI.scheduledate 
					  FROM planitems PI
					  WHERE PI.planid = $planId
					  ORDER BY PI.scheduledate ASC";

			return $query;
		}


		public function GetObjectsForMonthlyPlanListLazy($startFrom, $count, $orderType, $search, $year, $month, $areaIdsCommaSeparated)
		{
			$query = "SELECT DISTINCT
							O.id AS ObjectId, 
							C.name AS CustomerName, 
							O.name AS ObjectName, 
							O.streetname AS ObjectStreetName, 
							O.streetnumber AS ObjectStreetNumber, 
							CT.name AS ObjectCityName, 
							CT.postalcode AS ObjectCityPostalCode, 
							CT.post AS ObjectCityPost 
						FROM objects O 
						INNER JOIN objectitems OI ON O.id = OI.objectid 
						INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id 
						INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id 
						INNER JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id 
						INNER JOIN customers C ON C.id = O.customerid 
						INNER JOIN cities CT ON CT.id = O.cityid	
						
						WHERE O.active = TRUE 
						
						AND (
							SELECT COUNT(*)
							FROM objectitemmonitorings OIMs 
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
							INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							INNER JOIN objects Os ON OIs.objectid = Os.id
							
							WHERE Os.id = O.id
							AND OIMs.active = TRUE
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-$month-01')
							AND OIPSs.schedulemonth = $month ";
					if(!empty($areaIdsCommaSeparated))
					{
						$query .= "AND Os.areaid IN ($areaIdsCommaSeparated) ";
					}
			$query .= ") > 0
						AND (O.name LIKE '%$search%' OR C.name LIKE '%$search%')
						AND OIPS.schedulemonth = $month ";

			if(!empty($areaIdsCommaSeparated))
			{
				$query .= "AND O.areaid IN ($areaIdsCommaSeparated) ";
			}
			$query .= "LIMIT $startFrom, $count;";

			return $query;
		}

		public function GetObjectsForMonthlyPlanListLazyCount($search, $year, $month, $areaIdsCommaSeparated)
		{
			$query = "SELECT COUNT(*)
					  FROM 
					  (
					  	  SELECT DISTINCT O.id AS ObjectId,
										  C.name AS CustomerName,
										  O.name AS ObjectName,
										  O.streetname AS ObjectStreetName,
										  O.streetnumber AS ObjectStreetNumber,
										  CT.name AS ObjectCityName,
										  CT.postalcode AS ObjectCityPostalCode,
										  CT.post AS ObjectCityPost				      
				     
						  FROM objects O
						  INNER JOIN objectitems OI ON O.id = OI.objectid
						  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
						  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						  INNER JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
						  INNER JOIN customers C ON C.id = O.customerid
						  INNER JOIN cities CT ON CT.id = O.cityid					  
					  
						  WHERE O.active = TRUE 
						  AND (
							SELECT COUNT(*)
							FROM objectitemmonitorings OIMs 
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
							INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							INNER JOIN objects Os ON OIs.objectid = Os.id
							
							WHERE Os.id = O.id
							AND OIMs.active = TRUE
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-$month-01')
							AND OIPSs.schedulemonth = $month ";
					if(!empty($areaIdsCommaSeparated))
					{
						$query .= "AND Os.areaid IN ($areaIdsCommaSeparated) ";
					}

			$query .= ") > 0
						  AND (O.name LIKE '%$search%' OR C.name LIKE '%$search%') 
						  AND OIPS.schedulemonth = $month ";
			if(!empty($areaIdsCommaSeparated))
			{
				$query .= "AND O.areaid IN ($areaIdsCommaSeparated) ";
			}
			$query .= ") t";

			return $query;
		}

		public function GetPlanItemUserAndScheduleDate($objectId, $month, $year)
		{
			$query = "SELECT CONCAT(U.firstname, ' ', U.lastname) AS PlanUser, 
						     PI.scheduledate AS PlanScheduleDate
					  FROM planusersrel PUR
					  INNER JOIN plans P ON P.id = PUR.planid
					  INNER JOIN planitems PI ON P.id = PI.planid
					  INNER JOIN monitoring_common.users U ON U.id = PUR.userid
						
					  WHERE PI.objectid = $objectId
					  AND (P.month IS NULL OR (P.month = $month AND P.year = $year))
					  
					  ORDER BY PI.scheduledate ASC;";

			return $query;
		}

		public function GetObjectsForMonthlyPlanPdf($year, $month, $areaId, $objectTypeId)
		{
			$query = "SELECT DISTINCT ObjectId,
									 CustomerName,
									 CustomerOib,
									 ObjectName,
									 ObjectStreetName,
									 ObjectCityName,
									 ObjectCityPostalCode,
									 ObjectCityPost,
									 ContactPerson,
									 ContactPhone,	 
									GROUP_CONCAT(PlanUser) AS PlanUser 
					FROM (
						SELECT DISTINCT O.id AS ObjectId,
									  C.name AS CustomerName,
									  C.oib AS CustomerOib,
									  O.name AS ObjectName,
									  O.streetname AS ObjectStreetName,
									  O.streetnumber AS ObjectStreetNumber,
									  CT.name AS ObjectCityName,
									  CT.postalcode AS ObjectCityPostalCode,
									  CT.post AS ObjectCityPost,
									  O.contactpersonname AS ContactPerson,
									  O.contactpersonphone AS ContactPhone,
									  CONCAT(U.firstname, ' ', U.lastname) AS PlanUser,
									  PI.id						
						FROM objects O						
						INNER JOIN objectitems OI ON O.id = OI.objectid
						INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
						INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						INNER JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
						INNER JOIN customers C ON C.id = O.customerid
						INNER JOIN cities CT ON CT.id = O.cityid  
						
						LEFT JOIN planitems PI ON PI.objectid = O.id 
						LEFT JOIN plans P ON PI.planid = P.id AND P.month = $month AND P.year = $year 
						LEFT JOIN planusersrel PUR ON PUR.planid = P.id 
						LEFT JOIN monitoring_common.users U ON U.id = PUR.userid
						
						WHERE O.active = TRUE 
						AND OIPS.schedulemonth = $month 
						AND O.areaid = $areaId
						AND O.objecttypeid = $objectTypeId
					   AND ((P.month = $month AND P.year = $year) OR P.month IS NULL)
					   AND (
							SELECT COUNT(*)
							FROM objectitemmonitorings OIMs 
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
							INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							INNER JOIN objects Os ON OIs.objectid = Os.id
							
							WHERE Os.id = O.id
							AND OIMs.active = TRUE
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-$month-01')
							AND OIPSs.schedulemonth = $month) > 0 
				   ) t			   
				   GROUP BY ObjectId;";

			return $query;
		}


		public function GetObjectsForAnnuallyPlanListLazy($startFrom, $count, $orderType, $search, $year, $areaIdsCommaSeparated)
		{
			$query = "SELECT DISTINCT O.id AS ObjectId,
									  C.name AS CustomerName,
									  O.name AS ObjectName,
									  O.streetname AS ObjectStreetName,
									  O.streetnumber AS ObjectStreetNumber,
									  CT.name AS ObjectCityName,
									  CT.postalcode AS ObjectCityPostalCode,
									  CT.post AS ObjectCityPost
					  FROM objects O
					  INNER JOIN objectitems OI ON O.id = OI.objectid
					  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  INNER JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
					  INNER JOIN customers C ON C.id = O.customerid
					  INNER JOIN cities CT ON CT.id = O.cityid					  
					  
					  WHERE O.active = TRUE 
					  AND (O.name LIKE '%$search%' OR C.name LIKE '%$search%') 
					  AND (
							SELECT COUNT(*)
							FROM objectitemmonitorings OIMs 													
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							INNER JOIN objects Os ON OIs.objectid = Os.id
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemid = OIs.id
							
							WHERE Os.id = O.id
							AND OIMs.active = TRUE 
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-01-01') ";
					if(!empty($areaIdsCommaSeparated))
					{
						$query .= "AND Os.areaid IN ($areaIdsCommaSeparated) ";
					}
			$query .= ") > 0 ";

			if(!empty($areaIdsCommaSeparated))
			{
				$query .= "AND O.areaid IN ($areaIdsCommaSeparated) ";
			}
			$query .= "LIMIT $startFrom, $count;";

			return $query;
		}

		public function GetObjectsForAnnuallyPlanListLazyCount($search, $year, $areaIdsCommaSeparated)
		{
			$query = "SELECT COUNT(*)
					  FROM 
					  (
					  	  SELECT DISTINCT O.id AS ObjectId,
										  C.name AS CustomerName,
										  O.name AS ObjectName,
										  O.streetname AS ObjectStreetName,
										  O.streetnumber AS ObjectStreetNumber,
										  CT.name AS ObjectCityName,
										  CT.postalcode AS ObjectCityPostalCode,
										  CT.post AS ObjectCityPost				      
				     
						  FROM objects O
						  INNER JOIN objectitems OI ON O.id = OI.objectid
						  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
						  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
						  INNER JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
						  INNER JOIN customers C ON C.id = O.customerid
						  INNER JOIN cities CT ON CT.id = O.cityid					  
					  
						  WHERE O.active = TRUE 
						  AND (O.name LIKE '%$search%' OR C.name LIKE '%$search%') 
						  AND (
							SELECT COUNT(*)
							FROM objectitemmonitorings OIMs 													
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							INNER JOIN objects Os ON OIs.objectid = Os.id
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemid = OIs.id
							
							WHERE Os.id = O.id
							AND OIMs.active = TRUE 
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-01-01') ";
					if(!empty($areaIdsCommaSeparated))
					{
						$query .= "AND Os.areaid IN ($areaIdsCommaSeparated) ";
					}
			$query .= ") > 0 ";

			if(!empty($areaIdsCommaSeparated))
			{
				$query .= "AND O.areaid IN ($areaIdsCommaSeparated) ";
			}
			$query .= ") t";

			return $query;
		}


		public function GetObjectItemPlanScheduleDatesForAnnuallyPlan($objectId, $year)
		{
			$query = "SELECT DISTINCT OIPS.schedulemonth AS Month
					  FROM objectitemplanschedules OIPS
					  INNER JOIN objectitemplans OIP ON OIP.id = OIPS.objectitemplanid
					  INNER JOIN objectitems OI ON OIP.objectitemid = OI.id
					  INNER JOIN objectitemmonitorings OIM ON OIM.id = OIP.objectitemmonitoringid
					  WHERE OI.objectid = $objectId 
					  AND OIM.active = TRUE
					  AND (OIP.validfurther = TRUE OR OIP.enddate >= '$year-01-01') 
					  ORDER BY OIPS.schedulemonth ASC;";

			return $query;
		}

		public function GetObjectsForAnnuallyPlanPdf($year, $areaId, $objectTypeId)
		{
			$query = "SELECT DISTINCT O.id AS ObjectId,
									  C.name AS CustomerName,
									  C.oib AS CustomerOib,
									  O.name AS ObjectName,
									  O.streetname AS ObjectStreetName,
									  O.streetnumber AS ObjectStreetNumber,
									  CT.name AS ObjectCityName,
									  CT.postalcode AS ObjectCityPostalCode,
									  CT.post AS ObjectCityPost,
									  O.contactpersonname AS ContactPerson,
									  O.contactpersonphone AS ContactPhone
				     
					  FROM objects O
					  INNER JOIN objectitems OI ON O.id = OI.objectid
					  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  INNER JOIN objectitemmonitorings OIM ON OIM.objectitemid = OI.id
					  INNER JOIN customers C ON C.id = O.customerid
					  INNER JOIN cities CT ON CT.id = O.cityid
					  
					  WHERE O.active = TRUE 
					  AND O.areaid = $areaId 
					  AND O.objecttypeid = $objectTypeId
					  AND (
							SELECT COUNT(*)
							FROM objectitemmonitorings OIMs 
							INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
							INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
							INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
							INNER JOIN objects Os ON OIs.objectid = Os.id
							
							WHERE Os.id = O.id
							AND OIMs.active = TRUE
							AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-01-01')
                          ) > 0 ";
			return $query;
		}
	}