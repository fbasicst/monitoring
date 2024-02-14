<?php
	namespace API\QueryHandlers\ViewModel;

	class UserViewModel
	{
		public $UserId;
		public $FirstName;
		public $LastName;
		public $Environment;
		public $EnvironmentName;
		public $Token;
		
		public function __construct()
		{
			$this->Token = md5(uniqid(mt_rand(), true));
		}
	}
