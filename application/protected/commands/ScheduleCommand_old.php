<?php

/*
 * Is called using command: "php console.php schedule", doesn't requires arguements
 * Is called every minute using schtasks
 * 
 * This console script is looking for scheduled reports that 
 * have to be generated at the moment.
 */

class ScheduleCommand extends CConsoleCommand
{
	/**
	 * Logger.
	 * 
	 * @access protected
	 * @var ILogger
	 */
	protected static $_logger = null;

	public function init()
	{
		parent::init();
		
		ini_set('memory_limit', '-1');
		set_time_limit(0);
		
		self::$_logger = LoggerFactory::getFileLogger('reports');

		// All reports are generated basing on data in UTC time.
        TimezoneWork::set('UTC');
	}
	
    public function run($args)
    {
        if(!Yii::app()->mutex->lock('ScheduleCommand',3600)) {
            Yii::app()->end();
        }

        $synchronization = new Synchronization();
        if(!$synchronization->isMaster() and $synchronization->isProcessed())
            return;

        $generationTime = time();
        
        $proper_periods = ScheduleCommand::getProperPeriods($generationTime);


        if (count($proper_periods) === 0) {
			self::$_logger->log(__METHOD__ . ' Exiting. No proper periods found.' . "\n\n");
//            Yii::app()->mutex->unlock();
//			Yii::app()->end();
		}
		
		$criteria = new CDbCriteria();
        $criteria->select = array(
			'schedule_id',
			'report_type',
			'station_id',
			'report_format',
			'period',
			'last_scheduled_run_planned',
			'last_scheduled_run_fact',
			'(`last_scheduled_run_planned` + INTERVAL `period` MINUTE) AS nextScheduleTime',
			'UNIX_TIMESTAMP(`last_scheduled_run_planned` + INTERVAL `period` MINUTE) AS nextScheduleUnixTime',
		);

        $criteria->with=array('station');

        $criteria->compare('period', '>0');
		$criteria->compare('period', $proper_periods);
		$criteria->addCondition('(UNIX_TIMESTAMP(`last_scheduled_run_planned` + INTERVAL `period` MINUTE) <= UNIX_TIMESTAMP()
										OR
									`last_scheduled_run_planned` = "0000-00-00 00:00:00")');

        /** @var array|ScheduleReport[] $scheduledReports */
		$scheduledReports = ScheduleReport::model()->findAll($criteria);

		if (count($scheduledReports) === 0) {
			self::$_logger->log(__METHOD__ . ' Exiting. No proper reports found.' . "\n\n");
            Yii::app()->mutex->unlock();
			Yii::app()->end();
		}
		
		self::$_logger->log(__METHOD__ . ' New scheduled reports', array('report count' => count($scheduledReports)));
		
		$reportProcesses = array();
		foreach($scheduledReports as $scheduledReport) {
			self::$_logger->log("\n");
			self::$_logger->log(__METHOD__ .' Check scheduled report', array('schedule_id' => $scheduledReport->schedule_id));

			$check_period = ScheduleCommand::getCheckPeriod($generationTime, $scheduledReport->period);                     

			if ($scheduledReport->report_type === 'data_export') {
                self::$_logger->log(__METHOD__ . ' scheduledReport->report_type  = data_export');
				// add record about schedule running to process afterwards
                for ($i=0;$i<count($scheduledReport->station);$i++) {
                    $schedule_report_process = new ScheduleReportProcessed;

                    $schedule_report_process->sr_to_s_id = $scheduledReport->station[$i]->id;
                    $schedule_report_process->check_period_start = $check_period[3];
                    $schedule_report_process->check_period_end = $check_period[4];

                    $schedule_report_process->save();


                    $scheduledReport->last_scheduled_run_fact = $check_period[1];
                    $scheduledReport->last_scheduled_run_planned = $check_period[2];

                    if ($scheduledReport->validate()) {
                        $scheduledReport->save(false);
                    } else {
                        self::$_logger->log(__METHOD__ . ' Schedule report not saved ', array('schedule_error' => $scheduledReport->getErrors()));
                    }

                    $reportProcesses[$scheduledReport->station[$i]->id] = array(
                        'schedule_id' => $scheduledReport->schedule_id,
                        'schedule_processed_id' => $schedule_report_process->schedule_processed_id,
                        'schedule_info' => $scheduledReport,
                        'check_period_start' => $check_period[3],
                        'check_period_end' => $check_period[4]
                    );
                }
			} else {



                $logRecords=array();
                foreach ($scheduledReport->station as $station) {

                    $criteria = new CDbCriteria();

				    $criteria->compare('station_id', $station->station_id);
                    $criteria->compare('failed', '0');
                    $criteria->compare('measuring_timestamp', '>'. $check_period[0]);
                    $criteria->compare('measuring_timestamp', '<='. $check_period[1]);
                    $criteria->order = 'measuring_timestamp desc, log_id desc';
                    $criteria->limit = '1';


                    $logRecord = ListenerLog::model()->find($criteria);
                    if(!is_null($logRecord))
                        $logRecords[] = $logRecord;


                }



                // add record about schedule running to process afterwards (only in case system received base message)




				if (count($logRecords)>0)
				{
                    for ($i=0;$i<count($scheduledReport->station);$i++) {
                        $schedule_report_process = new ScheduleReportProcessed;

                        $continue = false;
                        foreach ($logRecords  as $logRecord) {

                            if ($logRecord->station_id == $scheduledReport->station[$i]->station_id) {
                                $schedule_report_process->listener_log_id = $logRecord->log_id;
                                $listener_log_id = $logRecord->log_id;

                                $continue = false;
                                break;
                            } else {
                                $continue = true;
                            }
                        }

                        if($continue)
                            continue;

                        $schedule_report_process->sr_to_s_id = $scheduledReport->station[$i]->id;
                        $schedule_report_process->check_period_start = $check_period[3];
                        $schedule_report_process->check_period_end = $check_period[4];


                        $schedule_report_process->save();

                        $scheduledReport->last_scheduled_run_fact = $check_period[1];
                        $scheduledReport->last_scheduled_run_planned = $check_period[2];

                        if ($scheduledReport->validate()) {
                            $scheduledReport->save(false);
                        } else {
                            self::$_logger->log(__METHOD__ .' Schedule report not saved ', array('schedule_error' => $scheduledReport->getErrors()));
                        }

                        $reportProcesses[$scheduledReport->station[$i]->id] = array(
                            'log_id'                => $listener_log_id,
                            'schedule_id'           => $scheduledReport->schedule_id,
                            'schedule_processed_id' => $schedule_report_process->schedule_processed_id,
                            'schedule_info'         => $scheduledReport,
                            'check_period_start'    => $check_period[3],
                            'check_period_end'      => $check_period[4]
                        );
                    }
				}
			}
		}




		if (count($reportProcesses) > 0) {
            $total = count($reportProcesses);
            $i = 1;
			
			foreach ($reportProcesses as $reportProcess) {

				$weatherReport = null;
				
				switch(strtolower($reportProcess['schedule_info']->report_type)) {
					case 'synop' :
						$weatherReport = WeatherReport::create('Synop', self::$_logger);
						break;
					
					case 'bufr' :
						$weatherReport = WeatherReport::create('Bufr', self::$_logger);
                        break;
					
					case 'metar' :
						$weatherReport = WeatherReport::create('Metar', self::$_logger);
						break;

					case 'odss' :
						$weatherReport = WeatherReport::create('ODSS', self::$_logger);
						break;

					default :
						$weatherReport = WeatherReport::create('Export', self::$_logger);
						break;
				}
                try{
                    $weatherReport->load($reportProcess['schedule_processed_id']);
                    $weatherReport->generate();
                    $weatherReport->saveProcess();
                    $weatherReport->deliverReport();
                } catch (Exteption $e) {
                    self::$_logger->log(__METHOD__ .' Error ', array('err' => $e->getMessage()));
                }
                self::$_logger->log(__METHOD__ .' Completed', array('num' => $i++, 'total' => $total));
			}
        }
        //send report from sttions together

        $scheduleProcessedIdArray_schedule_id=array();
        foreach ($reportProcesses as $reportProcess) {
            $scheduleProcessedIdArray_schedule_id[$reportProcess['schedule_id']][] =  $reportProcess['schedule_processed_id'];
        }
        foreach ($scheduleProcessedIdArray_schedule_id as $scheduleProcessedIdArray) {
             new WeatherReportMailSender($scheduleProcessedIdArray);
        }
        self::$_logger->log(__METHOD__ .' Schedule report completed'."\n\n\n\n\n\n\n\n\n");
        Yii::app()->mutex->unlock();
    }

