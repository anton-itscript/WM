<?php

class StubFactory
{
	private static $stubInfo = array();
	
	public static function stubFunction($name, $params = array('return' => null))
	{
		if (!is_array($params))
			throw new InvalidArgumentException('$params parameter should be an array');
		
		if (!array_key_exists($name, self::$stubInfo))
			
			self::$stubInfo[$name] = array('params' => $params, 'calls' => 0);
		else
			self::$stubInfo[$name]['params'] = $params;
	}
	
	public static function getStub($name)
	{
		if (array_key_exists($name, self::$stubInfo))
		{
			self::$stubInfo[$name]['calls']++;	
			
			$params = self::$stubInfo[$name]['params'];
			
			if (array_key_exists('return', $params))
				
				return $params['return'];
			elseif (array_key_exists('exception', $params))
			{
				$message = '';
				
				if (array_key_exists('message',  $params['exception']))
					$message = $params['exception']['message'];
				
				throw new $params['exception']['type']($message);
			}
			else
				throw new InvalidArgumentException('Stub *'. $name . '* has no return or exception param.');
		}
		else
			throw new InvalidArgumentException('Stub *'. $name . '* is not found');
	}
	
	public static function getStubCallCount($name)
	{
		if (array_key_exists($name, self::$stubInfo))
		{
			return self::$stubInfo[$name]['calls'];
		}
		else
			throw new InvalidArgumentException('Stub counter *'. $name . '* is not found');
	}
	
	public static function clear()
	{
		self::$stubInfo = array();
	}
};

?>
