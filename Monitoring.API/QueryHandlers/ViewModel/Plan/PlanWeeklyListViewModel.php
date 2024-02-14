<?php
    namespace API\QueryHandlers\ViewModel\Plan;

    class PlanWeeklyListViewModel
    {
        public $PlanId;
        public $StartDate;
        public $EndDate;
        public $Month;
        public $Year;
        public $DaysAmount;
        public $ObjectsAmount;
        public $ObjectsCompleted;
        public $ObjectsPending;
        public $Label;
        public $PlanLevelLabel;
        public $PlanUserFirstName;
        public $PlanUserLastName;
        public $PlanUser;
        public $PlanUserCreated;
        public $PlanUserControlled;
        public $IsLocked;
        public $IsUploaded;

        public function __construct()
        {
            $this->PlanUser = $this->PlanUserFirstName.' '.$this->PlanUserLastName;
            $this->StartDate = date("d.m.Y", strtotime($this->StartDate));
            $this->EndDate = date("d.m.Y", strtotime($this->EndDate));
        }
    }
