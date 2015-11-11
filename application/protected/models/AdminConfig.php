<?php

class AdminConfig extends MainInstall{
        // database section
        public $status;
        public $user;
        public $password;
        public $host;
        public $dbname;
        public $port;
        //sync task
        public $sync_id;
        public $sync_periodicity;
        public $sync_interval;
        public $sync_startTime;
        public $sync_delete_periodicity;
        public $sync_delete_period;
        public $sync_max_row;
        //backup db
        public $db_backup_id='delaircoBackupDBLong';
        //heartbeat report
        public $heartbeat_id='heartbeat_report';

        protected $install_db_long = "install_db_long.php";
        public function setupDB()
        {
            $dump_file = Yii::app()->basePath .
                DIRECTORY_SEPARATOR .'..'.
                DIRECTORY_SEPARATOR .'www'.
                DIRECTORY_SEPARATOR .'files'.
                DIRECTORY_SEPARATOR .'install'.
                DIRECTORY_SEPARATOR .'long_db.sql';

            if (!file_exists($dump_file)){
                return array('error' => 'Dump file was not found' . $dump_file);
            }

            $connection = new CDbConnection('mysql:host='.$this->host.';port='.$this->port, $this->user, $this->password);
            $connection->setActive(true);

            $sql = "DROP DATABASE IF EXISTS `{$this->dbname}`; CREATE DATABASE `{$this->dbname}` CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $connection->createCommand($sql)->query();

            $connection->setActive(false);
            $connection = null;

            $connection = new CDbConnection('mysql:host='.$this->host.';port='.$this->port.';dbname='.$this->dbname, $this->user,$this->password);
            $sql = file_get_contents($dump_file);

            $connection->createCommand($sql)->query();

            $this->saveDBLongInstallStatusConfig(1);
            return array('ok' => 1);
        }

        public function createSync(){

            $applicationsPaths = $this->getConfigFile('application_params');

            if (TaskManager::check($this->sync_id) === false){
                $command = $applicationsPaths['php_exe_path'] . " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php syncdb";
                TaskManager::create($this->sync_id, $command, $this->sync_periodicity, $this->sync_interval,$this->sync_startTime);
            }
            if (TaskManager::check($this->db_backup_id) === false)
            {

                $backup_path = Yii::app()->params['backups_path'] .
                    DIRECTORY_SEPARATOR . (It::isLinux() ? '`echo "$""(date +\%a)"`' : '%DATE:~0,3%') .'_long.sql';

                // Schedule daily database backup
//                $command = $install->getConfigSection('path','mysqldump_exe_path') .
                $command = $applicationsPaths['mysqldump_exe_path'] .
                    ' --user="'. $this->user .'"'.
                    ' --password="'. $this->password .'"'.
                    ' --result-file="'. $backup_path .'" '.
                    $this->dbname;

                TaskManager::create($this->db_backup_id, $command, 'daily', 1, '4:00');
            }
            if (TaskManager::check($this->heartbeat_id) === false){
                $command = $applicationsPaths['php_exe_path'] . " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php heartbeatreport";
                TaskManager::create($this->heartbeat_id, $command, 'minutely', 10);
            }
        }

        public function deleteSync(){
            if(TaskManager::check($this->sync_id))
                TaskManager::delete($this->sync_id);
            if(TaskManager::check($this->db_backup_id))
                TaskManager::delete($this->db_backup_id);
            if(TaskManager::check($this->heartbeat_id))
                TaskManager::delete($this->heartbeat_id);
        }

        /*
         * Rules
         */
        public function rules(){
            return array(
                //DB
                array('user,host,dbname,port', 'required', 'on' => 'DB'),
               // array('host', 'checkHost', 'on' => 'DB'),
                array('host', 'checkHostExists', 'on' => 'DB'),
                array('port', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'on' => 'DB'),
                array('dbname', 'match', 'pattern' => '/^[A-Z,a-z,0-9,_,-]{0,30}$/', 'on' => 'DB'),
                array('password', 'length', 'allowEmpty' => true, 'on' => 'DB'),
                array('user', 'checkUser', 'on' => 'DB'),
                //SYNC
                array('sync_id,sync_periodicity,sync_interval,sync_delete_period,sync_delete_periodicity', 'required', 'on' => 'DBSYNC'),
                array('sync_interval,sync_delete_period,sync_max_row','numerical','min' => 0,'integerOnly' => true,'on' => 'DBSYNC'),
                array('sync_startTime', 'match', 'pattern' => '/^(\d{1,2}):(\d{1,2})$/','on' => 'DBSYNC'),

            );
        }
        /*
         * check
         */
        public function checkUser(){
            if ($this->hasErrors('host') || $this->hasErrors('user') || $this->hasErrors('password') || $this->hasErrors('port'))
                return false;
            try {
                $connection = new CDbConnection('mysql:host='. $this->host, $this->user, $this->password);
                $connection->setActive(true);

                $sql = "DROP DATABASE IF EXISTS `{$this->dbname}`;";
                $connection->createCommand($sql)->query();

                $sql = "CREATE DATABASE `{$this->dbname}` CHARACTER SET utf8 COLLATE utf8_general_ci;";
                $connection->createCommand($sql)->query();
            }
            catch (CDbException $e){
                $this->addError('user', $e->getMessage());
                return false;
            }
            return true;
        }
        public function checkHost(){
            if (!$this->hasErrors('host')){
                if ($this->host == 'localhost' OR
                        preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->host) OR
                        preg_match('/^[a-zA-Z-\d]+(?:\.[a-zA-Z-\d]+)$/', $this->host))
                    return true;

                $this->addError('host', 'Incorrect host value');

                return false;
            }

