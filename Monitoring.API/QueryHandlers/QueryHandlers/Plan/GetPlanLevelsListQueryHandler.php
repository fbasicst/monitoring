<?php
	namespace API\QueryHandlers\QueryHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Plan\PlanDao;
	use API\QueryHandlers\Queries\Plan\GetPlanLevelsListQuery;
	use API\QueryHandlers\ViewModel\Plan\PlanLevelViewModel;

	//NOT IN USE
	final class GetPlanLevelsListQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetPlanLevelsListQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$planDao = new PlanDao();
			$queryDb = $this->pdo->query($planDao->GetPlanLevelsList());

			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanLevelViewModel()));
			$items = $queryDb->fetchAll();

			echo json_encode($items);
		}
	}
	QueryCommandController::Respond(
		new GetPlanLevelsListQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetPlanLevelsListQuery(json_decode(file_get_contents("php://input"), true)));