<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetPlansWeeklyListLazyQuery extends QueryBase
    {
        public $StartFrom;
        public $Count;
        public $OrderType;
        public $Search;

        public $Month;
        public $Year;
        public $PlanUserId;
        public $ObjectsAmount;
    }