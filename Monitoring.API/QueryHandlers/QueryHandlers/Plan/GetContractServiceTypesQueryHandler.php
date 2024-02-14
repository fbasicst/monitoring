<?php
	namespace API\QueryHandlers\QueryHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Plan\PlanDao;
	use API\QueryHandlers\Queries\Plan\GetContractServiceTypesQuery;
	use API\QueryHandlers\ViewModel\Plan\ContractServiceTypeViewModel;

	final class GetContractServiceTypesQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetContractServiceTypesQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$planDao = new PlanDao();
	
			$queryDb = $this->pdo->query($planDao->GetContractServiceTypes());
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ContractServiceTypeViewModel()));
			$types = $queryDb->fetchAll();
	
			echo json_encode($types);
		}
	}
	QueryCommandController::Respond(
		new GetContractServiceTypesQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetContractServiceTypesQuery(json_decode(file_get_contents("php://input"), true)));





		

