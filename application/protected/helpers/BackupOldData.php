<?php

/*
 *
 * This class contains functionality to process data backuping.
 * Admin can setup params of backuping in "Admin / Setup / DB Export"
 * The main idea of this process - is to move old data into another database with identical structure
 * Backup database can be located at any server
 * 
 * Params of backuping are:
 * - "Minimum period with data" - this is period of time how long sensors data 
 * can be stored in active database before moving to backup database.
 * - "Export frequency" - how often old data should be moved to backup database
 * 
 * TODO in future: create transactions (Yii supports work with them)
 */

class BackupOldData {
    
	// general settings from database
    var $settings;
	
	// connection with backup database
    var $db_conn;
	
	// information about current backup
    var $current_backup;
	
	/**
	 * runs backup process:
	 * gets last backup info to identify if need to run new backup or continue precious
	 * runs "makeBackup()"
	 * 
	 * @return type 
	 */
    public function run() {
        TimezoneWork::set('UTC');

        It::debug("\n\n== STARTED MOVE DATABASE TO BACKUP ==", 'backup_database');
        $this->settings = Settings::model()->findByPk(1);
        
		/**
         * check if backup process is enabled */
        if (!$this->settings->db_exp_enabled) {
            It::debug("disabled", 'backup_database');
            return;
        } else {
            It::debug("enabled", 'backup_database');
        }

        /**
         * check time */
        $now = time();
        // Here we check if we should make backup
        $last_backup = BackupOldDataTx::getLastBackupInfo();
        // calculate date of the next backup
        $next_planned = strtotime($last_backup->backup_date) + ($this->settings->db_exp_frequency * 86400);
        // exit if time not yet or prew backup not completed
        if($next_planned >= $now and $last_backup->completed == 1){
            It::debug("not time yet", 'backup_database');
            return;
        }

        /**
         * create new transaction */
        It::debug("create new transaction", 'backup_database');
        $last_backup = new BackupOldDataTx();
        $last_backup->backup_date = new CDbExpression('NOW()');
        $last_backup->data_timestamp_limit = date('Y-m-d 00:00:00', $now - $this->settings->db_exp_period * 86400);
        $last_backup->save();
        $this->current_backup = $last_backup;

        /**
         * add log */
        $this->addBackupLog("Need to Move Data stored before ".$last_backup->data_timestamp_limit.'; Date of backup: '.$last_backup->backup_date.'; Frequency = '.$this->settings->db_exp_frequency.'; ');
        It::debug("NOW = ".$now." = ".date('Y-m-d H:i:s', $now), 'backup_database');
        It::debug("last backup (backup_frequency) = ".$this->settings->db_exp_frequency, 'backup_database');
        It::debug("last backup (id) = ".$last_backup->id, 'backup_database');
        It::debug("last backup (date of backup) = ".$last_backup->backup_date, 'backup_database');
        It::debug("last backup (data timestamp limit) = ".$last_backup->data_timestamp_limit, 'backup_database');
        It::debug("last backup (completed) = ".$last_backup->completed, 'backup_database');       

        /**
         * make backup*/
        $this->makeBackup();//TODO: off delete row
    }

