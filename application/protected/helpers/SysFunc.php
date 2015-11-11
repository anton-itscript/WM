<?php

class SysFunc
{
	/**
	 * @access protected
	 * @var ILogger
	 */
	protected static $_logger = null;
	
	/**
	 * Initializes logger at first call.
	 * 
	 * @return ILogger 
	 */
	protected static function getLogger()
	{
		if (is_null(self::$_logger))
		{
			self::$_logger = LoggerFactory::getFileLogger('prepare_message');			
		}
		
		return self::$_logger;
	}
	
    public static function isActiveProcess($pid)
    {
		$output = null;
		$result = false;
		
        if (It::isLinux())
		{
			exec('ps h -o pid '. $pid, $output);
		
			$result = (is_array($output) && (count($output) > 0) && (trim($output[0]) == $pid));
		}
		else if (It::isWindows())
		{
			exec('tasklist /fi "PID eq '. $pid .'" /fo CSV /nh', $output);
			
			if (($output !== false) && (count($output) > 0))
			{
				$lines = explode(',', $output[0]);

				$result = (is_array($lines) && (count($lines) > 1) && ($lines[1] == '"'. $pid .'"'));
			}
		}
		
		self::getLogger()->log(__METHOD__, array('PID' => $pid, 'Status' => ($result ? 'active' : 'inactive')));
		
		return $result;
    }

    public function killProcess($pid)
    {
		self::getLogger()->log(__METHOD__, array('PID' => $pid));
		
        if (It::isLinux()) 
		{
            exec('kill -s KILL '. $pid);
        }
		else if (It::isWindows())
		{
			exec("taskkill /pid ".$pid." /f");
		}
    }

	/**
	 * Returns array of serial ports. Can return fake list for testing purposes.
	 * 
	 * @return array Array of serial ports.
	 */
    public static function getAvailableComPortsList()
	{
        if (Yii::app()->params['show_fake_com_ports']) {
            return array('COM1' => 'COM1', 'COM2' => 'COM2');
        }
	
		$output = null;
		$result = array();
		
		if (It::isLinux()) {

			exec('setserial -g /dev/ttyS*', $output);

			if (is_array($output)) {
				foreach ($output as $line) {
					$matches = array();
					if (preg_match('/\/dev\/ttyS([0-9])/', $line, $matches)) {
						$serialPort = 'COM' . ($matches[1] + 1);
						$result[$serialPort] = "$serialPort ($matches[0])";
					}
				}
			}
			
			$result = array_unique($result);

		} else if (it::isWindows()) {

			exec('wmic path Win32_SerialPort get Description, DeviceID /format:csv', $output);
			
			if (is_array($output) && (count($output) > 1)) {

				$output = array_slice($output, 2);
				foreach ($output as $line) {
					$values = explode(',', $line);
				    $result[$values[2]] = $values[1];
				}
			}
		}

        return $result;
    }


    public static function fullCopy($source, $target)
    {
        if (is_dir($source )) {
            if (!is_dir($target)) {
                @mkdir($target);
            }
           
            $d = dir($source);
           
            while (FALSE !== ($entry = $d->read())){
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
               
                $Entry = $source . '/' . $entry;           
                if (is_dir($Entry)) {
                    SysFunc::fullCopy($Entry, $target . '/' . $entry);
                    continue;
                }
                copy( $Entry, $target . '/' . $entry );
            }
           
            $d->close();
        } else {
            copy($source, $target);
        }        
    }    
    
    public static function string2binary($str)
	{
        $res = '';
        $chuncks = str_split($str, 8);

        foreach ($chuncks as $key)
		{
            $res.= chr(bindec($key ? $key : 0));
        }
		
        return $res;
    }
}