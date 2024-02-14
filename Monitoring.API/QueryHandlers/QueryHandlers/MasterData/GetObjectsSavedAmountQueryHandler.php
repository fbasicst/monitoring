<?php
	namespace API\QueryHandlers\QueryHandlers\MasterData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\MasterData\MasterDataDao;
	use API\QueryHandlers\Queries\MasterData\GetObjectsSavedAmountQuery;
	use API\QueryHandlers\ViewModel\MasterData\ObjectsSavedAmountViewModel;

	final class GetObjectsSavedAmountQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetObjectsSavedAmountQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$masterDataDao = new MasterDataDao();

			$queryDb = $this->pdo->query($masterDataDao->GetObjectsSavedAmount());
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectsSavedAmountViewModel()));
			$result = $queryDb->fetchAll();

			echo json_encode($result);
		}
	}
	QueryCommandController::Respond(
		new GetObjectsSavedAmountQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetObjectsSavedAmountQuery(json_decode(file_get_contents("php://input"), true)));