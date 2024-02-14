<?php
    namespace API\QueryHandlers\Queries\RemoteData;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetContractPdfQuery extends QueryBase
    {
        public $ContractBarcode;
    }