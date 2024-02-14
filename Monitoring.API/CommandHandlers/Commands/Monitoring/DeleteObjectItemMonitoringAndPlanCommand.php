<?php
    namespace API\CommandHandlers\Commands\Monitoring;
    require_once '../../../../ClassLoader.php';

    use Common\API\Command;

    final class DeleteObjectItemMonitoringAndPlanCommand extends Command\CommandBase
    {
        public $MonitoringId;
    }