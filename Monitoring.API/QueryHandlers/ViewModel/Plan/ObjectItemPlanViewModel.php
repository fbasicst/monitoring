<?php
    class ObjectItemPlanViewModel
    {
        public $ObjectItemMonitoringId;

        public $ContractServiceTypeName;
        public $ServiceName;
        public $ServiceItemName;
        public $Quantity;
        public $Description;
        
        public $CustomerName;
        public $ObjectName;
        public $ObjectItemName;
        public $ObjectStreetName;
        public $ObjectStreetNumber;
        public $ObjectCityName;
        public $ObjectCityPostalCode;
        public $ObjectCityPost;

        public $ObjectFull;
        public $ServiceFull;
        public $ObjectFullAddress;

        public function __construct()
        {
            $this->ObjectFull = $this->CustomerName. ' - '.$this->ObjectName. ' - '.$this->ObjectItemName;
            $this->ServiceFull = $this->Quantity. ' x '.$this->ServiceName.' '.$this->ServiceItemName.' - '.$this->Description;
            $this->ObjectFullAddress = $this->ObjectStreetName.' '.
                $this->ObjectStreetNumber.', '.
                $this->ObjectCityName.', '.
                $this->ObjectCityPostalCode.' '.
                $this->ObjectCityPost;
        }
    }
?>