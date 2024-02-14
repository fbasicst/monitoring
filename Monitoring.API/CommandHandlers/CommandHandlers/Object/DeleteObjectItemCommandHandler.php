<?php
	namespace API\CommandHandlers\CommandHandlers\Object;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\CommandHandlers\DAO\Monitoring\MonitoringDao;
	use API\CommandHandlers\DAO\Object\ObjectDao;
	use API\CommandHandlers\DAO\Plan\PlanDao;
	use API\CommandHandlers\Commands\Object\DeleteObjectItemCommand;
	use PDOException;

	final class DeleteObjectItemCommandHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param DeleteObjectItemCommand $command
		 */
		protected function QueryCommandResult($command)
		{
			$objectDao = new ObjectDao();
			$monitoringDao = new MonitoringDao();
			$planDao = new PlanDao();

			//Provjeri jeli odjel sadrži plan koji je već dodjeljivan(kopiran)
			$query = $this->pdo->prepare($monitoringDao->ObjectItemMonitoringsCopiedCount($command->ObjectItemId));
			$query->execute();
			$monitoringsCopiedCount = $query->fetchColumn(0);
			if ($monitoringsCopiedCount > 0)
				throw new PDOException("Brisanje nije moguće. Odjel sadrži plan koji je već kopiran.");

			//Dobavi id-ove objectitemplanschedules
			$query = $this->pdo->prepare($planDao->GetObjectItemPlanScheduleIdsFromObjectItem($command->ObjectItemId));
			$query->execute();
			$objectItemPlanScheduleIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach($objectItemPlanScheduleIds as $id)
			{
				$commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemPlanSchedules());
				$commandDb->execute(array(
					':id' => $id
				));
			}

			//Dobavi id-ove objectitemplans
			$query = $this->pdo->prepare($planDao->GetObjectItemPlanIdsFromObjectItem($command->ObjectItemId));
			$query->execute();
			$objectItemPlanIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach($objectItemPlanIds as $id)
			{
				$commandDb = $this->pdo->prepare($planDao->DeleteObjectItemPlans());
				$commandDb->execute(array(
					':id' => $id
				));
			}

			//Dobavi id-ove objectitemmonitorings
			$query = $this->pdo->prepare($monitoringDao->GetObjectItemMonitoringAnalysisRelsIdsFromObjectItem($command->ObjectItemId));
			$query->execute();
			$objectItemMonitoringAnalisysRelIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach($objectItemMonitoringAnalisysRelIds as $id)
			{
				$commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemMonitoringAnalisysRels());
				$commandDb->execute(array(
					':id' => $id
				));
			}

			//Dobavi id-ove objectitemmonitorings
			$query = $this->pdo->prepare($monitoringDao->GetObjectItemMonitoringIdsFromObjectItem($command->ObjectItemId));
			$query->execute();
			$objectItemMonitoringIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach($objectItemMonitoringIds as $id)
			{
				$commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemMonitorings());
				$commandDb->execute(array(
					':id' => $id
				));
			}

			//Delete objectitem
			$commandDb = $this->pdo->prepare($objectDao->DeleteObjectItem());
			$commandDb->execute(array(
				':id' => $command->ObjectItemId
			));
		}
	}
	QueryCommandController::Respond(
		new DeleteObjectItemCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new DeleteObjectItemCommand(json_decode(file_get_contents("php://input"), true)));