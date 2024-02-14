<?php
	namespace API\QueryHandlers\QueryHandlers\RemoteData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\RemoteData\RemoteDataDao;
	use API\QueryHandlers\Queries\RemoteData\GetContractsQuery;
	use API\QueryHandlers\ViewModel\RemoteData\ContractViewModel;

	final class GetContractsQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $accountingPdo;

		/**
		 * @param GetContractsQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$remoteDataDao = new RemoteDataDao();

			$queryDb = $this->accountingPdo->query($remoteDataDao->GetContracts($query->CustomerId));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ContractViewModel()));
			$contracts = $queryDb->fetchAll();

			echo !empty($contracts) ? json_encode($contracts) : json_encode(array());
		}
	}
	QueryCommandController::Respond(
		new GetContractsQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetContractsQuery(json_decode(file_get_contents("php://input"), true)));