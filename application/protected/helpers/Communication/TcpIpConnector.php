<?php

/**
 * Description of TcpIpConnector
 *
 * 
 */
class TcpIpConnector extends BaseConnector
{
	/**
	 * Target address for connection.
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_address;
	
	/**
	 * Port number.
	 * 
	 * @access protected
	 * @var int 
	 */
	protected $_port;
	
	/**
	 * Ctor.
	 * 
	 * @param ILogger $logger
	 * @param string $address
	 * @param int $port 
	 */
	public function __construct($logger, $address, $port)
	{
		parent::__construct($logger);
		
		$this->_address = $address;
		$this->_port = (int)$port;
	}
	
	public function readData(&$output) 
	{
		parent::readData($output);
		
		
	}
	
	public function check()
	{
		
	}
	
	/**
	 * Params:
	 * # address - 
	 * # port - COM port. E.g. COM1, COM2.
	 * @param array $params Array of params for connection
	 */
	public function setParams($params)
	{
		if (array_key_exists('address', $params))
		{
			$this->_address = $params['address'];
		}
		
		if (array_key_exists('port', $params))
		{
			$this->_port = $params['port'];
		}
	}
}

?>
