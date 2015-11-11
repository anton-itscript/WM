<?php

/*
 * Handler to work with data of Battery sensor
 * 
 */
class BatteryVoltageSensorHandler extends SensorHandler
{
    public $features = array(
        array(
            'feature_name'          => 'Battery Voltage', // name of measurement
            'feature_code'          => 'battery_voltage',  // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'battery_voltage', // measurement code to get possible metrics for. 
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,// is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need to display info about only one measurement for sensor
            'aws_graph_using'       => 'Battery Voltage', // if take parts in “AWS Graph” page
            'aws_panel_show'        => 1,
        )
    );  
 
    public function getSensorDescription()
	{
        return "<p>Processes string like \"<b>BV1XXX</b>\", where 
					<b>BV1</b> - device Id;
					<b>XXX</b> - battery voltage value
				</p>
				<p>Example: <b>BV1124</b> = BV1 sensor's value is 12.4(V).</p>";
    }
    
    public function getInfoForAwsPanel($sensor_pairs, $sensorList, $sensorData, $for = 'panel')
	{
        $return = array();

        $sensor_ids = array();
        $sensor_measuring_time = array();
        $last_logs_ids = array();
		
        foreach ($sensor_pairs as $value)
		{
            $sensor_ids[] = $value['sensor_id'];
            
			if (count($value['last_logs']) > 0)
			{
                $last_logs_ids[] = $value['last_logs'][0]->log_id;
                $sensor_measuring_time[$value['sensor_id']] = $value['last_logs'][0]->measuring_timestamp;
            }
			
            if (count($value['last_logs']) > 1)
			{
                $last_logs_ids[] = $value['last_logs'][1]->log_id; 
			}
        }
        
        $sensor_feature_ids = array();
        
		if (isset($sensorList['battery_voltage']) && is_array($sensorList['battery_voltage']))
		{
            foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['battery_voltage'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['battery_voltage'][$sensor_id];

				$sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
				
				$return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => '',
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
				if (isset($sensorData['battery_voltage'][$station_id][$sensor_id]) && (count($sensorData['battery_voltage'][$station_id][$sensor_id]) > 0))
				{
					$sensorValue = $sensorData['battery_voltage'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'battery_voltage');

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
						
						if (count($sensorData['battery_voltage'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['battery_voltage'][$station_id][$sensor_id][$i]->sensor_feature_value;
							if (SensorHandler::checkTrend($previousSensorValue,1))
								$sensorValues['change'] = 'up';
							else if (SensorHandler::checkTrend($previousSensorValue,-1))
								$sensorValues['change'] = 'down';

							if (isset($sensorValues['has_filter_diff']) && ($sensorValues['has_filter_diff'] == 1))
							{
								if (abs($sensorValue['sensor_feature_value'] - $previousSensorValue[1]) > $sensorValues['filter_diff'])
								{
									$sensorValues['last_filter_errors'][] = "|R1 - R0| > ". $sensorValues['filter_diff'];
								}                             
							}
						}
					}
				}

				foreach (array('filter_min', 'filter_max', 'filter_diff', 'has_filter_min', 'has_filter_max', 'has_filter_diff') as $unsetfield)
				{
					unset($sensorValues[$unsetfield]);
				}                    
			}
		}
        
        return $return;
    }    
   
    public function formatValue($value, $feature_name = 'battery_voltage')
	{
        return number_format(round($value, 1), 1);
    }
    
    public function _prepareDataPairs()
	{
        $length = strlen($this->incoming_sensor_value);
        
        if ($length != 3)
            return false;
        
        $needed_feature = array();
        
		foreach($this->sensor_features_info as $feature)
		{
            if ($feature['feature_code'] === 'battery_voltage') 
			{
                $needed_feature = $feature;
            }
        }
        
        $value = $this->incoming_sensor_value/10;
        $is_m = $this->incoming_sensor_value == 'MMM' ? 1 : 0;
		
        $this->prepared_pairs[] = array(
            'feature_code' => 'battery_voltage', 
            'period'       => 1, 
            'value'        => $value,
            'metric_id'    => $needed_feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature['metric_code'], $needed_feature['general_metric_code']),
            'is_m'         => $is_m
        );
        
        return true;
    }    
    
	
    public function getRandomValue($features)
	{
		return str_pad(rand(100, 135), 3, "0", STR_PAD_LEFT);
    }
}

?>