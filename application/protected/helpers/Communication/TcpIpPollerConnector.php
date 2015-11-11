<?php

/**
 * Description of TcpIpPollerConnector
 *
 * @author
 */
class TcpIpPollerConnector extends BaseConnector
{
	/**
	 *  Station to request data from.
	 * 
	 * @access protected
	 * @var Station
	 */
	protected $_station;
	
	/**
	 * Timeout for connect and waiting for answer.
	 * 
	 * @access protected
	 * @var int
	 */
	protected $_timeout;
	
	/**
	 * Ctor.
	 * 
	 * @param ILogger $logger
	 * @param Station $station
	 */
	public function __construct($logger, $station, $timeout)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_station = $station;
		$this->_timeout = $timeout;
	}
	
	public function readData(&$output) 
	{
		parent::readData($output);
		
		$this->_logger->log(__METHOD__);
		
		return $this->pollDataFromStation($this->_station, $this->_timeout);
	}
	
	protected function pollDataFromStation($station, $timeout)
	{
		$this->_logger->log(__METHOD__, array('station' => $station->station_id_code));
		
		$matches = array();
		
		$source = $station->communication_esp_ip .':'. $station->communication_esp_port;

		$this->_logger->log(__METHOD__. ': Check [IP_ADDRESS|DOMAIN_NAME:PORT] pattern', array('source' => $source));
		
		// Check IP_ADDRESS|DOMAIN_NAME:PORT pattern
		if(preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+)\:([0-9]{1,5})$/', $source, $matches))
		{
			$this->_logger->log(__METHOD__. ': Matched.');					

			$message = $this->pollData($matches[1], $matches[2], $this->generatePollCommand($station), $timeout);

			if ($message != false)
			{
				// Fail response:
				// @B [STATION_CODE] DR fail CRC $
				if(preg_match('/^@B'. strtoupper($station->station_id_code) .'DRfail[0-9a-zA-Z]{6}\$$/i', $message))
				{
					$this->_logger->log(__METHOD__. ': Fail response received.');
					
					return false;
				}
				
				$this->onReceiveMessageEvent($message, $station->station_id);
				
				return true;
			}
		}
		else 
		{
			$this->_logger->log(__METHOD__. ': No matches found.');
		}
		
		return false;
	}
	
	protected function pollData($address, $port, $command, $timeout)
	{
		$this->_logger->log(__METHOD__, array('address' => $address, 'port' => $port, 'command' => $command));
		
        // !!! PROTOCOL (TCP by default) SHOULD BE IN LOWER CASE !!!
		$uri = 'tcp'.'://'. $address .':'. $port;
		
		$this->_logger->log(__METHOD__ .': Trying to connect', array('uri' => $uri, 'timeout' => $timeout));
		
		// Creates socket connection with IP:port
        $client = @stream_socket_client($uri, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
		
		if ($client === false)
		{
			$this->_logger->log(__METHOD__ .': Client connect error', array('error' => $errstr, 'number' => $errno));
			
			return false;
		}
		
		@stream_set_timeout($client, $timeout);
		
		$this->_logger->log(__METHOD__ .': Sending command', array('command' => $command));
		
		$status = @stream_socket_sendto($client, $command ."\n");
		
		if ($status === false)
		{
			$erroInfo = $this->getSocketErrorInfo($client);
			
			$this->_logger->log(__METHOD__ .': Error on sending command', array('error' => $erroInfo['error'], 'number' => $erroInfo['number']));
			
			@stream_socket_shutdown($client, STREAM_SHUT_RDWR);
			
			return false;
		}
		
		$this->_logger->log(__METHOD__ .': Sent result', array('Bytes sent' => $status));
		
		//$time = 0;
		$line = null;
		$message = '';
		
		$this->_logger->log(__METHOD__ .': Starting to read response.');
		
		// Wait for message end
		while (strpos($line, '$') === false)
		{
			$line = @fread($client, 1024);
			
			$this->_logger->log(__METHOD__, array('read line' => $line));
			
			if (strlen($line) === 0)
			{
				$this->_logger->log(__METHOD__ .': Reading timeout exceeded.');
				
				@stream_socket_shutdown($client, STREAM_SHUT_RDWR);
					
				return false;
			}
			
			$message .= $line;
		}

		@stream_socket_shutdown($client, STREAM_SHUT_RDWR);

		$this->_logger->log(__METHOD__, array('return' => $message));
		
        return $message;    
	}	
	
	protected function getSocketErrorInfo($socket)
	{
		$number = @socket_last_error($socket);
	
		return array(
			'number' => $number,
			'error' => @socket_strerror($number), 
		);
	}	
	
	public function check()
	{
		$this->_logger->log(__METHOD__);
		
		$uri = 'tcp'.'://'. $this->_station->communication_esp_ip .':'. $this->_station->communication_esp_port;
		
		$server = @stream_socket_client($uri, $errno, $errstr, STREAM_CLIENT_CONNECT);
		
		if ($server !== false)
		{
			@fclose($server);
			
			return true;
		}
		
		$this->_errors[] = $errstr .'('. $errno .')';
		
		return false;
	}
	
	/**
	 * Generates poll command for a given station.
	 * 
	 * @param Station $station
	 * @return string
	 */
	protected function generatePollCommand($station)
	{
		$command = 'C';		
		$command .= $station->station_id_code;
		$command .= 'DR';
		$command .= It::prepareCRC($command);
		
		$command = '@'. $command .'$';
		
		return $command;		
	}
	
	/**
	 * Params:
	 * # timeout - 
	 * @param array $params Array of params for connection
	 */
	public function setParams($params)
	{
		if (array_key_exists('timeout', $params))
		{
			$this->_timeout = $params['timeout'];
		}
	}
}

?>
