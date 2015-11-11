<?php

class SiteController extends CController
{
    public function beforeAction($action)
    {
        Yii::app()->clientScript->registerPackage( $this->id . '.' . strtolower($this->getAction()->id));
        return true;
    }

    public function actionIndex(){
        $checkAccess = Yii::app()->user->access['site'];
        $defAccess = AccessGlobal::getDefaultAction();
        foreach($defAccess as $access){
            unset($checkAccess[array_search($access,$checkAccess)]);
        }

        $access = $checkAccess? array_shift($checkAccess):'Logout';

        $this->redirect($this->createUrl('site/'.$access));

    }

    public function actionRgPanel()
    {
        $stations = Station::getList('rain');
        $handlers = array();
        SensorDBHandler::handlerWithFeature($handlers,'rg');
        $features = array_shift($handlers)->features;
        if ($stations)		{
			foreach ($stations as $key => &$station){
                $station['sensor_details'] = array(
                    'last_msg'   => '-',
                    'amount'     => '-',
                    'period'     => '-',
                    'rate'       => '-',
                    '1hr_total'  => '-',
                    'batt_volt'  => '-',
                    '24hr_total' => '-'
                );

                $period = $station['event_message_period'];
                if ($period == 5) {
                    $use_field = '5min_sum';
                } else if ($period == 10) {
                    $use_field = '10min_sum';
                } else if ($period == 20) {
                    $use_field = '20min_sum';
                } else if ($period == 30) {
                    $use_field = '30min_sum';
                } else {
                    $use_field = '60min_sum';
                }
                $station['filter_limit_max'] = round(($features['rain']->filter_max/60) * $period, 2);
                $station['filter_limit_min'] = round(($features['rain']->filter_min/60) * $period, 2);
                $station['filter_limit_diff'] = round(($features['rain']->filter_diff/60) * $period, 2);

                $sql_groupped_table = "SELECT `station_id`, MAX(`measuring_timestamp`) AS `MaxDateTime`
                                       FROM `".SensorDataMinute::model()->tableName()."`
                                       WHERE `{$use_field}` > 0 AND `station_id` = '".$station['station_id']."' AND `is_tmp` = 0";

                $sql = "SELECT `tt`.*, `t2`.`html_code` AS `metric_html_code`
                        FROM `".SensorDataMinute::model()->tableName()."` `tt`
                        INNER JOIN `".RefbookMetric::model()->tableName()."` t2 ON t2.metric_id = tt.metric_id
                        INNER JOIN ( {$sql_groupped_table} ) `groupedtt` ON `tt`.`station_id` = `groupedtt`.`station_id` AND `tt`.`measuring_timestamp` = `groupedtt`.`MaxDateTime`";

                $res = Yii::app()->db->createCommand($sql)->queryRow();

                $last_logs = ListenerLog::getLast2Messages($station['station_id']);

                if ($res) {

                        $station['sensor_details'] = array(
                            'sensor_data_id' => $res['sensor_data_id'],
                            'last_msg'   => date('Y-m-d H:i', strtotime($res['measuring_timestamp'])),
                            'amount'     => $res[$use_field]*$res['bucket_size'],
                            'period'     => $period,
                            'rate'       => $res[$use_field]*(60/$period)*$res['bucket_size'],
                            '1hr_total'  => $res['60min_sum']*$res['bucket_size'],
                            'batt_volt'  => $res['battery_voltage']/10,
                            '24hr_total' => $res['1day_sum']*$res['bucket_size'],
                            'metric'     => $res['metric_html_code']
                        );

                        if (count($last_logs) > 0)
						{
                            $station['last_tx'] = date('m/d/Y H:i', strtotime($last_logs[0]['measuring_timestamp']));
                            $next_expected = strtotime($last_logs[0]['measuring_timestamp']) + $period + 300;

                            $station['sensor_details']['next_expected'] = date('m/d/Y H:i', $next_expected);
                            if ($next_expected < time()) {
                                $station['sensor_details']['next_lates'] = 1;
                            }
                        }

                        if ($station['filter_limit_max'] > 0 ) {
                            if ($station['sensor_details']['amount'] >= $station['filter_limit_max'])
                                $station['filter_errors'][] = ("R >= ".$station['filter_limit_max']);
                        }
                        if ($station['filter_limit_min'] > 0 ) {
                            if ($station['sensor_details']['amount'] <= $station['filter_limit_min'])
                                $station['filter_errors'][] = ("R <= ".$station['filter_limit_min']);
                        }
                }
            }
        }

        $template = 'index';
        $render_data = array(
            'stations'       => $stations,
        );

        if (Yii::app()->request->isAjaxRequest) {
            $this->renderPartial($template, array('render_data' => $render_data));
        } else {
            $this->render('autorefresh', array('render_data' => $render_data, 'template' => $template));
        }
    }

