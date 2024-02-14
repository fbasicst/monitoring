<?php
    namespace API\QueryHandlers\DAO\Report;

    class ReportDao
    {
        public function GetCompletedServiceItems($areaIdsCommaSeparated, $month, $year, $analysisIdsCommaSeparated)
        {
            $query = "SELECT 
                            PlanItemObjectItemMonitoringId,
                            ServiceName,
                            ServiceItemName,
                            SUM(t.Quantity) AS ServiceItemQuantitySum
                        FROM (
                            SELECT DISTINCT 
                               PIOIM.id AS PlanItemObjectItemMonitoringId,
                               PIOIM.serviceitemid AS ServiceItemId,
                               PIOIM.quantity AS Quantity,
                               S.name AS ServiceName, 
                               SI.name AS ServiceItemName
                                 
                            FROM planitemobjectitemmonitorings PIOIM 	
                            LEFT JOIN planitemobjectitemmonitoringanalysisrel PIOIMAR ON PIOIMAR.planitemobjectitemmonitoringid = PIOIM.id	
                            INNER JOIN planitemobjectitemplans PIOIP ON PIOIP.planitemobjectitemmonitoringid = PIOIM.id
                            INNER JOIN planitemobjectitemplanschedules PIOIPS ON PIOIPS.planitemobjectitemplanid = PIOIP.id
                            INNER JOIN planitemobjectitems PIOI ON PIOI.id = PIOIM.planitemobjectitemid
                            INNER JOIN planitems PI ON PI.id = PIOI.planitemid 
                            INNER JOIN planstatuses PS ON PS.id = PI.planstatusid 
                            INNER JOIN plans P ON P.id = PI.planid
                            INNER JOIN serviceitems SI ON SI.id = PIOIM.serviceitemid
                            INNER JOIN services S ON S.id = SI.serviceid 
                            INNER JOIN objects O ON O.id = PI.objectid 
                            
                            WHERE P.locked = TRUE 
                            AND PS.enumdescription = 'COMPLETED' ";

                            if(!empty($year))
                            {
                                $query .= "AND P.year = $year ";
                            }
                            if(!empty($month))
                            {
                                $query .= "AND P.month = $month ";
                            }
                            if(!empty($areaIdsCommaSeparated))
                            {
                                $query .= "AND O.areaid IN ($areaIdsCommaSeparated) ";
                            }
                            if(!empty($analysisIdsCommaSeparated))
                            {
                                $query .= "AND PIOIMAR.analysisid IN ($analysisIdsCommaSeparated) ";
                            }
                            $query .= ") t                          
                            GROUP BY ServiceItemId 
                            ORDER BY ServiceName ASC, ServiceItemName ASC";

            return $query;
        }
    }