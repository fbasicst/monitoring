<?php
    namespace API\CommandHandlers\Commands\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class DeleteObjectFromPlanCommand extends CommandBase
    {
        public $PlanId;
        public $PlanItemId;
        public $ObjectId;
    }