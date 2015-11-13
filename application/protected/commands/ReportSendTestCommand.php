<?php

/*
 * Is called using command: "php console.php schedule", doesn't requires arguements
 * Is called every minute using schtasks
 * 
 * This console script is looking for scheduled reports that 
 * have to be generated at the moment.
 */

class ReportSendTestCommand extends CConsoleCommand
{
	/**
	 * Logger.
	 * 
	 * @access protected
	 * @var ILogger
	 */
	protected  $logger = null;

	public function init()
	{
		parent::init();

		ini_set('memory_limit', '-1');
		set_time_limit(0);

		// All reports are generated basing on data in UTC time.
        TimezoneWork::set('UTC');
	}
	
    public function run($args)
    {
//        $this->logger = LoggerFactory::getFileLogger('ReportSendTest');
        $this->logger = LoggerFactory::getConsoleLogger();

//        $processedReportsModel = new ScheduleTypeReportProcessed();
//        $reports = $processedReportsModel->getUnsentReports();
//
//
//        $reports = $processedReportsModel->findByUIDsWithDestinations($reports['uids']);
//
//
//        $this->logger->log(__METHOD__.' reports:'.print_r($reports['result'],1));
//
//
//        foreach ($reports['result'] as $reportItem) {
//            $this->sendReport($reportItem);
//        }

        $scheduleTypeReportSendLog = new ScheduleTypeReportSendLog();
        $reports = $scheduleTypeReportSendLog->getUnsentReportItems();

        $result = $scheduleTypeReportSendLog->findByUIDsWithDestinations($reports['uids']);

        foreach ($result['result'] as $reportItem) {
            $this->sendReport($reportItem);
        }
        $this->logger->log(__METHOD__.' reports:'.print_r($reports['uids'],1));
        $this->logger->log(__METHOD__.' reports:'.print_r($result,1));
    }




    protected  function  sendReport($sendLogObject) {

        if (is_object($sendLogObject->report_processed->ex_schedule_report)) {

            $file_name = $sendLogObject->report_processed->getFileName($sendLogObject->report_processed->ex_schedule_report);
            $file_path = $sendLogObject->report_processed->full_file_name;

                    if ($sendLogObject->destination->method === 'mail') {
                        $this->logger->log(__METHOD__, array(
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
                        $this->logger->log(__METHOD__ . ' $sendResult: ' . $sendResult);
                        $this->logger->log(__METHOD__ . ' Message send with attached file');
                        $this->logger->log(__METHOD__ . ' Deliver via mail DONE.');

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
                        $this->logger->log(__METHOD__ . " ftp errors:" . print_r($errors, 1));
                        $this->logger->log(__METHOD__ . ' Deliver via ftp DONE.');
                    } else if ($sendLogObject->destination->method === 'local_folder') {
                        $this->logger->log(__METHOD__, array(
                                'report_type' => $sendLogObject->report_processed->ex_schedule_report->report_type,
                                'method' => $sendLogObject->destination->method,
                                'destination_folder' => $sendLogObject->destination->destination_local_folder,
                            )
                        );

                        $destinationPath = $sendLogObject->report_processed->getFileDir() .
                            DIRECTORY_SEPARATOR . $sendLogObject->destination->destination_local_folder;
                        $this->logger->log(__METHOD__ . ' $file_path: ' . $file_path);
                        $this->logger->log(__METHOD__ . ' $destinationPath: ' . $destinationPath);
                        $this->logger->log(__METHOD__ . ' $file_name: ' . $file_name);

                        $fileCopier = new FileCopier();
                        $errors = $fileCopier->copy($file_path, $destinationPath . DIRECTORY_SEPARATOR . $file_name)->getErrors();

                        if (!count($errors)) {
                            $sendLogObject->sent = 1;
                            $sendLogObject->save();
                        } else {
                            $sendLogObject->send_logs = serialize($errors);
                            $sendLogObject->save();
                        }

                        $this->logger->log(__METHOD__ . ' errors:' . print_r($errors, 1));
                        $this->logger->log(__METHOD__ . ' Deliver to local folder DONE.');
                    }
                }

    }
}

?>