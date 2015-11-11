<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListen extends BaseComponent
{
	// data source, examples: 
	// 1) com port: COM1, COM2,... 
	// or
	// 2) ESP's IP and port: 192.168.12.1:4000
    public $source;
	
	// who has initiated listening process?
	// just a string for extended logging
    public $by;
    // use this option for COMs ports. to see modem and datalogger
    public $communication_type;

	// system settings stored at DB
    public $settings;



	// object containing information about current listening process. 
	// it is the record from `listener` table
    /*
 *
 * @listener Listener
 * */
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
	 *	Used to send reset SMS messages to modems.
	 *  
	 * @access protected
	 * @var SmsMessagesender 
	 */
	protected $_smsMessageSender = null;
	
	/**
	 * Ctor.
	 * 
	 * @param ILogger $logger
	 * @param string $source Source string with connection info
	 * @param string $by User who runs listener
	 */
    public function __construct($logger, $source, $communication_type, $by)
	{
		parent::__construct($logger);
		
		$this->_logger->log(__METHOD__);
		
        $this->source = $source;
        $this->communication_type = $communication_type;
        $this->by = $by;
		
        $this->init();
    }
    
	// init listener object. If it is already exists and still active - there is 
	// no need to continue.
    public function init()
	{
		$this->_logger->log(__METHOD__);
		$this->settings = Settings::model()->find();
        $this->_logger->log(__METHOD__.$this->communication_type);
        $this->listener = Listener::getCurrent($this->source, $this->communication_type);


        $this->_logger->log(__METHOD__.': '. print_r($this->listener,1));
        if (!$this->listener->started) {
            $this->listener->process_pid = getmypid();
            $this->listener->started = time();
            $this->listener->save();
            Synchronization::trySetActualListenerId($this->source, $this->listener->listener_id);
			ListenerProcess::addComment($this->listener->listener_id, 'started', 'by '. $this->by);
        } else {
            ListenerProcess::addComment($this->listener->listener_id, 'still_in_process');
        }       
    }
    
	// run: recognize hardware and listen to it
    public function run()
	{
		$this->_logger->log(__CLASS__.' '.__METHOD__);
		
        $this->_recognizeHardware();

		switch ($this->hardware)
		{
			case 'com':
                new ProcessListenCom($this->_logger,$this->source,$this->by,$this->listener);
				break;
			
			case 'client':
                new ProcessListenClient($this->_logger,$this->source,$this->by,$this->listener);
                break;
			
			case 'poller':
                new ProcessListenPolling($this->_logger,$this->source,$this->by,$this->listener);
				break;
			
			case 'server':
                new ProcessListenServer($this->_logger,$this->source,$this->by,$this->listener);
				break;
		}
		
        ListenerProcess::addComment($this->listener->listener_id, 'stopped', 'Script execution was completed');
        Listener::stopConnection($this->listener->listener_id);
    }


	
    // recognize hardware by source string
    protected function _recognizeHardware()
	{
		$this->_logger->log(__CLASS__.' '.__METHOD__, array('source' => $this->source));
		$matches = array();
		
		// Check IP_ADDRESS:PORT pattern
		if(preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\:([0-9]{1,5})$/', $this->source, $matches))
		{
			$this->_logger->log(__CLASS__.' '.__METHOD__, array('hardware' => 'client'));
			$this->hardware = 'client';
			ListenerProcess::addComment($this->listener->listener_id, 'hardware_recognizing', $this->source.' is IP address. Script supposes it is ESP.');
		}
		
		// Check POLLER:STATION_CODE pattern
		else if(preg_match('/^POLLER\:([a-zA-Z0-9]{1,5})$/i', $this->source, $matches))
		{
			$this->_logger->log(__CLASS__.' '.__METHOD__, array('hardware' => 'poller'));
			$this->hardware = 'poller';
		}
		
		// Check PROTOCOL:IP_ADDRESS|DOMAIN_NAME:PORT pattern
		else if(preg_match('/^([a-zA-Z]{3,})\:([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+|localhost)\:([0-9]{1,5})$/i', $this->source, $matches))
		{
			$this->_logger->log(__CLASS__.' '.__METHOD__, array('hardware' => 'server'));
			$this->hardware = 'server';
			ListenerProcess::addComment($this->listener->listener_id, 'hardware_recognizing', $this->source.' is IP address with protocol. Script supposes it is Server.');
		}
		
		// Check COM## pattern
		else if (preg_match('/COM[0-9]+/', $this->source))
		{
			$this->_logger->log(__CLASS__.' '.__METHOD__ .' Check COM port');
            // else it is COM port connection: it can be DataLogger or GSM modem
			ListenerProcess::addComment($this->listener->listener_id, 'hardware_recognizing', $this->source.' is COM port.');
            $this->hardware = 'com';
        }

		else
		{
			$this->_logger->log(__CLASS__.' '.__METHOD__ .' Source has no matches');
		}
    }



}
?>