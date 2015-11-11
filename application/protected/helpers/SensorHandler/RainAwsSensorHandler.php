<?php

/*
 * Handler to work with data of Rain sensor (AWS)
 * 
 */

class RainAwsSensorHandler extends SensorHandler {

    /**
     * @var AWSFormatConfigForm
     */
    protected $awsFormat;

    public $features = array(
        array(
            'feature_name'          => 'Value since last tx', // name of measurement
            'feature_code'          => 'rain_in_period', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'rain', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 1, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Rain',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        ),
        array(
            'feature_name'          => 'Value total for day',
            'feature_code'          => 'rain_in_day',
            'measurement_type_code' => 'rain',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 1,
            'is_main'               => 0,
            'aws_graph_using'       => ''
        )
    );

    public $extra_features = array(
        array(
            'feature_name'          => 'Height of sensor above the ground',
            'feature_code'          => 'height',
            'measurement_type_code' => 'height',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0

        )
    );

    public function getSensorDescription() {
         return "Processes string like \"RN1 XXX ZZZZZZ CCCCCC\" where RN1 - device Id, XXX - period 1-180 minutes, ZZZZZZ - rain data accumulation over the period, CCCCCC - rain daily total";
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
        
		if (isset($sensorList['rain_in_period']) && is_array($sensorList['rain_in_period']))
		{
			foreach ($sensor_ids as $sensor_id)
			{
				if (!isset($sensorList['rain_in_period'][$sensor_id]))
					continue;
					
				$sensorFeature = $sensorList['rain_in_period'][$sensor_id];
				
                $sensor_feature_ids[] = $sensorFeature->sensor_feature_id;
                $sensor_feature_ids2[$sensor_id] = $sensorFeature->sensor_feature_id;
                
                $return[$sensorFeature->sensor->station_id][$sensor_id] = array(
                    'sensor_display_name'    => $sensorFeature->sensor->display_name,
                    'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
                    'metric_html_code'       => is_null($sensorFeature->metric) ? '' : $sensorFeature->metric->html_code,
                    'group'                  => 'last_hour_day',
                    'last'                   => '-',
                    'period'                 => '-',
                    'max24'                  => '-',
                    'min24'                  => '-',
                    'change'                 => 'no',
					'total_hour'             => '-',
                    'total_today'            => '-',
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
        
        if (!$last_logs_ids)
		{
            return $return;
        }
        
		foreach ($return as $station_id => &$sensors) 
		{
			foreach ($sensors as $sensor_id => &$sensorValues) 
			{
				if (isset($sensorData['rain_in_period'][$station_id][$sensor_id]) && (count($sensorData['rain_in_period'][$station_id][$sensor_id]) > 0) &&
					($sensorData['rain_in_period'][$station_id][$sensor_id][0]->listener_log_id == (isset($last_logs_per_station[$station_id][0]) ? $last_logs_per_station[$station_id][0] : -1)))
				{
					$sensorValue = $sensorData['rain_in_period'][$station_id][$sensor_id][0];
					
					if ($sensorValue->is_m != 1)
					{
						$sensorValues['last'] = $this->formatValue($sensorValue->sensor_feature_value, 'rain_in_period');
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

						if (count($sensorData['rain_in_period'][$station_id][$sensor_id]) > 3)
						{
                            $previousSensorValue = array();
                            for($i=0;$i<4;$i++)
                                $previousSensorValue[]=$sensorData['rain_in_period'][$station_id][$sensor_id][$i]->sensor_feature_value;
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
						$tmp = $this->getTotalHour($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), $sensorValues['timezone_id']);

						$sensorValues['total_hour'] = $tmp == '' ? '0' : $this->formatValue($tmp);
//                        $sensorValues['total_today'] = $this->getTotalFromHour($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 9, 'rain_in_period');
                        $total = $this->getTotalInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 1, 'rain_in_period');
                        $sensorValues['total_today'] = $total['total'];
                        $sensorValues['total_today_title'] = $total['total_title'];
                        $total = $this->getTotalInDay($sensor_feature_ids2[$sensor_id], strtotime($sensor_measuring_time[$sensor_id]), 2, 'rain_in_period');
                        $sensorValues['total_today_y'] = $total['total'];
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
    
    protected function getTotalHour($sensor_feature_id, $measuring_timestamp, $timezone_id)
	{
        $hour_start = mktime(date('H', $measuring_timestamp),0,0, date('m', $measuring_timestamp), date('d', $measuring_timestamp), date('Y', $measuring_timestamp) );
        
		$sql = "SELECT SUM(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `total_today`
                FROM `".SensorData::model()->tableName()."`
                WHERE `sensor_feature_id` = '".$sensor_feature_id."' 
                  AND `measuring_timestamp` <= '".date('Y-m-d H:i:s', $measuring_timestamp)."' 
                  AND `measuring_timestamp` > '".date('Y-m-d H:i:s', $hour_start)."'
                  AND `is_m` = '0'";
        
        $res = Yii::app()->db->createCommand($sql)->queryScalar();
        
        return $res;
    }
    
    public function formatValue($value, $feature_name = 'rain_in_period')
	{
		return number_format($value, 3);
    }
    
    public function _prepareDataPairs()
	{

        $this->_logger->log(__METHOD__. ': ' . print_r($this->incoming_sensor_value,1));

        if ($this->awsFormat->isOldAWSFormat()) {

            $length = strlen($this->incoming_sensor_value);

            if ($length <> 15)
                return false;

            $info_1 = intval(substr($this->incoming_sensor_value, 0, 3));

            $needed_feature_1 = array();
            $needed_feature_2 = array();

            foreach ($this->sensor_features_info as $feature) {
                if ($feature['feature_code'] == 'rain_in_period') {
                    $needed_feature_1 = $feature;
                } elseif ($feature['feature_code'] == 'rain_in_day') {
                    $needed_feature_2 = $feature;
                }
            }

            $value_1 = substr($this->incoming_sensor_value, 3, 6);
            $is_m = $value_1 == 'MMMMMM' ? 1 : 0;

            $value_1 = $value_1 / 1000;

            $this->prepared_pairs['rain_in_period'] = array(
                'feature_code' => 'rain_in_period',
                'period' => $info_1,
                'value' => $value_1,
                'metric_id' => $needed_feature_1['metric_id'],
                'normilized_value' => It::convertMetric($value_1, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
                'is_m' => $is_m
            );

            $value_2 = substr($this->incoming_sensor_value, 9, 6);
            $is_m = $value_2 == 'MMMMMM' ? 1 : 0;

            $value_2 = $value_2 / 1000;

            $this->prepared_pairs['rain_in_day'] = array(
                'feature_code' => 'rain_in_day',
                'period' => 1440,
                'value' => $value_2,
                'metric_id' => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value_2, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m' => $is_m
            );

            return true;

        } else {

            $length = strlen($this->incoming_sensor_value);

            if ($length <> 8)
                return false;

          //  $info_1 = intval(substr($this->incoming_sensor_value, 0, 3));

            $needed_feature_1 = array();
            $needed_feature_2 = array();

            foreach ($this->sensor_features_info as $feature) {
                if ($feature['feature_code'] == 'rain_in_period') {
                    $needed_feature_1 = $feature;
                } elseif ($feature['feature_code'] == 'rain_in_day') {
                    $needed_feature_2 = $feature;
                }
            }

            $value_1 = substr($this->incoming_sensor_value, 0, 4);
            $is_m = $value_1 == 'MMMM' ? 1 : 0;

            $value_1 = $value_1 / 1000;

            $this->prepared_pairs['rain_in_period'] = array(
                'feature_code' => 'rain_in_period',
              //  'period' => $info_1,
                'period' => 1,
                'value' => $value_1,
                'metric_id' => $needed_feature_1['metric_id'],
                'normilized_value' => It::convertMetric($value_1, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
                'is_m' => $is_m
            );

            $value_2 = substr($this->incoming_sensor_value, 4, 4);
            $is_m = $value_2 == 'MMMM' ? 1 : 0;

            $value_2 = $value_2 / 1000;

            $this->prepared_pairs['rain_in_day'] = array(
                'feature_code' => 'rain_in_day',
                'period' => 1440,
                'value' => $value_2,
                'metric_id' => $needed_feature_2['metric_id'],
                'normilized_value' => It::convertMetric($value_2, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
                'is_m' => $is_m
            );

            return true;
        }
    }
    
    public function getRandomValue($features)
	{

        if ($this->awsFormat->isOldAWSFormat()) {

            $period = rand(1,180);
            $ac_period = rand(0, 1000);
            $ac_total = round(1440*$ac_period/$period,0);

            return str_pad($period, 3, "0", STR_PAD_LEFT) . str_pad($ac_period, 6, "0", STR_PAD_LEFT) . str_pad($ac_total, 6, "0");

        } else {
            $period = rand(1,180);
            $ac_period = rand(0, 1000);
            $ac_total = round(1440*$ac_period/$period,0);

            return  str_pad($ac_period, 4, "0", STR_PAD_LEFT) . str_pad($ac_total, 4, "0");

        }

    }    
    
    public function prepareXMLValue($xml_data, $db_features)
	{
        if ($this->awsFormat->isOldAWSFormat()) {
            $data_1 = str_pad($xml_data['period'], 3, "0", STR_PAD_LEFT);

            if (isset($xml_data['rain_in_period']) && ($xml_data['rain_in_period'] != 'M')) {
                $tmp = It::convertMetric($xml_data['rain_in_period'], 'millimeter', $db_features['rain_in_period']);
                $data_2 = str_pad(round($tmp * 1000), 6, "0", STR_PAD_LEFT);
            } else {
                $data_2 = str_repeat('M', 6);
            }

            if (isset($xml_data['rain_in_day']) && ($xml_data['rain_in_day'] != 'M')) {
                $tmp = It::convertMetric($xml_data['rain_in_day'], 'inch', $db_features['rain_in_day']);
                $data_3 = str_pad(round($tmp * 1000), 6, "0");
            } else {
                $data_3 = str_repeat('M', 6);
            }

            return $data_1 . $data_2 . $data_3;
        } else {

            $data_1 = str_pad($xml_data['period'], 3, "0", STR_PAD_LEFT);

            if (isset($xml_data['rain_in_period']) && ($xml_data['rain_in_period'] != 'M')) {
                $tmp = It::convertMetric($xml_data['rain_in_period'], 'millimeter', $db_features['rain_in_period']);
                $data_2 = str_pad(round($tmp * 1000), 6, "0", STR_PAD_LEFT);
            } else {
                $data_2 = str_repeat('M', 6);
            }

            if (isset($xml_data['rain_in_day']) && ($xml_data['rain_in_day'] != 'M')) {
                $tmp = It::convertMetric($xml_data['rain_in_day'], 'inch', $db_features['rain_in_day']);
                $data_3 = str_pad(round($tmp * 1000), 6, "0");
            } else {
                $data_3 = str_repeat('M', 6);
            }

            return  $data_2 . $data_3;
        }
    }

    public function __construct($logger)
    {
        $this->awsFormat = new AWSFormatConfigForm;

        parent::__construct($logger);
    }

}

?>