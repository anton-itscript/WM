<?php

class SuperadminController extends CController
{
    public function beforeAction()
    {
        Yii::app()->clientScript->coreScriptPosition = CClientScript::POS_HEAD;
        return true;
    }
    public function actionIndex(){
		$this->redirect($this->createUrl('superadmin/users'));
    }
    /*
     * Users
     */

    public function actionUsers(){
        $criteria = new CDbCriteria();
        $criteria->compare('role',array_slice(Yii::app()->params['user_role'],0));
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

                $this->redirect($this->createUrl('superadmin/users'));
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
        $this->redirect($this->createUrl('superadmin/users'));
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

        $this->redirect($this->createUrl('superadmin/users'));
    }

    /*
     * access
     */
    public function actionAccess(){
        $criteria = new CDbCriteria();
        $criteria->order = "controller asc, action asc";

        $access = AccessGlobal::model()->findAll($criteria);
        $this->render('access', array(
            'access' => $access,
        ));
    }
    public function actionAccessDelete(){
        $id = intval($_REQUEST['id']);

        $access = AccessGlobal::model()->findByPk(isset($id) ? intval($id) : null);

        if (!is_null($access)){
            $access->delete();
        }
        $this->redirect($this->createUrl('superadmin/access'));
    }
    public function actionAccessEdit(){
        $access = AccessGlobal::model()->findByPk(isset($_REQUEST['id']) ? intval($_REQUEST['id']) : null);

        if(is_null($access))
            $access = new AccessGlobal();

        if (Yii::app()->request->isPostRequest && isset($_POST['AccessGlobal'])){
            $access->attributes = $_POST['AccessGlobal'];

            if($access->save()){
                $this->redirect($this->createUrl('superadmin/access'));
            }
        }

        $this->render('accessedit', array(
            'access'    => $access,
        ));
    }
    public function actionAccessChange(){
    $id = intval($_REQUEST['id']);

    $access = AccessGlobal::model()->findByPk(isset($id) ? intval($id) : null);

    if (!is_null($access)){

        $access->attributes = array(
            'enable' => $access->enable == '1'? '0': '1'
        );
        $access->save();
    }
    $this->redirect($this->createUrl('superadmin/access'));
}
    /*
     * setting LONG TERM DB
     */
    public function actionLongDbSetup(){
        $conf_form = new AdminConfig;
        //$conf_form->init_db();

        if (Yii::app()->request->isPostRequest AND
                (isset($_POST['save_db_config']) OR isset($_POST['db_create'])) ){

            $conf_form->attributes = $_POST['AdminConfig'];

            if ($conf_form->validate()){
                $conf_form->saveDBConfigAfterInstallation();
                if(isset($_POST['db_create'])) {
                    $conf_form->setupDb();
                    $this->redirect($this->createUrl('superadmin/longdbsetup'));
                }
            }
        }
        $this->render('longdb', array(
            'conf_form' => $conf_form,
        ));
    }
    public function actionLongDbTask(){
        $conf_form = new AdminConfig;
        if(!$conf_form->status) $this->redirect($this->createUrl('superadmin/longdbsetup'));

       // $conf_form->init_sync();

        if (Yii::app()->request->isPostRequest AND
            (isset($_POST['save_db_sync']) OR isset($_POST['delete_db_sync'])) ){

            $conf_form->attributes = $_POST['AdminConfig'];

            if ($conf_form->validate()){
                $conf_form->saveDBSYNCConfig();
                $conf_form->deleteSync();
                if(isset($_POST['save_db_sync'])) $conf_form->createSync();
            }
        }
        $this->render('longdbtask', array(
            'conf_form' => $conf_form,
            'const' => Yii::app()->params['CONST']['delete_periodicity'],
            'periodicity' => Yii::app()->params['CONST']['periodicity'],
        ));
    }
    /*
     * Heartbeat Report
     */
    public function actionHeartbeatReport(){
        $form = new HeartbeatReportForm();

        if(isset($_GET['email_id'])){
            unset($form->email[$_GET['email_id']]);
            $form->scenario = 'Delete';
            if($form->validate()){
                $form->update();
                $this->redirect($this->createUrl('superadmin/HeartbeatReport'));
            }
        }
        if(isset($_POST['HeartbeatReportForm'])){
            $form->attributes=$_POST['HeartbeatReportForm'];
            if(isset($_POST['scenario'])){
                $form->scenario = $_POST['scenario'];
                if($form->validate()){
                    $form->update();
                }
            }
        }


        $this->render('heartbeat_setting', array(
            'form'=>$form,
        ));
    }



