<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    class GetObjectsForPlanMonthlyLazyQuery extends QueryBase
    {
        public $StartFrom;
        public $Count;
        public $OrderType;
        public $Search;
        public $Year;
        public $Month;
        public $AreaIds;
    }