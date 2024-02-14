<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once '../../../../ClassLoader.php';

    use Common\API\Command\CommandBase;

    final class SaveObjectToPlanWeeklyCommand extends CommandBase
    {
        public $PlanWeeklyId;
        public $ScheduleDate;
        public $SelectedObjectId;
        public $Notes;
    }