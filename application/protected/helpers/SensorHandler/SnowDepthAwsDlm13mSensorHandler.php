<?php

/**
 * Description of SnowDepthAwsDlm13mSensorHandler
 *
 * @author
 */
class SnowDepthAwsDlm13mSensorHandler extends SensorHandler
{
    public $features = array(
        array(
            'feature_name'          => 'Snow Depth',
            'feature_code'          => 'snow_depth',
            'measurement_type_code' => 'snow_depth',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,
            'is_main'               => 1,
            'aws_graph_using'       => 'Snow Depth',
            'aws_panel_show'        => 1,
        ),
		array(
            'feature_name'          => 'Error code', // name of measurement
            'feature_code'          => 'error_code', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'error_code', // measurement code to get possible metrics for.
            'has_filter_min'        => 0, // should WM check this measurement's value for filters?
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => ''  // if takes part in “AWS Graph” page
        ),        
    );
    
    public function getSensorDescription()
	{
		return "Processes string like \"<b>SN1 xx eee.eeee</b>\",<br/> 
                 where <b>SN1</b> - sensor Id; <br/>
                 <b>xx</b> - error code <br/>
                 <b>eee.eeee</b> - snow depth <br/>";
    }
    
    public function getInfoForAwsPanel($sensor_pairs, $sensorList, $sensorData, $for = 'panel')
	{
        $return = array();

        $sensor_ids = array();
        $sensor_measuring_time = array();
        $last_logs_ids = array();
        $last_logs_per_station = array();
        
        foreach ($sensor_pairs as $value)
		{
            $sensor_ids[] = $value['sensor_id'];
			
            if (count($value['last_logs']) > 0)
			{
                $last_logs_ids[] = $value['last_logs'][0]->log_id;
                $sensor_measuring_time[$value['sensor_id']] = $value['last_logs'][0]->measuring_timestamp;
                $last_logs_per_station[$value['station_id']][0] = $value['last_logs'][0]->log_id;
            }
            
			if (count($value['last_logs']) > 1)
			{
                $last_logs_ids[] = $value['last_logs'][1]->log_id; 
                $last_logs_per_station[$value['station_id']][1] = $value['last_logs'][1]->log_id;
            }
        }
        
        $sensor_feature_ids = array();
        $sensor_feature_ids2 = array();
        
		if (isset($sensorList['snow_depth']) && is_array($sensorList['snow_depth']))
		{
			foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['snow_depth'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['snow_depth'][$sensor_id];

                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_min24_max_24',
                    'last'                   => '-',
                    'period'                 => '-',
					'error_code'			 => '-',
                    'max24'                  => '-',
                    'min24'                  => '-',
                    'change'                 => 'no',
                    'timezone_id'            => $sensorFeature->sensor->station->timezone_id,
                    'filter_max'             => $sensorFeature->default->filter_max,
                    'filter_min'             => $sensorFeature->default->filter_min,
                    'filter_diff'            => $sensorFeature->default->filter_diff,
                    'has_filter_max'         => $sensorFeature->has_filter_max,
                    'has_filter_min'         => $sensorFeature->has_filter_min,
                    'has_filter_diff'        => $sensorFeature->has_filter_diff,                       
                );
            }
        }
        
        if (count($last_logs_ids) === 0)
		{
            return $return;
        }
		
