<?php
	namespace API\Authorization;

	use API\Models\Environment;
	use PDO;

	class AuthorizationDao
	{
		/** @var PDO */
		private $pdo;

		public function __construct($pdo = null) //todo privremeno optional da ne pucaju ostali pozivi
		{
			$this->pdo = $pdo;
		}

		public function CheckUserAuthorization($username, $password)
		{
			$query = "SELECT U.id AS UserId,
							 U.firstname AS FirstName,
							 U.lastname AS LastName, 		
							 E.name AS Environment,							 
							 E.Comment AS EnvironmentName 
							
					  FROM monitoring_common.users U 
					  INNER JOIN monitoring_common.environments E ON E.id = U.environmentid 
					  WHERE U.username = '$username' 
					  AND U.password = '$password' 
					  LIMIT 1";		
			
			return $query;			
		}

		public function SetToken()
		{
			$query = "INSERT INTO monitoring_common.tokens (token, userid) 
					  VALUES (:token, :userid)
					  ON DUPLICATE KEY 
						UPDATE token = :token;";

			return $query;
		}

		public function GetUserFunctionsFromToken($token)
		{
			$query = "SELECT UF.enumdescription AS UserFunctionEnumDescription,
							 E.name AS Environment
					  FROM tokens T
					  INNER JOIN users U ON U.id = T.userid
					  INNER JOIN environments E ON E.id = U.environmentid
					  INNER JOIN userrolesrel URR ON URR.userid = T.userid
					  INNER JOIN userroles UR ON UR.id = URR.userroleid
					  INNER JOIN userfunctionsrolesrel UFRR ON UFRR.userroleid = UR.id
					  INNER JOIN userfunctions UF ON UF.id = UFRR.userfunctionid

					  WHERE T.token = '$token'";

			return $query;
		}

		/**
		 * @param $token
		 * @return Environment
		 */
		public function GetUserEnvironment($token)
		{
			$query = "SELECT E.id AS Id,
							 E.name AS Name,
							 E.accounting_name AS AccountingName,
							 E.remote_name AS RemoteName,
							 E.comment AS Comment
					  FROM environments E
					  INNER JOIN users U ON U.environmentid = E.id
					  INNER JOIN tokens T ON T.userid = U.id 						
					  WHERE T.token = :token";

			$queryDb = $this->pdo->prepare($query);
			$queryDb->execute(array(
				':token' => $token
			));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new Environment()));
			/** @var Environment $environment */
			$environment = $queryDb->fetch();

			return $environment;
		}
	}