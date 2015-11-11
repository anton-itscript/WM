<?php

/*
 * Handler to work with data of CloudHeight sensor (AWS station)
 * 
 */
class CloudHeightAWSSensorHandler extends SensorHandler
{
    public $features = array(
        array(
            'feature_name'          => 'Vertical Visibility', // name of measurement
            'feature_code'          => 'cloud_vertical_visibility', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_vertical_visibility', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => '' // if takes part in “AWS Graph” page
        ),
        array(
            'feature_name'          => 'Measuring Range', // name of measurement
            'feature_code'          => 'cloud_measuring_range', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_measuring_range', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Range' // if takes part in “AWS Graph” page
        ),        
        array(
            'feature_name'          => 'Cloud Height #1', // name of measurement
            'feature_code'          => 'cloud_height_height_1', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_height', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Height #1', // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        ),        
        array(
            'feature_name'          => 'Cloud Depth #1', // name of measurement
            'feature_code'          => 'cloud_height_depth_1', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_height', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Depth #1',
            'aws_panel_show'        => 1,
        ),
        array(
            'feature_name'          => 'Cloud Height #2', // name of measurement
            'feature_code'          => 'cloud_height_height_2', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_height', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Height #2', // if takes part in “AWS Graph” page
        ),
        array(
            'feature_name'          => 'Cloud Depth #2', // name of measurement
            'feature_code'          => 'cloud_height_depth_2', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_height', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Depth #2' // if takes part in “AWS Graph” page
        ),
        array(
            'feature_name'          => 'Cloud Height #3', // name of measurement
            'feature_code'          => 'cloud_height_height_3', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_height', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Height #3', // if takes part in “AWS Graph” page
        ),
        array(
            'feature_name'          => 'Cloud Depth #3', // name of measurement
            'feature_code'          => 'cloud_height_depth_3', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'cloud_height', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Cloud Depth #3' // if takes part in “AWS Graph” page
        ),     
        
        //array(
        //    'feature_name'          => 'Cloud Amount #1',
        //    'feature_code'          => 'cloud_height_amount_1',
        //    'measurement_type_code' => 'cloud_amount',
        //    'has_filter_min'        => 0,
        //    'has_filter_max'        => 0,
        //    'has_filter_diff'       => 0,
        //    'is_cumulative'         => 0,
        //    'is_main'               => 0,
        //    'aws_graph_using'       => ''
        //), 
        //        
        //array(
        //    'feature_name'          => 'Cloud Amount #2',
        //    'feature_code'          => 'cloud_height_amount_2',
        //    'measurement_type_code' => 'cloud_amount',
        //    'has_filter_min'        => 0,
        //    'has_filter_max'        => 0,
        //    'has_filter_diff'       => 0,
        //    'is_cumulative'         => 0,
        //    'is_main'               => 0,
        //    'aws_graph_using'       => ''
        //),     
        //    
        //array(
        //    'feature_name'          => 'Cloud Amount #3',
        //    'feature_code'          => 'cloud_height_amount_3',
        //    'measurement_type_code' => 'cloud_amount',
        //    'has_filter_min'        => 0,
        //    'has_filter_max'        => 0,
        //    'has_filter_diff'       => 0,
        //    'is_cumulative'         => 0,
        //    'is_main'               => 0,
        //    'aws_graph_using'       => ''
        //),        
    );
	
	public function getSensorDescription()
	{
         return "Processes string like \"<b>CH1 SSSS HHHHH DDDDD HHHHH DDDDD HHHHH DDDDD VVVVV RRRRR</b>\",<br/> 
                 where <b>CH1</b> - device Id; <br/>
                 <b>SSSS</b> - Status Word <br/>
                 <b>HHHHH</b> - Cloud Height #1<br/>
                 <b>DDDDD</b> - Cloud Depth #1<br/>
                 <b>HHHHH</b> - Cloud Height #2<br/>
                 <b>DDDDD</b> - Cloud Depth #2<br/>
                 <b>HHHHH</b> - Cloud Height #3<br/>
                 <b>DDDDD</b> - Cloud Depth #3<br/>
                 <b>VVVVV</b> - Vertical Visibility<br/>
                 <b>RRRRR</b> - Measuring Range<br/>";
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
        
        $features = array(
			'cloud_height_depth_1',
			'cloud_height_depth_2', 
			'cloud_height_depth_3', 
			'cloud_height_height_1', 
			'cloud_height_height_2',  
			'cloud_height_height_3', 
			'cloud_measuring_range', 
			'cloud_vertical_visibility',
		);
        
        $sensor_feature_ids = array();
		
		foreach ($features as $feature)
		{
			if (isset($sensorList[$feature]) && is_array($sensorList[$feature]))
			{
				foreach ($sensor_ids as $sensor_id)
				{
					if (!isset($sensorList[$feature][$sensor_id]))
						continue;
						
					$sensorFeature = $sensorList[$feature][$sensor_id];
					
					$sensor_feature_ids[] = $sensorFeature->sensor_feature_id;

					if (!isset($return[$sensorFeature->sensor->station_id][$sensor_id]))
					{
						$return[$sensorFeature->sensor->station_id][$sensor_id] = array(
							'sensor_display_name'    => $sensorFeature->sensor->display_name,
							'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
							'group'                  => 'clouds',
							'timezone_id'            => $sensorFeature->sensor->station->timezone_id,
						);                    
					}

					$return[$sensorFeature->sensor->station_id][$sensor_id][$sensorFeature->feature_code] = array(
						'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
						'last'                   => '-',
						'change'                 => 'no',
						'filter_max'             => $sensorFeature->default->filter_max,
						'filter_min'             => $sensorFeature->default->filter_min,
						'filter_diff'            => $sensorFeature->default->filter_diff,
						'has_filter_max'         => $sensorFeature->has_filter_max,
						'has_filter_min'         => $sensorFeature->has_filter_min,
						'has_filter_diff'        => $sensorFeature->has_filter_diff,  
					);
				}
			}
		}			
        
        if (count($last_logs_ids) === 0)
		{
            return $return;
        }

		foreach ($features as $feature_code)
		{		
			foreach ($return as $station_id => &$sensors) 
			{
				foreach ($sensors as $sensor_id => &$sensorValues) 
				{
					if (isset($sensorData[$feature_code][$station_id][$sensor_id]) && (count($sensorData[$feature_code][$station_id][$sensor_id]) > 0) &&
						($sensorData[$feature_code][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
					{
						$sensorValue = $sensorData[$feature_code][$station_id][$sensor_id][0];
						
						if ($sensorValue->is_m != 1)
						{
							$sensorValues[$feature_code]['last'] = $this->formatValue($sensorValue->sensor_feature_value, $feature_code);

							if (isset($sensorValues[$feature_code]['has_filter_max']) && ($sensorValues[$feature_code]['has_filter_max'] == 1))
							{
								if ($sensorValue->sensor_feature_value > $sensorValues[$feature_code]['filter_max'])
								{
									$sensorValues[$feature_code]['last_filter_errors'][] = "R > ". $sensorValues[$feature_code]['filter_max'];
								}
							}

							if (isset($sensorValues[$feature_code]['has_filter_min']) && ($sensorValues[$feature_code]['has_filter_min'] == 1))
							{
								if ($sensorValue->sensor_feature_value < $sensorValues[$feature_code]['filter_min'])
								{
									$sensorValues[$feature_code]['last_filter_errors'][] = "R < ". $sensorValues[$feature_code]['filter_min'];
								}            
							}
							
							if (count($sensorData[$feature_code][$station_id][$sensor_id]) > 3)
							{
                                $previousSensorValue = array();
                                for($i=0;$i<4;$i++)
                                    $previousSensorValue[]=$sensorData[$feature_code][$station_id][$sensor_id][$i]->sensor_feature_value;
                                if (SensorHandler::checkTrend($previousSensorValue,1))
                                    $sensorValues['change'] = 'up';
                                else if (SensorHandler::checkTrend($previousSensorValue,-1))
                                    $sensorValues['change'] = 'down';


								if (isset($sensorValues[$feature_code]['has_filter_diff']) && ($sensorValues[$feature_code]['has_filter_diff'] == 1))
								{
									if (abs($sensorValue->sensor_feature_value - $previousSensorValue[1]) > $sensorValues[$feature_code]['filter_diff'])
									{
										$sensorValues[$feature_code]['last_filter_errors'][] = "|R1 - R0| > ". $sensorValues[$feature_code]['filter_diff'];
									}   
								}
							}
						}
					}

					foreach (array('filter_min', 'filter_max', 'filter_diff', 'has_filter_min', 'has_filter_max', 'has_filter_diff') as $unsetfield)
					{
						unset($sensorValues[$feature_code][$unsetfield]);
					}
				}
			}
		}
        
        return $return;
    }    
   
    public function formatValue($value, $feature_name = 'cloud_vertical_visibility')
	{
        if ($feature_name == 'cloud_vertical_visibility')
		{
            return round($value);
        }
		else if (in_array($feature_name, array('cloud_height_height_1', 'cloud_height_height_2', 'cloud_height_height_3', 'cloud_measuring_range')))
		{
			return round($value);
        } 
		else if (in_array($feature_name, array('cloud_height_depth_1', 'cloud_height_depth_2', 'cloud_height_depth_3')))
		{
			return round($value);
        }
		else if (in_array($feature_name, array('cloud_height_amount_1', 'cloud_height_amount_2', 'cloud_height_amount_3')))
		{
			return round($value) .'/8';
        }
		
        return '';
    }    
    
    public function _prepareDataPairs()
	{
        $length = strlen($this->incoming_sensor_value);

        if ($length != 44)
            return false;
        
        foreach($this->sensor_features_info as $feature)
		{
            if ($feature['feature_code'] == 'cloud_vertical_visibility')
			{
				$needed_feature1 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_height_height_1')
			{
				$needed_feature2 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_height_height_2')
			{
				$needed_feature3 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_height_height_3')
			{
				$needed_feature4 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_height_depth_1')
			{
				$needed_feature5 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_height_depth_2')
			{
				$needed_feature6 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_height_depth_3')
			{
				$needed_feature7 = $feature;
            }
			else if ($feature['feature_code'] == 'cloud_measuring_range')
			{
				$needed_feature8 = $feature;
            }            
        }
        
        $value = substr($this->incoming_sensor_value, 4, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_height_height_1', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature2['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature2['metric_code'], $needed_feature2['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 9, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_height_depth_1', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature5['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature5['metric_code'], $needed_feature5['general_metric_code']),
            'is_m'             => $is_m
        );        
        
        
        $value = substr($this->incoming_sensor_value, 14, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code' => 'cloud_height_height_2', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature3['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature3['metric_code'], $needed_feature3['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 19, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code' => 'cloud_height_depth_2', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature6['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature6['metric_code'], $needed_feature6['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 24, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code' => 'cloud_height_height_3', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature4['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature4['metric_code'], $needed_feature4['general_metric_code']),
            'is_m'             => $is_m
        );        
        
        $value = substr($this->incoming_sensor_value, 29, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_height_depth_3', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature7['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature7['metric_code'], $needed_feature7['general_metric_code']),
            'is_m'             => $is_m
        );        
        
        $value = substr($this->incoming_sensor_value, 34, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_vertical_visibility', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature1['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature1['metric_code'], $needed_feature1['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 39, 5);
        $is_m = $value == 'MMMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_measuring_range', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature8['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature8['metric_code'], $needed_feature8['general_metric_code']),
            'is_m'             => $is_m
        );           

        return true;
    }        
    
    public function getRandomValue($features)
	{
        $s = array();
        
        //Status Word, 4 symbols
        $s[] = 'MMMM'; 
        
        // Cloud Height #1, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Depth #1, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Height #2, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Depth #2, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);         
        
        // Cloud Height #3, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Depth #3, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);         
        
        // Vertical Visibility, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);          
        
        // Measuring Range, 5 symbols
        $s[] = str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);         

        
        return implode('', $s);
    }  
    
    public function prepareXMLValue($xml_data, $db_features)
	{
        $s = array();
        
        //Status Word, 4 symbols
        $s[] = 'MMMM'; 
        
        // Cloud Height #1, 5 symbols
        if ($xml_data['cloud_height_height_1'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_height_height_1'], 'feet', $db_features['cloud_height_height_1']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }
        
        // Cloud Depth #1, 5 symbols
        if ($xml_data['cloud_height_depth_1'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_height_depth_1'], 'feet', $db_features['cloud_height_depth_1']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }        
        
        // Cloud Height #2, 5 symbols
        if ($xml_data['cloud_height_height_2'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_height_height_2'], 'feet', $db_features['cloud_height_height_2']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }
        
        // Cloud Depth #2, 5 symbols
        if ($xml_data['cloud_height_depth_2'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_height_depth_2'], 'feet', $db_features['cloud_height_depth_2']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }         
        
        // Cloud Height #3, 5 symbols
        if ($xml_data['cloud_height_height_3'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_height_height_3'], 'feet', $db_features['cloud_height_height_3']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }
        
        // Cloud Depth #3, 5 symbols
        if ($xml_data['cloud_height_depth_3'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_height_depth_3'], 'feet', $db_features['cloud_height_depth_3']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }         
        
        // Vertical Visibility, 5 symbols
        if ($xml_data['cloud_vertical_visibility'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_vertical_visibility'], 'feet', $db_features['cloud_vertical_visibility']);
            $s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
        }
        
        // Measuring Range, 5 symbols
        if ($xml_data['cloud_measuring_range'][0] == 'M') {
            $s[] = 'MMMMM';
        } else {
            $tmp = It::convertMetric($xml_data['cloud_measuring_range'], 'feet', $db_features['cloud_measuring_range']);
            $tmp2 = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
            $s[] = $tmp2;
        }

        return implode('', $s);
    }

    public static function getTrendForAwsPanel($stationData){
        return 'no';
    }
}

?>
