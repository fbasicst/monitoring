<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetPlanWeeklyItemsListQuery;
    use API\QueryHandlers\ViewModel\Plan\PlanItemListViewModel;

    final class GetPlanWeeklyItemsListQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetPlanWeeklyItemsListQuery $query
         */
        public function QueryCommandResult($query)
        {
            $planDao = new PlanDao();

            $queryDb = $this->pdo->query($planDao->GetPlanItemsList($query->PlanWeeklyId));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanItemListViewModel()));
            $items = $queryDb->fetchAll();

            echo json_encode($items);
        }
    }
    QueryCommandController::Respond(
        new GetPlanWeeklyItemsListQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanWeeklyItemsListQuery(json_decode(file_get_contents("php://input"), true)));