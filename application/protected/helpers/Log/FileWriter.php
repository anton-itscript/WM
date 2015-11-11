<?php

/**
 * Class for output logs into file. 
 */
class FileWriter implements ILogWriter
{
	/**
	 * Root dir for logs.
	 * 
	 * @access protected
	 * @var string Directory path. 
	 */
	protected $_logDir;
	
	/**
	 * Resouce handle to log file.
	 * 
	 * @access protected
	 * @var resource    
	 */
	protected $_handle;
	
	/**
	 * Deletes log file if it's age is more than $expirePeriod (in seconds).
	 * 
	 * @param string $fileName
	 * @param int $expirePeriod Expire period in seconds.
	 */
	protected function deleteExpiredLogFile($fileName, $expirePeriod)
	{
		if (file_exists($fileName))
		{
			$filemtime = @filemtime($fileName);
			
			if ($filemtime !== false)
			{
				if (time() - $filemtime >= $expirePeriod)
				{
					@unlink($fileName);
				}
			}
		}
	}

	/**
	 * Ctor.
	 * 
	 * @param string $logDir Path to log directory
	 */
	public function __construct($logDir)
    {
        $this->_logDir = $logDir;
		
		$this->_handle = -1;        
    }
	
	/**
	 * Destructor.
	 */
	public function __destruct() 
    {
        if ($this->_handle != -1)
		{
			fclose($this->_handle);
			
			$this->_handle = -1;
		}
    }
	
	/**
	 * Writes log messages in file.
	 * 
	 * @param string|array $message Log message (array of messages) for output
	 */
	public function write($message)
	{
		$logfile = $this->_logDir . DIRECTORY_SEPARATOR .'log_'. date('l') .'.log';
		
		$this->deleteExpiredLogFile($logfile, 60 * 60 * 24 * 6); // 6 days - to delete 1 week old log
		
		$this->_handle = @fopen($logfile, "a+"); 
		
		if ($this->_handle !== false)
		{
			try
			{
				if (is_array($message))
				{
					foreach ($message as $value) 
					{
						@fwrite($this->_handle, $value);
					}
				}
				else
				{
					@fwrite($this->_handle, $message);
				}
			}
			catch (Exception $ex)
			{

			}
			
			@fclose($this->_handle);
		
			@chmod($logfile, 0777);
		}
		
		$this->_handle = -1;		
	}
}
?>