    /*
     * returns array(
     *  0 => min timestamp for searching last message
     *  1 => fact timestamp of last schedule running (can be defferent (+/-) than HH:00) (max timestamp for searching last message)
     *  2 => timestamp of fact schedule run  
     *  3 => min timestamp for data export
     *  4 => max timestamp for data export
     * )
     */
    public static function getCheckPeriod($generationTime, $report_period)
	{
        $check_period = false;
        
		$cur_hour = date('H', $generationTime);
        $cur_min  = date('i', $generationTime);
		
        $month = date('m', $generationTime);
        $day   = date('d', $generationTime);
        $year  = date('Y', $generationTime);
        
        if (($cur_min >= 1) && ($cur_min <= 10))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour - 1, 50, 0,     $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 0, 0,        $month, $day, $year))
            );
        }
        
		if (($cur_min >= 31) && ($cur_min <= 40))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour, 20, 0,       $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 30, 0,       $month, $day, $year))
            );            
        }
        
        if (($cur_min >= 16) && ($cur_min <= 25))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour, 05, 0,       $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 15, 0,       $month, $day, $year))
            );            
        }     
        
        if (($cur_min >= 46) && ($cur_min <= 55))
		{
            $check_period = array(
                date('Y-m-d H:i:s', mktime($cur_hour, 35, 0,       $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, $cur_min, 0, $month, $day, $year)),
                date('Y-m-d H:i:s', mktime($cur_hour, 45, 0,       $month, $day, $year))
            );            
        }        
        
        if ($check_period === false)
		{
            $now =  mktime($cur_hour, $cur_min, 0, $month, $day, $year);
            
			$check_period = array(
                date('Y-m-d H:i:s', $now - $report_period * 60),
                date('Y-m-d H:i:s', $now),
                date('Y-m-d H:i:s', $now),
            );
        }        
        
        $max_timestamp_for_data_export = $check_period[2];
        $min_timestamp_for_data_export = date('Y-m-d H:i:s', strtotime($max_timestamp_for_data_export) - $report_period * 60);
        
		self::$_logger->log(__METHOD__ .' Prepare check period', array(
				'cur_hour' => $cur_hour, 
				'cur_min' => $cur_min, 
				'period' => $report_period, 
				'max_timestamp' => $max_timestamp_for_data_export, 
				'min_timestamp' => $min_timestamp_for_data_export
			)
		);
		
        $check_period[] = $min_timestamp_for_data_export;
        $check_period[] = $max_timestamp_for_data_export;        
       
        return $check_period;
    }

    public static function generatePeriodArray($minutesCount){

        $periodInSeconds = $minutesCount * 60;

        $minutes = array();
        $seconds = mktime(0, 0, 0, 1, 1, 1970);
        $seconds2 = mktime(0, 0, 0, 1, 2, 1970);
        $daySeconds = $seconds2 - $seconds;
        for($i=0;$i<$daySeconds;$i+=$periodInSeconds){
            $minutes[] =date('H:i', $seconds);
            $seconds+= $periodInSeconds;
        }

        return $minutes;
    }

    /**
     * Function returns array of periods. Script is able to generate schedule for these periods only.
	 * 
     * @param int $generationTime
     * @return array 
     */
    public static function getProperPeriods($generationTime)
	{
		self::$_logger->log(__METHOD__, array('generationTime' => $generationTime));
		
		$compare_min = null;
		$cur_hour = date('H', $generationTime);
        $cur_min  = date('i', $generationTime);
		
		if ($cur_min >= 0 && $cur_min <= 14) {
			$compare_min = '00';
		} elseif ($cur_min >= 15 && $cur_min <= 29) {
			$compare_min = '15';
		} elseif ($cur_min >= 30 && $cur_min <= 44) {
			$compare_min = '30';
		} if ($cur_min >= 45 && $cur_min <= 59) {
			$compare_min = '45';
		}         

		$cur_time = $cur_hour .':'. $compare_min;
		$proper_periods = array();




		$scheduler = [

            '1'  => self::generatePeriodArray(1),

            '5'  => self::generatePeriodArray(5),

			'15' => [
                '00:00', '00:15', '00:30', '00:45', '01:00', '01:15', '01:30', '01:45',
                '02:00', '02:15', '02:30', '02:45', '03:00', '03:15', '03:30', '03:45',
                '04:00', '04:15', '04:30', '04:45', '05:00', '05:15', '05:30', '05:45',
                '06:00', '06:15', '06:30', '06:45', '07:00', '07:15', '07:30', '07:45',
                '08:00', '08:15', '08:30', '08:45', '09:00', '09:15', '09:30', '09:45',
                '10:00', '10:15', '10:30', '10:45', '11:00', '11:15', '11:30', '11:45',
                '12:00', '12:15', '12:30', '12:45', '13:00', '13:15', '13:30', '13:45',
                '14:00', '14:15', '14:30', '14:45', '15:00', '15:15', '15:30', '15:45',
                '16:00', '16:15', '16:30', '16:45', '17:00', '17:15', '17:30', '17:45',
                '18:00', '18:15', '18:30', '18:45', '19:00', '19:15', '19:30', '19:45',
                '20:00', '20:15', '20:30', '20:45', '21:00', '21:15', '21:30', '21:45',
                '22:00', '22:15', '22:30', '22:45', '23:00', '23:15', '23:30', '23:45',
            ],
			'30'  => [
                '00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30',
                '04:00', '04:30', '05:00', '05:30', '06:00', '06:30', '07:00', '07:30',
                '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
                '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30',
                '20:00', '20:30', '21:00', '21:30', '22:00', '22:30', '23:00', '23:30',
            ],
			'60'  => [
                '00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00',
				'08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00',
				'16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00',
            ],
			'120' => ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '16:00', '18:00', '20:00', '22:00'],
			'180' => ['00:00', '03:00', '06:00', '09:00', '12:00', '15:00', '18:00', '21:00'],
			'360' => ['00:00', '06:00', '12:00', '18:00'],
			'540' => ['00:00', '09:00', '18:00', '03:00', '12:00', '21:00', '06:00', '15:00'],
			'720' => ['00:00', '12:00'],
			'900' => ['00:00', '15:00', '06:00', '21:00', '12:00', '03:00', '18:00', '09:00'],
			'1080' => ['00:00', '18:00', '12:00', '06:00'],
			'1440' => ['00:00']
		];

		foreach ($scheduler as $key => $value) {
			if (in_array($cur_time, $value)) {
				$proper_periods[] = $key;
			}
		}

		self::$_logger->log(__METHOD__, array('proper_periods' => implode(',', $proper_periods)));
		
		return $proper_periods;
    }
}

?>