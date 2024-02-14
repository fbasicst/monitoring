<?php
    namespace API\QueryHandlers\QueryHandlers\MasterData;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\MasterData\MasterDataDao;
    use API\QueryHandlers\Queries\MasterData\GetCompletedObjectsByUserQuery;

    final class GetCompletedObjectsByUserQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
        /** @var  PDO */
        public $pdo;

        /**
         * @param GetCompletedObjectsByUserQuery $query
         */
        protected function QueryCommandResult($query)
        {
            $masterDataDao = new MasterDataDao($this->pdo);
            echo json_encode($masterDataDao->GetCompletedObjectsByUser($query->Month, $query->Year));
        }
    }
    QueryCommandController::Respond(
        new GetCompletedObjectsByUserQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetCompletedObjectsByUserQuery(json_decode(file_get_contents("php://input"), true)));