    public function actionAwsPanel(){
        $tableCount = isset($_GET['tableCount']) ? $_GET['tableCount'] : 2;
        $tableSize = isset($_GET['tableSize']) ? $_GET['tableSize'] : 10 ;

        $pages = new CPagination();
        $pages->pageSize = $tableCount*$tableSize;
        //group station
        $stationGroup=StationGroup::getGroupName();
        $selectStationGroupIds = StationGroup::getStationArrFromGroup($_GET['group_id']);

        //data for view
        $stations = $handlers = $handlersCalc = $sensorData = $handlerGroup = $stationGroupView = array();

        $stationsId = Station::stationFromGroup($stations,$selectStationGroupIds,$pages);
        if(count($stations)){
            $lastLogsId = ListenerLog::lastMsgIds($stationsId,$stations);
            if($lastLogsId){
                SensorHandler::getGroupAwsPanel($handlerGroup);

                $handlersId = SensorDBHandler::handlerWithFeature($handlers,'aws_panel');
                SensorData::addSensorsData($sensorData,$lastLogsId,$handlers);

                $handlersCalcId = CalculationDBHandler::handlerWithFeatureAndMetric($handlersCalc);
                StationCalculationData::addCalculationData($sensorData,$lastLogsId,$handlersCalcId);
                // sensor data
                if(isset($sensorData['handlers'])){
                    foreach($sensorData['handlers'] as $handler_id => &$handler){
                        SensorHandler::setGroupAwsPanel(
                            $handlerGroup,
                            $handlers[$handler_id]->handler_id_code,
                            $handler_id,'handlers');
                        foreach($handler['code'] as &$code){
                            SensorHandler::getDataForAwsPanel(
                                $code,
                                $handlers[$handler_id],
                                $stations
                            );
                        }
                    }
                }
                //calculationData
                if(isset($sensorData['handlersCalc'])){
                    foreach($sensorData['handlersCalc'] as $handler_id => &$handler){
                        SensorHandler::setGroupAwsPanel(
                            $handlerGroup,
                            $handlersCalc[$handler_id]->handler_id_code,
                            $handler_id,'handlersCalc');

                        foreach($handler['stations'] as $station_id => &$station){
                            CalculationHandler::getDataForAwsPanel(
                                $station,
                                $stations[$station_id]->lastMessage->log_id,
                                $handlersCalc[$handler_id]->handler_id_code);
                        }
                    }
                }
            }
            //station groups view
            $stationGroupView = array_chunk($stationsId,$tableSize);
        }
        $render_data = array(
            'stations'      => $stations,
            'handlers'      => $handlers,
            'handlersCalc'  => $handlersCalc,
            'sensorData'    => $sensorData,
            'handlerGroup'  => $handlerGroup,
            'stationGroup'  => $stationGroupView,
        );

        if (Yii::app()->request->isAjaxRequest){
			$this->renderPartial('aws_panel', array(
                'render_data' => $render_data
            ), false, true);
        } else {
            $this->render('autorefresh_aws_panel', array(
                'render_data'   => $render_data,
                'template'      => 'aws_panel',
                'pages'         => $pages,
                'stationGroup'  => $stationGroup
            ));
        }
    }

