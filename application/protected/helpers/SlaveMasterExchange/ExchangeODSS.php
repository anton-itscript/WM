<?php
/**
 * Created by PhpStorm.
 * User: dima
 * Date: 21.07.2015
 * Time: 18:47
 *
 * singleton
 */

class ExchangeODSS  extends  Exchange  {

    protected $ident = 'ExchangeODSS';

    protected $clientMessage;
    protected $serverMessage;

    private static  $instance = null;
    protected static $className = __CLASS__;


    protected $_logger;
    protected $processedReportsModel;

    protected $client_step01_found_reports;

    protected $reportsComes;

    private function __construct()
    {

        $this->processedReportsModel = new ScheduleTypeReportProcessed();
        $this->synchronization = new Synchronization();
        $this->_logger = LoggerFactory::getFileLogger('ExchangeODSS');
        TimezoneWork::set('UTC');
    }

    private function __clone() {}

    /**
     * @return ExchangeODSS
     */
    public static function getInstance()
    {
        if (null === self::$instance)
        {
            self::$instance = new self::$className();
        }
        return self::$instance;
    }




    public function clientMessage()
    {

        //$this->clientMessage = array('messageIdent'=>$this->ident, 'messageData'=>'ZAPROS OT CLIENTA');
        switch ( $this->step) {
            case '01':
                $this->clientStep01();
                break;
            case '02':
                $this->clientStep02();
                break;
            case '03':
                $this->clientStep03();
                break;
            case '04':
                $this->clientStep04();
                break;
        }
        return serialize($this->clientMessage);
    }

    public function serverMessage()
    {
        switch ( $this->step) {
            case '01':
                $this->serverStep01();
                break;
            case '02':
                $this->serverStep02();
                break;
            case '03':
                $this->serverStep03();
                break;
            case '04':
                $this->serverStep04();
                break;
        }
        return serialize($this->serverMessage);
    }

    protected function clientStep01()
    {
        $sendData = $this->msStatus();
        $sendData['uids']   = array();
        $sendData['has_new_report'] = 0;
        $typeReportSendStatus = new TypeReportSendStatus;
        if ($typeReportSendStatus->hasNewReport()) {
            $sendData['has_new_report'] = 1;

            $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
            $reports = $scheduleTypeReportSendLog->getUnsentReportItems(0);

            $this->client_step01_found_reports    = $reports;
            $sendData['uids']       = $reports['uids'];
        }
        $this->createClientMessage('01',0,$sendData);
    }

    protected function serverStep01()
    {
        //comes message data and step
        $this->messageData;
        $this->step;

        $sendData = $this->msStatus();
        //only from master
        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="master") {
            $sendData['not_sent_reports'] = array();
            $sendData['sent_reports'] = array();
            if ($this->messageData['has_new_report']) {
                $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
                $reports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['uids']);
                foreach ($reports['result'] as $item) {
                    if ($item->report_processed->is_synchronized==2) {
                        $item->report_processed->is_synchronized=0;
                        $item->report_processed->save();
                    }
                }
                $sendData['not_sent_reports'] = $reports['uids'];
                $reports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['uids'], true);
                $sendData['sent_reports'] = $reports['uids'];
            }
        }
        $this->createServerMessage('02',0,$sendData);
    }

    protected function clientStep02()
    {
        //comes message data and step
        $this->messageData;
        $this->step;

        $sendData = $this->msStatus();
        // only from slave
        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="slave") {

            $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
            if (count($this->messageData['not_sent_reports'])) {
                $notSentReports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['not_sent_reports']);
//            array('result'=>$result, 'uids'=>$UIDsArray);
                foreach ($notSentReports['result'] as $reportItem) {
                    $this->sendReport($reportItem);
                    $reportItem->report_processed->is_synchronized=1;
                    $reportItem->report_processed->save();
                }
                $sentReports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['not_sent_reports'], true);
                $sendData['sent_reports'] = $sentReports['uids'];
            } else {
                $sendData['sent_reports'] = array();
            }

            if (count($this->messageData['sent_reports'])) {
                $notSentReports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['sent_reports']);