	/**
	 * 
	 * Backup process:
	 * - initiates connection with backup datatbase
	 * - if backup database is empty - creates whole backup
	 * - else updates backup database and moves old data
	 * 
	 * @return type 
	 */
    public function makeBackup() {
        
        It::debug("start", 'backup_database');

        try {
            $res = $this->initDbConnection();
        } catch (Exception $e) {
            $this->addBackupLog("cannot init DB Connection, ".$e->getMessage());
            It::debug($e->getMessage(), 'backup_database');
            return;
        }
        
		// for backup database: set the same timezone as we have at actual database
        $this->db_conn->createCommand("SET time_zone='".TimezoneWork::getOffsetFromUTC('UTC',1)."'")->query();        
        
        $limit_timestamp = $this->current_backup->data_timestamp_limit;
        
        // check if database doesn't exist
        $version = $this->backupDBVersion();
//        $this->addBackupLog("Backup database version: ".$version."; Actual database version: ".getConfigValue('version_name'));
        $this->addBackupLog("Backup database version: ; Actual database version: ");

        if ($version === false) {

            $this->addBackupLog("There are no tables yet. Going to copy current whole DB");
            It::debug("backup database structure is not ready. Going to copy current whole DB", 'backup_database');
            
            // create whole backup
            $cmd = Yii::app()->params['applications']['mysqldump_exe_path'].
                   ' --host="'.Yii::app()->params['db_params']['host'].'" --quick ' .
                   ' --port="'.Yii::app()->params['db_params']['port'].'"'  .
                   ' --user="'.Yii::app()->params['db_params']['username'].'"'  .
                   ' --password="'.Yii::app()->params['db_params']['password'].'"  '  .
                Yii::app()->params['db_params']['dbname'] . " | " . Yii::app()->params['applications']['mysqldump_exe_path'] .
                   ' --host="'.$this->settings->db_exp_sql_host.'"' .
                   ' --port="'.$this->settings->db_exp_sql_port.'"' .
                   ' --user="'.$this->settings->db_exp_sql_login.'"' .
                   ' --password="'.$this->settings->db_exp_sql_password.'" '.
                   $this->settings->db_exp_sql_dbname ;

            try {
                $this->addBackupLog("Command to execute: ".$cmd);
                exec($cmd, $output, $return); 
                $this->addBackupLog("Command has been executed. Whole db was copied to backup_db");
            } catch (Exception $e) {
                $this->addBackupLog("Command has not been executed. Cannot make whole backup");
                It::debug($e->getMessage(), 'backup_database');
                return;                
            }

            // check if database doesn't exist
            $version = $this->backupDBVersion();
//            $this->addBackupLog("Again check Backup database version: ".$version."; Actual database version: ".getConfigValue('version_name'));
            $this->addBackupLog("Again check Backup database version: ; Actual database version: ");
            if ($version !== false) {
				
				// remove old data from actual database
                $res = $this->removeDataBeforeDate($limit_timestamp);
                
				// mark current backup process as completed
                $this->current_backup->completed = '1';
                $this->current_backup->save(); 
            } else {
                $this->addBackupLog("Is false again!");
            }
            
                      
            
        } else {
            
            // apply all previous updates to that database
            $res = $this->applyVersionChanges($version);
            
            if ($res === FALSE) {
                $this->addBackupLog("Data was not moved. Process stopped!");
                return;
            }

            // copy data from actual DB to backup DB before some timestamp
            $this->copyDataBetweenDatabases($limit_timestamp);
        }

        
        $this->addBackupLog("Done");
        It::debug("\n\n== COMPLETED ==", 'backup_database');
    }
    
