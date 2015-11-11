<?php

/*
 * Handler to work with data of SeaLevel sensor
 * 
 */

class SeaLevelAWSSensorHandler extends SensorHandler {

    
    public $features = array(
		
        array(
            'feature_name'          => 'Mean', // name of measurement
            'feature_code'          => 'sea_level_mean', // feature code to be stored at table `station_sensor_feature`
            'measurement_type_code' => 'sea_level', // measurement code to get possible metrics for.
            'has_filter_min'        => 1, // should WM check this measurement's value for filters?
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0, // is it cumulative or not? (it is true for sun, rain)
            'is_main'               => 1, // display info about this measurement in places where we need  to display info about  only one measurement for sensor
            'aws_graph_using'       => 'Sea Level Mean',  // if takes part in “AWS Graph” page
            'aws_panel_show'        => 1,
        ),		
        array(
            'feature_name'          => 'Sigma',
            'feature_code'          => 'sea_level_sigma',
            'measurement_type_code' => 'sea_level',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => ''
        ),
        array(
            'feature_name'          => 'Wave Height',
            'feature_code'          => 'sea_level_wave_height',
            'measurement_type_code' => 'sea_level_wave_height',
            'has_filter_min'        => 1,
            'has_filter_max'        => 1,
            'has_filter_diff'       => 1,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'aws_graph_using'       => ''
        ),
        
    );

    public $extra_features = array(
        
        array(
            'feature_name'          => 'Baseline',
            'feature_code'          => 'sl_baseline',
            'measurement_type_code' => '',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'comment'               => 'Is using to set the "true" value of the sea/tide level. The same metric as Mean.',
            'default'               => 0,
        ),        
        array(
            'feature_name'          => 'Trend Treshold',
            'feature_code'          => 'sl_trend_treshold',
            'measurement_type_code' => '',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'comment'               => 'Is using to compare T0 and T1 and get trend calculation. The same metric as Mean.',
            'default'               => 0,
        ),
        array(
            'feature_name'          => 'Trend Treshold Period',
            'feature_code'          => 'sl_trend_avg_calculate_period',
            'measurement_type_code' => 'treshold_period',
            'has_filter_min'        => 0,
            'has_filter_max'        => 0,
            'has_filter_diff'       => 0,
            'is_cumulative'         => 0,
            'is_main'               => 0,
            'comment'               => 'Is period for choosing AVG data in (minutes)',
            'default'               => 30,
        )        
    );     
    
    public function getSensorDescription()
	{
        return "Handler \"Sea Level and Tide Data\" :
                Processes string like \"SL1XXXXYYYYZZZZ\", where <br/>SL1 - device Id; <br/>XXXX - Mean value;<br/>YYYY - Sigma value; <br/>ZZZZ - Wave Height
                <br/>
                Example: SL1179017900140 = SL1 sensor sent data: Mean value = 1.79, Sigma value = 1.79, Wave height = 140m.";
    }
    
    public function getInfoForAwsPanel($sensor_pairs, $sensorList, $sensorData, $for = 'panel')
	{
        $return = array();

		$sensor_ids = array();
        $sensor_logs = array();
        $last_logs_ids = array();
        $last_logs_per_station = array();
        
        foreach ($sensor_pairs as $value)
		{
            $sensor_ids[] = $value['sensor_id'];
			
            if (count($value['last_logs']) > 0)
			{
                $last_logs_ids[] = $value['last_logs'][0]->log_id;
                $sensor_logs[$value['sensor_id']] = $value['last_logs'][0]->log_id;
                $last_logs_per_station[$value['station_id']][0] = $value['last_logs'][0]->log_id;
            }
            
			if (count($value['last_logs']) > 1)
			{
                $last_logs_ids[] = $value['last_logs'][1]->log_id; 
                $last_logs_per_station[$value['station_id']][1] = $value['last_logs'][1]->log_id;
            }
        }
        
        $sensor_feature_ids = array();
		
		$features = array(
			'sea_level_mean',
			'sea_level_sigma',
			'sea_level_wave_height'
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

					if (!isset($return[$sensorFeature->sensor->station_id][$sensor_id]))
					{
						$return[$sensorFeature->sensor->station_id][$sensor_id] = array(
							'sensor_display_name'    => $sensorFeature->sensor->display_name,
							'sensor_id_code'         => $sensorFeature->sensor->sensor_id_code,
							'group'                  => 'sea_level_data',
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

							if (isset($sensorValues[$feature_code]['has_filter_min']) && ($sensorValues[$feature_code]['has_filter_max'] == 1))
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
						unset($return[$station_id][$sensor_id][$feature_code][$unsetfield]);
					}                         
				}

				if ($for === 'single') 
				{
					$res = $this->calculateTrend($sensor_logs[$sensor_id], $sensor_id); 
					
					if ($res)
					{
						$sensorValues['last_high'] = isset($res['last_high']) ? $res['last_high'] : null;
						$sensorValues['last_low'] = isset($res['last_low']) ? $res['last_low'] : null;
					}
				}                    
			}
		}
        
