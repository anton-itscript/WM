<?php

/*
 * Handler to work with data of CloudHeight sensor (AWS station)
 * 
 */
class CloudHeightAwsDlm13mSensorHandler extends SensorHandler
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
            'aws_graph_using'       => 'Vertical Visibility' // if takes part in “AWS Graph” page
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
            'aws_graph_using'       => 'Cloud Depth #1'
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
		
		 array(
            'feature_name'          => 'Status',
            'feature_code'          => 'status',
            'measurement_type_code' => 'status',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Status'
        ), 
        
        array(
            'feature_name'          => 'Cloud Amount #1',
            'feature_code'          => 'cloud_amount_amount_1',
            'measurement_type_code' => 'cloud_amount',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Amount #1',
            'aws_panel_show'        => 1,
        ), 
		
		array(
            'feature_name'          => 'Cloud Height #1',
            'feature_code'          => 'cloud_amount_height_1',
            'measurement_type_code' => 'cloud_height',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Height #1',
            'aws_panel_show'        => 1,
        ), 
                
        array(
            'feature_name'          => 'Cloud Amount #2',
            'feature_code'          => 'cloud_amount_amount_2',
            'measurement_type_code' => 'cloud_amount',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Amount #2',
        ),
            
		array(
            'feature_name'          => 'Cloud Height #2',
            'feature_code'          => 'cloud_amount_height_2',
            'measurement_type_code' => 'cloud_height',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Height #2',
        ),
		
        array(
            'feature_name'          => 'Cloud Amount #3',
            'feature_code'          => 'cloud_amount_amount_3',
            'measurement_type_code' => 'cloud_amount',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Amount #3',
        ),
		
		array(
            'feature_name'          => 'Cloud Height #3',
            'feature_code'          => 'cloud_amount_height_3',
            'measurement_type_code' => 'cloud_height',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Height #3',
        ),
		
		array(
            'feature_name'          => 'Cloud Amount #4',
            'feature_code'          => 'cloud_amount_amount_4',
            'measurement_type_code' => 'cloud_amount',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Amount #4'
        ),  
		
		array(
            'feature_name'          => 'Cloud Height #4',
            'feature_code'          => 'cloud_amount_height_4',
            'measurement_type_code' => 'cloud_height',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Height #4'
        ), 
		
		array(
            'feature_name'          => 'Cloud Amount Total',
            'feature_code'          => 'cloud_amount_amount_total',
            'measurement_type_code' => 'cloud_amount',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => 'Cloud Amount Total'
        ),  
    );
    
	public function getSensorDescription()
	{
		return "Processes string like \"<b>CH1 sSSSS HHHHH DDDDD HHHHH DDDDD HHHHH DDDDD VVVVV RRRRR <i>-OLLLLL,OLLLLL,OLLLLL,OLLLLL,T-</i></b>\",<br/> 
				where <b>CH1</b> - device Id; <br/>
				<b>sSSSS</b> - Status Word <br/>
				<b>HHHHH</b> - Cloud Height #1<br/>
				<b>DDDDD</b> - Cloud Depth #1<br/>
				<b>HHHHH</b> - Cloud Height #2<br/>
				<b>DDDDD</b> - Cloud Depth #2<br/>
				<b>HHHHH</b> - Cloud Height #3<br/>
				<b>DDDDD</b> - Cloud Depth #3<br/>
				<b>VVVVV</b> - Vertical Visibility<br/>
				<b>RRRRR</b> - Measuring Range<br/>
				
				<b>O</b> - Cloud Amount #1<br/>
				<b>LLLLL</b> - Cloud Height #1<br/>
				<b>O</b> - Cloud Amount #2<br/>
				<b>LLLLL</b> - Cloud Height #2<br/>
				<b>O</b> - Cloud Amount #3<br/>
				<b>LLLLL</b> - Cloud Height #3<br/>
				<b>O</b> - Cloud Amount #4<br/>
				<b>LLLLL</b> - Cloud Height #4<br/>
				<b>T</b> - Cloud Amount Total<br/>";
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
				$last_logs_per_station[$value['station_id']][1] = $value['last_logs'][0]->log_id;
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
			
			'cloud_amount_amount_1',
			'cloud_amount_height_1',
			'cloud_amount_amount_2',
			'cloud_amount_height_2',
			'cloud_amount_amount_3',
			'cloud_amount_height_3',
			'cloud_amount_amount_4',
			'cloud_amount_height_4',
			'cloud_amount_amount_total'
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
   
    public function formatValue($value, $feature_name = '')
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
		else if (in_array($feature_name, array(
			'cloud_amount_amount_1', 
			'cloud_amount_amount_2',
			'cloud_amount_amount_3',
			'cloud_amount_amount_4',
			'cloud_amount_amount_total',
		)))
		{
           return round($value) .'/8';
        }
		else if (in_array($feature_name, array(
			'cloud_amount_height_1', 
			'cloud_amount_height_2',
			'cloud_amount_height_3',
			'cloud_amount_height_4',
		)))
		{
           return round($value);
        }
		
        return '';
    }    
    
    public function _prepareDataPairs()
	{
		$length = strlen($this->incoming_sensor_value);

        if ($length < 45)
            return false;
        
		$amount_features = array();
		
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
			else if ($feature['feature_code'] == 'status')
			{
				$needed_feature9 = $feature;
            }  
			else if (in_array($feature['feature_code'], array(
				'cloud_amount_amount_1',
				'cloud_amount_height_1',
				'cloud_amount_amount_2',
				'cloud_amount_height_2',
				'cloud_amount_amount_3',
				'cloud_amount_height_3',
				'cloud_amount_amount_4',
				'cloud_amount_height_4',
				'cloud_amount_amount_total',
			)))
			{
				$amount_features[$feature['feature_code']] = $feature;
			}
        }
        
		$value = substr($this->incoming_sensor_value, 0, 5);
		$is_m = ($value === 'MMMMM') ? 1 : 0;
		
        if (preg_match('/^s\d{4}$/', $value))
		{
			$value = substr($value, 1);
			
			$this->prepared_pairs[] = array(
				'feature_code'     => 'status', 
				'period'           => 1, 
				'value'            => $value,
				'metric_id'        => $needed_feature9['metric_id'],
				'normilized_value' => It::convertMetric($value, $needed_feature9['metric_code'], $needed_feature9['general_metric_code']),
				'is_m'             => $is_m
			);
		}
		
		$value = substr($this->incoming_sensor_value, 5, 5);
		$is_m = ($value === 'MMMMM') ? 1 : 0;
		
		$this->prepared_pairs[] = array(
			'feature_code'     => 'cloud_height_height_1', 
			'period'           => 1, 
			'value'            => $value,
			'metric_id'        => $needed_feature2['metric_id'],
			'normilized_value' => It::convertMetric($value, $needed_feature2['metric_code'], $needed_feature2['general_metric_code']),
			'is_m'             => $is_m
		);
        
        $value = substr($this->incoming_sensor_value, 10, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_height_depth_1', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature5['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature5['metric_code'], $needed_feature5['general_metric_code']),
            'is_m'             => $is_m
        );        
        
        
        $value = substr($this->incoming_sensor_value, 15, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code' => 'cloud_height_height_2', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature3['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature3['metric_code'], $needed_feature3['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 20, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code' => 'cloud_height_depth_2', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature6['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature6['metric_code'], $needed_feature6['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 25, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code' => 'cloud_height_height_3', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature4['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature4['metric_code'], $needed_feature4['general_metric_code']),
            'is_m'             => $is_m
        );        
        
        $value = substr($this->incoming_sensor_value, 30, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_height_depth_3', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature7['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature7['metric_code'], $needed_feature7['general_metric_code']),
            'is_m'             => $is_m
        );        
        
        $value = substr($this->incoming_sensor_value, 35, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
        
		$this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_vertical_visibility', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature1['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature1['metric_code'], $needed_feature1['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 40, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code'     => 'cloud_measuring_range', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature8['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature8['metric_code'], $needed_feature8['general_metric_code']),
            'is_m'             => $is_m
        );           

		if ($length > 45)
		{
			$matches = array();
			
			if (preg_match('/^-([\d\/]+?),([\d\/]+?),([\d\/]+?),([\d\/]+?),([\d\/]{1})-$/', substr($this->incoming_sensor_value, 45), $matches))
			{
				for($i = 1; $i <= 5; $i++)
				{
					$match = $matches[$i];
					
					$is_m = ($match{0} === '/') ? 1 : 0;
					
					$amount = $is_m ? '' : $match{0};
					
					if ($i < 5)
					{
						$height = $is_m ? '' : substr($match, 1);

						$feature = isset($amount_features['cloud_amount_amount_'.$i]) ? $amount_features['cloud_amount_amount_'.$i] : array();
						
						$this->prepared_pairs[] = array(
							'feature_code'     => 'cloud_amount_amount_'.$i, 
							'period'           => 30, 
							'value'            => $amount,
							'metric_id'        => $feature['metric_id'],
							'normilized_value' => It::convertMetric($amount, $feature['metric_code'], $feature['general_metric_code']),
							'is_m'             => $is_m
						);
						
						$feature = isset($amount_features['cloud_amount_height_'.$i]) ? $amount_features['cloud_amount_height_'.$i] : array();

						$this->prepared_pairs[] = array(
							'feature_code'     => 'cloud_amount_height_'.$i, 
							'period'           => 30, 
							'value'            => $height,
							'metric_id'        => $feature['metric_id'],
							'normilized_value' => It::convertMetric($height, $feature['metric_code'], $feature['general_metric_code']),
							'is_m'             => $is_m
						);
					}
					else
					{
						$feature = isset($amount_features['cloud_amount_amount_total']) ? $amount_features['cloud_amount_amount_total'] : array();

						$this->prepared_pairs[] = array(
							'feature_code'     => 'cloud_amount_amount_total', 
							'period'           => 30, 
							'value'            => $amount,
							'metric_id'        => $feature['metric_id'],
							'normilized_value' => It::convertMetric($amount, $feature['metric_code'], $feature['general_metric_code']),
							'is_m'             => $is_m
						);
					}
				}
			}
		}
		
        return true;
    }        
    
    public function getRandomValue($features)
	{
        $s = array();
        
        //Status Word, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : ('s'. str_pad(rand(0,7600), 4, "0", STR_PAD_LEFT)); 
        
        // Cloud Height #1, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Depth #1, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Height #2, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Depth #2, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);         
        
        // Cloud Height #3, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT); 
        
        // Cloud Depth #3, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);         
        
        // Vertical Visibility, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);          
        
        // Measuring Range, 5 symbols
        $s[] = (rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0,7600), 5, "0", STR_PAD_LEFT);         

        if (rand(1, 2) > 1)
		{
			$s[] = '-';
			
			for($i = 1; $i <= 4; $i++)
			{
				if (rand(1, 10) > 9)
				{
					$s[] =  '//////';
				}
				else
				{
					$s[] = (string)rand(1, 9);
					// 1500 is a border value for some conditions.
					$s[] = str_pad(rand(0,3000), 5, "0", STR_PAD_LEFT);
				}
				
				$s[] = ',';
			}
			
			$s[] = (rand(1, 10) > 9) ? 'M' : (string)rand(1, 9);
			
			$s[] = '-';
		}
		
        return implode('', $s);
    }  
    
    public function prepareXMLValue($xml_data, $db_features)
	{
        $s = array();
       
        //Status Word, 4 symbols
        $s[] = 'MMMM'; 
        
		$features = array(
			'cloud_height_height_1',
			'cloud_height_depth_1',
			'cloud_height_height_2',
			'cloud_height_depth_2',
			'cloud_height_height_3',
			'cloud_height_depth_3',
			
			'cloud_vertical_visibility',
			'cloud_measuring_range',
		);
		
		foreach ($features as $feature)
		{
			if ($xml_data[$feature][0] == 'M')
			{
				$s[] = 'MMMMM';
			} 
			else 
			{
				$tmp = It::convertMetric($xml_data[$feature], 'feet', $db_features[$feature]);
				$s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
			}
		}
        
        $features = array(
			array(
				'cloud_amount_amount_1',
				'cloud_amount_height_1',
			),
			array(
				'cloud_amount_amount_2',
				'cloud_amount_height_2',
			),
			array(
				'cloud_amount_amount_3',
				'cloud_amount_height_3',
			),
			array(
				'cloud_amount_amount_4',
				'cloud_amount_height_4',
			),
		);
		
		foreach ($features as $feature)
		{
			if ($xml_data[$feature[0]][0] == 'M')
			{
				$s[] = '/';
			} 
			else 
			{
				$tmp = It::convertMetric($xml_data[$feature[0]], 'feet', $db_features[$feature[0]]);
				$s[] = str_pad(round($tmp), 1, "0", STR_PAD_LEFT); 
			}
			
			if ($xml_data[$feature[1]][0] == 'M')
			{
				$s[] = '/////';
			} 
			else 
			{
				$tmp = It::convertMetric($xml_data[$feature[1]], 'feet', $db_features[$feature[1]]);
				$s[] = str_pad(round($tmp), 5, "0", STR_PAD_LEFT); 
			}
		}
		
		if ($xml_data['cloud_amount_amount_total'][0] == 'M')
		{
			$s[] = '/';
		} 
		else 
		{
			$tmp = It::convertMetric($xml_data['cloud_amount_amount_total'], 'feet', $db_features['cloud_amount_amount_total']);
			$s[] = str_pad(round($tmp), 1, "0", STR_PAD_LEFT); 
		}
		
        return implode('', $s);
    }

    public static function getTrendForAwsPanel($stationData){
        return 'no';
    }
}

?>
