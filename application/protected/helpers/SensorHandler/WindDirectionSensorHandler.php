<?php

/*
 * Handler to work with data of Wind Direction sensor
 * 
 */

class WindDirectionSensorHandler extends SensorHandler
{
    public $features = array(
        array(
            'feature_name'          => '1-Minute', // name of measurement
            'feature_code'          => 'wind_direction_1', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'wind_direction', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Wind Direction',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        ),		
        array(
            'feature_name'          => '2-Minute Average',
            'feature_code'          => 'wind_direction_2',
            'measurement_type_code' => 'wind_direction',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'WD 2-Minute Average'
        ),
        array(
            'feature_name'          => '10-Minute Average',
            'feature_code'          => 'wind_direction_10',
            'measurement_type_code' => 'wind_direction',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'WD 10-Minute Average'
        )
    );

    public $extra_features = array(
        array(
            'feature_name'          => 'Height of sensor above the ground',
            'feature_code'          => 'height',
            'measurement_type_code' => 'height',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0

        )
    );

    public function getSensorDescription()
	{
		return "<p>Processes string like \"<b>WD1 Z XXX CCC VVV</b>\"; where: <b>WD1</b> - device ID; <b>Z</b> - sets of data (1 or 3); <b>XXX</b> - 1 minute average; <b>CCC</b> - 2 minutes average; <b>VVV</b> - 10 minutes average</p><p>Example: <b>WD1 1 234</b> - Wind Direction during 1 minute had direction = 234 degree</p>";
    }
     
   
    public function getInfoForAwsPanel($sensor_pairs, $sensorList, $sensorData, $for = 'panel')
	{
        $return = array();

        $sensor_ids = array();
        $sensor_measuring_time = array();
        $last_logs_ids = array();
        $first_logs_ids = array();
        $last_logs_per_station = array();
		
        foreach ($sensor_pairs as $value)
		{
            $sensor_ids[] = $value['sensor_id'];
            
			if (count($value['last_logs']) > 0)
			{
                $last_logs_ids[] = $value['last_logs'][0]->log_id;
                $first_logs_ids[] = $value['last_logs'][0]->log_id;
				$sensor_measuring_time[$value['sensor_id']] = $value['last_logs'][0]->measuring_timestamp;
				$last_logs_per_station[$value['station_id']][0] = $value['last_logs'][0]->log_id;
            }
			
            if (count($value['last_logs']) > 1)
			{
                $last_logs_ids[] = $value['last_logs'][1]->log_id;
				$last_logs_per_station[$value['station_id']][1] = $value['last_logs'][1]->log_id;
            }
        }
        
        $sensor_feature_ids  = array();
        $sensor_feature_ids2 = array();
		
		if (isset($sensorList['wind_direction_1']) && is_array($sensorList['wind_direction_1']))
		{
			foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['wind_direction_1'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['wind_direction_1'][$sensor_id];
				
                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_min24_max_24',
                    'last'                   => '-',
					'2minute_average'        => '-',
                    '10minute_average'       => '-',
                    'max24'                  => '-',
                    'min24'                  => '-',
					'max1hr'                 => '-',
                    'min1hr'                 => '-',
                    'change'                 => 'no',
					'magnetic_north_offset'	 => $sensorFeature->sensor->station->magnetic_north_offset,
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
		
		$windAverages = array(
			'wind_direction_2' => '2minute_average',
			'wind_direction_10' => '10minute_average',
		);
		
        foreach ($return as $station_id => &$sensors) 
		{
			foreach ($sensors as $sensor_id => &$sensorValues) 
			{ 
				if (isset($sensorData['wind_direction_1'][$station_id][$sensor_id]) && (count($sensorData['wind_direction_1'][$station_id][$sensor_id]) > 0) &&
					($sensorData['wind_direction_1'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['wind_direction_1'][$station_id][$sensor_id][0];
					$sensorValue->sensor_feature_value = $this->applyOffset($sensorValue->sensor_feature_value, $sensorValues['magnetic_north_offset']);
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'wind_direction_1');

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

						if (count($sensorData['wind_direction_1'][$station_id][$sensor_id]) > 1)
						{
							$previousSensorValue = $sensorData['wind_direction_1'][$station_id][$sensor_id][1];
							$previousSensorValue->sensor_feature_value = $this->applyOffset($previousSensorValue->sensor_feature_value, $sensorValues['magnetic_north_offset']);
							
							
//							if ($sensorValue->sensor_feature_value > $previousSensorValue->sensor_feature_value)
//							{
//								$sensorValues['change'] = 'up';
//							} 
//							else if ($sensorValue->sensor_feature_value < $previousSensorValue->sensor_feature_value)
//							{
//								$sensorValues['change'] = 'down';
//							}

							if (isset($sensorValues['has_filter_diff']) && ($sensorValues['has_filter_diff'] == 1))
							{
								if (abs($sensorValue->sensor_feature_value - $previousSensorValue->sensor_feature_value) > $sensorValues['filter_diff'])
								{
									$sensorValues['last_filter_errors'][] = "|R1 - R0| > ". $sensorValues['filter_diff'];
								}   
							}
						}
					}
					
					if (isset($sensor_measuring_time[$sensor_id]))
					{
						$maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, $return[$station_id][$sensor_id]['magnetic_north_offset'], 'wind_direction_1');
						$sensorValues['max24'] = $maxmin['max'];
						$sensorValues['min24'] = $maxmin['min'];
                        $sensorValues['mami_title'] = $maxmin['mami_title'];
						$maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, $return[$station_id][$sensor_id]['magnetic_north_offset'], 'wind_direction_1');
						$sensorValues['max24_y'] = $maxmin['max'];
						$sensorValues['min24_y'] = $maxmin['min'];
                        $sensorValues['mami_title_y'] = $maxmin['mami_title'];
						if ($for === 'single') 
						{
							$res = $this->getMaxMinInLastXHr($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, $return[$station_id][$sensor_id]['magnetic_north_offset'], 'wind_direction_1');
							$sensorValues['max1hr'] = $res['max'];
							$sensorValues['min1hr'] = $res['min'];        
						}
					}
				}
				
				if (($for === 'single') && (count($first_logs_ids) > 0))
				{
					foreach ($windAverages as $key => $label)
					{
						if (isset($sensorData[$key][$station_id][$sensor_id]))
						{
							foreach ($sensorData[$key][$station_id][$sensor_id] as $sensorDataRecord)
							{
								if (in_array($sensorDataRecord->listener_log_id, $first_logs_ids))
								{
									if ($sensorDataRecord->is_m == 1)
									{
										$sensorValues[$label] = '-';
									}
									else
									{
										$sensorDataRecord->sensor_feature_value = 
												$this->applyOffset($sensorDataRecord->sensor_feature_value, $sensorValues['magnetic_north_offset']);

										$sensorValues[$label] = $this->formatValue($sensorDataRecord->sensor_feature_value);
									}
								}
							}
						}
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
    
     
    public function formatValue($value, $feature_name = 'wind_direction_1')
	{
		return round($value, 0);
    } 
    
    public function applyOffset($value, $offset = 0)
	{
		$value = $value - $offset;
        
		if ($value < 0)
		{
            $value = 360 + $value;
        }
		
        return $value;
    }

    public function _prepareDataPairs() {
        
        $length = strlen($this->incoming_sensor_value);
        
        if ($length == 0)
            return false;
        
        $info_1 = substr($this->incoming_sensor_value, 0, 1);
        if (!in_array($info_1, array(1, 3)))
            return false;
        
        if ($info_1 == 1 && $length != 4) 
            return false;
        
        if ($info_1 == 3 && $length != 10) 
            return false;
        
        $needed_feature_1 = array();
        $needed_feature_2 = array();
        $needed_feature_3 = array();
        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'wind_direction_1') {
                $needed_feature_1 = $feature;
            } elseif ($feature['feature_code'] == 'wind_direction_2') {
                $needed_feature_2 = $feature;
            } elseif ($feature['feature_code'] == 'wind_direction_10') {
                $needed_feature_3 = $feature;
            }
        }
        
        $value = substr($this->incoming_sensor_value, 1, 3);
        $is_m  = 0;
        if ($value == 'MMM') {
            $is_m = 1;
        }          
        $this->prepared_pairs[] = array(
            'feature_code' => 'wind_direction_1', 
            'period'       => 1, 
            'value'        => $value,
            'metric_id'    => $needed_feature_1['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
            'is_m'         => $is_m
        );
        
        if ($info_1 == 3) {
            $value = substr($this->incoming_sensor_value, 4, 3);
            $is_m  = 0;
            if ($value == 'MMM') {
                $is_m = 1;
            }              
            $this->prepared_pairs[] = array(
                'feature_code' => 'wind_direction_2', 
                'period'       => 2, 
                'value'        => $value,
                'metric_id'    => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m'         => $is_m
                );
            
            $value = substr($this->incoming_sensor_value, 7, 3);
            $is_m  = 0;
            if ($value == 'MMM') {
                $is_m = 1;
            }              
            $this->prepared_pairs[] = array(
                'feature_code' => 'wind_direction_10', 
                'period'       => 10, 
                'value'        => $value,
                'metric_id'    => $needed_feature_3['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_3['metric_code'], $needed_feature_3['general_metric_code']),
                'is_m'         => $is_m
                );
        }
        return true;
    }    
    
    public function getRandomValue($features) {
        
        $data_sets = rand(1,2);
       
        if ($data_sets == 1) {
            return "1".str_pad(rand(0,360), 3, "0", STR_PAD_LEFT);
        }
        
        return "3".str_pad(rand(0,360), 3, "0", STR_PAD_LEFT).str_pad(rand(0,360), 3, "0", STR_PAD_LEFT).str_pad(rand(0,360), 3, "0", STR_PAD_LEFT);
    } 
    
    public function prepareXMLValue($xml_data, $db_features) {
        
        
        if ($xml_data['wind_direction_1'][0] == 'M') {
            $data_1 = '3MMM';
        } else {
            $tmp = It::convertMetric($xml_data['wind_direction_1'], 'degree', $db_features['wind_direction_1']);
            $data_1 =  "3".str_pad(round($tmp), 3, "0", STR_PAD_LEFT);
        }
        
        if ($xml_data['wind_direction_2'][0] == 'M') {
            $data_2 = 'MMM';
        } else {
            $tmp = It::convertMetric($xml_data['wind_direction_2'], 'degree', $db_features['wind_direction_2']);
            $data_2 = str_pad(round($tmp), 3, "0", STR_PAD_LEFT);
        }
        $data_3 = 'MMM';        

        return $data_1.$data_2.$data_3;
    }    
}

?>
