<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetObjectsForPlanAnnuallyLazyQuery extends QueryBase
    {
        public $StartFrom;
        public $Count;
        public $OrderType;
        public $Search;
        public $Year;
        public $AreaIds;
    }