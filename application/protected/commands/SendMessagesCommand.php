<?php
// run in 1 minut for to ask MasterTcpServer
class SendMessagesCommand extends CConsoleCommand
{
    /** @var  ILogger */
    protected $_logger;
    /**
     * Ini system param
     */
    public function init()
    {
        parent::init();

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $this->_logger = LoggerFactory::getFileLogger('SendMessagesCommand');
//        $this->_logger = LoggerFactory::getConsoleLogger();

    }


    /**
     * Start process
     * @param array $arg
     * @return int|void
     * @this_server Synchronization
     * @messagesToSend ForwardedMessage
     * @TcpSocketClientConnector TcpSocketClientConnector
     */
    public function run($arg)
    {
        $this_server = new Synchronization();

        $messagesToSend = ForwardedMessage::model()->getNewMessages();

        foreach ($messagesToSend as $message) {
            if (!is_object($message))
                continue;

            $TcpSocketClientConnector = new TcpSocketClientConnector($this->_logger, 'tcp', $this_server->remote_server_ip, $this_server->remote_server_port);
            $TcpSocketClientConnector->connect($timeout = 5);


            $message = serialize(array(
                'message'=>$message->message->message,

            ));

            $TcpSocketClientConnector->sendMessage($message, $timeout = 5);


//            $inputDataFromServer =  $TcpSocketClientConnector->readDataFromServer();
//            $serverData = unserialize($inputDataFromServer);

            $TcpSocketClientConnector->disconnect();
        }



//        $this->_logger->log(__METHOD__ .' inputDataFromServer: '. $inputDataFromServer);
//        $this->_logger->log(__METHOD__ .' serverData: '.print_r( $serverData,1));


    }
}

