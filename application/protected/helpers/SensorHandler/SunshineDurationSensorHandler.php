<?php

/*
 * Handler to work with data of Sunshine sensor
 * 
 */

class SunshineDurationSensorHandler extends SensorHandler {

    /**
     * @var AWSFormatConfigForm
     */
    protected $awsFormat;

    public $features = array(
        array(
            'feature_name'          => 'Value since last tx',
            'feature_code'          => 'sun_duration_in_period',
            'measurement_type_code' => 'sun_duration',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 1,
            'is_main'               => 1,
            'aws_graph_using'       => 'Sunshine Duration',
            'aws_panel_show'        => 1,
        ),
        array(
            'feature_name'          => 'Value total for day',
            'feature_code'          => 'sun_duration_in_day',
            'measurement_type_code' => 'sun_duration',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 1,
            'is_main'               => 0,
            'aws_graph_using'       => ''
        )
    );
    
    public function getSensorDescription() {
        return "<p>Processes string like \"<b>SD1 XXX MMM SSSS</b>\", where <b>SD1</b> - device ID; <b>XXX</b> - period of measurement; <b>MMM</b> - sunshine duration in minutes since last transmission; <b>SSS</b> - sunshine duration for total day in minutes</p><p>Example: <b>SD1 120 030 0600</b> - SD1 sensor's value for 120 minute is 30 minutes, and for total day - is 60 minutes</p>";
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
		
        if (isset($sensorList['sun_duration_in_period']) && is_array($sensorList['sun_duration_in_period']))
		{
			foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['sun_duration_in_period'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['sun_duration_in_period'][$sensor_id];
				
                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_period_today',
                    'last'                   => '-',
                    'period'                 => '-',
                    'max24'                  => '-',
                    'min24'                  => '-',
                    'change'                 => 'no',
                    'total_today_calculated' => '-',
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
				if (isset($sensorData['sun_duration_in_period'][$station_id][$sensor_id]) && (count($sensorData['sun_duration_in_period'][$station_id][$sensor_id]) > 0) &&
					($sensorData['sun_duration_in_period'][$station_id][$sensor_id][0]->listener_log_id == $last_logs_per_station[$station_id][0]))
				{
					$sensorValue = $sensorData['sun_duration_in_period'][$station_id][$sensor_id][0];
					   
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'sun_duration_in_period');
						$sensorValues['period'] = $sensorValue->period;
						
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

						if (count($sensorData['sun_duration_in_period'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['sun_duration_in_period'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
//						$sensorValues['total_today_calculated'] = $this->getTotalFromHour($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 0, 'sun_duration_in_day');
                        $total = $this->getTotalInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, 'sun_duration_in_day');
                        $sensorValues['total_today_calculated'] = $total['total'];
                        $sensorValues['total_today_title'] = $total['total_title'];
                        $total = $this->getTotalInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, 'sun_duration_in_day');
                        $sensorValues['total_today_calculated_y'] = $total['total'];
                        $sensorValues['total_today_title_y'] = $total['total_title'];
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
    
    public function formatValue($value, $feature_name = '') {
        $res = number_format(round($value,1),0);
        return $res;
    }       
    
    public function _prepareDataPairs() {
        if ($this->awsFormat->isOldAWSFormat()) {
            $length = strlen($this->incoming_sensor_value);

            if ($length <> 10)
                return false;

            $info_1 = intval(substr($this->incoming_sensor_value, 0, 3));

            $needed_feature_1 = array();
            $needed_feature_2 = array();
            foreach ($this->sensor_features_info as $feature) {
                if ($feature['feature_code'] == 'sun_duration_in_period') {
                    $needed_feature_1 = $feature;
                } elseif ($feature['feature_code'] == 'sun_duration_in_day') {
                    $needed_feature_2 = $feature;
                }
            }

            $value = substr($this->incoming_sensor_value, 3, 3);
            $is_m = ($value == 'MMM') ? 1 : 0;
            $this->prepared_pairs['sun_duration_in_period'] = array(
                'feature_code' => 'sun_duration_in_period',
                'period' => $info_1,
                'value' => $value,
                'metric_id' => $needed_feature_1['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
                'is_m' => $is_m
            );

            $value = substr($this->incoming_sensor_value, 6, 4);
            $is_m = ($value == 'MMMM') ? 1 : 0;
            $this->prepared_pairs['sun_duration_in_day'] = array(
                'feature_code' => 'sun_duration_in_day',
                'period' => 1440, //86400,
                'value' => $value,
                'metric_id' => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m' => $is_m
            );

            return true;
        } else {
            $length = strlen($this->incoming_sensor_value);

            if ($length <> 7)
                return false;

//            $info_1 = intval(substr($this->incoming_sensor_value, 0, 3));

            $needed_feature_1 = array();
            $needed_feature_2 = array();
            foreach ($this->sensor_features_info as $feature) {
                if ($feature['feature_code'] == 'sun_duration_in_period') {
                    $needed_feature_1 = $feature;
                } elseif ($feature['feature_code'] == 'sun_duration_in_day') {
                    $needed_feature_2 = $feature;
                }
            }

            $value = substr($this->incoming_sensor_value, 0, 3);
            $is_m = ($value == 'MMM') ? 1 : 0;
            $this->prepared_pairs['sun_duration_in_period'] = array(
                'feature_code' => 'sun_duration_in_period',
//                'period' => $info_1,
                'period' => 1,
                'value' => $value,
                'metric_id' => $needed_feature_1['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
                'is_m' => $is_m
            );

            $value = substr($this->incoming_sensor_value, 3, 4);
            $is_m = ($value == 'MMMM') ? 1 : 0;
            $this->prepared_pairs['sun_duration_in_day'] = array(
                'feature_code' => 'sun_duration_in_day',
                'period' => 1440, //86400,
                'value' => $value,
                'metric_id' => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m' => $is_m
            );

            return true;
        }
    }        
    
    public function getRandomValue($features)
	{
        if ($this->awsFormat->isOldAWSFormat()) {

            $period = str_pad('60', 3, "0", STR_PAD_LEFT);
            $ac_period = str_pad(rand(0, 60), 3, "0", STR_PAD_LEFT);
            $ac_total = str_pad(rand($ac_period, 1440), 4, "0", STR_PAD_LEFT);

            return $period . $ac_period . $ac_total;

        } else {

            $ac_period = str_pad(rand(0, 60), 3, "0", STR_PAD_LEFT);
            $ac_total = str_pad(rand($ac_period, 1440), 4, "0", STR_PAD_LEFT);

            return $ac_period . $ac_total;
        }
    }      
	
	public function prepareXMLValue($xml_data, $db_features)
	{
        if ($this->awsFormat->isOldAWSFormat()) {

            $data_1 = str_pad($xml_data['period'], 3, "0", STR_PAD_LEFT);

            if (isset($xml_data['sun_duration_in_period']) && ($xml_data['sun_duration_in_period'] != 'M')) {
                $tmp = It::convertMetric($xml_data['sun_duration_in_period'], 'minute', $db_features['sun_duration_in_period']);
                $data_2 = str_pad(round($tmp), 3, "0", STR_PAD_LEFT);
            } else {
                $data_2 = str_repeat('M', 3);
            }

            if (isset($xml_data['sun_duration_in_day']) && ($xml_data['sun_duration_in_day'] != 'M')) {
                $tmp = It::convertMetric($xml_data['sun_duration_in_day'], 'minute', $db_features['sun_duration_in_day']);
                $data_3 = str_pad(round($tmp), 4, "0", STR_PAD_LEFT);
            } else {
                $data_3 = str_repeat('M', 4);
            }

            return $data_1 . $data_2 . $data_3;
        } else {

            if (isset($xml_data['sun_duration_in_period']) && ($xml_data['sun_duration_in_period'] != 'M')) {
                $tmp = It::convertMetric($xml_data['sun_duration_in_period'], 'minute', $db_features['sun_duration_in_period']);
                $data_2 = str_pad(round($tmp), 3, "0", STR_PAD_LEFT);
            } else {
                $data_2 = str_repeat('M', 3);
            }

            if (isset($xml_data['sun_duration_in_day']) && ($xml_data['sun_duration_in_day'] != 'M')) {
                $tmp = It::convertMetric($xml_data['sun_duration_in_day'], 'minute', $db_features['sun_duration_in_day']);
                $data_3 = str_pad(round($tmp), 4, "0", STR_PAD_LEFT);
            } else {
                $data_3 = str_repeat('M', 4);
            }

            return $data_2 . $data_3;
        }
    }


    public function __construct($logger)
    {
        $this->awsFormat = new AWSFormatConfigForm;

        parent::__construct($logger);
    }
}

?>