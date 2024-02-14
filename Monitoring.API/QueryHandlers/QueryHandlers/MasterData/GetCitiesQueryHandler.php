<?php
	namespace API\QueryHandlers\QueryHandlers\MasterData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\MasterData\MasterDataDao;
	use API\QueryHandlers\Queries\MasterData\GetCitiesQuery;
	use API\QueryHandlers\ViewModel\MasterData\CityViewModel;

	final class GetCitiesQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetCitiesQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$masterDataDao = new MasterDataDao();
			$queryDb = $this->pdo->query($masterDataDao->GetCities());

			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new CityViewModel()));
			$cities = $queryDb->fetchAll();

			echo json_encode($cities);
		}
	}
	QueryCommandController::Respond(
		new GetCitiesQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetCitiesQuery(json_decode(file_get_contents("php://input"), true)));