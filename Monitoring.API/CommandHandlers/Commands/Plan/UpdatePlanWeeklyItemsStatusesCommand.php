<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once '../../../../ClassLoader.php';

    use Common\API\Command\CommandBase;

    final class UpdatePlanWeeklyItemsStatusesCommand extends CommandBase
    {
        public $PlanId;
        public $PlanStatusId;
        public $PlanStatusEnum;
        public $LockPlan;
    }