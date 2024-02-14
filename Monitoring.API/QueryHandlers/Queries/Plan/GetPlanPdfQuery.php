<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    class GetPlanPdfQuery extends QueryBase
    {
        public $PlanId;
    }