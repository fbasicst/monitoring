<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once '../../../../ClassLoader.php';

    use Common\API\Command\CommandBase;

    final class SavePlanWeeklyCommand extends CommandBase
    {
        public $AttachedUserIds;
        public $CreationDate;
        public $ExpirationDate;
        public $Label;
        public $UserIdControlled;
    }