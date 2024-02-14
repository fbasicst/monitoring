<?php
    namespace API\QueryHandlers\ViewModel\MasterData;

    class CompletedObjectsByUserViewModel
    {
        public $FirstName;
        public $LastName;
        public $CompletedObjects;
        public $PlannedObjects;

        public $Name;
        public $Percentage;
        public $Color;

        public function __construct()
        {
            $this->Name = $this->FirstName. ' '.$this->LastName;
            $this->Percentage = $this->PlannedObjects > 0
                ? (int)($this->CompletedObjects / $this->PlannedObjects * 100)
                : 0;
            $this->Color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        }
    }