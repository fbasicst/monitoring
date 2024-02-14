<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class SaveObjectItemMonitoringAndPlanCommand extends CommandBase
    {
        public $ObjectItemId;
        public $ContractServiceTypeId;
        public $ServiceItemId;
        public $Quantity;
        public $Description;
        public $ScheduleLevelId;
        public $MonthlyRepeatsCount;
        public $EndDate;
        public $IsValidFurther;
        public $Analysis;
        public $ScheduleMonths;
    }