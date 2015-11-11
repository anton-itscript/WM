<?php

/*
 * Handler to work with data of DLM13M Visibility sensor
 * 
 */

class VisibilityAwsDlm13mSensorHandler extends SensorHandler
{
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
		array(
            'feature_name'          => 'Extinction coeff.', // name of measurement
            'feature_code'          => 'extinction', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'extinction', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 0, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => ''  // if takes part in “AWS Graph” page
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
            'aws_graph_using'       => ''
        ),
    );
    
    public function getSensorDescription()
	{
		return "Processes string like \"<b>VI1 VVVVV VVVVV U xxx.xx -CCC-</b>\",<br/> 
                 where <b>VI1</b> - sensor Id; <br/>
                 <b>VVVVV</b> - visibility 1 minute average <br/>
                 <b>VVVVV</b> - visibility 10 minute average<br/>
                 <b>xxx.xx</b> - Extinction coefficient = ln(1/0.05)/(1_min_average)<br/>
                 <b>-CCC-</b> - -CCC- sensor status. Variable length: 3-5 (including 2 hyphens).<br/>";
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
		
		$features = array(
			'visibility_1',
			'extinction',
		);
		
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
					$sensor_feature_ids2[$sensor_id][$feature] = $sensorFeature->sensor_feature_id;

					if (!isset($return[$sensorFeature->sensor->station_id][$sensor_id]))
					{
						$return[$sensorFeature->sensor->station_id][$sensor_id] = array(
							'sensor_display_name'    => $sensorFeature->sensor->display_name,
							'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
							'group'                  => 'visibility',
							'timezone_id'            => $sensorFeature->sensor->station->timezone_id,
						);                    
					}

					if ($feature === 'visibility_1')
					{
						$return[$sensorFeature->sensor->station_id][$sensor_id][$sensorFeature->feature_code] = array(
							'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
							'last'                   => '-',
							'max24'                  => '-',
							'min24'                  => '-',
							'status'				 => '-',
							'change'                 => 'no',
							'filter_max'             => $sensorFeature->default->filter_max,
							'filter_min'             => $sensorFeature->default->filter_min,
							'filter_diff'            => $sensorFeature->default->filter_diff,
							'has_filter_max'         => $sensorFeature->has_filter_max,
							'has_filter_min'         => $sensorFeature->has_filter_min,
							'has_filter_diff'        => $sensorFeature->has_filter_diff,  
						);
					}
					else
					{
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
		}
        
        if (count($last_logs_ids) === 0)
		{
            return $return;
        }
        
		
		foreach ($return as $station_id => &$sensors) 
		{
			foreach ($sensors as $sensor_id => &$sensorValues) 
			{ 
				foreach ($features as $feature)
				{	
					if (isset($sensorData[$feature][$station_id][$sensor_id]) && (count($sensorData[$feature][$station_id][$sensor_id]) > 0) &&
						($sensorData[$feature][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
					{
						$sensorValue = $sensorData[$feature][$station_id][$sensor_id][0];

						if ($sensorValue->is_m != 1)
						{
							$sensorValues[$feature]['last'] = $this->formatValue($sensorValue->sensor_feature_value, $feature);

							if (isset($sensorValues[$feature]['has_filter_max']) && ($sensorValues[$feature]['has_filter_max'] == 1))
							{
								if ($sensorValue->sensor_feature_value > $sensorValues[$feature]['filter_max'])
								{
									$sensorValues[$feature]['last_filter_errors'][] = "R > ". $sensorValues[$feature]['filter_max'];
								}
							}

							if (isset($sensorValues[$feature]['has_filter_min']) && ($sensorValues[$feature]['has_filter_min'] == 1))
							{
								if ($sensorValue->sensor_feature_value < $sensorValues[$feature]['filter_min'])
								{
									$sensorValues[$feature]['last_filter_errors'][] = "R < ". $sensorValues[$feature]['filter_min'];
								}            
							}

							if (count($sensorData[$feature][$station_id][$sensor_id]) > 3)
							{
                                $previousSensorValue = array();
                                for($i=0;$i<4;$i++)
                                    $previousSensorValue[]=$sensorData[$feature][$station_id][$sensor_id][$i]->sensor_feature_value;
                                if (SensorHandler::checkTrend($previousSensorValue,1))
                                    $sensorValues['change'] = 'up';
                                else if (SensorHandler::checkTrend($previousSensorValue,-1))
                                    $sensorValues['change'] = 'down';

								if (isset($sensorValues[$feature]['has_filter_diff']) && ($sensorValues[$feature]['has_filter_diff'] == 1))
								{
									if (abs($sensorValue->sensor_feature_value - $previousSensorValue[1]) > $sensorValues[$feature]['filter_diff'])
									{
										$sensorValues[$feature]['last_filter_errors'][] = "|R1 - R0| > ". $sensorValues[$feature]['filter_diff'];
									}   
								}
							}
						}

						if (isset($sensor_measuring_time[$sensor_id]))
						{
							$maxmin = $this->getMaxMinFromHour($sensor_feature_ids2[$sensor_id][$feature], strtotime($sensor_measuring_time[$sensor_id]), 0, $feature);

							$sensorValues[$feature]['max24'] = $maxmin['max'];
							$sensorValues[$feature]['min24'] = $maxmin['min'];
						}
					}

					foreach (array('filter_min', 'filter_max', 'filter_diff', 'has_filter_min', 'has_filter_max', 'has_filter_diff') as $unsetfield)
					{
						unset($return[$station_id][$sensor_id][$unsetfield]);
					}
				}
				
				if (isset($sensorData['status'][$station_id][$sensor_id]) && (count($sensorData['status'][$station_id][$sensor_id]) > 0) &&
						($sensorData['status'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['status'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['visibility_1']['status'] = $sensorValue->sensor_feature_value;
					}
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
		else if ($feature_name === 'status')
		{
            return $value;
        }
		else if ($feature_name === 'extinction')
		{
            return $value;
        }
		
        return '';
    }     
    
    public function _prepareDataPairs()
	{
        $length = strlen($this->incoming_sensor_value);
        
		if ($length < 17 || $length > 22)
            return false;
        
        $needed_feature_1 = array();
        $needed_feature_2 = array();
		$needed_feature_3 = array();
		$needed_feature_4 = array();
		
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
			else if ($feature['feature_code'] == 'status')
			{
				$needed_feature_3 = $feature;
            }
			else if ($feature['feature_code'] == 'extinction')
			{
				$needed_feature_4 = $feature;
            }
        }
        
		$extinctionValue = substr($this->incoming_sensor_value, 11, 6);
        $is_m = ($extinctionValue === 'MMMMMM') ? 1 : 0;
        
		$this->prepared_pairs[] = array(
            'feature_code' => 'extinction', 
            'period'       => 1, 
            'value'        => $extinctionValue,
            'metric_id'    => $needed_feature_4['metric_id'],
            'normilized_value' => It::convertMetric($extinctionValue, $needed_feature_4['metric_code'], $needed_feature_4['general_metric_code']),
            'is_m'         => $is_m
        );
		
        $value = substr($this->incoming_sensor_value, 0, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
		
		// If 1 min value is missing then calculate it using extinction coefficient
		if ($is_m === 1)
		{
			$extCoeff = (float)$extinctionValue;
			
			if ($extCoeff != 0)
			{
				$value = 3 / $extCoeff; // ~= ln(1 / 0.05) / $extCoeff
			}
		}
        
		$this->prepared_pairs[] = array(
			'feature_code' => 'visibility_1', 
			'period'       => 1, 
			'value'        => $value,
			'metric_id'    => $needed_feature_1['metric_id'],
			'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
			'is_m'         => $is_m
		);
		
		$value = substr($this->incoming_sensor_value, 5, 5);
        $is_m = ($value === 'MMMMM') ? 1 : 0;
        
		$this->prepared_pairs[] = array(
            'feature_code' => 'visibility_10', 
            'period'       => 10, 
            'value'        => $value,
            'metric_id'    => $needed_feature_2['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
            'is_m'         => $is_m
        );
		
		$value = substr($this->incoming_sensor_value, 17, 5);
		$value = preg_replace('/^-(.*?)-$/', '$1', $value);
		
		$is_m = ($value === '') ? 1 : 0;
        
		$this->prepared_pairs[] = array(
            'feature_code' => 'status', 
            'period'       => 1, 
            'value'        => $value,
            'metric_id'    => $needed_feature_3['metric_id'],
            'normilized_value' => $value, // has no metric
            'is_m'         => $is_m,
        );
        
		return true;
    }
    
	public function getRandomValue($features)
	{
		// 10% to get null value (MMMM...)
		$result = ((rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0, 80000), 5, "0", STR_PAD_LEFT));
        $result .= ((rand(1, 10) > 9) ? 'MMMMM' : str_pad(rand(0, 80000), 5, "0", STR_PAD_LEFT));
		$result .= 'M';
		
		$result .= ((rand(1, 10) > 9) ? 'MMMMMM' : str_pad(number_format(rand(0, 2999) / 3, 2, '.', ''), 6, "0", STR_PAD_LEFT));
				
		$result .= ((rand(1, 10) > 9) ? '' : ('-'. str_repeat('C', rand(1, 3)) .'-'));
		
        return $result;
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
        
		if ($xml_data['extinction'][0] === 'M')
		{
			$tmp4 = 'MMMMMM';
        } 
		else
		{
			$tmp4  = str_pad($xml_data['extinction'], 5, "0", STR_PAD_LEFT);
        }
		
		$tmp5 = '';
		
		if (isset($xml_data['status']))
		{
			if ($xml_data['status'][0] === 'M')
			{
				$tmp5 = 'MMMMMM';
			} 
			else
			{
				$tmp5  = $xml_data['status'][0];
			}
		}
			
        
		return $tmp1.$tmp2.$tmp3.$tmp4.$tmp5;        
    }    
}

?>
