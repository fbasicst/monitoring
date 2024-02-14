<?php
	namespace API\QueryHandlers\DAO\RemoteData;

	use API\Models\Objects\Contract;
	use API\Models\Objects\Customer;
	use PDO;

	class RemoteDataDao
	{
		/** @var PDO */
		private $accountingPdo;

		public function __construct($accountingPdo = null) //todo privremeno optional da ne pucaju ostali pozivi
		{
			$this->accountingPdo = $accountingPdo;
		}

		public function GetCustomers($searchString)
		{
			$query = "SELECT O.cSPPf AS RemoteId, 
							 O.cNazv AS Name,
							 O.cAdre AS Address,
							 O.cKuBr AS HouseNumber,
							 O.cSfPT AS PostalCode,
							 OP.cNzPt AS Post,
							 O.cOIB AS Oib,
							 CONCAT(O.cAdre, ' ',O.cKuBr, ', ',O.cSfPT, ' ',OP.cNzPt) AS FullAddress 
					FROM oposparf O
					LEFT JOIN oposte OP ON O.cSfPT = OP.cSfPT
					WHERE O.cNazv LIKE '%$searchString%' OR O.cOIB LIKE '%$searchString%' 
					ORDER BY O.cNazv ASC;";
			
			return $query;			
		}
		
		public function GetContracts($customerId)
		{
			$query = "SELECT U.id AS RemoteId,
							 U.barkod AS Barcode,
							 U.datSklap AS ConclusionDate,
							 U.datPocet AS StartDate,
							 U.datKraj AS EndDate,
							 U.klasa AS Class,
							 U.uruBr AS DocketNumber,
							 U.aktivan AS Active,
							 U.cSPPf AS CustomerRemoteId 
				FROM ugovorik U 
				WHERE U.cSPPf = '$customerId' 
				ORDER BY U.barkod ASC;";		
			
			return $query;
		}

		public function GetCustomer($customerId)
		{
			$query = "SELECT O.cSPPf AS Id, 
							 O.cNazv AS Name,
							 O.cAdre AS Address,
							 O.cKuBr AS StreetNumber,
							 O.cSfPT AS PostalCode,
							 OP.cNzPt AS PostName,
							 O.cOIB AS Oib
							 
					FROM oposparf O
					LEFT JOIN oposte OP ON O.cSfPT = OP.cSfPT
					WHERE O.cSPPf = :id;";

			$queryDb = $this->accountingPdo->prepare($query);
			$queryDb->execute(array(
				':id' => $customerId
			));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new Customer()));
			/** @var Customer $customer */
			$customer = $queryDb->fetch();

			return $customer;
		}

		public function GetContract($contractBarcode)
		{
			$query = "SELECT U.id AS Id,
							 U.barkod AS Barcode,
							 U.cSPPf AS CustomerRemoteId,							 
							 U.datSklap AS ConclusionDate,
							 U.datPocet AS StartDate,
							 U.datKraj AS EndDate,
							 U.klasa AS Class,
							 U.uruBr AS DocketNumber,
							 U.aktivan AS Active
				FROM ugovorik U 
				WHERE U.barkod = :barcode;";

			$queryDb = $this->accountingPdo->prepare($query);
			$queryDb->execute(array(
				':barcode' => $contractBarcode
			));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new Contract()));
			/** @var Contract $contract */
			$contract = $queryDb->fetch();

			return $contract;
		}
	}