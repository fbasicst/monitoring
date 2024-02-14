<?php
    namespace API\CommandHandlers\Services;

    use API\CommandHandlers\DAO\Plan\PlanDao;
    use \PDO;

    class PlanService
    {
        /** @var PlanDao */
        private $planDao;
        /** @var  PDO */
        private $pdo;
        /** @var  PDO */
        private $onlinePdo;

        public function __construct($pdo, $onlinePdo)
        {
            $this->pdo = $pdo;
            $this->onlinePdo = $onlinePdo;
            $this->planDao = new PlanDao();
        }

        /**
         * @param $plan
         * @param $planId
         * @param $planItemId
         * @param $objectId
         */
        public function DeletePlanItem($plan, $planId, $planItemId, $objectId)
        {
            //Dobavljanje ID-ova, tako da znamo što treba brisati od podtablica (idovi su isti na remote bazi)
            //Dobavi id-ove planitemobjectitemplans
            $query = $this->pdo->prepare($this->planDao->GetPlanItemObjectItemPlanIds($planItemId));
            $query->execute();
            $planItemObjectItemPlanIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
            $planItemObjectItemMonitoringsCount = 0;

            //Dobavi id-ove planitemobjectitemmonitorings
            $query = $this->pdo->prepare($this->planDao->GetPlanItemObjectItemMonitoringIds($planItemId));
            $query->execute();
            $planItemObjectItemMonitoringIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
            $planItemObjectItemMonitoringsCount += count($planItemObjectItemMonitoringIds);

            //Ako je plan poslan -> onda OBAVEZNO prvo obriši objekt iz clouda, tek onda lokalno
            if($plan->IsUploaded == true)
            {
                //1) Spajanje na remote bazu
                //2) Remote brisanje objectitemmonitoringanalysisrel i objectitemmonitoring
                foreach($planItemObjectItemMonitoringIds as $id)
                {
                    //Brisanje analize
                    $commandDb = $this->onlinePdo->prepare($this->planDao->DeletePlanItemObjectItemMonitoringAnalysisRels());
                    $commandDb->execute(array(
                        ':id' => $id
                    ));

                    //Brisanje monitoringa
                    $commandDb = $this->onlinePdo->prepare($this->planDao->DeletePlanItemObjectItemMonitoringsFromCloud());
                    $commandDb->execute(array(
                        ':remoteid' => $id
                    ));
                }

                //3) Remote brisanje odjela
                $commandDb = $this->onlinePdo->prepare($this->planDao->DeletePlanItemObjectItems());
                $commandDb->execute(array(
                    ':id' => $planItemId
                ));

                //4) Remote brisanje objekta iz plana
                $commandDb = $this->onlinePdo->prepare($this->planDao->DeletePlanItemFromPlanFromCloud());
                $commandDb->execute(array(
                    ':planitemid' => $planItemId
                ));

                //Update broja objekata u headeru plana
                $commandDb = $this->onlinePdo->prepare($this->planDao->UpdatePlanObjectsAmountInCloud());
                $commandDb->execute(array(
                    ':planid' => $planId
                ));
            }

            //Brisanje objectitemplanschedules i objectitemplan
            foreach($planItemObjectItemPlanIds as $id)
            {
                //Brisanje rasporeda
                $commandDb = $this->pdo->prepare($this->planDao->DeletePlanItemObjectItemPlanSchedules());
                $commandDb->execute(array(
                    ':id' => $id
                ));

                //Brisanje plana
                $commandDb = $this->pdo->prepare($this->planDao->DeletePlanItemObjectItemPlans());
                $commandDb->execute(array(
                    ':id' => $id
                ));
            }
            //Brisanje objectitemmonitoringanalysisrel i objectitemmonitoring
            foreach($planItemObjectItemMonitoringIds as $id)
            {
                //Brisanje analize
                $commandDb = $this->pdo->prepare($this->planDao->DeletePlanItemObjectItemMonitoringAnalysisRels());
                $commandDb->execute(array(
                    ':id' => $id
                ));

                //Brisanje monitoringa
                $commandDb = $this->pdo->prepare($this->planDao->DeletePlanItemObjectItemMonitorings());
                $commandDb->execute(array(
                    ':id' => $id
                ));
            }

            //Brisanje odjela
            $commandDb = $this->pdo->prepare($this->planDao->DeletePlanItemObjectItems());
            $commandDb->execute(array(
                ':id' => $planItemId
            ));

            //Brisanje objekta iz plana
            $commandDb = $this->pdo->prepare($this->planDao->DeletePlanItemFromPlan());
            $commandDb->execute(array(
                ':planitemid' => $planItemId
            ));


            //Update broja objekata u headeru plana
            $commandDb = $this->pdo->prepare($this->planDao->UpdatePlanObjectsAmount());
            $commandDb->execute(array(
                ':planid' => $planId
            ));

            //Dodjeljeni objekti (ažuriranje planitemmonthlyschedules)
            //Prvo dobavi informacije o planu (mjesec,godinu)
            $queryDb = $this->pdo->prepare($this->planDao->GetPlanWeeklyInfo($planId));
            $queryDb->execute();
            $plan = $queryDb->fetch(PDO::FETCH_OBJ);

            //Pronađi jeli ovaj objekt već dodjeljivan u ovom mjesecu
            $queryDb = $this->pdo->prepare($this->planDao->GetPlanMonthlyAssignmentAmount($objectId, $plan->Month, $plan->Year));
            $queryDb->execute();
            $assignmentAmount = $queryDb->fetchColumn(0);

            if((intval($assignmentAmount) - $planItemObjectItemMonitoringsCount) == 0)
            {
                //Insert u dodijeljene planove u mjesecu
                $commandDb = $this->pdo->prepare($this->planDao->DeletePlanMonthlyAssignment());
                $commandDb->execute(array(
                    ':objectid' => $objectId,
                    ':month' => $plan->Month,
                    ':year' => $plan->Year
                ));
            }
            else
            {
                //Update dodijeljene planove u mjesecu
                $commandDb = $this->pdo->prepare($this->planDao->UpdatePlanMonthlyAssignment());
                $commandDb->execute(array(
                    ':objectid' => $objectId,
                    ':assignednumber' => intval($assignmentAmount) - $planItemObjectItemMonitoringsCount,
                    ':month' => $plan->Month,
                    ':year' => $plan->Year
                ));
            }
        }

        public function CopyPlanItemToCloud($planItem, $planItemObjectItems, $planItemObjectItemMonitorings, $planItemObjectItemMonitoringAnalysis)
        {
            //Upload objekta
            $commandDb = $this->onlinePdo->prepare($this->planDao->SavePlanItemsToCloud());
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
                ':userinsert' => $planItem->UserInsert
            ));

            //Upload planItemObjectItema(odjela)
            foreach ($planItemObjectItems as $planItemObjectItem)
            {
                $commandDb = $this->onlinePdo->prepare($this->planDao->SavePlanItemObjectItemsToCloud($planItemObjectItem->Seasonal));
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
                $commandDb = $this->onlinePdo->prepare($this->planDao->SavePlanItemObjectItemMonitoringsToCloud());
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
                $commandDb = $this->onlinePdo->prepare($this->planDao->SavePlanItemObjectItemMonitoringAnalysisToCloud());
                $commandDb->execute(array(
                    ':remoteid' => $analysis->RemoteId,
                    ':planitemobjectitemmonitoringid' => $analysis->PlanItemObjectItemMonitoringId,
                    ':analysisname' => $analysis->AnalysisName
                ));
            }

            //Update broja objekata u headeru plana
            $commandDb = $this->onlinePdo->prepare($this->planDao->UpdatePlanObjectsAmountInCloud());
            $commandDb->execute(array(
                ':planid' => $planItem->PlanId
            ));
        }
    }