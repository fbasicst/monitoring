<?php
    namespace API\QueryHandlers\Queries\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetObjectsListLazyQuery extends QueryBase
    {
        public $StartFrom;
        public $Count;
        public $OrderType;
        public $Search;
        public $IsActive;
    }