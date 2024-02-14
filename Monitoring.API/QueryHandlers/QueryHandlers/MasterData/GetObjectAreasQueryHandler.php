<?php
	namespace API\QueryHandlers\QueryHandlers\MasterData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\MasterData\MasterDataDao;
	use API\QueryHandlers\Queries\MasterData\GetObjectAreasQuery;
	use API\QueryHandlers\ViewModel\MasterData\AreaViewModel;

	final class GetObjectAreasQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetObjectAreasQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$masterDataDao = new MasterDataDao();
			$queryDb = $this->pdo->query($masterDataDao->GetObjectAreas());

			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new AreaViewModel()));
			$areas = $queryDb->fetchAll();

			echo json_encode($areas);
		}
	}
	QueryCommandController::Respond(
		new GetObjectAreasQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetObjectAreasQuery(json_decode(file_get_contents("php://input"), true)));