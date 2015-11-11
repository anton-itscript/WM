<?php

/*
	Class with common helpers functions
*/

class It 
{
	/**
	 * Contains string that identifies current OS. 
	 * @var string
	 * @access protected
	 */
	protected static $_os = null;
	
	protected static function getOs()
	{
		if (is_null(self::$_os))
		{
			self::$_os = strtolower(php_uname('s'));
		}
	}
	
	public static function userId() 
	{
        return Yii::app()->user->getId();
    }

    public static function baseUrl($suffix='') 
	{
        return Yii::app()->getBaseUrl(true).$suffix;
    }

    public static function isGuest() 
	{
        return Yii::app()->user->isGuest;
    }

    public static function isAdmin() 
	{
        return Yii::app()->user->getState('is_admin');
    }

    public static function isSuperAdmin()
	{
        return Yii::app()->user->isSuperAdmin();
    }

    public static function memStatus($status_code)
    {
        if (!isset($_SESSION['status_code'])) 
		{
            $_SESSION['status_code'] = array();
        }
		
        $_SESSION['status_code'][] = $status_code;
    }
    
    public static function setMem($param, $value)
    {
        if (!isset($_SESSION['memory']))
		{
            $_SESSION['memory'] = array();
        }
		
        $_SESSION['memory'][$param] = $value;
    }
    
    public static function getMem($param)
    {
        if (!isset($_SESSION['memory'])) 
		{
            $_SESSION['memory'] = array();
        }
		
        return $_SESSION['memory'][$param];
    }

    public static function extractMem($param)
    {
        if (!isset($_SESSION['memory'])) {
            $_SESSION['memory'] = array();
        }
        $val = $_SESSION['memory'][$param];
        unset($_SESSION['memory'][$param]);
        return $val;
    }

    public static function createTextPreview($text, $limit = 35, $replacement = '')
    {
        $limit_ = $limit - strlen($replacement);
		
        if (strlen($text) <= $limit)
        {
			return $text;
		}

        return mb_substr($text, 0, $limit_) . $replacement;
    }
    

