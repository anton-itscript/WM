<?php

/**
 * Description of TcpIpServerConnector
 *
 * 
 */
class TcpSocketServerConnector extends BaseConnector
{
	/**
	 * Server protocol.
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_protocol;
	
	/**
	 * Server bind address.
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_address;
	
	/**
	 * Port number.
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_port;
	
	/**
	 *  Timeout of one interation.
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_timeout;

    protected $_dataArray;
    protected $close_connection = false;


	/**
	 * 
	 * @access protected
	 * @param resource $server 
	 */
	protected function readStringFromSocket($socket)
	{
		$this->_logger->log(__METHOD__);
        stream_set_timeout($socket, 2);
		$i = 0;
		$content = '';
//        stream_set_read_buffer ($socket, 20000);
		do
		{
			$content .= @fread($socket, 1024);
            $this->_logger->log(__METHOD__.'$i'.$i);
            $this->_logger->log(__METHOD__.'len'.strlen($content));
		}
		while (($i += 1024) === strlen($content)  && !@feof($socket));
//        $content .= @fread($socket, 8096);
//        if(!@feof($socket)) {
//
//            stream_set_blocking($socket, 0);
//            do {
//                sleep(1);
//                $part = @fgets($socket, 124);
//                $this->_logger->log(__METHOD__ . ': strlen($content): ' . strlen($content));
//
//                if ((strlen($content) != 0 && strlen($content) == strlen($content . $part)) || @feof($socket)) {
//                    break;
//                }
//                $content .= $part;
//            } while (1);
//
//            stream_set_blocking($socket, 1);
//
//            $this->_logger->log(__METHOD__ . ': $i ' . $i);
//            $this->_logger->log(__METHOD__ . ': content length ' . strlen($content));
//        }
		return $content;
	}

	protected function writeStringToSocket($conn, $comming_message)
    {
        $message = $this->onSentMessageEvent($comming_message);
        $this->_logger->log(__METHOD__.' message:'.$message);
        $this->_logger->log(__METHOD__.' $conn:'.$conn);
//        stream_set_write_buffer($conn, 20000);

//        $message = 'a:4:{s:12:"messageIdent";s:12:"ExchangeODSS";s:4:"step";s:2:"01";s:15:"closeConnection";s:1:"0";s:11:"messageData";s:11:"messageData";}';

        $result = fwrite($conn,$message);


        if($result===false){
            $this->_logger->log(__METHOD__.' write was broken');
        } else {
            $this->_logger->log(__METHOD__.' write :'.$result);
        }

//        $this->onCloseConnectionEvent($message);
        if ($this->onCloseConnectionEvent($comming_message)===true) {
            fclose($conn);
        }

    }

//    public function closeConnection()
//    {
//        $this->close_connection=true;
//    }

    public function loadData(&$data)
    {
        $this->_dataArray = & $data;
        //$this->_logger->log(__METHOD__.print_r($data,1));

    }

	
	/**
	 * Ctor.
	 * 
	 * @param ILogger $logger
	 * @param string $protocol
	 * @param string $address
	 * @param int $port
	 * @param SmsMessageSender $smsMessageSender
	 * @param array $smsParams
	 */
	public function __construct($logger, $protocol, $address, $port)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_protocol = strtolower($protocol);
		$this->_address = $address;
		$this->_port = (int)$port;

	}
	
	public function readData(&$output) 
	{
		parent::readData($output);
		
		$this->_logger->log(__METHOD__);
		
		$master = array();
		
		// !!! PROTOCOL (TCP by default) SHOULD BE IN LOWER CASE !!!
		$uri = $this->_protocol .'://'. $this->_address .':'. $this->_port;
		$this->_logger->log(__METHOD__ .' Trying to start server', array('uri' => $uri));
		
		$server = @stream_socket_server($uri, $errno, $errstr);
//        stream_set_blocking($server,0);
		$this->_logger->log(__METHOD__ .' Server start result', array('socket' => $server));
		
		if ($server === false)
		{
			$this->_logger->log(__METHOD__ .' Server start error', array('error' => $errstr, 'number' => $errno));
		
			$this->_errors[] = $errstr . '('. $errno. ')';
		}
		else
		{
			$master[] = $server;
			
			$idleTime = 0;
			
			while (true)
			{
				$read = $master;
                $this->_logger->log(__METHOD__ .' Waiting for event.');
                $except = null;
                $write = null;
                $startTime = time();

                // If timeout is null then it will wait forever.
                // Use null in case when reset SMS message is not needed.
                $modCount = @stream_select($read, $write, $except, 2000);
//                $idleTime += time() - $startTime;
                $this->_logger->log(__METHOD__. ' Waiting result', array('count' => $modCount));
//
//
                if ($modCount === false) {
                    $this->_errors[] = 'Error during waiting for event.';
                    continue;
                }
				
				foreach($read as $readStream)
				{
                    if ($readStream === $server)
					{
						$this->_logger->log(__METHOD__ .' Client connected');

						// New client connected. Add to list.
						$client = @stream_socket_accept($server);

                        $master[] = $client;
					}
                    else
					{
                        // Client did something
						$socketData = $this->readStringFromSocket($readStream);
						
						$this->_logger->log(__METHOD__. ' Received data', array('data' => $socketData));
						
						if ($socketData === false) {
							// Error
							$index = array_search($readStream, $master, true);
							
							unset($master[$index]);
						} else if (strlen($socketData) === 0) {
							$this->_logger->log(__METHOD__ .' Client disconnected');

							// Client disconnected
							$index = array_search($readStream, $master, true);

							@fclose($readStream);
							unset($master[$index]);
						} else {
							// Client sent some data
							$this->_logger->log(__METHOD__. ' Received message', array('message' => $socketData));
							
							$output =  preg_split("/\r\n/", $socketData, -1, PREG_SPLIT_NO_EMPTY);

							foreach ($output as $message)
                            {
                                $this->_logger->log(__METHOD__. ' $readStream1 '.print_r($readStream,1));
                                $m = $this->onReceiveDataMessageEvent($message);
//                                    $this->_logger->log(__METHOD__. ' $readStream2 '.print_r($readStream,1));
									$this->writeStringToSocket($readStream,$m);
							}
						}
					}
				}

			}
		}
	}
	
	public function check()
	{
		$this->_logger->log(__METHOD__);
		
		$uri = $this->_protocol .'://'. $this->_address .':'. $this->_port;
		
		$server = @stream_socket_server($uri, $errno, $errstr, STREAM_SERVER_LISTEN | STREAM_SERVER_BIND);
		
		if ($server !== false)
		{
			@fclose($server);
			
			return true;
		}
		
		$this->_errors[] = $errstr .'('. $errno .')';
		
		return false;
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
