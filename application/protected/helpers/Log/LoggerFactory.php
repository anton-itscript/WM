<?php

/**
 * Factory for creating loggers 
 */
class LoggerFactory
{
	/**
	 * Internal array of created loggers
	 * @var array 
	 */
	protected static $_loggers = array();
	
	/**
	 * @return string Path to default log dir
	 */
	protected static function getDefaultLogDir()
	{
		return dirname(__FILE__) .
				DIRECTORY_SEPARATOR .'..'.
				DIRECTORY_SEPARATOR .'..'.
				DIRECTORY_SEPARATOR .'..'.
				DIRECTORY_SEPARATOR .'log';
	}
	
	/**
	 * 
	 * @param string $name
	 * @return ILogger 
	 */
    public static function getFileLogger($name)
    {
		if (!array_key_exists('default_'. $name, self::$_loggers))
		{
			if (isset(Yii::app()->params['isUnitTests']) && (Yii::app()->params['isUnitTests'] === true))
			{
				self::$_loggers['default_'. $name] = self::getTestLogger();
			}
			else
			{
				$dir = self::getDefaultLogDir() . DIRECTORY_SEPARATOR . $name;

				if (!file_exists($dir))
				{
					@mkdir($dir, 0777, true);
					@chmod($dir . DIRECTORY_SEPARATOR, 0777);
				}

				$writer = new FileWriter($dir);

				self::$_loggers['default_'. $name] = new Logger($writer);
			}
		}

		return self::$_loggers['default_'. $name];
    }
    
   /**
    * 
	* @return ILogger 
	*/
    public static function getConsoleLogger()
    {
		if (!array_key_exists('console', self::$_loggers))
		{
			$writer = new ConsoleWriter();

			self::$_loggers['console'] = new Logger($writer, 1);
		}

		return self::$_loggers['console'];
    }
	
	/**
	 *
	 * @param resource $handle
	 * @return ILogger 
	 */
    public static function getStreamLogger($handle)
    {
		$handleStr = print_r($handle, true);
		
		if (!array_key_exists('stream_'. $handleStr, self::$_loggers))
		{
			if (isset(Yii::app()->params['isUnitTests']) && (Yii::app()->params['isUnitTests'] === true))
			{
				self::$_loggers['stream'. $handleStr] = self::getTestLogger();
			}
			else
			{
				$writer = new StreamWriter($handle);

				self::$_loggers['stream'. $handleStr] = new Logger($writer, 1);
			}
		}

		return self::$_loggers['stream'. $handleStr];
    }
	
	public static function getTestLogger()
	{
		if (!array_key_exists('test', self::$_loggers))
		{
			$class = Yii::app()->params['testLoggerWriterClass'];
			
			self::$_loggers['test'] = new Logger(new $class(), 1);
		}

		return self::$_loggers['test'];
	}
}
?>
