<?php

class AdminController extends CController
{
    public function beforeAction()
    {
        Yii::app()->clientScript->coreScriptPosition = CClientScript::POS_HEAD;
        Yii::app()->clientScript->registerPackage( $this->id . '.' . strtolower($this->getAction()->id));
        return true;
    }

    public function actionIndex(){
        $this->redirect($this->createUrl('admin/stations'));
    }

    /**
     * Station
     */

    public function actionStations(){

        $importStations = new ImportStations();

        if (isset($_REQUEST['import_stations'])) {
            $count = count($_FILES['ImportStations']['name']['files']);
            for ($i=0; $i < $count; $i++) {
                $importStations->files[] = CUploadedFile::getInstance($importStations, "files[$i]");
            }

            if ($importStations->validate()) {
                $importStations->save();
            }

        }

        if (isset($_GET['get_config']) and (int)$_REQUEST['station_id'] ) {
            $station = Station::model()->with('sensors.handler','sensors.features.metric')->findbyPk($_REQUEST['station_id']);

            if (!is_null($station)) {
                $stationParams = $station->getAttributes();
                $fileName = $station->station_id.'_'.$station->station_id_code.'_'.$station->updated.'.conf';

                $fileContent['station'] = $stationParams;
                if (count($station['sensors'])) {
                    foreach ($station['sensors'] as $sensor) {
                        $sensorToAdd = array();
                        $sensorToAdd['display_name'] = $sensor->display_name;
                        $sensorToAdd['handler'] = $sensor['handler']->handler_id_code;
                        $sensorToAdd['features'] = array();
                        if (count($sensor['features'])) {
                            foreach ($sensor['features'] as $feature) {
                                $featuresToAdd = array();
                                $featuresToAdd['feature_constant_value'] = $feature->feature_constant_value;
                                $featuresToAdd['feature_display_name'] = $feature->feature_display_name;
                                $featuresToAdd['feature_code'] = $feature->feature_code;
                                $featuresToAdd['metric'] = array();
                                if ($feature['metric']) {
                                    $featuresToAdd['metric'] = $feature['metric']->getAttributes();
                                }
                                $sensorToAdd['features'][] = $featuresToAdd;
                            }
                        }

                        $fileContent['sensors'][] = $sensorToAdd;
                    }
                }

                $fileContent = json_encode($fileContent,1);
                It::downloadFile($fileContent,$fileName , 'text/conf');
            }
        }

        $stations = Station::model()->with('sensors')->findAll(array('order' => 't.station_id asc'));

        $this->render('station_list', array(
            'stations' => $stations,
            'importStations' => $importStations
        ));
    }

    public function actionStationSave()
    {
        $form = Station::model()->findByPk(isset($_REQUEST['station_id']) ? intval($_REQUEST['station_id']) : null);

        if (is_null($form))
        {
            $form = new Station();
            $form->communication_type = 'direct';
            $form->station_gravity = array_shift(array_keys(\yii::app()->params['station_gravity']));
        }

        $form->wmo_block_number    = $form->wmo_block_number > 0 ? $form->wmo_block_number : '';
        $form->wmo_member_state_id = $form->wmo_member_state_id > 0 ? $form->wmo_member_state_id : '';
        $form->national_aws_number = $form->national_aws_number > 0 ? $form->national_aws_number : '';

        if (Yii::app()->request->isPostRequest && isset($_POST['Station']))
        {
            $form->attributes = $_POST['Station'];

            if ($form->save())
            {
                It::memStatus($form->isNewRecord ? 'station_added' : 'station_updated');

                $this->redirect($this->createUrl('admin/StationSave', array('station_id' => $form->station_id)));
            }
        }

        $this->render('station_save', array(
            'form'          => $form,
            'comports_list' => SysFunc::getAvailableComPortsList(),
        ));
    }

    public function actionStationDelete()
    {
        $form = Station::model()->findByPk(isset($_REQUEST['station_id']) ? intval($_REQUEST['station_id']) : null);
        $formLong = Station::model()->long()->findByPk(isset($_REQUEST['station_id']) ? intval($_REQUEST['station_id']) : null);

        if (!is_null($form)){
            $form->delete();
            It::memStatus('admin_station_deleted');
        }
        if (!is_null($formLong))
            $formLong->long()->delete();

        $this->redirect($this->createUrl('admin/stations'));
    }

    /**
     * Station Sensor
     */

    public function actionSensors()
    {
        if (isset($_REQUEST['station_id'])) {
            $station = Station::model()
                ->with(['sensors' => ['with' => ['main_feature', 'handler']]])
                ->findByPk(intval($_REQUEST['station_id']));

            if (is_null($station)){
                $this->redirect($this->createUrl('admin/Stations'));
            }

            foreach ($station->sensors as $sensor){
                if (!is_null($sensor->main_feature)){
                    $handler_obj = SensorHandler::create($sensor->handler->handler_id_code);

                    $sensor->main_feature->filter_min  = $handler_obj->formatValue($sensor->main_feature->filter_min, $sensor->main_feature->feature_code);
                    $sensor->main_feature->filter_max  = $handler_obj->formatValue($sensor->main_feature->filter_max, $sensor->main_feature->feature_code);
                    $sensor->main_feature->filter_diff = $handler_obj->formatValue($sensor->main_feature->filter_diff, $sensor->main_feature->feature_code);
                }
            }

            $calculations = CalculationDBHandler::getHandlers();

            foreach ($calculations as $key => $calculation){
                if (!in_array($calculation->handler_id_code, array('DewPoint', 'PressureSeaLevel'))){
                    unset($calculations[$key]);
                }
            }

            $this->render('station_sensors', array(
                'station'      => $station,
                'calculations' => $calculations,
            ));

        } else {
            $this->redirect($this->createUrl('admin/Stations'));
        }
    }

    public function actionCalculationSave()
    {
        $station_id = intval($_REQUEST['station_id']);
        if (!$station_id) {
            $this->redirect($this->createUrl('admin/Stations'));
        }
        $handler_id = intval($_REQUEST['handler_id']);
        if (!$handler_id) {
            $this->redirect($this->createUrl('admin/Sensors', array('station_id' => $station_id)));
        }
        $station = Station::model()->findByPk($station_id);
        $handler_db = CalculationDBHandler::model()->findByPk($handler_id);

        if (!$station || !$handler_db) {
            $this->redirect($this->createUrl('admin/Sensors', array('station_id' => $station_id)));
        }

        $handler = CalculationHandler::create($handler_db->handler_id_code);

        $measurements = $handler->getMeasurements();
        $formulas     = $handler->getFormulas();

        $calculation_db = StationCalculation::model()->find('handler_id = :handler_id AND station_id = :station_id', array(':handler_id' => $handler_db->handler_id, ':station_id' => $station->station_id));
        if (!$calculation_db) {
            $calculation_db = new StationCalculation();
            $calculation_db->station_id   = $station->station_id;
            $calculation_db->handler_id   = $handler_db->handler_id;
            $calculation_db->formula      = $formulas ? $formulas[0] : 'default';
        }


        if ($calculation_db->calculation_id) {
            foreach ($measurements as $key => $value) {
                $measurements[$key]['object'] = StationCalculationVariable::model()->find('calculation_id = :calculation_id AND variable_name = :variable_name', array(':calculation_id' => $calculation_db->calculation_id, ':variable_name' => $value['variable_name']));
            }
        }
        foreach ($measurements as $key => $value) {
            if (!$measurements[$key]['object']->calculation_variable_id) {
                $measurements[$key]['object'] = new StationCalculationVariable();
                $measurements[$key]['object']->variable_name = $value['variable_name'];
            }
        }


        if ($measurements) {
            $sql = "SELECT t2.sensor_id_code, t1.sensor_feature_id
                    FROM `".StationSensorFeature::model()->tableName()."` t1
                    LEFT JOIN `".StationSensor::model()->tableName()."` t2 ON t2.station_sensor_id = t1.sensor_id
                    WHERE t2.station_id = ? AND  t1.feature_code = ?
                    ORDER BY t2.sensor_id_code";
            foreach ($measurements as $key => $value) {
                $measurements[$key]['sensors'] = Yii::app()->db->createCommand($sql)->queryAll(true, array($station->station_id, $value['variable_name']));
            }
        }

        $formulas_act = array();
        if ($formulas) {
            foreach($formulas as $key=>$value) {
                $formulas_act[$value] = $value;
            }
        }


        $validated = true;
        if (Yii::app()->request->isPostRequest) {
            $calculation_db->attributes = $_POST['StationCalculation'];
            foreach ($measurements as $key => $value) {
                $measurements[$key]['object']->sensor_feature_id = $_POST['StationCalculationVariable'][$key]['sensor_feature_id'];

                if ($measurements[$key]['required'] == 1 && !$measurements[$key]['object']->sensor_feature_id) {
                    $measurements[$key]['object']->addError('sensor_feature_id', $measurements[$key]['display_name'].' is required.');
                    $validated = false;
                }
            }

            $validated = $validated & $calculation_db->validate();
            if ($validated) {
                $calculation_db->save();
                foreach ($measurements as $key => $value) {
                    $measurements[$key]['object']->calculation_id = $calculation_db->calculation_id;
                    $measurements[$key]['object']->save();
                }

                It::memStatus('admin_station_calculation_saved');
                $this->redirect($this->createUrl('admin/sensors', array('station_id' => $station->station_id)));
            }
        }

        $this->render('station_calculation', array(
            'station'        => $station,
            'handler_db'     => $handler_db,
            'measurements'   => $measurements,
            'formulas'       => $formulas_act,
            'calculation_db' => $calculation_db,
        ));
    }

