<?php

/**
 * Writes log into stream.
 * 
 */
class StreamWriter  implements ILogWriter
{
	/**
	 * Stream resource to write messages in.
	 * 
	 * @var resource 
	 */
	protected $_handle;
	
	/**
	 * Ctor.
	 * 
	 * @param resource $handle 
	 */
	public function __construct($handle)
	{
		$this->_handle = $handle;
	}
	
	/**
	 * Writes message into stream.
	 * @param string $message Message for logging
	 */
	public function write($message) 
	{
		if (is_array($message))
		{
			foreach ($message as $msg)
			{
				@fwrite($this->_handle, $msg);
			}
		}
		else
		{
			@fwrite($this->_handle, $message);
		}
	}
}

?>
