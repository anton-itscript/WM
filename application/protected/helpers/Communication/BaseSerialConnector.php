<?php

/**
 * Description of BaseSerialConnector
 *
 * 
 */
abstract class BaseSerialConnector extends BaseConnector
{
	/**
	 * COM port component.
	 * 
	 * @access protected
	 * @var PhpSerial
	 * @link https://code.google.com/p/php-serial/ 
	 */
	protected $_serial;
	
	/**
	 * COM port.
	 * 
	 * @access protected
	 * @var string  
	 */
	protected $_port = null;
	
	
	/**
	 * COM port timeout in seconds.
	 * 
	 * @access protected
	 * @var int  
	 */
	protected $_timeout = 1;
	
	/**
	 * COM port baudrate.
	 * 
	 * @access protected
	 * @var int  
	 */
	protected $_baudrate = null;
	
	/**
	 * COM port flow control mode.
	 * 
	 * @access protected
	 * @var string  
	 */
	protected $_flowControl = null;
	
	/**
	 * COM port parity mode.
	 * 
	 * @access protected
	 * @var string  
	 */
	protected $_parity = null;
	
	/**
	 * COM port stop bits.
	 * 
	 * @access protected
	 * @var int  
	 */
	protected $_stopBits = null;
	
	/**
	 * COM port data bits.
	 * 
	 * @access protected
	 * @var int  
	 */
	protected $_dataBits = null;
	
	/**
	 * Error handler for 
	 * 
	 * @param int $errno Error level.
	 * @param string $errstr Error message.
	 */
	public function errorHandler($errno, $errstr)
	{
		$this->_logger->log(__METHOD__, array('number' => $errno, 'message' => $errstr));
		
		$this->_errors[] = $errstr;
	}
	
	/**
	 *
	 * @param array $output Returns aray of string or null if no need in response.
	 */
	protected function readCustomData(&$output)
	{
		$this->_logger->log(__METHOD__);
		
		$output = null;
		
		// Read ay data in this function overloads.
	}
	
	/**
	 * Checks specific data.
	 * 
	 * @abstract
	 * @access protected 
	 */
	protected abstract function checkCustomData();
	
	/**
	 * Sends seprate command to COM port and wait for response.
	 * 
	 * @param string $command Command to send.
	 * @param string $output Returns response here (optional).
	 * @param float $timeout Timeout for waiting response (optional).
	 * @return boolean True if there were no errors, false - otherwise.
	 */
	protected function sendCommand($command, &$output = null, $timeout = 0.1)
	{
		$this->_logger->log(__METHOD__, array('command' => $command));
		
		$this->_serial->sendMessage($command, $timeout);
		
		$response = $this->_serial->readPort();
		
		if ($response === false)
		{
			$this->_errors[] = 'Error during send command: '. $command;
			
			return false;
		}
		
		$output = $response;
		
		return true;
	}
	
	/**
	 *
	 * @param ILogger $logger
	 * @param PhpSerial $serial 
	 */
	public function __construct($logger, $serial)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_serial = $serial;
	}
	
	/**
	 * Opens COM port and tries to read messages.
	 * @param array $output
	 * @return boolean True if there was no errors, false - otherwise.
	 */
	public function readData(&$output)
	{
		parent::readData($output);	
		
		$this->_logger->log(__METHOD__);
		
		set_error_handler(array($this, 'errorHandler'));
		
		try 
		{
			if ($this->_serial->deviceSet($this->_port) === true)
			{
				if (!is_null($this->_baudrate))
				{
					$this->_serial->confBaudRate($this->_baudrate);
				}
				
				if (!is_null($this->_flowControl))
				{
					$this->_serial->confFlowControl($this->_flowControl);
				}
				
				if (!is_null($this->_parity))
				{
					$this->_serial->confParity($this->_parity);
				}
				
				if (!is_null($this->_stopBits))
				{
					$this->_serial->confStopBits($this->_stopBits);
				}
				
				if (!is_null($this->_dataBits))
				{
					$this->_serial->confCharacterLength($this->_dataBits);
				}
				
				if ($this->_serial->deviceOpen('r+b') === true)
				{
					$this->readCustomData($output);
					
					$this->_serial->deviceClose();
				}
				else
				{
					$this->_errors[] = 'Can\'t open port "'. $this->_port .'".';
				}
			}
			else
			{
				$this->_errors[] = 'Can\'t set port "'. $this->_port .'".';
			}
		}
		catch(Exception $ex)
		{
			if ($this->_serial->_dState === PhpSerial::SERIAL_DEVICE_OPENED)
			{
				$this->_serial->deviceClose();
			}
			
			$this->_errors[] = $ex->getMessage();
		}
		
		restore_error_handler();
		
		$result = (count($this->_errors) === 0);
		
		return $this->returnResult(__METHOD__, $result);
	}
	
	/**
	 * Opens COM port and test it on specific device.
	 * 
	 * @return boolean True if device is found, false - otherwise.
	 */
	public function check()
	{
		$this->_logger->log(__METHOD__);
		
		$result = false;
		
		set_error_handler(array($this, 'errorHandler'));
		
		try 
		{
			if ($this->_serial->deviceSet($this->_port) === true)
			{
				if (!is_null($this->_baudrate))
				{
					$this->_serial->confBaudRate($this->_baudrate);
				}
				
				if (!is_null($this->_flowControl))
				{
					$this->_serial->confFlowControl($this->_flowControl);
				}
				
				if (!is_null($this->_parity))
				{
					$this->_serial->confParity($this->_parity);
				}
				
				if (!is_null($this->_stopBits))
				{
					$this->_serial->confStopBits($this->_stopBits);
				}
				
				if (!is_null($this->_dataBits))
				{
					$this->_serial->confCharacterLength($this->_dataBits);
				}
				
				if ($this->_serial->deviceOpen('r+b') === true)
				{
					$result = $this->checkCustomData();
					
					$this->_serial->deviceClose();
				}
				else
				{
					$this->_errors[] = 'Can\'t open port "'. $this->_port .'".';
				}
			}
			else
			{
				$this->_errors[] = 'Can\'t set port "'. $this->_port .'".';
			}
		}
		catch(Exception $ex)
		{
			if ($this->_serial->_dState === PhpSerial::SERIAL_DEVICE_OPENED)
			{
				$this->_serial->deviceClose();
			}
			
			$this->_errors[] = $ex->getMessage();
		}
		
		restore_error_handler();
		
		$this->returnResult(__METHOD__, $result);
		
		return ($result && (count($this->_errors) === 0));
	}
	
	/**
	 * Params:
	 * # port - COM port. E.g. COM1, COM2.
	 * @param array $params Array of params for connection
	 */
	public function setParams($params)
	{
		$this->_logger->log(__METHOD__, array('params' => $params));
		
		if (array_key_exists('port', $params))
		{
			$this->_port = $params['port'];
		}
		
		if (array_key_exists('timeout', $params))
		{
			$this->_timeout = (int)$params['timeout'];
		}
		
		if (array_key_exists('baudrate', $params))
		{
			$this->_baudrate = (int)$params['baudrate'];
		}
		
		if (array_key_exists('flowControl', $params))
		{
			$this->_flowControl = $params['flowControl'];
		}
		
		if (array_key_exists('parity', $params))
		{
			$this->_parity = $params['parity'];
		}
		
		if (array_key_exists('stopBits', $params))
		{
			$this->_stopBits = $params['stopBits'];
		}
		
		if (array_key_exists('dataBits', $params))
		{
			$this->_dataBits = $params['dataBits'];
		}
	}
}

?>
