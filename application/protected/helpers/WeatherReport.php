<?php

/**
 * Class WeatherReport  is base class to create weather reports (bufr, synop, export_data).
 * It provides us with the interface to create report of any type, we just need to know report type and schedule_report_processed_id.
 * Its main duties:
 * 1.  create object to work with report of specific type. 
 * 2. load following important data needed to create report:
 * 2.1. schedule information basing on `schedule_report_processed`.`schedule_report_processed_id`
 * 2.2. find last appropriate message to current schedule’s run timeframe
 * 2.3. prepare station information
 * 3. Generate report. 
 *   We have 3 extra classes to extend WeatherReport class. Each  has own implementation of Generate() function:
 * 3.1. BufrWeatherReport (also has extension BufrSamoaWeatherReport with re-defined prepareSection4() function)
 * 3.2. SynopWeatherReport
 * 3.3. ExportDataWeatherReport  
 * 4. saveProcess.   Gets complete string with report, saves it. Also saves explanations (if need) and problems occurred during generation.
 * 5. deliverReport. Delivers report to all destinations registered for this schedule.
 */

class WeatherReport extends BaseComponent
{
	public $_schedule_processed_id;
  
    public $schedule_process_info;
    public $schedule_info;
    public $listener_log_info;
    public $station_info;
    
    // info required to build report
    
    public $sensors;
    public $calculations;
    
    // report information
    public $report_parts;
    public $report_complete;
    
    public $errors;
    public $explanations;
    
	
	// function to create specific WeatherReport object according to Report Type (Bufr, Synop, Metar, Speci, Export)
    public static function create($handler_id, $logger = null)
	{
		$client_code = Yii::app()->params['client_code'];
        
        $class = "{$handler_id}WeatherReport";
        $client_class = "{$client_code}{$handler_id}WeatherReport";

		if (class_exists($client_class))
		{
            if (is_null($logger))
			{
				$logger = LoggerFactory::getFileLogger('reports');
			}
			
			return new $client_class($logger); 
        }
        
		if (!class_exists($class))
		{
			return false;
        }
		
		if (is_null($logger))
		{
			$logger = LoggerFactory::getFileLogger('reports');
		}

        return new $class($logger);            
    }
    
	// loads record from `schedule_process` table and related information
    public function load($schedule_processed_id)
	{
		$this->_logger->log(__METHOD__, array('schedule_processed_id' => $schedule_processed_id));
		
        $this->_schedule_processed_id = $schedule_processed_id;
        $this->_init();        
    }
    
	// prepares information about schedule, base message(s), station.
    protected function _init()
	{
		$this->_logger->log(__METHOD__);
		
        $current_user_timezone = date_default_timezone_get();
        
		$timezone_id = 'UTC';
		
        if ($timezone_id !=  $current_user_timezone)
		{
			TimezoneWork::set($timezone_id);
        } 
        
        $this->_prepareScheduleInfo();
        $this->_prepareListenerLogInfo();
        $this->_prepareStationInfo();
        
        if ($timezone_id != $current_user_timezone) 
		{
			TimezoneWork::set($current_user_timezone);
        } 
        
        if ($this->errors)
		{
            return false;
        }
   
        return true;
    }

	// prepares information about schedule_process and schedule
    protected function _prepareScheduleInfo()
	{
		$this->_logger->log(__METHOD__);
		
		$criteria = new CDbCriteria();


        $criteria->with = array(
            'ScheduleReportToStation' => array(
                'with' => array('realStation','schedule_report'),
            ),
           // 'station' => array("with"=>array('realStation')),
        );
		$criteria->compare('schedule_processed_id', $this->_schedule_processed_id);
		
		$this->schedule_process_info = ScheduleReportProcessed::model()->find($criteria);

        $this->_logger->log(__METHOD__.' schedule_process_info ' .print_r($this->schedule_process_info,1));


		if (is_null($this->schedule_process_info))
		{
            $this->errors[] = 'Scheduled process info (#'. $this->_schedule_processed_id .') can not be found';
            
			return;
        }

        $this->schedule_info = $this->schedule_process_info->ScheduleReportToStation->schedule_report;
        $this->schedule_info->station_id = $this->schedule_process_info->ScheduleReportToStation->realStation->station_id;
		if (is_null($this->schedule_info))
		{
            $this->errors[] = 'Schedule info (#'. $this->schedule_process_info->schedule_id .') can not be found';
            
			return;
        }
		
        return;
    }
    
