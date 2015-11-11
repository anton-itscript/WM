<?php

class MainInstall extends CFormModel
{

    protected $config_params_path;
    protected $install_db_long                      = "install_db_long.php";
    protected $file_install                         = 'install.php';
    protected $file_schedule_params                 = 'schedule_params.php';
    protected $file_application_params              = 'application_params.php';
    protected $file_db_params                       = 'db_params.php';
    protected $file_db_long_params                  = 'db_long_params.php';
    protected $file_mysql                           = 'mysql.php';
    protected $file_mysql_long                      = 'mysql_long.php';
    protected $file_database_long_sync_config       = 'database_long_sync_config.php';



    //install
    public $install_completed;
    public $available_step;
    public $apache_mod_rewrite;
    public $htaccess;

    // path
    public $php_exe_path;
    public $mysqldump_exe_path;
    public $mysql_exe_path;
    public $site_url_for_console;


    // shedule
    public $db_backup_id;
    public $each_minute_process_id;
    public $get_xml_process_id;
    public $check_processes_process_id;
    public $each_minute_prepare_process_id;
    public $backup_process_id;



    public function init()
    {
        $this->config_params_path =  Yii::app()->basePath . DIRECTORY_SEPARATOR .'config' . DIRECTORY_SEPARATOR . 'params' . DIRECTORY_SEPARATOR;
        $this->createDefaultFilesMain();

        $applicationsPaths = $this->getConfigFile('application_params');

        if (empty($applicationsPaths['php_exe_path']) ||
            empty($applicationsPaths['mysqldump_exe_path']) ||
            empty($applicationsPaths['mysql_exe_path']) ||
            empty($applicationsPaths['site_url_for_console']))
        {

            $res = explode(DIRECTORY_SEPARATOR, dirname(php_ini_loaded_file()));
            $basePath = $res[0] . DIRECTORY_SEPARATOR . $res[1];

            if (empty($applicationsPaths['php_exe_path']))
            {
                $value = false;

                if (It::isWindows())
                {
                    $value = $this->findFile('php.exe', $basePath);
                }
                else if (It::isLinux())
                {
                    $value = $this->findFile('php', '/usr/bin/');
                }

                $applicationsPaths['php_exe_path'] = (($value === false) ? null : $value);
            }

            if (empty($applicationsPaths['mysqldump_exe_path']))
            {
                $value = false;

                if (It::isWindows())
                {
                    $value = $this->findFile('mysqldump.exe', $basePath);
                }
                else if (It::isLinux())
                {
                    $value = $this->findFile('mysqldump', '/usr/bin/');
                }

                $applicationsPaths['mysqldump_exe_path'] = $applicationsPaths['mysqldump_exe_path'] = (($value === false) ? null : $value);
            }

            if (empty($applicationsPaths['mysql_exe_path']))
            {
                $value = false;

                if (It::isWindows())
                {
                    $value = $this->findFile('mysql.exe', $basePath);
                }
                else if (It::isLinux())
                {
                    $value = $this->findFile('mysql', '/usr/bin/');
                }

                $applicationsPaths['mysql_exe_path'] = $applicationsPaths['mysql_exe_path'] = (($value === false) ? null : $value);
            }

            if (empty($applicationsPaths['site_url_for_console']))
            {
                $applicationsPaths['site_url_for_console'] = It::baseUrl();
            }

        }

        $this->php_exe_path         = $applicationsPaths['php_exe_path'];
        $this->mysqldump_exe_path   = $applicationsPaths['mysqldump_exe_path'];
        $this->mysql_exe_path       = $applicationsPaths['mysql_exe_path'];
        $this->site_url_for_console = $applicationsPaths['site_url_for_console'];

        $this->writeApplicationsPaths();

        $this->db_backup_id                   = 'delaircoDbBackup';
        $this->each_minute_process_id         = 'delaircoScheduleScript';
        $this->get_xml_process_id             = 'delaircoGetXml';
        $this->check_processes_process_id     = 'delaircoCheckProcessesScript';
        $this->each_minute_prepare_process_id = 'delaircoPrepareScript';
        $this->backup_process_id              = 'delaircoBackupOldDataScript';





        $values = $this->getConfigFile('install');

        if (isset($values['install_status']))
            $this->install_completed = $values['install_status'];
        else
            $this->install_completed = 0;
        //$this->apache_mod_rewrite = (apache_get_modules('mod_rewrite')) ? 1 : 0;
        $this->apache_mod_rewrite =  1 ;

        $this->htaccess = (file_exists(dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR.'.htaccess') ? 1 : 0);

        $this->getAvailableStep();



        return parent::init();
    }


