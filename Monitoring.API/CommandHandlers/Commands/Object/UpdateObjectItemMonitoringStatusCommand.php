<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once '../../../../ClassLoader.php';

    use Common\API\Query\QueryBase;

    final class UpdateObjectItemMonitoringStatusCommand extends QueryBase
    {
        public $ObjectItemMonitoringId;
    }