	// prepares base message for Bufr or Synop OR base messages for Export
    protected function _prepareListenerLogInfo()
	{
		$this->_logger->log(__METHOD__);
		
		if (!is_null($this->schedule_process_info)) {
			$criteria = new CDbCriteria();
			$criteria->compare('station_id', $this->schedule_info->station_id);
			$criteria->compare('failed', '0');
			$criteria->compare('measuring_timestamp', '>='. $this->schedule_process_info->check_period_start);
			$criteria->compare('measuring_timestamp', '<='. $this->schedule_process_info->check_period_end);
			$criteria->order = 'measuring_timestamp desc, log_id desc';
			
			if (in_array($this->schedule_info->report_type, array('bufr', 'synop', 'metar', 'speci'))) {
				$criteria->limit = '1';
				$logRecord = ListenerLog::model()->find($criteria);
//				$this->schedule_process_info->listener_log_id = is_null($logRecord) ? 0 : $logRecord->log_id;
//				$this->schedule_process_info->save();
				$this->listener_log_info = $logRecord;
				if (is_null($this->listener_log_info)) {
                    $this->errors[] = 'Listener log info (#'. $this->schedule_process_info->listener_log_id .') can not be found';
					return;
                }
            } else {
				$logRecords = ListenerLog::model()->findAll($criteria);

                if (count($logRecords) > 0) {
					$logIds = array();
					foreach ($logRecords as $logRecord) {
                        $logIds[] = $logRecord->log_id;
                    }
                    $this->listener_log_info = $logRecords;
                    $this->schedule_process_info->listener_log_ids = implode(',', $logIds);
                    $this->schedule_process_info->save();
                } else {
                    $this->listener_log_info = null;
                    $this->schedule_process_info->listener_log_ids = '';
                    $this->schedule_process_info->save();
					$this->errors[] = 'Listener log info (#'. $this->schedule_process_info->listener_log_id .') can not be found';
					return;
                }
            }       
        }
        return;
    }

	// prepares information about station
    protected function _prepareStationInfo()
	{
		$this->_logger->log(__METHOD__);
		
        if (isset($this->schedule_info->station)) {
            $this->station_info = $this->schedule_process_info->ScheduleReportToStation->realStation;

            if (is_null($this->station_info)) {
                $this->errors[] = 'Station (#'. $this->schedule_info->station_id .') can not be recognized';
				return;
            }
        }
    }
    
