<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 20.05.2015
 * Time: 17:40
 */

class SendMessageFromFileCommand  extends CConsoleCommand
{
    protected $_logger = null;

    protected $remote_server_ip;
    protected $remote_server_port;
    protected $file_name;
    protected $message;
    public function run($args)
    {

        ini_set('display_errors', 1);
        $this->_logger = LoggerFactory::getFileLogger('SendMessageFromFile');
        //$this->_logger = LoggerFactory::getConsoleLogger();

        $this->_logger->log(__METHOD__, array('args' => $args));

        if (count($args) < 3) {
            $this->_logger->log(__METHOD__ .' Too few params.');

            echo 'Expected 3 parameters: [IP ADDRESS: XXX.XXX.XXX.XXX], [PORT: XXXXX], [FILENAME: XXXXXX]';
            exit;
        }


        $this->remote_server_ip     = $args[0];
        $this->remote_server_port   = $args[1];
        $this->file_name     = __DIR__.'/../../www/files/temp/'.$args[2];
        $this->_logger->log(__METHOD__. ' ' . __DIR__ );
        $this->_logger->log(__METHOD__. 'filename '.$this->file_name );
        $this->readFile();
        $this->sendMessage();
    }

    protected function readFile()
    {
        $file = new TextFileWorker($this->file_name);
        $this->message = $file->getLine(0);
        $this->_logger->log(__METHOD__. 'message if: '.$this->message );
        $file->close();
    }

    protected function sendMessage()
    {
        if ($this->message!==false) {
            $TcpSocketClientConnector = new TcpClientConnector($this->_logger, 'tcp', $this->remote_server_ip, $this->remote_server_port);
            $TcpSocketClientConnector->connect($timeout = 5);
            if ($TcpSocketClientConnector->sendMessage($this->message, $timeout = 5)) {
                $file = new TextFileWorker($this->file_name);
                $file->removeLine(0);
                $file->close();
            }
            $TcpSocketClientConnector->disconnect();
            $this->_logger->log(__METHOD__. 'message sent!! ' );
        } else {
            $this->_logger->log(__METHOD__. 'message == false' );
        }
    }
}