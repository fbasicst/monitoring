<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class UpdatePlanItemWeeklyStatusCommand extends CommandBase
    {
        public $PlanItemId;
        public $PlanStatusId;
        public $PlanStatusFinishNotes;
        public $PlanId;
    }