		foreach ($return as $station_id => &$sensors) 
		{
			foreach ($sensors as $sensor_id => &$sensorValues) 
			{    
				if (isset($sensorData['snow_depth'][$station_id][$sensor_id]) && (count($sensorData['snow_depth'][$station_id][$sensor_id]) > 0) &&
					($sensorData['snow_depth'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['snow_depth'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'snow_depth');

						if (isset($sensorValues['has_filter_max']) && ($sensorValues['has_filter_max'] == 1))
						{
							if ($sensorValue->sensor_feature_value > $sensorValues['filter_max'])
							{
								$sensorValues['last_filter_errors'][] = "R > ". $sensorValues['filter_max'];
							}
						}

						if (isset($sensorValues['has_filter_min']) && ($sensorValues['has_filter_min'] == 1))
						{
							if ($sensorValue->sensor_feature_value < $sensorValues['filter_min']) 
							{
								$sensorValues['last_filter_errors'][] = "R < ". $sensorValues['filter_min'];
							} 
						}

						if (count($sensorData['snow_depth'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['snow_depth'][$station_id][$sensor_id][$i]->sensor_feature_value;
                            if (SensorHandler::checkTrend($previousSensorValue,1))
                                $sensorValues['change'] = 'up';
                            else if (SensorHandler::checkTrend($previousSensorValue,-1))
                                $sensorValues['change'] = 'down';

							if (isset($sensorValues['has_filter_diff']) && ($sensorValues['has_filter_diff'] == 1))
							{
								if (abs($sensorValue->sensor_feature_value - $previousSensorValue[1]) > $sensorValues['filter_diff'])
								{
									$sensorValues['last_filter_errors'][] = "|R1 - R0| > ". $sensorValues['filter_diff'];
								}      
							}
						}
					}
					
					if (isset($sensor_measuring_time[$sensor_id]))
					{
//						$maxmin = $this->getMaxMinFromHour($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 0, 'snow_depth');
                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]),  1, 0, 'snow_depth');
						$sensorValues['max24'] = $maxmin['max'];
						$sensorValues['min24'] = $maxmin['min'];
                        $sensorValues['mami_title'] = $maxmin['mami_title'];

                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]),  2, 0, 'snow_depth');
						$sensorValues['max24_y'] = $maxmin['max'];
						$sensorValues['min24_y'] = $maxmin['min'];
                        $sensorValues['mami_title_y'] = $maxmin['mami_title'];
					}
				}

				foreach (array('filter_min', 'filter_max', 'filter_diff', 'has_filter_min', 'has_filter_max', 'has_filter_diff') as $unsetfield)
				{
					unset($return[$station_id][$sensor_id][$unsetfield]);
				}
			}
		}
		
        return $return;
    }    
   
   
    public function formatValue($value, $feature_name = 'snow_depth')
	{
        if ($feature_name === 'snow_depth')
		{
            return number_format(round($value, 3), 3);
        } 
		else if ($feature_name === 'error_code')
		{
            return $value;
        }		
		
        return '';
    }     
    
    public function _prepareDataPairs()
	{
        $length = strlen($this->incoming_sensor_value);
        
		if ($length != 10)
            return false;
        
		$features = array();
		
        foreach($this->sensor_features_info as $feature)
		{
			$features[$feature['feature_code']] = $feature;
        }
		
        // Parsing Snow Depth
        $value = substr($this->incoming_sensor_value, 2, 8);
        $is_m = ($value === 'MMMMMMMM') ? 1 : 0;
        
		$feature = isset($features['snow_depth']) ? $features['error_code'] : array();
		
		$this->prepared_pairs[] = array(
            'feature_code' => 'snow_depth', 
            'period'       => 1, 
            'value'        => $value,
            'metric_id'    => $feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $feature['metric_code'], $feature['general_metric_code']),
            'is_m'         => $is_m
        );
		
		// Parsing Error Code
		$value = substr($this->incoming_sensor_value, 0, 2);
        $is_m = ($value === 'MM') ? 1 : 0;
		
		$feature = isset($features['error_code']) ? $features['error_code'] : array();
		
        $this->prepared_pairs[] = array(
            'feature_code' => 'error_code', 
            'period'       => 1, 
            'value'        => $value,
            'metric_id'    => $feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $feature['metric_code'], $feature['general_metric_code']),
            'is_m'         => $is_m
        );
		
		return true;
    }        
        
    
    public function getRandomValue($features)
	{   
        $result = (rand(1, 10) > 9) ? 'MM' : str_pad(rand(0,99), 2, "0", STR_PAD_LEFT);
		$result .= (rand(1, 10) > 9) ? 'MMMMMMMM' : (str_pad(rand(0,999), 3, "0", STR_PAD_LEFT) .'.'. str_pad(rand(0,9999), 4, "0", STR_PAD_LEFT));
		
		return $result;
    }    
    
    public function prepareXMLValue($xml_data, $db_features)
	{
        if ($xml_data['error_code'][0] === 'MM')
		{
			$tmp1 = 'MMMMM';
        } 
		else
		{
            $tmp1  = str_pad(round($tmp), 2, "0", STR_PAD_LEFT);
        }
        
        if ($xml_data['snow_depth'][0] === 'M')
		{
           $tmp2 = 'MMMMMMMM'; 
        }
		else
		{
            $tmp = It::convertMetric($xml_data['snow_depth'], 'meter', $db_features['snow_depth']);
            $tmp2  = str_pad(substr(number_format($tmp, 4, '.'), -8, 8), "0", STR_PAD_LEFT);
        }
		
        return $tmp1 . $tmp2;        
    }    
}
?>