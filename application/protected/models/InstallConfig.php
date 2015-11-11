<?php

	class InstallConfig extends MainInstall
	{
		/**
		 * @access protected
		 * @var array Values from "ini" config file.
		 */

		// database section
		public $user;
		public $password;
		public $host;
		public $dbname;
		public $port;
        protected $install_db = "install_db.php";

		public static function model($className=__CLASS__) 
		{
			return parent::model($className);
		}    
		
		public function init()
		{
            parent::init();
            $this->createDefaultFiles();

            $values = Yii::app()->params['db_params'];

            if(isset($values['username']))
			    $this->user     = $values['username'];
            if(isset($values['password']))
			    $this->password = $values['password'];
            if(isset($values['host']))
			    $this->host     = $values['host'];
            if(isset($values['dbname']))
                $this->dbname   = $values['dbname'];
            if(isset($values['port']))
                $this->port     = $values['port'];
		}

		public function rules()
		{
			return array(
				array('user,host,dbname,port', 'required', 'on' => 'database'),
				//array('host', 'checkHost', 'on' => 'database'),
				array('host', 'checkHostExists', 'on' => 'database'),
				array('port', 'numerical', 'integerOnly' => true, 'allowEmpty' => false, 'on' => 'database'),
				array('dbname', 'match', 'pattern' => '/^[A-Z,a-z,0-9,_,-]{0,30}$/', 'on' => 'database'),
				array('password', 'length', 'allowEmpty' => true, 'on' => 'database'),
				array('user', 'checkUser', 'on' => 'database'),

				array('php_exe_path', 'required', 'on' => 'path'),
				array('mysqldump_exe_path', 'required', 'on' => 'path'),
				array('mysql_exe_path', 'required', 'on' => 'path'),
				array('site_url_for_console', 'required', 'on' => 'path'),
				array('site_url_for_console', 'url', 'allowEmpty' => false, 'on' => 'path'),

			);
		}


		public function setupDB()
		{
			$dump_file = dirname(Yii::app()->request->scriptFile) .
							DIRECTORY_SEPARATOR .'files'.
							DIRECTORY_SEPARATOR .'install'.
							DIRECTORY_SEPARATOR .'db.sql';

			if (!file_exists($dump_file))
			{
				return array('error' => 'Dump file was not found');
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

			$this->saveDBInstallStatusConfig(1);

			return array('ok' => 1);
		}

		public function attributeLabels()
		{
			return array(
				'user' => Yii::t('project', 'User'),
				'host'  => Yii::t('project', 'Host'),
				'dbname'    => Yii::t('project', 'DB Name'),
				'password'  => Yii::t('project', 'Password'),
				'php_exe_path' => Yii::t('project', 'php.exe path detected'),
				'dbshort' => Yii::t('project', 'DB Short'),
			);
		}

		public function beforeValidate()
		{
			$this->host = trim($this->host);
			$this->user = trim($this->user);
			$this->dbname = trim($this->dbname);
			$this->password = trim($this->password);
			
			return parent::beforeValidate();
		}

		public function afterValidate()
		{
			if ($this->scenario == 'database' && $this->hasErrors())
			{
				$this->saveDBCheckStatusConfig(0);
			}
			
			$this->getAvailableStep();
			$this->writeToMysqlParamsFile();
			return parent::afterValidate();
		}




        public function createDefaultFiles()
        {

            if (!$this->configExists($this->config_params_path .$this->file_db_params)) {
                $db_params = new ConfigForm($this->config_params_path . $this->file_db_params);
                $db_params->updateParam('username', '');
                $db_params->updateParam('password', '');
                $db_params->updateParam('host', '');
                $db_params->updateParam('port', '');
                $db_params->updateParam('dbname', '');
                $db_params->saveToFile();
            }
            if (!$this->configExists($this->config_params_path .$this->install_db)) {
                $db_params = new ConfigForm($this->config_params_path . $this->install_db);
                $db_params->updateParam('checked_database_status', 0);
                $db_params->updateParam('install_database_status', 0);
                $db_params->saveToFile();
            }

            if (!$this->configExists($this->config_params_path .$this->file_mysql)) {
                $mysql = new ConfigForm($this->config_params_path . $this->file_mysql);
                $mysql->updateParam('class', 'CDbConnection');
                $mysql->updateParam('connectionString', 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname);
                $mysql->updateParam('username', $this->user);
                $mysql->updateParam('password', $this->password);
                $mysql->updateParam('charset', 'utf8');
                $mysql->updateParam('emulatePrepare', true);
                $mysql->updateParam('enableParamLogging', false);
                $mysql->updateParam('enableProfiling', false);
                $mysql->updateParam('persistent', true);
                $mysql->updateParam('initSQLs', array("set time_zone='+00:00';"));
                $mysql->saveToFile();
            }
        }








        public function writeToMysqlParamsFile()
        {


            $conf_form = new ConfigForm($this->config_params_path . $this->file_mysql);

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

            $conf_form = new ConfigForm($this->config_params_path . $this->file_db_params);
            $conf_form->updateParam('username',$this->user);
            $conf_form->updateParam('password', $this->password);
            $conf_form->updateParam('host', $this->host);
            $conf_form->updateParam('port', $this->port);
            $conf_form->updateParam('dbname', $this->dbname);
            $conf_form->saveToFile();

        }



        public function writeScheduleConfig()
        {


            $conf_form = new ConfigForm($this->config_params_path . $this->file_schedule_params);
            $conf_form->updateParam('delaircoDbBackup', $this->db_backup_id);
            $conf_form->updateParam('delaircoScheduleScript', $this->each_minute_process_id);
            $conf_form->updateParam('delaircoGetXml', $this->get_xml_process_id);
            $conf_form->updateParam('delaircoCheckProcessesScript', $this->check_processes_process_id);
            $conf_form->updateParam('delaircoPrepareScript', $this->each_minute_prepare_process_id);
            $conf_form->updateParam('delaircoBackupOldDataScript', $this->backup_process_id);

            $conf_form->saveToFile();

        }



        public function saveDBCheckStatusConfig($status)
        {
            $conf_form = new ConfigForm($this->config_params_path . $this->install_db);
            $conf_form->updateParam('checked_database_status', $status);
            $conf_form->saveToFile();
        }


        public function saveDBInstallStatusConfig($status)
        {
            $conf_form = new ConfigForm($this->config_params_path . $this->install_db);
            $conf_form->updateParam('install_database_status', $status);
            $conf_form->saveToFile();
        }

        public function saveDBConfig()
        {
            $this->writeToMysqlParamsFile();
            $this->saveDBInstallStatusConfig(0);
            $this->saveDBCheckStatusConfig(1);
            $this->unsetScheduleCommands();
            $this->saveInstallProcessStatus(0);


        }

    }
?>