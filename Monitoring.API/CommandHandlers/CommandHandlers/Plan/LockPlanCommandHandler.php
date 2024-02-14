<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\CommandHandlers\DAO\Object\ObjectDao;
    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use \PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\LockPlanCommand;
    use API\Authorization\Connection;

    final class LockPlanCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;
        /** @var  PDO */
        public $onlinePdo;

        /**
         * @param LockPlanCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planDao = new PlanDao($this->pdo);
            $objectDao = new ObjectDao($this->pdo);

            //Prije zaključavanja napravi provjeru jeli plan ima itema koji su "U tijeku"
            $queryDb = $this->pdo->prepare($planDao->CheckPendingPlanItemsExistence($command->PlanId));
            $queryDb->execute();
            $count = $queryDb->fetchColumn(0);
            if($count > 0)
                throw new PDOException("Plan sadrži stavke koje su U tijeku");

            //Prije zaključavanja lokalno, provjeri jeli plan uploadan,
            //ako da -> postavljanje statusa natrag na web i zaključaj plan na webu,
            //tek ako je to uspjelo, zaključaj plan lokalno!
            $queryDb = $this->pdo->prepare($planDao->GetPlanWeeklyInfo($command->PlanId));
            $queryDb->execute();
            $plan = $queryDb->fetch(PDO::FETCH_OBJ);

            if($plan->IsUploaded == true)
            {
                //1) dobavi statuse i bilješke iz lokalne baze
                $queryDb = $this->pdo->prepare($planDao->GetPlanStatuses($command->PlanId));
                $queryDb->execute();
                $planItems = $queryDb->fetchAll(PDO::FETCH_OBJ);

                //2) spremi statuse i bilješke u remote bazu
                foreach ($planItems as $planItem)
                {
                    $commandDb = $this->onlinePdo->prepare($planDao->UpdatePlanItemStatusCloud());
                    $commandDb->execute(array(
                        ':planitemid' => $planItem->Id,
                        ':planstatusenum' => $planItem->PlanStatusEnum,
                        ':finishnotes' => $planItem->FinishNotes
                    ));
                }

                //3) zaključaj remote plan
                $commandDb = $this->onlinePdo->prepare($planDao->LockPlanCloud());
                $commandDb->execute(array(
                    ':planid' => $command->PlanId
                ));
            }

            //Zaključaj plan
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
            echo $canceledPlanItems != null
                ? json_encode($canceledPlanItems)
                : json_encode(array());
        }
    }
    QueryCommandController::Respond(
        new LockPlanCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new LockPlanCommand(json_decode(file_get_contents("php://input"), true)));