    public function actionConfig()
    {
        $form = new SuperAdminConfigForm();

        if(Yii::app()->request->isPostRequest && isset($_POST['SuperAdminConfigForm'])) {
            $form->attributes = $_POST['SuperAdminConfigForm'];
            if ($form->validate()) {
                $form->save();
            }
        }
        $this->render('setup_config',['form' => $form]);
    }

    public function actionSyncSettings()
    {

//        $parseMessage =  new ParseMessage(LoggerFactory::getFileLogger('parse_message'),'@DAWS01150429145700TP21005BV1133CH1s51560648206968071350008706527043570250700718PR109940TP1101561782E43$');
//        echo "<pre>";
//        print_r( $parseMessage->getStationIdCode());
//        echo "</pre>";exit;


        $form = new SynchronizationForm();
        $synchronization = new Synchronization();
        if(Yii::app()->request->isPostRequest && isset($_POST['SynchronizationForm']) && isset($_POST['__save'])) {
            $form->attributes = $_POST['SynchronizationForm'];

            if ($form->validate()) {

                if ($form->process_status==0) {
                    if($form->flexibility_role==1) {
                        $synchronization->setInMaster();
                    }
                    if($form->flexibility_role==2) {
                        $synchronization->setInSlave();
                    }

                }

                $form->save();
                It::memStatus('Setting up synchronization was updated');
            }
        }


        if (Yii::app()->request->isPostRequest && isset($_POST['process_start'])) {
                $synchronization->switchProcess();
                It::memStatus($synchronization->process_status=='stopped' ?   'Process was stopped' : 'Process was started' );
        }
            $disabled='';
        if ($synchronization->process_status == 'processed') {
            $disabled='disabled';
        }
        $this->render('sync_settings',array('form' => $form,'synchronization'=>$synchronization,'disabled'=>$disabled));
    }

    public function actionMetrics()
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

            $this->redirect($this->createUrl('superadmin/metrics'));
        }

        $this->render('metrics', array(
            'meas_types' => $meas_types,
        ));
    }

    public function actionAwsFormat()
    {
         $form = new AWSFormatConfigForm();

        if(Yii::app()->request->isPostRequest && isset($_POST['AWSFormatConfigForm'])) {
            $form->attributes = $_POST['AWSFormatConfigForm'];
            if ($form->validate()) {
                $form->save();
            }
        }
        $this->render('setup_aws_format',array('form' => $form));
    }


    public function actionConvert()
    {
        $process = new ConvertDataloggerCsvToMessages();
        $allStations = Station::getList('all', false);
        $stations = array();
        if ($allStations) {
            foreach( $allStations as $key => $value)
                $stations[$value['station_id_code']] = $value['station_id_code'].' -  '.$value['display_name'];
        }

        $process->setSource(isset($_POST['source']) ? $_POST['source'] : '');
        $process->setStation(isset($_POST['station']) ? $_POST['station'] : '');

        $this->render('datalogger_convert', [
            'source'  => $process->getSource(),
            'station' => $process->getStation(),
            'stations' => $stations,
            'convert' => $process->getConvert(),
        ]);
    }

    /*
     * export admin settings
     * */
    public function actionExportAdminsSettings(){

        $exportAdminsSettings = new  ExportAdminsSettings();
        $importAdminsSettings = new  ImportAdminsSettings();

        if (Yii::app()->request->isPostRequest && $_POST['type']=='export') {
            $exportAdminsSettings->attributes=$_POST['ExportAdminsSettings'];

            $exportAdminsSettings->createExport();
        }

        if (Yii::app()->request->isPostRequest && $_POST['type']=='import') {
            $importAdminsSettings->imported_file=CUploadedFile::getInstance($importAdminsSettings,'imported_file');
            if ($importAdminsSettings->validate()) {
                $importAdminsSettings->save();
            }
        }

        $this->render('exportadminssettings',array(
            'exportAdminsSettings' => $exportAdminsSettings,
            'importAdminsSettings' => $importAdminsSettings,
        ));
    }


    public function filters(){
        return array('accessControl');
    }

    public function accessRules(){
        return array(
            array('allow',
                'users' => array('superadmin'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }
}