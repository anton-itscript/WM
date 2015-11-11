<?php

/*
 * Handler to work with data of Solar Radiation sensor
 * 
 */

class SolarRadiationSensorHandler extends SensorHandler
{
    /**
     * @var AWSFormatConfigForm
     */
    protected $awsFormat;

    public $features = array(
        array(
            'feature_name'          => 'Value since last tx', // name of measurement
            'feature_code'          => 'solar_radiation_in_period', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'solar_radiation', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 1, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Solar Radiation',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        ),			
		
        array(
            'feature_name'          => 'Value total for day',
            'feature_code'          => 'solar_radiation_in_day',
            'measurement_type_code' => 'solar_radiation',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 1,
            'is_main'               => 0,
            'aws_graph_using'       => ''
        )
    );
    
    public function getSensorDescription()
	{
        return "<p>Processes string like \"<b>SR1 DDD RRRR XX FFFF ZZ</b>\",  where: <b>SR1</b> - device ID; <b>DDD</b> - period of measurement; <b>RRRR</b>:  solar radiation value since last transmission; <b>XX</b> - 2 digit exponent for RRRR; <b>FFFF</b> - total value for day; <b>ZZ</b> - 2 digit exponent for FFFF</p><p>Example: <b>SR1120123416123416</b> = SR1 sensor's value for 120 minutes is 0.1234x2^16 and total for day is 0.1234x2^16.</p>";
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
		
		if (isset($sensorList['solar_radiation_in_period']) && is_array($sensorList['solar_radiation_in_period']))
		{
			foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['solar_radiation_in_period'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['solar_radiation_in_period'][$sensor_id];
				
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
				if (isset($sensorData['solar_radiation_in_period'][$station_id][$sensor_id]) && (count($sensorData['solar_radiation_in_period'][$station_id][$sensor_id]) > 0) &&
					($sensorData['solar_radiation_in_period'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['solar_radiation_in_period'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'solar_radiation_in_period');
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

						if (count($sensorData['solar_radiation_in_period'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['solar_radiation_in_period'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
//						$sensorValues['total_today_calculated'] = $this->getTotalFromHour($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 0, 'solar_radiation_in_day');
                        $total = $this->getTotalInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, 'solar_radiation_in_day');
                        $sensorValues['total_today_calculated'] = $total['total'];
                        $sensorValues['total_today_title'] = $total['total_title'];
                        $total = $this->getTotalInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, 'solar_radiation_in_day');
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
    
    
    
    public function formatValue($value, $feature_name = '')
	{
		if ($feature_name == 'solar_radiation_in_period')
		{
			return number_format(round($value, 1), 0);
        } 
		else if ($feature_name == 'solar_radiation_in_day')
		{
			return number_format(round($value, 1), 0);
        }
  
        return '0';
    }   
    
    public function _prepareDataPairs() {

        if ($this->awsFormat->isOldAWSFormat()) {
            $length = strlen($this->incoming_sensor_value);

            if ($length <> 15)
                return false;

            $needed_feature_1 = array();
            $needed_feature_2 = array();
            foreach ($this->sensor_features_info as $feature) {
                if ($feature['feature_code'] == 'solar_radiation_in_period') {
                    $needed_feature_1 = $feature;
                } elseif ($feature['feature_code'] == 'solar_radiation_in_day') {
                    $needed_feature_2 = $feature;
                }
            }


            $info_1 = intval(substr($this->incoming_sensor_value, 0, 3));

            $pow = substr($this->incoming_sensor_value, 7, 2);
            $value = (substr($this->incoming_sensor_value, 3, 4) / 1000) * pow(2, $pow);

            $tmp = substr($this->incoming_sensor_value, 3, 4) . substr($this->incoming_sensor_value, 7, 2);
            $is_m = ($tmp == 'MMMMMM') ? 1 : 0;
            $this->prepared_pairs['solar_radiation_in_period'] = array(
                'feature_code' => 'solar_radiation_in_period',
                'period' => $info_1,
                'value' => $value,
                'exp_value' => $pow,
                'metric_id' => $needed_feature_1['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
                'is_m' => $is_m
            );

            $pow = substr($this->incoming_sensor_value, 13, 2);
            $value = (substr($this->incoming_sensor_value, 9, 4) / 1000) * pow(2, $pow);

            $tmp = substr($this->incoming_sensor_value, 9, 4) . substr($this->incoming_sensor_value, 13, 2);
            $is_m = ($tmp == 'MMMMMM') ? 1 : 0;

            $this->prepared_pairs['solar_radiation_in_day'] = array(
                'feature_code' => 'solar_radiation_in_day',
                'period' => 1440, //86400,
                'value' => $value,
                'exp_value' => $pow,
                'metric_id' => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m' => $is_m
            );
            return true;
        } else {

            $length = strlen($this->incoming_sensor_value);

            if ($length <> 12)
                return false;

            $needed_feature_1 = array();
            $needed_feature_2 = array();
            foreach ($this->sensor_features_info as $feature) {
                if ($feature['feature_code'] == 'solar_radiation_in_period') {
                    $needed_feature_1 = $feature;
                } elseif ($feature['feature_code'] == 'solar_radiation_in_day') {
                    $needed_feature_2 = $feature;
                }
            }


           // $info_1 = intval(substr($this->incoming_sensor_value, 0, 3));

            $pow = substr($this->incoming_sensor_value, 4, 2);
            $value = (substr($this->incoming_sensor_value, 0, 4) / 1000) * pow(2, $pow);

            $tmp = substr($this->incoming_sensor_value, 0, 4) . substr($this->incoming_sensor_value, 4, 2);
            $is_m = ($tmp == 'MMMMMM') ? 1 : 0;
            $this->prepared_pairs['solar_radiation_in_period'] = array(
                'feature_code' => 'solar_radiation_in_period',
                //'period' => $info_1,
                'period' => 1,
                'value' => $value,
                'exp_value' => $pow,
                'metric_id' => $needed_feature_1['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
                'is_m' => $is_m
            );

            $pow = substr($this->incoming_sensor_value, 10, 2);
            $value = (substr($this->incoming_sensor_value, 6, 4) / 1000) * pow(2, $pow);

            $tmp = substr($this->incoming_sensor_value, 6, 4) . substr($this->incoming_sensor_value, 10, 2);
            $is_m = ($tmp == 'MMMMMM') ? 1 : 0;

            $this->prepared_pairs['solar_radiation_in_day'] = array(
                'feature_code' => 'solar_radiation_in_day',
                'period' => 1440, //86400,
                'value' => $value,
                'exp_value' => $pow,
                'metric_id' => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m' => $is_m
            );
            return true;
        }
    }        
    
    public function getRandomValue($features) {

        if ($this->awsFormat->isOldAWSFormat()) {

            $period = str_pad('60', 3, "0", STR_PAD_LEFT);

            $ac_period = rand(0, 3600000);
            $ac_total = rand($ac_period, 86400000);


            $ac_period = It::convertMetric($ac_period, 'joule_per_sq_meter', $features['solar_radiation_in_period']);
            $ac_total = It::convertMetric($ac_total, 'joule_per_sq_meter', $features['solar_radiation_in_day']);


            $ac_period_tmp = $this->getPow($ac_period);
            $ac_period_tmp_str = str_pad(round($ac_period_tmp[0], 3) * 1000, 4, '0', STR_PAD_LEFT) . str_pad($ac_period_tmp[1], 2, "0", STR_PAD_LEFT);

            $ac_total_tmp = $this->getPow($ac_total);
            $ac_total_tmp_str = str_pad(round($ac_total_tmp[0], 3) * 1000, 4, '0', STR_PAD_LEFT) . str_pad($ac_total_tmp[1], 2, "0", STR_PAD_LEFT);

            return $period . $ac_period_tmp_str . $ac_total_tmp_str;

        } else {

            $ac_period = rand(0, 3600000);
            $ac_total = rand($ac_period, 86400000);


            $ac_period = It::convertMetric($ac_period, 'joule_per_sq_meter', $features['solar_radiation_in_period']);
            $ac_total = It::convertMetric($ac_total, 'joule_per_sq_meter', $features['solar_radiation_in_day']);


            $ac_period_tmp = $this->getPow($ac_period);
            $ac_period_tmp_str = str_pad(round($ac_period_tmp[0], 3) * 1000, 4, '0', STR_PAD_LEFT) . str_pad($ac_period_tmp[1], 2, "0", STR_PAD_LEFT);

            $ac_total_tmp = $this->getPow($ac_total);
            $ac_total_tmp_str = str_pad(round($ac_total_tmp[0], 3) * 1000, 4, '0', STR_PAD_LEFT) . str_pad($ac_total_tmp[1], 2, "0", STR_PAD_LEFT);

            return  $ac_period_tmp_str . $ac_total_tmp_str;
        }
    }    
    
    private function getPow($val) {
        
        $pow = 0;
        while ($val > 2) {
            $val = $val / 2;
            $pow ++;
        }
        return array($val, $pow);
    }
    
    
    public function prepareXMLValue($xml_data, $db_features) {

        if ($this->awsFormat->isOldAWSFormat()) {
            $period = str_pad($xml_data['period'], 3, "0", STR_PAD_LEFT);

            if ($xml_data['solar_radiation_in_period'][0] == 'M') {
                $ac_period_tmp_str = 'MMMMMM';
            } else {
                $ac_period = It::convertMetric($xml_data['solar_radiation_in_period'], 'joule_per_sq_meter', $db_features['solar_radiation_in_period']);
                $ac_period_tmp = $this->getPow($ac_period);
                $ac_period_tmp_str = str_pad(round($ac_period_tmp[0], 3) * 1000, 4, '0', STR_PAD_LEFT) . str_pad($ac_period_tmp[1], 2, "0", STR_PAD_LEFT);
            }

            $ac_total_tmp_str = 'MMMMMM';

            return $period . $ac_period_tmp_str . $ac_total_tmp_str;
        } else {

            $period = str_pad($xml_data['period'], 3, "0", STR_PAD_LEFT);

            if ($xml_data['solar_radiation_in_period'][0] == 'M') {
                $ac_period_tmp_str = 'MMMMMM';
            } else {
                $ac_period = It::convertMetric($xml_data['solar_radiation_in_period'], 'joule_per_sq_meter', $db_features['solar_radiation_in_period']);
                $ac_period_tmp = $this->getPow($ac_period);
                $ac_period_tmp_str = str_pad(round($ac_period_tmp[0], 3) * 1000, 4, '0', STR_PAD_LEFT) . str_pad($ac_period_tmp[1], 2, "0", STR_PAD_LEFT);
            }

            $ac_total_tmp_str = 'MMMMMM';

            return   $ac_period_tmp_str . $ac_total_tmp_str;
        }
        
    }

    public function __construct($logger)
    {
        $this->awsFormat = new AWSFormatConfigForm;

        parent::__construct($logger);
    }
}

?>