<?php

/*
 * Handler to work with data of Rain sensor's log (Rain Station)
 * 
 */

class RainRgLogSensorHandler extends SensorHandler {
    public $features = array(
        array(
            'feature_name'          => '1-min value',
            'feature_code'          => 'rain',
            'measurement_type_code' => 'rain',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 1,
            'is_main'               => 1,
            'aws_graph_using'       => ''
        )
    );  

    public $extra_features = array(
        array(
            'feature_name'          => 'Bucket Size',
            'feature_code'          => 'bucket_size',
            'possible_constant_values' => array('0.100' => 0.1, '0.200' => 0.2, '0.250' => 0.25, '0.500' => 0.5),
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0
        )
    );      
    
    public function getSensorDescription() {
        return "Processes string like \"RN1 R60 XX XX XX XX XX XX ... XX \" where RN1 - device Id, R60 - info about rain minutes (is always R60), XX...XX - 60 pairs for rain data for every minute";
    }
    
    public function _prepareDataPairs() {
        
        $length = strlen($this->incoming_sensor_value);
        
        $initial_time = $this->incoming_measuring_timestamp;

        $needed_feature = array();
        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'rain') {
                $needed_feature = $feature;
            }
        }

        $this->prepared_pairs[] = array(
            'feature_code' => 'rain', 
            'period'       => 1, 
            'value'        => $this->incoming_sensor_value,
            'measuring_timestamp' => $this->incoming_measuring_timestamp,
            'metric_id'    => $needed_feature['metric_id'],
            'normilized_value' => It::convertMetric($this->incoming_sensor_value, $needed_feature['metric_code'], $needed_feature['general_metric_code']),
        );

        return true;
    }
    
    public function saveDataPairs($params)
	{
		if ($this->prepared_pairs)
		{
			foreach ($params['sensor_features'] as $key => $value)
			{
                if ($value['feature_code'] == 'bucket_size')
				{
					$bucket_size = $value['feature_constant_value'];
                }
            }
            
            foreach ($this->prepared_pairs as $key => $pair)
			{
				$measuring_timestamp = date('Y-m-d H:i:s', isset($pair['measuring_timestamp']) ? $pair['measuring_timestamp'] : $this->incoming_measuring_timestamp);
                
                $criteria = new CDbCriteria();
				
				$criteria->compare('DATE_FORMAT(measuring_timestamp, "%Y-%m-%d %H:%i:%s")', $measuring_timestamp);
                $criteria->compare('sensor_id', $params['sensor']->station_sensor_id);
				
				$sensor_data = SensorDataMinute::model()->find($criteria);

                if (!$sensor_data || $sensor_data->is_tmp ||  $params['rewrite_prev_values'])
				{
                    if (!$sensor_data) 
					{
                        $sensor_data = new SensorDataMinute();
                    }
                    
					$sensor_data->sensor_id = $params['sensor']->station_sensor_id;
                    $sensor_data->station_id = $params['sensor']->station_id;

                    $sensor_data->sensor_value = $pair['value'];
                    $sensor_data->metric_id = $pair['metric_id'];
                    $sensor_data->sensor_feature_normalized_value = $pair['normilized_value'];
                    
                    $sensor_data->bucket_size  = $bucket_size;
                    $sensor_data->listener_log_id = $params['listener_log_id'];
                    $sensor_data->measuring_timestamp = $measuring_timestamp;
                    $sensor_data->battery_voltage = $params['battery_voltage'];
                    $sensor_data->is_tmp = 0;
					
                    $sensor_data->save();
                }   
            }
        }
    }       
    
}

?>