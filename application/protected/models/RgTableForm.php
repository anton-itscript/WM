<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class RgTableForm extends CFormModel
{
    public $station_id;
    public $date_from;
    public $time_from;
    public $date_to;
    public $time_to;
    public $rate_volume;
    public $order_field;
    public $order_direction;
    
    private $stations;
    private $all_stations;
    private $session_name = 'rgtable_filter9';
    
    private $start_timestamp;
    private $end_timestamp;

    public static function model($className=__CLASS__)
	{
		return parent::model($className);
    }    
    
    public function init()
	{
        $this->getFromMemory();
    
        $this->all_stations = Station::getList('rain');
        $handler = array();
        SensorDBHandler::handlerWithFeature($handler,'rg');
        $features = array_shift($handler)->features;
        if ($this->all_stations)
		{
            foreach ($this->all_stations as $key => $value)
			{
                $this->all_stations[$key]['filter_limit_max'] = round(($features['rain']->filter_max/60) * $this->rate_volume,2);
                $this->all_stations[$key]['filter_limit_min'] = round(($features['rain']->filter_min/60) * $this->rate_volume,2);
                $this->all_stations[$key]['filter_limit_diff'] = round(($features['rain']->filter_diff/60) * $this->rate_volume,2);
//                $this->all_stations[$key]['filter_limit_max'] = round(($value['filter_limit_max']/60) * $this->rate_volume,2);
//                $this->all_stations[$key]['filter_limit_min'] = round(($value['filter_limit_min']/60) * $this->rate_volume,2);
//                $this->all_stations[$key]['filter_limit_diff'] = round(($value['filter_limit_diff']/60) * $this->rate_volume,2);

                $this->stations[$value['station_id']] = $value['station_id_code'].' - '.$value['display_name'];
            }
        }

        return parent::init();
    }
    
    //===== validation
    public function rules()
	{
        return array(
            array('station_id', 'numerical', 'integerOnly' =>true,  'allowEmpty' => true),
            array('date_from,date_to', 'match', 'pattern' => '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/'),
            array('date_from,date_to', 'length', 'max' => 10),
            array('time_from,time_to', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/'),
            array('time_from,time_to', 'length', 'max' => 5),
            array('date_to', 'checkDatesInterval'),
            array('rate_volume', 'checkRateVolume'),
        );
    }    
    
    public function checkDatesInterval()
	{
        if (!$this->hasErrors('date_from') && !$this->hasErrors('date_to') && !$this->hasErrors('time_from') && !$this->hasErrors('time_to'))
		{
            $this->start_timestamp = strtotime($this->date_from.' '.$this->time_from);
            $this->end_timestamp = strtotime($this->date_to.' '.$this->time_to);           
            
			if ($this->end_timestamp <= $this->start_timestamp)
			{
                $this->addError('date_to', 'End date and time must be later than start.');
            }
        }
    }

    public function checkRateVolume()
	{
        return true;
    }
    //======
    
    //==== work with memory
    public function putToMemory()
	{
        $session = new CHttpSession();
        $session->open();
 
        $session[$this->session_name] = array(
            'station_id'      => $this->station_id,
            'date_from'       => $this->date_from,
            'date_to'         => $this->date_to,
            'time_from'       => $this->time_from,
            'time_to'         => $this->time_to,        
            'order_field'     => $this->order_field,
            'order_direction' => $this->order_direction,
            'rate_volume'     => $this->rate_volume,
        );
    }
    
    public function clearMemory()
	{
        $session = new CHttpSession();
        $session->open();
        $session[$this->session_name] = array();
        
		$this->getFromMemory();
    }
    
    public function getFromMemory()
	{
        $session = new CHttpSession();
        $session->open();
        
        $this->station_id      = $session[$this->session_name] ? $session[$this->session_name]['station_id'] : 0;
        $this->date_from       = $session[$this->session_name] ? $session[$this->session_name]['date_from'] : '';
        $this->date_to         = $session[$this->session_name] ? $session[$this->session_name]['date_to'] : '';
        $this->time_from       = $session[$this->session_name] ? $session[$this->session_name]['time_from'] : '00:00';
        $this->time_to         = $session[$this->session_name] ? $session[$this->session_name]['time_to'] : '23:59';        
        $this->order_field     = $session[$this->session_name] ? $session[$this->session_name]['order_field'] : 'date';
        $this->order_direction = $session[$this->session_name] ? $session[$this->session_name]['order_direction'] : 'DESC';
        $this->rate_volume     = $session[$this->session_name] ? $session[$this->session_name]['rate_volume'] : '10';    
    }
    //==== 
        
    
    public function getGroupSumsList()
	{
		return array(1 => '1 min', 5 => '5 min', 10 => '10 min', 20 => '20 min', 30 => '30 min', 60 => '60 min');
    }
    
    public function getAllStations()
	{
		return $this->all_stations;
    }
    
    public function getStationsList()
	{
		return $this->stations;
    }
    
    
    public function setStationId($station_id)
	{
        $this->station_id = $station_id;
        $this->putToMemory();
    }

    public function getRainMetric()
	{
        if ($this->station_id)
		{
            $sql = "SELECT `t3`.`html_code`
                    FROM `".StationSensorFeature::model()->tableName()."` `t1`
                    LEFT JOIN `".StationSensor::model()->tableName()."` `t2` ON `t2`.`station_sensor_id` = `t1`.`sensor_id`
                    LEFT JOIN `".RefbookMetric::model()->tableName()."` `t3` ON `t3`.`metric_id` = `t1`.`metric_id`
                    WHERE `t2`.`station_id` = '".$this->station_id."' AND `t1`.`feature_code` = 'rain'
                    ORDER BY `t2`.`sensor_id_code`";

            return CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryScalar();
        }
        return 'mm';
    }
    
    public function getCurrentStation()
	{
        if (is_array($this->all_stations) && isset($this->station_id))
		{
            foreach ($this->all_stations as $key => $value)
			{
                if ($value['station_id'] == $this->station_id)
				{
                    return $value;
                }
            }
        }
		
        return null;
    }

    public function setOrders($field)
	{
        if (in_array($field, array('name', 'date', 'lasttx', 'lasthr', 'last24hr')))
		{
            if ($field == $this->order_field)
			{
				$this->order_direction = $this->order_direction == 'ASC' ? 'DESC' : 'ASC';
            } 
			else
			{
				$this->order_direction = 'ASC';
            }
			
            $this->order_field = $field;    
            $this->putToMemory();
        }
    }
    
    
    public function prepareList($page_size = 10)
	{
        $stations = $this->getAllStations();
      
        if ($stations)
		{
            $sql_where = array();
            
			//---------------- Start groupping
            $use_field = '';
            
			if ($this->rate_volume == 1)
			{
                $use_field = 'sensor_value';
            } 
			else 
			{
                if ($this->rate_volume == 5) {
                    $use_field = '5min_sum';
                    $tmp = array('00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
                } else if ($this->rate_volume == 10) {
                    $use_field = '10min_sum';
                    $tmp = array('00', '10', '20', '30', '40', '50');
                } else if ($this->rate_volume == 20) {
                    $use_field = '20min_sum';
                    $tmp = array('00', '20', '40');
                } else if ($this->rate_volume == 30) {
                    $use_field = '30min_sum';
                    $tmp = array('00', '30');
                } else if ($this->rate_volume == 60) {
                    $use_field = '60min_sum';
                    $tmp = array('00');
                }
                $sql_where[] = "DATE_FORMAT(`sd`.`measuring_timestamp`, '%i') IN ('".implode("','",$tmp)."')";
            }
            $sql_where[] = "`sd`.`".$use_field."` > 0";
            
            //---------------- End groupping



            //---------------- Start date filter
            if ($this->date_from) {
                $sql_where[] = "`sd`.`measuring_timestamp` >= '".date('Y-m-d H:i:s', strtotime($this->date_from.' '.$this->time_from))."'";
            }
            if ($this->date_to) {
                $sql_where[] = "`sd`.`measuring_timestamp` <= '".date('Y-m-d H:i:s', strtotime($this->date_to .' '.$this->time_to))."'";
            }
            //---------------- End date filter



            //---------------- Start Station filter
            if ($this->station_id) {
                $sql_where[] = "`sd`.`station_id` = '".$this->station_id."'";
                
            } else {

                $sql_groupped_table = "SELECT `sensor_id`, MAX(`measuring_timestamp`) AS `MaxDateTime` FROM `".SensorDataMinute::model()->tableName()."` WHERE `".$use_field."` > 0 ";
                if ($this->date_from) {
                    $sql_groupped_table .= " AND `measuring_timestamp` >= '".date('Y-m-d H:i:s', strtotime($this->date_from.' '.$this->time_from))."' ";
                }
                if ($this->date_to) {
                    $sql_groupped_table .= " AND `measuring_timestamp` <= '".date('Y-m-d H:i:s', strtotime($this->date_to .' '.$this->time_to))."' ";
                }
                $sql_groupped_table .= " GROUP BY `sensor_id` ";

                $sql = "SELECT `tt`.`sensor_data_id`
                        FROM `".SensorDataMinute::model()->tableName()."` `tt`
                        INNER JOIN ( {$sql_groupped_table} ) `groupedtt` ON `tt`.`sensor_id` = `groupedtt`.`sensor_id` AND `tt`.`measuring_timestamp` = `groupedtt`.`MaxDateTime`";
                $last_values = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryColumn();

                
                
                
                if (!$last_values) {
                    $last_values = array(0);
                }
                $sql_where[] = "`sd`.`station_id` IN (".implode(',', array_keys($stations)).") AND `sd`.`sensor_data_id` IN (".implode(',',$last_values).")";
            }
            //---------------- End Station filter



            if ($page_size > 0) {
                $sql = "SELECT COUNT(*)
                        FROM `".SensorDataMinute::model()->tableName()."` `sd`
                        WHERE ".implode(' AND ', $sql_where);
                $total = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryScalar();

                $pages = new CPagination($total);
                $pages->pageSize = $page_size;
                //$pages->applyLimit($criteria);
            }

            if ($this->order_field == 'date') {
                $sql_order = "`sd`.`measuring_timestamp` ".$this->order_direction;
            } elseif ($this->order_field == 'name') {
                $sql_order = "`st`.`display_name` ".$this->order_direction;
            } elseif ($this->order_field == 'lasttx') {
                $sql_order = "`sd`.`".$use_field."` ".$this->order_direction;
            } elseif ($this->order_field == 'lasthr') {
                $sql_order = "`sd`.`60min_sum` ".$this->order_direction;
            } elseif ($this->order_field == 'last24hr') {
                $sql_order = "`sd`.`1day_sum` ".$this->order_direction;
            }
            $sql = "SELECT `st`.`display_name`,
                           `st`.`station_id_code`,
                           `st`.`station_id`,

                           `ll`.`message`,
                           `ll`.`log_id`,

                           `sd`.`sensor_data_id`,
                           `sd`.`battery_voltage`,
                           `sd`.`sensor_id`,
                           `sd`.`measuring_timestamp`,
                            DATE_FORMAT(`sd`.`measuring_timestamp`, '%m/%d/%Y') AS `tx_date_formatted`,
                            DATE_FORMAT(`sd`.`measuring_timestamp`, '%H:%i') AS `tx_time_formatted`,
                           `sd`.`sensor_value`,
                           `sd`.`5min_sum`,
                           `sd`.`10min_sum`,
                           `sd`.`20min_sum`,
                           `sd`.`30min_sum`,
                           `sd`.`60min_sum`,
                           `sd`.`1day_sum`,

                           `sd`.`bucket_size`,
                           `sd`.`1day_sum` AS `day_value_mm`,
                           `sd`.`60min_sum` AS `hour_value_mm`

                    FROM `".SensorDataMinute::model()->tableName()."` `sd`
                    LEFT JOIN `".ListenerLog::model()->tableName()."` `ll` ON `sd`.`listener_log_id` = `ll`.`log_id`
                    LEFT JOIN `".Station::model()->tableName()."`     `st` ON `st`.`station_id` = `sd`.`station_id`

                    WHERE ".implode(' AND ', $sql_where)."
                    ORDER BY {$sql_order} ";
            if ($page_size) {
                $sql .= " LIMIT ".($pages->currentPage *$pages->pageSize).", ".$pages->pageSize;
            }
            $res = CStubActiveRecord::getDbConnect(true)->createCommand($sql)->queryAll();
            if ($res) {

                $total_found = count($res);

                foreach ($res as $key => $value) {
                    
                    $res[$key]['battery_voltage_formatted'] = $value['battery_voltage']/10;

                    $res[$key]['tx_value_mm']      = $value[$use_field]*$value['bucket_size'];
                    $res[$key]['tx_value_rate_mm'] = $value[$use_field]*$value['bucket_size'] * 60 / $this->rate_volume;

                    $res[$key]['day_value_mm'] = $value['day_value_mm']*$value['bucket_size'];
                    $res[$key]['hour_value_mm'] = $value['hour_value_mm']*$value['bucket_size'];

                    $res[$key]['period'] = $this->rate_volume;


                    $hour_value_id = date('YmdH', strtotime($value['measuring_timestamp']));
                    $res[$key]['hour_value_id'] = $hour_value_id;

                    $res[$key]['hour_value_rate_mm'] = 0;


                    if ($stations[$value['station_id']]['filter_limit_max'] > 0 ) {
                        if ($res[$key]['tx_value_mm'] >= $stations[$value['station_id']]['filter_limit_max'])
                            $res[$key]['filter_errors'][] = "R >= <b>".$stations[$value['station_id']]['filter_limit_max']."</b>  ";
                    }
                    if ($stations[$value['station_id']]['filter_limit_min'] > 0 ) {
                        if ($res[$key]['tx_value_mm'] <= $stations[$value['station_id']]['filter_limit_min'])
                            $res[$key]['filter_errors'][] = "R <= <b>".$stations[$value['station_id']]['filter_limit_min']."</b>  ";
                    }
                    if ($stations[$value['station_id']]['filter_limit_diff'] > 0 ) {
                        if ($key != 0 && (abs($res[$key]['tx_value_mm'] - $res[$key-1]['tx_value_mm'])) >= $stations[$value['station_id']]['filter_limit_diff'])
                            $res[$key]['filter_errors'][] = "|R - R0| >= <b>".$stations[$value['station_id']]['filter_limit_diff']."</b> ";
                    }
                }

                foreach ($res as $key => $value) {

                    if ($key != 0 && $res[$key]['hour_value_id'] != $res[$key-1]['hour_value_id']) {
                        $res[$key-1]['hour_value_rate_mm'] = $res[$key-1]['hour_value_mm'];
                    } elseif ($key == ($total_found-1)) {
                        $res[$key]['hour_value_rate_mm'] = $res[$key]['hour_value_mm'];
                    }

                }
            }
        }
        
        
        return array('list' => $res, 'pages' => $pages);    
    }
    
    public function exportList() {

        $res = $this->prepareList(0);
        $rain_metric = $this->getRainMetric();
        $rg_table_data = $res['list'];
        
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');

        // Turn off our amazing library autoload 
        // ( http://www.yiiframework.com/wiki/101/how-to-use-phpexcel-external-library-with-yii/ )
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
        ->setTitle("XLS RG Table Export")
        ->setSubject("XLS RG Table Export")
        ->setDescription("Was generated with Weather Monitor software.");        
        
        $objPHPExcel->setActiveSheetIndex(0);  
        $objPHPExcel->getActiveSheet()->setTitle("RG Table Export");        
        
        if ($rg_table_data) {
            
            $row = 2;
            $col = 1;
            
             
            // Prepare Table's header, Row 1
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(1, $row, 1, $row+2);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, $row, 'Display Name');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(1)->setWidth(13);
            
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(2, $row, 2, $row+2);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2, $row, 'Station ID');
            
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(3, $row, 3, $row+2);                
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, $row, 'Volt.');
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(4, $row, 11, $row); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $row, 'Rain');

            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(12, $row, 13, $row+1); 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12, $row, 'Last Check Msg');     
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(14, $row, 14, $row+2);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14, $row, 'Message');            
            
            $row++;
            
            // Prepare Table's header, Row 2
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(4, $row, 4, $row+1);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, $row, 'Date'); 
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(4)->setWidth(10);
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(5, $row, 5, $row+1);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, $row, 'Period');              
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(6, $row, 7, $row);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, $row, 'Last Period');             
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(8, $row, 9, $row);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8, $row, 'Last Hr');             
            
            $objPHPExcel->getActiveSheet()
                        ->mergeCellsByColumnAndRow(10, $row, 11, $row);             
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10, $row, 'Last 24Hr');                 
            
            $row++;
            
             // Prepare Table's header, Row 3
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, $row, 'Amt ('.$rain_metric.')');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(6)->setWidth(14);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7, $row, 'Rate ('.$rain_metric.'/hr)');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(7)->setWidth(14);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8, $row, 'Amt ('.$rain_metric.')');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(8)->setWidth(14);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9, $row, 'Rate ('.$rain_metric.'/hr)');  
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(9)->setWidth(14);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10, $row, 'Amt ('.$rain_metric.')');
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(10)->setWidth(14);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11, $row, 'Avg Rate ('.$rain_metric.'/hr)');   
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(11)->setWidth(14);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12, $row, 'Date/Time'); 
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimensionByColumn(12)->setWidth(15);
            
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13, $row, 'Status');            
            
            for ($i=1; $i <=14; $i++) {
                for ($j = 2; $j < 5; $j++) {
                    $objPHPExcel->getActiveSheet()
                    ->getStyleByColumnAndRow($i, $j)->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
                }
            }       
            
            
            // Prepare Table's Body
            $row = 6;
            foreach ($rg_table_data as $key => $value) {
                
                $col = 1;
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['display_name']);
                $col++;
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['station_id_code']);
                $col++;

                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['battery_voltage_formatted'].'V');
                $col++;
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['tx_date_formatted']);
                $col++;                

                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['tx_time_formatted']);
                $col++;                

                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['tx_value_mm']);
                $col++;                
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['tx_value_rate_mm']);
                $col++;                  
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['hour_value_mm']);
                $col++;                 
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, ($value['hour_value_rate_mm'] ? $value['hour_value_rate_mm'] : ' '));
                $col++;                  
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['day_value_mm']);
                $col++;                  
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, ' ');
                $col++;                   
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['tx_date_formatted'].' '.$value['tx_time_formatted']);
                $col++; 
                
                if (count($value['filter_errors'])) {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, implode("\n",$value['filter_errors']));
                } else {
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, 'OK');
                }  
                $col++;
                
                $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($col, $row, $value['message']);
                $col++;                
                
                $row++;
            }
        }
            

        // Set active sheet index to the first sheet, 
        // so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/xls');
        header('Content-Disposition: attachment;filename="RG_Table_Export.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        Yii::app()->end();

        // 
        // Once we have finished using the library, give back the 
        // power to Yii... 
        spl_autoload_register(array('YiiBase','autoload'));          
        
    }
    
    
    
    public function attributeLabels() {
         return array(
             'station_id'  => It::t('site_label', 'filter_select_station'),
             'date_from'   => It::t('site_label', 'filter_date_from'),
             'date_to'     => It::t('site_label', 'filter_date_to'),
             'time_from'   => It::t('site_label', 'filter_time_from'),
             'time_to'     => It::t('site_label', 'filter_time_to'),
             'rate_volume' => It::t('site_label', 'filter_select_rate_volume'),
         );
     }
     
    public function afterValidate() {
         $this->putToMemory();
         parent::afterValidate();
    }
}
?>