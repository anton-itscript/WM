<?php

/**
 * Description of MetarWeatherReport
 *
 * @author
 */
class MetarWeatherReport extends BaseMetarSpeciWeatherReport
{
	protected function getSections()
	{
		return array(
			'Row1' => array(
				'ReportType',
				'StationIdentifier',

				'ReportTime',
//				'ReportModifier',
				'ReportAutoModifier',
				
				'Wind',
			),
			'Row2' => array(
				'Visibility',
				'RunwayVisualRange',
				'PresentWeather',
				'SkyCodition',
			),
			'Row3' => array(
				'TemperatureAndDewPoint',
				'Altimeter',
			),
			
//			'Remarks' => array(
//				'ManualAndPlainLanguage',
//				'AdditiveData',
//			),
		);
	}
	
	protected function writeReportAutoModifier()
	{
		$reportAutoModifier = 'XXXX';
		
		$this->report_parts[$this->_section][$this->_subsection] = $reportAutoModifier;
		$this->explanations[$this->_section][$this->_subsection][] = 
				'Fully automated generation: <span>'. $reportAutoModifier .'</span>';
	}
	
	protected function getReportType() 
	{
		return 'METAR';
	}

	protected function getWindDirection()
	{
		$result = array(
			'source_value' => null,
			'value' => '///',
			'description' => '',
		);
		
		$sensor = isset($this->sensors['wind_direction_10']) ? $this->sensors['wind_direction_10'] : null;
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Wind Direction is unavailable';
            }
			else
			{
                // create object of wind direction handler
                $handler_obj = SensorHandler::create($sensor['handler_id_code'], $this->_logger);
				
				// apply magnetic north offset
				$value = $handler_obj->applyOffset($sensor['sensor_feature_value'], $this->station_info->magnetic_north_offset);
				$result['value'] = $this->formatWindDirection($value);
				
				$result['source_value'] = $sensor['sensor_feature_value'];
				$result['description'] = $result['source_value'] .' degree';
            }
        }
		else
		{
            $result['description'] = 'No Wind Direction sensor';   
        }
		
		$result['description'] = 'Wind Direction: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}

	protected function getWindSpeed()
	{
		$result = array(
			'source_value' => null,
			'value' => '00',
			'description' => '',
		);
		
		$sensor = isset($this->sensors['wind_speed_10']) ? $this->sensors['wind_speed_10'] : null;
		$metarMetric = 'meter_per_second';
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Visibility is unavailable';
            }
			else
			{
                $result['source_value'] = $sensor['sensor_feature_value'];
				
				$value = It::convertMetric($sensor['sensor_feature_value'], $sensor['metric_code'], $metarMetric);
				$result['value'] = $this->formatWindSpeed($value);
				
				$result['description'] = $result['source_value'] .' '. $sensor['metric_code'];
                
				if ($sensor['metric_code'] != $metarMetric)
				{
					$result['description'] .= ' = '. $value .' '. $metarMetric;
                }
            }
        }
		else
		{
            $result['description'] = 'No Wind Speed sensor';   
        }
		
		$result['description'] = 'Wind Speed: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		$result['metric'] = $this->formatMetric($metarMetric);
		
		return $result;
	}

	protected function getWindGust()
	{
		return array(
			'source_value' => null,
			'value' => '',
			'description' => 'Wind Gust: <span></span> => <span>Not available</span>',
		);
	}
	
	protected function getPrevailingVisibility()
	{
		$result = array(
			'source_value' => null,
			'value' => '////',
			'description' => '',
			'metric' => 'meter',
		);
		
		$sensor = isset($this->sensors['visibility_1']) ? $this->sensors['visibility_1'] : null;
		$metarMetric = 'meter';
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Visibility is unavailable';
            }
			else
			{
                $result['source_value'] = $sensor['sensor_feature_value'];
				
				$value = It::convertMetric($sensor['sensor_feature_value'], $sensor['metric_code'], $metarMetric);
				$result['value'] = $this->formatVisibility($value);
				
				$result['description'] = $result['source_value'] .' '. $sensor['metric_code'];
                
				if ($sensor['metric_code'] != $metarMetric)
				{
					$result['description'] .= ' = '. $value .' '. $metarMetric;
                }
            }
        }
		else
		{
            $result['description'] = 'No Visibility sensor';   
        }
		
		$result['description'] = 'Prevailing visibility: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}
	
	protected function hasDirectionalVisibility()
	{
		return false;
	}
	
	/**
	 * Always returns NDV - no directional visibility.
	 * 
	 * @return array 
	 */
	protected function getDirectionalVisibility()
	{
		return array(
			'source_value' => null,
			'value' => 'NDV',
			'description' => 'Directional visibility: <span>NDV</span> => <span>Not available</span>',
		);
	}

	/**
	 * Always returns empty value - not available .
	 * 
	 * @return string 
	 */
	protected function getRunwayVisualRange()
	{
		return array(
			'source_value' => null,
			'value' => '',
			'description' => 'Runaway Visual Range: <span></span> => <span>Not available</span>',
		);
	}

	/**
	 * Always returns empty value - not available .
	 * 
	 * @return array 
	 */
	protected function getPresentWeather()
	{
		return array(
			'source_value' => null,
			'value' => '',
			'description' => 'Present Weather: <span></span> => <span>Not available</span>',
		);
	}
	
	/**
	 * Returns value of Cloud Vertical Visibility.
	 * 
	 * @access protected
	 * @return array
	 */
	protected function getCloudVerticalVisibility()
	{
		$result = array(
			'source_value' => null,
			'value' => '////',
			'description' => '',
		);
		
		$sensorName = 'cloud_vertical_visibility';
		
		$sensor = isset($this->sensors[$sensorName]) ? $this->sensors[$sensorName] : null;
		$metarMetric = 'meter';
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Cloud Vertical Visibility is unavailable';
            }
			else
			{
                $result['source_value'] = $sensor['sensor_feature_value'];
				
				$value = It::convertMetric($sensor['sensor_feature_value'], $sensor['metric_code'], $metarMetric);
				$result['value'] = $this->formatCloudVerticalVisibility($value);
				
				$result['description'] = $result['source_value'] .' '. $sensor['metric_code'];
                
				if ($sensor['metric_code'] != $metarMetric)
				{
					$result['description'] .= ' = '. $value .' '. $metarMetric;
                }
            }
        }
		else
		{
            $result['description'] = 'No Cloud Vertical Visibility sensor';   
        }
		
		$result['description'] = 'Cloud Vertical Visibility: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}
	
	
	/**
	 * Returns value of Cloud Amount of given number.
	 * 
	 * @param int $number 1, 2 or 3. 
	 * @return array
	 */
	protected function getCloudAmount($number)
	{
		$result = array(
			'source_value' => null,
			'value' => '///',
			'description' => '',
		);
		
		$sensorName = 'cloud_amount_amount_'. $number;
		
		$sensor = isset($this->sensors[$sensorName]) ? $this->sensors[$sensorName] : null;
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Cloud Amount #'. $number .' is unavailable';
            }
			else
			{
                $result['source_value'] = $sensor['sensor_feature_value'];
				$result['value'] = $this->formatCloudAmount($sensor['sensor_feature_value']);
				
				$result['description'] = $result['source_value'] .'/8';
            }
        }
		else
		{
            $result['description'] = 'No Cloud Amount sensor';   
        }
		
		$result['description'] = 'Cloud Amount #'. $number .': <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}

	/**
	 * Returns value of Cloud Height of given number.
	 * 
	 * @param int $number 1, 2 or 3. 
	 * @return array
	 */
	protected function getCloudHeight($number)
	{
		$metarMetric = 'feet';
		
		$result = array(
			'source_value' => null,
			'value' => '///',
			'description' => '',
			'metric' => $metarMetric,
		);
		
		$sensorName = 'cloud_amount_height_'. $number;
		
		$sensor = isset($this->sensors[$sensorName]) ? $this->sensors[$sensorName] : null;
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Cloud Height #'. $number .' is unavailable';
            }
			else
			{
                $result['source_value'] = $sensor['sensor_feature_value'];
				$result['source_metric'] = $sensor['metric_code'];
						
				$value = It::convertMetric($sensor['sensor_feature_value'], $sensor['metric_code'], $metarMetric);
				$result['value'] = $this->formatCloudHeight($value, $metarMetric);
				
				$result['description'] = $result['source_value'] .' '. $sensor['metric_code'];
                
				if ($sensor['metric_code'] != $metarMetric)
				{
					$result['description'] .= ' = '. $value .' '. $metarMetric;
                }
            }
        }
		else
		{
            $result['description'] = 'No Cloud Height sensor';   
        }
		
		$result['description'] = 'Cloud Height #'. $number .': <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}
	
	protected function getTemperature()
	{
		$result = array(
			'source_value' => null,
			'value' => '//',
			'description' => '',
		);
		
		$sensor = isset($this->sensors['temperature']) ? $this->sensors['temperature'] : null;
		$metarMetric = 'celsius';
		
		if (!is_null($sensor))
		{
            if ($sensor['is_m'])
			{
                $result['source_value'] = 'M';
				$result['description'] = 'Last data about Temperature is unavailable';
            }
			else
			{
                $result['source_value'] = $sensor['sensor_feature_value'];
				
				$value = It::convertMetric($sensor['sensor_feature_value'], $sensor['metric_code'], $metarMetric);
				
				$result['value'] = $this->formatTemperature($value);				
				$result['description'] = $result['source_value'] .' '. $sensor['metric_code'];
                
				if ($sensor['metric_code'] != $metarMetric)
				{
					$result['description'] .= ' = '. $value .' '. $metarMetric;
                }
            }
        }
		else
		{
            $result['description'] = 'No Temperature sensor';   
        }
		
		$result['description'] = 'Temperature: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}
	
	protected function getDewPoint()
	{
		$result = array(
			'source_value' => null,
			'value' => '//',
			'description' => '',
		);
		
		$calculation = isset($this->calculations['DewPoint']) ? $this->calculations['DewPoint'] : null;
		
		$metarMetric = 'celsius';
		$dewPointMetric = 'celsius';
		
		if (!is_null($calculation))
		{
            $result['source_value'] = $calculation['value'];
			
			$value = It::convertMetric($calculation['value'], $dewPointMetric, $metarMetric);
			
			$result['value'] = $this->formatDewPoint($value);
			$result['description'] = $result['source_value'] .' '. $dewPointMetric;

			if ($dewPointMetric != $metarMetric)
			{
				$result['description'] .= ' = '. $value .' '. $metarMetric;
			}
        }
		else
		{
            $result['description'] = 'No Dew Point calculation';   
        }
		
		$result['description'] = 'Dew Point: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}
	
	protected function getPressure()
	{
		$result = array(
			'source_value' => null,
			'value' => 'Q////',
			'description' => '',
		);
		
		$calculation = isset($this->calculations['PressureSeaLevel']) ? $this->calculations['PressureSeaLevel'] : null;
		
		$metarMetric = 'hPascal';
		$pressureMetric = 'hPascal';
		
		if (!is_null($calculation))
		{
            $result['source_value'] = $calculation['value'];
			
			$value = It::convertMetric($calculation['value'], $pressureMetric, $metarMetric);
			
			$result['value'] = $this->formatPressure($value);
			$result['description'] = $result['source_value'] .' '. $pressureMetric;

			if ($pressureMetric != $metarMetric)
			{
				$result['description'] .= ' = '. $value .' '. $metarMetric;
			}
        }
		else
		{
            $result['description'] = 'No Pressure Sea Level calculation';   
        }
		
		$result['description'] = 'Pressure Sea Level: <span>'. $result['value'] .'</span> => <span>'. $result['description'] .'</span>';
		
		return $result;
	}
}

?>
