<?php

/**
 * This command is for test purposes to check possibility of polling data from datalogger.
 *
 * @author Alexandr
 */
class PollingCommand extends CConsoleCommand
{
	/**
	 * 
	 * @var ILogger
	 * @access protected
	 */
	protected $_logger = null;
	
	public function init()
	{
		parent::init();
		
		ini_set('memory_limit', '-1');
		ini_set('display_errors', '1');
        set_time_limit(0);
		
		error_reporting(E_ALL);
		
		$this->_logger = LoggerFactory::getFileLogger('poller');
//		$this->_logger = LoggerFactory::getConsoleLogger(); // For testing
	}
	
	// $args[0] = source name
    // $args[1] = who has started
    public function run($args) 
    {
		$this->_logger->log(__METHOD__);
		
        try
		{
			$stations = $this->getStationList();
		
			$this->_logger->log(__METHOD__ .': Found '. count($stations) .' stations.');
			
			foreach ($stations as $station) 
			{
				$this->pollDataFromStation($station);
			}			
        }
		catch (Exception $ex)
		{
            $this->_logger->log(__METHOD_, array('Error message' => $ex->getMessage()));            
        }
		
		$this->_logger->log(__METHOD__ .': Exiting.');
    }
	
	protected function pollDataFromStation($station)
	{
		$this->_logger->log(__METHOD__, array('station' => $station->station_id_code));
		
		$matches = array();
		
		$source = $station->communication_esp_ip .':'. $station->communication_esp_port;

		// Check IP_ADDRESS|DOMAIN_NAME:PORT pattern
		if(preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+)\:([0-9]{1,5})$/', $source, $matches))
		{
			$this->_logger->log(__METHOD__. ': Matched.');					

			$message = $this->pollData($matches[1], $matches[2], $this->generatePollCommand($station));

			if ($message != false)
			{
				ListenerLog::addNew($message, null, false, 'poller', $station->station_id);
			}
		}
	}
	
	protected function pollData($address, $port, $command)
	{
		$this->_logger->log(__METHOD__, array('address' => $address, 'port' => $port, 'command' => $command));
		
        $timeout = 6;
		
		// !!! PROTOCOL (TCP by default) SHOULD BE IN LOWER CASE !!!
		$uri = 'tcp'.'://'. $address .':'. $port;
		
		$this->_logger->log(__METHOD__ .': Trying to connect', array('uri' => $uri, 'timeout' => $timeout));
		
		// creates socket connection with IP:port
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
		$attempt = 1;
		
		$this->_logger->log(__METHOD__ .': Starting to read response.');
		
		// Wait for message end
		while (strpos($line, '$') === false)
		{
			$line = @fread($client, 1024);
			
			$this->_logger->log(__METHOD__, array('read line' => $line));
			
			if (strlen($line) === 0)
			{
				$this->_logger->log(__METHOD__ .': Reading timeout exceeded.', array('attempt' => $attempt));
				
				if ($attempt > 2)
				{
					$this->_logger->log(__METHOD__ .': Exceeded maximun attempt number.');
					
					@stream_socket_shutdown($client, STREAM_SHUT_RDWR);
					
					return false;
				}
			}
			
			$message .= $line;
			$attempt++;
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
	
	/**
	 * 
	 * @return array
	 */
	protected function getStationList()
	{
		return Station::model()->findAllByAttributes(array('communication_type' => 'gprs'));
	}
	
	protected function getStationByCode($code)
	{
		return Station::model()->findAllByAttributes(array('communication_type' => 'gprs', 'station_id_code' => $code));
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
}

?>
