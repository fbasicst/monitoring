<?php
	namespace API\Authorization;
	
	use \PDO;
	use \PDOException;
	
	class Connection
	{
		//TODO: sve smjestiti u nove tablice, u bazu common, s md5 passovima
		public function Connect($environment)
		{
			if($environment == 'monitoring_common')
			{
				$host = 'localhost';
				$dbname = 'monitoring_common';
				$port = '3306';
				$user = 'root';		//TODO: user je monitoring
				$password = '';		//TODO: pass je monitoring
			}
			else if($environment == 'accounting_test')
			{
				$host = 'localhost';
				$dbname = 'accounting_db_mock';
				$port = '3306';
				$user = 'root';
				$password = '';
			}
			else if($environment == 'monitoring_remote_test')
			{
				$host = 'localhost';
				$dbname = 'online_db_mock';
				$port = '3306';
				$user = 'root';
				$password = '';
			}
			else
			{
				throw new PDOException("Environment does not exist.");
			}

			return new PDO("mysql:host=$host;dbname=$dbname;port=$port","$user","$password", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		}
	}