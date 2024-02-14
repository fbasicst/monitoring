<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetPlanScheduleLevelsListQuery;
    use API\QueryHandlers\ViewModel\Plan\PlanScheduleLevelViewModel;

    final class GetPlanScheduleLevelsListQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetPlanScheduleLevelsListQuery $query
         */
        public function QueryCommandResult($query)
        {
            $planDao = new PlanDao();

            $queryDb = $this->pdo->query($planDao->GetPlanScheduleLevelsList());
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanScheduleLevelViewModel()));
            $results = $queryDb->fetchAll();

            echo json_encode($results);
        }
    }
    QueryCommandController::Respond(
        new GetPlanScheduleLevelsListQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanScheduleLevelsListQuery(json_decode(file_get_contents("php://input"), true)));