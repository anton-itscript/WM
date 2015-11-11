<?php

class AWSTableForm extends CFormModel
{
    use __AWSFormTrait;

    private $session_name = 'aws_table';
    private $custom_sensor_features = array();

    public function checkSensorFeatureCode()
    {
        if ($this->hasErrors()) {
            return false;
        }

        if (!count($this->getSelectedGroupSensorFeatureCode())) {
            $this->addError('sensor_feature_code',$this->getAttributeLabel('sensor_feature_code') . ' cannot be blank.');
            return false;
        }

        return true;
    }

    public function prepareCalculationList($handler_id)
    {

    }
    public function prepareList($station_id = 0)
	{
        if ($this->hasErrors()) {
            return ['prepared_header' => [], 'prepared_data' => []];
        }

        $prepared_header = array();
        $prepared_data = array();

        $sensor_feature_code = $this->getSensorFeatureCode();
        $handler_code = array_keys($this->getSelectedGroupSensorFeatureCode());

        $search_features = array();
        $search_calcs    = array();

        foreach ($sensor_feature_code as $key) {
            if (in_array($key, array_keys($this->calc_handlers))) {
                $search_calcs[] = $this->calc_handlers[$key];
            } else {
                $search_features[] = $key;
            }
        }

        if ($station_id <= 0) {
            $sql_part = "`t2`.`station_id` IN (".implode(',',$this->station_id).") ";
        } else {
            $sql_part = "`t2`.`station_id` = '".$station_id."' ";
        }

        // 1.a) GET FEATURES
        if (count($search_features) > 0) {
            $sql = "SELECT `t1`.`sensor_feature_id`,
                           `t1`.`feature_code`,
                           `t1`.`sensor_id`,
                           `t3`.`station_id_code`,
                           `t2`.`sensor_id_code`,
                           `t2`.`station_id`,
                           `t4`.`handler_id_code`,
                           `t3`.`magnetic_north_offset`
                    FROM `".StationSensorFeature::model()->tableName()."` `t1`
                    LEFT JOIN `".StationSensor::model()->tableName()."`   `t2` ON `t1`.`sensor_id` = `t2`.`station_sensor_id`
                    LEFT JOIN `".SensorDBHandler::model()->tableName()."` `t4` ON `t4`.`handler_id` = `t2`.`handler_id`
                    LEFT JOIN `".Station::model()->tableName()."`         `t3` ON `t3`.`station_id` = `t2`.`station_id`
                    WHERE ".$sql_part." AND `t1`.`feature_code` IN ('".implode("','",$search_features)."') AND `t4`.`handler_id_code` IN ('".implode("','",$handler_code)."')
                    ORDER BY `t1`.`feature_code`, `t3`.`station_id_code`, `t2`.`sensor_id_code`";

            $found_sensors = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();

            $total_found_sensors = count($found_sensors);
        }

        // 1.b) GET CALCS
        if (count($search_calcs) > 0) {
            $sql = "SELECT `t1`.`calculation_id`,
                           `t1`.`handler_id`,
                           `t2`.`station_id_code`,
                           `t2`.`station_id`,
                           IF(`t1`.`handler_id` = 1, 'DP', 'MSL') AS `sensor_id_code`,
                           IF(`t1`.`handler_id` = 1, 'Dew Point', 'Pressure MSL') AS `feature_code`,
                           IF(`t1`.`handler_id` = 1, 'DewPoint', 'PressureSeaLevel') AS `handler_id_code`
                    FROM `".StationCalculation::model()->tableName()."` `t1`
                    LEFT JOIN `".Station::model()->tableName()."`       `t2` ON `t2`.`station_id` = `t1`.`station_id`
                    WHERE ".$sql_part." AND `t1`.`handler_id` IN (".implode(',', $search_calcs).")
                    ORDER BY `t1`.`handler_id`, `t2`.`station_id_code`";

            $found_calcs = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();

            $total_found_calcs = count($found_calcs);
        }

        $start_datetime = strtotime($this->date_from.' '.$this->time_from);
        $end_datetime   = strtotime($this->date_to.' '.$this->time_to);

        $features_set = array();

        // 2.a) PREPARE HEADER
        if (is_array($found_sensors) && ($total_found_sensors > 0)) {
            $sensor_feature_ids = array();

            for ($i = 0; $i < $total_found_sensors; $i++) {
                $key = $found_sensors[$i]['handler_id_code'] . $found_sensors[$i]['feature_code'];

                if (!isset($prepared_header[$key])) {
                    $prepared_header[$key] = array(
                        'sensor_feature_code' => $found_sensors[$i]['feature_code'],
                        'handler_id_code'     => $found_sensors[$i]['handler_id_code'],
                        'sensors'             => array(),
                        'station_sensors'     => array()
                    );
                }

                $sensor_feature_ids[] = $found_sensors[$i]['sensor_feature_id'];

                $prepared_header[$key]['sensors'][] = array(
                        'station_id' => $found_sensors[$i]['station_id'],
                        'sensor_id_code' => $found_sensors[$i]['sensor_id_code'],
                    );

                if (isset($prepared_header[$key]['station_sensors'][$found_sensors[$i]['station_id']])) {
                    $prepared_header[$key]['station_sensors'][$found_sensors[$i]['station_id']]++;
                } else {
                    $prepared_header[$key]['station_sensors'][$found_sensors[$i]['station_id']] = 1;
                }

                $features_set[$found_sensors[$i]['sensor_feature_id']] = array(
                    'station_id'            => $found_sensors[$i]['station_id'],
                    'station_id_code'       => $found_sensors[$i]['station_id_code'],
                    'value'                 => '-',
                    'sensor_id'             => $found_sensors[$i]['sensor_id'],
                    'sensor_id_code'        => $found_sensors[$i]['sensor_id_code'],
                    'sensor_feature_code'   => $found_sensors[$i]['feature_code'],
                    'handler_id_code'       => $found_sensors[$i]['handler_id_code'],
                    'magnetic_north_offset' => $found_sensors[$i]['magnetic_north_offset']
                );
            }
        }

        // 2.b) PREPARE HEADER
        if (count($search_calcs) > 0) {
            $sql = "SELECT `t1`.`calculation_id`,
                           `t1`.`handler_id`,
                           `t2`.`station_id_code`,
                           `t2`.`station_id`,
                           IF(`t1`.`handler_id` = 1, 'DP', 'MSL') AS `sensor_id_code`,
                           IF(`t1`.`handler_id` = 1, 'Dew Point', 'Pressure MSL') AS `feature_code`,
                           IF(`t1`.`handler_id` = 1, 'DewPoint', 'PressureSeaLevel') AS `handler_id_code`
                    FROM `".StationCalculation::model()->tableName()."` `t1`
                    LEFT JOIN `".Station::model()->tableName()."`       `t2` ON `t2`.`station_id` = `t1`.`station_id`
                    WHERE ".$sql_part." AND `t1`.`handler_id` IN (".implode(',', $search_calcs).")
                    ORDER BY `t1`.`handler_id`, `t2`.`station_id`";

            $found_calcs = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();

            if (is_array($found_calcs) && (count($found_calcs) > 0)) {
                $calculation_ids = array();

                for ($i = 0; $i < count($found_calcs); $i++) {
                    $key = 'calc_'.$found_calcs[$i]['handler_id'];

                    if (!isset($prepared_header[$key])) {
                        $prepared_header[$key] = array(
                            'sensor_feature_code' => $key,
                            'sensors'             => array(),
                            'station_sensors'     => array()
                        );
                    }

                    $calculation_ids[] = $found_calcs[$i]['calculation_id'];
                    $prepared_header[$key]['sensors'][] = array(
                            'station_id' => $found_calcs[$i]['station_id'],
                            'sensor_id_code' => $found_calcs[$i]['sensor_id_code'],
                        );

                    if (isset($prepared_header[$key]['station_sensors'][$found_calcs[$i]['station_id']])) {
                        $prepared_header[$key]['station_sensors'][$found_calcs[$i]['station_id']]++;
                    } else {
                        $prepared_header[$key]['station_sensors'][$found_calcs[$i]['station_id']] = 1;
                    }

                    $features_set['calc_'.$found_calcs[$i]['calculation_id']] = array(
                        'station_id'          => $found_calcs[$i]['station_id'],
                        'station_id_code'     => $found_calcs[$i]['station_id_code'],
                        'value'               => '-',
                        'calculation_id'      => $found_calcs[$i]['calculation_id'],
                        'sensor_id_code'      => $found_calcs[$i]['sensor_id_code'],
                        'sensor_feature_code' => $found_calcs[$i]['feature_code'],
                        'handler_id_code'     => $found_calcs[$i]['handler_id_code']
                    );
                }
            }
        }

        // 3.a) PREPARE DATA
        if (is_array($found_sensors) && $total_found_sensors) {
            $qb = new CDbCriteria();
            $qb->select = ['sensor_data_id','station_id','sensor_id','sensor_feature_id','sensor_feature_normalized_value','is_m','measuring_timestamp'];
            $qb->addInCondition('sensor_feature_id',$sensor_feature_ids);
            $qb->addBetweenCondition('measuring_timestamp',date('Y-m-d H:i:s', $start_datetime), date('Y-m-d H:i:s', $end_datetime));
            $qb->order = 'measuring_timestamp DESC';
            $found_values = SensorData::model()->long()->findAll($qb);

            $total_found_values = count($found_values);

            if (is_array($found_values) && ($total_found_values > 0)) {
                if ($this->accumulation_period == 0) {
                    for ($j = 0; $j < $total_found_values; $j++) {
                        $f_id                  = $found_values[$j]['sensor_feature_id'];
                        $f_time                = $found_values[$j]['measuring_timestamp'];
                        $f_code                = $features_set[$f_id]['sensor_feature_code'];
                        $magnetic_north_offset = $features_set[$f_id]['magnetic_north_offset'];
                        $st_id                 = $found_values[$j]['station_id'];

                        if (!isset($prepared_data[$f_time])) {
                            $prepared_data[$f_time] = array();
                            $prepared_data[$f_time]['stations'] = array();
                        }

                        if (!isset($prepared_data[$f_time]['data'])) {
                            $prepared_data[$f_time]['data'] = $features_set;
                        }

                        $handler_obj = SensorHandler::create($features_set[$f_id]['handler_id_code']);

                        if ($found_values[$j]['is_m'] == 1) {
                            $prepared_data[$f_time]['data'][$f_id]['value'] = '-';
                        } else {
                            $found_values[$j]['sensor_feature_normalized_value'] = $handler_obj->applyOffset($found_values[$j]['sensor_feature_normalized_value'], $magnetic_north_offset);
                            $prepared_data[$f_time]['data'][$f_id]['value'] = $handler_obj->formatValue($found_values[$j]['sensor_feature_normalized_value'], $f_code);
                        }

                        if (!in_array($st_id, $prepared_data[$f_time]['stations'])) {
                            $prepared_data[$f_time]['stations'][] = $st_id;
                        }
                    }
                } else {
                    for ($j = 0; $j < $total_found_values; $j++) {
                        $f_id                  = $found_values[$j]['sensor_feature_id'];
                        $f_time                = $found_values[$j]['measuring_timestamp'];
                        $f_code                = $features_set[$f_id]['sensor_feature_code'];
                        $magnetic_north_offset = $features_set[$f_id]['magnetic_north_offset'];
                        $st_id                 = $found_values[$j]['station_id'];


                        $period = ($start_datetime + (intval((strtotime($f_time) - $start_datetime) / ($this->accumulation_period * 60)) + 1) * $this->accumulation_period * 60);
                        $period = $period > $end_datetime ? $end_datetime : $period;
                        $period = date('Y-m-d H:i:s',$period);

                        if (!isset($prepared_data[$period])) {
                            $prepared_data[$period] = array();
                            $prepared_data[$period]['stations'] = array();
                        }

                        if (!isset($prepared_data[$period]['data'])) {
                            $prepared_data[$period]['data'] = $features_set;
                        }

                        $handler_obj = SensorHandler::create($features_set[$f_id]['handler_id_code']);

                        if ($found_values[$j]['is_m'] == 1) {
                            $prepared_data[$period]['data'][$f_id]['value'] = '-';
                        } else {
                            $found_values[$j]['sensor_feature_normalized_value'] = $handler_obj->applyOffset($found_values[$j]['sensor_feature_normalized_value'], $magnetic_north_offset);
                            $prepared_data[$period]['data'][$f_id]['value'] =
                                ($prepared_data[$period]['data'][$f_id]['value'] ? $prepared_data[$period]['data'][$f_id]['value'] : 0)
                                + $handler_obj->formatValue($found_values[$j]['sensor_feature_normalized_value'], $f_code);
                        }

                        if (!in_array($st_id, $prepared_data[$period]['stations'])) {
                            $prepared_data[$period]['stations'][] = $st_id;
                        }
                    }
                }
            }

        }

        // 3.b) PREPARE DATA
        if (is_array($found_calcs) && ($total_found_calcs > 0)) {
            $sql = "SELECT `t2`.`station_id`,
                           `t1`.`calculation_id`,
                           `t1`.`value`,
                           `t2`.`measuring_timestamp`
                    FROM `".StationCalculationData::model()->tableName()."` `t1`
                    LEFT JOIN `".ListenerLog::model()->tableName()."` `t2` ON `t2`.`log_id` = `t1`.`listener_log_id`
                    WHERE `t1`.`calculation_id` IN (".implode(',',$calculation_ids).")
                      AND `t2`.`measuring_timestamp` >= '".date('Y-m-d H:i:s', $start_datetime)."'
                      AND `t2`.`measuring_timestamp` <= '".date('Y-m-d H:i:s', $end_datetime)."'
                    ORDER BY `t2`.`measuring_timestamp` DESC";

            $found_values = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
            $total_found_values = count($found_values);

            if (is_array($found_values) && $total_found_values) {
                for ($j = 0; $j < $total_found_values; $j++) {
                    $f_id   = 'calc_'.$found_values[$j]['calculation_id'];
                    $f_time = $found_values[$j]['measuring_timestamp'];
                    $st_id  = $found_values[$j]['station_id'];

                    if (!$prepared_data[$f_time]){
                        $prepared_data[$f_time] = array();
                        $prepared_data[$f_time]['stations'] = array();
                    }

                    if (!$prepared_data[$f_time]['data']){
                        $prepared_data[$f_time]['data'] = $features_set;
                    }

                    $prepared_data[$f_time]['data'][$f_id]['value'] = CalculationHandler::formatValue($found_values[$j]['value']);
                    $prepared_data[$f_time]['data'][$f_id]['station_id'] = $st_id;

                    if (!in_array($st_id, $prepared_data[$f_time]['stations'])) {
                        $prepared_data[$f_time]['stations'][] = $st_id;
                    }
                }
            }
        }
        //need sort
        krsort($prepared_data);
//print_r(array(
//            'prepared_header' => $prepared_header,
//            'prepared_data' => $prepared_data,
//        ));exit;
        return array(
            'prepared_header' => $prepared_header,
            'prepared_data' => $prepared_data,
        );
    }
    
