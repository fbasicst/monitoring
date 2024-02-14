<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once '../../../../ClassLoader.php';

    use Common\API\Command\CommandBase;

    final class SyncPlanToWebCommand extends CommandBase
    {
        public $PlanId;
    }