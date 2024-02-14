<?php
    namespace API\QueryHandlers\Queries\MasterData;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetCompletedObjectsByUserQuery extends QueryBase
    {
        public $Month;
        public $Year;
    }