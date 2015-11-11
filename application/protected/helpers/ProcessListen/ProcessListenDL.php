<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListenDL extends BaseComponent
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
    public function __construct($logger, $source, $by, $listener, $callerClass, $connector)
    {
        if ($callerClass!=='ProcessListenCom') {
            return false;
        }

        parent::__construct($logger);

        $this->_logger->log(__METHOD__);
        $this->_connector    = $connector;

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

		$this->_logger->log(__METHOD__);
		$this->settings = Settings::model()->find();
		$this->synchronization = new Synchronization();

        return true;
    }
    

    public function run()
	{

        ListenerProcess::addComment($this->listener->listener_id, 'comment','going to start listening datalogger, source= '. $this->source);
        $i = 0;
        while (true)
        {
          $this->listenDLTorrentTemp($i++);
        }

    }

    // read messages from DataLogger
    protected function listenDLTorrentTemp($cycle = 1)
    {
        $this->_logger->log(__METHOD__, array('cycle' => $cycle));

        $this->_connector->setParams(array('timeout' => 60));

        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Opening COM connection');

        $messages = null;

        $process = $this;
        $logger = $process->_logger;


        $this->_connector->onReceiveMessage = function ($message) use (&$process, &$logger) {
//            $logger->log(__METHOD__ . ' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));
//
//            $messageId = ListenerLogTemp::addNew($message, $process->listener->listener_id, $process->settings->overwrite_data_on_listening, 'datalogger');
//
//            ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #' . $messageId);

            $logger->log(__METHOD__ .' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));

            if (is_null(SMSCommand::setResponse($message))) {
                $this->_logger->log(__METHOD__ . ' Message type: ' . 'default');
                $messageId = ListenerLog::addNew($message, $process->listener->listener_id, $process->settings->overwrite_data_on_listening, 'datalogger');
                ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #'. $messageId);
            } else {
                $this->_logger->log(__METHOD__ . ' Message type: ' . 'sms command');
            }
            
        };

        $result = $this->_connector->readData($messages);

        $this->_logger->log(__METHOD__ .' Complete listen datalogger.', array('cycle' => $cycle, 'result' => $result));
    }
     // read messages from DataLogger
    protected function listenDLTorrent($cycle = 1)
    {
        $this->_logger->log(__METHOD__, array('cycle' => $cycle));

        $this->_connector->setParams(array('timeout' => 60));

        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Opening COM connection');

        $messages = null;

        $process = $this;
        $logger = $process->_logger;


        $this->_connector->onReceiveMessage = function ($message) use (&$process, &$logger) {
            $logger->log(__METHOD__ . ' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));

            $messageId = ListenerLog::addNew($message, $process->listener->listener_id, $process->settings->overwrite_data_on_listening, 'datalogger');

            ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #' . $messageId);
        };

        $result = $this->_connector->readData($messages);

        $this->_logger->log(__METHOD__ .' Complete listen datalogger.', array('cycle' => $cycle, 'result' => $result));
    }

}
?>