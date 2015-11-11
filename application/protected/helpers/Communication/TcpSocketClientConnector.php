<?php

/**
 * Can connect to tcp server (in general other protocols can be used) and send messages to it.
 *
 * @author
 */
class TcpSocketClientConnector extends BaseConnector
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
	protected $close_connection = false;

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
		
		$this->_socket = @stream_socket_client($source, $errorNum, $error, $timeout,STREAM_CLIENT_ASYNC_CONNECT|STREAM_CLIENT_CONNECT);

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
        $this->readData($message);
		return true;
	}


    public function readDataFromServer()
    {
        $this->_logger->log(__METHOD__ .' start read data');
        if(!is_resource($this->_socket))
            return false;
        $i = 0;
         $content = '';
        @stream_set_timeout($this->_socket, 1);

//        do
//        {
//            $content .= @fgets($this->_socket, 1024);
//            $i += 1024;
//        }
//        while ($i === strlen($content) && !@feof($this->_socket));

        do
        {
            time_nanosleep(0,20000000);
            $part = @fgets($this->_socket, 1024);
            $this->_logger->log(__METHOD__ .': strlen($content): '.strlen($content));

            if ((strlen($content)!=0 && strlen($content) == strlen($content.$part)) || @feof($this->_socket)) {
                break;
            }
            $content .= $part;
        }
        while (1);


        $this->_logger->log(__METHOD__ .': $content:'.print_r($content,1));
        $this->_logger->log(__METHOD__ .': $i:'.$i);
        $this->_logger->log(__METHOD__ .': strlen($content): '.strlen($content));
        return $content;
    }

    protected function writeStringToSocket($conn, $comming_message)
    {
        $this->_logger->log(__METHOD__.' $comming_message:'.$comming_message);
        $message = $this->onSentMessageEvent($comming_message);
        $this->_logger->log(__METHOD__.' message:'.$message);
        $this->_logger->log(__METHOD__.' $conn:'.$conn);

        fwrite($conn,$message);

        if ($this->onCloseConnectionEvent($comming_message)===true) {
            $this->disconnect();
        }

    }

    public function readData(&$output)
    {
        $this->_logger->log(__METHOD__ . ' START');
        while(1) {
            $result = $this->readDataFromServer();
            $this->_logger->log(__METHOD__ . "read data result: \r\n". "\r\n". print_r($result,1). "\r\n" . "\r\n");

            if (strlen($result) === 0) {
                $this->_logger->log(__METHOD__ . ' SERVER CLOSE CONNECTION');

                $this->disconnect();
                break;
            }


            if ($result != false && strlen($result)>0) {
                $m = $this->onReceiveDataMessageEvent($result);
                $this->writeStringToSocket($this->_socket,$m);
            }
        }
    }

    public function check()
    {

    }

    /**
     * Params:
     * # address -
     * # port -
     * # protocol - tcp, udp
     * @param array $params Array of params for connection
     */
    public function setParams($params)
    {
        if (array_key_exists('protocol', $params))
        {
            $this->_protocol = strtolower($params['protocol']);
        }

        if (array_key_exists('address', $params))
        {
            $this->_address = $params['address'];
        }

        if (array_key_exists('port', $params))
        {
            $this->_port = $params['port'];
        }
    }

}

?>
