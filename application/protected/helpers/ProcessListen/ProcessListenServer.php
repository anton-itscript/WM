<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListenServer extends BaseComponent
{

	// 1) ESP's IP and port: 192.168.12.1:4000
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
		
		$this->_logger->log(__CLASS__.' '.__METHOD__);
		
        $this->source       = $source;
        $this->listener     = $listener;
        $this->by           = $by;

        if (!$this->init()) {
            return false;
        }
        $this->run();
    }
    


    public function init()
	{
        if(preg_match('/^([a-zA-Z]{3,})\:([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|[a-zA-Z0-9\-\.]+\.[a-zA-Z]+|localhost)\:([0-9]{1,5})$/i', $this->source, $matches)) {
            $this->_connector = new TcpIpServerConnector($this->_logger, $matches[1], $matches[2], $matches[3]);
            $this->_logger->log(__CLASS__ . ' ' . __METHOD__);
            $this->settings = Settings::model()->find();
            $this->synchronization = new Synchronization();
            return true;
        } else {
            return false;
        }
    }
    

    public function run()
	{
        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Going to start TCP-server, source= '. $this->source);

        while (true) {
            $this->listenTemp();
            sleep(10);
        }
    }


	protected function listenTemp()
	{
		$this->_logger->log(__CLASS__.' '.__METHOD__ .' Start listen.', array('source' => $this->source));
		$messages = null;
		
		$process = $this;
		$logger = $process->_logger;
		
		$this->_connector->onReceiveMessage = function ($message, $station_id, $source_info) use (&$process, &$logger)
			{
				$logger->log(__CLASS__.' '.__METHOD__ .' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));

				$messageId = ListenerLogTemp::addNew(
                                                        $message,
                                                        $process->listener->listener_id,
                                                        $process->settings->overwrite_data_on_listening,
                                                        'server',
                                                        0,
                                                        $source_info
                                                     );

				ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #'. $messageId);
			};
		
		$this->_connector->readData($messages);

		$this->_logger->log(__CLASS__.' '.__METHOD__ .' Complete listen.', array('source' => $this->source));
	}

	protected function listen()
	{
		$this->_logger->log(__CLASS__.' '.__METHOD__ .' Start listen.', array('source' => $this->source));
		$messages = null;

		$process = $this;
		$logger = $process->_logger;

		$this->_connector->onReceiveMessage = function ($message, $station_id, $source_info) use (&$process, &$logger)
			{
				$logger->log(__CLASS__.' '.__METHOD__ .' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));

				$messageId = ListenerLog::addNew($message, $process->listener->listener_id, $process->settings->overwrite_data_on_listening, 'server', 0, $source_info);

				ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #'. $messageId);
			};

		$this->_connector->readData($messages);

		$this->_logger->log(__CLASS__.' '.__METHOD__ .' Complete listen.', array('source' => $this->source));
	}

}
?>