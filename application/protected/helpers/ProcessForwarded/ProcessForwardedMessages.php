<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 05.05.2015
 * Time: 12:38
 */

class ProcessForwardedMessages extends BaseComponent {

    protected $_log;
    protected $_forwardingClients = array();
  //  protected $synchronization;


    public function __construct($logger)
    {
        parent::__construct($logger);

        $this->loadForwardingClients();
    }


    /**
     * Initializes array of clients for message forwarding
     */
    protected function loadForwardingClients()
    {
        $this->_logger->log(__CLASS__.'::'.__METHOD__);

        $this->_forwardingClients = array();

        $messageForwardingInfos = MessageForwardingInfoForTcpServer::model()->findAll();

        $this->_logger->log(__CLASS__.'::'.__METHOD__ .': Found '. count($messageForwardingInfos) .' forwarding clients.');

        foreach ($messageForwardingInfos as $messageForwardingInfo)
        {
            $this->_forwardingClients[$messageForwardingInfo->id] = new TcpClientConnector($this->_logger, 'tcp', $messageForwardingInfo->address, $messageForwardingInfo->port);
        }
    }

    public function saveNewForwardedMessage($log)
    {
        $this->_log = $log;

        $forwardedMessage = ForwardedMessage::model()->findByAttributes(array('message_id' => $this->_log->temp_log_id));
        if (is_null($forwardedMessage))
        {
            foreach ($this->_forwardingClients as $client_id => $client)
            {
                $matches = array();
                if (!preg_match('/((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)/', $this->_log->source_info, $matches)
                    || $client->getAddress() != $matches[0]
                ) {
                    $forwardedMessage = new ForwardedMessage();

                    $forwardedMessage->message_id = $this->_log->temp_log_id;
                    $forwardedMessage->client_id = $client_id;
                    $forwardedMessage->status = 'new';

                    if (!$forwardedMessage->save(false)) {
                        $this->_logger->log(__METHOD__ .' Forwarded message is not saved', array('log_id' => $this->_log->temp_log_id, 'client_id' => $client_id));
                    }
                }
                $this->_logger->log(__METHOD__ ,array('matches' => $matches));

            }
        }
    }

    public function forwardMessages($timeout = 5)
    {
        $this->_logger->log(__METHOD__);

        foreach ($this->_forwardingClients as $client_id => $client)
        {
            $criteria = new CDbCriteria();

            $criteria->with = array('messageTemp');

            $criteria->compare('client_id', $client_id);
            $criteria->compare('status', 'new');
            $criteria->order = "message_id asc";
            $criteria->limit = 100;

            $messages = ForwardedMessage::model()->findAll($criteria);

            $this->_logger->log(__METHOD__ .' Found messages', array('client_id' => $client_id, 'count' => count($messages)));
//            $this->_logger->log(__METHOD__.print_r($messages,1));
            if (count($messages) > 0)
            {
                if (!$client->connect($timeout))
                {
                    $this->_logger->log(__METHOD__ .' Can`t connect. Skip client', array('client_id' => $client_id));

                    continue;
                }
                $this->_logger->log(__METHOD__.print_r($messages,1));
                foreach ($messages as $message)
                {
                    if (!$client->sendMessage($message->messageTemp->message ."\r\n", $timeout))
                    {
                        $this->_logger->log(__METHOD__, array('errors' => $client->errors()));

                        break;
                    } else {
                        $message->status = 'sent';
                        if (!$message->save(false))
                        {
                            $this->_logger->log(__METHOD__ .' Message was not saved', array('message' => $message->mesage_id));
                        };
                    }
                }

                $client->disconnect();
            }
        }
    }

} 