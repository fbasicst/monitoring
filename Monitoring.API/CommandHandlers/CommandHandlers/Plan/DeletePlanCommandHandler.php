<?php
    namespace API\CommandHandlers\CommandHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use \PDOException;
    use API\CommandHandlers\DAO\Plan\PlanDao;
    use API\CommandHandlers\Commands\Plan\DeletePlanCommand;

    final class DeletePlanCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
        /** @var  PDO */
        public $pdo;

        /**
         * @param DeletePlanCommand $command
         */
        protected function QueryCommandResult($command)
        {
            //Provjeri jeli plan zaključan
            $planDao = new PlanDao();

            //Učitaj header plana za kontrole
            $query = $this->pdo->prepare($planDao->GetPlanWeeklyInfo($command->PlanId));
            $query->execute();
            $plan = $query->fetch(PDO::FETCH_OBJ);

            //Kontrole brisanja objekata iz plana
            //1) jeli plan već uploadan //TODO dozvoliti, i prvo brisati s remote, pa lokalno (ako nema itema)
            if ($plan->IsUploaded == true)
            {
                throw new PDOException("Brisanje plana nije moguće. Plan je već poslan u cloud.");
            }

            //2) jeli plan zaključan
            if ($plan->IsLocked == true)
            {
                throw new PDOException("Brisanje plana nije moguće. Plan je zaključan.");
            }

            //3) provjeri ima li planitema
            $query = $this->pdo->prepare($planDao->CheckPlanItemsExistence($command->PlanId));
            $query->execute();
            $count = $query->fetchColumn(0);
            if ($count > 0)
            {
                throw new PDOException("Nije moguće brisanje. Plan sadrži stavke.");
            }

            //Briši plan user rels
            $query = $this->pdo->prepare($planDao->DeletePlanUserRels());
            $query->execute(array(
                ':planid' => $command->PlanId
            ));

            //Briši plan header
            $query = $this->pdo->prepare($planDao->DeletePlan());
            $query->execute(array(
                ':planid' => $command->PlanId
            ));
        }
    }
    QueryCommandController::Respond(
        new DeletePlanCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new DeletePlanCommand(json_decode(file_get_contents("php://input"), true)));