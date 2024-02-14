<?php
    namespace API\QueryHandlers\Queries\Plan;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetObjectsForTransferListLazyQuery extends QueryBase
    {
        public $PlanId;
        public $Month;
        public $Year;
        public $AreaId;
        public $CityId;
        public $ObjectTypeId;
        public $ContractServiceTypeId;
        public $ServiceItemId;
        public $AnalysisId;

        public $StartFrom;
        public $Count;
        public $OrderType;
        public $Search;
    }