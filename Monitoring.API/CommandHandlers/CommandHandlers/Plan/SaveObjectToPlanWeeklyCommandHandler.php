<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use PDO;
    use PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\DAO\MasterData\MasterDataDao;
    use API\CommandHandlers\DAO\Monitoring\MonitoringDao;
    use API\CommandHandlers\DAO\Object\ObjectDao;
    use API\CommandHandlers\Services\PlanService;
    use API\CommandHandlers\Commands\Plan\SaveObjectToPlanWeeklyCommand;

    final class SaveObjectToPlanWeeklyCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;
        /** @var  PDO */
        public $onlinePdo;

        /**
         * @var SaveObjectToPlanWeeklyCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planDao = new PlanDao();
            $masterDataDao = new MasterDataDao();
            $objectDao = new ObjectDao();
            $monitoringDao = new MonitoringDao();

            $queryDb = $this->pdo->prepare($planDao->GetPlanWeeklyInfo($command->PlanWeeklyId));
            $queryDb->execute();
            $plan = $queryDb->fetch(PDO::FETCH_OBJ);

            //KONTROLE:
            //Jeli plan zaključan
            if($plan->IsLocked == true)
            {
                throw new PDOException("Dodavanje objekata u plan nije moguće. Plan je zaključan.");
            }

            //Provjeri jeli datum obrade objekta unutar intervala headera tjednog plana
            $query = $this->pdo->prepare($planDao->CheckPlanWeeklyDateRange($command->PlanWeeklyId, $command->ScheduleDate));
            $query->execute();
            $count = $query->fetchColumn(0);
            if($count == 0)
            {
                throw new PDOException("Dani datum nije u rasponu tjednog plana.");
            }

            //Dobavi neradne dane
            $year = date("Y", strtotime($command->ScheduleDate));
            $month = date("m", strtotime($command->ScheduleDate));
            $query = $this->pdo->prepare($masterDataDao->GetNonWorkingDays($year));
            $query->execute();
            $nonWorkingDays = $query->fetchAll(PDO::FETCH_COLUMN);

            if(in_array($command->ScheduleDate, $nonWorkingDays))
            {
                throw new PDOException("Dani datum je praznik");
            }

            if(date('N', strtotime($command->ScheduleDate)) >= 6)
            {
                throw new PDOException("Dani datum je vikend");
            }

            //Provjeri jeli se smije prebaciti objekt na način da mu se provjeri kvota ponavljanja u mjesecu
            $query = $this->pdo->prepare($planDao->CheckObjectItemPlanRepeatQuote($command->SelectedObjectId, $month, $year));
            $query->execute();
            $count = $query->fetchColumn(0);
            if($count != 0)
            {
                throw new PDOException("Objekt je već ispunio kvotu dodjeljivanja u plan za mjesec.");
            }

            //TODO Provjeriti i jeli objekt otkazan u nekom planu

            //Dobavi status "U tijeku"
            $query = $this->pdo->prepare($planDao->GetPlanStatus('PENDING'));
            $query->execute();
            $planStatus = $query->fetch(PDO::FETCH_OBJ);

            //Spremi planitem
            $commandDb = $this->pdo->prepare($planDao->SavePlanItemsWeekly());
            $commandDb->execute(array(
                ':planid' => $command->PlanWeeklyId,
                ':objectid' => $command->SelectedObjectId,
                ':scheduledate' => $command->ScheduleDate,
                ':planstatusid' => $planStatus->Id,
                ':notes' => $command->Notes,
                ':useridinsert' => $command->UserId
            ));
            $planItemId = $this->pdo->lastInsertId();

            //Kopiranje podataka u plan
            //Dobavi odjele objekta, koji nisu ispunili kvotu ponavljanja za mjesec
            $query = $this->pdo->prepare($objectDao->GetObjectItemsIdsForTransferList($command->SelectedObjectId, $command->ScheduleDate));
            $query->execute();
            $objectItemsIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
            $objectItemMonitoringsCount = 0;

            foreach ($objectItemsIds as $objectItemId)
            {
                $commandDb = $this->pdo->prepare($planDao->CopyObjectItemToPlan());
                $commandDb->execute(array(
                    ':planitemid' => $planItemId,
                    ':objectitemid' => $objectItemId
                ));
                $planItemObjectItemId = $this->pdo->lastInsertId();

                //Dobavi monitoringe odjela, koji nisu ispunili kvotu ponavljanja za mjesec
                $query = $this->pdo->prepare($monitoringDao->GetObjectItemMonitoringIdsFromObjectItemForTransferList($objectItemId, $command->ScheduleDate));
                $query->execute();
                $objectItemMonitoringsIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
                $objectItemMonitoringsCount += count($objectItemMonitoringsIds);

                foreach ($objectItemMonitoringsIds as $objectItemMonitoringId)
                {
                    $commandDb = $this->pdo->prepare($planDao->CopyObjectItemMonitoringToPlan());
                    $commandDb->execute(array(
                        ':planitemobjectitemid' => $planItemObjectItemId,
                        ':objectitemmonitoringid' => $objectItemMonitoringId
                    ));
                    $planItemObjectItemMonitoringId = $this->pdo->lastInsertId();

                    //Kopiraj analize
                    $commandDb = $this->pdo->prepare($planDao->CopyObjectItemMonitoringAnalysisToPlan());
                    $commandDb->execute(array(
                        ':planitemobjectitemmonitoringid' => $planItemObjectItemMonitoringId,
                        ':objectitemmonitoringid' => $objectItemMonitoringId
                    ));

                    //Dobavi planove monitoringa
                    $query = $this->pdo->prepare($planDao->GetObjectItemPlanIdFromMonitoring($objectItemMonitoringId));
                    $query->execute();
                    $objectItemPlansIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);

                    foreach ($objectItemPlansIds as $objectItemPlanId)
                    {
                        $commandDb = $this->pdo->prepare($planDao->CopyObjectItemPlanToPlan());
                        $commandDb->execute(array(
                            ':planitemobjectitemmonitoringid' => $planItemObjectItemMonitoringId,
                            ':planitemobjectitemid' => $planItemObjectItemId,
                            ':objectitemplanid' => $objectItemPlanId
                        ));
                        $planItemObjectItemPlanId = $this->pdo->lastInsertId();

                        //Kopiraj rasporede
                        $commandDb = $this->pdo->prepare($planDao->CopyObjectItemPlanSchedulesToPlan());
                        $commandDb->execute(array(
                            ':planitemobjectitemplanid' => $planItemObjectItemPlanId,
                            ':objectitemplanid' => $objectItemPlanId
                        ));
                    }
                }
            }

            //Dodjeljeni objekti (ažuriranje planitemmonthlyschedules)
            //Pronađi jeli ovaj objekt već dodjeljivan u ovom mjesecu
            $queryDb = $this->pdo->prepare($planDao->GetPlanMonthlyAssignmentAmount($command->SelectedObjectId, $month, $year));
            $queryDb->execute();
            $assignmentAmount = $queryDb->fetchColumn(0);

            if($assignmentAmount == 0)
            {
                //Insert u dodijeljene planove u mjesecu
                $commandDb = $this->pdo->prepare($planDao->SavePlanMonthlyAssignment());
                $commandDb->execute(array(
                    ':objectid' => $command->SelectedObjectId,
                    ':assignednumber' => $objectItemMonitoringsCount,
                    ':month' => $month,
                    ':year' => $year
                ));
            }
            else
            {
                //Update dodijeljene planove u mjesecu
                $commandDb = $this->pdo->prepare($planDao->UpdatePlanMonthlyAssignment());
                $commandDb->execute(array(
                    ':objectid' => $command->SelectedObjectId,
                    ':assignednumber' => intval($assignmentAmount) + $objectItemMonitoringsCount,
                    ':month' => $month,
                    ':year' => $year
                ));
            }


            //Nakon uspješno spremljenog plana lokalno -> ovdje pokušati kopirati objekt u remote bazu, ako ne uspije
            // -> izbrisati lokalno (pozvati plan service)
            if($plan->IsUploaded == true)
            {
                $planService = new PlanService($this->pdo, $this->onlinePdo);

                //Dobavi planitem(objekt)
                $queryDb = $this->pdo->prepare($planDao->GetPlanItemsForCloud($command->PlanWeeklyId, $planItemId));
                $queryDb->execute();
                $planItem = $queryDb->fetch(PDO::FETCH_OBJ);

                //Dobavi planitemobjectiteme(odjele)
                $queryDb = $this->pdo->prepare($planDao->GetPlanItemObjectItemsForCloud($command->PlanWeeklyId, $planItemId));
                $queryDb->execute();
                $planItemObjectItems = $queryDb->fetchAll(PDO::FETCH_OBJ);

                //Dobavi sve planitemobjectitemmonitoringe(monitoringe odjela)
                $queryDb = $this->pdo->prepare($planDao->GetPlanItemObjectItemMonitoringsForCloud($command->PlanWeeklyId, $planItemId));
                $queryDb->execute();
                $planItemObjectItemMonitorings = $queryDb->fetchAll(PDO::FETCH_OBJ);

                //Dobavi sve planitemobjectitemmonitoringanalysise(analize monitoringe odjela)
                $queryDb = $this->pdo->prepare($planDao->GetPlanItemObjectItemMonitoringAnalysisForCloud($command->PlanWeeklyId, $planItemId));
                $queryDb->execute();
                $planItemObjectItemMonitoringAnalysis = $queryDb->fetchAll(PDO::FETCH_OBJ);

                try
                {
                    //KOPIRANJE objekta u plan u remote bazi
                    $planService->CopyPlanItemToCloud($planItem, $planItemObjectItems, $planItemObjectItemMonitorings, $planItemObjectItemMonitoringAnalysis);
                }
                catch(PDOException $e)
                {
                    //Nakon neusješnog kopiranja u udaljenu bazu, forsiraj brisanje spremljenog objekta iz plana
                    $planService->DeletePlanItem($plan, $command->PlanWeeklyId, $planItemId, $planItem->ObjectId);
                    throw new PDOException("Dodavanje novog objekta u plan nije moguće, jer slanje objekta u plan nije uspjelo.");
                }
            }

            //Update headera s brojem objekata
            $commandDb = $this->pdo->prepare($planDao->UpdatePlanObjectsAmount());
            $commandDb->execute(array(
                ':planid' => $command->PlanWeeklyId
            ));
        }
    }
    QueryCommandController::Respond(
        new SaveObjectToPlanWeeklyCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new SaveObjectToPlanWeeklyCommand(json_decode(file_get_contents("php://input"), true)));