    public function actionAwsSingle(){

		$criteria = new CDbCriteria();
            $criteria->compare('station_type', array('aws', 'awos'));
            $criteria->order = 'station_id_code asc';
		$stations = Station::model()->findAll($criteria);

        if (count($stations) > 0){
            $session = Yii::app()->session;

			$id = isset($_REQUEST['station_id'])
						? intval($_REQUEST['station_id'])
						: (isset($session['single_aws']['station_id']) ? $session['single_aws']['station_id'] : '');

			$station = null;
			if ($id){
				foreach($stations as $st){
					if ($id == $st->station_id){
						$station = $st;
						break;
					}
				}
            }
            $station = is_null($station) ? $stations[0] : $station;

            $log_id = isset($_REQUEST['log_id']) ? intval($_REQUEST['log_id']) : null;
            $last2Messages = ListenerLog::getAllLast2Messages(array($station->station_id), $log_id);

			if (array_key_exists($station->station_id, $last2Messages) && (count($last2Messages[$station->station_id]) > 0)){
                $station->lastMessage = date('m/d/Y H:i', strtotime($last2Messages[$station->station_id][0]->measuring_timestamp));
                $next_expected = strtotime($last2Messages[$station->station_id][0]->measuring_timestamp) + $station->event_message_period*60 + 300;
                $station->nextMessageExpected = date('m/d/Y H:i', $next_expected);
				if ($next_expected < time()){
                    $station->nextMessageIsLates = 1;
                }
            }
			else{
                $station->lastMessage = 'Unknown';
            }

            if (count($last2Messages[$station->station_id]) > 1){
				$station->previousMessage = date('m/d/Y H:i', strtotime($last2Messages[$station->station_id][1]->measuring_timestamp));
            } else{
                $station->previousMessage = 'Unknown';
            }

            $list = StationSensor::getSensorsForAWSDisplay(array($station->station_id), 'aws_single');
            $sensors = isset($list[$station->station_id]) ? $list[$station->station_id] : null;

			$handler_sensor = array();

			if (!is_null($sensors)){
                $sensorPairs = array();

				foreach($sensors as $sensor)
				{
                    $sensorPairs[$sensor->handler->handler_id_code][] = array(
                        'station_id' => $station->station_id,
                        'sensor_id'  => $sensor->station_sensor_id,
                        'last_logs'  => isset($last2Messages[$station->station_id]) ? $last2Messages[$station->station_id] : array(),
                        'aws_single_group' => $sensor->handler->aws_single_group,
                    );
                }
                $handlersDefault = array();
                SensorDBHandler::handlerWithFeature($handlersDefault,'single');

				$sensorList = SensorHandler::getFullSensorList(array($station->station_id),$handlersDefault);
				$sensorData = SensorData::getSensorData($last2Messages, $sensorList);

                foreach ($sensorPairs as $handler_id_code => $data)
				{
					$handler_obj = SensorHandler::create($handler_id_code);
                    $res = $handler_obj->getInfoForAwsPanel($data, $sensorList, $sensorData, 'single');

                    if ($res)
					{
                        foreach ($res as $value_sensors)
						{
                            foreach ($value_sensors as $value_sensor_data)
							{
                                if ($handler_id_code === 'WindDirection')
								{
									if ($session['single_aws']['chosen_wind_direction'] == 2)
									{
										$chosen_direction = '10minute_average';
                                    }
									else if ($session['single_aws']['chosen_wind_direction'] == 1)
									{
										$chosen_direction = '2minute_average';
                                    }
									else
									{
                                        $chosen_direction = 'last';
                                    }

                                    $value_sensor_data['chosen_wind_direction'] = $chosen_direction;
                                    $radians = ($value_sensor_data[$chosen_direction]-90)/180 * M_PI;

                                    //$value_sensor_data['chosen_wind_coordinates']['x'] = round(86.5+ cos($radians)*70);
                                    //$value_sensor_data['chosen_wind_coordinates']['y'] = round(86.5+ sin($radians)*70);
                                    $value_sensor_data['chosen_wind_coordinates']['x'] = round(83+ cos($radians)*70);
                                    $value_sensor_data['chosen_wind_coordinates']['y'] = round(83+ sin($radians)*70);

                                }
                                $value_sensor_data['handler_id_code'] = $handler_id_code;
                                $handler_sensor[$data[0]['aws_single_group']][$handler_id_code][] = $value_sensor_data;
                            }
                        }
                    }
                }
            }

            $handlers = StationCalculation::getStationCalculationHandlers(array($station->station_id));

			$calculation = array();

            if (array_key_exists($station->station_id, $handlers))
			{
                foreach ($handlers[$station->station_id] as $value)
				{
					$handler = CalculationHandler::create($value->handler->handler_id_code);
                    $used_sensors = $handler->getUsedSensors($station->station_id);

                    if ($used_sensors)
					{
                        foreach ($handler_sensor as $group => &$v2)
						{
                            foreach ($v2 as $handler_id_code => &$value_sensor_data)
							{
                                if (
                                        ($value->handler->handler_id_code == 'DewPoint' && in_array($handler_id_code, array('Temperature', 'TemperatureSoil', 'TemperatureWater')))
                                        ||
                                        ($value->handler->handler_id_code == 'PressureSeaLevel' && in_array($handler_id_code, array('Pressure')))
                                    )
								{
                                    foreach ($value_sensor_data as $k4 => &$v4)
									{
                                        if (in_array($v4['sensor_id_code'], $used_sensors))
										{

                                            $calculation[$group][$value->handler->handler_id_code] = 1;
                                            $v4[$value->handler->handler_id_code] = $handler->getCalculatedValue($station->station_id, $last2Messages[$station->station_id]);
										 }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $session['single_aws'] = array(
                'station_id' => $station->station_id,
                'chosen_wind_direction' => $session['single_aws']['chosen_wind_direction']
            );
        }

		$speciReport = null;

		if (count($last2Messages[$station->station_id]) > 0)
		{
			$criteria = new CDbCriteria();

			$criteria->with = array('ScheduleReportToStation.schedule_report');

			$criteria->compare('report_type', 'speci');
			$criteria->compare('ScheduleReportToStation.station_id', $station->station_id);
//			$criteria->compare('is_last', 1);
			$criteria->compare('listener_log_id', $last2Messages[$station->station_id][0]->log_id);
//			$criteria->order = 't.updated desc';
			$criteria->order = 't.created desc';
			$criteria->limit = 1;

			$speciReport = ScheduleReportProcessed::model()->find($criteria);

			if (is_null($speciReport))
			{
				$reportRecord = ScheduleReport::model()->findByAttributes(array('report_type' => 'speci', 'station_id' => $station->station_id,));

				if (!is_null($reportRecord))
				{
					$speciReport = new ScheduleReportProcessed();

					$speciReport->schedule_id = $reportRecord->schedule_id;
					$speciReport->listener_log_id = $last2Messages[$station->station_id][0]->log_id;

					$speciReport->is_processed = 0;

					$speciReport->check_period_start = $last2Messages[$station->station_id][0]->measuring_timestamp;
					$speciReport->check_period_end   = $last2Messages[$station->station_id][0]->measuring_timestamp;

					$speciReport->save();
				}
			}
		}
//        echo "<pre>";
//        print_r($handler_sensor);
//        echo "</pre>";exit;
		$template = 'aws_single';
        $render_data = array(
            'stations'       => $stations,
            'station'        => $station,
            'last_logs'      => $last2Messages[$station->station_id],
            'handler_sensor' => $handler_sensor,
            'calculation'    => $calculation,
			'speciReport'	 => $speciReport,
        );

        if (Yii::app()->request->isAjaxRequest)
		{
			$this->renderPartial($template, array('render_data' => $render_data));
        }
		else
		{
            $this->render('autorefresh_aws_single', array('render_data' => $render_data, 'template' => $template));
        }
    }

    public function actionAwsGraph()
	{
        $form = new AWSGraphForm();
        $res = array();
        if (Yii::app()->request->isPostRequest) {
            if (isset($_POST['clear'])) {
                $form->clearMemory();
                $this->redirect($this->createUrl('site/awsgraph'));
            }

            if (isset($_POST['filter'])) {
                $form->setAttributes($_POST['AWSGraphForm']);
                if ($form->validate()) {
                    $res = $form->prepareList();

                }
            }
        }

        $this->render('aws_graph', array(
            'form' => $form,
            'res'  => $res
        ));
    }

    public function actionAwsTable()
	{
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $form      = new AWSTableForm;
        $res       = array();
        $requested = null;

        if (Yii::app()->request->isPostRequest) {
            if(isset($_POST['clear']) OR isset($_GET['clear'])) {
                $form->clearMemory();
                $this->redirect($this->createUrl('site/awstable'));
            }
            if (isset($_POST['filter'])) {
                $form->setAttributes($_POST['AWSTableForm']);
                if ($form->validate()) {
                    $requested = $form->station_id[0];
                    $res = $form->prepareList($requested);
                }
            }
            if (isset($_POST['export'])) {
                $form->setAttributes($_POST['AWSTableForm']);
                if ($form->validate()) {
                    $form->exportList();
                }
            }
        }

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_REQUEST['show_station'])) {
                $requested = intval($_REQUEST['show_station']);
                $res = $form->prepareList($requested);
            }
        }
        $render_data = [
            'form'            => $form,
            'res'             => $res,
            'show_station'    => $requested,
        ];

        if (Yii::app()->request->isAjaxRequest) {
			$this->renderPartial('__aws_table_part', $render_data, false, true);
        } else {
            $this->render('aws_table', $render_data);
        }
    }

    public function actionRgTable()
    {
		set_time_limit(0);

        $form = new RgTableForm();

        if ((Yii::app()->request->isPostRequest && isset($_POST['clear'])) OR isset($_GET['clear'])){
            $form->clearMemory();
            $this->redirect($this->createUrl('site/rgtable'));
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['filter']))
		{
            $form->attributes = $_POST['RgTableForm'];

			if ($form->validate())
			{
				$this->redirect($this->createUrl('site/rgtable'));
            }
        }

        if (isset($_REQUEST['of']) && in_array($_REQUEST['of'], array('name', 'date', 'lasttx', 'lasthr', 'last24hr')))
		{
            $form->setOrders($_REQUEST['of']);
            $this->redirect($this->createUrl('site/rgtable'));
        }

        $stid = intval($_GET['station_id']);

		if ($stid)
		{
            $form->setStationId($stid);
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['export']))
		{
            $form->exportList();
        }

        $rain_metric = $form->getRainMetric();

        $stations = $form->getAllStations();

        $res = $form->prepareList();

        $template = 'rgtable';
        $render_data = array(
            'stations'        => $stations,
            'listing'         => $res['list'],
            'pages'           => $res['pages'],
            'rain_metric'     => $rain_metric,
            'form'            => $form,
            'current_station' => $form->getCurrentStation()
        );

        if (Yii::app()->request->isAjaxRequest)
		{
			$this->renderPartial($template, array('render_data' => $render_data));
        }
		else
		{
			$this->render('autorefresh_rgtable', array('render_data' => $render_data, 'template' => $template));
        }
    }

    public function actionRgGraph()
    {
        $form = new RgGraphForm();

        if ((Yii::app()->request->isPostRequest && isset($_POST['clear'])) OR isset($_GET['clear'])){
            $form->clearMemory();
            $this->redirect($this->createUrl('site/rggraph'));
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['filter']))
		{
            $form->attributes = $_POST['RgGraphForm'];

			if ($form->validate())
			{
                $this->redirect($this->createUrl('site/rggraph'));
            }
        }

        $res = $form->prepareList(60);

        $this->render('rggraph', array(
            'form'         => $form,
            'series_data'  => $res['series_data'],
            'series_names' => $res['series_names'],
            'total_ticks'  => $res['total_ticks'],
            'min_tick'     => $res['min_tick'],
            'max_tick'     => $res['max_tick']
        ));
    }

    public function actionMsgHistory()
    {
        $form = new MessagesLogFilterForm();

        $ids_to_delete = array();

		if (Yii::app()->request->isPostRequest && isset($_POST['delete_checked'])){
            if (isset($_POST['log_id'])){
                $ids_to_delete = $_POST['log_id'];
            }
        }
		if (isset($_REQUEST['delete'])){
            $id = intval($_REQUEST['delete']);
			if ($id){
                $ids_to_delete[] = $id;
            }
        }

        if ($ids_to_delete){
            if (!Yii::app()->user->isSuperAdmin()){
                It::memStatus('only_admin_can_delete_message_history_log_messages');
            }else{
                $cnt = 0;
				foreach ($ids_to_delete as $id){
                    $sensorData = SensorData::model()->findall('listener_log_id=:log_id', array(':log_id' => $id));
                    if ($sensorData) {
                        foreach ($sensorData as $item) {
                            $item->delete();
                        }
                    }
                    $seaLevelTrend = SeaLevelTrend::model()->findall('log_id=:log_id', array(':log_id' => $id));
                    if ($seaLevelTrend) {
                        foreach ($seaLevelTrend as $item) {
                            $item->delete();
                        }
                    }
                    $calcData = StationCalculationData::model()->findall('listener_log_id=:log_id', array(':log_id' => $id));
                    if ($calcData) {
                        foreach ($calcData as $item) {
                            $item->delete();
                        }
                    }

                    $log = ListenerLog::model()->find('log_id=:log_id', array(':log_id' => $id));
                    if ($log) {
                        $log->delete();
                        $cnt++;
                    }

                    $sensorData = SensorData::model()->selectDb(true)->findall('listener_log_id=:log_id', array(':log_id' => $id));

                    if ($sensorData) {
                        foreach ($sensorData as $item) {
                            $item->selectDb(true)->delete();
                        }
                    }
                    $seaLevelTrend = SeaLevelTrend::model()->selectDb(true)->findall('log_id=:log_id', array(':log_id' => $id));
                    if ($seaLevelTrend) {
                        foreach ($seaLevelTrend as $item) {
                            $item->selectDb(true)->delete();
                        }
                    }
                    $calcData = StationCalculationData::model()->selectDb(true)->findall('listener_log_id=:log_id', array(':log_id' => $id));
                    if ($calcData) {
                        foreach ($calcData as $item) {
                            $item->selectDb(true)->delete();
                        }
                    }
                    $log = new ListenerLog;
                    $log = $log->selectDb(true)->find('log_id=:log_id', array(':log_id' => $id));

                    if ($log) {
                        $log->selectDb(true)->delete();
                        $cnt++;
                    }

                }
                It::memStatus($cnt ? 'message_history_log_was_deleted' : 'message_history_log_not_found');
            }

            $this->redirect($this->createUrl('site/msghistory'));
        }

        if ((Yii::app()->request->isPostRequest && isset($_POST['clear'])) OR isset($_GET['clear'])){
            $form->clearMemory();
            $this->redirect($this->createUrl('site/msghistory'));
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['filter'])){
            $form->attributes = $_POST['MessagesLogFilterForm'];
			if ($form->validate()){
                $this->redirect($this->createUrl('site/msghistory'));
            }
        }

        if (isset($_REQUEST['of'])){
            $form->setOrders($_REQUEST['of']);
            $this->redirect($this->createUrl('site/msghistory'));
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['export'])){
            $form->attributes = $_POST['MessagesLogFilterForm'];
			if ($form->validate()){
                 $res = $form->makeExport();
            }
        }

