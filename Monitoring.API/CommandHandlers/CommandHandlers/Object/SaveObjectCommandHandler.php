<?php
	namespace API\CommandHandlers\CommandHandlers\Object;
	require_once('../../../../ClassLoader.php');

	use API\CommandHandlers\DAO\Object\ObjectDao;
	use API\CommandHandlers\Factories\ObjectFactory;
	use API\QueryCommandController;
	use API\QueryHandlers\DAO\RemoteData\RemoteDataDao;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use API\CommandHandlers\Commands\Object\SaveObjectCommand;
	use PDO;
	use PDOException;

	final class SaveObjectCommandHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
		/** @var  PDO */
		public $pdo;
		/** @var  PDO */
		public $accountingPdo;
		
		/**
		 * @param SaveObjectCommand $command
		 */
		protected function QueryCommandResult($command)
		{
			$remoteDataDao = new RemoteDataDao($this->accountingPdo);
			$objectDao = new ObjectDao($this->pdo);

			$remoteCustomer = $remoteDataDao->GetCustomer($command->CustomerRemoteId);
			if($remoteCustomer == null) throw new PDOException("Došlo je do greške. Posl. partner nije pronađen.");
			$customer = $objectDao->GetCustomer($command->CustomerRemoteId);

			if($customer == null)
				$objectDao->AddCustomer($remoteCustomer);
			else
				$objectDao->UpdateCustomer($remoteCustomer, $customer->Id);

			if($command->ContractBarcode != null)
			{
				$remoteContract = $remoteDataDao->GetContract($command->ContractBarcode);
				if($remoteContract == null) throw new PDOException("Došlo je do greške. Ugovor nije pronađen.");

				$contract = $objectDao->GetContract($command->ContractBarcode);
				$customer = $objectDao->GetCustomer($command->CustomerRemoteId);

				if ($contract == null)
					$objectDao->AddContract($remoteContract, $customer->Id);
				else
					$objectDao->UpdateContract($remoteContract, $customer->Id, $contract->Id);
			}

			$customer = $objectDao->GetCustomer($command->CustomerRemoteId);
			$contract = $objectDao->GetContract($command->ContractBarcode);
			$contractId = $contract != null ? $contract->Id : null;
			$object = ObjectFactory::CreateObject($command, $customer->Id, $contractId);
			$objectDao->AddObject($object);
		}
	}
	QueryCommandController::Respond(
		new SaveObjectCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new SaveObjectCommand(json_decode(file_get_contents("php://input"), true)));