<?php
    namespace API\QueryHandlers\QueryHandlers\Plan;
    require_once '../../../../ClassLoader.php';

    use API\QueryCommandController;
    use API\SecurityFunctionsHandler;
    use Common\API\SecurityFunctionConst;
    use \PDO;
    use API\QueryHandlers\DAO\Plan\PlanDao;
    use API\QueryHandlers\Queries\Plan\GetPlanPdfQuery;
    use API\QueryHandlers\ViewModel\Plan\PlanItemListViewModel;
    use API\QueryHandlers\ViewModel\Plan\PlanWeeklyListViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectItemMonitoringViewModel;
    use API\QueryHandlers\ViewModel\Object\ObjectDepartmentViewModel;
    use \tFPDF;
    use Common\API\DateTimeHelpers;

    final class GetPlanPdfQueryHandler extends SecurityFunctionsHandler
    {
        protected $securityFunction = SecurityFunctionConst::PLANS_READ;
        /** @var  PDO */
        public $pdo;
        
        /**
         * @param GetPlanPdfQuery $query
         */
        public function QueryCommandResult($query)
        {
            $planDao = new PlanDao();
            
            //Dobavi informacije o planu, za prikaz u headeru
            $queryDb = $this->pdo->query($planDao->GetPlanWeeklyInfo($query->PlanId));
            $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanWeeklyListViewModel()));
            $planWeeklyInfo = $queryDb->fetch();

            $queryDb = $this->pdo->query($planDao->GetPlanItemsScheduleDates($query->PlanId));
            $queryDb->setFetchMode(PDO::FETCH_COLUMN, 0);
            $planDates = $queryDb->fetchAll();

            $pdf = new tFPDF();
            $pdf->AddPage();
            $pdf->AliasNbPages();

            //Dodaj Unicode font (UTF-8)
            $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);

            GetPlanPdfQueryHandler::DrawCompanyHeader($pdf, $planWeeklyInfo);

            $xPosStart = 20;
            $yPosStart = 55;
            $lineHeight = 16;//TIP: Ako bude trebala još jedna linija, staviti 19
            $counter = 1;
            $mainTextFontSize = 8;

            $yPosition = $yPosStart;
            $xPosition = $xPosStart;

            foreach ($planDates as $date)
            {
                $pdf->SetFont('DejaVu', '', 10);

                $dayName = DateTimeHelpers::GetDayNameFromDate($date);
                $dateFormatted = date('d.m.Y', strtotime($date));

                //Učitaj planIteme za dani planId i datum
                $queryDb = $this->pdo->query($planDao->GetPlanItemsList($query->PlanId, $date));
                $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new PlanItemListViewModel()));
                $planItems = $queryDb->fetchAll();

                //Crtanje pravokutnika headera dana
                $pdf->Rect($xPosition, $yPosition, 10, $lineHeight);

                //Dan i datum
                $pdf->Rect($xPosition + 10, $yPosition, 100, $lineHeight);
                $pdf->SetXY($xPosition + 10, $yPosition);
                $pdf->MultiCell(100, $lineHeight, $dayName.' '.$dateFormatted, 0, 'C');

                //Postupci uzorkovanja
                $pdf->Rect($xPosition + 110, $yPosition, 30, $lineHeight);
                $pdf->SetXY($xPosition + 110, $yPosition);
                $pdf->MultiCell(30, $lineHeight/2, 'POSTUPCI UZORKOVANJA', 0, 'C');

                //Napomena
                $pdf->Rect($xPosition + 140, $yPosition, 30, $lineHeight);
                $pdf->SetXY($xPosition + 140, $yPosition);
                $pdf->MultiCell(30, $lineHeight, 'NAPOMENA', 0, 'C');

                $yPosition += $lineHeight;

                /**
                 * @var PlanItemListViewModel $item
                 */
                foreach ($planItems as $item)
                {
                    //Dobavi broj monitoringa za trenutni objekt u planu,
                    //za izračun visine pravokutnika objekta
                    $queryDb = $this->pdo->query($planDao->GetMonitoringsCountFromPlanItem($item->PlanItemId));
                    $monitoringsObjectCount = $queryDb->fetchColumn(0);

                    $yPosition = GetPlanPdfQueryHandler::CheckPageBreak($yPosition, $yPosStart, $pdf, $planWeeklyInfo, $monitoringsObjectCount, $lineHeight);
                    $pdf->SetFont('DejaVu', '', $mainTextFontSize);

                    //Crtanje pravokutnika redni broj, objekt i napomena
                    //Brojač
                    $pdf->Rect($xPosition, $yPosition, 10, $lineHeight * $monitoringsObjectCount);
                    $pdf->SetXY($xPosition, $yPosition);
                    $pdf->MultiCell(10, $lineHeight, $counter.'.', 0, 'C');

                    //Objekt
                    $pdf->Rect($xPosition + 10, $yPosition, 70, $lineHeight * $monitoringsObjectCount);
                    $pdf->SetXY($xPosition + 10, $yPosition + 0.5);
                    $pdf->MultiCell(70, 3, $item->CustomerName." - ".$item->ObjectName.
                        "\nOIB: ".$item->CustomerOib.
                        "\n".$item->ObjectFullAddress.
                        "\n".(isset($item->ContactPerson) ? "Kontakt: ".$item->ContactPerson : "")." ".(isset($item->ContactPerson) ? "Tel: ".$item->ContactPhone : ""),
                        0, 'L');

                    //Napomena
                    $pdf->Rect($xPosition + 140, $yPosition, 30, $lineHeight * $monitoringsObjectCount);
                    $pdf->SetXY($xPosition + 140, $yPosition + 0.5);
                    $pdf->MultiCell(30, 3, $item->PlanItemNotes, 0, 'L');

                    //Dobavi objectIteme
                    $queryDb = $this->pdo->query($planDao->GetPlanItemObjectItems($item->PlanItemId));
                    $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectDepartmentViewModel()));
                    $planItemObjectItems = $queryDb->fetchAll();

                    //Resetiranje y pozicije odjela unutar objekta
                    $yPositionItem = $yPosition;
                    /**
                     * @var ObjectDepartmentViewModel $planItemObjectItem
                     */
                    foreach ($planItemObjectItems as $planItemObjectItem)
                    {
                        //Dobavi broj monitoringa za trenutni odjel objekta u planu,
                        //za izračun visine pravokutnika odjela objekta
                        $queryDb = $this->pdo->query($planDao->GetMonitoringsCountFromPlanItemObjectItem($planItemObjectItem->Id));
                        $monitoringsObjectItemCount = $queryDb->fetchColumn(0);

                        //Crtanje pravokutnika odjela objekta
                        $pdf->Rect($xPosition + 80, $yPositionItem, 30, $lineHeight * $monitoringsObjectItemCount);
                        $pdf->SetXY($xPosition + 80, $yPositionItem + 0.5);
                        $pdf->MultiCell(30, 3, $planItemObjectItem->Name.
                            "\n".$planItemObjectItem->LocationDescription, 0, 'L');

                        //Dobavi monitoringe
                        $queryDb = $this->pdo->query($planDao->GetPlanItemObjectItemMonitoringsAndPlans($planItemObjectItem->Id));
                        $queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new ObjectItemMonitoringViewModel()));
                        $planItemObjectItemMonitorings = $queryDb->fetchAll();

                        $yPositionItemMonitoring = $yPositionItem;
                        /**
                         * @var ObjectItemMonitoringViewModel $planItemObjectItemMonitoring
                         */
                        foreach ($planItemObjectItemMonitorings as $planItemObjectItemMonitoring)
                        {
                            //Crtanje pravokutnika monitoringa odjela objekta
                            $pdf->Rect($xPosition + 110, $yPositionItemMonitoring, 30, $lineHeight);
                            $pdf->SetXY($xPosition + 110, $yPositionItemMonitoring + 0.5);
                            $pdf->MultiCell(30, 3, $planItemObjectItemMonitoring->Quantity." "
                                .$planItemObjectItemMonitoring->ServiceName." - "
                                .$planItemObjectItemMonitoring->ServiceItemName
                                .(isset($planItemObjectItemMonitoring->Description) ? " (".$planItemObjectItemMonitoring->Description.")" : ""),
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
            $pdf->Output('Tjedni_plan.pdf','D');
        }

        /**
         * @param tFPDF $pdf
         * @param PlanWeeklyListViewModel $planWeeklyInfo
         */
        private static function DrawCompanyHeader($pdf, $planWeeklyInfo)
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

            $pdf->Text($xPosStart + 125.0, $yPosStart + 9.0, 'Oznaka: '.$planWeeklyInfo->PlanLevelLabel);

            $pdf->Text($xPosStart + 150.0, $yPosStart + 20.0, 'Stranica: ');
            $pdf->Text($xPosStart + 150, $yPosStart + 25, $pdf->PageNo().' / {nb}');

            $pdf->SetFont('DejaVu', '', 12);

            $pdf->SetXY($xPosStart + 20,  $yPosStart + 15);
            $pdf->MultiCell(100, 6, 'TJEDNI RADNI NALOG', 0, 'C');

            $pdf->SetFont('DejaVu', '', 10);
            $pdf->SetXY($xPosStart + 20,  $yPosStart + 20);
            $pdf->MultiCell(100, 5, $planWeeklyInfo->PlanUserFirstName. ' '.$planWeeklyInfo->PlanUserLastName, 0, 'C');
            $pdf->SetXY($xPosStart + 20,  $yPosStart + 25);
            $pdf->MultiCell(100, 5, $planWeeklyInfo->StartDate.' - '.$planWeeklyInfo->EndDate, 0, 'C');

            //Labela na dnu
            $pdf->Text($xPosStart, $yPosStart + 270, $planWeeklyInfo->Label);

            //Poziv footera samo na prvoj stranici
            if($pdf->PageNo() == 1)
            {
                GetPlanPdfQueryHandler::DrawFirstPageFooter($pdf, $planWeeklyInfo);
            }
        }

        private static function CheckPageBreak($yPosition, $yPosStart, $pdf, $planWeeklyInfo, $monitoringsObjectCount, $lineHeight)
        {
            $limit = 270;
            if($pdf->PageNo() == 1)
            {
                $limit = 240;
            }
            //Ako je trenutni y plus visina objekta veća od limita, dodaj novu stranicu
            if(($yPosition + $monitoringsObjectCount * $lineHeight) > $limit)
            {
                $pdf->AddPage();
                GetPlanPdfQueryHandler::DrawCompanyHeader($pdf, $planWeeklyInfo);
                return $yPosStart;
            }
            return $yPosition;
        }

        /**
         * @param tFPDF $pdf
         * @param PlanWeeklyListViewModel $planWeeklyInfo
         */
        private static function DrawFirstPageFooter($pdf, $planWeeklyInfo)
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
            $pdf->MultiCell(34, 7.5, $planWeeklyInfo->PlanUserCreated, 0, 'L');

            $pdf->Rect($xPosStart + 102, $yPosStart + 5, 34, 7.5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart + 5);
            $pdf->MultiCell(34, 7.5, $planWeeklyInfo->StartDate, 0, 'L');

            $pdf->Rect($xPosStart + 136, $yPosStart + 5, 34, 7.5);


            $pdf->Rect($xPosStart, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart,  $yPosStart + 12.5);
            $pdf->MultiCell(34, 7.5, 'Izvršio', 0, 'L');

            $pdf->Rect($xPosStart + 34, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart + 34,  $yPosStart + 12.5);
            //TODO ovdje dinamički dobijati pozicije iz organizacijske strukture
            $pdf->MultiCell(34, 7.5, 'Uzorkivač', 0, 'L');

            $pdf->Rect($xPosStart + 68, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart + 68,  $yPosStart + 12.5);
            $pdf->MultiCell(34, 7.5, $planWeeklyInfo->PlanUser, 0, 'L');

            $pdf->Rect($xPosStart + 102, $yPosStart + 12.5, 34, 7.5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart + 12.5);
            $pdf->MultiCell(34, 7.5, $planWeeklyInfo->EndDate, 0, 'L');

            $pdf->Rect($xPosStart + 136, $yPosStart + 12.5, 34, 7.5);


            $pdf->Rect($xPosStart, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, 'Kontrolirao', 0, 'L');

            $pdf->Rect($xPosStart + 34, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart + 34,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, 'Voditelj odsjeka', 0, 'L');

            $pdf->Rect($xPosStart + 68, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart + 68,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, $planWeeklyInfo->PlanUserControlled, 0, 'L');

            $pdf->Rect($xPosStart + 102, $yPosStart + 20, 34, 7.5);
            $pdf->SetXY($xPosStart + 102,  $yPosStart + 20);
            $pdf->MultiCell(34, 7.5, $planWeeklyInfo->EndDate, 0, 'L');

            $pdf->Rect($xPosStart + 136, $yPosStart + 20, 34, 7.5);
        }
    }
    QueryCommandController::Respond(
        new GetPlanPdfQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
        new GetPlanPdfQuery(json_decode(file_get_contents("php://input"), true)));