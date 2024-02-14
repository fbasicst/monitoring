<?php
	namespace API\QueryHandlers\QueryHandlers\RemoteData;
	require_once '../../../../ClassLoader.php';

	use API\QueryCommandController;
	use API\SecurityFunctionsHandler;
	use Common\API\SecurityFunctionConst;
	use \Exception;
	use API\QueryHandlers\Queries\RemoteData\GetContractPdfQuery;

	final class GetContractPdfQueryHandler extends SecurityFunctionsHandler
	{
		protected $securityFunction = SecurityFunctionConst::MASTERDATA_READ;

		/**
		 * @param GetContractPdfQuery $query
		 * @throws Exception
		 */
		protected function QueryCommandResult($query)
		{
			if(!empty($query->ContractBarcode))
			{
				$file = 'D:\skenirano/ugovoriKupci/'.$query->ContractBarcode.'/ug00001.pdf';

				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename=dok.pdf');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
				header('Content-Length: ' .filesize($file));

				ob_clean();
				flush();
				readfile($file);
			}
			else
			{
				throw new Exception('ContractBarcode Not Sent');
			}
		}
	}
	QueryCommandController::Respond(
		new GetContractPdfQueryHandler($_SERVER['HTTP_AUTHORIZATIONTOKEN']),
		(object)array_fill_keys(array("ContractBarcode"), $_GET['contractBarcode']));