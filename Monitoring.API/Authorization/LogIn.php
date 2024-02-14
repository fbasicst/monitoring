<?php
    namespace API\Authorization;
    require_once('../../ClassLoader.php');

    use Common\API\Query\QueryBase;

    class LogIn extends QueryBase
    {
        public $UserName;
        public $Password;
    }