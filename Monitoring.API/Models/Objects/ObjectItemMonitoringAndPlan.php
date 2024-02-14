<?php
    namespace API\Models\Objects;

    class ObjectItemMonitoringAndPlan
    {
        public $Id;
        public $ObjectItemId;
        public $ContractServiceTypeId;
        public $ServiceItemId;
        public $Quantity;
        public $Description;
        public $InsertedBy;

        public $ScheduleLevelId;
        public $MonthlyRepeatsCount;
        public $IsValidFurther;
        public $EndDate;
        public $ObjectItemMonitoringId;

        /** @var ObjectItemMonitoringAnalysisRel[] */
        public $Analysis;
        /** @var ObjectItemPlanSchedule[] */
        public $ScheduleMonths;
    }