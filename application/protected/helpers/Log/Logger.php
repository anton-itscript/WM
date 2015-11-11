<?php

/**
 * Buffered logger.
 */
class Logger implements ILogger
{
	/**
	 * Log writer. Defines where log wil be dump on flushing.
	 * 
	 * @access protected
	 * @var ILogWriter 
	 */
    protected $_logWriter;
	
	/**
	 * Length of log buffer.
	 * 
	 * @access protected
	 * @var ILogWriter 
	 */
    protected $_bufferSize;
	
	/**
	 * Buffer for log messages.
	 * 
	 * @access protected
	 * @var ILogWriter 
	 */
    protected $_logs = array();
    
	/**
	 *  Counter of log messages. For flushing.
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_counter = 0;

	/**
	 * Ctor.
	 * 
	 * @param ILogWriter $logWriter Writer for dumping logs
	 * @param int $bufferSize
	 */
    public function __construct($logWriter, $bufferSize = 30)
    {
        $this->_logWriter = $logWriter;
		$this->_bufferSize = $bufferSize;
    }

	/**
	 * Destructor. 
	 */
    public function __destruct()
    {
        $this->flush();
    }

	/**
	 * Flushes buffer into log writer.
	 */
    protected function flush()
    {
        if ($this->_counter > 0)
        {
            $this->_logWriter->write($this->_logs);

			$this->_logs = array();
            $this->_counter = 0;
        }
    }
	
	/**
	 * Prepends datetime to message. Appends formatted params.
	 * 
	 * @param string|array $message
	 * @param array $params
	 * @return string Compiled log message. 
	 */
    protected function formatLogMessage($message, $params = array())
    {
        $result = '['.date('Y-m-d H:i:s').']: ' . $message;
		
		$isFirstParam = true;
		
		foreach ($params as $key => $param) 
		{
			if ($isFirstParam)
			{
				$result .= ': ';
				$isFirstParam = false;
			}
			else
			{
				$result .= '; ';
			}
			
			$result .= $this->formatParam($key, $param);
		}
		
		$result .= "\n";
		
		return 	$result;
    }
	
	/**
	 *
	 * @param string $key
	 * @param string $param 
	 */
	protected function formatParam($key, $param)
	{
		$result = $key .'= ';
		
		if (is_object($param))
		{
			$result .= '#Object('. get_class($param) .')';
		}
		else if (is_array($param))
		{
			$result .= " Array\n(\n";
			
			foreach ($param as $key1 => $value)
			{
				$result .= "\t". $this->formatParam($key1, $value) . "\n";
			}
			
			$result .= ")\n";		
		}
		else
		{
			$result .= print_r($param, true);
		}
		
		return $result;
	}
	
	/**
	 * Main funtion for logging messages.
	 * 
	 * @param string $message Message to log
	 * @param array Array of params 
	 */
    public function log($message, $params = array())
    {
		try
		{
			array_push($this->_logs, $this->formatLogMessage($message, $params));

			$this->_counter += 1;

			if ($this->_counter >= $this->_bufferSize)
			{
				$this->flush();
			}
		}
		catch (Exception $exc)
		{
			// suppress any errors to prevent execution stop
		}
    }
}

?>