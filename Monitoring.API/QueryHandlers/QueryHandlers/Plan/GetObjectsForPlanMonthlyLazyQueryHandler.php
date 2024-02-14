<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetObjectsForPlanMonthlyLazyQuery;
    use API\QueryHandlers\ViewModel\Object\ObjectListMonthlyViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectPlanUserScheduleViewModel;
    use Common\API\LazyList;

    final class GetObjectsForPlanMonthlyLazyQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;

        /**
         * @param GetObjectsForPlanMonthlyLazyQuery $query
         */
        public function QueryCommandResult($query)
        {
            $planDao = new PlanDao();

            $objects = array();
            $objectsCount = 0;

            //Upit samo ako je poslan filter područja
            if(count($query->AreaIds) > 0)
            {
                $areaIdsCommaSeparated = implode(",", $query->AreaIds);

                //Fetch data
                $queryDb = $this->pdo->query($planDao->GetObjectsForMonthlyPlanListLazy($query->StartFrom, $query->Count, $query->OrderType, $query->Search, $query->Year, $query->Month, $areaIdsCommaSeparated));
                $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectListMonthlyViewModel()));
                $objects = $queryDb->fetchAll();

                foreach ($objects as $object)
                {
                    $queryDb = $this->pdo->query($planDao->GetPlanItemUserAndScheduleDate($object->ObjectId, $query->Month, $query->Year));
                    $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectPlanUserScheduleViewModel()));
                    $object->ObjectPlanUserSchedules = $queryDb->fetchAll();
                }

                //Count
                $queryDb = $this->pdo->query($planDao->GetObjectsForMonthlyPlanListLazyCount($query->Search, $query->Year, $query->Month, $areaIdsCommaSeparated));
                $objectsCount = $queryDb->fetchColumn(0);
            }

            $result = new LazyList();
            $result->Records = $objects;
            $result->Total = $objectsCount;
            $result->Filtered = count($result->Records);

            echo json_encode($result);
        }
    }
    QueryCommandController::Respond(
        new GetObjectsForPlanMonthlyLazyQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetObjectsForPlanMonthlyLazyQuery(json_decode(file_get_contents("php://input"), true)));