<?php

/**
 * For testing purpuses.
 *
 * @author
 */
class SmsSendCommand extends CConsoleCommand
{
	protected $_logger = null;
	
	public function init()
	{
		parent::init();
		
		ini_set('memory_limit', '-1');
		set_time_limit(0);
		
		$this->_logger = LoggerFactory::getFileLogger('sms');
//		$this->_logger = LoggerFactory::getConsoleLogger();
	}
	
	public function run($args)
	{
		$this->_logger->log(__METHOD__, array('args' => $args));
		
		if (count($args) < 3) {
			$this->_logger->log(__METHOD__ .' Too few params.');
			
			echo 'Expected 3 parameters: [COM_PORT: COM#], [PHONE_NUMBER: ######], [SMS_MESSAGE: string]';
			exit;
		}
		$phpSerial = new PhpSerial($this->_logger);
		
		$smsSender = new SmsMessageSender($this->_logger,new Listener(), $phpSerial, Yii::app()->params['com_connect_params'], $args[0], $args[1], $args[2]);
		
		$this->_logger->log(__METHOD__ .' perform sending', array('serial_port' => $args[0], 'phone_number' => $args[1], 'message' => $args[2]));
		
		if (!$smsSender->send())
		{
			$this->_logger->log(__METHOD__ .' Errors occured', array('errors' => $smsSender->errors()));
			
			exit;
		}		
	}
}

?>
