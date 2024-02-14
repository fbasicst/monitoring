<?php
	namespace Common\API;
	
	final class ErrorHelpers
	{
		public static function SaveToErrorLog($message, $handler)
		{
			file_put_contents('../../../ErrorLog.txt', "Error: ".date('d/m/Y H:i:s', time())." ".$message." at ".$handler.PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}