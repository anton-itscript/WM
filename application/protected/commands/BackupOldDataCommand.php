<?php

/*
 * Is called using command: "php console.php backupOldData", doesn't requires arguements
 * Is called every minute using schtasks
 * 
 * Runs functionality to export old sensors data to backup database
 * according to configurations at "Admin / Setup / DB Backup" page
 * 
 * To avoid parallel running of a few processes of this script - we remember current process's ID.
 * 
 */


class BackupOldDataCommand extends CConsoleCommand 
{
    public function run($args)
    {
		// don't run backup at the most busy minutes
        if (in_array(date('i'), array('00', '01', '15', '16', '30', '31', '45', '46'))){
			return;
        }

        if(Yii::app()->mutex->lock('BackupOldData',60*60)) {
            try {
                $obj = new BackupOldData();
                $obj->run();
            } catch (Exception $e) {
                It::debug('BackupOldDataCommand: backup old data process was failed: '.$e->getMessage(), 'backup_database');
            }
            Yii::app()->mutex->unlock();
        }
    }
}