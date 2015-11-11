<?php

/**
 * Description of LesothoBufrWeatherReport
 *
 * @author
 */
class LesothoBufrWeatherReport extends Template307081BufrWeatherReport
{
	public function prepareSection4()
	{
		$this->_logger->log(__METHOD__);
		
        $s = 4;
        $i = 0;         
        
        $measuring_timestamp = strtotime($this->listener_log_info->measuring_timestamp);
        
//        $measuring_year      = date('Y', $measuring_timestamp);
//        $measuring_month     = date('m', $measuring_timestamp);
//        $measuring_day       = date('d', $measuring_timestamp);
//        $measuring_hour      = date('H', $measuring_timestamp);
//        $measuring_minute    = date('i', $measuring_timestamp);
//        $measuring_second    = date('s', $measuring_timestamp);
        
        $script_runtime           = strtotime($this->schedule_process_info->check_period_end);
        $script_runtime_year      = date('Y', $script_runtime);
        $script_runtime_month     = date('m', $script_runtime);
        $script_runtime_day       = date('d', $script_runtime);
        $script_runtime_hour      = date('H', $script_runtime);
        $script_runtime_minute    = date('i', $script_runtime);
        $script_runtime_second    = date('s', $script_runtime);  
        
        $sensors_3hr_ago     = array();
        $sensors_24hr_ago    = array();
        
        $x_hr_ago_1 = date('Y-m-d H:i:s', mktime($script_runtime_hour-3, $script_runtime_minute, $script_runtime_second, $script_runtime_month, $script_runtime_day, $script_runtime_year));
        $x_hr_ago_2 = date('Y-m-d H:i:s', mktime($script_runtime_hour-4, $script_runtime_minute, $script_runtime_second, $script_runtime_month, $script_runtime_day, $script_runtime_year));
        
        $log_3_hr_ago = ListenerLog::getMessageWithTime($this->station_info->station_id, $x_hr_ago_1, $x_hr_ago_2);
        
		if (!is_null($log_3_hr_ago))
		{
            $sensors_3hr_ago = $this->prepareSensorsInfo($log_3_hr_ago->log_id);
        }
        
        $x_hr_ago_1 = date('Y-m-d H:i:s', mktime($script_runtime_hour, $script_runtime_minute, $script_runtime_second, $script_runtime_month, $script_runtime_day - 1, $script_runtime_year));
        $x_hr_ago_2 = date('Y-m-d H:i:s', mktime($script_runtime_hour-1, $script_runtime_minute, $script_runtime_second, $script_runtime_month, $script_runtime_day - 1, $script_runtime_year));
        
        $log_24_hr_ago = ListenerLog::getMessageWithTime($this->station_info->station_id, $x_hr_ago_1, $x_hr_ago_2);
        
		if (!is_null($log_24_hr_ago))
		{
			$sensors_24hr_ago = $this->prepareSensorsInfo($log_24_hr_ago->log_id);
        }     
        
        /// ??? RECALCULATE !!!!
        $this->report_parts[$s][$i] = $this->prepare_binary_value(0, 24);
        $this->explanations[$s][$i][] = 'Octets 1-3: <span>'.$this->report_parts[$s][$i].'</span> => <span></span> (length of section4 in octets)';
        $i++;
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value(0, 8); 
        $this->explanations[$s][$i][] = 'Octets 4: <span>'.$this->report_parts[$s][$i].'</span> reserved';
        $i++;
        
        //3 01 090: Fixed surface station identification, time, horizontal and vertical coordinates
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($this->station_info->wmo_block_number, 7); 
        $this->explanations[$s][$i][] = '301090 -> 301004 -> 001001: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$this->station_info->wmo_block_number.'</span> (WMO Block #, 7 bits)';   
        $i++;   
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($this->station_info->station_number, 10);
        $this->explanations[$s][$i][] = '301090 -> 301004 -> 001002: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$this->station_info->station_number.'</span> (station number, 10 bits)';   
        $i++;         
        
        
        $station_display_name = substr($this->station_info->display_name, 0, 20);
        $tmp_str = '';
        $tmp_str_2 = '';
		
        for ($j = 0; $j < strlen($station_display_name); $j++)
		{
			$tmp = $this->prepare_binary_value(ord($station_display_name[$j]), 8);
			$tmp_str .= $tmp;
            $tmp_str_2 .= ' '. $tmp;
        }

        $this->report_parts[$s][$i] = str_pad($tmp_str, 160, '0', STR_PAD_RIGHT);
        $this->explanations[$s][$i][] = '301090 -> 301004 -> 001015: <span>'.str_pad($tmp_str_2, 160 + strlen($station_display_name), '0', STR_PAD_RIGHT).'</span> => <span>'.$station_display_name.'</span> (station code, 160 bits. Each letter incoded into 7bits binary with and padded with left 0 to 8bits. Then joined string is right-padded with 0 to fit 160bits length)';   
        $i++;   
        
        $this->report_parts[$s][$i] = '00';
        $this->explanations[$s][$i][] = '301090 -> 301004 -> 002001: <span>'.$this->report_parts[$s][$i].'</span> => <span>0</span> (Type of station, 2 bits)';   
        $i++;   
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($script_runtime_year, 12);
        $this->explanations[$s][$i][] = '301090 -> 301011 -> 00 4001: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$script_runtime_year.'</span> (Year, 12 bits)';   
        $i++;   
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($script_runtime_month, 4);
        $this->explanations[$s][$i][] = '301090 -> 301011 -> 004002: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$script_runtime_month.'</span> (Month, 4 bits)';   
        $i++;        
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($script_runtime_day, 6);
        $this->explanations[$s][$i][] = '301090 -> 301011 -> 004003: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$script_runtime_day.'</span> (Day, 6 bits)';   
        $i++;         
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($script_runtime_hour, 5);
        $this->explanations[$s][$i][] = '301090 -> 301012 -> 004004: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$script_runtime_hour.'</span> (Hour, 5 bits)';   
        $i++;         
        
        $this->report_parts[$s][$i] = $this->prepare_binary_value($script_runtime_minute, 6);
        $this->explanations[$s][$i][] = '301090 -> 301012 -> 004005: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$script_runtime_minute.'</span> (Minute, 6 bits)';   
        $i++;     
        
		// 301090 -> 301021 -> 005001 => Latitude
		// 25 bits. Metric = degree. Scale = 5. Reference = –9000000
        $tmp = round($this->station_info->lat, 5) * 100000 + 9000000;
        $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 25);
        $this->explanations[$s][$i][] = '301090 -> 301021 -> 005001: <span>'.$this->report_parts[$s][$i].'</span> => <span>round('.$this->station_info->lat.', 5) * 10^5 -(-9000000) = '.$tmp.'</span> (Latitude, 25 bits)';   
        $i++;          
        
		// 301090 -> 301021 -> 006001 => Longitude. 
		// 26 bits. Metric = degree.  Scale = 5.  Ref value = -18000000
        $tmp = round($this->station_info->lng, 5) * 100000 + 18000000;
        $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 26);
        $this->explanations[$s][$i][] = '301090 -> 301021 -> 006001: <span>'.$this->report_parts[$s][$i].'</span> => <span>round('. $this->station_info->lng .', 5) * 10^5 -(-18000000) = '.$tmp.'</span> (Longitude, 26 bits)';   
        $i++;   
        
