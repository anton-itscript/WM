<?php

class UpdateScriptForm extends CFormModel {
    
    var $file;
    var $update_zip;
    var $update_version;
    
    public static function model($className=__CLASS__) {
        return parent::model($className);
    }    

    
    public function rules() {
        return array(
            array('update_zip', 'file',  'allowEmpty' => false, 'types' => 'zip'),
            array('update_zip', 'checkUpdateZipName'),
        );
    }    
    
   
    public function checkUpdateZipName() {
        if (!$this->getError('update_zip')) {
            $original_file_name = $this->file->getName();
            
            
             if (!preg_match('/^update_(\d{1,2})_(\d{1,2})_(\d{1,2}).zip$/', $original_file_name, $matches)) {
                 $this->addError('update_zip', 'Name of zipped file has incorrect format.');
                 return false;
             }
             $this->update_version = array('stage' => $matches[1], 'sprint' => $matches[2], 'update' => $matches[3]);
             $current_versions = getConfigValue('version');
             if ($this->update_version['stage'] > $current_versions['stage'] || $this->update_version['sprint'] > $current_versions['sprint'] || $this->update_version['update'] > $current_versions['update']) {
                 
             } else {
                 $this->addError('update_zip', 'Current version is newer than update-file\'s version');
                 return false;                 
             }
        }
        return true;
    }
    
    
    public function processUpdate() {
        
        $result = array();
        $source_path = $this->file->getTempName();
        
        $destination_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR .'files' . DIRECTORY_SEPARATOR . 'updates'; 
        if (!is_dir($destination_path)) {
            mkdir($destination_path, 0777);
        } 
        $destination_path .=  DIRECTORY_SEPARATOR.$this->update_version['stage'].'_'.$this->update_version['sprint'].'_'.$this->update_version['update'];
        if (!is_dir($destination_path)) {
            mkdir($destination_path, 0777);
        }         
        $zip = new ZipArchive() ;

        // open archive
        if ($zip->open($source_path) !== TRUE) {
            $result['errors'][] = 'Could not open archive';
        } else {
            $zip->extractTo($destination_path);
            $result['total_entries'] = $zip->numFiles;
            
            if (file_exists($destination_path.DIRECTORY_SEPARATOR.'src') && file_exists($destination_path.DIRECTORY_SEPARATOR.'update.ini')) {
                It::fullCopy($destination_path.DIRECTORY_SEPARATOR.'src', dirname(Yii::app()->request->scriptFile));

                $update_ini =  $destination_path.DIRECTORY_SEPARATOR.'update.ini';
                $values = parse_ini_file($update_ini, true);       
                InstallConfig::setConfigSection('version', $values['version']);
                
            }
        }

        $zip->close();
        return $result;
    }
    
    
    /*
     * @params: $db_obj object to work with database (sometimes we will need to update remote backup database)
     */
    
    public function m_0_05_02(&$db_obj) {
        
        $sql = "UPDATE `settings` SET `database_version` = '".getConfigValue('version_name')."'";
        $db_obj->createCommand($sql)->query();        
    }    
    
    public function m_0_05_01(&$db_obj) {
        
        $sql = "UPDATE `settings` SET `database_version` = '".getConfigValue('version_name')."'";
        $db_obj->createCommand($sql)->query();        
    }
    
