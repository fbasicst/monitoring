<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class SaveObjectCommand extends CommandBase
    {
        public $CustomerRemoteId;
        public $ContractBarcode;
        public $Name;
        public $StreetName;
        public $StreetNumber;
        public $CityId;
        public $AreaId;
        public $ObjectTypeId;
        public $ContactPerson;
        public $ContactPhone;
        public $ContactMail;
        public $Notes;

        public $Departments;

        public $Legal;
        public $GeneralInfo;
        public $Plan;
        public $OtherInfo;
    }