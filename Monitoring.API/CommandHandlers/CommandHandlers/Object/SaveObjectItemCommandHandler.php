<?php
	namespace API\CommandHandlers\CommandHandlers\Object;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\Models\Objects\ObjectItem;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use API\CommandHandlers\DAO\Object\ObjectDao;
	use API\CommandHandlers\Commands\Object\SaveObjectItemCommand;
	use PDO;

	final class SaveObjectItemCommandHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param SaveObjectItemCommand $command
		 */
		protected function QueryCommandResult($command)
		{
			$objectDao = new ObjectDao($this->pdo);

			$objectItem = new ObjectItem();
			$objectItem->Name = $command->Name;
			$objectItem->IsSeasonal = $command->IsSeasonal;
			$objectItem->LocationDescription = $command->LocationDescription;
			$objectItem->ObjectId = $command->ObjectId;
			$objectItem->InsertedBy = $command->UserId;

			$objectDao->AddObjectItem($objectItem, $command->ObjectId);
		}
	}
	QueryCommandController::Respond(
		new SaveObjectItemCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new SaveObjectItemCommand(json_decode(file_get_contents("php://input"), true)));