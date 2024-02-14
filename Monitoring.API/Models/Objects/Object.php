<?php
    namespace API\Models\Objects;

    class Object
    {
        public $Id;
        public $Name;
        public $StreetName;
        public $StreetNumber;
        public $ContactPerson;
        public $ContactPhone;
        public $ContactMail;
        public $Notes;
        public $InsertedBy;

        public $CustomerId;
        public $ContractId;
        public $CityId;
        public $AreaId;
        public $ObjectTypeId;

        /** @var ObjectItem[] */
        public $Items;
    }