    public function actionCalculationDelete()
    {
        $calculation = StationCalculation::model()->findByPk(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);

        if (!is_null($calculation))
        {
            $url = $this->createUrl('admin/Sensors', array('station_id' => $calculation->station_id));

            $calculation->delete();
            It::memStatus('admin_station_calculation_deleted');

            $this->redirect($url);
        }

        $this->redirect($this->createUrl('admin/Stations'));
    }

    public function actionDeleteSensor()
    {
        $sensor = StationSensor::model()->findByPk(isset($_REQUEST['sensor_id']) ? intval($_REQUEST['sensor_id']) : null);
        $sensorLong = StationSensor::model()->long()->findByPk(isset($_REQUEST['sensor_id']) ? intval($_REQUEST['sensor_id']) : null);

        if (!is_null($sensor)){
            $url = $this->createUrl('admin/sensors', array('station_id' => $sensor->station_id));

            $sensor->delete();
            if (!is_null($sensorLong))
                $sensorLong->long()->delete();

            It::memStatus('admin_station_sensor_deleted');

            $this->redirect($url);
        }

        $this->redirect($this->createUrl('admin/stations'));
    }

    public function actionEditSensor()
    {
        $result = array();

        if (isset($_POST['sensor_id'])) {
            $form = new StationSensorEditForm();
            if ($form->loadBySensorId((int)$_POST['sensor_id'])) {
                $result['form'] = $form->draw();
            }
        }

        if (isset($_POST['StationSensorEditForm'])) {
            $form = new StationSensorEditForm();
            $form->attributes = $_POST['StationSensorEditForm'];
            if ($form->validate()) {
                $result = $form->saveSensor();
                if (empty($result)) {
                    $result['form'] = $form->draw();
                }
            } else {
                $result['form'] = $form->draw();
            }
        }

        echo json_encode($result);
        CApplication::end();
    }

    public function actionSensor(){
        $station = Station::model()->findByPk(isset($_REQUEST['station_id']) ? intval($_REQUEST['station_id']) : null);
        $handler = SensorDBHandler::model()->with('features')->findByPk($_REQUEST['handler_id']);

        if (is_null($station)){
            It::memStatus('station not found');
            $this->redirect($this->createUrl('admin/stations'));
        }
        if (is_null($handler)){
            It::memStatus('handler not found');
            $this->redirect($this->createUrl('admin/StationSave', array('station_id' => $station->station_id)));
        }

        $sensor = new StationSensor();
        $sensor->station_id = $station->station_id;
        $sensor->handler_id = $handler->handler_id;
        $sensor->display_name = $handler->handler_default_display_name;

        $sql = "SELECT UPPER(`sensor_id_code`) FROM `".StationSensor::model()->tableName()."` WHERE `station_id` = ? AND `sensor_id_code` <> ?";
        $used_code_id = Yii::app()->db->createCommand($sql)->queryColumn(array($station->station_id, $sensor->sensor_id_code ? $sensor->sensor_id_code : ''));
        for ($i=1; $i<=9; $i++){
            $code = $handler->default_prefix.$i;
            if (!$used_code_id || !in_array($code, $used_code_id)){
                $sensor->sensor_id_code = $code;
                break;
            }
        }
        if(!$sensor->sensor_id_code){
            It::memStatus('sensor '.$handler->display_name.' is full');
            $this->redirect($this->createUrl('admin/sensors', array('station_id' => $station->station_id)));
        }

        $sensorHandler = SensorHandler::create($handler->handler_id_code);
        $sensorFeatures = array();

        $ft_1 = $sensorHandler->getFeatures();
        $ft_2 = $sensorHandler->getExtraFeatures();
        if ($ft_2) {
            foreach ($ft_2 as $key => $value)
                $ft_2[$key]['is_extra'] = 1;
        }
        $handler_sensor_features = array_merge($ft_1, $ft_2);

        if ($handler_sensor_features) {
            foreach ($handler_sensor_features as $value) {
                $sf = new StationSensorFeature();
                $default = $handler->features[$value['feature_code']];
                $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(array(
                    'code' => $value['measurement_type_code']
                ));
                $sf->feature_constant_value = isset($value['default']) ? $value['default'] : null;

                if ($default) {
                    $sf->feature_constant_value = $default->feature_constant_value;
                    $sf->metric_id   = $default->metric_id;
                    $sf->filter_max  = $default->filter_max;
                    $sf->filter_min  = $default->filter_min;
                    $sf->filter_diff = $default->filter_diff;
                }

                $sf->metric_id             = $metric->metricMain->metric_id;
                $sf->feature_code          = $value['feature_code'];
                $sf->feature_display_name  = $value['feature_name'];
                $sf->is_constant           = isset($value['is_extra']) ? 1 : 0;
                $sf->comment               = isset($value['comment']) ? $value['comment'] : null;
                $sf->measurement_type_code = $value['measurement_type_code'];
                $sf->is_cumulative         = $value['is_cumulative'];
                $sf->is_main               = $value['is_main'];
                $sf->has_filter_min        = $value['has_filter_min'];
                $sf->has_filter_max        = $value['has_filter_max'];
                $sf->has_filter_diff       = $value['has_filter_diff'];

                $sensorFeatures[] = $sf;
            }
        }

        $validated = $sensor->validate();

        if ($validated and $sensorFeatures){
            foreach ($sensorFeatures as $feature){
                $feature->sensor_id = 1;
                $validated = $validated & $feature->validate();
            }

            if ($validated){
                $sensor->save(false);
                if ($sensorFeatures){
                    foreach ($sensorFeatures as $feature){
                        $feature->sensor_id  = $sensor->station_sensor_id;
                        $feature->save(false);
                    }
                }
                It::memStatus('sensor created');
                It::setMem('sensor_id', $sensor->station_sensor_id);
                $this->redirect($this->createUrl('admin/sensors', array('station_id' => $station->station_id)));
            }
        }
        It::memStatus('sensor save fail');
        $this->redirect($this->createUrl('admin/sensors', array('station_id' => $station->station_id)));
    }

    /**
     * Connection
     */

