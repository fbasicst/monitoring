<?php
	namespace API\QueryHandlers\QueryHandlers\RemoteData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\RemoteData\RemoteDataDao;
	use API\QueryHandlers\Queries\RemoteData\GetCustomersQuery;
	use API\QueryHandlers\ViewModel\RemoteData\CustomerViewModel;

	final class GetCustomersQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $accountingPdo;

		/**
		 * @param GetCustomersQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$remoteDataDao = new RemoteDataDao();

			$queryDb = $this->accountingPdo->query($remoteDataDao->GetCustomers($query->SearchString));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new CustomerViewModel()));
			$customers = $queryDb->fetchAll();

			echo !empty($customers) ? json_encode($customers) : json_encode(array());
		}
	}
	QueryCommandController::Respond(
		new GetCustomersQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetCustomersQuery(json_decode(file_get_contents("php://input"), true)));