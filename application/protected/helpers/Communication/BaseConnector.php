<?php

/**
 * Description of BaseConnector
 *
 * 
 */
abstract class BaseConnector extends BaseComponent implements IConnector
{
	/**
	 * Array of errors.
	 * @var array 
	 */
	protected $_errors = array();
	
	/**
	 * Base event for handling messages.
	 * 
	 * @access protected
	 * @param array $messages Array of string messages
	 */
	protected function onReceiveMessagesEvent($messages)
	{
		$this->_logger->log(__METHOD__, array('messages' => $messages));
		
		if (is_callable($this->onReceiveMessages))
		{
			call_user_func_array($this->onReceiveMessage, array($messages));
		}
	}

    /**
     * Base event for handling parsed message.
     *
     * @access protected
     * @param $message
     * @param string $stationId Station id
     * @param null|string $source_info
     * @internal param string $messages Array of string messages
     */
	protected function onReceiveMessageEvent($message, $stationId, $source_info = null)
	{
		$this->_logger->log(__METHOD__, array('message' => $message, 'stationId' => $stationId));
		
		if (is_callable($this->onReceiveMessage))
		{
			call_user_func_array($this->onReceiveMessage, array($message, $stationId, $source_info));
		}
	}
	
	/**
	 * Base event which is raising before message delete.
	 * 
	 * @access protected
	 * @param string $message Message
	 * @param int $index Message index
	 */
	protected function onDeleteMessageEvent($message, $index)
	{
		$this->_logger->log(__METHOD__, array('message' => $message, 'index' => $index));
		
		if (is_callable($this->onDeleteMessage))
		{
			call_user_func_array($this->onReceiveMessage, array($message, $index));
		}
	}




	protected function onSentMessageEvent($comming_message)
	{
		//$this->_logger->log(__METHOD__.print_r($message,1));

		if (is_callable($this->onSentMessage))
		{
		   return call_user_func_array($this->onSentMessage, array($comming_message));
		}
	}

    protected function onReceiveDataMessageEvent($message)
    {

        if (is_callable($this->onReceiveDataMessage))
        {
            return call_user_func_array($this->onReceiveDataMessage, array($message));
        }
    }

    protected function onCloseConnectionEvent($message)
    {
        if (is_callable($this->onCloseConnection))
        {
            return call_user_func_array($this->onCloseConnection, array($message));
        }
    }

    public $onReceiveMessages;
	public $onReceiveMessage;
	public $onReceiveDataMessage;
	public $onDeleteMessage;
	public $onSentMessage;
	public $onCloseConnection;

	/**
	 *
	 * @return array Array of string with errors. 
	 */
	public function errors()
	{
		return $this->_errors;
	}
	
	/**
	 * Base functionality. Clears errors. Set output to null.
	 * @param array $output
	 * @return boolean True if there was no errors, false - otherwise.
	 */
	public function readData(&$output)
	{
		$this->_logger->log(__METHOD__);
		
		$output = null;
		
		// Clear errors
		$this->_errors = array();
	}
}

?>