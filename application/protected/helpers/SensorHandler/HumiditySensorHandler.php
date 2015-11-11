<?php

/*
 * Handler to work with data of Humidity sensor
 * 
 */

class HumiditySensorHandler extends SensorHandler {

    public $features = array(
        array(
            'feature_name'          => '1-Minute Value', // name of measurement
            'feature_code'          => 'humidity', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'humidity', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Humidity',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        )
    );
    
     public function getSensorDescription() {
         return "<p>Processes string like \"<b>HU1XXX</b>\", where <b>HU1</b> - device Id; <b>XXX</b> - humidity value</p><p>Example: <b>HU1095</b> = HU1 sensor's value is 95(%).</p>";
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
        
		if (isset($sensorList['humidity']) && is_array($sensorList['humidity']))
		{
            foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['humidity'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['humidity'][$sensor_id];
				
                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_min24_max_24',
                    'last'                   => '-',
                    'period'                 => '-',
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
				if (isset($sensorData['humidity'][$station_id][$sensor_id]) && (count($sensorData['humidity'][$station_id][$sensor_id]) > 0) &&
					($sensorData['humidity'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['humidity'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'humidity');

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

						if (count($sensorData['humidity'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['humidity'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
						$maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, 0, 'humidity');
						$sensorValues['max24'] = $maxmin['max'];
						$sensorValues['min24'] = $maxmin['min'];
                        $sensorValues['mami_title'] = $maxmin['mami_title'];

                        $maxmin = $this->getMaxMinInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, 0, 'humidity');
						$sensorValues['max24_y'] = $maxmin['max'];
						$sensorValues['min24_y'] = $maxmin['min'];
                        $sensorValues['mami_title_y'] = $maxmin['mami_title'];

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
    
    public function formatValue($value, $feature_name = 'humidity') {
        return round($value,1);
    }    
    
    public function _prepareDataPairs() {
        
        $length = strlen($this->incoming_sensor_value);
        
        if ($length != 3)
            return false;
        
        $needed_feature = array();
        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'humidity') {
                $needed_feature = $feature;
            }
        }
        
        $value = $this->incoming_sensor_value;
        $is_m = $value == 'MMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code' => 'humidity', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature['metric_code'], $needed_feature['general_metric_code']),
            'is_m'             => $is_m
        );
        
        return true;
    }        
    
    public function getRandomValue($features) {
        
        return str_pad(rand(50, 100), 3, "0", STR_PAD_LEFT);
    }   
    
    public function prepareXMLValue($xml_data, $db_features) {
        
        if ($xml_data['humidity'][0] == 'M') {
            return 'MMM';
        }
        return str_pad(abs($xml_data['humidity']), 3, "0", STR_PAD_LEFT);
        
    }    
    
}

?>