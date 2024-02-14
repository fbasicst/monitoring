<?php
	namespace API\QueryHandlers\QueryHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Plan\PlanDao;
	use API\QueryHandlers\Queries\Plan\GetServiceItemsQuery;
	use API\QueryHandlers\ViewModel\Plan\ServiceItemViewModel;

	final class GetServiceItemsQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetServiceItemsQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$planDao = new PlanDao();

			$queryDb = $this->pdo->query($planDao->GetServiceItems());
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ServiceItemViewModel()));
			$items = $queryDb->fetchAll();

			echo json_encode($items);
		}
	}
	QueryCommandController::Respond(
		new GetServiceItemsQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetServiceItemsQuery(json_decode(file_get_contents("php://input"), true)));