<?php
	namespace API\QueryHandlers\QueryHandlers\MasterData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\MasterData\MasterDataDao;
	use API\QueryHandlers\Queries\MasterData\GetUsersFromRoleQuery;
	use API\QueryHandlers\ViewModel\UserViewModel;

	final class GetUsersFromRoleQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param GetUsersFromRoleQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$masterDataDao = new MasterDataDao();
			$query = $this->pdo->query($masterDataDao->GetUsersFromRole($query->RoleName));

			$query->setFetchMode(PDO::FETCH_CLASS, get_class(new UserViewModel()));
			$users = $query->fetchAll();

			echo json_encode($users);
		}
	}
	QueryCommandController::Respond(
		new GetUsersFromRoleQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetUsersFromRoleQuery(json_decode(file_get_contents("php://input"), true)));

