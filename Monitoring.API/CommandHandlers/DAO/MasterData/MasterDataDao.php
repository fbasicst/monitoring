<?php
	namespace API\CommandHandlers\DAO\MasterData;

	class MasterDataDao
	{
		public function GetUser($username)
		{
			$query = "SELECT U.id AS Id,
							 U.firstname AS FirstName,
							 U.lastname AS LastName
					  FROM monitoring_common.users U
					  WHERE U.username = '$username';";
			
			return $query;
		}
		
		public function GetNonWorkingDays($year)
		{
		    $query = "SELECT NWD.nonworkingdate
                      FROM nonworkingdays NWD
                      WHERE YEAR(str_to_date(NWD.nonworkingdate, '%Y-%m-%d')) = $year
                      AND NWD.active = true
		              ORDER BY NWD.nonworkingdate ASC;";
		    
		    return $query;
		}
	}