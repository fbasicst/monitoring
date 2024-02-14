<?php
    namespace API\Models\Objects;

    class ObjectItem
    {
        public $Id;
        public $ObjectId;
        public $Name;
        public $IsSeasonal;
        public $LocationDescription;
        public $InsertedBy;
        /** @var ObjectItemMonitoringAndPlan[] */
        public $MonitoringsAndPlans;
        /** @var ObjectItemPlan[] */
        public $Plans;
    }