	/**
	 * Function applies all database changes starting from version of backuped database
	 * and up to version of actual database. It uses /models/UpdateScriptForm.php model 
	 *
	 * we assume that version number should be: X.YY.ZZ
	 * where X - stage number, YY - sprint number, ZZ - update number
	 * 
	 * @param type $backup_version - version of backup database
	 * @return type 
	 */
    private function applyVersionChanges($backup_version) {
        
        $this->addBackupLog("Check if need to applyVersionChanges");
        $actual_version = getConfigValue('version_name');
        
        $backup_version_str = str_replace('.', '', $backup_version);
        $actual_version_str = str_replace('.', '', $actual_version);
        
        $backup_versions = explode('.', $backup_version);
        $actual_versions = explode('.', $actual_version);
        
        $update_methods = array();
        
        
        if ($backup_version_str < $actual_version_str) {
            
            $this->addBackupLog("Need to update backup_database structure");
            $form = new UpdateScriptForm();
            
            for ($num_stage = $backup_versions[0]; $num_stage <= $actual_versions[0]; $num_stage++) {
                
                for ($num_sprint = $backup_versions[1]; $num_sprint <= $actual_versions[1]; $num_sprint++) {
                    
                    $max_update = ($num_sprint == $actual_versions[1]) ? $actual_versions[2] : 30;
                     
                    $start_update = ($num_sprint == $backup_versions[1]) ? ($backup_versions[2]+1) : 1;
                    
                    for ($num_update = $start_update; $num_update <= $max_update; $num_update++) {
                        
                        $method_name = 'm_'.$num_stage.'_'.str_pad($num_sprint, 2, '0', STR_PAD_LEFT).'_'.str_pad($num_update, 2, '0', STR_PAD_LEFT);
                        if (method_exists($form, $method_name)) {
                           $update_methods[] = $method_name;
                           $this->addBackupLog("Can apply update ".$method_name);
                        }                        
                    }
                }
            }
            
            if ($update_methods) {
                foreach ($update_methods as $key => $value) {
                    $this->addBackupLog('Try to apply update '.$value.' to remote database');
                    try {
                        $form->$value($this->db_conn);
                    } catch (Exception $e) {
                        $this->addBackupLog('Failed to apply update '.$value.' to remote database. '.$e->getMessage());
                        return false;                
                    }
                }
            }
        } else {
            $this->addBackupLog("Don not need to update backup_database structure");
        }        
        return true;
    }
    
	
	/**
	 * Remove all old sensors data from actual database
	 * @param type $timestamp 
	 */
    private function removeDataBeforeDate($timestamp) {
        return;//TODO: because of LONG DB
        $this->addBackupLog("DELETE all records before ".$timestamp);
        It::debug("DELETE all records before ".$timestamp, 'backup_database');
        
		// delete from `listener`
        $sql = "SELECT listener_id FROM `".Listener::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
        $res = Yii::app()->db->createCommand($sql)->queryColumn();
        It::debug("DELETE ".count($res)." from ".Listener::model()->tableName(), 'backup_database');
        $this->addBackupLog("DELETE ".count($res)." from ".Listener::model()->tableName());
        if ($res) {
            $sql = "DELETE FROM `".Listener::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
            $res = Yii::app()->db->createCommand($sql)->query();
        }
        
		// delete from `listener_log`
        $sql = "SELECT log_id FROM `".ListenerLog::model()->tableName()."` WHERE `created` <= '".$timestamp."' ";
        $res = Yii::app()->db->createCommand($sql)->queryColumn();
        It::debug("DELETE ".count($res)." from ".ListenerLog::model()->tableName(), 'backup_database');
        $this->addBackupLog("DELETE ".count($res)." from ".ListenerLog::model()->tableName());
        if ($res) {        
            $sql = "DELETE FROM `".ListenerLog::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
            $res = Yii::app()->db->createCommand($sql)->query();
        }
        
		// delete from `schedule_processed`
        $sql = "SELECT `schedule_processed_id` FROM `".ScheduleReportProcessed::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
        $res = Yii::app()->db->createCommand($sql)->queryColumn();
        It::debug("DELETE ".count($res)." from ".ScheduleReportProcessed::model()->tableName(), 'backup_database');
        $this->addBackupLog("DELETE ".count($res)." from ".ScheduleReportProcessed::model()->tableName());
        if ($res) {         
            $sql = "DELETE FROM `".ScheduleReportProcessed::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
            $res = Yii::app()->db->createCommand($sql)->query();       
        }
        
		// delete from `xml_log`
        $sql = "SELECT `xml_log_id` FROM `".XmlLog::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
        $res = Yii::app()->db->createCommand($sql)->queryColumn();
        It::debug("DELETE ".count($res)." from ".XmlLog::model()->tableName(), 'backup_database');
        $this->addBackupLog("DELETE ".count($res)." from ".XmlLog::model()->tableName());
        if ($res) {          
            $sql = "DELETE FROM `".XmlLog::model()->tableName()."` WHERE `created` <= '".$timestamp."'";
            $res = Yii::app()->db->createCommand($sql)->query();            
        }
    }
    
	/*
	 * remove particular data from actual database
	 */
    private function removeDataByIds($table, $ids) {
        return;//TODO: because of LONG DB
        $this->addBackupLog("DELETE ".count($ids)." records by IDs from ".$table);
        if ($table == 'listener') {
            
            $sql = "DELETE FROM `".Listener::model()->tableName()."` WHERE `listener_id` IN (".implode(',', $ids).")";
            $res = Yii::app()->db->createCommand($sql)->query();            
        }
        
        if ($table == 'listener_log') {
            $sql = "DELETE FROM `".ListenerLog::model()->tableName()."` WHERE `log_id` IN (".implode(',', $ids).")";
            $res = Yii::app()->db->createCommand($sql)->query();                
        }
    }
    

