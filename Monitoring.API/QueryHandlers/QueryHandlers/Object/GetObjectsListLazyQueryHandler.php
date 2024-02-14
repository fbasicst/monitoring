<?php
	namespace API\QueryHandlers\QueryHandlers\Object;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Object\ObjectDao;
	use API\QueryHandlers\Queries\Object\GetObjectsListLazyQuery;
	use API\QueryHandlers\ViewModel\Object\ObjectListViewModel;
	use Common\API\LazyList;

	final class GetObjectsListLazyQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_READ;
		/** @var  PDO */
		public $pdo;
		/**
		 * @param GetObjectsListLazyQuery $query
		 */
		public function QueryCommandResult($query)
		{
			$objectDao = new ObjectDao();

			$isActive = !empty($query->IsActive) ? 'true' : 'false';
			$queryDb = $this->pdo->query($objectDao->GetObjectsListLazy($query->StartFrom, $query->Count, $query->OrderType, $query->Search, $isActive));
			//Fetch data
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectListViewModel()));
			$objects = $queryDb->fetchAll();

			//Count
			$queryDb = $this->pdo->query($objectDao->GetObjectsListLazyCount($query->Search, $isActive));
			$objectsCount = $queryDb->fetchColumn(0);

			$result = new LazyList();
			$result->Records = $objects;
			$result->Total = $objectsCount;
			$result->Filtered = count($result->Records);

			echo json_encode($result);
		}
	}
	QueryCommandController::Respond(
		new GetObjectsListLazyQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetObjectsListLazyQuery(json_decode(file_get_contents("php://input"), true)));