<?php
	namespace API\QueryHandlers\QueryHandlers\Object;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \PDO;
	use API\QueryHandlers\DAO\Object\ObjectDao;
	use API\QueryHandlers\Queries\Object\GetObjectFullQuery;
	use API\QueryHandlers\ViewModel\Object\ObjectFullViewModel;
	use API\QueryHandlers\ViewModel\Object\ObjectGeneralInfoViewModel;
	use API\QueryHandlers\ViewModel\Object\ObjectDepartmentViewModel;
	use API\QueryHandlers\ViewModel\Object\ObjectItemMonitoringViewModel;
	use API\QueryHandlers\ViewModel\Object\ObjectItemMonitoringAnalysisViewModel;
	use API\QueryHandlers\ViewModel\Object\ObjectItemPlanScheduleDateViewModel;

	final class GetObjectFullQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::OBJECTS_READ;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param GetObjectFullQuery $query
		 */
		protected function QueryCommandResult($query)
		{
			$objectDao = new ObjectDao();
			$objectFull = new ObjectFullViewModel();

			//Dobavi generalInfo
			$queryDb = $this->pdo->query($objectDao->GetObjectFull($query->ObjectId));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectGeneralInfoViewModel()));
			$objectFull->GeneralInfo = $queryDb->fetch();

			//Dobavi objectIteme
			$queryDb = $this->pdo->query($objectDao->GetObjectItems($query->ObjectId));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectDepartmentViewModel()));
			$objectFull->Departments = $queryDb->fetchAll();

			foreach($objectFull->Departments as $Department)
			{
				$queryDb = $this->pdo->query($objectDao->GetObjectItemMonitoringsAndPlans($Department->Id));
				$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemMonitoringViewModel()));
				$Department->Monitorings = $queryDb->fetchAll();

				//Dobavi analize i raspored
				foreach($Department->Monitorings as $Monitoring)
				{
					$queryDb = $this->pdo->query($objectDao->GetObjectItemMonitoringAnalysis($Monitoring->Id));
					$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemMonitoringAnalysisViewModel()));
					$Monitoring->Analysis = $queryDb->fetchAll();

					if($Monitoring->ScheduleLevelEnum == 'MONTHLY')
					{
						$queryDb = $this->pdo->query($objectDao->GetObjectItemPlanScheduleDates($Monitoring->PlanId));
						$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemPlanScheduleDateViewModel()));
						$Monitoring->ScheduleDates = $queryDb->fetchAll();
					}
					else if($Monitoring->ScheduleLevelEnum == 'FIXED_DATES')
					{
						$queryDb = $this->pdo->query($objectDao->GetObjectItemPlanScheduleFixedDates($Monitoring->PlanId));
						$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemPlanScheduleDateViewModel()));
						$Monitoring->ScheduleDates = $queryDb->fetchAll();
					}
				}
			}
			echo json_encode($objectFull);
		}
	}
	QueryCommandController::Respond(
		new GetObjectFullQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new GetObjectFullQuery(json_decode(file_get_contents("php://input"), true)));