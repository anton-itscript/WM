<?php

class ScheduleReports extends Schedule
{

    public function run($args)
    {
//        if(!Yii::app()->mutex->lock('ScheduleReports',3600)) {
//            Yii::app()->end();
//        }

//        $synchronization = new Synchronization();
//        if(!$synchronization->isMaster() and $synchronization->isProcessed())
//            return;

        $generationTime = time();
        
        $proper_periods = $this->getProperPeriods($generationTime);


        if (count($proper_periods) === 0) {
			$this->_logger->log(__METHOD__ . ' Exiting. No proper periods found.' . "\n\n");
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
			$this->_logger->log(__METHOD__ . ' Exiting. No proper reports found.' . "\n\n");
//            Yii::app()->mutex->unlock();
//			Yii::app()->end();
		}
		
		$this->_logger->log(__METHOD__ . ' New scheduled reports', array('report count' => count($scheduledReports)));
		
		$reportProcesses = array();
		foreach($scheduledReports as $scheduledReport) {
			$this->_logger->log("\n");
			$this->_logger->log(__METHOD__ .' Check scheduled report', array('schedule_id' => $scheduledReport->schedule_id));

			$check_period = $this->getCheckPeriod($generationTime, $scheduledReport->period);

			if ($scheduledReport->report_type === 'data_export') {
                $this->_logger->log(__METHOD__ . ' scheduledReport->report_type  = data_export');
				// add record about schedule running to process afterwards
                for ($i=0;$i<count($scheduledReport->station);$i++) {
                    $schedule_report_process = new ScheduleReportProcessed;

                    $schedule_report_process->sr_to_s_id            = $scheduledReport->station[$i]->id;
                    $schedule_report_process->check_period_start    = $check_period[3];
                    $schedule_report_process->check_period_end      = $check_period[4];

                    $schedule_report_process->save();


                    $scheduledReport->last_scheduled_run_fact       = $check_period[3];//1
                    $scheduledReport->last_scheduled_run_planned    = $check_period[4];//2

                    if ($scheduledReport->validate()) {
                        $scheduledReport->save(false);
                    } else {
                        $this->_logger->log(__METHOD__ . ' Schedule report not saved ', array('schedule_error' => $scheduledReport->getErrors()));
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
                            $this->_logger->log(__METHOD__ .' Schedule report not saved ', array('schedule_error' => $scheduledReport->getErrors()));
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
						$weatherReport = WeatherReport::create('Synop', $this->_logger);
						break;
					
					case 'bufr' :
						$weatherReport = WeatherReport::create('Bufr', $this->_logger);
                        break;
					
					case 'metar' :
						$weatherReport = WeatherReport::create('Metar', $this->_logger);
						break;

					case 'odss' :
						$weatherReport = WeatherReport::create('ODSS', $this->_logger);
						break;

					default :
						$weatherReport = WeatherReport::create('Export', $this->_logger);
						break;
				}
                try{
                    $weatherReport->load($reportProcess['schedule_processed_id']);
                    $weatherReport->generate();
                    $weatherReport->saveProcess();
                    $weatherReport->deliverReport();
                } catch (Exteption $e) {
                    $this->_logger->log(__METHOD__ .' Error ', array('err' => $e->getMessage()));
                }
                $this->_logger->log(__METHOD__ .' Completed', array('num' => $i++, 'total' => $total));
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
        $this->_logger->log(__METHOD__ .' Schedule report completed'."\n\n\n\n\n\n\n\n\n");
//        Yii::app()->mutex->unlock();
    }

}

?>