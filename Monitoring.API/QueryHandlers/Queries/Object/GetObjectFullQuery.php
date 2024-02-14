<?php
    namespace API\QueryHandlers\Queries\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    class GetObjectFullQuery extends QueryBase
    {
        public $ObjectId;
    }