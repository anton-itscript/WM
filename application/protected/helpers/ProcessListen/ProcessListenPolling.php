<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListenPolling extends BaseComponent
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
    
	// init listener object. If it is already exists and still active - there is 
	// no need to continue.
    public function init()
	{
        preg_match('/^POLLER\:([a-zA-Z0-9]{1,5})$/i', $this->source, $matches);

        $station = Station::model()->findByAttributes(array('station_id_code' => strtoupper($matches[1])));

        if (is_null($station))
        {
            $this->_logger->log(__CLASS__.' '.__METHOD__ .': Station is not found', array('station_code' => strtoupper($matches[1])));
            return false;
        }
        else
        {
            $this->_connector = new TcpIpPollerConnector($this->_logger, $station, 60);

            if (Yii::app()->params['sms_params']['enabled'] === true)
            {
                $phpSerial = new PhpSerial($this->_logger);

                $this->_smsMessageSender = new SmsMessageSender(
                    $this->_logger,
                    $this->listener,
                    $phpSerial,
                    Yii::app()->params['com_connect_params'],
                    Yii::app()->params['sms_params']['serial_port'],
                    $station->phone_number,
                    $station->sms_message);
            }
            else
            {
                $this->_logger->log(__CLASS__.' '.__METHOD__ .' Reset SMS message is disabled');
            }
        }

//        $this->_connector = new TcpIpServerConnector($this->_logger, $matches[1], $matches[2], $matches[3]);
		$this->_logger->log(__CLASS__.' '.__METHOD__);
		$this->settings = Settings::model()->find();
        $this->synchronization = new Synchronization();

        return true;

    }
    

    public function run()
	{

        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'going to start polling, source= '. $this->source);

        $failureCount = 0;
        $modemFailureCount = 0;

        $resetSmsMessageWasSent = false;
        $modemReset = false;

        $i = 0;

        while (true)
        {
            $time = time();

            for ($j = 0; $j < 4; $j++) {
                $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Trying to poll data', array('try' => $j));

                // If message was received then exit loop
                if ($this->listenPollingTemp($i++)) {
                    $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Message received on', array('try' => $j));

                    $resetSmsMessageWasSent = false;
                    $modemReset = false;

                    break;
                }

                $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Delay 1 minute before next try.');

                // Wait 1 minute before next try
                sleep(60); // 1min

                if ($j === 3) {
                    $failureCount++;
                    $modemFailureCount++;
                }
            }

            if (!is_null($this->_smsMessageSender) && ($failureCount >= Yii::app()->params['sms_params']['failure_count_before_send_sms'])) {
                $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Going to send reset SMS message.', array('try' => $resetSmsMessageWasSent + 1));

                $failureCount = 0;

                if ($resetSmsMessageWasSent > 3) {
                    $this->_logger->log(__CLASS__.' '.__METHOD__ . ' Reset SMS message was already sent');
                } else {
                    if ($this->_smsMessageSender->send()) {
                        $resetSmsMessageWasSent += 1;

                        $this->_logger->log(__CLASS__.' '.__METHOD__ . ' SMS message has been sent');
                    } else {
                        $this->_logger->log(__CLASS__.' '.__METHOD__ . ' Failed to send SMS message');
                    }
                }
            }

            if ($modemFailureCount >= Yii::app()->params['polling_params']['failure_count_before_modem_reset']) {
                if (Yii::app()->params['polling_params']['enabled'] === true) {
                    $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Going to reset polling modem.');

                    $modemFailureCount = 0;

                    if ($modemReset) {
                        $this->_logger->log(__CLASS__.' '.__METHOD__ . ' Modem was already reset.');
                    } else {
                        $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Resetting.');
                        ListenerProcess::addComment($this->listener->listener_id, 'modem_reset', ' Polling modem was reset.');

                        // Send KILL to wvdial to force modem to re-create connection
                        It::runAsynchCommand('sudo killall wvdial');

                        $modemReset = true;
                    }
                } else {
                    $this->_logger->log(__CLASS__.' '.__METHOD__ . ': Resetting of polling modem is disabled.');
                }
            }

            // Delay for 10 minutes - time spent on tries.
            $time = Yii::app()->params['polling_params']['interval'] - (time() - $time);
            $this->_logger->log(__CLASS__.' '.__METHOD__ .': Delay before next poll', array('seconds' => $time));

            sleep($time);
        }
    }

    /**
     * Read messages from DataLogger using TCP/IP
     *
     * @param int $cycle
     * @return boolean
     */
    protected function listenPolling($cycle = 1)
    {
        $this->_logger->log(__CLASS__.' '.__METHOD__, array('cycle' => $cycle));

        $this->_connector->setParams(array('timeout' => 60)); // 1min

        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Start polling');

        $messages = null;

        $process = $this;
        $logger = $process->_logger;



            $this->_connector->onReceiveMessage = function ($message, $stationId) use (&$process, &$logger) {
                $logger->log(__CLASS__.' '.__METHOD__ . ' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));

                $messageId = ListenerLog::addNew($message, $process->listener->listener_id, $process->settings->overwrite_data_on_listening, 'poller', $stationId);

                ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #' . $messageId);

            };

        $result = $this->_connector->readData($messages);
        $this->_logger->log(__CLASS__.' '.__METHOD__ .' Complete listen datalogger.', array('cycle' => $cycle, 'result' => $result));


        return $result;
    }

    protected function listenPollingTemp($cycle = 1)
    {
        $this->_logger->log(__CLASS__.' '.__METHOD__, array('cycle' => $cycle));

        $this->_connector->setParams(array('timeout' => 60)); // 1min

        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Start polling');

        $messages = null;

        $process = $this;
        $logger = $process->_logger;



            $this->_connector->onReceiveMessage = function ($message, $stationId) use (&$process, &$logger) {
                $logger->log(__CLASS__.' '.__METHOD__ . ' New message', array('message' => $message, 'listener_id' => $process->listener->listener_id, 'overwrite' => $process->settings->overwrite_data_on_listening));

                $messageId = ListenerLogTemp::addNew($message, $process->listener->listener_id, $process->settings->overwrite_data_on_listening, 'poller', $stationId);

                ListenerProcess::addComment($process->listener->listener_id, 'comment', 'got msg #' . $messageId);

            };

        $result = $this->_connector->readData($messages);
        $this->_logger->log(__CLASS__.' '.__METHOD__ .' Complete listen datalogger.', array('cycle' => $cycle, 'result' => $result));


        return $result;
    }

}
?>