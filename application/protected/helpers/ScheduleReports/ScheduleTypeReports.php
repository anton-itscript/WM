<?php


class ScheduleTypeReports extends Schedule
{

    public function run($args)
    {
//        if (!Yii::app()->mutex->lock('ScheduleTypeReports',3600)) {
//            Yii::app()->end();
//        }

//        $synchronization = new Synchronization();
//        if (!$synchronization->isMaster() and $synchronization->isProcessed())
//            return;

        $generationTime = time();
        $proper_periods = $this->getProperPeriods($generationTime);
        $this->_logger->log(__METHOD__ . ' proper periods: ' . print_r($proper_periods,1));
//        if (count($proper_periods) === 0) {
//			$this->_logger->log(__METHOD__ . ' Exiting. No proper periods found.' . "\n\n");
//          Yii::app()->mutex->unlock();
//			Yii::app()->end();
//		}

        $criteria = new CDbCriteria();
        $criteria->select = array(
			'ex_schedule_id',
			'report_type',
			'station_type',
			'report_format',
			'period',
			'aging_time_delay',
			'start_datetime',
			'next_run_planned',
//			'(`last_scheduled_run_planned` + INTERVAL `period` MINUTE) AS nextScheduleTime',
//			'UNIX_TIMESTAMP(`last_scheduled_run_planned` + INTERVAL `period` MINUTE) AS nextScheduleUnixTime',
		);

        $criteria->compare('period', '>0');
		$criteria->addCondition('active=1');


        /** @var array|ScheduleTypeReport[] $scheduledReports */
		$scheduledTypeReports = ScheduleTypeReport::model()->findAll($criteria);

        $this->_logger->log(__METHOD__ . ' $scheduledReports: '.print_r($scheduledTypeReports,1));

		if (count($scheduledTypeReports) === 0) {
//			$this->_logger->log(__METHOD__ . ' Exiting. No proper reports found.' . "\n\n");
//            Yii::app()->mutex->unlock();
//			Yii::app()->end();
		}

        /**
         * @var $typeReportItem ScheduleTypeReport
         */
        foreach ($scheduledTypeReports as $typeReportItem ) {

            if(
                strtotime($typeReportItem->next_run_planned_delayed) != mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y') )
                &&
                strtotime($typeReportItem->next_run_planned_delayed) > mktime(date('H'), date('i'), 0, date('m'), date('d'), date('Y'))
            )
                continue;


            $weatherTypeReport = null;

            switch(strtolower($typeReportItem->report_type)) {

//                case 'synop' :
//                    $weatherTypeReport = WeatherTypeReport::create('Synop', $this->_logger);
//                    break;
//
//                case 'bufr' :
//                    $weatherTypeReport = WeatherTypeReport::create('Bufr', $this->_logger);
//                    break;
//
//                case 'metar' :
//                    $weatherTypeReport = WeatherTypeReport::create('Metar', $this->_logger);
//                    break;

                case 'odss' :
                    $weatherTypeReport = WeatherTypeReport::create('ODSS', $this->_logger);
                    break;

//                default :
//                    $weatherTypeReport = WeatherTypeReport::create('Export', $this->_logger);
//                    break;
            }
            try{

                $weatherTypeReport->load($typeReportItem);
                $typeReportItem->setTimeStep();

//                $synchronization = new Synchronization();
//                if(!$synchronization->isMaster() and $synchronization->isProcessed())
//                    return;

//                $weatherTypeReport->deliverReport();
                $weatherTypeReport->newReportAdd();
            } catch (Exteption $e) {
                $this->_logger->log(__METHOD__ .' Error ', array('err' => $e->getMessage()));
            }

        }

    }


}

?>