	/**
	 * Init connection with backup database
	 */
    private function initDbConnection() {
        It::debug("try to init DB connection (0)", 'backup_database');
        try {
            It::debug("try to init DB connection (1)", 'backup_database');
            
			// check if (remote) host is reachable
            $fp = @fsockopen($this->settings->db_exp_sql_host, $this->settings->db_exp_sql_port);
            if($fp === false) {
                throw new Exception("Host ".$this->settings->db_exp_sql_host.':'.$this->settings->db_exp_sql_port.' is unreachable');            
            }            
            
			// create connection with database
            $dsn = 'mysql:host='.$this->settings->db_exp_sql_host.';port='.$this->settings->db_exp_sql_port.';dbname='.$this->settings->db_exp_sql_dbname;
            $connection = new CDbConnection($dsn, $this->settings->db_exp_sql_login, $this->settings->db_exp_sql_password);
            It::debug("try to init DB connection (2)", 'backup_database');

            try {
                It::debug("try to init DB connection (3)", 'backup_database');
                $res = $connection->setActive(true);
                It::debug("try to init DB connection (4)", 'backup_database');
                $this->db_conn = $connection;
                It::debug("try to init DB connection (5)", 'backup_database');

            } catch (PDOException $e) {
                
                It::debug("try to init DB connection (6)", 'backup_database');
                throw new Exception($e->getMessage());
            }

            It::debug("try to init DB connection (7)", 'backup_database');
        } catch (CDbException $e) {
            It::debug("try to init DB connection (8)", 'backup_database');
            throw new Exception($e->getMessage());
        }           
        It::debug("try to init DB connection (DONE)", 'backup_database');
    }
    
	
	/**
	 * get version of backup database (this is test for database existing)
	 * @return type 
	 */
    private function backupDBVersion() {
        
        $sql = "SHOW TABLES LIKE 'settings';";
        $res = $this->db_conn->createCommand($sql)->queryAll(); 

        if (count($res)) {
            $this->addBackupLog("Table `settings` has been found at backup_db");
            $sql = "SHOW COLUMNS FROM `settings` LIKE 'database_version'";
            $res2 = $this->db_conn->createCommand($sql)->queryAll(); 
            if ($res2) {
                $this->addBackupLog("Field `settings`.`database_version` has been found at backup_db");
                
                $sql = "SELECT `database_version` FROM `settings` LIMIT 1";
                $backup_version = $this->db_conn->createCommand($sql)->queryScalar();
                
                if ($backup_version == '') return false;
                
                return $backup_version;
            } else {
                $this->addBackupLog("Field `settings`.`database_version` has NOT been found at backup_db");
            }
        } else {
            $this->addBackupLog("Table `settings` has not been found at backup_db");
        }
        return false;
    }    
    
    
	/**
	 * Update general tables in backup database.
	 * Actual Data in these tables are required to support integrity of database and store sensors values
	 * @return string 
	 */
    private function updateMainStationInformation() {
        
        
        $tables = array(
            Station::model()->tableName(),
            Settings::model()->tableName(),
            StationSensor::model()->tableName(),
            StationSensorFeature::model()->tableName(),
            StationCalculation::model()->tableName(),
            StationCalculationVariable::model()->tableName());
        
        $result_sql = array();
        
        foreach ($tables as $table) {
            $sql = "SELECT * FROM `".$table."`";
            $res = Yii::app()->db->createCommand($sql)->queryAll();
            $total = count($res);

            It::debug("updateMainStationInformation: Table = ".$table.", TOTAL = ".$total, 'backup_database');
            if ($res) {

                $fields = array();
                foreach ($res[0] as $key2 => $value2) {
                    $fields[] = $key2;
                }            

                $sql_header = "INSERT IGNORE INTO `".$table."` (`".implode('`,`', $fields)."`) VALUES ";

                $res_sql = $sql_header;
                foreach ($res as $key => $value) {

                    $res_sql.= "('".implode("','", $value)."')";

                    if ($key+1 < $total)  $res_sql .= ", ";
                }
                $result_sql[] = $res_sql;
            }
        }
        It::debug("updateMainStationInformation: DONE", 'backup_database');
        return $result_sql;
    }
    
    
	/**
	 * Copy old data from actula database to backup database
	 * 
	 * @param type $limit_timestamp 
	 */
    private function copyDataBetweenDatabases($limit_timestamp) {
        
        $completed = true;
        $refresh_data = $this->updateMainStationInformation();
        if ($refresh_data) {
            $this->addBackupLog("Refresh info about stations, sensors, features.");
            foreach ($refresh_data as $sql) {
                $res = $this->db_conn->createCommand($sql)->query();
            }
        }            
        
        // table 'listener_log' dependances
        $res = $this->prepareListenerLogInserts($limit_timestamp);
        $listener_log_ids = $res[0];
        $inserts_listener_log = $res[1];
        
        if (count($listener_log_ids)) {
            $inserts_listener_log_dependant = $this->prepareListenerLogDependantInserts($listener_log_ids);
            $completed = false;
        }
        
        // table 'listener' dependances
        $res = $this->prepareListenerInserts($limit_timestamp);
        $listener_ids = $res[0];
        $inserts_listener = $res[1];  
        if (count($listener_ids)) {
            $inserts_listener_dependant = $this->prepareListenerDependantInserts($listener_ids);
            $completed = false;
        }
        
        
        if ($inserts_listener_log) {
            $res = $this->db_conn->createCommand($inserts_listener_log)->query();
            $completed = false;
        }
           
        if ($inserts_listener_log_dependant) {
            foreach ($inserts_listener_log_dependant as $sql) {
                $res = $this->db_conn->createCommand($sql)->query();
            }
            $completed = false;
        }
        
        if ($inserts_listener) {
            $res = $this->db_conn->createCommand($inserts_listener)->query();
        }
        
        if ($inserts_listener_dependant) {
            foreach ($inserts_listener_dependant as $sql) {
                $res = $this->db_conn->createCommand($sql)->query();
            }
        }   
        
        if ($listener_ids) {
            $this->removeDataByIds('listener', $listener_ids);
        }
        if ($listener_log_ids) {
            $this->removeDataByIds('listener_log', $listener_log_ids);
        }        
        if (count($listener_ids) >= 50 || count($listener_log_ids) >= 50) {
            $completed = false;
        }
        
        if ($completed) {
            $this->addBackupLog("Backup for this date is completed.");
            It::debug("copyDataBetweenDatabases (19)", 'backup_database');
            $res = $this->removeDataBeforeDate($limit_timestamp);
            It::debug("copyDataBetweenDatabases (20)", 'backup_database');
            $this->current_backup->completed = '1';
            $this->current_backup->save();               
        } else {
            $this->addBackupLog("Backup for this date is not completed yet. Continue in a few minutes.");
        }
    }
    
    
    
    
    
