<?php

/**
 * Description of DataLoggerSerialConnector
 *
 * 
 */
class DataLoggerSerialConnector extends BaseSerialConnector 
{	
	/**
	 * Overload.
	 * Reads data from data logger.
	 * 
	 * @access protected
	 * @param array $output 
	 */
	protected function readCustomData(&$output)
	{
		parent::readCustomData($output);
		
		$this->_logger->log(__METHOD__);
		
		// Waiting for messages
		while (true)
		{	
			$response = $this->_serial->readString($this->_timeout);
			
			if ($response === false)
			{
				$this->_errors[] = 'Error during read message';

				return false;
			}
			
			if (!empty($response))
			{
				$this->_logger->log(__METHOD__, array('response' => $response));
				
				$output =  preg_split("/\r\n/", $response, -1, PREG_SPLIT_NO_EMPTY);
				
				foreach ($output as $message)
				{
					$this->onReceiveMessageEvent($message, null);
				}
			}
		}
		
		$this->_errors[] = 'Timeout exceeded during waiting message';
		
		return false;
	}

	protected function checkCustomData()
	{
		return true;
	}
}

?>
