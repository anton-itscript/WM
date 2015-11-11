<?php

/**
 * Connects to serial port and send messages to it.
 *
 * @author
 */
class SerialPortClientConnector extends BaseClientConnector
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
	 * @access protected
	 * @var int 
	 */
	protected $_port;
	
	/**
	 * @access protected
	 * @var array 
	 */
	protected $_portParams;
	
	/**
	 * Ctor.
	 * 
	 * @param type $logger
	 * @param type $port
	 */
	public function __construct($logger, $port, $portParams)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_port = $port;		
		$this->_portParams = $portParams;	
		
		$this->_serial = new PhpSerial($this->_logger);
	}
	
	/**
	 * 
	 * @param int $timeout Not used here
	 * @return boolean True on success, false - otherwise.
	 */
	public function connect($timeout = 0)
	{
		$this->_logger->log(__METHOD__, array('port' => $this->_port));
		
		set_error_handler(array($this, 'errorHandler'));
		
		try 
		{
			if ($this->_serial->deviceSet($this->_port) === true)
			{
				$this->_serial->confFlowControl($this->_portParams['hardwareflowcontrol']);
				$this->_serial->confBaudRate($this->_portParams['baudrate']);
				$this->_serial->confParity($this->_portParams['parity']);
				$this->_serial->confStopBits($this->_portParams['stopbits']);
				$this->_serial->confCharacterLength($this->_portParams['databits']);
				
				if ($this->_serial->deviceOpen('w+b') === true)
				{
					return true;
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
			$this->_errors[] = $ex->getMessage();
		}
		
		restore_error_handler();
	}
	
	/**
	 * 
	 * @return boolean True on success, false - otherwise.
	 */
	public function disconnect()
	{
		$this->_logger->log(__METHOD__);
		
		if ($this->_serial->_dState !== PhpSerial::SERIAL_DEVICE_OPENED)
		{
			$this->_logger->log(__METHOD__ .' Serial port is not opened', array('port' => $this->_serial->_device));
			
			$this->_errors[] = 'Serial port '. $this->_serial->_device .' is not opened';
			
			return false;
		}
		
		$result = $this->_serial->deviceClose();
		
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
	 * @return boolean True on success, false - otherwise.
	 */
	public function sendMessage($message)
	{
		$this->_logger->log(__METHOD__, array('message' => $message));
		
		if ($this->_serial->_dState !== PhpSerial::SERIAL_DEVICE_OPENED)
		{
			$this->_logger->log(__METHOD__ .' Serial port is not opened', array('port' => $this->_serial->_device));
			
			$this->_errors[] = 'Serial port '. $this->_serial->_device .' is not opened';
			
			return false;
		}
		
		$this->_serial->sendMessage($message);
		
		return true;
	}
	
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
}

?>
