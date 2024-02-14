<?php
    namespace API;

    use PDO;
    use \PDOException;
    use Common\API\ErrorHelpers;

    class QueryCommandController
    {
        /**
         * @param ISecurityFunctionsHandler $handler
         * @param $queryCommand
         */
        public static function Respond($handler, $queryCommand)
        {
            try
            {
                QueryCommandController::BeginPdoTransactions($handler);
                $handler->ExecuteQueryCommand($queryCommand);
                QueryCommandController::CommitPdoTransactions($handler);
            }
            catch(PDOException $e)
            {
                QueryCommandController::RollbackPdoTransactions($handler);
                header("HTTP/1.1 500 Internal Server Error");
                ErrorHelpers::SaveToErrorLog($e->getMessage(), get_class($handler));
                echo $e->getMessage();
                die();
            }
        }

        private static function BeginPdoTransactions($handler)
        {
            $pdos = QueryCommandController::GetHandlerPdos($handler);
            foreach ($pdos as $pdo)
                $pdo->beginTransaction();
        }

        private static function CommitPdoTransactions($handler)
        {
            $pdos = QueryCommandController::GetHandlerPdos($handler);
            foreach ($pdos as $pdo)
                $pdo->commit();
        }

        private static function RollbackPdoTransactions($handler)
        {
            $pdos = QueryCommandController::GetHandlerPdos($handler);
            foreach ($pdos as $pdo)
                $pdo->rollBack();
        }

        /**
         * @param ISecurityFunctionsHandler $handler
         * @return PDO[]
         */
        private static function GetHandlerPdos($handler)
        {
            $pdos = array();
            if(property_exists($handler, 'pdo'))
                array_push($pdos, $handler->pdo);
            if(property_exists($handler, 'accountingPdo'))
                array_push($pdos, $handler->accountingPdo);
            if(property_exists($handler, 'onlinePdo'))
                array_push($pdos, $handler->onlinePdo);

            return $pdos;
        }
    }