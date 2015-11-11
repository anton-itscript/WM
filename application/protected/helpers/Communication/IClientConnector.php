<?php

/**
 *
 * @author 
 */
interface IClientConnector
{
	/**
	 * Connects to a client.
	 */
	function connect($timeout = 5);
	
	/**
	 * Disconnects from client.
	 */
	function disconnect();
	
	/**
	 * Sends message to a client.
	 */
	function sendMessage($message);
	
	/**
	 * Returns errors.
	 */
	function errors();
}

?>
