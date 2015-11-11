<?php

/**
 * Base interface for providing parameters of any kind
 */
interface IParamProvider
{
	/**
	 * Returns param by given name 
	 */
	function getParam($name);
}

?>