	// prepares information about sensors and their data basing on specific message
    public function prepareSensorsInfo($listener_log_id)
	{
		$this->_logger->log(__METHOD__);
		
        $sensors = array();

        if (!is_null($this->schedule_process_info->ScheduleReportToStation->realStation->station_id))
		{
            // get sensors
            $sql = "SELECT `t1`.`station_sensor_id`, `t1`.`sensor_id_code`, 
                           `t2`.`handler_id_code`, 
                           `t3`.`feature_code`, `t3`.`feature_constant_value`,
                           `t3`.`sensor_feature_id`,
                           `t4`.`code` AS `metric_code`, 
                           `t5`.`sensor_feature_value`, 
                           `t5`.`period` AS `sensor_feature_period`,
                           `t6`.`code` AS `value_metric_code`,
                           `t5`.`is_m`
                    FROM `".StationSensor::model()->tableName()."`             `t1`
                    LEFT JOIN `".SensorDBHandler::model()->tableName()."`      `t2` ON `t2`.`handler_id` = `t1`.`handler_id`
                    LEFT JOIN `".StationSensorFeature::model()->tableName()."` `t3` ON (`t3`.`sensor_id` = `t1`.`station_sensor_id`)
                    LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t4` ON `t4`.`metric_id` = `t3`.`metric_id`
                    LEFT JOIN `".SensorData::model()->tableName()."`           `t5` ON (`t5`.`sensor_feature_id` = `t3`.`sensor_feature_id` AND `t5`.`listener_log_id` = '". $listener_log_id ."')
                    LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t6` ON `t6`.`metric_id` = `t5`.`metric_id`
                    WHERE `t1`.`station_id` = '". $this->schedule_process_info->ScheduleReportToStation->realStation->station_id ."'
                    ORDER BY `t1`.`sensor_id_code` ASC";
			
            $sensors_info = Yii::app()->db->createCommand($sql)->queryAll();        

            if (!$sensors_info)
			{
                $this->errors[] = 'Station has no sensors';
                return;
            }   

            $sensors = array ();

            foreach ($sensors_info as $value) 
			{
				if (($value['feature_code'] === 'height') && ($value['handler_id_code'] === 'Pressure'))
				{
					$sensors['pressure_height']['height'] = $value['feature_constant_value'];   
					$sensors['pressure_height']['height_metric_code'] = $value['metric_code'];       
					
					continue;
                }
				
                $value['metric_code'] = isset($value['value_metric_code']) ? $value['value_metric_code'] : $value['metric_code'];
                unset($value['value_metric_code']);

                if (!isset($sensors[$value['feature_code']])) 
                {
					$sensors[$value['feature_code']] = $value;
				}
            }
        }
        
        return $sensors;
    }
	
	// prepares calculations values basing on specific message
    public function _prepareCalculationsInfo($listener_log_id)
	{
		$this->_logger->log(__METHOD__);
		
        $calculations = array();
        
        if (!is_null($this->station_info))
		{
            $sql = "SELECT `t1`.`value`, `t3`.`handler_id_code`
                    FROM `".StationCalculationData::model()->tableName()."`    `t1`
                    LEFT JOIN `".StationCalculation::model()->tableName()."`   `t2` ON `t2`.`calculation_id` = `t1`.`calculation_id`
                    LEFT JOIN `".CalculationDBHandler::model()->tableName()."` `t3` ON `t3`.`handler_id` = `t2`.`handler_id`
                    WHERE `t1`.`listener_log_id` = '". $listener_log_id ."'";
            
			$calculations_info = Yii::app()->db->createCommand($sql)->queryAll();

            if ($calculations_info)
			{
                foreach ($calculations_info as $value)
				{
                    if (!isset($calculations[$value['handler_id_code']]))
					{
						$calculations[$value['handler_id_code']] = $value;
					}
                }
            }        
        }
        
        return $calculations;        
    }
        

	// each extended file has its own implementation of generation function
    public function generate($schedule_processed_id)
	{
		return;
    }
    
	// prepares complete string of report to be wrote into file
    public function getReportComplete()
	{
        if (!$this->report_complete)
		{
			$this->prepareReportComplete();
        }
        
        return $this->report_complete;
    }

	// returns prepared report parts
    public function getReportParts()
	{
		return $this->report_parts;
    }

	// returns prepared report problems
    public function getReportErrors()
	{
		return $this->errors;
    }
    
    // saves report into file and related information into database
    public function saveProcess()
	{
		$this->_logger->log(__METHOD__);
        $string = $this->getReportComplete();
		$obj = ScheduleReportProcessed::model()->findByPk($this->_schedule_processed_id);
		$obj->is_processed = 1;
        $obj->serialized_report_errors = serialize($this->errors);
        $obj->serialized_report_explanations = serialize($this->explanations);
        $obj->save();
		$file_path = $this->tmpReportFile();
    }

