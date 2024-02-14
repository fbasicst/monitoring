<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use PDO;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\SyncPlanFromCloudCommand;

    final class SyncPlanFromCloudCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;
        /** @var  PDO */
        public $onlinePdo;

        /**
         * @param SyncPlanFromCloudCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planDao = new PlanDao();

            $queryDb = $this->onlinePdo->prepare($planDao->GetPlanStatusesFromCloud($command->PlanId));
            $queryDb->execute();
            $planItems = $queryDb->fetchAll(PDO::FETCH_OBJ);

            foreach ($planItems as $planItem)
            {
                $queryDb = $this->pdo->prepare($planDao->GetPlanStatus($planItem->PlanStatusEnum));
                $queryDb->execute();
                $planStatusId = $queryDb->fetchColumn(0);

                $commandDb = $this->pdo->prepare($planDao->UpdatePlanItemWeeklyStatus());
                $commandDb->execute(array(
                    ':planitemid' => $planItem->Id,
                    ':planstatusid' => $planStatusId,
                    ':finishnotes' => $planItem->FinishNotes
                ));
            }
        }
    }
    QueryCommandController::Respond(
        new SyncPlanFromCloudCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new SyncPlanFromCloudCommand(json_decode(file_get_contents("php://input"), true)));