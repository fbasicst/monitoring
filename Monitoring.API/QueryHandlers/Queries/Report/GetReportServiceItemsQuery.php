<?php
    namespace API\QueryHandlers\Queries\Report;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetReportServiceItemsQuery extends QueryBase
    {
        public $AreaIds;
        public $Month;
        public $Year;
        public $AnalysisIds;
    }