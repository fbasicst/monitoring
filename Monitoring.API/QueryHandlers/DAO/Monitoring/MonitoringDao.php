<?php
    class MonitoringDao
    {
        //NOT IN USE
        public function GetObjectItemMonitoringDetails($MonitoringId)
        {
            $query = "SELECT 
                        C.name AS CustomerName,
                        C.oib AS CustomerOib,
                        C.address AS CustomerAddress,
                        C.streetnumber AS CustomerStreetNumber,
                        C.postalcode AS CustomerPostalCode,
                        C.postname AS CustomerPostName,
                        
                        O.name AS ObjectName,
                        O.streetname AS ObjectStreetName,
                        O.streetnumber AS ObjectStreetNumber,
                        A.name AS ObjectAreaName,
                        CI.name AS ObjectCityName,
                        CI.postalcode AS ObjectPostalCode,
                        CI.post AS ObjectPostName,	
                        OT.name AS ObjectTypeName,
                        O.contactpersonname AS ObjectContactPersonName,
                        O.contactpersonphone AS ObjectContactPersonPhone,
                        O.contactpersonemail AS ObjectContactPersonEmail,
                        O.notes AS ObjectNotes,
                        
                        OI.name AS ObjectItemName,
                        OI.seasonal AS ObjectItemSeasonal,
                        OI.sublocation AS ObjectItemSublocation,
                        
                        CST.statusname AS ObjectItemMonitoringContractServiceTypeName,
                        SI.name AS ObjectItemMonitoringServiceItemName,
                        S.name AS ObjectItemMonitoringServiceName,
                        OIM.quantity AS ObjectItemMonitoringQuantity,
                        OIM.description AS ObjectItemMonitoringDescription,
                        
                        OIP.validfurther AS ObjectItemPlanValidFurther
                    
                    FROM objectitemmonitorings OIM
                    INNER JOIN contractservicetype CST ON CST.id = OIM.contractservicetypeid
                    INNER JOIN serviceitems SI ON SI.id = OIM.serviceitemid
                    INNER JOIN services S ON S.id = SI.serviceid
                    
                    INNER JOIN objectitemplans OIP ON OIP.objectitemmonitoringid = OIM.id
                    INNER JOIN objectitems OI ON OI.id = OIM.objectitemid
                    INNER JOIN objects O ON O.id = OI.objectid
                    
                    INNER JOIN areas A ON A.id = O.areaid
                    INNER JOIN objecttypes OT ON OT.id = O.objecttypeid
                    INNER JOIN cities CI ON CI.id = O.cityid
                    
                    INNER JOIN customers C ON C.id = O.customerid
                    LEFT JOIN contracts CO ON CO.id = O.contractid
                    
                    WHERE OIM.id = $MonitoringId";

            return $query;
        }
    }
?>
