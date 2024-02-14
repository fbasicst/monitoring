<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetPlanWeeklyInfoQuery;
    use API\QueryHandlers\ViewModel\Plan\PlanWeeklyListViewModel;

    final class GetPlanWeeklyInfoQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetPlanWeeklyInfoQuery $query
         */
        protected function QueryCommandResult($query)
        {
            $planDao = new PlanDao();
            $queryDb = $this->pdo->query($planDao->GetPlanWeeklyInfo($query->PlanId));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanWeeklyListViewModel()));
            $result = $queryDb->fetch();

            echo json_encode($result);
        }
    }
    QueryCommandController::Respond(
        new GetPlanWeeklyInfoQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanWeeklyInfoQuery(json_decode(file_get_contents("php://input"), true)));