<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use PDO;
    use PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\SyncPlanToWebCommand;

    final class SyncPlanToWebCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;
        /** @var  PDO */
        public $onlinePdo;

        /**
         * @param SyncPlanToWebCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planDao = new PlanDao();

            //Prvo dobavi informacije o planu (mjesec,godinu)
            $queryDb = $this->pdo->prepare($planDao->GetPlanWeeklyInfo($command->PlanId));
            $queryDb->execute();
            $plan = $queryDb->fetch(PDO::FETCH_OBJ);

            //Kontrole prije kopiranja na web
            //1) jeli plan već uploadan
            if($plan->IsUploaded == true)
            {
                throw new PDOException("Slanje nije moguće. Plan je već poslan u cloud.");
            }

            //2) jeli plan zaključan
            if($plan->IsLocked == true)
            {
                throw new PDOException("Slanje nije moguće. Plan je zaključan.");
            }

            //Dobavi sve planiteme(objekte)
            $queryDb = $this->pdo->prepare($planDao->GetPlanItemsForCloud($command->PlanId));
            $queryDb->execute();
            $planItems = $queryDb->fetchAll(PDO::FETCH_OBJ);

            //Dobavi sve planitemobjectiteme(odjele)
            $queryDb = $this->pdo->prepare($planDao->GetPlanItemObjectItemsForCloud($command->PlanId));
            $queryDb->execute();
            $planItemObjectItems = $queryDb->fetchAll(PDO::FETCH_OBJ);

            //Dobavi sve planitemobjectitemmonitoringe(monitoringe odjela)
            $queryDb = $this->pdo->prepare($planDao->GetPlanItemObjectItemMonitoringsForCloud($command->PlanId));
            $queryDb->execute();
            $planItemObjectItemMonitorings = $queryDb->fetchAll(PDO::FETCH_OBJ);

            //Dobavi sve planitemobjectitemmonitoringanalysise(analize monitoringe odjela)
            $queryDb = $this->pdo->prepare($planDao->GetPlanItemObjectItemMonitoringAnalysisForCloud($command->PlanId));
            $queryDb->execute();
            $planItemObjectItemMonitoringAnalysis = $queryDb->fetchAll(PDO::FETCH_OBJ);

            if(count($planItemObjectItemMonitorings) == 0)
            {
                throw new PDOException("Broj monitoringa je 0. Slanje plana nije moguće.");
            }

            //Upload headera plana
            $commandDb = $this->onlinePdo->prepare($planDao->SavePlanHeaderWeeklyToCloud());
            $commandDb->execute(array(
                ':remoteid' => $plan->PlanId,
                ':startdate' => $plan->StartDate,
                ':enddate' => $plan->EndDate,
                ':month' => $plan->Month,
                ':year' => $plan->Year,
                ':daysamount' => $plan->DaysAmount,
                ':objectsamount' => $plan->ObjectsAmount,
                ':label' => $plan->Label,
                ':planuserid' => $plan->PlanUserId,
                ':userinsert' => $command->UserId,
                ':usercontrolled' => $plan->UserControlled
            ));

            //Upload planitema
            foreach ($planItems as $planItem)
            {
                $commandDb = $this->onlinePdo->prepare($planDao->SavePlanItemsToCloud());
                $commandDb->execute(array(
                    ':remoteid' => $planItem->RemoteId,
                    ':planid' => $planItem->PlanId,
                    ':scheduledate' => $planItem->ScheduleDate,
                    ':planstatusenum' => $planItem->PlanStatusEnum,
                    ':notes' => $planItem->Notes,
                    ':customername' => $planItem->CustomerName,
                    ':customeraddressfull' => $planItem->CustomerAddressFull,
                    ':customeroib' => $planItem->CustomerOib,
                    ':objectid' => $planItem->ObjectId,
                    ':objectname' => $planItem->ObjectName,
                    ':objectaddressfull' => $planItem->ObjectAddressFull,
                    ':objectcontactperson' => $planItem->ObjectContactPerson,
                    ':objectcontactphone' => $planItem->ObjectContactPhone,
                    ':userinsert' => $command->UserId
                ));
            }

            //Upload planItemObjectItema(odjela)
            foreach ($planItemObjectItems as $planItemObjectItem)
            {
                $commandDb = $this->onlinePdo->prepare($planDao->SavePlanItemObjectItemsToCloud($planItemObjectItem->Seasonal));
                $commandDb->execute(array(
                    ':remoteid' => $planItemObjectItem->RemoteId,
                    ':planitemid' => $planItemObjectItem->PlanItemId,
                    ':name' => $planItemObjectItem->Name,
                    ':sublocation' => $planItemObjectItem->Sublocation
                ));
            }

            //Upload planItemObjectItemMonitoringa
            foreach ($planItemObjectItemMonitorings as $planItemObjectItemMonitoring)
            {
                $commandDb = $this->onlinePdo->prepare($planDao->SavePlanItemObjectItemMonitoringsToCloud());
                $commandDb->execute(array(
                    ':remoteid' => $planItemObjectItemMonitoring->RemoteId,
                    ':planitemobjectitemid' => $planItemObjectItemMonitoring->PlanItemObjectItemId,
                    ':contractservicetypename' => $planItemObjectItemMonitoring->ContractServiceTypeName,
                    ':servicefull' => $planItemObjectItemMonitoring->ServiceFull,
                    ':quantity' => $planItemObjectItemMonitoring->Quantity,
                    ':description' => $planItemObjectItemMonitoring->Description
                ));
            }

            //Upload planItemObjectItemMonitoringAnalysis(analiza)
            foreach ($planItemObjectItemMonitoringAnalysis as $analysis)
            {
                $commandDb = $this->onlinePdo->prepare($planDao->SavePlanItemObjectItemMonitoringAnalysisToCloud());
                $commandDb->execute(array(
                    ':remoteid' => $analysis->RemoteId,
                    ':planitemobjectitemmonitoringid' => $analysis->PlanItemObjectItemMonitoringId,
                    ':analysisname' => $analysis->AnalysisName
                ));
            }

            //Flag uploaded = true nakon što je plan kopiran u cloud
            $commandDb = $this->pdo->prepare($planDao->SetPlanUploadFlag());
            $commandDb->execute(array(
                ':planid' => $command->PlanId
            ));
        }
    }
    QueryCommandController::Respond(
        new SyncPlanToWebCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new SyncPlanToWebCommand(json_decode(file_get_contents("php://input"), true)));