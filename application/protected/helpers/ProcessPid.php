<?php

/*
 * class with functionality to work with Process Pid. Checks if some process with
 * 
 */
class ProcessPid
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
	
	
	/**
	 * Checks $process_name.lock if it contains PID number if current running process
	 * 
	 * @param string $process_name - type of process to check if it is running at the moment
	 * (this is the name of lock file
	 * this lock file contains ID of currently running process)
	 * 
	 * @return boolean 
	 */
    public static function isActive($process_name = 'process_message')
	{
        $pidfile = ProcessPid::getDir() . DIRECTORY_SEPARATOR . $process_name .'.lock';

		if (file_exists($pidfile))
		{
            if (ProcessPid::isLocked($process_name))
			{
                return true;
            }
            
            $running_pid = file_get_contents($pidfile);
            
			if ($running_pid)
			{
                return self::isActiveProcess($running_pid);
            }
        }
		
        return false;
    }
    
	/**
	 *
	 * Checks if process with PID = $pid is active at the moment
	 * 
	 * @param int $pid
	 * @return boolean 
	 */
    public static function isActiveProcess($pid)
    {
		$output = null;
		
        if (It::isLinux())
		{
			exec('ps h -o pid '. $pid, $output);
		
			return (is_array($output) && (count($output) > 0) && (trim($output[0]) == $pid));
		}
		else if (It::isWindows())
		{
			exec('tasklist /fi "PID eq '. $pid .'" /fo CSV /nh', $output);
			
			if (($output === false) || (count($output) === 0))
			{
				return false;
			}
			
			$lines = explode(',', $output[0]);
			
			return (is_array($lines) && (count($lines) > 1) && ($lines[1] == '"'. $pid .'"'));
		}
    }      
    
	/**
	 * Kills process with PID = $pid
	 *  
	 * @param int $pid
	 * @return boolean 
	 */
    public static function killProcess($pid)
	{
    	if (It::isLinux())
		{
			exec('kill -s KILL '. $pid);
		}
		else if (It::isWindows())
		{
			exec("taskkill /pid ". $pid ." /f");
		}
    }    
    
	/**
	 * Remembers current PID number into $process_name.lock
	 * @param string $process_name
	 * @return boolean 
	 */
    public static function remember($process_name = 'process_message')
	{
        $pidfile = ProcessPid::getDir() . DIRECTORY_SEPARATOR . $process_name . '.lock';        
        
		if (!ProcessPid::isLocked($process_name))
		{
			$h = @fopen($pidfile, "w+");
            
			if ($h !== false) 
			{
                fwrite($h, getmypid());
                fclose($h);
                
				@chmod($pidfile, 0777);
				
				return true;
            }
        }
		
        return false;
    }
    
	/**
	 * Checks if file locked
	 * 
	 * @param string $process_name file name
	 * @return boolean 
	 */
    public static function isLocked($process_name = 'process_message')
	{
        $pidfile = ProcessPid::getDir() . DIRECTORY_SEPARATOR . $process_name . '.lock';
        
        $running_pid = 0;

        if (file_exists($pidfile)) 
		{
            $size = @filesize($pidfile);
            $running_pid = @file_get_contents($pidfile);
            
			if (($size > 0) && ($running_pid == false))
			{
                return true;
            }
        }
		
        return false;
    }
       
    /**
	 * returns path to dir with lock-files
	 * 
	 * @return string 
	 */
    public static function getDir()
	{
        $dir = dirname(Yii::app()->runtimePath) . DIRECTORY_SEPARATOR .'locks';
        
		if (!is_dir($dir)) 
		{
            @mkdir($dir, 0777);
        }
        
        return $dir;
    }   
}