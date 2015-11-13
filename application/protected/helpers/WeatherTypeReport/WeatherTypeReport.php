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

abstract class WeatherTypeReport extends BaseComponent
{
    protected $station_type;
    protected $start_time;
    protected $end_time;
    protected $schedule_type_report;


    protected $data;
    protected $transformed_data;
    protected $report_complete;
    protected $csv_exporter_object;

    protected $schedule_type_report_processed;

    public abstract function transformData();
    public abstract function prepareReportComplete();

    // function to create specific WeatherReport object according to Report Type (Bufr, Synop, Metar, Speci, Export)
    public static function create($handler_id, $logger = null)
    {
        $client_code = Yii::app()->params['client_code'];

        $class = "{$handler_id}WeatherTypeReport";
        $client_class = "{$client_code}{$handler_id}WeatherTypeReport";

        if (class_exists($client_class))
        {
            if (is_null($logger))
            {
                $logger = LoggerFactory::getFileLogger($client_class);
            }

            return new $client_class($logger);
        }

        if (!class_exists($class))
        {
            return false;
        }

        if (is_null($logger))
        {
            $logger = LoggerFactory::getFileLogger('typesReports');
        }

        return new $class($logger);
    }


    public function load($schedule_type_report)
    {
        $this->schedule_type_report = $schedule_type_report;
        $this->station_type         = $schedule_type_report->station_type;
        $this->start_time           = $schedule_type_report->start_datetime;
        $this->end_time             = $schedule_type_report->next_run_planned;

        $this->strp = new ScheduleTypeReportProcessed();
        $res = $this->strp->isItemExists($schedule_type_report->ex_schedule_id, $schedule_type_report->start_datetime);
        if ($res) {
            $this->strp = $res;
        }

        if($this->getData()){
            $this->transformData();
            $this->prepareReportComplete();
            $this->saveReport();
        }


    }

    protected function getData()
    {
        $this->_logger->log(__METHOD__.' $this->start_time: ' . print_r($this->start_time,1));
        $this->_logger->log(__METHOD__.' $this->end_time: ' . print_r($this->end_time,1));

        $result = ListenerLog::model()->getAllDataInType($this->station_type, $this->start_time,$this->end_time);
        $this->_logger->log(__METHOD__.' $result COUNT: '.print_r(COUNT($result),1));
        if(!is_null($result))  {
//            $this->schedule_type_report->updateLastReportedMessageData($result[0]->created);
            $this->data = $result ;
            return true;
        } else {
            $this->data = array();
//            $this->schedule_type_report->updateLastReportedMessageData($this->end_time);
            return false;
        }
    }

    public function saveReport()
    {

        $this->strp->ex_schedule_id       = $this->schedule_type_report->ex_schedule_id;
        $this->strp->check_period_start   = $this->schedule_type_report->start_datetime;


        $check_period_end = strtotime($this->schedule_type_report->start_datetime) + ($this->schedule_type_report->period*60);
        $this->strp->check_period_end     = date('Y-m-d H:i:s',$check_period_end);
        //        aging_time_delay
        $this->strp->aging_time = (time() + ($this->schedule_type_report->aging_time_delay*60));
//        $this->_logger->log(__METHOD__.' time(): '.time());
//        $this->_logger->log(__METHOD__.' $strp->aging_time: '.$strp->aging_time);
//        $this->_logger->log(__METHOD__.' $this->schedule_type_report->aging_time_delay: '.$this->schedule_type_report->aging_time_delay);
//        $this->_logger->log(__METHOD__.' $this->schedule_type_report: '.print_r($this->schedule_type_report,1));
        $this->strp->save();
        $pk = $this->strp->getSavedPk();


        $file_dir = $this->strp->getFileDir();
        $this->_logger->log(__METHOD__.'  '.print_r($file_dir. DIRECTORY_SEPARATOR . $pk,1));
        //file_put_contents($file_dir. DIRECTORY_SEPARATOR . $pk,$this->report_complete);
        $this->csv_exporter_object->createCSV($file_dir. DIRECTORY_SEPARATOR . $pk);

        $this->strp->file_content = file_get_contents($file_dir. DIRECTORY_SEPARATOR . $pk);

        $this->schedule_type_report_processed = $this->strp;
        $this->createReportSendLog();
    }

    public function createReportSendLog()
    {
        $destinations = ScheduleTypeReportDestination::getList($this->schedule_type_report->ex_schedule_id);
        if (is_array($destinations)) {
            foreach ($destinations as $destItem) {
                $sendLog = ScheduleTypeReportSendLog::isExist($this->schedule_type_report_processed->getSavedPk(),$destItem->ex_schedule_destination_id);
                if (!$sendLog) {
                    $sendLog = new ScheduleTypeReportSendLog();
                    $sendLog->ex_schedule_processed_id = $this->schedule_type_report_processed->getSavedPk();
                    $sendLog->ex_schedule_destination_id = $destItem->ex_schedule_destination_id;
                    $sendLog->sent = 0;
                    $sendLog->save();
                }
            }
        }
    }


