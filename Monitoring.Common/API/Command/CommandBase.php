<?php
    namespace Common\API\Command;

    abstract class CommandBase
    {
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
