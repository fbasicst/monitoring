<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class SaveObjectItemCommand extends CommandBase
    {
        public $ObjectId;
        public $Name;
        public $IsSeasonal;
        public $LocationDescription;
    }