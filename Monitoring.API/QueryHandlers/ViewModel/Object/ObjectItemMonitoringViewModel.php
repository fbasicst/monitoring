<?php
    namespace API\QueryHandlers\ViewModel\Object;
    
    class ObjectItemMonitoringViewModel
    {
        public $Id;
        public $Quantity;
        public $Description;
        public $ServiceTypeName;
        public $ServiceTypeId;
        public $ServiceItemName;
        public $ServiceItemId;
        public $ServiceName;
        public $PlanId;
        public $ValidFurther;
        public $ScheduleLevelEnum;
        public $ScheduleLevelId;
        public $MonthlyRepeats;
        public $EndDate;
        public $IsActive;
        public $MonitoringCopiedCount;

        public $Analysis;
        public $ScheduleDates;

        public $IsMonitoringCopied;
        public function __construct()
        {
            $this->IsMonitoringCopied = $this->MonitoringCopiedCount > 0 ? true : false;
        }
    }