    public function exportList()
	{
		$res = $this->prepareList();
        $chosen_stations = count($this->station_id);
		
        if (!$res['prepared_header'] || !is_array($this->station_id) || !$chosen_stations)
		{
            return false;
        }
        
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');

        // Turn off our amazing library autoload 
        // ( http://www.yiiframework.com/wiki/101/how-to-use-phpexcel-external-library-with-yii/)
        spl_autoload_unregister(array('YiiBase','autoload'));        

        // making use of our reference, include the main class
        // when we do this, phpExcel has its own autoload registration
        // procedure (PHPExcel_Autoloader::Register();)
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
 
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();

        // Set properties
        $objPHPExcel->getProperties()->setCreator("Delairco")
        ->setLastModifiedBy("Delairco")
        ->setTitle("XLS AWS Table Export")
        ->setSubject("XLS AWS Table Export")
        ->setDescription("Was generated with Weather Monitor software.");        
        
        $all_stations = $this->getStationsList();
        $all_features = $this->getSensorsFeaturesList();
        
        for ($i = 0; $i < $chosen_stations; $i++)
		{
            $objPHPExcel->createSheet($i);

            $objPHPExcel->setActiveSheetIndex($i);  
            $objPHPExcel->getActiveSheet()->setTitle($all_stations[$this->station_id[$i]]);
            
            $col = 1;
            $row = 2;
            $objPHPExcel->setActiveSheetIndex($i)->getColumnDimensionByColumn($col)->setWidth(20);
            $objPHPExcel->setActiveSheetIndex($i)->setCellValueByColumnAndRow($col, $row, 'Datetime');
            
            $col++;
            
			foreach ($res['prepared_header'] as $prepared_header_key => $prepared_header_value)
			{
                if (isset($prepared_header_value['station_sensors'][$this->station_id[$i]]))
				{
					if ($prepared_header_value['station_sensors'][$this->station_id[$i]] > 1)
					{
						$objPHPExcel->getActiveSheet()
						->mergeCellsByColumnAndRow($col, $row, ($col + $prepared_header_value['station_sensors'][$this->station_id[$i]] - 1), $row);

						$objPHPExcel->getActiveSheet()
						->getStyleByColumnAndRow($col, $row)->getAlignment()
						->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);            
					}    
					
                    $objPHPExcel->setActiveSheetIndex($i)->setCellValueByColumnAndRow($col, $row, $all_features[$prepared_header_value['sensor_feature_code']]);
                    $col = $col + $prepared_header_value['station_sensors'][$this->station_id[$i]];
                }
            }
            
            $col = 2;
			
            foreach ($res['prepared_header'] as $prepared_header_key => $prepared_header_value)
			{
                if (isset($prepared_header_value['station_sensors'][$this->station_id[$i]]))
				{
                    foreach ($prepared_header_value['sensors'] as $prepared_header_value_sensor_value)
					{
                        if ($prepared_header_value_sensor_value['station_id'] == $this->station_id[$i])
						{
                            $objPHPExcel->setActiveSheetIndex($i)->setCellValueByColumnAndRow($col, 3, $prepared_header_value_sensor_value['sensor_id_code']);
                            $col++;
                        }
                    }
                }
            }
            
            $row = 4;
            
            foreach ($res['prepared_data'] as $prepared_data_key => $prepared_data_value)
			{
                if (in_array($this->station_id[$i], $prepared_data_value['stations']) && $prepared_data_value['data'])
				{
                    $col = 1;
                    $objPHPExcel->setActiveSheetIndex($i)->setCellValueByColumnAndRow($col, $row, $prepared_data_key);
                    $col++;
                    
                    foreach($prepared_data_value['data'] as $prepared_data_value_k => $prepared_data_value_v)
					{
                        if ($prepared_data_value_v['station_id'] == $this->station_id[$i])
						{
                            $objPHPExcel->setActiveSheetIndex($i)->setCellValueByColumnAndRow($col, $row, $prepared_data_value_v['value']);
                            $objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);            
                            $col++;
                        }
                    }
      
                    $row++;
                }
            }    
        }

        // Set active sheet index to the first sheet, 
        // so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/xls');
        header('Content-Disposition: attachment;filename="AWS_Table_Export.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        Yii::app()->end();

        // 
        // Once we have finished using the library, give back the 
        // power to Yii... 
        spl_autoload_register(array('YiiBase','autoload'));        
    }
}