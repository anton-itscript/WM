<?php

/**
 *	Base interface for low-level connections.
 * 
 */
interface IConnector 
{
	function setParams($params);
	
	function readData(&$output);
	function check();
	
	function errors();
}

?>
