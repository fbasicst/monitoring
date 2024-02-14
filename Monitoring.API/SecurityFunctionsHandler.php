<?php
    namespace API;
    
    use API\Authorization\Connection;
    use API\Authorization\AuthorizationDao;
    use API\Models\Environment;
    use \PDOException;
    use \PDO;

    abstract class SecurityFunctionsHandler implements ISecurityFunctionsHandler
    {
        /** @var Connection */
        private $connection;
        /** @var Environment */
        private $environment;
        /** @var  AuthorizationDao */
        private $authorizationDao;
        /** @var  PDO */
        private $commonPdo;
        private $token;

        public function __construct($token)
        {
            $this->token = $token;
            $this->connection = new Connection();
            $this->commonPdo = $this->connection->Connect("monitoring_common");
            $this->commonPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->authorizationDao = new AuthorizationDao($this->commonPdo);
            $this->environment = $this->authorizationDao->GetUserEnvironment($token);

            if(property_exists($this, 'pdo'))
                $this->pdo = $this->GetDatabasePdo($this->environment->Name);
            if(property_exists($this, 'accountingPdo'))
                $this->accountingPdo = $this->GetDatabasePdo($this->environment->AccountingName);
            if(property_exists($this, 'onlinePdo'))
                $this->onlinePdo = $this->GetDatabasePdo($this->environment->RemoteName);
        }

        protected abstract function QueryCommandResult($queryCommand);

        public function ExecuteQueryCommand($queryCommand)
        {
            $this->CheckAuthorization();
            $this->QueryCommandResult($queryCommand);
        }

        /**
         * @param $environmentName
         * @return PDO
         */
        private function GetDatabasePdo($environmentName)
        {
            $pdo = $this->connection->Connect($environmentName);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }

        protected $securityFunction;
        private function CheckAuthorization()
        {
            $queryDb = $this->commonPdo->query($this->authorizationDao->GetUserFunctionsFromToken($this->token));
            $userFunctions = $queryDb->fetchAll(PDO::FETCH_COLUMN, 0);

            if(!in_array($this->securityFunction, $userFunctions))
                throw new PDOException("Nemate autorizaciju za pristup odabranim podacima");
        }
    }