            return true;
        }
        public function checkHostExists()
        {
            if (!$this->hasErrors('host') && !$this->hasErrors('port'))
            {
                $errorNum = null;
                $errorMsg = null;

                $fp = @fsockopen($this->host, $this->port, $errorNum, $errorMsg);

                if($fp === false)
                {
                    $this->addError('host', 'This host is unreachable. #'. $errorNum .': '. $errorMsg);

                    return false;
                }
            }

            return true;
        }
        /*
         * validate
         */
        public function beforeValidate(){
            $this->host = trim($this->host);
            $this->user = trim($this->user);
            $this->dbname = trim($this->dbname);
            $this->password = trim($this->password);
            return parent::beforeValidate();
        }
        /*
         * Other
         */
        public function attributeLabels(){
            return array(
                //db
                'user' => Yii::t('project', 'User'),
                'host'  => Yii::t('project', 'Host'),
                'port'  => Yii::t('project', 'Port'),
                'dbname'    => Yii::t('project', 'DB Name'),
                'password'  => Yii::t('project', 'Password'),
                'dblong'  => Yii::t('project', 'DB Long'),

                //sync
                'sync_id'  => Yii::t('project', 'Sync Id'),
                'sync_interval'  => Yii::t('project', 'Interval Cron'),
                'sync_startTime'  => Yii::t('project', 'Cron Start Time '),
                'sync_startTime_desc'  => Yii::t('project', 'Format "HH:mm"'),
                'sync_delete_period'  => Yii::t('project', 'Save Short Term'),
                'sync_value'  => Yii::t('project', 'Value'),
                'sync_max_row'  => Yii::t('project', 'Max Row in Query'),
                'sync_max_row_desc'  => Yii::t('project', 'If set 0, limitation will be lifted'),
            );
        }
        public function init(){

            parent::init();

            $this->createDefaultFiles();
            $values = $this->getConfigFile('db_long_params');

            $this->user     = $values['username'];
            $this->password = $values['password'];
            $this->host     = $values['host'];
            $this->dbname   = $values['dbname'];
            $this->port     = $values['port'];


            $this->scenario = 'DBSYNC';
            $values = $this->getConfigFile('database_long_sync_config');

            $this->sync_id                   = $values['sync_id'];
            $this->sync_periodicity          = $values['periodicity'];
            $this->sync_interval             = $values['interval'];
            $this->sync_startTime            = $values['startTime'];
            $this->sync_delete_periodicity   = $values['delete_periodicity'];
            $this->sync_delete_period        = $values['delete_period'];
            $this->sync_max_row              = $values['max_row'];

            $values = $this->getConfigFile('install_db_long');
            $this->status  = $values['install_database_long_status'];
        }


        public function saveDBConfig()
        {
            $this->writeToMysqlParamsFile();
            $this->saveDBLongInstallStatusConfig(0);
            $this->saveDBLongCheckStatusConfig(1);
            $this->saveDBSYNCConfig();
            $this->saveInstallProcessStatus(0);
        }

        public function saveDBConfigAfterInstallation()
        {
            $this->writeToMysqlParamsFile();
            $this->saveDBLongInstallStatusConfig(1);
            $this->saveDBLongCheckStatusConfig(1);
            $this->saveDBSYNCConfig();
            $this->saveInstallProcessStatus(1);
        }


        public function saveDBSYNCConfig(){

            $conf_form = new ConfigForm($this->config_params_path . $this->file_database_long_sync_config);
            $conf_form->updateParam('sync_id', $this->sync_id);
            $conf_form->updateParam('periodicity',$this->sync_periodicity );
            $conf_form->updateParam('interval',$this->sync_interval );
            $conf_form->updateParam('startTime', $this->sync_startTime);
            $conf_form->updateParam('delete_periodicity',$this->sync_delete_periodicity );
            $conf_form->updateParam('delete_period',$this->sync_delete_period );
            $conf_form->updateParam('max_row', $this->sync_max_row);

            $conf_form->saveToFile();
        }


        public function writeToMysqlParamsFile()
        {

            $conf_form = new ConfigForm($this->config_params_path . $this->file_mysql_long);
            $conf_form->updateParam('class','CDbConnection');
            $conf_form->updateParam('connectionString','mysql:host='. $this->host .';port='. $this->port .';dbname='.$this->dbname);
            $conf_form->updateParam('username',$this->user);
            $conf_form->updateParam('password', $this->password);
            $conf_form->updateParam('charset','utf8');
            $conf_form->updateParam('emulatePrepare',true);
            $conf_form->updateParam('enableParamLogging',false);
            $conf_form->updateParam('enableProfiling',false);
            $conf_form->updateParam('persistent',true);
            $conf_form->updateParam('initSQLs',array("set time_zone='+00:00';"));
            $conf_form->saveToFile();

            $conf_form = new ConfigForm($this->config_params_path . $this->file_db_long_params);
            $conf_form->updateParam('username',$this->user);
            $conf_form->updateParam('password', $this->password);
            $conf_form->updateParam('host', $this->host);
            $conf_form->updateParam('port', $this->port);
            $conf_form->updateParam('dbname', $this->dbname);
            $conf_form->saveToFile();

        }

        public function writeDatabaseLongSyncConfig()
        {

            $conf_form = new ConfigForm($this->config_params_path . $this->file_database_long_sync_config);
            $conf_form->updateParam('sync_id', $this->sync_id);
            $conf_form->updateParam('periodicity', $this->sync_periodicity);
            $conf_form->updateParam('interval', $this->sync_interval);
            $conf_form->updateParam('startTime', $this->sync_startTime);
            $conf_form->updateParam('delete_periodicity', $this->sync_delete_periodicity);
            $conf_form->updateParam('delete_period', $this->sync_delete_period);
            $conf_form->updateParam('max_row', $this->sync_max_row);


            $conf_form->saveToFile();

        }


        public function saveDBLongCheckStatusConfig($status)
        {
            $conf_form = new ConfigForm($this->config_params_path . $this->install_db_long);
            $conf_form->updateParam('checked_database_long_status', $status);
            $conf_form->saveToFile();
        }


        public function saveDBLongInstallStatusConfig($status)
        {
            $conf_form = new ConfigForm($this->config_params_path . $this->install_db_long);
            $conf_form->updateParam('install_database_long_status', $status);
            $conf_form->saveToFile();
        }

        public function createDefaultFiles()
        {


            if (!$this->configExists($this->config_params_path . $this->file_db_long_params)) {
                $db_params = new ConfigForm($this->config_params_path . $this->file_db_long_params);
                $db_params->updateParam('username', '');
                $db_params->updateParam('password', '');
                $db_params->updateParam('host', '');
                $db_params->updateParam('port', '');
                $db_params->updateParam('dbname', '');
                $db_params->saveToFile();
            }
            if (!$this->configExists($this->config_params_path . $this->install_db_long)) {
                $db_params = new ConfigForm($this->config_params_path . $this->install_db_long);
                $db_params->updateParam('checked_database_long_status', 0);
                $db_params->updateParam('install_database_long_status', 0);
                $db_params->saveToFile();
            }

            if (!$this->configExists($this->config_params_path . $this->file_database_long_sync_config)) {

                $conf_form = new ConfigForm($this->config_params_path . $this->file_database_long_sync_config);
                $conf_form->updateParam('sync_id', 'delaircoSyncDb');
                $conf_form->updateParam('periodicity', 'minutely');
                $conf_form->updateParam('interval', 1);
                $conf_form->updateParam('startTime', '');
                $conf_form->updateParam('delete_periodicity', 'DAY');
                $conf_form->updateParam('delete_period', 3);
                $conf_form->updateParam('max_row', 3000);

                $conf_form->saveToFile();
            }

            if (!$this->configExists($this->config_params_path .$this->file_mysql_long)) {

                $conf_form = new ConfigForm($this->config_params_path . $this->file_mysql_long);
                $conf_form->updateParam('class','CDbConnection');
                $conf_form->updateParam('connectionString','mysql:host='. $this->host .';port='. $this->port .';dbname='.$this->dbname);
                $conf_form->updateParam('username',$this->user);
                $conf_form->updateParam('password', $this->password);
                $conf_form->updateParam('charset','utf8');
                $conf_form->updateParam('emulatePrepare',true);
                $conf_form->updateParam('enableParamLogging',false);
                $conf_form->updateParam('enableProfiling',false);
                $conf_form->updateParam('persistent',true);
                $conf_form->updateParam('initSQLs',array("set time_zone='+00:00';"));
                $conf_form->saveToFile();
            }
        }






















	}
?>