    /**
     * Recursievly searches file.
     *
     * @param string $fileName
     * @param string $basePath Root dir for search. Ignored in Linux.
     * @return false|string Path to found file.
     */
    protected function findFile($fileName, $basePath)
    {
        if (It::isWindows())
        {
            $path = $basePath . DIRECTORY_SEPARATOR . $fileName;

            $output = null;
            $return = null;

            exec('dir '. $path .' /s/b', $output, $return);

            if (($return === 0) && (count($output) > 0))
            {
                return $output[0];
            }
        }
        else if (It::isLinux())
        {
            $return = null;
            $output = null;

            exec('find '. $basePath .' -name '. $fileName, $output, $return);

            return (($return === 0) ? $output[0] : false);
        }

        return false;
    }


    public function checkUser()
    {
        if ($this->hasErrors('host') || $this->hasErrors('user') || $this->hasErrors('password') || $this->hasErrors('port'))
        {
            return false;
        }

        try
        {
            $connection = new CDbConnection('mysql:host='. $this->host, $this->user, $this->password);
            $connection->setActive(true);

            $sql = "DROP DATABASE IF EXISTS `{$this->dbname}`;";
            $connection->createCommand($sql)->query();

            $sql = "CREATE DATABASE `{$this->dbname}` CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $connection->createCommand($sql)->query();
        }
        catch (CDbException $e)
        {
            $this->addError('user', $e->getMessage());

            return false;
        }

        return true;
    }

    public function checkHost()
    {
        if (!$this->hasErrors('host'))
        {
            if ($this->host == 'localhost')
            {
                return true;
            }

            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->host))
            {
                return true;
            }

            if (preg_match('/^[a-zA-Z-\d]+(?:\.[a-zA-Z-\d]+)$/', $this->host))
            {
                return true;
            }

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


