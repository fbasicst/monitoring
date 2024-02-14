<?php
	namespace API\CommandHandlers\CommandHandlers\Plan;
	require_once '../../../../ClassLoader.php';

	use API\CommandHandlers\DAO\MasterData\MasterDataDao;
	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use PDO;
	use \PDOException;
	use API\CommandHandlers\DAO\Plan\PlanDao;
	use API\CommandHandlers\Commands\Plan\SavePlanWeeklyCommand;

	final class SavePlanWeeklyCommandHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::PLANS_WRITE;
		/** @var  PDO */
		public $pdo;

		/**
		 * @param SavePlanWeeklyCommand $command
		 */
		protected function QueryCommandResult($command)
		{
			$planDao = new PlanDao();
			$masterDataDao = new MasterDataDao();

			if(date("m", strtotime($command->CreationDate)) != date("m", strtotime($command->ExpirationDate)))
			{
				throw new PDOException('Datumi nisu unutar istog mjeseca');
			}

			$startDay = date("j", strtotime($command->CreationDate));
			$endDay = date("j", strtotime($command->ExpirationDate));
			$month = date("m", strtotime($command->CreationDate));
			$year = date("Y", strtotime($command->CreationDate));
			$daysAmount = 0;

			//Dobavi razinu plana "Tjedni"
			$query = $this->pdo->prepare($planDao->GetPlanLevel('WEEKLY'));
			$query->execute();
			$planLevel = $query->fetch(PDO::FETCH_OBJ);

			//Dobavi neradne dane
			$query = $this->pdo->prepare($masterDataDao->GetNonWorkingDays($year));
			$query->execute();
			$nonWorkingDays = $query->fetchAll(PDO::FETCH_COLUMN);

			//Izračun broja dana
			for($day = $startDay; $day <= $endDay; $day++)
			{
				$currentDate = $year.'-'.$month.'-'.sprintf('%02d', $day);
				//Ako je dan državni praznik ili vikend ne uračunaj ga u plan
				if(in_array($currentDate, $nonWorkingDays) or date('N', strtotime($currentDate)) >= 6)
					continue;
				$daysAmount++;
			}

			//Za svakog attachanog zaposlenika spremi tjedni plan
			foreach ($command->AttachedUserIds as $userId)
			{
				$query = $this->pdo->prepare($planDao->SavePlanHeaderWeekly());
				$query->execute(array(
					':startdate' => $command->CreationDate,
					':enddate' => $command->ExpirationDate,
					':planlevelid' => $planLevel->Id,
					':useridinsert' => $command->UserId,
					':useridcontrolled' => $command->UserIdControlled,
					':daysamount' => $daysAmount,
					':objectsamount' => 0,
					':label' => $command->Label,
					':month' => $month,
					':year' => $year
				));
				$planId = $this->pdo->lastInsertId();

				$query = $this->pdo->prepare($planDao->SavePlanWeeklyUsers());
				$query->execute(array(
					':planid' => $planId,
					':userid' => $userId
				));
			}
		}
	}
	QueryCommandController::Respond(
		new SavePlanWeeklyCommandHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		new SavePlanWeeklyCommand(json_decode(file_get_contents("php://input"), true)));