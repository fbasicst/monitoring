<?php
	namespace API\QueryHandlers\QueryHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Plan\PlanDao;
	use API\QueryHandlers\Queries\Plan\GetObjectsForTransferListLazyQuery;
	use API\QueryHandlers\ViewModel\Plan\PlanWeeklyListViewModel;
	use API\QueryHandlers\ViewModel\Object\ObjectListViewModel;
	use Common\API\LazyList;

	final class GetObjectsForTransferListLazyQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetObjectsForTransferListLazyQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$planDao = new PlanDao();

			//Učitaj informacije o headeru plana, i u sljedeći upit pošalji mjesec,godinu tog plana
			$queryDb = $this->pdo->query($planDao->GetPlanWeeklyInfo($query->PlanId));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanWeeklyListViewModel()));
			$planWeeklyInfo = $queryDb->fetch();

			//Fetch data
			$queryDb = $this->pdo->query($planDao->GetObjectsForTransferListLazy(
				$query->StartFrom,
				$query->Count,
				$query->OrderType,
				$query->Search,
				$query->AreaId,
				$query->CityId,
				$query->ObjectTypeId,
				$query->ContractServiceTypeId,
				$query->ServiceItemId,
				$query->AnalysisId,
				$planWeeklyInfo->Month,
				$planWeeklyInfo->Year));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectListViewModel()));
			$objects = $queryDb->fetchAll();

			//Count
			$queryDb = $this->pdo->query($planDao->GetObjectsForTransferListLazyCount(
				$query->Search,
				$query->AreaId,
				$query->CityId,
				$query->ObjectTypeId,
				$query->ContractServiceTypeId,
				$query->ServiceItemId,
				$query->AnalysisId,
				$planWeeklyInfo->Month,
				$planWeeklyInfo->Year));
			$objectsCount = $queryDb->fetchColumn(0);

			$result = new LazyList();
			$result->Records = $objects;
			$result->Total = $objectsCount;
			$result->Filtered = count($result->Records);

			echo json_encode($result);
		}
	}
	QueryCommandController::Respond(
		new GetObjectsForTransferListLazyQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetObjectsForTransferListLazyQuery(json_decode(file_get_contents("php://input"), true)));