<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetPlanStatusesListQuery;
    use API\QueryHandlers\ViewModel\Plan\PlanStatusViewModel;

    final class GetPlanStatusesListQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
        /** @var  PDO */
        public $pdo;

        /**
         * @param GetPlanStatusesListQuery $query
         */
        protected function QueryCommandResult($query)
        {
            $planDao = new PlanDao();

            $queryDb = $this->pdo->query($planDao->GetPlanStatusesList());
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanStatusViewModel()));
            $items = $queryDb->fetchAll();

            echo json_encode($items);
        }
    }
    QueryCommandController::Respond(
        new GetPlanStatusesListQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanStatusesListQuery(json_decode(file_get_contents("php://input"), true)));