<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetPlanItemDetailsFullQuery extends QueryBase
    {
        public $PlanItemId;
    }