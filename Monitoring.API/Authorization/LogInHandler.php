<?php
	namespace API\Authorization;
	require_once('../../ClassLoader.php');

	use API\ISecurityFunctionsHandler;
	use \PDO;
	use \PDOException;
	use API\QueryCommandController;
	use API\QueryHandlers\ViewModel\UserViewModel;

	final class LoginHandler implements ISecurityFunctionsHandler
	{
		/**
		 * @param LogIn $query
		 */
		public function QueryCommandResult($query)
		{
			$conn = new Connection();
			$db = $conn->Connect('monitoring_common');

			$auth = new AuthorizationDao();
			$queryDb = $db->query($auth->CheckUserAuthorization($query->UserName, md5($query->Password)));

			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new UserViewModel()));
			$user = $queryDb->fetch();

			if(!$user)
				throw new PDOException("Access denied.");
			else
			{
				$commandDb = $db->prepare($auth->SetToken());
				$commandDb->execute(array(
					':token' => $user->Token,
					':userid' => $user->UserId
				));
				echo json_encode($user);
			}
		}

		public function ExecuteQueryCommand($loginRequest)
		{
			$this->QueryCommandResult($loginRequest);
		}
	}
	QueryCommandController::Respond(
		new LoginHandler(),
		new LogIn(json_decode(file_get_contents("php://input"), true)));