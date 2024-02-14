<?php
    namespace API\CommandHandlers\CommandHandlers\Object;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use API\CommandHandlers\DAO\Object\ObjectDao;
    use API\QueryHandlers\DAO\RemoteData\RemoteDataDao;
    use API\CommandHandlers\Commands\Object\UpdateObjectGeneralInfoCommand;
    use PDO;

    final class UpdateObjectGeneralInfoCommandHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
        /** @var  PDO */
        public $pdo;
        /** @var  PDO */
        public $accountingPdo;

        /**
         * @param UpdateObjectGeneralInfoCommand $command
         */
        protected function QueryCommandResult($command)
        {
            $objectDao = new ObjectDao($this->pdo);
            $remoteDataDao = new RemoteDataDao($this->accountingPdo);

            $contractId = null;
            if($command->ContractBarcode != null)
            {
                $contract = $objectDao->GetContract($command->ContractBarcode);
                $contractRemote = $remoteDataDao->GetContract($command->ContractBarcode);

                if ($contract == null)
                {
                    $objectDao->AddContract($contractRemote, $command->CustomerId);
                    $contractId = $this->pdo->lastInsertId();
                }
                else
                {
                    $contractId = $contract->Id;
                    $objectDao->UpdateContract($contractRemote, $command->CustomerId, $contractId);
                }
            }

            $commandDb = $this->pdo->prepare($objectDao->UpdateObjectHeader($command->IsActive));
            $commandDb->execute(array(
                ':contractid' => $contractId,
                ':name' => $command->ObjectName,
                ':streetname' => $command->ObjectStreetName,
                ':streetnumber' => $command->ObjectStreetNumber,
                ':cityid' => $command->ObjectCityId,
                ':objecttypeid' => $command->ObjectTypeId,
                ':areaid' => $command->ObjectAreaId,
                ':contactpersonname' => $command->ContactPersonName,
                ':contactpersonphone' => $command->ContactPersonPhone,
                ':contactpersonemail' => $command->ContactPersonMail,
                ':notes' => $command->Notes,
                ':objectid' => $command->ObjectId
            ));
        }
    }
    QueryCommandController::Respond(
        new UpdateObjectGeneralInfoCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new UpdateObjectGeneralInfoCommand(json_decode(file_get_contents("php://input"), true)));