    // Table ListenerLog
    private function prepareListenerLogInserts($limit_timestamp) {
        
        $result_sql = "";
        $sql = "SELECT * 
                FROM `".ListenerLog::model()->tableName()."`
                WHERE `created` <= '".$limit_timestamp."' 
                LIMIT 0, 50";
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        $total = count($res);
        
        $ids = array();
        
        if ($res) {
            
            $fields = array();
            foreach ($res[0] as $key2 => $value2) {
                $fields[] = $key2;
            }            

            $sql_header = "INSERT IGNORE INTO `".ListenerLog::model()->tableName()."` (`".implode('`,`', $fields)."`) VALUES ";
            
            $result_sql = $sql_header;
            foreach ($res as $key => $value) {

                $ids[] = $value['log_id'];
                $result_sql.= "('".implode("','", $value)."')";
                
                if ($key+1 < $total) $result_sql .= ", ";
            }
            
            $this->addBackupLog(count($ids)." inserts for ListenerLog");
        }
        return array($ids, $result_sql);
    }
    
    
    // Tables Dependend on ListenerLog
    private function prepareListenerLogDependantInserts($listener_log_ids) {
        
        $tables = array(
            ListenerLogProcessError::model()->tableName() => 'log_id',
            SensorData::model()->tableName()              => 'listener_log_id',
            SensorDataMinute::model()->tableName()        => 'listener_log_id',
            SeaLevelTrend::model()->tableName()           => 'log_id',
            StationCalculationData::model()->tableName()  => 'listener_log_id'
        );
        
        $result_sql = array();
        
        foreach ($tables as $table => $log_id_field) {
            $sql = "SELECT * 
                    FROM `".$table."`
                    WHERE `".$log_id_field."` IN (".implode(',',$listener_log_ids).")";
            $res = Yii::app()->db->createCommand($sql)->queryAll();
            $total = count($res);

            if ($res) {

                $fields = array();
                foreach ($res[0] as $key2 => $value2) {
                    $fields[] = $key2;
                }            

                $sql_header = "INSERT IGNORE INTO `".$table."` (`".implode('`,`', $fields)."`) VALUES ";

                $res_sql = $sql_header;
                
                $cnt = 0;
                foreach ($res as $key => $value) {

                    $res_sql.= "('".implode("','", $value)."')";

                    if ($key+1 < $total)  $res_sql .= ", ";
                    
                    $cnt++;
                }
                $result_sql[] = $res_sql;
                
                $this->addBackupLog("Prepared ".$cnt." inserts for ".$table);
            }
        }
        return $result_sql;
    } 
    