        return $return;
    }

    public function calculateTrend($last_log_id, $sensor_id)
	{
        $return = array();
        $sql = "SELECT `trend_id`, `measuring_timestamp`, `trend` , `last_high`, `last_high_timestamp`, `last_low`, `last_low_timestamp`
                FROM `".SeaLevelTrend::model()->tableName()."`
                WHERE `log_id` = '".$last_log_id."' AND `sensor_id` = '".$sensor_id."'
                ORDER BY `measuring_timestamp` DESC
                LIMIT 1";
        $current_trend = Yii::app()->db->createCommand($sql)->queryRow();
       
        if ($current_trend)
		{
            if ($current_trend['last_high_timestamp'] != '0000-00-00 00:00:00')
			{
                $return['last_high']['value'] = $current_trend['last_high'];
                $return['last_high']['measuring_timestamp'] = $current_trend['last_high_timestamp'];
            }
			
            if ($current_trend['last_low_timestamp'] != '0000-00-00 00:00:00')
			{
                $return['last_low']['value'] = $current_trend['last_low'];
                $return['last_low']['measuring_timestamp'] = $current_trend['last_low_timestamp'];
            }
        } 
        
        return $return;
    }
    
    public function getMaxMinDayFromDayStart($sensor_id, $measuring_timestamp, $timezone_id)
	{
        $today_start = mktime(0,0,0, date('m', $measuring_timestamp), date('d', $measuring_timestamp), date('Y', $measuring_timestamp));
        
        $sql_groupped_table2 = "SELECT `t1`.`sensor_feature_id`, MAX(CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4))) AS `MaxVal`, MIN(CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4))) AS `MinVal` 
                               FROM `".SensorData::model()->tableName()."` `t1`
                               LEFT JOIN ".StationSensorFeature::model()->tableName()." `t2` ON `t2`.`sensor_feature_id` = `t1`.`sensor_feature_id`
                               WHERE `t1`.`sensor_id` = '".$sensor_id."'
                                   AND `t1`.`measuring_timestamp` <= '".date('Y-m-d H:i:s', $measuring_timestamp)."' 
                                   AND `t1`.`measuring_timestamp` >= '".date('Y-m-d H:i:s', $today_start)."'
                                   AND `t2`.`feature_code` = 'sea_level_mean'";

        $sql = "SELECT CAST(`tt`.`sensor_feature_value` AS DECIMAL(15,4)) as `sensor_feature_value`, `tt`.`measuring_timestamp`
                FROM `".SensorData::model()->tableName()."` `tt`

                INNER JOIN ( {$sql_groupped_table2} ) `groupedtt` ON `tt`.`sensor_feature_id` = `groupedtt`.`sensor_feature_id` AND (`tt`.`sensor_feature_value` = `groupedtt`.`MaxVal` OR `tt`.`sensor_feature_value` = `groupedtt`.`MinVal`)
                GROUP BY CAST(`tt`.`sensor_feature_value` AS DECIMAL(15,4))
                ORDER BY CAST(`tt`.`sensor_feature_value` AS DECIMAL(15,4))
                ";
                
               
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        
		$return = array(
			'sea_level_mean' => array(
				'max24' => $this->formatValue($res[1]['sensor_feature_value'], 'sea_level_mean'),
				'max24_time' => $res[1]['measuring_timestamp'],
				'min24' => $this->formatValue($res[0]['sensor_feature_value'], 'sea_level_mean'),
				'min24_time' => $res[0]['measuring_timestamp'],
			),
		);

        return $return;        
    }


    public function formatValue($value, $feature_name='') {

        if ($feature_name == 'sea_level_mean') {
            return number_format(round($value,3),3);
        } else if ($feature_name == 'sea_level_sigma') {
            return round($value, 3); 
        } else if ($feature_name == 'sea_level_wave_height') {
            return round($value, 3);
        }
        return round($value,1);
    }       


    public function _prepareDataPairs()
	{
        $length = strlen($this->incoming_sensor_value);
        
        if ($length <> 12)
            return false;
        
        $needed_feature_1 = array();
        $needed_feature_2 = array();
        $needed_feature_3 = array();
        $baseline = 0;
        foreach($this->sensor_features_info as $feature) {
            if ($feature['feature_code'] == 'sea_level_mean') {
                $needed_feature_1 = $feature;
            } elseif ($feature['feature_code'] == 'sea_level_sigma') {
                $needed_feature_2 = $feature;
            } elseif ($feature['feature_code'] == 'sea_level_wave_height') {
                $needed_feature_3 = $feature;
            } elseif ($feature['feature_code'] == 'sl_baseline') {
                $baseline = $feature['feature_constant_value'];
            }
        }
        
        $value = substr($this->incoming_sensor_value, 0, 4);
        $is_m = $value == 'MMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'sea_level_mean', 
            'period'           => 1, 
            'value'            => $baseline - $value/1000,
            'metric_id'        => $needed_feature_1['metric_id'],
            'normilized_value' => It::convertMetric($baseline - $value/1000, $needed_feature_1['metric_code'], $needed_feature_1['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 4, 4);
        $is_m = $value == 'MMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'sea_level_sigma', 
            'period'           => 1, 
            'value'            => $value/1000,
            'metric_id'        => $needed_feature_2['metric_id'],
            'normilized_value' => It::convertMetric($value/1000, $needed_feature_2['metric_code'], $needed_feature_2['general_metric_code']),
            'is_m'             => $is_m
        );
        
        $value = substr($this->incoming_sensor_value, 8, 4);
        $is_m = $value == 'MMMM' ? 1 : 0;
        $this->prepared_pairs[] = array(
            'feature_code'     => 'sea_level_wave_height', 
            'period'           => 1, 
            'value'            => $value/10,
            'metric_id'        => $needed_feature_3['metric_id'],
            'normilized_value' => It::convertMetric($value/10, $needed_feature_3['metric_code'], $needed_feature_3['general_metric_code']),
            'is_m'             => $is_m
        );
         
        return true;
    }        
    
    public function getRandomValue($features)
	{
        $tmp1  = str_pad(rand(1, 5000), 4, "0", STR_PAD_LEFT);
        $tmp2  = str_pad(rand(1, 5000), 4, "0", STR_PAD_LEFT);
        $tmp3  = str_pad(rand(1, 5000), 4, "0", STR_PAD_LEFT);;
  
        return $tmp1.$tmp2.$tmp3;
    }    
   
    public function afterDataPairsSaved($save_data_params)
	{
        $res = $this->_findFeatureConstantValue('sl_trend_avg_calculate_period', $save_data_params['sensor_features']);
        $feature_treshold_period = $res > 0 ? round($res) : 30;

        $res = $this->_findFeatureConstantValue('sl_trend_treshold', $save_data_params['sensor_features']);
        $feature_treshold = $res != 0 ? round($res, 3) : 0;
        
        $sensor_feature_id = $this->_findFeatureId('sea_level_mean', $save_data_params['sensor_features']);
        
		$params = array(
            'incoming_measuring_timestamp' => $this->incoming_measuring_timestamp,
            'log_id'                       => $save_data_params['listener_log_id'],
            'sensor_id'                    => $save_data_params['sensor']->station_sensor_id,
            'feature_treshold_period'      => $feature_treshold_period,
            'feature_treshold'             => $feature_treshold,
            'sensor_feature_id'            => $sensor_feature_id
        );
        
		$this->repairTrend($params);
		
        return true;
    }
    
    public function repairTrend($params)
	{
        $measuring_timestamp1 = date('Y-m-d H:i:s', $params['incoming_measuring_timestamp']);
        $measuring_timestamp2 = date('Y-m-d H:i:s', $params['incoming_measuring_timestamp'] - $params['feature_treshold_period']*60);
        $measuring_timestamp3 = date('Y-m-d H:i:s', $params['incoming_measuring_timestamp'] - $params['feature_treshold_period']*2*60);
        $measuring_timestamp4 = date('Y-m-d H:i:s', $params['incoming_measuring_timestamp'] - 18000);
        
        $criteria = new CDbCriteria();
		$criteria->compare('log_id', $params['log_id']);
        $criteria->compare('sensor_id', $params['sensor_id']);

		$sea_trend = SeaLevelTrend::model()->find($criteria);

        if (!$sea_trend)
		{
            $sea_trend = new SeaLevelTrend();
            $sea_trend->log_id    = $params['log_id'];
            $sea_trend->sensor_id = $params['sensor_id'];
        }
		
        $sea_trend->measuring_timestamp = $measuring_timestamp1;        
        
        $minutes = array('00');
        for ($i = 1; $i <= 60; $i++)
		{
            if ($i % $params['feature_treshold_period'] == 0)
			{
                $minutes[] = ($i == 60) ? '00' : str_pad($i, 2, '0', STR_PAD_LEFT);
                $minutes[] = ($i == 60) ? '01' : str_pad($i+1, 2, '0', STR_PAD_LEFT);
            }
        }
        
		$criteria = new CDbCriteria();
		$criteria->compare('sensor_id', $params['sensor_id']);
		$criteria->compare('measuring_timestamp', '<'. $measuring_timestamp1);
		
		$criteria->order = 'measuring_timestamp desc';
		$criteria->limit = '1';
		
		$last_trend = SeaLevelTrend::model()->find($criteria);
        
		if (!in_array(date('i', $params['incoming_measuring_timestamp']), $minutes))
		{
            if (is_null($last_trend))
			{
                $sea_trend->trend = 'unknown';
                $sea_trend->is_significant      = 0;
                $sea_trend->last_high           = 0;
                $sea_trend->last_low            = 0;
                $sea_trend->last_high_timestamp = '0000-00-00 00:00:00';
                $sea_trend->last_low_timestamp  = '0000-00-00 00:00:00';            
                
            } 
			else
			{
                $sea_trend->trend               = $last_trend->trend;
                $sea_trend->is_significant      = $last_trend->is_significant;
                $sea_trend->last_high           = $last_trend->last_high;
                $sea_trend->last_low            = $last_trend->last_low;
                $sea_trend->last_high_timestamp = $last_trend->last_high_timestamp;
                $sea_trend->last_low_timestamp  = $last_trend->last_low_timestamp;
            }   
			
            $sea_trend->save();
            
			return true;
        }

        
        $sql = "SELECT SUM(CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4))) AS `sum`, COUNT(`t1`.`sensor_feature_id`) AS `cnt`
                FROM `".SensorData::model()->tableName()."` `t1`
                WHERE `t1`.`sensor_feature_id` = ?
                  AND `t1`.`measuring_timestamp` > ? 
                  AND `t1`.`measuring_timestamp` <= ?
                  AND `t1`.`is_m` = '0'
                GROUP BY `t1`.`sensor_feature_id`";
        
        $p = array($params['sensor_feature_id'], $measuring_timestamp2, $measuring_timestamp1);
        $res_last = Yii::app()->db->createCommand($sql)->queryRow(true, $p);

        
        $p = array($params['sensor_feature_id'], $measuring_timestamp3, $measuring_timestamp2);
        $res_prev = Yii::app()->db->createCommand($sql)->queryRow(true, $p);
        
        if (!$res_prev)
		{
            if (is_null($last_trend))
			{
                $sea_trend->is_significant      = 0;
                $sea_trend->last_high           = 0;
                $sea_trend->last_low            = 0;
                $sea_trend->last_high_timestamp = '0000-00-00 00:00:00';
                $sea_trend->last_low_timestamp  = '0000-00-00 00:00:00';
            }
			else
			{
				$sea_trend->trend               = $last_trend->trend;
                $sea_trend->is_significant      = $last_trend->is_significant;
                $sea_trend->last_high			= $last_trend->last_high;
                $sea_trend->last_low            = $last_trend->last_low;
                $sea_trend->last_high_timestamp = $last_trend->last_high_timestamp;
                $sea_trend->last_low_timestamp  = $last_trend->last_low_timestamp;      
            }
        }
		else
		{
            $t0 = $res_last['sum'] / $res_last['cnt'];
            $t1 = $res_prev['sum'] / $res_prev['cnt'];
            $difference = $t0 - $t1;
            
            if ($difference > 0)
			{
				$sea_trend->trend = 'up';
            } 
			else if ($difference < 0)
			{
				$sea_trend->trend = 'down';
            }
			else
			{
				$sea_trend->trend = $last_trend->trend;
            }
            
			$sea_trend->is_significant = (abs($difference) >= $params['feature_treshold'] ? 1 : 0);
			
            $sea_trend->last_high           = $last_trend->last_high;
            $sea_trend->last_low            = $last_trend->last_low;
            $sea_trend->last_high_timestamp = $last_trend->last_high_timestamp;
            $sea_trend->last_low_timestamp  = $last_trend->last_low_timestamp;  
            
            if ($sea_trend->is_significant && ($last_trend->trend != $sea_trend->trend || !$last_trend->is_significant))
			{
                if ($sea_trend->trend === 'down')
				{
                    $sql_groupped_table = "SELECT `sensor_feature_id`, MAX(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `MaxValue` 
                                           FROM `".SensorData::model()->tableName()."` 
                                           WHERE `sensor_feature_id` = ?
                                             AND `measuring_timestamp` > ? 
                                             AND `measuring_timestamp` <= ?
                                             AND `is_m` = '0'";
					
                    $sql = "SELECT CAST(`tt`.`sensor_feature_value` AS DECIMAL(15,4)) as `sensor_feature_value`, `tt`.measuring_timestamp
                            FROM `".SensorData::model()->tableName()."` `tt`
                            INNER JOIN ( {$sql_groupped_table} ) `gr` ON `tt`.`sensor_feature_id` = `gr`.`sensor_feature_id` AND `tt`.`sensor_feature_value` = `gr`.`MaxValue`
                            WHERE `tt`.`measuring_timestamp` > ? AND `tt`.`measuring_timestamp` <= ?";

                    $p = array($params['sensor_feature_id'], $measuring_timestamp4, $measuring_timestamp1, $measuring_timestamp4, $measuring_timestamp1);
                    $extr = Yii::app()->db->createCommand($sql)->queryRow(true, $p);
					
                    if ($extr)
					{
						$sea_trend->last_high = $extr['sensor_feature_value'];
						$sea_trend->last_high_timestamp = $extr['measuring_timestamp'];
                    }
                }
				else
				{
                    $sql_groupped_table = "SELECT `sensor_feature_id`, MIN(CAST(`sensor_feature_value` AS DECIMAL(15,4))) AS `MinValue` 
                                           FROM `".SensorData::model()->tableName()."` 
                                           WHERE `sensor_feature_id` = ?
                                             AND `measuring_timestamp` > ? 
                                             AND `measuring_timestamp` <= ?
                                             AND `is_m` = '0'";
					
                    $sql = "SELECT CAST(`tt`.`sensor_feature_value` AS DECIMAL(15,4)) as `sensor_feature_value`, `tt`.measuring_timestamp
                            FROM `".SensorData::model()->tableName()."` `tt`
                            INNER JOIN ( {$sql_groupped_table} ) `gr` ON `tt`.`sensor_feature_id` = `gr`.`sensor_feature_id` AND `tt`.`sensor_feature_value` = `gr`.`MinValue`
                            WHERE `tt`.`measuring_timestamp` > ? AND `tt`.`measuring_timestamp` <= ?";

                    $p = array($params['sensor_feature_id'], $measuring_timestamp4, $measuring_timestamp1, $measuring_timestamp4, $measuring_timestamp1);
                    $extr = Yii::app()->db->createCommand($sql)->queryRow(true, $p);
                    
					if ($extr) 
					{
                        $sea_trend->last_low = $extr['sensor_feature_value'];
                        $sea_trend->last_low_timestamp = $extr['measuring_timestamp'];
                    }                
                }
            }
        }
       
		$sea_trend->save();
		
        return true;        
    }
}

?>