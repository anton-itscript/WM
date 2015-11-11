<?php

/**
 * This class contains owm implementation of generate() and prepareReportComplete() functions of WeatherReport class.
 * 
 */
class SynopWeatherReport extends WeatherReport
{
    public function load($schedule_processed_id)
	{
        parent::load($schedule_processed_id);
        
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
    
    
    /*
     * Generation of SYNOP report is based on data from the last message (UTC time)
     * Source documentation can be found here: 
     * Dropbox\Delairco_Share\input_docs\BUFR & SYNOP\SYNOP\SYNOP (FM 12–XIV) 2011-10-11.doc
     * 
     * generate() function prepares value for each data item for 3 lines: 
	 * section #0, 
	 * section #1, 
	 * section #3. 
	 * Also it prepares explanation why it put particular value for 
	 * particular item. 
	 * PS: if measurement data is unknown or sensor doesn't exist at station  
	 * then “/” is used.
     */
    public function generate()
	{
        $this->_logger->log(__METHOD__);
        
        $current_user_timezone = date_default_timezone_get();
        
		$timezone_id = 'UTC';

		if ($timezone_id !=  $current_user_timezone)
		{
			TimezoneWork::set($timezone_id);
        }
        if(is_null($this->listener_log_info))
            return false;
        $this->_logger->log(__METHOD__."this->listener_log_info->log_id ".$this->listener_log_info->log_id);
        $this->sensors      = $this->prepareSensorsInfo($this->listener_log_info->log_id);
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
        
        $measuring_timestamp = strtotime($this->listener_log_info->measuring_timestamp);
        
		$measuring_day       = gmdate('d', $measuring_timestamp);
        $measuring_hour      = gmdate('H', $measuring_timestamp);
        $measuring_minute    = gmdate('i', $measuring_timestamp);
        
        $section = 0;
        $i = 0;
      
        // Section #0 - #0 
        // CONSTANT
        $this->report_parts[$section][$i]   = 'AAXX';
        $this->explanations[$section][$i][] = '<span>AAXX</span>: Fixed';
        
        $i++;
        $this->explanations[$section][$i][] = ' ';
        
        
        // Section #0 - #1 
        // Day of the month (UTC), with 01 indicating the first day, 02 the second day, 
        // etc.: @DTKY01111229 => 29
        $this->report_parts[$section][$i]   = $measuring_day;
        $this->explanations[$section][$i][] = "<span>".$this->report_parts[$section][$i]."</span>: Day of the month. Measurement timestamp = '". $this->listener_log_info->measuring_timestamp ." UTC'";
        
        // Section #0 - #2 
        // Actual time of observation, to the nearest whole hour UTC (Barometer reading time) 
        // (round up or down to nearest hour): 
        // @DTKY011112290653 => 0653 => 07
        $this->report_parts[$section][$i] .= ($measuring_minute >= 30 ? ($measuring_hour + 1) : $measuring_hour);
        $this->explanations[$section][$i][] = "<span>".$measuring_hour."</span>: Actual time of observation, to the nearest whole hour UTC.". ($measuring_minute >= 30 ? (' +1 = '.($measuring_hour + 1).'. Because minutes >= 30' ) : '');


        // Section #0 - #3 
        // Indicator for source and units of wind speed (1 = m/s) => taken from table 1855 
        if (isset($this->sensors['wind_speed_10']))
		{
            if ($this->sensors['wind_speed_10']['metric_code'] === 'meter_per_second')
			{
				$this->report_parts[$section][$i]  .= '1';
				$this->explanations[$section][$i][] = "<span>1</span>: Indicator for source and units of wind speed. Sensor ".$this->sensors['wind_speed_10']['sensor_id_code']." metric is 'meter_per_second' (see table 1855)";
            } 
			else if ($this->sensors['wind_speed_10']['metric_code'] === 'knot')
			{
				$this->report_parts[$section][$i]  .= '4';
				$this->explanations[$section][$i][] = "<span>4</span>: Indicator for source and units of wind speed. Sensor ".$this->sensors['wind_speed_10']['sensor_id_code']." metric is 'knot' (see table 1855)";
            }
			else
			{
				$this->report_parts[$section][$i]  .= '/';
				$this->explanations[$section][$i][] = "<span>/</span>: Indicator for source and units of wind speed. Sensor ".$this->sensors['wind_speed_10']['sensor_id_code']." metric is '".$this->sensors['wind_speed_10']['metric_code']."' wich has not association in Table 1855";
            }
        }
		else
		{
            $this->report_parts[$section][$i]  .= '/';
            $this->explanations[$section][$i][] = "<span>/</span>: Indicator for source and units of wind speed. Station has not Wind Speed sensor";
        }
        
        $i++;
        $this->explanations[$section][$i][] = ' ';
        
        // Section #0 - #4 
        // WMO Block# (take from the station setup) => admin > stations > tky01 = 08 (make sure to add the 0's)
        $this->report_parts[$section][$i] = str_pad($this->station_info['wmo_block_number'], 2, '0', STR_PAD_LEFT);
        $this->explanations[$section][$i][] = '<span>'.$this->station_info['wmo_block_number'].'</span>: WMO Block#.';
        
        // Section #0 - #5 
        // WMO Station # (take from the station setup) => admin > stations > tky01 = 007
        $this->report_parts[$section][$i] .= str_pad($this->station_info['station_number'], 3, '0', STR_PAD_LEFT);
        $this->explanations[$section][$i][] = '<span>'.$this->station_info['station_number'].'</span>: WMO Station#.';
        
        $i++;
        $section = 1;
        
        
        // SECTION #1
        // Section #1 - #0 
        // Indicator for inclusion or omission of precipitation data (Table 1819): 
        // RN1038003018114366: there was rain, we only include in section 1
        $this->report_parts[$section][$i] = (isset($this->sensors['rain_in_period']) ? '1' : '0');
        $this->explanations[$section][$i][] = "<span>".(isset($this->sensors['rain_in_period']) ? '1' : '0')."</span>: Indicator for RAIN inclusion. The station has rain sensor ". $this->sensors['rain_in_period']['sensor_id_code'];
        
        
        // Section #1 - #1 
        // Indicator for type of station operation (manned or automatic) and for present and past weather data (1860): 
        // 6 (if automatic) or 3 (if manned).. 
        $this->report_parts[$section][$i] .= '6';
        $this->explanations[$section][$i][] = "<span>6</span>: Automatic station. Because all stations in this software are automatic.";        
        
        // If Cloud Amount Height is present then take it, otherwise - Cloud Depth Height
		$cloudHeightSensor = (isset($this->sensors['cloud_amount_height_1']) 
									? $this->sensors['cloud_amount_height_1'] 
									: (isset($this->sensors['cloud_height_height_1']) 
											? $this->sensors['cloud_height_height_1']
											: null));
		
        // Section #1 - #2 
        // Height above surface of the base of the lowest cloud seen
        if (!is_null($cloudHeightSensor))
		{
			if ($cloudHeightSensor['is_m'] == 1)
			{
				$this->report_parts[$section][$i] .= '/';
				$this->explanations[$section][$i][] = "<span>/</span>: Height above surface of the base of the lowest cloud seen. Because Cloud Sensor height#1 value is unavailable";
			}
			else
			{
				$tmp_data = It::convertMetric($cloudHeightSensor['sensor_feature_value'], $cloudHeightSensor['metric_code'], 'meter');
				$tmp_data2 = $this->_get_cloud_height_code($tmp_data);

				$this->report_parts[$section][$i] .= $tmp_data2;
				$this->explanations[$section][$i][] = "<span>". $tmp_data2 ."</span>: Height above surface of the base of the lowest cloud seen. (Cloud Height #1 = ". round($cloudHeightSensor['sensor_feature_value'], 1) ." ". $cloudHeightSensor['metric_code'] ." = ". round($tmp_data, 1) ." meter, see table 1600.)";
			}
        }
		else
		{
            $this->report_parts[$section][$i] .= '/';
            $this->explanations[$section][$i][] = "<span>/</span>: Height above surface of the base of the lowest cloud seen. Because no Cloud Sensor";
        }
        
		// Section #1 - #3 
        // Horizontal visibility at surface 
        if (isset($this->sensors['visibility_1']))
		{
            if ($this->sensors['visibility_1']['is_m'])
			{
				$this->report_parts[$section][$i] .= '//';
				$this->explanations[$section][$i][] = "<span>//</span>: Horizontal visibility at surface. Because value of Visibility Sensor is unavailable.";
            }
			else
			{
                $tmp_data = It::convertMetric($this->sensors['visibility_1']['sensor_feature_value'], $this->sensors['visibility_1']['metric_code'], 'kilometer');
                $tmp_data2 = $this->_get_visibility_code($tmp_data);
                
				$this->report_parts[$section][$i] .= $tmp_data2;
                $this->explanations[$section][$i][] = "<span>".$tmp_data2."</span>: Horizontal visibility at surface. (Visibility = ".round($this->sensors['visibility_1']['sensor_feature_value'], 1)." ". $this->sensors['visibility_1']['metric_code']." = ".round($tmp_data, 1)." km, See table 4377) ";
            }
        }
		else
		{
			$this->report_parts[$section][$i] .= '//';
			$this->explanations[$section][$i][] = "<span>//</span>: Horizontal visibility at surface. Because no Visibility Sensor";
        }
		
        $i++; 
        $this->explanations[$section][$i][] = ' ';
        
        // Section #1 - #4 
        // Cloud cover (2700)
        if (isset($this->sensors['cloud_amount_amount_total']))
		{
            if ($this->sensors['cloud_amount_amount_total']['is_m'])
			{
                $this->report_parts[$section][$i] = '/';
                $this->explanations[$section][$i][] = "<span>/</span>: Cloud cover. Because value of Cloud Sensor is unavailable";
            } 
			else
			{
                $tmp_data = $this->_get_cloud_cover_code(round($this->sensors['cloud_amount_amount_total']['sensor_feature_value'],1));
                
				$this->report_parts[$section][$i] = $tmp_data;
                $this->explanations[$section][$i][] = "<span>".$tmp_data."</span>: Cloud cover. (Cloud Amount #1 = ".round($this->sensors['cloud_amount_amount_total']['sensor_feature_value'])."/8. See code table 2700)";            
            }
        }
		else
		{
			$this->report_parts[$section][$i] = '/';
			$this->explanations[$section][$i][] = "<span>/</span>: Cloud cover. Because no info about Cloud Amount";
        }
        
        // Section #1 - #5 
        // True Wind Direction (Table 0877)
        // Example: WD11245 => 245 degrees => table 0877 => 25 
        if (isset($this->sensors['wind_direction_10'])) 
		{
            if ($this->sensors['wind_direction_10']['is_m'])
			{
                $this->report_parts[$section][$i]  .= '//';
                $this->explanations[$section][$i][] = "<span>//</span>: True Wind Direction. Because value of Wind Direction sensor is unavailable.";
            }
			else
			{
                $handler_obj = SensorHandler::create($this->sensors['wind_direction_10']['handler_id_code']);
                
				$value = $handler_obj->applyOffset($this->sensors['wind_direction_10']['sensor_feature_value'], $this->station_info['magnetic_north_offset']);            
                $value = It::convertMetric($value, $this->sensors['wind_direction_10']['metric_code'], 'degree');
                
				$tmp_val = $this->_get_wind_direction_code($value);
                
				$this->report_parts[$section][$i]  .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: True Wind Direction. Because value of ".$this->sensors['wind_direction_10']['sensor_id_code'].' = '.$value.' degree. See table 0877.';
            }
        } 
		else 
		{
			$this->report_parts[$section][$i]  .= '//';
			$this->explanations[$section][$i][] = "<span>//</span>: True Wind Direction. Because no Wind Direction sensor";
		}        
        
        // Section #1 - #6 
        // Wind Speed in Units (rounded meter per second )
        // Example: WS23069006540632 => instantaneous = 0690
        if (isset($this->sensors['wind_speed_10']))
		{
            if ($this->sensors['wind_speed_10']['is_m'])
			{
                $this->report_parts[$section][$i]  .= '//';
                $this->explanations[$section][$i][] = "<span>//</span>: Because value of Wind Speed sensor is unavailable.";
            }
			else
			{
                $value = It::convertMetric($this->sensors['wind_speed_10']['sensor_feature_value'], $this->sensors['wind_speed_10']['metric_code'], 'meter_per_second');
                
				$tmp_val = round($value);
                $tmp_val = substr($tmp_val, -2);
                $tmp_val = str_pad($tmp_val, 2, '0', STR_PAD_LEFT);                
                
				$this->report_parts[$section][$i]  .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span> m/s: Wind Speed in Units. Because value of ".$this->sensors['wind_speed_10']['sensor_id_code']." = ".$this->sensors['wind_speed_10']['sensor_feature_value']." ".$this->sensors['wind_speed_10']['metric_code'];
            }
        }
		else
		{
			$this->report_parts[$section][$i]  .= '//';
			$this->explanations[$section][$i][] = "<span>//</span>: Wind Speed in Units. Because no Wind Speed sensor.";
        }  
        
        $i++;         
        $this->explanations[$section][$i][] = ' ';
        
        
        // Section #1 - #7 
        // FIXED
        $this->report_parts[$section][$i] = '1';
        $this->explanations[$section][$i][] = '<span>1</span>: Fixed.';
        
        
        // Section #1 - #8 - Sign of the data (Code table 3845), in this case relative to temperature (Celsius): TP11772
        // Section #1 - #9 - Air temperature, in tenths of a degree Celsius (observed): TP11772
        if (isset($this->sensors['temperature']))
		{
            if ($this->sensors['temperature']['is_m'])
			{
                $this->report_parts[$section][$i] .= '/';
                $this->explanations[$section][$i][] = "<span>/</span>: Sign of the temperature data (Celsius). Because value of Temperature Sensor is unavailable.";
                
				$this->report_parts[$section][$i] .= '///';
                $this->explanations[$section][$i][] = "<span>///</span>: Air temperature, in tenths of a degree Celsius. Because value Temperature Sensor is unavailable.";
            } 
			else 
			{
                $value = It::convertMetric($this->sensors['temperature']['sensor_feature_value'], $this->sensors['temperature']['metric_code'], 'celsius');
                $tmp_val = ($value >= 0 ? '0' : '1');
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Sign of the temperature data (Celsius). Because value of ".$this->sensors['temperature']['sensor_id_code']. " = ".$value." Celsius degree".($this->sensors['temperature']['metric_code'] != 'celsius' ? ' ('.$this->sensors['temperature']['sensor_feature_value'].' '. $this->sensors['temperature']['metric_code'].')' : '').". See table 3845.";

                $tmp_val = abs(round($value*10));
                $tmp_val = substr($tmp_val, -3);
                $tmp_val = str_pad($tmp_val, 3, '0', STR_PAD_LEFT);
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Air temperature, in tenths of a degree Celsius. Because value of ".$this->sensors['temperature']['sensor_id_code']. " = ".$value." Celsius degree".($this->sensors['temperature']['metric_code'] != 'celsius' ? ' ('.$this->sensors['temperature']['sensor_feature_value'].' '. $this->sensors['temperature']['metric_code'].')' : '')."";
            }
        }
		else
		{
            $this->report_parts[$section][$i] .= '/';
            $this->explanations[$section][$i][] = "<span>/</span>: Sign of the temperature data (Celsius). Because no Temperature Sensor.";
            $this->report_parts[$section][$i] .= '///';
            $this->explanations[$section][$i][] = "<span>///</span>: Air temperature, in tenths of a degree Celsius. Because no Temperature Sensor.";
        }    
        
        $i++;
        $this->explanations[$section][$i][] = " ";
        
        // Section #1 - #10 FIXED
        $this->report_parts[$section][$i] = '2';
        $this->explanations[$section][$i][] = "<span>2</span>: Fixed.";
        
        
        // Section #1 - #11 - Sign of the data (Code table 3845), in this case relative to DewPoint or Humidity (if there is now DewPoint)
        // Section #1 - #12 - Dew Point (include if calculated), else Humidity (HU in case sn =9) (observed) => no DewPoint or Humidity.. Can we exclude it? Have to add '///'? Can we exclude 'Sn' as well?
        if (isset($this->calculations['DewPoint']))
		{
            $tmp_val = $this->calculations['DewPoint']['value'] >= 0 ? '0' : '1';
            
			$this->report_parts[$section][$i] .= $tmp_val;
            $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Sign of the data (Code table 3845), in this case relative to DewPoint or Humidity (if there is no DewPoint). Because 1) we have DewPoint; 2) DewPoint value is = ".$this->calculations['DewPoint']['value'];
            
            $tmp_val = round(abs($this->calculations['DewPoint']['value']*10));
            $tmp_val = substr($tmp_val, -3);
            $tmp_val = str_pad($tmp_val, 3, '0', STR_PAD_LEFT);
            
			$this->report_parts[$section][$i] .= $tmp_val;
            $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Dew Point (include if calculated), else Humidity (HU in case sn=9). Because we have Dew Point calculation and its vaue = ".$this->calculations['DewPoint']['value'];
            
        } 
		else if (isset($this->sensors['humidity']) && !$this->sensors['humidity']['is_m'])
		{
            $this->report_parts[$section][$i] .= '9';
            $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Sign of the data (Code table 3845), in this case relative to DewPoint or Humidity (if there is no DewPoint). Because 1) we have don't have DewPoint, but we have Humidity; 2) result is 9 according to table 3845.";
            
