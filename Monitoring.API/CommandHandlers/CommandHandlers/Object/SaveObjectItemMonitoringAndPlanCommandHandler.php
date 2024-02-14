<?php
	namespace API\CommandHandlers\CommandHandlers\Object;
	require_once '../../../../ClassLoader.php';

	use API\CommandHandlers\DAO\Object\ObjectDao;
	use API\CommandHandlers\Factories\ObjectFactory;
	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use API\CommandHandlers\Commands\Object\SaveObjectItemMonitoringAndPlanCommand;
	use PDO;

	final class SaveObjectItemMonitoringAndPlanCommandHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_WRITE;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param SaveObjectItemMonitoringAndPlanCommand $command
		 */
		protected function QueryCommandResult($command)
		{
			$objectDao = new ObjectDao($this->pdo);

			$objectItemMonitoringAndPlan = ObjectFactory::CreateObjectItemMonitoringAndPlan($command);

			$objectDao->AddObjectItemMonitoring($objectItemMonitoringAndPlan, $command->ObjectItemId);
			$objectItemMonitoringId = $this->pdo->lastInsertId();
			foreach ((array)$objectItemMonitoringAndPlan->Analysis as $analysis)
				$objectDao->AddObjectItemMonitoringAnalysisRel($analysis, $objectItemMonitoringId);
			$objectDao->AddObjectItemPlan($objectItemMonitoringAndPlan, $command->ObjectItemId, $objectItemMonitoringId);
			$objectItemPlanId = $this->pdo->lastInsertId();
			foreach ($objectItemMonitoringAndPlan->ScheduleMonths as $scheduleMonth)
				$objectDao->AddObjectItemPlanSchedule($scheduleMonth->Month, $objectItemPlanId);
		}
	}
	QueryCommandController::Respond(
		new SaveObjectItemMonitoringAndPlanCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new SaveObjectItemMonitoringAndPlanCommand(json_decode(file_get_contents("php://input"), true)));