        $res = $form->prepareList();
        $this->render('messages_history', array(
            'list'  => $res['list'],
            'pages' => $res['pages'],
            'form'  => $form
        ));
    }

    public function actionExport()
	{
		set_time_limit(0);
		ini_set('memory_limit', '-1');

        $form = new ExportForm();

        if (Yii::app()->request->isPostRequest)
		{
            $form->attributes = $_POST['ExportForm'];
            $form->station_id = $_POST['ExportForm']['station_id'];

            if ($form->validate())
			{
                $form->createExport();
            }
        }

        $this->render('export', array(
            'form' => $form
        ));
    }

    public function actionSchedule()
	{
        /** @var ScheduleReport $form */
        /** @var ScheduleReportDestination[] $forms_d] */


        $forms_s[] =  new ScheduleReportToStation;

		if (isset($_REQUEST['delete_id'])) {
            $form = ScheduleReport::model()->findByPk(intval($_REQUEST['delete_id']));
			if ($form && $form->delete()) {
                It::memStatus('schedule_deleted');
                $this->redirect($this->createUrl('site/schedule'));
            }
        }

		if (isset($_REQUEST['resend_schedule_id'])) {
            $form = ScheduleReport::model()->findByPk(intval($_REQUEST['resend_schedule_id']));
        }

        if (isset($_REQUEST['schedule_id'])) {
			$form = ScheduleReport::model()->findByPk(intval($_REQUEST['schedule_id']));
        }

        if (isset($form)) {
			$forms_d = ScheduleReportDestination::model()->findAllByAttributes(array('schedule_id' => $form->schedule_id));
			$forms_s = ScheduleReportToStation::model()->findAllByAttributes(array('schedule_id' => $form->schedule_id));
        } else {
            $form = new ScheduleReport();
            $forms_d = array();

        }

        $valid = true;

		if (Yii::app()->request->isPostRequest && isset($_POST['ScheduleReport'])) {

            $form->attributes = $_POST['ScheduleReport'];
            $valid = $valid & $form->validate();


            if (isset($_POST['ScheduleReportDestination'])) {
                foreach ($_POST['ScheduleReportDestination'] as $key => $value) {
                    if ($value['schedule_destination_id']) {
						$forms_d[$key] = ScheduleReportDestination::model()->findByPk($value['schedule_destination_id']);
                    } else {
						$forms_d[$key] = new ScheduleReportDestination();
                    }

                    $forms_d[$key]->attributes = $value;
                    $valid = $valid & $forms_d[$key]->validate();
                }
            }

            if (isset($_POST['ScheduleReportToStation'])) {
                $forms_s = array();
                foreach ($_POST['ScheduleReportToStation'] as $key => $value) {
                    if ($value['id']) {
                        $forms_s[$key] = ScheduleReportToStation::model()->findByPk($value['id']);
                    } elseif ($value['remove_id']) {
                        $stationToRemove = ScheduleReportToStation::model()->findByPk($value['remove_id']);
                        $stationToRemove->delete();
                        unset($forms_s[$key]);
                        continue;
                    } else {
						$forms_s[$key] = new ScheduleReportToStation();
                    }

                    $forms_s[$key]->attributes = $value;
//                    $valid = $valid & $forms_s[$key]->validate();
                }
            }

            if ($valid) {
                $form->save(false);

				foreach ($forms_d as $key => $value) {
					$forms_d[$key]->schedule_id = $form->schedule_id;
					$forms_d[$key]->save();
                }

				foreach ($forms_s as $key => $value) {
					$forms_s[$key]->schedule_id = $form->schedule_id;
					$forms_s[$key]->save();
                }
//
                It::memStatus($form->isNewRecord ? 'schedule_added' : 'schedule_updated');
//                if ($form->isNewRecord)
                    $this->redirect($this->createUrl('site/schedule'));

            }

        }

        $schedule_list = ScheduleReport::getScheduleList();

        $this->render('schedule_report', array(
            'form'     => $form,
            'forms_d'  => $forms_d,
            'forms_s'  => $forms_s,
            'reportsList'     => $schedule_list,
        ));


    }

    public function actionScheduleHistory()
	{
        $schedule_id = isset($_REQUEST['schedule_id']) ? intval($_REQUEST['schedule_id']) : null;

        if (!$schedule_id)
		{
			$this->redirect($this->createUrl('site/schedule'));
        }

        // this would a very cool, but need some pagination
//        $schedule = ScheduleReport::model()->with('station.realStation','station.processed.listenerLog','destinations')->findByPk($schedule_id);
        $schedule = ScheduleReport::model()->with('station.realStation','destinations')->findByPk($schedule_id);

        if (!$schedule)
		{
			$this->redirect($this->createUrl('site/schedule'));
        }


        $scheduleFormated = array();

        $scheduleFormated['report'] = $schedule->attributes;
        foreach ( $schedule->destinations as $destinationItem) {
            $scheduleFormated['report']['destinations'][] = $destinationItem->attributes;
        }

        foreach ($schedule->station as $key => $station) {
            $scheduleFormated['report']['stations'][$key] = $station->attributes;
            $scheduleFormated['report']['realStation'][$key] = $station->realStation->attributes;

        }


        $this->render('schedule_report_stations',
            array(
                  'schedule' => $scheduleFormated,
            )
        );
    }

    public function actionScheduleStationHistory()
    {
        $station_to_report_id = isset($_REQUEST['station_to_report_id']) ? intval($_REQUEST['station_to_report_id']) : null;

        if (!$station_to_report_id )
        {
            $this->redirect($this->createUrl('site/schedule'));
        }


        $criteria=new CDbCriteria();
        $criteria->condition = 'sr_to_s_id = :sr_to_s_id';
        $criteria->params = array(':sr_to_s_id'=>$station_to_report_id);
        $criteria->order  = 'schedule_processed_id DESC';
        $count=ScheduleReportProcessed::model()->count($criteria);
        $pages=new CPagination($count);
        $pages->pageSize=15;
        $pages->applyLimit($criteria);
        $scheduleProcessed = ScheduleReportProcessed::model()->with('listenerLog','ScheduleReportToStation.schedule_report','ScheduleReportToStation.realStation')->findAll($criteria);

        $files_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."schedule_reports";

        $scheduleProcessedFormatted = array();
        foreach ($scheduleProcessed as  $key => $value) {

            $scheduleProcessedFormatted[$key] =  $value->attributes;

            $scheduleProcessedFormatted[$key]['report_type'] = $value->ScheduleReportToStation['schedule_report']['report_type'];
            $scheduleProcessedFormatted[$key]['report_format'] = $value->ScheduleReportToStation['schedule_report']['report_format'];
            $scheduleProcessedFormatted[$key]['station_id'] = $value->ScheduleReportToStation->station_id;
            $scheduleProcessedFormatted[$key]['measuring_timestamp'] = $value->listenerLog['measuring_timestamp'];
            $scheduleProcessedFormatted[$key]['message'] = $value->listenerLog->message;


            if ($scheduleProcessedFormatted[$key]['serialized_report_errors'])
            {
                $scheduleProcessedFormatted[$key]['report_errors'] = unserialize($scheduleProcessedFormatted[$key]['serialized_report_errors']);
            }

            if ($scheduleProcessedFormatted[$key]['serialized_report_explanations'])
            {
                $scheduleProcessedFormatted[$key]['report_explanations'] = unserialize($scheduleProcessedFormatted[$key]['serialized_report_explanations']);
            }

            if (in_array($value->ScheduleReportToStation['schedule_report']['report_type'], array('synop', 'metar', 'speci')) && file_exists($files_path.DIRECTORY_SEPARATOR.$scheduleProcessedFormatted[$key]['schedule_processed_id']))
            {
                $scheduleProcessedFormatted[$key]['report_string_initial'] = file_get_contents($files_path.DIRECTORY_SEPARATOR.$scheduleProcessedFormatted[$key]['schedule_processed_id']);
            }

            if ($scheduleProcessedFormatted[$key]['listener_log_ids'])
            {
                $sql = "SELECT t2.log_id, t2.message, t2.measuring_timestamp
                                FROM  `". ListenerLog::model()->tableName() ."` t2
                                WHERE t2.log_id IN (". $scheduleProcessedFormatted[$key]['listener_log_ids'] .")
                                ORDER BY `t2`.`measuring_timestamp` DESC";

                $scheduleProcessedFormatted[$key]['logs'] = Yii::app()->db->createCommand($sql)->queryAll();
            }

        }


        $reportInfo = $scheduleProcessed[0]->ScheduleReportToStation['schedule_report']->attributes;
        $stationInfo = $scheduleProcessed[0]->ScheduleReportToStation->realStation->attributes;

        $this->render('schedule_report_history', array(
            'scheduleProcessed' => $scheduleProcessedFormatted,
            'stationInfo' => $stationInfo,
            'reportInfo' => $reportInfo,
            'pages' => $pages
        ));
    }

    public function actionStationTypeDataExport()
    {
        /** @var ScheduleReport $form */
        /** @var ScheduleReportDestination[] $forms_d] */
        if (Yii::app()->request->isPostRequest) {

        }
        TimezoneWork::set('UTC');
        if (isset($_REQUEST['ex_schedule_id']) and (int)$_REQUEST['ex_schedule_id'] and isset($_REQUEST['active'])) {
            $scheduleTypeReportForm = ScheduleTypeReport::model()->findByPk(intval($_REQUEST['ex_schedule_id']));
            $scheduleTypeReportForm->setAttribute('active', $_REQUEST['active']);
            $scheduleTypeReportForm->save();
        }

        $scheduleTypeReportForm =  new ScheduleTypeReport;

        if (isset($_REQUEST['ex_delete_id'])) {
            $scheduleTypeReportForm = ScheduleTypeReport::model()->findByPk(intval($_REQUEST['ex_delete_id']));
            if ($scheduleTypeReportForm && $scheduleTypeReportForm->delete()) {
                It::memStatus('schedule_deleted');
                $this->redirect($this->createUrl('site/StationTypeDataExport'));
            }
        }

//		if (isset($_REQUEST['resend_schedule_id'])) {
//            $form = ScheduleTypeReport::model()->findByPk(intval($_REQUEST['resend_schedule_id']));
//        }


        if (isset($_REQUEST['ex_schedule_id']) and (int)$_REQUEST['ex_schedule_id'] and !isset($_REQUEST['active'])) {
            $scheduleTypeReportForm = ScheduleTypeReport::model()->findByPk(intval($_REQUEST['ex_schedule_id']));

        }


        if (isset($scheduleTypeReportForm)) {
            $scheduleTypeReportDestination = ScheduleTypeReportDestination::model()->findAllByAttributes(array('ex_schedule_id' => $scheduleTypeReportForm->ex_schedule_id));

        } else {
            $scheduleTypeReportDestination = array();

        }


        $valid = true;

        if (Yii::app()->request->isPostRequest && isset($_POST['ScheduleTypeReport'])) {

            $scheduleTypeReportForm->attributes = $_POST['ScheduleTypeReport'];
            $valid = $valid & $scheduleTypeReportForm->validate();


            if (isset($_POST['ScheduleTypeReportDestination'])) {
                foreach ($_POST['ScheduleTypeReportDestination'] as $key => $value) {
                    if ($value['ex_schedule_destination_id']) {
                        $scheduleTypeReportDestination[$key] = ScheduleTypeReportDestination::model()->findByPk($value['ex_schedule_destination_id']);
                    } else {
                        $scheduleTypeReportDestination[$key] = new ScheduleTypeReportDestination();
                    }

                    $scheduleTypeReportDestination[$key]->attributes = $value;
                    $valid = $valid & $scheduleTypeReportDestination[$key]->validate();
                }
            }

            if ($valid) {
                // $scheduleTypeReportForm->scenario='admin';
                $scheduleTypeReportForm->save(false);

                foreach ($scheduleTypeReportDestination as $key => $value) {
                    $scheduleTypeReportDestination[$key]->ex_schedule_id = $scheduleTypeReportForm->ex_schedule_id;
                    $scheduleTypeReportDestination[$key]->save();
                }
//                foreach ($forms_s as $key => $value) {
//                    $forms_s[$key]->schedule_id = $form->schedule_id;
//                    $forms_s[$key]->save();
//                }
                It::memStatus($scheduleTypeReportForm->isNewRecord ? 'schedule_added' : 'schedule_updated');
                $this->redirect($this->createUrl('site/StationTypeDataExport'));
            }
        }

        $str = new ScheduleTypeReport;
        $scheduleTypeReportProcessed = new ScheduleTypeReportProcessed;
        $scheduleTypesReports = $str->getList(10);

        $this->render('schedule_type_report', array(
            'forms_d'                           => $scheduleTypeReportDestination,
            'scheduleTypeReportForm'            => $scheduleTypeReportForm,
            'scheduleTypesReports'              => $scheduleTypesReports,
            'scheduleTypeReportProcessed'       => $scheduleTypeReportProcessed,
        ));
    }

    public function actionStationTypeDataHistory()
    {
        $ex_schedule_id = isset($_REQUEST['ex_schedule_id']) ? intval($_REQUEST['ex_schedule_id']) : null;

        if (!(int)$ex_schedule_id)
        {
            $this->redirect($this->createUrl('site/StationTypeDataExport'));
        }
        TimezoneWork::set('UTC');

        $history =  ScheduleTypeReportProcessed::getHistory($ex_schedule_id);
        $scheduleTypeReport = new ScheduleTypeReport;
        $report = $scheduleTypeReport->findByPk($ex_schedule_id);

        $this->render('schedule_type_report_history', array(
            'report' => $report,
            'history' => $history,
        ));
    }

    public function actionScheduleDownload()
	{
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
        $process = ScheduleReportProcessed::model()->with('ScheduleReportToStation.realStation')->with('ScheduleReportToStation.schedule_report')->findByPk($id);

        if (!$process) {
			$this->redirect($_SERVER['HTTP_REFERER']);
        }

        $station = $process->ScheduleReportToStation->realStation;
        $schedule = $process->ScheduleReportToStation->schedule_report;
        $file_name = $station->station_id_code.'_'.strtoupper($schedule->report_type).'_'.gmdate('Y-m-d_Hi', strtotime($process->check_period_end ? $process->check_period_end : $process->created)).'.'.$schedule->report_format;
        $file_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."schedule_reports".DIRECTORY_SEPARATOR.$id;
		$report_string = file_exists($file_path) ? file_get_contents($file_path) : $report_string = '';

        It::downloadFile($report_string, $file_name);
    }

    public function actionScheduleTypeDownload()
	{
        $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
        if ((int)$id) {
            $report= new ScheduleTypeReportProcessed;
            $reportResult = $report->with('ex_schedule_report')->findByPk($id);
            $reportResult->file_content;
            $extension = $reportResult->ex_schedule_report->report_format;
            $file_name = $reportResult->check_period_start . ' - ' . $reportResult->check_period_end;
            $report_string = !empty($reportResult->file_content)? $reportResult->file_content : '';
            $file_name .= '.'.$extension;
            It::downloadFile($report_string, $file_name);
        } else {
            $this->redirect($_SERVER['HTTP_REFERER']);
        }

    }

    public function actionLogin() {
        $form = new Login();
        if (Yii::app()->request->isPostRequest && isset($_POST['Login']))
		{
            $form->attributes = $_POST['Login'];

			if ($form->validate())
			{
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }

        $this->render('login', array(
            'form' => $form,
        ));
    }

    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->user->returnUrl);
    }

    public function actionError()
    {

        if($error=Yii::app()->errorHandler->error)
        {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', array('error'=>$error));
        }
    }

    public function filters()
    {
        return array('accessControl');
    }

    public function accessRules(){

        if(!Yii::app()->user->isGuest){
            return array(
                array('allow',
                    'actions' => array_merge(Yii::app()->user->access['site'],['error','logout','index','login']),
                ),
                array('deny',
                    'actions' => array(),
                )
            );
        } else {
            return array(
                array('allow',
                    'actions' => array('login','error'),
                ),
                array('deny',
                    'users' => array(),
                )
            );
        }
    }
}