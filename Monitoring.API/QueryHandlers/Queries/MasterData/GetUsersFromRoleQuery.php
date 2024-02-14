<?php
    namespace API\QueryHandlers\Queries\MasterData;
    require_once('../../../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    final class GetUsersFromRoleQuery extends QueryBase
    {
        public $RoleName;
    }