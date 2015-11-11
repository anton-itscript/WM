<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListenModem extends BaseComponent
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

        $this->_logger->log(__CLASS__.' '.__METHOD__);
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

		$this->_logger->log(__CLASS__.' '.__METHOD__);
		$this->settings = Settings::model()->find();
        $this->synchronization = new Synchronization();

        return true;
    }
    

    public function run()
	{

        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'going to start listening for SMS messages, source= '. $this->source);

        $this->_connector->onReceiveMessages = function ($messages) {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'found '. count($messages) .' messages at modem');
        };

        $this->_connector->onReceiveMessage = function ($message, $stationId) {
            $this->_logger->log(__CLASS__.' '.__METHOD__ . ' Found message at modem: ' . $message);
            /**
             * if message is not response by sms command
             * then use default function
             */
            if (is_null(SMSCommand::setResponse($message))) {
                $this->_logger->log(__CLASS__.' '.__METHOD__ . ' Message type: ' . 'default');
//                $messageId = ListenerLog::addNew($message, $this->listener->listener_id, $this->settings->overwrite_data_on_listening, $stationId);
                $messageId = ListenerLogTemp::addNew($message, $this->listener->listener_id, $this->settings->overwrite_data_on_listening, $stationId);
                ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Found message at modem, message id:' . $messageId . ', message: ' . $message);
            } else {
                $this->_logger->log(__CLASS__.' '.__METHOD__ . ' Message type: ' . 'sms command');
            }
        };

        $i = 0;

        $SMSCOMPort = new SMSCOMPort();
        // For send sms command
        //$SMSCOMPort->COM;
        //$com = yii::app()->params['com_for_send_sms_command'];
        if ($this->source == $SMSCOMPort->COM) {
            ListenerProcess::addComment($this->listener->listener_id, 'sms_command', 'going to start send SMS command, source= '. $this->source);
            while(true) {
                if ($this->synchronization->isMaster()) {
                    $this->grabModemMessages($i++);
                    $this->sendSMSByModem($i);
                }
                sleep(20);
            }
        } else {
            while(true) {
                if ($this->synchronization->isMaster()) {
                    $this->grabModemMessages($i++);
                }
                sleep(20);
            }
        }

    }

    // read messages from modem and delete them
    protected function grabModemMessages($cycle)
    {
        $this->_logger->log(__CLASS__.' '.__METHOD__, array('cycle' => $cycle));

        $output = null;

        if ($this->_connector->readData($output) === false) {}

        $this->returnResult(__CLASS__.' '.__METHOD__, $output);
    }

    /**
     * Send sms commands
     *
     * @param $cycle int
     */
    protected function sendSMSByModem($cycle)
    {
        $this->_logger->log(__CLASS__.' '.__METHOD__, array('cycle' => $cycle));

        // Find unsent sms
        /** @var array|SMSCommand[] $sms_commands */
        $sms_commands = SMSCommand::model()->with('station')->findAllByAttributes(
            ['sms_command_status' => SMSCommand::STATUS_NEW],
            ['limit' => 5]
        );

        // Sent sms
        if (isset($sms_commands) && $count = count($sms_commands)) {


            foreach ($sms_commands as $sms_command) {

                $this->_logger->log(__CLASS__.' '.__METHOD__ . ' sms_command_id: ' . $sms_command->sms_command_id);

                $sms = new SmsMessageSender(
                    $this->_logger,
                    $this->listener,
                    (new PhpSerial($this->_logger)),
                    Yii::app()->params['com_connect_params'],
                    $this->source,
                    $sms_command->station->phone_number,
                    $sms_command->sms_command_message);

                if ($sms->send()) {
                    $sms_command->sms_command_status = SMSCommand::STATUS_SENT;
                    if (!$sms_command->save()) {
                        $this->_logger->log(__CLASS__.' '.__METHOD__ . ' sms_command: ' . 'not save! ' . json_encode($sms_command->getErrors()));
                    }
                } elseif ($sms->hasError()) {
                    foreach ($sms->errors() as $error) {
                        $this->_logger->log(__CLASS__.' '.__METHOD__ . ' error: ' . $error);
                    }
                }

                $this->_logger->log(__CLASS__.' '.__METHOD__ . ' sms_command_status: ' . $sms_command->sms_command_status);
            }

            $sms_commands_sent = array_filter($sms_commands, function($sms_command) {
                return $sms_command->sms_command_status == SMSCommand::STATUS_SENT;
            });


            if ($count = count($sms_commands_sent)) {
                ListenerProcess::addComment(
                    $this->listener->listener_id, 'sms_command',
                    'Sending complete. Submitted ' . $count . ' SMS command.
                    SMSCommand ids:' . implode(', ', array_keys(CHtml::listData($sms_commands_sent, 'sms_command_id', 'sms_command_message'))));
            } else {
                ListenerProcess::addComment(
                    $this->listener->listener_id, 'sms_command',
                    'Sending complete. Submitted ' . $count . ' SMS command.');
            }
        }
    }
}
?>