    public static function convertMetric($input_value, $input_metric, $output_metric)
	{
        if ($input_metric == $output_metric) 
		{
            return $input_value;
        }
        
        // -------------------- TEMPERATURE
        if ($input_metric == 'celsius') 
		{
            if ($output_metric == 'farenheit')
                return ($input_value * 1.8 + 32);
            
			if ($output_metric == 'kelvin')
                return ($input_value + 273.15);
        }
        
        if ($input_metric == 'farenheit') 
		{
            if ($output_metric == 'celsius')
                return ($input_value - 32)/1.8;
            
			if ($output_metric == 'kelvin')
                return ($input_value + 459.67)/1.8;
        }
        
        if ($input_metric == 'kelvin') 
		{
            if ($output_metric == 'celsius') 
                return ($input_value - 273.15);
        
			if ($output_metric == 'farenheit') 
                return ($input_value*1.8 - 459.67);
        }
        
        // -------------------- LENGTH/HIGHT/DEPTH/DISTANCE
        if ($input_metric == 'millimeter') {
            if ($output_metric == 'meter')
                return (0.001 * $input_value);
            if ($output_metric == 'centimeter')
                return (0.1 * $input_value);
            if ($output_metric == 'inch')
                return (39.3700787 * 0.001 * $input_value);
            if ($output_metric == 'feet')
                return (3.2808398950131235 * 0.001 * $input_value);        
            if ($output_metric == 'kilometer')
                return (0.001 * 0.001 * $input_value);
        }
        if ($input_metric == 'centimeter') 
		{
            if ($output_metric == 'meter')
                return (0.01 * $input_value);
            if ($output_metric == 'millimeter')
                return (10 * $input_value);
            if ($output_metric == 'inch')
                return (39.3700787 * 0.01 * $input_value);
            if ($output_metric == 'feet') 
                return (3.2808398950131235 * 0.01 * $input_value);
            if ($output_metric == 'kilometer')
                return (0.01 * 0.001 * $input_value);            
        }
		
        if ($input_metric == 'meter') 
		{
            //1 inch = 0.0254 meters;
            //1 meter = 39.3700787 inches
            if ($output_metric == 'centimeter')
                return (100 * $input_value);
			
            if ($output_metric == 'millimeter')
                return (1000 * $input_value);
            
			if ($output_metric == 'inch')
                return (39.3700787 * $input_value);
            
			if ($output_metric == 'feet')
                return (3.2808398950131235 * $input_value);
            
			if ($output_metric == 'kilometer')
                return (0.001 * $input_value);            
        }
		
        if ($input_metric == 'inch') 
		{
            if ($output_metric == 'centimeter')
                return (0.0254 * 100 * $input_value);
            if ($output_metric == 'millimeter')
                return (0.0254 * 1000 * $input_value);
            if ($output_metric == 'meter')
                return (0.0254 * $input_value);
            if ($output_metric == 'feet')
                return ($input_value/12);   
            if ($output_metric == 'kilometer')
                return (0.0254 * 0.001 * $input_value);
        }
        
        if ($input_metric == 'feet') {
            if ($output_metric == 'centimeter')
                return (30.48 * $input_value);
            if ($output_metric == 'millimeter')
                return (304.8 * $input_value);
            if ($output_metric == 'inch')
                return (12 * $input_value);       
            if ($output_metric == 'meter')
                return (0.3048 * $input_value);   
            if ($output_metric == 'kilometer')
                return (0.3048 * 0.001 * $input_value);            
        }
        
        if ($input_metric == 'kilometer') {
            if ($output_metric == 'meter')
                return (1000 * $input_value);            
            if ($output_metric == 'centimeter')
                return (1000 * 100 * $input_value);
            if ($output_metric == 'millimeter')
                return (1000 * 1000 * $input_value);
            if ($output_metric == 'inch')
                return (1000 * 39.3700787 * $input_value);
            if ($output_metric == 'feet')
                return (1000 * 3.2808398950131235 * $input_value);            
        }
        
        
        // -------------------- PRESSURE
        if ($input_metric == 'pascal') {
            if ($output_metric == 'hpascal')
                return ($input_value * 0.01);
            if ($output_metric == 'inHg')
                return ($input_value / 3386);               
        }
        if ($input_metric == 'hpascal') {
            if ($output_metric == 'pascal')
                return ($input_value * 100);
            if ($output_metric == 'inHg')
                return ($input_value * 100 / 3386);            
        }
        if ($input_metric == 'inHg') {
            //1 inch of mercury = 3386 pascals
            if ($output_metric == 'pascal')
                return ($input_value * 3386);
            if ($output_metric == 'hpascal')
                return ($input_value * 3386 * 0.01);            
        }
        
        // -------------------- SPEED
        // http://en.wikipedia.org/wiki/Knot_%28unit%29
        if ($input_metric == 'meter_per_second') {
            if ($output_metric == 'knot')
                return ($input_value * 1.943844);
            if ($output_metric == 'miles_per_hour')
                return ($input_value * 0.44704);
            if ($output_metric == 'kilometers_per_hour')
                return ($input_value * 0.277778);
        }
        if ($input_metric == 'knot') {
            if ($output_metric == 'meter_per_second')
                return ($input_value *  0.51444);
            if ($output_metric == 'miles_per_hour')
                return ($input_value * 0.868976);
            if ($output_metric == 'kilometers_per_hour')
                return ($input_value * 0.539957);
        }
        if ($input_metric == 'miles_per_hour') {
            if ($output_metric == 'meter_per_second')
                return ($input_value * 2.236936);
            if ($output_metric == 'knot')
                return ($input_value * 1.150779);
            if ($output_metric == 'kilometers_per_hour')
                return ($input_value * 0.621371);
        }
        if ($input_metric == 'kilometers_per_hour') {
            if ($output_metric == 'meter_per_second')
                return ($input_value * 3.6);
            if ($output_metric == 'knot')
                return ($input_value * 1.852);
            if ($output_metric == 'miles_per_hour') 
                return ($input_value * 1.609344);
        }
        
        if ($input_metric == 'joule_per_sq_meter') {
            if ($output_metric == 'kjoule_per_sq_meter') 
                return ($input_value / 1000);
        }
        if ($input_metric == 'kjoule_per_sq_meter') {
            if ($output_metric == 'joule_per_sq_meter') 
                return ($input_value * 1000);
        }
        
        
        return -999999;
    }

    public static function prepareCRC($string) 
	{
        $check_sum = crc32($string);

        $check_str =  strtoupper(dechex($check_sum));
        
		if (strlen($check_str) < 8) 
		{
            $check_str = str_pad($check_str, 8, "0", STR_PAD_LEFT);
        }
		
        return $check_str;
    }    
    
    public static function prepareStringCSV($res)
	{
        $return_string = "";
        $c = 0;
		
        foreach ($res as $key => $value) 
		{
            $val_array = array();
            $key_array = array(); 
            
			foreach($value AS $k => $v) 
			{
                $key_array[] = "\"{$k}\"";
                $val_array[] = "\"{$v}\"";
            }
            
			if($c == 0) 
			{
                $return_string .= implode(",", $key_array)."\n";
            }
			
            $return_string .= implode(",", $val_array)."\n";
            $c++;                
        }   
		
        return $return_string;
    }
	
	
    public static function downloadFile($string, $filename, $content_type = 'application/octet-stream')
	{
        $strlen = mb_strlen($string);
        
		header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: private");
        header("Content-type: {$content_type}");
        header("Content-Disposition: attachment; filename=$filename");
        header("Accept-Ranges: {$strlen}bytes");
        
		echo $string;
        
		exit;        
    }    
    