            $tmp_val = round($this->sensors['humidity']['sensor_feature_value']);
            $tmp_val = substr($tmp_val, -3);
            $tmp_val = str_pad($tmp_val, 3, '0', STR_PAD_LEFT);
            
			$this->report_parts[$section][$i] .= $tmp_val;
            $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Dew Point (include if calculated), else Humidity (HU in case sn=9). Because we don't have DewPoint calculation, but we have Humidity Sensor ".$this->sensors['humidity']['sensor_id_code'].' with value = '.$this->sensors['humidity']['sensor_feature_value'].'%';
        } 
		else
		{
            $this->report_parts[$section][$i] .= '/';
            $this->explanations[$section][$i][] = "<span>/</span>: Sign of the data (Code table 3845), in this case relative to DewPoint or Humidity (if there is no DewPoint). Because Station has not DewPoint Calculation, Humidity Sensor";
            $this->report_parts[$section][$i] .= '///';
            $this->explanations[$section][$i][] = "<span>///</span>: Dew Point (include if calculated), else Humidity (HU in case sn=9). Because Station has not DewPoint Calculation, Humidity Sensor";
        }
		
        $i++;
        $this->explanations[$section][$i][] = " ";
        
        // Section #1 - #13 
        // FIXED
        $this->report_parts[$section][$i] = '3';
        $this->explanations[$section][$i][] = "<span>3</span>: Fixed.";               
        
        // Section #1 - #14 
        // Pressure at station level, in tenths of a hectopascal, omitting thousands 
        // digit of hectopascals of the pressure value. (observed)
        // Example: PR105090
        if (isset($this->sensors['pressure']))
		{
            if ($this->sensors['pressure']['is_m'])
			{
                $this->report_parts[$section][$i] .= '////';
                $this->explanations[$section][$i][] = "<span>////</span>: Pressure at station level, in tenths of a hectopascal, omitting thousands digit of hectopascals of the pressure value. Because value of Pressure Sensor is unavailable.";
            }
			else
			{
                $value = It::convertMetric($this->sensors['pressure']['sensor_feature_value'], $this->sensors['pressure']['metric_code'], 'hpascal');

                $tmp_val = round($value*10);
                $tmp_val = substr($tmp_val, -4);
                $tmp_val = str_pad($tmp_val, 4, '0', STR_PAD_LEFT);
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Pressure at station level, in tenths of a hectopascal, omitting thousands digit of hectopascals of the pressure value. Because value of Pressure Sensor ".$this->sensors['pressure']['sensor_id_code'].' = '.$this->sensors['pressure']['sensor_feature_value'].' '. $this->sensors['pressure']['metric_code'].($this->sensors['pressure']['metric_code'] != 'hpascal' ? ' ('.$value.' hPa)':'');
            }
        }
		else
		{
            $this->report_parts[$section][$i] .= '////';
            $this->explanations[$section][$i][] = "<span>////</span>: Pressure at station level, in tenths of a hectopascal, omitting thousands digit of hectopascals of the pressure value. Because no Pressure Sensor.";
        }
        
        $i++;        
        $this->explanations[$section][$i][] = " ";
        
        // Section #1 - #15 FIXED
        $this->report_parts[$section][$i] = '4';
        $this->explanations[$section][$i][] = "<span>4</span>: Fixed.";
        
        // Section #1 - #16 - Pressure at mean sea level, in tenths of a hectopascal, omitting thousands digit of hectopascals of the pressure value. (assuming accurate adjustment to sea level in possible) (Calculated)
        if (isset($this->calculations['PressureSeaLevel']))
		{
            $tmp_val = round(abs($this->calculations['PressureSeaLevel']['value']*10));
            $tmp_val = substr($tmp_val, -4);
            $tmp_val = str_pad($tmp_val, 4, '0', STR_PAD_LEFT);
            
			$this->report_parts[$section][$i] .= $tmp_val;
            $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Pressure at mean sea level, in tenths of a hectopascal, omitting thousands digit of hectopascals of the pressure value. Because Pressure MSL=".$this->calculations['PressureSeaLevel']['value'];
        } 
		else
		{
			$this->report_parts[$section][$i] .= '////';
			$this->explanations[$section][$i][] = "<span>////</span>: Pressure at mean sea level, in tenths of a hectopascal, omitting thousands digit of hectopascals of the pressure value. Because no calculation Pressure MSL";
        }
        
        $i++;        
        $this->explanations[$section][$i][] = " ";
        
        // Section #1 - #17 - FIXED
        $this->report_parts[$section][$i] = '6';
        $this->explanations[$section][$i][] = "<span>6</span>: Fixed.";
        
        // Section #1 - #18
        // Amount of precipitation which has fallen during the period preceding the time of observation, as indicated by tR (Table 3590)
        if (isset($this->sensors['rain_in_period']))
		{
            if ($this->sensors['rain_in_period']['is_m'])
			{
                $this->report_parts[$section][$i] .= '///';
                $this->explanations[$section][$i][] = '<span>///</span>: Amount of precipitation which has fallen during the period preceding the time of observation. Because value of Rain Sensor is unavailable.';
            } 
			else 
			{
                $value = It::convertMetric($this->sensors['rain_in_period']['sensor_feature_value'], $this->sensors['rain_in_period']['metric_code'], 'millimeter');
                $tmp_val = $this->_get_rain_code($value);
                
				$this->report_parts[$section][$i] .= $tmp_val;
				$this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Amount of precipitation which has fallen during the period preceding the time of observation. Because value of Rain Sensor ".$this->sensors['rain_in_period']['sensor_id_code']." = ".$this->sensors['rain_in_period']['sensor_feature_value'].' '. $this->sensors['rain_in_period']['metric_code'].', see table 3590 to understand result.';
            }
        }
		else
		{
			$this->report_parts[$section][$i] .= '///';
			$this->explanations[$section][$i][] = '<span>///</span>: Amount of precipitation which has fallen during the period preceding the time of observation. Because no Rain sensor';
        }
        
        // Section #1 - #19 
        // Duration of period of reference for amount of precipitation (Table 4019);  
        // This depends upon the period of transmission of SYNOP. If hourly transmission then the data should be for 1 hour.
        $tmp_val = $this->_get_duration_of_synop_transmission($this->schedule_info->period);
        
		$this->report_parts[$section][$i] .= $tmp_val;
        $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Duration of period of reference for amount of precipitation. Because period of SYNOP transmission is ". $this->schedule_info->period.' minutes, see table 4019 to understand result';
        
		$i++;
        
        // Section #2 is not included
        // Section #3 - #20 - FIXED
        $section = 3;
        $this->report_parts[$section][$i] = '333';
        $this->explanations[$section][$i][] = '<span>333</span>: Fixed.';
        
        $i++;
        $this->explanations[$section][$i][] = " ";
        
        $this->report_parts[$section][$i] = '5';
        $this->explanations[$section][$i][] = "<span>5</span>: Fixed.";
        
        //$i++;
        if ((!isset($this->sensors['solar_radiation_in_period']) || $this->sensors['solar_radiation_in_period']['is_m']) && (!isset($this->sensors['sun_duration_in_period']) || $this->sensors['sun_duration_in_period']['is_m']))
		{
			$this->report_parts[$section][$i] = '/////////';
			$this->explanations[$section][$i][] = "<span>/////////</span>: No Solar Radiation, no Sun Duration sensor.";
        }
		else
		{
            $this->report_parts[$section][$i] = '53';
            $this->explanations[$section][$i][] = "<span>53</span>: Is fixed.";
            
            if (isset($this->sensors['solar_radiation_in_period']))
			{
                $tmp_val = ceil($this->sensors['solar_radiation_in_period']['sensor_feature_period']/6);
                $tmp_val = substr($tmp_val, -2);
                $tmp_val = str_pad($tmp_val, 2, '0', STR_PAD_LEFT);
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Duration of period for Solar Radiation ".$this->sensors['solar_radiation_in_period']['sensor_id_code']." (".$this->sensors['solar_radiation_in_period']['sensor_feature_period']." / 6). Highest whole figure was taken.";
                
                $this->report_parts[$section][$i] .= '2';
                $this->explanations[$section][$i][] = "<span>2</span>: Fixed and = 2, because period of Solar Radiation ".$this->sensors['solar_radiation_in_period']['sensor_id_code']." was taken (Not period of Sun Duration). Period for Solar Radiation (".$this->sensors['solar_radiation_in_period']['sensor_feature_period']." / 6). Highest whole figure was taken.";
                
                $value = It::convertMetric($this->sensors['solar_radiation_in_period']['sensor_feature_value'], $this->sensors['solar_radiation_in_period']['metric_code'], 'kjoule_per_sq_meter');
                $tmp_val = substr(round($value), -4);
                $tmp_val = str_pad($tmp_val, 4, '0', STR_PAD_LEFT);
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Value of Solar Radiation Sensor ".$this->sensors['solar_radiation_in_period']['sensor_id_code']." = ".$this->sensors['solar_radiation_in_period']['sensor_feature_value'].' '.$this->sensors['solar_radiation_in_period']['metric_code']. ($this->sensors['solar_radiation_in_period']['metric_code'] != 'kjoule_per_sq_meter' ? ' ( = '.$value.' kjoule_per_sq_meter)' : '');
            }
			else
			{
                $tmp_val = ceil($this->sensors['sun_duration_in_period']['sensor_feature_period']/6);
                $tmp_val = substr($tmp_val, -2);
                $tmp_val = str_pad($tmp_val, 2, '0', STR_PAD_LEFT);
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Duration of period for Sun Duration ".$this->sensors['sun_duration_in_period']['sensor_id_code']." measurememt (".$this->sensors['sun_duration_in_period']['sensor_feature_period']." / 6). Highest whole figure was taken.";
                
                $this->report_parts[$section][$i] .= '3'; 
                $this->explanations[$section][$i][] = "<span>3</span>: Fixed and = 3, because period of Sun Duration ".$this->sensors['sun_duration_in_period']['sensor_id_code']." was taken (Not period of Solar Radiation). Period for Sun Duration (".$this->sensors['sun_duration_in_period']['sensor_feature_period']." / 6). Highest whole figure was taken.";
                
                $tmp_val = round($this->sensors['sun_duration_in_period']['sensor_feature_value']);
                $tmp_val = substr($tmp_val, -4);
                $tmp_val = str_pad($tmp_val, 4, '0', STR_PAD_LEFT);
                
				$this->report_parts[$section][$i] .= $tmp_val;
                $this->explanations[$section][$i][] = "<span>".$tmp_val."</span>: Value of Sun Duration Sensor ".$this->sensors['sun_duration_in_period']['sensor_id_code']." = ".$this->sensors['sun_duration_in_period']['sensor_feature_value'].' '.$this->sensors['sun_duration_in_period']['metric_code'].' minutes';
            }
        }
        
        return true;
    }
    
    public function prepareReportComplete()
	{
        $this->report_complete = '';

		if ($this->report_parts)
		{
			if ($this->schedule_info->report_format === 'txt')
			{
                foreach ($this->report_parts as $section => $data)
				{
                    $this->report_complete .= implode(' ', $data);
                    $this->report_complete .= "\n";
                }
            } 
			else
			{
                foreach ($this->report_parts as $section => $data)
				{
                    $this->report_complete .= implode(',', $data);
                    $this->report_complete .= "\n";
                }                
            }
        }
    }
    
	/*
	 * Code table 0877  - Direction in two figures
	 */    
    protected function _get_wind_direction_code($d)
	{
        if ($d == 0)
            return '00';
        if ($d >= 5 && $d <= 14)
            return '01';
        if ($d >= 15 && $d <= 24)
            return '02';
        if ($d >= 25 && $d <= 34)
            return '03';
        if ($d >= 35 && $d <= 44)
            return '04';
        if ($d >= 45 && $d <= 54)
            return '05';
        if ($d >= 55 && $d <= 64)
            return '06';
        if ($d >= 65 && $d <= 74)
            return '07';
        if ($d >= 75 && $d <= 84)
            return '08';
        if ($d >= 85 && $d <= 94)
            return '09';
        if ($d >= 95 && $d <= 104)
            return '10';
        if ($d >= 105 && $d <= 114)
            return '11';
        if ($d >= 115 && $d <= 124)
            return '12';
        if ($d >= 125 && $d <= 134)
            return '13';
        if ($d >= 135 && $d <= 144)
            return '14';
        if ($d >= 145 && $d <= 154)
            return '15';
        if ($d >= 155 && $d <= 164)
            return '16';
        if ($d >= 165 && $d <= 174)
            return '17';
        if ($d >= 175 && $d <= 184)
            return '18';
        if ($d >= 185 && $d <= 194)
            return '19';
        if ($d >= 195 && $d <= 204)
            return '20';        
        if ($d >= 205 && $d <= 214)
            return '21';
        if ($d >= 215 && $d <= 224)
            return '22';
        if ($d >= 225 && $d <= 234)
            return '23';
        if ($d >= 235 && $d <= 244)
            return '24';
        if ($d >= 245 && $d <= 254)
            return '25';
        if ($d >= 255 && $d <= 264)
            return '26';
        if ($d >= 265 && $d <= 274)
            return '27';
        if ($d >= 275 && $d <= 284)
            return '28';
        if ($d >= 285 && $d <= 294)
            return '29';
        if ($d >= 295 && $d <= 304)
            return '30';
        if ($d >= 305 && $d <= 314)
            return '31';       
        if ($d >= 315 && $d <= 324)
            return '32';
        if ($d >= 325 && $d <= 334)
            return '33';
        if ($d >= 335 && $d <= 344)
            return '34';
        if ($d >= 345 && $d <= 354)
            return '35';
        if ($d >= 355 && $d <= 4)
            return '36';
		
        return '99';        
    }
    
    protected function _get_rain_code($value)
	{
        if ($value == 0)
            return '000';
        
		$value = round($value, 1);

        if ($value < 1) 
		{
			return 990 + $value*10;
		}
		
        $value = round($value);
		
        if ($value >= 989) 
        {
			return 989;
		}
        
        return str_pad($value, 3, '0', STR_PAD_LEFT);
    }    
    
    protected function _get_duration_of_synop_transmission($d)
	{
        //
        /* 4019												
												
            tR Duration of period of reference for amount of precipitation, ending at the time of the report												

            Code												
            figure												
            1	Total precipitation during the 6 hours preceding the observation											
            2	Total precipitation during the 12 hours preceding the observation											
            3	Total precipitation during the 18 hours preceding the observation											
            4	Total precipitation during the 24 hours preceding the observation											
            5	Total precipitation during the 1 hour preceding the observation											
            6	Total precipitation during the 2 hours preceding the observation											
            7	Total precipitation during the 3 hours preceding the observation											
            8	Total precipitation during the 9 hours preceding the observation											
            9	Total precipitation during the 15 hours preceding the observation											

            N o t e s :												

            (1) If the duration of the period of reference is not covered by Code table 4019 or the period does not end												
            at the time of the report, tR shall be coded 0.												
            (2) Members are recommended to avoid any deviations from international practices which require the use of code												
            figure 0. The specification of code figure 0 should be indicated in Volume I I of the Manual on Codes under												
            national coding procedures.												
												
        */										
		
        if ($d == 360)  return '1';
        if ($d == 720)  return '2';
        if ($d == 1080) return '3';
        if ($d == 1440) return '4';
        if ($d == 60)   return '5';
        if ($d == 120)  return '6';
        if ($d == 180)  return '7';
        if ($d == 540)  return '8';
        if ($d == 960)  return '9';
        
        return '0';
    }
    
	/*
	 * Table 4377
	 * Horizontal visibility at surface
	 */
    protected function _get_visibility_code($v)
	{
        if ($v < 0.1) return '00';
        
		$vr = round($v,1);
        
        for ($i = 1; $i <= 50; $i++)
		{
            $compare = $i/10;
            
			if ($vr == $compare) 
			{
				return str_pad($i, 2, '0', STR_PAD_LEFT);
			}
        }

        $vr = floor($v);
		
        for ($i = 6; $i <= 30; $i++)
		{
			if ($vr == $i) return ($i+50);
        }
        
        if ($vr <= 35) return '81';
        if ($vr <= 40) return '82';
        if ($vr <= 45) return '83';
        if ($vr <= 50) return '84';
        if ($vr <= 55) return '85';
        if ($vr <= 60) return '86';
        if ($vr <= 65) return '87';
        if ($vr <= 70) return '88';
		
        return '89';
    }
    
    protected function _get_cloud_height_code($v)
	{
        /*
         * Table 1600
         * 0 0 to 50 m
         * 1 50 to 100 m
         * 2 100 to 200 m
         * 3 200 to 300 m
         * 4 300 to 600 m
         * 5 600 to 1 000 m
         * 6 1 000 to 1 500 m
         * 7 1 500 to 2 000 m
         * 8 2 000 to 2 500 m
         * 9 2 500 m or more, or no clouds
         * / Height of base of cloud not known or base of clouds at a level lower and tops at a level higher
         * than that of the station  
         */
        if ($v < 50) return '0';
        if ($v < 100) return '1';
        if ($v < 200) return '2';
        if ($v < 300) return '3';
        if ($v < 600) return '4';
        if ($v < 1000) return '5';
        if ($v < 1500) return '6';
        if ($v < 2000) return '7';
        if ($v < 2500) return '8';
		
        return '9';
    }
    
    protected function _get_cloud_cover_code($v)
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
}

?>
