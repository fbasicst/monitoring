<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    class LockPlanCommand extends CommandBase
    {
        public $PlanId;
    }