    /**
     * Function for windows to retreive environment variable value.
     *
     * @param string $name environment variable name.
     * @return false|string False if environment variable is not found, it's value - otherwise.
     */
    public function getEnvironmentVariable($name)
    {
        if (It::isWindows())
        {
            $output = null;

            exec('wmic environment where Name="'. $name .'" get Name,VariableValue /format:csv', $output);

            // strip header
            $output = array_slice($output, 2);

            if (count($output) > 0)
            {
                $lines = explode(',', $output[0]);

                if ((count($lines) > 2) && ($lines[1] == $name))
                {
                    return $lines[2];
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Creates or updates environment variable.
     *
     * @param string $name
     * @param string $value
     * @return boolean True on successful creation.
     */
    public function setEnvironmentVariable($name, $value)
    {
        if (It::isWindows())
        {
            $output = null;
            $return = null;

            $variableValue = $this->getEnvironmentVariable($name);

            if ($variableValue === false)
            {
                exec('wmic environment create Name="'. $name .'",VariableValue="'. $value .'",Username="%username%"', $output, $return);

                return ($return === 0);
            }
            else
            {
                exec('wmic environment where Name="'. $name .'" set VariableValue="'. $value .'"', $output, $return);

                return (count($output) === 2);
            }
        }

        return false;
    }

    /**
     * Deletes environment variable.
     *
     * @param string $name
     * @return boolean True on success.
     */
    public function deleteEnvironmentVariable($name)
    {
        if (It::isWindows())
        {
            $output = null;
            $return = null;

            exec('wmic environment where Name="'. $name .'" delete', $output, $return);

            return (($return === 0) && (count($output) === 2));
        }

        return false;
    }

    /**
     * Check whether scheduled task exists or not.
     *
     * @access protected
     * @param string $name Task name
     * @return boolean True is task exists.
     */
    public static function checkTask($name)
    {
        $output = null;

        if (It::isLinux())
        {
            exec('crontab -l | grep -i '. $name, $output);

            return (is_array($output) && (count($output) > 0));
        }
        else if (It::isWindows())
        {
            exec('schtasks /query /nh /fo:CSV', $output);

            // strip header
            $output = array_slice($output, 1);

            if (count($output) > 0)
            {
                foreach ($output as $csvLine)
                {
                    $lines = explode(',', $csvLine);

                    if ((count($lines) > 1) && ($lines[0] == '"'. $name .'"'))
                    {
                        return true;
                    }
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Creates scheduled task.
     *
     * @access protected
     * @param string $name Task name.
     * @param string $command Coomand line string to run in task.
     * @param string $periodicity Possible values: minutely, hourly, daily, monthly, weekly ...
     * @param string $interval Task interval (once an minute, once in 5 hours, etc).
     * @param string $startTime Exact time of start for smae periodicities.
     */
    public static function createTask($name, $command, $periodicity, $interval = 1, $startTime = null)
    {
        if (It::isLinux())
        {
            // Build start-up time-line fron crontab
            $cronTimes = '';

            $startHour = 0;
            $startMinute = 0;
            $matches = array();

            if (!is_null($startTime) && (preg_match('/^([0-9]+):([0-9]+)$/', $startTime, $matches) > 0))
            {
                $startHour = (int)$matches[1];
                $startMinute = (int)$matches[2];
            }

            switch ($periodicity)
            {
                case 'minutely':

                    if ($startMinute === 0)
                    {
                        $cronTimes = ($interval > 1 ? '*/'. $interval : '*') .' * * * *';
                    }
                    else
                    {
                        $cronTimes = $startMinute .'-59'. ($interval > 1 ? '/'. $interval : '') .' * * * *';
                    }

                    break;

                case 'hourly':

                    $cronTimes = $startMinute .' ' . ($interval > 1 ? '*/'. $interval : '*') .' * * *';
                    break;

                case 'daily':

                    $cronTimes = $startMinute .' '. $startHour .' '. ($interval > 1 ? '*/'. $interval : '*') .' * *';
                    break;

                case 'monthly':

                    $cronTimes = $startMinute .' '. $startHour .' 1 ' . ($interval > 1 ? '*/'. $interval : '*') .' *';
                    break;

                case 'weekly':

                    $cronTimes = $startMinute .' '. $startHour .' * * ' . ($interval > 1 ? '*/'. $interval : '*');
                    break;
            }

            exec('crontab -l | { cat; echo "'. $cronTimes .' '. $command .' #'. $name .'"; } | crontab -');
        }
        else if (It::isWindows())
        {
            exec('schtasks /create /sc '. $periodicity .' /mo '. $interval . (is_null($startTime) ? '' : ' /st '. $startTime) .' /ru "SYSTEM" /tn '. $name .' /tr "'. $command .'"');
        }
    }

    /**
     * Deletes task by name.
     *
     * @param string $name
     */
    public static function deleteTask($name)
    {
        if (It::isLinux())
        {
            exec('crontab -l | grep -v '. $name .' | crontab -');
        }
        else if (It::isWindows())
        {
            exec('schtasks /delete /tn '. $name .' /f');
        }
    }





    public function savePathConfig()
    {

        $this->writeApplicationsPaths();
        $this->unsetScheduleCommands();
        $this->saveInstallProcessStatus(0);

    }

    public function saveScheduleConfig()
    {
        $this->saveInstallProcessStatus(1);

    }

    public function unsetScheduleCommands()
    {
        if ($this->checkTask($this->db_backup_id))
        {
            $this->deleteTask($this->db_backup_id);
        }

        if ($this->checkTask($this->each_minute_process_id))
        {
            $this->deleteTask($this->each_minute_process_id);
        }

        if ($this->checkTask($this->get_xml_process_id))
        {
            $this->deleteTask($this->get_xml_process_id);
        }

        if ($this->checkTask($this->check_processes_process_id))
        {
            $this->deleteTask($this->check_processes_process_id);
        }

        if ($this->checkTask($this->each_minute_prepare_process_id))
        {
            $this->deleteTask($this->each_minute_prepare_process_id);
        }

        if ($this->checkTask($this->backup_process_id))
        {
            $this->deleteTask($this->backup_process_id);
        }
    }

    public function setSchedule()
    {
        $this->unsetScheduleCommands();

        if ($this->checkTask($this->db_backup_id) === false)
        {
            $backup_path = Yii::app()->params['backups_path'] .
                DIRECTORY_SEPARATOR . (It::isLinux() ? '`echo "$""(date +\%a)"`' : '%DATE:~0,3%') .'.sql';

            // Schedule daily database backup
            $command = $this->mysqldump_exe_path .
                ' --user="'. $this->user .'"'.
                ' --password="'. $this->password .'"'.
                ' --result-file="'. $backup_path .'" '.
                $this->dbname;

            $this->createTask($this->db_backup_id, $command, 'daily', 1, '2:00');
        }

        if ($this->checkTask($this->each_minute_process_id) === false)
        {
            // Schedule report generation
            $command = Yii::app()->params['applications']['php_exe_path'] . " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php schedule";

            $this->createTask($this->each_minute_process_id, $command, 'minutely');
//				$this->createTask($this->each_minute_process_id, $command, 'minutely', 15, '00:02');
        }

        if ($this->checkTask($this->get_xml_process_id) === false)
        {
            // Schedule XML messages grabbing
            $command = Yii::app()->params['applications']['php_exe_path'] . " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php getxml";

            $this->createTask($this->get_xml_process_id, $command, 'minutely');
        }

        if ($this->checkTask($this->check_processes_process_id) === false)
        {
            // Schedule check listening process
            $command = Yii::app()->params['applications']['php_exe_path']. " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php checkprocesses";

            $this->createTask($this->check_processes_process_id, $command, 'minutely');
        }

        if ($this->checkTask($this->each_minute_prepare_process_id) === false)
        {
            // Schedule prepare message script
            $command = Yii::app()->params['applications']['php_exe_path'] . " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php prepare";

            $this->createTask($this->each_minute_prepare_process_id, $command, 'minutely');
        }

        if ($this->checkTask($this->backup_process_id) === false)
        {
            // Schedule backup old data
            $command = Yii::app()->params['applications']['php_exe_path'] . " -f  ". dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR. "console.php backupOldData";

            $this->createTask($this->backup_process_id, $command, 'monthly');
        }

        $this->saveScheduleConfig();
    }



    public function writeApplicationsPaths()
    {

        $conf_form = new ConfigForm($this->config_params_path . $this->file_application_params);
        $conf_form->updateParam('php_exe_path', $this->php_exe_path);
        $conf_form->updateParam('mysqldump_exe_path', $this->mysqldump_exe_path);
        $conf_form->updateParam('mysql_exe_path', $this->mysql_exe_path);
        $conf_form->updateParam('site_url_for_console', $this->site_url_for_console);
        $conf_form->updateParam('console_app_path', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www'
            . DIRECTORY_SEPARATOR . 'console.php');
        $conf_form->updateParam('backups_path', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www'
            . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'backups');

        $conf_form->saveToFile();

    }




    public function createDefaultFilesMain()
    {

        if (!is_file($this->config_params_path .$this->file_install)) {
            $install = new ConfigForm($this->config_params_path .$this->file_install);
            $install->updateParam('install_status', 0);
            $install->saveToFile();
        }

        if (!is_file($this->config_params_path .$this->file_schedule_params)) {
            $schedule_params = new ConfigForm($this->config_params_path . $this->file_schedule_params);
            $schedule_params->updateParam('delaircoDbBackup', '');
            $schedule_params->updateParam('delaircoScheduleScript', '');
            $schedule_params->updateParam('delaircoGetXml', '');
            $schedule_params->updateParam('delaircoCheckProcessesScript', '');
            $schedule_params->updateParam('delaircoPrepareScript', '');
            $schedule_params->updateParam('delaircoBackupOldDataScript', '');
            $schedule_params->saveToFile();
        }

        if (!is_file($this->config_params_path .$this->file_application_params)) {
            $application_params = new ConfigForm($this->config_params_path . $this->file_application_params);
            $application_params->updateParam('php_exe_path', '');
            $application_params->updateParam('mysqldump_exe_path', '');
            $application_params->updateParam('mysql_exe_path', '');
            $application_params->updateParam('site_url_for_console', '');
            $application_params->updateParam('console_app_path', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www'
                . DIRECTORY_SEPARATOR . 'console.php');
            $application_params->updateParam('backups_path', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www'
                . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'backups');
            $application_params->saveToFile();
        }

        if (!is_file($this->config_params_path .$this->file_db_params)) {
            $db_params = new ConfigForm($this->config_params_path . $this->file_db_params);
            $db_params->updateParam('username', '');
            $db_params->updateParam('password', '');
            $db_params->updateParam('host', '');
            $db_params->updateParam('port', '');
            $db_params->updateParam('dbname', '');
            $db_params->saveToFile();
        }


        if (!is_file($this->config_params_path .$this->file_mysql)) {
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



    public function saveInstallProcessStatus($status)
    {
        $conf_form = new ConfigForm($this->config_params_path . $this->file_install);
        $conf_form->updateParam('install_status', $status);
        $conf_form->saveToFile();
    }

    public function getAvailableStep()
    {
        $this->saveInstallProcessStatus(0);

        $this->available_step = 1;
        $installStatus = $this->getConfigFile('install');

        $installStatus_db = $this->getConfigFile('install_db');
        $installStatus_db_long = $this->getConfigFile('install_db_long');

        if ($this->apache_mod_rewrite && $this->htaccess)
        {
            if (isset($installStatus_db['checked_database_status']) && $installStatus_db['checked_database_status']==1)
                $check_database_status = 1;
            else
                $check_database_status = 0;

            if (isset($installStatus_db_long['checked_database_long_status']) && $installStatus_db_long['checked_database_long_status']==1)
                $check_database_long_status = 1;
            else
                $check_database_long_status = 0;


            if ($check_database_status and $check_database_long_status)
            {
                $this->available_step = 2;
            }
        }

        //step3 = setup database from dump
        if ($this->available_step == 2)
        {
            $install_database_status = $installStatus_db['install_database_status'];
            $install_database_status_long = $installStatus_db_long['install_database_long_status'];

            if ($install_database_status and $install_database_status_long)
            {
                $this->available_step = 3;
            }
        }

        if ($this->available_step == 3)
        {
            $this->available_step = 4;
        }

        if ($this->available_step == 4)
        {
            if ($this->checkTask($this->db_backup_id) &&
                $this->checkTask($this->each_minute_process_id) &&
                $this->checkTask($this->get_xml_process_id) &&
                $this->checkTask($this->check_processes_process_id) &&
                $this->checkTask($this->each_minute_prepare_process_id) &&
                $this->checkTask($this->backup_process_id)
                && $this->checkTask(Yii::app()->params['db_long_sync_config']['sync_id'])
            )
            {
                $this->available_step = 5;
                $this->saveInstallProcessStatus(1);
            }
        }
    }



    public function getConfigFile($fileName)
    {
        $fileArray = array();
        if (is_file($this->config_params_path . $fileName . '.php')) {
            $fileArray = require($this->config_params_path . $fileName . '.php');
        }

        if (is_scalar($fileArray)) {
            file_put_contents($this->config_params_path . $fileName . '.php',"<?php \n return array()  \n ?>");
            $fileArray = require($this->config_params_path . $fileName . '.php');
        }
        return $fileArray;
    }




    protected function configExists($fullFileName)
    {
        if (is_file($fullFileName)) {
            $array = require($fullFileName);
            if (is_array($array) and count($array)>0)
                return true;
        }

        return false;
    }
}
?>