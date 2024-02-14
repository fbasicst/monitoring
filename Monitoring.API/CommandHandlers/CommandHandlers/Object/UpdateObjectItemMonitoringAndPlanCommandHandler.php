<?php
    namespace API\CommandHandlers\CommandHandlers\Object;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\CommandHandlers\DAO\Monitoring\MonitoringDao;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Object\UpdateObjectItemMonitoringAndPlanCommand;

    final class UpdateObjectItemMonitoringAndPlanCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
        /** @var  PDO */
        public $pdo;

        /**
         * @param UpdateObjectItemMonitoringAndPlanCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $monitoringDao = new MonitoringDao();
            $planDao = new PlanDao();

            $commandDb = $this->pdo->prepare($monitoringDao->UpdateObjectItemMonitoring());
            $commandDb->execute(array(
                ':contractservicetypeid' => $command->ContractServiceTypeId,
                ':serviceitemid' => $command->ServiceItemId,
                ':quantity' => $command->Quantity,
                ':description' => $command->Description,
                ':objectitemmonitoringid' => $command->ObjectItemMonitoringId
            ));

            //Dobavi sve idove analiza iz monitoringa
            $queryDb = $this->pdo->prepare($monitoringDao->GetObjectItemMonitoringAnalysisRelsIdsFromMonitoring($command->ObjectItemMonitoringId));
            $queryDb->execute();
            $analysisIds = $queryDb->fetchAll(PDO::FETCH_COLUMN);
            //Brisanje postojećih analiza
            foreach ($analysisIds as $analysisId)
            {
                $commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemMonitoringAnalisysRels());
                $commandDb->execute(array(
                    ':id' => $analysisId
                ));
            }
            //Spremanje novih analiza
            foreach ($command->AnalysisIds as $analysisId)
            {
                $commandDb = $this->pdo->prepare($monitoringDao->SaveObjectItemMonitoringAnalysis());
                $commandDb->execute(array(
                    ':objectitemmonitoringid' => $command->ObjectItemMonitoringId,
                    ':analysisid' => $analysisId
                ));
            }

            //Spremi objectitemplan
            $commandDb = $this->pdo->prepare($planDao->UpdateObjectItemPlan($command->ValudFurther));
            $commandDb->execute(array(
                ':schedulelevelid' => $command->PlanLevelId,
                ':monthlyrepeats' => $command->MonthlyRepeats,
                ':enddate' => $command->EndDate,
                ':objectitemmonitoringid' => $command->ObjectItemMonitoringId
            ));
            //Dobavi sve scheduleidove plana
            $queryDb = $this->pdo->prepare($planDao->GetObjectItemPlanScheduleIdsFromMonitoring($command->ObjectItemMonitoringId));
            $queryDb->execute();
            $objectItemPlanScheduleIds = $queryDb->fetchAll(PDO::FETCH_COLUMN);

            //Dobavi planid from monitoringid
            $queryDb = $this->pdo->prepare($planDao->GetObjectItemPlanIdFromMonitoring($command->ObjectItemMonitoringId));
            $queryDb->execute();
            $objectItemPlanId = $queryDb->fetchColumn(0);

            //Brisanje scheduleidova plana
            foreach ($objectItemPlanScheduleIds as $objectItemPlanScheduleId)
            {
                $commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemPlanSchedules());
                $commandDb->execute(array(
                    ':id' => $objectItemPlanScheduleId
                ));
            }
            //Spremanje novih analiza
            foreach ($command->Months as $month)
            {
                $commandDb = $this->pdo->prepare($planDao->SaveObjectItemPlanSchedule());
                $commandDb->execute(array(
                    ':schedulemonth' => $month,
                    ':objectitemplanid' => $objectItemPlanId
                ));
            }
        }
    }
    QueryCommandController::Respond(
        new UpdateObjectItemMonitoringAndPlanCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new UpdateObjectItemMonitoringAndPlanCommand(json_decode(file_get_contents("php://input"), true)));