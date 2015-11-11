<?php

/*
 * Handler to work with data of Pressure sensor
 * 
 */

class PressureSensorHandler extends SensorHandler{
	
    public $features = array(
        array(
            'feature_name'          => '1-Minute Value', // name of measurement
            'feature_code'          => 'pressure', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'pressure', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Pressure',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        )
    );


    public $extra_features = array(
        array(
            'feature_name'          => 'Barometer Height above the station',
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
        return "Processes string with Pressure meassurement like \"PR1XXXXX\" where PR1 - device Id, XXXXX - pressure value";
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
        
		if (isset($sensorList['pressure']) && is_array($sensorList['pressure']))
		{
            foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['pressure'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['pressure'][$sensor_id];
				
                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->default) ? '' : $sensorFeature->metric->html_code,
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
				if (isset($sensorData['pressure'][$station_id][$sensor_id]) && (count($sensorData['pressure'][$station_id][$sensor_id]) > 0) &&
					($sensorData['pressure'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['pressure'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'pressure');

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

						if (count($sensorData['pressure'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['pressure'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
					unset($return[$station_id][$sensor_id][$unsetfield]);
				} 
			}
		}
        
        return $return;
    }    
    
    
    public function formatValue($value, $feature_name = 'pressure') {
        if ($feature_name == 'pressure') {
            return number_format(round($value,1),1);
        }
        return number_format(round($value,1),1);
    }       
    
    
    public function _prepareDataPairs() {
        
        $length = strlen($this->incoming_sensor_value);
        
        if ($length != 5)
            return false;
        
        $needed_feature = array();
        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'pressure') {
                $needed_feature = $feature;
            }
        }
        
        $is_m = $this->incoming_sensor_value == 'MMMMM' ? 1 : 0;
        $value = $this->incoming_sensor_value/10;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'pressure', 
            'period'           => 1, 
            'value'            => $value,
            'metric_id'        => $needed_feature['metric_id'],
            'normilized_value' => It::convertMetric($value, $needed_feature['metric_code'], $needed_feature['general_metric_code']),
            'is_m'             => $is_m
        );
        
        return true;
    }        
       
    
    public function getRandomValue($features) {
        
        $data = rand(950, 1060);
        $data = It::convertMetric($data, 'hpascal', $features['pressure']);
        $data = $data*10;
        if (strlen($data) > 5) {
            $data = substr($data, -5);
        }
        return str_pad($data, 5, "0", STR_PAD_LEFT);
    }    
    
    
    public function prepareXMLValue($xml_data, $db_features) {
        
        if ($xml_data['pressure'][0] == 'M') {
            return "MMMMM";
        }
        
        $data = It::convertMetric(round($xml_data['pressure']*10), 'hpascal', $db_features['pressure']);
        if (strlen($data) > 5) {
            $data = substr($data, -5);
        }
        return str_pad($data, 5, "0", STR_PAD_LEFT);
    }  
    
}

?>