    public function actionConnections()
    {

        $this_server = new Synchronization();

        if ( $this_server->isMaster() ) {

            $criteria = new CDbCriteria();
            $criteria->order = "communication_port asc, communication_type asc";

            $stations = Station::model()->findAll($criteria);

            $connections = array();
            if (count($stations) > 0)
            {
                foreach ($stations as $station)
                {
                    switch($station->communication_type)
                    {
                        case 'direct':
                            $communication_type = $station->communication_type;
                            $connection_type = $station->communication_port ;
                            $key = $station->communication_port .' '. $communication_type;
                            break;
                        case 'sms':
                            $communication_type = $station->communication_type;
                            $connection_type = $station->communication_port;
                            $key = $station->communication_port .' '. $communication_type;
                            break;

                        case 'tcpip':
                            $communication_type = '';
                            $connection_type = $station->communication_esp_ip .':'. $station->communication_esp_port;
                            $key = $station->communication_esp_ip .':'. $station->communication_esp_port;
                            break;

                        case 'gprs':
                            $communication_type = '';
                            $connection_type = 'poller:'. $station->station_id_code;
                            $key = 'poller:'. $station->station_id_code;

                            break;

                        case 'server':
                            $communication_type = '';
                            $connection_type = 'tcp:'. $station->communication_esp_ip .':'. $station->communication_esp_port;
                            $key = 'tcp:'. $station->communication_esp_ip .':'. $station->communication_esp_port;

                            break;
                        default:
                            $communication_type = '';
                            $connection_type = '';
                            $key = '';

                    }

                    $connections[$key]['stations'][] = $station;
                    $connections[$key]['connection_type'] = $connection_type;
                    $connections[$key]['communication_type'] = $communication_type;
                    $connections[$key]['blocked'] = false;

                    if (!isset($connections[$key]['last_connection']))
                    {
                        $last_connection = Listener::getLastConnectionInfo($connection_type, $communication_type);

                        $connections[$key]['last_connection'] = $last_connection;
                    }
                }

                $connectionsInProcess = array();
                foreach ($connections as $key => $connection) {
                    if (empty($connections[$key]['last_connection']['stopped_show'])) {
                        $connectionsInProcess[$key]['connection_type'] = $connections[$key]['connection_type'];
                        $connectionsInProcess[$key]['communication_type'] = $connections[$key]['communication_type'];
                    }
                }
                foreach ($connections as $key => $connection){
                    foreach ($connectionsInProcess as $key2 => $connectionActive) {

                        if (
                            $connections[$key]['connection_type'] == $connectionActive['connection_type']
                            &&
                            $connections[$key]['communication_type'] != $connectionActive['communication_type']
                        ) {
                            $connections[$key]['blocked'] = true;
                        }
                    }
                }
            }
            $this->render('connections', array(
                'connections' => $connections
            ));
        } else {

            $this->render('connections_slave_mode', array(

            ));

        }

    }