    public static function debug($msg, $debug_file_name = 'schedule')
	{
        $debug_msg = date('Y-m-d H:i:s').' '.$msg;
        
		print "\n".$debug_msg;
        
		$dir = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR;
        
		$debug_file = $dir.$debug_file_name.".log";
        
        if (file_exists($debug_file) && filesize($debug_file) > 5242880 && is_writable($debug_file)) 
		{
            $max = 1;
            $counter = $dir.$debug_file_name.'_cnt';
            
			if (file_exists($counter)) 
			{
                $max = intval(file_get_contents($counter)) + 1;
            } 
            
            $res = @rename($debug_file, __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR.$debug_file_name.$max.".log");
            
			if ($res === true) {
                file_put_contents($debug_file, '');

				$h = fopen($counter, "w+");
                
				if ($h) 
				{
                    fwrite($h, $max);
                    fclose($h);
                }  
            }
        }
        
		$h = fopen($debug_file, "a+");
        
		if ($h) 
		{
            fwrite($h, "\n".$debug_msg);
            fclose($h);
        }

        return true;        
    }
    
    public static function sendLetter($recipient, $subject, $body, $attachments = array())
	{
        $settings = Settings::model()->findByPk(1);
        
        ini_set('sendmail_from', $settings->mail__sender_address);

//        if ($settings->mail__use_fake_sendmail)
//		{
//            ini_set('sendmail_path', Yii::app()->params['sendmail_fake_params']['Sendmail']);
//        }
            
        $mailer = Yii::createComponent('application.extensions.mailer.EMailer');

        $mailer->Mailer = Yii::app()->params['sendmail_fake_params']['Mailer'];
		
        if ($settings->mail__use_fake_sendmail)
		{
            $mailer->Sendmail = Yii::app()->params['sendmail_fake_params']['Sendmail'];
        }
            
        $mailer->From     = $settings->mail__sender_address;
        $mailer->FromName = $settings->mail__sender_name;

        $mailer->AddAddress($recipient);
        $mailer->isHTML(true);
        $mailer->Subject  = $subject;
        $mailer->Body     = $body;    
        $mailer->CharSet  = 'UTF-8';

        if ($attachments)
		{
            foreach ($attachments as $key => $attachment) 
			{
                $mailer->AddAttachment($attachment['file_path'], $attachment['file_name']);
            }
        }

		$result = $mailer->Send();

		if (!$result)
		{
			$logger = LoggerFactory::getFileLogger('mail');
			
			$logger->log(__METHOD__ .'Mail send error', array('errorInfo' => $mailer->ErrorInfo));
					
			unset($logger);
		}
		
        return $result;               
    }
   
	public static function fullCopy($source, $target) 
	{
        if (is_dir($source )) 
		{
            if (!is_dir($target)) 
			{
                @mkdir($target);
            }
           
            $d = dir($source);
           
            while (FALSE !== ($entry = $d->read()))
			{
                if ($entry == '.' || $entry == '..')
				{
                    continue;
                }
               
                $Entry = $source . '/' . $entry;           
                
				if (is_dir($Entry)) 
				{
                    It::fullCopy($Entry, $target . '/' . $entry);
                    continue;
                }
				
                copy( $Entry, $target . '/' . $entry );
            }
           
            $d->close();
        } 
		else 
		{
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
    
    
    public static function t($section, $string) 
	{
        if (Yii::t($section, $string, array(), null, 'wm') != '') 
		{
            return Yii::t($section, $string, array(), null, 'wm');
        }
		
        return Yii::t($section, $string, array(), null, 'en');
    }
	

	public static function isLinux()
	{
		self::getOs();
		
		return (substr(self::$_os, 0, 5) === 'linux');
	}
	
	public static function isWindows()
	{
		self::getOs();
		
		return (substr(self::$_os, 0, 7) === 'windows');
	}
	
	public static function runAsynchCommand($command)
	{
		if (It::isLinux())
		{
			$output = null;
			$return = null;
			
			exec('nohup '. $command .' > /dev/null 2> /dev/null &', $output, $return);
		}
		else if (It::isWindows())
		{
			@pclose(@popen('start /B '. $command, 'r'));
		}
	}

//    public static function checkDirPath($path, $bPermission=true)
//    {
//        $path = str_replace(array("\\", "//"), "/", $path);
//        //remove file name
//        if(substr($path, -1) != "/")
//        {
//            $p = strrpos($path, "/");
//            $path = substr($path, 0, $p);
//        }
//
//        $path = rtrim($path, "/");
//
//        if(!file_exists($path))
//            return mkdir($path, 0755, true);
//        else
//            return is_dir($path);
//    }


    /**
     * @param $time string
     * @return bool|int
     */
    public static function timeToUnixTimestamp($time)
    {
        if( preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/",$time,$time_matches)){
            return mktime($time_matches[4],$time_matches[5],$time_matches[6],$time_matches[2],$time_matches[3],$time_matches[1]);
        }
        return false;
    }

    /**
     * @param $time int
     * @return string
     */
    public static function UnixTimestampToTime($time)
    {
        return date('Y-m-d H:i:s',$time);

    }
}

?>