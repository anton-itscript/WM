<?php

/**
 * Provides data from an associative array.
 *
 * @author
 */
class ArrayParamProvider implements IParamProvider
{
	/**
	 * Array with params
	 * 
	 * @access protected
	 * @var array 
	 */
	protected $_params;
	
	/**
	 * Ctor.
	 * 
	 * @param array $params 
	 */
	public function __construct($params = array())
	{
		$this->_params = $params;
	}
	
	/**
	 * Returns value from array or null if value is not found.
	 * 
	 * @param string $name
	 * @return mixed 
	 */
	public function getParam($name)
	{
		return array_key_exists($name, $this->_params) ? $this->_params[$name] : null;
	}
}

?>
