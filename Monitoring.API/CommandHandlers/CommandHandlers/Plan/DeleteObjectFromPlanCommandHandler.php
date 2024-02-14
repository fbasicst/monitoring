<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use \PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\DeleteObjectFromPlanCommand;
    use API\CommandHandlers\Services\PlanService;

    final class DeleteObjectFromPlanCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;
        /** @var  PDO */
        public $onlinePdo;
        
        /**
         * @param DeleteObjectFromPlanCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planService = new PlanService($this->pdo, $this->onlinePdo);
            $planDao = new PlanDao();

            $query = $this->pdo->prepare($planDao->GetPlanWeeklyInfo($command->PlanId));
            $query->execute();
            $plan = $query->fetch(PDO::FETCH_OBJ);

            if($plan->IsLocked == true)
                throw new PDOException("Brisanje iz plana nije moguće. Plan je zaključan.");

            $planService->DeletePlanItem($plan, $command->PlanId, $command->PlanItemId, $command->ObjectId);
        }
    }
    QueryCommandController::Respond(
        new DeleteObjectFromPlanCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new DeleteObjectFromPlanCommand(json_decode(file_get_contents("php://input"), true)));

