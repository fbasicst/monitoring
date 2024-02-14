<?php
	namespace API\QueryHandlers\DAO\MasterData;

	use API\QueryHandlers\ViewModel\MasterData\CompletedObjectsByUserViewModel;
	use PDO;

	final class MasterDataDao
	{
		/** @var PDO */
		private $pdo;

		public function __construct($pdo = null) //todo privremeno optional da ne pucaju ostali pozivi
		{
			$this->pdo = $pdo;
		}

		public function GetUserFromId($userId)
		{
			$query = "SELECT U.id AS Id,
							 U.firstname AS FirstName,
							 U.lastname AS LastName
					  FROM monitoring_common.users U
					  WHERE U.id = $userId;";

			return $query;
		}

		public function GetCities()
		{
			$query = "SELECT C.id AS Id,
							 C.name AS Name,
							 C.postalcode AS PostalCode,
							 C.post AS Post
					  FROM cities C 
					  ORDER BY C.name ASC;";		
								
			return $query;			
		}
		
		public function GetObjectAreas()
		{
			$query = "SELECT A.id AS Id,
							 A.name AS Name
					  FROM areas A 
					  WHERE A.active = true 
					  ORDER BY A.name ASC";
								
			return $query;
		}
		
		public function GetObjectsSavedAmount()
		{
			$query = "SELECT COUNT(*) AS ObjectsAmount, 
							 OT.name  AS ObjectTypeName
					  FROM objects O 
					  INNER JOIN objecttypes OT ON O.objecttypeid = OT.id
					  GROUP BY OT.name";

			return $query;
		}
		
		public function GetUsersFromRole($roleName)
		{
		    $query = "SELECT U.id AS UserId,
                    		 U.firstname AS FirstName,
                    		 U.lastname AS LastName,
                    		 U.username AS UserName
                      FROM monitoring_common.users U
                      INNER JOIN monitoring_common.userrolesrel URR ON URR.userid = U.id
                      INNER JOIN monitoring_common.userroles UR ON UR.id = URR.userroleid
                      WHERE UR.enumdescription = '$roleName'
                      ORDER BY U.lastname ASC;";
		    
		    return $query;
		}

		public function GetObjectsSavedByUserFromDate($userId, $date)
		{
			$query = " SELECT COALESCE(COUNT(*), 0) AS ObjectsAmount
					   FROM objects O 
					   WHERE O.useridinsert = $userId
					   AND DATE(O.datetimeinsert) = '".$date."';";

			return $query;
		}

		public function GetRecentUsersObjectsSaved()
		{
			$query = "SELECT DISTINCT CONCAT(U.firstname, ' ', U.lastname) AS UserFullName, U.id AS UserId
					  FROM objects O
					  INNER JOIN monitoring_common.users U ON U.id = O.useridinsert
						
					  WHERE DATE(O.datetimeinsert) >= DATE(NOW()) - INTERVAL 7 DAY;";

			return $query;
		}

		public function GetCompletedObjectsByUser($month, $year)
		{
			$query = "SELECT s.FirstName, 
						s.LastName, 
						SUM(s.CompletedObjects) AS CompletedObjects,
						SUM(s.PlannedObjects) AS PlannedObjects
						FROM
						(SELECT U.firstname as FirstName, U.lastname as LastName, P.objectsamount AS PlannedObjects,
						(SELECT COUNT(*) 
						FROM planitems PIs 
						INNER JOIN planstatuses PSs ON PSs.id = PIs.planstatusid
						WHERE PIs.planid = P.id 
						AND PSs.enumdescription != 'PENDING'
						AND P.locked = TRUE) as CompletedObjects
						
						FROM plans P
						INNER JOIN planusersrel PUR ON PUR.planid = P.id
						INNER JOIN monitoring_common.users U ON U.id = PUR.userid
						WHERE P.month = :month
						AND P.year = :year) s
						
						WHERE s.PlannedObjects > 0
						GROUP BY s.FirstName, s.LastName
						
						ORDER BY s.LastName, s.FirstName";

			$queryDb = $this->pdo->prepare($query);
			$queryDb->execute(array(
				':month' => $month,
				':year' => $year
			));

			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new CompletedObjectsByUserViewModel()));
			/** @var CompletedObjectsByUserViewModel[] */
			$completedObjects = $queryDb->fetchAll();

			return $completedObjects;
		}
	}
?>