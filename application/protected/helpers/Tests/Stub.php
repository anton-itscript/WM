<?php

/**
 * Description of Stub
 *
 */
class Stub
{
	protected $_attributes = array();
	protected $_functions = array();
	
	public function __construct($params = array())
	{
		if (array_key_exists('attributes', $params) && is_array($params['attributes']))
		{
			$this->_attributes = $params['attributes'];
		}
		
		if (array_key_exists('functions', $params) && is_array($params['functions']))
		{
			$this->_functions = $params['functions'];
		}
	}
	
	public function __call($method, $arguments)
	{
		if (array_key_exists($method, $this->_functions))
		{
			if (array_key_exists('return', $this->_functions[$method]))
			{
				return $this->_functions[$method]['return'];
			}
			else if (array_key_exists('exception', $this->_functions[$method]))
			{
				throw $this->_functions[$method]['exception'];
			}
			else
			{
				return null;
			}
		}
	}
	
	public function __isset($name)
	{
		return array_key_exists($name, $this->_attributes);
	}
	
	public function __unset($name)
	{
		unset($this->_attributes[$name]);
	}
	
	public function __get($name)
	{
		if (array_key_exists($name, $this->_attributes))
		{
			return $this->_attributes[$name];
		}
	}
	
	public function __set($name, $value)
	{
		if (array_key_exists($name, $this->_attributes))
		{
			$this->_attributes[$name] = $value;
		}
	}
}


?>
