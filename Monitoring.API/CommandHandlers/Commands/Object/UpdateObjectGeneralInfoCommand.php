<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class UpdateObjectGeneralInfoCommand extends CommandBase
    {
        public $ObjectId;
        public $CustomerId;
        public $ContractBarcode;
        public $ObjectName;
        public $ObjectTypeId;
        public $ObjectAreaId;
        public $ObjectStreetName;
        public $ObjectStreetNumber;
        public $ObjectCityId;
        public $ContactPersonName;
        public $ContactPersonPhone;
        public $ContactPersonMail;
        public $Notes;
        public $IsActive;
    }