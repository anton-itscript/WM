<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class AjaxController extends CController
{
    public function init()
	{
        if (!Yii::app()->request->isAjaxRequest)
		{
            $this->redirect($this->createUrl('site/index'));
        }        
    }

    public function actionLoadSensors()
	{
		$res = StationSensor::getList(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);

        print json_encode($res);

        CApplication::end();        
    }

    public function actionImportMessage()
	{

        if (It::isGuest())
		{
            print json_encode(array('errors' => array('Sign in first.'), 'ok' => 0));
            
			CApplication::end();  
        }
		
        $message = isset($_POST['message']) ? trim($_POST['message']) : null;
        
		if ($message)
		{
            $st = time();
            $settings = Settings::model()->find();

            ListenerLogTemp::addNew($message, 0, $settings->overwrite_data_on_import, 'import', 0);
//			ListenerLog::addNew($message, 0, $settings->overwrite_data_on_import, 'import', 0);

			print json_encode(array('ok' => 1));
        } 
		else
		{
			print json_encode(array('ok' => 0));
        }

        CApplication::end();          
    }

//    public function actionRememberChosenWindDial()
//    {
//        $session = new CHttpSession();
//        $session->open();
//
//        $session['single_aws'] = array(
//            'station_id' => intval($_REQUEST['station_id']),
//            'chosen_wind_direction' => intval($_REQUEST['index'])
//        );
//
//        print '';
//
//        CApplication::end();
//    }


    public function actionScheduleResendReport()
	{

		$return = array('ok' => 0);
        if (is_array($_REQUEST['schedule_processed_id'])) {
            new WeatherReportMailSender($_REQUEST['schedule_processed_id']);
            $return['ok'] = 1;
        } else {

            $schedule_processed_id = isset($_REQUEST['schedule_processed_id']) ? intval($_REQUEST['schedule_processed_id']) : null;

            if ($schedule_processed_id) {
                $processedReport = ScheduleReportProcessed::model()->with('ScheduleReportToStation.schedule_report')->findByPk($schedule_processed_id);

                if (!is_null($processedReport)) {
                    $reportType = null;

                    switch ($processedReport->ScheduleReportToStation->schedule_report->report_type) {
                        case 'bufr':
                            $reportType = 'Bufr';
                            break;

                        case 'synop':
                            $reportType = 'Synop';
                            break;

                        case 'metar':
                            $reportType = 'Metar';
                            break;

                        case 'speci':
                            $reportType = 'Speci';
                            break;

                        default:
                            $reportType = 'Export';
                            break;
                    }

                    if (!is_null($reportType)) {
                        $weather_report = WeatherReport::create($reportType);
                        $weather_report->load($schedule_processed_id);

                        if (!$weather_report->errors) {
                            $weather_report->deliverReport();
                            $return['ok'] = 1;
                        } else {
                            $return['errors'] = $weather_report->errors;
                        }
                    } else {
                        $return['errors'][] = 'Unknown report type "' . $processedReport->report->report_type . '".';
                    }
                } else {
                    $return['errors'][] = 'Processed report with id "' . $schedule_processed_id . '" is not found.';
                }
            } else {
                $return['errors'][] = 'schedule_processed_id is not specified.';
            }

            print json_encode($return);

        }
        CApplication::end();
    }

    public function actionScheduleResaveReport()
	{
        $return = array('ok' => 0);
        $schedule_processed_id = isset($_REQUEST['schedule_processed_id']) ? intval($_REQUEST['schedule_processed_id']) : null;

		if ($schedule_processed_id && isset($_REQUEST['ScheduleReportProcessed']['report_string_initial'])) {
			$report = ScheduleReportProcessed::model()->long()->findByPk($schedule_processed_id);

			if ($report) {
                $file_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."schedule_reports".DIRECTORY_SEPARATOR.$schedule_processed_id;
                file_put_contents($file_path, $_REQUEST['ScheduleReportProcessed']['report_string_initial']);

                if (!$report->getErrors()) {
					$return['ok'] = 1;
                } else {
                    $return['errors'] = $report->getErrors();
                }
            } else {
				$return['errors'][] = 'Unknown report process';
            }
        }

		print json_encode($return);
		CApplication::end();        
    }

    public function actionScheduleRegenerateReport()
	{
		// All reports are generated basing on data in UTC time.
        TimezoneWork::set('UTC');
		
		$return = array('ok' => 0);
        $schedule_processed_id = isset($_REQUEST['schedule_processed_id']) ? intval($_REQUEST['schedule_processed_id']) : null;
        
		if ($schedule_processed_id) {
            $data = ScheduleReportProcessed::getInfoForRegenerate($schedule_processed_id);

            if (!is_null($data)) {
				$reportType = null;

				switch ($data->ScheduleReportToStation->schedule_report->report_type) {
					case 'bufr':
						$reportType = 'Bufr';
						break;

					case 'synop':
						$reportType = 'Synop';
						break;

					case 'metar':
						$reportType = 'Metar';
						break;

					case 'speci':
						$reportType = 'Speci';
						break;

					default:
						$reportType = 'Export';
						break;
				}

				$weather_report = WeatherReport::create($reportType);
                $weather_report->load($schedule_processed_id);

                if (!$weather_report->errors)
				{

                    $weather_report->generate();
                    $weather_report->prepareReportComplete();
                    $weather_report->saveProcess();

					$return['ok'] = 1;
                    $return['report_string_initial'] = $weather_report->getReportComplete();
                }
				else
				{
                    $return['errors'] = $weather_report->errors;
                }
            }
        }
		
        print json_encode($return);
        
		CApplication::end();
    }    

    public function actionDeleteScheduleDestination()
	{
        $schedule_id = isset($_REQUEST['sid']) ? intval($_REQUEST['sid']) : null;
        $destination_id = isset($_REQUEST['did']) ? intval($_REQUEST['did']) : null;
        
        $return = array('ok' => 0);
        
		if ($schedule_id && $destination_id)
		{
            $res = ScheduleReportDestination::model()->deleteAllByAttributes(array('schedule_id' => $schedule_id, 'schedule_destination_id' => $destination_id));
            
			if ($res)
			{
                $return['ok'] = 1;
            }
        }
		
        print json_encode($return);
        
		CApplication::end();        
    }

    public function actionDeleteScheduleTypeDestination()
	{
        $ex_schedule_id = isset($_REQUEST['sid']) ? intval($_REQUEST['sid']) : null;
        $destination_id = isset($_REQUEST['did']) ? intval($_REQUEST['did']) : null;

        $return = array('ok' => 0);

		if ($ex_schedule_id && $destination_id)
		{
            $res = ScheduleTypeReportDestination::model()->deleteAllByAttributes(array('ex_schedule_id' => $ex_schedule_id, 'ex_schedule_destination_id' => $destination_id));

			if ($res)
			{
                $return['ok'] = 1;
            }
        }

        print json_encode($return);

		CApplication::end();
    }

    public function filters()
    {
        return array('accessControl');
    }

    public function accessRules(){
        if(!Yii::app()->user->isGuest){
            return array();
        } else {
            return array(
                array('allow',
                      'actions' => array('login'),
                ),
                array('deny',
                      'users' => array(),
                )
            );
        }
    }
}

?>
