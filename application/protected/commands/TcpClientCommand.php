<?php


class TcpClientCommand extends CConsoleCommand
{
    /** @var  ILogger */
    protected $_logger;
    /*
     * @var Synchronization
     *  */
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
        $this->_synchronization = new Synchronization();

        $this->_logger = LoggerFactory::getFileLogger('TcpClientCommand');
//        $this->_logger = LoggerFactory::getConsoleLogger();

        if (Synchronization::setTcpClientPid(getmypid())) {
            $this->_logger->log(__METHOD__.' '.'process can take pid = '.getmypid() );
        } else {
            $this->_logger->log(__METHOD__.' '.'process can not take pid.' );
        }
    }


    /**
     * Start process
     * @param array $arg
     * @return int|void
     * @this_server SyncSettings
     */
    public function run($arg)
    {
        while (1) {
            $this->_logger->log(__METHOD__.' '.'NEW CONNECTION' );
            $this->dataExchange();
            sleep(1);
        }

    }

    protected function dataExchange()
    {


        $TcpSocketClientConnector = new TcpSocketClientConnector($this->_logger, 'tcp', $this->_synchronization->remote_server_ip, $this->_synchronization->tcp_client_command_port);



        // send to server
        $TcpSocketClientConnector->onSentMessage = function($coming_message)
        {
           // $this->_logger->log(' $comming_message '.print_r($coming_message,1));
            $answer = 'FALSE';
            $exchangeArray = array(ExchangeODSS::getInstance());
            foreach ($exchangeArray as & $exchangeItem) {
                if($exchangeItem->init($coming_message)) {
                    $answer = $exchangeItem->clientMessage();
                } else {
                    $exchangeItem->getErrors();
                    $this->_logger->log(' ERRORS '.print_r($exchangeItem->getErrors(),1));
                }
            }
            return $answer;
        };

        $TcpSocketClientConnector->onCloseConnection = function($serverMessage) {

            $this->_logger->log(' $serverMessage '.print_r(@unserialize($serverMessage),1));

            $exchangeArray = array(ExchangeODSS::getInstance());
            foreach ($exchangeArray as & $exchangeItem) {

                if($exchangeItem->init($serverMessage)) {
                    if($exchangeItem->closeConnection()) {
                        return true;
                    }
                }
            }
            return false;
        };

        //comes messages
        $TcpSocketClientConnector->onReceiveDataMessage = function($message)
        {
            //$this->_logger->log(__METHOD__.' $message : '. print_r($message,1));
            $validMessage="FALSE";
            $exchangeArray = array(ExchangeODSS::getInstance());
            foreach ($exchangeArray as & $exchangeItem) {
                if($exchangeItem->init($message)) {
                    $validMessage = $exchangeItem->returnReceivedMessage();
                } else {
                    $exchangeItem->getErrors();
                    $this->_logger->log(' ERRORS '.print_r($exchangeItem->getErrors(),1));

                }
            }
            return $validMessage;
        };

        $exchangeArray = array(ExchangeODSS::getInstance());
        foreach ($exchangeArray as & $exchangeItem) {
            $TcpSocketClientConnector->connect($timeout = 1);
            $messageToServer = $exchangeItem->clientMessage();
            $this->_logger->log(' $messageToServer '.print_r($messageToServer,1));
            $TcpSocketClientConnector->sendMessage($messageToServer, $timeout = 1);

        }
    }


}

