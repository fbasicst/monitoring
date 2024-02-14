<?php
    namespace API\QueryHandlers\ViewModel\Report;

    class CompletedServiceItemsViewModel
    {
        public $PlanItemObjectItemMonitoringId;
        public $ServiceName;
        public $ServiceItemName;
        public $ServiceItemQuantitySum;

        public $ServiceItemFull;
        public function __construct()
        {
            $this->ServiceItemFull = $this->ServiceName. " - ".$this->ServiceItemName;
        }
    }