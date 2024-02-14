<?php
	namespace API\CommandHandlers\CommandHandlers\Monitoring;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use PDO;
	use API\CommandHandlers\DAO\Monitoring\MonitoringDao;
	use API\CommandHandlers\DAO\Plan\PlanDao;
	use API\CommandHandlers\Commands\Monitoring\DeleteObjectItemMonitoringAndPlanCommand;
	use PDOException;

	final class DeleteObjectItemMonitoringAndPlanCommandHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param DeleteObjectItemMonitoringAndPlanCommand $command
		 */
		protected function QueryCommandResult($command)
		{
			$monitoringDao = new MonitoringDao();
			$planDao = new PlanDao();

			//Provjeri jeli plan već dodjeljivan(kopiran)
			$query = $this->pdo->prepare($monitoringDao->ObjectItemMonitoringCopiedCount($command->MonitoringId));
			$query->execute();
			$monitoringCopiedCount = $query->fetchColumn(0);
			if ($monitoringCopiedCount > 0)
				throw new PDOException("Brisanje nije moguće. Plan je već kopiran.");

			//Dobavi id-ove objectitemplanschedules
			$query = $this->pdo->prepare($planDao->GetObjectItemPlanScheduleIdsFromMonitoring($command->MonitoringId));
			$query->execute();
			$monitoringCopiedCount = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach($monitoringCopiedCount as $id)
			{
				$commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemPlanSchedules());
				$commandDb->execute(array(
					':id' => $id
				));
			}

			//Dobavi id-ove objectitemplans
			$query = $this->pdo->prepare($planDao->GetObjectItemPlanIdFromMonitoring($command->MonitoringId));
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
			$query = $this->pdo->prepare($monitoringDao->GetObjectItemMonitoringAnalysisRelsIdsFromMonitoring($command->MonitoringId));
			$query->execute();
			$objectItemMonitoringAnalisysRelIds = $query->fetchAll(PDO::FETCH_COLUMN, 0);
			foreach($objectItemMonitoringAnalisysRelIds as $id)
			{
				$commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemMonitoringAnalisysRels());
				$commandDb->execute(array(
					':id' => $id
				));
			}

			//Delete objectitemmonitoring
			$commandDb = $this->pdo->prepare($monitoringDao->DeleteObjectItemMonitorings());
			$commandDb->execute(array(
				':id' => $command->MonitoringId
			));
		}
	}
	QueryCommandController::Respond(
		new DeleteObjectItemMonitoringAndPlanCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new DeleteObjectItemMonitoringAndPlanCommand(json_decode(file_get_contents("php://input"), true)));