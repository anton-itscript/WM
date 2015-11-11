<?php

/*
 * Handler to work with data of Visibility sensor
 * 
 */

class VisibilityAWSSensorHandler extends SensorHandler {

    
    public $features = array(
        array(
            'feature_name'          => '1 minute avg', // name of measurement
            'feature_code'          => 'visibility_1', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'visibility', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Visibility',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        ),
        array(
            'feature_name'          => '10 min avg',
            'feature_code'          => 'visibility_10',
            'measurement_type_code' => 'visibility',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => ''
        ),
    );
    
    public function getSensorDescription() {
         return "No description for this Sensor";
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
		
		if (isset($sensorList['visibility_1']) && is_array($sensorList['visibility_1']))
		{
			foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['visibility_1'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['visibility_1'][$sensor_id];
				
                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_min24_max_24',
                    'last'                   => '-',
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
				if (isset($sensorData['visibility_1'][$station_id][$sensor_id]) && (count($sensorData['visibility_1'][$station_id][$sensor_id]) > 0) &&
					($sensorData['visibility_1'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['visibility_1'][$station_id][$sensor_id][0];

					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'visibility_1');

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

						if (count($sensorData['visibility_1'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['visibility_1'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
						$maxmin = $this->getMaxMinFromHour($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 0, 'visibility_1');
						
						$sensorValues['max24'] = $maxmin['max'];
						$sensorValues['min24'] = $maxmin['min'];
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
   
   
    public function formatValue($value, $feature_name = 'visibility_1')
	{
        if ($feature_name === 'visibility_1')
		{
            return number_format(round($value, 1), 1);
        } 
		else if ($feature_name === 'visibility_10')
		{
            return number_format(round($value, 1), 1);
        }		
		
        return '';
    }     
    
    public function _prepareDataPairs()
	{
        $length = strlen($this->incoming_sensor_value);
        
        if ($length <> 19)
            return false;
        
        $needed_feature_1 = array();
        $needed_feature_2 = array();

        foreach($this->sensor_features_info as $feature)
		{
            if ($feature['feature_code'] == 'visibility_1')
			{
                $needed_feature_1 = $feature;
            }
			else if ($feature['feature_code'] == 'visibility_10')
			{
				$needed_feature_2 = $feature;
            }
        }
        
        $value = substr($this->incoming_sensor_value, 0, 5);
        $is_m = ($value == 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code' => 'visibility_1', 
            'period'       => 1, 
            'value'        => $value,
            'metric_id'    => $needed_feature_1['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
            'is_m'         => $is_m
        );
        
		$value = substr($this->incoming_sensor_value, 5, 5);
        $is_m = ($value == 'MMMMM') ? 1 : 0;
        
		$this->prepared_pairs[] = array(
            'feature_code' => 'visibility_10', 
            'period'       => 10, 
            'value'        => $value,
            'metric_id'    => $needed_feature_2['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
            'is_m'         => $is_m
        );
        
		return true;
    }        
        
    
    public function getRandomValue($features)
	{
        $tmp1  = str_pad(rand(0,80000), 5, "0", STR_PAD_LEFT);
        $tmp2  = str_pad(rand(0,80000), 5, "0", STR_PAD_LEFT);
        
		$tmp3  = 'M';
        $tmp4 = 'MMMMMMMM';
		
        return $tmp1 . $tmp2 . $tmp3 . $tmp4;
    }    
    
    public function prepareXMLValue($xml_data, $db_features)
	{
        if ($xml_data['visibility_1'][0] === 'M')
		{
			$tmp1 = 'MMMMM';
        } 
		else
		{
            $tmp = It::convertMetric($xml_data['visibility_1'], 'meter', $db_features['visibility_1']);
            $tmp1  = str_pad(round($tmp), 5, "0", STR_PAD_LEFT);
        }
        
        if ($xml_data['visibility_10'][0] === 'M')
		{
			$tmp2 = 'MMMMM'; 
        }
		else
		{
            $tmp = It::convertMetric($xml_data['visibility_10'], 'meter', $db_features['visibility_10']);
            $tmp2  = str_pad(round($tmp), 5, "0", STR_PAD_LEFT);
        }
		
        $tmp3  = 'M';
        $tmp4 = 'MMMMMMMM';
        
		return $tmp1 . $tmp2 . $tmp3 . $tmp4;        
    }    
}

?>
