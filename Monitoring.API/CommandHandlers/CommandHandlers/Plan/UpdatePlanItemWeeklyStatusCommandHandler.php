<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use PDO;
    use \PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\UpdatePlanItemWeeklyStatusCommand;

    final class UpdatePlanItemWeeklyStatusCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;

        /**
         * @param UpdatePlanItemWeeklyStatusCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $planDao = new PlanDao();

            //Provjeri jeli plan zaključan
            $query = $this->pdo->prepare($planDao->CheckPlanWeeklyIsLocked($command->PlanId));
            $query->execute();

            $isLocked = $query->fetchColumn(0);
            if($isLocked == true)
            {
                throw new PDOException("Plan je zaključan");
            }

            //Update planitema sa poslanim statusom
            $query = $this->pdo->prepare($planDao->UpdatePlanItemWeeklyStatus());
            $query->execute(array(
                ':planitemid' => $command->PlanItemId,
                ':planstatusid' => $command->PlanStatusId,
                ':finishnotes' => $command->PlanStatusFinishNotes
            ));
        }
    }
    QueryCommandController::Respond(
        new UpdatePlanItemWeeklyStatusCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new UpdatePlanItemWeeklyStatusCommand(json_decode(file_get_contents("php://input"), true)));