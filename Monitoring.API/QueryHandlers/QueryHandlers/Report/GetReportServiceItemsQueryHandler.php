<?php
    namespace API\QueryHandlers\QueryHandlers\Report;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\Queries\Report\GetReportServiceItemsQuery;
    use API\QueryHandlers\DAO\Report\ReportDao;
    use API\QueryHandlers\ViewModel\Report\CompletedServiceItemsViewModel;

    final class GetReportServiceItemsQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
        /** @var  PDO */
        public $pdo;

        /**
         * @param GetReportServiceItemsQuery $query
         */
        protected function QueryCommandResult($query)
        {
            $reportDao = new ReportDao();

            $areaIdsCommaSeparated = !empty($query->AreaIds) ? implode(",", $query->AreaIds) : array();
            $analysisIdsCommaSeparated = !empty($query->AnalysisIds) ? implode(",", $query->AnalysisIds) : array();

            $queryDb = $this->pdo->query($reportDao->GetCompletedServiceItems($areaIdsCommaSeparated, $query->Month, $query->Year, $analysisIdsCommaSeparated));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new CompletedServiceItemsViewModel()));
            $items = $queryDb->fetchAll();

            echo json_encode($items);
        }
    }
    QueryCommandController::Respond(
        new GetReportServiceItemsQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetReportServiceItemsQuery(json_decode(file_get_contents("php://input"), true)));