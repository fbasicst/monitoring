<?php
	namespace API\QueryHandlers\QueryHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Plan\PlanDao;
	use API\QueryHandlers\Queries\Plan\GetPlansWeeklyListLazyQuery;
	use API\QueryHandlers\ViewModel\Plan\PlanWeeklyListViewModel;
	use Common\API\LazyList;

	final class GetPlansWeeklyListLazyQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::PLANS_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetPlansWeeklyListLazyQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$planDao = new PlanDao();

			$queryDb = $this->pdo->query($planDao->GetPlansListLazy(
				$query->StartFrom,
				$query->Count,
				$query->OrderType,
				$query->Search,
				$query->Month,
				$query->Year,
				$query->PlanUserId,
				$query->ObjectsAmount
				));

			//Fetch data
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanWeeklyListViewModel()));
			$plans = $queryDb->fetchAll();

			//Count
			$queryDb = $this->pdo->query($planDao->GetPlansWeeklyListLazyCount(
				$query->Search,
				$query->Month,
				$query->Year,
				$query->PlanUserId,
				$query->ObjectsAmount));
			$plansCount = $queryDb->fetchColumn(0);

			$result = new LazyList();
			$result->Records = $plans;
			$result->Total = $plansCount;
			$result->Filtered = count($result->Records);

			echo json_encode($result);
		}
	}
	QueryCommandController::Respond(
		new GetPlansWeeklyListLazyQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetPlansWeeklyListLazyQuery(json_decode(file_get_contents("php://input"), true)));