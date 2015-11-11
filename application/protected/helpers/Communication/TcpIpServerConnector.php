<?php

/**
 * Description of TcpIpServerConnector
 *
 * 
 */
class TcpIpServerConnector extends BaseConnector
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

	/**
	 *	Used to send reset SMS messages to modems.
	 *  
	 * @access protected
	 * @var SmsMessagesender 
	 */
	protected $_smsMessageSender = null;
	
	/**
	 * 
	 * @access protected
	 * @var array 
	 */
	protected $_smsParams;
	
	/**
	 *
	 * @access protected
	 * @var int 
	 */
	protected $_sentSmsCount = 0;
	
	/**
	 * In seconds.
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_smsSendInterval;

	/**
	 * 
	 * @access protected
	 * @param resource $server 
	 */
	protected function readStringFromSocket($socket)
	{
		$this->_logger->log(__METHOD__);
		
		$i = 0;
		$content = '';
		
		do 
		{
			$content .= @fread($socket, 1024);
		}
		while (($i += 1024) === strlen($content));
		
		return $content;
	}

	/**
	 * 
	 * @access protected
	 */
	protected function trySendSms()
	{
		$this->_logger->log(__METHOD__);
		
		if (is_null($this->_smsMessageSender))
		{
			$this->_logger->log(__METHOD__ .' Sms sender is null -> don\'t send message.');
			
			return;
		}		

		if ($this->_sentSmsCount >= 3)
		{
			$this->_logger->log(__METHOD__ .' Reset SMS message limit is exceeded.');

			return;
		}

		$this->_logger->log(__METHOD__ .' Sending reset SMS message', array('station' => $this->_smsParams['station_code']));

		if ($this->_smsMessageSender->send())
		{
			$this->_sentSmsCount += 1;

			$this->_logger->log(__METHOD__ .' SMS message has been sent');
		}
		else 
		{
			$this->_logger->log(__METHOD__ .' Failed to send SMS message');
		}
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
	public function __construct($logger, $protocol, $address, $port, $smsMessageSender = null, $smsParams = null)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_protocol = strtolower($protocol);
		$this->_address = $address;
		$this->_port = (int)$port;
		
		$this->_smsMessageSender = $smsMessageSender;
		$this->_smsParams = $smsParams;
		
		$this->_smsSendInterval = $this->_smsParams['failure_count_before_send_sms'] * $this->_smsParams['failure_timeout'];
		$this->_timeout = $this->_smsParams['failure_timeout'];
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
				$modCount = @stream_select($read, $write, $except, $this->_timeout);
				$idleTime += time() - $startTime;
				$this->_logger->log(__METHOD__. ' Waiting result', array('count' => $modCount));
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
								if (strlen($message) > 10)
								{
									// Reset SMS send time when message is arrived
									$idleTime = 0;
									$this->_sentSmsCount = 0;
									
									$this->onReceiveMessageEvent($message, null, stream_socket_get_name($readStream, true));
								}
								else
								{
									$this->_logger->log(__METHOD__. ' Message is too short -> ignore it', array('message' => $message));
								}
							}
						}
					}
				}
				
				if ($idleTime >= $this->_smsSendInterval)
				{
					$this->trySendSms();
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

?>
