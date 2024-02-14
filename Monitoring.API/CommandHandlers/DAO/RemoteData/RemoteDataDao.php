<?php
    namespace API\CommandHandlers\DAO\RemoteData;

    class RemoteDataDao
    {
        public function GetContract($barcode)
        {
            $query = "SELECT U.id AS Id,
							 U.cSPPf AS RemoteId,
							 U.barkod AS Barcode,
							 U.datSklap AS ConclusionDate,
							 U.datPocet AS StartDate,
							 U.datKraj AS EndDate,
							 U.klasa AS Class,
							 U.uruBr AS DocketNumber,
							 U.aktivan AS Active,
							 U.cSPPf AS CustomerRemoteId 
                      FROM ugovorik U 
                      WHERE U.barkod = $barcode;";

            return $query;
        }
    }