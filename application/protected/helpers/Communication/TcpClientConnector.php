<?php

/**
 * Can connect to tcp server (in general other protocols can be used) and send messages to it.
 *
 * @author
 */
class TcpClientConnector extends BaseClientConnector
{
	/**
	 * @access protected
	 * @var string 
	 */
	protected $_protocol;
	
	/**
	 * @access protected
	 * @var string 
	 */
	protected $_address;
	
	/**
	 * @access protected
	 * @var int 
	 */
	protected $_port;
	
	/**
	 * @access protected
	 * @var array 
	 */
	protected $_errors = array();
	
	/**
	 * @access protected
	 * @var resource 
	 */
	protected $_socket;
	
	public function __construct($logger, $protocol, $address, $port)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_protocol = $protocol;
		$this->_address = $address;
		$this->_port = $port;
	}

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->_address;
    }

	/**
	 * 
	 * @param int $timeout
	 * @return boolean True on success, false - otherwise.
	 */
	public function connect($timeout = 5)
	{
		$this->_logger->log(__METHOD__);
		
		$this->_errors = array();
		
		$error = '';
		$errorNum = 0;
		
		$source = $this->_protocol .'://'. $this->_address .':'. $this->_port;
		
		$this->_logger->log(__METHOD__ .' Trying to connect', array('source' => $source));
		
		$this->_socket = @stream_socket_client($source, $errorNum, $error, $timeout);
		$this->_logger->log(__METHOD__ .' Client connect result', array('socket' => $this->_socket));
		
		if ($this->_socket === false)
		{
			$this->_logger->log(__METHOD__ .' Client connect error', array('error' => $error, 'number' => $errorNum));
			
			$this->_errors[] = $error . '('. $errorNum. ')';
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * 
	 * @return boolean True on success, false - otherwise.
	 */
	public function disconnect()
	{
		$this->_logger->log(__METHOD__);
		
		$this->_errors = array();
		
		if (!is_resource($this->_socket))
		{
			$this->_logger->log(__METHOD__ .' Socket is not a resource', array('socket' => $this->_socket));
			
			$this->_errors[] = 'Socket is not a resource';
			
			return false;
		}
		
		$result = @fclose($this->_socket);
		
		if ($result === false)
		{
			$this->_logger->log(__METHOD__ .' Error on disconnect');
			
			$this->_errors[] = 'Error occured during socket closing';
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * 
	 * @param string $message Message to sent to a client.
	 * @param int $timeout
	 * @return boolean True on success, false - otherwise.
	 */
	public function sendMessage($message, $timeout = 5)
	{
		$this->_logger->log(__METHOD__, array('message' => $message, 'timeout' => $timeout));
		
		$this->_errors = array();
		
		if (!is_resource($this->_socket))
		{
			$this->_logger->log(__METHOD__ .' Socket is not a resource', array('socket' => $this->_socket));
			
			$this->_errors[] = 'Socket is not a resource';
			
			return false;
		}
		
		@stream_set_timeout($this->_socket, $timeout);
		
		$result = @fwrite($this->_socket, $message);
		
		$this->_logger->log(__METHOD__ .' Message is sent', array('result' => $result, 'message length' => strlen($message)));
		
		if (($result === false) || ($result != strlen($message)))
		{
			$this->_logger->log(__METHOD__ .' Error on message sending');
			
			$this->_errors[] = 'Error occured during message sending';
			
			return false;
		}
		
		return true;
	}
}

?>