	// delovers report to all required destinations
    public function deliverReport()
	{
        $this->_logger->log(__METHOD__);
				
		ini_set('memory_limit', '-1');
        
        $destinations = ScheduleReportDestination::getList($this->schedule_info->schedule_id);
        $total = count($destinations);
    	
		$this->_logger->log(__METHOD__, array('destination_count' => $total));
        
        $file_path = dirname(Yii::app()->request->scriptFile) .
						DIRECTORY_SEPARATOR ."files".
						DIRECTORY_SEPARATOR ."schedule_reports".
						DIRECTORY_SEPARATOR . $this->schedule_process_info->schedule_processed_id;
		
        $report_type = strtoupper($this->schedule_info->report_type);
        
		$file_name = $this->station_info->station_id_code .'_'.
						$report_type .'_'.
            
						gmdate('Y-m-d_Hi', strtotime($this->schedule_process_info->check_period_end)) .'.'. 
						$this->schedule_info->report_format;
        
        if (count($destinations) > 0)
		{
            foreach($destinations as $i => $destination)
			{
                if ($destination->method === 'mail' and !$this->schedule_info->send_email_together)
                {
                    $this->_logger->log(__METHOD__, array(
                            'destination' => $i + 1,
                            'report_type' => $report_type,
                            'method' => $destination->method,
                            'email' => $destination->destination_email
                        )
                    );

                    if ($this->schedule_info->send_like_attach) {
                        $mail_params = array(
                            '{station_id_code}' => $this->station_info->station_id_code,
                            '{actuality_time}' => $this->schedule_process_info->created,
                            '{schedule_period}' => Yii::app()->params['schedule_generation_period'][$this->schedule_info->period],
                            '{report_file_name}' => $file_name,
                            '{link}' => Yii::app()->params['site_url_for_console'] . '/site/schedulehistory/schedule_id/' . $this->schedule_info->schedule_id,
                            '{report_type}' => $report_type,
                        );

                        $subject = Yii::t('letter', 'scheduled_report_mail_subject', $mail_params, null, 'en');
                        $body = Yii::t('letter', 'scheduled_report_mail_message', $mail_params, null, 'en');

                        It::sendLetter($destination->destination_email, $subject, $body, array(0 => array('file_path' => $file_path, 'file_name' => $file_name)));

                        $this->_logger->log(__METHOD__ .' Message send with attached file');

                    } else {

                        $mail_params = array(
                            '{station_id_code}' => $this->station_info->station_id_code,
                            '{actuality_time}' => $this->schedule_process_info->created,
                            '{schedule_period}' => Yii::app()->params['schedule_generation_period'][$this->schedule_info->period],
                            '{messages_content}' => file_get_contents($file_path),
                            '{link}' => Yii::app()->params['site_url_for_console'] . '/site/schedulehistory/schedule_id/' . $this->schedule_info->schedule_id,
                            '{report_type}' => $report_type,
                        );

                        $subject = Yii::t('letter', 'scheduled_report_mail_subject', $mail_params, null, 'en');
                        $body = Yii::t('letter', 'scheduled_report__messages_inside_mail_message', $mail_params, null, 'en');

                        It::sendLetter($destination->destination_email, $subject, $body);

                        $this->_logger->log(__METHOD__ .' Message send in letter body');
                        $this->_logger->log(__METHOD__ .'     '.file_get_contents($file_path));
                    }
                    $this->_logger->log(__METHOD__ .' Deliver via mail DONE.');
                }
				else if ($destination->method === 'ftp')
				{
					$this->_logger->log(__METHOD__, array(
							'destination' => $i + 1,
							'report_type' => $report_type,
							'method' => $destination->method,
						
							'destination_ip' => $destination->destination_ip,
							'destination_ip_port' => $destination->destination_ip_port,
							'destination_ip_user' => $destination->destination_ip_user,
							'destination_ip_password' => $destination->destination_ip_password,
						)
					);
					
                    $errors = array();
                    $conn_id = @ftp_connect($destination->destination_ip, $destination->destination_ip_port);

                    if ($conn_id !== false)
					{
						$result = false;
						
                        if (isset($destination->destination_ip_user))
						{
							$result = @ftp_login($conn_id, $destination->destination_ip_user, $destination->destination_ip_password);
                        }
						
						if ($result === true)
						{
                            if (@ftp_chdir($conn_id, $destination->destination_ip_folder))
							{
								$mode = ($this->schedule_info->report_type === 'bufr') ? 'rb' : 'r';
                                $fp = @fopen($file_path, $mode);
                                
                                if ($fp !== false)
								{
                                    // try to upload $file
                                    $res = @ftp_pasv($conn_id, true);
                                    
									if ($res === true)
									{
                                        $mode = $this->schedule_info->report_type == 'bufr' ? FTP_BINARY : FTP_ASCII;
                                        
										if (!@ftp_fput($conn_id, $file_name, $fp, $mode))
										{
											$errors[] = 'There was a problem with uploading file '. $file_name .' to ftp '. $destination->destination_ip;
                                        }  
                                    }
									else
									{
										$errors[] = 'Can not set passive mode';                                        
                                    }
									
                                    @fclose($fp);
                                } 
								else
								{
									$errors[] = 'Can not open stream to copy file to '. $file_path;
                                }
                            }
							else
							{ 
								$errors[] = 'Can not change ftp directory to '. $destination->destination_ip_folder;
                            }
                        }
						else
						{
                            $errors[] = 'Connection with FTP '. $destination->destination_ip .' was failed with given login & password';
                        }
						
                        @ftp_close($conn_id); 
                    } 
					else 
					{
						$errors[] = 'Connection with FTP '.$destination->destination_ip.' was failed';
                    }

                    if ($errors)
					{
                        foreach ($errors as $error)
						{
							$this->errors[] = date('Y-m-d H:i') .' '. $error;
                        }
						
                        $this->saveProcess();
                    }
					
					$this->_logger->log(__METHOD__ .' Deliver via ftp DONE.');
                }
				else if ($destination->method === 'local_folder')
				{ 
                    $this->_logger->log(__METHOD__, array(
							'destination' => $i + 1,
							'report_type' => $report_type,
							'method' => $destination->method,
							'destination_folder' => $destination->destination_local_folder,
						)
					);
					
                    $settings = Settings::model()->findByPk(1);
                    $destinationPath = $settings->scheduled_reports_path . 
											DIRECTORY_SEPARATOR . $destination->destination_local_folder;
                    
					if (!is_dir($destinationPath))
					{
                       @mkdir($destinationPath, 0777, true);
                    }              
					
                    copy($file_path, $destinationPath . DIRECTORY_SEPARATOR . $file_name); 
                    
					$this->_logger->log(__METHOD__ .' Deliver to local folder DONE.');
                }                
            }
        }
        
        $this->_logger->log(__METHOD__ .' Delivery completed.');
    }

