<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class SaveObjectsGroupToPlanCommand extends CommandBase
    {
        public $ObjectIdsList;
        public $PlanWeeklyId;
        public $ScheduleDate;
        public $Notes;
    }