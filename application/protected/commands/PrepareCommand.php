<?php

/*
 * Is called using command: "php console.php prepare", doesn't requires arguements
 * Is called every minute using schtasks
 */
class PrepareCommand extends CConsoleCommand
{
    /** @var null|Logger */
	protected $_logger = null;


    /*
     * @var Synchronization
     * */
    protected $synchronization;

	/**
	 * Clients for message forwarding.
	 * 
	 * @access protected
	 * @var array|TcpClientConnector[]
	 */
	protected $_forwardingClients = array();


    public function run($args){

        if(Yii::app()->mutex->lock('prepare',300)) {

            ini_set('memory_limit', '-1');
            set_time_limit(0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
            $this->synchronization  = new Synchronization();
            $this->_logger = LoggerFactory::getFileLogger('process_message');
//            $this->_logger = LoggerFactory::getConsoleLogger();
           if ($this->synchronization->isProcessed()) {
               $this->_logger->log(__METHOD__ .' synchronization in process');
               $this->run_process();
           } else {
               $this->_logger->log(__METHOD__ .' synchronization stopped');
               $this->run_process_base();
           }

            Yii::app()->mutex->unlock();

        }
    }


	/*
	 * gets first 100 unprocessed messages and process them
	 */
    public function run_process()
	{
		$this->_logger->log(__METHOD__);


        //теперь тут берём лог не из Listenerlog, a из ListenerLogTemp
//
//        $criteria = new CDbCriteria();
//            $criteria->condition = "is_processed = 0 and is_processing = 0";
//            $criteria->order = "log_id asc";
//            $criteria->limit = 100;
//
//        /** @var array|ListenerLog[] $logs */
//        $logs = ListenerLog::model()->findAll($criteria);


        // sent unsented messages right from alive master who take this role right here

            $processForwardedSlaveMessages = new ProcessForwardedSlaveMessages($this->_logger);
            $processForwardedSlaveMessages->forwardMessagesToSlave(10);



        //master
        if ($this->synchronization->isMaster()) {
            $this->_logger->log(__METHOD__ .' role Master');

            $criteria = new CDbCriteria();
            $criteria->condition = "is_processed = 0 and is_processing = 0";
            $criteria->condition .= " and from_master = 0";
            $criteria->condition .= " and synchronization_mode = 'master'";

            $criteria->order = "temp_log_id asc";
            $criteria->limit = 100;

            /** @var array|ListenerLogTemp[] $logs */
            $logs = ListenerLogTemp::model()->findAll($criteria);


            $this->_logger->log(__METHOD__ .'['. getmypid() .'] PrepareCommand...  found '. count($logs) .' unprocessed messages');

            // Forwarding messages
            $processForwardedMessages       = new ProcessForwardedMessages($this->_logger);
            $processForwardedSlaveMessages  = new ProcessForwardedSlaveMessages($this->_logger);

            $this->_logger->log(__METHOD__ .' Create records for forwarding');
            $this->_logger->log(__METHOD__ .' Create records for slave forwarding');
            foreach ($logs as $log)
            {
                // Create records for forwarding

                $processForwardedMessages->saveNewForwardedMessage($log);
                $processForwardedSlaveMessages->saveNewForwardedSlaveMessage($log);

                $log->is_processing = 1;
                $log->save();

                // run processing of message
                // $log - from ListenerLogTemp model
                $process_obj = new ProcessMessage($this->_logger, $log);
                $process_obj->run();
                // update message as "is processed"
                $log->is_processed = 1;
                $log->is_processing = 0;

                $log->save();


            }
                $this->_logger->log(__METHOD__ .' sent Forwarded Messages');
                $this->_logger->log(__METHOD__ .' sent Forwarded Slave Messages');

                $processForwardedMessages->forwardMessages(10);
                $processForwardedSlaveMessages->forwardMessagesToSlave(10);
        }


        //slave
        if ($this->synchronization->isSlave()) {
            $this->_logger->log(__METHOD__ .' role Slave');

            $criteria = new CDbCriteria();
            $criteria->condition = "is_processed = 0 and is_processing = 0";
            $criteria->condition .= ' and from_master = 1';
            $criteria->order = "temp_log_id asc";
            $criteria->limit = 100;

            /** @var array|ListenerLogTemp[] $logs */
            $logs = ListenerLogTemp::model()->findAll($criteria);


            $this->_logger->log(__METHOD__ . '[' . getmypid() . '] PrepareCommand...  found ' . count($logs) . ' unprocessed messages');

            foreach ($logs as $log) {

                $log->is_processing = 1;
                $log->save();

                // run processing of message
                // $log - from ListenerLogTemp model
                $process_obj = new ProcessMessage($this->_logger, $log);
                $process_obj->run();
                // update message as "is processed"
                $log->is_processed = 1;
                $log->is_processing = 0;

                $log->save();

            }

        }



















        // !! difficult situation
        // if has unprocess messages in master mode
        if ($this->synchronization->isSlave()) {
            $this->_logger->log(__METHOD__ .'  if has unprocess messages in master mode');
            $this->_logger->log(__METHOD__ .' role Slave');


            $processForwardedMessages       = new ProcessForwardedMessages($this->_logger);
            $processForwardedSlaveMessages  = new ProcessForwardedSlaveMessages($this->_logger);

            $criteria = new CDbCriteria();
            $criteria->condition = "is_processed = 0 and is_processing = 0";
            $criteria->condition .= " and from_master = 0";
            $criteria->condition .= " and synchronization_mode = 'master'";
            $criteria->order = "temp_log_id asc";
            $criteria->limit = 100;

            $logs = ListenerLogTemp::model()->findAll($criteria);

            $this->_logger->log(__METHOD__ . '[' . getmypid() . '] PrepareCommand...  found ' . count($logs) . ' unprocessed messages');

            $this->_logger->log(__METHOD__ .' Create records for forwarding');
            $this->_logger->log(__METHOD__ .' Create records for slave forwarding');

            foreach ($logs as $log)
            {

                $processForwardedMessages->saveNewForwardedMessage($log);
                $processForwardedSlaveMessages->saveNewForwardedSlaveMessage($log);

                $log->is_processing = 1;
                $log->save();

                // run processing of message
                // $log - from ListenerLogTemp model
                $process_obj = new ProcessMessage($this->_logger, $log);
                $process_obj->run();
                // update message as "is processed"
                $log->is_processed = 1;
                $log->is_processing = 0;

                $log->save();
            }


        }

         // where server is master, has message writes in slave mode
        // or processed message thats have been come from slave but them have been write on him in maser mode
        if ($this->synchronization->isMaster()) {
            $this->_logger->log(__METHOD__ .' where server is master, has message writes in slave mode');
            $this->_logger->log(__METHOD__ .' role Master');


            $criteria = new CDbCriteria();
            $criteria->condition = "is_processed = 0 and is_processing = 0";
            $criteria->condition .= ' and from_master = 1';
           // $criteria->condition .= " and synchronization_mode = 'slave'";
            $criteria->order = "temp_log_id asc";
            $criteria->limit = 100;


            $logs = ListenerLogTemp::model()->findAll($criteria);
            $this->_logger->log(__METHOD__ . '[' . getmypid() . '] PrepareCommand...  found ' . count($logs) . ' unprocessed messages');

            foreach ($logs as $log)
            {

                $log->is_processing = 1;
                $log->save();

                // run processing of message
                // $log - from ListenerLogTemp model
                $process_obj = new ProcessMessage($this->_logger, $log);
                $process_obj->run();
                // update message as "is processed"
                $log->is_processed = 1;
                $log->is_processing = 0;

                $log->save();
            }
        }
	}


    /*
     * gets first 100 unprocessed messages and process them
     */
    public function run_process_base()
    {
        $this->_logger->log(__METHOD__);
        $this->_logger->log(__METHOD__ .' role none; ');

        $this->_logger->log(__METHOD__ .' find  messages, that have been received in a base mode');

        $criteria = new CDbCriteria();
        $criteria->condition = "is_processed = 0 and is_processing = 0";
        $criteria->condition .= " and synchronization_mode = 'none'";

        $criteria->order = "temp_log_id asc";
        $criteria->limit = 100;

        /** @var array|ListenerLogTemp[] $logs */
        $logs = ListenerLogTemp::model()->findAll($criteria);


        $this->_logger->log(__METHOD__ .'['. getmypid() .'] PrepareCommand...  found '. count($logs) .' unprocessed messages');

        $this->_logger->log(__METHOD__ .' Create records for forwarding');

        // Forwarding messages
        $processForwardedMessages  = new ProcessForwardedMessages($this->_logger);
        $this->_logger->log(__METHOD__ .' Create records for forwarding');



        foreach ($logs as $log)
        {
            // Create records for forwarding
            $processForwardedMessages->saveNewForwardedMessage($log);

            $log->is_processing = 1;
            $log->save();

            // run processing of message
            // $log - from ListenerLogTemp model
            $process_obj = new ProcessMessage($this->_logger, $log);
            $process_obj->run();
            // update message as "is processed"
            $log->is_processed = 1;
            $log->is_processing = 0;

            $log->save();
        }
            $this->_logger->log(__METHOD__ .' sent Forwarded Messages');
            $processForwardedMessages->forwardMessages(10);



        //find slave messages


        $this->_logger->log(__METHOD__ .' find  messages, that have been received in a Salve mode');
        $criteria = new CDbCriteria();
        $criteria->condition = "is_processed = 0 and is_processing = 0";
        $criteria->condition .= ' and from_master = 1';
        $criteria->condition .= " and synchronization_mode = 'slave'";

        $criteria->order = "temp_log_id asc";
        $criteria->limit = 100;
        $logs = ListenerLogTemp::model()->findAll($criteria);

        $this->_logger->log(__METHOD__ .'['. getmypid() .'] PrepareCommand...  found '. count($logs) .' unprocessed messages');


        foreach ($logs as $log)
        {
                $log->is_processing = 1;
                $log->save();

                // run processing of message
                // $log - from ListenerLogTemp model
                $process_obj = new ProcessMessage($this->_logger, $log);
                $process_obj->run();
                // update message as "is processed"
                $log->is_processed = 1;
                $log->is_processing = 0;

                $log->save();

        }

        //find master messages
        $this->_logger->log(__METHOD__ .' find  messages, that have been received in a Master mode');
        $criteria = new CDbCriteria();
        $criteria->condition = "is_processed = 0 and is_processing = 0";

            $criteria->condition .= " and from_master = 0";
            $criteria->condition .= " and synchronization_mode = 'master'";

        $criteria->order = "temp_log_id asc";
        $criteria->limit = 100;

        /** @var array|ListenerLogTemp[] $logs */
        $logs = ListenerLogTemp::model()->findAll($criteria);
        $this->_logger->log(__METHOD__ .'['. getmypid() .'] PrepareCommand...  found '. count($logs) .' unprocessed messages');

        // Forwarding messages
        $processForwardedMessages       = new ProcessForwardedMessages($this->_logger);
        $processForwardedSlaveMessages  = new ProcessForwardedSlaveMessages($this->_logger);
        $this->_logger->log(__METHOD__ .' Create records for forwarding');
        $this->_logger->log(__METHOD__ .' Create records for slave forwarding');

        foreach ($logs as $log)
        {

            // Create records for forwarding
            if($this->synchronization->isMaster()) {
                $processForwardedMessages->saveNewForwardedMessage($log);
                $processForwardedSlaveMessages->saveNewForwardedSlaveMessage($log);


                $log->is_processing = 1;
                $log->save();

                // run processing of message
                // $log - from ListenerLogTemp model
                $process_obj = new ProcessMessage($this->_logger, $log);
                $process_obj->run();
                // update message as "is processed"
                $log->is_processed = 1;
                $log->is_processing = 0;

                $log->save();

            }
        }
    }




}
?>