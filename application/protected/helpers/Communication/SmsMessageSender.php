<?php

/**
 * Sends SMS message using AT commands.
 *
 * @author
 */
class SmsMessageSender extends BaseComponent
{
	/**
	 *
	 * @access protected
	 * @var Listener 
	 */
	protected $_listener;
	
	/**
	 * Serial port connector.
	 * 
	 * @access protected
	 * @var PhpSerial 
	 */
	protected $_serial;
	
	/**
	 * 
	 * @access protected
	 * @var array 
	 */
	protected $_serialPortParams;
	
	/**
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_serialPort;
	
	/**
	 * @access protected
	 * @var string 
	 */
	protected $_phoneNumber;
	
	/**
	 * Text of SMS message.
	 * 
	 * @access protected
	 * @var string 
	 */
	protected $_messageText;
	
	/**
	 * Array of errors.
	 * @var array 
	 */
	protected $_errors = array();
	
	/**
	 * Sends seprate command to COM port and wait for response.
	 * 
	 * @param string $command Command to send.
	 * @param string $output Returns response here (optional).
	 * @param float $timeout Timeout for waiting response (optional).
	 * @return boolean True if there were no errors, false - otherwise.
	 */
	protected function sendCommand($command, &$output = null, $timeout = 1)
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
	 */
	protected function sendSmsMessage()
	{
		$this->_logger->log(__METHOD__);
				
		// Set SMS text mode
		$command = 'AT+CMGF=1'."\r";
		
		if (!$this->sendCommand($command))
		{
			$this->_logger->log(__METHOD__ ." Can't set SMS text mode.");
			
			return false;
		}
		
		$this->_logger->log(__METHOD__ .' Wait 2 seconds.');
		sleep(2);
		
		$command = 'AT+CMGS="'. $this->_phoneNumber .'"'."\r";
		$response = null;
		
		if (!$this->sendCommand($command, $response))
		{
			$this->_logger->log(__METHOD__ ." Can't send command", array('command' => $command, 'response' => $response));
			
			return false;
		}
		
		$this->_logger->log(__METHOD__, array('response' => $response));
		
		
		$this->_logger->log(__METHOD__ .' Wait 2 seconds.');
		sleep(2);
		
		$command = $this->_messageText . chr(26);
		
		if (!$this->sendCommand($command, $response))
		{
			$this->_logger->log(__METHOD__ ." Can't send command", array('command' => $command, 'response' => $response));
			
			return false;
		}
		
		$this->_logger->log(__METHOD__, array('response' => $response));
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * @param ILogger $logger
	 * @param Listener $listener
	 * @param PhpSerial $serial
	 * @param array $serialPortParams
	 * @param int $serialPort
	 * @param string $phoneNumber
	 * @param string $messageText
	 */
	public function __construct($logger, $listener, $serial, $serialPortParams, $serialPort, $phoneNumber, $messageText)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
		$this->_listener = $listener;
		
		$this->_serial = $serial;
		$this->_serialPortParams = $serialPortParams;
		
		$this->_serialPort = $serialPort;
		
		$this->_phoneNumber = $phoneNumber;
		$this->_messageText = $messageText;
	}
	
	/**
	 *
	 * @return array Array of string with errors. 
	 */
	public function errors()
	{
		return $this->_errors;
	}

	/**
	 * Error handler for 
	 * 
	 * @param int $errno Error level.
	 * @param string $errstr Error message.
	 */
	public function errorHandler($errno, $errstr)
	{
		$this->_logger->log(__METHOD__, array('level' => $errno, 'message' => $errstr));
		
		$this->_errors[] = $errstr;
	}
	
	/**
	 * 
	 */
	public function send()
	{
		$this->_logger->log(__METHOD__, array('phone' => $this->_phoneNumber, 'message' => $this->_messageText));
		
		$this->_errors = array();
		
		set_error_handler(array($this, 'errorHandler'));
		
		try {
			if ($this->_serial->deviceSet($this->_serialPort) === true) {

				$this->_serial->confBaudRate($this->_serialPortParams['baudrate']);
				$this->_serial->confStopBits($this->_serialPortParams['stopbits']);
				$this->_serial->confParity($this->_serialPortParams['parity']);
				$this->_serial->confCharacterLength($this->_serialPortParams['databits']);
				$this->_serial->confFlowControl($this->_serialPortParams['hardwareflowcontrol']);
				
				if ($this->_serial->deviceOpen('r+b') === true) {

					if ($this->sendSmsMessage()) {
						$this->_logger->log(__METHOD__ .' SMS message was sent', array('phone_number' => $this->_phoneNumber, 'serial_port' => $this->_serialPort, 'message' => $this->_messageText));
						ListenerProcess::addComment($this->_listener->listener_id, 'sms_command', 'SMS message was sent to: "'. $this->_phoneNumber .'" using: '. $this->_serialPort);
					} else {
						$this->_logger->log(__METHOD__ .' Failed to send SMS message', array('phone_number' => $this->_phoneNumber, 'serial_port' => $this->_serialPort, 'message' => $this->_messageText));
						ListenerProcess::addComment($this->_listener->listener_id, 'sms_command', 'Failed to send SMS message to: "'. $this->_phoneNumber .'" using: '. $this->_serialPort);
						$this->_errors[] = 'SMS message was not sent.';
					}
					
					$this->_serial->deviceClose();
				} else {
					$this->_errors[] = 'Can\'t open port "'. $this->_serialPort .'".';
				}
			} else {
				$this->_errors[] = 'Can\'t set port "'. $this->_serialPort .'".';
			}
		} catch(Exception $ex) {
			if ($this->_serial->_dState === PhpSerial::SERIAL_DEVICE_OPENED) {
				$this->_serial->deviceClose();
			}
			
			$this->_errors[] = $ex->getMessage();
		}
		
		restore_error_handler();	
		
		return (count($this->_errors) === 0);
	}

    /**
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->_errors);
    }
}

?>
