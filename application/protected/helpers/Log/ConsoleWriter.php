<?php

/**
 * Writes log into console output.
 *
 * 
 */
class ConsoleWriter  implements ILogWriter
{
	/**
	 * Writes message into console output.
	 * @param string $message Message for output
	 */
	public function write($message) 
	{
		if (is_array($message))
		{
			foreach ($message as $msg)
			{
				//fwrite(STDOUT, $msg);
				echo $msg;				
			}
		}
		else
		{
			//fwrite(STDOUT, $message);
			echo $message;			
		}
	}
}

?>
