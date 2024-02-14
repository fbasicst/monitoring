<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\DAO\MasterData\MasterDataDao;
    use API\QueryHandlers\DAO\Object\ObjectDao;
    use API\QueryHandlers\Queries\Plan\GetPlanMonthlyPdfQuery;
    use API\QueryHandlers\ViewModel\MasterData\AreaViewModel;
    use API\QueryHandlers\ViewModel\UserViewModel;
    use API\QueryHandlers\ViewModel\Plan\PlanItemListViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectTypeViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectItemMonitoringViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectDepartmentViewModel;
    use \tFPDF;
    use Common\API\DateTimeHelpers;

    final class GetPlanMonthlyPdfQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetPlanMonthlyPdfQuery $query
         */
        public function QueryCommandResult($query)
        {
            $planDao = new PlanDao();
            $masterDataDao = new MasterDataDao();
            $objectDao = new ObjectDao();

            //TODO poslati areaid kao opcionalni parametar
            $queryDb = $this->pdo->query($masterDataDao->GetObjectAreas());
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new AreaViewModel()));
            $areas = $queryDb->fetchAll();

            $queryDb = $this->pdo->query($masterDataDao->GetUserFromId($query->UserId));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new UserViewModel()));
            $user = $queryDb->fetch();

            $pdf = new tFPDF();
            $pdf->AddPage();
            $pdf->AliasNbPages();

            //Dodaj Unicode font (UTF-8)
            $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);

            GetPlanMonthlyPdfQueryHandler::DrawCompanyHeader($pdf, $query->Month, $query->Year, $user);

            $xPosStart = 20;
            $yPosStart = 55;
            $lineHeight = 16;
            $mainTextFontSize = 8;

            $yPosition = $yPosStart;
            $xPosition = $xPosStart;
            /**
             * @var AreaViewModel $area
             */
            foreach ($areas as $area)
            {
                $counter = 1;
                if (!in_array($area->Id, $query->AreaIds))
                {
                    continue;
                }

                $queryDb = $this->pdo->query($objectDao->GetObjectTypesForMonth($query->Month, $area->Id));
                $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectTypeViewModel()));
                $objectTypes = $queryDb->fetchAll();
                /**
                 * @var ObjectTypeViewModel $objectType
                 */
                foreach ($objectTypes as $objectType)
                {
                    $pdf->SetFont('DejaVu', '', 10);

                    $queryDb = $this->pdo->query($planDao->GetObjectsForMonthlyPlanPdf($query->Year, $query->Month, $area->Id, $objectType->Id));
                    $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanItemListViewModel()));
                    $objects = $queryDb->fetchAll();

                    //Crtanje pravokutnika headera dana
                    $pdf->Rect($xPosition, $yPosition, 10, $lineHeight);

                    //Područje
                    $pdf->Rect($xPosition + 10, $yPosition, 100, $lineHeight);
                    $pdf->SetXY($xPosition + 10, $yPosition);
                    $pdf->MultiCell(100, $lineHeight, strtoupper($area->Name).' - '.$objectType->Name, 0, 'C');

                    //Postupci uzorkovanja
                    $pdf->Rect($xPosition + 110, $yPosition, 30, $lineHeight);
                    $pdf->SetXY($xPosition + 110, $yPosition);
                    $pdf->MultiCell(30, $lineHeight / 2, 'POSTUPCI UZORKOVANJA', 0, 'C');

                    //Djelatnik
                    $pdf->Rect($xPosition + 140, $yPosition, 30, $lineHeight);
                    $pdf->SetXY($xPosition + 140, $yPosition);
                    $pdf->MultiCell(30, $lineHeight, 'DJELATNIK', 0, 'C');

                    $yPosition += $lineHeight;

                    /**
                     * @var PlanItemListViewModel $object
                     */
                    foreach ($objects as $object)
                    {
                        //Dobavi broj monitoringa za trenutni objekt,
                        //za izračun visine pravokutnika objekta
                        $queryDb = $this->pdo->query($objectDao->GetMonitoringsCountFromObjectMonthly($object->ObjectId, $query->Month));
                        $monitoringsObjectCount = $queryDb->fetchColumn(0);

                        $yPosition = GetPlanMonthlyPdfQueryHandler::CheckPageBreak($yPosition, $yPosStart, $pdf, $query->Month, $query->Year, $user, $monitoringsObjectCount, $lineHeight);
                        $pdf->SetFont('DejaVu', '', $mainTextFontSize);

                        //Crtanje pravokutnika redni broj, objekt i napomena
                        //Brojač
                        $pdf->Rect($xPosition, $yPosition, 10, $lineHeight * $monitoringsObjectCount);
                        $pdf->SetXY($xPosition, $yPosition);
                        $pdf->MultiCell(10, $lineHeight, $counter . '.', 0, 'C');

                        //Objekt
                        $pdf->Rect($xPosition + 10, $yPosition, 70, $lineHeight * $monitoringsObjectCount);
                        $pdf->SetXY($xPosition + 10, $yPosition + 0.5);
                        $pdf->MultiCell(70, 3, $object->CustomerName . " - " . $object->ObjectName .
                            "\nOIB: " . $object->CustomerOib .
                            "\n" . $object->ObjectFullAddress .
                            "\n" . (isset($object->ContactPerson) ? "Kontakt: " . $object->ContactPerson : "") . " " . (isset($object->ContactPerson) ? "Tel: " . $object->ContactPhone : ""),
                            0, 'L');

                        //Uzorkivač
                        $pdf->Rect($xPosition + 140, $yPosition, 30, $lineHeight * $monitoringsObjectCount);
                        $pdf->SetXY($xPosition + 140, $yPosition + 0.5);
                        $pdf->MultiCell(30, 3, $object->PlanUser, 0, 'L');

                        //Dobavi objectIteme
                        $queryDb = $this->pdo->query($objectDao->GetObjectItemsForMonth($object->ObjectId, $query->Month));
                        $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectDepartmentViewModel()));
                        $objectItems = $queryDb->fetchAll();

                        //Resetiranje y pozicije odjela unutar objekta
                        $yPositionItem = $yPosition;

                        /**
                         * @var ObjectDepartmentViewModel $objectItem
                         */
                        foreach ($objectItems as $objectItem)
                        {
                            //Dobavi broj monitoringa za trenutni odjel objekta u planu,
                            //za izračun visine pravokutnika odjela objekta
                            $queryDb = $this->pdo->query($objectDao->GetMonitoringsCountFromObjectItemMonthly($objectItem->Id, $query->Month));
                            $monitoringsObjectItemCount = $queryDb->fetchColumn(0);

                            //Crtanje pravokutnika odjela objekta
                            $pdf->Rect($xPosition + 80, $yPositionItem, 30, $lineHeight * $monitoringsObjectItemCount);
                            $pdf->SetXY($xPosition + 80, $yPositionItem + 0.5);
                            $pdf->MultiCell(30, 3, $objectItem->Name .
                                "\n" . $objectItem->LocationDescription, 0, 'L');

                            //Dobavi monitoringe
                            $queryDb = $this->pdo->query($objectDao->GetObjectItemMonitoringsForMonth($objectItem->Id, $query->Month));
                            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemMonitoringViewModel()));
                            $objectItemMonitorings = $queryDb->fetchAll();

                            $yPositionItemMonitoring = $yPositionItem;
                            /**
                             * @var ObjectItemMonitoringViewModel $objectItemMonitoring
                             */
                            foreach ($objectItemMonitorings as $objectItemMonitoring)
                            {
                                //Crtanje pravokutnika monitoringa odjela objekta
                                $pdf->Rect($xPosition + 110, $yPositionItemMonitoring, 30, $lineHeight);
                                $pdf->SetXY($xPosition + 110, $yPositionItemMonitoring + 0.5);
                                $pdf->MultiCell(30, 3, $objectItemMonitoring->Quantity . " "
                                    . $objectItemMonitoring->ServiceName . " - "
                                    . $objectItemMonitoring->ServiceItemName
                                    . (isset($objectItemMonitoring->Description) ? " (" . $objectItemMonitoring->Description . ")" : ""),
                                    0, 'L');

                                $yPositionItemMonitoring += $lineHeight;
                            }
                            //Nova iteracija odjela objekta
                            $yPositionItem += $lineHeight * $monitoringsObjectItemCount;
                        }
                        //Nova iteracija objekta
                        $yPosition += $lineHeight * $monitoringsObjectCount;
                        $counter++;
                    }
                }
            }
            $pdf->Output('Mjesecni_plan.pdf', 'D');
        }
        /**
         * @param tFPDF $pdf
         */
        private static function DrawCompanyHeader($pdf, $month, $year, $user)
        {
            //Crtanje heaeder pravokutnika
            $xPosStart = 20.0;
            $yPosStart = 15.0;

            $pdf->Rect($xPosStart, $yPosStart, 20.0, 30.0);
            $pdf->Rect($xPosStart + 20.0, $yPosStart, 100.0, 15.0);
            $pdf->Rect($xPosStart + 20.0, $yPosStart + 15.0, 100.0, 15.0);
            $pdf->Rect($xPosStart + 120.0, $yPosStart, 50.0, 15.0);
            $pdf->Rect($xPosStart + 120.0, $yPosStart + 15.0, 25.0, 15.0);
            $pdf->Rect($xPosStart + 145.0, $yPosStart + 15.0, 25.0, 15.0);

            $pdf->Image('../../../../Monitoring.UI/Content/images/logoCompany.png', 22.0, 21.0, 16.0, 16.0, 'PNG');
            $pdf->SetFont('DejaVu', '', 8);
            $pdf->Text($xPosStart + 22.0, $yPosStart + 4.0, 'NASTAVNI ZAVOD ZA JAVNO ZDRAVSTVO');
            $pdf->Text($xPosStart + 22.0, $yPosStart + 8.0, 'SPLITSKO-DALMATINSKE ŽUPANIJE');
            $pdf->Text($xPosStart + 22.0, $yPosStart + 12.0, 'SLUŽBA ZA ZDRAVSTVENU EKOLOGIJU');

            $pdf->Text($xPosStart + 125.0, $yPosStart + 9.0, 'Oznaka: V2');

            $pdf->Text($xPosStart + 150.0, $yPosStart + 20.0, 'Stranica: ');
            $pdf->Text($xPosStart + 150, $yPosStart + 25, $pdf->PageNo().' / {nb}');

            $pdf->SetFont('DejaVu', '', 12);

            $pdf->SetXY($xPosStart + 20,  $yPosStart + 15);
            $pdf->MultiCell(100, 6, 'MJESEČNI PLAN UZORKOVANJA', 0, 'C');

            $pdf->SetFont('DejaVu', '', 10);
            $pdf->SetXY($xPosStart + 20,  $yPosStart + 20);

            $monthName = DateTimeHelpers::GetMonthNameFromInt($month);
            $pdf->MultiCell(100, 5, $monthName.' '.$year, 0, 'C');

            //Labela na dnu
            $pdf->Text($xPosStart, $yPosStart + 270, 'PI-5.7.-V.2./02');

            //Poziv footera samo na prvoj stranici
            if($pdf->PageNo() == 1)
            {
                GetPlanMonthlyPdfQueryHandler::DrawFirstPageFooter($pdf, $user);
            }
        }

        private static function CheckPageBreak($yPosition, $yPosStart, $pdf, $month, $year, $user, $monitoringsObjectCount, $lineHeight)
        {
            $limit = 259;
            if($pdf->PageNo() == 1)
            {
                $limit = 239;
            }
            //Ako je trenutni y plus visina objekta veća od limita, dodaj novu stranicu
            if(($yPosition + $monitoringsObjectCount * $lineHeight) > $limit)
            {
                $pdf->AddPage();
                GetPlanMonthlyPdfQueryHandler::DrawCompanyHeader($pdf, $month, $year, $user);
                return $yPosStart;
            }
            return $yPosition;
        }

        /**
         * @param tFPDF $pdf
         * @param UserViewModel $user
         */
        private static function DrawFirstPageFooter($pdf, $user)
        {
            $xPosStart = 20;
            $yPosStart = 245;
            $pdf->SetFont('DejaVu', '', 8);

            $pdf->Rect($xPosStart, $yPosStart, 34, 5);
            $pdf->SetXY($xPosStart,  $yPosStart);
            $pdf->MultiCell(34, 5, 'Aktivnost', 0, 'C');

            $pdf->Rect($xPosStart + 34, $yPosStart, 34, 5);
            $pdf->SetXY($xPosStart + 34,  $yPosStart);
            $pdf->MultiCell(34, 5, 'Funkcija', 0, 'C');

            $pdf->Rect($xPosStart + 68, $yPosStart, 34, 5);
            $pdf->SetXY($xPosStart + 68,  $yPosStart);
            $pdf->MultiCell(34, 5, 'Ime i prezime', 0, 'C');

            $pdf->Rect($xPosStart + 102, $yPosStart, 34, 5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart);
            $pdf->MultiCell(34, 5, 'Datum', 0, 'C');

            $pdf->Rect($xPosStart + 136, $yPosStart, 34, 5);
            $pdf->SetXY($xPosStart + 136,  $yPosStart);
            $pdf->MultiCell(34, 5, 'Potpis', 0, 'C');


            $pdf->SetFont('DejaVu', '', 7);

            $pdf->Rect($xPosStart, $yPosStart + 5, 34, 7.5);
            $pdf->SetXY($xPosStart,  $yPosStart + 5);
            $pdf->MultiCell(34, 7.5, 'Izradio', 0, 'L');

            $pdf->Rect($xPosStart + 34, $yPosStart + 5, 34, 7.5);
            $pdf->SetXY($xPosStart + 34,  $yPosStart + 5);
            $pdf->MultiCell(34, 7.5, 'Voditelj odsjeka', 0, 'L');

            $pdf->Rect($xPosStart + 68, $yPosStart + 5, 34, 7.5);
            $pdf->SetXY($xPosStart + 68,  $yPosStart + 5);
            $pdf->MultiCell(34, 7.5, $user->FirstName.' '.$user->LastName, 0, 'L');

            $pdf->Rect($xPosStart + 102, $yPosStart + 5, 34, 7.5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart + 5);
            $pdf->MultiCell(34, 7.5, date('d.m.Y'), 0, 'L');

            $pdf->Rect($xPosStart + 136, $yPosStart + 5, 34, 7.5);


            $pdf->Rect($xPosStart, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart,  $yPosStart + 12.5);
            $pdf->MultiCell(34, 7.5, 'Kontrolirao', 0, 'L');

            $pdf->Rect($xPosStart + 34, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart + 34,  $yPosStart + 12.5);
            //TODO ovdje dinamički dobijati pozicije iz organizacijske strukture
            $pdf->MultiCell(34, 7.5, 'Voditelj odjela', 0, 'L');

            $pdf->Rect($xPosStart + 68, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart + 68,  $yPosStart + 12.5);
            $pdf->MultiCell(34, 7.5, "Meri Prodan Bedalov", 0, 'L');

            $pdf->Rect($xPosStart + 102, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart + 12.5);
            $pdf->MultiCell(34, 7.5, date('d.m.Y'), 0, 'L');

            $pdf->Rect($xPosStart + 136, $yPosStart + 12.5, 34, 7.5);

            $pdf->Rect($xPosStart, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, 'Odobrio', 0, 'L');

            $pdf->Rect($xPosStart + 34, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart + 34,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, 'Voditelj odjela', 0, 'L');

            $pdf->Rect($xPosStart + 68, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart + 68,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, "Meri Prodan Bedalov", 0, 'L');

            $pdf->Rect($xPosStart + 102, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, date('d.m.Y'), 0, 'L');

            $pdf->Rect($xPosStart + 136, $yPosStart + 20, 34, 7.5);
        }
    }
    QueryCommandController::Respond(
        new GetPlanMonthlyPdfQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanMonthlyPdfQuery(json_decode(file_get_contents("php://input"), true)));