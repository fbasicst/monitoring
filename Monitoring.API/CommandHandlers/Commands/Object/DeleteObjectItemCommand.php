<?php
    namespace API\CommandHandlers\Commands\Object;
    require_once('../../../../ClassLoader.php');

    use Common\API\Command\CommandBase;

    final class DeleteObjectItemCommand extends CommandBase
    {
        public $ObjectItemId;
    }