<?php

/*
 * This is helper class. It parses XML file received from AWOS and converts it into regular message
 * 
 */

class ConvertXmlToMessages 
{
    public function process($path) 
	{
        if (!file_exists($path)) 
		{
            throw new Exception('Can\'t find file '. $path);
        }
        
        $pathinfo = pathinfo($path);
        $base_filename = $pathinfo['basename'];
        
        $xml_content = file_get_contents($path);
        
        if (!strlen($xml_content))
		{
			throw new Exception($base_filename ." is empty");
        }

        libxml_use_internal_errors(true);
        $sxe = simplexml_load_string($xml_content);
		
		$error_str = "";
        
		if ($sxe === false) 
		{
            foreach(libxml_get_errors() as $error)
			{
                $error_str .= "\n".$error->message;
            }
			
            throw new Exception($error_str);
        }        
        
		// XML must contain 1 RUNWAY tag
        if (count($sxe->RUNWAY) > 1)
		{
			throw new Exception('XML '.$base_filename.' contains '.count($sxe->RUNWAY).' RUNWAY tags');
        }
		
        if (count($sxe->RUNWAY) == 0)
		{
			throw new Exception('XML '.$base_filename.' doesn\'t contain RUNWAY tags');
        }       
        
		// RUNWAY's "NAME" attribute must be "08/26"
        if ($sxe->RUNWAY['NAME'] != '08/26')
		{
			throw new Exception('XML '. $base_filename .' RUNWAY name = "'. $sxe->RUNWAY['NAME'] .'", "08/26" was expected');
        }
        
		// RUNWAY must contain at least 1 ZONE tag
        if (count($sxe->RUNWAY->ZONE) == 0)
		{
			throw new Exception('XML '. $base_filename .' doesn\'t contain ZONE tags');
        }
        
		// XML must contain "UNITS" tag
        if (!isset($sxe->UNITS))
		{
            throw new Exception('XML '. $base_filename .' doesn\'t contain UNITS section');
        }
        
        $str = '';
		
        $possible_units = array(
            'WIND'      => 'kt',
            'VISBILITY' => 'meters',
            'RVR'       => 'meters',
            'ALTIMETER' => 'hpa'
        );
		
        foreach ($possible_units as $key => $value)
		{
            if (!isset($sxe->UNITS->$key))
			{
				$str .= ($str ? '; ' : '') .' UNITS['. $key. '] is missed';
            } 
			else if ($sxe->UNITS->$key != $value)
			{
				$str .= ($str ? '; ' : '').' unknown metric "'. $sxe->UNITS->$key .'" in UNITS['. $key .']';
            }
        }
		
        if ($str)
		{
			throw new Exception($str);
        }
        
        $result = array();
        $messages = array();
        
		for ($key = 0; $key < count($sxe->RUNWAY->ZONE); $key++)
		{
			// Get's Station ID
            if ($sxe->RUNWAY->ZONE[$key]['NAME'] == '08')
			{
				$messages[$key]['station_id_code'] = 'AWS08';
            }
			else if ($sxe->RUNWAY->ZONE[$key]['NAME'] == '26')
			{
				$messages[$key]['station_id_code'] = 'AWS26';
            } 
			else 
			{
				continue;
            }
            
			//Gets sensor's data from tags:
            // WIND SPEED
            if (isset($sxe->RUNWAY->ZONE[$key]->WSPD_5SEC))
			{
				$messages[$key]['sensors']['WindSpeed'][0]['wind_speed_1'] = (string)$sxe->RUNWAY->ZONE[$key]->WSPD_5SEC;
            }
            
			if (isset($sxe->RUNWAY->ZONE[$key]->WSPD_2MIN))
			{
				$messages[$key]['sensors']['WindSpeed'][0]['wind_speed_2'] = (string)$sxe->RUNWAY->ZONE[$key]->WSPD_2MIN;
            }
            
            // WIND DIRECTION
            if (isset($sxe->RUNWAY->ZONE[$key]->WDIR_5SEC))
			{
				$messages[$key]['sensors']['WindDirection'][0]['wind_direction_1'] = (string)$sxe->RUNWAY->ZONE[$key]->WDIR_5SEC;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->WDIR_2MIN))
			{
				$messages[$key]['sensors']['WindDirection'][0]['wind_direction_2'] = (string)$sxe->RUNWAY->ZONE[$key]->WDIR_2MIN;
            }            
            
            // TEMPERATURE
            if (isset($sxe->RUNWAY->ZONE[$key]->TEMP_5MIN))
			{
                $messages[$key]['sensors']['Temperature'][0]['temperature'] = (string)$sxe->RUNWAY->ZONE[$key]->TEMP_5MIN;
            }
            
            // HUMIDITY
            if (isset($sxe->RUNWAY->ZONE[$key]->HUM_5MIN))
			{
				$messages[$key]['sensors']['Humidity'][0]['humidity'] = (string)$sxe->RUNWAY->ZONE[$key]->HUM_5MIN;
            }            
            
            // PRESSURE
            if (isset($sxe->RUNWAY->ZONE[$key]->PRESSURE1))
			{
                $messages[$key]['sensors']['Pressure'][0]['pressure'] = (string)$sxe->RUNWAY->ZONE[$key]->PRESSURE1;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->PRESSURE2))
			{
				$messages[$key]['sensors']['Pressure'][1]['pressure'] = (string)$sxe->RUNWAY->ZONE[$key]->PRESSURE2;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->PRESSURE3))
			{
				$messages[$key]['sensors']['Pressure'][2]['pressure'] = (string)$sxe->RUNWAY->ZONE[$key]->PRESSURE3;
            }             

            // CLOUD
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDRANGE)) 
			{
				$messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_measuring_range'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDRANGE;
            }   
			
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDVV))
			{
				$messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_vertical_visibility'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDVV;
            }   
			
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDH1))
			{
				$messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_height_height_1'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDH1;
            } 
            
			if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDH2))
			{
                $messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_height_height_2'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDH2;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDH3))
			{
                $messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_height_height_3'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDH3;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDD1))
			{
                $messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_height_depth_1'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDD1;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDD2))
			{
                $messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_height_depth_2'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDD2;
            }
			
            if (isset($sxe->RUNWAY->ZONE[$key]->CLOUDD3))
			{
                $messages[$key]['sensors']['CloudHeightAWS'][0]['cloud_height_depth_3'] = (string)$sxe->RUNWAY->ZONE[$key]->CLOUDD3;
            }             
         
            // VISIBILITY
            $vis = (string)$sxe->RUNWAY->ZONE[$key]->EXC;
			
            if (isset($sxe->RUNWAY->ZONE[$key]->EXC) && is_numeric($vis) && ($vis != 0))
			{
                //P = (1/σ) x ln (1/0.05)
                //where ln is the log to base e or the natural logarithm. σ  is the extinction cooefficient.
                //This number will be in km, so we will need to multiply by 1000.
                $messages[$key]['sensors']['VisibilityAWS'][0]['visibility_1'] = (1 / $vis) * log(20) * 1000;
            }            
            
            // SOLAR 
            if (isset($sxe->RUNWAY->ZONE[$key]->SOLAR_1MIN))
			{
				$messages[$key]['sensors']['SolarRadiation'][0]['solar_radiation_in_period'] = (string)$sxe->RUNWAY->ZONE[$key]->SOLAR_1MIN;
            }
			
			// Rain fall
			if (isset($sxe->RUNWAY->ZONE[$key]->PRECIP_ACCUM))
			{
				$messages[$key]['sensors']['RainAws'][0]['period'] = 5;
                $messages[$key]['sensors']['RainAws'][0]['rain_in_period'] = (string)$sxe->RUNWAY->ZONE[$key]->PRECIP_ACCUM;
            }   

			// Sunshine duration
			if (isset($sxe->RUNWAY->ZONE[$key]->SUN_ACCUM))
			{
				$messages[$key]['sensors']['SunshineDuration'][0]['period'] = 5;
                $messages[$key]['sensors']['SunshineDuration'][0]['sun_duration_in_period'] = (string)$sxe->RUNWAY->ZONE[$key]->SUN_ACCUM;
            }		
        }

        if (!$messages)
		{
            $result[] = $base_filename ." : No datasets found";
            
			return implode("\n", $result);
        }
        
        $result[] = $base_filename ." : ". count($messages) .' datasets were found';
        
        $sql = "SELECT * 
                FROM `".Station::model()->tableName()."` `t1`
                WHERE `t1`.`station_id_code` IN ('AWS08', 'AWS26')";
		
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        
		if (!$res)
		{
            $result[] = "AWS08 and AWS26 are not exist in database, no sense to convert XML into messages";
            
			return implode("\n", $result);            
        }
        
		
		// for each stationID looks for sensors and features of this station
        $stations = array();
		
        foreach ($res as $key => $value)
		{
            $stations[$value['station_id_code']] = $value;
            
            $sql = "SELECT `t1`.`sensor_id_code`,
                           `t1`.`station_id`,
                           `t2`.`feature_code`,
                           `t3`.`code` AS `metric_code`,
                           `t4`.`handler_id_code`
                    FROM `".StationSensor::model()->tableName()."` `t1`
                    LEFT JOIN `".StationSensorFeature::model()->tableName()."` `t2` ON `t1`.`station_sensor_id` = `t2`.`sensor_id`
                    LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t3` ON `t3`.`metric_id` = `t2`.`metric_id`
                    LEFT JOIN `".SensorDBHandler::model()->tableName()."`      `t4` ON `t4`.`handler_id` = `t1`.`handler_id`
                    WHERE `t1`.`station_id` = '".$value['station_id']."'
                    ORDER BY `t4`.`handler_id_code` ASC, `t1`.`sensor_id_code` ASC";
			
            $res2 = Yii::app()->db->createCommand($sql)->queryAll();
            
			if ($res2)
			{
                $tmp = array();
				
                foreach ($res2 as $value2)
				{
					$tmp[$value2['handler_id_code']][$value2['sensor_id_code']][$value2['feature_code']] = $value2['metric_code'];
                }
				
                foreach ($tmp as $key_handler => $value_sensors)
				{
                    foreach ($value_sensors as $key_sensor => $value_features)
					{
						$stations[$value['station_id_code']]['sensors'][$key_handler][] = array('sensor_id_code' => $key_sensor, 'features' => $value_features);
					}
                }
            }
        }
        
        $date_parsed = date_parse_from_format("D, j M Y H:i:s", $sxe->DATE);   
        $date_prepared = mktime($date_parsed['hour'], $date_parsed['minute'], $date_parsed['second'], $date_parsed['month'], $date_parsed['day'], $date_parsed['year']);
        
		// convert parsed XML data into regular message
		// for this kind of messages we have an agreement to put X instead of D at the beginning of message.
        foreach ($messages as $key => $value)
		{
            if (!$stations[$value['station_id_code']])
			{
                $result[] = $value['station_id_code']." station is not exists in database, no sense to convert RNWY part into message";
                continue;
            }
			
            $result_message_body = 'X'.$value['station_id_code'];
            $result_message_body .= date('ymdHi', $date_prepared);
            $result_message_body .= '00';
            
			// we need last_log for this satation to calculate period of measurement (some sensors' strings should contain this period
            $last_logs = ListenerLog::getLast2Messages($stations[$value['station_id_code']]['station_id']);

            if (isset($value['sensors']))
			{
                foreach ($value['sensors'] as $key_handler => $value_sensors) 
				{
                    if ($value_sensors)
					{
                        foreach ($value_sensors as $key_sensor => $value2)
						{
                            if (isset($stations[$value['station_id_code']]['sensors'][$key_handler][$key_sensor]))
							{
								// create handler for each sensor (we parsed new data for)
                                $handler = SensorHandler::create($key_handler);

                                if ($key_handler == 'SolarRadiation' && $last_logs[0]['log_id'])
								{
                                    if ($last_logs[0]['log_id']) 
									{
                                        $total_minutes = round(abs($date_prepared - strtotime($last_logs[0]['measuring_timestamp'])) / 60);
                                        $value2['period'] = $total_minutes;
                                    } 
									else 
									{
                                        $value2['period'] = 1;
                                    }
									
                                    if ($value2['solar_radiation_in_period'][0] != 'M')
									{
                                        $value2['solar_radiation_in_period'] = $value2['solar_radiation_in_period']*$value2['period']*60;
                                    }                                    
                                }
                                
								// each handler has it's own implementation of preparing sensors string for message basing on XML data
                                $res = $handler->prepareXMLValue($value2, $stations[$value['station_id_code']]['sensors'][$key_handler][$key_sensor]['features']);
                                $result_message_body .= $stations[$value['station_id_code']]['sensors'][$key_handler][$key_sensor]['sensor_id_code'] . $res;
                            }
                        }
                    }
                }
                
                $result_message_body.= It::prepareCRC($result_message_body);
                $result_message_body = '@'.$result_message_body.'$';
                
				// add new message into database. It will be processed later as all newcame messages
                $log_id = ListenerLog::addNew($result_message_body, 0, 1);
                $result[] = $base_filename." : New message #". $log_id ." was added";
            }
        }
        
        // return some comments created during convertation
        return implode("\n", $result);
    }
}
?>