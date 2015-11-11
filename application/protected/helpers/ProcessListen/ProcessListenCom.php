<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListenCom extends BaseComponent
{


    public $source;
	
	// who has initiated listening process?
	// just a string for extended logging
    public $by;
	
	// system settings stored at DB
    public $settings;

	// server role settings
    public $synchronization;

	// object containing information about current listening process. 
	// it is the record from `listener` table 
    public $listener;
	
	// hardware recognized for listening
    public $hardware;
	
	/**
	 * 
	 * @access protected
	 * @var BaseConnector
	 */
	protected $_connector;
	
	

	
	/**
	 * Ctor.
	 * 
	 * @param ILogger $logger
	 * @param string $source Source string with connection info
	 * @param string $by User who runs listener
	 */
    public function __construct($logger, $source, $by, $listener)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
        $this->source       = $source;
        $this->listener     = $listener;
        $this->by           = $by;
		
        if (!$this->init()) {
            return false;
        }
        $this->run();
    }
    
	// init listener object. If it is already exists and still active - there is 
	// no need to continue.
    public function init()
	{
        if (preg_match('/COM[0-9]+/', $this->source))
        {
            $this->_logger->log(__METHOD__ .' Check COM port');

            // else it is COM port connection: it can be DataLogger or GSM modem
            ListenerProcess::addComment($this->listener->listener_id, 'hardware_recognizing', $this->source.' is COM port.');

            try
            {
                // try to send AT command
                // if script recieves "OK" - this is GSM modem. Else - it can be only DataLogger
                $serial = new PhpSerial($this->_logger);
                $this->_logger->log(__METHOD__ .$this->source);
                $serial->deviceSet($this->source);

                $serial->confFlowControl(Yii::app()->params['com_connect_params']['hardwareflowcontrol']);
                $serial->confBaudRate(Yii::app()->params['com_connect_params']['baudrate']);
                $serial->confParity(Yii::app()->params['com_connect_params']['parity']);
                $serial->confStopBits(Yii::app()->params['com_connect_params']['stopbits']);
                $serial->confCharacterLength(Yii::app()->params['com_connect_params']['databits']);

                $this->_connector = new GsmModemSerialConnector($this->_logger, $serial);
                $this->_connector->setParams(array('port' => $this->source));

                ListenerProcess::addComment($this->listener->listener_id, 'hardware_recognizing', 'Script started to recognize hardware connected to PC via COM port');

                if ($this->listener->additional_param == 'SMS') {

                    while(1) {
                        if ($this->_connector->check ()) {
                            $this->_logger->log (__METHOD__, array('hardware' => 'modem'));
                            $this->hardware = 'modem';

                            ListenerProcess::addComment ($this->listener->listener_id, 'hardware_recognizing', 'Hardware connected to PC via COM port - is GSM Modem');

                            $this->_logger->log (__METHOD__, array('hardware' => $this->hardware));

                            return true;
                        }
                        sleep(60);
                    }
                }

                $this->_connector = new DataLoggerSerialConnector($this->_logger, $serial);
                $this->_connector->setParams(array('port' => $this->source));

                if ($this->listener->additional_param == 'DIRECT') {

                    if ($this->_connector->check ()) {
                        $this->_logger->log (__METHOD__, array('hardware' => 'datalogger'));
                        $this->hardware = 'dl';

                        ListenerProcess::addComment ($this->listener->listener_id, 'hardware_recognizing', 'Hardware connected to PC via COM port - is Datalogger');

                        $this->_logger->log (__METHOD__, array('hardware' => $this->hardware));

                        return true;
                    }
                }
            }
            catch (Exception $e)
            {
                $this->_logger->log(__METHOD__, array('ExceptionMessage' => $e->getMessage()));
            }

            $this->_logger->log(__METHOD__, array('hardware' => 'unknown'));
            $this->hardware = 'unknown';

            ListenerProcess::addComment($this->listener->listener_id, 'hardware_recognizing', 'No device found.');

            $this->_logger->log(__METHOD__, array('hardware' => $this->hardware));

            return false;
        }
    }
    
	// run: recognize hardware and listen to it
    public function run()
	{
        switch ($this->hardware)
        {
            case 'modem':
                new ProcessListenModem($this->_logger,$this->source,$this->by,$this->listener, __CLASS__,$this->_connector);
                break;

            case 'dl':
                new ProcessListenDL($this->_logger,$this->source,$this->by,$this->listener, __CLASS__,$this->_connector);
                break;
        }
    }

}
?>