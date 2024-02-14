<?php
	namespace API\CommandHandlers\DAO\Monitoring;
	
	use PDO;

	class MonitoringDao
	{
		/** @var PDO */
		private $pdo;

		public function __construct($pdo = null) //todo privremeno optional da ne pucaju ostali pozivi
		{
			$this->pdo = $pdo;
		}

		public function SaveObjectItemMonitoringAnalysis()
		{
			$command = "INSERT INTO objectitemmonitoringanalysisrel (objectitemmonitoringid, analysisid) 
									 VALUES (:objectitemmonitoringid,
											 :analysisid
											);";

			return $command;
		}

		//TO DO: Zašto ovo nije u PlanDao?
		public function DeleteObjectItemPlanSchedules()
		{
			$command = "DELETE FROM objectitemplanschedules 
						WHERE id = :id";

			return $command;
		}

		public function DeleteObjectItemMonitoringAnalisysRels()
		{
			$command = "DELETE FROM objectitemmonitoringanalysisrel 
						WHERE id = :id";

			return $command;
		}

		public function DeleteObjectItemMonitorings()
		{
			$command = "DELETE FROM objectitemmonitorings 
						WHERE id = :id";

			return $command;
		}

		public function GetObjectItemMonitoringAnalysisRelsIdsFromMonitoring($monitoringId)
		{
			$command = "SELECT OIMAR.id
						FROM objectitemmonitoringanalysisrel OIMAR
						WHERE OIMAR.objectitemmonitoringid = '$monitoringId'";

			return $command;
		}

		public function GetObjectItemMonitoringAnalysisRelsIdsFromObjectItem($objectItemId)
		{
			$command = "SELECT OIMA.id
					    FROM objectitemmonitoringanalysisrel OIMA
					    WHERE OIMA.objectitemmonitoringid IN 
							(SELECT OIM.id 
							FROM objectitemmonitorings OIM 
							WHERE OIM.objectitemid = '$objectItemId');";

			return $command;
		}

		public function GetObjectItemMonitoringIdsFromObjectItem($objectItemId)
		{
			$query = "SELECT OIM.id 
					  FROM objectitemmonitorings OIM 
					  WHERE OIM.objectitemid = '$objectItemId';";

			return $query;
		}

		/*
		 * Kopiranje samo onih monitoringa kojima je mjesečno ponavljanje veće od
		 * broja kopiranih monitoringa */
		public function GetObjectItemMonitoringIdsFromObjectItemForTransferList($objectItemId, $date)
		{
			$query = "	SELECT OIM.id
					    FROM objectitemmonitorings OIM 
					    INNER JOIN objectitemplans OIP ON OIM.id = OIP.objectitemmonitoringid
					    INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					    WHERE OIM.objectitemid = $objectItemId
					    AND OIM.active = TRUE 
					    AND (OIP.validfurther = TRUE OR OIP.enddate >= CONCAT(DATE_FORMAT('$date', '%Y-%m-'), '01'))
					    AND EXTRACT(MONTH FROM '$date') = OIPS.schedulemonth
					    AND 
					    (OIP.monthlyrepeats > 
						  (SELECT COUNT(*)
						   FROM planitemobjectitemmonitorings PIOIMs
						   INNER JOIN planitemobjectitems PIOIs ON PIOIs.id = PIOIMs.planitemobjectitemid
						   INNER JOIN planitems PIs ON PIs.id = PIOIs.planitemid
						   INNER JOIN plans Ps ON Ps.id = PIs.planid
						   WHERE PIOIMs.objectitemmonitoringid = OIM.id
						   AND Ps.month =  EXTRACT(MONTH FROM '$date')
						   AND Ps.year = EXTRACT(YEAR FROM '$date'))
							
						   OR
							
						   OIM.id IN
						   (SELECT DISTINCT PIOIMs.objectitemmonitoringid
							FROM planitemobjectitemmonitorings PIOIMs
							INNER JOIN planitemobjectitems PIOIs ON PIOIMs.planitemobjectitemid
							INNER JOIN planitems PIs ON PIOIs.planitemid = PIs.id
							INNER JOIN plans Ps ON Ps.id = PIs.planid
							INNER JOIN planstatuses PSs ON PIs.planstatusid = PSs.id
							WHERE PIOIs.objectitemid = $objectItemId
							AND Ps.locked = true
							AND Ps.month = EXTRACT(MONTH FROM '$date')
							AND Ps.year = EXTRACT(YEAR FROM '$date')
							AND PSs.enumdescription = 'DELAYED')		
						);";

			return $query;
		}

		public function UpdateObjectItemMonitoring()
		{
			$command = "UPDATE objectitemmonitorings 
						SET contractservicetypeid = :contractservicetypeid, 
							serviceitemid = :serviceitemid, 
							quantity = :quantity, 
							description = :description
						WHERE id = :objectitemmonitoringid;";

			return $command;
		}

		public function ToggleObjectItemMonitoringStatus()
		{
			$command = "UPDATE objectitemmonitorings 
						SET active = !active
						WHERE id = :objectitemmonitoringid;";

			return $command;
		}

		public function ObjectItemMonitoringCopiedCount($objectItemMonitoringId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitemobjectitemmonitorings PIOIM
					  WHERE PIOIM.objectitemmonitoringid = $objectItemMonitoringId;";
			return $query;
		}

		public function ObjectItemMonitoringsCopiedCount($objectItemId)
		{
			$query = "SELECT COUNT(*)
					  FROM planitemobjectitemmonitorings PIOIM
					  INNER JOIN planitemobjectitems PIOI ON PIOI.id = PIOIM.planitemobjectitemid
					  WHERE PIOI.objectitemid = $objectItemId;";
			return $query;
		}
	}
