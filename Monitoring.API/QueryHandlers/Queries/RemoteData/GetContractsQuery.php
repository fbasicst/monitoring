<?php
    namespace API\QueryHandlers\Queries\RemoteData;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetContractsQuery extends QueryBase
    {
        public $CustomerId;
    }