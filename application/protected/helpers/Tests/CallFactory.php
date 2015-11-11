<?php

class CallFactory
{
	public static function staticCall($className, $functionName, $params = array())
	{
		return self::doCall('static', $className, $functionName, $params);
	}
	
	public static function call($instance, $functionName, $params = array())
	{
		return self::doCall('instance', $instance, $functionName, $params);
	}
	
	private static function doCall($type, $type_param, $functionName, $params = array())
	{
		if (Yii::app()->params['isUnitTests'] === true)
		{
			switch ($type)
			{
				case 'static':
					
					return StubFactory::getStub($type_param . '::' . $functionName);
				case 'instance':
					
					return StubFactory::getStub(get_class($type_param) .'->'. $functionName);	
			
			default:
				throw new InvalidArgumentException('Unknown type of call: '. $type);
			}						
		}
		
		if (!$params)
			
			$params= array();
		else if (!is_array($params))
			
			$params = array($params);
		
		switch ($type)
		{
			case 'static':

				return forward_static_call_array(array($type_param, $functionName), $params);

			case 'instance':
				
				return call_user_func_array(array($type_param, $functionName), $params);
			
			default:
				throw new InvalidArgumentException('Unknown type of call: '. $type);
		}		
	}
};
?>
