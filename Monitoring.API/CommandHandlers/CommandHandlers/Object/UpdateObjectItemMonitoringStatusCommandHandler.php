<?php
    namespace API\CommandHandlers\CommandHandlers\Object;
    require_once '../../../../ClassLoader.php';

    use API\CommandHandlers\Commands\Object\UpdateObjectItemMonitoringStatusCommand;
    use API\CommandHandlers\DAO\Monitoring\MonitoringDao;
    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use PDO;

    final class UpdateObjectItemMonitoringStatusCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
        /** @var  PDO */
        public $pdo;

        /**
         * @param UpdateObjectItemMonitoringStatusCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $monitoringDao = new MonitoringDao($this->pdo);

            $commandDb = $this->pdo->prepare($monitoringDao->ToggleObjectItemMonitoringStatus());
            $commandDb->execute(array(
                ':objectitemmonitoringid' => $command->ObjectItemMonitoringId
            ));
        }
    }
    QueryCommandController::Respond(
        new UpdateObjectItemMonitoringStatusCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new UpdateObjectItemMonitoringStatusCommand(json_decode(file_get_contents("php://input"), true))
    );