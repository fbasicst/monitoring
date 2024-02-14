<?php
    namespace API\QueryHandlers\QueryHandlers\MasterData;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\MasterData\MasterDataDao;
    use API\QueryHandlers\Queries\MasterData\GetRecentSavedObjectsByUserQuery;
    use API\QueryHandlers\ViewModel\MasterData\ObjectsAmountViewModel;
    use API\QueryHandlers\ViewModel\MasterData\ObjectsSavedRecentViewModel;
    use API\QueryHandlers\ViewModel\MasterData\UserObjectSavedViewModel;
    use Common\API\DateTimeHelpers;

    final class GetRecentSavedObjectsByUserQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetRecentSavedObjectsByUserQuery $query
         */
        protected function QueryCommandResult($query)
        {
            $masterDataDao = new MasterDataDao();

            //Dobavi sve korisnike koji su unosili zadnjih 7 dana
            $queryDb = $this->pdo->query($masterDataDao->GetRecentUsersObjectsSaved());
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new UserObjectSavedViewModel()));
            $usersList = $queryDb->fetchAll();

            //Napravi niz datuma zadnjih 7 dana
            $datesList = DateTimeHelpers::getLastNDays(7);

            $result = new ObjectsSavedRecentViewModel();
            $result->DatesList = $datesList;
            $result->UsersList = $usersList;
            $result->ObjectsAmountsList = array();

            //Za svakog korisnika, i datum, puni koliko je objekata unio
            foreach ($usersList as $user)
            {
                $tempArray = array();
                foreach ($datesList as $date)
                {
                    $queryDb = $this->pdo->query($masterDataDao->GetObjectsSavedByUserFromDate($user->UserId, $date));
                    //TODO jeli ovdje stvarno treba ViewModel za samo jedan int ??
                    $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectsAmountViewModel()));
                    array_push($tempArray, $queryDb->fetchColumn(0));
                }
                array_push($result->ObjectsAmountsList, $tempArray);
            }

            //Ispravno formatiraj datume, za prikaz na GUIu
            $result->DatesListFormatted = array();
            foreach ($datesList as $date)
            {
                array_push($result->DatesListFormatted, date("d.m.Y", strtotime($date)));
            }

           echo json_encode($result);
        }
    }
    QueryCommandController::Respond(
        new GetRecentSavedObjectsByUserQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetRecentSavedObjectsByUserQuery(json_decode(file_get_contents("php://input"), true)));