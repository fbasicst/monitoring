<?php
    namespace Common\API\Query;

    abstract class QueryBase
    {
        //TODO kasnije ukloniti userid i environment, jer ova dva podatka ne treba slati više
        public $UserId;
        public $Environment;

        public function __construct(array $data)
        {
            foreach($data as $key => $val)
            {
                $this->$key = $val;
            }
        }
    }