    public function actionConnectionsLog()
    {
        $form = new ConnectionsLogForm();
        $sources = $form->getAllSources();

        if (isset($_GET['source']))
        {
            $src = str_replace('__', ':', trim($_GET['source']));
            $src = str_replace('_', '.', $src);

            if ($src)
            {
                $form->source = $src;

                if ($form->validate())
                {
                    $this->redirect($this->createUrl('admin/connectionslog'));
                }
            }
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['clear']))
        {
            $form->clearMemory();

            $this->redirect($this->createUrl('admin/connectionslog'));
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['filter']))
        {
            $form->attributes = $_POST['ConnectionsLogForm'];

            if ($form->validate())
            {
                $this->redirect($this->createUrl('admin/connectionslog'));
            }
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['export']))
        {
            $form->attributes = $_POST['ConnectionsLogForm'];

            if ($form->validate())
            {
                $form->exportList();
                //$this->redirect($this->createUrl('admin/connectionslog'));
            }
        }

        if (isset($_GET['source']))
        {
            $form->setSource(trim($_GET['source']));
        }

        if (!$form->source)
        {
            foreach ($sources as $key)
            {
                $form->setSource($key);
                break;
            }
        }

        $this->render('connections_log', array(
            'form' => $form,
            'sources' => $sources,
            'provider' => $form->prepareList(),
        ));
    }

    public function actionXmllog()
    {
        $form = new XmlLogForm();

        if (Yii::app()->request->isPostRequest && isset($_POST['clear']))
        {
            $form->clearMemory();
            $this->redirect($this->createUrl('admin/xmllog'));
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['filter']))
        {
            $form->attributes = $_POST['XmlLogForm'];

            if ($form->validate())
            {
                $this->redirect($this->createUrl('admin/xmllog'));
            }
        }

        $res = $form->prepareList();

        $this->render('xml_log', array(
            'form' => $form,
            'list' => $res['list'],
            'pages' => $res['pages']
        ));
    }

    // Ajax methods - run/stop/check listening
    public function actionStartListening()
    {
        if (!isset($_REQUEST['source']))
        {
            echo json_encode(array('errors' => array('Unknown connection type')));

            Yii::app()->end();
        }


        $source = strtoupper ($_REQUEST['source']);
        $communication_type = !empty($_REQUEST['communication_type']) ? strtoupper ($_REQUEST['communication_type']) : 'COMMUNICATION_TYPE_NONE' ;

        $last_connection = Listener::getLastConnectionInfo($source);

        if ($last_connection && !$last_connection['stopped'])
        {
            echo json_encode(array('ok_still' => 1));

            Yii::app()->end();
        }



        $command = Yii::app()->params['applications']['php_exe_path'] . ' -f ' . Yii::app()->params['console_app_path'] . ' listen '. $source .'  '. $communication_type .' Admin';
        It::runAsynchCommand($command);

        echo json_encode(array('ok' => 1));

        Yii::app()->end();
    }

    public function actionStopListening()
    {
        if (!$_REQUEST['source'])
        {
            echo json_encode(array('errors' => array('Unknown connection type')));

            Yii::app()->end();
        }

        $source = strtoupper($_REQUEST['source']);
        $last_connection = Listener::getLastConnectionInfo($source);

        if ($last_connection && !$last_connection['stopped'])
        {
            ProcessPid::killProcess($last_connection['process_pid']);
            ListenerProcess::addComment($last_connection['listener_id'], 'comment', 'Stop by user');
            Listener::stopConnection($last_connection['listener_id']);
        }

        echo json_encode(array('ok' => 1));

        Yii::app()->end();
    }

    public function actionGetStatus()
    {
        if (!$_REQUEST['source'])
        {
            echo json_encode(array('errors' => array('Unknown connection type')));

            Yii::app()->end();
        }

        $return = array();
        $source = strtoupper($_REQUEST['source']);
        $last_connection = Listener::getLastConnectionInfo($source);

        $return = array(
            'listener_id'  => $last_connection ? $last_connection['listener_id'] : 0,
            'started_show' => $last_connection ? $last_connection['started_show'] : '',
            'stopped_show' => $last_connection ? $last_connection['stopped_show'] : '',
            'duration'     => $last_connection ? $last_connection['duration'] : 0,
            'duration_formatted' => $last_connection['duration_formatted']
        );

        echo json_encode($return);

        Yii::app()->end();
    }
    // End of Ajax listening methods

    public function actionSetup()
	{
		$criteria = new CDbCriteria();
        $criteria->condition = "ord > 0";
        $criteria->order = "ord ASC";

		$meas_types = RefbookMeasurementType::model()->findAll($criteria);

		if ($meas_types){
            foreach ($meas_types as $key => $value){
                $sql = "SELECT `t1`.`metric_id`, CONCAT(`t2`.`html_code`, ' (', `t2`.`full_name`, ')') AS `name`, `t1`.`is_main`, `t1`.`measurement_type_metric_id`
                        FROM `".RefbookMeasurementTypeMetric::model()->tableName()."` `t1`
                        LEFT JOIN `".RefbookMetric::model()->tableName()."` `t2` ON `t2`.`metric_id` = `t1`.`metric_id`
                        WHERE `t1`.`measurement_type_id` = '".$value->measurement_type_id."'";

				$meas_types[$key]->metrics_list = Yii::app()->db->createCommand($sql)->queryAll();
            }
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['main_metric'])){
            foreach ($_POST['main_metric'] as $key => $value){
                if ($meas_types[$key]->metrics_list){
                    foreach($meas_types[$key]->metrics_list as $v1){
                        $update = array('is_main' => $v1['metric_id'] == $value ? 1 : 0);
						RefbookMeasurementTypeMetric::model()->updateByPk($v1['measurement_type_metric_id'], $update);
                    }
                }
            }

            StationSensorFeature::updateMetric();

            $DB = array(
                'db'        => CStubActiveRecord::getDbConnect(),
                'db_long'   => CStubActiveRecord::getDbConnect(true)
            );
            foreach($DB as $db){
                $db->createCommand("DELETE FROM `" . ScheduleReportProcessed::model()->tableName() . "`")->query();
                $db->createCommand("DELETE FROM `" . ForwardedMessage::model()->tableName() . "`")->query();
                $db->createCommand("DELETE FROM `" . StationCalculationData::model()->tableName() . "`")->query();
                $db->createCommand("DELETE FROM `" . SeaLevelTrend::model()->tableName() . "`")->query();
                $db->createCommand("DELETE FROM `" . SensorDataMinute::model()->tableName() . "`")->query();
                $db->createCommand("DELETE FROM `" . SensorData::model()->tableName() . "`")->query();
                $db->createCommand("DELETE FROM `" . ListenerLog::model()->tableName() . "`")->query();
            }

            It::memStatus('admin_metrics_saved');

            $this->redirect($this->createUrl('admin/setup'));
        }

        $this->render('setup', array(
            'meas_types' => $meas_types,
        ));
    }

    public function actionSetupOther()
    {
        $settings = Settings::model()->find();
        if (is_null($settings)){
            $settings = new Settings();
        }

        if (Yii::app()->request->isPostRequest && isset($_POST['Settings']))
        {
            $settings->scenario = 'other';
            $settings->attributes = $_POST['Settings'];

            if ($settings->validate()){
                if ($settings->save()){
                    It::memStatus('admin_settings_saved');
                    $this->redirect($this->createUrl('admin/setupother'));
                }
            }
        }

        $this->render('setup_other', array(
            'settings' => $settings
        ));
    }

    public function actionDbsetup(){
// #############################################################################
        $backups_path = Yii::app()->params['backups_path'];

        if (isset($_REQUEST['apply']))
        {
            $apply = trim($_REQUEST['apply']);
            if (file_exists($backups_path . DIRECTORY_SEPARATOR . $apply))
            {
                ini_set('memory_limit', '-1');
                set_time_limit(0);

                $sql = file_get_contents($backups_path . DIRECTORY_SEPARATOR . $apply);
                //use long or short db
                if(stripos($apply,'long'))
                    $res = Yii::app()->db_long->createCommand($sql)->query();
                else
                    $res = Yii::app()->db->createCommand($sql)->query();

                It::memStatus('admin_backup_applied');
                $this->redirect($this->createUrl('admin/dbsetup'));
            }
        }
        if (isset($_REQUEST['delete']))
        {
            $delete = trim($_REQUEST['delete']);

            if (file_exists($backups_path.DIRECTORY_SEPARATOR.$delete))
            {
                unlink($backups_path . DIRECTORY_SEPARATOR . $delete);

                It::memStatus('admin_backup_deleted');
                $this->redirect($this->createUrl('admin/dbsetup'));
            }
        }
        if (isset($_REQUEST['create']))
        {
//            $backup_path = dirname(Yii::app()->request->scriptFile) .
//                DIRECTORY_SEPARATOR .'files'.
//                DIRECTORY_SEPARATOR .'backups';
//
//            if (It::isLinux())
//            {
//                $backup_path .= DIRECTORY_SEPARATOR .'`date +%Y_%m_%d`.sql';
//            }
//            else if (It::isWindows())
//            {
//                $backup_path .= DIRECTORY_SEPARATOR .'%DATE:~7,4%_%DATE:~3,2%_%DATE:~0,2%.sql';
//            }

//            $command = Yii::app()->params['applications']['mysqldump_exe_path'] .
//                ' --user="'. Yii::app()->params['db_params']['username'] .'"'.
//                ' --password="'. Yii::app()->params['db_params']['password'] .'"'.
//                ' --result-file="'. $backup_path .'" '.
//                Yii::app()->params['db_params']['dbname'];


            $backup_name= '`date +%Y_%m_%d`.sql';
            $command = 'echo '.$backup_name . ' >'. dirname(Yii::app()->request->scriptFile) .
                DIRECTORY_SEPARATOR . 'files' .
                DIRECTORY_SEPARATOR .'backups' .
                DIRECTORY_SEPARATOR . 'new';

            set_time_limit(1800); // 30min

            $output = null;
            $return = null;

            exec($command, $output, $return);

            if ($return == 0)
            {
                It::memStatus('admin_backup_refreshed');
                $this->redirect($this->createUrl('admin/dbsetup'));
            }
        }
        if (isset($_REQUEST['create_long']))
        {
            $backup_path = dirname(Yii::app()->request->scriptFile) .
                DIRECTORY_SEPARATOR .'files'.
                DIRECTORY_SEPARATOR .'backups';

//            if (It::isLinux())
//            {
//                $backup_path .= DIRECTORY_SEPARATOR .'`date +%Y_%m_%d`_long.sql';
//            }
//            else if (It::isWindows())
//            {
//                $backup_path .= DIRECTORY_SEPARATOR .'%DATE:~7,4%_%DATE:~3,2%_%DATE:~0,2%_long.sql';
//            }


//            $command = Yii::app()->params['applications']['mysqldump_exe_path'] .
//                ' --user="'. Yii::app()->params['db_long_params']['username'] .'"'.
//                ' --password="'. Yii::app()->params['db_long_params']['password'] .'"'.
//                ' --result-file="'. $backup_path .'" '.
//                Yii::app()->params['db_long_params']['dbname'];

            $backup_name= '`date +%Y_%m_%d`_long.sql';
            $command = 'echo '.$backup_name . ' >'. dirname(Yii::app()->request->scriptFile) .
                DIRECTORY_SEPARATOR . 'files' .
                DIRECTORY_SEPARATOR .'backups' .
                DIRECTORY_SEPARATOR . 'new_long';

            set_time_limit(1800); // 30min

            $output = null;
            $return = null;

            exec($command, $output, $return);

            if ($return == 0)
            {
                It::memStatus('admin_backup_refreshed');
                $this->redirect($this->createUrl('admin/dbsetup'));
            }
        }

        $outputs = null;

        if (It::isLinux())
        {
            $cmd = 'ls -1At '. $backups_path .' | egrep -i *.sql';

            exec($cmd, $outputs);
        }
        else if (It::isWindows())
        {
            exec('dir '. $backups_path . DIRECTORY_SEPARATOR . '*.sql /B /4 /T:C /O:D', $outputs);

            array_slice($outputs, 0, -1);
        }

        $backups = array();

        foreach ($outputs as $output)
        {
            $backups[] = array(
                'filename' => $output,
                'created' => filemtime($backups_path . DIRECTORY_SEPARATOR . $output),
            );
        }
// #############################################################################
        $settings = Settings::model()->find();

        if (is_null($settings))
        {
            $settings = new Settings();
        }

        $settings->scenario = 'dbexport';

        if (Yii::app()->request->isPostRequest && isset($_POST['Settings']))
        {
            $settings->attributes =  $_POST['Settings'];

            if (!$settings->attributes['db_exp_enabled'])
            {
                Settings::model()->updateByPk(1, array('db_exp_enabled' => 0));

                It::memStatus('admin_dbexport_settings_saved');
                $this->redirect($this->createUrl('admin/dbsetup'));
            }
            else
            {
                if ($settings->validate())
                {
                    if ($settings->save(false))
                    {
                        It::memStatus('admin_dbexport_settings_saved');
                        $this->redirect($this->createUrl('admin/dbsetup'));
                    }
                }
            }
        }
// #############################################################################
        $res = BackupOldDataTxLog::model()->prepareList();
// ###########################################################################
        $this->render('dbsetup', array(
            'backups' => $backups,
            'settings' => $settings,
            'list' => $res['list'],
            'pages' => $res['pages']
        ));
    }

    public function actionSetupSensors(){

        $form = new DefaultSensorsForm();

        if(isset($_POST['DefaultSensorsForm'])){
            $form->updateData($_POST['DefaultSensorsForm']);
        }

        $this->render('setup_default_sensors', array(
            'form' => $form
        ));
    }

    public function actionSetupSensor()
    {
        $handler_db = SensorDBHandler::model()->findByPk(isset($_REQUEST['handler_id']) ? intval($_REQUEST['handler_id']) : null);

        if (is_null($handler_db)){
            $this->redirect($this->createUrl('admin/setupsensors'));
        }

        $handler = SensorHandler::create($handler_db->handler_id_code);

        $features = $handler->getFeatures();
        $extraFeatures = $handler->getExtraFeatures();

        if (is_array($extraFeatures)){
            foreach ($extraFeatures as &$extraFeature){
                $extraFeature['is_extra'] = 1;
            }
        }

        $handlerSensorFeatures = array_merge($features, $extraFeatures);
        $sensorFeatures = array();

        if ($handlerSensorFeatures){
            foreach ($handlerSensorFeatures as $key => $handlerSensorFeature){
                $sensorFeature = SensorDBHandlerDefaultFeature::model()->find('handler_id = :handler_id AND feature_code = :feature_code', array(':handler_id' => $handler_db->handler_id, ':feature_code' => $handlerSensorFeature['feature_code']));

                $metric = RefbookMeasurementType::model()->with('metricMain')->findByAttributes(array(
                    'code' => $handlerSensorFeature['measurement_type_code']
                ));

                if (!$sensorFeature){
                    $sensorFeature = new SensorDBHandlerDefaultFeature();
                    $sensorFeature->feature_constant_value = isset($handlerSensorFeature['default']) ? $handlerSensorFeature['default'] : null;
                }

                $sensorFeature->handler_id            = $handler_db->handler_id;
                $sensorFeature->feature_code          = $handlerSensorFeature['feature_code'];
                $sensorFeature->feature_display_name  = $handlerSensorFeature['feature_name'];
                $sensorFeature->is_constant           = isset($handlerSensorFeature['is_extra']) ? 1 : 0;
                $sensorFeature->comment               = isset($handlerSensorFeature['comment']) ? $handlerSensorFeature['comment'] : null;
                $sensorFeature->measurement_type_code = $handlerSensorFeature['measurement_type_code'];
                $sensorFeature->is_cumulative         = $handlerSensorFeature['is_cumulative'];
                $sensorFeature->has_filter_min        = $handlerSensorFeature['has_filter_min'];
                $sensorFeature->has_filter_max        = $handlerSensorFeature['has_filter_max'];
                $sensorFeature->has_filter_diff       = $handlerSensorFeature['has_filter_diff'];
                $sensorFeature->metrics_list          = $metric->metricMain->metric->html_code;
                $sensorFeature->metric_id             = $metric->metricMain->metric->metric_id;
                $sensorFeature->aws_panel_show        = isset($handlerSensorFeature['aws_panel_show']) ? 1 : 0;

                $sensorFeatures[] = $sensorFeature;
            }
        }

        $validated = true;

        if (Yii::app()->request->isPostRequest){
            if(isset($_POST['SensorDBHandler'])){
                $handler_db->setAttributes($_POST['SensorDBHandler']);
                $validated = $validated & $handler_db->save();
            }

            foreach ($sensorFeatures as $key => $value){
                $sensorFeatures[$key]->attributes = $_POST['SensorDBHandlerDefaultFeature'][$key];
                $validated = $validated & $sensorFeatures[$key]->validate();
            }

            if ($validated){
                foreach ($sensorFeatures as $key => $value){
                    $sensorFeatures[$key]->save(false);
                    StationSensorFeature::updateByDefault($handler_db,$sensorFeatures[$key]);
                }

                It::memStatus('admin_default_sensor_saved');
                $this->redirect($this->createUrl('admin/setupsensors'));
            }
        }
        $arrh = array();
        if(SensorDBHandler::checkHandlersFor24h($handler_db->default_prefix)){
            $arrh[-1]='now';
            for($i=0;$i<24;$i++) $arrh[$i]=($i<10?'0'.$i:$i).':00';
        }
        $this->render('setup_default_sensor', array(
            'handler_db'          => $handler_db,
            'validated'           => $validated,
            'sensor_features'     => $sensorFeatures,
            'handler_description' => $handler->getSensorDescription(),
            'arrh'                => $arrh,
        ));

    }

    public  function actionMailsetup(){
//######################################
        $settings = Settings::model()->find();

        if (!$settings)
        {
            $settings = new Settings();
        }

        $settings->scenario = 'mail';

        if (Yii::app()->request->isPostRequest && isset($_POST['Settings']))
        {
            $settings->attributes =  $_POST['Settings'];

            if ($settings->validate())
            {
                if ($settings->save())
                {
                    It::memStatus('admin_mail_settings_saved');

                    $this->redirect($this->createUrl('admin/mailsetup'));
                }
            }
        }
//######################################
        $form = new CheckMailingForm();

        if (Yii::app()->request->isPostRequest && isset($_POST['CheckMailingForm']))
        {
            $form->attributes = $_POST['CheckMailingForm'];

            if ($form->validate())
            {
                if ($form->send()) {
                    It::memStatus('admin_test_mail_was_send');
                } else {
                    It::memStatus('error');
                }

                $this->redirect($this->createUrl('admin/mailsetup'));
            }
        }

        $this->render('mailsetup', array(
            'form' => $form,
            'settings' => $settings
        ));
    }

    /*--------------- Manual work ------------*/

    public function actionImportmsg()
    {

        $settings = Settings::model()->find();
        $log = new Import();
        $log->scenario = 'msg';
        $res = $log->prepareTypes();
        $source_types = $res['source_types'];

        $station_types = $res['station_types'];
        $station_timezones = $res['station_timezones'];


        if (Yii::app()->request->isPostRequest) {

            $lines = array();

            $counter = 0;
            foreach(preg_split("/(\r?\n)/", $_POST['Import']['import_data']) as $line){
                $line = trim($line);
                if ($line) {
                    $lines[] = $line;
                }
            }

            if ($lines) {

                foreach ($lines as $line) {
                    $res = ListenerLogTemp::addNew($line, 0, $settings->overwrite_data_on_import, 'import', $_POST['Import']['source_type']);
                }

                $counter = count($lines);

                It::memStatus($counter.' new message'.($counter > 1 ? 's were' : ' was').' added to processing');
                $this->redirect($this->createUrl('admin/importmsg'));
            }
        }

        $this->render('import', array(
            'form' => $log,
            'source_types' => $source_types,
            'settings' => $settings
        ));
    }

    public function actionImportxml() {

        $settings = Settings::model()->findByPk(1);
        $log = new Import();
        $log->scenario = 'xml';

        if (Yii::app()->request->isPostRequest) {
            if ($_FILES['Import']) {
                if (!empty($_FILES['Import']['name'] ) ) {
                    $log->xml_file = CUploadedFile::getInstance($log, 'xml_file');
                    $original_file_name = $log->xml_file->getName();
                }
                if ($log->validate()) {
                    $log->xml_file->saveAs(Yii::app()->user->getSetting('xml_messages_path').DIRECTORY_SEPARATOR.$original_file_name);
                    It::memStatus('XML file was uploaded into '.Yii::app()->user->getSetting('xml_messages_path').' . It will be processed soon');
                    $this->redirect($this->createUrl('admin/importxml'));
                }
            }
        }

        $this->render('importxml', array(
            'form' => $log,
        ));
    }

    public function actionMsgGeneration()
    {
        ini_set('memory_limit', '-1');
        $form = new GenerateMessageForm();

        $messages = array();
        $sensors  = array();

        if (Yii::app()->request->isPostRequest && (isset($_POST['generate']) || isset($_POST['import'])))
        {
            $form->attributes = $_POST['GenerateMessageForm'];

            if ($form->validate()) {

                $sensors = array();

                if ($form->sensor_id) {

                    $sql = "SELECT `t1`.`station_sensor_id`, `t1`.`sensor_id_code`, `t2`.`handler_id_code`, `t3`.`feature_code`, `t4`.`code` AS `metric_code` 
                            FROM `".StationSensor::model()->tableName()."` `t1`
                            LEFT JOIN `".SensorDBHandler::model()->tableName()."`      `t2` ON `t2`.`handler_id` = `t1`.`handler_id`
                            LEFT JOIN `".StationSensorFeature::model()->tableName()."` `t3` ON `t3`.`sensor_id`  = `t1`.`station_sensor_id`
                            LEFT JOIN `".RefbookMetric::model()->tableName()."`        `t4` ON `t4`.`metric_id`  = `t3`.`metric_id`
                            WHERE `t1`.`station_id` = '".$form->station_id."' AND `t1`.`station_sensor_id` IN (".implode(',',$form->sensor_id).")";
                    $res = Yii::app()->db->createCommand($sql)->queryAll();

                    if ($res)
                    {
                        foreach ($res as $key => $value)
                        {
                            if (!isset($sensors[$value['station_sensor_id']]))
                            {
                                $sensors[$value['station_sensor_id']] = array(
                                    'station_sensor_id' => $value['station_sensor_id'],
                                    'sensor_id_code'    => $value['sensor_id_code'],
                                    'handler_id_code'   => $value['handler_id_code']
                                );
                            }
                            $sensors[$value['station_sensor_id']]['features'][$value['feature_code']] = $value['metric_code'];

                        }
                    }
                }

                $i = $form->start_timestamp;

                while ($i <= $form->end_timestamp)
                {
                    $messages[$i]['timestamp'] = $i;
                    $i = $i + $form->interval * 60;
                }

                foreach ($messages as $key => $value)
                {
                    if ($form->choosed_station['station_type'] === 'rain')
                    {
                        $messages[$key]['parts'][] = 'D';
                        $messages[$key]['parts'][] = $form->choosed_station['station_id_code'];
                        $messages[$key]['parts'][] = date('ymd', $key);
                        $messages[$key]['parts'][] = date('Hi', $key);
                        $messages[$key]['parts'][] = str_pad(rand(100, 135), 3, "0", STR_PAD_LEFT);
                        $messages[$key]['parts'][] = '00';
                    }
                    else
                    {
                        $messages[$key]['parts'][] = 'D';
                        $messages[$key]['parts'][] = $form->choosed_station['station_id_code'];
                        $messages[$key]['parts'][] = date('ymd', $key);
                        $messages[$key]['parts'][] = date('Hi', $key);
                        $messages[$key]['parts'][] = '00';
                    }

                    $sensors_values = array();

                    if ($sensors)
                    {
                        foreach ($sensors as $k1 => $v1)
                        {
                            $handler = SensorHandler::create($v1['handler_id_code']);

                            $random_value = $handler->getRandomValue($v1['features']);
                            $sensors_values[] = $v1['sensor_id_code'].$random_value;
                        }

                        shuffle($sensors_values);
                        foreach($sensors_values as $k1 => $v1) {
                            $messages[$key]['parts'][] = $v1;
                        }
                    }

                    $crc = It::prepareCRC(implode('', $messages[$key]['parts']));
                    $messages[$key]['parts'][] = $crc;

                    array_push($messages[$key]['parts'], '$');
                    array_unshift($messages[$key]['parts'], '@');
                }
            }
        }

        $messages_display = array();
        $messages_copy    = array();

        foreach ($messages as $key => $value)
        {
            $messages_display[] = implode(' ', $value['parts']);
            $messages_copy[] = implode('', $value['parts']);
        }

        $station_sensors = StationSensor::getList($form->station_id);

        if ($station_sensors)
        {
            foreach ($station_sensors as $key => $value)
            {
                $station_sensors[$key]['checked'] = 1;
            }
        }

        if (isset($_POST['GenerateMessageForm']['sensor_id']))
        {
            if ($station_sensors)
            {
                foreach ($station_sensors as $key => $value)
                {
                    $station_sensors[$key]['checked'] = in_array($value['station_sensor_id'], $_POST['GenerateMessageForm']['sensor_id']) ? 1 : 0;
                }
            }
        }

        $this->render('msg_generation', array(
            'form'     => $form,
            'messages_display' => $messages_display,
            'messages_copy'    => $messages_copy,
            'station_sensors'  => $station_sensors
        ));
    }

    public function actionAwsFiltered()
    {


        $delete = isset($_REQUEST['delete']) ? intval($_REQUEST['delete']) : null;
        if ($delete) {
            $obj = SensorData::model()->findByPk($delete);
            if ($obj)
            {
                $obj->delete();
                It::memStatus('admin_suspicious_value_was_deleted');
            }
        }

        $session = new CHttpSession();
        $session->open();
        $sess_name = 'awsfiltered_filter1';
        $fparams = $session[$sess_name];
        $fparams['showdata'] = false;

        if ($fparams['redirect']==true or isset($_GET['page'])) {
            $fparams['showdata'] = true;
        }


        $stations = Station::getList("'aws','awos'", false);

        $time_pattern = "/^(\d{1,2}):(\d{1,2})$/";

        if (!$fparams || isset($_POST['clear']) || isset($_POST['filter']))
        {
            $cur_time = time();
            $some_time_ago = mktime(0, 0, 0, date('m',$cur_time), date('d',$cur_time), date('Y',$cur_time));

            $fparams = array(
                'station_id'      => $stations[0]['station_id'],
                'date_from'       => date('m/d/Y', $some_time_ago),
                'date_to'         => date('m/d/Y', $cur_time),
                'time_from'       => '00:00',
                'time_to'         => '23:59',
                'order_field'     => 'date',
                'order_direction' => 'DESC'
            );

        }

        if (isset($_POST['filter']))
        {
            $fparams['station_id'] = intval($_POST['search']['station_id']);
            $fparams['date_from']  = $_POST['search']['date_from'];
            $fparams['date_to']    = $_POST['search']['date_to'];

            if (preg_match($time_pattern, $_POST['search']['time_from']))
            {
                $fparams['time_from'] = $_POST['search']['time_from'];
            }

            if (preg_match($time_pattern, $_POST['search']['time_to']))
            {
                $fparams['time_to'] = $_POST['search']['time_to'];
            }

        }

        if (isset($_REQUEST['of']) && in_array($_REQUEST['of'], array('stationid', 'date', 'sensorid', 'value')))
        {
            if ($_REQUEST['of'] == $fparams['order_field'])
            {
                $fparams['order_direction'] = $fparams['order_direction'] == 'ASC' ? 'DESC' : 'ASC';
            }
            else
            {
                $fparams['order_direction'] = 'ASC';
            }

            $fparams['order_field'] = $_REQUEST['of'];

        }



        $session[$sess_name] = $fparams;

        if ($_POST || $_REQUEST['of'])
        {
            $fparams['showdata'] = true;
            $fparams['redirect'] = true;
            $session[$sess_name] = $fparams;
            $this->redirect($this->createUrl('admin/awsfiltered') . (isset($_GET['page']) ? 'page/'. $_GET['page'] : ''));
        } else {
            $fparams['redirect'] = false;
            $session[$sess_name] = $fparams;
        }

        /*----------- filter prepare -------------*/
        $sql_where = array();
        $sql_where[] = "`t1`.`is_m` = '0'";
        if ($fparams['date_from']) {
            $sql_where[] = "`t1`.`measuring_timestamp` >= '".date('Y-m-d H:i:s', strtotime($fparams['date_from'].' '.$fparams['time_from']))."'";
        }
        if ($fparams['date_to']) {
            $sql_where[] = "`t1`.`measuring_timestamp` <= '".date('Y-m-d H:i:s', strtotime($fparams['date_to'] .' '.$fparams['time_to']))."'";
        }
        if ($fparams['station_id']) {
            $sql_where[] = "t1.station_id = '".$fparams['station_id']."'";
        }
        $sql_where_str = count($sql_where) ? " AND ".implode(' AND ', $sql_where)." " : "";

        if ($fparams['order_field'] == 'date') {
            $sql_order = "`t1`.`measuring_timestamp` ".$fparams['order_direction'];
        } elseif ($fparams['order_field'] == 'stationid') {
            $sql_order = "`t1`.`station_id` ".$fparams['order_direction'];
        } elseif ($fparams['order_field'] == 'sensorid') {
            $sql_order = "`t1`.`sensor_id` ".$fparams['order_direction'];
        } elseif ($fparams['order_field'] == 'value') {
            $sql_order = "CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4)) ".$fparams['order_direction'];
        } else {
            $sql_order = "`t1`.`measuring_timestamp` ";
        }


        /*----------- /end filter prepare --------*/
        if ($fparams['showdata']) {
            $sql_groupped = "SELECT `sensor_data_id`, `sensor_feature_id`, `measuring_timestamp`, CAST(`sensor_feature_value` AS DECIMAL(15,4)) as `sensor_feature_value`, `listener_log_id`
                         FROM `".SensorData::model()->tableName()."`
                         ORDER BY `measuring_timestamp` DESC
                         LIMIT 1000";

            $sql = "SELECT `t1`.`sensor_data_id`
                FROM ".SensorData::model()->tableName()." `t1`
                LEFT JOIN ({$sql_groupped}) `gt` ON `gt`.`sensor_feature_id` = `t1`.`sensor_feature_id` AND `gt`.`measuring_timestamp` < `t1`.`measuring_timestamp`
                LEFT JOIN `".StationSensorFeature::model()->tableName()."` t2 ON t2.sensor_feature_id = t1.sensor_feature_id
                LEFT JOIN `".SensorDBHandlerDefaultFeature::model()->tableName()."` t3 ON t3.feature_code LIKE t2.feature_code
                WHERE (
                        (t2.has_filter_max AND CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4)) >  (t3.filter_max * IF(t2.is_cumulative, t1.period/60, 1) ) )
                        OR (t2.has_filter_min AND CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4)) < (t3.filter_min * IF(t2.is_cumulative, t1.period/60, 1) ))
                        OR (t2.has_filter_diff AND `gt`.`sensor_data_id` > 0 AND ABS(CAST(`gt`.`sensor_feature_value` AS DECIMAL(15,4)) - CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4))) > (t3.filter_diff * IF(t2.is_cumulative, t1.period/60, 1)))
                       )
                  {$sql_where_str}
                GROUP BY `t1`.`sensor_data_id`
                LIMIT 1000";
            $total = count(Yii::app()->db->createCommand($sql)->queryColumn());

            $pages = new CPagination($total);
            $pages->pageSize = 20;
            //$pages->applyLimit($criteria);

            $sql = "SELECT `t1`.`sensor_data_id`, `t1`.`sensor_feature_id`, CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4)) as `sensor_feature_value`, `t1`.`measuring_timestamp`, t1.period,
                       `gt`.`measuring_timestamp` AS `prev_measuring_timestamp`, `gt`.`listener_log_id` AS `prev_listener_log_id`,  CAST(`gt`.`sensor_feature_value` AS DECIMAL(15,4)) as `prev_sensor_feature_value`, `gt`.`sensor_data_id` as `prev_sensor_data_id`,
                       `t5`.`filter_max`, `t5`.`filter_min`, `t5`.`filter_diff`, t2.has_filter_max, t2.has_filter_min, t2.has_filter_diff, t2.is_cumulative,
                       `t3`.`sensor_id_code`,
                       `t4`.`station_id_code`
                FROM ".SensorData::model()->tableName()." `t1`
                LEFT JOIN ({$sql_groupped}) `gt` ON `gt`.`sensor_feature_id` = `t1`.`sensor_feature_id` AND `gt`.`measuring_timestamp` < `t1`.`measuring_timestamp`
                LEFT JOIN `".StationSensorFeature::model()->tableName()."` t2 ON t2.sensor_feature_id = t1.sensor_feature_id
                LEFT JOIN `".StationSensor::model()->tableName()."`  t3  ON t3.station_sensor_id = t1.sensor_id
                LEFT JOIN `".Station::model()->tableName()."` t4 ON t4.station_id = t1.station_id
                LEFT JOIN `".SensorDBHandlerDefaultFeature::model()->tableName()."` t5 ON t5.feature_code LIKE t2.feature_code
                WHERE (
                       (t2.has_filter_max AND CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4)) > (t5.filter_max * IF(t2.is_cumulative, t1.period/60, 1) ) )
                       OR (t2.has_filter_min AND CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4)) < (t5.filter_min * IF(t2.is_cumulative, t1.period/60, 1) ))
                       OR (t2.has_filter_diff AND `gt`.`sensor_data_id` > 0 AND ABS(CAST(`gt`.`sensor_feature_value` AS DECIMAL(15,4)) - CAST(`t1`.`sensor_feature_value` AS DECIMAL(15,4))) > (t5.filter_diff * IF(t2.is_cumulative, t1.period/60, 1) ))
                      )
                  {$sql_where_str}
                GROUP BY `t1`.`sensor_data_id`
                 HAVING  (
                        (t2.has_filter_max AND CAST(`sensor_feature_value` AS DECIMAL(15,4)) > (t5.filter_max * IF(t2.is_cumulative, t1.period/60, 1) ) )
                        OR (t2.has_filter_min AND CAST(`sensor_feature_value` AS DECIMAL(15,4)) < (t5.filter_min * IF(t2.is_cumulative, t1.period/60, 1) ))
                        OR (t2.has_filter_diff AND prev_sensor_data_id > 0 AND ABS(CAST(`prev_sensor_feature_value` AS DECIMAL(15,4)) - CAST(`sensor_feature_value` AS DECIMAL(15,4))) > (t5.filter_diff * IF(t2.is_cumulative, t1.period/60, 1) ))
			            )
                ORDER BY {$sql_order}
                LIMIT ".($pages->currentPage *$pages->pageSize).", ".$pages->pageSize;

            $list = Yii::app()->db->createCommand($sql)->queryAll();

            if ($list) {
                foreach ($list as $key =>  &$value)
                {
                    $multiplyer_str = '';
                    $multiplyer = 1;

                    if ($value['is_cumulative'])
                    {
                        $multiplyer_str = $value['period'] != 60 ? ' * '.$value['period'].'min/60' : '';
                        $multiplyer = $value['period']/60;
                    }
                    if (isset($value['has_filter_max']) && $value['sensor_feature_value'] > $value['filter_max']*$multiplyer) {
                        $value['filter_reason'][] = array('main' => 'T1 > '.$value['filter_max'].$multiplyer_str);
                    }
                    if (isset($value['has_filter_min']) && $value['sensor_feature_value'] < $value['filter_min']*$multiplyer) {
                        $value['filter_reason'][] = array('main' => 'T1 < '.$value['filter_min'].$multiplyer_str);
                    }

                    if (isset($value['prev_sensor_feature_value']) && isset($value['has_filter_diff']) && abs($value['sensor_feature_value'] - $value['prev_sensor_feature_value']) > $value['filter_diff']*$multiplyer) {
                        $value['filter_reason'][] = array('main' => '|T1 - T0| > '.$value['filter_diff'].$multiplyer_str, 'extra' => '(Previous value: '.$value['prev_sensor_feature_value'].' on '.$value['prev_measuring_timestamp'].')');
                    }

                }
            }

            $this->render('awsfiltered', array(
                'list'          => $list,
                'clean_page'    => false,
                'pages'         => $pages,
                'stations'      => $stations,
                'fparams'       => $fparams,
            ));
        } else {

            $this->render('awsfiltered', array(
                'list'          => $list,
                'clean_page'    => true,
                'stations'      => $stations,
                'fparams'       => $fparams,
            ));
        }

    }

    public function actionDeleteForwardInfo($id)
    {
        $this->loadModelForwardInfo($id)->delete();

        $this->redirect(array('admin/ForwardList'));
    }

    public function actionForwardList()
    {
        $model = new MessageForwardingInfoForTcpServer(false,'search');
        $model->unsetAttributes();  // clear any default values

        $createModel = new MessageForwardingInfoForTcpServer(false,'create');

        if(isset($_POST['MessageForwardingInfoForTcpServer']))
        {
            $createModel->attributes = $_POST['MessageForwardingInfoForTcpServer'];

            if($createModel->save())
                $this->redirect(array('admin/ForwardList'));
        }

        $this->render('message_forwarding_list',array(
            'model' => $model,
            'createModel' => $createModel,
        ));
    }

    protected function loadModelForwardInfo($id)
    {
        $model = MessageForwardingInfoForTcpServer::model()->findByPk($id);

        if($model === null)
            throw new CHttpException(404,'The requested page does not exist.');

        return $model;
    }
    /**
     * Users
     */
    public function actionUsers(){
        $criteria = new CDbCriteria();
        $criteria->compare('role',array_slice(Yii::app()->params['user_role'],1));
        $criteria->order = "role desc";
        $users = User::model()->findAll($criteria);

        $criteria = new CDbCriteria();
        $criteria->compare('controller',Yii::app()->params['controllers'][2]);
        $criteria->compare('enable','1');
        $criteria->addNotInCondition('action',AccessGlobal::getDefaultAction());
        $criteria->order = "action asc";
        $actions = AccessGlobal::model()->findAll($criteria);

        $this->render('users', array(
            'users'     => $users,
            'actions'    => $actions
        ));
    }
    public function actionUser(){
        $user = User::model()->findByPk(isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : null);
        if(is_null($user))
            $user = new User();

        $criteria = new CDbCriteria();
        $criteria->compare('controller',Yii::app()->params['controllers'][2]);
        $criteria->compare('enable','1');
        $criteria->addNotInCondition('action',AccessGlobal::getDefaultAction());
        $criteria->order = "action asc";
        $actions = AccessGlobal::model()->findAll($criteria);

        if (Yii::app()->request->isPostRequest && isset($_POST['User'])){
            $user->attributes = $_POST['User'];
            $user->pass = $_POST['User']['pass'];

            if($user->save()){
                AccessUser::model()->deleteAllByAttributes(array('user_id' => $user->user_id));
                $accessForUser = array_merge($_POST['access']?$_POST['access']:array(),AccessGlobal::getIdDefaultAction());

                foreach($accessForUser as  $val){
                    $access = new AccessUser;
                    $access->user_id = $user->user_id;
                    $access->action_id=$val;
                    $access->save();
                }

                $this->redirect($this->createUrl('admin/users'));
            }
        }

        $this->render('user', array(
            'user'       => $user,
            'actions'    => $actions,
            'access'     => AccessUser::getActionIdFromUser($user->user_id)
        ));
    }
    public function actionUserDelete(){
        $user_id = intval($_REQUEST['user_id']);

        $user = User::model()->findByPk(isset($user_id) ? intval($user_id) : null);

        if (!is_null($user) && $user->username != Yii::app()->user->name){
            AccessUser::model()->deleteAllByAttributes(array('user_id' => $user->user_id));
            $user->delete();
        }
        $this->redirect($this->createUrl('admin/users'));
    }
    public function actionUserAccessChange(){
        $user_id = intval($_REQUEST['user_id']);
        $action_id = intval($_REQUEST['action_id']);

        if(isset($action_id) and isset($user_id)){
            if(AccessUser::checkActionAtUser($user_id,$action_id)){
                AccessUser::model()->deleteAllByAttributes(array('user_id' => $user_id, 'action_id' => $action_id));
            } else {
                $access = new AccessUser;
                $access->user_id = $user_id;
                $access->action_id=$action_id;
                $access->save();
            }
        }

        $this->redirect($this->createUrl('admin/users'));
    }
    /**
     * station groups
     */
    public function actionStationGroups(){
        $accessEdit = Yii::app()->user->isSuperAdmin();

        $group = new StationGroup();
        if(isset($_GET['group_id'])){
            $group_id = $_GET['group_id'];
            if($_GET['action'] == 'delete'){
                StationGroup::deleteGroupId($group_id);
                $this->redirect($this->createUrl('admin/StationGroups'));
            }
            $group = StationGroup::model()->findByPk($group_id);
        }

        if(isset($_POST['StationGroup']['name']) && $_POST['StationGroup']['name']){
            if(isset($_POST['StationGroup']['group_id']) && $_POST['StationGroup']['group_id'])
                $group = StationGroup::model()->findByPk($_POST['StationGroup']['group_id']);

            $group->name = $_POST['StationGroup']['name'];
            if($group->validate()){
                $group->save();
                $this->redirect($this->createUrl('admin/StationGroups'));
            }
        }

        if(isset($_POST['StationGroupsForm'])){
            StationGroupDestination::setStationGroupArray($_POST['StationGroupsForm']['data']);
        }

        $form = new StationGroupsForm();
        $this->render('station_groups', array(
            'form'          => $form,
            'group'         => $group,
            'accessEdit'    => $accessEdit
        ));
    }
    /**
     * Heartbeat report
     */
    public function actionHeartbeatReports(){
        $pages = new CPagination();
        $pages->pageSize = 6*8;
        $reports = HeartbeatReport::getReportList($pages);
        $this->render('heartbeat_reports',array(
            'reports'   => $reports,
            'pages'     => $pages
        ));
    }
    public function actionHeartbeatReport(){
        $report = HeartbeatReport::getReport($_GET['report_id']);
        $data = new GetStatistics($report->report_id);

        if($_GET['download']){
            $data->downloadExcel($report);
        }
        $this->render('heartbeat_report',array(
            'report'      => $report,
            'data'        => $data,
        ));
    }



    /**
     * Send command on station by SMS
     */
    public function actionSendSMSCommand()
    {
        $form = new SMSCommandSendForm();

        /**
         * Update sms status
         */
        if (isset($_GET['view_id']) || isset($_GET['sms_command_id'])) {
            if (isset($_GET['view_id'])) $form->setSMS(SMSCommand::model()->findByPk($_GET['view_id']));
            if (isset($_GET['sms_command_id'])) $form->setSMS(SMSCommand::model()->findByPk($_GET['sms_command_id']));
            if (Yii::app()->request->isAjaxRequest) {
                return $this->renderPartial('__sms_command_status', ['sms' => $form->getSMS()]);
            }
        }

        /**
         * Delete sms
         */
        if (isset($_GET['delete_id'])) {
            SMSCommand::model()->deleteByPk($_GET['delete_id']);
        }

        /**
         * Create sms
         */
        if (isset($_POST['SMSCommandSendForm'])) {
            if ($_POST['send']) {
                $form->setScenario($form::SCENARIO_SEND);
                $form->setAttributes($_POST['SMSCommandSendForm']);

                if ($form->validate()) {
                    $form->getSMS()->setAttributes($form->getAttributes());

                    if ($form->getSMS()->save()) {
                        $form = (new SMSCommandSendForm())->setSMS(SMSCommand::model()->findByPk($form->getSMS()->sms_command_id));
                        Yii::app()->user->setFlash('SendSMSCommandForm_success', "Command send!");
                    }
                }
            }
        }

        /**
         * Grid
         */

        if (isset($_POST['date_range'])) {

            if (preg_match("/([\d]{1,2})\/([\d]{1,2})\/([\d]{2,4})/i",$_POST['SMSCommand']['updated_from'],$matches1)) {
                $dateFromViewFormat = $matches1[0];
                $dateFrom = $matches1[3].'-'.$matches1[1].'-'.$matches1[2].' 00:00:00';
                Yii::app()->request->cookies['dateFrom'] = new CHttpCookie('dateFrom', $dateFromViewFormat);
                Yii::app()->request->cookies['from_date'] = new CHttpCookie('from_date', $dateFrom);
            } else {
                unset(Yii::app()->request->cookies['dateFrom']);
                unset(Yii::app()->request->cookies['from_date']);
            }

            if (preg_match("/([\d]{1,2})\/([\d]{1,2})\/([\d]{2,4})/i",$_POST['SMSCommand']['updated_to'],$matches2)) {
                $dateToViewFormat = $matches2[0];
                $dateTo = $matches2[3].'-'.$matches2[1].'-'.$matches2[2].' 23:59:59';
                Yii::app()->request->cookies['dateTo'] = new CHttpCookie('dateTo', $dateToViewFormat);
                Yii::app()->request->cookies['to_date'] = new CHttpCookie('to_date', $dateTo);
            } else {
                unset(Yii::app()->request->cookies['dateTo']);
                unset(Yii::app()->request->cookies['to_date']);
            }

        }

        if (isset($_GET['reset'])) {
            unset(Yii::app()->request->cookies['dateFrom']);
            unset(Yii::app()->request->cookies['dateTo']);
            unset(Yii::app()->request->cookies['from_date']);
            unset(Yii::app()->request->cookies['to_date']);
        }


        $SMSCommand = new SMSCommand();
        $SMSCommand->unsetAttributes();

        $SMSCommand->from_date = Yii::app()->request->cookies['from_date'];
        $SMSCommand->to_date = Yii::app()->request->cookies['to_date'];

        if ($_GET['SMSCommand']) {
            $SMSCommand->attributes = $_GET['SMSCommand'];
        }
        $dataProvider = $SMSCommand->search();

        //csv
        if ($_GET['getcsv']) {
            $items = $dataProvider->model->findAll($dataProvider->criteria);
            if (!is_null($items)) {

                $data = array();
                foreach ($items as $item) {
                    $dataItem = $item->getAttributes();
                    $data[] = $dataItem;
                }

                $ECSVExporter = new ECSVExporter($data);
                It::downloadFile($ECSVExporter->getString(), 'csv.csv', 'text/csv');
                exit;
            }
        }

        return $this->render('send_sms_command',['form' => $form, 'dataProvider' => $dataProvider,'dateFrom'=>Yii::app()->request->cookies['dateFrom'], 'dateTo'=>Yii::app()->request->cookies['dateTo']]);
    }

    public function actionGenerateSMSCommand()
    {
        if ($_POST['SMSCommandGenerateMessageForm']) {
            $form = new SMSCommandSendForm();
            $form->setScenario($form::SCENARIO_GENERATE);
            $form->setAttributes($_POST['SMSCommandGenerateMessageForm']);
            if (!$form->validate()) {
                return null;
            }

            $form = new SMSCommandGenerateMessageForm();
            $form->setAttributes($_POST['SMSCommandGenerateMessageForm']);

            if ((!$_POST['open'] || !$form->getParamsList()) && $form->validate()) {
                echo json_encode(
                    ['status' => 'success', 'message' => $form->generateMessage()]
                );
            } else {
                echo json_encode(['form' => $this->renderPartial('form/generate_sms_command',['form' => $form], true)]);
            }
        }
    }

    public function actionSMSCommandSetup()
    {
        $SMSCOMPort = new SMSCOMPort();
        if ($_POST['setup_com']) {
            $SMSCOMPort->COM = $_POST['setup_com'];
            $SMSCOMPort->save();
        }
        return $this->render('setup_sms_command',array('SMSCOMPort' => $SMSCOMPort));

    }
    /**
     * Admin Access
     */
    public function filters()
    {
        return array('accessControl');
    }
    public function accessRules(){
        if(!Yii::app()->user->isGuest){
            return array(
                array('allow',
                    'actions' => Yii::app()->user->access['admin'],
                ),
                array('deny',
                    'actions' => array(),
                )
            );
        } else {
            return array(
                array('allow',
                    'actions' => array('login'),
                    'users' => array('?'),
                ),
                array('deny',
                    'users' => array('?'),
                )
            );
        }
    }


}