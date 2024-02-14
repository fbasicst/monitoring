<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetPlanItemDetailsFullQuery;
    use API\QueryHandlers\ViewModel\Object\ObjectFullViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectGeneralInfoViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectDepartmentViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectItemMonitoringViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectItemMonitoringAnalysisViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectItemPlanScheduleDateViewModel;

    final class GetPlanItemDetailsFullQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetPlanItemDetailsFullQuery $query
         */
        public function QueryCommandResult($query)
        {
            $_planDao = new PlanDao();
            $objectFull = new ObjectFullViewModel();

            //Učitaj detalje o planu
            $queryDb = $this->pdo->query($_planDao->GetPlanItemDetails($query->PlanItemId));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectGeneralInfoViewModel()));
            $objectFull->GeneralInfo = $queryDb->fetch();

            //Dobavi objectIteme
            $queryDb = $this->pdo->query($_planDao->GetPlanItemObjectItems($query->PlanItemId));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectDepartmentViewModel()));
            $objectFull->Departments = $queryDb->fetchAll();

            foreach($objectFull->Departments as $Department)
            {
                $queryDb = $this->pdo->query($_planDao->GetPlanItemObjectItemMonitoringsAndPlans($Department->Id));
                $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemMonitoringViewModel()));
                $Department->Monitorings = $queryDb->fetchAll();

                //Dobavi analize i raspored
                foreach($Department->Monitorings as $Monitoring)
                {
                    $queryDb = $this->pdo->query($_planDao->GetPlanItemObjectItemMonitoringAnalysis($Monitoring->Id));
                    $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemMonitoringAnalysisViewModel()));
                    $Monitoring->Analysis = $queryDb->fetchAll();

                    if($Monitoring->ScheduleLevelEnum == 'MONTHLY')
                    {
                        $queryDb = $this->pdo->query($_planDao->GetPlanItemObjectItemPlanScheduleDates($Monitoring->PlanId));
                        $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemPlanScheduleDateViewModel()));
                        $Monitoring->ScheduleDates = $queryDb->fetchAll();
                    }
                    else if($Monitoring->ScheduleLevelEnum == 'FIXED_DATES')
                    {
                        $queryDb = $this->pdo->query($_planDao->GetPlanItemObjectItemPlanScheduleFixedDates($Monitoring->PlanId));
                        $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemPlanScheduleDateViewModel()));
                        $Monitoring->ScheduleDates = $queryDb->fetchAll();
                    }
                }
            }
            echo json_encode($objectFull);
        }
    }
    QueryCommandController::Respond(
        new GetPlanItemDetailsFullQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanItemDetailsFullQuery(json_decode(file_get_contents("php://input"), true)));