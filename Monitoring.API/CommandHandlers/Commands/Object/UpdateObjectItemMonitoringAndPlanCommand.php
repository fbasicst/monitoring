<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    class UpdateObjectItemMonitoringAndPlanCommand extends CommandBase
    {
        public $ObjectItemMonitoringId;
        public $ContractServiceTypeId;
        public $ServiceItemId;
        public $AnalysisIds;
        public $Quantity;
        public $Description;
        public $PlanLevelId;
        public $MonthlyRepeats;
        public $ValudFurther;
        public $EndDate;
        public $Months;
    }