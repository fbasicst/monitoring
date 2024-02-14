<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\CommandHandlers\DAO\Object\ObjectDao;
    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use PDO;
    use PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\UpdatePlanWeeklyItemsStatusesCommand;

    final class UpdatePlanWeeklyItemsStatusesCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;

        /**
         * @param UpdatePlanWeeklyItemsStatusesCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planDao = new PlanDao($this->pdo);
            $objectDao = new ObjectDao($this->pdo);

            //Provjeri jeli plan zaključan
            $queryDb = $this->pdo->prepare($planDao->CheckPlanWeeklyIsLocked($command->PlanId));
            $queryDb->execute();

            $isLocked = $queryDb->fetchColumn(0);
            if($isLocked == true)
            {
                throw new PDOException("Plan je zaključan");
            }

            //Update planitema sa poslanim statusom
            $queryDb = $this->pdo->prepare($planDao->UpdatePlanItemsWeeklyStatus());
            $queryDb->execute(array(
                ':planid' => $command->PlanId,
                ':planstatusid' => $command->PlanStatusId
            ));

            $canceledPlanItems = array();
            //Postavi da je plan zaključan, ako je uključio zaključavanje, i ako status nije "U tijeku"
            if($command->LockPlan == true and $command->PlanStatusEnum != 'PENDING')
            {
                $queryDb = $this->pdo->prepare($planDao->LockPlan());
                $queryDb->execute(array(
                    ':planid' => $command->PlanId
                ));

                //Ažuriranje planitemmonthlyschedules - ako ima objekata koji su ODGOĐENI
                //Prvo dobavi informacije o planu (mjesec,godinu)
                $queryDb = $this->pdo->prepare($planDao->GetPlanWeeklyInfo($command->PlanId));
                $queryDb->execute();
                $plan = $queryDb->fetch(PDO::FETCH_OBJ);

                //Dobavi sve ODGOĐENE objekte iz zaključanog plana
                $queryDb = $this->pdo->prepare($planDao->GetDelayedPlanItemsIds($command->PlanId));
                $queryDb->execute();
                $planItemsIds = $queryDb->fetchAll(PDO::FETCH_COLUMN, 0);

                foreach ($planItemsIds as $planItemId)
                {
                    $queryDb = $this->pdo->prepare($planDao->GetPlanItemMonitoringsCount($command->PlanId, $planItemId));
                    $queryDb->execute();
                    $monitoringsCount = $queryDb->fetchColumn(0);

                    $queryDb = $this->pdo->prepare($planDao->GetObjectIdFromPlanItemId($planItemId));
                    $queryDb->execute();
                    $objectId = $queryDb->fetchColumn(0);

                    $queryDb = $this->pdo->prepare($planDao->GetPlanMonthlyAssignmentAmount($objectId, $plan->Month, $plan->Year));
                    $queryDb->execute();
                    $assignmentAmount = $queryDb->fetchColumn(0);

                    if((intval($assignmentAmount) - $monitoringsCount) == 0)
                    {
                        //Insert u dodijeljene planove u mjesecu
                        $commandDb = $this->pdo->prepare($planDao->DeletePlanMonthlyAssignment());
                        $commandDb->execute(array(
                            ':objectid' => $objectId,
                            ':month' => $plan->Month,
                            ':year' => $plan->Year
                        ));
                    }
                    else
                    {
                        //Update dodijeljene planove u mjesecu
                        $commandDb = $this->pdo->prepare($planDao->UpdatePlanMonthlyAssignment());
                        $commandDb->execute(array(
                            ':objectid' => $objectId,
                            ':assignednumber' => intval($assignmentAmount) - intval($monitoringsCount),
                            ':month' => $plan->Month,
                            ':year' => $plan->Year
                        ));
                    }
                }

                $canceledPlanItems = $planDao->GetPlanItemsByStatus($command->PlanId, 'CANCELED');
                foreach ($canceledPlanItems as $planItem)
                    $objectDao->UpdateObjectStatus($planItem->ObjectId, false);
            }
            echo json_encode($canceledPlanItems);
        }
    }
    QueryCommandController::Respond(
        new UpdatePlanWeeklyItemsStatusesCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new UpdatePlanWeeklyItemsStatusesCommand(json_decode(file_get_contents("php://input"), true)));