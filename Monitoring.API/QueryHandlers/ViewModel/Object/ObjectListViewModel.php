<?php
	namespace API\QueryHandlers\ViewModel\Object;
	
	class ObjectListViewModel
	{
		public $ObjectId;
		public $ObjectName;
		public $CustomerName;
		public $ContractBarcode;
		public $ObjectStreetName;
		public $ObjectStreetNumber;
		public $ObjectStreet;
		public $ObjectCityName;
		public $ObjectCityPostalCode;
		public $ObjectCityPost;
		public $TotalPlansInMonth;
		public $PlansAssigned;

		public $ObjectFullAddress;
		public $PlansAssignedFull;
		public function __construct()
		{
			$this->ObjectStreet = $this->ObjectStreetName.' '.$this->ObjectStreetNumber;
			$this->ObjectFullAddress = $this->ObjectStreetName.' '.
				$this->ObjectStreetNumber.', '.
				$this->ObjectCityName.', '.
				$this->ObjectCityPostalCode.' '.
				$this->ObjectCityPost;
			$this->PlansAssignedFull = $this->PlansAssigned.' / '.$this->TotalPlansInMonth;
		}
	}