    public function deliverReport()
    {
        $this->_logger->log(__METHOD__);


        if(count($this->data)==0)
            return false;

        ini_set('memory_limit', '-1');

        $this->_logger->log(__METHOD__, array('ex_schedule_id' => $this->schedule_type_report->ex_schedule_id));
        $this->_logger->log(__METHOD__. ' :' . print_r($this->schedule_type_report,1));

        $destinations = ScheduleTypeReportDestination::getList($this->schedule_type_report->ex_schedule_id);
        $total = count($destinations);

        $this->_logger->log(__METHOD__, array('destination_count' => $total));
        //$this->_logger->log(__METHOD__ . ' :'. print_r($destinations,1));


        $file_path = $this->schedule_type_report_processed->full_file_name;

//        $report_type = strtoupper($this->schedule_info->report_type);

        $file_name = $this->schedule_type_report_processed->check_period_start
                        . '-' . $this->schedule_type_report_processed->check_period_end
                        . '.' . $this->schedule_type_report->report_format;

        //$this->schedule_type_report->report_format;
        $report_type = $this->schedule_type_report->report_type;
        //$this->schedule_type_report_processed->file_content;


        if (count($destinations) > 0)
        {
            foreach($destinations as $i => $destination)
            {
                if ($destination->method === 'mail' )
                {
                    $this->_logger->log(__METHOD__, array(
                            'destination' => $i + 1,
                            'report_type' => $report_type,
                            'method' => $destination->method,
                            'email' => $destination->destination_email
                        )
                    );

                    $mail_params = array(
                        'station_id_code' => $this->schedule_type_report->station_type,
                        'actuality_time' => $this->schedule_type_report->created,
                        'schedule_period' => Yii::app()->params['schedule_generation_period'][$this->schedule_type_report->period],
                        'report_file_name' => $file_name,
                        'link' => '', //Yii::app()->params['site_url_for_console'] . '/site/schedulehistory/schedule_id/' . $this->schedule_type_report->ex_schedule_id,
                        'report_type' => $report_type,
                    );

                    $subject = Yii::t('letter', 'scheduled_report_mail_subject', $mail_params, null, 'en');

                    $settings = Settings::model()->findByPk(1);
                    $mailSender = new mailSender('odss_reports',$mail_params);
                    $sendResult = $mailSender->setAttachments(array(array('file_path' => $file_path, 'file_name' => $file_name)))
                                ->setRecipient($destination->destination_email)
                                ->setFrom($settings->mail__sender_address,$settings->mail__sender_name)
                                ->setSubject($subject)
                                ->setHtmlBody()
                                ->send();

                    $this->_logger->log(__METHOD__ .' $sendResult: '.$sendResult);
                    $this->_logger->log(__METHOD__ .' Message send with attached file');
                    $this->_logger->log(__METHOD__ .' Deliver via mail DONE.');

                }
                else if ($destination->method === 'ftp')
                {
                    // use it if you have some superstition about "../"
//                    $fileCopier = new FileCopier;
//                    $file_path = $fileCopier->rmPathSteps($file_path);

                    $ftpClient =new FtpClient();
                    $errors = $ftpClient->connect($destination->destination_ip,$destination->destination_ip_port)
                                        ->login($destination->destination_ip_user,$destination->destination_ip_password)
                                        ->setFolder($destination->destination_ip_folder)
                                        ->openLocalFile($file_path)
                                        ->upload($file_name)
                                        ->closeLocalFile()
                                        ->getErrors();

                    $this->_logger->log(__METHOD__ ." ftp errors:". print_r($errors,1));
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

                    $destinationPath =  $this->schedule_type_report_processed->getFileDir() .
                        DIRECTORY_SEPARATOR . $destination->destination_local_folder;
                    $this->_logger->log(__METHOD__ .' $file_path: '. $file_path);
                    $this->_logger->log(__METHOD__ .' $destinationPath: '. $destinationPath);
                    $this->_logger->log(__METHOD__ .' $file_name: '. $file_name);

                    $fileCopier = new FileCopier();
                    $errors =  $fileCopier->copy($file_path,$destinationPath . DIRECTORY_SEPARATOR . $file_name)->getErrors();

                    $this->_logger->log(__METHOD__ . print_r($errors,1));
                    $this->_logger->log(__METHOD__ .' Deliver to local folder DONE.');
                }
            }
        }

        $this->_logger->log(__METHOD__ .' Delivery completed.');
    }


    public function newReportAdd()
    {
        $typeReportSendStatus = new TypeReportSendStatus;
        $typeReportSendStatus->newReportAdd();

        $object = ExchangeODSS::getInstance();
        $this->_logger->log(__METHOD__.'  '.print_r($object, 1));
    }



    public function dataSort($array, $key, $order='desc')
    {
        ini_set('xdebug.max_nesting_level', 0);
//        $this->_logger->log(__METHOD__.' $array COUNT: '.print_r(COUNT($array),1));
        $count = count($array);

        if ($order=='asc') {
            for ($i=$count-1;$i>0;$i--) {
                if ($array[$i]->$key < $array[$i-1]->$key ) {
                    $temp=$array[$i];
                    $array[$i]=$array[$i-1];
                    $array[$i-1]=$temp;
                    $array = $this->dataSort($array,$key,$order);
                }
            }
        }

        if ($order=='desc') {
            for ($i=$count-1;$i>0;$i--) {
                if ($array[$i]->$key > $array[$i-1]->$key ) {
                    $temp=$array[$i];
                    $array[$i]=$array[$i-1];
                    $array[$i-1]=$temp;
                    $array = $this->dataSort($array,$key,$order);
                }
            }
        }

        return $array;
    }

}
?>