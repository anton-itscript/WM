<?php

/**
 * Writes log into /dev/null :).
 *
 * 
 */
class NullWriter  implements ILogWriter
{
	/**
	 * Does nothing...
	 * @param string $message Message for output
	 */
	public function write($message) 
	{

	}
}

?>