		// 301090 -> 007030 => Altitude, Height of station ground above mean sea level. 
		// 17 bits. Metric = m, Scale = 1, Ref = 4000
        $tmp = round($this->station_info->altitude, 1) * 10 + 4000;
        $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 17);
        $this->explanations[$s][$i][] = '301090 -> 007030: <span>'.$this->report_parts[$s][$i].'</span> => <span>round('. $this->station_info->altitude .', 1) * 10^1 -(-4000) = '.$tmp.'</span> (Altitude, 17 bits)';   
        $i++;   
		
        // 301090 -> 007031: Barometer Height. 
		// 17 bits. Metric = m, Scale = 1, Ref = 4000
        $expl_val = '';
		
        if (isset($this->sensors['pressure']))
		{
            $tmp = It::convertMetric($this->sensors['pressure_height']['height'], $this->sensors['pressure_height']['height_metric_code'], 'meter');
            $str_tmp = round(($tmp+$this->station_info->altitude), 1) * 10 + 4000;
            $this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 17); 

            $expl_val = $this->sensors['pressure_height']['height'] .' '. $this->sensors['pressure_height']['height_metric_code']; 
            
			if ($this->sensors['pressure_height']['height_metric_code'] != 'meter')
			{
                $expl_val .= (' = '. $tmp .' meter');
            }
			
            $expl_val .= ' ~ round( ('. $tmp .' + '. $this->station_info->altitude .'), 1)*10^1 -(-4000) = '. $str_tmp; 
        } 
		else
		{
            $this->report_parts[$s][$i] = '11111111111111111';
            $expl_val = 'No Pressure sensor';
        }
		
        $this->explanations[$s][$i][] = '301090 -> 007031: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Barometer Height, m, 17 bits)'; 
        $i++;   
        
        // 3 02 031: Pressure. 
		// 302031 -> 302001 -> 010004: Pressure data
		// Metric = Pa. Scale = –1. Reference value = 0. Size = 14 bits
        if (isset($this->sensors['pressure']))
		{
            if ($this->sensors['pressure']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111111';
                $expl_val = 'Data from Pressure sensor is unavailable';            
            }
			else
			{
                $tmp = It::convertMetric($this->sensors['pressure']['sensor_feature_value'], $this->sensors['pressure']['metric_code'], 'pascal');
                $str_tmp = round($tmp/10, 0);
                $this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 14);

                $expl_val = $this->sensors['pressure']['sensor_feature_value'] .' '. $this->sensors['pressure']['metric_code'];
                
				if ($this->sensors['pressure']['metric_code'] != 'pascal')
				{
                    $expl_val.= ' = '. $tmp .' pascal';
                }
				
                $expl_val.=  ' ~ round('. $tmp .' * 10^(-1), 0) - 0 = '. $str_tmp;
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111111';
            $expl_val = 'No Pressure sensor';            
        }
		
        $this->explanations[$s][$i][] = '302031 -> 302001 -> 010004: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span>  (Barometer Pressure, Pa, 14 bits)';   
        $i++;   
        
		// 302031 -> 302001 -> 010051: Pressure reduced to mean sea level
		// 14 bits. Metric = Pa. Scale = –1. Ref =  0.
        if (isset($this->calculations['PressureSeaLevel']))
		{
            $tmp = It::convertMetric($this->calculations['PressureSeaLevel']['value'], 'hpascal', 'pascal');
            $str_tmp = round($tmp/10, 0);
            
			$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 14);
            $expl_val = $this->calculations['PressureSeaLevel']['value'] .' hPa = '. $tmp .' Pa ~ round('. $tmp .' * 10^(-1), 0) - 0 = '. $str_tmp .'</span>';   
        } 
		else 
		{
            $this->report_parts[$s][$i] = '11111111111111';
            $expl_val = 'No Pressure MSL Calculation';           
        }
		
        $this->explanations[$s][$i][] = '302031 -> 302001 -> 010051: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Pressure MSL, Pa, 14bits)'; 
        $i++;   
        
		// 302031 -> 302001 -> 010061: 3-hour pressure change
		// 10 bits. Metric = Pa. Scale = -1. Ref = -500
		// AND
		// 302031 -> 302001 -> 010063: Characteristic of pressure tendency
		// 4 bits. Code table value. Scale = 0. Ref = 0
        if (isset($this->sensors['pressure']) && isset($sensors_3hr_ago['pressure']))
		{
            if ($this->sensors['pressure']['is_m'])
			{
                $this->report_parts[$s][$i] = '1111111111';
                $this->explanations[$s][$i][] = '302031 -> 302001 -> 010061: <span>'.$this->report_parts[$s][$i].'</span> => <span>Last data from Pressure sensor is unavailable</span> (3h Pressure change, 10 bits)';            
                
				$i++;
                
				$this->report_parts[$s][$i] = '1111';
                $this->explanations[$s][$i][] = '302031 -> 302001 -> 010063: <span>'.$this->report_parts[$s][$i].'</span> => <span>Last data from Pressure sensor is unavailable</span> (3h Pressure change Tendency, 4bits)';                             
            } 
			else if ($sensors_3hr_ago['pressure']['is_m'])
			{
                $this->report_parts[$s][$i] = '1111111111';
                $this->explanations[$s][$i][] = '302031 -> 302001 -> 010061: <span>'.$this->report_parts[$s][$i].'</span> => <span>3h ago data from Pressure sensor is unavailable</span> (3h Pressure change, 10 bits)';            
                
				$i++;
                
				$this->report_parts[$s][$i] = '1111';
                $this->explanations[$s][$i][] = '302031 -> 302001 -> 010063: <span>'.$this->report_parts[$s][$i].'</span> => <span>3h ago data from Pressure sensor is unavailable</span> (3h Pressure change Tendency, 4bits)';                             
                
            } 
			else
			{
				$pressure_3hr = $sensors_3hr_ago['pressure']['sensor_feature_value'];
				$pressure_3hr_metric = $sensors_3hr_ago['pressure']['metric_code'];

				$pressure_3hr = It::convertMetric($pressure_3hr, $pressure_3hr_metric, 'pascal');

				$pressure_now = $this->sensors['pressure']['sensor_feature_value'];
				$pressure_now_metric = $this->sensors['pressure']['metric_code'];

				$pressure_now = It::convertMetric($pressure_now, $pressure_now_metric, 'pascal');
				
				$tmp3 = round(($pressure_now - $pressure_3hr)/10, 0);
                $str_tmp = abs($tmp3) + 500;
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 10);
                
				$expl_val = '302031 -> 302001 -> 010061: <span>'. $this->report_parts[$s][$i] .'</span> => ';
                $expl_val.= '<span>~ round('. $pressure_now .' - '. $pressure_3hr .') * 10^(-1), 0) Pa = '. $tmp3 .' Pa ~  |'. $tmp3 .'| -(-500) = '. $str_tmp .' </span> (3h Pressure change, Pa, 10bits)';
				
				$this->explanations[$s][$i][] = $expl_val;
                $i++;

                if ($pressure_now > $pressure_3hr)
				{
                    $this->report_parts[$s][$i] = '0010';
                    $expl_val = '<span>2 = Increased</span>';  
                }
				else if ($pressure_now < $pressure_3hr)
				{
                    $this->report_parts[$s][$i] = '0111';
                    $expl_val = '<span>7 = Decreased</span>'; 
                }
				else 
				{
                    $this->report_parts[$s][$i] = '0000';
                    $expl_val = '<span>4 = Stable</span>'; 
                }
				
                $this->explanations[$s][$i][] = '302031 -> 302001 -> 010063: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (3h Pressure change Tendency, 4bits)';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111111111';
            $this->explanations[$s][$i][] = '302031 -> 302001 -> 010061: <span>'.$this->report_parts[$s][$i].'</span> => <span>No pressure sensor</span> (3h Pressure change, 10 bits)';            
            
            $i++;
            
            $this->report_parts[$s][$i] = '1111';
            $this->explanations[$s][$i][] = '302031 -> 302001 -> 010063: <span>'.$this->report_parts[$s][$i].'</span> => <span>No pressure sensor</span> (3h Pressure change Tendency, 4bits)';             
        }
        $i++;
        
		// 302031 -> 010062: 24-hour pressure change
		// 11 bits. Metric = Pa. Scale = -1. Ref = -1000
        if (isset($this->sensors['pressure']) && isset($sensors_24hr_ago['pressure']))
		{
            if ($this->sensors['pressure']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111';
                $expl_val = '<span>Last data from Pressure sensor is unavailable</span>';               
            }
			else if ($sensors_24hr_ago['pressure']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111';
                $expl_val = '<span>24h ago data from Pressure sensor is unavailable</span>';               
            } 
			else 
			{
                $pressure_24hr = It::convertMetric($sensors_24hr_ago['pressure']['sensor_feature_value'], $sensors_24hr_ago['pressure']['metric_code'], 'pascal');
                $pressure_now = It::convertMetric($this->sensors['pressure']['sensor_feature_value'], $this->sensors['pressure']['metric_code'], 'pascal');

                $tmp3 = round(($pressure_now - $pressure_24hr)/10, 0);
                $str_tmp = abs($tmp3) + 1000;
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 11);           

                $expl_val = '<span>~ round(('. $pressure_now .' - '. $pressure_24hr .') * 10^(-1), 0) Pa = '. $tmp3 .' Pa ~ |'. $tmp3 .'| -(-1000) = '. $str_tmp .'</span>';   
            }
        }
		else
		{
			$this->report_parts[$s][$i] = '11111111111';
			$expl_val = '<span>No pressure sensor</span>';               
        }
		
        $this->explanations[$s][$i][] = '302031 -> 010062: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (24h Pressure change, Pa, 11bits)';  
        $i++;  
        
		// 302031 -> 007004: Pressure
		// 14 bits. Metric = Pa. Scale = -1. Ref = 0
        $this->report_parts[$s][$i] = '11111111111111';
        $this->explanations[$s][$i][] = '302031 -> 007004: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Pressure, Pa, 14bits)';   
        $i++;
        
		//302031 -> 010009: Geopotential height
		// 17 bits. Metric = gpm. Scale = 0. Ref = -1000.
        $this->report_parts[$s][$i] = '11111111111111111';
        $this->explanations[$s][$i][] = '302031 -> 010009: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (GEOPOTENTIAL HEIGHT, GPM, 17bits)';   
        $i++;
        
        //3 02 035: Basic synoptic “instantaneous” data
        //3 02 035 -> 3 02 032: Temperature and Humidity data.  
		//
		// 302035 -> 302032 -> 012101: Height of temperature sensor above local ground 
		// Metric = m. Scale = 2. Ref = 0. 16 bits      
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302035 -> 302032 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of temperature/humidity sensors, 16bits)';   
        $i++;        
        
		// 302035 -> 302032 -> 012101: Temperature/dry-bulb temperature
		// 16 bits. Metric = K. Scale = 2. Ref = 0.
        if (isset($this->sensors['temperature']))
		{
            if ($this->sensors['temperature']['is_m'])
			{
                $this->report_parts[$s][$i] = '1111111111111111';
                $expl_val = '<span>Last data from Temperature sensor is unavailable</span>';                   
            }
			else
			{
                $tmp = It::convertMetric($this->sensors['temperature']['sensor_feature_value'], $this->sensors['temperature']['metric_code'], 'kelvin');
                $str_tmp = round($tmp*100);

				$this->report_parts[$s][$i] = ($tmp > 0 ? '0' : '1') . $this->prepare_binary_value(abs($str_tmp), 15);
                $expl_val = '<span>'.$this->sensors['temperature']['sensor_feature_value'] .' '. $this->sensors['temperature']['metric_code'];
                
				if ($this->sensors['temperature']['metric_code'] != 'kelvin')
				{
					$expl_val.= ' = '. $tmp. ' kelvin';   
                }
				
                $expl_val.= ' ~ |round('.$tmp.' * 10^2 - 0)| = '. round($tmp*100) . ' (+left bit = '.($tmp > 0 ? '0' : '1').' because '.($tmp > 0 ? 'positive' : 'negative').') ';
                $expl_val.= '</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111111111111111';
            $expl_val = '<span>No temperature sensor</span>';   
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302032 -> 012101: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Temperature, kelvin degree, 16bits)';   
        $i++;

        // 302035 -> 302032 -> 012103: Dew-point temperature
		// 16 bits. Metric = K. Scale = 2. Ref = 0.
        if (isset($this->calculations['DewPoint']))
		{
            $tmp = It::convertMetric($this->calculations['DewPoint']['value'], 'celsius', 'kelvin');
            $str_tmp = round($tmp*100);
            
			$this->report_parts[$s][$i] = ($tmp > 0 ? '0' : '1') . $this->prepare_binary_value(abs($str_tmp), 15);

            $expl_val = '<span>'. $this->calculations['DewPoint']['value'] .' celsius';
            $expl_val.= ' = '. $tmp .' kelvin';
            $expl_val.= ' ~ |round('. $tmp .' * 10^2 - 0)| = '. $str_tmp. ' (+left bit = '. ($tmp > 0 ? '0' : '1') .' because '. ($tmp > 0 ? 'positive' : 'negative') .')';
            $expl_val.= '</span>';
        }
		else
		{
            $this->report_parts[$s][$i] = '1111111111111111';
            $expl_val = '<span>No calculation</span>';   
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302032 -> 012103: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.'  (DewPoint, kelvin, 16bits)'; 
        $i++; 
        
		// 302035 -> 302032 -> 013003: Relative humidity
		// 7 bits. Metric = %. Scale = 0. Ref = 0.
        if (isset($this->sensors['humidity']))
		{
            if ($this->sensors['humidity']['is_m'])
			{
                $this->report_parts[$s][$i] = '1111111';
                $expl_val = '<span>Last data from Humidity sensor is unavailable</span>';                  
            } 
			else 
			{
                $str_tmp = round($this->sensors['humidity']['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 7);
                $expl_val = '<span>round('.$this->sensors['humidity']['sensor_feature_value'].')</span>';   
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111111';
            $expl_val = '<span>No humidity sensor</span>';              
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302032 -> 013003:<span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.'   (RELATIVE HUMIDITY, %, 7bits)';              
        $i++; 
        
		// 302035 -> 302033 -> 007032:  Height of visibility sensor above local ground
		// 16 bits. Metric = m. Scale = 2. Ref = 0
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302035 -> 302033 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Hight of visibility sensor, 16bits)';   
        $i++; 
        
		// 302035 -> 302033 -> 020001: Horizontal Visibility
		// 13 bits. Metric = m. Scale = -1. Ref = 0
        if (isset($this->sensors['visibility_1']))
		{
            if ($this->sensors['visibility_1']['is_m'])
			{
                $this->report_parts[$s][$i] = '1111111111111';
                $expl_val = '<span>Last data from Visibility sensor is unavailable</span>';
            } 
			else 
			{
                $tmp = It::convertMetric($this->sensors['visibility_1']['sensor_feature_value'], $this->sensors['visibility_1']['metric_code'], 'meter');
                $str_tmp = round($tmp / 10, 0);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 13);
                $expl_val = '<span>'.$this->sensors['visibility_1']['sensor_feature_value'].' '.$this->sensors['visibility_1']['metric_code'].' = '.$tmp.' meter. Apply scale=-1 => '.$str_tmp.' </span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111111111111';
            $expl_val = '<span>No Visibility sensor</span>';
        }
        
        $this->explanations[$s][$i][] = '302035 -> 302033 -> 020001: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Horizontal Visibility, 13bits)';   
        $i++;         
        
		// 302035 -> 302034 -> 007032: Height of Rain sensor
		// 16 bits. Metric = m. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302035 -> 302034 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of Rain sensor, 16bits)';   
        $i++;

		// 302035 -> 302034 -> 013023: Total precipitation past 24 hours, kg/m2
		// 14 bits. Metric = kg/m2. Scale = 1. Ref = -1.
        if (isset($this->sensors['rain_in_period']))
		{
            if ($this->sensors['rain_in_period']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111111';
                $expl_val = '<span>Last data from Rain sensor is unavailable</span>';                 
            }
			else
			{
                $handler_obj = SensorHandler::create($this->sensors['rain_in_period']['handler_id_code']);

                $rain_last_24_hr = $handler_obj->getTotalInLastXHr($this->sensors['rain_in_period']['sensor_feature_id'], $measuring_timestamp, 24, false)['total'];
                $tmp = It::convertMetric($rain_last_24_hr, $this->sensors['rain_in_period']['metric_code'], 'millimeter');

                $tmp_str = round($tmp, 1) * 10 - (-1);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp_str, 14);
                $expl_val = '<span>'.$rain_last_24_hr. ' '.$this->sensors['rain_in_period']['metric_code'];
                
				if ($this->sensors['rain_in_period']['metric_code'] != 'millimeter')
				{
                    $expl_val .= ' = '.$tmp.' millimeter';
                }
                $expl_val .= ' ~ round('.$tmp.', 1) * 10^1 -(-1) = '.$tmp_str.'</span>';
            }
        } 
		else 
		{
            $this->report_parts[$s][$i] = '11111111111111';
            $expl_val = '<span>No rain sensor</span>';             
        }
        $this->explanations[$s][$i][] = '302035 -> 302034 -> 013023: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.'(Total precipitation past 24 hours, kg/m2, 14bits)';
        $i++;  
        
		// 302035 -> 007032: Height of Rain sensor
		// 16bits. Metric = m. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302035 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of Rain sensor, 16bits)';   
        $i++;          
        
        // -- 3 02 035 -> 3 02 004: Cloud Data --
		
		// 302035 -> 302004 -> 020010: Cloud cover
		// 7 bits. Metric = %. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111111'; 
        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020010: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud cover, %, 7bits)'; // 7bits
        $i++;          
        
		// 302035 -> 302004 -> 008002: Vertical Significance
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = ' 302035 -> 302004 -> 008002: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Vertical Significance, 6 bits)';   
        $i++;
        
		// 302035 -> 302004 -> 020011: Cloud Amount
		// 4 bits. Metric = code table. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '1111';
//		if (isset($this->sensors['cloud_amount_amount_total']))
//		{
//            if ($this->sensors['cloud_amount_amount_total']['is_m'])
//			{
//                $this->report_parts[$s][$i] = '1111';
//                $expl_val = 'Last data about Cloud Amount is unavailable';                
//            }
//			else
//			{
//                $tmp1 = $this->_get_cloud_cover_code(round($this->sensors['cloud_amount_amount_total']['sensor_feature_value'], 1));
//                
//				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 4);
//                $expl_val = $tmp1 .' ('. round($this->sensors['cloud_amount_amount_total']['sensor_feature_value']) .'/8)';
//            }
//        }
//		else
//		{
//            $this->report_parts[$s][$i] = '1111';
//            $expl_val = 'No Cloud Amount sensor';
//        }
		
//        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020011: <span>'.$this->report_parts[$s][$i].'</span> => <span>'. $expl_val .'</span> (Cloud Amount, 4bits)';   
        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020011: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud Amount, 4bits)';   
		$i++;
        
		// 302035 -> 302004 -> 020013: Height of base cloud
		// 11 bits. Metric = m. Scale = -1. Ref = -40.
        $this->report_parts[$s][$i] = '11111111111';
        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020013: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of base cloud, 11bits)';   
        $i++;   
        
		// 302035 -> 302004 -> 020012: Cloud Type of low clouds
		// 6 bits. Metric = code table. Scale = 0. Ref = 0. 
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020012: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud Type of low clouds, 6bits)';   
        $i++;          
        
		// 302035 -> 302004 -> 020012: Cloud Type of middle clouds
		// 6 bits. Metric = code table. Scale = 0. Ref = 0. 
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020012: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud Type of middle clouds, 6bits)';   
        $i++;          

		// 302035 -> 302004 -> 020012: Cloud Type of high clouds
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.	
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302035 -> 302004 -> 020012: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud Type of high clouds, 6bits)';   
        $i++;          

		// 307080 -> 302035 -> 031001: 3 repetitions for Cloud Layers.
		// 8 bits
		// number of repetitions should be there. 
        // As far as we  have 3 layers of clouds – this value is fixed to 3.
        $this->report_parts[$s][$i] = $this->prepare_binary_value(3, 8);
        $this->explanations[$s][$i][] = '307080 -> 302035 -> 031001: <span>'.$this->report_parts[$s][$i].'</span> => <span>3</span> repetitions for 3 Cloud Layers.(Decide here how much repetition we need, 8bits)';   
        $i++;          
		
		// Repetition #1
		$cloudAmountSensor = isset($this->sensors['cloud_amount_amount_1'])
								? $this->sensors['cloud_amount_amount_1']
								: null;

        // 302035 -> 302005 -> 008002 (Repetition #1): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        if (isset($cloudAmountSensor))
		{
            if ($cloudAmountSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '111111';
                $expl_val = '<span>Missing</span>';                
            }
			else
			{
                $tmp1 = $this->getVerticalSignificance(1, $cloudAmountSensor['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 6);
                $expl_val = '<span>'. $tmp1 .' ('. $cloudAmountSensor['sensor_feature_value'] .'/8)</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '111111';
            $expl_val = '<span>Missing (No such measurement)</span>';
        }
		
		$this->explanations[$s][$i][] = '302035 -> 302005 -> 008002 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Vertical significance (surface observations), 6bits)';   
        $i++;   
        
		// 302035 -> 302005 -> 020011 (Repetition #1): Cloud amount
		// 4 bits. Metric = code table. Scale = 0. Ref = 0.
        if (isset($cloudAmountSensor))
		{
            if ($cloudAmountSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '1111';
                $expl_val = '<span>Last data about Cloud Amount is unavailable</span>';                
            }
			else
			{
                $tmp1 = $this->_get_cloud_cover_code($cloudAmountSensor['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 4);
                $expl_val = '<span>'. $tmp1 .' ('. $cloudAmountSensor['sensor_feature_value'] .'/8)</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111';
            $expl_val = '<span>No Cloud Amount sensor</span>';
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020011 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> =>  '.$expl_val.' (Cloud amount, 4bits)';   
        $i++; 
        
		
		// 302035 -> 302005 -> 020012 (Repetition #1): Cloud Type
		// 6 bits. Metric = code table. Scale = 0. Ref = 0. 
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020012 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing (No such measurement)</span> (Cloud Type, 6bits)';   
        $i++;          
        
		$cloudHeightSensor = isset($this->sensors['cloud_amount_height_1'])
								? $this->sensors['cloud_amount_height_1']
								:
									(isset($this->sensors['cloud_height_height_1'])
										? $this->sensors['cloud_height_height_1']
										: null
									);

		// 302035 -> 302005 -> 020013 (Repetition #1): Height of Base Cloud
		// 11 bits. Metric = m. Scale = -1. Ref = -40.
        if (isset($cloudHeightSensor))
		{
            if ($cloudHeightSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111';
        		$expl_val = '<span>Last data about Cloud Height is unavailable</span>';
            } 
			else 
			{
                $tmp1 = It::convertMetric($cloudHeightSensor['sensor_feature_value'], $cloudHeightSensor['metric_code'], 'meter');
                $tmp2 = round($tmp1/10 + 40);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp2, 11);
                $expl_val = '<span>'.round($cloudHeightSensor['sensor_feature_value']).' '. $cloudHeightSensor['metric_code'].' = '.round($tmp1,1).' meter.</span> With scale=-1 and ref=40: <span>'.$tmp2.'</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111';
            $expl_val = '<span>No Cloud Height sensor</span>';
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020013 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Height of Base Cloud, 11bits)';   
        $i++;          

		// Repetition #2
        $cloudAmountSensor = isset($this->sensors['cloud_amount_amount_2'])
								? $this->sensors['cloud_amount_amount_2']
								: null;

        // 302035 -> 302005 -> 008002 (Repetition #2): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        if (isset($cloudAmountSensor))
		{
            if ($cloudAmountSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '111111';
                $expl_val = '<span>Missing</span>';                
            }
			else
			{
                $tmp1 = $this->getVerticalSignificance(2, $cloudAmountSensor['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 6);
                $expl_val = '<span>'. $tmp1 .' ('. $cloudAmountSensor['sensor_feature_value'] .'/8)</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '111111';
            $expl_val = '<span>Missing (No such measurement)</span>';
        }
		
		$this->explanations[$s][$i][] = '302035 -> 302005 -> 008002 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Vertical significance (surface observations), 6bits)';   
        $i++;   
        
		// 302035 -> 302005 -> 020011 (Repetition #2): Cloud amount
		// 4 bits. Metric = code table. Scale = 0. Ref = 0.
        if (isset($cloudAmountSensor))
		{
            if ($cloudAmountSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '1111';
                $expl_val = '<span>Last data about Cloud Amount is unavailable</span>';                
            }
			else
			{
                $tmp1 = $this->_get_cloud_cover_code($cloudAmountSensor['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 4);
                $expl_val = '<span>'. $tmp1 .' ('. $cloudAmountSensor['sensor_feature_value'] .'/8)</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111';
            $expl_val = '<span>No Cloud Amount sensor</span>';
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020011 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> =>  '.$expl_val.' (Cloud amount, 4bits)';   
        $i++;         
                
		// 302035 -> 302005 -> 020012 (Repetition #2): Cloud Type
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020012 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing (No such measurement)</span> (Cloud Type, 6bits)';  
        $i++;   
        
		$cloudHeightSensor = isset($this->sensors['cloud_amount_height_2'])
								? $this->sensors['cloud_amount_height_2']
								:
									(isset($this->sensors['cloud_height_height_2'])
										? $this->sensors['cloud_height_height_2']
										: null
									);

		// 302035 -> 302005 -> 020013 (Repetition #2): Height of Base Cloud
		// 11 bits. Metric = m. Scale = -1. Ref = -40.
        if (isset($cloudHeightSensor))
		{
            if ($cloudHeightSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111';
                $expl_val = '<span>Last data about Cloud Height is unavailable</span>';
            }
			else
			{
                $tmp1 = It::convertMetric($cloudHeightSensor['sensor_feature_value'], $cloudHeightSensor['metric_code'], 'meter');
                $tmp2 = round($tmp1/10 + 40);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp2, 11);
                $expl_val = '<span>'.round($cloudHeightSensor['sensor_feature_value']).' '. $cloudHeightSensor['metric_code'].' = '.round($tmp1,1).' meter.</span> With scale=-1 and ref=40: <span>'.$tmp2.'</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111';
            $expl_val = '<span>No Cloud Height sensor</span>';
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020013 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Height of Base Cloud, 11bits)';   
        $i++;            
        
		// Repetition #3

		// if layer 3 is missing then check layer 4.
		$cloudAmountSensor = (isset($this->sensors['cloud_amount_amount_3']) && ($this->sensors['cloud_amount_amount_3']['is_m'] != 1))
								? $this->sensors['cloud_amount_amount_3']
								:
									(isset($this->sensors['cloud_amount_amount_4'])
										? $this->sensors['cloud_amount_amount_4']
										: null
									);
		
		$group = (isset($this->sensors['cloud_amount_amount_3']) && ($this->sensors['cloud_amount_amount_3']['is_m'] != 1))
								? 3
								:
									(isset($this->sensors['cloud_amount_amount_4'])
										? 4
										: null
									);
		
		// 302035 -> 302005 -> 008002 (Repetition #3): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.	
        if (isset($cloudAmountSensor))
		{
            if ($cloudAmountSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '111111';
                $expl_val = '<span>Missing</span>';                
            }
			else
			{
                $tmp1 = $this->getVerticalSignificance($group, $cloudAmountSensor['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 6);
                $expl_val = '<span>'. $tmp1 .' ('. $cloudAmountSensor['sensor_feature_value'] .'/8)</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '111111';
            $expl_val = '<span>Missing (No such measurement)</span>';
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 008002 (Repetition #3): <span>'.$this->report_parts[$s][$i].'</span> => <span>'. $expl_val .'</span> (Vertical significance (surface observations), 6bits)';   
        $i++;          
        
		// 302035 -> 302005 -> 020011 (Repetition #3): Cloud amount
		// 4 bits. Metric = code table. Scale = 0. Ref = 0.
		
        if (isset($cloudAmountSensor))
		{
            if ($cloudAmountSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '1111';
                $expl_val = '<span>Last data about Cloud Amount is unavailable</span>';                
            } 
			else
			{
                $tmp1 = $this->_get_cloud_cover_code($cloudAmountSensor['sensor_feature_value']);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp1, 4);
                $expl_val = '<span>'. $tmp1 .' ('. $cloudAmountSensor['sensor_feature_value'] .'/8)</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '1111';
            $expl_val = '<span>No Cloud Amount sensor</span>';
        }
		
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020011 (Repetition #3): <span>'.$this->report_parts[$s][$i].'</span> =>  '.$expl_val.' (Cloud amount, 4bits)';   
        $i++;         
                
		// 302035 -> 302005 -> 020012 (Repetition #3): Cloud Type
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302035 -> 302005 -> 020012 (Repetition #3): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing (No such measurement)</span> (Cloud Type, 6bits)';  
        $i++;   
        
		$cloudHeightSensor = isset($this->sensors['cloud_amount_height_3'])
								? $this->sensors['cloud_amount_height_3']
								:
									(isset($this->sensors['cloud_height_height_3'])
										? $this->sensors['cloud_height_height_3']
										: null
									);
		
		// 302035 -> 302005 -> 020013 (Repetition #3): Height of Base Cloud
		// 11 bits. Metric = m. Scale = -1. Ref = -40.
        if (isset($cloudHeightSensor))
		{
            if ($cloudHeightSensor['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111';
                $expl_val = '<span>Last data about Cloud Height is unavailable</span>';
            } 
			else 
			{
                $tmp1 = It::convertMetric($cloudHeightSensor['sensor_feature_value'], $cloudHeightSensor['metric_code'], 'meter');
                $tmp2 = round($tmp1/10 + 40);
                
				$this->report_parts[$s][$i] = $this->prepare_binary_value($tmp2, 11);
                $expl_val = '<span>'.round($cloudHeightSensor['sensor_feature_value']).' '. $cloudHeightSensor['metric_code'].' = '.round($tmp1,1).' meter.</span> With scale=-1 and ref=40: <span>'.$tmp2.'</span>';
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111';
            $expl_val = '<span>No Cloud Height sensor</span>';
        }
        
		$this->explanations[$s][$i][] = '302035 -> 302005 -> 020013 (Repetition #3): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Height of Base Cloud, 11bits)';   
        $i++;         
        
        // 3 02 036: Clouds with bases below station level  
		// 302036 -> 031001: 1 repetition
		// 8 bits
		// 1 repetition only, so 1 is fixed
        $this->report_parts[$s][$i] = $this->prepare_binary_value(1, 8);
        $this->explanations[$s][$i][] = '302036 -> 031001 (Decide here how much repetition we need): <span>'.$this->report_parts[$s][$i].'</span> => <span>1</span>';   
        $i++;          
        
		// 302036 -> 008002 (Repetition #1): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302036 -> 008002 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Vertical significance (surface observations), 6bits)';   
        $i++;   
        
		// 302036 -> 020011 (Repetition #1): Cloud amount
		// 4 bits. Metric = code table. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '302036 -> 020011 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud amount, 4bits)';   
        $i++;   
        
		// 302036 -> 020012 (Repetition #1): Cloud Type
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302036 -> 020012 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud Type, 6bits)';   
        $i++;   
        
		// 302036 -> 020014 (Repetition #1): Height of top of cloud
		// 11 bits. Metric = m. Scale = -1. Ref = -40;
		$this->report_parts[$s][$i] = '11111111111';
        $this->explanations[$s][$i][] = '302036 -> 020014 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of top of cloud, 11bits)';   
        $i++;   
        
		// 302036 -> 020017 (Repetition #1): Cloud top description
		// 4 bits. Metric = code table. Scale = 0. Ref = 0;
		$this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '302036 -> 020017 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud top description, 4bits)';   
        $i++;   
        
        // 3 02 047: Direction of cloud drift  
		// 302047 -> 008002 (Repetition #1): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302047 -> 008002 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Vertical significance (surface observations), 6bits)';   
        $i++;
		
		// 302047 -> 020054 (Repetition #1): True direction
		// 9 bits. Metric = degree true. Scale = 0. Ref = 9
        $this->report_parts[$s][$i] = '111111111';
        $this->explanations[$s][$i][] = '302047 -> 020054 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (True direction from which clouds are moving, 9bits)';   
        $i++;   
        
		// 302047 -> 008002 (Repetition #2): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302047 -> 008002 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Vertical significance (surface observations), 6bits)';   
        $i++;   
        
		// 302047 -> 020054 (Repetition #2): True direction
		// 9 bits. Metric = degree true. Scale = 0. Ref = 9
		$this->report_parts[$s][$i] = '111111111';
        $this->explanations[$s][$i][] = '302047 -> 020054 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (True direction from which clouds are moving, 9bits)';   
        $i++;          
        
		// 302047 -> 008002 (Repetition #3): Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0
		$this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302047 -> 008002 (Repetition #3): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Vertical significance (surface observations), 6bits)';   
        $i++;   
        
		// 302047 -> 020054 (Repetition #3): True direction
		// 9 bits. Metric = degree true. Scale = 0. Ref = 9
		$this->report_parts[$s][$i] = '111111111';
        $this->explanations[$s][$i][] = '302047 -> 020054 (Repetition #3): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (True direction from which clouds are moving, 9bits)';   
        $i++;  
        
		// 008002: Vertical significance (surface observations)
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '008002: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Vertical significance, 6bits)';   
        $i++;   
        
        // 3 02 048: Direction and elevation of cloud   
		// 302048 -> 005021: Bearing of azimuth
		// 16 bits. Metric = degree. Scale = 2. Ref = 0 
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302048 -> 005021: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Bearing of azimuth, 16bits)';   
        $i++;   
		
		// 302048 -> 007021: Elevation
		// 15 bits. Metric = Degree. Scale = 2. Ref = -9000.
        $this->report_parts[$s][$i] = '111111111111111';
        $this->explanations[$s][$i][] = '302048 -> 007021: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Elevation, 15bits)';   
        $i++;   
		
		// 302048 -> 020012: Cloud type
		// 6 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111';
        $this->explanations[$s][$i][] = '302048 -> 020012: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Cloud type, 6bits)';   
        $i++;
		
		// 302048 -> 005021: Bearing of azimuth
		// 16 bits. Metric = degree. Scale = 2. Ref = 0
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302048 -> 005021: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Bearing of azimuth, 16bits)';   
        $i++;   
		
		// 302048 -> 007021: Elevation
		// 15 bits. Metric = Degree. Scale = 2. Ref = -9000.
        $this->report_parts[$s][$i] = '111111111111111';
        $this->explanations[$s][$i][] = '302048 -> 007021: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Elevation, 15bits)';   
        $i++;   
        
        // 3 02 037: State of ground, snow depth, ground minimum temperature 
		// 302037 -> 020062: State of the ground
		// 5 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '11111'; // 31 - missing value from "State of ground" code table.
        $this->explanations[$s][$i][] = '302037 -> 020062: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (State of the ground, 5bits)';   
        $i++;        
        
		// 302037 -> 013013: Total snow depth
		// 16 bits. Metric = m. Scale = 2. Ref = -2.
		if (isset($this->sensors['snow_depth']))
		{
			if ($this->sensors['snow_depth']['is_m'])
			{
				$this->report_parts[$s][$i] = '1111111111111111';
				$this->explanations[$s][$i][] = '302037 -> 013013: <span>'.$this->report_parts[$s][$i].'</span> => <span>Last data about Snow Depth is unavailable</span> (Total snow depth, 16bits)';
			}
			else
			{
				$value = round($this->sensors['snow_depth']['sensor_feature_value'] * 100) + 2;
				$this->report_parts[$s][$i] = $this->prepare_binary_value($value, 16);
				
				$this->explanations[$s][$i][] = '302037 -> 013013: <span>'.$this->report_parts[$s][$i].'</span> => <span>'. $value .'</span> (Total snow depth, 16bits)';
			}
		}
		else
		{
			$this->report_parts[$s][$i] = '1111111111111111';
			$this->explanations[$s][$i][] = '302037 -> 013013: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Total snow depth, 16bits)';
		}
		$i++;
		
		// 302037 -> 012113: Ground minimum temperature
		// 16 bits. Metric = K. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302037 -> 012113: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Ground minimum temperature, 16bits)';   
        $i++; 
        
		// 0 12 122: Ground minimum temperature of the preceding night
		// 16 bits. Metric = K. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '0 12 122: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Ground minimum temperature of the preceding night, 16bits)';   
        $i++;   
		
		// 0 13 056: Character and intensity of precipitation
        // 4 bits. Metric = -. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 13 056: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Character and intensity of precipitation, 4bits)';   
        $i++;   
		
		// 0 13 057: Time of beginning or end of precipitation
        // 4 bits. Metric = -. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 13 057: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time of beginning or end of precipitation, 4bits)';   
        $i++;
		
		// -- Locust data --
		
		// 0 20 101: Locust (acridian) name 
		// 4 bits. Metric = -. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 101: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Locust (acridian) name, 4bits)';   
        $i++;
		
		// 0 20 102: Locust (maturity) colour 
        // 4 bits. Metric = -. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 102: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Locust (maturity) colour, 4bits)';   
        $i++;
		
		// 0 20 103: Stage of development of locusts
        // 4 bits. Metric = -. Scale = 0. Ref = 0.
		$this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 103: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Stage of development of locusts, 4bits)';   
        $i++;
		
		// 0 20 104: Organization state of swarm or band of locusts 
		// 4 bits. Metric = -. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 104: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Organization state of swarm or band of locusts, 4bits)';   
        $i++;
		
		// 0 20 105: Size of swarm or band of locusts and duration of passage of swarm
		// 4 bits. Metric = -. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 105: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Size of swarm or band of locusts and duration of passage of swarm, 4bits)';   
        $i++;
		
		// 0 20 106: Locust population density 
		// 4 bits. Metric = -. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 106: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Locust population density , 4bits)';   
        $i++;
		
		// 0 20 107: Direction of movements of locust swarm
		// 4 bits. Metric = -. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 107: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Direction of movements of locust swarm, 4bits)';   
        $i++;
		
		// 0 20 108: Extent of vegetation
		// 4 bits. Metric = -. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '0 20 108: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Extent of vegetation, 4bits)';   
        $i++;
		
		// -- End of Locust data --
		
        // 3 02 043: Basic  synoptic “Period” data
        // 3 02 043 → 3 02 038: Present and past weather.  
		// 302043 -> 302038 -> 020003: Present weather
		// 9 bits. Metric = Code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111111';
        $this->explanations[$s][$i][] = '302043 -> 302038 -> 020003: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> Present weather, 9bits)';   
        $i++; 
        // SEE NOTE 1!
        
		// 302043 -> 302038 -> 004024: Time period of displacement
		// 12 bits. Metric = Hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302038 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time period of displacement, 12bits)';   
        $i++; 
		
		// 302043 -> 302038 -> 020004: Past Weather 1
		// 5 bits. Metric = -. Scale = 0. Ref = 0. 
        $this->report_parts[$s][$i] = '11111';
        $this->explanations[$s][$i][] = '302043 -> 302038 -> 020004: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Past Weather 1, 5bits)';   
        $i++; 
        // SEE NOTE 2!
        
        // 302043 -> 302038 -> 020005: Past Weather 2
		// 5 bits. Metric = Code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '11111';
        $this->explanations[$s][$i][] = '302043 -> 302038 -> 020005: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Past Weather 2, 5bits)';   
        $i++;  
        // SEE NOTE 3!
      
        // 3 02 043 → 3 02 039: Sunshine data (from 1 hour and 24 hour period)
		// 302043 -> 302039 -> 004024 (Repetition #1): Time period of sunshine measuring
		// 12 bits.  Metric = Hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = $this->prepare_binary_value(2047, 12);
        $this->explanations[$s][$i][] = '302043 -> 302039 -> 004024 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>-1 - (-2048) = 2047</span> (Time period of sunshine measuring, hours,  12 bits)';   
        $i++;          
        
		// 302043 -> 302039 -> 014031 (Repetition #1): Total Sunshine
		// 11 bits. Metric = minute. Scale = 0. Ref = 0.
		if (isset($this->sensors['sun_duration_in_period']) && $this->sensors['sun_duration_in_period']['sensor_feature_period'])
		{
            if ($this->sensors['sun_duration_in_period']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111';
                $expl_val = '<span>Last data about Sunshine is unavailable</span>';                
            }
			else
			{
                // get object of SunDuration handler
                $handler_obj = SensorHandler::create($this->sensors['sun_duration_in_period']['handler_id_code']);
                // get total sunshine in last 1 hour                
                $last_1_hr = $handler_obj->getTotalInLastXHr($this->sensors['sun_duration_in_period']['sensor_feature_id'], $script_runtime, 1, false)['total'];

				// round to get integer			
                $str_tmp = round($last_1_hr);
				
                $this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 11);

                $expl_val = '<span>' . $str_tmp . ' (during last hour)</span>';    
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111';
            $expl_val = '<span>No sunshine sensor</span>';
        }
		$this->explanations[$s][$i][] = '302043 -> 302039 -> 014031 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Total Sunshine, minutes, 11bits)';             
        $i++;          
        
		// 302043 -> 302039 -> 004024 (Repetition #2): Time period of sunshine measuring
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = '100000011000';
        $this->explanations[$s][$i][] = '302043 -> 302039 -> 004024 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>24 - (-2048) = 2072</span> (Time period of sunshine measuring, hrs, 12bits)';   
        $i++;         
        
		// 302043 -> 302039 -> 014031 (Repetition #2): Total Sunshine
		// 11 bits. Metric = minute. Scale = 0. Ref = 0.
		if (isset($this->sensors['sun_duration_in_period']) && $this->sensors['sun_duration_in_period']['sensor_feature_period'])
		{
            $handler_obj = SensorHandler::create($this->sensors['sun_duration_in_period']['handler_id_code']);
			$last_24_hr = $handler_obj->getTotalInLastXHr($this->sensors['sun_duration_in_period']['sensor_feature_id'], $measuring_timestamp, 24, false)['total'];
            
			$str_tmp = round($last_24_hr);
			
			$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 11);
            $expl_val = '<span>'. $str_tmp .'</span>';             
        } 
		else
		{
            $this->report_parts[$s][$i] = '11111111111';
            $this->explanations[$s][$i][] = '<span>No sunshine sensor</span>';             
        }
        
		$this->explanations[$s][$i][] = '302043 -> 302039 -> 014031 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Total Sunshine, minutes, 11bits)'; 
        $i++;         
        
        // 3 02 043 → 3 02 040: Precipitation measurement
		// 302043 -> 302040 -> 007032: Height of rain sensor
		// 16 bits. Metric = m. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302040 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of rain sensor, 16bits)';   
        $i++;          
               
		// 302043 -> 302040 -> 004024 (Repetition #1): Time period or displacement
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = $this->prepare_binary_value(2047, 12);
        $this->explanations[$s][$i][] = '302043 -> 302040 -> 004024 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>-1 - (-2048) = 2049</span> (Time period or displacemnt, Hours, 12bits)';   
        $i++;          

		// 302043 -> 302040 -> 013011 (Repetition #1): Total rain for 1 hour
		// 14 bits. Metric = kg/m2. Scale = 1. Ref = -1.
        if (isset($this->sensors['rain_in_period']) && $this->sensors['rain_in_period']['sensor_feature_period'])
		{
            if ($this->sensors['rain_in_period']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111111';
                $expl_val = '<span>last data from Rain sensor is unavalable.</span>';             
            } 
			else
			{
                // create object of Rain handler
                $handler_obj = SensorHandler::create($this->sensors['rain_in_period']['handler_id_code']);
                // get total rain in last 1 hour
                $last_1_hr = $handler_obj->getTotalInLastXHr($this->sensors['rain_in_period']['sensor_feature_id'], $script_runtime, 1, false)['total'];

                $tmp1 = It::convertMetric($last_1_hr, $this->sensors['rain_in_period']['metric_code'], 'millimeter');
				
				// 1. apply scale = -2 (value * 10^1 )
				// 2. round to get integer			
				// 3. apply ref = -1				
                $tmp = round($tmp1 * 10) - (-1);
                $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 14);

                $expl_val = '<span>'. $last_1_hr .' '. $this->sensors['rain_in_period']['metric_code'] .' (during 60 min)';
                
				if ($this->sensors['rain_in_period']['metric_code'] != 'millimeter')
				{
					$expl_val .= ' = '. $tmp1 .' mm ';
                }
				
                $expl_val.= ' ~ round('. $tmp1 .' * 10^1) -(-1) = '. $tmp .'</span>';   
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111111';
            $expl_val = '<span>No rain sensor</span>';               
        }
		
        $this->explanations[$s][$i][] = '302043 -> 302040 -> 013011 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.'(Total rain for 1 hour, 14bits)';               
        $i++;          

		// The following descriptors about rain depend on script run timestamp. 
		// At 00:00 report shows total rain for last 24 hours. 
		// At 06:00, 12:00, 18:00 report shows total rain for last 6 hours. 
		// For any other timestamp report shows missed value (111...)        
        $rain_total_hours = 0;
		
        if ($script_runtime_minute == '00' && $script_runtime_hour == '00') 
		{
			$rain_total_hours = 24;
        } 
		else if ($script_runtime_minute == '00' && in_array($script_runtime_hour, array('06', '12', '18')))
		{
			$rain_total_hours = 6;
        } 
		
        // 302043 -> 302040 -> 004024 (Repetition #2): Time period of precipitation measuring
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = $rain_total_hours ? $this->prepare_binary_value((2048 - $rain_total_hours), 12) : '111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302040 -> 004024 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>'. ($rain_total_hours ? ('-'.$rain_total_hours.' - (-2048)') : 'This is not 00, 06, 12 or 18 hr') .'</span> (Time period of precipitation measuring, hrs, 12bits)'; 
        $i++;
        
		// 302043 -> 302040 -> 013011 (Repetition #2): Total precipitation in last 24 or 6 hours
		// 14bits. Metric = kg/m2. Scale = 1. Ref = -1.
        if (isset($this->sensors['rain_in_period']))
		{
            if ($rain_total_hours === 0)
			{
                $this->report_parts[$s][$i] = '11111111111111';
                $expl_val = '<span>Total rain can be calculated only at 00, 06, 12 or 18 hr</span>';                   
            } 
			else 
			{
            	// create object of rain Handler
                $handler_obj = SensorHandler::create($this->sensors['rain_in_period']['handler_id_code']);

                $rain_last_24_hr = $handler_obj->getTotalInLastXHr($this->sensors['rain_in_period']['sensor_feature_id'], $script_runtime, $rain_total_hours, false)['total'];
				
                $tmp = It::convertMetric($rain_last_24_hr, $this->sensors['rain_in_period']['metric_code'], 'millimeter');

				// 1. apply scale = -2 (value * 10^1 )
				// 2. round to get integer			
				// 3. apply ref = -1
                $tmp_str = round($tmp * 10) - (-1);
                $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp_str, 14);
                
				$expl_val = '<span>';
                $expl_val.= $rain_last_24_hr.' '.$this->sensors['rain_in_period']['metric_code'];
                
				if ($this->sensors['rain_in_period']['metric_code'] != 'millimeter')
				{
					$expl_val .= ' = '. $tmp .' mm';
                }
				
                $expl_val .= '~ round('. $tmp .' * 10^1) -(-1) = ' . $tmp_str . '</span>';   
            }  
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111111';
            $expl_val = '<span>No rain sensor</span>';             
        }
		
        $this->explanations[$s][$i][] = '302043 -> 302040 -> 013011 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span>  => '.$expl_val.' (Total precipitation past '. $rain_total_hours .' hours, kg/m2, 14bits)';
        $i++;         
        
        // 3 02 043 -> 3 02 041: Extreme temperature data
		// 302043 -> 302041 -> 007032: Height of temperature sensor
		// 16 bits. Metric = m. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of temperature sensor, 16bits)';   
        $i++;          

		// prepare temperature extremums
        if (isset($this->sensors['temperature']))
		{
            $handler_obj = SensorHandler::create($this->sensors['temperature']['handler_id_code']);
            
			if ($script_runtime_hour == '06')
			{
            	// get temperature extremums in period [21:00 of day before previous day; 21:00 of previous day)
                $tmp_measuring_timestamp = mktime(20, 59, 59, $script_runtime_month, $script_runtime_day - 1, $script_runtime_year);
				$temperature_extr = $handler_obj->getMaxMinFromHour($this->sensors['temperature']['sensor_feature_id'], $tmp_measuring_timestamp, 21, false);
            }
			else if ($script_runtime_hour == '00')
			{
            	// get temperature extremums in period [09:00 of previous day; 09:00 of this day)
                $tmp_measuring_timestamp = mktime(8, 59, 59, $script_runtime_month, $script_runtime_day - 1, $script_runtime_year);
                $temperature_extr = $handler_obj->getMaxMinFromHour($this->sensors['temperature']['sensor_feature_id'], $tmp_measuring_timestamp, 9, false);
            }
        }
                
		// Max tempr
		// 302043 -> 302041 -> 004024: Time period of temperature measuring
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '011111100100' : '111111111111');
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>24 * 10^0 - (-2048) = 2072</span> (Time period of temperature measuring, hours, 12bits)';   
        $i++;          
        
		// 302043 -> 302041 -> 004024: Time period of temperature measuring
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '011111111100' : '111111111111');
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time period or replacement, 12bits)';         
        $i++;
        
		// 302043 -> 302041 -> 012111: Maximum temperature, at height and over period specified
		// 16 bits. Metric = K. Scale = 2. Ref = 0.
        if (isset($this->sensors['temperature']))
		{
            if ($script_runtime_hour == '06' && $script_runtime_minute == '00')
			{
                $tmp1 = It::convertMetric($temperature_extr['max'] ? $temperature_extr['max'] : 0, $this->sensors['temperature']['metric_code'], 'kelvin');
				
				// 1. apply scale = 2 (value * 10^2)
				// 2. round to get integer				
                $tmp = round($tmp1 * 100);
                $this->report_parts[$s][$i] = ($tmp > 0 ? '0' : '1') . $this->prepare_binary_value(abs($tmp), 15);
                $expl_val = $temperature_extr['max'] .' '. $this->sensors['temperature']['metric_code']; 
                
				if ($this->sensors['temperature']['metric_code'] != 'kelvin')
				{
                    $expl_val .= ' = '. $tmp1 .' kelvin';
                }
				
                $expl_val .= ' ~ |round('. $tmp1 .' * 10^2) - 0| = '. $tmp .'(+left bit = '. ($tmp > 0 ? '0' : '1') .' because '. ($tmp > 0 ? 'positive' : 'negative') .') ';
            } 
			else 
			{
                $this->report_parts[$s][$i] = '1111111111111111';
                $expl_val = 'MAX tempr can be calculated only at 06UTC';                
            }
        } 
		else 
		{
            $this->report_parts[$s][$i] = '1111111111111111';
            $expl_val = 'No temperature sensor';
        }
		
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 012111: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Maximum temperature, K, 16bits)';
        $i++; 
        
		 // Min tempr
		// 302043 -> 302041 -> 004024: Time period of temperature measuring
        $this->report_parts[$s][$i] = (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '011111100100' : '111111111111');
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>'. (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '-28 - (-2048) = 2020' : 'Missed because it is not at 00 UTC') .'</span> (Time period of temperature measuring, 12bits)';   
        $i++;     
        
		// 302043 -> 302041 -> 004024: Time period of temperature measuring
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '011111111100' : '111111111111');
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>'. (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '-4 - (-2048) = 2044' : 'Missed because it is not at 00 UTC') .'</span> (Time period or displacement, 12bits)';         
        $i++;
        
		// 302043 -> 302041 -> 012112: Minimum temperature, at height and over period specified
		// 16 bits. Metric = K. Scale = 2. Ref = 0.
        if (isset($this->sensors['temperature']))
		{
            if ($script_runtime_hour == '06' && $script_runtime_minute == '00')
			{
                $tmp1 = It::convertMetric($temperature_extr['min'] ? $temperature_extr['min'] : 0, $this->sensors['temperature']['metric_code'], 'kelvin');
				
				// 1. apply scale = 2 (value * 10^2)
				// 2. round to get integer				
                $tmp = round($tmp1 * 100);
                $this->report_parts[$s][$i] = ($tmp > 0 ? '0' : '1') . $this->prepare_binary_value(abs($tmp), 15);
                $expl_val = $temperature_extr['min'] .' '. $this->sensors['temperature']['metric_code'];
                
				if ($this->sensors['temperature']['metric_code'] != 'kelvin')
				{
                    $expl_val .= ' = '. $tmp1 .' kelvin';
                }
				
                $expl_val .= ' ~ |round('.$tmp1.' * 10^2) - 0| = '. abs($tmp) .' (+left bit = '. ($tmp > 0 ? '0' : '1') .' because '. ($tmp > 0 ? 'positive' : 'negative') .')';
            } 
			else 
			{
                $this->report_parts[$s][$i] = '1111111111111111';
                $expl_val = 'MIN tempr can be calculated only at 00UTC';                
            }
		}
		else
		{
            $this->report_parts[$s][$i] = '1111111111111111';
            $expl_val = 'No temperature sensor';
        }
		
        $this->explanations[$s][$i][] = '302043 -> 302041 -> 012112: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Minimum temperature, K, 16bits)';
        $i++;        
        
        // 3 02 043 -> 3 02 042:  Wind data
        // 302043 -> 302042 -> 007032: Height of wind sensor
		// 16 bits. Metric = m. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of wind sensor, 16bits)';   
        $i++;          
        
		// 302043 -> 302042 -> 002002: Type of instrumentation for wind measurement
		// 4 bits. Metric = Flag table. Scale = 0. Ref = 0
        $this->report_parts[$s][$i] = $this->prepare_binary_value(8, 4);
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 002002: <span>'.$this->report_parts[$s][$i].'</span> => <span>8</span> (Type of instrumentation for wind measurement, 4 bits)';   
        $i++; 
        
		// 302043 -> 302042 -> 008021: Time significance
		// 5 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = $this->prepare_binary_value(2, 5);
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 008021: <span>'.$this->report_parts[$s][$i].'</span> => <span>2=time averaged</span> (Time significance, 5 bits)';   
        $i++;          
        
		// 302043 -> 302042 -> 004025: Time period or displacement
		// 12 bits. Metric = minute. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = $this->prepare_binary_value(2038, 12);
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 004025: <span>'.$this->report_parts[$s][$i].'</span> => <span>-10 - (-2048) = 2038</span> (Time period or displacement, min, 12bits)';   
        $i++; 
        
		// 302043 -> 302042 -> 011001: Wind direction
		// 9 bits. Metric = degree. Scale = 0. Ref = 0.
        if (isset($this->sensors['wind_direction_10']))
		{
            if ($this->sensors['wind_direction_10']['is_m'])
			{
                $this->report_parts[$s][$i] = '111111111';
                $expl_val = 'Last data about Wind Direction is unavailable';                  
            }
			else
			{
				// create object of wind direction handler
                $handler_obj = SensorHandler::create($this->sensors['wind_direction_10']['handler_id_code']);
				
				// apply magnetic north offset
                $tmp = $handler_obj->applyOffset($this->sensors['wind_direction_10']['sensor_feature_value'], $this->station_info->magnetic_north_offset);            
                $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 9); 
                
				$expl_val = $this->sensors['wind_direction_10']['sensor_feature_value'];
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '111111111';
            $expl_val = 'No Wind Direction sensor';   
        }
		
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 011001: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Wind direction, degree, 9 bits)';   
        $i++;          
        
		// 302043 -> 302042 -> 011002: Wind speed
		// 12 bits. Metric = m/s. Scale = 1. Ref = 0.
        if (isset($this->sensors['wind_speed_10']))
		{
            if ($this->sensors['wind_speed_10']['is_m'])
			{
                $this->report_parts[$s][$i] = '111111111111';
                $expl_val = 'Last data about Wind Speed is unavailable';                  
            } 
			else 
			{
                $tmp1 = It::convertMetric($this->sensors['wind_speed_10']['sensor_feature_value'], $this->sensors['wind_speed_10']['metric_code'], 'meter_per_second');
				
				// 1. apply scale = 1 (value * 10)
				// 2. round to get integer
                $tmp = round($tmp1 * 10);
                $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 12);

                $expl_val = $this->sensors['wind_speed_10']['sensor_feature_value'] .' '. $this->sensors['wind_speed_10']['metric_code'];
                
				if ($this->sensors['wind_speed_10']['metric_code'] != 'meter_per_second')
				{
					$expl_val .= ' = '. $tmp1 .' meter_per_second';
                }
                
				$expl_val .= ' ~ round('. $tmp1 .' * 10^1) = '. $tmp;
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '111111111111';
            $expl_val = 'No Wind Speed sensor';   
        }
		
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 011002: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Wind speed, m/s, 12bits)';   
        $i++;         
        
		// 302043 -> 302042 -> 008021: Time significance
		// 5 bits. Metric = code table. Scale = 0. Ref =0 .
        $this->report_parts[$s][$i] = '11111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 008021: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time significance, 5bits)';   
        $i++; 
        
		// 302043 -> 302042 -> 004025 (Repetition #1): Time of preiod or displacement
		// 12 bits. Metric = minute. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 004025 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time of preiod or displacement, min, 12 bits)';   
        $i++;          
        
		// 302043 -> 302042 -> 011043 (Repetition #1): Maximum wind gust direction
		// 9 bits. Metric = degree. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 011043 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Maximum wind gust direction, degree, 9 bits)';   
        $i++; 
        
		// 302043 -> 302042 -> 011041 (Repetition #1): Maximum wind gust speed
		// 12 bits. Metric = m/s. Scale = 1. Ref = 0.
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 011041 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Maximum wind gust speed, 12 bits)';   
        $i++;     
        
		// 302043 -> 302042 -> 004025 (Repetition #2): Time of preiod or displacement
		// 12 bits. Metric = minute. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 004025 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time of preiod or displacement, min, 12 bits)';   
        $i++;          
        
		// 302043 -> 302042 -> 011043 (Repetition #2): Maximum wind gust direction
		// 9 bits. Metric = degree. Scale = 0. Ref = 0.	
        $this->report_parts[$s][$i] = '111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 011043 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Maximum wind gust direction, degree, 9 bits)';   
        $i++; 
        
		// 302043 -> 302042 -> 011041 (Repetition #2): Maximum wind gust speed
		// 12 bits. Metric = m/s. Scale = 1. Ref = 0.
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302043 -> 302042 -> 011041 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Maximum wind gust speed, 12 bits)';   
        $i++;         
        
        // ADDED BLOCK!
		// 302043 -> 007032: Height of sensor above local ground
		// 16 bits. Metric = m. Scale = 2. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111111111';
        $this->explanations[$s][$i][] = '302043 -> 007032: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Height of sensor above local ground (or deck of marine platform, 16 bits)';   
        $i++;          
        // END OF ADDED BLOCK!
        
        // 3 02 044: Evaporation data 
		// 302044 -> 004024: Time period or displacement
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302044 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time period or displacement, Hour, 12 bits)';   
        $i++; 
        
		// 302044 -> 002004: Type of instrumentation for evaporation measurement or type of crop for which evapotranspiration is reported
		// 4 bits. Metric = code table. Scale = 0. Ref = 0.
        $this->report_parts[$s][$i] = '1111';
        $this->explanations[$s][$i][] = '302044 -> 002004: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Type of ..., 4bits)';   
        $i++;
        
		// 302044 -> 013033: Evaporation/evapotranspiration
		// 10 bits. Metric = kg/m2. Scale = 1. Ref = 0.
        $this->report_parts[$s][$i] = '1111111111';
        $this->explanations[$s][$i][] = '302044 -> 013033: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing (Evaporation/evapotranspiration, 10bits)</span>';   
        $i++;
        
        // 3 02 045: Radiation data (from 1 hour and 24 hour period)
		// 302045 -> 004024 (Repetition #1): Time period or displacement
		// 12 bits. Metric = hour. Scale = 0. Ref = 2048.
        $this->report_parts[$s][$i] = $this->prepare_binary_value(2047, 12);
        $this->explanations[$s][$i][] = '302045 -> 004024 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>-1 - (-2048) = 2047</span> (Time period or displacement, Hour, 12 bits)';   
        $i++;
        
		// 302045 -> 014002 (Repetition #1): Long-wave radiation
		// 17 bits. Metric: J/m2. Scale = -3. Ref = -65536.
        $this->report_parts[$s][$i] = '11111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014002 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Long-wave radiation, 17 bits)';   
        $i++;
        
		// 302045 -> 014004 (Repetition #1): Short-wave radiation
		// 17 bits. Metric: J/m2. Scale = -3. Ref = -65536.
        $this->report_parts[$s][$i] = '11111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014004 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Short-wave radiation, 17 bits)';   
        $i++;  
        
		// 302045 -> 014016 (Repetition #1): Net radiation
		// 15 bits. Metric: J/m2. Scale = -4. Ref = -16384
        $this->report_parts[$s][$i] = '111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014016 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Net radiation, 15 bits)';   
        $i++;
        
		// 302045 -> 014028 (Repetition #1): Global solar radiation
		// 20 bits. Metric = J/m2. Scale = -2. Ref = 0.
        if (isset($this->sensors['solar_radiation_in_period']) && $this->sensors['solar_radiation_in_period']['sensor_feature_period'])
		{
            if ($this->sensors['solar_radiation_in_period']['is_m'])
			{
                $this->report_parts[$s][$i] = '11111111111111111111';
                $expl_val = 'Last data about Sun Radiation is unavailable';                
            } 
			else
			{
                // create object of Solar Radiation Handler
                $handler_obj = SensorHandler::create($this->sensors['solar_radiation_in_period']['handler_id_code']);
				// get total radiation in last 1 hr
                $last_1_hr = $handler_obj->getTotalInLastXHr($this->sensors['solar_radiation_in_period']['sensor_feature_id'], $script_runtime, 1, false)['total'];
                
                $tmp1 = It::convertMetric($last_1_hr, $this->sensors['solar_radiation_in_period']['metric_code'], 'joule_per_sq_meter');
                $tmp = round($tmp1/100);            

                $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 20);
                $expl_val = $tmp1 .' '. $this->sensors['solar_radiation_in_period']['metric_code'] .' (during 60 min)';
                
				if ($this->sensors['solar_radiation_in_period']['metric_code'] != 'joule_per_sq_meter')
				{
					$expl_val .= ' = '. $tmp1 .' joule_per_sq_meter (during 60 min)'; 
                }

                $expl_val.= ' ~ round('. $tmp1 .' * 10^(-2)) = '. $tmp;
            }
        }
		else
		{
            $this->report_parts[$s][$i] = '11111111111111111111';
            $expl_val = 'No Sun Radiation sensor';   
        }
		
        $this->explanations[$s][$i][] = '302045 -> 014028 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (Global solar radiation, J/m2, 20 bits)';   
        $i++;
       
		// 302045 -> 014029 (Repetition #1): Diffuse solar radiation
		// 20 bits. Metric = J/m2. Scale = -2. Ref = 0.
        $this->report_parts[$s][$i] = '11111111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014029 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Diffuse solar radiation, 20 bits)';   
        $i++;
        
		// 302045 -> 014030 (Repetition #1): Direct solar radiation
		// 20 bits. Metric = J/m2. Scale = -2. Ref = 0.
        $this->report_parts[$s][$i] = '11111111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014030 (Repetition #1): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Direct solar radiation, 20 bits)';   
        $i++;         
        
		// 302045 -> 004024 (Repetition #2): Time period or displacement (24 hr only for report at 00:00. Miss for other times)
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048
        $this->report_parts[$s][$i] = ($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '011111101000' : '111111111111';
        $this->explanations[$s][$i][] = '302045 -> 004024 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>'. (($script_runtime_hour == '06' && $script_runtime_minute == '00') ? '(-24) - (-2048) = 2024' : 'Missing, It is not 06UTC') .'</span> (Time period or displacement, Hour, 12 bits)';
        $i++;
        
		// 302045 -> 014002 (Repetition #2): Long-wave radiation
		// 17 bits. Metric: J/m2. Scale = -3. Ref = -65536.
        $this->report_parts[$s][$i] = '11111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014002 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Long-wave radiation, 17 bits)';   
        $i++;
        
		// 302045 -> 014004 (Repetition #2): Short-wave radiation
		// 17 bits. Metric: J/m2. Scale = -3. Ref = -65536.
        $this->report_parts[$s][$i] = '11111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014004 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Short-wave radiation, 17 bits)';   
        $i++;  
        
		// 302045 -> 014016 (Repetition #2): Net radiation
		// 15 bits. Metric: J/m2. Scale = -4. Ref = -16384
        $this->report_parts[$s][$i] = '111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014016 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Net radiation, 15 bits)';   
        $i++;
        
		// 302045 -> 014028 (Repetition #2): GLOBAL SOLAR RADIATION
		// 20 bits. Metric = J/m2. Scale = -2. Ref = 0.
        if (isset($this->sensors['solar_radiation_in_period']))
		{
            if ($script_runtime_hour == '06' && $script_runtime_minute == '00')
			{
				// create object of Solar Radiation Handler
                $handler_obj = SensorHandler::create($this->sensors['solar_radiation_in_period']['handler_id_code']);
                
				// get total radiation in last 24 hr
                $last_24_hr = $handler_obj->getTotalInLastXHr($this->sensors['solar_radiation_in_period']['sensor_feature_id'], $script_runtime, 24, false)['total'];
                $tmp1 = It::convertMetric($last_24_hr, $this->sensors['solar_radiation_in_period']['metric_code'], 'joule_per_sq_meter');
                $tmp = round($tmp1 / 100);       

                $this->report_parts[$s][$i] = $this->prepare_binary_value($tmp, 20); 
                $expl_val = $last_24_hr .' '. $this->sensors['solar_radiation_in_period']['metric_code'];   
                
				if ($this->sensors['solar_radiation_in_period']['metric_code'] != 'joule_per_sq_meter') 
				{
					$expl_val .= ' = '. $tmp1 . ' joule_per_sq_meter';
                }
				
                $expl_val.= ' ~ round('. $tmp1 .' * 10^(-2)) = '. $tmp;
            } 
			else 
			{
                $this->report_parts[$s][$i] = '11111111111111111111';
                $expl_val = '24hr radiation can be calculated only at 06UTC';                 
            }
        } 
		else
		{
            $this->report_parts[$s][$i] = '11111111111111111111';
            $expl_val = 'No Sun Radiation sensor';   
        }
		
        $this->explanations[$s][$i][] = '302045 -> 014028 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$expl_val.'</span> (GLOBAL SOLAR RADIATION, J/m2, 20 bits)';   
        $i++;
       
		// 302045 -> 014029 (Repetition #2): Diffuse solar radiation
		// 20 bits. Metric = J/m2. Scale = -2. Ref = 0.
        $this->report_parts[$s][$i] = '11111111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014029 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Diffuse solar radiation, 20 bits)';   
        $i++;
        
		// 302045 -> 014030 (Repetition #2): Direct solar radiation
		// 20 bits. Metric = J/m2. Scale = -2. Ref = 0.	
        $this->report_parts[$s][$i] = '11111111111111111111';
        $this->explanations[$s][$i][] = '302045 -> 014030 (Repetition #2): <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Direct solar radiation, 20 bits)';   
        $i++;          
        
        // 3 02 046: Temperature change
		// 302046 -> 004024: Time period or displacement
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302046 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time period or displacement, Hour, 12 bits)';   
        $i++;
        
		// 302046 -> 004024: Time period or displacement
		// 12 bits. Metric = hour. Scale = 0. Ref = -2048.
        $this->report_parts[$s][$i] = '111111111111';
        $this->explanations[$s][$i][] = '302046 -> 004024: <span>'.$this->report_parts[$s][$i].'</span> => <span>Missing</span> (Time period or displacement, Hour, 12 bits)';   
        $i++;      
        
        // 302046 -> 012049: Temperature change over period specified
		// 6 bits. Metric = K. Scale = 0. Ref = -30.
		if (isset($this->sensors['temperature']) && isset($sensors_24hr_ago['temperature']))
		{
			if ($script_runtime_minute == '00' && in_array($script_runtime_hour, array('00', '03', '06', '09', '12', '15', '18', '21')))
			{
				if ($this->sensors['temperature']['is_m'])
				{
					$this->report_parts[$s][$i] = '111111';
					$expl_val = 'Data about last Temperature is unavailable';                
				} 
				else if ($sensors_24hr_ago['temperature']['is_m']) 
				{
					$this->report_parts[$s][$i] = '111111';
					$expl_val = 'Data about 24h ago Temperature is unavailable';                   
				}
				else
				{
					$tmp1 = It::convertMetric($this->sensors['temperature']['sensor_feature_value'], $this->sensors['temperature']['metric_code'], 'kelvin');
					$tmp2 = It::convertMetric($sensors_24hr_ago['temperature']['sensor_feature_value'], $sensors_24hr_ago['temperature']['metric_code'], 'kelvin');
					$str_tmp = abs(round($tmp1 - $tmp2)) + 30;

					$this->report_parts[$s][$i] = $this->prepare_binary_value($str_tmp, 6);

					$expl_val = 'Last Tempr = '. round($this->sensors['temperature']['sensor_feature_value'], 1) .' '. $this->sensors['temperature']['metric_code']. ' ('. round($tmp1, 1) .' kelvin)';
					$expl_val .= '; 24h ago Tempr = '. round($sensors_24hr_ago['temperature']['sensor_feature_value'], 1) .' '. $sensors_24hr_ago['temperature']['metric_code'] .' ('. round($tmp2, 1) .' kelvin)';

					$expl_val .= '; |Last - 24h| = |'. ($tmp1 - $tmp2) .'| ~ '. abs(round($tmp1 - $tmp2));
					$expl_val .= '; Apply ref.value -30: <span>'. (abs(round($tmp1 - $tmp2)) + 30) .'</span>';   
				}
			}
			else
			{
				$this->report_parts[$s][$i] = '111111';
				$expl_val = '<span>It is not 06UTC. Total 24h Sunshine is calculated only at 06:00UTC</span>'; 
			}
        }
		else
		{
            $this->report_parts[$s][$i] = '111111';
            $expl_val = 'No Temperature sensor';
        }  
		
		$this->explanations[$s][$i][] = '302046 -> 012049: <span>'.$this->report_parts[$s][$i].'</span> => '.$expl_val.' (Temperature change over period specified, K, 6 bits)';
        $i++;
        
        $concatenated = implode('', $this->report_parts[$s]);
        
        $reminder = fmod(strlen($concatenated), 8); 
		
        $this->report_parts[$s][$i] = str_pad('0', (8 - $reminder), '0', STR_PAD_LEFT);
        $this->explanations[$s][$i][] = 'Extra <span>'. (8 - $reminder) .'</span> bits to make int count of octets in section 4: <span>'.$this->report_parts[$s][$i].'</span>';
                
        $s = 4;
        $i = 0;         
        
        $concatenated = implode('', $this->report_parts[$s]);
        $total = strlen($concatenated) / 8;
        
		/// ??? RECALCULATE !!!!
        $this->report_parts[$s][$i] = $this->prepare_binary_value($total, 24);
        $this->explanations[$s][$i][0] = 'Octets 1-3: <span>'.$this->report_parts[$s][$i].'</span> => <span>'.$total.'</span> (length of section4 in octets)';
        
		$i++;       
    }
}

?>
