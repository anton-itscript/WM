<?php

/**
 * Description of GsmModemSerialConnector
 *
 * 
 */
class GsmModemSerialConnector extends BaseSerialConnector
{
	/**
	 * Overload. Appends to command '\r\n'.
	 * 
	 * @param string $command Command to send.
	 * @param string $output Returns response here (optional).
	 * @param float $timeout Timeout for waiting response (optional).
	 * @return boolean True if there were no errors, false - otherwise.
	 */
	protected function sendCommand($command, &$output = null, $timeout = 0.1)
	{
		$this->_logger->log(__METHOD__);
		
		$result = parent::sendCommand($command ."\r\n", $output, $timeout);
		
		if (($result === true) && !empty($output))
		{
			$output =  preg_split("/\r\n/", $output, -1, PREG_SPLIT_NO_EMPTY);
		}
		
		return true;
	}

    /**
     * Overload.
     * Reads data from gsm modem.
     *
     * @access protected
     *
     * @param array $output
     *
     * @return bool|void
     */
	protected function readCustomData(&$output)
	{
		parent::readCustomData($output);
		
		$this->_logger->log(__METHOD__);

        /**
         * AT+CMGL="REC UNREAD" - Read all unread messages
		 * AT+CMGL="ALL" - Read ALL messages
		 */
		if ($this->sendCommand('AT+CMGL="REC UNREAD"', $output, 2) === false) {
            $this->_logger->log(__METHOD__ . 'AT+CMGL="REC UNREAD"' . ' error');
			return false;
		}
		
		if (is_array($output)) {
            // TODO: uncomment and test method
            //$this->onReceiveMessagesEvent($output);
			foreach($output as $response) {
                // Laos method
                if (preg_match("/^@(.*)/", $response)) {
                    $this->onReceiveMessageEvent($response, 0);
                }
                // Old method parse messages
                //if (preg_match_all("/^[\+](CMGL: )([\d]*),(\"ALL\"|\"REC READ\"|\"REC UNREAD\"|\"STO SENT\"|\"STO UNSENT\"),\"(.*?)\",(.*)/i", $response, $matches)) {
                //	$this->onReceiveMessageEvent($response, $matches[4][0]);
                //}
			}
		}
		
		// Delete all read messages
		$this->sendCommand('AT+CMGD=0,1');
		
		return true;
	}
	
	/**
	 * Checks specific data.
	 * 
	 * @access protected 
	 */
	protected function checkCustomData()
	{
		$this->_logger->log(__METHOD__);
		
		$output = null;
		
		// Read all messages
		if ($this->sendCommand('AT', $output, 2) === true) {
			if (is_array($output)) {
				return (end($output) == 'OK');
			}
		}
		
		return false;
	}
}

?>
