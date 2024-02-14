<?php
    namespace API\QueryHandlers\ViewModel\Plan;
    
    class PlanItemListViewModel
    {
        public $PlanItemId;
        public $ScheduleDate;
        public $PlanStatusId;
        public $PlanStatusDescription;
        public $PlanStatusEnum;
        public $PlanItemNotes;
        public $PlanItemFinishNotes;
        public $PlanUser;

        public $CustomerName;
        public $CustomerOib;
        public $ObjectName;
        public $ObjectId;
        public $ContactPerson;
        public $ContactPhone;

        public $ObjectStreetName;
        public $ObjectStreetNumber;
        public $ObjectCityName;
        public $ObjectCityPostalCode;
        public $ObjectCityPost;

        public $ObjectFullAddress;
        public $HasFinishNotes;
        public function __construct()
        {
            $this->ObjectFullAddress = $this->ObjectStreetName.' '.
                $this->ObjectStreetNumber.', '.
                $this->ObjectCityName.', '.
                $this->ObjectCityPostalCode.' '.
                $this->ObjectCityPost;
            $this->HasFinishNotes = !empty($this->PlanItemFinishNotes);
        }
    }
?>