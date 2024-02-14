<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetObjectsForPlanAnnuallyLazyQuery;
    use API\QueryHandlers\ViewModel\Object\ObjectListAnnuallyViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectItemPlanScheduleDateViewModel;
    use Common\API\LazyList;

    final class GetObjectsForPlanAnnuallyLazyQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;
        
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
                $queryDb = $this->pdo->query($planDao->GetObjectsForAnnuallyPlanListLazy($query->StartFrom, $query->Count, $query->OrderType, $query->Search, $query->Year, $areaIdsCommaSeparated));
                $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectListAnnuallyViewModel()));
                $objects = $queryDb->fetchAll();

                foreach ($objects as $object)
                {
                    $queryDb = $this->pdo->query($planDao->GetObjectItemPlanScheduleDatesForAnnuallyPlan($object->ObjectId, $query->Year));
                    $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemPlanScheduleDateViewModel()));
                    $object->PlanMonths = $queryDb->fetchAll();
                }

                //Count
                $queryDb = $this->pdo->query($planDao->GetObjectsForAnnuallyPlanListLazyCount($query->Search, $query->Year, $areaIdsCommaSeparated));
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
        new GetObjectsForPlanAnnuallyLazyQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetObjectsForPlanAnnuallyLazyQuery(json_decode(file_get_contents("php://input"), true)));