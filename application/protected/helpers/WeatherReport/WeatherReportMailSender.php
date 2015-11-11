<?php

/**
 * Class WeatherReportMailSender
*/

class WeatherReportMailSender extends BaseComponent
{
    protected $destinations ;
    protected $schedule_report ;

    protected $attachments;
    protected $scheduleProcessedReports;



    public function __construct($scheduleProcessedIdArray=array(),$logger = null)
    {
        if(!is_null($logger))
            $this->_logger = $logger;
        else
            $this->_logger = LoggerFactory::getFileLogger('email_report');


        $this->_logger->log(__METHOD__ .' schedule_processed_ids: '.print_r($scheduleProcessedIdArray,1));
        if(count($scheduleProcessedIdArray)==0)
            return false;

        $this->scheduleProcessedReports = ScheduleReportProcessed::model()
            ->with('listenerLog','ScheduleReportToStation.realStation')
            ->findAllByPk($scheduleProcessedIdArray);
        $this->schedule_report = $this->scheduleProcessedReports[0]->ScheduleReportToStation->schedule_report;

        if(!$this->schedule_report->send_email_together)
            return false;

        $this->destinations = $this->scheduleProcessedReports[0]->ScheduleReportToStation->schedule_report->destinations;


        if ($this->schedule_report->send_like_attach) {
             $this->_sendLikeAttach();
        } else {
            $this->_sendInMessageBody();
        }

    }

    protected function _sendLikeAttach(){


        $stationsIdCodesArray=array();
        foreach ($this->scheduleProcessedReports as $process ) {

            $fileArray['file_path'] = dirname(Yii::app()->request->scriptFile) .
                DIRECTORY_SEPARATOR ."files".
                DIRECTORY_SEPARATOR ."schedule_reports".
                DIRECTORY_SEPARATOR . $process->schedule_processed_id;

            $report_type = strtoupper($this->schedule_report->report_type);

            $fileArray['file_name'] = $process->ScheduleReportToStation->realStation->station_id_code .'_'.
                $report_type.'_'.
                gmdate('Y-m-d_Hi', strtotime($process->check_period_end)) .'.'.
                $this->schedule_report->report_format;

            $attachments[] = $fileArray;

            $stationsIdCodesArray[] = $process->ScheduleReportToStation->realStation->station_id_code;

        }

        $this->_logger->log(__METHOD__ .' stations: '.print_r($stationsIdCodesArray,1));

        $mail_params = array(
            '{report_id}'           => $this->schedule_report->schedule_id,
            '{stations_id_code}'           => implode(', ',$stationsIdCodesArray),
            '{actuality_time}'   => $this->scheduleProcessedReports[0]->created,
            '{schedule_period}'  => Yii::app()->params['schedule_generation_period'][$this->schedule_report->period],
            '{link}'             => Yii::app()->params['site_url_for_console'] .'/site/schedulehistory/schedule_id/'. $this->schedule_report->schedule_id,
            '{report_type}'      => $report_type,
        );

        $subject = Yii::t('letter', 'scheduled_report_allstations_mail_subject', $mail_params, null, 'en');
        $body = Yii::t('letter', 'scheduled_report_allstations_mail_message', $mail_params, null, 'en');

        if (count($this->destinations) > 0) {
            foreach ($this->destinations as $i => $destination) {
                if ($destination->method === 'mail') {
                    It::sendLetter($destination->destination_email, $subject, $body, $attachments);
                }
            }
        }
    }

    protected function _sendInMessageBody(){

        $stationsIdCodesArray=array();
        $reportStationsMessage='';
        foreach ($this->scheduleProcessedReports as $process ) {

            $file_path = dirname(Yii::app()->request->scriptFile) .
                DIRECTORY_SEPARATOR ."files".
                DIRECTORY_SEPARATOR ."schedule_reports".
                DIRECTORY_SEPARATOR . $process->schedule_processed_id;

            $report_type = strtoupper($this->schedule_report->report_type);

            $fileArray['file_name'] = $process->ScheduleReportToStation->realStation->station_id_code .'_'.
                $report_type.'_'.
                gmdate('Y-m-d_Hi', strtotime($process->check_period_end)) .'.'.
                $this->schedule_report->report_format;

            $fileArray['file_string'] = file_get_contents($file_path);


            $attachments[] = $fileArray;
            $reportStationsMessage .=  '<b>'.$process->ScheduleReportToStation->realStation->station_id_code.' ';
            $reportStationsMessage .=  $report_type.' </b><br> ';
            $reportStationsMessage .=  $fileArray['file_string'];
            $reportStationsMessage .= "<br>=====================================================<br><br>";
            $stationsIdCodesArray[] = $process->ScheduleReportToStation->realStation->station_id_code;

        }

        $this->_logger->log(__METHOD__ .' stations: '.print_r($stationsIdCodesArray,1));

        $mail_params = array(
            '{report_id}'           => $this->schedule_report->schedule_id,
            '{stations_id_code}'           => implode(', ',$stationsIdCodesArray),
            '{actuality_time}'   => $this->scheduleProcessedReports[0]->created,
            '{schedule_period}'  => Yii::app()->params['schedule_generation_period'][$this->schedule_report->period],
            '{link}'             => Yii::app()->params['site_url_for_console'] .'/site/schedulehistory/schedule_id/'. $this->schedule_report->schedule_id,
            '{report_type}'      => $report_type,
            '{messages_content}' => $reportStationsMessage
        );

        $subject = Yii::t('letter', 'scheduled_report_allstations_mail_subject', $mail_params, null, 'en');
        $body = Yii::t('letter', 'scheduled_report_allstations_messages_inside_mail_message', $mail_params, null, 'en');

        if (count($this->destinations) > 0) {
            foreach ($this->destinations as $i => $destination) {
                if ($destination->method === 'mail') {
                    It::sendLetter($destination->destination_email, $subject, $body);
                }
            }
        }

    }


}
