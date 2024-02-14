<?php
	namespace API\QueryHandlers\QueryHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Plan\PlanDao;
	use API\QueryHandlers\Queries\Plan\GetAnalysisListQuery;
	use API\QueryHandlers\ViewModel\Plan\AnalysisViewModel;

	final class GetAnalysisListQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
		/** @var  PDO */
		public $pdo;
		
		/**
		 * @param GetAnalysisListQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$planDao = new PlanDao();
			
			$queryDb = $this->pdo->query($planDao->GetAnalysisList());
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new AnalysisViewModel()));
			$items = $queryDb->fetchAll();

			echo json_encode($items);
		}
	}
	QueryCommandController::Respond(
		new GetAnalysisListQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetAnalysisListQuery(json_decode(file_get_contents("php://input"), true)));