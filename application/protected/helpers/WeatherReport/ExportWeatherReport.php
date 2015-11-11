<?php

/**
 * This class contains owm implementation of generate() and prepareReportComplete() functions of WeatherReport class.
 *
 * @author
 */
class ExportWeatherReport extends WeatherReport
{
    public function load($schedule_processed_id)
	{
		parent::load($schedule_processed_id);
        
        if (isset($this->schedule_process_info->schedule_processed_id))
		{
            $file_path = dirname(Yii::app()->request->scriptFile) .
							DIRECTORY_SEPARATOR ."files".
							DIRECTORY_SEPARATOR ."schedule_reports".
							DIRECTORY_SEPARATOR . $this->schedule_process_info->schedule_processed_id;
          
			if (file_exists($file_path))
			{
                $this->report_complete = file_get_contents($file_path);
            }
        }
        
        if ($this->schedule_process_info->serialized_report_errors)
		{
            $this->errors = unserialize($this->schedule_process_info->serialized_report_errors);
        }
    }    
    
	/*
	 * this function gets all sensors data and calculated values from database 
	 * for each message received in reporting period.  
	 * It prepares array where each element is record-data array 
	 * (“field_name_1”, “field_value_1”, “field_name_2” , “field_value_2”...) based 
	 * on each record selected from database, calculated values and station 
	 * information.
	 * Prepared array is ready to be written into CSV file.
	 */
    public function generate()
	{
		$this->_logger->log(__METHOD__);
        
		if ($this->errors)
		{
            $this->_logger->log(__METHOD__, array('errors' => $this->errors));
			
            return false;
        }
        
        $current_user_timezone = date_default_timezone_get();
        $timezone_id = 'UTC';
		
        if ($timezone_id !=  $current_user_timezone)
		{
            TimezoneWork::set($timezone_id);
        }         
        
        $this->report_parts = array();
        $this->explanations = array();

		// get sensors' values for all messages received in reporting period
        $sql = "SELECT `t5`.`listener_log_id`,
                       `t5`.`measuring_timestamp`,
                       `t1`.`station_sensor_id`, `t1`.`sensor_id_code`, 
                       
                       `t3`.`feature_code`, `t3`.`feature_constant_value`,
                       `t4`.`code` AS `metric_code`, 
                       `t5`.`sensor_feature_value`, 
                       `t5`.`is_m`,
                       `t5`.`period` AS `sensor_feature_period`,
                       `t6`.`code` AS `value_metric_code`,
                       `t7`.`handler_id_code`

                FROM `".SensorData::model()->tableName()."`  `t5`
                LEFT JOIN `".StationSensor::model()->tableName()."`        `t1` ON t1.station_sensor_id = t5.sensor_id
                LEFT JOIN `".StationSensorFeature::model()->tableName()."` `t3` ON (`t3`.`sensor_feature_id` = `t5`.`sensor_feature_id`)
                LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t4` ON `t4`.`metric_id` = `t3`.`metric_id`
                LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t6` ON `t6`.`metric_id` = `t5`.`metric_id`
                LEFT JOIN `".SensorDBHandler::model()->tableName()."`      `t7` ON t7.handler_id = t1.handler_id
                WHERE `t5`.`station_id` = '". $this->station_info->station_id ."' AND `t5`.`listener_log_id` IN (". $this->schedule_process_info->listener_log_ids .")
                ORDER BY `t5`.`measuring_timestamp` DESC, `t1`.`sensor_id_code` ASC, `t3`.`feature_code` ASC";
		
        $sensor_data = Yii::app()->db->createCommand($sql)->queryAll();

		$data = array();
		
        if ($sensor_data) 
		{
        	// get calculation values for all messages received in reporting period
            $sql = "SELECT `t1`.`listener_log_id`,
                           `t1`.`value`,
                           `t3`.`handler_id_code`
                    FROM `".StationCalculationData::model()->tableName()."`    `t1`
                    LEFT JOIN `".StationCalculation::model()->tableName()."`   `t2` ON t2.calculation_id = t1.calculation_id
                    LEFT JOIN `".CalculationDBHandler::model()->tableName()."` `t3` ON `t3`.`handler_id` = `t2`.`handler_id`
                    WHERE `t2`.`station_id` = '". $this->station_info->station_id ."' AND `t1`.`listener_log_id` IN (". $this->schedule_process_info->listener_log_ids .")
                    ORDER BY `t3`.`handler_id_code`";            
            
			$res2 = Yii::app()->db->createCommand($sql)->queryAll();

            if ($res2)
			{
                foreach ($res2 as $key => $value)
				{
                    $calculations[$value['listener_log_id']][] = $value;
                }
            }

            foreach ($sensor_data as $key => $value)
			{
                $data[$value['listener_log_id']][] = $value;
            }
			
			// prepare $result_item array, where each line represents line in report.
            foreach ($data as $key => $value)
			{
                $result_item = array(
                    'StationId',
                    $this->station_info->station_id_code,
                    'WMO AWS #',
                    $this->station_info->wmo_block_number . $this->station_info->station_number,
                    'National AWS #',
                    $this->station_info->national_aws_number,
                    'Tx DateTime',
                    date('m/d/Y H:i', strtotime($value[0]['measuring_timestamp']))
                );
                
                foreach ($value as $key2 => $value2)
				{
                    $handler_obj = SensorHandler::create($value2['handler_id_code']);
					
                    if (in_array($value2['handler_id_code'], array('BatteryVoltage', 'Humidity', 'Pressure', 'Temperature')))
					{
						$sensor_id_code = $value2['sensor_id_code'];
                    } 
					else
					{
                        $sensor_id_code = $value2['sensor_id_code'].' ('.$handler_obj->getFeatureName($value2['feature_code']).')';
                    }
                    
                    $result_item[] = $sensor_id_code;
                    
					if ($value2['is_m'])
					{
						$result_item[] = '-';
                    } 
					else
					{
                        $value2['sensor_feature_value'] = $handler_obj->applyOffset($value2['sensor_feature_value'], $this->station_info->magnetic_north_offset);
                        $result_item[] = str_replace(',', ' ', $handler_obj->formatValue($value2['sensor_feature_value'], $value2['feature_code']));
                    }
                }
                
                if (isset($calculations[$key]))
				{
                    foreach ($calculations[$key] as $key2 => $value2)
					{
                        if ($value2['handler_id_code'] === 'DewPoint')
						{
							$result_item[] = 'DP';
                        } 
						else if ($value2['handler_id_code'] === 'PressureSeaLevel')
						{
							$result_item[] = 'MSL';
                        } 
						else
						{
                            $result_item[] = 'Unknown calculation';
                        }
						
                        $result_item[] = str_replace(',', ' ', number_format(round($value2['value'], 1),1));
                    }
                }
				
                $this->report_parts[] = $result_item;
            }
        }
        
        if ($timezone_id != $current_user_timezone)
		{
			TimezoneWork::set($current_user_timezone);
        } 
        
        $this->_logger->log(__METHOD__ . ' Export generation completed.');
		
        return true;
    }
    
    public function prepareReportComplete()
	{
        $this->report_complete = '';
        
		if ($this->report_parts)
		{
            foreach ($this->report_parts as $data)
			{
				$this->report_complete .= implode(',', $data);
				$this->report_complete .= "\n";
            }                
        }
    }    
}

?>