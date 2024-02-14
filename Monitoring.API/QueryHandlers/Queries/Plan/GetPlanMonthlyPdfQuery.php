<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetPlanMonthlyPdfQuery extends QueryBase
    {
        public $Year;
        public $Month;
        public $AreaIds;
    }