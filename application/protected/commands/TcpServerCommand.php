<?php

class TcpServerCommand extends CConsoleCommand
{
    /** @var  ILogger */
    protected $_logger;

    /*
     * @var Synchronization
     * */
    protected $_synchronization;
    /**
     * Ini system param
     */
    public function init()
    {
        parent::init();

        ini_set('memory_limit', '-1');
        set_time_limit(0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        $this->_logger = LoggerFactory::getFileLogger('TcpServerCommand');
//        $this->_logger = LoggerFactory::getConsoleLogger();

        $this->_synchronization = new Synchronization();

        if (Synchronization::setTcpServerPid(getmypid())) {
            $this->_logger->log(__METHOD__.' '.'process can take pid = '.getmypid() );
        } else {
            $this->_logger->log(__METHOD__.' '.'process can not take pid.' );
        }
        $this->_logger->log(__METHOD__.' '.'process can not take pid.' );
    }

    public function run($args)
    {
        while (1) {
            $this->_logger->log(__METHOD__.' '.'NEW CIRCULE' );
            $this->dataExchange();
            time_nanosleep(0,20000000);
        }
    }

    public function dataExchange()
    {


            $TcpSocketServerConnector = new TcpSocketServerConnector($this->_logger, 'tcp', $this->_synchronization->server_ip, $this->_synchronization->tcp_server_command_port);

            $TcpSocketServerConnector->loadData($null);

            // send answer to client
            $TcpSocketServerConnector->onSentMessage = function($coming_message)
            {


                $this->_logger->log(' $comming_message '.print_r($coming_message,1));
                $answer = 'FALSE';
                $exchangeArray = array(ExchangeODSS::getInstance());
                foreach ($exchangeArray as & $exchangeItem) {
//
                    if($exchangeItem->init($coming_message)) {
                       $answer = $exchangeItem->serverMessage();

                    } else {
                        $exchangeItem->getErrors();
                        $this->_logger->log(' ERRORS '.print_r($exchangeItem->getErrors(),1));

                    }


                }

                $this->_logger->log(' $answer '.print_r($answer,1));

                return $answer;
            };

            $TcpSocketServerConnector->onCloseConnection = function($clientMessage) {
                $this->_logger->log(' $clientMessage '.print_r(@unserialize($clientMessage),1));
                $exchangeArray = array(ExchangeODSS::getInstance());
                foreach ($exchangeArray as & $exchangeItem) {
//
                    if($exchangeItem->init($clientMessage)) {
                        if($exchangeItem->closeConnection()) {
                           return true;
                        }
                    }
                }
                return false;
            };
            //comes messages
            $TcpSocketServerConnector->onReceiveDataMessage = function($message)
            {
                $this->_logger->log(' $message : '. print_r($message,1));
                $validMessage="FALSE";
                $exchangeArray = array(ExchangeODSS::getInstance());
                foreach ($exchangeArray as & $exchangeItem) {

                    if($exchangeItem->init($message)) {
                        $validMessage = $exchangeItem->returnReceivedMessage();
                    } else {
                        $exchangeItem->getErrors();
                        $this->_logger->log(' onReceiveDataMessage ERRORS '.print_r($exchangeItem->getErrors(),1));

                    }
                }

                return $validMessage;
            };

           $TcpSocketServerConnector->readData($message);
    }
}
