<?php
    namespace API\QueryHandlers\ViewModel\Object;
    
    class ObjectGeneralInfoViewModel
    {
        public $ObjectId;
        public $ObjectName;
        public $ObjectStreetName;
        public $ObjectStreetNumber;
        public $ObjectCityName;
        public $PostalCode;
        public $PostName;

        public $CustomerId;
        public $CustomerRemoteId;
        public $CustomerName;
        public $CustomerOib;
        public $CustomerAddress;
        public $CustomerStreetNumber;
        public $CustomerPostalCode;
        public $CustomerPostName;
        public $ContractRemoteId;
        public $ContractBarcode;

        public $ObjectFullAddress;
        public $CustomerFullAddress;
        public function __construct()
        {
            $this->ObjectFullAddress = $this->ObjectStreetName.' '.
                $this->ObjectStreetNumber.', '.
                $this->ObjectCityName.', '.
                $this->PostalCode.' '.
                $this->PostName;
            $this->CustomerFullAddress = $this->CustomerAddress.' '.
                $this->CustomerStreetNumber.', '.
                $this->CustomerPostalCode.' '.
                $this->CustomerPostName;
        }

        public $ObjectTypeName;
        public $ObjectAreaName;
        public $ContactPersonName;
        public $ContactPersonPhone;
        public $ContactPersonMail;
        public $Notes;
        public $IsActive;

        public $ObjectTypeId;
        public $ObjectAreaId;
        public $ObjectCityId;
    }