<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 05.05.2015
 * Time: 12:40
 */

class ProcessForwardedSlaveMessages extends BaseComponent {

    protected $_log;
    protected $_slaveClient = array();
    protected $synchronization;
    protected $slave_ip;
    protected $slave_port;

    public function __construct($logger)
    {
        parent::__construct($logger);
        $this->synchronization  = new Synchronization();
        $this->slave_ip         = $this->synchronization->getRemoteIpToSentMessages();
        $this->slave_port       = $this->synchronization->getRemotePortToSentMessages();

        $this->loadSlaveClient();
    }


    protected function loadSlaveClient()
    {
        $this->_logger->log(__CLASS__.'::'.__METHOD__);
        $this->_slaveClient['slave'] = new TcpClientConnector($this->_logger, 'tcp', $this->slave_ip ,  $this->slave_port );
    }

    public function saveNewForwardedSlaveMessage($log)
    {
        $this->_log = $log;

        // Create records for forwarding
        $forwardedSlaveMessage = ForwardedSlaveMessage::model()->findByAttributes(array('forwarded_slave_message_id' => $this->_log->temp_log_id));
        if (is_null($forwardedSlaveMessage))
        {
            foreach ($this->_slaveClient as $client_id => $client)
            {
                $matches = array();
                if (!preg_match('/((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)/', $this->_log->source_info, $matches)
                    || $client->getAddress() != $matches[0]
                ) {
                    $forwardedSlaveMessage = new ForwardedSlaveMessage();

                    $forwardedSlaveMessage->forwarded_slave_message_id = $this->_log->temp_log_id;
                    //$forwardedSlaveMessage->client_id = $client_id;
                    $forwardedSlaveMessage->forwarded_slave_status = 'new';

                    if (!$forwardedSlaveMessage->save(false)) {
                        $this->_logger->log(__METHOD__ .' Forwarded slave message is not saved', array('log_id' => $this->_log->temp_log_id, 'client_id' => $client_id));
                    }
                }
                $this->_logger->log(__METHOD__ ,array('matches' => $matches));

            }
        }
    }

    // for slave forwarded messages
    public function forwardMessagesToSlave($timeout = 5)
    {
        $this->_logger->log(__CLASS__.'::'.__METHOD__);

        // now its works for one client
        foreach ($this->_slaveClient as $client_id => $client)
        {
            $criteria = new CDbCriteria();

            $criteria->with = array('slave_message');

            //$criteria->compare('client_id', $client_id);
            $criteria->compare('forwarded_slave_status', 'new');
            $criteria->order = "forwarded_slave_message_id asc";
            $criteria->limit = 100;

            $messages = ForwardedSlaveMessage::model()->findAll($criteria);

            $this->_logger->log(__METHOD__ .' Found messages', array('client_id' => $client_id, 'count' => count($messages)));

            if (count($messages) > 0)
            {
                if (!$client->connect($timeout))
                {
                    $this->_logger->log(__METHOD__ .' Can`t connect. Skip client', array('client_id' => $client_id));

                    continue;
                }

                foreach ($messages as $message)
                {
                    if (!$client->sendMessage($message->slave_message->message ."\r\n", $timeout))
                    {
                        $this->_logger->log(__METHOD__, array('errors' => $client->errors()));

                        break;
                    } else {
                        $message->forwarded_slave_status = 'sent';
                        if (!$message->save(false))
                        {
                            $this->_logger->log(__METHOD__ .' Message was not saved', array('slave_message' => $message->forwarded_slave_message_id));
                        };
                    }
                }

                $client->disconnect();
            }
        }
    }

} 