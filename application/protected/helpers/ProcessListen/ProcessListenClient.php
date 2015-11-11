<?php

/**
 * Listening process. Has functions to recognize hardware, read data from it 
 * and put new messages into database.
 * 
 * @author 
 */

class ProcessListenClient extends BaseComponent
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
    


    public function init()
	{
        if (preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\:([0-9]{1,5})$/', $this->source, $matches)) {

            $this->_connector = new TcpIpConnector($this->_logger, $matches[1], $matches[2] );
            $this->_logger->log(__CLASS__.' '.__METHOD__);
            $this->settings = Settings::model()->find();
            $this->synchronization = new Synchronization();

            return true;

        } else {
            return false;
        }
    }
    

    public function run()
	{
        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'going to start TCP-client connection, source= '. $this->source);

        $this->listenESPTorrentTemp();

    }

    // read messages from ESP, using sockets
    // this process is auto-restarted. If connection was broken - it makes 3 attempts to re-connect. And repeats them after 2 minutes
    // it can be stopped only by admin
    protected function listenESPTorrentTemp($attempt = 0)
    {

        $this->_logger->log(__CLASS__.' '.__METHOD__, array('attempt' => $attempt));

        $timeout = 10;

        if ($attempt == 0)
        {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'trying to connect with '.$this->source);
        }
        else
        {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Trying to reconnect with '.$this->source.'; attempt #'.$attempt.'/3');
        }

        $source_parts = explode(':', $this->source);

        // creates socket connection with IP:port
        $fp = @fsockopen($source_parts[0], $source_parts[1], $errno, $errstr, $timeout);

        if ($fp !== false)
        {
            $attempt = 0;
            ListenerProcess::addComment($this->listener->listener_id, 'connected', 'successfully');

            $message = '';

            while (!feof($fp))
            {
                $res = fwrite($fp, ' ', 2); //print "\n try write: ".$res;
                $line = fread($fp, 8192);
                $line = trim($line);

                $occurances_dl = strpos($line, '$');

                if ($line != '')
                {
                    $message .= $line;

                    if ($occurances_dl !== false)
                    {

                            $res = ListenerLogTemp::addNew($message, $this->listener->listener_id, $this->settings->overwrite_data_on_listening, 'datalogger');

                        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'got msg #' . $res);
                        $message = '';

                    }
                }
            }

            ListenerProcess::addComment($this->listener->listener_id, 'stopped', 'can not receive data anymore - ESP is unreachable');

            fclose($fp);
        }
        else
        {
            ListenerProcess::addComment($this->listener->listener_id, 'cannot_connect', '['.$errno.'] '.$errstr);
        }

        if ($attempt < 3)
        {
            $attempt++;
            sleep(3);
        }
        else
        {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Three attempts to reconnect failed. Waiting for 2 minutes before trying to reconnect.');
            sleep(120);

            $attempt = 0;
        }

        return $this->listenESPTorrentTemp($attempt);
    }

    // read messages from ESP, using sockets
    // this process is auto-restarted. If connection was broken - it makes 3 attempts to re-connect. And repeats them after 2 minutes
    // it can be stopped only by admin
    protected function listenESPTorrent($attempt = 0)
    {

        $this->_logger->log(__CLASS__.' '.__METHOD__, array('attempt' => $attempt));

        $timeout = 10;

        if ($attempt == 0)
        {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'trying to connect with '.$this->source);
        }
        else
        {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Trying to reconnect with '.$this->source.'; attempt #'.$attempt.'/3');
        }

        $source_parts = explode(':', $this->source);

        // creates socket connection with IP:port
        $fp = @fsockopen($source_parts[0], $source_parts[1], $errno, $errstr, $timeout);

        if ($fp !== false)
        {
            $attempt = 0;
            ListenerProcess::addComment($this->listener->listener_id, 'connected', 'successfully');

            $message = '';

            while (!feof($fp))
            {
                $res = fwrite($fp, ' ', 2); //print "\n try write: ".$res;
                $line = fread($fp, 8192);
                $line = trim($line);

                $occurances_dl = strpos($line, '$');

                if ($line != '')
                {
                    $message .= $line;

                    if ($occurances_dl !== false)
                    {
                        if ($this->synchronization->isMaster()) {

                            $res = ListenerLog::addNew($message, $this->listener->listener_id, $this->settings->overwrite_data_on_listening, 'datalogger');

                        } else {
                            // slave
                            //$res = ListenerLog::addNew($message, $this->listener->listener_id, $this->settings->overwrite_data_on_listening, 'datalogger');

                        }
                        ListenerProcess::addComment($this->listener->listener_id, 'comment', 'got msg #' . $res);
                        $message = '';

                    }
                }
            }

            ListenerProcess::addComment($this->listener->listener_id, 'stopped', 'can not receive data anymore - ESP is unreachable');

            fclose($fp);
        }
        else
        {
            ListenerProcess::addComment($this->listener->listener_id, 'cannot_connect', '['.$errno.'] '.$errstr);
        }

        if ($attempt < 3)
        {
            $attempt++;
            sleep(3);
        }
        else
        {
            ListenerProcess::addComment($this->listener->listener_id, 'comment', 'Three attempts to reconnect failed. Waiting for 2 minutes before trying to reconnect.');
            sleep(120);

            $attempt = 0;
        }

        return $this->listenESPTorrent($attempt);
    }
}
?>