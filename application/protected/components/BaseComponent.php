<?php

/**
 * 
 */
abstract class BaseComponent
{
	/**
	 * @access protected
	 * @var ILogger
	 */
	protected $_logger;

	/**
	 * Ctor.
	 * 
	 * @param type $logger 
	 */
	public function __construct($logger)
	{
		$this->_logger = $logger;
	}

	/**
	 *
	 * @param string $method
	 * @param type $result
	 * @return type 
	 */
	protected function returnResult($method, $result)
	{
		$this->_logger->log($method, array('Return value' => $result));		
		
		return $result;
	}

}

?>