    public function m_0_05_00() {
        
        $db_obj = Yii::app()->db;
        $sql = "ALTER TABLE `sensor_data` CHANGE `is_m` `is_m` ENUM( '1', '0' ) NOT NULL DEFAULT '0'";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        /* db export settings */
        $sql = "ALTER TABLE `settings` ADD `db_exp_enabled` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' AFTER `local_timezone_offset` "; 
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` ADD `db_exp_period` ENUM( '1', '10', '30', '60', '90' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '90' AFTER `db_exp_enabled`";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` ADD `db_exp_frequency` ENUM( '1', '5', '10', '30' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '30' AFTER `db_exp_period` ";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` ADD `db_exp_sql_host` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `db_exp_frequency` ";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` ADD `db_exp_sql_port` SMALLINT( 5 ) UNSIGNED NOT NULL DEFAULT '3306'";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();        

        $sql = "ALTER TABLE `settings` ADD `db_exp_sql_dbname` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `db_exp_sql_port`";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` ADD `db_exp_sql_login` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `db_exp_sql_dbname` ";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `settings` ADD `db_exp_sql_password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `db_exp_sql_login` ";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();

        $sql = "ALTER TABLE `settings` ADD `database_version` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.05.00' AFTER `setting_id`";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();
        
        
        $sql = "ALTER TABLE `settings` CHANGE `current_company_name` `current_company_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();        
        
        
        $sql = "CREATE TABLE `backup_old_data` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `backup_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `data_timestamp_limit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `completed` enum('0','1') NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";      
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();  
        
        $sql = "CREATE TABLE `backup_old_data_log` (
                    `log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `backup_id` int(11) NOT NULL,
                    `comment` varchar(255) DEFAULT NULL,
                    `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                    PRIMARY KEY (`log_id`),
                    KEY `backup_id` (`backup_id`),
                    CONSTRAINT `backup_old_data_log_fk` FOREIGN KEY (`backup_id`) REFERENCES `backup_old_data` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $res = $db_obj->createCommand($sql)->query();
        $res = $db_obj->createCommand("COMMIT")->query();  
        
        
        $values = getConfigValue('path');
        if (!$values['mysql_exe_path']) {
            
            $res = explode(DIRECTORY_SEPARATOR, dirname(php_ini_loaded_file()));
            
            $start_path = $res[0].DIRECTORY_SEPARATOR.$res[1].DIRECTORY_SEPARATOR."mysql.exe"; 
            exec("dir {$start_path} /s/b", $output3);
            $values['mysql_exe_path'] = $output3[0];       

            InstallConfig::setConfigSection('path', $values);
        }          
        
        
        $bat_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR .'files' . DIRECTORY_SEPARATOR . 'at' . DIRECTORY_SEPARATOR .'backupOldData.bat';
        $schedule_bat_content = getConfigValue('php_exe_path') . " -f  ".dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."console.php backupOldData";
        file_put_contents($bat_path, $schedule_bat_content);
        
        $values = getConfigValue('schedule');
        $values['backup_process_id'] = 'delaircoBackupOldDataScript'.date('YmdHi');
        exec('schtasks /create /sc minute /mo 1 /F /ru "SYSTEM" /tn '.$values['backup_process_id'].' /tr '.$bat_path, $output); 
        InstallConfig::setConfigSection('schedule', $values);
       
        
        $this->flushNotification("<br/><br/>...Updated ....");

        
        
    }
    
     public function m_0_4_23() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $sql = "ALTER TABLE `sensor_sea_level_trend` ADD `last_high` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0' AFTER `difference`";   
        $res = Yii::app()->db->createCommand($sql)->query();
        $res = Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_sea_level_trend` ADD `last_low` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0' AFTER `last_high`";
        $res = Yii::app()->db->createCommand($sql)->query();
        $res = Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_sea_level_trend` ADD `is_significant` ENUM( '0', '1' ) NOT NULL DEFAULT '0' AFTER `trend`";
        $res = Yii::app()->db->createCommand($sql)->query();
        $res = Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_sea_level_trend` ADD `last_high_timestamp` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `last_high`";
        $res = Yii::app()->db->createCommand($sql)->query();
        $res = Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_sea_level_trend` ADD `last_low_timestamp` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `last_low`";
        $res = Yii::app()->db->createCommand($sql)->query();
        $res = Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_sea_level_trend` DROP `difference` , DROP `comment`";
        $res = Yii::app()->db->createCommand($sql)->query();
        $res = Yii::app()->db->createCommand("COMMIT")->query();
        

        $this->flushNotification("<br/><br/>...Updated ....");

        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
    }
    
      public function m_0_4_21() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $sql = "SELECT * FROM `".StationSensor::model()->tableName()."` WHERE `handler_id` = 15";        
        
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
            foreach ($res as $key => $value) {
                $sql = "UPDATE  `".StationSensorFeature::model()->tableName()."` 
                        SET `is_main` = 0 WHERE `feature_code` IN ('cloud_height_depth_1', 'cloud_height_depth_2', 'cloud_height_depth_3')";
                $res2 = Yii::app()->db->createCommand($sql)->query();
   
            }
        }
        
        $this->flushNotification("<br/><br/>...Updated ....");

        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
    }    
    
      public function m_0_4_20() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Add new measurement....");
        
   
        
        $sql = "SELECT * FROM `".StationSensor::model()->tableName()."` WHERE `handler_id` = 15";        
        
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
            foreach ($res as $key => $value) {
                $sql = "SELECT * 
                        FROM `".StationSensorFeature::model()->tableName()."` 
                        WHERE sensor_id = '".$value['station_sensor_id']."' AND `feature_code` IN ('cloud_height_height_1', 'cloud_height_depth_1', 'cloud_height_depth_2', 'cloud_height_depth_3')
                        ORDER BY `feature_code`";
                $res2 = Yii::app()->db->createCommand($sql)->queryAll();
                if ($res2 && count($res2) == 1) {
                    $obj = new StationSensorFeature();
                    $obj->sensor_id = $res2[0]['sensor_id'];
                    $obj->feature_code = 'cloud_height_depth_1';
                    $obj->feature_display_name = 'Cloud Depth #1';
                    $obj->feature_constant_value = 0;
                    $obj->measurement_type_code = 'cloud_height';
                    $obj->metric_id = $res2[0]['metric_id'];
                    $obj->is_main = 0;
                    $obj->has_filters = 1;
                    $obj->has_filter_min = 1;
                    $obj->has_filter_max = 1;
                    $obj->has_filter_diff = 1;
                    $obj->is_constant = 0;
                    $obj->is_cumulative = 0;
                    $obj->save();
                    
                    $obj = new StationSensorFeature();
                    $obj->sensor_id = $res2[0]['sensor_id'];
                    $obj->feature_code = 'cloud_height_depth_2';
                    $obj->feature_display_name = 'Cloud Depth #2';
                    $obj->feature_constant_value = 0;
                    $obj->measurement_type_code = 'cloud_height';
                    $obj->metric_id = $res2[0]['metric_id'];
                    $obj->is_main = 0;
                    $obj->has_filters = 1;
                    $obj->has_filter_min = 1;
                    $obj->has_filter_max = 1;
                    $obj->has_filter_diff = 1;
                    $obj->is_constant = 0;
                    $obj->is_cumulative = 0;
                    $obj->save();   
                    
                    $obj = new StationSensorFeature();
                    $obj->sensor_id = $res2[0]['sensor_id'];
                    $obj->feature_code = 'cloud_height_depth_3';
                    $obj->feature_display_name = 'Cloud Depth #3';
                    $obj->feature_constant_value = 0;
                    $obj->measurement_type_code = 'cloud_height';
                    $obj->metric_id = $res2[0]['metric_id'];
                    $obj->is_main = 0;
                    $obj->has_filters = 1;
                    $obj->has_filter_min = 1;
                    $obj->has_filter_max = 1;
                    $obj->has_filter_diff = 1;
                    $obj->is_constant = 0;
                    $obj->is_cumulative = 0;
                    $obj->save();                    
                }
            }
        }
        

        $this->flushNotification("<br/><br/>...Updated ....");

        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }         
    
      public function m_0_4_18() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Add new measurement....");
        
        $sql = "INSERT INTO `refbook_measurement_type` (`measurement_type_id` ,`display_name` ,`code` ,`ord`)
                VALUES ('21', 'Cloud Measuring Range', 'cloud_measuring_range', '18')";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query(); 
              
        $sql = "INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id` ,`measurement_type_id` ,`metric_id` ,`is_main`)
                VALUES ('32', '21', '11', '1'), ('33', '21', '22', '0')";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_data` ADD `is_m` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `sensor_feature_value` ";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "ALTER TABLE `sensor_data` DROP INDEX `sensor_data__feature_measuuring_index` ,
                ADD INDEX `sensor_data__feature_measuuring_index` ( `sensor_feature_id` , `measuring_timestamp` , `is_m` )";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        
        $sql = "SELECT * FROM `".StationSensor::model()->tableName()."` WHERE `handler_id` = 15";        
        
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
            foreach ($res as $key => $value) {
                $sql = "SELECT * 
                        FROM `".StationSensorFeature::model()->tableName()."` 
                        WHERE sensor_id = '".$value['station_sensor_id']."' AND `feature_code` IN ('cloud_height_height_1', 'cloud_measuring_range')
                        ORDER BY `feature_code`";
                $res2 = Yii::app()->db->createCommand($sql)->queryAll();
                if ($res2 && count($res2) == 1) {
                    $obj = new StationSensorFeature();
                    $obj->sensor_id = $res2[0]['sensor_id'];
                    $obj->feature_code = 'cloud_measuring_range';
                    $obj->feature_display_name = 'Measuring Range';
                    $obj->feature_constant_value = 0;
                    $obj->measurement_type_code = 'cloud_measuring_range';
                    $obj->metric_id = $res2[0]['metric_id'];
                    $obj->is_main = 0;
                    $obj->has_filters = 1;
                    $obj->has_filter_min = 1;
                    $obj->has_filter_max = 1;
                    $obj->has_filter_diff = 1;
                    $obj->is_constant = 0;
                    $obj->is_cumulative = 0;
                    $obj->save();
                }
            }
        }
        
        $this->flushNotification("<br/><br/>...Updated ....");

        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }     
    
      public function m_0_4_17() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Update mewasurements & metrics....");
        
        $sql = "INSERT INTO `refbook_measurement_type` (`measurement_type_id`, `display_name`, `code`, `ord`)
                VALUES ('20', 'Sea Level (Wave Height)', 'sea_level_wave_height', '17')";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query(); 
        
        $sql = "UPDATE `refbook_measurement_type` SET `display_name` = 'Sea Level (Mean, Sigma)' WHERE `measurement_type_id` = 18";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query(); 
        
        $sql = "INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`)
                VALUES ('31', '20', '5', '1')";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query(); 
        
        $sql = "UPDATE `station_sensor_feature` SET `metric_id` = '5'  WHERE `feature_code` = 'sea_level_wave_height'";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "UPDATE `sensor_data` `t1` 
                LEFT JOIN `station_sensor_feature` `t2` ON `t2`.`sensor_feature_id` = `t1`.`sensor_feature_id`
                SET `t1`.`metric_id` = '5',  `t1`.`sensor_feature_value` = `t1`.`sensor_feature_value`/10, `t1`.`sensor_feature_normalized_value`  = `t1`.`sensor_feature_normalized_value`/10
                WHERE `t2`.`feature_code` = 'sea_level_wave_height'";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();        
        
        $sql = "UPDATE `sensor_handler_default_feature` 
                SET `metric_id` = '5'
                WHERE `feature_code` = 'sea_level_wave_height'";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();        
        
        $this->flushNotification("<br/><br/>...Updated ....");

        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }        
    
      public function m_0_4_16() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Update settings table....");
        
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `local_timezone_id` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'UTC'")->query();
        Yii::app()->db->createCommand("COMMIT")->query(); 
        
        $this->flushNotification("<br/><br/>...Update settings table....");
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `local_timezone_offset` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '+00:00'")->query(); 
        Yii::app()->db->createCommand("COMMIT")->query();        
        
        $this->flushNotification("<br/><br/>...Create new table....");
        $sql = "CREATE TABLE `schedule_report_destination` (
                  `schedule_destination_id` int(11) NOT NULL AUTO_INCREMENT,
                  `schedule_id` int(11) NOT NULL,
                  `method` varchar(20) NOT NULL DEFAULT 'mail',
                  `destination_email` varchar(255) NOT NULL,
                  `destination_local_folder` varchar(255) NOT NULL,
                  `destination_ip` varchar(15) NOT NULL,
                  `destination_ip_port` smallint(5) NOT NULL DEFAULT '21',
                  `destination_ip_folder` varchar(255) NOT NULL DEFAULT '/',
                  `destination_ip_user` varchar(255) NOT NULL,
                  `destination_ip_password` varchar(255) NOT NULL,
                  PRIMARY KEY (`schedule_destination_id`),
                  KEY `schedule_id` (`schedule_id`),
                  CONSTRAINT `schedule_report_destination_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_report` (`schedule_id`) ON DELETE CASCADE ON UPDATE NO ACTION
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $this->flushNotification("<br/><br/>...Group existed schedules...");

        $sql = "SELECT * from `".ScheduleReport::model()->tableName()."`
                ORDER BY station_id, report_type, report_format, period";        
        
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        
        if ($res) {
            $groupped = array();
            $delete   = array();
            foreach ($res as $key => $value) {
                $groupped_code = $value['station_id'].'_'.$value['report_type'].'_'.$value['period'].'_'.$value['report_format'];
                if (!$groupped[$groupped_code]) {
                    $groupped[$groupped_code] = array('schedule_id' => $value['schedule_id'], 'destinations' => array());   
                } else {
                    $delete[] = $value['schedule_id'];
                }
                $groupped[$groupped_code]['destinations'][] = array(
                    'schedule_id' => $value['schedule_id'],
                    'method' => $value['method'],
                    'destination_email' => $value['destination_email'],
                    'destination_local_folder' => $value['destination_local_folder'],
                    'destination_ip' => $value['destination_ip'],
                    'destination_ip_port' => $value['destination_ip_port'],
                    'destination_ip_folder' => $value['destination_ip_folder'],
                    'destination_ip_user' => $value['destination_ip_user'],
                    'destination_ip_password' => $value['destination_ip_password'],
                );                
            }
            foreach ($groupped as $groupped_code => $groupped_data) {
                $schedule_id = $groupped_data['schedule_id'];
                foreach ($groupped_data['destinations'] as $key => $value) {
                    $dest = new ScheduleReportDestination();
                    $dest->schedule_id = $schedule_id;
                    $dest->method = $value['method'];
                    $dest->destination_email = $value['destination_email'];
                    $dest->destination_local_folder = $value['destination_local_folder'];
                    $dest->destination_ip = $value['destination_ip'];
                    $dest->destination_ip_port = $value['destination_ip_port'];
                    $dest->destination_ip_folder = $value['destination_ip_folder'];
                    $dest->destination_ip_user = $value['destination_ip_user'];
                    $dest->destination_ip_password = $value['destination_ip_password'];
                    $dest->save();
                }
            }
        }        
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }    
    
      public function m_0_4_15() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Update fields types!");
        $sql = "ALTER TABLE `station_calculation_data` CHANGE `value` `value` DECIMAL( 15, 4 ) NOT NULL";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $this->flushNotification("<br/><br/>...Update fields types!");
        $sql = "ALTER TABLE `sensor_data` CHANGE `sensor_feature_value` `sensor_feature_value` DECIMAL( 15, 4 ) NOT NULL";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");        

        $this->flushNotification("<br/><br/>...Update fields types!");
        $sql = "ALTER TABLE `sensor_data` CHANGE `sensor_feature_normalized_value` `sensor_feature_normalized_value` DECIMAL( 15, 4 ) NOT NULL";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");     
         
        
        $sql = "SELECT t2.sensor_data_id, `t1`.`calculation_data_id`, `t1`.`listener_log_id`, `t1`.`value`
                FROM `".StationCalculationData::model()->tableName()."` `t1`
                LEFT JOIN `".  SensorData::model()->tableName()."` `t2` ON `t2`.`listener_log_id` = `t1`.`listener_log_id`
                where    t2.sensor_data_id is null 
                ";        
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
             $total = count($res);
             for ($i = 0; $i < $total; $i ++) {
                 if (!$res[$i]['sensor_data_id']) {
                     StationCalculationData::model()->deleteByPk($res[$i]['calculation_data_id']);
                 }
             }
        }
  
        $sql = "SELECT `t1`.`station_id`, t1.handler_id
                FROM `".StationCalculation::model()->tableName()."` `t1`
                LEFT JOIN `".Station::model()->tableName()."` `t2` ON t2.station_id = t1.station_id";
        $res_stations = Yii::app()->db->createCommand($sql)->queryAll();

       
        $count = count($res_stations);
        $stations       = array();

        
        if (is_array($res_stations) && $count) {
            
            $station_ids = array();
            for ($i = 0; $i < $count; $i++) {
                if (!$stations[$res_stations[$i]['station_id']]) {
                    $stations[$res_stations[$i]['station_id']]['station_object'] = Station::model()->findByPk($res_stations[$i]['station_id']);
                    $stations[$res_stations[$i]['station_id']]['last_logs'] = ListenerLog::getLastMessages($res_stations[$i]['station_id']);
                }
                
                $handler_id = $res_stations[$i]['handler_id'] == 1 ? 'DewPoint' : 'PressureSeaLevel';
                
                $handler = CalculationHandler::create($handler_id);
                if ($stations[$res_stations[$i]['station_id']]['last_logs'][0]['log_id']) {
                    $handler->calculate($stations[$res_stations[$i]['station_id']]['station_object'], $stations[$res_stations[$i]['station_id']]['last_logs'][0]['log_id']); 
                }
                if ($stations[$res_stations[$i]['station_id']]['last_logs'][1]['log_id']) {
                    $handler->calculate($stations[$res_stations[$i]['station_id']]['station_object'], $stations[$res_stations[$i]['station_id']]['last_logs'][1]['log_id']); 
                }                
            }

        }        
        
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }
    
    public function m_0_4_12() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Add new indexes!");
        $sql = "ALTER TABLE `sensor_data` ADD INDEX `sensor_data_station_id_index` ( `station_id` )";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `sensor_data` ADD INDEX `sensor_data_sensor_id_index` ( `sensor_id` ) ";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `refbook_measurement_type` CHANGE `ord` `ord` SMALLINT( 6 ) NOT NULL DEFAULT '0'";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `sensor_data` CHANGE `period` `period` SMALLINT( 6 ) NOT NULL COMMENT 'in minutes'";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `sensor_handler` DROP `description`";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `station_sensor_feature` ADD INDEX `sensor_feature__id_code_index` ( `sensor_id` , `feature_code` )";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `sensor_data` ADD INDEX `sensor_data__log_feature_index` ( `listener_log_id` , `sensor_feature_id` )";   
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `sensor_data` ADD INDEX `sensor_data__feature_measuuring_index` ( `sensor_feature_id` , `measuring_timestamp` )";  
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
        
        $sql = "ALTER TABLE `listener_log` ADD INDEX `listener_log__last_station_failed` ( `is_last` , `station_id` , `failed` )";        
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Done!");
         
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }
        
    public function m_0_4_11() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");           
        
        $this->flushNotification("<br/><br/>...Add new settings columns!");
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__use_fake_sendmail` TINYINT( 1 ) NOT NULL DEFAULT '1'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
         
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__sender_address` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'delairco@gmail.com'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();         
        
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__sender_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Delairco'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();         

        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__sender_password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'delaircoweathermonitor'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();   
        
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__smtp_server` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'smtp.gmail.com'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();         

        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__smtp_port` MEDIUMINT( 9 ) NOT NULL DEFAULT '587'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();          

        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `mail__smtps_support` ENUM( 'auto', 'ssl', 'tls', 'none' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'tls'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();    
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }
    
    public function m_0_4_10() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");          
        
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("<br/><br/>...Drop field `description` from `calculation_handler`!");
        Yii::app()->db->createCommand("ALTER TABLE `calculation_handler` DROP `description`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
         
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report_processed` DROP `serialized_report_problems`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        
        $this->flushNotification("<br/><br/>...Move schduled outputs from database into files !");
        $sql = "SELECT `schedule_processed_id`, `report_string_initial`
                FROM `".ScheduleReportProcessed::model()->tableName()."`";
        $res = Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
            $files_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."schedule_reports";
            if (!is_dir($files_path)) {
                mkdir($files_path, 0777, TRUE);
            }             
            foreach ($res as $key => $value) {
                $file_path = $files_path.DIRECTORY_SEPARATOR.$value['schedule_processed_id'];
                if ($h = fopen($file_path, "w+")) {
                    fwrite($h, $value['report_string_initial']);
                    fclose($h);
                }                
            }
        }
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("<br/><br/>...Delete useless columns !");
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report_processed` DROP `report_string_initial`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report_processed` DROP `report_string_changed`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();        

        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
    }
    
    public function m_0_4_8() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");  
        
        
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification("\n\n...Create table 'sea_level_trend'!");
        
        $sql = "CREATE TABLE `sensor_sea_level_trend` (
              `trend_id` int(11) NOT NULL AUTO_INCREMENT,
              `log_id` int(11) NOT NULL,
              `sensor_id` int(11) NOT NULL,
              `trend` varchar(20) NOT NULL COMMENT 'up / down / no_change / unknown',
              `difference` decimal(11,3) NOT NULL,
              `measuring_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `comment` varchar(255) NOT NULL,
              `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`trend_id`),
              KEY `sensor_sea_level_trend_fk` (`log_id`),
              KEY `sensor_id` (`sensor_id`),
              CONSTRAINT `sensor_sea_level_trend_fk` FOREIGN KEY (`log_id`) REFERENCES `listener_log` (`log_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
              CONSTRAINT `sensor_sea_level_trend_fk1` FOREIGN KEY (`sensor_id`) REFERENCES `station_sensor` (`station_sensor_id`) ON DELETE CASCADE ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";        
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("\n\n...New measurement type!");
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type` (`measurement_type_id` ,`display_name` ,`code` ,`ord`) VALUES (19 , 'Treshold Period', 'treshold_period', '0')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id` ,`measurement_type_id` ,`metric_id` ,`is_main`) VALUES (30 , '19', '13', '1')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }
    
    public function m_0_4_7() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        $this->flushNotification("<br/>...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");       
        
       
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("<br/>...Update `settings` table!");
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `xml_messages_path` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'C:\\\weather_monitor_ftp\\\xml_messages'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("<br/>...Update `settings` table!");
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `xml_check_frequency` TINYINT( 2 ) NOT NULL DEFAULT '15'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("<br/>...Create  `xml_process_log` table!");
        Yii::app()->db->createCommand("CREATE TABLE `xml_process_log` (
              `xml_log_id` int(11) NOT NULL AUTO_INCREMENT,
              `comment` text NOT NULL,
              `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`xml_log_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("<br/>...Add new metric!");
        Yii::app()->db->createCommand("INSERT INTO `refbook_metric` (`metric_id` , `html_code` , `short_name` , `full_name` , `code`) VALUES (24 , 'inHg', 'inHg', 'inHg', 'inHg')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();        
        
        
        $bat_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR .'files' . DIRECTORY_SEPARATOR . 'at' . DIRECTORY_SEPARATOR .'get_xml.bat';
        $schedule_bat_content = getConfigValue('php_exe_path') . " -f  ".dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."console.php getxml";
        file_put_contents($bat_path, $schedule_bat_content);
        
        exec('schtasks /create /sc minute /mo 1 /F /ru "SYSTEM" /tn delaircoGetXml /tr '.$bat_path, $output);
        
        $values = getConfigValue('schedule');
        $values['get_xml_process_id'] = 'delaircoGetXml';
        
        InstallConfig::setConfigSection('schedule', $values); 
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
        
    }
    
    public function m_0_4_5() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        
        $this->flushNotification("<br/>...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 5000)</script>');
    }
    
    public function m_0_4_4() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');   
        
        $values = getConfigValue('path');
        if (!$values['site_url_for_console']) {
            $values['site_url_for_console']  = It::baseUrl();
            InstallConfig::setConfigSection('path', $values);     
        }
        
        $this->flushNotification("<br/>...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");
        Yii::app()->db->createCommand("ALTER TABLE `sensor_data` CHANGE `sensor_feature_value` `sensor_feature_value` DECIMAL( 13, 4 ) NOT NULL")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        Yii::app()->db->createCommand("ALTER TABLE `sensor_data` CHANGE `sensor_feature_normalized_value` `sensor_feature_normalized_value` DECIMAL( 13, 4 ) NOT NULL")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        Yii::app()->db->createCommand("ALTER TABLE `station` ADD `wmo_originating_centre` INT( 11 ) NOT NULL DEFAULT '202' AFTER `wmo_station_number`")->query();        
        Yii::app()->db->createCommand("COMMIT")->query();
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 10000)</script>');
    }
    
    public function m_0_4_3() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');    
        
        $this->flushNotification("\n...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!");
        $this->flushNotification("\n...Change table schedule_report");        
        
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report` ADD `destination_local_folder` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `destination_email`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("\n...Change table settings");   
        Yii::app()->db->createCommand("ALTER TABLE `settings` ADD `scheduled_reports_path` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'C:\\weather_monitor_reports'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();

        $this->flushNotification("\n...Change table schedule_report_processed");  
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report_processed` DROP FOREIGN KEY `schedule_report_processed_ibfk_1`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("\n...Change table schedule_report_processed");  
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report_processed` ADD `check_period_start` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `schedule_id` , ADD `check_period_end` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `check_period_start`")->query() ;
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification("\n...Change table schedule_report_processed"); 
        Yii::app()->db->createCommand("ALTER TABLE `schedule_report_processed` ADD `listener_log_ids` TEXT NOT NULL DEFAULT '' AFTER `listener_log_id`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 10000)</script>');
                 
    }
    
    public function m_0_4_2() {
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');    
        
        $this->flushNotification('...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!');
        $this->flushNotification('...Change table Station');
        Yii::app()->db->createCommand("ALTER TABLE `station` CHANGE `communication_type` `communication_type` ENUM( 'direct', 'sms', 'tcpip', 'gprs' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'direct'")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification('...Change table Station');
        Yii::app()->db->createCommand("ALTER TABLE `station` ADD `communication_esp_ip` VARCHAR( 15 ) NOT NULL DEFAULT '' AFTER `communication_port`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification('...Change table Station');
        Yii::app()->db->createCommand("ALTER TABLE `station` ADD `communication_esp_port` INT( 11 ) NOT NULL DEFAULT '0' AFTER `communication_esp_ip`")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification('...Change table Listener');
        Yii::app()->db->createCommand("ALTER TABLE `listener` CHANGE `port` `source` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $this->flushNotification('...Create table Listener Process');
        $sql = "CREATE TABLE `listener_process` (
              `listener_process_id` int(11) NOT NULL AUTO_INCREMENT,
              `listener_id` int(11) NOT NULL,
              `status` varchar(255) NOT NULL,
              `comment` text NOT NULL,
              `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (`listener_process_id`),
              KEY `listener_id` (`listener_id`),
              CONSTRAINT `listener_process_fk` FOREIGN KEY (`listener_id`) REFERENCES `listener` (`listener_id`) ON DELETE CASCADE ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        Yii::app()->db->createCommand($sql)->query();   
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $bat_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR .'files' . DIRECTORY_SEPARATOR . 'at' . DIRECTORY_SEPARATOR .'prepare.bat';
        $schedule_bat_content = getConfigValue('php_exe_path') . " -f  ".dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."console.php prepare";
        file_put_contents($bat_path, $schedule_bat_content);
        
        exec('schtasks /create /sc minute /mo 1 /F /ru "SYSTEM" /tn delaircoPrepareScript /tr '.$bat_path, $output);
        
        $values = getConfigValue('schedule');

        $values['each_minute_prepare_process_id'] = 'delaircoPrepareScript';
        
        InstallConfig::setConfigSection('schedule', $values);
              
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 10000)</script>');
                
    }
    
    public function m_0_4_1() {
        
        @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);        
        ini_set('memory_limit', '-1');
        //ob_start();
        
        $this->flushNotification('...Please wait... Updating is going on... DO NOT LEAVE THIS PAGE!');
        
        $this->flushNotification('<br/>...Going to add "is_last" fields to `listener_log` table...');
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".ListenerLog::model()->tableName()."` LIKE 'is_last'")->queryAll();
        if (!$res) {
            Yii::app()->db->createCommand("ALTER TABLE `".ListenerLog::model()->tableName()."` ADD `is_last` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_processed`")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $stations = Yii::app()->db->createCommand("SELECT * FROM `".Station::model()->tableName()."`")->queryAll();
            if ($stations) {
                foreach ($stations as $key => $value) 
                    ListenerLog::updateIsLastForStation($value['station_id']);
            }
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... not need');             
        }        
        
        $this->flushNotification('<br/>...Going to add "is_processing" column to `listener_log` table...');
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".ListenerLog::model()->tableName()."` LIKE 'is_processing'")->queryAll();
        if (!$res) {
            Yii::app()->db->createCommand("ALTER TABLE `".ListenerLog::model()->tableName()."` ADD `is_processing` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_processed`")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... not need');             
        }        
       
        // station
        
        $this->flushNotification('<br/><br/>Station table:');
        $this->flushNotification('<br/>...Going to update  "national_aws_number" field at `station` table...'); 
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".Station::model()->tableName()."` LIKE 'national_aws_number'")->queryAll();
        if ($res[0]['Null'] == 'NO') {
            Yii::app()->db->createCommand("ALTER TABLE `station` CHANGE `national_aws_number` `national_aws_number`  int(11) DEFAULT '0'")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... not need');   
        }
        
        $this->flushNotification('<br/>...Going to add new column "country_id" to `station` table...');
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".Station::model()->tableName()."` LIKE 'country_id'")->queryAll();
        if (!$res) {   
            Yii::app()->db->createCommand("ALTER TABLE `station` ADD `country_id` int(11) NOT NULL DEFAULT '0' AFTER `magnetic_north_offset`")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists');
        }    
        
        $this->flushNotification('<br/>...Going to add new column "city_id" to `station` table...');
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".Station::model()->tableName()."` LIKE 'city_id'")->queryAll();
        if (!$res) {   
            Yii::app()->db->createCommand("ALTER TABLE `station` ADD `city_id` int(11) NOT NULL DEFAULT '0' AFTER `country_id`")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists');
        }    
        
        $this->flushNotification('<br/>...Going to add new column "timezone_offset" to `station` table...');  
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".Station::model()->tableName()."` LIKE 'timezone_offset'")->queryAll();
        if (!$res) {
            Yii::app()->db->createCommand("ALTER TABLE `station` ADD `timezone_offset` varchar(20) NOT NULL AFTER `timezone_id`")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists');
        }        
        
        $this->flushNotification('<br/>...Going to update "timezone_offset" data in `station` table...');  
        $sql = "SELECT `station_id`, `timezone_id` FROM `".Station::model()->tableName()."` WHERE `timezone_offset` = ''";
        $res =  Yii::app()->db->createCommand($sql)->queryAll();
        if ($res) {
            foreach ($res as $key => $value) {
                $sql = "UPDATE `".Station::model()->tableName()."` SET `timezone_offset` = '".TimezoneWork::getOffsetFromUTC($value['timezone_id'], 1)."' WHERE `station_id` = '".$value['station_id']."'";
                Yii::app()->db->createCommand($sql)->query();
            }
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... not need');
        }        
        
        $this->flushNotification('<br/>...Going to add new column "awos_msg_source_folder" to `station` table...'); 
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `".Station::model()->tableName()."` LIKE 'awos_msg_source_folder'")->queryAll();
        if (!$res) {
            Yii::app()->db->createCommand("ALTER TABLE `station` ADD `awos_msg_source_folder` TEXT CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL AFTER `city_id`")->query(); 
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists');
        }        
        
        
        // Sensor Data
        
        $this->flushNotification('<br/>...Going to update `sensor_data` table\'s data...');
        Yii::app()->db->createCommand("UPDATE `sensor_data` SET `period` = 1440 WHERE `period` = 86400")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done'); 
        
        //Schedule report
        
        $this->flushNotification('<br/>...Going to create `schedule_report` table...');
        $tables = array();
        $res =  Yii::app()->db->createCommand("SHOW TABLES")->queryAll();
        if ($res) {
            foreach ($res as $key => $value) {
                foreach ($value as $k1 => $v1) {
                    $tables[] = $v1;
                }
            }
        }
        
        if (!in_array('schedule_report', $tables)) {
            $sql = "CREATE TABLE `schedule_report` (
                      `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
                      `report_type` varchar(50) NOT NULL DEFAULT 'synop' COMMENT 'synop, bufr',
                      `station_id` int(11) NOT NULL,
                      `period` int(11) NOT NULL DEFAULT '60' COMMENT 'in minutes',
                      `method` varchar(20) NOT NULL DEFAULT 'email' COMMENT 'email, ftp',
                      `destination_email` varchar(255) NOT NULL,
                      `destination_ip` varchar(15) NOT NULL,
                      `destination_ip_port` int(5) NOT NULL DEFAULT '21',
                      `destination_ip_folder` varchar(255) NOT NULL DEFAULT '/',
                      `destination_ip_user` varchar(255) NOT NULL,
                      `destination_ip_password` varchar(255) NOT NULL,
                      `report_format` varchar(20) NOT NULL DEFAULT 'csv' COMMENT 'txt, csv',
                      `last_scheduled_run_fact` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `last_scheduled_run_planned` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      PRIMARY KEY (`schedule_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            Yii::app()->db->createCommand($sql)->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists'); 
        }
        
        $this->flushNotification('<br/>...Going to create `schedule_report_processed` table...');
        if (!in_array('schedule_report_processed', $tables)) {
            $sql = "CREATE TABLE `schedule_report_processed` (
                      `schedule_processed_id` int(11) NOT NULL AUTO_INCREMENT,
                      `schedule_id` int(11) NOT NULL,
                      `listener_log_id` int(11) NOT NULL,
                      `is_processed` tinyint(1) NOT NULL DEFAULT '0',
                      `report_string_initial` text NOT NULL,
                      `report_string_changed` text NOT NULL,
                      `serialized_report_problems` text NOT NULL,
                      `serialized_report_errors` text NOT NULL,
                      `serialized_report_explanations` text NOT NULL,
                      `is_last` tinyint(1) NOT NULL DEFAULT '0',
                      `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
                      PRIMARY KEY (`schedule_processed_id`),
                      KEY `schedule_id` (`schedule_id`),
                      KEY `listener_log_id` (`listener_log_id`),
                      CONSTRAINT `schedule_report_processed_fk` FOREIGN KEY (`schedule_id`) REFERENCES `schedule_report` (`schedule_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                      CONSTRAINT `schedule_report_processed_ibfk_1` FOREIGN KEY (`listener_log_id`) REFERENCES `listener_log` (`log_id`) ON DELETE CASCADE ON UPDATE NO ACTION
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            Yii::app()->db->createCommand($sql)->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists'); 
        }
        
        // metrics

        $this->flushNotification('<br/><br/>New Metrics:');
        $this->flushNotification('<br/>...Going to add new metric "kJ/sq.m" ...');
        Yii::app()->db->createCommand("INSERT INTO `refbook_metric` (`metric_id`, `html_code`, `short_name`, `full_name`, `code`) VALUES ('21', 'kJ/sq.m', 'kJ/sq.m', 'Kilo Joule per square meter', 'kjoule_per_sq_meter')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');

        $this->flushNotification('<br/>...Going to add new metric "feet"...');
        Yii::app()->db->createCommand("INSERT INTO `refbook_metric` (`metric_id`, `html_code`, `short_name`, `full_name`, `code`) VALUES ('22', 'ft', 'ft', 'Feet', 'feet')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new metric "km"...');
        Yii::app()->db->createCommand("INSERT INTO `refbook_metric` (`metric_id`,`html_code`,`short_name`,`full_name`,`code`) VALUES (23 , 'km', 'km', 'Kilometer', 'kilometer')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new relation between "solar radiation" and "kj/sq.m"...');        
        RefbookMeasurementTypeMetric::model()->deleteByPk(23);
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES ('23', '9', '21', '0')")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new measuring type "Cloud Vertical Visibility"...');
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type` (`measurement_type_id`, `display_name`, `code`, `ord`) VALUES (16 ,'Cloud Vertical Visibility', 'cloud_vertical_visibility', '14')")->query();        
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new measuring type "Cloud Height" ...');
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type` (`measurement_type_id`, `display_name`, `code`, `ord`) VALUES (17, 'Cloud Height', 'cloud_height', 15)")->query();        
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new measuring type "Sea Level" ...');
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type` (`measurement_type_id`, `display_name`, `code`, `ord`) VALUES (18 , 'Sea Level (Mean, Sigma, Wave Hight)', 'sea_level', '16')")->query();        
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add relation between "Cloud Vertical Visibility" and "ft" ...');      
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES (24,16,22,1)")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');

        $this->flushNotification('<br/>...Going to add new relation between "Cloud Vertical Visibility" and "meter"...');      
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES (25,16,11,0)")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new relation between "Cloud Height" and "ft"...');      
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES (26,17,22,1)")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new relation between "Cloud Height" and "meter"...');      
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES (27,17,11,0)")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        $this->flushNotification('<br/>...Going to add new relation between "Visibility" and "meter"...');      
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES (28, 11, 11, 1)")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');        

        $this->flushNotification('<br/>...Going to add new relation between "Sea Level" and "meter"...');      
        Yii::app()->db->createCommand("INSERT INTO `refbook_measurement_type_metric` (`measurement_type_metric_id`, `measurement_type_id`, `metric_id`, `is_main`) VALUES (29, 18, 11, 1)")->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');        
        
        
        
        // sensor handler
        
        $this->flushNotification('<br/>...Going to add new column "awos_station_uses" to `sensor_handler` table...');
        $res =  Yii::app()->db->createCommand("SHOW COLUMNS FROM `sensor_handler` LIKE 'awos_station_uses'")->queryAll();
        if (!$res) {
            $res =  Yii::app()->db->createCommand("ALTER TABLE `sensor_handler` ADD `awos_station_uses` TINYINT( 1 ) NOT NULL DEFAULT '0'")->query();
            Yii::app()->db->createCommand("COMMIT")->query();
            $this->flushNotification(' ... done');
        } else {
            $this->flushNotification(' ... already exists');
        }
        
        $this->flushNotification('<br/>...Going to update `sensor_handler` table...');      
        
        $sql = "UPDATE `sensor_handler` SET 
                `handler_id_code` = 'SeaLevelAWS',
                `display_name` = 'Sea Level and Tide Data',
                `description` = 'Handler \"Sea Level and Tide Data\" : Processes string like \"SL1XXXXYYYYZZZZ\", where <br/>SL1 - device Id; <br/>XXXX - Mean value;<br/>YYYY - Sigma value; <br/>ZZZZ - Wave Height <br/>Example: SL1179017900140 = SL1 sensor sent data: Mean value = 1.79, Sigma value = 1.79, Wave height = 140m.',
                `default_prefix` = 'SL',
                `aws_station_uses` = 1,
                `rain_station_uses` = 0,
                `awos_station_uses` = 0,
                `aws_single_group` = 'sea_level'
                WHERE `handler_id` = 13";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "UPDATE `sensor_handler` SET 
                `handler_id_code` = 'VisibilityAWS',
                `display_name` = 'Visibility',
                `description` = 'Handler \"Visibility\"',
                `default_prefix` = 'VI',
                `aws_station_uses` = 1,
                `rain_station_uses` = 0,
                `awos_station_uses` = 0,
                `aws_single_group` = 'visibility'
                WHERE `handler_id` = 14";
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        
        $sql = "UPDATE `sensor_handler` SET `handler_id_code` = 'CloudHeightAWS',
                `display_name` = 'Cloud Height',
                `description` = 'Handler \"Cloud Height\"',
                `default_prefix` = 'CH',
                `aws_station_uses` = 1,
                `rain_station_uses` = 0,
                `awos_station_uses` = 0,
                `aws_single_group` = 'clouds'
                WHERE `handler_id` = 15";        
        Yii::app()->db->createCommand($sql)->query();
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        

        $this->flushNotification('<br/>...Going to update calculation_handler table...');    
        $res =  Yii::app()->db->createCommand("UPDATE `calculation_handler` SET `display_name` = 'Pressure Adjusted to MSL' WHERE `handler_id` = 2");
        Yii::app()->db->createCommand("COMMIT")->query();
        $this->flushNotification(' ... done');
        
        
        $bat_path = dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR .'files' . DIRECTORY_SEPARATOR . 'at' . DIRECTORY_SEPARATOR .'schedule.bat';
        $schedule_bat_content = getConfigValue('php_exe_path') . " -f  ".dirname(Yii::app()->request->scriptFile). DIRECTORY_SEPARATOR."console.php schedule";
        file_put_contents($bat_path, $schedule_bat_content);
        
        exec('schtasks /create /sc minute /mo 1 /F /ru "SYSTEM" /tn delaircoScheduleScript /tr '.$bat_path, $output);
        
        $values = getConfigValue('schedule');
        $values['each_minute_process_id'] = 'delaircoScheduleScript';
        
        InstallConfig::setConfigSection('schedule', $values);
        
        $values = getConfigValue('path');
        $values['site_url_for_console']  = It::baseUrl();
        InstallConfig::setConfigSection('path', $values);
        
        It::memStatus('update__success');
        $this->flushNotification('<script type="text/javascript"> setTimeout(function(){document.location.href="'.Yii::app()->controller->createUrl('update/index').'"}, 10000)</script>');
        
    }
    
    
    private function flushNotification($msg) {
        print $msg;
        flush();
    }
}
?>