	// write prepared report string into file, returns path to file
    public function tmpReportFile()
	{
		$this->_logger->log(__METHOD__);
		
        $file_name = $this->schedule_process_info->schedule_processed_id;
        
        $file_dir  = dirname(Yii::app()->request->scriptFile) .
						DIRECTORY_SEPARATOR ."files".
						DIRECTORY_SEPARATOR ."schedule_reports";

        if (!is_dir($file_dir))
		{
			@mkdir($file_dir, 0777, true);
        }   
		
        $file_path = $file_dir . DIRECTORY_SEPARATOR . $file_name;
        
        $string = $this->getReportComplete();
        $this->_logger->log(__METHOD__.'------------'.$string);
        $this->_logger->log(__METHOD__.'$file_path: '.$file_path);
        if ($this->schedule_info->report_type === 'bufr')
		{
			$string = It::string2binary($string);
        } 
		
        if (($h = @fopen($file_path, "w+")) !== false)
		{
            $this->_logger->log(__METHOD__.'$h:'.$h);
            $this->_logger->log(__METHOD__.'$string:'.$string);
            fwrite($h, $string);
            fclose($h);
        }

        return $this->returnResult(__METHOD__, $file_path);
    }
    
	// 
    public function prepareReportComplete()
	{
		throw new Exception('Parent method called');	
	}
}
?>