//            array('result'=>$result, 'uids'=>$UIDsArray);
                foreach ($notSentReports['result'] as $reportItem) {
                    $reportItem->sent = 1;
                    $reportItem->report_processed->is_synchronized=1;
                    $reportItem->report_processed->save();
                    $reportItem->save();
                }
            }
        }
        $this->createClientMessage('02',0,$sendData);
    }

    protected function serverStep02()
    {

        $sendData = $this->msStatus();
        //only from master
        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="master") {
            if (count($this->messageData['sent_reports'])) {
                $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
                $notSentReports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['sent_reports']);
                foreach ($notSentReports['result'] as $sendLogReportItems) {
                    $sendLogReportItems->sent = 1;
                    $sendLogReportItems->save();
                    $sendLogReportItems->report_processed->is_synchronized=1;
                    $sendLogReportItems->report_processed->save();
                }
            }
        }
        $this->createServerMessage('03',0,$sendData);
    }

    protected function clientStep03()
    {

        $sendData = $this->msStatus();
        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="slave") {
            $this->client_step01_found_reports['uids'];
            $scheduleTypeReportProcessed = new ScheduleTypeReportProcessed();
            $uids = array();
            if (count($this->client_step01_found_reports['uids'])){
                foreach ($this->client_step01_found_reports['uids'] as $uidItem) {
                    $uids[] = $uidItem['report_uid'];
                }
            }
            // slave has not uid like this;
            $unSynchronizedReports = $scheduleTypeReportProcessed->findByUIDs($uids, 0);

            if (!is_null($unSynchronizedReports))
                foreach ($unSynchronizedReports['result'] as $reportItem) {

                    if ($reportItem->aging_time < time()) {
                        $reportItem->is_synchronized = 2; // could not synchronize
                        $reportItem->save();
                    }
                }
            //we don't have new reports
            $typeReportSendStatus = new TypeReportSendStatus;
            $typeReportSendStatus->noReport();
        }
        $this->createClientMessage('03',0,$sendData);
    }

    protected function serverStep03()
    {
        $sendData = $this->msStatus();

        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="master") {
            $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
            $reports = $scheduleTypeReportSendLog->getUnsentReportItems(0);

            $this->server_step03_found_reports    = $reports;
            $sendData['not_sent_reports']       = $reports['uids'];

        }
        $this->createServerMessage('04', 0, $sendData);
    }

    protected function clientStep04()
    {
        $sendData = $this->msStatus();
        $sendData['not_searched_reports'] = array();
        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="slave") {
            $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
            $notSentReports = $scheduleTypeReportSendLog->findByUIDsWithDestinations($this->messageData['not_sent_reports']);
            if(!is_null($notSentReports['uids']))
                foreach ($notSentReports['uids'] as $key => $uidItem) {
                    foreach ($this->messageData['not_sent_reports'] as $key2 => $uidItem2) {
                        if($notSentReports['uids'][$key]['report_uid'] == $this->messageData['not_sent_reports'][$key2]['report_uid'] ) {

                            //if was found unsent reports with is_synchronized = 2
                            // get it to next circle with is_synchronized = 0
                            foreach($notSentReports['result'] as  $sendLogItem){

                                if ($sendLogItem->report_processed->ex_schedule_processed_id == $key) {
                                    $sendLogItem->report_processed->is_synchronized = 0 ;
                                    $sendLogItem->report_processed->save();


                                    //we have new reports ^)
                                    $typeReportSendStatus = new TypeReportSendStatus;
                                    $typeReportSendStatus->newReportAdd();

                                }
                            }
                            unset($this->messageData['not_sent_reports'][$key2]);
                        }
                    }
                }
            if (is_array($this->messageData['not_sent_reports']))
                $sendData['not_searched_reports'] = array_values($this->messageData['not_sent_reports']);
            else
                $sendData['not_searched_reports'] = array();
        }

        $this->createClientMessage('04', 0, $sendData);
    }

    protected function serverStep04()
    {
        $sendData = $this->msStatus();
        if ($this->messageData['ms_status_processed']==1 && $this->messageData['ms_status']=="master") {
            $scheduleTypeReportProcessed = new ScheduleTypeReportProcessed();
            $uids = array();
            foreach ($this->messageData['not_searched_reports'] as $uidItem) {
                $uids[] = $uidItem['report_uid'];
            }
            // master has not uid like this;
            $unSynchronizedReports = $scheduleTypeReportProcessed->findByUIDs($uids, 0);

            if (!is_null($unSynchronizedReports))
                foreach ($unSynchronizedReports['result'] as $reportItem) {
                    if ($reportItem->aging_time < time()) {
                        $reportItem->is_synchronized = 2; // could not synchronize
                        $reportItem->save();
                    }
                }
        }
        $this->createServerMessage('01',1,'SERVER ANSWER 04 (END)'.time());
    }































    protected  function  sendReport($sendLogObject) {

        if (is_object($sendLogObject->report_processed->ex_schedule_report)) {

            $file_name = $sendLogObject->report_processed->getFileName($sendLogObject->report_processed->ex_schedule_report);
            $file_path = $sendLogObject->report_processed->full_file_name;

            if ($sendLogObject->destination->method === 'mail') {
                $this->_logger->log(__METHOD__, array(
                        // 'report_type' => $reportObject->ex_schedule_report->station_type,
                        'method' => $sendLogObject->destination->method,
                        'email' => $sendLogObject->destination->destination_email
                    )
                );

                $mail_params = array(
                    // 'station_id_code' => $reportObject->ex_schedule_report->station_type,
                    'actuality_time' => $sendLogObject->created,
                    'schedule_period' => '',
                    'report_file_name' => $file_name,
                    'link' => '', //Yii::app()->params['site_url_for_console'] . '/site/schedulehistory/schedule_id/' . $this->schedule_type_report->ex_schedule_id,
                    'report_type' => $sendLogObject->report_processed->ex_schedule_report->report_type,
                );

                $subject = Yii::t('letter', 'scheduled_report_mail_subject', $mail_params, null, 'en');

                $settings = Settings::model()->findByPk(1);
                $mailSender = new mailSender('odss_reports', array());
                $sendResult = $mailSender->setAttachments(array(array('file_path' => $file_path, 'file_name' => $file_name)))
                    ->setRecipient($sendLogObject->destination->destination_email)
                    ->setFrom($settings->mail__sender_address, $settings->mail__sender_name)
                    ->setSubject($subject)
                    ->setHtmlBody()
                    ->send();
                if ($sendResult!==false) {
                    $sendLogObject->sent = 1;
                    $sendLogObject->save();
                }
                $this->_logger->log(__METHOD__ . ' $sendResult: ' . $sendResult);
                $this->_logger->log(__METHOD__ . ' Message send with attached file');
                $this->_logger->log(__METHOD__ . ' Deliver via mail DONE.');

            } else if ($sendLogObject->destination->method === 'ftp') {
                // use it if you have some superstition about "../"
//                    $fileCopier = new FileCopier;
//                    $file_path = $fileCopier->rmPathSteps($file_path);

                $ftpClient = new FtpClient();
                $errors = $ftpClient->connect(
                    $sendLogObject->destination->destination_ip,
                    $sendLogObject->destination->destination_ip_port
                )
                    ->login(
                        $sendLogObject->destination->destination_ip_user,
                        $sendLogObject->destination->destination_ip_password
                    )
                    ->setFolder($sendLogObject->destination->destination_ip_folder)
                    ->openLocalFile($file_path)
                    ->upload($file_name)
                    ->closeLocalFile()
                    ->getErrors();

                if (!count($errors)) {
                    $sendLogObject->sent = 1;
                    $sendLogObject->save();
                } else {
                    $sendLogObject->send_logs = serialize($errors);
                    $sendLogObject->save();
                }
                $this->_logger->log(__METHOD__ . " ftp errors:" . print_r($errors, 1));
                $this->_logger->log(__METHOD__ . ' Deliver via ftp DONE.');
            } else if ($sendLogObject->destination->method === 'local_folder') {
                $this->_logger->log(__METHOD__, array(
                        'report_type' => $sendLogObject->report_processed->ex_schedule_report->report_type,
                        'method' => $sendLogObject->destination->method,
                        'destination_folder' => $sendLogObject->destination->destination_local_folder,
                    )
                );

                $destinationPath = $sendLogObject->report_processed->getFileDir() .
                    DIRECTORY_SEPARATOR . $sendLogObject->destination->destination_local_folder;
                $this->_logger->log(__METHOD__ . ' $file_path: ' . $file_path);
                $this->_logger->log(__METHOD__ . ' $destinationPath: ' . $destinationPath);
                $this->_logger->log(__METHOD__ . ' $file_name: ' . $file_name);

                $fileCopier = new FileCopier();
                $errors = $fileCopier->copy($file_path, $destinationPath . DIRECTORY_SEPARATOR . $file_name)->getErrors();

                if (!count($errors)) {
                    $sendLogObject->sent = 1;
                    $sendLogObject->save();
                } else {
                    $sendLogObject->send_logs = serialize($errors);
                    $sendLogObject->save();
                }

                $this->_logger->log(__METHOD__ . ' errors:' . print_r($errors, 1));
                $this->_logger->log(__METHOD__ . ' Deliver to local folder DONE.');
            }
        }

    }


    protected function msStatus()
    {

        $sendData = array();
        if ($this->synchronization->isMaster()) {
            $sendData['ms_status'] = 'master';
        }
        if ($this->synchronization->isProcessed()) {
            $sendData['ms_status_processed'] = 1;
        } else {
            $sendData['ms_status_processed'] = 0;
        }
        if ($this->synchronization->isSlave()) {
            $sendData['ms_status'] = 'slave';
        }
        return $sendData;
    }
} 