    // Table Listener
    private function prepareListenerInserts($limit_timestamp) {
        $result_sql = "";
        $sql = "SELECT * 
                FROM `".Listener::model()->tableName()."`
                WHERE `created` <= '".$limit_timestamp."' 
                LIMIT 0, 50";
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        $total = count($res);
        
        $ids = array();
        
        if ($res) {
            
            $fields = array();
            foreach ($res[0] as $key2 => $value2) {
                $fields[] = $key2;
            }            

            $sql_header = "INSERT IGNORE INTO `".Listener::model()->tableName()."` (`".implode('`,`', $fields)."`) VALUES ";
            
            $result_sql = $sql_header;
            foreach ($res as $key => $value) {

                $ids[] = $value['listener_id'];
                $result_sql.= "('".implode("','", $value)."')";
                
                if ($key+1 < $total)  $result_sql .= ", ";
            }
            
            $this->addBackupLog("Prepared ".count($ids)." inserts for listener");
        }
        return array($ids, $result_sql);
    }    
    
    // Table Listener Dependancies
    private function prepareListenerDependantInserts($listener_ids) {
        
        $tables = array(
            ListenerProcess::model()->tableName() => 'listener_id',
        );
                
        $result_sql = array();
        
        foreach ($tables as $table => $log_id_field) {
            $sql = "SELECT * 
                    FROM `".$table."`
                    WHERE `".$log_id_field."` IN (".implode(',',$listener_ids).")";
            $res = Yii::app()->db->createCommand($sql)->queryAll();
            $total = count($res);

            if ($res) {

                $fields = array();
                foreach ($res[0] as $key2 => $value2) {
                    $fields[] = $key2;
                }            

                $sql_header = "INSERT IGNORE INTO `".$table."` (`".implode('`,`', $fields)."`) VALUES ";

                $res_sql = $sql_header;
                $cnt = 0;
                foreach ($res as $key => $value) {

                    $res_sql.= "('".implode("','", $value)."')";

                    if ($key+1 < $total) $res_sql .= ", ";
                    $cnt ++;
                }
                $result_sql[] = $res_sql;
                
                $this->addBackupLog("Prepared ".$cnt." inserts for ".$table);
            }
        }
        return $result_sql;
    } 
        
    

    private function addBackupLog($msg) {
        $log = new BackupOldDataTxLog();
        $log->backup_id = $this->current_backup->id;
        $log->comment   = $msg;
        $log->save();
    }
}

?>
