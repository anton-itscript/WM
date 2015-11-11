<?php

/**
 * Description of BaseBufrWeatherReport
 *
 * @author
 */
abstract class BaseBufrWeatherReport extends WeatherReport
{
	protected function prepare_binary_value($decimal, $length)
	{
        $binary = decbin($decimal);

		if (strlen($binary) > $length) {
            $binary = substr($binary, 0, $length);
        }

        return str_pad($binary, $length, '0', STR_PAD_LEFT);
    }
    
    public function prepareReportComplete()
	{
        $this->_logger->log(__METHOD__);
		
        $this->report_complete = '';
        
        if ($this->report_parts)
		{
            foreach ($this->report_parts as $value)
			{
                foreach ($value as $v2)
				{
                    $this->report_complete .= $v2;
                }
            }             
        }
    }
    
    protected static function _get_cloud_cover_code($v)
	{
		/*
         * Table 2700
         * 0 0 
         * 1 1 okta or less, but not zero 
         * 2 2 oktas 
         * 3 3 oktas 
         * 4 4 oktas 
         * 5 5 oktas
         * 6 6 oktas
         * 7 7 oktas
         * 8 8 oktas
         * 9 Sky obscured by fog and/or other meteorological phenomena
         * / Cloud cover is indiscernible for reasons other than fog or other meteorological phenomena, or observation is not made
        */

        if ($v == 0) return '0';
        if ($v <= 1) return '1';
        if ($v <= 2) return '2';
        if ($v <= 3) return '3';
        if ($v <= 4) return '4';
        if ($v <= 5) return '5';
        if ($v <= 6) return '6';
        if ($v <= 7) return '7';
        if ($v <= 8) return '8';
		
        return '9';
    }  
	
	protected function getSubCategory($currentUtcHour)
	{
		if (in_array($currentUtcHour, array(0, 6, 12, 18)))
		{
			return 2;
		}
		else if (in_array($currentUtcHour, array(3, 9, 15, 21)))
		{
			return 1;
		}
		else // all other hours
		{
			return 0;
		}
	}
	
	/**
	 * 20 - no clouds detected 
	 * 20 + i - clouds detected on i-layer
	 * @param int $group
	 * @param int $value
	 * @return int Value from code table 0 08 002
	 */
	protected function getVerticalSignificance($group, $value)
	{
		return $value 
				? 20 + $group 
				: 20;
	}
	
	protected function prepareSection0()
	{
		$this->_logger->log(__METHOD__);
		
		$s = 0;
        $i = 0;
		
        $this->report_parts[$s][$i] = '01000010';
        $this->explanations[$s][$i][] = 'Octet 1: <span>'.$this->report_parts[$s][$i].'</span> => <span>B</span> (8 bits)';   
        $i++;

        $this->report_parts[$s][$i] = '01010101';
        $this->explanations[$s][$i][] = 'Octet 2: <span>'.$this->report_parts[$s][$i].'</span> => <span>U</span> (8 bits)';   
        $i++;

        $this->report_parts[$s][$i] = '01000110';
        $this->explanations[$s][$i][] = 'Octet 3: <span>'.$this->report_parts[$s][$i].'</span> => <span>F</span> (8 bits)';   
        $i++;     

        $this->report_parts[$s][$i] = '01010010';
        $this->explanations[$s][$i][] = 'Octet 4: <span>'.$this->report_parts[$s][$i].'</span> => <span>R</span> (8 bits)';   
        $i++;        
        
        $total = $this->getTotalNumberOfOctets();
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($total, 24);
        $this->explanations[$s][$i][0] = 'Octets 5-7: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$total.'</span> (Total number of octets, 24 bits)';
        $i++;
		
        $this->report_parts[$s][$i] = $this->prepare_binary_value(4, 8);
        $this->explanations[$s][$i][] = 'Octet 8: <span>'.$this->report_parts[$s][$i].'</span> => <span>4</span> (Fixed, 8 bits)';
	}
	
	protected function prepareSection1()
	{
		$this->_logger->log(__METHOD__);
	}
	
	protected function prepareSection2()
	{
		$this->_logger->log(__METHOD__);
	}
	
	protected function prepareSection3()
	{
		$this->_logger->log(__METHOD__);
	}
	
	protected function prepareSection4()
	{
		$this->_logger->log(__METHOD__);
	}
	
	protected function prepareSection5()
	{
		$this->_logger->log(__METHOD__);
		
		$s = 5;
        $i = 0;
        $this->report_parts[$s][$i] =  '00110111001101110011011100110111';
        $this->explanations[$s][$i][] = 'Octets 1-4: <span>'. $this->report_parts[$s][$i] .'</span> => <span>7777</span> (fixed)';
	}
	
	protected abstract function getTotalNumberOfOctets();
	
	public function load($schedule_processed_id)
	{
        parent::load($schedule_processed_id);
        
		$this->_logger->log(__METHOD__);
		
        if ($this->schedule_process_info->schedule_processed_id)
		{
            $file_path = dirname(Yii::app()->request->scriptFile) .
							DIRECTORY_SEPARATOR ."files".
							DIRECTORY_SEPARATOR ."schedule_reports".
							DIRECTORY_SEPARATOR . $this->schedule_process_info->schedule_processed_id;
            
			if (file_exists($file_path))
			{
                $this->report_complete = file_get_contents($file_path);
            }
        }
        
        if ($this->schedule_process_info->serialized_report_errors)
		{
			$this->errors = unserialize($this->schedule_process_info->serialized_report_errors);
        }
    } 
	

	public function generate()
	{
		$this->_logger->log(__METHOD__);
        $current_user_timezone = date_default_timezone_get();
        $timezone_id = 'UTC';
		
        if ($timezone_id !=  $current_user_timezone)
		{
            TimezoneWork::set($timezone_id);
        } 
		
        $this->sensors = $this->prepareSensorsInfo($this->listener_log_info->log_id);
        $this->calculations = $this->_prepareCalculationsInfo($this->listener_log_info->log_id);
        
        if ($timezone_id != $current_user_timezone)
		{
            TimezoneWork::set($current_user_timezone);
        } 
        
        if ($this->errors)
		{
            $this->_logger->log(__METHOD__, array('errors' => $this->errors));
			
            return false;
        }
        $this->report_parts = array();
        $this->explanations = array();
        $this->prepareSection0();
        $this->prepareSection1();
        $this->prepareSection3();
        $this->prepareSection4();
        $this->prepareSection5();
	}
}

?>
