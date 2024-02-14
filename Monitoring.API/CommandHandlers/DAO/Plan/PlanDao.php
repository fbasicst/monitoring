<?php
	namespace API\CommandHandlers\DAO\Plan;
	
	use API\Models\Plans\PlanItem;
	use PDO;

	class PlanDao
	{
		/** @var PDO */
		private $pdo;

		public function __construct($pdo = null) //todo privremeno optional da ne pucaju ostali pozivi
		{
			$this->pdo = $pdo;
		}

		public function SaveObjectItemPlanSchedule()
		{
			$command = "INSERT INTO objectitemplanschedules (schedulemonth,  objectitemplanid) 
							 VALUES (:schedulemonth,									 
									 :objectitemplanid
									 );";
											 
			return $command;
		}

		public function SaveObjectItemPlanScheduleFixedDates()
		{
			$command = "INSERT INTO objectitemplanschedules (scheduledate,  objectitemplanid) 
							 VALUES (STR_TO_DATE(:scheduledate,'%d.%m.%Y'),									 
									 :objectitemplanid
									 );";

			return $command;
		}
		
		public function DeleteObjectItemPlans()
		{
			$command = "DELETE FROM objectitemplans 
						WHERE id = :id";
			
			return $command;
		}
		
		public function GetObjectItemPlanScheduleIdsFromMonitoring($monitoringId)
		{
			$command = "SELECT OIPS.id 
						FROM objectitemplanschedules OIPS
						WHERE OIPS.objectitemplanid = (
							SELECT OIP.id 
							FROM objectitemplans OIP 
							WHERE OIP.objectitemmonitoringid = '$monitoringId' 
						);";
						
			return $command;
		}
		
		public function GetObjectItemPlanIdFromMonitoring($monitoringId)
		{
			$command = "SELECT OIP.id
						FROM objectitemplans OIP
						WHERE OIP.objectitemmonitoringid = '$monitoringId'";

			return $command;
		}
		
		public function GetObjectItemPlanScheduleIdsFromObjectItem($objectItemId)
		{
			$command = "SELECT OIPS.id
					    FROM objectitemplanschedules OIPS
					    WHERE OIPS.objectitemplanid IN  
							(SELECT OIP.id 
							 FROM  objectitemplans OIP 
							 WHERE OIP.objectitemid = '$objectItemId');";
						
			return $command;
		}
		
		public function GetObjectItemPlanIdsFromObjectItem($objectItemId)
		{
			$command = "SELECT OIP.id
						FROM objectitemplans OIP
						WHERE OIP.objectitemid = '$objectItemId'";

			return $command;
		}
		
		public function SavePlanHeaderWeekly()
		{
			$command = "INSERT INTO plans (startdate, enddate, planlevelid, useridinsert, 
										   useridcontrolled, daysamount, objectsamount, label, month, year, datetimeinsert) 
						VALUES (:startdate,
								:enddate,
								:planlevelid,
								:useridinsert,
								:useridcontrolled,
								:daysamount,
								:objectsamount,
								:label,
								:month,
								:year,
								now());";

			return $command;
		}
		
		public function GetPlanStatus($statusEnum)
		{
			$query = "SELECT PS.id AS Id,
							 PS.description AS Description,
							 PS.enumdescription AS EnumDescription 
					  FROM planstatuses PS
					  WHERE PS.active = true
					  AND PS.enumdescription = '$statusEnum';";
			
			return $query;
		}
		
		public function GetPlanLevel($levelEnum)
		{
			$query = "SELECT PL.id AS Id,
							 PL.description AS Description,
							 PL.enumdescription AS EnumDescription 
					  FROM planlevels PL
					  WHERE PL.active = true
					  AND PL.enumdescription = '$levelEnum';";
			
			return $query;
		}
		
		public function SavePlanWeeklyUsers()
		{
		    $command = "INSERT INTO planusersrel (planid, userid)
		                VALUES (:planid,
		                        :userid);";
		    
		    return $command;
		}

		public function SavePlanItemsWeekly()
		{
			$command = "INSERT INTO planitems (planid, objectid, scheduledate, planstatusid, notes, useridinsert, datetimeinsert)
						VALUES (:planid,
								:objectid,
								:scheduledate,
								:planstatusid,
								:notes,
								:useridinsert,
								now()
						);";

			return $command;
		}

		public function UpdatePlanObjectsAmount()
		{
			$command = "UPDATE plans P
						SET P.objectsamount = (
							SELECT COUNT(*)
							FROM planitems PIs 
							WHERE PIs.planid = P.id
							)
						WHERE P.id = :planid;";

			return $command;
		}

		public function GetPlanMonthlyAssignmentAmount($objectId, $month, $year)
		{
			$query = "SELECT COALESCE((
					  SELECT PIMS.assignednumber
					  FROM planitemmonthlyschedules PIMS 
					  WHERE PIMS.objectid = $objectId
					  AND PIMS.month = $month
					  AND PIMS.year = $year) , 0) AS AssignmentsAmount;";

			return $query;
		}

		public function GetObjectIdFromPlanItemId($planItemId)
		{
			$query = "SELECT PI.objectid
					  FROM planitems PI
					  WHERE PI.id = $planItemId;";

			return $query;
		}

		public function SavePlanMonthlyAssignment()
		{
			$command = "INSERT INTO planitemmonthlyschedules (objectid, assignednumber, month, year)
						VALUES
						(:objectid, 
						 :assignednumber, 
						 :month, 
						 :year);";

			return $command;
		}

		public function UpdatePlanMonthlyAssignment()
		{
			$command = "UPDATE planitemmonthlyschedules PIMS
						SET assignednumber = :assignednumber 
						WHERE PIMS.objectid = :objectid 
						AND PIMS.month = :month 
						AND PIMS.year = :year;";

			return $command;
		}

		//NOT IN USE - stari način provjere jeli objekt postoji u drugom planu
		/*
		public function CheckObjectItemPlanExistence($objectId, $planId, $month, $year)
		{
			$query = "SELECT COUNT(*)
					  FROM planitems PI
					  INNER JOIN plans P ON P.id = PI.planid
					  INNER JOIN planstatuses PS ON PS.id = PI.planstatusid
					  WHERE PI.objectid = $objectId
					  AND P.id != $planId
					  AND P.month = $month
					  AND P.year = $year
					  AND (PS.enumdescription = 'COMPLETED' 
					  	OR PS.enumdescription = 'PENDING' 
					   	OR (P.locked = FALSE AND PS.enumdescription = 'DELAYED') 
					   	OR PS.enumdescription = 'CANCELED');";

			return $query;
		}*/

		public function CheckObjectItemPlanRepeatQuote($objectId, $month, $year)
		{
			$query = "SELECT COUNT(*)
					  FROM objects O
					  INNER JOIN planitemmonthlyschedules PIMS ON PIMS.objectid = O.id
					  WHERE O.id = $objectId 
					  AND PIMS.month = $month 
					  AND PIMS.year = $year 
					  AND (SELECT COALESCE((SELECT PIMS.assignednumber						
						  FROM planitemmonthlyschedules PIMS
						  WHERE PIMS.objectid = $objectId
						  AND PIMS.month = $month
						  AND PIMS.year = $year), 0)) >= (SELECT COALESCE((SELECT SUM(OIPs.monthlyrepeats)
												  FROM objectitemmonitorings OIMs
												  INNER JOIN objectitemplans OIPs ON OIPs.objectitemmonitoringid = OIMs.id
												  INNER JOIN objectitemplanschedules OIPSs ON OIPSs.objectitemplanid = OIPs.id
												  INNER JOIN objectitems OIs ON OIs.id = OIMs.objectitemid
												  WHERE OIs.objectid = O.id
												  AND OIPSs.schedulemonth = $month 
												  AND OIMs.active = TRUE
												  AND (OIPs.validfurther = TRUE OR OIPs.enddate >= '$year-$month-01')), 0));";

			return $query;
		}

		public function CheckPlanWeeklyDateRange($planId, $scheduleDate)
		{
			$query = "SELECT COUNT(*)
					  FROM plans P
					  WHERE P.id = $planId 
					  AND P.startdate <= '$scheduleDate'
					  AND P.enddate >= '$scheduleDate'";

			return $query;
		}

		public function UpdatePlanItemsWeeklyStatus()
		{
			$command = "UPDATE planitems PI
						SET PI.planstatusid = :planstatusid
						WHERE PI.planid = :planid";

			return $command;
		}

		public function UpdatePlanItemWeeklyStatus()
		{
			$command = "UPDATE planitems PI
						SET PI.planstatusid = :planstatusid,
							PI.finishnotes = :finishnotes
						WHERE PI.id = :planitemid";

			return $command;
		}

		public function UpdatePlanItemStatusCloud()
		{
			$command = "UPDATE planitems PI
						SET PI.planstatusenum = :planstatusenum,
							PI.finishnotes = :finishnotes
						WHERE PI.remoteid = :planitemid";

			return $command;
		}

		public function CheckPlanWeeklyIsLocked($planId)
		{
			$query = "SELECT locked
					  FROM plans P
					  WHERE P.id = $planId";

			return $query;
		}

		//NOT IN USE
		public function DeletePlanItems()
		{
			$command = "DELETE FROM planitems WHERE planid = :planid";

			return $command;
		}

		public function DeletePlanUserRels()
		{
			$command = "DELETE FROM planusersrel WHERE planid = :planid";

			return $command;
		}

		public function DeletePlan()
		{
			$command = "DELETE FROM plans WHERE id = :planid";

			return $command;
		}

		public function LockPlan()
		{
			$command = "UPDATE plans 
						SET locked = true
						WHERE id = :planid";

			return $command;
		}

		public function LockPlanCloud()
		{
			$command = "UPDATE plans 
						SET locked = true
						WHERE remoteid = :planid";

			return $command;
		}

		public function DeletePlanItemFromPlan()
		{
			$command = "DELETE FROM planitems WHERE id = :planitemid";

			return $command;
		}

		public function CheckPendingPlanItemsExistence($planId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitems PI
					  INNER JOIN planstatuses PS ON PS.id = PI.planstatusid
					  WHERE PI.planid = $planId
					  AND PS.enumdescription = 'PENDING';";

			return $query;
		}

		public function CopyObjectItemToPlan()
		{
			$command = "INSERT INTO planitemobjectitems (objectitemid, name, seasonal, sublocation, planitemid)
						SELECT OI.id, OI.name, OI.seasonal, OI.sublocation, :planitemid
						FROM   objectitems OI
						WHERE  OI.id = :objectitemid";

			return $command;
		}

		public function CopyObjectItemMonitoringToPlan()
		{
			$command = "INSERT INTO planitemobjectitemmonitorings (objectitemmonitoringid, contractservicetypeid, serviceitemid, quantity, description, planitemobjectitemid)
						SELECT OIM.id, OIM.contractservicetypeid, OIM.serviceitemid, OIM.quantity, OIM.description, :planitemobjectitemid
						FROM   objectitemmonitorings OIM
						WHERE  OIM.id = :objectitemmonitoringid";

			return $command;
		}

		public function CopyObjectItemMonitoringAnalysisToPlan()
		{
			$command = "INSERT INTO planitemobjectitemmonitoringanalysisrel (analysisid, planitemobjectitemmonitoringid)
						SELECT OIMAR.analysisid, :planitemobjectitemmonitoringid
						FROM   objectitemmonitoringanalysisrel OIMAR
						WHERE  OIMAR.objectitemmonitoringid = :objectitemmonitoringid";

			return $command;
		}

		public function CopyObjectItemPlanToPlan()
		{
			$command = "INSERT INTO planitemobjectitemplans (objectitemplanid, validfurther, enddate, schedulelevelid, monthlyrepeats, planitemobjectitemmonitoringid, planitemobjectitemid)
						SELECT OIP.id, OIP.validfurther, OIP.enddate, OIP.schedulelevelid, OIP.monthlyrepeats, :planitemobjectitemmonitoringid, :planitemobjectitemid
						FROM   objectitemplans OIP
						WHERE  OIP.id = :objectitemplanid";

			return $command;
		}

		public function CopyObjectItemPlanSchedulesToPlan()
		{
			$command = "INSERT INTO planitemobjectitemplanschedules (schedulemonth, scheduledate, planitemobjectitemplanid)
						SELECT OIPS.schedulemonth, OIPS.scheduledate, :planitemobjectitemplanid
						FROM   objectitemplanschedules OIPS
						WHERE  OIPS.objectitemplanid = :objectitemplanid";

			return $command;
		}

		public function GetPlanItemObjectItemPlanIds($planItemId)
		{
			$query = "SELECT PIOIP.id 
					  FROM  planitemobjectitemplans PIOIP 
					  WHERE PIOIP.planitemobjectitemid 
					  IN 
					  (
						SELECT PIOI.id
						FROM planitemobjectitems PIOI 
						WHERE PIOI.planitemid = $planItemId
					  );";

			return $query;
		}

		public function DeletePlanItemObjectItemPlanSchedules()
		{
			$command = "DELETE FROM planitemobjectitemplanschedules
						WHERE planitemobjectitemplanid = :id;";

			return $command;
		}

		public function DeletePlanItemObjectItemPlans()
		{
			$command = "DELETE FROM planitemobjectitemplans
						WHERE id = :id;";

			return $command;
		}

		public function GetPlanItemObjectItemMonitoringIds($planItemId)
		{
			$query = "SELECT OIM.id 
					  FROM planitemobjectitemmonitorings OIM 
					  WHERE OIM.planitemobjectitemid IN 
					  (
						  SELECT OI.id
						  FROM planitemobjectitems OI 
						  WHERE OI.planitemid = $planItemId
					  );";

			return $query;
		}

		public function DeletePlanItemObjectItemMonitoringAnalysisRels()
		{
			$command = "DELETE FROM planitemobjectitemmonitoringanalysisrel
						WHERE planitemobjectitemmonitoringid = :id;";

			return $command;
		}

		public function DeletePlanItemObjectItemMonitorings()
		{
			$command = "DELETE FROM planitemobjectitemmonitorings
						WHERE id = :id;";

			return $command;
		}

		public function DeletePlanItemObjectItems()
		{
			$command = "DELETE FROM planitemobjectitems
						WHERE planitemid = :id;";

			return $command;
		}

		public function CheckPlanItemsExistence($planId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitems
					  WHERE planid = $planId;";

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
	                         U.id AS PlanUserId,
	                         (SELECT CONCAT(Us.firstname, ' ', Us.lastname) 
	                         FROM monitoring_common.users Us
	                         WHERE Us.id = P.useridcontrolled) AS UserControlled
                      FROM plans P 
			          INNER JOIN planusersrel PUR ON PUR.planid = P.id 
                      INNER JOIN monitoring_common.users U ON PUR.userid = U.id 
                      WHERE P.id = $planId;";

			return $query;
		}

		public function DeletePlanMonthlyAssignment()
		{
			$command = "DELETE FROM planitemmonthlyschedules 
						WHERE objectid = :objectid
						AND month = :month
						AND year = :year;";

			return $command;
		}

		public function GetDelayedPlanItemsIds($planId)
		{
			$query = "SELECT PI.id 
					  FROM planitems PI 
					  INNER JOIN planstatuses PS ON PS.id = PI.planstatusid 
					  INNER JOIN plans P ON P.id = PI.planid
					  WHERE P.id = $planId
					  AND PS.enumdescription = 'DELAYED'
					  AND P.locked = true";

			return $query;
		}

		public function GetPlanItemMonitoringsCount($planId, $planItemId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitemobjectitemmonitorings PIOIM
					  INNER JOIN planitemobjectitems PIOI ON PIOI.id = PIOIM.planitemobjectitemid
					  INNER JOIN planitems PI ON PI.id = PIOI.planitemid
						
					  WHERE PI.planid = $planId
					  AND PI.id = $planItemId";

			return $query;
		}
		
		public function GetPlanItemsForCloud($planId, $planItemId = null)
		{
			$query = "SELECT  
							PI.id AS RemoteId,
							PI.planid AS PlanId,							
						    PI.scheduledate AS ScheduleDate,
						    PS.enumdescription AS PlanStatusEnum,
						    PI.notes AS Notes,
						    C.name AS CustomerName,						    
						    CONCAT(C.address, ' ', COALESCE(C.streetnumber, ''), ', ', C.postalcode, ' ', C.postname) AS CustomerAddressFull, 
						    C.oib AS CustomerOib,
						    O.id AS ObjectId,
						    O.name AS ObjectName,
						    CONCAT(O.streetname, ' ', COALESCE(O.streetnumber, ''), ', ', CT.name, ', ', CT.postalcode, ' ', CT.post) AS ObjectAddressFull,						    
						    O.contactpersonname AS ObjectContactPerson,
						    O.contactpersonphone AS ObjectContactPhone,
						    CONCAT(U.firstname, ' ', U.lastname) AS UserInsert
				     
				     FROM planitems PI 
				     INNER JOIN planstatuses PS ON PS.id = PI.planstatusid
					 INNER JOIN objects O ON O.id = PI.objectid 
					 INNER JOIN customers C ON C.id = O.customerid 
					 INNER JOIN cities CT ON CT.id = O.cityid 
					 INNER JOIN monitoring_common.users U ON U.id = PI.useridinsert 
					 
					 WHERE PI.planid = $planId ";

			if(isset($planItemId))
			{
				$query .= "AND PI.id = $planItemId";
			}

			return $query;
		}

		public function GetPlanItemObjectItemsForCloud($planId, $planItemId = null)
		{
			$query = "SELECT PIOI.id AS RemoteId,
							 PIOI.planitemid AS PlanItemId,
							 PIOI.name AS Name,
							 PIOI.seasonal AS Seasonal,
							 PIOI.sublocation AS Sublocation
					  FROM planitemobjectitems PIOI 
					  INNER JOIN planitems PI ON PI.id = PIOI.planitemid
					  WHERE PI.planid = $planId ";

			if(isset($planItemId))
			{
				$query .= "AND PI.id = $planItemId";
			}

			return $query;
		}

		public function GetPlanItemObjectItemMonitoringsForCloud($planId, $planItemId = null)
		{
			$query = "SELECT PIOIM.id AS RemoteId,
							 PIOIM.planitemobjectitemid AS PlanItemObjectItemId,
							 CST.statusname AS ContractServiceTypeName,
							 CONCAT(S.name, ' - ', SI.name) AS ServiceFull,
							 PIOIM.quantity AS Quantity,
							 PIOIM.description AS Description 
							 
					 FROM planitemobjectitemmonitorings PIOIM
					 INNER JOIN planitemobjectitems PIOI ON PIOI.id = PIOIM.planitemobjectitemid
					 INNER JOIN planitems PI ON PI.id = PIOI.planitemid
					 INNER JOIN contractservicetype CST ON CST.id = PIOIM.contractservicetypeid
					 INNER JOIN serviceitems SI ON SI.id = PIOIM.serviceitemid 
					 INNER JOIN services S ON S.id = SI.serviceid 					
					 WHERE PI.planid = $planId ";

			if(isset($planItemId))
			{
				$query .= "AND PI.id = $planItemId";
			}

			return $query;
		}

		public function GetPlanItemObjectItemMonitoringAnalysisForCloud($planId, $planItemId = null)
		{
			$query = "SELECT 
						PIOIMAR.id AS RemoteId,
						PIOIMAR.planitemobjectitemmonitoringid AS PlanItemObjectItemMonitoringId,
						A.name AS AnalysisName
						
					  FROM planitemobjectitemmonitoringanalysisrel PIOIMAR
					  INNER JOIN analysis A ON A.id = PIOIMAR.analysisid
					  INNER JOIN planitemobjectitemmonitorings PIOIM ON PIOIM.id = PIOIMAR.planitemobjectitemmonitoringid
					  INNER JOIN planitemobjectitems PIOI ON PIOI.id = PIOIM.planitemobjectitemid
					  INNER JOIN planitems PI ON PI.id = PIOI.planitemid						
					  WHERE PI.planid = $planId ";

			if(isset($planItemId))
			{
				$query .= "AND PI.id = $planItemId";
			}

			return $query;
		}

		public function SavePlanHeaderWeeklyToCloud()
		{
			$command = "INSERT INTO plans (remoteid, startdate, enddate, month, year, daysamount, objectsamount, 
										   label, planuserid, userinsert, usercontrolled, datetimeinsert) 
						VALUES (
								:remoteid,
								:startdate,
								:enddate,
								:month,
								:year,
								:daysamount,
								:objectsamount,
								:label,
								:planuserid,
								:userinsert,
								:usercontrolled,
								now());";

			return $command;
		}

		public function SavePlanItemsToCloud()
		{
			$command = "INSERT INTO planitems (remoteid, planid, scheduledate, planstatusenum, notes, customername, customeraddressfull, customeroib,
										   objectid, objectname, objectaddressfull, objectcontactperson, objectcontactphone, userinsert, datetimeinsert) 
						VALUES (
								:remoteid,
								:planid,
								:scheduledate,
								:planstatusenum,
								:notes,
								:customername,
								:customeraddressfull,
								:customeroib,
								:objectid,
								:objectname,
								:objectaddressfull,
								:objectcontactperson,
								:objectcontactphone,
								:userinsert,
								now());";

			return $command;
		}

		public function SavePlanItemObjectItemsToCloud($seasonal)
		{
			if($seasonal == true)
			{
				$seasonalString = "true";
			}
			else
			{
				$seasonalString = "false";
			}

			$command = "INSERT INTO planitemobjectitems (remoteid, planitemid, name, seasonal, sublocation) 
						VALUES (
								:remoteid,
								:planitemid,
								:name,
								".$seasonalString.",
								:sublocation
								);";

			return $command;
		}

		public function SavePlanItemObjectItemMonitoringsToCloud()
		{
			$command = "INSERT INTO planitemobjectitemmonitorings (remoteid, planitemobjectitemid, contractservicetypename, servicefull, quantity, description) 
						VALUES (
								:remoteid,
								:planitemobjectitemid,
								:contractservicetypename,
								:servicefull,
								:quantity,
								:description								
								);";

			return $command;
		}

		public function SavePlanItemObjectItemMonitoringAnalysisToCloud()
		{
			$command = "INSERT INTO planitemobjectitemmonitoringanalysisrel (remoteid, planitemobjectitemmonitoringid, analysisname) 
						VALUES (
								:remoteid,
								:planitemobjectitemmonitoringid,
								:analysisname							
								);";

			return $command;
		}

		public function GetPlanStatusesFromCloud($planId)
		{
			$query = "SELECT PI.remoteid AS Id,
							 PI.planstatusenum AS PlanStatusEnum,
							 PI.finishnotes AS FinishNotes
					  FROM planitems PI
					  WHERE PI.planid = $planId;";

			return $query;
		}

		public function SetPlanUploadFlag()
		{
			$command = "UPDATE plans SET uploaded = true WHERE id = :planid;";

			return $command;
		}

		public function GetPlanStatuses($planId)
		{
			$query = "SELECT PI.id AS Id,
							 PS.enumdescription AS PlanStatusEnum,
							 PI.finishnotes AS FinishNotes
					  FROM planitems PI
					  INNER JOIN planstatuses PS ON PS.id = PI.planstatusid
					  WHERE PI.planid = $planId;";

			return $query;
		}

		public function DeletePlanItemObjectItemMonitoringsFromCloud()
		{
			$command = "DELETE FROM planitemobjectitemmonitorings
						WHERE remoteid = :remoteid;";

			return $command;
		}

		public function DeletePlanItemFromPlanFromCloud()
		{
			$command = "DELETE FROM planitems WHERE remoteid = :planitemid";

			return $command;
		}

		public function UpdatePlanObjectsAmountInCloud()
		{
			$command = "UPDATE plans P
						SET P.objectsamount = (
							SELECT COUNT(*)
							FROM planitems PIs 
							WHERE PIs.planid = P.remoteid
							)
						WHERE P.remoteid = :planid;";

			return $command;
		}

		public function UpdateObjectItemPlan($validFurther)
		{
			if($validFurther == true)
			{
				$validFurtherString = "true";
			}
			else
			{
				$validFurtherString = "false";
			}
			$command = "UPDATE objectitemplans 
						SET schedulelevelid = :schedulelevelid, 
							monthlyrepeats = :monthlyrepeats, 
							validfurther = ".$validFurtherString.", 
							enddate = :enddate
						WHERE objectitemmonitoringid = :objectitemmonitoringid;";

			return $command;
		}

		/**
		 * @param $planId
		 * @param $status
		 * @return PlanItem[]
		 */
		public function GetPlanItemsByStatus($planId, $status)
		{
			$query = "SELECT PI.id AS Id,
							 PI.objectid AS ObjectId,
							 O.name AS Name,
							 C.name AS CustomerName,
							 C.oib AS Oib
					  FROM planitems PI 
					  INNER JOIN planstatuses PS ON PS.id = PI.planstatusid 
					  INNER JOIN plans P ON P.id = PI.planid
					  INNER JOIN objects O ON O.id = PI.objectid
					  INNER JOIN customers C ON C.id = O.customerid
					  WHERE P.id = :planid
					  AND PS.enumdescription = :status;";

			$queryDb = $this->pdo->prepare($query);
			$queryDb->execute(array(
				':planid' => $planId,
				':status' => $status
			));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanItem()));
			/** @var PlanItem[] $planItems */
			$planItems = $queryDb->fetchAll();

			return $planItems;
		}
	}
