<?php
	namespace API\QueryHandlers\QueryHandlers\Object;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Object\ObjectDao;
	use API\QueryHandlers\Queries\Object\GetObjectTypesQuery;
	use API\QueryHandlers\ViewModel\Object\ObjectTypeViewModel;

	final class GetObjectTypesQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetObjectTypesQuery $query
		 */
		public function QueryCommandResult($query)
		{
			$objectDao = new ObjectDao();

			$queryDb = $this->pdo->query($objectDao->GetObjectTypes());
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectTypeViewModel()));
			$types = $queryDb->fetchAll();

			echo json_encode($types);
		}
	}
	QueryCommandController::Respond(
		new GetObjectTypesQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetObjectTypesQuery(json_decode(file_get_contents("php://input"), true)));