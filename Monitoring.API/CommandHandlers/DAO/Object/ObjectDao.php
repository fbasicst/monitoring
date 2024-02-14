<?php
	namespace API\CommandHandlers\DAO\Object;

	use API\Models\Objects\Contract;
	use API\Models\Objects\Customer;
	use API\Models\Objects\Object;
	use API\Models\Objects\ObjectItem;
	use API\Models\Objects\ObjectItemMonitoringAndPlan;
	use API\Models\Objects\ObjectItemMonitoringAnalysisRel;
	use PDO;

	class ObjectDao
	{
		/** @var PDO */
		private $pdo;

		public function __construct($pdo = null) //todo privremeno optional da ne pucaju ostali pozivi
		{
			$this->pdo = $pdo;
		}

		/**
		 * @param Object $object
		 */
		public function AddObject($object)
		{
			$commandSql = "INSERT INTO objects (customerid, contractid, annexid, name, streetname, streetnumber, cityid,
											 googleplaceid, areaid, objecttypeid, contactpersonname, contactpersonphone, 
											 contactpersonemail, notes, useridinsert, datetimeinsert) 
									 VALUES (:customerid,
											 :contractid,
											 :annexid,
											 :name,
											 :streetname,
											 :streetnumber,
											 :cityid,
											 :googleplaceid,
											 :areaid,
											 :objecttypeid,
											 :contactpersonname,
											 :contactpersonphone,
											 :contactpersonemail,
											 :notes,
											 :useridinsert,
											 now()
											 );";

			$commandDb = $this->pdo->prepare($commandSql);
			$commandDb->execute(array(
				':customerid' => $object->CustomerId,
				':contractid' => $object->ContractId,
				':annexid' => null,
				':name' => $object->Name,
				':streetname' => $object->StreetName,
				':streetnumber' => $object->StreetNumber,
				':cityid' => $object->CityId,
				':googleplaceid' => null,
				':areaid' => $object->AreaId,
				':objecttypeid' => $object->ObjectTypeId,
				':contactpersonname' => $object->ContactPerson,
				':contactpersonphone' => $object->ContactPhone,
				':contactpersonemail' => $object->ContactMail,
				':notes' => $object->Notes,
				':useridinsert' => $object->InsertedBy
			));

			$objectId = $this->pdo->lastInsertId();
			/** @var ObjectItem $objectItem */
			foreach ((array)$object->Items as $objectItem)
			{
				$this->AddObjectItem($objectItem, $objectId);
				$objectItemId = $this->pdo->lastInsertId();
				foreach ((array)$objectItem->MonitoringsAndPlans as $monitoringsAndPlan)
				{
					$this->AddObjectItemMonitoring($monitoringsAndPlan, $objectItemId);
					$objectItemMonitoringId = $this->pdo->lastInsertId();
					foreach ((array)$monitoringsAndPlan->Analysis as $analysis)
						$this->AddObjectItemMonitoringAnalysisRel($analysis, $objectItemMonitoringId);

					$this->AddObjectItemPlan($monitoringsAndPlan, $objectItemId, $objectItemMonitoringId);
					$objectItemPlanId = $this->pdo->lastInsertId();
					foreach ($monitoringsAndPlan->ScheduleMonths as $scheduleMonth)
						$this->AddObjectItemPlanSchedule($scheduleMonth->Month, $objectItemPlanId);
				}
			}
		}

		/**
		 * @param ObjectItem $objectItem
		 * @param $objectId
		 */
		public function AddObjectItem($objectItem, $objectId)
		{
			$command = "INSERT INTO objectitems (name, seasonal, sublocation, objectid, useridinsert, datetimeinsert)
						VALUES (:name,
								:seasonal,
								:sublocation,
								:objectid,
								:useridinsert,
								now()
								);";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->bindValue(':name', $objectItem->Name);
			$commandDb->bindValue(':seasonal', $objectItem->IsSeasonal, PDO::PARAM_BOOL);
			$commandDb->bindValue(':sublocation', $objectItem->LocationDescription);
			$commandDb->bindValue(':objectid', $objectId);
			$commandDb->bindValue(':useridinsert', $objectItem->InsertedBy);
			$commandDb->execute();
		}

		/**
		 * @param ObjectItemMonitoringAndPlan $objectItemMonitoring
		 * @param $objectItemId
		 */
		public function AddObjectItemMonitoring($objectItemMonitoring, $objectItemId) // TODO prebaciti one koji koriste SaveObjectItemMonitoring da koriste ovu
		{
			$command = "INSERT INTO objectitemmonitorings (contractservicetypeid, serviceitemid, quantity, 
														   description, objectitemid, useridinsert, datetimeinsert) 
									 VALUES (:contractservicetypeid,
											 :serviceitemid,
											 :quantity,
											 :description,
											 :objectitemid,
											 :useridinsert,
											  now()
											 );";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->execute(array(
				':contractservicetypeid' => $objectItemMonitoring->ContractServiceTypeId,
				':serviceitemid' => $objectItemMonitoring->ServiceItemId,
				':quantity' => $objectItemMonitoring->Quantity,
				':description' =>  $objectItemMonitoring->Description,
				':useridinsert' => $objectItemMonitoring->InsertedBy,
				':objectitemid' => $objectItemId
			));
		}

		/**
		 * @param ObjectItemMonitoringAnalysisRel $objectItemMonitoringAnalysisRel
		 * @param $objectItemMonitoringId
		 */
		public function AddObjectItemMonitoringAnalysisRel($objectItemMonitoringAnalysisRel, $objectItemMonitoringId)
		{
			$command = "INSERT INTO objectitemmonitoringanalysisrel (objectitemmonitoringid, analysisid) 
									 VALUES (:objectitemmonitoringid,
											 :analysisid
											);";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->execute(array(
				':objectitemmonitoringid' => $objectItemMonitoringId,
				':analysisid' => $objectItemMonitoringAnalysisRel->AnalysisId
			));
		}

		/**
		 * @param ObjectItemMonitoringAndPlan $objectItemMonitoringAndPlan
		 * @param $objectItemId
		 * @param $objectItemMonitoringId
		 */
		public function AddObjectItemPlan($objectItemMonitoringAndPlan, $objectItemId, $objectItemMonitoringId)
		{
			$command = "INSERT INTO objectitemplans (schedulelevelid, monthlyrepeats, validfurther, enddate, objectitemmonitoringid, objectitemid) 
						 VALUES (:schedulelevelid,
								 :monthlyrepeats,
								 :validfurther,
								 :enddate,
								 :objectitemmonitoringid,
								 :objectitemid
								 );";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->bindValue(':schedulelevelid', $objectItemMonitoringAndPlan->ScheduleLevelId);
			$commandDb->bindValue(':monthlyrepeats', $objectItemMonitoringAndPlan->MonthlyRepeatsCount);
			$commandDb->bindValue(':validfurther', $objectItemMonitoringAndPlan->IsValidFurther, PDO::PARAM_BOOL);
			$commandDb->bindValue(':enddate', $objectItemMonitoringAndPlan->EndDate);
			$commandDb->bindValue(':objectitemmonitoringid', $objectItemMonitoringId);
			$commandDb->bindValue(':objectitemid', $objectItemId);
			$commandDb->execute();
		}

		/**
		 * @param $month
		 * @param $objectItemPlanId
		 */
		public function AddObjectItemPlanSchedule($month, $objectItemPlanId)
		{
			$command = "INSERT INTO objectitemplanschedules (schedulemonth,  objectitemplanid) 
							 VALUES (:schedulemonth,									 
									 :objectitemplanid
									 );";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->execute(array(
				':schedulemonth' => $month,
				':objectitemplanid' => $objectItemPlanId
			));
		}

		/**
		 * @param Customer $customer
		 */
		public function AddCustomer($customer)
		{
			$command = "INSERT INTO customers (remoteid, name, address, postalcode, streetnumber, postname, oib) 											 
						VALUES (:remoteid,
								:name,
								:address,
								:postalcode,
								:streetnumber,
								:postname,
								:oib
								);";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->execute(array(
				':remoteid' => $customer->Id,
				':name' => $customer->Name,
				':address' => $customer->Address,
				':postalcode' => $customer->PostalCode,
				':streetnumber' => $customer->StreetNumber,
				':postname' => $customer->PostName,
				':oib' => $customer->Oib
			));
		}

		/**
		 * @param Customer $remoteCustomer
		 */
		public function UpdateCustomer($remoteCustomer, $customerId)
		{
			$command = "UPDATE customers 
							SET 
								name = :name, 
								address = :address, 
								postalcode = :postalcode, 
								streetnumber = :streetnumber, 
								oib = :oib, 
								postname = :postname
							WHERE id = :id;";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->execute(array(
				':id' => $customerId,
				':name' => $remoteCustomer->Name,
				':address' => $remoteCustomer->Address,
				':postalcode' => $remoteCustomer->PostalCode,
				':streetnumber' => $remoteCustomer->StreetNumber,
				':postname' => $remoteCustomer->PostName,
				':oib' => $remoteCustomer->Oib
			));
		}
		
		public function GetCustomer($customerRemoteId)
		{
			$query = "SELECT C.id AS Id,
 							 C.name AS Name,
 							 C.address AS Address,
 							 C.postalcode AS PostalCode,
 							 C.streetnumber AS StreetNumber,
 							 C.postname AS PostName,
 							 C.oib AS Oib
					  FROM customers C 
					  WHERE C.remoteid = :remoteid;";

			$queryDb = $this->pdo->prepare($query);
			$queryDb->execute(array(
				':remoteid' => $customerRemoteId
			));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new Customer()));
			/** @var Customer $customer */
			$customer = $queryDb->fetch();

			return $customer;
		}

		/**
		 * @param Contract $remoteContract
		 * @param $customerId
		 */
		public function AddContract($remoteContract, $customerId)
		{
			$command = "INSERT INTO contracts (remoteid, barcode, customerid, conclusiondate, startdate, enddate, class, docketnumber, active) 											 
						VALUES (:remoteid,
								:barcode,
								:customerid,
								:conclusiondate,
								:startdate,
								:enddate,
								:class,
								:docketnumber,
								:active
								);";
			$isActive = $remoteContract->Active == 'D' ? true : false;

			$commandDb = $this->pdo->prepare($command);
			$commandDb->bindValue(':remoteid', $remoteContract->Id);
			$commandDb->bindValue(':barcode', $remoteContract->Barcode);
			$commandDb->bindValue(':customerid', $customerId);
			$commandDb->bindValue(':conclusiondate', $remoteContract->ConclusionDate);
			$commandDb->bindValue(':startdate', $remoteContract->StartDate);
			$commandDb->bindValue(':enddate', $remoteContract->EndDate);
			$commandDb->bindValue(':class', $remoteContract->Class);
			$commandDb->bindValue(':docketnumber', $remoteContract->DocketNumber);
			$commandDb->bindValue(':active', $isActive, PDO::PARAM_BOOL);
			$commandDb->execute();
		}

		/**
		 * @param Contract $remoteContract
		 * @param $customerId
		 * @param $contractId
		 */
		public function UpdateContract($remoteContract, $customerId, $contractId)
		{
			$command = "UPDATE contracts 
						SET 
							remoteid = :remoteid, 
							barcode = :barcode,
							customerid = :customerid,
							conclusiondate = :conclusiondate,
							startdate = :startdate,
							enddate = :enddate,
							class = :class,
							docketnumber = :docketnumber,
							active = :active
						WHERE id = :id;";
			$isActive = $remoteContract->Active == 'D' ? true : false;

			$commandDb = $this->pdo->prepare($command);
			$commandDb->bindValue(':id', $contractId);
			$commandDb->bindValue(':remoteid', $remoteContract->Id);
			$commandDb->bindValue(':barcode', $remoteContract->Barcode);
			$commandDb->bindValue(':customerid', $customerId);
			$commandDb->bindValue(':conclusiondate', $remoteContract->ConclusionDate);
			$commandDb->bindValue(':startdate', $remoteContract->StartDate);
			$commandDb->bindValue(':enddate', $remoteContract->EndDate);
			$commandDb->bindValue(':class', $remoteContract->Class);
			$commandDb->bindValue(':docketnumber', $remoteContract->DocketNumber);
			$commandDb->bindValue(':active', $isActive, PDO::PARAM_BOOL);
			$commandDb->execute();
		}
		
		public function GetContract($contractBarcode)
		{
			$query = "SELECT C.id AS Id,
 							 C.customerid AS CustomerId,
 							 C.barcode AS Barcode,
 							 C.conclusiondate AS ConclusionDate,
 							 C.startdate AS StartDate,
 							 C.enddate AS EndDate,
 							 C.class AS Class,
 							 C.docketnumber AS DocketNumber,
 							 C.active AS Active 							 
					  FROM contracts C 
					  WHERE C.barcode = :barcode;";

			$queryDb = $this->pdo->prepare($query);
			$queryDb->execute(array(
				':barcode' => $contractBarcode
			));
			$queryDb->setFetchMode(PDO::FETCH_CLASS, get_class(new Contract()));
			/** @var Contract $contract */
			$contract = $queryDb->fetch();

			return $contract;
		}
		
		public function DeleteObjectItem()
		{
			$command = "DELETE FROM objectitems
						WHERE id = :id";
			
			return $command;
		}

		/*
		 * Kopiranje samo onih odjela čiji monitorinzi je mjesečno ponavljanje veće od
		 * broja kopiranih monitoringa u tom mjesecu i godini, skupa s onima koji su odgođeni*/
		public function GetObjectItemsIdsForTransferList($objectId, $date)
		{
			$query = "SELECT DISTINCT OI.id
					  FROM objectitems OI
					  INNER JOIN objectitemplans OIP ON OIP.objectitemid = OI.id
					  INNER JOIN objectitemplanschedules OIPS ON OIPS.objectitemplanid = OIP.id
					  INNER JOIN objectitemmonitorings OIM ON OIM.id = OIP.objectitemmonitoringid
					  WHERE OI.objectid = $objectId
					  AND OIM.active = TRUE 
					  AND EXTRACT(MONTH FROM '$date') = OIPS.schedulemonth
					  AND (OIP.validfurther = TRUE OR OIP.enddate >= CONCAT(DATE_FORMAT('$date', '%Y-%m-'), '01'))
					  AND 
						(OIP.monthlyrepeats > 
							(SELECT COUNT(*)
							FROM planitemobjectitemplans PIOIPs 
							INNER JOIN planitemobjectitems PIOIs ON PIOIs.id = PIOIPs.planitemobjectitemid
							INNER JOIN planitems PIs ON PIs.id = PIOIs.planitemid
							INNER JOIN plans Ps ON Ps.id = PIs.planid
							WHERE PIOIPs.objectitemplanid = OIP.id
							AND Ps.month =  EXTRACT(MONTH FROM '$date')
							AND Ps.year = EXTRACT(YEAR FROM '$date'))
							
							OR
							
							OI.id IN
							(SELECT PIOIs.objectitemid
							FROM planitemobjectitems PIOIs
							INNER JOIN planitems PIs ON PIOIs.planitemid = PIs.id
							INNER JOIN plans Ps ON Ps.id = PIs.planid
							INNER JOIN planstatuses PSs ON PIs.planstatusid = PSs.id
							WHERE PIs.objectid = $objectId
							AND Ps.locked = true
							AND Ps.month = EXTRACT(MONTH FROM '$date')
							AND Ps.year = EXTRACT(YEAR FROM '$date')
							AND PSs.enumdescription = 'DELAYED')
						);";

			return $query;
		}

		public function UpdateObjectHeader($isActive)
		{
			//TIP: hardcodirano ovako jer PDO odbija spremiti bool polja preko prepare
			if($isActive == true)
			{
				$activeString = "true";
			}
			else
			{
				$activeString = "false";
			}

			$command = "UPDATE objects 
						SET contractid = :contractid, 
						    name = :name,
						    streetname = :streetname,
						    streetnumber = :streetnumber,
						    cityid = :cityid,
						    objecttypeid = :objecttypeid,
						    areaid = :areaid,
						    contactpersonname = :contactpersonname,
						    contactpersonphone = :contactpersonphone,
						    contactpersonemail = :contactpersonemail,
						    notes = :notes,
						    active = ".$activeString."
						WHERE id = :objectid;";

			return $command;
		}

		/**
		 * @param $objectId
		 * @param $isActive
		 */
		public function UpdateObjectStatus($objectId, $isActive)
		{
			$command = "UPDATE objects
						SET active = :isactive
						WHERE id = :id;";

			$commandDb = $this->pdo->prepare($command);
			$commandDb->bindValue(':id', $objectId);
			$commandDb->bindValue(':isactive', $isActive, PDO::PARAM_BOOL);
			$commandDb->execute();
		}
	}
